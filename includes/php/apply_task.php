<?php
// includes/php/apply_task.php
session_start();
require 'dhb.inc.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to apply.']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $runner_id = $_SESSION['user_id'];
    $task_id = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;
    $message = isset($_POST['message']) ? trim($_POST['message']) : 'I would like to help with this task!';

    if ($task_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid task.']);
        exit();
    }

    try {
        // 1. Check if the user is trying to apply to their own task
        $checkTask = $pdo->prepare("SELECT PosterID FROM tasks WHERE TaskID = :task_id");
        $checkTask->execute([':task_id' => $task_id]);
        $task = $checkTask->fetch(PDO::FETCH_ASSOC);

        if ($task && $task['PosterID'] == $runner_id) {
            echo json_encode(['status' => 'error', 'message' => 'You cannot apply to your own task!']);
            exit();
        }

        // 2. Check if they already applied
        $checkApp = $pdo->prepare("SELECT ApplyingID FROM applying WHERE TaskID = :task_id AND RunnerID = :runner_id");
        $checkApp->execute([':task_id' => $task_id, ':runner_id' => $runner_id]);
        
        if ($checkApp->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'You have already applied for this task.']);
            exit();
        }

        // 3. Insert into the applying table
        $sql = "INSERT INTO applying (TaskID, RunnerID, Status, Message) VALUES (:task_id, :runner_id, 'Pending', :message)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':task_id' => $task_id,
            ':runner_id' => $runner_id,
            ':message' => $message
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Application submitted! Waiting for poster approval.']);

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>