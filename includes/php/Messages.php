<?php
session_start();
require 'dhb.inc.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in.']);
    exit();
}

$sender_id = $_SESSION['user_id'];
$task_id = $_POST['task_id'] ?? null;
$message_text = $_POST['message'] ?? null;

if (!$task_id || !$message_text) {
    echo json_encode(['status' => 'error', 'message' => 'Missing task ID or message.']);
    exit();
}

try {
    // 1. Find out who posted the task (they will be the receiver of the message)
    $stmtTask = $pdo->prepare("SELECT PosterID FROM tasks WHERE TaskID = :tid");
    $stmtTask->execute([':tid' => $task_id]);
    $task = $stmtTask->fetch(PDO::FETCH_ASSOC);

    if (!$task) {
        echo json_encode(['status' => 'error', 'message' => 'Task not found.']);
        exit();
    }

    $receiver_id = $task['PosterID'];

    // 2. Insert the message into your database
    // IMPORTANT: Change "messages" and the column names if your chat table is named differently!
    // Look closely at the INSERT INTO line below:
    $stmtMsg = $pdo->prepare("
        INSERT INTO messages (SenderID, ReceiverID, TaskID, Content, Sent_At) 
        VALUES (:sender, :receiver, :task, :msg, NOW())
    ");
    
    $stmtMsg->execute([
        ':sender' => $sender_id,
        ':receiver' => $receiver_id,
        ':task' => $task_id,
        ':msg' => $message_text
    ]);


    echo json_encode(['status' => 'success']);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>