<?php
include 'connect.php';

// Check if column exists
$checkSql = "SHOW COLUMNS FROM customer LIKE 'credit_balance'";
$result = $conn->query($checkSql);

if ($result->num_rows == 0) {
    // Add column
    $sql = "ALTER TABLE customer ADD COLUMN credit_balance INT(11) DEFAULT 0";
    if ($conn->query($sql) === TRUE) {
        echo "Column 'credit_balance' added successfully.";
    } else {
        echo "Error adding column: " . $conn->error;
    }
} else {
    echo "Column 'credit_balance' already exists.";
}

$conn->close();
?>
