<?php
session_start();
include '../connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../login.php");
    exit();
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
