let currentReceiverId = null;
let currentTaskId = null;

document.addEventListener("DOMContentLoaded", function () {
    loadContacts();

    // Check for new messages every 2 seconds
    setInterval(() => {
        if (currentReceiverId && currentTaskId) loadMessages();
    }, 2000);

    const messageInput = document.getElementById('message-input');
    if (messageInput) {
        messageInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                sendMessage();
            }
        });
    }
});

function loadContacts() {
    fetch('includes/php/get_contacts.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('contact-list-container');
            container.innerHTML = '';

            if (data.status === 'success' && data.contacts.length > 0) {
                data.contacts.forEach((contact) => {
                    const div = document.createElement('div');
                    div.className = `contact-item ${currentReceiverId === contact.UserID && currentTaskId === contact.TaskID ? 'active' : ''}`;
                    div.style = "padding: 15px; display: flex; align-items: center; gap: 12px; cursor: pointer; border-bottom: 1px solid #2f2235;";
                    
                    if (currentReceiverId === contact.UserID && currentTaskId === contact.TaskID) {
                        div.style.backgroundColor = "#34243c";
                    }

                    const pfpUrl = contact.Profile_Pic_URL ? contact.Profile_Pic_URL : 'images/PFP.jpg';

                    div.innerHTML = `
                        <img src="${pfpUrl}" style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover;" onerror="this.src='images/PFP.jpg'">
                        <div style="flex: 1; min-width: 0;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <strong style="color: white; font-size: 0.95rem;">${contact.Username}</strong>
                                <span style="font-size: 0.75rem; background: #6c5ce7; color: white; padding: 2px 6px; border-radius: 4px;">${contact.CounterpartRole}</span>
                            </div>
                            <p style="margin: 4px 0 0 0; font-size: 0.8rem; color: #948b9c; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                📦 Task: ${contact.TaskTitle}
                            </p>
                        </div>
                    `;

                    div.addEventListener('click', () => {
                        currentReceiverId = contact.UserID;
                        currentTaskId = contact.TaskID;
                        
                        document.querySelector('.chat-user-info h2').textContent = contact.Username;
                        const headerImg = document.querySelector('.chat-user-info img');
                        if(headerImg) {
                            headerImg.src = pfpUrl;
                            headerImg.style.display = "block";
                        }
                        
                        const statusSpan = document.querySelector('.chat-user-info span');
                        if(statusSpan) {
                            statusSpan.style.display = "block";
                            statusSpan.textContent = `Task: ${contact.TaskTitle}`;
                        }

                        // Enable inputs
                        const msgInput = document.getElementById('message-input');
                        const sendBtn = document.getElementById('send-btn');
                        if(msgInput) msgInput.disabled = false;
                        if(sendBtn) sendBtn.disabled = false;
                        
                        document.querySelectorAll('.contact-item').forEach(item => item.style.backgroundColor = "transparent");
                        div.style.backgroundColor = "#34243c";

                        loadMessages();
                    });

                    container.appendChild(div);
                });
            } else {
                container.innerHTML = `<p style="text-align: center; color: #948b9c; padding: 20px; font-size:0.9rem;">No active tasks found to chat about.</p>`;
            }
        });
}

function loadMessages() {
    if (!currentReceiverId || !currentTaskId) return;

    fetch('includes/php/load_messages.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ receiver_id: currentReceiverId, task_id: currentTaskId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const container = document.getElementById('chat-messages-container');
            container.innerHTML = '';

            data.messages.forEach(msg => {
                const div = document.createElement('div');
                div.style.width = "100%";
                div.style.display = "flex";
                div.style.marginBottom = "12px";
                
                let bubbleStyle = "padding: 12px 16px; border-radius: 12px; max-width: 65%; font-size: 0.95rem; line-height: 1.4; word-wrap: break-word;";
                
                if (msg.is_mine) {
                    div.style.justifyContent = "flex-end";
                    bubbleStyle += "background: #6c5ce7; color: white; border-bottom-right-radius: 2px;";
                } else {
                    div.style.justifyContent = "flex-start";
                    bubbleStyle += "background: #251b2e; color: white; border: 1px solid #3d2f44; border-bottom-left-radius: 2px;";
                }

                // THE FIX: This safely catches uppercase Content, lowercase content, or empty strings!
                const messageText = msg.content || msg.Content || "[Message error]";

                div.innerHTML = `
                    <div style="${bubbleStyle}">
                        <p style="margin: 0 0 4px 0;">${messageText}</p>
                        <span style="display: block; text-align: right; font-size: 0.7rem; color: rgba(255,255,255,0.6);">${msg.time}</span>
                    </div>
                `;
                container.appendChild(div);
            });

            container.scrollTop = container.scrollHeight;
        }
    });
}

function sendMessage() {
    const input = document.getElementById('message-input');
    if (!input) return;

    const text = input.value.trim();

    if (!currentReceiverId || !currentTaskId) {
        alert("Please select a conversation from the sidebar first!");
        return;
    }

    if (text === '') return;

    // Optimistically clear the input right away
    input.value = '';

    fetch('includes/php/send_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            receiver_id: currentReceiverId,
            task_id: currentTaskId, 
            message: text
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            loadMessages();
        } else {
            alert("Error sending: " + data.message);
        }
    })
    .catch(err => console.error("Send error:", err));
}