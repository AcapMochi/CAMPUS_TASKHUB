<?php
session_start();
require 'dhb.inc.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "You must be logged in to post a task."]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $poster_id = $_SESSION['user_id'];
    $title = $_POST['title'];
    $category_text = $_POST['category'];
    $description = $_POST['desc'];
    $location = $_POST['location'];
    $reward = $_POST['reward'];
    
    // Map the HTML dropdown text to your database CategoryID
    // Make sure your `categories` table has these IDs!
    $category_id = 1; // Default
    if ($category_text == "Food") $category_id = 1;
    if ($category_text == "Delivery") $category_id = 2;

    try {
        $sql = "INSERT INTO tasks (CategoryID, PosterID, Title, Description, Location, `Specific Location`, `Reward Amount`, Status) 
                VALUES (:cat_id, :poster, :title, :desc, :loc, :specific_loc, :reward, 'Open')";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':cat_id' => $category_id,
            ':poster' => $poster_id,
            ':title' => $title,
            ':desc' => $description,
            ':loc' => $location,
            ':specific_loc' => '', // From your form's specific location input
            ':reward' => str_replace('RM', '', $reward) // Clean the RM off if the user typed it
        ]);

        echo json_encode(["status" => "success", "message" => "Task posted successfully!"]);

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
}
?>