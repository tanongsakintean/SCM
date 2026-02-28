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
$sql_today = "SELECT COALESCE(SUM(sale_credit), 0) AS total_today FROM sale WHERE sale_date = '$today'";
$res_today = $conn->query($sql_today);
$sale_today = 0;
if ($res_today && $row_t = $res_today->fetch_assoc()) {
    $sale_today = $row_t['total_today'];
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

// ---- Notification: Pending Orders Count ----
$sql_pending_count = "SELECT COUNT(*) as cnt FROM purchase_credit WHERE order_status = 'Pending'";
$res_pending_count = $conn->query($sql_pending_count);
$pending_count = 0;
if ($res_pending_count) {
    $row_pc = $res_pending_count->fetch_assoc();
    $pending_count = $row_pc['cnt'];
}
?>
<style>
    .alert-box-warning {
        background-color: #ffebee;
        color: #c62828;
        padding: 15px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        font-weight: 500;
    }
    .alert-box-info {
        background-color: #e3f2fd;
        color: #1565c0;
        padding: 15px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        font-weight: 500;
    }
    .alert-box-process {
        background-color: #fff8e1;
        color: #e65100;
        padding: 15px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        font-weight: 500;
    }
    .alert-icon {
        font-size: 20px;
        margin-right: 15px;
    }

    /* KPI Cards */
    .kpi-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        border-radius: 14px;
        padding: 20px 24px;
        margin-bottom: 16px;
        box-shadow: 0 4px 18px rgba(102,126,234,0.25);
    }
    .kpi-card.green {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        box-shadow: 0 4px 18px rgba(17,153,142,0.25);
    }
    .kpi-card.blue {
        background: linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%);
        box-shadow: 0 4px 18px rgba(33,147,176,0.20);
    }
    .kpi-card .kpi-label {
        font-size: 13px;
        opacity: 0.85;
        margin-bottom: 6px;
        font-weight: 500;
    }
    .kpi-card .kpi-value {
        font-size: 30px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .kpi-card .kpi-unit {
        font-size: 14px;
        font-weight: 400;
        opacity: 0.85;
        margin-left: 4px;
    }
    .kpi-badge {
        display: inline-block;
        font-size: 12px;
        background: rgba(255,255,255,0.2);
        border-radius: 20px;
        padding: 2px 10px;
        margin-top: 6px;
    }
</style>

<div class="content-body">
    <div class="row">
        <!-- Left Column -->
        <div class="col-md-6">
            <!-- Card 1: SMS Credit Balance -->
            <div class="card mb-4">
                <div class="card-title">เครดิต SMS ปัจจุบัน</div>
                <div class="card-value"><?php echo number_format($credit_balance); ?></div>
                <div class="card-footer">เครดิตขั้นต่ำ: <?php echo number_format($credit_min); ?></div>
            </div>

            <!-- Card: Pending Orders -->
            <div class="card mb-4">
                <div class="card-title">คำสั่งซื้อที่รอดำเนินการ</div>
                <div class="card-value"><?php echo number_format($pending_count); ?></div>
                <?php if ($pending_count > 0): ?>
                <div class="card-footer" style="color:#e65100;">
                    <i class="fas fa-exclamation-circle"></i>
                    มีคำสั่งซื้อรออนุมัติ <?php echo $pending_count; ?> รายการ
                </div>
                <?php endif; ?>
            </div>

            <!-- KPI: ยอดขายเครดิตวันนี้ -->
            <div class="kpi-card green">
                <div class="kpi-label">ยอดขายเครดิตที่ขายไป (วันนี้)</div>
                <div class="kpi-value">
                    <?php echo number_format($sale_today); ?>
                    <span class="kpi-unit">เครดิต</span>
                </div>
                <span class="kpi-badge"><?php echo date('d/m/Y'); ?></span>
            </div>

            <!-- KPI: ยอดขายเครดิตเดือนปัจจุบัน -->
            <div class="kpi-card">
                <div class="kpi-label">ยอดขายเครดิตที่ขายไป (เดือนปัจจุบัน)</div>
                <div class="kpi-value">
                    <?php echo number_format($sale_this_month); ?>
                    <span class="kpi-unit">เครดิต</span>
                </div>
                <span class="kpi-badge"><?php echo date('m/Y'); ?></span>
            </div>

            <!-- KPI: ยอดขายเครดิตเดือนก่อนหน้า -->
            <div class="kpi-card blue">
                <div class="kpi-label">ยอดขายเครดิตที่ขายไป (เดือนก่อนหน้า)</div>
                <div class="kpi-value">
                    <?php echo number_format($sale_prev_month); ?>
                    <span class="kpi-unit">เครดิต</span>
                </div>
                <span class="kpi-badge"><?php echo date('m/Y', strtotime('last month')); ?></span>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-md-6">
            <!-- Card: Notifications -->
            <div class="card mb-4" style="min-height: 100%;">
                <div class="card-title" style="margin-bottom: 20px;">ศูนย์การแจ้งเตือน</div>

                <?php if ($show_warning): ?>
                <a href="index.php?p=settings" style="text-decoration: none;">
                    <div class="alert-box-warning">
                        <i class="fas fa-exclamation-triangle alert-icon"></i>
                        <div>คำเตือน: เครดิต SMS เหลือน้อย ต่ำกว่าเกณฑ์ขั้นต่ำ (<?php echo number_format($credit_min); ?>)</div>
                    </div>
                </a>
                <?php endif; ?>

                <?php if ($pending_count > 0): ?>
                <a href="index.php?p=approve_orders" style="text-decoration: none;">
                    <div class="alert-box-process">
                        <i class="fas fa-spinner fa-spin alert-icon"></i>
                        <div>
                            <strong>กำลังดำเนินการ:</strong> มีคำสั่งซื้อที่รออนุมัติ
                            <strong><?php echo $pending_count; ?> รายการ</strong>
                        </div>
                    </div>
                </a>
                <?php endif; ?>

                <?php
                // Fetch each pending order for notification detail
                $result_pending->data_seek(0); // rewind result pointer
                if ($result_pending->num_rows > 0):
                    while($row = $result_pending->fetch_assoc()):
                        $display_id = !empty($row['order_number']) ? $row['order_number'] : str_pad($row['order_id'], 5, '0', STR_PAD_LEFT);
                ?>
                <a href="index.php?p=approve_orders" style="text-decoration: none;">
                    <div class="alert-box-info">
                        <i class="fas fa-clock alert-icon"></i>
                        <div>คำสั่งซื้อใหม่ #<?php echo $display_id; ?> อยู่ระหว่างรอการอนุมัติ</div>
                    </div>
                </a>
                <?php
                    endwhile;
                elseif (!$show_warning && $pending_count == 0):
                ?>
                    <div class="text-muted text-center" style="padding: 20px;">ไม่มีการแจ้งเตือนใหม่</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
