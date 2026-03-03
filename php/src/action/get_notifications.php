<?php
session_start();
include '../connect.php';
include '../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0, 'notifications' => []]);
    exit;
}

$role_id = $_SESSION['role_id'] ?? 0;
$notifications = [];
$count = 0;

// ─────────────────────────────────────────────
// 1. เครดิต SMS ต่ำกว่าเกณฑ์ → ต้องมี permission: settings
// ─────────────────────────────────────────────
if (has_permission($role_id, 'settings')) {
    $sql_credit = "SELECT credit_balance, credit_min FROM credit_setting WHERE user_id = 2 LIMIT 1";
    $res_credit = $conn->query($sql_credit);
    if ($res_credit && $res_credit->num_rows > 0) {
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
}

// ─────────────────────────────────────────────
// 2. คำสั่งซื้อรออนุมัติ → ต้องมี permission: approve_orders
// ─────────────────────────────────────────────
if (has_permission($role_id, 'approve_orders')) {
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

// ─────────────────────────────────────────────
// 3. รออนุมัติที่รอรับเครดิต → ต้องมี permission: receive_credit
// ─────────────────────────────────────────────
if (has_permission($role_id, 'receive_credit')) {
    $sql_receive = "SELECT COUNT(*) as wait_count FROM purchase_credit WHERE order_status = 'Approved'";
    $res_receive = $conn->query($sql_receive);
    $row_receive = $res_receive->fetch_assoc();
    if ($row_receive['wait_count'] > 0) {
        $notifications[] = [
            'type' => 'info',
            'icon' => 'fa-hand-holding-usd',
            'text' => 'มีคำสั่งซื้อที่อนุมัติแล้วรอรับเครดิต ' . $row_receive['wait_count'] . ' รายการ',
            'link' => 'index.php?p=receive_credit'
        ];
        $count++;
    }
}

echo json_encode([
    'count' => $count,
    'notifications' => $notifications
]);
?>
