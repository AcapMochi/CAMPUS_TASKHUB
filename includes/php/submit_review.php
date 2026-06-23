<?php
session_start();
require 'dhb.inc.php';

header('Content-Type: application/json');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Please log in.']);
    exit();
}

// Read the incoming JSON data from JavaScript
$data = json_decode(file_get_contents("php://input"), true);
$task_id = $data['task_id'] ?? null;
$rating = $data['rating'] ?? 0;
$feedback = trim($data['feedback'] ?? '');
$reviewer_id = $_SESSION['user_id']; // The Poster

if (!$task_id || $rating == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Task ID and Rating are required.']);
    exit();
}

try {
    // 1. Get the Runner's ID from the task
    $taskStmt = $pdo->prepare("SELECT RunnerID FROM tasks WHERE TaskID = :tid AND PosterID = :uid");
    $taskStmt->execute([':tid' => $task_id, ':uid' => $reviewer_id]);
    $task = $taskStmt->fetch(PDO::FETCH_ASSOC);

    if (!$task || !$task['RunnerID']) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid task or runner not found.']);
        exit();
    }

    $runner_id = $task['RunnerID'];

    // 2. Insert the Review into the database
    // (Make sure you have a `reviews` table in your database!)
    $reviewStmt = $pdo->prepare("INSERT INTO reviews (ReviewerID, RevieweeID, TaskID, Rating, Comment, Created_Date) 
                                 VALUES (:reviewer, :reviewee, :task, :rating, :comment, NOW())");
    $reviewStmt->execute([
        ':reviewer' => $reviewer_id,
        ':reviewee' => $runner_id,
        ':task' => $task_id,
        ':rating' => $rating,
        ':comment' => $feedback
    ]);

    // 3. Automatically Recalculate the Runner's overall Rating Average
    $avgStmt = $pdo->prepare("SELECT AVG(Rating) as newAvg FROM reviews WHERE RevieweeID = :runner");
    $avgStmt->execute([':runner' => $runner_id]);
    $avgResult = $avgStmt->fetch(PDO::FETCH_ASSOC);
    
    // Round to 1 decimal place (e.g., 4.7)
    $newAverage = round($avgResult['newAvg'], 1);

    // 4. Update the Runner's profile with their new rating
    $updateUser = $pdo->prepare("UPDATE users SET Rating_Average = :avg WHERE UserID = :runner");
    $updateUser->execute([
        ':avg' => $newAverage, 
        ':runner' => $runner_id
    ]);

    echo json_encode(['status' => 'success']);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>