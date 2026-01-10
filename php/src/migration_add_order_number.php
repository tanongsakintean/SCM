<?php
include 'connect.php';

// Check if column exists
$result = $conn->query("SHOW COLUMNS FROM purchase_credit LIKE 'order_number'");
if ($result->num_rows == 0) {
    echo "Adding order_number column...\n";
    $sql = "ALTER TABLE purchase_credit ADD COLUMN order_number VARCHAR(20) DEFAULT NULL AFTER order_id";
    if ($conn->query($sql) === TRUE) {
        echo "Column order_number added successfully.\n";
        // Optional: Backfill old orders?
        // Let's just update NULL ones to "OLD-" + order_id for safety, or leave them.
        // User didn't ask for backfill, but for NEW orders.
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
} else {
    echo "Column order_number already exists.\n";
}
?>
