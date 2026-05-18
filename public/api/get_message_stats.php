<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../database/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$friendId = $_GET['friend_id'] ?? null;

if (!$friendId) {
    echo json_encode(['success' => false, 'message' => 'No friend specified']);
    exit();
}

try {
    $db = getDBConnection();
    $userId = $_SESSION['user_id'];
    
    // Get when the friendship started
    $stmt = $db->prepare("
        SELECT created_at FROM friends 
        WHERE ((user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?))
        AND status = 'accepted'
        ORDER BY created_at ASC
        LIMIT 1
    ");
    $stmt->execute([$userId, $friendId, $friendId, $userId]);
    $friendship = $stmt->fetch();
    
    if (!$friendship) {
        echo json_encode(['success' => false, 'message' => 'Not friends']);
        exit();
    }
    
    $friendSince = new DateTime($friendship['created_at']);
    $now = new DateTime();
    
    $friendSinceDate = $friendSince->format('Y-m-d');
    $nowDate = $now->format('Y-m-d');
    $daysDiff = (int)(strtotime($nowDate) - strtotime($friendSinceDate)) / 86400;
    if ($daysDiff < 0) $daysDiff = 0;
    
    // Get all messages between users
    $stmt = $db->prepare("
        SELECT DATE(created_at) as msg_date, COUNT(*) as cnt
        FROM messages
        WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?))
        AND is_deleted = 0
        GROUP BY DATE(created_at)
        ORDER BY msg_date ASC
    ");
    $stmt->execute([$userId, $friendId, $friendId, $userId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Build day map
    $dayMap = [];
    foreach ($messages as $m) {
        $dayMap[$m['msg_date']] = (int)$m['cnt'];
    }
    
    // Build data points from friendship start to now (date only)
    $dataPoints = [];
    $cumulative = 0;
    $cursor = strtotime($friendSinceDate);
    $end = strtotime($nowDate);
    
    while ($cursor <= $end) {
        $dateKey = date('Y-m-d', $cursor);
        $dayNum = (int)(($cursor - strtotime($friendSinceDate)) / 86400);
        
        if (isset($dayMap[$dateKey])) {
            $cumulative += $dayMap[$dateKey];
        }
        
        $dataPoints[] = [
            'day' => $dayNum,
            'count' => $cumulative
        ];
        
        $cursor = strtotime('+1 day', $cursor);
    }
    
    echo json_encode([
        'success' => true,
        'friend_since' => $friendship['created_at'],
        'total_messages' => $cumulative,
        'days_friends' => $daysDiff,
        'data_points' => $dataPoints
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}