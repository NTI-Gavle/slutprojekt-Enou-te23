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
if (isLoggedIn()) {
    require_once __DIR__ . '/../database/db.php';
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT warning FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $currentUser = $stmt->fetch();
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
            <form class="mb-3">
                <input type="search" class="form-control" placeholder="Search...">
            </form>

<?php if (isset($_SESSION['user_id'])): ?>
                <?php 
                $img = getValidProfileImage($_SESSION['profile_image'] ?? null);
                ?>
                <div class="mobile-user mb-3">
                    <img src="<?= $img ?>" alt="Profile" class="avatar">
                    <span><?= htmlspecialchars($_SESSION['display_name'] ?? 'User') ?></span>
                </div>
                <a href="profile.php" class="btn btn-outline w-100 mb-2">Profile</a>
                <hr>
                <a href="index.php" class="btn btn-outline w-100 mb-2">Home</a>
                <a href="about.php" class="btn btn-outline w-100 mb-2">About Us</a>
                <a href="legal.php" class="btn btn-outline w-100 mb-2">Legal</a>
                <hr>
                <a href="auth/process-logout.php" class="btn btn-outline w-100">Logout</a>
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