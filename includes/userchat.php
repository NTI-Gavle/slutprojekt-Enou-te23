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
        <input type="file" id="userChatFileInput" multiple accept="image/*,.pdf,.txt,.doc,.docx" style="display: none;" onchange="handleUserChatFileUpload(this)">
        <button class="input-btn" title="Attach Files" onclick="document.getElementById('userChatFileInput').click()">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M15 21H9C6.17157 21 4.75736 21 3.87868 20.1213C3 19.2426 3 17.8284 3 15M21 15C21 17.8284 21 19.2426 20.1213 20.1213C19.8215 20.4211 19.4594 20.6186 19 20.7487" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M12 16V3M12 3L16 7.375M12 3L8 7.375" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
        <button class="input-btn" title="Emoji" onclick="toggleUserEmojiPicker()">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                <path d="M8 14s1.5 2 4 2 4-2 4-2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <circle cx="9" cy="10" r="1" fill="currentColor"/>
                <circle cx="15" cy="10" r="1" fill="currentColor"/>
            </svg>
        </button>
        <input type="text" class="message-input" placeholder="Type a message..." id="messageInput">
        <button class="input-btn send-btn" title="Send" onclick="sendMessage()">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M10.3009 13.6949L20.102 3.89742M10.5795 14.1355L12.8019 18.5804C13.339 19.6545 13.6075 20.1916 13.9458 20.3356C14.2394 20.4606 14.575 20.4379 14.8492 20.2747C15.1651 20.0866 15.3591 19.5183 15.7472 18.3818L19.9463 6.08434C20.2845 5.09409 20.4535 4.59896 20.3378 4.27142C20.2371 3.98648 20.013 3.76234 19.7281 3.66167C19.4005 3.54595 18.9054 3.71502 17.9151 4.05315L5.61763 8.2523C4.48114 8.64037 3.91289 8.83441 3.72478 9.15032C3.56153 9.42447 3.53891 9.76007 3.66389 10.0536C3.80791 10.3919 4.34498 10.6605 5.41912 11.1975L9.86397 13.42C10.041 13.5085 10.1295 13.5527 10.2061 13.6118C10.2742 13.6643 10.3352 13.7253 10.3876 13.7933C10.4468 13.87 10.491 13.9585 10.5795 14.1355Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
    </div>
<div id="userChatFilePreview" class="file-preview-container" style="display: none;"></div>
        
        <div id="userEmojiPicker" class="emoji-picker" style="display: none;">
            <div class="emoji-picker-header">
                <input type="text" id="userEmojiSearch" class="emoji-search" placeholder="Search emojis..." oninput="filterUserEmojis(this.value)">
                <button class="emoji-close" onclick="toggleUserEmojiPicker()">&times;</button>
            </div>
            <div class="emoji-categories">
                <button class="emoji-cat-btn active" data-cat="smileys" onclick="showUserEmojiCategory('smileys')">😀</button>
                <button class="emoji-cat-btn" data-cat="animals" onclick="showUserEmojiCategory('animals')">🐶</button>
                <button class="emoji-cat-btn" data-cat="food" onclick="showUserEmojiCategory('food')">🍔</button>
                <button class="emoji-cat-btn" data-cat="activities" onclick="showUserEmojiCategory('activities')">⚽</button>
                <button class="emoji-cat-btn" data-cat="travel" onclick="showUserEmojiCategory('travel')">🚗</button>
                <button class="emoji-cat-btn" data-cat="objects" onclick="showUserEmojiCategory('objects')">💡</button>
                <button class="emoji-cat-btn" data-cat="symbols" onclick="showUserEmojiCategory('symbols')">❤️</button>
            </div>
            <div class="emoji-grid" id="userEmojiGrid"></div>
        </div>
</div>

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
                markMessagesAsRead(userId);
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
                sender_name: '<?= htmlspecialchars($_SESSION['display_name'] ?? 'You') ?>',
                sender_avatar: '<?= getValidProfileImage($_SESSION['profile_image'] ?? null) ?>',
                message: text,
                is_own: true
            });
            document.getElementById('userChatMessages').scrollTop = document.getElementById('userChatMessages').scrollHeight;
        } else {
            showAlert('Error', data.message || 'Failed to send message', 'error');
        }
    })
    .catch(err => console.error('Error sending message:', err));
}

function appendPrivateMessage(msg) {
    // Check if message already exists to prevent duplicates
    if (document.querySelector(`[data-message-id="${msg.id}"]`)) {
        return; // Message already displayed
    }
    
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

// File handling for private chat
let userChatPendingFiles = [];

function handleUserChatFileUpload(input) {
    const files = input.files;
    if (!files || files.length === 0) return;
    
    if (userChatPendingFiles.length + files.length > 4) {
        showAlert('Warning', 'Maximum 4 files allowed', 'warning');
        return;
    }
    
    Array.from(files).forEach(file => {
        userChatPendingFiles.push({
            file: file,
            name: file.name,
            size: file.size,
            type: file.type,
            preview: URL.createObjectURL(file)
        });
        updateUserChatFilePreview();
    });
    
    input.value = '';
}

function updateUserChatFilePreview() {
    const container = document.getElementById('userChatFilePreview');
    if (userChatPendingFiles.length === 0) {
        container.style.display = 'none';
        container.innerHTML = '';
        return;
    }
    
    container.style.display = 'flex';
    container.innerHTML = userChatPendingFiles.map((file, index) => `
        <div class="file-preview-item">
            ${file.type.startsWith('image/') 
                ? `<img src="${file.preview}" alt="${file.name}">`
                : `<div style="display:flex;align-items:center;justify-content:center;height:100%;font-size:10px;padding:5px;text-align:center;word-break:break-all;">${file.name}</div>`}
            <button class="remove-file" onclick="removeUserChatFile(${index})">×</button>
        </div>
    `).join('');
}

function removeUserChatFile(index) {
    URL.revokeObjectURL(userChatPendingFiles[index].preview);
    userChatPendingFiles.splice(index, 1);
    updateUserChatFilePreview();
}

// Update sendMessage to include files
const originalSendMessage = sendMessage;
sendMessage = function() {
    const input = document.getElementById('messageInput');
    const message = input.value.trim();
    
    if (message || userChatPendingFiles.length > 0) {
        if (userChatPendingFiles.length > 0) {
            const formData = new FormData();
            formData.append('receiver_id', currentChatUserId);
            formData.append('message', message);
            
            // Add actual file objects
            userChatPendingFiles.forEach(f => {
                formData.append('files[]', f.file);
            });
            
            fetch('api/upload_file.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(uploadData => {
                console.log('Private chat upload response:', uploadData);
                if (uploadData.success && uploadData.files && uploadData.files.length > 0) {
                    const attachments = JSON.stringify(uploadData.files);
                    sendPrivateMessageWithAttachments(currentChatUserId, message, attachments);
                } else if (message) {
                    sendPrivateMessageWithAttachments(currentChatUserId, message, null);
                } else {
                    showAlert('Error', uploadData.message || 'Failed to upload files', 'error');
                }
                userChatPendingFiles = [];
                updateUserChatFilePreview();
            })
            .catch(err => {
                console.error('Upload error:', err);
                if (message) sendPrivateMessageWithAttachments(currentChatUserId, message, null);
                userChatPendingFiles = [];
                updateUserChatFilePreview();
            });
        } else {
            sendPrivateMessageWithAttachments(currentChatUserId, message, null);
        }
        
        input.value = '';
    }
};

function sendPrivateMessageWithAttachments(receiverId, message, attachments) {
    const formData = new FormData();
    formData.append('receiver_id', receiverId);
    formData.append('message', message);
    if (attachments) formData.append('attachments', attachments);
    
    fetch('api/send_message.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadPrivateChat();
        } else {
            showAlert('Error', data.message || 'Failed to send message', 'error');
        }
    })
    .catch(err => console.error('Error sending message:', err));
}

function loadPrivateChat() {
    if (!currentChatUserId) return;
    
    fetch(`api/get_messages.php?user_id=${currentChatUserId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.messages) {
                const container = document.getElementById('userChatMessages');
                container.innerHTML = '';
                data.messages.forEach(msg => appendPrivateMessage(msg));
                if (data.messages.length > 0) {
                    lastPrivateMessageId = data.messages[data.messages.length - 1].id;
                }
                container.scrollTop = container.scrollHeight;
            }
        })
        .catch(err => console.error('Error loading private chat:', err));
}

// Update appendPrivateMessage to show attachments
const originalAppendPrivateMessage = appendPrivateMessage;
appendPrivateMessage = function(msg) {
    if (document.querySelector(`[data-message-id="${msg.id}"]`)) return;
    
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
    
    const messagesDiv = document.getElementById('userChatMessages');
    const msgDiv = document.createElement('div');
    msgDiv.className = 'message' + (msg.is_own ? ' outgoing' : ' incoming');
    msgDiv.dataset.messageId = msg.id;
    msgDiv.innerHTML = `
        <img src="${msg.sender_avatar || 'img/default-avatar.svg'}" alt="" class="message-avatar">
        <div class="message-bubble">
            ${!msg.is_own ? '<span class="message-user">' + escapeHtml(msg.sender_name) + '</span>' : ''}
            <p class="message-text">${escapeHtml(msg.message || '')}${attachmentsHtml}</p>
        </div>
        ${msg.is_own ? '<button class="delete-msg-btn" onclick="deletePrivateMessage(' + msg.id + ')" title="Delete"><i class="bi bi-trash"></i></button>' : ''}
    `;
    messagesDiv.appendChild(msgDiv);
};

document.getElementById('messageInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') sendMessage();
});

// Emoji picker functions
const userEmojiData = {
    smileys: ['😀','😃','😄','😁','😆','😅','🤣','😂','🙂','🙃','😉','😊','😇','🥰','😍','🤩','😘','😗','☺️','😚','😙','😋','😛','😜','🤪','😝','🤑','🤗','🤭','🤫','🤔','🤐','🤨','😐','😑','😶','😏','😒','🙄','😬','🤥','😌','😔','😪','🤤','😴','😷','🤒','🤕','🤢','🤮','🤧','🥵','🥶','🥴','😵','🤯','🤠','🥳','😎','🤓','🧐','😕','😟','🙁','☹️','😮','😯','😲','😳','🥺','😦','😧','😨','😰','😥','😢','😭','😱','😖','😣','😞','😓','😩','😫','🥱','😤','😡','😠','🤬','😈','👿','💀','☠️','💩','🤡','👹','👺','👻','👽','👾','🤖'],
    animals: ['🐶','🐱','🐭','🐹','🐰','🦊','🐻','🐼','🐨','🐯','🦁','🐮','🐷','🐸','🐵','🙈','🙉','🙊','🐒','🐔','🐧','🐦','🐤','🐣','🐥','🦆','🦅','🦉','🦇','🐺','🐗','🐴','🦄','🐝','🐛','🦋','🐌','🐞','🐜','🦟','🦗','🕷️','🦂','🐢','🐍','🦎','🦖','🦕','🐙','🦑','🦐','🦞','🦀','🐡','🐠','🐟','🐬','🐳','🐋','🦈','🐊','🐅','🐆','🦓','🦍','🦧','🐘','🦛','🦏','🐪','🐫','🦒','🦘','🐃','🐂','🐄','🐎','🐖','🐏','🐑','🦙','🐐','🦌','🐕','🐩','🦮','🐕‍🦺','🐈','🐓','🦃','🦚','🦜','🦢','🦩','🕊️','🐇','🦝','🦨','🦡','🦫','🦦','🦥','🐁','🐀','🐿️','🦔'],
    food: ['🍎','🍐','🍊','🍋','🍌','🍉','🍇','🍓','🫐','🍈','🍒','🍑','🥭','🍍','🥥','🥝','🍅','🍆','🥑','🥦','🥬','🥒','🌶️','🫑','🌽','🥕','🧄','🧅','🥔','🍠','🥐','🥯','🍞','🥖','🥨','🧀','🥚','🍳','🧈','🥞','🧇','🥓','🥩','🍗','🍖','🦴','🌭','🍔','🍟','🍕','🫓','🥪','🥙','🧆','🌮','🌯','🫔','🥗','🥘','🫕','🍝','🍜','🍲','🍛','🍣','🍱','🥟','🦪','🍤','🍙','🍚','🍘','🍥','🥠','🥮','🍢','🍡','🍧','🍨','🍦','🥧','🧁','🍰','🎂','🍮','🍭','🍬','🍫','🍿','🍩','🍪','🌰','🥜','🍯','🥛','🍼','☕','🫖','🍵','🧃','🥤','🧋','🍶','🍺','🍻','🥂','🍷','🥃','🍸','🍹','🧉','🍾','🧊'],
    activities: ['⚽','🏀','🏈','⚾','🥎','🎾','🏐','🏉','🥏','🎱','🪀','🏓','🏸','🏒','🏑','🥍','🏏','🪃','🥅','⛳','🪁','🏹','🎣','🤿','🥊','🥋','🎽','🛹','🛼','🛷','⛸️','🥌','🎿','⛷️','🏂','🪂','🏋️','🤼','🤸','🤺','⛹️','🤾','🏌️','🏇','🧘','🏄','🏊','🤽','🚣','🧗','🚴','🚵','🎖️','🏆','🥇','🥈','🥉','🎪','🤹','🎭','🎨','🎬','🎤','🎧','🎼','🎹','🥁','🪘','🎷','🎺','🪗','🎸','🪕','🎻','🎲','♟️','🎯','🎳','🎮','🎰'],
    travel: ['🚗','🚕','🚙','🚌','🚎','🏎️','🚓','🚑','🚒','🚐','🛻','🚚','🚛','🚜','🏍️','🛵','🚲','🛴','🛺','🚨','🚔','🚍','🚘','🚖','🚡','🚠','🚟','🚃','🚋','🚞','🚝','🚄','🚅','🚈','🚂','🚆','🚇','🚊','🚉','✈️','🛫','🛬','🛩️','💺','🛰️','🚀','🛸','🚁','🛶','⛵','🚤','🛥️','🛳️','⛴️','🚢','⚓','🪝','⛽','🚧','🚦','🚥','🗺️','🗿','🗽','🗼','🏰','🏯','🏟️','🎡','🎢','🎠','⛲','⛱️','🏖️','🏝️','🏜️','🌋','⛰️','🏔️','🗻','🏕️','⛺','🛖','🏠','🏡','🏘️','🏚️','🏗️','🏭','🏢','🏬','🏣','🏤','🏥','🏦','🏨','🏪','🏫','🏩','💒','🏛️','⛪','🕌','🕍','🛕','🕋','⛩️'],
    objects: ['⌚','📱','📲','💻','⌨️','🖥️','🖨️','🖱️','🖲️','🕹️','🗜️','💽','💾','💿','📀','📼','📷','📸','📹','🎥','📽️','🎞️','📞','☎️','📟','📠','📺','📻','🎙️','🎚️','🎛️','🧭','⏱️','⏲️','⏰','🕰️','⌛','⏳','📡','🔋','🔌','💡','🔦','🕯️','🪔','🧯','🛢️','💸','💵','💴','💶','💷','💰','💳','💎','⚖️','🧰','🔧','🔨','⚒️','🛠️','⛏️','🪚','🔩','⚙️','🪤','🧱','⛓️','🧲','🔫','💣','🧨','🪓','🔪','🗡️','⚔️','🛡️','🚬','⚰️','🪦','⚱️','🏺','🔮','📿','🧿','💈','⚗️','🔭','🔬','🕳️','🩹','🩺','💊','💉','🩸','🧬','🦠','🧫','🧪','🌡️','🧹','🪠','🧺','🧻','🚽','🚰','🚿','🛁','🛀','🧼','🪥','🪒','🧽','🪣','🧴','🛎️','🔑','🗝️','🚪','🪑','🛋️','🛏️','🛌','🧸','🪆','🖼️','🪞','🪟','🛍️','🛒','🎁','🎈','🎏','🎀','🪄','🪅','🎊','🎉','🎎','🏮','🧧','✉️','📩','📨','📧','💌','📥','📤','📦','🏷️','📪','📫','📬','📭','📮','📯','📜','📃','📄','📑','📊','📈','📉','📆','📅','🗓️','📇','🗃️','🗳️','🗄️','📋','📁','📂','🗂️','🗞️','📰','📓','📔','📒','📕','📗','📘','📙','📚','📖','🔖','🧷','🔗','📎','🖇️','📐','📏','🧮','📌','📍','✂️','🖊️','🖋️','✒️','🖌️','🖍️','📝','✏️','🔍','🔎','🔏','🔐','🔒','🔓'],
    symbols: ['❤️','🧡','💛','💚','💙','💜','🖤','🤍','🤎','💔','❣️','💕','💞','💓','💗','💖','💘','💝','💟','☮️','✝️','☪️','🕉️','☸️','✡️','🔯','🕎','☯️','☦️','🛐','⛎','♈','♉','♊','♋','♌','♍','♎','♏','♐','♑','♒','♓','🆔','⚛️','🉑','☢️','☣️','📴','📳','🈶','🈚','🈸','🈺','🈷️','✴️','🆚','💮','🉐','㊙️','㊗️','🈴','🈵','🈹','🈲','🅰️','🅱️','🆎','🆑','🅾️','🆘','❌','⭕','🛑','⛔','📛','🚫','💯','💢','♨️','🚷','🚯','🚳','🚱','🔞','📵','🚭','❗','❕','❓','❔','‼️','⁉️','🔅','🔆','〽️','⚠️','🚸','🔱','⚜️','🔰','♻️','✅','🈯','💹','❇️','✳️','❎','🌐','💠','Ⓜ️','🌀','💤','🏧','🚾','♿','🅿️','🛗','🈳','🈂️','🛂','🛃','🛄','🛅','🚹','🚺','🚼','⚧️','🚻','🚮','🎦','📶','🈁','🔣','ℹ️','🔤','🔡','🔠','🆖','🆗','🆙','🆒','🆕','🆓','0️⃣','1️⃣','2️⃣','3️⃣','4️⃣','5️⃣','6️⃣','7️⃣','8️⃣','9️⃣','🔟','🔢','#️⃣','*️⃣','⏏️','▶️','⏸️','⏯️','⏹️','⏺️','⏭️','⏮️','⏩','⏪','⏫','⏬','◀️','🔼','🔽','➡️','⬅️','⬆️','⬇️','↗️','↘️','↙️','↖️','↕️','↔️','↪️','↩️','⤴️','⤵️','🔀','🔁','🔂','🔄','🔃','🎵','🎶','➕','➖','➗','✖️','♾️','💲','💱','™️','©️','®️','👁️‍🗨️','🔚','🔙','🔛','🔝','🔜','〰️','➰','✔️','☑️','🔘','🔴','🟠','🟡','🟢','🔵','🟣','⚫','⚪','🟤','🔺','🔻','🔸','🔹','🔶','🔷','🔳','🔲','▪️','▫️','◾','◽','◼️','◻️','🟥','🟧','🟨','🟩','🟦','🟪','⬛','⬜','🟫','🔈','🔇','🔉','🔊','🔔','🔕','📣','📢','💬','💭','♠️','♣️','♥️','♦️','🃏','🎴','🀄','🕐','🕑','🕒','🕓','🕔','🕕','🕖','🕗','🕘','🕙','🕚','🕛','🕜','🕝','🕞','🕟','🕠','🕡','🕢','🕣','🕤','🕥','🕦','🕧']
};

let currentUserEmojiCategory = 'smileys';

function toggleUserEmojiPicker() {
    const picker = document.getElementById('userEmojiPicker');
    if (picker.style.display === 'none') {
        picker.style.display = 'block';
        showUserEmojiCategory(currentUserEmojiCategory);
    } else {
        picker.style.display = 'none';
    }
}

function showUserEmojiCategory(cat) {
    currentUserEmojiCategory = cat;
    document.querySelectorAll('#userEmojiPicker .emoji-cat-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelector(`#userEmojiPicker [data-cat="${cat}"]`).classList.add('active');
    const grid = document.getElementById('userEmojiGrid');
    grid.innerHTML = userEmojiData[cat].map(emoji => 
        `<button class="emoji-btn" onclick="insertUserEmoji('${emoji}')">${emoji}</button>`
    ).join('');
}

function filterUserEmojis(search) {
    if (search === '') {
        showUserEmojiCategory(currentUserEmojiCategory);
    } else {
        const allEmojis = Object.values(userEmojiData).flat();
        const grid = document.getElementById('userEmojiGrid');
        grid.innerHTML = allEmojis.slice(0, 64).map(emoji => 
            `<button class="emoji-btn" onclick="insertUserEmoji('${emoji}')">${emoji}</button>`
        ).join('');
    }
}

function insertUserEmoji(emoji) {
    const input = document.getElementById('messageInput');
    input.value += emoji;
    input.focus();
    document.getElementById('userEmojiPicker').style.display = 'none';
}
</script>