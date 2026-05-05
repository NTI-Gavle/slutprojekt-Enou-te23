<div class="groupchat-overlay" id="groupChatOverlay">
    <div class="groupchat-modal">
        <div class="groupchat-header">
            <div class="groupchat-header-info">
                <i class="bi bi-chat-dots"></i>
                <div>
                    <span class="groupchat-room-name" id="groupChatRoomName">Room Name</span>
                    <span class="groupchat-member-count" id="groupChatMemberCount">0 members</span>
                </div>
            </div>
            <div class="groupchat-header-actions">
                <button class="groupchat-action-btn" title="Members" onclick="toggleMemberList()">
                    <i class="bi bi-people"></i>
                </button>
                <button class="groupchat-action-btn" title="Close" onclick="closeGroupChat()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
        
        <div class="groupchat-body">
            <div class="groupchat-messages" id="groupChatMessages">
                <div class="chat-loading" id="chatLoading">
                    <div class="spinner-border spinner-border-sm" role="status"></div>
                    <span class="ms-2">Loading messages...</span>
                </div>
            </div>
            
            <div class="groupchat-member-list" id="memberList">
                <h6>Members</h6>
                <div id="memberListContent"></div>
            </div>
        </div>
        
        <div class="groupchat-input">
            <button class="input-btn" title="Emoji" onclick="toggleEmojiPicker()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                    <path d="M8 14s1.5 2 4 2 4-2 4-2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="9" cy="10" r="1" fill="currentColor"/>
                    <circle cx="15" cy="10" r="1" fill="currentColor"/>
                </svg>
            </button>
            <input type="text" class="message-input" placeholder="Type a message..." id="groupMessageInput" onkeypress="if(event.key==='Enter') sendGroupMessage()">
            <button class="input-btn send-btn" title="Send" onclick="sendGroupMessage()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="M10.3 13.7L20.1 3.9M10.6 14.1L12.8 18.6C13.3 19.7 13.6 20.2 13.9 20.3C14.2 20.5 14.6 20.4 14.8 20.3C15.2 20.1 15.4 19.5 15.7 18.4L19.9 6.1C20.3 5.1 20.5 4.6 20.3 4.3C20.2 4 20 3.8 19.7 3.7C19.4 3.5 18.9 3.7 18.4 3.9L6.1 8.1C5 8.5 4.5 8.7 4.3 9.1C4.2 9.4 4.2 9.7 4.3 10C4.5 10.3 4.9 10.6 5.8 11.1L10.3 13.7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<style>
.groupchat-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1060;
    padding: 20px;
}

.groupchat-overlay.open {
    display: flex;
}

.groupchat-modal {
    background: white;
    border-radius: 16px;
    width: 100%;
    max-width: 800px;
    height: 80vh;
    max-height: 600px;
    display: flex;
    flex-direction: column;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    overflow: hidden;
}

.groupchat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background: #f8f8f8;
    border-bottom: 1px solid #ddd;
}

.groupchat-header-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.groupchat-header-info i {
    font-size: 1.5rem;
    color: #6c5ce7;
}

.groupchat-room-name {
    display: block;
    font-weight: 600;
    font-size: 1rem;
}

.groupchat-member-count {
    font-size: 0.75rem;
    color: #666;
}

.groupchat-header-actions {
    display: flex;
    gap: 8px;
}

.groupchat-action-btn {
    background: none;
    border: none;
    padding: 8px;
    cursor: pointer;
    color: #666;
    border-radius: 8px;
    transition: background 0.2s;
}

.groupchat-action-btn:hover {
    background: #e8e8e8;
}

.groupchat-body {
    flex: 1;
    display: flex;
    overflow: hidden;
    position: relative;
}

.groupchat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.groupchat-member-list {
    width: 250px;
    border-left: 1px solid #ddd;
    padding: 16px;
    overflow-y: auto;
    display: none;
    background: #f8f8f8;
}

.groupchat-member-list.open {
    display: block;
}

.groupchat-member-list h6 {
    margin-bottom: 12px;
    color: #666;
    font-size: 0.85rem;
    text-transform: uppercase;
}

.member-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px;
    border-radius: 8px;
    margin-bottom: 8px;
}

.member-item:hover {
    background: #e8e8e8;
}

.member-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
}

.member-info {
    flex: 1;
}

.member-name {
    display: block;
    font-size: 0.85rem;
    font-weight: 500;
}

.member-role {
    font-size: 0.7rem;
    color: #666;
}

.member-actions {
    display: none;
}

.member-item:hover .member-actions {
    display: block;
}

.btn-small {
    padding: 4px 8px;
    font-size: 0.7rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    background: #f8f8f8;
    color: #666;
}

.btn-small:hover {
    background: #e8e8e8;
}

.btn-small.btn-danger {
    color: #e74c3c;
}

.chat-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #666;
}

.groupchat-input {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: #f8f8f8;
    border-top: 1px solid #ddd;
}

.group-message {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    max-width: 70%;
}

.group-message.own {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.group-message-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}

.group-message-content {
    flex: 1;
}

.group-message-sender {
    display: block;
    font-size: 0.7rem;
    color: #666;
    margin-bottom: 2px;
}

.group-message-bubble {
    padding: 10px 14px;
    border-radius: 16px;
    word-wrap: break-word;
    font-size: 0.9rem;
    line-height: 1.4;
}

.group-message:not(.own) .group-message-bubble {
    background: #e8e8e8;
    color: black;
}

.group-message.own .group-message-bubble {
    background: #080110;
    color: white;
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

.group-message:hover .delete-msg-btn {
    opacity: 1;
}

@media (max-width: 768px) {
    .groupchat-modal {
        height: 100vh;
        max-height: 100vh;
        border-radius: 0;
    }
    
    .groupchat-member-list {
        position: absolute;
        right: 0;
        top: 0;
        bottom: 0;
        background: white;
        z-index: 10;
        box-shadow: -2px 0 8px rgba(0,0,0,0.1);
    }
}
</style>

<script>
let currentRoomId = null;
let lastMessageId = 0;
let pollInterval = null;
let isPolling = false;

function openGroupChat(roomId, roomName) {
    currentRoomId = roomId;
    document.getElementById('groupChatRoomName').textContent = roomName;
    document.getElementById('groupChatOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
    
    lastMessageId = 0;
    loadMessages();
    loadMembers();
    
    startPolling();
}

function closeGroupChat() {
    document.getElementById('groupChatOverlay').classList.remove('open');
    document.body.style.overflow = '';
    currentRoomId = null;
    lastMessageId = 0;
    stopPolling();
}

function startPolling() {
    stopPolling();
    pollInterval = setTimeout(checkNewMessages, 3000);
}

function stopPolling() {
    if (pollInterval) {
        clearTimeout(pollInterval);
        pollInterval = null;
    }
    isPolling = false;
}

function toggleMemberList() {
    document.getElementById('memberList').classList.toggle('open');
}

function loadMessages() {
    if (!currentRoomId) return;
    
    document.getElementById('chatLoading').style.display = 'flex';
    document.getElementById('groupChatMessages').innerHTML = '';
    document.getElementById('groupChatMessages').appendChild(document.getElementById('chatLoading'));
    
    fetch(`api/get_messages.php?room_id=${currentRoomId}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('chatLoading').style.display = 'none';
            if (data.success && data.messages) {
                data.messages.forEach(msg => appendMessage(msg, false));
                if (data.messages.length > 0) {
                    lastMessageId = data.messages[data.messages.length - 1].id;
                }
                scrollToBottom();
            }
        })
        .catch(err => {
            document.getElementById('chatLoading').style.display = 'none';
            console.error('Error loading messages:', err);
        });
}

function sendGroupMessage() {
    const input = document.getElementById('groupMessageInput');
    const text = input.value.trim();
    if (!text || !currentRoomId) return;
    
    fetch('api/send_message.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `room_id=${currentRoomId}&message=${encodeURIComponent(text)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            appendMessage({
                id: data.message_id,
                sender_name: '<?= $_SESSION['display_name'] ?>',
                sender_avatar: '<?= getValidProfileImage($_SESSION['profile_image'] ?? null) ?>',
                message: text,
                is_own: true
            }, true);
            scrollToBottom();
        }
    })
    .catch(err => console.error('Error sending message:', err));
}

function appendMessage(msg, prepend) {
    const messagesDiv = document.getElementById('groupChatMessages');
    const msgDiv = document.createElement('div');
    msgDiv.className = 'group-message' + (msg.is_own ? ' own' : '');
    msgDiv.dataset.messageId = msg.id;
    msgDiv.innerHTML = `
        <img src="${msg.sender_avatar || 'img/default-avatar.svg'}" alt="" class="group-message-avatar">
        <div class="group-message-content">
            <span class="group-message-sender">${escapeHtml(msg.sender_name)}</span>
            <div class="group-message-bubble">${escapeHtml(msg.message)}</div>
        </div>
        ${msg.is_own ? '<button class="delete-msg-btn" onclick="deleteMessage(' + msg.id + ')" title="Delete"><i class="bi bi-trash"></i></button>' : ''}
    `;
    messagesDiv.appendChild(msgDiv);
}

function deleteMessage(messageId) {
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

function checkNewMessages() {
    if (!currentRoomId || isPolling) return;
    
    isPolling = true;
    
    fetch(`api/get_messages.php?room_id=${currentRoomId}&after=${lastMessageId}`)
        .then(res => res.json())
        .then(data => {
            isPolling = false;
            if (data.success && data.messages && data.messages.length > 0) {
                data.messages.forEach(msg => appendMessage(msg, false));
                lastMessageId = data.messages[data.messages.length - 1].id;
                scrollToBottom();
            }
            startPolling();
        })
        .catch(err => {
            isPolling = false;
            console.error('Error checking messages:', err);
            startPolling();
        });
}

function loadMembers() {
    if (!currentRoomId) return;
    
    fetch(`api/get_room_members.php?room_id=${currentRoomId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.members) {
                const container = document.getElementById('memberListContent');
                document.getElementById('groupChatMemberCount').textContent = data.members.length + ' members';
                
                let html = '';
                data.members.forEach(member => {
                    html += `
                    <div class="member-item">
                        <img src="${member.profile_image || 'img/default-avatar.svg'}" alt="" class="member-avatar">
                        <div class="member-info">
                            <span class="member-name">${escapeHtml(member.display_name)}</span>
                            <span class="member-role">${member.role}</span>
                        </div>
                        ${member.is_admin ? '<div class="member-actions"><button class="btn-small btn-danger" onclick="kickMember(' + member.id + ')">Kick</button></div>' : ''}
                    </div>
                    `;
                });
                container.innerHTML = html;
            }
        })
        .catch(err => console.error('Error loading members:', err));
}

function kickMember(userId) {
    if (!confirm('Are you sure you want to kick this member?')) return;
    
    fetch('api/kick_member.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `room_id=${currentRoomId}&user_id=${userId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadMembers();
        } else {
            alert(data.message || 'Failed to kick member');
        }
    });
}

function scrollToBottom() {
    const messagesDiv = document.getElementById('groupChatMessages');
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Close modal on overlay click (not on modal click)
document.getElementById('groupChatOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeGroupChat();
});
</script>
