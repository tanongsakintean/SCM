<?php
session_start();
include '../connect.php';
include '../includes/functions.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id = $_POST['order_id'];
    $user_id = $_SESSION['user_id'];
    $role_id = $_SESSION['role_id'] ?? 0;

    // Validate Input
    if (empty($order_id)) {
        header("Location: ../index.php?p=orders&error=invalid_request");
        exit();
    }
    
    // Instead of directly checking for 'Admin' role, we check if they have specific permissions
    // Usually, admins can do approve_orders and others cannot. Based on previous functions let's rely on that or simplify it since this feature belongs to the requester.
    if (has_permission($role_id, 'approve_orders')) {
         // Those with approval access can cancel any order, others can only cancel their own
         $sql = "SELECT order_status, user_id FROM purchase_credit WHERE order_id = ?";
         $stmt = $conn->prepare($sql);
         $stmt->bind_param("i", $order_id);
    } else {
         // Staff/User: Must match user_id
         $sql = "SELECT order_status, user_id FROM purchase_credit WHERE order_id = ? AND user_id = ?";
         $stmt = $conn->prepare($sql);
         $stmt->bind_param("ii", $order_id, $user_id);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        header("Location: ../index.php?p=orders&error=order_not_found");
        exit();
    }

    $row = $result->fetch_assoc();

    if ($row['order_status'] !== 'Pending') {
        header("Location: ../index.php?p=orders&error=cannot_cancel_processed_order");
        exit();
    }

    // Proceed to Cancel
    $update_stmt = $conn->prepare("UPDATE purchase_credit SET order_status = 'Cancelled' WHERE order_id = ?");
    $update_stmt->bind_param("i", $order_id);

    if ($update_stmt->execute()) {
        // Audit Log
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $log_action = "Cancel Order";
        $log_details = "Cancelled Order ID: $order_id";
        $stmt_log = $conn->prepare("INSERT INTO system_log (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        if ($stmt_log) {
            $stmt_log->bind_param("isss", $user_id, $log_action, $log_details, $ip_address);
            $stmt_log->execute();
        }
        header("Location: ../index.php?p=orders&success_cancel=1");
    } else {
        header("Location: ../index.php?p=orders&error=db_error");
    }

    $stmt->close();
    $update_stmt->close();
    $conn->close();

} else {
    header("Location: ../index.php?p=orders");
    exit();
}
?>
