<?php
session_start();
include '../connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
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

// Permission Check: Staff can only see their own data
if ($role != 'Admin' && $role != 'Manager') {
    // Force own user_id
    $filter_user = $user_id; 
}

$params = [];
$types = "";

// ---------------------------------------------------------
// REPORT: SALES
// ---------------------------------------------------------
if ($report_type == 'sales') {
    $sql = "SELECT s.sale_id, s.sale_date, s.sale_amount, s.sale_credit, u.firstname, u.lastname, c.customer_name 
            FROM sale s 
            JOIN user u ON s.user_id = u.user_id 
            JOIN customer c ON s.customer_id = c.customer_id 
            WHERE s.sale_date BETWEEN ? AND ? ";
    
    $params[] = $start_date;
    $params[] = $end_date;
    $types .= "ss";

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

// ---------------------------------------------------------
// REPORT: ORDERS (Purchase Credit)
// ---------------------------------------------------------
} elseif ($report_type == 'orders') {
    $sql = "SELECT p.order_id, p.order_number, p.order_date, p.order_quantity, p.order_status, u.firstname, u.lastname, a.agent_name 
            FROM purchase_credit p 
            JOIN user u ON p.user_id = u.user_id 
            JOIN agent a ON p.agent_id = a.agent_id 
            WHERE p.order_date BETWEEN ? AND ? ";
    
    $params[] = $start_date;
    $params[] = $end_date;
    $types .= "ss";

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

// ---------------------------------------------------------
// REPORT: COMBINED (Show sales and orders in one table)
// ---------------------------------------------------------
} elseif ($report_type == 'combined') {
    $sql_sales = "SELECT 'Sales' as transaction_type, s.sale_date as t_date, c.customer_name as reference, 
               s.sale_credit as qty, s.sale_amount as total_amount, s.user_id 
        FROM sale s
        JOIN customer c ON s.customer_id = c.customer_id
        WHERE s.sale_date BETWEEN ? AND ?";
    
    $sql_purchases = "SELECT 'Purchase' as transaction_type, p.order_date as t_date, p.order_number as reference, 
               p.order_quantity as qty, 0 as total_amount, p.user_id
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
    
// ---------------------------------------------------------
// REPORT: LOGS (System Activity)
// ---------------------------------------------------------
} elseif ($report_type == 'logs') {
    $sql = "SELECT l.log_id, l.timestamp, l.action, l.details, u.firstname, u.lastname, l.ip_address 
            FROM system_log l 
            JOIN user u ON l.user_id = u.user_id 
            WHERE DATE(l.timestamp) BETWEEN ? AND ? ";
    
    $params[] = $start_date;
    $params[] = $end_date;
    $types .= "ss";

    if (!empty($filter_user)) {
        $sql .= " AND l.user_id = ?";
        $params[] = $filter_user;
        $types .= "i";
    }

    $sql .= " ORDER BY l.timestamp DESC";
} else {
    echo json_encode(['error' => 'Invalid report type']);
    exit();
}

// Execute Query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Log View Action
$action = "View Report";
$details = "Viewed $report_type report from $start_date to $end_date";
$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$log_sql = "INSERT INTO system_log (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)";
$stmt_log = $conn->prepare($log_sql);
if ($stmt_log) {
    $stmt_log->bind_param("isss", $user_id, $action, $details, $ip);
    $stmt_log->execute();
}

echo json_encode(['data' => $data, 'user_role' => $role]);

$stmt->close();
$conn->close();
?>
