<?php
session_start();
require 'dhb.inc.php';

header('Content-Type: application/json');

try {
    // 1. BASE QUERY
    $sql = "SELECT t.*, 
                   t.Attachment_URL, /* Explicitly requesting the attachment */
                   u.UserID AS PosterID, 
                   u.Username AS PosterName, 
                   u.Rating_Average AS Rating, 
                   u.Profile_Pic_URL AS PosterPic,
                   c.Name AS CategoryName 
            FROM tasks t 
            JOIN users u ON t.PosterID = u.UserID 
            LEFT JOIN categories c ON t.CategoryID = c.CategoryID 
            WHERE t.Status = 'Open'";
    $params = [];

    // 2. DYNAMIC FILTERS

    // --- NEW: Handle Search ---
    if (!empty($_GET['search'])) {
        // This checks if the search word is in the Title OR the Description
        $sql .= " AND (t.Title LIKE ? OR t.Description LIKE ?)";
        $searchQuery = '%' . $_GET['search'] . '%';
        $params[] = $searchQuery;
        $params[] = $searchQuery;
    }

    if (!empty($_GET['locations']) && $_GET['locations'] !== 'All') {
        $locations = explode(',', $_GET['locations']);
        $placeholders = implode(',', array_fill(0, count($locations), '?'));
        $sql .= " AND t.Location IN ($placeholders)";
        foreach ($locations as $loc) {
            $params[] = trim($loc);
        }
    }

    if (!empty($_GET['categories']) && $_GET['categories'] !== 'All') {
        $categories = explode(',', $_GET['categories']);
        $placeholders = implode(',', array_fill(0, count($categories), '?'));
        $sql .= " AND c.Name IN ($placeholders)"; // Matching against c.Name for text strings
        foreach ($categories as $cat) {
            $params[] = trim($cat);
        }
    }

    if (isset($_GET['min']) && is_numeric($_GET['min'])) {
        $sql .= " AND t.Reward_Amount >= ?";
        $params[] = $_GET['min'];
    }

    if (isset($_GET['max']) && is_numeric($_GET['max']) && $_GET['max'] > 0) {
        $sql .= " AND t.Reward_Amount <= ?";
        $params[] = $_GET['max'];
    }

    // 3. FINALIZE QUERY WITH SORTING
    // --- NEW: Handle Sorting ---
    $sort = $_GET['sort'] ?? 'newest';

    switch ($sort) {
        case 'reward-high':
            $sql .= " ORDER BY t.Reward_Amount DESC";
            break;
        case 'reward-low':
            $sql .= " ORDER BY t.Reward_Amount ASC";
            break;
        case 'newest':
        default:
            $sql .= " ORDER BY t.Created_Date DESC";
            break;
    }

    // 4. EXECUTE
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'tasks' => $tasks]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>