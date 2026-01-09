<?php
include 'connect.php';

// Modify role_label to be nullable
$sql = "ALTER TABLE roles MODIFY role_label VARCHAR(255) NULL";
if ($conn->query($sql) === TRUE) {
    echo "Column role_label modified to NULLABLE successfully";
} else {
    echo "Error modifying column: " . $conn->error;
}

$conn->close();
?>
