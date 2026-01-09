<?php
session_start();
include '../connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role_name = $_POST['role_name'];
    // $role_label = $_POST['role_label']; // Removed
    // $role_label = $role_name; // Use role_name as label for now -> Removed as per request
    $is_default = isset($_POST['is_default']) ? 1 : 0;

    // Check duplicate
    $check = $conn->query("SELECT * FROM roles WHERE role_name = '$role_name'");
    if ($check->num_rows > 0) {
        header("Location: ../index.php?p=users&tab=role&error=ชื่อสิทธิ์นี้มีอยู่แล้ว");
        exit();
    }

    if ($is_default == 1) {
        // Unset other defaults
        $conn->query("UPDATE roles SET is_default = 0");
    }

    $sql = "INSERT INTO roles (role_name, is_default) VALUES ('$role_name', $is_default)";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../index.php?p=users&tab=role&success=เพิ่มสิทธิ์เรียบร้อยแล้ว");
    } else {
        header("Location: ../index.php?p=users&tab=role&error=" . $conn->error);
    }

    $conn->close();
}
?>
