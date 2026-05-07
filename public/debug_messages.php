<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../database/db.php';

if (!isLoggedIn()) {
    die('Not logged in');
}

echo "<h2>Debug: Get All Message Stats</h2>";

try {
    $db = getDBConnection();
    
    // Test all messages query
    $stmt = $db->prepare("
        SELECT MIN(created_at) as first_message, COUNT(*) as total
        FROM messages
        WHERE sender_id = ? AND is_deleted = 0
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>First message: " . ($result['first_message'] ?? 'NULL') . "</p>";
    echo "<p>Total messages: " . ($result['total'] ?? 0) . "</p>";
    
    // Test daily messages
    $stmt = $db->prepare("
        SELECT DATE(created_at) as msg_date, COUNT(*) as count
        FROM messages
        WHERE sender_id = ? AND is_deleted = 0
        GROUP BY DATE(created_at)
        ORDER BY msg_date ASC
        LIMIT 10
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $daily = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Daily messages (first 10):</h3>";
    echo "<pre>";
    print_r($daily);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "<hr><h2>Debug: Get Message Stats (with friend 6)</h2>";

try {
    $db = getDBConnection();
    $friendId = 6;
    
    // Get when friendship started
    $stmt = $db->prepare("
        SELECT created_at FROM friends 
        WHERE ((user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?))
        AND status = 'accepted'
        ORDER BY created_at ASC
        LIMIT 1
    ");
    $stmt->execute([$_SESSION['user_id'], $friendId, $friendId, $_SESSION['user_id']]);
    $friendship = $stmt->fetch();
    
    echo "<p>Friendship started: " . ($friendship['created_at'] ?? 'NULL') . "</p>";
    
    if ($friendship) {
        // Get messages between users
        $stmt = $db->prepare("
            SELECT DATE(created_at) as msg_date, COUNT(*) as count
            FROM messages
            WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?))
            AND created_at >= ?
            AND is_deleted = 0
            GROUP BY DATE(created_at)
            ORDER BY msg_date ASC
            LIMIT 10
        ");
        $stmt->execute([$_SESSION['user_id'], $friendId, $friendId, $_SESSION['user_id'], $friendship['created_at']]);
        $daily = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Daily messages with friend 5 (first 10):</h3>";
        echo "<pre>";
        print_r($daily);
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "<hr><h2>Your Friends</h2>";
$stmt = getDBConnection()->prepare("
    SELECT f.*, u.display_name, u.username 
    FROM friends f 
    JOIN users u ON u.id = CASE WHEN f.user_id = ? THEN f.friend_id ELSE f.user_id END
    WHERE (f.user_id = ? OR f.friend_id = ?) AND f.status = 'accepted'
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
$friends = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($friends);
echo "</pre>";