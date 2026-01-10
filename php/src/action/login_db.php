<?php
session_start();
include '../connect.php';

// Receive data from login form
$username = $_POST['username'];
$password = $_POST['password'];

// Prevent SQL Injection (Basic)
$username = mysqli_real_escape_string($conn, $username);
$password = mysqli_real_escape_string($conn, $password);


$password_hash = md5($password);
$sql = "SELECT u.*, p.permission_name 
        FROM user u 
        LEFT JOIN permission p ON u.user_id = p.user_id 
        WHERE u.username = '$username' AND u.password = '$password_hash'";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    $_SESSION['user_id'] = $row['user_id'];
    $_SESSION['username'] = $row['username'];
    $_SESSION['firstname'] = $row['firstname'];
    $_SESSION['lastname'] = $row['lastname'];
    $_SESSION['role'] = $row['permission_name']; // Admin, Staff, Manager
    
    // Fetch Role ID from roles table
    $role_name = $row['permission_name'];
    $role_query = $conn->query("SELECT role_id FROM roles WHERE role_name = '$role_name'");
    if ($role_query && $role_query->num_rows > 0) {
        $role_row = $role_query->fetch_assoc();
        $_SESSION['role_id'] = $role_row['role_id'];
    } else {
        $_SESSION['role_id'] = 0; // No role or User role
    }
    
    header("Location: ../index.php");
} else {
    // Login failed
    header("Location: ../login.php?error=1");
}

$conn->close();
?>
