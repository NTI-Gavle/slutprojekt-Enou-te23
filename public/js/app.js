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
    }
});

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
    // This will be handled by the userchat modal
    openUserChatById(userId);
}

function initiateCall(userId) {
    console.log('Initiating call with user:', userId);
    alert('Voice calling feature coming soon!');
}

function respondToRequest(userId, action) {
    if (!confirm(action === 'accept' ? 'Accept friend request?' : 'Decline friend request?')) {
        return;
    }
    
    fetch('api/respond_friend.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `user_id=${userId}&action=${action}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to respond to request');
        }
    })
    .catch(err => alert('Error processing request'));
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
            location.reload();
        } else {
            alert(data.message || 'Failed to add friend');
        }
    })
    .catch(err => alert('Error sending request'));
}
