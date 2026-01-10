<?php
include 'connect.php';

// Check if row exists for ID 2
$user_id = 2;
$result = $conn->query("SELECT * FROM credit_setting WHERE user_id = $user_id");
if ($result->num_rows == 0) {
    echo "Inserting default settings for User ID $user_id...\n";
    $sql = "INSERT INTO credit_setting (user_id, credit_balance, credit_min, notify_channels) VALUES ($user_id, 0, 10000, 'dashboard')";
    if ($conn->query($sql) === TRUE) {
        echo "Successfully inserted default settings for ID $user_id.\n";
    } else {
        echo "Error inserting settings: " . $conn->error . "\n";
    }
} else {
    echo "Settings row already exists for ID $user_id.\n";
}

// Cleanup ID 1 if it exists (it shouldn't based on error, but good to be clean)
$conn->query("DELETE FROM credit_setting WHERE user_id = 1");
?>
