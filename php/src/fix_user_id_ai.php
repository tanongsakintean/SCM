<?php
include 'connect.php';

echo "Attempting to fix user_id auto_increment...<br>";

// 1. Check current structure (Optional, but good for debug)
echo "Current Structure:<br>";
$result = $conn->query("DESCRIBE user");
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . " - " . $row['Key'] . " - " . $row['Extra'] . "<br>";
}
echo "<hr>";

// 2. ALTER TABLE to add AUTO_INCREMENT
$sql = "ALTER TABLE user MODIFY user_id INT(11) NOT NULL AUTO_INCREMENT";

if ($conn->query($sql) === TRUE) {
    echo "<h3 style='color:green'>Success: user_id is now AUTO_INCREMENT</h3>";
} else {
    echo "<h3 style='color:red'>Error: " . $conn->error . "</h3>";
}

// 3. Verify
echo "<hr>New Structure:<br>";
$result = $conn->query("DESCRIBE user");
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . " - " . $row['Key'] . " - " . $row['Extra'] . "<br>";
}

$conn->close();
?>
