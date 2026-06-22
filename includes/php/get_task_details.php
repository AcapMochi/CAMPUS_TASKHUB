<?php
require 'dhb.inc.php';
header('Content-Type: application/json');

$id = $_GET['id'] ?? null;

if (!$id) { 
    echo json_encode(['status' => 'error', 'message' => 'No Task ID provided']); 
    exit(); 
}

// 1. Select all the columns your HTML needs
// Make sure these match your database column names exactly!
$stmt = $pdo->prepare("SELECT Title, Description, Reward_Amount, PosterID, CategoryID FROM tasks WHERE TaskID = :id");
$stmt->execute([':id' => $id]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if ($task) {
    // 2. Return the full task object
    echo json_encode(['status' => 'success', 'task' => $task]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Task not found']);
}
?>