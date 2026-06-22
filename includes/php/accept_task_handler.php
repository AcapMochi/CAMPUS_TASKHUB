<?php
// includes/accept_task_handler.php
session_start();
require 'dhb.inc.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized. Please log in."]);
    exit();
}

$runner_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);
$task_id = $data['task_id'] ?? null;
// Optional: If you ever add a text box for runners to say "I'm near the cafe!"
$message = $data['message'] ?? 'I would like to help with this task!'; 

if (!$task_id) {
    echo json_encode(["status" => "error", "message" => "No task ID provided."]);
    exit();
}

try {
    // 1. Check if the task is still open and the user isn't the poster [cite: 37, 42]
    $checkStmt = $pdo->prepare("SELECT PosterID, Status FROM tasks WHERE TaskID = :tid");
    $checkStmt->execute([':tid' => $task_id]);
    $task = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$task || $task['Status'] !== 'Open') {
        echo json_encode(["status" => "error", "message" => "This task is no longer available."]);
        exit();
    }

    if ($task['PosterID'] == $runner_id) {
        echo json_encode(["status" => "error", "message" => "You cannot apply for a task you posted!"]);
        exit();
    }

    // 2. Prevent duplicate applications [cite: 1, 7]
    $applyCheck = $pdo->prepare("SELECT * FROM applying WHERE TaskID = :tid AND RunnerID = :rid");
    $applyCheck->execute([':tid' => $task_id, ':rid' => $runner_id]);
    if ($applyCheck->fetch()) {
        echo json_encode(["status" => "error", "message" => "You have already applied for this task. Please wait for the poster to decide."]);
        exit();
    }

    // 3. Insert into the applying table with 'Pending' status [cite: 1]
    $sql = "INSERT INTO applying (TaskID, RunnerID, Status, Message, `Applied_Date`) 
            VALUES (:tid, :rid, 'Pending', :msg, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':tid' => $task_id,
        ':rid' => $runner_id,
        ':msg' => htmlspecialchars($message)
    ]);

    // 4. Notify the poster [cite: 17]
    $notifSql = "INSERT INTO notifications (UserID, Message, Type, `Is_Read`, `Created_Date`)
                 VALUES (:poster, 'Someone applied to your task!', 'Task Update', 0, NOW())";
    $pdo->prepare($notifSql)->execute([':poster' => $task['PosterID']]);

    echo json_encode(["status" => "success", "message" => "Application sent! Wait for the poster to accept."]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>