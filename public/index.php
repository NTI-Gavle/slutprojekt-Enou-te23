<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../database/db.php';

$pageTitle = "Home";
$userProfileImage = $_SESSION['profile_image'] ?? 'img/default-avatar.svg';

$popularRooms = [];

// Fetch popular chat rooms sorted by member count (most popular first)
try {
    $dbconn = getDBConnection();
    if ($dbconn) {
        // Query retrieves rooms with their member count via subquery
        // Results limited to 20 most popular rooms
        $stmt = $dbconn->prepare("SELECT r.id, r.name, r.description, r.tag, r.is_private,
            (SELECT COUNT(*) FROM room_members WHERE room_id = r.id) as member_count
            FROM rooms r ORDER BY member_count DESC LIMIT 20");
        $stmt->execute();
        $popularRooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    // If query fails, display empty state
    $popularRooms = [];
}

require_once __DIR__ . '/../includes/header.php';   
?>

<section>
    <h1 class="hero-title" style="background: linear-gradient(to right, #AD46FF 0%, #F6339A 50%, #2B7FFF 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Welcome to Quacko</h1>
    <p class="hero-subtitle">Connect with friends, join chat rooms, and communicate seamlessly.</p>
    
    <?php if (isLoggedIn()): ?>
        <p>Hello, <?= htmlspecialchars($_SESSION['display_name'] ?? 'User') ?>!</p>
    <?php else: ?>
        <div class="d-flex gap-2 justify-content-center">
            <a href="auth/register.php" class="btn btn-primary btn-lg">Get Started</a>
            <a href="auth/login.php" class="btn btn-outline btn-lg">Login</a>
        </div>
    <?php endif; ?>
</section>

<section class="mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Popular Chat Rooms</h2>
        <?php if (isLoggedIn()): ?>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRoomModal">
                <i class="bi bi-plus-lg me-2"></i>Create Room
            </button>
        <?php endif; ?>
    </div>
    
    <div class="room-grid">
        <?php if (empty($popularRooms)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-chat-dots display-4"></i>
                <p class="mt-3">No chat rooms yet. Be the first to create one!</p>
            </div>
        <?php else: ?>
            <?php foreach ($popularRooms as $room): ?>
                <?php $onclick = $room['is_private'] ? "joinPrivateRoom({$room['id']})" : "openGroupChat({$room['id']}, '" . htmlspecialchars($room['name'], ENT_QUOTES) . "')"; ?>
                <div class="room-card" data-room-id="<?= $room['id'] ?>" data-room-name="<?= htmlspecialchars($room['name'], ENT_QUOTES) ?>" data-private="<?= $room['is_private'] ? 1 : 0 ?>">
                    <div class="room-header">
                        <div class="room-info">
                            <h4><?= htmlspecialchars($room['name']) ?></h4>
                            <?php if ($room['tag']): ?>
                                <span class="room-tag"><?= htmlspecialchars($room['tag']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <p class="room-desc"><?= htmlspecialchars($room['description'] ?? 'No description') ?></p>
                    <div class="room-meta">
                        <span><i class="bi bi-people"></i> <?= $room['member_count'] ?? 0 ?> members</span>
                        <span><?= $room['is_private'] ? '<i class="bi bi-lock"></i> Private' : 'Public' ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php if (isLoggedIn()): ?>
<div class="modal fade" id="createRoomModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-dark">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Create Chat Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="create_room.php" method="POST" id="createRoomForm">
                    <div class="mb-3">
                        <label for="roomName" class="form-label">Room Name</label>
                        <input type="text" class="form-control" id="roomName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="roomDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="roomDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="roomTag" class="form-label">Tag</label>
                        <input type="text" class="form-control" id="roomTag" name="tag" placeholder="e.g. Gaming, Tech, Music">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="roomPrivate" name="is_private">
                        <label class="form-check-label" for="roomPrivate">Private Room</label>
                    </div>
                    <div class="mb-3" id="chatCodeField" style="display: none;">
                        <label for="roomChatCode" class="form-label">Chat Code</label>
                        <input type="text" class="form-control" id="roomChatCode" name="chat_code" maxlength="8" placeholder="Enter code to join">
                        <small class="text-muted">Share this code with others to let them join</small>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Create Room</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('roomPrivate').addEventListener('change', function() {
    document.getElementById('chatCodeField').style.display = this.checked ? 'block' : 'none';
});
</script>
<?php endif; ?>

<script>
document.querySelectorAll('.room-card').forEach(card => {
    card.addEventListener('click', () => {
        <?php if (isLoggedIn()): ?>
            var roomId = card.dataset.roomId;
            var roomName = card.dataset.roomName;
            var isPrivate = card.dataset.private === '1';
            if (isPrivate) {
                joinPrivateRoom(roomId, roomName);
            } else {
                openGroupChat(parseInt(roomId), roomName);
            }
        <?php else: ?>
            window.location.href = 'auth/login.php';
        <?php endif; ?>
    });
});

function joinPrivateRoom(roomId, roomName) {
    // Check if user already has access (remembered)
    const verifiedRooms = JSON.parse(localStorage.getItem('verifiedRooms') || '[]');
    if (verifiedRooms.includes(parseInt(roomId))) {
        openGroupChat(parseInt(roomId), roomName);
        return;
    }
    
    // Create a custom modal for entering the code
    const modalHtml = `
        <div class="modal fade show" id="joinRoomModal" style="display: block;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(-60deg, #B148FF 0%, #F4369E 100%); color: white;">
                        <h5 class="modal-title">Join Private Room</h5>
                        <button type="button" class="btn-close btn-close-white" onclick="closeJoinRoomModal()"></button>
                    </div>
                    <div class="modal-body">
                        <p>Enter the chat code to join "<strong>${roomName}</strong>":</p>
                        <input type="text" id="joinRoomCode" class="form-control" placeholder="Enter code" maxlength="8" autofocus>
                        <div class="form-check mt-2">
                            <input type="checkbox" class="form-check-input" id="rememberRoomCode">
                            <label class="form-check-label" for="rememberRoomCode">Remember this room (no need to enter code again)</label>
                        </div>
                        <p id="joinRoomError" class="text-danger mt-2" style="display: none;"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeJoinRoomModal()">Cancel</button>
                        <button type="button" class="btn btn-primary" style="background: linear-gradient(-60deg, #B148FF 0%, #F4369E 100%); border: none;" onclick="submitJoinRoomCode(${roomId}, '${roomName.replace(/'/g, "\\'")}')">Join</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" id="joinRoomBackdrop" style="display: block;" onclick="closeJoinRoomModal()"></div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    document.getElementById('joinRoomCode').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            submitJoinRoomCode(roomId, roomName);
        }
    });
}

function closeJoinRoomModal() {
    document.getElementById('joinRoomModal')?.remove();
    document.querySelector('.modal-backdrop')?.remove();
}

function submitJoinRoomCode(roomId, roomName) {
    const code = document.getElementById('joinRoomCode').value.trim();
    const rememberMe = document.getElementById('rememberRoomCode')?.checked || false;
    const errorEl = document.getElementById('joinRoomError');
    
    if (!code) {
        errorEl.textContent = 'Please enter a code';
        errorEl.style.display = 'block';
        return;
    }
    
    fetch('api/join_room.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'room_id=' + roomId + '&chat_code=' + encodeURIComponent(code)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (rememberMe) {
                const verifiedRooms = JSON.parse(localStorage.getItem('verifiedRooms') || '[]');
                if (!verifiedRooms.includes(parseInt(roomId))) {
                    verifiedRooms.push(parseInt(roomId));
                    localStorage.setItem('verifiedRooms', JSON.stringify(verifiedRooms));
                }
            }
            closeJoinRoomModal();
            openGroupChat(parseInt(roomId), roomName);
        } else {
            errorEl.textContent = data.message || 'Invalid code';
            errorEl.style.display = 'block';
        }
    })
    .catch(err => {
        errorEl.textContent = 'Error joining room';
        errorEl.style.display = 'block';
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
