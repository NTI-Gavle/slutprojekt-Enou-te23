<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../database/db.php';

if (!isLoggedIn()) {
    die('Not logged in');
}

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'unknown';

echo "<h2>Current User: ID=$userId, Username=$username</h2>";

echo "<h3>Messages Table Structure:</h3>";
$stmt = getDBConnection()->query("DESCRIBE messages");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($columns);
echo "</pre>";

echo "<h3>All Messages for User $userId:</h3>";
$stmt = getDBConnection()->prepare("
    SELECT m.*, 
           u1.display_name as sender_name,
           u2.display_name as receiver_name
    FROM messages m
    LEFT JOIN users u1 ON u1.id = m.sender_id
    LEFT JOIN users u2 ON u2.id = m.receiver_id
    WHERE m.sender_id = ? OR m.receiver_id = ?
    ORDER BY m.created_at DESC
    LIMIT 20
");
$stmt->execute([$userId, $userId]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($messages)) {
    echo "<p>No messages found</p>";
} else {
    echo "<pre>";
    print_r($messages);
    echo "</pre>";
}

echo "<h3>Test: Get messages with user 5 (Fred):</h3>";
$otherUserId = ($userId == 5) ? 6 : 5;
$stmt = getDBConnection()->prepare("
    SELECT m.*, u.display_name as sender_name, u.profile_image as sender_avatar
    FROM messages m
    JOIN users u ON u.id = m.sender_id
    WHERE ((m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?))
    ORDER BY m.created_at DESC
    LIMIT 10
");
$stmt->execute([$userId, $otherUserId, $otherUserId, $userId]);
$msgs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($msgs)) {
    echo "<p>No messages between you and user $otherUserId</p>";
} else {
    echo "<pre>";
    print_r($msgs);
    echo "</pre>";
}