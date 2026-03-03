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
$filter_customer = $_GET['customer_id'] ?? '';
$filter_agent = $_GET['agent_id'] ?? '';

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
    fputcsv($output, ['Sale ID', 'Date', 'Customer', 'Seller', 'Credit Sold', 'Amount (THB)'], ',', '"', '\\');

    $sql = "SELECT s.sale_id, s.sale_date, c.customer_name, CONCAT(u.firstname, ' ', u.lastname) as seller_name, s.sale_credit, s.sale_amount
            FROM sale s 
            JOIN user u ON s.user_id = u.user_id 
            JOIN customer c ON s.customer_id = c.customer_id 
            WHERE s.sale_date BETWEEN ? AND ? ";
    
    $params = [$start_date, $end_date];
    $types = "ss";

    if (!empty($filter_user)) {
        $sql .= " AND s.user_id = ?";
        $params[] = $filter_user;
        $types .= "i";
    }
    if (!empty($filter_customer)) {
        $sql .= " AND s.customer_id = ?";
        $params[] = $filter_customer;
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
        ], ',', '"', '\\');
    }

// ---------------------------------------------------------
// EXPORT: ORDERS
// ---------------------------------------------------------
} elseif ($report_type == 'orders') {
    // Header Row
    fputcsv($output, ['Order ID', 'Date', 'Supplier', 'Ordered By', 'Quantity', 'Status'], ',', '"', '\\');

    $sql = "SELECT p.order_id, p.order_number, p.order_date, a.agent_name, CONCAT(u.firstname, ' ', u.lastname) as buyer_name, p.order_quantity, p.order_status
            FROM purchase_credit p 
            JOIN user u ON p.user_id = u.user_id 
            JOIN agent a ON p.agent_id = a.agent_id 
            WHERE p.order_date BETWEEN ? AND ? ";

    $params = [$start_date, $end_date];
    $types = "ss";

    if (!empty($filter_user)) {
        $sql .= " AND p.user_id = ?";
        $params[] = $filter_user;
        $types .= "i";
    }
    if (!empty($filter_agent)) {
        $sql .= " AND p.agent_id = ?";
        $params[] = $filter_agent;
        $types .= "i";
    }
    $sql .= " ORDER BY p.order_date DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $orderIdStr = !empty($row['order_number']) ? $row['order_number'] : $row['order_id'];
        fputcsv($output, [
            $orderIdStr, 
            $row['order_date'], 
            $row['agent_name'], 
            $row['buyer_name'], 
            $row['order_quantity'], 
            $row['order_status']
        ], ',', '"', '\\');
    }

// ---------------------------------------------------------
// EXPORT: COMBINED (Show sales and orders in one table)
// ---------------------------------------------------------
} elseif ($report_type == 'combined') {
    // Header Row
    fputcsv($output, ['Date', 'Transaction Type', 'Order ID / Customer', 'Quantity/Credit', 'Total Amount'], ',', '"', '\\');

    $sql_sales = "SELECT 'Sales' as transaction_type, s.sale_date as t_date, c.customer_name as reference, 
               s.sale_credit as qty, s.sale_amount as total_amount
        FROM sale s
        JOIN customer c ON s.customer_id = c.customer_id
        WHERE s.sale_date BETWEEN ? AND ?";
        
    $sql_purchases = "SELECT 'Purchase' as transaction_type, p.order_date as t_date, p.order_number as reference, 
               p.order_quantity as qty, 0 as total_amount
        FROM purchase_credit p
        WHERE p.order_date BETWEEN ? AND ?";
    
    $sale_params = [$start_date, $end_date];
    $sale_types = "ss";
    
    $purchase_params = [$start_date, $end_date];
    $purchase_types = "ss";

    if (!empty($filter_user)) {
        $sql_sales .= " AND s.user_id = ?";
        $sale_params[] = $filter_user;
        $sale_types .= "i";
        
        $sql_purchases .= " AND p.user_id = ?";
        $purchase_params[] = $filter_user;
        $purchase_types .= "i";
    }
    
    if (!empty($filter_customer)) {
        $sql_sales .= " AND s.customer_id = ?";
        $sale_params[] = $filter_customer;
        $sale_types .= "i";
    }
    
    if (!empty($filter_agent)) {
        $sql_purchases .= " AND p.agent_id = ?";
        $purchase_params[] = $filter_agent;
        $purchase_types .= "i";
    }

    $sql = "($sql_sales) UNION ALL ($sql_purchases) ORDER BY t_date DESC";
    
    $params = array_merge($sale_params, $purchase_params);
    $types = $sale_types . $purchase_types;

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['t_date'], 
            $row['transaction_type'], 
            $row['reference'], 
            $row['qty'], 
            $row['total_amount']
        ], ',', '"', '\\');
    }

// ---------------------------------------------------------
// EXPORT: LOGS
// ---------------------------------------------------------
} elseif ($report_type == 'logs') {
    // Header Row
    fputcsv($output, ['Log ID', 'Timestamp', 'User', 'Action', 'Details', 'IP Address'], ',', '"', '\\');

    $sql = "SELECT l.log_id, l.created_at, CONCAT(u.firstname, ' ', u.lastname) as user_name, l.action, l.details, l.ip_address 
            FROM system_log l 
            JOIN user u ON l.user_id = u.user_id 
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
        ], ',', '"', '\\');
    }
}

// Log Export Action if needed in future
$action = "Export Report";
$details = "Exported $report_type report as CSV from $start_date to $end_date";
$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$log_sql = "INSERT INTO system_log (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)";
$stmt_log = $conn->prepare($log_sql);
if ($stmt_log) {
    $stmt_log->bind_param("isss", $user_id, $action, $details, $ip);
    $stmt_log->execute();
}

fclose($output);
$conn->close();
exit();
?>
