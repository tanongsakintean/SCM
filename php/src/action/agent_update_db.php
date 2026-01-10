<?php
session_start();
include '../connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../login.php");
    exit();
}

$agent_id = $_POST['agent_id'];
$agent_name = $_POST['agent_name'];
$agent_phone = $_POST['agent_phone'];
$agent_email = $_POST['agent_email'];

$sql = "UPDATE agent SET agent_name = ?, agent_phone = ?, agent_email = ? WHERE agent_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $agent_name, $agent_phone, $agent_email, $agent_id);

if ($stmt->execute()) {
    header("Location: ../index.php?p=users&tab=supplier&success=แก้ไขข้อมูลซัพพลายเออร์สำเร็จ");
} else {
    header("Location: ../index.php?p=users&tab=supplier&error=แก้ไขข้อมูลซัพพลายเออร์ไม่สำเร็จ");
}

$stmt->close();
$conn->close();
?>
