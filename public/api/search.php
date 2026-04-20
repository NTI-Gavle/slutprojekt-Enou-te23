<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');

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
    }
} catch (Exception $e) {
    error_log('Search error: ' . $e->getMessage());
    echo json_encode(['users' => [], 'rooms' => [], 'error' => $e->getMessage()]);
    exit();
}

echo json_encode(['users' => $users, 'rooms' => $rooms]);