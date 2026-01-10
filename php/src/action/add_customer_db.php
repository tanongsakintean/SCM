<?php
session_start();
include '../connect.php';

// Check permissions (Admin/Staff)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$name = trim($_POST['customer_name']);
$phone = trim($_POST['customer_phone']);
$email = trim($_POST['customer_email']);

if (empty($name) || empty($phone)) {
    header("Location: ../index.php?p=sales&error=" . urlencode("กรุณากรอกชื่อและเบอร์โทรศัพท์"));
    exit();
}

// Prepare Insert
$sql = "INSERT INTO customer (customer_name, customer_phone, customer_email, credit_balance) VALUES (?, ?, ?, 0)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $name, $phone, $email);

if ($stmt->execute()) {
    header("Location: ../index.php?p=sales&success_add=1");
} else {
    header("Location: ../index.php?p=sales&error=" . urlencode("เพิ่มลูกค้าไม่สำเร็จ: " . $stmt->error));
}

$stmt->close();
$conn->close();
?>
