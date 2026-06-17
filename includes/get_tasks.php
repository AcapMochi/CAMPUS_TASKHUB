<?php
require 'dhb.inc.php';

try {
    // We join the tasks and users tables so we can display the name of the person who posted it!
    $sql = "SELECT t.TaskID, t.Title, t.Description, t.`Reward_Amount`, t.Status, t.`Created_Date`, u.Username 
            FROM tasks t 
            JOIN users u ON t.PosterID = u.UserID 
            WHERE t.Status = 'Open' 
            ORDER BY t.`Created_Date` DESC";
            
    $stmt = $pdo->query($sql);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "tasks" => $tasks]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>