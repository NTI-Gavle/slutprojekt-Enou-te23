<?php
session_start();
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../database/db.php';

// Mock session for testing
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // Assume current user is ID 1
}

echo "<h2>Testing Accept/Decline</h2>";

try {
    $db = getDBConnection();
    
    // Check pending requests for user 1
    $stmt = $db->prepare("
        SELECT f.id, f.user_id, f.friend_id, f.status, u.display_name 
        FROM friends f
        JOIN users u ON u.id = f.user_id
        WHERE f.friend_id = ? AND f.status = 'pending'
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $pending = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Pending Requests:</h3>";
    echo "<pre>" . print_r($pending, true) . "</pre>";
    
    if (count($pending) > 0) {
        echo "<p>Found " . count($pending) . " pending request(s).</p>";
        echo "<p>To test accept: <a href='test_friend.php?action=accept&user_id=" . $pending[0]['user_id'] . "'>Accept</a></p>";
        echo "<p>To test decline: <a href='test_friend.php?action=decline&user_id=" . $pending[0]['user_id'] . "'>Decline</a></p>";
    } else {
        echo "<p>No pending requests found.</p>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Handle accept/decline
if (isset($_GET['action']) && isset($_GET['user_id'])) {
    $action = $_GET['action'];
    $userId = $_GET['user_id'];
    
    try {
        $db = getDBConnection();
        
        // Find the pending request
        $stmt = $db->prepare("SELECT * FROM friends WHERE user_id = ? AND friend_id = ? AND status = 'pending'");
        $stmt->execute([$userId, $_SESSION['user_id']]);
        $request = $stmt->fetch();
        
        if (!$request) {
            echo "<p style='color:red;'>No pending request found for user_id=$userId, friend_id=" . $_SESSION['user_id'] . "</p>";
            exit();
        }
        
        if ($action === 'accept') {
            $stmt = $db->prepare("UPDATE friends SET status = 'accepted' WHERE id = ?");
            $stmt->execute([$request['id']]);
            
            $stmt = $db->prepare("INSERT IGNORE INTO friends (user_id, friend_id, status, created_at) VALUES (?, ?, 'accepted', NOW())");
            $stmt->execute([$_SESSION['user_id'], $userId]);
            
            echo "<p style='color:green;'>Friend request accepted!</p>";
        } else {
            $stmt = $db->prepare("DELETE FROM friends WHERE id = ?");
            $stmt->execute([$request['id']]);
            echo "<p style='color:orange;'>Friend request declined!</p>";
        }
        
        echo "<p><a href='test_friend.php'>Back to test</a></p>";
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
