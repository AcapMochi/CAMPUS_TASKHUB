<?php
// includes/complete_task_handler.php
session_start();
require 'dhb.inc.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$task_id = $data['task_id'] ?? null;

if (!$task_id) {
    echo json_encode(["status" => "error", "message" => "Missing task ID"]);
    exit();
}

try {
    // Start transaction to ensure money and task status update together
    $pdo->beginTransaction();

    // 1. Verify the task belongs to the poster and is in progress
    $stmt = $pdo->prepare("SELECT RunnerID FROM tasks WHERE TaskID = :tid AND PosterID = :poster AND Status = 'In Progress'");
    $stmt->execute([':tid' => $task_id, ':poster' => $_SESSION['user_id']]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$task) {
        throw new Exception("Invalid task or you do not have permission.");
    }

    // 2. Mark the Task as Completed
    $updateTask = $pdo->prepare("UPDATE tasks SET Status = 'Completed', `Completed Date` = NOW() WHERE TaskID = :tid");
    $updateTask->execute([':tid' => $task_id]);

    // 3. Release the Escrow Payment!
    $releasePayment = $pdo->prepare("UPDATE payments SET Status = 'Released to Runner' WHERE TaskID = :tid");
    $releasePayment->execute([':tid' => $task_id]);

    // 4. Send a notification to the Runner
    $notify = $pdo->prepare("INSERT INTO notifications (UserID, Message, Type, `Is Read`, `Created Date`) 
                             VALUES (:runner, 'Task completed! RM has been released to your account.', 'Payment', 0, NOW())");
    $notify->execute([':runner' => $task['RunnerID']]);

    $pdo->commit();
    echo json_encode(["status" => "success"]);
    
} catch(Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>