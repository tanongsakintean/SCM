<?php
session_start();
include '../includes/functions.php';
include '../connect.php';

if (!isset($_SESSION['user_id']) || !has_permission($_SESSION['role_id'] ?? 0, 'settings')) {
    $_SESSION['error'] = "คุณไม่มีสิทธิ์เพิ่มหมวดหมู่";
    header("Location: ../index.php?p=settings");
    exit;
}

$category_name = $_POST['category_name'];

$sql = "INSERT INTO category (category_name) VALUES (?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $category_name);

if ($stmt->execute()) {
    header("Location: ../index.php?p=users&tab=supplier&success=Category+Created");
} else {
    header("Location: ../index.php?p=users&tab=supplier&error=Failed+to+create+category");
}

$stmt->close();
$conn->close();
?>
