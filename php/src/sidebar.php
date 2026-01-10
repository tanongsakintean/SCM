<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Ensure connection and functions are available
include_once 'connect.php';

$role = $_SESSION['role'] ?? ''; 
$role_id = $_SESSION['role_id'] ?? 0;
$currentPage = $_GET['p'] ?? 'dashboard'; 
?>
<div class="sidebar">
    <div class="sidebar-header">
        SMS
        <div style="font-size: 0.8rem; margin-top: 5px; color: #aaa;">
            <?php echo htmlspecialchars($role); ?>
        </div>
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="index.php?p=dashboard" class="<?php echo ($currentPage == 'dashboard' || $currentPage == 'home') ? 'active' : ''; ?>">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
        </li>
        
        <?php if (has_permission($role_id, 'sales')): ?>
        <li>
            <a href="index.php?p=sales" class="<?php echo $currentPage == 'sales' ? 'active' : ''; ?>">
                <i class="fas fa-tag"></i> ฝ่ายขาย
            </a>
        </li>
        <?php endif; ?>

        <?php if (has_permission($role_id, 'orders')): ?>
        <li>
            <a href="index.php?p=orders" class="<?php echo $currentPage == 'orders' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-bag"></i> คำสั่งซื้อ
            </a>
        </li>
        <?php endif; ?>

        <?php if (has_permission($role_id, 'approve_orders')): ?>
        <li>
            <a href="index.php?p=approve_orders" class="<?php echo $currentPage == 'approve_orders' ? 'active' : ''; ?>">
                <i class="fas fa-check-square"></i> อนุมัติคำสั่งซื้อ
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (has_permission($role_id, 'receive_credit')): ?>
        <li>
             <a href="index.php?p=receive_credit" class="<?php echo $currentPage == 'receive_credit' ? 'active' : ''; ?>">
                <i class="fas fa-file-import"></i> รับเครดิต
            </a>
        </li>
        <?php endif; ?>

        <?php if (has_permission($role_id, 'reports')): ?>
        <li>
            <a href="index.php?p=reports" class="<?php echo $currentPage == 'reports' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i> รายงาน
            </a>
        </li>
        <?php endif; ?>

        <?php if (has_permission($role_id, 'settings')): ?>
        <li>
            <a href="index.php?p=settings" class="<?php echo $currentPage == 'settings' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i> การตั้งค่า
            </a>
        </li>
        <?php endif; ?>

        <?php if (has_permission($role_id, 'users')): ?>
        <li>
            <a href="index.php?p=users" class="<?php echo $currentPage == 'users' ? 'active' : ''; ?>">
                <i class="fas fa-user"></i> ผู้ใช้
            </a>
        </li>
        <?php endif; ?>
        
    </ul>
</div>
