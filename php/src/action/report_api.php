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
$filter_user = $_GET['user_id'] ?? ''; // For Admin to filter by specific user

// Base WHERE clause for date range
$where_clauses = [];
$params = [];
$types = "";

// Permission Check: Staff can only see their own data
if ($role != 'Admin' && $role != 'Manager') {
    // Force own user_id
    $filter_user = $user_id; 
}

// ---------------------------------------------------------
// REPORT: SALES
// ---------------------------------------------------------
if ($report_type == 'sales') {
    $sql = "SELECT s.sale_id, s.sale_date, s.sale_amount, s.sale_credit, u.firstname, u.lastname, c.customer_name 
            FROM sale s 
            JOIN users u ON s.user_id = u.user_id 
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

    $sql .= " ORDER BY s.sale_date DESC";

// ---------------------------------------------------------
// REPORT: ORDERS (Purchase Credit)
// ---------------------------------------------------------
} elseif ($report_type == 'orders') {
    $sql = "SELECT p.order_id, p.order_date, p.order_quantity, p.order_status, u.firstname, u.lastname, a.agent_name 
            FROM purchase_credit p 
            JOIN users u ON p.user_id = u.user_id 
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

    $sql .= " ORDER BY p.order_date DESC";

// ---------------------------------------------------------
// REPORT: LOGS (System Activity)
// ---------------------------------------------------------
} elseif ($report_type == 'logs') {
    // Admin/Manager only for logs? Or everyone see their own?
    // Let's allow everyone to see their own logs, Admin sees all.
    $sql = "SELECT l.log_id, l.created_at, l.action, l.details, u.firstname, u.lastname, l.ip_address 
            FROM system_log l 
            JOIN users u ON l.user_id = u.user_id 
            WHERE DATE(l.created_at) BETWEEN ? AND ? ";
    
    $params[] = $start_date;
    $params[] = $end_date;
    $types .= "ss";

    if (!empty($filter_user)) {
        $sql .= " AND l.user_id = ?";
        $params[] = $filter_user;
        $types .= "i";
    }

    $sql .= " ORDER BY l.created_at DESC";
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
if ($role == 'Admin' || $role == 'Manager') {
    // Optional: Log when admin views reports to avoid cluttering logs 
    // or just log it specific actions.
}

echo json_encode(['data' => $data, 'user_role' => $role]);

$stmt->close();
$conn->close();
?>
