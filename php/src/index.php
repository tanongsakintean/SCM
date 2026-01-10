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
    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- jQuery, Popper.js, Bootstrap 4 JS (Moved to HEAD for dependency handling) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        /* Specific page styles that need to be global now */
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
                    <!-- Notification Bell -->
                    <div class="notification-dropdown mr-3" style="position: relative;">
                        <div class="notification-icon" onclick="toggleNotification()" style="cursor: pointer; position: relative; padding: 5px;">
                            <i class="fas fa-bell" style="font-size: 20px; color: #555;"></i>
                            <span class="badge badge-danger notification-badge" id="notify-count" style="display: none; position: absolute; top: -5px; right: -5px; font-size: 10px; border-radius: 50%;">0</span>
                        </div>
                        <div class="dropdown-menu dropdown-menu-right" id="notificationDropdown" style="width: 300px; padding: 0;">
                             <div class="dropdown-header" style="padding: 10px 15px; border-bottom: 1px solid #eee; font-weight: 600;">
                                การแจ้งเตือน
                             </div>
                             <div id="notification-list" style="max-height: 300px; overflow-y: auto;">
                                 <!-- Items will be loaded here -->
                                 <div class="text-center text-muted p-3">ไม่มีการแจ้งเตือนใหม่</div>
                             </div>
                        </div>
                    </div>

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
                $p = $_REQUEST["p"];
                $protected_pages = ['sales', 'orders', 'approve_orders', 'receive_credit', 'reports', 'settings', 'users'];
                
                // Check if page requires permission
                if (in_array($p, $protected_pages)) {
                    $role_id = $_SESSION['role_id'] ?? 0;
                    if (has_permission($role_id, $p)) {
                        include $p . ".php";
                    } else {
                        // Access Denied
                        echo '<div class="alert alert-danger" style="margin: 20px;">
                                <i class="fas fa-exclamation-triangle"></i> คุณไม่มีสิทธิ์เข้าถึงหน้านี้ (Access Denied)
                              </div>';
                        include "dashboard.php";
                    }
                } else {
                    // Public/Dashboard pages
                    include $p . ".php";
                }
            } else {
                if ($_SESSION["role"] == 'Admin' || $_SESSION["role"] == 'Manager') {
                    include "dashboard.php";
                } else {
                    // Default logic: Check if they have sales permission, else dashboard
                    $role_id = $_SESSION['role_id'] ?? 0;
                    if (has_permission($role_id, 'sales')) {
                        include "sales.php";
                    } else {
                        include "dashboard.php";
                    }
                }
            } 
            ?>
        </div>
    </div>

    <!-- Modals that need to be available globally or per page -->
   
    <script>
        function toggleNotification() {
            const dropdown = document.getElementById('notificationDropdown');
            dropdown.classList.toggle('show');
            // Close user dropdown if open
            document.getElementById('userDropdown').classList.remove('show');
        }

        function toggleDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
            // Close notification dropdown if open
            document.getElementById('notificationDropdown').classList.remove('show');
        }

        // Close dropdowns when clicking outside
        window.addEventListener('click', function(event) {
            if (!event.target.closest('.user-info') && !event.target.closest('.notification-icon') && !event.target.closest('.dropdown-menu')) {
                const dropdowns = document.getElementsByClassName("dropdown-menu");
                for (let i = 0; i < dropdowns.length; i++) {
                    dropdowns[i].classList.remove('show');
                }
            }
        });

        // Notification System
        function fetchNotifications() {
            fetch('action/get_notifications.php')
                .then(response => response.json())
                .then(data => {
                    const countBadge = document.getElementById('notify-count');
                    const listContainer = document.getElementById('notification-list');
                    
                    // Update Badge
                    if (data.count > 0) {
                        countBadge.innerText = data.count;
                        countBadge.style.display = 'block';
                    } else {
                        countBadge.style.display = 'none';
                    }

                    // Update List
                    if (data.notifications.length > 0) {
                        let html = '';
                        data.notifications.forEach(item => {
                            let colorClass = item.type === 'warning' ? 'text-danger' : 'text-primary';
                            let bgClass = item.type === 'warning' ? '#fff3cd' : '#e3f2fd';
                            
                            html += `
                                <a href="${item.link}" class="dropdown-item" style="padding: 10px 15px; border-bottom: 1px solid #f8f9fa; white-space: normal;">
                                    <div style="display: flex; align-items: start;">
                                        <div style="margin-right: 10px; margin-top: 3px; color: ${item.type === 'warning' ? '#dc3545' : '#0066ff'};">
                                            <i class="fas ${item.icon}"></i>
                                        </div>
                                        <div>
                                            <div style="font-size: 13px; font-weight: 500;">${item.text}</div>
                                            <small class="text-muted">เมื่อสักครู่</small>
                                        </div>
                                    </div>
                                </a>
                            `;
                        });
                        listContainer.innerHTML = html;
                    } else {
                        listContainer.innerHTML = '<div class="text-center text-muted p-3">ไม่มีการแจ้งเตือนใหม่</div>';
                    }
                })
                .catch(error => console.error('Error fetching notifications:', error));
        }

        // Initial fetch and poller
        document.addEventListener('DOMContentLoaded', function() {
            fetchNotifications();
            setInterval(fetchNotifications, 30000); // Poll every 30 seconds
        });
    </script>
</body>
</html>