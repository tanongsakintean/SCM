<?php
session_start();
include '../connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../login.php");
    exit();
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
