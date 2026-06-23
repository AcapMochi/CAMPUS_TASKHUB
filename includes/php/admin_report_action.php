<?php
session_start();
require 'dhb.inc.php';

header('Content-Type: application/json');

// Security Check
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized action.']);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$report_id = $data['report_id'] ?? null;
$new_status = $data['status'] ?? null;

if (!$report_id || !$new_status) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required data.']);
    exit();
}

try {
    // Update the report status
    $stmt = $pdo->prepare("UPDATE reports SET Status = :status WHERE ReportID = :rid");
    $stmt->execute([':status' => $new_status, ':rid' => $report_id]);
    
    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>