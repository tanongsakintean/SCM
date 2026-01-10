<?php
// Function to check if a role has a specific permission
function has_permission($role_id, $permission_key) {
    global $conn;
    
    // Super Admin Bypass
    // If the session role is 'Admin', allow everything.
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin') {
        return true;
    }

    if (!$role_id) return false;

    // Use prepared statement to prevent injection and for better practice
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM role_permissions WHERE role_id = ? AND permission_key = ?");
    $stmt->bind_param("is", $role_id, $permission_key);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['count'] > 0;
}

// Function to get all permissions for a role (for editing)
function get_role_permissions($role_id) {
    global $conn;
    $permissions = [];
    if (!$role_id) return $permissions;

    $stmt = $conn->prepare("SELECT permission_key FROM role_permissions WHERE role_id = ?");
    $stmt->bind_param("i", $role_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $permissions[] = $row['permission_key'];
    }
    return $permissions;
}
?>
