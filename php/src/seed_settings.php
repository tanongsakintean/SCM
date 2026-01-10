<?php
include 'connect.php';

// Check if row exists again
$result = $conn->query("SELECT * FROM credit_setting WHERE user_id = 1");
if ($result->num_rows == 0) {
    echo "Inserting default settings for User ID 1...\n";
    $sql = "INSERT INTO credit_setting (user_id, credit_balance, credit_min, notify_channels) VALUES (1, 0, 10000, 'dashboard')";
    if ($conn->query($sql) === TRUE) {
        echo "Successfully inserted default settings.\n";
    } else {
        echo "Error inserting settings: " . $conn->error . "\n";
    }
} else {
    echo "Settings row already exists.\n";
}
?>
