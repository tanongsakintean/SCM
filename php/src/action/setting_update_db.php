<?php
session_start();
include '../connect.php';

// 1. Auth Check
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Manager')) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$credit_min = (int)$_POST['credit_min'];

// 2. Validation
if ($credit_min < 0) {
    header("Location: ../index.php?p=settings&error=" . urlencode("ยอดเครดิตขั้นต่ำต้องไม่ติดลบ"));
    exit();
}

// 3. Channels Construction
$channels = [];
if (isset($_POST['notify_dashboard'])) $channels[] = 'dashboard';
if (isset($_POST['notify_sms'])) $channels[] = 'sms';
if (isset($_POST['notify_email'])) $channels[] = 'email';

$channels_str = implode(',', $channels);

// 4. Get Old Values (for Logging)
$old_res = $conn->query("SELECT credit_min, notify_channels FROM credit_setting WHERE user_id = 1");
$old_row = $old_res->fetch_assoc();
$old_min = $old_row['credit_min'] ?? 0;
$old_channels = $old_row['notify_channels'] ?? '';

// 5. Update Database
// Assuming user_id=1 for system settings as per existing logic
$sql = "UPDATE credit_setting SET credit_min = ?, notify_channels = ? WHERE user_id = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $credit_min, $channels_str);

if ($stmt->execute()) {
    // 6. System Log
    $log_action = "Update Settings";
    $log_details = "Min Credit: $old_min -> $credit_min, Channels: $old_channels -> $channels_str";
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    $log_stmt = $conn->prepare("INSERT INTO system_log (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $log_stmt->bind_param("isss", $user_id, $log_action, $log_details, $ip_address);
    $log_stmt->execute();
    $log_stmt->close();

    header("Location: ../index.php?p=settings&success=1");
} else {
    header("Location: ../index.php?p=settings&error=" . urlencode("Database Error: " . $stmt->error));
}

$stmt->close();
$conn->close();
?>
