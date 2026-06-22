document.addEventListener("DOMContentLoaded", function () {
    fetchDashboardData();
});

function fetchDashboardData() {
    fetch('includes/php/get_dashboard_data.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                
                // 1. Update Welcome Message based on time of day
                const hour = new Date().getHours();
                let greeting = "Good evening";
                if (hour < 12) greeting = "Good morning";
                else if (hour < 18) greeting = "Good afternoon";
                
                document.getElementById('welcome-msg').innerText = `${greeting}, ${data.username}!`;

                // 2. Update Stats
                document.getElementById('dashboard-open').innerText = data.stats.open_tasks;
                document.getElementById('dashboard-posted').innerText = data.stats.posted_tasks;
                document.getElementById('dashboard-done').innerText = data.stats.completed_tasks;
                document.getElementById('dashboard-earned').innerText = `RM ${data.stats.total_earned}`;

                // 3. Update Progress Bars (Assuming a goal of 10 for the visual bar)
                const goal = 10;
                const completedPercent = Math.min((data.stats.completed_tasks / goal) * 100, 100);
                const postedPercent = Math.min((data.stats.posted_tasks / goal) * 100, 100);
                
                document.getElementById('barCompleted').style.width = `${completedPercent}%`;
                document.getElementById('barPosted').style.width = `${postedPercent}%`;

                // 4. Render "Tasks You May Like"
                renderRecommendedTasks(data.recommended_tasks);
            } else {
                console.error("Error loading dashboard:", data.message);
            }
        })
        .catch(error => console.error("Fetch error:", error));
}

function renderRecommendedTasks(tasks) {
    const list = document.getElementById("taskList");
    list.innerHTML = "";

    if (tasks.length === 0) {
        list.innerHTML = "<p style='color: #888;'>No open tasks available right now.</p>";
        return;
    }

    tasks.forEach(task => {
        // Create a mini-card similar to browse tasks
        const div = document.createElement("div");
        div.style.background = "rgba(0, 0, 0, 0.4)";
        div.style.padding = "15px";
        div.style.borderRadius = "10px";
        div.style.marginBottom = "10px";
        div.style.border = "1px solid rgba(255,255,255,0.1)";

        div.innerHTML = `
            <h4 style="margin: 0 0 5px 0; color: #fff;">${task.Title}</h4>
            <p style="margin: 0 0 10px 0; font-size: 0.85em; color: #aaa;">By ${task.Username} | RM ${task.Reward_Amount}</p>
            <p style="margin: 0 0 10px 0; font-size: 0.9em;">📍 ${task.Location} | 🏷️ ${task.CategoryName}</p>
            <button class="btn-primary" style="padding: 8px 12px; font-size: 0.85em;" onclick="window.location.href='taskDetail.html?id=${task.TaskID}'">View Task</button>
        `;
        list.appendChild(div);
    });
}