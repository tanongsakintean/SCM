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
    <title>Login - SMS Credit Management</title>
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap');

        body {
            background-color: #ffffff; /* Revert to white based on last image look, or kept light gray? Image background looks white or very light gray. Keeping site style #F4F6F9 usually better but card is white. Let's stick to #F4F6F9 for contrast unless requested otherwise. Wait, image shows white background outside? The card has shadow, so bg must be different. */
            background-color: #fff; /* Actually the latest image looks like it might be just white or very faint. Let's use #fff to match "clean" look or stick to F4F6F9. The image 1768093713301.png shows a shadow around the card, implying a non-white background, or a border. The border is visible. Let's use white background for BODY as requested "like this". */
            font-family: 'Prompt', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .login-container {
            width: 100%;
            max-width: 480px;
            padding: 20px;
            text-align: center;
        }

        .login-card {
            background: #fff;
            border-radius: 20px;
            /* box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); Remove shadow to match flat border look if needed, but image has shadow? Image has a border radius and a border. */
            border: 1px solid #e0e0e0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            padding: 40px;
            text-align: left; /* Inputs are left aligned? No, placeholders are left. */
            position: relative;
        }

        /* Brand Section */
        .brand-section {
            margin-bottom: 30px;
            text-align: center;
        }
        
        .brand-icon {
            font-size: 50px;
            color: #3399ff; /* Brighter blue icon */
            margin-bottom: 10px;
            display: inline-block;
        }

        .brand-icon i {
             /* transform: rotate(-20deg);  Image shows paper plane pointing up-right standard fa-paper-plane */
        }

        .brand-title {
            font-size: 32px;
            font-weight: 700;
            color: #003366; /* Dark Blue */
            margin: 0;
            line-height: 1.2;
        }

        .brand-subtitle {
            font-size: 20px;
            font-weight: 700;
            color: #003366;
            margin-top: 5px;
            margin-bottom: 0;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            font-size: 16px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            background-color: #fff;
            box-sizing: border-box; 
            font-family: 'Prompt', sans-serif;
            color: #495057;
        }

        .form-control:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .form-control::placeholder {
            color: #6c757d;
            opacity: 0.8;
        }

        .btn-container {
            text-align: center;
            margin-top: 10px;
        }

        .btn-login {
            background-color: #0d6efd;
            color: #fff;
            border: none;
            padding: 10px 40px; /* Width based on content, or full? Image shows button is NOT full width. It's centered. */
            font-size: 18px;
            font-weight: 500;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
            font-family: 'Prompt', sans-serif;
        }

        .btn-login:hover {
            background-color: #0b5ed7;
        }

        /* Footer Section */
        .error-message {
            color: red;
            margin-top: 15px;
            font-size: 16px;
            text-align: center;
            display: block; 
        }

        .forgot-password-container {
            text-align: center;
            margin-top: 15px;
        }

        .forgot-password {
            color: #0d6efd;
            text-decoration: underline;
            font-size: 16px;
            background: none;
            border: none;
            cursor: pointer;
        }

        .forgot-password:hover {
            color: #0a58ca;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <!-- Brand Section Outside Card -->
        <div class="brand-section">
            <div class="brand-icon">
                <i class="fas fa-paper-plane"></i>
            </div>
            <h1 class="brand-title">JP Digital Agency</h1>
            <h2 class="brand-subtitle">SMS Credit Management System</h2>
        </div>

        <div class="login-card">
            <!-- Login Form -->
            <form action="action/login_db.php" method="post">
                <div class="form-group">
                    <input type="text" name="username" class="form-control" placeholder="ชื่อผู้ใช้" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" class="form-control" placeholder="รหัสผ่าน" required>
                </div>

                <div class="btn-container">
                    <button type="submit" class="btn-login">เข้าสู่ระบบ</button>
                </div>
            </form>

            <!-- Error Message -->
            <?php if(isset($_GET['error'])): ?>
            <div class="error-message" style="display: block;">
                ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง กรุณาลองอีกครั้ง
            </div>
            <?php endif; ?>

            <!-- Forgot Password -->
             <div class="forgot-password-container">
                <a href="forgot_password.php" class="forgot-password">ลืมรหัสผ่าน?</a>
             </div>
        </div>
    </div>

    <!-- Success Message -->
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
