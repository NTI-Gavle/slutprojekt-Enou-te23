<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../database/db.php';

if (!isLoggedIn()) {
    die('Not logged in');
}

try {
    $db = getDBConnection();
    
    // Check if last_activity column exists
    $stmt = $db->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('last_activity', $columns)) {
        $db->exec("ALTER TABLE users ADD COLUMN last_activity DATETIME DEFAULT NULL AFTER profile_image");
        echo "Added last_activity column to users table!";
    } else {
        echo "last_activity column already exists.";
    }
    
    // Check if updated_at column exists in friends table  
    $stmt = $db->query("DESCRIBE friends");
    $friendColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('updated_at', $friendColumns)) {
        $db->exec("ALTER TABLE friends ADD COLUMN updated_at DATETIME DEFAULT NULL AFTER created_at");
        echo "<br>Added updated_at column to friends table!";
    } else {
        echo "<br>updated_at column already exists.";
    }
    
    // Check if read_status column exists in messages table
    $stmt = $db->query("DESCRIBE messages");
    $msgColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('read_status', $msgColumns)) {
        $db->exec("ALTER TABLE messages ADD COLUMN read_status TINYINT(1) DEFAULT 0 AFTER is_deleted");
        echo "<br>Added read_status column to messages table!";
    } else {
        echo "<br>read_status column already exists.";
    }
    
    // Check if is_admin column exists in users table
    $stmt = $db->query("DESCRIBE users");
    $userColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('is_admin', $userColumns)) {
        $db->exec("ALTER TABLE users ADD COLUMN is_admin TINYINT(1) DEFAULT 0 AFTER last_activity");
        echo "<br>Added is_admin column to users table!";
    } else {
        echo "<br>is_admin column already exists.";
    }
    
    // Check if role column exists in room_members table
    $stmt = $db->query("DESCRIBE room_members");
    $rmColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('role', $rmColumns)) {
        $db->exec("ALTER TABLE room_members ADD COLUMN role ENUM('admin', 'moderator', 'member') DEFAULT 'member' AFTER joined_at");
        echo "<br>Added role column to room_members table!";
    } else {
        echo "<br>role column already exists.";
    }
    
    // Check if warning column exists in users table
    if (!in_array('warning', $userColumns)) {
        $db->exec("ALTER TABLE users ADD COLUMN warning TEXT NULL AFTER is_admin");
        echo "<br>Added warning column to users table!";
    } else {
        echo "<br>warning column already exists.";
    }
    
    // Check if attachments column exists in messages table
    $stmt = $db->query("DESCRIBE messages");
    $msgColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('attachments', $msgColumns)) {
        $db->exec("ALTER TABLE messages ADD COLUMN attachments JSON NULL AFTER content");
        echo "<br>Added attachments column to messages table!";
    } else {
        echo "<br>attachments column already exists.";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}