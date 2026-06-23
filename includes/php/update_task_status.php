<?php
session_start();
require 'dhb.inc.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in.']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $task_id = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;
    $new_status = isset($_POST['status']) ? trim($_POST['status']) : '';

    if ($task_id <= 0 || empty($new_status)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data.']);
        exit();
    }

    try {
        // Only update if the current user is actually the assigned runner
        $sql = "UPDATE tasks SET Status = :status WHERE TaskID = :task_id AND RunnerID = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':status' => $new_status, ':task_id' => $task_id, ':user_id' => $user_id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Update failed. Task may not exist.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>