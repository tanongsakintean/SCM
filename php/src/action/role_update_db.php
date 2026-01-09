<?php
session_start();
include '../connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role_id = $_POST['role_id'];
    $role_name = $_POST['role_name'];
    // $role_label = $_POST['role_label'];
    $role_label = $role_name; // Fallback or keep existing logic? We should probably keep existing label if not provided, or update to role_name. Let's update to role_name as per request to remove input.
    $is_default = isset($_POST['is_default']) ? 1 : 0;

    if ($is_default == 1) {
        // Unset other defaults
        $conn->query("UPDATE roles SET is_default = 0");
    }

    // Also update is_default for this role
    $sql = "UPDATE roles SET role_name='$role_name', role_label='$role_label', is_default=$is_default WHERE role_id=$role_id";

    if ($conn->query($sql) === TRUE) {
        // ... (existing comments) ...
        
        header("Location: ../index.php?p=users&tab=role&success=แก้ไขสิทธิ์เรียบร้อยแล้ว");
    } else {
        header("Location: ../index.php?p=users&tab=role&error=" . $conn->error);
    }

    $conn->close();
}
?>
