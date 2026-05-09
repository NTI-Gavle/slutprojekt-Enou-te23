<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../database/db.php';

$pageTitle = "Admin Panel";

if (!isLoggedIn()) {
    header('Location: auth/login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$db = getDBConnection();

// Check if user is admin
$stmt = $db->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([$userId]);
$currentUser = $stmt->fetch();

if (!$currentUser || $currentUser['is_admin'] != 1) {
    die('Access denied. Admin only.');
}

// Handle actions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user'])) {
        $deleteId = (int)$_POST['user_id'];
        try {
            $db->beginTransaction();
            // Delete user's messages
            $db->exec("DELETE FROM messages WHERE sender_id = $deleteId OR receiver_id = $deleteId");
            // Delete user's room memberships
            $db->exec("DELETE FROM room_members WHERE user_id = $deleteId");
            // Delete user's friend relationships
            $db->exec("DELETE FROM friends WHERE user_id = $deleteId OR friend_id = $deleteId");
            // Delete user
            $db->exec("DELETE FROM users WHERE id = $deleteId");
            $db->commit();
            $message = 'User deleted successfully!';
        } catch (Exception $e) {
            $db->rollBack();
            $message = 'Error deleting user: ' . $e->getMessage();
        }
    } elseif (isset($_POST['send_warning'])) {
        $warnUserId = (int)$_POST['user_id'];
        $warning = $_POST['warning_message'];
        $stmt = $db->prepare("UPDATE users SET warning = ? WHERE id = ?");
        $stmt->execute([$warning, $warnUserId]);
        $message = 'Warning sent to user!';
    } elseif (isset($_POST['delete_room'])) {
        $roomId = (int)$_POST['room_id'];
        try {
            $db->beginTransaction();
            $db->exec("DELETE FROM messages WHERE room_id = $roomId");
            $db->exec("DELETE FROM room_members WHERE room_id = $roomId");
            $db->exec("DELETE FROM rooms WHERE id = $roomId");
            $db->commit();
            $message = 'Room deleted successfully!';
        } catch (Exception $e) {
            $db->rollBack();
            $message = 'Error deleting room: ' . $e->getMessage();
        }
    }
}

// Get search filters
$searchUser = $_GET['search_user'] ?? '';
$searchRoom = $_GET['search_room'] ?? '';

// Get all users
if ($searchUser) {
    $stmt = $db->prepare("SELECT * FROM users WHERE username LIKE ? OR display_name LIKE ? ORDER BY id DESC LIMIT 50");
    $stmt->execute(["%$searchUser%", "%$searchUser%"]);
} else {
    $stmt = $db->query("SELECT * FROM users ORDER BY id DESC LIMIT 50");
}
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all rooms
if ($searchRoom) {
    $stmt = $db->prepare("SELECT r.*, u.username as owner_name FROM rooms r LEFT JOIN users u ON r.creator_id = u.id WHERE r.name LIKE ? OR r.description LIKE ? ORDER BY r.id DESC LIMIT 50");
    $stmt->execute(["%$searchRoom%", "%$searchRoom%"]);
} else {
    $stmt = $db->query("SELECT r.*, u.username as owner_name FROM rooms r LEFT JOIN users u ON r.creator_id = u.id ORDER BY r.id DESC LIMIT 50");
}
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> | Quacko</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .admin-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .admin-tabs { display: flex; gap: 10px; margin-bottom: 20px; }
        .admin-tab { padding: 10px 20px; background: #f0f0f0; border: none; cursor: pointer; border-radius: 5px; }
        .admin-tab.active { background: #6c5ce7; color: white; }
        .admin-content { display: none; }
        .admin-content.active { display: block; }
        .admin-table { width: 100%; background: white; border-radius: 8px; overflow: hidden; }
        .admin-table th, .admin-table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .admin-table th { background: #f8f9fa; font-weight: 600; }
        .warning-badge { background: #ffc107; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; }
        .search-box { margin-bottom: 20px; display: flex; gap: 10px; }
    </style>
</head>
<body class="logged-in" style="background: #f5f5f5;">
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
        <div class="admin-container" style="background: white; border-radius: 12px; padding: 30px;">
                    <h2><i class="bi bi-shield-lock"></i> Admin Panel</h2>
                    
                    <?php if ($message): ?>
                        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>
                    
                    <div class="admin-tabs">
                        <button class="admin-tab active" onclick="showTab('rooms')">Chat Rooms</button>
                        <button class="admin-tab" onclick="showTab('users')">Users</button>
                    </div>
                    
                    <div id="rooms" class="admin-content active">
                        <form method="GET" class="search-box">
                            <input type="text" name="search_room" class="form-control" placeholder="Search rooms..." value="<?= htmlspecialchars($searchRoom) ?>">
                            <button type="submit" class="btn btn-primary">Search</button>
                            <a href="admin.php" class="btn btn-secondary">Clear</a>
                        </form>
                        
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Owner</th>
                                    <th>Type</th>
                                    <th>Members</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rooms as $room): 
                                    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM room_members WHERE room_id = ?");
                                    $stmt->execute([$room['id']]);
                                    $memberCount = $stmt->fetch()['cnt'];
                                ?>
                                <tr>
                                    <td><?= $room['id'] ?></td>
                                    <td><?= htmlspecialchars($room['name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($room['description'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($room['owner_name'] ?? '') ?></td>
                                    <td><?= ($room['is_private'] ?? 0) ? 'Private' : 'Public' ?></td>
                                    <td><?= $memberCount ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
                                            <button type="submit" name="delete_room" class="btn btn-sm btn-danger" onclick="return confirm('Delete this room?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div id="users" class="admin-content">
                        <form method="GET" class="search-box">
                            <input type="text" name="search_user" class="form-control" placeholder="Search users..." value="<?= htmlspecialchars($searchUser) ?>">
                            <button type="submit" class="btn btn-primary">Search</button>
                            <a href="admin.php" class="btn btn-secondary">Clear</a>
                        </form>
                        
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Display Name</th>
                                    <th>Email</th>
                                    <th>Admin</th>
                                    <th>Warning</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= $user['id'] ?></td>
                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                    <td><?= htmlspecialchars($user['display_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= $user['is_admin'] ? '<span class="badge bg-danger">Admin</span>' : '-' ?></td>
                                    <td>
                                        <?php if ($user['warning']): ?>
                                            <span class="warning-badge" title="<?= htmlspecialchars($user['warning']) ?>">!</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="showWarningModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>')">Warn</button>
                                        <?php if ($user['id'] != $userId && !$user['is_admin']): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" name="delete_user" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user and all their data?')">Delete</button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div class="modal" id="warningModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Send Warning</h5>
                    <button type="button" class="btn-close" onclick="closeWarningModal()"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="warnUserId">
                        <input type="hidden" name="send_warning" value="1">
                        <p>Send warning to: <strong id="warnUsername"></strong></p>
                        <textarea name="warning_message" class="form-control" placeholder="Enter warning message..." required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeWarningModal()">Cancel</button>
                        <button type="submit" class="btn btn-warning">Send Warning</button>
                    </div>
                </form>
            </div>
        </div>
        </div>
    </div>
    </div>

    <script>
    function showTab(tab) {
        document.querySelectorAll('.admin-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.admin-content').forEach(c => c.classList.remove('active'));
        event.target.classList.add('active');
        document.getElementById(tab).classList.add('active');
    }
    
    function showWarningModal(userId, username) {
        document.getElementById('warnUserId').value = userId;
        document.getElementById('warnUsername').textContent = username;
        document.getElementById('warningModal').style.display = 'block';
    }
    
    function closeWarningModal() {
        document.getElementById('warningModal').style.display = 'none';
    }
    </script>
</body>
</html>