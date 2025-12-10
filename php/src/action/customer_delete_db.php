<?php
session_start();
include '../connect.php';

if (isset($_GET['id'])) {
    $customer_id = $_GET['id'];
    
    $sql = "DELETE FROM customer WHERE customer_id = $customer_id";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: ../index.php?p=users&tab=customer&success=ลบลูกค้าสำเร็จ");
    } else {
        header("Location: ../index.php?p=users&tab=customer&error=" . $conn->error);
    }
    
    $conn->close();
}
?>
