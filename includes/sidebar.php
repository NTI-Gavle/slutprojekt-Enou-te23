<?php
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../includes/session.php';

if (!isset($friends)) {
    $friends = [];
    
    // Fetch friends from database if user is logged in
    if (isset($_SESSION['user_id'])) {
        try {
            $dbconn = getDBConnection();
            if ($dbconn) {
                // Get unique friends - only show each friend once
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
                    if (isset($friend['is_online']) && $friend['is_online'] == 1) {
                        $friend['is_online'] = true;
                    } else {
                        $friend['is_online'] = false;
                    }
                }
                unset($friend);
            }
        } catch (Exception $e) {
            $friends = [];
        }
    }
}

if (!isset($pendingRequests)) {
    $pendingRequests = [];
    
    // Fetch pending requests if user is logged in
    if (isset($_SESSION['user_id'])) {
        try {
            $dbconn = getDBConnection();
            if ($dbconn) {
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
            }
        } catch (Exception $e) {
            $pendingRequests = [];
        }
    }
}

// Fetch outgoing (sent) pending requests
if (!isset($sentRequests)) {
    $sentRequests = [];
    
    if (isset($_SESSION['user_id'])) {
        try {
            $dbconn = getDBConnection();
            if ($dbconn) {
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
            }
        } catch (Exception $e) {
            $sentRequests = [];
        }
    }
}
?>
<div class="sidebar-title">
    <h5 class="mb-0">My Friends</h5>
    <div class="friend-chat-icon" title="Chats">
        <i class="bi bi-chat-left-text"></i>
        <span class="chat-badge" id="chatBadge" style="display: none;">1</span>
    </div>
</div>

<?php if (isset($_SESSION['user_id'])): ?>
<div class="add-friend-inline">
    <form action="add_friend.php" method="POST" class="add-friend-form">
        <input type="text" class="form-control form-control-sm" name="username" placeholder="Add by username..." required>
        <button type="submit" class="btn btn-add btn-sm"><i class="bi bi-plus-lg"></i></button>
    </form>
</div>
<?php endif; ?>

<div class="friends">
    <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="no-friends">
            <p>Login to see friends</p>
        </div>
    <?php elseif (empty($friends) && empty($pendingRequests) && empty($sentRequests)): ?>
        <div class="no-friends">
            <i class="bi bi-people"></i>
            <p>No friends yet</p>
        </div>
    <?php else: ?>
        <?php if (!empty($pendingRequests)): ?>
            <div class="pending-section">
                <div class="sidebar-subtitle">Pending Requests</div>
                <?php foreach ($pendingRequests as $request): ?>
                    <div class="friend pending" data-user-id="<?= $request['user_id'] ?>">
                        <div class="friend-avatar-wrap">
                            <img src="<?= htmlspecialchars($request['profile_image'] ?? 'img/default-avatar.svg') ?>" alt="" class="friend-avatar">
                        </div>
                        <div class="friend-info">
                            <span class="friend-name"><?= htmlspecialchars($request['display_name']) ?></span>
                            <span class="friend-status text-muted">Wants to be friends</span>
                        </div>
                        <div class="friend-btns">
                            <button class="btn btn-icon btn-sm text-success" onclick="respondToRequest(<?= $request['user_id'] ?>, 'accept')" title="Accept">
                                <i class="bi bi-check-lg"></i>
                            </button>
                            <button class="btn btn-icon btn-sm text-danger" onclick="respondToRequest(<?= $request['user_id'] ?>, 'decline')" title="Decline">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (empty($sentRequests)): ?>
            <hr class="sidebar-divider">
            <?php endif; ?>
        <?php endif; ?>
        
        <?php if (!empty($sentRequests)): ?>
            <div class="pending-section">
                <div class="sidebar-subtitle">Sent Requests</div>
                <?php foreach ($sentRequests as $request): ?>
                    <div class="friend pending" data-user-id="<?= $request['user_id'] ?>">
                        <div class="friend-avatar-wrap">
                            <img src="<?= htmlspecialchars($request['profile_image'] ?? 'img/default-avatar.svg') ?>" alt="" class="friend-avatar">
                        </div>
                        <div class="friend-info">
                            <span class="friend-name"><?= htmlspecialchars($request['display_name']) ?></span>
                            <span class="friend-status text-muted">Request sent</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (!empty($pendingRequests) || !empty($friends)): ?>
            <hr class="sidebar-divider">
            <?php endif; ?>
        <?php endif; ?>
        
        <?php foreach ($friends as $friend): ?>
            <div class="friend" data-user-id="<?= $friend['id'] ?>">
                <div class="friend-avatar-wrap">
                    <img src="<?= htmlspecialchars($friend['profile_image'] ?? 'img/default-avatar.svg') ?>" alt="" class="friend-avatar">
                    <span class="status-dot <?= $friend['is_online'] ? 'online' : 'offline' ?>"></span>
                </div>
                <div class="friend-info">
                    <span class="friend-name"><?= htmlspecialchars($friend['display_name']) ?></span>
                    <span class="friend-status"><?= $friend['is_online'] ? 'Online' : 'Offline' ?></span>
                </div>
                <div class="friend-btns">
                    <button class="btn btn-icon btn-chat">
                        <i class="bi bi-chat-left-text"></i>
                        <span class="friend-badge" id="badge-<?= $friend['id'] ?>" style="display: none;">1</span>
                    </button>
                    <button class="btn btn-icon btn-call"><i class="bi bi-telephone"></i></button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>