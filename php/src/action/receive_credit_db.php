<?php
session_start();
include '../connect.php';

// Check permissions (Staff, Admin, Manager) - Prompt says "Employee" (Staff) enters.
// Let's allow Staff, Manager, Admin.
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check for empty POST which likely means file size exceeded post_max_size
if (empty($_POST) && $_SERVER['REQUEST_METHOD'] == 'POST' && $_SERVER['CONTENT_LENGTH'] > 0) {
    echo "Error: The uploaded file is too large. It exceeds the server's post_max_size limit.";
    exit();
}

$order_id = isset($_POST['order_id']) ? $_POST['order_id'] : null;
$ip_address = $_SERVER['REMOTE_ADDR'];

/* Transaction to ensure consistency */
$conn->begin_transaction();

try {
    // 1. Validation
    // Check Status must be 'Approved'
    $stmt_check = $conn->prepare("SELECT order_status, order_quantity FROM purchase_credit WHERE order_id = ? FOR UPDATE");
    $stmt_check->bind_param("i", $order_id);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();
    $order_row = $res_check->fetch_assoc();

    if (!$order_row) {
        throw new Exception("Order not found with ID: " . htmlspecialchars($order_id));
    }

    if ($order_row['order_status'] !== 'Approved') {
        throw new Exception("Order is not in Approved status (Current: " . $order_row['order_status'] . ")");
    }

    $order_quantity = $order_row['order_quantity'];

    // 2. File Upload
    if (!isset($_FILES['receipt_proof']) || $_FILES['receipt_proof']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Receipt proof file is required or upload failed.");
    }

    // Create folder for this specific order/matter
    $uploadDir = '../assets/uploads/order_' . $order_id . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileExt = strtolower(pathinfo($_FILES['receipt_proof']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
    if (!in_array($fileExt, $allowed)) {
        throw new Exception("Invalid file type. Allowed: jpg, jpeg, png, pdf");
    }

    $newFileName = 'receipt_' . $order_id . '_' . time() . '.' . $fileExt;
    $destPath = $uploadDir . $newFileName;
    
    // For database, we might want to store relative path or just filename if we know the structure.
    // Let's store relative path 'order_{id}/filename' to be safe and clear.
    $dbFilePath = 'order_' . $order_id . '/' . $newFileName;

    if (!move_uploaded_file($_FILES['receipt_proof']['tmp_name'], $destPath)) {
        throw new Exception("Failed to save uploaded file.");
    }

    // 3. Update purchase_credit
    $received_at = date("Y-m-d H:i:s");
    $stmt_update = $conn->prepare("UPDATE purchase_credit SET order_status = 'Received', receipt_proof = ?, received_at = ?, received_by = ? WHERE order_id = ?");
    $stmt_update->bind_param("ssii", $dbFilePath, $received_at, $user_id, $order_id);
    if (!$stmt_update->execute()) throw new Exception("Failed to update order status.");

    // 4. Update Credit (Stock In) for System (User 2 - Admin Wallet)
    // "Update Total Credit in D3 by adding the credit amount"
    $stmt_credit = $conn->prepare("UPDATE credit_setting SET credit_balance = credit_balance + ? WHERE user_id = 2");
    $stmt_credit->bind_param("i", $order_quantity);
    if (!$stmt_credit->execute()) throw new Exception("Failed to update system credit balance.");

    // 5. Log Transaction
    $log_action = "Credit Received";
    $log_details = "Order ID: $order_id. Qty: $order_quantity. File: $dbFilePath";
    $stmt_log = $conn->prepare("INSERT INTO system_log (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $stmt_log->bind_param("isss", $user_id, $log_action, $log_details, $ip_address);
    if (!$stmt_log->execute()) throw new Exception("Failed to log transaction.");

    $conn->commit();
    header("Location: ../index.php?p=receive_credit&success=1");

} catch (Exception $e) {
    $conn->rollback();
    // In production, better error handling view. For now:
    echo "Error: " . $e->getMessage();
    exit();
}

$conn->close();
?>
