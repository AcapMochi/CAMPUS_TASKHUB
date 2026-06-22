loadContacts();

let currentReceiverId = null; // Changed from 2 to null since we don't know who is first yet

document.addEventListener("DOMContentLoaded", function () {
    // 1. Load the contact list first!
    loadContacts();

    // Check for new messages every 2 seconds (only if a chat is selected)
    setInterval(() => {
        if (currentReceiverId) loadMessages();
    }, 2000);

    // Send message on button click
    document.getElementById('send-btn').addEventListener('click', sendMessage);

    // Send message on Enter key press
    document.getElementById('message-input').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
});

// --- NEW FUNCTION TO LOAD CONTACTS ---
function loadContacts() {
    fetch('includes/php/get_contacts.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('contact-list-container');
            container.innerHTML = '';

            if (data.status === 'success' && data.contacts.length > 0) {
                data.contacts.forEach((contact, index) => {
                    const pic = contact.Profile_Pic_URL || 'images/PFP.jpg';
                    const isActive = (currentReceiverId === contact.UserID) || (index === 0 && !currentReceiverId);

                    // If this is the first contact loaded and none is selected, click it automatically
                    if (isActive && !currentReceiverId) {
                        switchChat(contact.UserID, contact.Username, pic);
                    }

                    const div = document.createElement('div');
                    div.className = `contact-item ${isActive ? 'active' : ''}`;
                    div.setAttribute('data-user-id', contact.UserID);
                    div.onclick = () => switchChat(contact.UserID, contact.Username, pic);

                    div.innerHTML = `
                        <img src="${pic}" alt="${contact.Username}" class="contact-avatar">
                        <div class="contact-info">
                            <h4>${contact.Username}</h4>
                        </div>
                    `;
                    container.appendChild(div);
                });
            } else {
                container.innerHTML = '<p style="text-align: center; color: #888; padding: 20px;">No contacts yet. Accept a runner to start chatting!</p>';
            }
        });
}

// Switch chat when clicking a different contact
function switchChat(userId, userName, userPic) {
    currentReceiverId = userId;

    document.querySelector('.chat-user-info h2').innerText = userName;
    document.querySelector('.chat-user-info img').src = userPic;

    document.querySelectorAll('.contact-item').forEach(item => item.classList.remove('active'));

    const activeItem = document.querySelector(`.contact-item[data-user-id="${userId}"]`);
    if (activeItem) activeItem.classList.add('active');

    loadMessages();
}

let currentReceiverId = 2; // Default to the first person in your list

document.addEventListener("DOMContentLoaded", function () {
    // Load messages immediately on page load
    loadMessages();

    // Check for new messages every 2 seconds
    setInterval(loadMessages, 2000);

    // Send message on button click
    document.getElementById('send-btn').addEventListener('click', sendMessage);

    // Send message on Enter key press
    document.getElementById('message-input').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
});


// Switch chat when clicking a different contact
function switchChat(userId, userName, userPic) {
    currentReceiverId = userId;

    // Update the UI header
    document.querySelector('.chat-user-info h2').innerText = userName;
    document.querySelector('.chat-user-info img').src = userPic;

    // Update active class on the sidebar
    document.querySelectorAll('.contact-item').forEach(item => item.classList.remove('active'));
    document.querySelector(`.contact-item[data-user-id="${userId}"]`).classList.add('active');

    // Load their messages
    loadMessages();
}

function loadMessages() {
    fetch(`includes/php/get_messages.php?receiver_id=${currentReceiverId}`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('chat-messages-container');
            container.innerHTML = '';

            if (data.status === 'success') {
                data.messages.forEach(msg => {
                    const div = document.createElement('div');
                    // Check if I sent the message or they sent it
                    div.className = msg.is_mine ? 'message sent' : 'message received';

                    div.innerHTML = `
                        <div class="message-content" style="padding: 10px; border-radius: 10px; margin-bottom: 10px; max-width: 70%; ${msg.is_mine ? 'background: #00ff88; color: black; margin-left: auto;' : 'background: #333; color: white; margin-right: auto;'}">
                            <p style="margin: 0;">${msg.Content}</p>
                            <span style="font-size: 0.7em; color: ${msg.is_mine ? '#333' : '#aaa'};">${msg.time}</span>
                        </div>
                    `;
                    container.appendChild(div);
                });

                // Scroll to the bottom to see the newest message
                container.scrollTop = container.scrollHeight;
            }
        });
}

function sendMessage() {
    const input = document.getElementById('message-input');
    const text = input.value.trim();

    if (text === '') return;

    fetch('includes/php/send_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            receiver_id: currentReceiverId,
            message: text
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                input.value = ''; // Clear the input box
                loadMessages();   // Instantly reload to show the sent message
            } else {
                alert("Error sending message.");
            }
        });
}