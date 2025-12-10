<?php
session_start();
include '../connect.php';

// Check permissions
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'Manager' && $_SESSION['role'] != 'Admin')) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_POST['order_id'];
$action = $_POST['action']; // 'Approved' or 'Rejected'
$note = $_POST['note'] ?? '';

// Validate action
if ($action !== 'Approved' && $action !== 'Rejected') {
    die("Invalid action");
}

/* Transaction to ensure consistency */
$conn->begin_transaction();

try {
    // 1. Update purchase_credit
    $stmt1 = $conn->prepare("UPDATE purchase_credit SET order_status = ? WHERE order_id = ?");
    $stmt1->bind_param("si", $action, $order_id);
    if (!$stmt1->execute()) throw new Exception("Failed to update order status");

    // 2. Insert into approve table
    // approval_date is DATE, let's use curdate.
    $approval_date = date("Y-m-d");
    $stmt2 = $conn->prepare("INSERT INTO approve (order_id, user_id, approval_status, approval_date, approval_note) VALUES (?, ?, ?, ?, ?)");
    $stmt2->bind_param("iisss", $order_id, $user_id, $action, $approval_date, $note);
    if (!$stmt2->execute()) throw new Exception("Failed to record approval");

    // 3. If Approved, update credit_setting
    if ($action === 'Approved') {
        // Get order quantity
        $res = $conn->query("SELECT order_quantity FROM purchase_credit WHERE order_id = $order_id");
        $order_row = $res->fetch_assoc();
        $qty = $order_row['order_quantity'];

        // Update Admin's wallet (User ID 1) - OR Update the FIRST finding settings?
        // Let's safe bet: Update where user_id = 1.
        $stmt3 = $conn->prepare("UPDATE credit_setting SET credit_balance = credit_balance + ? WHERE user_id = 1");
        $stmt3->bind_param("i", $qty);
        if (!$stmt3->execute()) throw new Exception("Failed to update credit balance");
    }

    $conn->commit();
    header("Location: ../index.php?p=approve_orders&success=1");

} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}

$conn->close();
?>
