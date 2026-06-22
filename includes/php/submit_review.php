<?php
session_start();
require 'dhb.inc.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$reviewer_id = $_SESSION['user_id'];
$task_id = $data['task_id'];

try {
    // 1. Get Runner info
    $stmtRunner = $pdo->prepare("SELECT RunnerID FROM tasks WHERE TaskID = :task_id");
    $stmtRunner->execute([':task_id' => $task_id]);
    $runner = $stmtRunner->fetch(PDO::FETCH_ASSOC);

    // 2. Insert Review
    $stmt = $pdo->prepare("INSERT INTO reviews (TaskID, ReviewerID, RevieweeID, Rating, Comment, Created_Date) 
                           VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $task_id, 
        $reviewer_id, 
        $runner['RunnerID'], 
        $data['rating'], 
        $data['feedback'],
        date('Y-m-d H:i:s')
    ]);

    // 3. DELETE the task permanently
    $deleteStmt = $pdo->prepare("DELETE FROM tasks WHERE TaskID = ?");
    $deleteStmt->execute([$task_id]);
    
    echo json_encode(['status' => 'success']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>