<?php
// Database connection
include '../database/db_connect.php';

$query = "SELECT * FROM messages ORDER BY timestamp DESC LIMIT 10";
$result = $conn->query($query);

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode($messages);  // Send messages as JSON
?>