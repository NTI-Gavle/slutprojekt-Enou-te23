<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | Quacko' : 'Quacko' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="js/app.js" defer></script>
</head>
<body<?= isLoggedIn() ? ' class="logged-in"' : '' ?>>
<?php
// Check if user is logged in and has an active warning from admin
if (isLoggedIn()) {
    require_once __DIR__ . '/../database/db.php';
    $db = getDBConnection();

    // Retrieve warning message from user's record if exists
    $stmt = $db->prepare("SELECT warning FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $currentUser = $stmt->fetch();

    // Display modal warning popup if user has warning set
    // Modal blocks all interaction until user acknowledges (clicks "I Understand")
    if (!empty($currentUser['warning'])) {
        ?>
        <div class="modal fade show" id="warningModal" data-bs-backdrop="static" tabindex="-1" style="display: block;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill"></i> Warning Received</h5>
                    </div>
                    <div class="modal-body">
                        <p><?= htmlspecialchars($currentUser['warning']) ?></p>
                        <p class="text-muted small">Please read and follow our community guidelines.</p>
                    </div>
                    <div class="modal-footer">
                        <form method="POST" action="profile.php">
                            <button type="submit" name="clear_warning" class="btn btn-primary">I Understand</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" style="display: block;"></div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var modal = new bootstrap.Modal(document.getElementById('warningModal'));
            modal.show();
        });
        </script>
        <?php
    }
}
?>

    <header class="site-header">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between py-2">
                <div class="d-flex align-items-center">
                    <div class="logo">
                        <span>Q</span>
                    </div>
                    <a href="index.php" class="brand">Quacko</a>
                </div>

<div class="d-none d-md-flex align-items-center gap-3">
                    <div class="search-wrapper">
                        <form class="quacko-search search-bar" id="headerSearchForm" autocomplete="off">
                            <input type="search" class="form-control" id="headerSearchInput" name="q" placeholder="Search users & rooms..." autocomplete="off">
                            <button class="btn btn-search" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </form>
                        <div class="search-results" id="searchResults"></div>
                    </div>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php 
                        $img = getValidProfileImage($_SESSION['profile_image'] ?? null);
                        $isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
                        ?>
                        <img src="<?= $img ?>" alt="Profile" class="avatar">
                        <?php if ($isAdmin): ?>
                        <a href="admin.php" class="btn btn-danger btn-sm">Admin</a>
                        <?php endif; ?>
                        <a href="profile.php" class="btn btn-outline btn-sm">Profile</a>
                        <a href="auth/process-logout.php" class="btn btn-outline btn-sm">Logout</a>
                    <?php else: ?>
                        <a href="auth/login.php" class="btn btn-outline">Login</a>
                        <a href="auth/register.php" class="btn btn-primary">Register</a>
                    <?php endif; ?>
                </div>

                <button class="hamburger" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu">
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </div>
    </header>

    <div class="offcanvas offcanvas-end mobile-nav" tabindex="-1" id="mobileMenu">
        <div class="offcanvas-header">
            <div class="d-flex align-items-center">
                <div class="logo">
                    <span>Q</span>
                </div>
                <span class="brand ms-2">Quacko</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <div class="mobile-search">
                <input type="search" id="mobileSearchInput" class="form-control" placeholder="Search users, rooms, pages..." oninput="mobileSearch(this.value)">
                <div id="mobileSearchResults" class="mobile-search-results"></div>
            </div>

<?php if (isset($_SESSION['user_id'])): ?>
                <?php
                $img = getValidProfileImage($_SESSION['profile_image'] ?? null);

                // Load database connection for friends query
                require_once __DIR__ . '/../database/db.php';
                $db = getDBConnection();

                // Get friends list with their online status and last message timestamp
                // DISTINCT prevents duplicate entries when both users added each other
                // Subquery gets most recent message for sorting by conversation activity
                $friendsStmt = $db->prepare("
                    SELECT DISTINCT u.id, u.display_name, u.profile_image, u.last_activity,
                    (SELECT created_at FROM messages WHERE (sender_id = u.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.id) ORDER BY created_at DESC LIMIT 1) as last_message
                    FROM friends f
                    JOIN users u ON (CASE WHEN f.user_id = ? THEN f.friend_id = u.id ELSE f.user_id = u.id END)
                    WHERE (f.user_id = ? OR f.friend_id = ?) AND f.status = 'accepted'
                    ORDER BY last_message DESC
                ");
                $friendsStmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
                $mobileFriends = $friendsStmt->fetchAll();
                ?>
                <div class="mobile-user mb-3">
                    <img src="<?= $img ?>" alt="Profile" class="avatar">
                    <span><?= htmlspecialchars($_SESSION['display_name'] ?? 'User') ?></span>
                </div>
                
                <div id="mobileUnreadSection" class="mobile-section d-block d-md-none" style="display: none;">
                    <h6>Unread Messages (<span id="mobileUnreadCount">0</span>)</h6>
                    <div id="mobileUnreadList" class="mobile-unread-list"></div>
                </div>

                <hr>
                <a href="profile.php" class="btn btn-outline w-100 mb-2">Profile</a>
                <a href="index.php" class="btn btn-outline w-100 mb-2">Home</a>
                <a href="about.php" class="btn btn-outline w-100 mb-2">About Us</a>
                <a href="legal.php" class="btn btn-outline w-100 mb-2">Legal</a>
                <hr>
                <a href="auth/process-logout.php" class="btn btn-outline w-100 mb-3">Logout</a>
                
                <?php /* Unread messages now loaded via JS - see app.js checkUnreadMessages() */ ?>
                
                <div class="mobile-section d-block d-md-none">
                    <h6>Friends</h6>
                    <?php if (count($mobileFriends) > 0): ?>
                    <div class="mobile-friends-list">
                        <?php foreach ($mobileFriends as $friend):
                            $isOnline = $friend['last_activity'] && (time() - strtotime($friend['last_activity']) < 300);
                            $friendImg = getValidProfileImage($friend['profile_image']);
                        ?>
                        <div class="mobile-friend-item" data-user-id="<?= $friend['id'] ?>">
                            <div class="friend-avatar-wrap" onclick="openUserChatById(<?= $friend['id'] ?>)">
                                <img src="<?= $friendImg ?>" class="avatar-small">
                                <?php if ($isOnline): ?>
                                    <span class="online-dot"></span>
                                <?php endif; ?>
                            </div>
                            <span class="friend-name" onclick="openUserChatById(<?= $friend['id'] ?>)"><?= htmlspecialchars($friend['display_name']) ?></span>
                            <div class="friend-btns">
                                <div class="dropdown">
                                    <button class="btn btn-icon btn-more btn-sm" data-bs-toggle="dropdown" onclick="event.stopPropagation()">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="profile.php?user=<?= $friend['id'] ?>"><i class="bi bi-person me-2"></i>View Profile</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); openUserChatById(<?= $friend['id'] ?>)"><i class="bi bi-chat-left-text me-2"></i>Chat</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); initiateCall(<?= $friend['id'] ?>)"><i class="bi bi-telephone me-2"></i>Call</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); unfriend(<?= $friend['id'] ?>)"><i class="bi bi-person-x me-2"></i>Unfriend</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p class="text-muted small">No friends yet</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <a href="auth/login.php" class="btn btn-outline w-100 mb-2">Login</a>
                <a href="auth/register.php" class="btn btn-primary w-100">Register</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="app-container">
        <div class="content-wrapper">
            <aside class="sidebar">
                <?php include __DIR__ . '/sidebar.php'; ?>
            </aside>

            <main class="main-content">

<div class="modal fade" id="customModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customModalTitle">Title</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="customModalBody">
                Message goes here
            </div>
            <div class="modal-footer" id="customModalFooter">
                <!-- Buttons will be added dynamically -->
            </div>
        </div>
    </div>
</div>