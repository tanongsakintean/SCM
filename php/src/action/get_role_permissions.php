<?php
include '../connect.php';

if (isset($_GET['role_id'])) {
    $role_id = $_GET['role_id'];
    $permissions = get_role_permissions($role_id);
    echo json_encode($permissions);
} else {
    echo json_encode([]);
}
?>
