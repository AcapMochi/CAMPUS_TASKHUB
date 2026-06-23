<?php
session_start();
require 'dhb.inc.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$task_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

try {
    // Fetch task details AND the Poster's info
    $sql = "SELECT t.*, p.Username AS PosterName, p.Profile_Pic_URL AS PosterPic, p.Rating_Average AS PosterRating 
            FROM tasks t 
            JOIN users p ON t.PosterID = p.UserID 
            WHERE t.TaskID = :task_id AND t.RunnerID = :user_id";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':task_id' => $task_id, ':user_id' => $user_id]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($task) {
        echo json_encode(['status' => 'success', 'task' => $task]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Task not found or you are not the assigned runner.']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>