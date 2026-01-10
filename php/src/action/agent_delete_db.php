<?php
session_start();
include '../connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../login.php");
    exit();
}

$agent_id = $_GET['id'];

// Check dependencies? For now, strict FK might block deletion if used. 
// Ideally handle exception.
$sql = "DELETE FROM agent WHERE agent_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $agent_id);

if ($stmt->execute()) {
    header("Location: ../index.php?p=users&tab=supplier&success=ลบซัพพลายเออร์สำเร็จ");
} else {
    // Likely FK constraint error
    header("Location: ../index.php?p=users&tab=supplier&error=ไม่สามารถลบซัพพลายเออร์ได้เนื่องจากมีการใช้งานอยู่");
}

$stmt->close();
$conn->close();
?>
