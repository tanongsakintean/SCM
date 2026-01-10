<?php
include 'connect.php';

// Create role_permissions table
$sql = "CREATE TABLE IF NOT EXISTS role_permissions (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    role_id INT(11) NOT NULL,
    permission_key VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'role_permissions' created successfully\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

// Add index for performance
$sql_index = "CREATE INDEX idx_role_id ON role_permissions (role_id)";
if ($conn->query($sql_index) === TRUE) {
    echo "Index created successfully\n";
} else {
    // Index might already exist
    echo "Index creation check: " . $conn->error . "\n";
}

$conn->close();
?>
