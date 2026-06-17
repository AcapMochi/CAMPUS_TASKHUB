<?php
session_start();
require 'dhb.inc.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Not logged in"]);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Fetch all tasks where the user is either the poster or the runner
    $sql = "SELECT * FROM tasks WHERE PosterID = :uid OR RunnerID = :uid ORDER BY `Created Date` DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':uid' => $user_id]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "tasks" => $tasks]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>