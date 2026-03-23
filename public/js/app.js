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
    const searchForms = document.querySelectorAll('.quacko-search');
    searchForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const query = this.querySelector('input[type="search"]').value;
            if (query.trim()) {
                window.location.href = `search.php?q=${encodeURIComponent(query)}`;
            }
        });
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
