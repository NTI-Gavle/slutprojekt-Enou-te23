document.addEventListener('DOMContentLoaded', function() {
    initMobileMenu();
    initFriendActions();
    initSearchForm();
});

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
    document.querySelectorAll('.friend-item .btn-chat').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const userId = this.dataset.userId;
            openPrivateChat(userId);
        });
    });
    
    document.querySelectorAll('.friend-item .btn-call').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const userId = this.dataset.userId;
            initiateCall(userId);
        });
    });
    
    document.querySelectorAll('.friend-item').forEach(item => {
        item.addEventListener('click', function() {
            const userId = this.dataset.userId;
            openPrivateChat(userId);
        });
    });
}

function openPrivateChat(userId) {
    console.log('Opening private chat with user:', userId);
    window.location.href = `chat.php?user=${userId}`;
}

function initiateCall(userId) {
    console.log('Initiating call with user:', userId);
    alert('Voice calling feature coming soon!');
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
                                html += `<div class='search-item' onclick="window.location.href='profile.php?user=${user.id}'">`
                                    + `<img src='${sanitizeHTML(user.profile_image)}' class='avatar me-2' style='width:28px;height:28px;'>`
                                    + `${sanitizeHTML(user.display_name || user.username)}`
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
