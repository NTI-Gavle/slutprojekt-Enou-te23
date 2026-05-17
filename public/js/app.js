/**
 * Main initialization function that runs when DOM is fully loaded.
 * Sets up mobile menu, friend action handlers, search functionality.
 * For logged-in users: starts periodic background tasks for activity tracking.
 */
document.addEventListener('DOMContentLoaded', function() {
    initMobileMenu();
    initFriendActions();
    initSearchForm();

    // Only initialize for logged-in users
    if (document.body.classList.contains('logged-in')) {
        // Report user activity to server every 3 minutes (keeps online status current)
        updateActivity();
        setInterval(updateActivity, 180000);

        // Check friends' online status every minute (updates sidebar dots)
        updateFriendStatus();
        setInterval(updateFriendStatus, 60000);

        // Check for unread messages every 10 seconds (updates notification badge)
        checkUnreadMessages();
        setInterval(checkUnreadMessages, 10000);
    }
});

/**
 * Checks for unread messages and updates the notification badge.
 * Also loads preview of message senders in sidebar and mobile menu.
 * Called periodically every 10 seconds.
 */
function checkUnreadMessages() {
    fetch('api/get_unread_count.php')
        .then(res => res.json())
        .then(data => {
            const badge = document.getElementById('chatBadge');
            const unreadSection = document.getElementById('unreadMessagesSection');
            const unreadList = document.getElementById('unreadMessagesList');
            // Mobile menu elements
            const mobileUnreadSection = document.getElementById('mobileUnreadSection');
            const mobileUnreadList = document.getElementById('mobileUnreadList');
            const mobileUnreadCount = document.getElementById('mobileUnreadCount');

            if (data.success && data.unread_count > 0) {
                // Update badge in sidebar
                if (badge) {
                    badge.textContent = data.unread_count > 9 ? '9+' : data.unread_count;
                    badge.style.display = 'flex';
                }

                // Update sidebar unread section (desktop)
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

                // Update mobile menu unread section
                if (mobileUnreadSection && mobileUnreadList && mobileUnreadCount && data.senders && data.senders.length > 0) {
                    mobileUnreadCount.textContent = data.unread_count;
                    mobileUnreadList.innerHTML = '';
                    data.senders.slice(0, 5).forEach(sender => {
                        const img = sender.profile_image || 'img/default-avatar.svg';
                        const name = sender.display_name || sender.username;
                        const preview = sender.preview.length > 30 ? sender.preview.substring(0, 30) + '...' : sender.preview;
                        const countText = sender.count > 1 ? ` (${sender.count})` : '';

                        const item = document.createElement('div');
                        item.className = 'mobile-unread-item';
                        item.onclick = () => openPrivateChat(sender.sender_id);
                        item.innerHTML = `
                            <div class="friend-avatar-wrap">
                                <img src="${img}" class="avatar-small">
                            </div>
                            <div class="unread-info">
                                <strong>${escapeHtml(name)}${countText}</strong>
                                <span class="text-muted small">${escapeHtml(preview)}</span>
                            </div>
                        `;
                        mobileUnreadList.appendChild(item);
                    });
                    mobileUnreadSection.style.display = 'block';
                }
            } else {
                // Hide all unread indicators when no unread messages
                if (badge) badge.style.display = 'none';
                if (unreadSection) unreadSection.style.display = 'none';
                if (mobileUnreadSection) mobileUnreadSection.style.display = 'none';
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

// ============================================================
// Modal helper functions for custom dialogs and alerts
// ============================================================

/**
 * Closes the custom Bootstrap modal and cleans up all related elements.
 * Removes backdrops, resets body styles, and clears focus.
 */
function closeCustomModal() {
    // Remove focus before hiding to avoid accessibility warnings
    if (document.activeElement) {
        document.activeElement.blur();
    }

    const modal = document.getElementById('customModal');
    modal.classList.remove('show');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    modal.inert = true;

    // Remove all possible backdrop elements from the page
    document.querySelectorAll('.modal-backdrop').forEach(bp => bp.remove());
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
}

/**
 * Shows a custom modal dialog with title, message, and buttons.
 * @param {string} title - Modal header text
 * @param {string} message - Modal body text
 * @param {Array} buttons - Array of button objects with text, class, onclick
 * @param {Function} onShow - Optional callback after modal is shown
 */
function showModal(title, message, buttons, onShow) {
    const modal = document.getElementById('customModal');
    modal.inert = false;
    modal.setAttribute('aria-hidden', 'false');
    document.getElementById('customModalTitle').textContent = title;
    document.getElementById('customModalBody').textContent = message;
    modal.style.zIndex = '1070';
    
    const footer = document.getElementById('customModalFooter');
    footer.innerHTML = '';
    
    if (buttons) {
        buttons.forEach(btn => {
            const button = document.createElement('button');
            button.className = btn.class || 'btn btn-secondary';
            button.textContent = btn.text;
            if (btn.action === 'dismiss') {
                button.onclick = closeCustomModal;
            } else if (btn.onclick) {
                button.onclick = () => {
                    closeCustomModal();
                    btn.onclick();
                };
            } else if (btn.action === 'confirm' && btn.onclick === undefined) {
                // For showConfirm, will be handled in onShow callback
            }
            footer.appendChild(button);
        });
    } else {
        const okBtn = document.createElement('button');
        okBtn.className = 'btn btn-primary';
        okBtn.textContent = 'OK';
        okBtn.onclick = closeCustomModal;
        footer.appendChild(okBtn);
    }
    
    if (onShow) onShow();
    
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    setTimeout(() => {
        const backdrop = document.querySelector('#customModal + .modal-backdrop');
        if (backdrop) backdrop.style.zIndex = '1065';
        modal.style.zIndex = '1070';
    }, 10);
}

/**
 * Displays a simple alert dialog with one OK button.
 * @param {string} title - Alert title
 * @param {string} message - Alert message
 * @param {string} type - 'success', 'error', or default primary styling
 */
function showAlert(title, message, type) {
    const btnClass = type === 'success' ? 'btn-success' : type === 'error' ? 'btn-danger' : 'btn-primary';
    showModal(title, message, [{ text: 'OK', class: 'btn ' + btnClass, onclick: null }]);
}

/**
 * Shows a confirmation dialog with Cancel and Confirm buttons.
 * @param {string} title - Dialog title
 * @param {string} message - Confirmation question/message
 * @param {Function} onConfirm - Callback when user clicks Confirm
 * @param {Function} onCancel - Optional callback when user clicks Cancel
 * @param {string} confirmClass - CSS class for confirm button (e.g., 'btn-danger')
 */
function showConfirm(title, message, onConfirm, onCancel, confirmClass) {
    const buttons = [
        { text: 'Cancel', class: 'btn btn-outline-secondary', action: 'dismiss' },
        { text: 'Confirm', class: confirmClass || 'btn btn-primary', action: 'confirm' }
    ];
    
    showModal(title, message, buttons, function() {
        const confirmBtn = document.querySelector('#customModalFooter .btn:last-child');
        const cancelBtn = document.querySelector('#customModalFooter .btn:first-child');
        
        cancelBtn.onclick = function() {
            closeCustomModal();
            if (onCancel) onCancel();
        };
        
        confirmBtn.onclick = function() {
            closeCustomModal();
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

/**
 * Updates online/offline status dots for all friends in the sidebar.
 * Polls status API for each friend and updates their visual indicator.
 * Called every minute via setInterval in DOMContentLoaded.
 */
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

/**
 * Opens private chat with a specific user.
 * First marks their messages as read, then opens the chat modal.
 * @param {number} userId - The user ID to chat with
 */
function openPrivateChat(userId) {
    console.log('Opening private chat with user:', userId);
    // Mark messages as read when opening chat
    markMessagesAsRead(userId);
    // This will be handled by the userchat modal
    openUserChatById(userId);
}

/**
 * Placeholder for voice/video call functionality.
 * Currently shows "Coming Soon" alert.
 * @param {number} userId - The user to call
 */
function initiateCall(userId) {
    console.log('Initiating call with user:', userId);
    showAlert('Coming Soon', 'Voice calling feature is coming soon!', 'info');
}

/**
 * Responds to a friend request (accept or decline).
 * @param {number} userId - The user who sent the request
 * @param {string} action - 'accept' or 'decline'
 */
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

/**
 * Initializes the header search functionality.
 * Handles debounced search input, displays results in dropdown.
 * Searches for both users and chat rooms.
 */
function initSearchForm() {
    const searchInput = document.getElementById('headerSearchInput');
    const resultsDiv = document.getElementById('searchResults');
    let debounceTimeout;

    if (!searchInput || !resultsDiv) return;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimeout);
        const query = this.value.trim();

        // Require at least 2 characters before searching
        if (query.length < 2) {
            resultsDiv.innerHTML = '';
            resultsDiv.style.display = 'none';
            return;
        }

        // Debounce: wait 200ms after typing stops before searching
        debounceTimeout = setTimeout(() => {
            fetch(`api/search.php?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    let html = '';
                    // Display users and/or rooms if results found
                    if ((data.users && data.users.length) || (data.rooms && data.rooms.length)) {
                        if (data.users && data.users.length) {
                            html += '<div class="search-section"><strong>Users</strong>';
                            data.users.forEach(user => {
                                // Show action button based on friendship status
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

    // Hide results when clicking outside the search box
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

function hideChatBadge(userId) {
    fetch('api/mark_read.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `user_id=${userId}`
    });
}

// Mobile search functionality
let mobileSearchTimeout;

function mobileSearch(query) {
    const resultsDiv = document.getElementById('mobileSearchResults');
    if (!resultsDiv) return;
    
    if (query.length < 2) {
        resultsDiv.classList.remove('show');
        resultsDiv.innerHTML = '';
        return;
    }
    
    clearTimeout(mobileSearchTimeout);
    mobileSearchTimeout = setTimeout(() => {
        fetch('api/search.php?q=' + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                let html = '';
                
                // Users
                if (data.users && data.users.length > 0) {
                    html += '<div class="search-section"><strong>Users</strong></div>';
                    data.users.slice(0, 3).forEach(user => {
                        html += '<div class="mobile-search-item" onclick="window.location.href=\'profile.php?user=' + user.id + '\'">' +
                            '<img src="' + (user.profile_image || 'img/default-avatar.svg') + '" class="avatar-small">' +
                            '<span>' + sanitizeHTML(user.display_name || user.username) + '</span>' +
                            '</div>';
                    });
                }
                
                // Rooms
                if (data.rooms && data.rooms.length > 0) {
                    html += '<div class="search-section"><strong>Rooms</strong></div>';
                    data.rooms.slice(0, 3).forEach(room => {
                        html += '<div class="mobile-search-item" onclick="openGroupChat(' + room.id + ', \'' + sanitizeHTML(room.name) + '\')">' +
                            '<i class="bi bi-chat-dots"></i>' +
                            '<span>' + sanitizeHTML(room.name) + '</span>' +
                            '</div>';
                    });
                }
                
                // Pages
                const pages = [
                    { name: 'Home', url: 'index.php' },
                    { name: 'Profile', url: 'profile.php' },
                    { name: 'About Us', url: 'about.php' },
                    { name: 'Legal', url: 'legal.php' },
                    { name: 'Contact', url: 'contact.php' }
                ];
                const matchingPages = pages.filter(p => p.name.toLowerCase().includes(query.toLowerCase()));
                if (matchingPages.length > 0) {
                    html += '<div class="search-section"><strong>Pages</strong></div>';
                    matchingPages.forEach(page => {
                        html += '<div class="mobile-search-item" onclick="window.location.href=\'' + page.url + '\'">' +
                            '<i class="bi bi-file-text"></i>' +
                            '<span>' + page.name + '</span>' +
                            '</div>';
                    });
                }
                
                if (html === '') {
                    html = '<div class="mobile-search-item"><span class="text-muted">No results found</span></div>';
                }
                
                resultsDiv.innerHTML = html;
                resultsDiv.classList.add('show');
            })
            .catch(() => {
                resultsDiv.innerHTML = '';
                resultsDiv.classList.remove('show');
            });
    }, 300);
}

// Close mobile search when clicking outside
document.addEventListener('click', function(e) {
    const searchInput = document.getElementById('mobileSearchInput');
    const resultsDiv = document.getElementById('mobileSearchResults');
    if (searchInput && resultsDiv && !searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
        resultsDiv.classList.remove('show');
    }
});
