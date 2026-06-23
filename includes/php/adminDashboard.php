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
        <h2>142</h2>
        <p>Registered Users</p>
        <span class="stat-change positive">↑ +12 this week</span>
    </div>

    <div class="stat-card">
        <div class="stat-icon">📋</div>
        <h2>67</h2>
        <p>Active Tasks</p>
        <span class="stat-change positive">↑ +4 today</span>
    </div>

    <div class="stat-card stat-danger urgent-card">
        <div class="stat-icon">⚠</div>
        <h2>3</h2>
        <p>Open Reports</p>
        <span class="stat-change negative">2 unresolved reports</span>
    </div>

    <div class="stat-card">
        <div class="stat-icon">✓</div>
        <h2>88%</h2>
        <p>Completion Rate</p>
        <span class="stat-change positive">↑ +5%</span>
    </div>

</div>
    

    <!-- LEFT PANEL -->
    <div class="admin-chart-panel">
        <div class="panel-header">
            <h3 class="panel-title">Platform Activity (Tasks Completed - Last 6 Months)</h3>

        
        </div>

        <div class="mock-chart-container">
            <div class="y-axis">
                <span>50</span>
                <span>40</span>
                <span>30</span>
                <span>20</span>
                <span>10</span>
                <span>0</span>
            </div>

            <div class="chart-area">

                <div class="grid-line" style="bottom:100%;"></div>
                <div class="grid-line" style="bottom:80%;"></div>
                <div class="grid-line" style="bottom:60%;"></div>
                <div class="grid-line" style="bottom:40%;"></div>
                <div class="grid-line" style="bottom:20%;"></div>
                <div class="grid-line" style="bottom:0;"></div>

                <div class="bar-group">
                    <div class="bar-fill bar-cyan" style="height:40%;"></div>
                    <span class="x-label">Jan</span>
                </div>

                <div class="bar-group">
                    <div class="bar-fill bar-purple" style="height:65%;"></div>
                    <span class="x-label">Feb</span>
                </div>

                <div class="bar-group">
                    <div class="bar-fill bar-pink" style="height:45%;"></div>
                    <span class="x-label">Mar</span>
                </div>

                <div class="bar-group">
                    <div class="bar-fill bar-yellow" style="height:80%;"></div>
                    <span class="x-label">Apr</span>
                </div>

                <div class="bar-group">
                    <div class="bar-fill bar-green" style="height:95%;"></div>
                    <span class="x-label">May</span>
                </div>

                <div class="bar-group">
                    <div class="bar-fill bar-orange" style="height:55%;"></div>
                    <span class="x-label">Jun</span>
                </div>

               

            </div>
        </div>
    </div>


    <!-- RIGHT PANEL -->
  <div class="dashboard-analytics-row">

    <!-- LEFT SIDE CATEGORY BARS -->
    <div class="category-panel">

        <div class="panel-header">
            <h3 class="panel-title">Top 5 Categories This Week</h3>
        </div>

        <div class="category-bars">

            <div class="category-row">
                <span>Food</span>
                <div class="category-bar-bg">
                    <div class="category-bar food" style="width:100%"></div>
                </div>
                <span>32</span>
            </div>

            <div class="category-row">
                <span>Assignment</span>
                <div class="category-bar-bg">
                    <div class="category-bar assignment" style="width:84%"></div>
                </div>
                <span>27</span>
            </div>

            <div class="category-row">
                <span>Grocery</span>
                <div class="category-bar-bg">
                    <div class="category-bar grocery" style="width:62%"></div>
                </div>
                <span>20</span>
            </div>

            <div class="category-row">
                <span>Printing</span>
                <div class="category-bar-bg">
                    <div class="category-bar printing" style="width:46%"></div>
                </div>
                <span>15</span>
            </div>

            <div class="category-row">
                <span>Tech</span>
                <div class="category-bar-bg">
                    <div class="category-bar tech" style="width:34%"></div>
                </div>
                <span>11</span>
            </div>
            <div class="mini-chart-section">

    <h4 class="mini-chart-title">Activity Peak Today</h4>

    <canvas id="hourlyActivityChart"></canvas>

</div>

        </div>
    </div>


    <!-- RIGHT SIDE PIE CHART -->
    <div class="status-chart-panel">

        <div class="panel-header">
            <h3 class="panel-title">Today's Task Distribution
        </div>

        <canvas id="taskStatusChart"></canvas>
        <div class="custom-legend">

    <div class="legend-item">
        <span class="legend-circle open-dot"></span>
        Open <strong>14</strong>
    </div>

    <div class="legend-item">
        <span class="legend-circle progress-dot"></span>
        In Progress <strong>9</strong>
    </div>

    <div class="legend-item">
        <span class="legend-circle cancel-dot"></span>
        Cancelled <strong>3</strong>
    </div>

</div>

    </div>

</div>

</div>
        <div class="admin-panel recent-activity">

    <div class="panel-header">
        <h3 class="panel-title">Recent Activity</h3>
    </div>

    <div class="list-container">

        <div class="list-item">
            <div class="item-info">
                <h4>New user registered</h4>
                <span class="subtext">Ahmad joined 5 minutes ago</span>
            </div>
        </div>

        <div class="list-item">
            <div class="item-info">
                <h4>Task completed</h4>
                <span class="subtext">Task #0045 completed successfully</span>
            </div>
        </div>

        <div class="list-item">
            <div class="item-info">
                <h4>New report submitted</h4>
                <span class="subtext">Task #0102 reported by user</span>
            </div>
        </div>

        <div class="list-item">
            <div class="item-info">
                <h4>Payment received</h4>
                <span class="subtext">RM15 payment confirmed</span>
            </div>
        </div>

    </div>

</div>

    </main>
   <script>
const ctx = document.getElementById('taskStatusChart');

new Chart(ctx, {
    type: 'doughnut',

    data: {
        labels: ['Open', 'In Progress', 'Cancelled'],

        datasets: [{
            data: [14, 9, 3],

            backgroundColor: [
                '#3b82f6',
                '#facc15',
                '#ef4444'
            ],

            borderWidth: 0,

            hoverOffset: 10
        }]
    },

    options: {
        cutout: '68%',

        plugins: {
            legend: {
                display: false
            }
        }
    },

    plugins: [{
        id: 'centerText',

        beforeDraw(chart) {
            const { width } = chart;
            const { height } = chart;
            const ctx = chart.ctx;

            ctx.restore();

            ctx.font = "bold 28px Arial";
            ctx.fillStyle = "#ffffff";
            ctx.textAlign = "center";
            ctx.textBaseline = "middle";

            ctx.fillText("26", width / 2, height / 2 - 10);

            ctx.font = "14px Arial";
            ctx.fillStyle = "#948b9c";

            ctx.fillText("Tasks", width / 2, height / 2 + 18);

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
            
            /* lower activity numbers */

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

        plugins: {
            legend: {
                display: false
            }
        },

        scales: {

            x: {
                ticks: {
                    color: '#948b9c'
                },

                grid: {
                    display: false
                }
            },

            y: {

                /* THIS FORCES 5,10,15,20,25 */

                min: 0,
                max: 30,

                ticks: {
                    stepSize: 5,
                    color: '#948b9c'
                },

                grid: {
                    color: '#1f1524'
                }
            }
        }
    }
});

</script>

</body>
</html>