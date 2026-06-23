<?php
session_start();
require 'dhb.inc.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$my_id = $_SESSION['user_id'] ?? null;
$receiver_id = $data['receiver_id'] ?? null;
$task_id = $data['task_id'] ?? null;

if (!$my_id || !$receiver_id || !$task_id) {
    echo json_encode(["status" => "error", "message" => "Missing parameters"]);
    exit();
}

try {
    $sql = "SELECT SenderID, ReceiverID, Content, Sent_At 
            FROM messages 
            WHERE TaskID = :task_id 
              AND ((SenderID = :my_id AND ReceiverID = :rec_id)
               OR (SenderID = :rec_id AND ReceiverID = :my_id))
            ORDER BY Sent_At ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':task_id' => $task_id,
        ':my_id' => $my_id, 
        ':rec_id' => $receiver_id
    ]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $messages = [];
    foreach ($rows as $row) {
        $messages[] = [
            // Forces the key to be standardized for the JS
            'content' => $row['Content'] ?? $row['content'] ?? '',
            'is_mine' => ($row['SenderID'] == $my_id),
            'time' => date('h:i A', strtotime($row['Sent_At']))
        ];
    }

    echo json_encode(["status" => "success", "messages" => $messages]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>