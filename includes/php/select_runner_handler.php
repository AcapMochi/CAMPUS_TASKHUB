<?php
session_start();
// Adjust this path to wherever your database connection file actually is!
require 'dhb.inc.php';

header('Content-Type: application/json');

// Read the incoming JSON data from JavaScript
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['task_id']) || !isset($data['runner_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Task ID or Runner ID is missing.']);
    exit();
}

$task_id = $data['task_id'];
$runner_id = $data['runner_id'];

try {
    // Start database transaction
    $pdo->beginTransaction();

    // 1. Mark the chosen runner as 'Accepted' in the applying table
    $sqlAccept = "UPDATE applying SET Status = 'Accepted' WHERE TaskID = :task_id AND RunnerID = :runner_id";
    $stmtAccept = $pdo->prepare($sqlAccept);
    $stmtAccept->execute([':task_id' => $task_id, ':runner_id' => $runner_id]);

    // 2. Mark all other applicants for this specific task as 'Rejected'
    $sqlReject = "UPDATE applying SET Status = 'Rejected' WHERE TaskID = :task_id AND RunnerID != :runner_id";
    $stmtReject = $pdo->prepare($sqlReject);
    $stmtReject->execute([':task_id' => $task_id, ':runner_id' => $runner_id]);

    // 3. Update the tasks table: Change Status to 'In_Progress' AND assign the RunnerID
    $sqlTask = "UPDATE tasks SET Status = 'In_Progress', RunnerID = :runner_id WHERE TaskID = :task_id";
    $stmtTask = $pdo->prepare($sqlTask);
    $stmtTask->execute([
        ':task_id' => $task_id,
        ':runner_id' => $runner_id
    ]);

    // Commit the changes to the database
    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Runner successfully accepted!'
    ]);

} catch (PDOException $e) {
    // If something broke, roll back the changes
    $pdo->rollBack();
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>