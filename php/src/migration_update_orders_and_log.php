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


// 1. Add columns to purchase_credit
$alter_table_sql = "ALTER TABLE purchase_credit 
                    ADD COLUMN order_attachment VARCHAR(255) NULL AFTER order_status,
                    ADD COLUMN order_note TEXT NULL AFTER order_attachment,
                    ADD COLUMN expected_date DATE NULL AFTER order_date";

if ($conn->query($alter_table_sql) === TRUE) {
    echo "Table 'purchase_credit' altered successfully.\n";
} else {
    echo "Error altering table: " . $conn->error . "\n";
}

// 2. Create system_log table
$create_log_table_sql = "CREATE TABLE IF NOT EXISTS `system_log` (
    `log_id` INT(10) NOT NULL AUTO_INCREMENT,
    `user_id` INT(10) NOT NULL,
    `action` VARCHAR(100) NOT NULL,
    `details` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`log_id`),
    FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($create_log_table_sql) === TRUE) {
    echo "Table 'system_log' created successfully.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

$conn->close();
?>
