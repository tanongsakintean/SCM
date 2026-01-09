<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตั้งรหัสผ่านใหม่ - SMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <div class="main-wrapper">
        <div class="page-title">
            <h1>SMS</h1>
            <p style="font-size: 1.2rem; margin-top: 10px; color: #333;">สร้างรหัสผ่านใหม่</p>
        </div>

        <div class="login-container">
            <?php 
                $token = isset($_GET['token']) ? $_GET['token'] : '';
                $email = isset($_GET['email']) ? $_GET['email'] : '';
            ?>
            
            <form action="action/reset_password_db.php" method="post">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">

                <div class="form-group">
                    <label style="text-align: left; display: block; margin-bottom: 5px;">รหัสผ่านใหม่</label>
                    <input type="password" name="password" class="form-control" placeholder="New Password" required minlength="4">
                </div>
                
                <div class="form-group">
                    <label style="text-align: left; display: block; margin-bottom: 5px;">ยืนยันรหัสผ่านใหม่</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required minlength="4">
                </div>

                <button type="submit" class="btn-login" style="margin-top: 20px;">บันทึกรหัสผ่าน</button>
            </form>

            <?php if(isset($_GET['error'])): ?>
            <div class="error-message" style="display: block; margin-top: 15px;">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
