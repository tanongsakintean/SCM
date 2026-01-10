<?php
include 'connect.php';

// Define defaults
$roles_defaults = [
    'Manager' => ['sales', 'orders', 'approve_orders', 'receive_credit', 'reports', 'settings', 'users'], // Manager gets everything for now
    'Staff' => ['sales', 'orders'] // Staff gets Sales and Orders
];

foreach ($roles_defaults as $role_name => $perms) {
    // Find Role ID
    $sql = "SELECT role_id FROM roles WHERE role_name = '$role_name'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $role_id = $row['role_id'];
        echo "Found $role_name ID: $role_id. Assigning permissions...\n";
        
        // Clear old
        $conn->query("DELETE FROM role_permissions WHERE role_id = $role_id");
        
        // Insert new
        $stmt = $conn->prepare("INSERT INTO role_permissions (role_id, permission_key) VALUES (?, ?)");
        foreach ($perms as $key) {
            $stmt->bind_param("is", $role_id, $key);
            $stmt->execute();
        }
    } else {
        echo "Role $role_name not found.\n";
    }
}
echo "Seeding completed.\n";
?>
