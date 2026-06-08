document.addEventListener("DOMContentLoaded", function () {

    let tasks = JSON.parse(localStorage.getItem("posttasks")) || [];

    // =====================
    // CALCULATE DATA
    // =====================
    let open = tasks.filter(t => t.status === "open").length;
    let done = tasks.filter(t => t.status === "done").length;
    let posted = tasks.length;

    let earned = tasks
        .filter(t => t.status === "done")
        .reduce((sum, t) => sum + Number(t.reward || 0), 0);

    // UPDATE UI
    document.getElementById("dashboard-open").innerText = open;
    document.getElementById("dashboard-posted").innerText = posted;
    document.getElementById("dashboard-done").innerText = done;
    document.getElementById("dashboard-earned").innerText = "RM " + earned;

    // =====================
    // TASK SUGGESTIONS
    // =====================
    const list = document.getElementById("taskList");

    tasks.slice(0, 5).forEach(task => {

        let div = document.createElement("div");
        div.className = "task";

        div.innerHTML = `
            <h4>${task.title}</h4>
            <span class="tag open">Open</span>
            <p>Reward: RM ${task.reward}</p>
        `;

        list.appendChild(div);
    });

    // =====================
    // PROGRESS BAR
    // =====================
    let completedPercent = posted ? (done / posted) * 100 : 0;
    let postedPercent = posted ? Math.min(posted * 10, 100) : 0;

    document.getElementById("barCompleted").style.width = completedPercent + "%";
    document.getElementById("barPosted").style.width = postedPercent + "%";

    // =====================
    // RECENT ACTIVITY
    // =====================
    const recent = document.getElementById("recent");

    tasks.slice(-3).forEach(task => {

        let div = document.createElement("div");
        div.className = "activity";

        let status = task.status === "done"
            ? `<span class="tag done">Completed</span>`
            : `<span class="tag progress">In Progress</span>`;

        div.innerHTML = `
            <span>${task.title}</span>
            ${status}
        `;

        recent.appendChild(div);
    });

});