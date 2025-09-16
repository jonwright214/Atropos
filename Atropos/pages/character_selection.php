<?php
include '../database/db_connect.php';

$message = "";
$toastClass = "";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    die('You must be logged in to create a character.');
}

$user_id = $_SESSION['user_id'];

// Get characters for the current user
$stmt = $conn->prepare("SELECT character_name FROM characters WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$characterNames = [];
while ($row = $result->fetch_assoc()) {
    $characterNames[] = $row['character_name'];
}
$stmt->close();

// Fill up to 5 slots
$maxSlots = 5;
while (count($characterNames) < $maxSlots) {
    $characterNames[] = 'Empty';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- ... head as before ... -->
</head>
<body class="bg-light">
    <div class="container p-5 d-flex flex-column align-items-center">
        <?php if ($message): ?>
            <!-- ... toast code ... -->
        <?php endif; ?>
        <form action="./main_page.php" method="post" class="form-control mt-5 p-4"
            style="height:auto; width:380px;
            box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px,
            rgba(60, 64, 67, 0.15) 0px 2px 6px 2px;">
            <div class="row text-center">
                <i class="fa fa-user-circle-o fa-3x mt-1 mb-2" style="color: green;"></i>
                <h5 class="p-4" style="font-weight: 700;">Select Your Character</h5>
            </div>
            <!-- Dynamically generate up to 5 radio buttons -->
            <?php for ($i = 0; $i < $maxSlots; $i++): ?>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="radioCharacterSelection"
                        id="radioCharacterSelection<?php echo $i+1; ?>"
                        value="<?php echo htmlspecialchars($characterNames[$i]); ?>"
                        <?php if ($i == 0) echo 'required'; ?>>
                    <label class="form-check-label" for="radioCharacterSelection<?php echo $i+1; ?>">
                        <?php echo htmlspecialchars($characterNames[$i]); ?>
                    </label>
                </div>
            <?php endfor; ?>
            <div class="mb-2 mt-3">
                <button type="submit" name="enter_world" id="enter_world"
                  class="btn btn-success bg-success" style="font-weight: 600;">Enter World</button>
            </div>
        </form>
    </div>
    <!-- ... toast JS ... -->
</body>
</html>