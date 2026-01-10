<?php
session_start();
include '../connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    
    // Optional Password Update
    $password_sql = "";
    if (!empty($_POST['password'])) {
        $password = md5($_POST['password']);
        $password_sql = ", password = '$password'";
    }
    
    // Check for duplicate username (excluding current user)
    $check_sql = "SELECT * FROM user WHERE username = '$username' AND user_id != $user_id";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows > 0) {
        header("Location: ../index.php?p=users&error=ชื่อผู้ใช้นี้มีอยู่ในระบบแล้ว");
        exit();
    }

    // Check for duplicate email (excluding current user)
    $check_email_sql = "SELECT * FROM user WHERE email = '$email' AND user_id != $user_id";
    $check_email_result = $conn->query($check_email_sql);
    
    if ($check_email_result->num_rows > 0) {
        header("Location: ../index.php?p=users&error=อีเมลนี้มีอยู่ในระบบแล้ว");
        exit();
    }

     // Validate Phone Format (Thai)
     if (!preg_match("/^0[0-9]{8,9}$/", $phone)) {
        header("Location: ../index.php?p=users&error=รูปแบบเบอร์โทรศัพท์ไม่ถูกต้อง");
        exit();
    }
    
    // Validate Email Format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../index.php?p=users&error=รูปแบบอีเมลไม่ถูกต้อง");
        exit();
    }
    
    $sql = "UPDATE user SET 
            username = '$username', 
            firstname = '$firstname', 
            lastname = '$lastname', 
            address = '$address', 
            email = '$email', 
            phone = '$phone' 
            $password_sql 
            WHERE user_id = $user_id";
            
    if ($conn->query($sql) === TRUE) {
        header("Location: ../index.php?p=users&success=แก้ไขข้อมูลผู้ใช้สำเร็จ");
    } else {
        header("Location: ../index.php?p=users&error=" . $conn->error);
    }
    
    $conn->close();
}
?>
