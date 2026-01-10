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
$ip_address = $_SERVER['REMOTE_ADDR'];

// Validate action
if ($action !== 'Approved' && $action !== 'Rejected') {
    die("Invalid action");
}

/* Transaction to ensure consistency */
$conn->begin_transaction();

try {
    
    // Check validation if Approved
    if ($action === 'Approved') {
       // Validation logic for Purchase Order (Stock In) can be added here if needed.
       // Currently, we just allow the approval to proceed to the 'Receive' stage.
    }

    // 2. Update purchase_credit
    // Use 'Approved' or 'Rejected'
    // Also likely update 'reject_reason' if I had that column, but I will put it in 'approve' table.
    // Wait, prompt output says: "Output Data Flow: Status, Record Approval/Reject Reason".
    // I put Reason in `approve` table.
    $stmt1 = $conn->prepare("UPDATE purchase_credit SET order_status = ? WHERE order_id = ?");
    $stmt1->bind_param("si", $action, $order_id);
    if (!$stmt1->execute()) throw new Exception("Failed to update order status");

    // 3. Insert into approve table
    // approval_date is DATE or DATETIME. The table has DATE. 
    $approval_date = date("Y-m-d");
    $stmt2 = $conn->prepare("INSERT INTO approve (order_id, user_id, approval_status, approval_date, approval_note) VALUES (?, ?, ?, ?, ?)");
    $stmt2->bind_param("iisss", $order_id, $user_id, $action, $approval_date, $note);
    if (!$stmt2->execute()) throw new Exception("Failed to record approval");

    // 4. Insert into system_log (Audit Trail)
    $log_action = "Order " . $action;
    $log_details = "Order ID: $order_id. Note: $note";
    $stmt_log = $conn->prepare("INSERT INTO system_log (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $stmt_log->bind_param("isss", $user_id, $log_action, $log_details, $ip_address);
    if (!$stmt_log->execute()) throw new Exception("Failed to create audit log");
    
    // 5. [Note]: Process 5.0 (Stock Update) should trigger here or be a separate step.
    // Based on requirements, Process 4.0 ends here.
    // Current logic does NOT update credit_balance. It relies on Process 5.0.
    
    $conn->commit();
    header("Location: ../index.php?p=approve_orders&success=" . $action);

} catch (Exception $e) {
    $conn->rollback();
    // header("Location: ../index.php?p=approve_orders&error=" . urlencode($e->getMessage()));
    echo "Error: " . $e->getMessage();
    exit();
}

$conn->close();
?>
