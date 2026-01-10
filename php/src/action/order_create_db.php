<?php
session_start();
include '../connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$agent_id = $_POST['agent_id'];
$category_id = isset($_POST['category_id']) ? $_POST['category_id'] : null;

// Default Category Logic
if (empty($category_id)) {
    $cat_res = $conn->query("SELECT category_id FROM category LIMIT 1");
    if ($cat_res->num_rows > 0) {
        $cat_row = $cat_res->fetch_assoc();
        $category_id = $cat_row['category_id'];
    } else {
        // Fallback or Error if no categories exist at all
        // For now, let's assume at least one exists or set 0 (which might fail FK)
        // Ideally we should fail if no default category found?
        // Let's exit with error if no category found to be safe.
        header("Location: ../index.php?p=orders&error=no_category_found");
        exit();
    }
}

$order_quantity = $_POST['order_quantity'];
$expected_date = date("Y-m-d"); // Auto-set to today
$order_note = $_POST['order_note'] ?? '';
$order_date = date("Y-m-d H:i:s"); 
$order_status = 'Pending';
$ip_address = $_SERVER['REMOTE_ADDR'];

// 1. Validation
if (empty($agent_id) || empty($order_quantity)) {
    header("Location: ../index.php?p=orders&error=missing_fields");
    exit();
}

if ($order_quantity <= 0) {
    header("Location: ../index.php?p=orders&error=invalid_quantity");
    exit();
}

// Limit Validation (Example: 10,000,000)
if ($order_quantity > 10000000) {
     header("Location: ../index.php?p=orders&error=limit_exceeded");
     exit();
}

// 2. File Upload
$attachment_path = NULL;
if (isset($_FILES['order_attachment']) && $_FILES['order_attachment']['error'] == 0) {
    $target_dir = "../assets/uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_name = time() . '_' . basename($_FILES['order_attachment']['name']);
    $target_file = $target_dir . $file_name;
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    $allowed = ['pdf', 'doc', 'docx', 'jpg', 'png'];
    if (in_array($file_type, $allowed)) {
        if (move_uploaded_file($_FILES['order_attachment']['tmp_name'], $target_file)) {
            $attachment_path = "assets/uploads/" . $file_name;
        } else {
             // Handle upload error (optional warning or failure)
        }
    }
}

// 3. Generate Order Number (Format: YYMMDD-XXX in Buddhist Year)
$year = (date("Y") + 543) % 100; // Last 2 digits of Thai Year
$month = date("m");
$day = date("d");
$prefix = $year . $month . $day . "-";

// Find last running number for today
$sql_run = "SELECT order_number FROM purchase_credit WHERE order_number LIKE '$prefix%' ORDER BY order_number DESC LIMIT 1";
$res_run = $conn->query($sql_run);

if ($res_run->num_rows > 0) {
    $row_run = $res_run->fetch_assoc();
    $last_num = (int)substr($row_run['order_number'], -3);
    $new_seq = str_pad($last_num + 1, 3, '0', STR_PAD_LEFT);
} else {
    $new_seq = "001";
}

$order_number = $prefix . $new_seq;

// 4. Insert Order
$sql = "INSERT INTO purchase_credit (user_id, agent_id, category_id, order_date, order_quantity, order_status, order_attachment, order_note, expected_date, order_number) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiisisssss", $user_id, $agent_id, $category_id, $order_date, $order_quantity, $order_status, $attachment_path, $order_note, $expected_date, $order_number);

if ($stmt->execute()) {
    $order_id = $stmt->insert_id;
    
    // 4. Audit Log
    $log_action = "Create Order";
    $log_details = "Created Order: $order_number (ID: $order_id), Msg: $order_note, Qty: $order_quantity";
    
    $log_sql = "INSERT INTO system_log (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)";
    $log_stmt = $conn->prepare($log_sql);
    $log_stmt->bind_param("isss", $user_id, $log_action, $log_details, $ip_address);
    $log_stmt->execute();
    $log_stmt->close();

    header("Location: ../index.php?p=orders&success=1&order_id=$order_id");
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
