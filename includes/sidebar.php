<?php
if (!isset($friends)) {
    $friends = [];
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
    <?php elseif (empty($friends)): ?>
        <div class="no-friends">
            <i class="bi bi-people"></i>
            <p>No friends yet</p>
        </div>
    <?php else: ?>
        <?php foreach ($friends as $friend): ?>
            <div class="friend">
                <div class="friend-avatar-wrap">
                    <img src="<?= htmlspecialchars($friend['profile_image'] ?? 'img/default-avatar.svg') ?>" alt=""
                        class="friend-avatar">
                    <span class="status-dot <?= $friend['is_online'] ? 'online' : 'offline' ?>"></span>
                </div>
                <div class="friend-info">
                    <span class="friend-name"><?= htmlspecialchars($friend['display_name']) ?></span>
                    <span class="friend-status"><?= $friend['is_online'] ? 'Online' : 'Offline' ?></span>
                </div>
                <div class="friend-btns">
                    <button class="btn btn-icon" onclick="openUserChat(<?= $friend['id'] ?>, '<?= htmlspecialchars($friend['display_name']) ?>', '<?= htmlspecialchars($friend['profile_image'] ?? 'img/default-avatar.svg') ?>')">
                        <i class="bi bi-chat-left-text"></i>
                        <span class="friend-badge" id="badge-<?= $friend['id'] ?>" style="display: none;">1</span>
                    </button>
                    <button class="btn btn-icon"><i class="bi bi-telephone"></i></button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>