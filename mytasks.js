document.addEventListener("DOMContentLoaded", function () {

    loadTasks();

});

function loadTasks() {

    let tasks = JSON.parse(localStorage.getItem("posttasks")) || [];

    console.log(tasks); // 🔥 check data

    const posted = document.getElementById("postedList");
    const running = document.getElementById("runningList");

    posted.innerHTML = "";
    running.innerHTML = "";

    if (tasks.length === 0) {
        posted.innerHTML = "No tasks yet";
        return;
    }

    tasks.forEach((task, index) => {

        let div = document.createElement("div");
        div.className = "task";

        let status = "";

        if (task.status === "open") {
            status = `<span class="status open">Open</span>`;
        } 
        else if (task.status === "accepted") {
            status = `<span class="status accepted">Accepted</span>`;
        } 
        else {
            status = `<span class="status done">Completed</span>`;
        }

        let button = "";

        if (task.status === "open") {
            button = `<button onclick="acceptTask(${index})">Accept</button>`;
        } 
        else if (task.status === "accepted") {
            button = `<button onclick="completeTask(${index})">Mark Done</button>`;
        }

        div.innerHTML = `
            <h4>${task.title}</h4>
            ${status}
            <small>${task.location}</small><br>
            <span>RM ${task.reward}</span><br>
            ${button}
        `;

        if (task.status === "accepted") {
            running.appendChild(div);
        } else {
            posted.appendChild(div);
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