<?php
session_start();
require 'dhb.inc.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_GET['receiver_id'])) {
    echo json_encode(['status' => 'error']);
    exit();
}

$me = $_SESSION['user_id'];
$them = $_GET['receiver_id'];

try {
    // UPDATED: Changed SentAt to Sent_At to match your database
    $sql = "SELECT *, DATE_FORMAT(Sent_At, '%H:%i') as time 
            FROM messages 
            WHERE (SenderID = :me AND ReceiverID = :them) 
               OR (SenderID = :them AND ReceiverID = :me) 
            ORDER BY Sent_At ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':me' => $me, ':them' => $them]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Tell the frontend which messages belong to the logged-in user
    foreach ($messages as &$msg) {
        $msg['is_mine'] = ($msg['SenderID'] == $me);
    }

    echo json_encode([
        'status' => 'success',
        'messages' => $messages
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>