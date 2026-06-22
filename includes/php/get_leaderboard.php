<?php
// includes/get_leaderboard.php
session_start();
require 'dhb.inc.php';
header('Content-Type: application/json');

$type = $_GET['type'] ?? 'runners';

try {
    if ($type === 'runners') {
        // Top Runners: Count tasks where they are the Runner AND the status is Completed
        $sql = "SELECT u.Username as name, u.`Profile Pic URL` as img, u.`Rating Average` as rating, COUNT(t.TaskID) as count 
                FROM users u 
                JOIN tasks t ON u.UserID = t.RunnerID 
                WHERE t.Status = 'Completed' 
                GROUP BY u.UserID 
                ORDER BY count DESC, rating DESC 
                LIMIT 100";
    } else {
        // Top Posters: Count tasks where they are the Poster
        $sql = "SELECT u.Username as name, u.`Profile Pic URL` as img, u.`Rating Average` as rating, COUNT(t.TaskID) as count 
                FROM users u 
                JOIN tasks t ON u.UserID = t.PosterID 
                GROUP BY u.UserID 
                ORDER BY count DESC, rating DESC 
                LIMIT 100";
    }

    $stmt = $pdo->query($sql);
    $usersList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If a user has no profile pic, assign the default one so the UI doesn't break
    foreach ($usersList as &$user) {
        if (empty($user['img'])) {
            $user['img'] = 'images/PFP.jpg';
        }
    }

    echo json_encode($usersList);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>