<?php
session_start();
if (isset($_GET['logout'])) {
    session_destroy();
    unset($_SESSION['user_id']);
    unset($_SESSION['role']);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <div class="main-wrapper">
        <div class="page-title">
            <h1>SMS</h1>
            <p style="font-size: 1.2rem; margin-top: 10px; color: #333;">เข้าสู่ระบบบัญชีของคุณ</p>
        </div>

        <div class="login-container">
            <form action="action/login_db.php" method="post">
                <div class="form-group">
                    <input type="text" name="username" class="form-control" placeholder="ชื่อผู้ใช้" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" class="form-control" placeholder="รหัสผ่าน" required>
                </div>

                <button type="submit" class="btn-login">เข้าสู่ระบบ</button>
            </form>

            <?php if(isset($_GET['error'])): ?>
            <div class="error-message" style="display: block;">
                ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง กรุณาลองอีกครั้ง
            </div>
            <?php endif; ?>

            <a href="forgot_password.php" class="forgot-password">ลืมรหัสผ่าน?</a>
        </div>
    </div>

    <?php if(isset($_GET['success'])): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'สำเร็จ!',
            text: '<?php echo htmlspecialchars($_GET['success']); ?>',
            confirmButtonText: 'ตกลง',
            confirmButtonColor: '#28a745'
        });
    </script>
    <?php endif; ?>

</body>
</html>
