<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../database/db.php';

$pageTitle = "Home";
$userProfileImage = $_SESSION['profile_image'] ?? 'img/default-avatar.png';

$popularRooms = [
    ['id' => 1, 'name' => 'General Chat', 'description' => 'A place for everyone to chat.', 'tag' => 'General', 'members' => 156, 'is_private' => false],
    ['id' => 2, 'name' => 'General Chat', 'description' => 'A place for everyone to chat.', 'tag' => 'General', 'members' => 156, 'is_private' => false],
    ['id' => 3, 'name' => 'General Chat', 'description' => 'A place for everyone to chat.', 'tag' => 'General', 'members' => 156, 'is_private' => false],
    ['id' => 4, 'name' => 'General Chat', 'description' => 'A place for everyone to chat.', 'tag' => 'General', 'members' => 156, 'is_private' => false],
    ['id' => 5, 'name' => 'General Chat', 'description' => 'A place for everyone to chat.', 'tag' => 'General', 'members' => 156, 'is_private' => false],
    ['id' => 6, 'name' => 'General Chat', 'description' => 'A place for everyone to chat.', 'tag' => 'General', 'members' => 156, 'is_private' => false],
];

if (isLoggedIn()) {
    $friends = [
        ['id' => 2, 'display_name' => 'Bob Johnson', 'profile_image' => 'img/default-avatar.png', 'is_online' => true],
        ['id' => 3, 'display_name' => 'Bob Johnson', 'profile_image' => 'img/default-avatar.png', 'is_online' => false],
        ['id' => 4, 'display_name' => 'Bob Johnson', 'profile_image' => 'img/default-avatar.png', 'is_online' => true], 
    ];
} else {
    $friends = [];
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
            <a href="/auth/register.php" class="btn btn-primary btn-lg">Get Started</a>
            <a href="/auth/login.php" class="btn btn-outline btn-lg">Login</a>
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
        <?php foreach ($popularRooms as $room): ?>
            <div class="room-card" data-room-id="<?= $room['id'] ?>">
                <div class="room-header">
                    <div class="room-info">
                        <h4><?= htmlspecialchars($room['name']) ?></h4>
                        <span class="room-tag"><?= htmlspecialchars($room['tag']) ?></span>
                    </div>
                </div>
                <p class="room-desc"><?= htmlspecialchars($room['description']) ?></p>
                <div class="room-meta">
                    <span><i class="bi bi-people"></i> <?= $room['members'] ?> members</span>
                    <span><?= $room['is_private'] ? 'Private' : 'Public' ?></span>
                </div>
            </div>
        <?php endforeach; ?>
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
                <form action="create_room.php" method="POST">
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
                        <input type="text" class="form-control" id="roomTag" name="tag">
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Create Room</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.querySelectorAll('.room-card').forEach(card => {
    card.addEventListener('click', () => {
        <?php if (isLoggedIn()): ?>
            window.location.href = 'chatroom.php?id=' + card.dataset.roomId;
        <?php else: ?>
            window.location.href = '/auth/login.php';
        <?php endif; ?>
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
