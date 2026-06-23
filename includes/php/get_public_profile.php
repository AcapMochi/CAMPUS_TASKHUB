<?php
session_start();
require 'dhb.inc.php';
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(["status" => "error", "message" => "No user specified."]);
    exit();
}

$target_id = $_GET['id'];

try {
    // 1. Fetch Public User Data
    $stmt = $pdo->prepare("SELECT UserID, Username, FullName, Faculty, Bio_Text, Profile_Pic_URL, Is_Verified, Rating_Average 
                           FROM users WHERE UserID = :uid");
    $stmt->execute([':uid' => $target_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["status" => "error", "message" => "User not found."]);
        exit();
    }

    // 2. Fetch their recent reviews
    $revStmt = $pdo->prepare("SELECT r.Rating, r.Comment, u.Username as ReviewerName, u.Profile_Pic_URL as ReviewerPic 
                              FROM reviews r 
                              JOIN users u ON r.ReviewerID = u.UserID 
                              WHERE r.RevieweeID = :uid 
                              ORDER BY r.Created_Date DESC LIMIT 5");
    $revStmt->execute([':uid' => $target_id]);
    $reviews = $revStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "user" => $user, "reviews" => $reviews]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error."]);
}
?>