<?php
function startSecureSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
        ini_set('session.use_strict_mode', 1);
        session_start();
    }
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin(): void {
    startSecureSession();
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? 'index.php';
        header('Location: login.php');
        exit();
    }
}

function getCurrentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'display_name' => $_SESSION['display_name'] ?? null,
        'profile_image' => getValidProfileImage($_SESSION['profile_image'] ?? null)
    ];
}

function loginUser(int $userId, string $username, string $email, string $displayName, ?string $profileImage = null): void {
    startSecureSession();
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    $_SESSION['display_name'] = $displayName;
    $_SESSION['profile_image'] = getValidProfileImage($profileImage);
    $_SESSION['login_time'] = time();
}

function logoutUser(): void {
    startSecureSession();
    $_SESSION = [];
    
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
}

function regenerateSession(): void {
    startSecureSession();
    session_regenerate_id(true);
}

function setFlashMessage(string $type, string $message): void {
    startSecureSession();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlashMessage(): ?array {
    startSecureSession();
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function getValidProfileImage(?string $imagePath): string {
    // Handle empty or any default-avatar reference
    if (empty($imagePath) || strpos($imagePath, 'default-avatar') !== false) {
        return 'img/default-avatar.svg';
    }
    
    // Calculate correct path relative to project root
    $projectRoot = dirname(__DIR__);
    $fullPath = $projectRoot . '/public/' . $imagePath;
    
    if (file_exists($fullPath)) {
        return $imagePath;
    }
    
    return 'img/default-avatar.svg';
}

function displayFlashMessages(): string {
    $flash = getFlashMessage();
    if (!$flash) {
        return '';
    }
    
    $typeClass = match($flash['type']) {
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info',
        default => 'alert-info'
    };
    
    return "<div class='alert {$typeClass} alert-dismissible fade show' role='alert'>
        {$flash['message']}
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    </div>";
}

startSecureSession();
