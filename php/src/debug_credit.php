<?php
include 'connect.php';
$sql = "SELECT * FROM credit_setting WHERE user_id = 1";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    print_r($result->fetch_assoc());
} else {
    echo "No credit_setting found for user_id 1";
}
?>
