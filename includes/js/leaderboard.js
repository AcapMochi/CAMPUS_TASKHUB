// includes/leaderboard.js

let activeSystemTab = 'runners';

async function fetchSystemData() {
    try {
        // UPDATED: Added 'includes/' to the path
        const response = await fetch(`includes/php/get_leaderboard.php?type=${activeSystemTab}`);

        if (!response.ok) {
            throw new Error('Network response was not stable');
        }

        const data = await response.json();

        renderSystemLeaderboard(data);

    } catch (error) {
        console.error('Error fetching data from PHP system:', error);
    }
}

function renderSystemLeaderboard(usersList) {
    const listElement = document.getElementById('leaderboard-list');
    listElement.innerHTML = '';

    if (!usersList || usersList.length === 0) {
        listElement.innerHTML = `<li style="justify-content: center; color: #a0a0a0; font-size: 1rem; padding: 20px;">Waiting for system updates...</li>`;
        return;
    }

    usersList.forEach((user, index) => {
        const listItem = document.createElement('li');
        const unitLabel = activeSystemTab === 'runners' ? 'tasks' : 'posts';

        listItem.innerHTML = `
            <span class="rank">${index + 1}</span>
            <img class="avatar" src="${user.img}" alt="${user.name}">
            <div class="user-details">
                <span class="name">${user.name}</span>
                <span class="rating">${user.rating} ★</span>
            </div>
            <span class="task-count">${user.count} ${unitLabel}</span>
        `;

        listElement.appendChild(listItem);
    });
}

function switchTab(tabName) {
    activeSystemTab = tabName;

    document.getElementById('btn-posters').classList.remove('active-btn');
    document.getElementById('btn-runners').classList.remove('active-btn');

    if (tabName === 'posters') {
        document.getElementById('btn-posters').classList.add('active-btn');
    } else {
        document.getElementById('btn-runners').classList.add('active-btn');
    }

    fetchSystemData();
}

fetchSystemData();
setInterval(fetchSystemData, 60000);