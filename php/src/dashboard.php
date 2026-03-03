<?php
include 'connect.php';

// Fetch System Credit Balance and Settings (User ID 2)
$sql = "SELECT credit_balance, credit_min, notify_channels FROM credit_setting WHERE user_id = 2";
$result = $conn->query($sql);
$credit_balance = 0;
$credit_min = 10000; // Default
$notify_channels = [];

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $credit_balance = $row['credit_balance'];
    $credit_min = $row['credit_min'];
    $notify_channels = explode(',', $row['notify_channels']);
}

// Check for Low Credit Warning
$show_warning = false;
if ($credit_balance < $credit_min && in_array('dashboard', $notify_channels)) {
    $show_warning = true;
}

// Fetch Pending Orders
$sql_pending = "SELECT * FROM purchase_credit WHERE order_status = 'Pending' ORDER BY order_date ASC LIMIT 5";
$result_pending = $conn->query($sql_pending);

// ---- KPI: ยอดขายเครดิตวันนี้ ----
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$sql_today = "SELECT COALESCE(SUM(sale_credit), 0) AS total_today FROM sale WHERE sale_date = '$today'";
$sql_yesterday = "SELECT COALESCE(SUM(sale_credit), 0) AS total_yesterday FROM sale WHERE sale_date = '$yesterday'";
$res_today = $conn->query($sql_today);
$res_yesterday = $conn->query($sql_yesterday);
$sale_today = 0;
$sale_yesterday = 0;
if ($res_today && $row_t = $res_today->fetch_assoc()) {
    $sale_today = $row_t['total_today'];
}
if ($res_yesterday && $row_y = $res_yesterday->fetch_assoc()) {
    $sale_yesterday = $row_y['total_yesterday'];
}
// % เทียบกับเมื่อวาน
$pct_today = 0;
if ($sale_yesterday > 0) {
    $pct_today = round((($sale_today - $sale_yesterday) / $sale_yesterday) * 100, 1);
} elseif ($sale_today > 0) {
    $pct_today = 100;
}

// ---- KPI: ยอดขายเครดิตเดือนปัจจุบัน ----
$this_month_start = date('Y-m-01');
$this_month_end   = date('Y-m-t');
$sql_this_month = "SELECT COALESCE(SUM(sale_credit), 0) AS total_this_month FROM sale WHERE sale_date BETWEEN '$this_month_start' AND '$this_month_end'";
$res_this_month = $conn->query($sql_this_month);
$sale_this_month = 0;
if ($res_this_month && $row_tm = $res_this_month->fetch_assoc()) {
    $sale_this_month = $row_tm['total_this_month'];
}

// ---- KPI: ยอดขายเครดิตเดือนก่อนหน้า ----
$prev_month_start = date('Y-m-01', strtotime('first day of last month'));
$prev_month_end   = date('Y-m-t',  strtotime('last day of last month'));
$sql_prev_month = "SELECT COALESCE(SUM(sale_credit), 0) AS total_prev_month FROM sale WHERE sale_date BETWEEN '$prev_month_start' AND '$prev_month_end'";
$res_prev_month = $conn->query($sql_prev_month);
$sale_prev_month = 0;
if ($res_prev_month && $row_pm = $res_prev_month->fetch_assoc()) {
    $sale_prev_month = $row_pm['total_prev_month'];
}
// % เดือนปัจจุบัน เทียบกับเดือนก่อน
$pct_month = 0;
if ($sale_prev_month > 0) {
    $pct_month = round((($sale_this_month - $sale_prev_month) / $sale_prev_month) * 100, 1);
} elseif ($sale_this_month > 0) {
    $pct_month = 100;
}

// ---- Notification: Pending Orders Count ----
$sql_pending_count = "SELECT COUNT(*) as cnt FROM purchase_credit WHERE order_status = 'Pending'";
$res_pending_count = $conn->query($sql_pending_count);
$pending_count = 0;
if ($res_pending_count) {
    $row_pc = $res_pending_count->fetch_assoc();
    $pending_count = $row_pc['cnt'];
}

// ---- Notification: Approved (รอรับเครดิต) Count ----
$sql_approved_count = "SELECT COUNT(*) as cnt FROM purchase_credit WHERE order_status = 'Approved'";
$res_approved_count = $conn->query($sql_approved_count);
$approved_count = 0;
if ($res_approved_count) {
    $row_ac = $res_approved_count->fetch_assoc();
    $approved_count = $row_ac['cnt'];
}

// ---- Notification: Today's Sales Count ----
$sql_today_sales_count = "SELECT COUNT(*) as cnt FROM sale WHERE sale_date = '$today'";
$res_today_sales_count = $conn->query($sql_today_sales_count);
$today_sales_count = 0;
if ($res_today_sales_count) {
    $row_tsc = $res_today_sales_count->fetch_assoc();
    $today_sales_count = $row_tsc['cnt'];
}
?>
<style>
    /* ===== Dashboard Layout ===== */
    .db-wrapper {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }
    
    .db-grid-main {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 300px;
        gap: 18px;
        align-items: start;
    }

    @media (max-width: 1100px) {
        .db-grid-main { grid-template-columns: 1fr 1fr; }
        .db-col-notify { grid-column: 1 / -1; }
    }
    @media (max-width: 768px) {
        .db-grid-main { grid-template-columns: 1fr; }
    }

    /* Column Stacking */
    .db-col {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    /* ===== Credit Balance Card (Blue) ===== */
    .credit-card {
        background: #3b5bdb; /* Blue from screenshot */
        border-radius: 12px;
        padding: 18px;
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .credit-card .cc-label {
        font-size: 14px;
        font-weight: 500;
        opacity: 0.9;
        margin-bottom: 8px;
    }
    .credit-card .cc-value {
        font-size: 32px;
        font-weight: 700;
        line-height: 1.1;
    }
    .credit-card .cc-unit {
        display: none; /* Hidden in screenshot */
    }
    .credit-card .cc-min {
        margin-top: 12px;
        font-size: 13px;
        background: rgba(255,255,255,0.85);
        color: #666;
        border-radius: 6px;
        display: block;
        padding: 6px 10px;
        font-weight: 500;
    }

    /* ===== Pending Orders Card (White) ===== */
    .pending-card {
        background: #fff;
        border-radius: 12px;
        padding: 18px;
        border: 1px solid #eaeaea;
    }
    .pending-card .pc-label {
        font-size: 14px;
        color: #333;
        font-weight: 500;
        margin-bottom: 8px;
    }
    .pending-card .pc-value {
        font-size: 32px;
        font-weight: 700;
        color: #222;
        line-height: 1.1;
    }
    .pending-card .pc-footer {
        margin-top: 12px;
        font-size: 12px;
        color: #d97706; /* Warning orange */
        background: #fef3c7;
        padding: 6px 10px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    /* ===== KPI Cards (White & Purple) ===== */
    .kpi-card-white {
        background: #fff;
        border-radius: 12px;
        padding: 18px;
        border: 1px solid #eaeaea;
        color: #333;
    }
    
    .kpi-card-purple {
        background: #fff;
        border-radius: 12px;
        position: relative;
        overflow: hidden;
        border: 1px solid #eaeaea;
    }
    .kpi-purple-top {
        background: linear-gradient(135deg, #7c4dff 0%, #a485f6 100%);
        padding: 20px 22px 28px 22px;
        color: #fff;
        position: relative;
    }
    /* SVG Swoosh Background */
    .kpi-purple-top::before {
        content: '';
        position: absolute;
        bottom: -2px; left: 0; right: 0;
        height: 65%;
        background-image: url('data:image/svg+xml;utf8,<svg viewBox="0 0 1440 200" xmlns="http://www.w3.org/2000/svg"><path fill="%23ffffff" fill-opacity="0.2" d="M0,96L80,117.3C160,139,320,181,480,181.3C640,181,800,139,960,117.3C1120,96,1280,96,1360,96L1440,96L1440,200L1360,200C1280,200,1120,200,960,200C800,200,640,200,480,200C320,200,160,200,80,200L0,200Z"></path><path fill="%23ffffff" fill-opacity="0.3" d="M0,160L80,149.3C160,139,320,117,480,122.7C640,128,800,160,960,165.3C1120,171,1280,149,1360,138.7L1440,128L1440,200L1360,200C1280,200,1120,200,960,200C800,200,640,200,480,200C320,200,160,200,80,200L0,200Z"></path></svg>');
        background-size: cover;
        background-position: bottom;
        background-repeat: no-repeat;
    }
    
    .kpi-card-label {
        font-size: 15px;
        font-weight: 500;
        margin-bottom: 0px;
        color: inherit;
        opacity: 0.95;
        position: relative;
        z-index: 2;
    }
    .kpi-card-value {
        font-size: 38px;
        font-weight: 700;
        line-height: 1.1;
        position: relative;
        z-index: 2;
    }
    .kpi-card-unit {
        font-size: 18px;
        font-weight: 500;
        opacity: 0.9;
        margin-left: 2px;
        position: relative;
        z-index: 2;
    }
    
    .kpi-badge-green {
        color: #10b981;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 13.5px;
    }
    .kpi-card-purple .kpi-badge-green.top-badge {
        color: #fff;
        background: rgba(255,255,255,0.22);
        padding: 5px 12px;
        border-radius: 6px;
    }
    .kpi-purple-bottom {
        padding: 16px 22px 20px 22px;
        background: #fff;
        color: #333;
    }
    .kpi-sub-label {
        font-size: 14.5px;
        color: #718096;
        font-weight: 500;
        margin-bottom: 0px;
    }
    .kpi-sub-value {
        font-size: 26px;
        font-weight: 700;
        color: #2d3748;
        margin-top: 8px;
    }

    /* ===== Notification Center ===== */
    .notify-card {
        background: #fff;
        border-radius: 16px;
        padding: 20px 18px;
        box-shadow: 0 2px 14px rgba(0,0,0,0.07);
        min-height: 100%;
    }
    .notify-card .nc-title {
        font-size: 15px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .notify-card .nc-title i {
        color: #764ba2;
    }
    .notify-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 14px;
        border-radius: 12px;
        margin-bottom: 10px;
        text-decoration: none;
        transition: transform 0.15s, box-shadow 0.15s;
    }
    .notify-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 14px rgba(0,0,0,0.10);
        text-decoration: none;
    }
    .notify-item.n-red    { background: #fff1f0; }
    .notify-item.n-orange { background: #fff8e1; }
    .notify-item.n-yellow { background: #fffde7; }
    .notify-item.n-blue   { background: #e8f4fd; }
    .notify-item.n-green  { background: #e8f8f0; }
    .notify-icon {
        width: 36px; height: 36px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }
    .notify-icon.red    { background: #ffcdd2; color: #c62828; }
    .notify-icon.orange { background: #ffe0b2; color: #e65100; }
    .notify-icon.yellow { background: #fff9c4; color: #f57f17; }
    .notify-icon.blue   { background: #bbdefb; color: #1565c0; }
    .notify-icon.green  { background: #c8e6c9; color: #2e7d32; }
    .notify-text {
        display: flex;
        flex-direction: column;
    }
    .notify-text strong { font-size: 13px; color: #2d3748; font-weight: 600; }
    .notify-text span   { font-size: 11.5px; color: #6c757d; margin-top: 2px; }
    .notify-empty {
        text-align: center;
        padding: 30px 0;
        color: #b0bec5;
    }
    .notify-empty i { font-size: 32px; margin-bottom: 8px; display: block; }
    .notify-empty p { font-size: 13px; margin: 0; }
</style>

<div class="content-body">
    <div class="db-wrapper">

        <div class="db-grid-main">
            
            <!-- ====== Column 1: Left (Credit, Pending, Sales KPIs) ====== -->
            <div class="db-col">
                
                <!-- Top Row: Credit SMS & Pending Orders (side-by-side) -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px;">
                    <!-- เครดิต SMS ปัจจุบัน -->
                    <div class="credit-card" style="margin: 0;">
                        <div class="cc-label">เครดิต SMS ปัจจุบัน</div>
                        <div>
                            <span class="cc-value"><?php echo number_format($credit_balance); ?></span>
                        </div>
                        <div class="cc-min">เครดิตขั้นต่ำ: <?php echo number_format($credit_min); ?></div>
                    </div>

                    <!-- คำสั่งซื้อที่รอดำเนินการ -->
                    <div class="pending-card" style="margin: 0; display: flex; flex-direction: column;">
                        <div class="pc-label">คำสั่งซื้อที่รอดำเนินการ</div>
                        <div class="pc-value" style="flex: 1;"><?php echo number_format($pending_count); ?></div>
                        <?php if ($pending_count > 0): ?>
                        <div class="pc-footer">
                            <i class="fas fa-exclamation-triangle"></i>
                            คำสั่งซื้อใหม่ดันขึ้น <?php echo $pending_count; ?> รายการ
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ยอดขายเครดิตวันนี้ (Full width of left column) -->
                <div class="kpi-card-white">
                    <div class="kpi-card-label">ยอดขายเครดิตที่ขายไป (วันนี้)</div>
                    <div>
                        <span class="kpi-card-value"><?php echo number_format($sale_today); ?></span>
                        <span class="kpi-card-unit">เครดิต</span>
                    </div>
                    <div class="kpi-card-footer">
                        <span class="kpi-badge-green">
                            <i class="fas fa-arrow-up"></i>
                            <?php echo abs($pct_today); ?>% จากเมื่อวาน
                        </span>
                    </div>
                </div>

                <!-- ยอดขายเครดิตเดือนปัจจุบัน & เดือนก่อนหน้า (แบ่งครึ่งสีม่วง-ขาวตามรูป) -->
                <div class="kpi-card-purple">
                    <div class="kpi-purple-top">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; position:relative; z-index:2;">
                            <div class="kpi-card-label">ยอดเครดิตที่ขายไป (รายเดือน)</div>
                            <span class="kpi-badge-green top-badge">
                                +<?php echo abs($pct_month); ?>% จากเมื่อวาน
                            </span>
                        </div>
                        <div style="position:relative; z-index:2;">
                            <span class="kpi-card-value"><?php echo number_format($sale_this_month); ?></span>
                            <span class="kpi-card-unit">เครดิต</span>
                        </div>
                    </div>
                    
                    <div class="kpi-purple-bottom">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div class="kpi-sub-label">ยอดเครดิตที่ขายไป (รายเดือน)</div>
                            <span class="kpi-badge-green" style="background:transparent; color:#10b981; padding:0;">
                                +<?php echo abs($pct_month); ?>% จากเดือนก่อน
                            </span>
                        </div>
                        <div>
                            <div class="kpi-sub-value">
                                <?php echo number_format($sale_prev_month); ?> 
                                <span style="font-size:18px; font-weight:500; color:#4a5568; margin-left:2px;">เครดิต</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ====== Column 2: Right (Notifications) ====== -->
            <div class="db-col db-col-notify">
            <div class="notify-card">
                <div class="nc-title">
                    <i class="fas fa-bell"></i> ศูนย์การแจ้งเตือน
                </div>

                <?php
                $has_any = false;

                // 1. เครดิต SMS ต่ำกว่าเกณฑ์ (Critical)
                if (has_permission($role_id, 'settings') && $show_warning):
                    $has_any = true;
                ?>
                <a href="index.php?p=settings" class="notify-item n-red">
                    <div class="notify-icon red"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="notify-text">
                        <strong>⚠️ คำเตือน: เครดิต SMS เหลือน้อย ต่ำกว่าเกณฑ์ขั้นต่ำ</strong>
                        <span>ต่ำกว่า <?php echo number_format($credit_min); ?> เครดิต — คลิกเพื่อดูการตั้งค่า</span>
                    </div>
                </a>
                <?php endif; ?>

                <?php
                // 2. เครดิต SMS เหลือน้อย (Warning แม้ไม่ถึง notify)
                if (has_permission($role_id, 'settings') && $credit_balance < $credit_min && !$show_warning):
                    $has_any = true;
                ?>
                <a href="index.php?p=settings" class="notify-item n-yellow">
                    <div class="notify-icon yellow"><i class="fas fa-battery-quarter"></i></div>
                    <div class="notify-text">
                        <strong>เครดิต SMS เหลือน้อย</strong>
                        <span>ต่ำกว่า <?php echo number_format($credit_min); ?> เครดิต</span>
                    </div>
                </a>
                <?php endif; ?>

                <?php
                // 3. กำลังรออนุมัติ (Pending)
                if (has_permission($role_id, 'approve_orders') && $pending_count > 0):
                    $has_any = true;
                ?>
                <a href="index.php?p=approve_orders" class="notify-item n-orange">
                    <div class="notify-icon orange"><i class="fas fa-spinner fa-spin"></i></div>
                    <div class="notify-text">
                        <strong>กำลังดำเนินการ: รออนุมัติคำสั่งซื้อ</strong>
                        <span><?php echo $pending_count; ?> รายการ รอการอนุมัติ</span>
                    </div>
                </a>
                <?php endif; ?>

                <?php
                // 4. อนุมัติแล้ว รอรับเครดิต (Approved → ยังไม่ Received)
                if (has_permission($role_id, 'receive_credit') && $approved_count > 0):
                    $has_any = true;
                ?>
                <a href="index.php?p=receive_credit" class="notify-item n-blue">
                    <div class="notify-icon blue"><i class="fas fa-inbox"></i></div>
                    <div class="notify-text">
                        <strong>มีคำสั่งซื้ออนุมัติแล้ว รอรับเครดิต</strong>
                        <span><?php echo $approved_count; ?> รายการ รอดำเนินการรับเครดิต</span>
                    </div>
                </a>
                <?php endif; ?>

                <?php
                // 5. การขายเกิดขึ้นวันนี้ (Active process)
                if (has_permission($role_id, 'reports') && $today_sales_count > 0):
                    $has_any = true;
                ?>
                <a href="index.php?p=reports" class="notify-item n-green">
                    <div class="notify-icon green"><i class="fas fa-check-circle"></i></div>
                    <div class="notify-text">
                        <strong>การขายเครดิตปกติ เมื่อวันนี้</strong>
                        <span><?php echo $today_sales_count; ?> รายการ ดำเนินการสำเร็จ</span>
                    </div>
                </a>
                <?php endif; ?>

                <?php
                // รายละเอียด pending orders แต่ละรายการ
                if (has_permission($role_id, 'approve_orders')):
                    $result_pending->data_seek(0);
                    if ($result_pending->num_rows > 0):
                        while($row = $result_pending->fetch_assoc()):
                            $has_any = true;
                            $display_id = !empty($row['order_number']) ? $row['order_number'] : str_pad($row['order_id'], 5, '0', STR_PAD_LEFT);
                ?>
                <a href="index.php?p=approve_orders" class="notify-item n-blue">
                    <div class="notify-icon blue"><i class="fas fa-clock"></i></div>
                    <div class="notify-text">
                        <strong>คำสั่งซื้อใหม่ #<?php echo $display_id; ?></strong>
                        <span>อยู่ระหว่างรอการอนุมัติ — <?php echo date('d/m/Y', strtotime($row['order_date'])); ?></span>
                    </div>
                </a>
                <?php
                        endwhile;
                    endif;
                endif;
                ?>

                <?php if (!$has_any): ?>
                <div class="notify-empty">
                    <i class="fas fa-check-circle" style="color:#b0bec5;"></i>
                    <p>ไม่มีการแจ้งเตือนใหม่</p>
                </div>
                <?php endif; ?>

            </div>
        </div><!-- end .db-row-notify -->

    </div><!-- end .db-wrapper -->
</div>
