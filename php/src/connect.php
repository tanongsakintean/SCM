<?php
$servername = "db"; // Docker service name usually
$username = "root";
$password = "root";
$dbname = "DB_SCM";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
