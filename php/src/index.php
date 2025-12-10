<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS System</title>
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Specific page styles that need to be global now */
        .custom-select-wrapper { position: relative; }
        .custom-select-wrapper::after { content: '\f107'; font-family: 'Font Awesome 5 Free'; font-weight: 900; position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #888; pointer-events: none; }
        .input-group-text { display: flex; align-items: center; font-size: 14px; color: #888; margin-top: 5px; } 
        .input-group-text i { margin-right: 5px; }
        .filter-group-label { font-size: 14px; color: #888; margin-bottom: 8px; }
        .filter-input { width: 100%; padding: 10px; border: 1px solid #eee; border-radius: 6px; color: #aaa; background-color: white; font-family: 'Prompt', sans-serif; }
        .filter-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 15px; }
        .toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-toolbar { background-color: #0066ff; color: white; border: none; padding: 8px 20px; border-radius: 4px; font-size: 14px; }
        .pagination-container { display: flex; justify-content: flex-end; padding: 10px 20px; background-color: white; border-top: 1px solid #eee; }
    </style>
</head>
<body>

    <div class="wrapper">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Page Content -->
        <div class="content-page">
<?php
            // Navbar Title Logic
            $p = $_GET['p'] ?? 'dashboard';
            $pageTitle = 'Dashboard';
            switch ($p) {
                case 'dashboard': $pageTitle = 'SMS Dashboard'; break;
                case 'sales': $pageTitle = 'ฝ่ายขาย'; break;
                case 'orders': $pageTitle = 'คำสั่งซื้อ'; break;
                case 'approve_orders': $pageTitle = 'อนุมัติคำสั่งซื้อ'; break;
                case 'reports': $pageTitle = 'รายงาน'; break;
                case 'settings': $pageTitle = 'ตั้งค่าระบบ'; break;
                case 'users': $pageTitle = 'จัดการผู้ใช้'; break;
                default: $pageTitle = 'SMS Dashboard';
            }
            // Check if default landing override
            if (!isset($_GET['p'])) {
                if ($_SESSION["role"] != 'Admin' && $_SESSION["role"] != 'Manager') {
                     $pageTitle = 'ฝ่ายขาย'; // Staff lands on sales
                }
            }
            ?>
            <!-- Global Top Navbar -->
            <div class="top-navbar">
                <div class="navbar-title">
                    <h4><?php echo $pageTitle; ?></h4>
                </div>
                <div class="navbar-content">
                    <div class="user-dropdown">
                        <div class="user-info" onclick="toggleDropdown()">
                            <i class="fas fa-user-circle user-avatar"></i>
                            <span class="user-name"><?php echo htmlspecialchars($_SESSION['firstname'] . ' ' . $_SESSION['lastname']); ?></span>
                            <i class="fas fa-chevron-down ml-2" style="font-size: 12px;"></i>
                        </div>
                        <div class="dropdown-menu" id="userDropdown">
                             <div class="dropdown-item-header">
                                <strong><?php echo htmlspecialchars($_SESSION['firstname']); ?></strong>
                                <small><?php echo htmlspecialchars($_SESSION['role']); ?></small>
                             </div>
                             <a href="login.php?logout=1" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt"></i> ออกจากระบบ</a>
                        </div>
                    </div>
                </div>
            </div>

            <?php 
            if (isset($_REQUEST["p"])) {
                include $_REQUEST["p"] . ".php";
            } else {
                if ($_SESSION["role"] == 'Admin' || $_SESSION["role"] == 'Manager') {
                    include "dashboard.php";
                } else {
                    include "sales.php";
                }
            } 
            ?>
        </div>
    </div>

    <!-- Modals that need to be available globally or per page -->
   
    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        window.addEventListener('click', function(event) {
            if (!event.target.matches('.user-info') && !event.target.matches('.user-info *')) {
                const dropdowns = document.getElementsByClassName("dropdown-menu");
                for (let i = 0; i < dropdowns.length; i++) {
                    const openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        });
    </script>
</body>
</html>