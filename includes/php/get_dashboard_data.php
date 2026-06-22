<?php
session_start();
require 'dhb.inc.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // 1. Get User Info (Username)
    $stmtUser = $pdo->prepare("SELECT Username FROM users WHERE UserID = :id");
    $stmtUser->execute([':id' => $user_id]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    // 2. Count Open Tasks (System-wide, so the user knows what's available)
    $stmtOpen = $pdo->query("SELECT COUNT(*) as count FROM tasks WHERE Status = 'Open'");
    $openTasks = $stmtOpen->fetch(PDO::FETCH_ASSOC)['count'];

    // 3. Count Tasks Posted by this user
    $stmtPosted = $pdo->prepare("SELECT COUNT(*) as count FROM tasks WHERE PosterID = :id");
    $stmtPosted->execute([':id' => $user_id]);
    $postedTasks = $stmtPosted->fetch(PDO::FETCH_ASSOC)['count'];

    // 4. Get Total Earned and Completed Tasks (As a Runner)
    // Assuming 'Done' is the status for a completed task
    $stmtEarned = $pdo->prepare("
        SELECT 
            COUNT(*) as completed_count, 
            COALESCE(SUM(Reward_Amount), 0) as total_earned 
        FROM tasks 
        WHERE RunnerID = :id AND Status = 'Done'
    ");
    $stmtEarned->execute([':id' => $user_id]);
    $runnerStats = $stmtEarned->fetch(PDO::FETCH_ASSOC);

    // 5. Get "Tasks You May Like" (Top 3 newest open tasks not posted by the user)
    $sqlRecommended = "SELECT t.*, u.Username, c.Name AS CategoryName 
                       FROM tasks t 
                       LEFT JOIN users u ON t.PosterID = u.UserID
                       LEFT JOIN categories c ON t.CategoryID = c.CategoryID
                       WHERE t.Status = 'Open' AND t.PosterID != :id 
                       ORDER BY t.Created_Date DESC LIMIT 3";
    
    $stmtRec = $pdo->prepare($sqlRecommended);
    $stmtRec->execute([':id' => $user_id]);
    $recommendedTasks = $stmtRec->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'username' => $user['Username'],
        'stats' => [
            'open_tasks' => $openTasks,
            'posted_tasks' => $postedTasks,
            'completed_tasks' => $runnerStats['completed_count'],
            'total_earned' => number_format($runnerStats['total_earned'], 2)
        ],
        'recommended_tasks' => $recommendedTasks
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>