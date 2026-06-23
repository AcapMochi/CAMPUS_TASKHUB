<?php
// includes/check_session.php
session_start();

// Disable standard PHP error output so it doesn't corrupt our JSON response
error_reporting(0); 

require 'dhb.inc.php';
header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    
    $profile_pic = 'images/PFP.jpg'; // Default fallback

    try {
        // Fetch the user's specific profile picture URL[cite: 40]
        $stmt = $pdo->prepare("SELECT `Profile_Pic_URL` FROM users WHERE UserID = :uid");
        $stmt->execute([':uid' => $_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && !empty($user['Profile_Pic_URL'])) {
            $profile_pic = $user['Profile_Pic_URL'];
        }

        echo json_encode([
            "status" => "logged_in", 
            "user_id" => $_SESSION['user_id'],
            "username" => $_SESSION['username'],
            "profile_pic" => $profile_pic
        ]);
        
    } catch (Exception $e) {
        // If the DB fails, STILL tell the frontend they are logged in!
        echo json_encode([
            "status" => "logged_in", 
            "user_id" => $_SESSION['user_id'],
            "username" => $_SESSION['username'],
            "profile_pic" => $profile_pic,
            "debug_error" => $e->getMessage()
        ]);
    }
    
} else {
    echo json_encode(["status" => "logged_out"]);
}
?>