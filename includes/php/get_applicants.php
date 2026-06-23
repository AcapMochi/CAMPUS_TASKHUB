<?php
session_start();
require 'dhb.inc.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

if (!isset($_GET['task_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Task ID missing']);
    exit();
}

$task_id = $_GET['task_id'];

try {
    // UPDATED SQL: 
    // We removed the u.Rating column and just send 'New' to the frontend 
    // so your JavaScript doesn't break.
    $sql = "SELECT 
                a.RunnerID, 
                u.Username, 
                u.Profile_Pic_URL, 
                'New' AS `Rating Average` 
            FROM applying a
            JOIN users u ON a.RunnerID = u.UserID
            WHERE a.TaskID = :task_id 
            AND a.Status = 'Pending'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':task_id' => $task_id]);

    $applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'applicants' => $applicants
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>