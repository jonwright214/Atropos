<?php
include '../database/db_connect.php';

// Fetch all active users (session started but not ended), and their character names
$query = "
    SELECT c.character_name
    FROM users u
    JOIN characters c ON u.selected_character_id = c.character_id
    WHERE u.session_start IS NOT NULL AND u.session_end IS NULL
";
$result = $conn->query($query);

$userList = [];
while ($row = $result->fetch_assoc()) {
    $userList[] = $row['character_name'];
}

header('Content-Type: application/json');
echo json_encode($userList);
?>