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
    .alert-icon {
        font-size: 20px;
        margin-right: 15px;
    }
</style>

<div class="content-body">
    <div class="row">
        <!-- Left Column -->
        <div class="col-md-6">
            <!-- Card 1: SMS Credit -->
            <div class="card mb-4">
                <div class="card-title">เครดิต SMS ปัจจุบัน</div>
                <div class="card-value"><?php echo number_format($credit_balance); ?></div>
                <div class="card-footer">เครดิตขั้นต่ำ: <?php echo number_format($credit_min); ?></div>
            </div>

            <!-- Card 3: Pending Orders -->
            <div class="card mb-4">
                <div class="card-title">คำสั่งซื้อที่รอดำเนินการ</div>
                <div class="card-value">3</div>
            </div>

            <!-- Card 4: Sales Today -->
            <div class="card mb-4">
                <div class="card-title">ยอดขายวันนี้</div>
                <div class="card-value">7,500 บาท</div>
                <div class="card-footer">ยอดขายต่อเดือน: 120,000 บาท</div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-md-6">
            <!-- Card 2: Notifications -->
            <div class="card mb-4" style="min-height: 100%;"> <!-- Allow it to be tall -->
                <div class="card-title" style="margin-bottom: 20px;">ศูนย์การแจ้งเตือน</div>
                
                <?php if ($show_warning): ?>
                <a href="index.php?p=settings" style="text-decoration: none;">
                    <div class="alert-box-warning">
                        <i class="fas fa-exclamation-triangle alert-icon"></i>
                        <div>คำเตือน: เครดิต SMS เหลือน้อย ต่ำกว่าเกณฑ์ขั้นต่ำ (<?php echo number_format($credit_min); ?>)</div>
                    </div>
                </a>
                <?php endif; ?>

                <?php if ($result_pending->num_rows > 0): ?>
                    <?php while($row = $result_pending->fetch_assoc()): ?>
                        <?php 
                            $display_id = !empty($row['order_number']) ? $row['order_number'] : str_pad($row['order_id'], 5, '0', STR_PAD_LEFT); 
                        ?>
                        <a href="index.php?p=approve_orders" style="text-decoration: none;">
                            <div class="alert-box-info">
                                <i class="fas fa-clock alert-icon"></i>
                                <div>คำสั่งซื้อใหม่ #<?php echo $display_id; ?> อยู่ระหว่างรอการอนุมัติ</div>
                            </div>
                        </a>
                    <?php endwhile; ?>
                <?php elseif (!$show_warning): ?>
                    <div class="text-muted text-center" style="padding: 20px;">ไม่มีการแจ้งเตือนใหม่</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
