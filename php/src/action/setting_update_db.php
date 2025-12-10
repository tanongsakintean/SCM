<?php
session_start();
include '../connect.php';

// Check permissions
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'Manager' && $_SESSION['role'] != 'Admin')) {
    header("Location: ../login.php");
    exit();
}

$credit_min = str_replace(',', '', $_POST['credit_min']);

// Validation
if (!is_numeric($credit_min) || $credit_min < 0) {
    header("Location: ../index.php?p=settings&error=invalid_value");
    exit();
}

// Update settings for Admin (User ID 1)
$sql = "UPDATE credit_setting SET credit_min = ? WHERE user_id = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $credit_min);

if ($stmt->execute()) {
    header("Location: ../index.php?p=settings&success=1");
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
