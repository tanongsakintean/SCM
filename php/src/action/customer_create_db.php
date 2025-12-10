<?php
session_start();
include '../connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_name = $_POST['customer_name'];
    $customer_phone = $_POST['customer_phone'];
    $customer_email = $_POST['customer_email'];
    
    // Check duplicate email
    $check_email_sql = "SELECT * FROM customer WHERE customer_email = '$customer_email'";
    $check_email_result = $conn->query($check_email_sql);
    
    if ($check_email_result->num_rows > 0) {
         header("Location: ../index.php?p=users&tab=customer&error=อีเมลลูกค้านี้มีอยู่ในระบบแล้ว");
         exit();
    }

    // Validate Phone Format (Thai)
    if (!preg_match("/^0[0-9]{8,9}$/", $customer_phone)) {
         header("Location: ../index.php?p=users&tab=customer&error=รูปแบบเบอร์โทรศัพท์ไม่ถูกต้อง");
         exit();
    }

    // Validate Email Format
    if (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
         header("Location: ../index.php?p=users&tab=customer&error=รูปแบบอีเมลไม่ถูกต้อง");
         exit();
    }
    
    $sql = "INSERT INTO customer (customer_name, customer_phone, customer_email) 
            VALUES ('$customer_name', '$customer_phone', '$customer_email')";
            
    if ($conn->query($sql) === TRUE) {
        header("Location: ../index.php?p=users&tab=customer&success=เพิ่มลูกค้าสำเร็จ");
    } else {
        header("Location: ../index.php?p=users&tab=customer&error=" . $conn->error);
    }
    
    $conn->close();
}
?>
