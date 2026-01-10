<?php
session_start();
include '../connect.php';

// Check permissions
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$customer_id = $_POST['customer_id'];
$sale_credit = $_POST['sale_credit']; // Amount of credit sold
$sale_price = $_POST['sale_price'];   // Price per unit (optional logic? or total price?)
                                      // Data dict says: sale_price (DECIMAL 10,2)
$sale_amount = $_POST['sale_amount']; // Total amount (DECIMAL 10,2)

// Basic validation
if (empty($customer_id) || empty($sale_credit) || $sale_credit <= 0) {
    header("Location: ../index.php?p=sales&error=invalid_input");
    exit();
}

$sale_date = date("Y-m-d");

/* Transaction Phase */
$conn->begin_transaction();

try {
    // 1. Check Admin Credit Balance (User ID 2)
    $res = $conn->query("SELECT credit_balance FROM credit_setting WHERE user_id = 2 FOR UPDATE");
    $admin_row = $res->fetch_assoc();
    
    if (!$admin_row || $admin_row['credit_balance'] < $sale_credit) {
        throw new Exception("Insufficient credit balance in system.");
    }

    // 2. Insert Sale Record
    $stmt1 = $conn->prepare("INSERT INTO sale (sale_date, sale_amount, sale_price, sale_credit, user_id, customer_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt1->bind_param("sddiii", $sale_date, $sale_amount, $sale_price, $sale_credit, $user_id, $customer_id);
    if (!$stmt1->execute()) throw new Exception("Failed to record sale.");

    // 3. Deduct from Admin's Credit
    $stmt2 = $conn->prepare("UPDATE credit_setting SET credit_balance = credit_balance - ? WHERE user_id = 2");
    $stmt2->bind_param("i", $sale_credit);
    if (!$stmt2->execute()) throw new Exception("Failed to update system credit balance.");

    // 4. ADD to Customer's Credit
    $stmt3 = $conn->prepare("UPDATE customer SET credit_balance = credit_balance + ? WHERE customer_id = ?");
    $stmt3->bind_param("ii", $sale_credit, $customer_id);
    if (!$stmt3->execute()) throw new Exception("Failed to update customer credit balance.");

    $conn->commit();
    header("Location: ../index.php?p=sales&success=1");

} catch (Exception $e) {
    $conn->rollback();
    // Redirect with error message
    header("Location: ../index.php?p=sales&error=" . urlencode($e->getMessage()));
}

$stmt1->close();
$stmt2->close();
$conn->close();
?>
