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
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';

    if (empty($old_password) || empty($new_password)) {
        echo json_encode(['status' => 'error', 'message' => 'Both fields are required.']);
        exit();
    }

    try {
        // 1. Get the current hashed password from DB
        $stmt = $pdo->prepare("SELECT Password_Hash FROM users WHERE UserID = :uid");
        $stmt->execute([':uid' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Verify old password
        if (!$user || !password_verify($old_password, $user['Password_Hash'])) {
            echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect.']);
            exit();
        }

        // 3. Hash and update new password
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $updateStmt = $pdo->prepare("UPDATE users SET Password_Hash = :hash WHERE UserID = :uid");
        $updateStmt->execute([
            ':hash' => $new_hash,
            ':uid' => $user_id
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Password updated.']);

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>