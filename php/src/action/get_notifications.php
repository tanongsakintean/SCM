<?php
session_start();
include '../connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0, 'notifications' => []]);
    exit;
}

$user_role = $_SESSION['role'];
$notifications = [];
$count = 0;

// 1. Check Credit Balance (Only for Admin/Manager usually, or everyone?) 
// Assuming Admin manages the credit.
if ($user_role == 'Admin' || $user_role == 'Manager') {
    $sql_credit = "SELECT credit_balance, credit_min FROM credit_setting WHERE user_id = 2 LIMIT 1";
    $res_credit = $conn->query($sql_credit);
    if ($res_credit->num_rows > 0) {
        $row = $res_credit->fetch_assoc();
        if ($row['credit_balance'] < $row['credit_min']) {
            $notifications[] = [
                'type' => 'warning',
                'icon' => 'fa-exclamation-triangle',
                'text' => 'เครดิต SMS ต่ำกว่าเกณฑ์ (' . number_format($row['credit_balance']) . ')',
                'link' => 'index.php?p=settings'
            ];
            $count++;
        }
    }

    // 2. Check Pending Orders
    $sql_orders = "SELECT COUNT(*) as pending_count FROM purchase_credit WHERE order_status = 'Pending'";
    $res_orders = $conn->query($sql_orders);
    $row_orders = $res_orders->fetch_assoc();
    if ($row_orders['pending_count'] > 0) {
        $notifications[] = [
            'type' => 'info',
            'icon' => 'fa-clipboard-check',
            'text' => 'มีคำสั่งซื้อรออนุมัติ ' . $row_orders['pending_count'] . ' รายการ',
            'link' => 'index.php?p=approve_orders'
        ];
        $count++;
    }
}

echo json_encode([
    'count' => $count,
    'notifications' => $notifications
]);
?>
