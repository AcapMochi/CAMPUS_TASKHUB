<?php
session_start();
require 'dhb.inc.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Not authorized"]);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $sql = "SELECT DISTINCT 
                u.UserID, 
                u.Username, 
                u.Profile_Pic_URL, 
                t.TaskID, 
                t.Title AS TaskTitle,
                CASE WHEN t.PosterID = :uid THEN 'Runner' ELSE 'Poster' END AS CounterpartRole
            FROM tasks t
            JOIN users u ON (t.PosterID = u.UserID OR t.RunnerID = u.UserID)
            WHERE (t.PosterID = :uid OR t.RunnerID = :uid)
              AND t.RunnerID IS NOT NULL 
              AND t.Status != 'Cancelled'
              AND u.UserID != :uid
            ORDER BY t.Created_Date DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':uid' => $user_id]);
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "contacts" => $contacts]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>