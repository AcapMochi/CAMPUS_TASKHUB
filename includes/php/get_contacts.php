<?php
session_start();
require 'dhb.inc.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // This query finds unique users you've messaged, PLUS users you share an active task with.
    $sql = "SELECT UserID, Username, Profile_Pic_URL FROM (
                -- 1. People you have messaged
                SELECT u.UserID, u.Username, u.Profile_Pic_URL
                FROM users u
                JOIN messages m ON (u.UserID = m.SenderID OR u.UserID = m.ReceiverID)
                WHERE (m.SenderID = :me1 OR m.ReceiverID = :me2) AND u.UserID != :me3
                
                UNION
                
                -- 2. People you share an active task with
                SELECT u.UserID, u.Username, u.Profile_Pic_URL
                FROM users u
                JOIN tasks t ON (u.UserID = t.PosterID OR u.UserID = t.RunnerID)
                WHERE (t.PosterID = :me4 OR t.RunnerID = :me5) 
                  AND t.Status = 'In_Progress' 
                  AND u.UserID != :me6
            ) AS contacts
            GROUP BY UserID";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':me1' => $user_id, ':me2' => $user_id, ':me3' => $user_id,
        ':me4' => $user_id, ':me5' => $user_id, ':me6' => $user_id
    ]);
    
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'contacts' => $contacts
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>