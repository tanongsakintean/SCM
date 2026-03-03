<?php
session_start();
include '../connect.php';
include '../includes/functions.php';

// Check permissions (Admin only)
if (!isset($_SESSION['user_id']) || !has_permission($_SESSION['role_id'] ?? 0, 'supplier')) {
    $_SESSION['error'] = "คุณไม่มีสิทธิ์เพิ่มซัพพลายเออร์";
    header("Location: ../index.php?p=users&tab=supplier");
    exit();
}

$agent_name = $_POST['agent_name'];
$agent_phone = $_POST['agent_phone'];
$agent_email = $_POST['agent_email'];

$sql = "INSERT INTO agent (agent_name, agent_phone, agent_email) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $agent_name, $agent_phone, $agent_email);

if ($stmt->execute()) {
    header("Location: ../index.php?p=users&tab=supplier&success=เพิ่มซัพพลายเออร์สำเร็จ");
} else {
    header("Location: ../index.php?p=users&tab=supplier&error=เพิ่มซัพพลายเออร์ไม่สำเร็จ");
}

$stmt->close();
$conn->close();
?>
