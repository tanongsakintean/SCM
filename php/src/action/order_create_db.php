<?php
session_start();
include '../connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get data
$user_id = $_SESSION['user_id'];
$agent_id = $_POST['agent_id'];
$category_id = $_POST['category_id'];
$order_quantity = $_POST['order_quantity'];
$order_date = date("Y-m-d"); // Current date
$order_status = 'Pending';

// Simple validaton
if(empty($agent_id) || empty($order_quantity)) {
     header("Location: ../index.php?p=orders&error=missing_fields");
     exit();
}

$sql = "INSERT INTO purchase_credit (user_id, agent_id, category_id, order_date, order_quantity, order_status) 
        VALUES (?, ?, ?, ?, ?, ?)";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiisis", $user_id, $agent_id, $category_id, $order_date, $order_quantity, $order_status);

if ($stmt->execute()) {
    header("Location: ../index.php?p=orders&success=1");
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
