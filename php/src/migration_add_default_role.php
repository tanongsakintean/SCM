<?php
include 'connect.php';

// Add is_default column if it doesn't exist
$sql = "ALTER TABLE roles ADD COLUMN is_default TINYINT(1) DEFAULT 0";
if ($conn->query($sql) === TRUE) {
    echo "Column is_default added successfully";
} else {
    echo "Error adding column: " . $conn->error;
}

$conn->close();
?>
