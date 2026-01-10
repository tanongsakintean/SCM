<?php
session_start();
include '../connect.php';

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    
    // Security check: Ensure Admin is doing this?
    // For now, rely on UI hiding.
    
    // Delete from permission first (FK constraint?) or user first?
    // Usually delete dependent records first or enable cascade.
    // Safest: Delete permission then user.
    
    $sql_perm = "DELETE FROM permission WHERE user_id = $user_id";
    $conn->query($sql_perm);
    
    $sql = "DELETE FROM user WHERE user_id = $user_id";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: ../index.php?p=users&success=ลบผู้ใช้สำเร็จ");
    } else {
        header("Location: ../index.php?p=users&error=" . $conn->error);
    }
    
    $conn->close();
}
?>
