<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: adminLogin.php?error=unauthorized");
    exit();
}

require 'includes/php/dhb.inc.php';

try {
    // 1. Get Total Reports Count
    $totalReports = $pdo->query("SELECT COUNT(*) FROM reports")->fetchColumn();

    // 2. Get Chart Data (Group by Status)
    $stmt = $pdo->query("SELECT Status, COUNT(*) as count FROM reports GROUP BY Status");
    $statusCounts = ['Pending' => 0, 'Reviewed' => 0, 'Resolved' => 0, 'Dismissed' => 0];
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (array_key_exists($row['Status'], $statusCounts)) {
            $statusCounts[$row['Status']] = $row['count'];
        }
    }

    // 3. Fetch All Reports
    $sql = "SELECT r.*, u.Username as ReporterName 
            FROM reports r 
            LEFT JOIN users u ON r.ReporterID = u.UserID 
            ORDER BY r.Created_Date DESC";
    $stmt = $pdo->query($sql);
    $reportsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reports | Campus TaskHub Admin</title>
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
        
        <a href="adminDashboard.php" class="admin-back-link">← Back to Dashboard</a>

        <div class="admin-header">
            <h1 class="page-title">Manage Reports</h1>
        </div>

        <div class="admin-chart-wrapper">
            <div class="total-report-card">
                <span>Total Reports</span>
                <h1><?= $totalReports ?></h1>
            </div>
            <div class="admin-chart-card">
                <h3>Reports Overview</h3>
                <canvas id="reportChart"></canvas>
            </div>
        </div>

        <div class="admin-panel full-width-panel">
            <div class="panel-header">
                <h3 class="panel-title">Active Reports</h3>
                <div class="panel-filters" id="reportFilters">
                    <button class="filter-pill active" data-filter="All">All</button>
                    <button class="filter-pill" data-filter="Pending">Pending</button>
                    <button class="filter-pill" data-filter="Reviewed">Reviewed</button>
                    <button class="filter-pill" data-filter="Resolved">Resolved</button>
                    <button class="filter-pill" data-filter="Dismissed">Dismissed</button>
                </div>
            </div>

            <div class="list-container" id="reportListContainer">
                <?php if (count($reportsList) > 0): ?>
                    <?php foreach($reportsList as $report): 
                        // Determine badge color dynamically
                        $status = $report['Status'] ?: 'Pending'; // Default to Pending if empty
                        $badgeClass = 'badge-red'; // Pending
                        if ($status === 'Resolved' || $status === 'Dismissed') $badgeClass = 'badge-green';
                        elseif ($status === 'Reviewed') $badgeClass = 'badge-yellow';
                    ?>
                        <div class="list-item report-row" data-status="<?= $status ?>">
                            <div class="item-info">
                                <h4><?= htmlspecialchars($report['Reason'] ?? 'No reason provided') ?></h4>
                                <span class="subtext">Reported by <?= htmlspecialchars($report['ReporterName'] ?? 'Unknown') ?> - Task #<?= str_pad($report['TaskID'] ?? 0, 4, '0', STR_PAD_LEFT) ?></span>
                            </div>
                            <div class="item-actions">
                                <span class="badge <?= $badgeClass ?>"><?= $status ?></span>
                                
                                <?php if($status === 'Pending'): ?>
                                    <button class="btn-outline-small" onclick="changeReportStatus(<?= $report['ReportID'] ?>, 'Reviewed')">Mark Reviewed</button>
                                <?php endif; ?>
                                
                                <?php if($status === 'Pending' || $status === 'Reviewed'): ?>
                                    <button class="btn-outline-small" style="border-color: var(--accent); color: var(--accent);" onclick="changeReportStatus(<?= $report['ReportID'] ?>, 'Resolved')">Resolve</button>
                                    <button class="btn-outline-small btn-danger" onclick="suspendUser(<?= $report['ReportedUserID'] ?? 'null' ?>)">Suspend User</button>
                                <?php endif; ?>
                                
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #948b9c; padding: 20px;">No reports have been filed yet.</p>
                <?php endif; ?>
            </div>
        </div>

    </main>

    <script>
        // ----------------------------------------------------
        // 1. CHART JS SETUP
        // ----------------------------------------------------
        const ctx = document.getElementById('reportChart');
        const chartData = [
            <?= $statusCounts['Pending'] ?>, 
            <?= $statusCounts['Reviewed'] ?>, 
            <?= $statusCounts['Resolved'] ?>, 
            <?= $statusCounts['Dismissed'] ?>
        ];

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Pending', 'Reviewed', 'Resolved', 'Dismissed'],
                datasets: [{
                    label: 'Number of Reports',
                    data: chartData,
                    borderWidth: 1,
                    backgroundColor: ['#ff4d4d', '#ffc107', '#00ffa6', '#3b82f6']
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { color: '#948b9c', stepSize: 1, precision: 0 }, grid: { color: '#1f1524' } },
                    x: { ticks: { color: '#948b9c' }, grid: { display: false } }
                }
            }
        });

        // ----------------------------------------------------
        // 2. FILTER FUNCTIONALITY
        // ----------------------------------------------------
        document.querySelectorAll('.filter-pill').forEach(pill => {
            pill.addEventListener('click', function() {
                // Remove active class from all pills, add to clicked
                document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
                this.classList.add('active');

                const filterValue = this.getAttribute('data-filter');
                const rows = document.querySelectorAll('.report-row');

                rows.forEach(row => {
                    // Compare row's data-status to the filter
                    if (filterValue === 'All' || row.getAttribute('data-status') === filterValue) {
                        row.style.display = 'flex'; // Standard layout for your list items
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });

        // ----------------------------------------------------
        // 3. ACTION BUTTONS (Update Report Status)
        // ----------------------------------------------------
        function changeReportStatus(reportId, newStatus) {
            if (!confirm(`Are you sure you want to mark this report as ${newStatus}?`)) return;

            fetch('includes/php/admin_report_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ report_id: reportId, status: newStatus })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload(); // Reload to show updated badge colors
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(err => alert("Network error. Please try again."));
        }

        // ----------------------------------------------------
        // 4. ACTION BUTTON (Suspend User)
        // ----------------------------------------------------
        function suspendUser(userId) {
            if (!userId) {
                alert("Error: Could not identify the user.");
                return;
            }
            if (!confirm("Are you sure you want to suspend this user? They will not be able to log in.")) return;

            fetch('includes/php/admin_user_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'status', user_id: userId, status: 'Suspended' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert("User suspended successfully.");
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(err => alert("Network error. Please try again."));
        }
    </script>
</body>
</html>