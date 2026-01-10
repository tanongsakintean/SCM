<?php
session_start();
include '../connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = $conn->real_escape_string($_POST['firstname']);
    $lastname = $conn->real_escape_string($_POST['lastname']);
    $phone = $conn->real_escape_string($_POST['phone']);

    // Check if user exists matching all 3 fields
    $check_sql = "SELECT * FROM user WHERE firstname = '$firstname' AND lastname = '$lastname' AND phone = '$phone'";
    $result = $conn->query($check_sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $email = $user['email']; // Get email from the found user

        $token = bin2hex(random_bytes(50)); // Generate unique token
        $sql = "INSERT INTO password_resets (email, token) VALUES ('$email', '$token')";
        
        if ($conn->query($sql) === TRUE) {
            // Direct redirect to reset password page
            header("Location: ../reset_password.php?token=" . $token . "&email=" . urlencode($email));
        } else {
            header("Location: ../forgot_password.php?error=เกิดข้อผิดพลาดในการสร้าง Token");
        }
    } else {
        header("Location: ../forgot_password.php?error=ไม่พบข้อมูลผู้ใช้ที่ระบุ (ตรวจสอบ ชื่อ นามสกุล และเบอร์โทรศัพท์)");
    }

    $conn->close();
}
?>
