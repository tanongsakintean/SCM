<?php
$servername = "127.0.0.1";
$username = "root";
$password = "root";
$dbname = "DB_SCM";
$port = 9906;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add columns to purchase_credit if they don't exist
$sql = "ALTER TABLE purchase_credit 
        ADD COLUMN receipt_proof VARCHAR(255) NULL AFTER order_note,
        ADD COLUMN received_at DATETIME NULL AFTER receipt_proof,
        ADD COLUMN received_by INT(10) NULL AFTER received_at";

if ($conn->query($sql) === TRUE) {
    echo "Table 'purchase_credit' altered successfully (Added receipt columns).\n";
} else {
    // Check if duplicate column error, which is fine
    if ($conn->errno == 1060) {
        echo "Columns already exist.\n";
    } else {
        echo "Error altering table: " . $conn->error . "\n";
    }
}

$conn->close();
?>
