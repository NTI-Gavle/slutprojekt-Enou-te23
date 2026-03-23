<?php
if (!isset($friends)) {
    $friends = [];
}
?>

<div class="sidebar-title">
    <h5 class="mb-0">My Friends</h5>
    <?php if (isset($_SESSION['user_id'])): ?>
        <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addFriendModal">
            <i class="bi bi-person-plus"></i>
        </button>
    <?php endif; ?>
</div>

<div class="friends">
    <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="no-friends">
            <p>Login to see friends</p>
        </div>
    <?php elseif (empty($friends)): ?>
        <div class="no-friends">
            <i class="bi bi-people"></i>
            <p>No friends yet</p>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addFriendModal">
                Add Friends
            </button>
        </div>
    <?php else: ?>
        <?php foreach ($friends as $friend): ?>
            <div class="friend">
                <div class="friend-avatar-wrap">
                    <img src="<?= htmlspecialchars($friend['profile_image'] ?? 'img/default-avatar.png') ?>" alt="" class="friend-avatar">
                    <span class="status-dot <?= $friend['is_online'] ? 'online' : 'offline' ?>"></span>
                </div>
                <div class="friend-info">
                    <span class="friend-name"><?= htmlspecialchars($friend['display_name']) ?></span>
                    <span class="friend-status"><?= $friend['is_online'] ? 'Online' : 'Offline' ?></span>
                </div>
                <div class="friend-btns">
                    <button class="btn btn-icon"><i class="bi bi-chat-left-text"></i></button>
                    <button class="btn btn-icon"><i class="bi bi-telephone"></i></button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if (isset($_SESSION['user_id'])): ?>
<div class="modal fade" id="addFriendModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-dark">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Add Friend</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="add_friend.php" method="POST">
                    <div class="mb-3">
                        <label for="friendUsername" class="form-label">Username or Email</label>
                        <input type="text" class="form-control" id="friendUsername" name="username" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Find User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
