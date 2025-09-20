<?php
include '../database/db_connect.php';

// Get parameters
$tab = $_GET['tab'] ?? 'main';
$currentUser = $_GET['user'] ?? '';

$type = $tab;
$targetUser = '';

// Determine actual type and target for private chat
if (strpos($tab, 'private-') === 0) {
    $type = 'private';
    $targetUser = substr($tab, strlen('private-'));
}

// Build SQL query
if ($type === 'private' && $targetUser) {
    // Private: show messages where (user=currentUser AND target=targetUser) OR (user=targetUser AND target=currentUser)
    $stmt = $conn->prepare(
        "SELECT user, messages_main, type, target, timestamp FROM messages
        WHERE type = 'private' AND (
            (user = ? AND target = ?) OR
            (user = ? AND target = ?)
        )
        ORDER BY timestamp ASC LIMIT 50"
    );
    $stmt->bind_param("ssss", $currentUser, $targetUser, $targetUser, $currentUser);
} else {
    // Main/combat/server: show messages of that type only
    $stmt = $conn->prepare(
        "SELECT user, messages_main, type, target, timestamp FROM messages
        WHERE type = ?
        ORDER BY timestamp ASC LIMIT 50"
    );
    $stmt->bind_param("s", $type);
}

// Execute and fetch
$stmt->execute();
$result = $stmt->get_result();
$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
$stmt->close();

header('Content-Type: application/json');
echo json_encode($messages);
?>