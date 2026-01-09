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

// 1. Create approve table
$create_approve_sql = "CREATE TABLE IF NOT EXISTS `approve` (
    `approval_id` INT(10) NOT NULL AUTO_INCREMENT,
    `order_id` INT(10) NOT NULL,
    `user_id` INT(10) NOT NULL,
    `approval_status` ENUM('Approved', 'Rejected') NOT NULL,
    `approval_date` DATE NOT NULL,
    `approval_note` TEXT NULL,
    PRIMARY KEY (`approval_id`),
    KEY `order_id` (`order_id`),
    KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($create_approve_sql) === TRUE) {
    echo "Table 'approve' created successfully.\n";
} else {
    echo "Error creating table 'approve': " . $conn->error . "\n";
}

// 2. Ensure system_log exists (Just in case)
$create_log_sql = "CREATE TABLE IF NOT EXISTS `system_log` (
    `log_id` INT(10) NOT NULL AUTO_INCREMENT,
    `user_id` INT(10) NOT NULL,
    `action` VARCHAR(100) NOT NULL,
    `details` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($create_log_sql) === TRUE) {
    echo "Table 'system_log' checked/created successfully.\n";
} else {
    echo "Error checking/creating 'system_log': " . $conn->error . "\n";
}

$conn->close();
?>
