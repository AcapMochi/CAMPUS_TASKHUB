<?php
session_start();
require 'dhb.inc.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // 1. Get User Info (Username, Rating)
    $stmt = $pdo->prepare("SELECT Username, Rating_Average FROM users WHERE UserID = :uid");
    $stmt->execute([':uid' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Global Stats (Total Open Tasks & Total Active Users)
    $openTasksStmt = $pdo->query("SELECT COUNT(*) FROM tasks WHERE Status = 'Open'");
    $totalOpenTasks = $openTasksStmt->fetchColumn();

    $usersStmt = $pdo->query("SELECT COUNT(*) FROM users");
    $totalUsers = $usersStmt->fetchColumn();

    // 3. User Earnings (Sum of all completed tasks where user was the runner)
    $earnedStmt = $pdo->prepare("SELECT SUM(Reward_Amount) FROM tasks WHERE RunnerID = :uid AND Status = 'Completed'");
    $earnedStmt->execute([':uid' => $user_id]);
    $totalEarned = $earnedStmt->fetchColumn() ?: 0; // Default to 0 if null

    // 4. Tasks You May Like (3 Latest Open Tasks not posted by the user)
    $suggestedStmt = $pdo->prepare("
        SELECT t.TaskID, t.Title, t.Reward_Amount, t.Created_Date, 
               c.Name AS CategoryName, u.Rating_Average AS PosterRating, u.Profile_Pic_URL AS PosterPic 
        FROM tasks t 
        JOIN categories c ON t.CategoryID = c.CategoryID 
        JOIN users u ON t.PosterID = u.UserID 
        WHERE t.Status = 'Open' AND t.PosterID != :uid 
        ORDER BY t.Created_Date DESC LIMIT 3
    ");
    $suggestedStmt->execute([':uid' => $user_id]);
    $suggestedTasks = $suggestedStmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. User Activity Progress
    // Tasks they are running (Completed vs Total Assigned)
    $runnerProgStmt = $pdo->prepare("
        SELECT 
            COUNT(CASE WHEN Status = 'Completed' THEN 1 END) as CompletedRunner,
            COUNT(*) as TotalRunner
        FROM tasks WHERE RunnerID = :uid
    ");
    $runnerProgStmt->execute([':uid' => $user_id]);
    $runnerProgress = $runnerProgStmt->fetch(PDO::FETCH_ASSOC);

    // Tasks they posted
    $postedStmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE PosterID = :uid");
    $postedStmt->execute([':uid' => $user_id]);
    $totalPosted = $postedStmt->fetchColumn();

    // 6. Recent Activity (Latest 3 tasks user is involved in)
    $recentStmt = $pdo->prepare("
        SELECT Title, Status 
        FROM tasks 
        WHERE PosterID = :uid OR RunnerID = :uid 
        ORDER BY Created_Date DESC LIMIT 3
    ");
    $recentStmt->execute([':uid' => $user_id]);
    $recentActivity = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

    // Send everything to the frontend
    echo json_encode([
        'status' => 'success',
        'user' => [
            'username' => $user['Username'],
            'rating' => number_format($user['Rating_Average'], 1)
        ],
        'stats' => [
            'open_tasks' => $totalOpenTasks,
            'total_users' => $totalUsers,
            'total_earned' => number_format($totalEarned, 0)
        ],
        'progress' => [
            'completed_runner' => $runnerProgress['CompletedRunner'],
            'total_runner' => $runnerProgress['TotalRunner'],
            'total_posted' => $totalPosted
        ],
        'suggested_tasks' => $suggestedTasks,
        'recent_activity' => $recentActivity
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>