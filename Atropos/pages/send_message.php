<?php
include '../database/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = $_POST['message'] ?? '';
    $user = $_POST['user'] ?? '';
    $type = $_POST['type'] ?? 'main';
    $target = $_POST['target'] ?? '';

    // Validation
    if (trim($message) === '' || trim($user) === '' || trim($type) === '') {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }

    // Use prepared statements for safety
    $stmt = $conn->prepare(
        "INSERT INTO messages (user, messages_main, type, target) VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("ssss", $user, $message, $type, $target);
    $success = $stmt->execute();
    $stmt->close();

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit;
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}
?>
