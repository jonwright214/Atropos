<?php
include '../database/db_connect.php';

$message = "";
$toastClass = "";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
	
}

//$userId = $_SESSION['user_id'];

// Assume a database connection is established as $pdo
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    die('You must be logged in to create a character.');
	
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_character'])) {
    
	$characterName = htmlspecialchars ($_POST['character_name'], ENT_QUOTES, 'UTF-8');
	$characterRace = htmlspecialchars ($_POST['character_race'], ENT_QUOTES, 'UTF-8');
	$characterClass = htmlspecialchars ($_POST['character_class'], ENT_QUOTES, 'UTF-8');
	
	$userId = $_SESSION['user_id'];
	  
	//$characterName = filter_input(INPUT_POST, 'character_name', FILTER_SANITIZE_STRING);
	//$characterRace = htmlspecialchars ("character_race", ENT_QUOTES);
	//$characterClass = htmlspecialchars ("character_class", ENT_QUOTES);
  
	//$tusername=htmlspecialchars($_POST['username'], ENT_QUOTES,'UTF-8');
	//$password = $_POST['password'];
	
    if ($characterName && $userId && $characterRace && $characterClass) {
        try {
            $stmt = $conn->prepare("INSERT INTO characters (character_name, user_id, character_race, character_class) VALUES (?, ?, ?, ?)");
            $stmt->execute([$characterName, $userId, $characterRace, $characterClass]);
            echo "Character created successfully!";
        } catch (PDOException $e) {
            echo "Error creating character: " . $e->getMessage();
        }
    } else {
        echo "Please provide a character name and class.";
    }
}



//End of PHP
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href=
"https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href=
"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
    <link rel="shortcut icon" href=
"https://cdn-icons-png.flaticon.com/512/295/295128.png">
    <script src=
"https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <title>Registration</title>
</head>

<body class="bg-light">
    <div class="container p-5 d-flex flex-column align-items-center">
        <?php if ($message): ?>
            <div class="toast align-items-center text-white border-0" 
          role="alert" aria-live="assertive" aria-atomic="true"
                style="background-color: <?php echo $toastClass; ?>;">
                <div class="d-flex">
                    <div class="toast-body">
                        <?php echo $message; ?>
                    </div>
                    <button type="button" class="btn-close
                    btn-close-white me-2 m-auto" 
                          data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>
        <form method="post" class="form-control mt-5 p-4"
            style="height:auto; width:380px;
            box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px,
            rgba(60, 64, 67, 0.15) 0px 2px 6px 2px;">
            <div class="row text-center">
                <i class="fa fa-user-circle-o fa-3x mt-1 mb-2" style="color: green;"></i>
                <h5 class="p-4" style="font-weight: 700;">Create Your Character</h5>
            </div>
            <div class="mb-2">
                <label for="username"><i 
                  class="fa fa-user"></i> Character Name</label>
                <input type="text" name="character_name" id="character_name"
                  class="form-control" required>
            </div>
            <div class="mb-2 mt-2">
                <label for="email"><i 
                  class="fa fa-envelope"></i> Race</label>
                <input type="text" name="character_race" id="character_race"
                  class="form-control" required>
            </div>
            <div class="mb-2 mt-2">
                <label for="password"><i 
                  class="fa fa-lock"></i> Class</label>
                <input type="text" name="character_class" id="character_class"
                  class="form-control" required>
            </div>
            <div class="mb-2 mt-3">
                <button type="submit" name="create_character" id="create_character"
                  class="btn btn-success 
                bg-success" style="font-weight: 600;">Create
                    Character</button>
            </div>
			<div class="mb-2 mt-4">
                <p class="text-center" style="font-weight: 600; 
                color: navy;">I have a character already. <a href="./character_selection.php"
                        style="text-decoration: none;">Character Select</a></p>
            </div>
        </form>
    </div>
    <script>
        let toastElList = [].slice.call(document.querySelectorAll('.toast'))
        let toastList = toastElList.map(function (toastEl) {
            return new bootstrap.Toast(toastEl, { delay: 3000 });
        });
        toastList.forEach(toast => toast.show());
    </script>
</body>


</html>

