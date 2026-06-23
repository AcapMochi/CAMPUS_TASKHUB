<?php
session_start();
require 'dhb.inc.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Task ID is required.']);
    exit();
}

$task_id = $_GET['id'];

try {
    // 1. Fetch Task Info (We use LEFT JOIN on users as 'r' to get the Runner's Name)
    $sqlTask = "SELECT t.*, 
                       p.Username AS PosterName, 
                       r.Username AS RunnerName, 
                       r.Profile_Pic_URL AS RunnerPic,
                       c.Name AS CategoryName 
                FROM tasks t 
                JOIN users p ON t.PosterID = p.UserID 
                LEFT JOIN users r ON t.RunnerID = r.UserID 
                LEFT JOIN categories c ON t.CategoryID = c.CategoryID 
                WHERE t.TaskID = :tid";
                
    $stmtTask = $pdo->prepare($sqlTask);
    $stmtTask->execute([':tid' => $task_id]);
    $task = $stmtTask->fetch(PDO::FETCH_ASSOC);

    if (!$task) {
        echo json_encode(['status' => 'error', 'message' => 'Task not found.']);
        exit();
    }

    // 2. Fetch Applicants (This keeps your taskProgressPoster.html working!)
    $sqlApplicants = "SELECT u.UserID, u.Username, u.Rating_Average AS Rating, u.Profile_Pic_URL 
                      FROM applying a 
                      JOIN users u ON a.RunnerID = u.UserID 
                      WHERE a.TaskID = :tid AND a.Status = 'Pending'";
                      
    $stmtApp = $pdo->prepare($sqlApplicants);
    $stmtApp->execute([':tid' => $task_id]);
    $applicants = $stmtApp->fetchAll(PDO::FETCH_ASSOC);

    // Send everything back to the frontend
    echo json_encode([
        'status' => 'success', 
        'task' => $task, 
        'applicants' => $applicants
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>