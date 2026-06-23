<?php
session_start();
// If they are not logged in OR they are not an admin, kick them out!
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: adminLogin.php?error=unauthorized");
    exit();
}

require 'includes/php/dhb.inc.php';

try {
    // 1. Get Top Stats
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $activeUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE Account_Status = 'Active' OR Account_Status IS NULL")->fetchColumn();
    $suspendedUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE Account_Status = 'Suspended'")->fetchColumn();

    // 2. Fetch All Users
    // This query grabs user info and counts how many tasks they have completed as a runner
    $sql = "SELECT u.UserID, u.Username, u.FullName, u.Faculty, u.Profile_Pic_URL, 
                   COALESCE(u.Account_Status, 'Active') as Account_Status,
                   (SELECT COUNT(*) FROM tasks WHERE RunnerID = u.UserID AND Status IN ('Completed', 'Done')) as TasksDone
            FROM users u 
            ORDER BY u.UserID DESC";
            
    $stmt = $pdo->query($sql);
    $usersList = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Campus TaskHub Admin</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/admin.css">
</head>

<body class="admin-body">

    <header class="navbar admin-nav">
        <div class="logo">
            <span class="campus">CAMPUS </span><span class="taskhub">ADMIN</span>
        </div>
        <div class="header-icons">
            <button class="btn-outline-small" style="border-color: #ff4d4d; color: #ff4d4d;" onclick="window.location.href='includes/php/admin_logout.php'">Logout Admin</button>
        </div>
    </header>

    <main class="main-container">
        
        <a href="adminDashboard.php" class="admin-back-link">← Back to Dashboard</a>

        <div class="admin-header">
            <h1 class="page-title">Manage Users</h1>
        </div>

        <div class="stats-grid grid-3">
            <div class="stat-card">
                <h2><?= $totalUsers ?></h2>
                <p>Total Users</p>
            </div>
            <div class="stat-card stat-success">
                <h2><?= $activeUsers ?></h2>
                <p>Active</p>
            </div>
            <div class="stat-card stat-danger">
                <h2><?= $suspendedUsers ?></h2>
                <p>Suspended</p>
            </div>
        </div>

        <div class="admin-panel full-width-panel">
            <div class="panel-header">
                <h3 class="panel-title">All Users</h3>
            </div>

            <div class="list-container">
                <?php if (count($usersList) > 0): ?>
                    <?php foreach($usersList as $user): 
                        $status = $user['Account_Status'];
                        $badgeClass = ($status === 'Suspended') ? 'badge-red' : 'badge-green';
                        $displayName = !empty($user['FullName']) ? $user['FullName'] : $user['Username'];
                        $faculty = !empty($user['Faculty']) ? $user['Faculty'] : 'No Faculty listed';
                        $pfp = !empty($user['Profile_Pic_URL']) ? $user['Profile_Pic_URL'] : 'images/PFP.jpg';
                    ?>
                        <div class="list-item">
                            <div class="user-info">
                                <img src="<?= htmlspecialchars($pfp) ?>" class="admin-avatar" alt="Avatar" onerror="this.src='images/PFP.jpg'">
                                <div>
                                    <h4><?= htmlspecialchars($displayName) ?></h4>
                                    <span class="subtext"><?= htmlspecialchars($faculty) ?> - <?= $user['TasksDone'] ?> tasks done</span>
                                </div>
                            </div>
                            <div class="item-actions">
                                <span class="badge <?= $badgeClass ?>"><?= $status ?></span>
                                
                                <?php if($status === 'Suspended'): ?>
                                    <button class="btn-outline-small btn-success" onclick="updateUserStatus(<?= $user['UserID'] ?>, 'Active')">Activate</button>
                                <?php else: ?>
                                    <button class="btn-outline-small btn-danger" onclick="updateUserStatus(<?= $user['UserID'] ?>, 'Suspended')">Suspend</button>
                                <?php endif; ?>
                                
                                <button class="btn-outline-small btn-danger" onclick="deleteUser(<?= $user['UserID'] ?>)">Remove</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #948b9c; padding: 20px;">No users found in the system.</p>
                <?php endif; ?>
            </div>
        </div>

    </main>

    <script>
        // Handle Suspend / Activate
        function updateUserStatus(userId, newStatus) {
            if (!confirm(`Are you sure you want to ${newStatus === 'Suspended' ? 'suspend' : 'activate'} this user?`)) {
                return;
            }

            fetch('includes/php/admin_user_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'status', user_id: userId, status: newStatus })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload(); // Refresh the page to see changes
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(err => alert("Network error occurred."));
        }

        // Handle Permanent Deletion
        function deleteUser(userId) {
            if (!confirm("WARNING: Are you absolutely sure you want to PERMANENTLY delete this user and all their tasks? This cannot be undone.")) {
                return;
            }

            fetch('includes/php/admin_user_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete', user_id: userId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(err => alert("Network error occurred."));
        }
    </script>
</body>
</html>