<?php
include 'connect.php';
$result = $conn->query("SHOW COLUMNS FROM customer");
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>
