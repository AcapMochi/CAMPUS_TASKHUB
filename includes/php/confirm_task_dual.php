<?php
session_start();
require 'dhb.inc.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = $_POST['task_id'] ?? null;
    $user_id = $_SESSION['user_id'];

    if (!$task_id) {
        echo json_encode(['status' => 'error', 'message' => 'Missing Task ID']);
        exit();
    }

    try {
        // 1. Get the current task state
        $stmt = $pdo->prepare("SELECT PosterID, RunnerID, Runner_Confirmed, Poster_Confirmed, Status FROM tasks WHERE TaskID = :tid");
        $stmt->execute([':tid' => $task_id]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$task) {
            echo json_encode(['status' => 'error', 'message' => 'Task not found.']);
            exit();
        }

        $is_poster = ($user_id == $task['PosterID']);
        $is_runner = ($user_id == $task['RunnerID']);

        if (!$is_poster && !$is_runner) {
            echo json_encode(['status' => 'error', 'message' => 'Permission denied.']);
            exit();
        }

        // 2. Lock in this user's confirmation
        $updateCol = $is_poster ? 'Poster_Confirmed' : 'Runner_Confirmed';
        $pdo->prepare("UPDATE tasks SET $updateCol = 1 WHERE TaskID = :tid")->execute([':tid' => $task_id]);

        // 3. Re-check if BOTH are now 1
        $runner_conf = $is_runner ? 1 : $task['Runner_Confirmed'];
        $poster_conf = $is_poster ? 1 : $task['Poster_Confirmed'];
        
        $fully_completed = false;

        if ($runner_conf == 1 && $poster_conf == 1) {
            // BOTH have confirmed! Finalize the task.
            $pdo->prepare("UPDATE tasks SET Status = 'Completed', Completed_Date = NOW() WHERE TaskID = :tid")->execute([':tid' => $task_id]);
            $fully_completed = true;
        }

        echo json_encode([
            'status' => 'success', 
            'fully_completed' => $fully_completed,
            'waiting_on_other' => !$fully_completed
        ]);

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error.']);
    }
}
?>