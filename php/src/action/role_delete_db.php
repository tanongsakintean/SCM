<?php
session_start();
include '../connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $role_id = $_GET['id'];
    
    // Optional: Check if role is in use
    // $check = $conn->query("SELECT * FROM permission WHERE permission_name = (SELECT role_name FROM roles WHERE role_id=$role_id)");
    // if ($check->num_rows > 0) { ... error ... }

    $sql = "DELETE FROM roles WHERE role_id = $role_id";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../index.php?p=users&tab=role&success=ลบสิทธิ์เรียบร้อยแล้ว");
    } else {
        header("Location: ../index.php?p=users&tab=role&error=" . $conn->error);
    }

    $conn->close();
}
?>
