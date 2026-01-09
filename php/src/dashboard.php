<?php
include 'connect.php';

// Fetch System Credit Balance (User ID 1)
$sql = "SELECT credit_balance FROM credit_setting WHERE user_id = 1";
$result = $conn->query($sql);
$credit_balance = 0;
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $credit_balance = $row['credit_balance'];
}
?>

<div class="content-body">
    <div class="dashboard-grid">
        <!-- Card 1: SMS Credit -->
        <div class="card">
            <div class="card-title">เครดิต SMS ปัจจุบัน</div>
            <div class="card-value"><?php echo number_format($credit_balance); ?></div>
            <div class="card-footer">เครดิตขั้นต่ำ: 10,000</div>
        </div>

        <!-- Card 2: Notifications -->
        <div class="card">
            <div class="card-title">ศูนย์การแจ้งเตือน</div>
            
            <div class="alert-box">
                <i class="fas fa-exclamation-triangle"></i>
                <div>คำเตือน: เครดิต SMS เหลือน้อย ต่ำกว่าเกณฑ์ขั้นต่ำ</div>
            </div>

            <div class="info-box">
                <i class="fas fa-clock"></i>
                <div>คำสั่งซื้อใหม่ #2024005 อยู่ระหว่างรอการอนุมัติ</div>
            </div>
        </div>

        <!-- Card 3: Pending Orders -->
        <div class="card">
            <div class="card-title">คำสั่งซื้อที่รอดำเนินการ</div>
            <div class="card-value">3</div>
        </div>

        <!-- Card 4: Sales Today -->
        <div class="card">
            <div class="card-title">ยอดขายวันนี้</div>
            <div class="card-value">7,500 บาท</div>
            <div class="card-footer">ยอดขายต่อเดือน: 120,000 บาท</div>
        </div>
    </div>
</div>
