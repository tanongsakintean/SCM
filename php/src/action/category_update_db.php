<?php
session_start();
include '../includes/functions.php';

if (!isset($_SESSION['user_id']) || !has_permission($_SESSION['role_id'] ?? 0, 'settings')) {
    $_SESSION['error'] = "คุณไม่มีสิทธิ์แก้ไขหมวดหมู่";
    header("Location: ../index.php?p=settings");
    exit;
}

$category_id = $_POST['category_id'];
$category_name = $_POST['category_name'];

$sql = "UPDATE category SET category_name = ? WHERE category_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $category_name, $category_id);

if ($stmt->execute()) {
    header("Location: ../index.php?p=users&tab=supplier&success=Category+Updated");
} else {
    header("Location: ../index.php?p=users&tab=supplier&error=Failed+to+update");
}

$stmt->close();
$conn->close();
?>
