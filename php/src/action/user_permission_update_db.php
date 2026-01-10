<?php
session_start();
include '../connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $permission_name = $_POST['permission_name'];

    // Check if permission exists for this user
    $check_sql = "SELECT * FROM permission WHERE user_id = $user_id";
    $result = $conn->query($check_sql);

    if ($result->num_rows > 0) {
        // Update existing permission
        $sql = "UPDATE permission SET permission_name = '$permission_name' WHERE user_id = $user_id";
        if ($conn->query($sql) === TRUE) {
            header("Location: ../index.php?p=users&success=อัพเดทสิทธิ์การใช้งานสำเร็จ");
        } else {
            header("Location: ../index.php?p=users&error=" . $conn->error);
        }
    } else {
        // Insert new permission
        $sql = "INSERT INTO permission (user_id, permission_name) VALUES ('$user_id', '$permission_name')";
        if ($conn->query($sql) === TRUE) {
            header("Location: ../index.php?p=users&success=เพิ่มสิทธิ์การใช้งานสำเร็จ");
        } else {
            header("Location: ../index.php?p=users&error=" . $conn->error);
        }
    }

    $conn->close();
}
?>
