<?php
session_start();
require 'dhb.inc.php';

header('Content-Type: application/json');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = $_POST['task_id'] ?? null;
    $user_id = $_SESSION['user_id']; // The Poster

    if (!$task_id) {
        echo json_encode(['status' => 'error', 'message' => 'Task ID is missing.']);
        exit();
    }

    try {
        // 1. Verify that this user is actually the Poster of this task
        $checkStmt = $pdo->prepare("SELECT Status FROM tasks WHERE TaskID = :tid AND PosterID = :uid");
        $checkStmt->execute([':tid' => $task_id, ':uid' => $user_id]);
        $task = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$task) {
            echo json_encode(['status' => 'error', 'message' => 'Task not found or permission denied.']);
            exit();
        }

        if ($task['Status'] === 'Completed') {
            // If it's already completed, just tell the frontend it was a success so it redirects
            echo json_encode(['status' => 'success']);
            exit();
        }

        // 2. Update the task status to 'Completed' and set the Completed_Date
        $updateStmt = $pdo->prepare("UPDATE tasks SET Status = 'Completed', Completed_Date = NOW() WHERE TaskID = :tid");
        $updateStmt->execute([':tid' => $task_id]);

        echo json_encode(['status' => 'success']);

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>