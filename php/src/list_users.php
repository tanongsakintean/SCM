<?php
include 'connect.php';

$result = $conn->query("SELECT user_id, username, role FROM user"); // role might be via join?
// Wait, `user` table structure from `users.php`:
// $sql_users = "SELECT u.*, p.permission_name as role FROM user u LEFT JOIN permission p ON u.user_id = p.user_id ...";
// So check `user` table raw.
$result = $conn->query("SELECT user_id, username FROM user");
echo "Users found:\n";
while ($row = $result->fetch_assoc()) {
    echo "ID: " . $row['user_id'] . ", Username: " . $row['username'] . "\n";
}
?>
