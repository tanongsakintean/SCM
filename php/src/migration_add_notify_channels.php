<?php
include 'connect.php';

// Check if column exists
$checkSql = "SHOW COLUMNS FROM credit_setting LIKE 'notify_channels'";
$result = $conn->query($checkSql);

if ($result->num_rows == 0) {
    // Add column
    $sql = "ALTER TABLE credit_setting ADD COLUMN notify_channels VARCHAR(255) DEFAULT 'dashboard'";
    if ($conn->query($sql) === TRUE) {
        echo "Column 'notify_channels' added successfully.";
    } else {
        echo "Error adding column: " . $conn->error;
    }
} else {
    echo "Column 'notify_channels' already exists.";
}

$conn->close();
?>
