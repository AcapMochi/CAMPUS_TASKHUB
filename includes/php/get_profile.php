<?php
// includes/get_profile.php
session_start();
require 'dhb.inc.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // 1. Fetch User Data
    $stmt = $pdo->prepare("SELECT Username, FullName, Email, Phone, Faculty, StudentID, `Bio_Text`, `Profile_Pic_URL`, `Is_Verified`, `Rating_Average` FROM users WHERE UserID = :uid");
    $stmt->execute([':uid' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("User not found.");
    }

    $revStmt = $pdo->prepare("SELECT r.Rating, r.Comment, r.`Created_Date`, u.Username as ReviewerName, u.`Profile_Pic_URL` as ReviewerPic 
                              FROM reviews r 
                              JOIN users u ON r.ReviewerID = u.UserID 
                              WHERE r.RevieweeID = :uid 
                              ORDER BY r.`Created_Date` DESC LIMIT 3");
    $revStmt->execute([':uid' => $user_id]);
    $reviews = $revStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "user" => $user, "reviews" => $reviews]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>