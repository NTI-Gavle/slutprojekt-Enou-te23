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
                <button class="groupchat-action-btn" id="roomVisibilityBtn" title="Change Visibility" onclick="showRoomVisibilitySettings()" style="display: none;">
                    <i class="bi bi-lock-unlock"></i>
                </button>
                <button class="groupchat-action-btn" id="roomCodeBtn" title="Change Code" onclick="showRoomCodeSettings()" style="display: none;">
                    <i class="bi bi-key"></i>
                </button>
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
                <div id="bannedListContent" style="display: none;"></div>
            </div>
        </div>
        
        <div class="groupchat-input">
            <input type="file" id="groupFileInput" multiple accept="image/*,.pdf,.txt,.doc,.docx" style="display: none;" onchange="handleGroupFileUpload(this)">
            <button class="input-btn" title="Attach Files" onclick="document.getElementById('groupFileInput').click()">
                <i class="bi bi-paperclip"></i>
            </button>
            <button class="input-btn" title="Emoji" onclick="toggleGroupEmojiPicker()">
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
        <div id="groupFilePreview" class="file-preview-container" style="display: none;"></div>
        
        <div id="groupEmojiPicker" class="emoji-picker" style="display: none;">
            <div class="emoji-picker-header">
                <input type="text" id="groupEmojiSearch" class="emoji-search" placeholder="Search emojis..." oninput="filterGroupEmojis(this.value)">
                <button class="emoji-close" onclick="toggleGroupEmojiPicker()">&times;</button>
            </div>
            <div class="emoji-categories">
                <button class="emoji-cat-btn active" data-cat="smileys" onclick="showGroupEmojiCategory('smileys')">😀</button>
                <button class="emoji-cat-btn" data-cat="animals" onclick="showGroupEmojiCategory('animals')">🐶</button>
                <button class="emoji-cat-btn" data-cat="food" onclick="showGroupEmojiCategory('food')">🍔</button>
                <button class="emoji-cat-btn" data-cat="activities" onclick="showGroupEmojiCategory('activities')">⚽</button>
                <button class="emoji-cat-btn" data-cat="travel" onclick="showGroupEmojiCategory('travel')">🚗</button>
                <button class="emoji-cat-btn" data-cat="objects" onclick="showGroupEmojiCategory('objects')">💡</button>
                <button class="emoji-cat-btn" data-cat="symbols" onclick="showGroupEmojiCategory('symbols')">❤️</button>
            </div>
            <div class="emoji-grid" id="groupEmojiGrid"></div>
        </div>
    </div>
</div>

<script>
// Global state variables for group chat functionality
let currentRoomId = null;       // ID of currently open chat room
let lastMessageId = 0;           // ID of last received message for polling
let pollInterval = null;        // Timer ID for message polling
let isPolling = false;          // Flag to prevent concurrent polling requests

/**
 * Opens the group chat modal for a specific room.
 * Initializes polling to fetch new messages every 3 seconds.
 * @param {number} roomId - The unique ID of the room to open
 * @param {string} roomName - Display name of the room
 */
function openGroupChat(roomId, roomName) {
    currentRoomId = roomId;
    document.getElementById('groupChatRoomName').textContent = roomName;
    document.getElementById('groupChatOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
    
    lastMessageId = 0;
    loadMessages().then(() => {
        loadMembers();
        // Auto-show member list
        document.getElementById('memberList').classList.add('open');
    });
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
    if (!currentRoomId) return Promise.resolve();
    
    var loadingEl = document.getElementById('chatLoading');
    var messagesEl = document.getElementById('groupChatMessages');
    if (!loadingEl || !messagesEl) return Promise.resolve();
    
    loadingEl.style.display = 'flex';
    messagesEl.innerHTML = '';
    messagesEl.appendChild(loadingEl);
    
    return fetch(`api/get_messages.php?room_id=${currentRoomId}`)
        .then(res => res.json())
        .then(data => {
            var loadingEl2 = document.getElementById('chatLoading');
            if (loadingEl2) loadingEl2.style.display = 'none';
            if (data.success && data.messages) {
                data.messages.forEach(msg => appendMessage(msg, false));
                if (data.messages.length > 0) {
                    lastMessageId = data.messages[data.messages.length - 1].id;
                }
                scrollToBottom();
            }
        })
        .catch(err => {
            var loadingEl2 = document.getElementById('chatLoading');
            if (loadingEl2) loadingEl2.style.display = 'none';
            console.error('Error loading messages:', err);
        });
}

/**
 * Sends a text message to the current group chat room.
 * Gets input value, sends to server via POST, then reloads messages.
 * Also handles file attachments if any are pending.
 */
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
            // Reload messages to get proper format and avoid duplicates
            loadMessages();
        }
    })
    .catch(err => console.error('Error sending message:', err));
}

function appendMessage(msg, prepend) {
    // Check if message already exists to prevent duplicates
    if (document.querySelector(`[data-message-id="${msg.id}"]`)) {
        return;
    }
    
    // Parse attachments
    let attachmentsHtml = '';
    if (msg.attachments) {
        try {
            const attachments = typeof msg.attachments === 'string' ? JSON.parse(msg.attachments) : msg.attachments;
            if (Array.isArray(attachments) && attachments.length > 0) {
                attachmentsHtml = '<div class="message-attachments">' + attachments.map(file => {
                    if (file.type && file.type.startsWith('image/')) {
                        return `<div class="message-attachment"><img src="${file.path}" alt="${file.name}" onclick="window.open('${file.path}', '_blank')"></div>`;
                    } else {
                        return `<div class="message-attachment"><a href="${file.path}" target="_blank"><i class="bi bi-file-earmark"></i> ${file.name}</a></div>`;
                    }
                }).join('') + '</div>';
            }
        } catch (e) {
            console.error('Error parsing attachments:', e);
        }
    }
    
    const messagesDiv = document.getElementById('groupChatMessages');
    const msgDiv = document.createElement('div');
    msgDiv.className = 'group-message' + (msg.is_own ? ' own' : '');
    msgDiv.dataset.messageId = msg.id;
    msgDiv.innerHTML = `
        <img src="${msg.sender_avatar || 'img/default-avatar.svg'}" alt="" class="group-message-avatar">
        <div class="group-message-content">
            <span class="group-message-sender">${escapeHtml(msg.sender_name)}</span>
            <div class="group-message-bubble">${escapeHtml(msg.message || '')}${attachmentsHtml}</div>
        </div>
        ${msg.is_own ? '<button class="delete-msg-btn" onclick="deleteMessage(' + msg.id + ')" title="Delete"><i class="bi bi-trash"></i></button>' : ''}
    `;
    messagesDiv.appendChild(msgDiv);
}

function deleteMessage(messageId) {
    showConfirm('Delete Message', 'Are you sure you want to delete this message?', function() {
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
                showAlert('Error', data.message || 'Failed to delete message', 'error');
            }
        })
        .catch(err => showAlert('Error', 'Error deleting message', 'error'));
    }, null, 'btn btn-danger');
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

/**
 * Loads and displays all members of the current room.
 * Shows member list with roles (owner/moderator/member) and actions.
 * Only room admins/moderators see action buttons (kick, ban, promote).
 */
function loadMembers() {
    if (!currentRoomId) return;

    fetch(`api/get_room_members.php?room_id=${currentRoomId}`)
        .then(res => res.json())
        .then(data => {
            console.log('Members API response:', data);
            if (data.success && data.members) {
                const container = document.getElementById('memberListContent');
                document.getElementById('groupChatMemberCount').textContent = data.members.length + ' members';
                
                // Show code button for admins
                const codeBtn = document.getElementById('roomCodeBtn');
                if (codeBtn) codeBtn.style.display = data.is_admin ? 'inline-flex' : 'none';
                
                // Show visibility button for admins
                const visBtn = document.getElementById('roomVisibilityBtn');
                if (visBtn) visBtn.style.display = data.is_admin ? 'inline-flex' : 'none';
                
                if (data.members.length === 0) {
                    container.innerHTML = '<p class="text-muted small">No members found</p>';
                    return;
                }
                
                // Separate active members and banned members
                const activeMembers = data.members.filter(m => !m.is_banned);
                const bannedMembers = data.members.filter(m => m.is_banned);
                
                // Show active members
                let html = '';
                activeMembers.forEach(member => {
                    const canManage = data.is_admin && member.role !== 'admin';
                    const actions = canManage 
                        ? '<div class="member-actions">' + 
                          (member.role === 'member' ? '<button class="btn-small btn-warning" onclick="promoteMember(' + member.id + ', \'promote\')">Promote</button> ' : '') +
                          '<button class="btn-small btn-danger" onclick="kickMember(' + member.id + ')">Kick</button> ' +
                          '<button class="btn-small btn-dark" onclick="banMember(' + member.id + ', \'ban\')">Ban</button></div>' 
                        : '';
                    
                    html += '<div class="member-item">' +
                        '<img src="' + (member.profile_image || 'img/default-avatar.svg') + '" class="member-avatar">' +
                        '<div class="member-info">' +
                        '<span class="member-name">' + escapeHtml(member.display_name) + '</span>' +
                        '<span class="member-role">' + (member.role === 'admin' ? '<i class="bi bi-shield-fill"></i> Owner' : member.role === 'moderator' ? '<i class="bi bi-star-fill"></i> Moderator' : 'Member') + '</span>' +
                        '</div>' + actions + '</div>';
                });
                container.innerHTML = html;
                
                // Show banned members section (only for admins)
                if (data.is_admin && bannedMembers.length > 0) {
                    const bannedContainer = document.getElementById('bannedListContent');
                    if (bannedContainer) {
                        bannedContainer.style.display = 'block';
                        let bannedHtml = '<h6 class="mt-3">Banned Users</h6>';
                        bannedMembers.forEach(member => {
                            bannedHtml += '<div class="member-item">' +
                                '<img src="' + (member.profile_image || 'img/default-avatar.svg') + '" class="member-avatar">' +
                                '<div class="member-info">' +
                                '<span class="member-name">' + escapeHtml(member.display_name) + '</span>' +
                                '<span class="member-role text-danger">Banned</span>' +
                                '</div>' +
                                '<div class="member-actions"><button class="btn-small btn-success" onclick="banMember(' + member.id + ', \'unban\')">Unban</button></div>' +
                                '</div>';
                        });
                        bannedContainer.innerHTML = bannedHtml;
                    }
                }
            } else {
                document.getElementById('memberListContent').innerHTML = '<p class="text-danger small">Error loading members</p>';
            }
        })
        .catch(err => {
            console.error('Error loading members:', err);
            document.getElementById('memberListContent').innerHTML = '<p class="text-danger small">Error loading members</p>';
        });
}

function kickMember(userId) {
    showConfirm('Kick Member', 'Are you sure you want to kick this member from the group?', function() {
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
                showAlert('Error', data.message || 'Failed to kick member', 'error');
            }
        })
        .catch(err => showAlert('Error', 'Error kicking member', 'error'));
    }, null, 'btn btn-danger');
}

function banMember(userId, action) {
    const title = action === 'ban' ? 'Ban User' : 'Unban User';
    const message = action === 'ban' ? 'This will prevent the user from joining this room. Are you sure?' : 'This will allow the user to rejoin this room. Are you sure?';
    showConfirm(title, message, function() {
        fetch('api/ban_member.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `room_id=${currentRoomId}&user_id=${userId}&action=${action}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Small delay to ensure DB is updated
                setTimeout(loadMembers, 100);
                showAlert('Success', data.message, 'success');
            } else {
                showAlert('Error', data.message || 'Failed to update ban status', 'error');
            }
        })
        .catch(err => showAlert('Error', 'Error updating ban status', 'error'));
    });
}

function promoteMember(userId, action) {
    const title = action === 'promote' ? 'Promote to Moderator' : 'Demote to Member';
    showConfirm(title, action === 'promote' ? 'Make this user a moderator?' : 'Remove moderator privileges?', function() {
        fetch('api/manage_moderator.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `room_id=${currentRoomId}&user_id=${userId}&action=${action}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadMembers();
            } else {
                showAlert('Error', data.message || 'Failed to update role', 'error');
            }
        })
        .catch(err => showAlert('Error', 'Error updating role', 'error'));
    });
}

function showRoomCodeSettings() {
    const currentCode = document.getElementById('groupChatRoomName').dataset.code || '';
    const modalHtml = `
        <div class="modal fade show" id="roomCodeModal" style="display: block;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(-60deg, #B148FF 0%, #F4369E 100%); color: white;">
                        <h5 class="modal-title">Change Chat Code</h5>
                        <button type="button" class="btn-close btn-close-white" onclick="document.getElementById('roomCodeModal').remove(); document.querySelector('.modal-backdrop')?.remove();"></button>
                    </div>
                    <div class="modal-body">
                        <p>Enter a new chat code (min 4 characters):</p>
                        <input type="text" id="newRoomCode" class="form-control" value="${currentCode}" maxlength="8" placeholder="Enter code">
                        <p id="roomCodeError" class="text-danger mt-2" style="display: none;"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('roomCodeModal').remove(); document.querySelector('.modal-backdrop')?.remove();">Cancel</button>
                        <button type="button" class="btn btn-primary" style="background: linear-gradient(-60deg, #B148FF 0%, #F4369E 100%); border: none;" onclick="submitNewRoomCode()">Save</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" style="display: block;"></div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    document.getElementById('newRoomCode').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') submitNewRoomCode();
    });
}

function submitNewRoomCode() {
    const newCode = document.getElementById('newRoomCode').value.trim();
    const errorEl = document.getElementById('roomCodeError');
    
    if (!newCode) {
        errorEl.textContent = 'Please enter a code';
        errorEl.style.display = 'block';
        return;
    }
    if (newCode.length < 4) {
        errorEl.textContent = 'Chat code must be at least 4 characters';
        errorEl.style.display = 'block';
        return;
    }
    
    document.getElementById('roomCodeModal').remove();
    document.querySelector('.modal-backdrop')?.remove();
    
    fetch('api/update_room_code.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `room_id=${currentRoomId}&chat_code=${encodeURIComponent(newCode)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showAlert('Success', 'Chat code updated!', 'success');
        } else {
            showAlert('Error', data.message || 'Failed to update chat code', 'error');
        }
    })
    .catch(err => showAlert('Error', 'Error updating chat code', 'error'));
}

function showRoomVisibilitySettings() {
    showConfirm('Change Room Visibility', 'Make this room Public or Private?', function() {
        // Show another prompt for choice
        const choice = confirm('Click OK to make room PUBLIC, Cancel to make it PRIVATE');
        const isPrivate = choice ? 0 : 1;
        
        fetch('api/toggle_room_visibility.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `room_id=${currentRoomId}&is_private=${isPrivate}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert('Success', data.message, 'success');
            } else {
                showAlert('Error', data.message || 'Failed to update visibility', 'error');
            }
        })
        .catch(err => showAlert('Error', 'Error updating visibility', 'error'));
    }, 'Make Public', 'Make Private');
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

// File handling for group chat
let groupPendingFiles = [];

function handleGroupFileUpload(input) {
    const files = input.files;
    if (!files || files.length === 0) return;
    
    if (groupPendingFiles.length + files.length > 4) {
        showAlert('Warning', 'Maximum 4 files allowed', 'warning');
        return;
    }
    
    // Store file objects for preview and upload
    Array.from(files).forEach(file => {
        groupPendingFiles.push({
            file: file,
            name: file.name,
            size: file.size,
            type: file.type,
            preview: URL.createObjectURL(file)
        });
        updateGroupFilePreview();
    });
    
    input.value = '';
}

function updateGroupFilePreview() {
    const container = document.getElementById('groupFilePreview');
    if (groupPendingFiles.length === 0) {
        container.style.display = 'none';
        container.innerHTML = '';
        return;
    }
    
    container.style.display = 'flex';
    container.innerHTML = groupPendingFiles.map((file, index) => `
        <div class="file-preview-item">
            ${file.type.startsWith('image/') 
                ? `<img src="${file.preview}" alt="${file.name}">`
                : `<div style="display:flex;align-items:center;justify-content:center;height:100%;font-size:10px;padding:5px;text-align:center;word-break:break-all;">${file.name}</div>`}
            <button class="remove-file" onclick="removeGroupFile(${index})">×</button>
        </div>
    `).join('');
}

function removeGroupFile(index) {
    URL.revokeObjectURL(groupPendingFiles[index].preview);
    groupPendingFiles.splice(index, 1);
    updateGroupFilePreview();
}

// Update sendGroupMessage to include files
const originalSendGroupMessage = sendGroupMessage;
sendGroupMessage = function() {
    const input = document.getElementById('groupMessageInput');
    const message = input.value.trim();
    
    if (message || groupPendingFiles.length > 0) {
        const formData = new FormData();
        formData.append('room_id', currentRoomId);
        formData.append('message', message);
        
        if (groupPendingFiles.length > 0) {
            // Add actual file objects to FormData
            groupPendingFiles.forEach(f => {
                formData.append('files[]', f.file);
            });
            
            fetch('api/upload_file.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(uploadData => {
                console.log('Upload response:', uploadData);
                if (uploadData.success && uploadData.files && uploadData.files.length > 0) {
                    console.log('Files uploaded:', uploadData.files);
                    const attachments = JSON.stringify(uploadData.files);
                    sendMessageWithAttachments(currentRoomId, null, message, attachments);
                } else if (message) {
                    console.log('No files, sending message only');
                    sendMessageWithAttachments(currentRoomId, null, message, null);
                } else {
                    showAlert('Error', uploadData.message || 'Failed to upload files', 'error');
                }
                groupPendingFiles = [];
                updateGroupFilePreview();
            })
            .catch(err => {
                console.error('Upload error:', err);
                if (message) sendMessageWithAttachments(currentRoomId, null, message, null);
                groupPendingFiles = [];
                updateGroupFilePreview();
            });
        } else {
            sendMessageWithAttachments(currentRoomId, null, message, null);
        }
        
        input.value = '';
    }
};

function sendMessageWithAttachments(roomId, receiverId, message, attachments) {
    const formData = new FormData();
    if (roomId) formData.append('room_id', roomId);
    if (receiverId) formData.append('receiver_id', receiverId);
    formData.append('message', message);
    if (attachments) formData.append('attachments', attachments);
    
    fetch('api/send_message.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (roomId) {
                loadMessages();
            }
        } else {
            showAlert('Error', data.message || 'Failed to send message', 'error');
        }
    })
    .catch(err => console.error('Error sending message:', err));
}

// Close modal on overlay click (not on modal click)
document.getElementById('groupChatOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeGroupChat();
});

// Emoji picker
const emojiData = {
    smileys: ['😀','😃','😄','😁','😆','😅','🤣','😂','🙂','🙃','😉','😊','😇','🥰','😍','🤩','😘','😗','☺️','😚','😙','😋','😛','😜','🤪','😝','🤑','🤗','🤭','🤫','🤔','🤐','🤨','😐','😑','😶','😏','😒','🙄','😬','🤥','😌','😔','😪','🤤','😴','😷','🤒','🤕','🤢','🤮','🤧','🥵','🥶','🥴','😵','🤯','🤠','🥳','😎','🤓','🧐','😕','😟','🙁','☹️','😮','😯','😲','😳','🥺','😦','😧','😨','😰','😥','😢','😭','😱','😖','😣','😞','😓','😩','😫','🥱','😤','😡','😠','🤬','😈','👿','💀','☠️','💩','🤡','👹','👺','👻','👽','👾','🤖'],
    animals: ['🐶','🐱','🐭','🐹','🐰','🦊','🐻','🐼','🐨','🐯','🦁','🐮','🐷','🐸','🐵','🙈','🙉','🙊','🐒','🐔','🐧','🐦','🐤','🐣','🐥','🦆','🦅','🦉','🦇','🐺','🐗','🐴','🦄','🐝','🐛','🦋','🐌','🐞','🐜','🦟','🦗','🕷️','🦂','🐢','🐍','🦎','🦖','🦕','🐙','🦑','🦐','🦞','🦀','🐡','🐠','🐟','🐬','🐳','🐋','🦈','🐊','🐅','🐆','🦓','🦍','🦧','🐘','🦛','🦏','🐪','🐫','🦒','🦘','🐃','🐂','🐄','🐎','🐖','🐏','🐑','🦙','🐐','🦌','🐕','🐩','🦮','🐕‍🦺','🐈','🐓','🦃','🦚','🦜','🦢','🦩','🕊️','🐇','🦝','🦨','🦡','🦫','🦦','🦥','🐁','🐀','🐿️','🦔'],
    food: ['🍎','🍐','🍊','🍋','🍌','🍉','🍇','🍓','🫐','🍈','🍒','🍑','🥭','🍍','🥥','🥝','🍅','🍆','🥑','🥦','🥬','🥒','🌶️','🫑','🌽','🥕','🧄','🧅','🥔','🍠','🥐','🥯','🍞','🥖','🥨','🧀','🥚','🍳','🧈','🥞','🧇','🥓','🥩','🍗','🍖','🦴','🌭','🍔','🍟','🍕','🫓','🥪','🥙','🧆','🌮','🌯','🫔','🥗','🥘','🫕','🍝','🍜','🍲','🍛','🍣','🍱','🥟','🦪','🍤','🍙','🍚','🍘','🍥','🥠','🥮','🍢','🍡','🍧','🍨','🍦','🥧','🧁','🍰','🎂','🍮','🍭','🍬','🍫','🍿','🍩','🍪','🌰','🥜','🍯','🥛','🍼','☕','🫖','🍵','🧃','🥤','🧋','🍶','🍺','🍻','🥂','🍷','🥃','🍸','🍹','🧉','🍾','🧊'],
    activities: ['⚽','🏀','🏈','⚾','🥎','🎾','🏐','🏉','🥏','🎱','🪀','🏓','🏸','🏒','🏑','🥍','🏏','🪃','🥅','⛳','🪁','🏹','🎣','🤿','🥊','🥋','🎽','🛹','🛼','🛷','⛸️','🥌','🎿','⛷️','🏂','🪂','🏋️','🤼','🤸','🤺','⛹️','🤾','🏌️','🏇','🧘','🏄','🏊','🤽','🚣','🧗','🚴','🚵','🎖️','🏆','🥇','🥈','🥉','🎪','🤹','🎭','🎨','🎬','🎤','🎧','🎼','🎹','🥁','🪘','🎷','🎺','🪗','🎸','🪕','🎻','🎲','♟️','🎯','🎳','🎮','🎰'],
    travel: ['🚗','🚕','🚙','🚌','🚎','🏎️','🚓','🚑','🚒','🚐','🛻','🚚','🚛','🚜','🏍️','🛵','🚲','🛴','🛺','🚨','🚔','🚍','🚘','🚖','🚡','🚠','🚟','🚃','🚋','🚞','🚝','🚄','🚅','🚈','🚂','🚆','🚇','🚊','🚉','✈️','🛫','🛬','🛩️','💺','🛰️','🚀','🛸','🚁','🛶','⛵','🚤','🛥️','🛳️','⛴️','🚢','⚓','🪝','⛽','🚧','🚦','🚥','🗺️','🗿','🗽','🗼','🏰','🏯','🏟️','🎡','🎢','🎠','⛲','⛱️','🏖️','🏝️','🏜️','🌋','⛰️','🏔️','🗻','🏕️','⛺','🛖','🏠','🏡','🏘️','🏚️','🏗️','🏭','🏢','🏬','🏣','🏤','🏥','🏦','🏨','🏪','🏫','🏩','💒','🏛️','⛪','🕌','🕍','🛕','🕋','⛩️'],
    objects: ['⌚','📱','📲','💻','⌨️','🖥️','🖨️','🖱️','🖲️','🕹️','🗜️','💽','💾','💿','📀','📼','📷','📸','📹','🎥','📽️','🎞️','📞','☎️','📟','📠','📺','📻','🎙️','🎚️','🎛️','🧭','⏱️','⏲️','⏰','🕰️','⌛','⏳','📡','🔋','🔌','💡','🔦','🕯️','🪔','🧯','🛢️','💸','💵','💴','💶','💷','💰','💳','💎','⚖️','🧰','🔧','🔨','⚒️','🛠️','⛏️','🪚','🔩','⚙️','🪤','🧱','⛓️','🧲','🔫','💣','🧨','🪓','🔪','🗡️','⚔️','🛡️','🚬','⚰️','🪦','⚱️','🏺','🔮','📿','🧿','💈','⚗️','🔭','🔬','🕳️','🩹','🩺','💊','💉','🩸','🧬','🦠','🧫','🧪','🌡️','🧹','🪠','🧺','🧻','🚽','🚰','🚿','🛁','🛀','🧼','🪥','🪒','🧽','🪣','🧴','🛎️','🔑','🗝️','🚪','🪑','🛋️','🛏️','🛌','🧸','🪆','🖼️','🪞','🪟','🛍️','🛒','🎁','🎈','🎏','🎀','🪄','🪅','🎊','🎉','🎎','🏮','灯笼','🧧','✉️','📩','📨','📧','💌','📥','📤','📦','🏷️','📪','📫','📬','📭','📮','📯','📜','📃','📄','📑','📊','📈','📉','📆','📅','🗓️','📇','🗃️','🗳️','🗄️','📋','📁','📂','🗂️','🗞️','📰','📓','📔','📒','📕','📗','📘','📙','📚','📖','🔖','🧷','🔗','📎','🖇️','📐','📏','🧮','📌','📍','✂️','🖊️','🖋️','✒️','🖌️','🖍️','📝','✏️','🔍','🔎','🔏','🔐','🔒','🔓'],
    symbols: ['❤️','🧡','💛','💚','💙','💜','🖤','🤍','🤎','💔','❣️','💕','💞','💓','💗','💖','💘','💝','💟','☮️','✝️','☪️','🕉️','☸️','✡️','🔯','🕎','☯️','☦️','🛐','⛎','♈','♉','♊','♋','♌','♍','♎','♏','♐','♑','♒','♓','🆔','⚛️','🉑','☢️','☣️','📴','📳','🈶','🈚','🈸','🈺','🈷️','✴️','🆚','💮','🉐','㊙️','㊗️','🈴','🈵','🈹','🈲','🅰️','🅱️','🆎','🆑','🅾️','🆘','❌','⭕','🛑','⛔','📛','🚫','💯','💢','♨️','🚷','🚯','🚳','🚱','🔞','📵','🚭','❗','❕','❓','❔','‼️','⁉️','🔅','🔆','〽️','⚠️','🚸','🔱','⚜️','🔰','♻️','✅','🈯','💹','❇️','✳️','❎','🌐','💠','Ⓜ️','🌀','💤','🏧','🚾','♿','🅿️','🛗','🈳','🈂️','🛂','🛃','🛄','🛅','🚹','🚺','🚼','⚧️','🚻','🚮','🎦','📶','🈁','🔣','ℹ️','🔤','🔡','🔠','🆖','🆗','🆙','🆒','🆕','🆓','0️⃣','1️⃣','2️⃣','3️⃣','4️⃣','5️⃣','6️⃣','7️⃣','8️⃣','9️⃣','🔟','🔢','#️⃣','*️⃣','⏏️','▶️','⏸️','⏯️','⏹️','⏺️','⏭️','⏮️','⏩','⏪','⏫','⏬','◀️','🔼','🔽','➡️','⬅️','⬆️','⬇️','↗️','↘️','↙️','↖️','↕️','↔️','↪️','↩️','⤴️','⤵️','🔀','🔁','🔂','🔄','🔃','🎵','🎶','➕','➖','➗','✖️','♾️','💲','💱','™️','©️','®️','👁️‍🗨️','🔚','🔙','🔛','🔝','🔜','〰️','➰','✔️','☑️','🔘','🔴','🟠','🟡','🟢','🔵','🟣','⚫','⚪','🟤','🔺','🔻','🔸','🔹','🔶','🔷','🔳','🔲','▪️','▫️','◾','◽','◼️','◻️','🟥','🟧','🟨','🟩','🟦','🟪','⬛','⬜','🟫','🔈','🔇','🔉','🔊','🔔','🔕','📣','📢','💬','💭','♠️','♣️','♥️','♦️','🃏','🎴','🀄','🕐','🕑','🕒','🕓','🕔','🕕','🕖','🕗','🕘','🕙','🕚','🕛','🕜','🕝','🕞','🕟','🕠','🕡','🕢','🕣','🕤','🕥','🕦','🕧']
};

let currentGroupEmojiCategory = 'smileys';

function toggleGroupEmojiPicker() {
    const picker = document.getElementById('groupEmojiPicker');
    if (picker.style.display === 'none') {
        picker.style.display = 'block';
        showGroupEmojiCategory(currentGroupEmojiCategory);
    } else {
        picker.style.display = 'none';
    }
}

function showGroupEmojiCategory(cat) {
    currentGroupEmojiCategory = cat;
    document.querySelectorAll('.emoji-cat-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelector(`[data-cat="${cat}"]`).classList.add('active');
    const grid = document.getElementById('groupEmojiGrid');
    grid.innerHTML = emojiData[cat].map(emoji => 
        `<button class="emoji-btn" onclick="insertGroupEmoji('${emoji}')">${emoji}</button>`
    ).join('');
}

function filterGroupEmojis(search) {
    const allEmojis = Object.values(emojiData).flat();
    const filtered = allEmojis.filter(() => true); // Show all - could add keyword matching
    const grid = document.getElementById('groupEmojiGrid');
    if (search === '') {
        showGroupEmojiCategory(currentGroupEmojiCategory);
    } else {
        grid.innerHTML = allEmojis.slice(0, 64).map(emoji => 
            `<button class="emoji-btn" onclick="insertGroupEmoji('${emoji}')">${emoji}</button>`
        ).join('');
    }
}

function insertGroupEmoji(emoji) {
    const input = document.getElementById('groupMessageInput');
    input.value += emoji;
    input.focus();
    toggleGroupEmojiPicker();
}
</script>
