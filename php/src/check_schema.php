<?php
include 'connect.php';

$result = $conn->query("DESCRIBE roles");
while($row = $result->fetch_assoc()) {
    echo "Field: " . $row['Field'] . " | Type: " . $row['Type'] . " | Null: " . $row['Null'] . " | Default: " . $row['Default'] . "\n";
}
$conn->close();
?>
