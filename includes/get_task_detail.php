<?php
require 'dhb.inc.php';

if (isset($_GET['id'])) {
    $task_id = $_GET['id'];

    try {
        // We join the tasks and users table so we can show who posted it
        $sql = "SELECT t.*, u.Username 
                FROM tasks t 
                JOIN users u ON t.PosterID = u.UserID 
                WHERE t.TaskID = :id";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $task_id]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($task) {
            echo json_encode(["status" => "success", "task" => $task]);
        } else {
            echo json_encode(["status" => "error", "message" => "Task not found."]);
        }

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No task ID provided."]);
}
?>