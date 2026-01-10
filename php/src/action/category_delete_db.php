<?php
session_start();
include '../connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../login.php");
    exit();
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
