<?php
session_start();
include_once '../connect.php';
include_once '../includes/functions.php';

$role_id = $_SESSION['role_id'] ?? 0;
if (!has_permission($role_id, 'approve_orders')) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
if ($order_id > 0) {
    $stmt = $conn->prepare("UPDATE purchase_credit SET order_status = ? WHERE order_id = ?");
    $stmt->bind_param('si', $_REQUEST["action"], $order_id);
    if ($stmt->execute()) {
        // redirect back with flag for SweetAlert
        if($_REQUEST['action'] == 'Approved'){
            $msg = "อนุมัติคำสั่งซื้อสำเร็จ";
        }else if($_REQUEST['action'] == 'Rejected'){
            $msg = "ปฏิเสธคำสั่งซื้อสำเร็จ";
        }
        header('Location: ../index.php?p=orders&success_approve=1&msg='.$msg);
        exit;
    }
}
// on failure fall back to a generic error
header('Location: ../index.php?p=orders&error=approve_failed');
exit;
?>
