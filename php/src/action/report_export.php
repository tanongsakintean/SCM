<?php
session_start();
include '../connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$report_type = $_GET['type'] ?? 'sales';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$filter_user = $_GET['user_id'] ?? '';

// Permission Check
if ($role != 'Admin' && $role != 'Manager') {
    $filter_user = $user_id;
}

$filename = "report_" . $report_type . "_" . date('Ymd_His') . ".csv";

// Set Headers for Download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Create File Pointer
$output = fopen('php://output', 'w');

// Add BOM for Excel UTF-8 compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// ---------------------------------------------------------
// EXPORT: SALES
// ---------------------------------------------------------
if ($report_type == 'sales') {
    // Header Row
    fputcsv($output, ['Sale ID', 'Date', 'Customer', 'Seller', 'Credit Sold', 'Amount (THB)']);

    $sql = "SELECT s.sale_id, s.sale_date, c.customer_name, CONCAT(u.firstname, ' ', u.lastname) as seller_name, s.sale_credit, s.sale_amount
            FROM sale s 
            JOIN users u ON s.user_id = u.user_id 
            JOIN customer c ON s.customer_id = c.customer_id 
            WHERE s.sale_date BETWEEN ? AND ? ";
    
    $params = [$start_date, $end_date];
    $types = "ss";

    if (!empty($filter_user)) {
        $sql .= " AND s.user_id = ?";
        $params[] = $filter_user;
        $types .= "i";
    }
    $sql .= " ORDER BY s.sale_date DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['sale_id'], 
            $row['sale_date'], 
            $row['customer_name'], 
            $row['seller_name'], 
            $row['sale_credit'], 
            $row['sale_amount']
        ]);
    }

// ---------------------------------------------------------
// EXPORT: ORDERS
// ---------------------------------------------------------
} elseif ($report_type == 'orders') {
    // Header Row
    fputcsv($output, ['Order ID', 'Date', 'Supplier', 'Ordered By', 'Quantity', 'Status']);

    $sql = "SELECT p.order_id, p.order_date, a.agent_name, CONCAT(u.firstname, ' ', u.lastname) as buyer_name, p.order_quantity, p.order_status
            FROM purchase_credit p 
            JOIN users u ON p.user_id = u.user_id 
            JOIN agent a ON p.agent_id = a.agent_id 
            WHERE p.order_date BETWEEN ? AND ? ";

    $params = [$start_date, $end_date];
    $types = "ss";

    if (!empty($filter_user)) {
        $sql .= " AND p.user_id = ?";
        $params[] = $filter_user;
        $types .= "i";
    }
    $sql .= " ORDER BY p.order_date DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['order_id'], 
            $row['order_date'], 
            $row['agent_name'], 
            $row['buyer_name'], 
            $row['order_quantity'], 
            $row['order_status']
        ]);
    }

// ---------------------------------------------------------
// EXPORT: LOGS
// ---------------------------------------------------------
} elseif ($report_type == 'logs') {
    // Header Row
    fputcsv($output, ['Log ID', 'Timestamp', 'User', 'Action', 'Details', 'IP Address']);

    $sql = "SELECT l.log_id, l.created_at, CONCAT(u.firstname, ' ', u.lastname) as user_name, l.action, l.details, l.ip_address 
            FROM system_log l 
            JOIN users u ON l.user_id = u.user_id 
            WHERE DATE(l.created_at) BETWEEN ? AND ? ";
    
    $params = [$start_date, $end_date];
    $types = "ss";

    if (!empty($filter_user)) {
        $sql .= " AND l.user_id = ?";
        $params[] = $filter_user;
        $types .= "i";
    }
    $sql .= " ORDER BY l.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['log_id'], 
            $row['created_at'], 
            $row['user_name'], 
            $row['action'], 
            $row['details'], 
            $row['ip_address']
        ]);
    }
}

// Log Export Action if needed in future
fclose($output);
$conn->close();
exit();
?>
