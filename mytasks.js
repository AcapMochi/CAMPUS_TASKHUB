document.addEventListener("DOMContentLoaded", function () {

    loadTasks();

});

function loadTasks() {
    fetch('includes/get_my_tasks.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const posted = document.getElementById("postedList");
                const running = document.getElementById("runningList");

                posted.innerHTML = "";
                running.innerHTML = "";

                if (data.tasks.length === 0) {
                    posted.innerHTML = "<p>No tasks found.</p>";
                    return;
                }

                data.tasks.forEach(task => {
                    let div = document.createElement("div");
                    div.className = "task";

                    // Logic: If I am the Runner, show "Mark Done" button. 
                    // If I am the Poster, I just view status.
                    let isRunner = (task.RunnerID == '<?php echo $_SESSION["user_id"]; ?>'); // Simplified logic

                    div.innerHTML = `
                        <h4>${task.Title}</h4>
                        <span class="tag ${task.Status.toLowerCase()}">${task.Status}</span>
                        <p>Reward: RM ${task['Reward Amount']}</p>
                        ${task.Status === 'In Progress' && task.RunnerID == '<?php echo $_SESSION["user_id"]; ?>'
                            ? `<button onclick="completeTask(${task.TaskID})">Mark as Done</button>`
                            : ''}
                    `;

                    if (task.RunnerID == '<?php echo $_SESSION["user_id"]; ?>') {
                        running.appendChild(div);
                    } else {
                        posted.appendChild(div);
                    }
                });
            }
        });
}

// BUTTON
function acceptTask(i) {
    let tasks = JSON.parse(localStorage.getItem("posttasks"));
    tasks[i].status = "accepted";
    localStorage.setItem("posttasks", JSON.stringify(tasks));
    loadTasks();
}

function completeTask(i) {
    let tasks = JSON.parse(localStorage.getItem("posttasks"));
    tasks[i].status = "done";
    localStorage.setItem("posttasks", JSON.stringify(tasks));
    loadTasks();
}