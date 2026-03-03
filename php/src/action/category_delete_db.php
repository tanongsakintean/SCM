<?php
session_start();
include '../includes/functions.php';
include '../connect.php';

if (!isset($_SESSION['user_id']) || !has_permission($_SESSION['role_id'] ?? 0, 'settings')) {
    $_SESSION['error'] = "คุณไม่มีสิทธิ์ลบหมวดหมู่";
    header("Location: ../index.php?p=settings");
    exit;
}

$category_id = $_GET['id'];

$sql = "DELETE FROM category WHERE category_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $category_id);

if ($stmt->execute()) {
    header("Location: ../index.php?p=users&tab=supplier&success=Category+Deleted");
} else {
    header("Location: ../index.php?p=users&tab=supplier&error=Cannot+delete+category+in+use");
}

$stmt->close();
$conn->close();
?>
