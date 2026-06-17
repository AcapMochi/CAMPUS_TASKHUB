<?php
session_start();
require 'dhb.inc.php';

// Tell the browser we are sending JSON data back
header('Content-Type: application/json');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "You must be logged in to accept tasks."]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $runner_id = $_SESSION['user_id'];
    
    // Read the incoming JSON data from JavaScript
    $data = json_decode(file_get_contents("php://input"), true);
    $task_id = $data['task_id'] ?? null;

    if (!$task_id) {
        echo json_encode(["status" => "error", "message" => "No task ID provided."]);
        exit();
    }

    try {
        // Step 1: Check if the task is still open and the user isn't the poster
        $checkStmt = $pdo->prepare("SELECT PosterID, Status FROM tasks WHERE TaskID = :tid");
        $checkStmt->execute([':tid' => $task_id]);
        $task = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$task) {
            echo json_encode(["status" => "error", "message" => "Task not found."]);
            exit();
        }

        if ($task['Status'] !== 'Open') {
            echo json_encode(["status" => "error", "message" => "This task has already been accepted by someone else."]);
            exit();
        }

        if ($task['PosterID'] == $runner_id) {
            echo json_encode(["status" => "error", "message" => "You cannot accept a task you posted yourself!"]);
            exit();
        }

        // Step 2: Update the task! Assign the Runner and change status to 'In Progress'
        $updateStmt = $pdo->prepare("UPDATE tasks SET RunnerID = :runner, Status = 'In Progress' WHERE TaskID = :tid");
        $updateStmt->execute([
            ':runner' => $runner_id,
            ':tid' => $task_id
        ]);

        echo json_encode(["status" => "success", "message" => "Task accepted successfully!"]);

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
}
?>