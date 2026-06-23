document.addEventListener("DOMContentLoaded", function () {
    loadTasks();
});

function loadTasks() {
    fetch('includes/php/get_my_tasks.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                renderRunnerTasks(data.runner_tasks);
                renderPostedTasks(data.posted_tasks);
            }
        });
}

function renderRunnerTasks(tasks) {
    const runningList = document.getElementById("runningList");
    runningList.innerHTML = "";

    if (tasks.length === 0) {
        runningList.innerHTML = "<p style='color: var(--text-muted);'>You haven't applied for any tasks yet.</p>";
        return;
    }

    tasks.forEach(task => {
        let div = document.createElement("div");
        div.className = "task-card";

        let statusTag = '';
        let actionBtn = '';

        if (task.AppStatus === 'Pending') {
            statusTag = `<span class="tag" style="background: #333; color: white;">Pending Approval</span>`;
        } else if (task.AppStatus === 'Accepted' && task.TaskStatus === 'In Progress') {
            statusTag = `<span class="tag" style="background: var(--accent); color: black;">In Progress</span>`;
            actionBtn = `<button class="btn-primary" onclick="window.location.href='taskprogressrunner.html?id=${task.TaskID}'">View Progress</button>`;
        } else {
            statusTag = `<span class="tag" style="background: #27c46b; color: white;">${task.TaskStatus}</span>`;
        }

        div.innerHTML = `
            <h4>${task.Title}</h4>
            ${statusTag}
            <p>Reward: RM ${task['Reward Amount']}</p>
            ${actionBtn}
        `;
        runningList.appendChild(div);
    });
}


function renderPostedTasks(tasks) {
    const postedList = document.getElementById("postedList");
    postedList.innerHTML = "";

    if (tasks.length === 0) {
        postedList.innerHTML = "<p style='color: var(--text-muted);'>You haven't posted any tasks yet.</p>";
        return;
    }

    tasks.forEach(task => {
        let div = document.createElement("div");
        div.className = "task-card";

        // Fix Status string match to handle the database's underscore ('In_Progress')
        let displayStatus = task.Status.replace('_', ' ');
        let statusClass = task.Status === 'Open' ? 'open' : (task.Status === 'In_Progress' ? 'progress' : 'done');

        div.innerHTML = `
            <h4>${task.Title}</h4>
            <span class="tag ${statusClass}">${displayStatus}</span>
            <p>Reward: RM ${task['Reward_Amount']}</p>
        `;

        if (task.Status === 'Open') {
            // Show applicants if it's still open
            div.innerHTML += `<div class="applicants-container" id="applicants-${task.TaskID}"><p class="loading-text">Checking for applicants...</p></div>`;
            postedList.appendChild(div);
            loadApplicants(task.TaskID);

        } else if (task.Status === 'In_Progress') {
            // Show the accepted runner's info!
            let runnerName = task.RunnerUsername || 'Unknown Runner';
            let runnerPic = task.RunnerPic || 'images/PFP.jpg';

            div.innerHTML += `
                <div style="margin: 15px 0; padding: 10px; background: rgba(0, 255, 136, 0.1); border-radius: 8px; border: 1px solid rgba(0, 255, 136, 0.3); display: flex; align-items: center; gap: 15px;">
                    <img src="${runnerPic}" alt="Runner Profile" style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover;">
                    <div>
                        <p style="margin: 0; font-size: 0.85em; color: #aaaaaa;">Assigned Runner:</p>
                        <p style="margin: 0; font-weight: bold; color: rgb(0, 255, 136); font-size: 1.1em;">${runnerName}</p>
                    </div>
                </div>
                <button class="btn-primary" onclick="window.location.href='taskprogressposter.html?id=${task.TaskID}'">Track Runner</button>
            `;
            postedList.appendChild(div);

        } else {
            postedList.appendChild(div);
        }
    });
}

// --- 3. FETCH & DISPLAY APPLICANTS ---
function loadApplicants(taskId) {
    fetch(`includes/php/get_applicants.php?task_id=${taskId}`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById(`applicants-${taskId}`);
            if (data.status === 'success' && data.applicants.length > 0) {
                container.innerHTML = `<h5 class="applicant-header">Applicants (${data.applicants.length})</h5>`;

                data.applicants.forEach(app => {
                    const pic = app['Profile_Pic_URL'] || 'images/PFP.jpg';
                    container.innerHTML += `
                        <div class="applicant-card">
                            <div class="app-info">
                                <img src="${pic}" class="app-avatar" alt="Profile">
                                <div>
                                    <p class="app-name">${app.Username} <span>⭐ ${app['Rating_Average']}</span></p>
                                </div>
                            </div>
                            <button class="btn-accept-runner" onclick="acceptRunner(${taskId}, ${app.RunnerID})">Accept Runner</button>
                        </div>
                    `;
                });
            } else {
                container.innerHTML = `<p style="color: #888; font-size: 0.9em; margin-top: 10px;">No applicants yet.</p>`;
            }
        });
}

function acceptRunner(taskId, runnerId) {
    if (!confirm("Are you sure you want to select this runner?")) return;

    fetch('includes/php/select_runner_handler.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ task_id: taskId, runner_id: runnerId })
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Redirect to Escrow Payment Page
                window.location.href = `payment.html?id=${taskId}`;
            } else {
                alert("Error: " + data.message);
            }
        });
}