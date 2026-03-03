<?php
session_start();
include '../includes/functions.php';

if (!isset($_SESSION['user_id']) || !has_permission($_SESSION['role_id'] ?? 0, 'supplier')) {
    $_SESSION['error'] = "คุณไม่มีสิทธิ์ลบข้อมูลซัพพลายเออร์";
    header("Location: ../index.php?p=users&tab=supplier");
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
