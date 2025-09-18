<?php
session_start();
include '../database/db_connect.php'; // Adjust path if needed

if (!isset($_SESSION['user_id'])) {
    die('You must be logged in to view this page.');
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedCharacter = $_POST['radioCharacterSelection'] ?? '';
    if ($selectedCharacter === 'Empty') {
        header("Location: characterselect.php?error=" . urlencode("You are unable to choose an empty slot."));
        exit();
    }

    // Only select the requested columns
    $stmt = $conn->prepare(
        "SELECT character_name, character_title, character_race, character_class, character_hitpoints, character_stamina, character_mana, character_strength, character_dexterity, character_intelligence, character_armor_rating 
         FROM characters 
         WHERE user_id = ? AND character_name = ?"
    );
    $stmt->bind_param("is", $user_id, $selectedCharacter);
    $stmt->execute();
    $result = $stmt->get_result();
    $characterData = $result->fetch_assoc();
    $stmt->close();

    if (!$characterData) {
        header("Location: characterselect.php?error=" . urlencode("Selected character not found."));
        exit();
    }

    // Define your label mapping
    $labels = [
        "character_name" => "Name",
        "character_title" => "Title",
        "character_race" => "Race",
        "character_class" => "Class",
        "character_hitpoints" => "HP",
        "character_stamina" => "Stam",
        "character_mana" => "Mana",
        "character_strength" => "Str",
        "character_dexterity" => "Dex",
        "character_intelligence" => "Int",
        "character_armor_rating" => "Armor"
    ];
} else {
    header("Location: characterselect.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Main Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
	
	<!-- chat begin -->
	<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real-Time Chat</title>
    <style>
        #chatBox {
            height: 300px;
            overflow-y: scroll;
            border: 1px solid #ccc;
            padding: 10px;
        }
    </style>
	<!-- chat end -->
	
</head>
<body class="bg-light">
    <div class="container p-5">
        <h2 class="mb-4">Character Details</h2>
        <div class="card float-start" style="width: 24rem;">
            <div class="card-body">
                <?php foreach ($labels as $key => $label): ?>
                    <p><strong><?php echo $label; ?>:</strong>
                        <?php echo htmlspecialchars($characterData[$key]); ?>
                    </p>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
	<!-- chat begin -->
	<div id="chatBox"></div>
    <input type="text" id="messageInput" placeholder="Type a message">
    <button id="sendBtn">Send</button>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Fetch messages every 3 seconds
            setInterval(fetchMessages, 3000);

            // Send message on button click
            $('#sendBtn').click(function() {
                const message = $('#messageInput').val();
                const user = 'User1';  // Hardcoded user for simplicity

                $.ajax({
                    url: 'send_message.php',
                    method: 'POST',
                    data: { message: message, user: user },
                    success: function() {
                        $('#messageInput').val('');  // Clear input field
                        fetchMessages();  // Refresh message display
                    }
                });
            });

            // Function to fetch messages
            function fetchMessages() {
                $.ajax({
                    url: 'fetch_messages.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(messages) {
                        $('#chatBox').empty();  // Clear previous messages
                        messages.reverse().forEach(function(message) {
                            $('#chatBox').append('<p><strong>' + message.user + ':</strong> ' + message.message + '</p>');
                        });
                    }
                });
            }
        });
    </script>
	<!-- chat end -->
</body>
</html>