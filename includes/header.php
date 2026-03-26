<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quacko <?= isset($pageTitle) ? "| " . htmlspecialchars($pageTitle) : "" ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/styles.css">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="js/app.js" defer></script>
</head>

<body>
    <header class="site-header">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between py-2">
                <div class="d-flex align-items-center">
                    <div class="logo">
                        <span>Q</span>
                    </div>
                    <a href="index.php" class="brand ms-2">Quacko <span class="text-body-secondary fst-italic fs-6">
                            ● <?= htmlspecialchars($pageTitle) ?>
                        </span>
                    </a>
                </div>

                <div class="d-none d-md-flex align-items-center gap-3">
                    <form class="search-bar">
                        <input type="search" class="form-control" placeholder="Search...">
                        <button class="btn btn-search" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <img src="<?= $userProfileImage ?? 'img/default-avatar.png' ?>" alt="Profile" class="avatar">
                        <a href="profile.php" class="btn btn-outline btn-sm">Profile</a>
                        <a href="logout.php" class="btn btn-outline btn-sm">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline">Login</a>
                        <a href="register.php" class="btn btn-primary">Register</a>
                    <?php endif; ?>
                </div>

                <button class="hamburger" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu">
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </div>
    </header>

    <div class="offcanvas offcanvas-start mobile-nav" tabindex="-1" id="mobileMenu">
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
                <div class="mobile-user mb-3">
                    <img src="<?= $userProfileImage ?? 'img/default-avatar.png' ?>" alt="Profile" class="avatar">
                    <span><?= htmlspecialchars($_SESSION['display_name'] ?? 'User') ?></span>
                </div>
                <a href="profile.php" class="btn btn-outline w-100 mb-2">Profile</a>
                <a href="logout.php" class="btn btn-outline w-100">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline w-100 mb-2">Login</a>
                <a href="register.php" class="btn btn-primary w-100">Register</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="app-container">
        <aside class="sidebar">
            <?php include __DIR__ . '/sidebar.php'; ?>
        </aside>

        <main class="main-content">