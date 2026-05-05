<div class="userchat-modal" id="userChatModal">
    <div class="userchat-header" onclick="toggleUserChat()">
        <img src="img/default-avatar.svg" alt="" class="userchat-avatar" id="chatHeaderAvatar">
        <div class="userchat-title">
            <span class="userchat-name" id="chatHeaderName">Username</span>
            <span class="userchat-status" id="chatHeaderStatus">Online</span>
        </div>
        <button class="userchat-close" onclick="closeUserChat(event)">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div class="userchat-messages" id="userChatMessages">
        <div class="message incoming">
            <img src="img/default-avatar.svg" alt="" class="message-avatar">
            <div class="message-bubble">
                <span class="message-user">Other User</span>
                <p class="message-text">Hey! How are you?</p>
            </div>
        </div>
        <div class="message outgoing">
            <div class="message-bubble">
                <p class="message-text">I'm good, thanks!</p>
            </div>
            <img src="img/default-avatar.svg" alt="" class="message-avatar">
        </div>
    </div>
    <div class="userchat-input">
        <button class="input-btn" title="Emoji">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                <path d="M8 14s1.5 2 4 2 4-2 4-2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <circle cx="9" cy="10" r="1" fill="currentColor"/>
                <circle cx="15" cy="10" r="1" fill="currentColor"/>
            </svg>
        </button>
        <input type="text" class="message-input" placeholder="Type a message..." id="messageInput">
        <button class="input-btn" title="Attach Image">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M15 21H9C6.17157 21 4.75736 21 3.87868 20.1213C3 19.2426 3 17.8284 3 15M21 15C21 17.8284 21 19.2426 20.1213 20.1213C19.8215 20.4211 19.4594 20.6186 19 20.7487" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M12 16V3M12 3L16 7.375M12 3L8 7.375" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
        <button class="input-btn send-btn" title="Send" onclick="sendMessage()">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M10.3009 13.6949L20.102 3.89742M10.5795 14.1355L12.8019 18.5804C13.339 19.6545 13.6075 20.1916 13.9458 20.3356C14.2394 20.4606 14.575 20.4379 14.8492 20.2747C15.1651 20.0866 15.3591 19.5183 15.7472 18.3818L19.9463 6.08434C20.2845 5.09409 20.4535 4.59896 20.3378 4.27142C20.2371 3.98648 20.013 3.76234 19.7281 3.66167C19.4005 3.54595 18.9054 3.71502 17.9151 4.05315L5.61763 8.2523C4.48114 8.64037 3.91289 8.83441 3.72478 9.15032C3.56153 9.42447 3.53891 9.76007 3.66389 10.0536C3.80791 10.3919 4.34498 10.6605 5.41912 11.1975L9.86397 13.42C10.041 13.5085 10.1295 13.5527 10.2061 13.6118C10.2742 13.6643 10.3352 13.7253 10.3876 13.7933C10.4468 13.87 10.491 13.9585 10.5795 14.1355Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
    </div>
</div>

<style>
.userchat-modal {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 360px;
    max-width: calc(100vw - 40px);
    background: white;
    border: 1px solid #ddd;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.15);
    z-index: 1050;
    display: none;
    overflow: hidden;
}

.userchat-modal.open {
    display: block;
}

.userchat-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    background: #f8f8f8;
    border-bottom: 1px solid #ddd;
    cursor: pointer;
}

.userchat-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
}

.userchat-title {
    flex: 1;
}

.userchat-name {
    display: block;
    font-weight: 600;
    font-size: 0.9rem;
}

.userchat-status {
    font-size: 0.75rem;
    color: #00b894;
}

.userchat-close {
    background: none;
    border: none;
    padding: 4px;
    cursor: pointer;
    font-size: 1.1rem;
    color: #666;
}

.userchat-messages {
    height: 300px;
    overflow-y: auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.message {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    max-width: 85%;
}

.message.incoming {
    align-self: flex-start;
}

.message.outgoing {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.message-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}

.message-bubble {
    padding: 10px 14px;
    border-radius: 16px;
    max-width: 75%;
    word-wrap: break-word;
}

.message.incoming .message-bubble {
    background: #e8e8e8;
    border: 1px solid #e0e0e0;
    color: black;
}

.message.incoming .message-user {
    display: block;
    font-size: 0.7rem;
    color: #666;
    margin-bottom: 4px;
}

.message.outgoing .message-bubble {
    background: #080110;
    border: 1px solid #080110;
    color: white;
    max-width: 75%;
    word-wrap: break-word;
}

.message-text {
    margin: 0;
    font-size: 0.9rem;
    line-height: 1.4;
    word-wrap: break-word;
}

.userchat-input {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    background: #f8f8f8;
    border-top: 1px solid #ddd;
}

.message-input {
    flex: 1;
    padding: 10px 14px;
    border: 1px solid #ddd;
    border-radius: 24px;
    font-size: 0.9rem;
    outline: none;
}

.message-input:focus {
    border-color: #080110;
}

.input-btn {
    background: none;
    border: none;
    padding: 8px;
    cursor: pointer;
    color: #666;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.input-btn:hover {
    background: #e8e8e8;
}

.send-btn {
    color: #080110;
}

.delete-msg-btn {
    background: none;
    border: none;
    color: #999;
    cursor: pointer;
    padding: 4px;
    font-size: 0.8rem;
    align-self: center;
    opacity: 0;
    transition: opacity 0.2s;
}

.message:hover .delete-msg-btn {
    opacity: 1;
}

@media (max-width: 480px) {
    .userchat-modal {
        bottom: 0;
        right: 0;
        left: 0;
        width: 100%;
        max-width: 100%;
        border-radius: 16px 16px 0 0;
    }
}
</style>

<script>
let currentChatUserId = null;
let lastPrivateMessageId = 0;
let privatePollInterval = null;
let isPrivatePolling = false;

function openUserChat(userId, userName, userAvatar) {
    document.getElementById('chatHeaderName').textContent = userName;
    document.getElementById('chatHeaderAvatar').src = userAvatar;
    document.getElementById('chatHeaderStatus').textContent = 'Online';
    document.getElementById('userChatModal').classList.add('open');
    currentChatUserId = userId;
    lastPrivateMessageId = 0;
    
    loadPrivateMessages();
    startPrivatePolling();
    hideChatBadge();
}

function openUserChatById(userId) {
    fetch(`api/search.php?q=&user_id=${userId}`)
        .then(res => res.json())
        .then(data => {
            if (data.users && data.users.length > 0) {
                const user = data.users[0];
                openUserChat(userId, user.display_name || user.username, user.profile_image || 'img/default-avatar.svg');
            }
        });
}

function toggleUserChat() {
    var modal = document.getElementById('userChatModal');
    modal.classList.toggle('open');
}

function closeUserChat(e) {
    if (e) e.stopPropagation();
    document.getElementById('userChatModal').classList.remove('open');
    currentChatUserId = null;
    lastPrivateMessageId = 0;
    stopPrivatePolling();
}

function startPrivatePolling() {
    stopPrivatePolling();
    privatePollInterval = setTimeout(checkNewPrivateMessages, 3000);
}

function stopPrivatePolling() {
    if (privatePollInterval) {
        clearTimeout(privatePollInterval);
        privatePollInterval = null;
    }
    isPrivatePolling = false;
}

function loadPrivateMessages() {
    if (!currentChatUserId) return;
    
    const messagesDiv = document.getElementById('userChatMessages');
    messagesDiv.innerHTML = '<div class="chat-loading"><div class="spinner-border spinner-border-sm"></div><span class="ms-2">Loading...</span></div>';
    
    fetch(`api/get_messages.php?user_id=${currentChatUserId}`)
        .then(res => res.json())
        .then(data => {
            messagesDiv.innerHTML = '';
            if (data.success && data.messages) {
                data.messages.forEach(msg => appendPrivateMessage(msg));
                if (data.messages.length > 0) {
                    lastPrivateMessageId = data.messages[data.messages.length - 1].id;
                }
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            }
        })
        .catch(err => {
            messagesDiv.innerHTML = '<div class="text-muted text-center p-3">Error loading messages</div>';
        });
}

function sendMessage() {
    var input = document.getElementById('messageInput');
    var text = input.value.trim();
    if (!text || !currentChatUserId) return;
    
    fetch('api/send_message.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `receiver_id=${currentChatUserId}&message=${encodeURIComponent(text)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            appendPrivateMessage({
                id: data.message_id,
                sender_name: '<?= $_SESSION['display_name'] ?>',
                sender_avatar: '<?= getValidProfileImage($_SESSION['profile_image'] ?? null) ?>',
                message: text,
                is_own: true
            });
            document.getElementById('userChatMessages').scrollTop = document.getElementById('userChatMessages').scrollHeight;
        }
    })
    .catch(err => console.error('Error sending message:', err));
}

function appendPrivateMessage(msg) {
    const messagesDiv = document.getElementById('userChatMessages');
    const msgDiv = document.createElement('div');
    msgDiv.className = 'message' + (msg.is_own ? ' outgoing' : ' incoming');
    msgDiv.dataset.messageId = msg.id;
    msgDiv.innerHTML = `
        <img src="${msg.sender_avatar || 'img/default-avatar.svg'}" alt="" class="message-avatar">
        <div class="message-bubble">
            ${!msg.is_own ? '<span class="message-user">' + escapeHtml(msg.sender_name) + '</span>' : ''}
            <p class="message-text">${escapeHtml(msg.message)}</p>
        </div>
        ${msg.is_own ? '<button class="delete-msg-btn" onclick="deletePrivateMessage(' + msg.id + ')" title="Delete"><i class="bi bi-trash"></i></button>' : ''}
    `;
    messagesDiv.appendChild(msgDiv);
}

function deletePrivateMessage(messageId) {
    if (!confirm('Delete this message?')) return;
    
    fetch('api/delete_message.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `message_id=${messageId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const msgDiv = document.querySelector(`[data-message-id="${messageId}"]`);
            if (msgDiv) msgDiv.remove();
        } else {
            alert(data.message || 'Failed to delete message');
        }
    })
    .catch(err => alert('Error deleting message'));
}

function checkNewPrivateMessages() {
    if (!currentChatUserId || isPrivatePolling) return;
    
    isPrivatePolling = true;
    
    fetch(`api/get_messages.php?user_id=${currentChatUserId}&after=${lastPrivateMessageId}`)
        .then(res => res.json())
        .then(data => {
            isPrivatePolling = false;
            if (data.success && data.messages && data.messages.length > 0) {
                data.messages.forEach(msg => appendPrivateMessage(msg));
                lastPrivateMessageId = data.messages[data.messages.length - 1].id;
                document.getElementById('userChatMessages').scrollTop = document.getElementById('userChatMessages').scrollHeight;
            }
            startPrivatePolling();
        })
        .catch(err => {
            isPrivatePolling = false;
            startPrivatePolling();
        });
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.getElementById('messageInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') sendMessage();
});
</script>