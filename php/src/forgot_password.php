<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลืมรหัสผ่าน - SMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <div class="main-wrapper">
        <div class="page-title">
            <h1>SMS</h1>
            <p style="font-size: 1.2rem; margin-top: 10px; color: #333;">กู้คืนรหัสผ่านของคุณ</p>
        </div>

        <div class="login-container">
            <form action="action/forgot_password_db.php" method="post">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="text-align: left; display: block; margin-bottom: 5px;">ชื่อ (Firstname)</label>
                    <input type="text" name="firstname" class="form-control" required>
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="text-align: left; display: block; margin-bottom: 5px;">นามสกุล (Lastname)</label>
                    <input type="text" name="lastname" class="form-control" required>
                </div>
                <div class="form-group">
                    <label style="text-align: left; display: block; margin-bottom: 5px;">เบอร์โทรศัพท์ (Phone)</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <a href="login.php" class="btn-secondary" style="flex: 1; text-align: center; text-decoration: none; line-height: 38px;">ยกเลิก</a>
                    <button type="submit" class="btn-login" style="flex: 1; margin-top: 0;">ส่งลิงก์กู้คืน</button>
                </div>
            </form>

            <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success" style="margin-top: 15px; text-align: left; font-size: 14px; background-color: #d4edda; color: #155724; padding: 10px; border-radius: 4px;">
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
            <?php endif; ?>

            <?php if(isset($_GET['error'])): ?>
            <div class="error-message" style="display: block; margin-top: 15px;">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
