<?php
session_start();
require 'dhb.inc.php'; // Ensure this points to your DB connection file

header('Content-Type: application/json');

// 1. Security Check: Only admins can do this
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized action.']);
    exit();
}

// 2. Read the incoming JSON data from the JavaScript fetch request
$data = json_decode(file_get_contents("php://input"), true);
$action = $data['action'] ?? '';
$user_id = $data['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'User ID is missing.']);
    exit();
}

try {
    // ---------------------------------------------------------
    // ACTION: SUSPEND OR ACTIVATE USER
    // ---------------------------------------------------------
    if ($action === 'status') {
        $new_status = ($data['status'] === 'Suspended') ? 'Suspended' : 'Active';
        
        $stmt = $pdo->prepare("UPDATE users SET Account_Status = :status WHERE UserID = :uid");
        $stmt->execute([':status' => $new_status, ':uid' => $user_id]);
        
        echo json_encode(['status' => 'success']);
        exit();
    } 
    
    // ---------------------------------------------------------
    // ACTION: PERMANENTLY DELETE USER
    // ---------------------------------------------------------
    elseif ($action === 'delete') {
        // Start a transaction so if anything fails, it rolls back safely
        $pdo->beginTransaction();

        // Step A: Delete any reports made by or against this user
        $stmtReports = $pdo->prepare("DELETE FROM reports WHERE ReporterID = :uid OR ReportedUserID = :uid");
        $stmtReports->execute([':uid' => $user_id]);

        // Step B: Delete any tasks POSTED by this user
        $stmtPoster = $pdo->prepare("DELETE FROM tasks WHERE PosterID = :uid");
        $stmtPoster->execute([':uid' => $user_id]);

        // Step C: If this user is currently a RUNNER for someone else's task, abandon the task so the poster can find a new runner
        $stmtRunner = $pdo->prepare("UPDATE tasks SET RunnerID = NULL, Status = 'Open' WHERE RunnerID = :uid");
        $stmtRunner->execute([':uid' => $user_id]);

        // Step D: Finally, delete the user account
        $stmtUser = $pdo->prepare("DELETE FROM users WHERE UserID = :uid");
        $stmtUser->execute([':uid' => $user_id]);

        $pdo->commit();
        echo json_encode(['status' => 'success']);
        exit();
    } 
    
    // Unknown action
    else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action requested.']);
        exit();
    }

} catch (PDOException $e) {
    // If we have an active transaction and hit an error, roll it back
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    exit();
}
?>