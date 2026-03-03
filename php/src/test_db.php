<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'connect.php';

$start_date = '2024-01-01';
$end_date = '2026-12-31';

$sql_sales = "SELECT 'Sales' as transaction_type, s.sale_date as t_date, c.customer_name as reference, 
           s.sale_credit as qty, s.sale_amount as total_amount, s.user_id 
    FROM sale s
    JOIN customer c ON s.customer_id = c.customer_id
    WHERE s.sale_date BETWEEN ? AND ?";

$sql_purchases = "SELECT 'Purchase' as transaction_type, p.order_date as t_date, p.order_number as reference, 
           p.order_quantity as qty, 0 as total_amount, p.user_id
    FROM purchase_credit p
    WHERE p.order_date BETWEEN ? AND ?";

$sql = "($sql_sales) UNION ALL ($sql_purchases) ORDER BY t_date DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("ssss", $start_date, $end_date, $start_date, $end_date);
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}

$result = $stmt->get_result();
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo "Success, rows: " . count($data);
?>
