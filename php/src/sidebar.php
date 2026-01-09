<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$role = $_SESSION['role'] ?? ''; 
$currentPage = $_GET['p'] ?? 'dashboard'; // Default to dashboard for matching
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
        
        <?php if ($role == 'Admin' || $role == 'Staff'): ?>
        <li>
            <a href="index.php?p=sales" class="<?php echo $currentPage == 'sales' ? 'active' : ''; ?>">
                <i class="fas fa-tag"></i> ฝ่ายขาย
            </a>
        </li>
        <?php endif; ?>

        <?php if ($role == 'Admin' || $role == 'Staff' || $role == 'Manager'): ?>
        <li>
            <a href="index.php?p=orders" class="<?php echo $currentPage == 'orders' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-bag"></i> คำสั่งซื้อ
            </a>
            <!-- Submenu logic could go here if needed to distinguish order vs approve, usually separate links or role based conditional link -->
        </li>
        <?php if ($role == 'Manager' || $role == 'Admin'): ?>
        <li>
            <a href="index.php?p=approve_orders" class="<?php echo $currentPage == 'approve_orders' ? 'active' : ''; ?>">
                <i class="fas fa-check-square"></i> อนุมัติคำสั่งซื้อ
            </a>
        </li>
        <?php endif; ?>
        <?php endif; ?>
        
        <?php if ($role == 'Admin' || $role == 'Manager' || $role == 'Staff'): ?>
        <li>
             <a href="index.php?p=receive_credit" class="<?php echo $currentPage == 'receive_credit' ? 'active' : ''; ?>">
                <i class="fas fa-file-import"></i> รับเครดิต
            </a>
        </li>
        <?php endif; ?>

        <!-- Let's fix the orders link logic based on role -->
        <!-- Logic: If Manager, go to approve orders. If Staff, go to order form. If Admin, maybe see both? Or default to one? -->
        <!-- I will blindly link to 'orders' for now to match strict prev behavior, but note that approve_orders might be unreachable from sidebar unless I add logic. -->
        <!-- Actually, let's look at step 337 again. It just linked to orders.php. -->
        <!-- So I will link to index.php?p=orders. -->
        
        <!-- Reports -->
        <li>
            <a href="index.php?p=reports" class="<?php echo $currentPage == 'reports' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i> รายงาน
            </a>
        </li>

        <?php if ($role == 'Admin' || $role == 'Manager'): ?>
        <li>
            <a href="index.php?p=settings" class="<?php echo $currentPage == 'settings' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i> การตั้งค่า
            </a>
        </li>
        <?php endif; ?>

        <?php if ($role == 'Admin'): ?>
        <li>
            <a href="index.php?p=users" class="<?php echo $currentPage == 'users' ? 'active' : ''; ?>">
                <i class="fas fa-user"></i> ผู้ใช้
            </a>
        </li>
        <?php endif; ?>
        

    </ul>
</div>
