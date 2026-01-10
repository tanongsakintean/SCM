<?php
$servername = "localhost"; 
$username = "root";
$password = "";
$dbname = "DB_SCM";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Include helper functions
include_once __DIR__ . '/includes/functions.php';
?>
