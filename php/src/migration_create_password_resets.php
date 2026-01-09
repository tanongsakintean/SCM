<?php
include 'connect.php';

// Create password_resets table
$sql = "CREATE TABLE IF NOT EXISTS password_resets (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table password_resets created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
