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
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}