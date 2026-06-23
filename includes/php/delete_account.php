<?php
session_start();
require 'dhb.inc.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];

    try {
        // Optional safety: Unlink/cancel tasks associated with this user before deleting
        // $pdo->exec("UPDATE tasks SET Status = 'Cancelled' WHERE PosterID = $user_id");

        // Delete the user
        $stmt = $pdo->prepare("DELETE FROM users WHERE UserID = :uid");
        $stmt->execute([':uid' => $user_id]);

        // Destroy the login session
        session_unset();
        session_destroy();

        echo json_encode(['status' => 'success']);

    } catch (PDOException $e) {
        // If this fails, it's usually because the user is tied to tasks/reviews 
        // in the database due to "Foreign Key Constraints".
        echo json_encode(['status' => 'error', 'message' => 'Could not delete account. Ensure all your active tasks are cancelled first.']);
    }
}
?>