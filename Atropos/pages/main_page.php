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
        header("Location: character_selection.php?error=" . urlencode("You are unable to choose an empty slot."));
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
        header("Location: character_selection.php?error=" . urlencode("Selected character not found."));
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
    header("Location: character_selection.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Main Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .chatbox-wrapper {
            position: fixed;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%);
            width: 600px;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-top: 1px solid #ccc;
            padding: 15px;
            z-index: 9999;
            display: flex;
        }
        #userList {
            width: 150px;
            border-right: 1px solid #ccc;
            padding-right: 10px;
            margin-right: 10px;
            height: 230px;
            overflow-y: auto;
        }
        #chatTabs {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .tabs {
            display: flex;
        }
        .tab-btn {
            flex: 1;
            background: #eee;
            border: none;
            padding: 8px 0;
            cursor: pointer;
        }
        .tab-btn.active {
            background: #ddd;
            font-weight: bold;
        }
        .tab-content {
            height: 150px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
            background: #f9f9f9;
            margin-bottom: 8px;
        }
        .input-group {
            margin-top: 2px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container p-5">
        <!-- Character card here -->
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
    <!-- Chatbox UI -->
    <div class="chatbox-wrapper">
        <div id="userList"></div>
        <div id="chatTabs">
            <div class="tabs" id="tabsBar">
                <button class="tab-btn active" data-tab="main">Main Chat</button>
                <button class="tab-btn" data-tab="combat">Combat</button>
                <button class="tab-btn" data-tab="server">Server</button>
            </div>
            <div id="tabContents">
                <div class="tab-content" id="mainTab"></div>
                <div class="tab-content d-none" id="combatTab"></div>
                <div class="tab-content d-none" id="serverTab"></div>
            </div>
            <div class="input-group">
                <input type="text" id="messageInput" class="form-control" placeholder="Type a message">
                <button id="sendBtn" class="btn btn-primary">Send</button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Track private chat tabs
        let privateTabs = {};
        let lastTellUser = "";
        const user = <?php echo json_encode($selectedCharacter); ?>;

        // Tab switching logic
        $(document).on('click', '.tab-btn', function() {
            $('.tab-btn').removeClass('active');
            $(this).addClass('active');
            // Hide all tab-content divs
            $('#tabContents .tab-content').addClass('d-none');
            // Show selected
            let tab = $(this).data('tab');
            $('#' + tab + 'Tab').removeClass('d-none');
        });

        // Fetch messages/users every 3 seconds
        setInterval(function() {
            fetchMessages("main");
            fetchMessages("combat");
            fetchMessages("server");
            fetchUserList();
            // Fetch private tabs
            Object.keys(privateTabs).forEach(function(u) {
                fetchMessages("private-" + u);
            });
        }, 3000);

        // Send message
        $('#sendBtn').click(function() {
            const rawMsg = $('#messageInput').val();
            if (!rawMsg.trim()) return;

            // Command parsing
            if (rawMsg.startsWith('/tell ')) {
                let parts = rawMsg.split(' ');
                if (parts.length < 3) return;
                let target = parts[1];
                let msg = parts.slice(2).join(' ');

                lastTellUser = target;
                openPrivateTab(target);

                sendMessage(msg, "private", target);
            } else if (rawMsg.startsWith('/reply')) {
                if (!lastTellUser) return;
                let msg = rawMsg.replace('/reply', '').trim();
                openPrivateTab(lastTellUser);
                sendMessage(msg, "private", lastTellUser);
            } else if (rawMsg.startsWith('/combat ')) {
                let msg = rawMsg.replace('/combat', '').trim();
                sendMessage(msg, "combat");
            } else if (rawMsg.startsWith('/server ')) {
                let msg = rawMsg.replace('/server', '').trim();
                sendMessage(msg, "server");
            } else {
                sendMessage(rawMsg, "main");
            }
            $('#messageInput').val('');
        });

        function sendMessage(message, type, targetUser = "") {
            $.ajax({
                url: 'send_message.php',
                method: 'POST',
                data: { message: message, user: user, type: type, target: targetUser },
                success: function(response) {
                    // Optionally handle response
                }
            });
        }

        function fetchMessages(tabType) {
            $.ajax({
                url: 'fetch_messages.php',
                method: 'GET',
                data: { tab: tabType, user: user }, // Pass the tab type
                dataType: 'json',
                success: function(messages) {
                    let tabId = tabType === "main" ? "#mainTab"
                               : tabType === "combat" ? "#combatTab"
                               : tabType === "server" ? "#serverTab"
                               : "#private-" + tabType.split('-')[1] + "Tab";
                    $(tabId).empty();
                    messages.forEach(function(message) {
                        $(tabId).append('<span><strong>' + message.user + ':</strong> ' + message.messages_main + '</span>');
                    });
                }
            });
        }

        function fetchUserList() {
            $.ajax({
                url: 'fetch_users.php',
                method: 'GET',
                dataType: 'json',
                success: function(users) {
                    $('#userList').empty();
                    users.forEach(function(u) {
                        $('#userList').append('<div>' + u + '</div>');
                    });
                }
            });
        }

        function openPrivateTab(targetUser) {
            if (!privateTabs[targetUser]) {
                // Add tab button
                $('#tabsBar').append('<button class="tab-btn" data-tab="private-' + targetUser + '">' + targetUser + '</button>');
                // Add tab content
                $('#tabContents').append('<div class="tab-content d-none" id="private-' + targetUser + 'Tab"></div>');
                privateTabs[targetUser] = true;
            }
            $('.tab-btn').removeClass('active');
            $('.tab-content').addClass('d-none');
            $('.tab-btn[data-tab="private-' + targetUser + '"]').addClass('active');
            $('#private-' + targetUser + 'Tab').removeClass('d-none');
        }
    </script>
</body>
