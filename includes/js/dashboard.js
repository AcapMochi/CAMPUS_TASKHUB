document.addEventListener("DOMContentLoaded", function () {
    fetch('includes/php/get_dashboard_data.php')
        .then(res => res.json())
        .then(data => {
            if (data.status !== 'success') {
                console.error("Error loading dashboard data:", data.message);
                return;
            }

            // 1. UPDATE GREETING & STATS
            const hour = new Date().getHours();
            let greeting = "Good evening";
            if (hour < 12) greeting = "Good morning";
            else if (hour < 18) greeting = "Good afternoon";

            const username = data.user?.username || data.username || "User";
            const welcomeMsg = document.getElementById('welcome-msg');
            if (welcomeMsg) welcomeMsg.innerText = `${greeting}, ${username}`;

            // --- THE NEW CARDS (Streak & Active Count) ---
            const streakCount = data.daily_streak || data.stats?.daily_streak || 0;
            const streakEl = document.getElementById('dailyStreak');
            if (streakEl) streakEl.innerText = streakCount;

            const activeCountEl = document.getElementById('dashboard-active-count');
// This pulls the total 'Open' tasks straight from your PHP file
if (activeCountEl) activeCountEl.innerText = data.stats?.open_tasks || 0;

            // --- THE ORIGINAL CARDS ---
            const earnedEl = document.getElementById('dashboard-earned');
            if (earnedEl) earnedEl.innerText = `RM${data.stats?.total_earned || '0.00'}`;

            const ratingEl = document.getElementById('dashboard-rating');
            const userRating = data.user?.rating || data.rating || "New";
            if (ratingEl) ratingEl.innerText = userRating;

            // 2. GENERATE "TASKS YOU MAY LIKE"
            const taskListContainer = document.getElementById('taskList');
            const suggestedTasks = data.suggested_tasks || data.recommended_tasks || [];
            
            if (taskListContainer) {
                if (suggestedTasks.length > 0) {
                    taskListContainer.innerHTML = suggestedTasks.map(task => {
                        const reward = parseFloat(task.Reward_Amount).toFixed(0);
                        const posterPic = task.PosterPic || 'images/PFP.jpg';
                        const rating = parseFloat(task.PosterRating || 5.0).toFixed(1);
                        
                        return `
                            <div class="dash-task-item" style="cursor: pointer;" onclick="window.location.href='taskDetail.html?id=${task.TaskID}'">
                                <div class="task-info">
                                    <h4>${task.Title}</h4>
                                    <div class="task-tags">
                                        <span class="tag tag-open">Open</span>
                                        <span class="tag tag-delivery" style="background: #2f2235; color: #b0a7b8;">${task.CategoryName || 'General'}</span>
                                    </div>
                                </div>
                                <div class="task-user">
                                    <img src="${posterPic}" onerror="this.src='images/PFP.jpg'" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;">
                                    <span>${rating} ⭐</span>
                                </div>
                                <div class="task-meta">
                                    <div class="reward" style="color: var(--accent, #00ffa6);">Reward: <strong>RM${reward}</strong></div>
                                </div>
                            </div>
                        `;
                    }).join('');
                } else {
                    taskListContainer.innerHTML = `<p style="color: #948b9c;">No new tasks available right now.</p>`;
                }
            }

            // 3. GENERATE PROGRESS BARS
            if (data.progress) {
                let totalAssigned = data.progress.total_runner == 0 ? 1 : data.progress.total_runner; 
                let completionPercentage = (data.progress.completed_runner / totalAssigned) * 100;
                
                let postedPercentage = (data.progress.total_posted / 10) * 100;
                if (postedPercentage > 100) postedPercentage = 100;

                const progressSections = document.querySelectorAll('.progress-section');
                if(progressSections.length >= 2) {
                    // Runner Progress (FIXED: Now only shows the single number, e.g., "4")
                    progressSections[0].querySelector('.progress-value').innerText = `${data.progress.completed_runner}`;
                    progressSections[0].querySelector('.progress-fill').style.width = `${completionPercentage}%`;
                    
                    // Poster Progress
                    progressSections[1].querySelector('.progress-value').innerText = `${data.progress.total_posted}`;
                    progressSections[1].querySelector('.progress-fill').style.width = `${postedPercentage}%`;
                }
            }

            // 4. GENERATE RECENT ACTIVITY
            const recentListContainer = document.getElementById('recentList');
            const activities = data.recent_activity || [];
            
            if (recentListContainer) {
                if (activities.length > 0) {
                    recentListContainer.innerHTML = activities.map(activity => {
                        let statusColor = "yellow"; 
                        let displayStatus = activity.Status || "Active";

                        if (displayStatus.toLowerCase() === 'completed' || displayStatus.toLowerCase() === 'done') statusColor = "green";
                        if (displayStatus.toLowerCase() === 'open') {
                            statusColor = "cyan"; 
                            displayStatus = "Posted";
                        }

                        return `
                            <div class="recent-item">
                                <div class="recent-left">
                                    <span class="status-dot dot-${statusColor}"></span>
                                    <span>${activity.Title}</span>
                                </div>
                                <span class="recent-badge badge-${statusColor}">${displayStatus}</span>
                            </div>
                        `;
                    }).join('');
                } else {
                    recentListContainer.innerHTML = `<p style="color: #948b9c;">No recent activity found.</p>`;
                }
            }

            // 5. GENERATE ACTIVE TASK TRACKER (Below Recent Activity)
            const activeTaskContainer = document.getElementById('activeTaskContainer');
            const activeTasksList = data.active_tasks || [];

            if (activeTaskContainer) {
                if (activeTasksList.length > 0) {
                    activeTaskContainer.innerHTML = activeTasksList.map(task => `
                        <div class="active-task">
                            <div class="active-task-title">${task.Title}</div>
                            <div class="active-status">${task.Status || 'In Progress'}</div>
                            <button class="progress-btn" onclick="window.location.href='taskProgressPoster.html?id=${task.TaskID}'">
                                View Progress
                            </button>
                        </div>
                    `).join('');
                } else {
                    activeTaskContainer.innerHTML = `<p class="empty-placeholder">No active tasks right now</p>`;
                }
            }

        })
        .catch(err => console.error("Error fetching dashboard:", err));
});