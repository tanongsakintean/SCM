<?php
include 'connect.php';

// 1. Find Admin Role ID
$sql = "SELECT role_id FROM roles WHERE role_name = 'Admin'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $role_id = $row['role_id'];
    echo "Found Admin Role ID: " . $role_id . "\n";

    // 2. Define all permissions
    $all_permissions = ['sales', 'orders', 'approve_orders', 'receive_credit', 'reports', 'settings', 'users'];

    // 3. Clear existing permissions for Admin
    $conn->query("DELETE FROM role_permissions WHERE role_id = $role_id");

    // 4. Insert all permissions
    $stmt = $conn->prepare("INSERT INTO role_permissions (role_id, permission_key) VALUES (?, ?)");
    foreach ($all_permissions as $key) {
        $stmt->bind_param("is", $role_id, $key);
        $stmt->execute();
    }
    echo "Successfully assigned all permissions to Admin.\n";
} else {
    echo "Admin role not found.\n";
}

$conn->close();
?>
