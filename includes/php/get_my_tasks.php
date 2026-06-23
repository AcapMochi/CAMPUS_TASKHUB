<?php
session_start();
// Adjust path if your database connection file is elsewhere
require 'dhb.inc.php';

header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

// THE FIX: Initialize these variables as empty arrays first!
// Now PHP won't freak out if the database returns zero results.
$posted_tasks = [];
$runner_tasks = [];

try {
    // 1. GET TASKS POSTED BY THIS USER
    // We use a LEFT JOIN here to grab the profile picture and username of the accepted runner
    $sqlPosted = "SELECT 
                      t.*, 
                      u.Username AS RunnerUsername, 
                      u.Profile_Pic_URL AS RunnerPic 
                  FROM tasks t 
                  LEFT JOIN users u ON t.RunnerID = u.UserID 
                  WHERE t.PosterID = :user_id 
                  ORDER BY t.Created_Date DESC";

    $stmtPosted = $pdo->prepare($sqlPosted);
    $stmtPosted->execute([':user_id' => $user_id]);
    $posted_tasks = $stmtPosted->fetchAll(PDO::FETCH_ASSOC);

    // 2. GET TASKS THIS USER APPLIED TO BE A RUNNER FOR
    // We added a LEFT JOIN to the 'users' table to get the Poster's name
    $sqlRunner = "SELECT 
                      a.Status AS AppStatus, 
                      t.*,
                      u.Username AS PosterName 
                  FROM applying a
                  JOIN tasks t ON a.TaskID = t.TaskID
                  LEFT JOIN users u ON t.PosterID = u.UserID
                  WHERE a.RunnerID = :user_id
                  ORDER BY a.Applied_Date DESC";

    $stmtRunner = $pdo->prepare($sqlRunner);
    $stmtRunner->execute([':user_id' => $user_id]);
    $runner_tasks = $stmtRunner->fetchAll(PDO::FETCH_ASSOC);

    // 3. SEND BOTH LISTS TO JAVASCRIPT
    echo json_encode([
        'status' => 'success',
        'posted_tasks' => $posted_tasks,
        'runner_tasks' => $runner_tasks
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>