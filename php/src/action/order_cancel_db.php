<?php
session_start();
include '../connect.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id = $_POST['order_id'];
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];

    // Validate Input
    if (empty($order_id)) {
        header("Location: ../index.php?p=orders&error=invalid_request");
        exit();
    }

    // Prepare SQL to check order ownership and status
    // Admin can cancel any pending order? Usually user cancels their own. Let's stick to user cancels own for now as per request.
    // Logic: User must own the order AND status must be 'Pending'.
    
    // However, if Admin wants to cancel, they usually use "Reject". This "Cancel" feature is for the requester.
    if ($role == 'Admin') {
         // Admin might use this too, but let's safe guard it to Pending only
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
