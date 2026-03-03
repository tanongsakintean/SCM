<?php
session_start();
include '../connect.php';

if (!isset($_SESSION['user_id'])) {
    die('Unauthorized');
}

$user_id = $_SESSION['user_id'];
$role_id = $_SESSION['role_id'] ?? 0;
$report_type = $_GET['type'] ?? 'sales';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$filter_user = $_GET['user_id'] ?? '';
$filter_customer = $_GET['customer_id'] ?? '';
$filter_agent = $_GET['agent_id'] ?? '';

// Permission Check Removed: All users can view all data

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
    fputcsv($output, ['รายงานการขาย'], ',', '"', '\\');
    fputcsv($output, ['ข้อมูลตั้งแต่วันที่: ' . date('d/m/Y', strtotime($start_date)) . ' ถึง ' . date('d/m/Y', strtotime($end_date)), '', 'พิมพ์เมื่อ: ' . date('d/m/Y H:i')], ',', '"', '\\');
    fputcsv($output, [], ',', '"', '\\');
    fputcsv($output, ['รหัสการขาย', 'วันที่', 'ลูกค้า', 'พนักงานขาย', 'เครดิตที่ขาย', 'จำนวนเงิน (บาท)'], ',', '"', '\\');

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
    
    // Summary
    fputcsv($output, [], ',', '"', '\\');
    $sql_sum = "SELECT SUM(sale_credit) as total_qty, SUM(sale_amount) as total_amt, COUNT(*) as total_rows 
            FROM sale s 
            WHERE s.sale_date BETWEEN ? AND ? ";
    if (!empty($filter_user)) $sql_sum .= " AND s.user_id = $filter_user";
    if (!empty($filter_customer)) $sql_sum .= " AND s.customer_id = $filter_customer";
    $stmt_sum = $conn->prepare($sql_sum);
    $stmt_sum->bind_param("ss", $start_date, $end_date);
    $stmt_sum->execute();
    $sum_row = $stmt_sum->get_result()->fetch_assoc();
    fputcsv($output, ['','','','รวมจำนวนเงิน', number_format($sum_row['total_amt'], 2)], ',', '"', '\\');
    fputcsv($output, ['','','','รวมจำนวนรายการ', number_format($sum_row['total_rows'])], ',', '"', '\\');

// ---------------------------------------------------------
// EXPORT: ORDERS
// ---------------------------------------------------------
} elseif ($report_type == 'orders') {
    // Header Row
    fputcsv($output, ['รายงานการสั่งซื้อ'], ',', '"', '\\');
    fputcsv($output, ['ข้อมูลตั้งแต่วันที่: ' . date('d/m/Y', strtotime($start_date)) . ' ถึง ' . date('d/m/Y', strtotime($end_date)), '', 'พิมพ์เมื่อ: ' . date('d/m/Y H:i')], ',', '"', '\\');
    fputcsv($output, [], ',', '"', '\\');
    fputcsv($output, ['รหัสคำสั่งซื้อ', 'วันที่', 'ซัพพลายเออร์', 'ผู้สั่งซื้อ', 'ปริมาณ', 'สถานะ'], ',', '"', '\\');

    $sql = "SELECT p.order_id, p.order_number, p.order_date, a.agent_name, CONCAT(u.firstname, ' ', u.lastname) as buyer_name, p.order_quantity, p.order_status
            FROM purchase_credit p 
            JOIN user u ON p.user_id = u.user_id 
            JOIN agent a ON p.agent_id = a.agent_id 
            WHERE p.order_date BETWEEN ? AND ? AND p.order_status = 'Received' ";

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
        $statusTH = $row['order_status'];
        if ($statusTH == 'Approved') $statusTH = 'อนุมัติแล้ว';
        elseif ($statusTH == 'Pending') $statusTH = 'รอดำเนินการ';
        elseif ($statusTH == 'Rejected') $statusTH = 'ถูกปฏิเสธ';
        elseif ($statusTH == 'Received') $statusTH = 'ได้รับเครดิตแล้ว';
        
        fputcsv($output, [
            $orderIdStr, 
            $row['order_date'], 
            $row['agent_name'], 
            $row['buyer_name'], 
            $row['order_quantity'], 
            $statusTH
        ], ',', '"', '\\');
    }
    
    // Summary
    fputcsv($output, [], ',', '"', '\\');
    $sql_sum = "SELECT SUM(order_quantity) as total_qty, COUNT(*) as total_rows 
            FROM purchase_credit p 
            WHERE p.order_date BETWEEN ? AND ? AND p.order_status = 'Received' ";
    if (!empty($filter_user)) $sql_sum .= " AND p.user_id = $filter_user";
    if (!empty($filter_agent)) $sql_sum .= " AND p.agent_id = $filter_agent";
    $stmt_sum = $conn->prepare($sql_sum);
    $stmt_sum->bind_param("ss", $start_date, $end_date);
    $stmt_sum->execute();
    $sum_row = $stmt_sum->get_result()->fetch_assoc();
    fputcsv($output, ['','','','รวมปริมาณเครดิต', number_format($sum_row['total_qty'])], ',', '"', '\\');
    fputcsv($output, ['','','','รวมจำนวนรายการ', number_format($sum_row['total_rows'])], ',', '"', '\\');

// ---------------------------------------------------------
// EXPORT: COMBINED (Show sales and orders in one table)
// ---------------------------------------------------------
} elseif ($report_type == 'combined') {
    // Header Row
    fputcsv($output, ['รายงานรวม (Combined)'], ',', '"', '\\');
    fputcsv($output, ['ข้อมูลตั้งแต่วันที่: ' . date('d/m/Y', strtotime($start_date)) . ' ถึง ' . date('d/m/Y', strtotime($end_date)), '', 'พิมพ์เมื่อ: ' . date('d/m/Y H:i')], ',', '"', '\\');
    fputcsv($output, [], ',', '"', '\\');
    fputcsv($output, ['วันที่', 'ประเภทธุรกรรม', 'รหัสคำสั่งซื้อ / ลูกค้า', 'ปริมาณ/เครดิต', 'จำนวนเงินทั้งหมด'], ',', '"', '\\');

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
        $transType = $row['transaction_type'] == 'Sales' ? 'ขาย' : 'สั่งซื้อ';
        fputcsv($output, [
            $row['t_date'], 
            $transType, 
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
    fputcsv($output, ['ประวัติการใช้งาน (System Logs)'], ',', '"', '\\');
    fputcsv($output, ['ข้อมูลตั้งแต่วันที่: ' . date('d/m/Y', strtotime($start_date)) . ' ถึง ' . date('d/m/Y', strtotime($end_date)), '', 'พิมพ์เมื่อ: ' . date('d/m/Y H:i')], ',', '"', '\\');
    fputcsv($output, [], ',', '"', '\\');
    fputcsv($output, ['รหัส Log', 'เวลา', 'ผู้ใช้งาน', 'การกระทำ', 'รายละเอียด', 'IP Address'], ',', '"', '\\');

    $sql = "SELECT l.log_id, l.timestamp, CONCAT(u.firstname, ' ', u.lastname) as user_name, l.action, l.details, l.ip_address 
            FROM system_log l 
            JOIN user u ON l.user_id = u.user_id 
            WHERE DATE(l.timestamp) BETWEEN ? AND ? ";
    
    $params = [$start_date, $end_date];
    $types = "ss";

    if (!empty($filter_user)) {
        $sql .= " AND l.user_id = ?";
        $params[] = $filter_user;
        $types .= "i";
    }
    $sql .= " ORDER BY l.timestamp DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['log_id'], 
            $row['timestamp'], 
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
