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
    $user_id = $_SESSION['user_id'];

    if (!$task_id) {
        echo json_encode(['status' => 'error', 'message' => 'Task ID is missing.']);
        exit();
    }

    try {
        // First, check if the task belongs to the user and is still 'Open'
        $checkStmt = $pdo->prepare("SELECT Status FROM tasks WHERE TaskID = :tid AND PosterID = :uid");
        $checkStmt->execute([':tid' => $task_id, ':uid' => $user_id]);
        $task = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$task) {
            echo json_encode(['status' => 'error', 'message' => 'Task not found or you do not have permission to cancel it.']);
            exit();
        }

        if ($task['Status'] !== 'Open') {
            echo json_encode(['status' => 'error', 'message' => 'You cannot cancel a task that is already in progress or completed.']);
            exit();
        }

        // Update the task status to 'Cancelled'
        $updateStmt = $pdo->prepare("UPDATE tasks SET Status = 'Cancelled' WHERE TaskID = :tid");
        $updateStmt->execute([':tid' => $task_id]);

        echo json_encode(['status' => 'success']);

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>