<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');
$userId = $_GET['user_id'] ?? null;

// Handle direct user lookup by ID (for opening private chat)
if ($userId) {
    $hostname = 'localhost';
    $dbname = 'quacko';
    $DB_USER = 'root';
    $DB_PASSWORD = 'EnDa5792!';
    
    try {
        $dbconn = new PDO(
            "mysql:host={$hostname};dbname={$dbname};charset=utf8mb4",
            $DB_USER,
            $DB_PASSWORD,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        $stmt = $dbconn->prepare("SELECT id, username, display_name, profile_image FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            if (empty($user['profile_image']) || $user['profile_image'] === 'img/default-avatar.png') {
                $user['profile_image'] = 'img/default-avatar.svg';
            }
            echo json_encode(['users' => [$user], 'rooms' => []]);
        } else {
            echo json_encode(['users' => [], 'rooms' => []]);
        }
    } catch (Exception $e) {
        echo json_encode(['users' => [], 'rooms' => []]);
    }
    exit();
}

if (strlen($query) < 2) {
    echo json_encode(['users' => [], 'rooms' => []]);
    exit();
}

$hostname = 'localhost';
$dbname = 'quacko';
$DB_USER = 'root';
$DB_PASSWORD = 'EnDa5792!';

try {
    $dbconn = new PDO(
        "mysql:host={$hostname};dbname={$dbname};charset=utf8mb4",
        $DB_USER,
        $DB_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    $searchTerm = "%{$query}%";
    
    $stmt = $dbconn->prepare("SELECT id, username, display_name, profile_image FROM users WHERE (username LIKE ? OR display_name LIKE ?) LIMIT 5");
    $stmt->execute([$searchTerm, $searchTerm]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $dbconn->prepare("SELECT id, name, tag FROM rooms WHERE name LIKE ? OR tag LIKE ? LIMIT 5");
    $stmt->execute([$searchTerm, $searchTerm]);
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as &$u) {
        if (empty($u['profile_image']) || $u['profile_image'] === 'img/default-avatar.png') {
            $u['profile_image'] = 'img/default-avatar.svg';
        }
        // Check if already friends or request pending
        $stmt2 = $dbconn->prepare("SELECT id, status FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?) LIMIT 1");
        $stmt2->execute([$_SESSION['user_id'] ?? 0, $u['id'], $u['id'], $_SESSION['user_id'] ?? 0]);
        $friendship = $stmt2->fetch(PDO::FETCH_ASSOC);
        $u['friend_status'] = $friendship ? $friendship['status'] : null;
    }
} catch (Exception $e) {
    error_log('Search error: ' . $e->getMessage());
    echo json_encode(['users' => [], 'rooms' => [], 'error' => $e->getMessage()]);
    exit();
}

echo json_encode(['users' => $users, 'rooms' => $rooms]);