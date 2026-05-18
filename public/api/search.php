<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../database/db.php';

$query = trim($_GET['q'] ?? '');
$userId = $_GET['user_id'] ?? null;

if ($userId) {
    try {
        $dbconn = getDBConnection();
        
        $stmt = $dbconn->prepare("SELECT id, username, display_name, profile_image FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $user['profile_image'] = getValidProfileImage($user['profile_image'] ?? null);
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

try {
    $dbconn = getDBConnection();
    
    $searchTerm = "%{$query}%";
    
    $stmt = $dbconn->prepare("SELECT id, username, display_name, profile_image FROM users WHERE (username LIKE ? OR display_name LIKE ?) LIMIT 5");
    $stmt->execute([$searchTerm, $searchTerm]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $dbconn->prepare("SELECT id, name, tag, is_private FROM rooms WHERE name LIKE ? OR tag LIKE ? LIMIT 5");
    $stmt->execute([$searchTerm, $searchTerm]);
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as &$u) {
        $u['profile_image'] = getValidProfileImage($u['profile_image'] ?? null);
        $stmt2 = $dbconn->prepare("SELECT id, status FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?) LIMIT 1");
        $stmt2->execute([$_SESSION['user_id'] ?? 0, $u['id'], $u['id'], $_SESSION['user_id'] ?? 0]);
        $friendship = $stmt2->fetch(PDO::FETCH_ASSOC);
        $u['friend_status'] = $friendship ? $friendship['status'] : null;
    }
} catch (Exception $e) {
    error_log('Search error: ' . $e->getMessage());
    echo json_encode(['users' => [], 'rooms' => []]);
    exit();
}

echo json_encode(['users' => $users, 'rooms' => $rooms]);