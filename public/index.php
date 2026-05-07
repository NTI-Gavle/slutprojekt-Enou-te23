<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../database/db.php';

$pageTitle = "Home";
$userProfileImage = $_SESSION['profile_image'] ?? 'img/default-avatar.svg';

$popularRooms = [];
$friends = [];
$pendingRequests = [];
$sentRequests = [];

if (isset($_SESSION['user_id'])) {
    try {
        $dbconn = getDBConnection();
        
        // Fetch friends
        $stmt = $dbconn->prepare("
            SELECT DISTINCT u.id, u.display_name, u.profile_image,
                   CASE WHEN u.last_activity >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN 1 ELSE 0 END as is_online
            FROM friends f
            JOIN users u ON u.id = 
                CASE WHEN f.user_id = ? THEN f.friend_id ELSE f.user_id END
            WHERE (f.user_id = ? OR f.friend_id = ?) 
            AND f.status = 'accepted'
        ");
        $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
        $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($friends as &$friend) {
            $friend['profile_image'] = getValidProfileImage($friend['profile_image'] ?? null);
            $friend['is_online'] = (isset($friend['is_online']) && $friend['is_online'] == 1);
        }
        unset($friend);
        
        // Fetch pending requests
        $stmt = $dbconn->prepare("
            SELECT f.id, u.id as user_id, u.display_name, u.profile_image 
            FROM friends f 
            JOIN users u ON u.id = f.user_id 
            WHERE f.friend_id = ? AND f.status = 'pending'
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $pendingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($pendingRequests as &$req) {
            $req['profile_image'] = getValidProfileImage($req['profile_image'] ?? null);
        }
        unset($req);
        
        // Fetch sent (outgoing) pending requests
        $stmt = $dbconn->prepare("
            SELECT f.id, u.id as user_id, u.display_name, u.profile_image 
            FROM friends f 
            JOIN users u ON u.id = f.friend_id
            WHERE f.user_id = ? AND f.status = 'pending'
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $sentRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($sentRequests as &$req) {
            $req['profile_image'] = getValidProfileImage($req['profile_image'] ?? null);
        }
        unset($req);
        
    } catch (Exception $e) {
        $friends = [];
        $pendingRequests = [];
        $sentRequests = [];
    }
}

// Fetch popular rooms
try {
    $dbconn = getDBConnection();
    if ($dbconn) {
        $stmt = $dbconn->prepare("SELECT r.id, r.name, r.description, r.tag, r.is_private, 
            (SELECT COUNT(*) FROM room_members WHERE room_id = r.id) as member_count 
            FROM rooms r ORDER BY member_count DESC LIMIT 20");
        $stmt->execute();
        $popularRooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $popularRooms = [];
}

require_once __DIR__ . '/../includes/header.php';   
?>

<section>
    <h1 class="hero-title">Welcome to Quacko</h1>
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
    var code = prompt('Enter join code for "' + roomName + '":');
    if (!code || !code.trim()) return;
    
    fetch('api/join_room.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'room_id=' + roomId + '&chat_code=' + encodeURIComponent(code.trim())
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            openGroupChat(parseInt(roomId), roomName);
        } else {
            alert(data.message || 'Invalid code');
        }
    })
    .catch(err => alert('Error joining room'));
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
