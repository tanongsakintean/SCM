<?php
session_start();
include '../connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    
    // Check for duplicate username
    $check_sql = "SELECT * FROM user WHERE username = '$username'";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows > 0) {
        header("Location: ../index.php?p=users&error=ชื่อผู้ใช้นี้มีอยู่ในระบบแล้ว");
        exit();
    }
    
    // Check for duplicate email
    $check_email_sql = "SELECT * FROM user WHERE email = '$email'";
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
    
    $password = md5($password);
    
    $sql = "INSERT INTO user (username, password, firstname, lastname, address, email, phone) 
            VALUES ('$username', '$password', '$firstname', '$lastname', '$address', '$email', '$phone')";
            
    if ($conn->query($sql) === TRUE) {
        $last_id = $conn->insert_id;
        $last_id = $conn->insert_id;
        
        // Fetch Role from form or use default
        $role_name = 'User'; // Fallback
        
        if (isset($_POST['role']) && !empty($_POST['role'])) {
            $role_name = $_POST['role'];
        } else {
             // If not provided, find default
             $default_role_result = $conn->query("SELECT role_name FROM roles WHERE is_default = 1 LIMIT 1");
             if ($default_role_result && $default_role_result->num_rows > 0) {
                 $default_role = $default_role_result->fetch_assoc();
                 $role_name = $default_role['role_name'];
             }
        }

        $sql_perm = "INSERT INTO permission (user_id, permission_name) VALUES ('$last_id', '$role_name')";
        $conn->query($sql_perm);
        
        header("Location: ../index.php?p=users&success=เพิ่มผู้ใช้สำเร็จ");
    } else {
        header("Location: ../index.php?p=users&error=" . $conn->error);
    }
    
    $conn->close();
}
?>
