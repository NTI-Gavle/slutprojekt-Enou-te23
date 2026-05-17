<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../database/db.php';

if (!isLoggedIn()) {
    die('Login required');
}

try {
    $db = getDBConnection();
    
    echo "<h3>Checking messages table...</h3>";
    
    // Check current columns
    $stmt = $db->query("DESCRIBE messages");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Current columns: " . implode(', ', $columns) . "<br><br>";
    
    // Add message column if missing
    if (!in_array('message', $columns)) {
        $db->exec("ALTER TABLE messages ADD COLUMN message TEXT NOT NULL AFTER room_id");
        echo "Added 'message' column<br>";
    }
    
    // Add attachments if missing
    if (!in_array('attachments', $columns)) {
        $db->exec("ALTER TABLE messages ADD COLUMN attachments JSON AFTER message");
        echo "Added 'attachments' column<br>";
    }
    
    // Verify final structure
    $stmt = $db->query("DESCRIBE messages");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<br>Final columns: " . implode(', ', $columns) . "<br>";
    
    if (in_array('message', $columns)) {
        echo "<br><strong style='color:green'>Messages table is now correct!</strong>";
    } else {
        echo "<br><strong style='color:red'>Error: message column still missing</strong>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}