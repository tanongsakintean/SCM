<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
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

            <div class="error-message">
                ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง กรุณาลองอีกครั้ง
            </div>

            <a href="forgotpassword.php" class="forgot-password">ลืมรหัสผ่าน?</a>
        </div>
    </div>

</body>
</html>
