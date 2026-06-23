<?php
session_start();
// If they are not logged in OR they are not an admin, kick them out!
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: adminLogin.php?error=unauthorized");
    exit();
}

require 'includes/php/dhb.inc.php'; // Connect to DB

try {
    // 1. User Stats (Total + Recent)
    $stmt = $pdo->query("SELECT COUNT(*) as total, SUM(CASE WHEN Created_Date >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as recent FROM users");
    $userStats = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalUsers = $userStats['total'] ?: 0;
    $recentUsers = $userStats['recent'] ?: 0;

    // 2. Active Task Stats (Total Open/Progress + Created Today)
    $stmt = $pdo->query("SELECT COUNT(*) as total, SUM(CASE WHEN DATE(Created_Date) = CURDATE() THEN 1 ELSE 0 END) as today 
                         FROM tasks WHERE Status IN ('Open', 'Pending', 'In Progress')");
    $activeTaskStats = $stmt->fetch(PDO::FETCH_ASSOC);
    $activeTasks = $activeTaskStats['total'] ?: 0;
    $tasksToday = $activeTaskStats['today'] ?: 0;

    // 3. Open Reports
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM reports WHERE Status = 'Pending'");
    $openReports = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?: 0;

    // 4. Completion Rate
    $stmt = $pdo->query("SELECT COUNT(*) as total, SUM(CASE WHEN Status IN ('Completed', 'Done') THEN 1 ELSE 0 END) as completed FROM tasks");
    $compStats = $stmt->fetch(PDO::FETCH_ASSOC);
    $compRate = $compStats['total'] > 0 ? round(($compStats['completed'] / $compStats['total']) * 100) : 0;

    // 5. Chart Data: Task Status Distribution
    $stmt = $pdo->query("SELECT Status, COUNT(*) as count FROM tasks GROUP BY Status");
    $statusCounts = ['Open' => 0, 'In Progress' => 0, 'Cancelled' => 0];
    $totalChartTasks = 0;
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $status = strtolower($row['Status']);
        if (strpos($status, 'open') !== false || strpos($status, 'pending') !== false) $statusCounts['Open'] += $row['count'];
        if (strpos($status, 'progress') !== false) $statusCounts['In Progress'] += $row['count'];
        if (strpos($status, 'cancel') !== false) $statusCounts['Cancelled'] += $row['count'];
        $totalChartTasks += $row['count'];
    }

    // 6. Top 5 Categories
    $stmt = $pdo->query("SELECT c.Name, COUNT(t.TaskID) as count 
                         FROM tasks t 
                         JOIN categories c ON t.CategoryID = c.CategoryID 
                         GROUP BY c.CategoryID 
                         ORDER BY count DESC LIMIT 5");
    $topCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Find the max count to calculate percentage widths for the CSS bars
    $maxCat = count($topCategories) > 0 ? $topCategories[0]['count'] : 1; 

    // 7. Recent Activity (Fetch 4 most recent tasks)
    $stmt = $pdo->query("SELECT Title, Status, Created_Date FROM tasks ORDER BY Created_Date DESC LIMIT 4");
    $recentActivity = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | Campus TaskHub</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        
        <div class="admin-header">
            <h1 class="page-title">Admin Panel</h1>
        </div>

        <div class="admin-quick-actions">
            <a href="adminManageUsers.php" class="action-card">
                <div class="action-icon">👥</div>
                <div class="action-text">
                    <h3>Manage Users</h3>
                    <p>View, suspend, or remove accounts</p>
                </div>
                <div class="action-arrow">→</div>
            </a>

            <a href="adminManageReports.php" class="action-card">
                <div class="action-icon">🚩</div>
                <div class="action-text">
                    <h3>Manage Reports</h3>
                    <p>Review and resolve platform issues</p>
                </div>
                <div class="action-arrow">→</div>
            </a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <h2><?= $totalUsers ?></h2>
                <p>Registered Users</p>
                <span class="stat-change positive">↑ +<?= $recentUsers ?> this week</span>
            </div>

            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <h2><?= $activeTasks ?></h2>
                <p>Active Tasks</p>
                <span class="stat-change positive">↑ +<?= $tasksToday ?> today</span>
            </div>

            <div class="stat-card stat-danger urgent-card">
                <div class="stat-icon">⚠</div>
                <h2><?= $openReports ?></h2>
                <p>Open Reports</p>
                <span class="stat-change negative"><?= $openReports ?> unresolved reports</span>
            </div>

            <div class="stat-card">
                <div class="stat-icon">✓</div>
                <h2><?= $compRate ?>%</h2>
                <p>Completion Rate</p>
                <span class="stat-change positive">All time avg</span>
            </div>
        </div>
    
        <div class="admin-chart-panel">
            <div class="panel-header">
                <h3 class="panel-title">Platform Activity (Tasks Completed - Last 6 Months)</h3>
            </div>
            <div class="mock-chart-container">
                <div class="y-axis">
                    <span>50</span><span>40</span><span>30</span><span>20</span><span>10</span><span>0</span>
                </div>
                <div class="chart-area">
                    <div class="grid-line" style="bottom:100%;"></div>
                    <div class="grid-line" style="bottom:80%;"></div>
                    <div class="grid-line" style="bottom:60%;"></div>
                    <div class="grid-line" style="bottom:40%;"></div>
                    <div class="grid-line" style="bottom:20%;"></div>
                    <div class="grid-line" style="bottom:0;"></div>

                    <div class="bar-group"><div class="bar-fill bar-cyan" style="height:40%;"></div><span class="x-label">Jan</span></div>
                    <div class="bar-group"><div class="bar-fill bar-purple" style="height:65%;"></div><span class="x-label">Feb</span></div>
                    <div class="bar-group"><div class="bar-fill bar-pink" style="height:45%;"></div><span class="x-label">Mar</span></div>
                    <div class="bar-group"><div class="bar-fill bar-yellow" style="height:80%;"></div><span class="x-label">Apr</span></div>
                    <div class="bar-group"><div class="bar-fill bar-green" style="height:95%;"></div><span class="x-label">May</span></div>
                    <div class="bar-group"><div class="bar-fill bar-orange" style="height:55%;"></div><span class="x-label">Jun</span></div>
                </div>
            </div>
        </div>

        <div class="dashboard-analytics-row">
            <div class="category-panel">
                <div class="panel-header">
                    <h3 class="panel-title">Top Categories</h3>
                </div>
                <div class="category-bars">
                    <?php 
                    $colors = ['food', 'assignment', 'grocery', 'printing', 'tech']; // CSS classes
                    foreach ($topCategories as $index => $cat): 
                        $width = ($cat['count'] / $maxCat) * 100;
                        $colorClass = $colors[$index % count($colors)];
                    ?>
                        <div class="category-row">
                            <span><?= htmlspecialchars($cat['Name']) ?></span>
                            <div class="category-bar-bg">
                                <div class="category-bar <?= $colorClass ?>" style="width:<?= $width ?>%"></div>
                            </div>
                            <span><?= $cat['count'] ?></span>
                        </div>
                    <?php endforeach; ?>

                    <div class="mini-chart-section">
                        <h4 class="mini-chart-title">Activity Peak Today</h4>
                        <canvas id="hourlyActivityChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="status-chart-panel">
                <div class="panel-header">
                    <h3 class="panel-title">Current Task Distribution</h3>
                </div>
                <canvas id="taskStatusChart"></canvas>
                <div class="custom-legend">
                    <div class="legend-item">
                        <span class="legend-circle open-dot"></span> Open <strong><?= $statusCounts['Open'] ?></strong>
                    </div>
                    <div class="legend-item">
                        <span class="legend-circle progress-dot"></span> In Progress <strong><?= $statusCounts['In Progress'] ?></strong>
                    </div>
                    <div class="legend-item">
                        <span class="legend-circle cancel-dot"></span> Cancelled <strong><?= $statusCounts['Cancelled'] ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-panel recent-activity">
            <div class="panel-header">
                <h3 class="panel-title">Recent Activity</h3>
            </div>
            <div class="list-container">
                <?php foreach($recentActivity as $activity): 
                    $timeAgo = date('d M, h:i A', strtotime($activity['Created_Date']));
                ?>
                    <div class="list-item">
                        <div class="item-info">
                            <h4>New Task Posted</h4>
                            <span class="subtext">"<?= htmlspecialchars($activity['Title']) ?>" (<?= $activity['Status'] ?>) - <?= $timeAgo ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </main>

<script>
// Parse PHP arrays to JavaScript
const chartData = <?= json_encode([$statusCounts['Open'], $statusCounts['In Progress'], $statusCounts['Cancelled']]) ?>;
const totalTasks = <?= $totalChartTasks ?>;

const ctx = document.getElementById('taskStatusChart');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Open', 'In Progress', 'Cancelled'],
        datasets: [{
            data: chartData,
            backgroundColor: ['#3b82f6', '#facc15', '#ef4444'],
            borderWidth: 0,
            hoverOffset: 10
        }]
    },
    options: {
        cutout: '68%',
        plugins: {
            legend: { display: false }
        }
    },
    plugins: [{
        id: 'centerText',
        beforeDraw(chart) {
            const { width, height } = chart;
            const ctx = chart.ctx;
            ctx.restore();
            ctx.font = "bold 28px Arial";
            ctx.fillStyle = "#ffffff";
            ctx.textAlign = "center";
            ctx.textBaseline = "middle";
            ctx.fillText(totalTasks.toString(), width / 2, height / 2 - 10);
            ctx.font = "14px Arial";
            ctx.fillStyle = "#948b9c";
            ctx.fillText("Active", width / 2, height / 2 + 18);
            ctx.save();
        }
    }]
});
</script>

<script>
const hourCtx = document.getElementById('hourlyActivityChart');
new Chart(hourCtx, {
    type: 'line',
    data: {
        labels: ['8AM', '10AM', '12PM', '2PM', '4PM', '6PM', '8PM', '10PM'],
        datasets: [{
            data: [5, 8, 15, 12, 18, 26, 14, 7],
            tension: 0.45,
            borderColor: '#00ffa6',
            borderWidth: 3,
            fill: false,
            pointBackgroundColor: '#00ffa6',
            pointRadius: 4
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            x: {
                ticks: { color: '#948b9c' },
                grid: { display: false }
            },
            y: {
                min: 0,
                max: 30,
                ticks: { stepSize: 5, color: '#948b9c' },
                grid: { color: '#1f1524' }
            }
        }
    }
});
</script>

</body>
</html>