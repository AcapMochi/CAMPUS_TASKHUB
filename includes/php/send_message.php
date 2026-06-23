<?php
session_start();
require 'dhb.inc.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$sender_id = $_SESSION['user_id'] ?? null;
$receiver_id = $data['receiver_id'] ?? null;
$task_id = $data['task_id'] ?? null; 
$message = trim($data['message'] ?? '');

if (!$sender_id || !$receiver_id || !$task_id || $message === '') {
    echo json_encode(["status" => "error", "message" => "Invalid payload fields."]);
    exit();
}

try {
    $sql = "INSERT INTO messages (SenderID, ReceiverID, TaskID, Content, Sent_At) 
            VALUES (:sender, :receiver, :task_id, :content, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':sender' => $sender_id,
        ':receiver' => $receiver_id,
        ':task_id' => $task_id,
        ':content' => $message
    ]);

    echo json_encode(["status" => "success"]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>