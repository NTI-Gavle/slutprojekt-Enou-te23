document.addEventListener('DOMContentLoaded', function() {
    initMobileMenu();
    initFriendActions();
    initSearchForm();
    
    // Update online status every 3 minutes
    if (document.body.classList.contains('logged-in')) {
        updateActivity();
        setInterval(updateActivity, 180000);
        
        // Update friends' online status every minute
        updateFriendStatus();
        setInterval(updateFriendStatus, 60000);
        
        // Check for unread messages every 10 seconds
        checkUnreadMessages();
        setInterval(checkUnreadMessages, 10000);
    }
});

// Unread message notification
function checkUnreadMessages() {
    fetch('api/get_unread_count.php')
        .then(res => res.json())
        .then(data => {
            const badge = document.getElementById('chatBadge');
            const unreadSection = document.getElementById('unreadMessagesSection');
            const unreadList = document.getElementById('unreadMessagesList');
            
            if (data.success && data.unread_count > 0) {
                if (badge) {
                    badge.textContent = data.unread_count > 9 ? '9+' : data.unread_count;
                    badge.style.display = 'flex';
                }
                
                // Show senders in unread section
                if (unreadSection && unreadList && data.senders && data.senders.length > 0) {
                    unreadList.innerHTML = '';
                    data.senders.forEach(sender => {
                        const img = sender.profile_image || 'img/default-avatar.svg';
                        const name = sender.display_name || sender.username;
                        const preview = sender.preview.length > 30 ? sender.preview.substring(0, 30) + '...' : sender.preview;
                        const countText = sender.count > 1 ? ` (${sender.count})` : '';
                        
                        const item = document.createElement('div');
                        item.className = 'unread-message-item';
                        item.onclick = () => openPrivateChat(sender.sender_id);
                        item.innerHTML = `
                            <img src="${img}" alt="${name}">
                            <div>
                                <div class="unread-sender">${escapeHtml(name)}${countText}</div>
                                <div class="unread-preview">${escapeHtml(preview)}</div>
                            </div>
                        `;
                        unreadList.appendChild(item);
                    });
                    unreadSection.style.display = 'block';
                }
            } else {
                if (badge) badge.style.display = 'none';
                if (unreadSection) unreadSection.style.display = 'none';
            }
        })
        .catch(err => console.error('Error checking unread:', err));
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Mark messages as read when viewing
function markMessagesAsRead(userId) {
    fetch('api/mark_read.php?user_id=' + userId)
        .then(res => res.json())
        .then(data => {
            if (data.success) checkUnreadMessages();
        })
        .catch(err => console.error('Error marking read:', err));
}

// Modal helper functions
function showModal(title, message, buttons, onShow) {
    const modal = document.getElementById('customModal');
    document.getElementById('customModalTitle').textContent = title;
    document.getElementById('customModalBody').textContent = message;
    
    // Set higher z-index for the modal
    modal.style.zIndex = '1070';
    
    const footer = document.getElementById('customModalFooter');
    footer.innerHTML = '';
    
    if (buttons) {
        buttons.forEach(btn => {
            const button = document.createElement('button');
            button.className = btn.class || 'btn btn-secondary';
            button.textContent = btn.text;
            button.onclick = btn.onclick;
            footer.appendChild(button);
        });
    } else {
        const okBtn = document.createElement('button');
        okBtn.className = 'btn btn-primary';
        okBtn.textContent = 'OK';
        okBtn.setAttribute('data-bs-dismiss', 'modal');
        footer.appendChild(okBtn);
    }
    
    if (onShow) onShow();
    
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    // Ensure backdrop is on top of group chat
    setTimeout(() => {
        const backdrop = document.querySelector('#customModal + .modal-backdrop');
        if (backdrop) {
            backdrop.style.zIndex = '1065';
        }
        modal.style.zIndex = '1070';
    }, 10);
}

function showAlert(title, message, type) {
    const btnClass = type === 'success' ? 'btn-success' : type === 'error' ? 'btn-danger' : 'btn-primary';
    showModal(title, message, [{ text: 'OK', class: 'btn ' + btnClass }]);
}

function showConfirm(title, message, onConfirm, onCancel, confirmClass) {
    const buttons = [
        { text: 'Cancel', class: 'btn btn-outline-secondary', action: 'dismiss' },
        { text: 'Confirm', class: confirmClass || 'btn btn-primary', action: 'confirm' }
    ];
    
    showModal(title, message, buttons, function() {
        const modal = document.getElementById('customModal');
        const confirmBtn = modal.querySelector('.modal-footer .btn:last-child');
        const cancelBtn = modal.querySelector('.modal-footer .btn:first-child');
        
        cancelBtn.setAttribute('data-bs-dismiss', 'modal');
        
        confirmBtn.onclick = function() {
            modal.classList.remove('show');
            modal.style.display = 'none';
            document.querySelector('.modal-backdrop')?.remove();
            document.body.classList.remove('modal-open');
            if (onConfirm) onConfirm();
        };
    });
}

function updateActivity() {
    fetch('api/update_activity.php', {method: 'POST'})
        .catch(err => console.log('Activity update failed'));
}

function initMobileMenu() {
    const offcanvasElement = document.getElementById('mobileMenu');
    if (offcanvasElement) {
        offcanvasElement.addEventListener('shown.bs.offcanvas', function() {
            document.body.style.overflow = 'hidden';
        });
        offcanvasElement.addEventListener('hidden.bs.offcanvas', function() {
            document.body.style.overflow = '';
        });
    }
}

function initFriendActions() {
    document.querySelectorAll('.friend .btn-chat').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const userId = this.closest('.friend').dataset.userId;
            openPrivateChat(userId);
        });
    });
    
    document.querySelectorAll('.friend .btn-call').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const userId = this.closest('.friend').dataset.userId;
            initiateCall(userId);
        });
    });
    
    document.querySelectorAll('.friend').forEach(item => {
        item.addEventListener('click', function() {
            const userId = this.dataset.userId;
            openPrivateChat(userId);
        });
    });
}

function updateFriendStatus() {
    document.querySelectorAll('.friend').forEach(friendDiv => {
        const userId = friendDiv.dataset.userId;
        if (!userId) return;
        
        fetch(`api/get_user_status.php?user_id=${userId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const dot = friendDiv.querySelector('.status-dot');
                    if (dot) {
                        if (data.is_online) {
                            dot.classList.add('online');
                            dot.classList.remove('offline');
                        } else {
                            dot.classList.add('offline');
                            dot.classList.remove('online');
                        }
                    }
                    const statusText = friendDiv.querySelector('.friend-status');
                    if (statusText) {
                        statusText.textContent = data.is_online ? 'Online' : 'Offline';
                    }
                }
            })
            .catch(err => console.log('Status check failed for user ' + userId));
    });
}

function openPrivateChat(userId) {
    console.log('Opening private chat with user:', userId);
    // Mark messages as read when opening chat
    markMessagesAsRead(userId);
    // This will be handled by the userchat modal
    openUserChatById(userId);
}

function initiateCall(userId) {
    console.log('Initiating call with user:', userId);
    showAlert('Coming Soon', 'Voice calling feature is coming soon!', 'info');
}

function respondToRequest(userId, action) {
    const title = action === 'accept' ? 'Accept Friend Request' : 'Decline Friend Request';
    const message = action === 'accept' ? 'Are you sure you want to accept this friend request?' : 'Are you sure you want to decline this friend request?';
    const confirmClass = action === 'accept' ? 'btn btn-success' : 'btn btn-danger';
    
    showConfirm(title, message, function() {
        fetch('api/respond_friend.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `user_id=${userId}&action=${action}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const url = new URL(window.location.href);
                url.searchParams.set('_', Date.now());
                window.location.href = url.toString();
            } else {
                showAlert('Error', data.message || 'Failed to respond to request', 'error');
            }
        })
        .catch(err => showAlert('Error', 'Error processing request', 'error'));
    }, null, confirmClass);
}

function initSearchForm() {
    const searchInput = document.getElementById('headerSearchInput');
    const resultsDiv = document.getElementById('searchResults');
    let debounceTimeout;

    if (!searchInput || !resultsDiv) return;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimeout);
        const query = this.value.trim();
        if (query.length < 2) {
            resultsDiv.innerHTML = '';
            resultsDiv.style.display = 'none';
            return;
        }
        debounceTimeout = setTimeout(() => {
            fetch(`api/search.php?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    let html = '';
                    if ((data.users && data.users.length) || (data.rooms && data.rooms.length)) {
                        if (data.users && data.users.length) {
                            html += '<div class="search-section"><strong>Users</strong>';
                            data.users.forEach(user => {
                                let actionBtn = '';
                                if (user.friend_status === null) {
                                    actionBtn = `<button class="btn btn-sm btn-primary ms-2" onclick="addFriendFromSearch('${user.username}')">Add</button>`;
                                } else if (user.friend_status === 'pending') {
                                    actionBtn = '<span class="text-muted ms-2" style="font-size:0.8rem;">Pending</span>';
                                } else if (user.friend_status === 'accepted') {
                                    actionBtn = '<span class="text-success ms-2" style="font-size:0.8rem;">Friends</span>';
                                }
                                html += `<div class='search-item' onclick="window.location.href='profile.php?user=${user.id}'">`
                                    + `<img src='${sanitizeHTML(user.profile_image)}' class='avatar me-2' style='width:28px;height:28px;'>`
                                    + `${sanitizeHTML(user.display_name || user.username)}`
                                    + actionBtn
                                    + '</div>';
                            });
                            html += '</div>';
                        }
                        if (data.rooms && data.rooms.length) {
                            html += '<div class="search-section"><strong>Rooms</strong>';
                            data.rooms.forEach(room => {
                                html += `<div class='search-item' onclick="window.location.href='chatroom.php?id=${room.id}'">`
                                    + `<i class='bi bi-chat-dots me-2'></i>`
                                    + `${sanitizeHTML(room.name)}`
                                    + (room.tag ? ` <span class='room-tag ms-2'>${sanitizeHTML(room.tag)}</span>` : '')
                                    + '</div>';
                            });
                            html += '</div>';
                        }
                    } else {
                        html = '<div class="search-item text-muted">No results found</div>';
                    }
                    resultsDiv.innerHTML = html;
                    resultsDiv.style.display = 'block';
                })
                .catch(() => {
                    resultsDiv.innerHTML = '<div class="search-item text-danger">Error loading results</div>';
                    resultsDiv.style.display = 'block';
                });
        }, 200);
    });

    document.addEventListener('click', function(e) {
        if (!resultsDiv.contains(e.target) && e.target !== searchInput) {
            resultsDiv.innerHTML = '';
            resultsDiv.style.display = 'none';
        }
    });
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function sanitizeHTML(str) {
    const temp = document.createElement('div');
    temp.textContent = str;
    return temp.innerHTML;
}

function addFriendAndRefresh(userId, username) {
    fetch('add_friend.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `username=${encodeURIComponent(username)}&ajax=1`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showAlert('Success', 'Friend request sent!', 'success');
            setTimeout(() => {
                const url = new URL(window.location.href);
                url.searchParams.set('_', Date.now());
                window.location.href = url.toString();
            }, 1500);
        } else {
            showAlert('Error', data.message || 'Failed to add friend', 'error');
        }
    })
    .catch(err => showAlert('Error', 'Error sending request', 'error'));
}

function unfriend(userId) {
    showConfirm('Unfriend', 'Are you sure you want to unfriend this user?', function() {
        fetch('api/unfriend.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `user_id=${userId}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const url = new URL(window.location.href);
                url.searchParams.set('_', Date.now());
                window.location.href = url.toString();
            } else {
                showAlert('Error', data.message || 'Failed to unfriend', 'error');
            }
        })
        .catch(err => showAlert('Error', 'Error processing request', 'error'));
    }, null, 'btn btn-danger');
}

function addFriendFromSearch(username) {
    fetch('add_friend.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `username=${encodeURIComponent(username)}&ajax=1`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('headerSearchInput').value = '';
            document.getElementById('searchResults').innerHTML = '';
            showAlert('Success', 'Friend request sent!', 'success');
            setTimeout(() => {
                const url = new URL(window.location.href);
                url.searchParams.set('_', Date.now());
                window.location.href = url.toString();
            }, 1500);
        } else {
            showAlert('Error', data.message || 'Failed to add friend', 'error');
        }
    })
    .catch(err => showAlert('Error', 'Error sending request', 'error'));
}
