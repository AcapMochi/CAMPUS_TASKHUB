<?php
session_start();
require 'dhb.inc.php';

header('Content-Type: application/json');

try {
    // BASE QUERY: Join tasks, categories, and users
    $sql = "SELECT 
                tasks.*, 
                categories.Name AS CategoryName,
                users.Username 
            FROM tasks 
            LEFT JOIN categories ON tasks.CategoryID = categories.CategoryID 
            LEFT JOIN users ON tasks.PosterID = users.UserID 
            WHERE 1=1";
            
    $params = [];

    // 1. Filter by MULTIPLE Locations
    if (isset($_GET['locations']) && !empty($_GET['locations'])) {
        $locArray = explode(',', $_GET['locations']); // Convert "FTMK,FAIX" to array
        $locPlaceholders = [];

        foreach ($locArray as $index => $loc) {
            $paramName = ":loc" . $index;
            $locPlaceholders[] = $paramName;
            $params[$paramName] = $loc;
        }

        // Specify tasks.Location to avoid ambiguity if users also has a Location column
        $sql .= " AND tasks.Location IN (" . implode(',', $locPlaceholders) . ")";
    }

    // 2. Filter by MULTIPLE Categories
    if (isset($_GET['categories']) && !empty($_GET['categories'])) {
        $catArray = explode(',', $_GET['categories']);
        $catPlaceholders = [];

        foreach ($catArray as $index => $cat) {
            $paramName = ":cat" . $index;
            $catPlaceholders[] = $paramName;
            $params[$paramName] = $cat;
        }

        $sql .= " AND categories.Name IN (" . implode(',', $catPlaceholders) . ")";
    }

    // 3. Filter by Min Reward
    if (isset($_GET['min']) && is_numeric($_GET['min'])) {
        $sql .= " AND tasks.Reward_Amount >= :min";
        $params[':min'] = $_GET['min'];
    }

    // 4. Filter by Max Reward
    if (isset($_GET['max']) && is_numeric($_GET['max'])) {
        $sql .= " AND tasks.Reward_Amount <= :max";
        $params[':max'] = $_GET['max'];
    }

    // Fixed the quotes around Created_At so it sorts properly
    $sql .= " ORDER BY tasks.Created_Date DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'tasks' => $tasks
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>