<?php
include 'connect.php';

// Check columns
$result = $conn->query("SHOW COLUMNS FROM credit_setting");
echo "Columns in credit_setting:\n";
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}

// Check content
$result = $conn->query("SELECT * FROM credit_setting WHERE user_id = 1");
if ($result->num_rows > 0) {
    print_r($result->fetch_assoc());
} else {
    echo "No row found for user_id = 1\n";
}
?>
