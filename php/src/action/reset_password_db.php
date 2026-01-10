<?php
session_start();
include '../connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $token = $conn->real_escape_string($_POST['token']);
    $email = $conn->real_escape_string($_POST['email']);

    if ($password !== $confirm_password) {
        header("Location: ../reset_password.php?token=$token&email=$email&error=รหัสผ่านไม่ตรงกัน");
        exit();
    }

    // Check token validity (basic check: exists and matches email)
    // Ideally check time expiry too (e.g. within 1 hour)
    $stmt = $conn->prepare("SELECT * FROM password_resets WHERE email = ? AND token = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Check expiry (e.g. 1 hour)
        $created_at = strtotime($row['created_at']);
        if (time() - $created_at > 3600) { // 3600 seconds = 1 hour
            header("Location: ../forgot_password.php?error=ลิงก์หมดอายุ กรุณาขอใหม่");
            exit();
        }

        // Get user details for message
        $user_query = "SELECT firstname, lastname FROM user WHERE email = '$email'";
        $user_res = $conn->query($user_query);
        $user_info_str = "";
        if ($user_res->num_rows > 0) {
            $user_data = $user_res->fetch_assoc();
            $user_info_str = "ของ " . $user_data['firstname'] . " " . $user_data['lastname'];
        }

        // Update User Password
        $new_password_hash = md5($password);
        $update_sql = "UPDATE user SET password = '$new_password_hash' WHERE email = '$email'";
        
        if ($conn->query($update_sql) === TRUE) {
            // Delete token
            $conn->query("DELETE FROM password_resets WHERE email = '$email'");
            header("Location: ../login.php?success=" . urlencode("รีเซ็ตรหัสผ่าน" . $user_info_str . " สำเร็จ กรุณาเข้าสู่ระบบ"));
        } else {
            header("Location: ../reset_password.php?token=$token&email=$email&error=เกิดข้อผิดพลาดในการอัปเดตรหัสผ่าน");
        }

    } else {
        header("Location: ../forgot_password.php?error=ลิงก์ไม่ถูกต้องหรือถูกใช้งานไปแล้ว");
    }

    $conn->close();
}
?>
