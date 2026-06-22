<?php
// includes/process_payment.php
session_start();
require 'dhb.inc.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$task_id = $data['task_id'] ?? null;
$method = $data['method'] ?? 'Online Banking';

if (!$task_id) {
    echo json_encode(["status" => "error", "message" => "Missing task details."]);
    exit();
}

try {
    $pdo->beginTransaction();

    // 1. Get the task details to confirm the reward amount and Runner
    $taskStmt = $pdo->prepare("SELECT Reward_Amount, RunnerID FROM tasks WHERE TaskID = :tid AND PosterID = :poster");
    $taskStmt->execute([':tid' => $task_id, ':poster' => $_SESSION['user_id']]);
    $task = $taskStmt->fetch(PDO::FETCH_ASSOC);

    if (!$task) {
        throw new Exception("Task not found or you are not the poster.");
    }

    $reward = $task['Reward_Amount'];
    $platform_fee = 0.50; // Standard TaskHub platform fee

    // 2. Insert into the payments table as 'Held in Escrow'
    $paySql = "INSERT INTO payments (PayerID, TaskID, `Reward Amount`, Platform_Fee, `Payment Method`, Status, `Paid Date`) 
               VALUES (:payer, :tid, :reward, :fee, :method, 'Held in Escrow', NOW())";
    $payStmt = $pdo->prepare($paySql);
    $payStmt->execute([
        ':payer' => $_SESSION['user_id'],
        ':tid' => $task_id,
        ':reward' => $reward,
        ':fee' => $platform_fee,
        ':method' => $method
    ]);

    // 3. Notify the Runner that funds are secured and they can start
    $notifySql = "INSERT INTO notifications (UserID, Message, Type, `Is Read`, `Created Date`) 
                  VALUES (:runner, 'Payment secured in escrow! You can now start the task.', 'Payment', 0, NOW())";
    $pdo->prepare($notifySql)->execute([':runner' => $task['RunnerID']]);

    $pdo->commit();
    echo json_encode(["status" => "success", "message" => "Payment secured in escrow."]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => "Transaction failed: " . $e->getMessage()]);
}
?>