<?php
// prevent the file being processed multiple times
if (!defined('SCM_FUNCTIONS_INCLUDED')) {
    define('SCM_FUNCTIONS_INCLUDED', true);

    // make sure the function isn't defined already if this file is loaded multiple times
    if (!function_exists('has_permission')) {
        function has_permission($role_id, $permission_key) {
            // make sure we have a db connection
            global $conn;
            if (!$conn) {
                include_once __DIR__ . '/../connect.php';
            }

            // Fetching the role has been updated, permissions define everything

            if (!$role_id) return false;

            // Use prepared statement to prevent injection and for better practice
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM role_permissions WHERE role_id = ? AND permission_key = ?");
            $stmt->bind_param("is", $role_id, $permission_key);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['count'] > 0;
        }
    }

    if (!function_exists('get_role_permissions')) {
        function get_role_permissions($role_id) {
            global $conn;
            if (!$conn) {
                include_once __DIR__ . '/../connect.php';
            }

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
    }
}
