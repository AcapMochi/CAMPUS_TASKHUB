<?php
// includes/post_task_handler.php
session_start();
require 'dhb.inc.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "You must be logged in to post a task."]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $poster_id = $_SESSION['user_id'];
    $title = $_POST['title'] ?? '';
    $category_text = $_POST['category'] ?? '';
    $description = $_POST['desc'] ?? '';
    $location = $_POST['location'] ?? '';
    $specific_loc = $_POST['specific_location'] ?? '';
    $reward = $_POST['reward'] ?? '';

    // Clean the RM formatting off if the user typed it
    $reward = str_replace(['RM', ' '], '', $reward);

    // Map Category to ID (Ensure these match your categories table!)[cite: 9]
    $category_id = 1;
    if (strtolower($category_text) == "food")
        $category_id = 1;
    if (strtolower($category_text) == "delivery")
        $category_id = 2;
    if (strtolower($category_text) == "printing")
        $category_id = 3;

    // --- HANDLE FILE UPLOAD ---
    $attachment_url = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['attachment']['tmp_name'];
        $fileName = $_FILES['attachment']['name'];
        $fileSize = $_FILES['attachment']['size'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Allow images and PDFs (printing tasks might need PDFs!)
        $allowedExts = ['jpg', 'jpeg', 'png', 'pdf'];

        if (in_array($fileExtension, $allowedExts) && $fileSize < 5242880) { // 5MB limit
            $newFileName = "task_" . $poster_id . "_" . time() . "." . $fileExtension;

            // We save it in a tasks folder. Path is relative to the includes folder.
            $uploadFileDir = '../images/tasks/';
            $dbPath = 'images/tasks/' . $newFileName; // The path we save to DB
            $actualPath = $uploadFileDir . $newFileName;

            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            if (move_uploaded_file($fileTmpPath, $actualPath)) {
                $attachment_url = $dbPath;
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid file. Must be JPG/PNG/PDF and under 5MB."]);
            exit();
        }
    }

    try {
        // Insert task including the Attachment URL and Specific Location
        $sql = "INSERT INTO tasks (CategoryID, PosterID, Title, Description, Location, `Specific_Location`, `Reward_Amount`, `Attachment_URL`, Status, `Created_Date`) 
                VALUES (:cat_id, :poster, :title, :desc, :loc, :specific_loc, :reward, :attachment, 'Open', NOW())";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':cat_id' => $category_id,
            ':poster' => $poster_id,
            ':title' => $title,
            ':desc' => $description,
            ':loc' => $location,
            ':specific_loc' => $specific_loc,
            ':reward' => $reward,
            ':attachment' => $attachment_url
        ]);

        echo json_encode(["status" => "success", "message" => "Task posted successfully!"]);

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
}
?>