<?php
session_start();
require 'dhb.inc.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "You must be logged in to report an issue."]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$reporter_id = $_SESSION['user_id'];
$task_id = $data['task_id'] ?? null;
$reason = trim($data['reason'] ?? '');

if (!$task_id || empty($reason)) {
    echo json_encode(["status" => "error", "message" => "Task ID and details are required."]);
    exit();
}

try {
    // 1. Get the Task details
    $taskStmt = $pdo->prepare("SELECT PosterID, RunnerID FROM tasks WHERE TaskID = :tid");
    $taskStmt->execute([':tid' => $task_id]);
    $task = $taskStmt->fetch(PDO::FETCH_ASSOC);

    if (!$task) {
        echo json_encode(["status" => "error", "message" => "Task not found."]);
        exit();
    }

    // 2. Identify who is being reported
    $reported_user_id = null;
    
    if ($reporter_id == $task['PosterID']) {
        // Poster is reporting the Runner (ensure a runner exists first)
        $reported_user_id = $task['RunnerID'] ? $task['RunnerID'] : $task['PosterID']; 
    } elseif ($task['RunnerID'] && $reporter_id == $task['RunnerID']) {
        // Runner is reporting the Poster
        $reported_user_id = $task['PosterID'];
    } else {
        // A standard user is browsing and reporting the task listing itself (Report the Poster)
        $reported_user_id = $task['PosterID'];
    }

    // 3. Insert into reports table
    $sql = "INSERT INTO reports (TaskID, ReporterID, ReportedUserID, Reason, Created_Date) 
            VALUES (:task_id, :reporter, :reported, :reason, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':task_id' => $task_id,
        ':reporter' => $reporter_id,
        ':reported' => $reported_user_id,
        ':reason' => $reason
    ]);

    echo json_encode(["status" => "success"]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>