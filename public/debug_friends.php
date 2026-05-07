<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../database/db.php';

if (!isLoggedIn()) {
    die('Not logged in');
}

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'unknown';

echo "<h2>Current User: ID=$userId, Username=$username</h2>";

echo "<h3>All Friends Records for You:</h3>";
$stmt = getDBConnection()->prepare("SELECT * FROM friends WHERE user_id = ? OR friend_id = ?");
$stmt->execute([$userId, $userId]);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($records)) {
    echo "<p>No records found</p>";
} else {
    echo "<pre>";
    print_r($records);
    echo "</pre>";
}

echo "<h3>Friends Query Result (accepted only):</h3>";
$stmt = getDBConnection()->prepare("
    SELECT DISTINCT u.id, u.display_name, u.profile_image,
           CASE WHEN u.last_activity >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN 1 ELSE 0 END as is_online
    FROM friends f
    JOIN users u ON u.id = 
        CASE WHEN f.user_id = ? THEN f.friend_id ELSE f.user_id END
    WHERE (f.user_id = ? OR f.friend_id = ?) 
    AND f.status = 'accepted'
");
$stmt->execute([$userId, $userId, $userId]);
$friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<p>Query: SELECT with parameters $userId, $userId, $userId</p>";

if (empty($friends)) {
    echo "<p>No friends found - THIS IS THE BUG!</p>";
} else {
    echo "<p><strong>FOUND " . count($friends) . " FRIEND(S):</strong></p>";
    echo "<pre>";
    print_r($friends);
    echo "</pre>";
}

echo "<h3>Pending Requests (incoming):</h3>";
$stmt = getDBConnection()->prepare("
    SELECT f.id, u.id as user_id, u.display_name, u.profile_image 
    FROM friends f 
    JOIN users u ON u.id = f.user_id 
    WHERE f.friend_id = ? AND f.status = 'pending'
");
$stmt->execute([$userId]);
$pending = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($pending)) {
    echo "<p>No pending requests</p>";
} else {
    echo "<pre>";
    print_r($pending);
    echo "</pre>";
}

echo "<h3>Sent Requests (outgoing):</h3>";
$stmt = getDBConnection()->prepare("
    SELECT f.id, u.id as user_id, u.display_name, u.profile_image 
    FROM friends f 
    JOIN users u ON u.id = f.friend_id
    WHERE f.user_id = ? AND f.status = 'pending'
");
$stmt->execute([$userId]);
$sent = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($sent)) {
    echo "<p>No sent requests</p>";
} else {
    echo "<pre>";
    print_r($sent);
    echo "</pre>";
}