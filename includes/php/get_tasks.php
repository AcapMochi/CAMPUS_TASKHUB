<?php
session_start();
require 'dhb.inc.php';

header('Content-Type: application/json');

try {
    $sql = "SELECT * FROM tasks WHERE 1=1";
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

        // Adds: AND Location IN (:loc0, :loc1, ...)
        $sql .= " AND Location IN (" . implode(',', $locPlaceholders) . ")";
    }

    $sql = "SELECT tasks.*, categories.Name AS CategoryName 
            FROM tasks 
            LEFT JOIN categories ON tasks.CategoryID = categories.CategoryID 
            WHERE 1=1";
    $params = [];

    // ... (Keep your Location filter code here) ...

    // 2. Filter by MULTIPLE Categories using the JOINed table
    if (isset($_GET['categories']) && !empty($_GET['categories'])) {
        $catArray = explode(',', $_GET['categories']);
        $catPlaceholders = [];

        foreach ($catArray as $index => $cat) {
            $paramName = ":cat" . $index;
            $catPlaceholders[] = $paramName;
            $params[$paramName] = $cat;
        }

        // Change 'Category' to 'categories.Name' so it searches the correct table column
        $sql .= " AND categories.Name IN (" . implode(',', $catPlaceholders) . ")";
    }

    // 3. Filter by Min Reward
    if (isset($_GET['min']) && is_numeric($_GET['min'])) {
        $sql .= " AND Reward_Amount >= :min";
        $params[':min'] = $_GET['min'];
    }

    // 4. Filter by Max Reward
    if (isset($_GET['max']) && is_numeric($_GET['max'])) {
        $sql .= " AND Reward_Amount <= :max";
        $params[':max'] = $_GET['max'];
    }

    $sql .= " ORDER BY 'Created_At' DESC";

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