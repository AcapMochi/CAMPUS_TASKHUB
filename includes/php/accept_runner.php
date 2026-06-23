<?php
// includes/php/accept_runner.php
session_start();
require 'dhb.inc.php';

header('Content-Type: application/json');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in.']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $poster_id = $_SESSION['user_id'];
    $task_id = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;
    $runner_id = isset($_POST['runner_id']) ? intval($_POST['runner_id']) : 0;

    if ($task_id <= 0 || $runner_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data provided.']);
        exit();
    }

    try {
        // Start a transaction so if one query fails, they all fail safely
        $pdo->beginTransaction();

        // 1. Update the 'tasks' table
        $updateTask = "UPDATE tasks 
                       SET RunnerID = :runner_id, Status = 'In Progress' 
                       WHERE TaskID = :task_id AND PosterID = :poster_id AND Status IN ('Open', 'Pending')";
        $stmt1 = $pdo->prepare($updateTask);
        $stmt1->execute([
            ':runner_id' => $runner_id,
            ':task_id' => $task_id,
            ':poster_id' => $poster_id
        ]);

        // Check if the task was actually updated (prevents modifying tasks they don't own)
        if ($stmt1->rowCount() === 0) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Could not accept runner. Task may not exist, or you are not the poster.']);
            exit();
        }

        // 2. Update the 'applying' table for the ACCEPTED runner
        $acceptRunner = "UPDATE applying SET Status = 'Accepted' WHERE TaskID = :task_id AND RunnerID = :runner_id";
        $stmt2 = $pdo->prepare($acceptRunner);
        $stmt2->execute([':task_id' => $task_id, ':runner_id' => $runner_id]);

        // 3. Update the 'applying' table to REJECT the other runners who applied
        $rejectOthers = "UPDATE applying SET Status = 'Rejected' WHERE TaskID = :task_id AND RunnerID != :runner_id";
        $stmt3 = $pdo->prepare($rejectOthers);
        $stmt3->execute([':task_id' => $task_id, ':runner_id' => $runner_id]);

        // Commit the transaction
        $pdo->commit();

        echo json_encode(['status' => 'success', 'message' => 'Runner accepted successfully.']);

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>