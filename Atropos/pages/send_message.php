<?php
// Database connection
include '../database/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = $_POST['message'];
    $user = $_POST['user'];

    $query = "INSERT INTO messages (user, messages_main) VALUES ('$user', '$message')";
    $conn->query($query);
}
?>