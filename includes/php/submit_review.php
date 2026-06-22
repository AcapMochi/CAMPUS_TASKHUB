<?php
// includes/submit_review.php
session_start();
require 'dhb.inc.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$task_id = $data['task_id'] ?? null;
$rating = $data['rating'] ?? 0;
$comment = $data['comment'] ?? '';

if (!$task_id || $rating < 1 || $rating > 5) {
    echo json_encode(["status" => "error", "message" => "Invalid rating or task data."]);
    exit();
}

try {
    $pdo->beginTransaction();

    // 1. Find the Runner who did this task
    $stmt = $pdo->prepare("SELECT RunnerID FROM tasks WHERE TaskID = :tid AND PosterID = :poster");
    $stmt->execute([':tid' => $task_id, ':poster' => $_SESSION['user_id']]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$task || !$task['RunnerID']) {
        throw new Exception("Invalid task data.");
    }
    
    $runner_id = $task['RunnerID'];

    // 2. Insert into the reviews table
    $insReview = $pdo->prepare("INSERT INTO reviews (TaskID, ReviewerID, RevieweeID, Rating, Comment, `Created Date`) 
                                VALUES (:tid, :reviewer, :reviewee, :rating, :comment, NOW())");
    $insReview->execute([
        ':tid' => $task_id,
        ':reviewer' => $_SESSION['user_id'],
        ':reviewee' => $runner_id,
        ':rating' => $rating,
        ':comment' => htmlspecialchars($comment) // Prevent XSS!
    ]);

    // 3. Recalculate the Runner's Average Rating
    $calcAvg = $pdo->prepare("SELECT AVG(Rating) as avg_rating FROM reviews WHERE RevieweeID = :rid");
    $calcAvg->execute([':rid' => $runner_id]);
    $avg = $calcAvg->fetch()['avg_rating'];

    // 4. Update the Runner's profile in the users table
    $updateUser = $pdo->prepare("UPDATE users SET `Rating Average` = :avg WHERE UserID = :rid");
    $updateUser->execute([
        ':avg' => round($avg, 1), 
        ':rid' => $runner_id
    ]);

    $pdo->commit();
    echo json_encode(["status" => "success", "message" => "Review submitted!"]);

} catch(Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>