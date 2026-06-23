<?php
session_start();
// If they are not logged in OR they are not an admin, kick them out!
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: adminLogin.php?error=unauthorized");
    exit();
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

        <!-- REPORT CHART -->
<div class="admin-chart-wrapper">

    <!-- TOTAL REPORTS CARD -->
    <div class="total-report-card">
        <span>Total Reports</span>
        <h1>3</h1>
    </div>

    <!-- CHART -->
    <div class="admin-chart-card">
        <h3>Reports Overview</h3>
        <canvas id="reportChart"></canvas>
    </div>

</div>

        <!-- REPORTS PANEL -->
        <div class="admin-panel full-width-panel">
            <div class="panel-header">
                <h3 class="panel-title">Active Reports</h3>
                <div class="panel-filters">
                    <button class="filter-pill active">All</button>
                    <button class="filter-pill">Open</button>
                    <button class="filter-pill">Reviewing</button>
                    <button class="filter-pill">Resolved</button>
                    <button class="filter-pill">Dismissed</button>
                </div>
            </div>

            <div class="list-container">
                <!-- Report Item 1 -->
                <div class="list-item">
                    <div class="item-info">
                        <h4>Saya ditipu dan tidak menerima duit</h4>
                        <span class="subtext">Reported by 2 users - Task #0067</span>
                    </div>
                    <div class="item-actions">
                        <span class="badge badge-red">Open</span>
                        <button class="btn-outline-small btn-danger">Suspend</button>
                    </div>
                </div>

                <!-- Report Item 2 -->
                <div class="list-item">
                    <div class="item-info">
                        <h4>Runner tidak memberikan respon</h4>
                        <span class="subtext">Poster complaint - Task #0144</span>
                    </div>
                    <div class="item-actions">
                        <span class="badge badge-red">Open</span>
                        <button class="btn-outline-small btn-danger">Suspend</button>
                    </div>
                </div>

                <!-- Report Item 3 -->
                <div class="list-item">
                    <div class="item-info">
                        <h4>Task yang janggal dan tidak masuk akal</h4>
                        <span class="subtext">Task complaint - Task #0102</span>
                    </div>
                    <div class="item-actions">
                        <span class="badge badge-green">Resolved</span>
                        <button class="btn-outline-small">Reopen</button>
                    </div>
                </div>
            </div>
        </div>

    </main>
    <script>
const ctx = document.getElementById('reportChart');

new Chart(ctx, {
    type: 'bar',

    data: {
        labels: ['Open', 'Reviewing', 'Resolved'],

        datasets: [{
            label: 'Number of Reports',

            data: [2, 2, 1],

            borderWidth: 1,

            backgroundColor: [
                '#00ffa6',
                '#ff4d4d',
                '#ffc107',
                '#00ff88'
            ]
        }]
    },

    options: {
        responsive: true,

        plugins: {
            legend: {
                display: false
            }
        },

        scales: {
            y: {
                beginAtZero: true,

                ticks: {
                    color: '#948b9c',

                    stepSize:1,

                    precision:0
                },

                grid: {
                    color: '#1f1524'
                }
            },

            x: {
                ticks: {
                    color: '#948b9c'
                },

                grid: {
                    display: false
                }
            }
        }
    }
});
</script>
</body>
</html>