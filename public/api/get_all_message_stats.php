<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../database/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

try {
    $db = getDBConnection();
    $userId = $_SESSION['user_id'];
    
    // Get first message date
    $stmt = $db->prepare("
        SELECT MIN(DATE(created_at)) as first_date
        FROM messages
        WHERE sender_id = ? AND is_deleted = 0
    ");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    
    if (!$result || !$result['first_date']) {
        echo json_encode([
            'success' => true,
            'total_messages' => 0,
            'days_active' => 0,
            'data_points' => []
        ]);
        exit();
    }
    
    $startDateStr = $result['first_date'];
    $now = new DateTime();
    $nowDateStr = $now->format('Y-m-d');
    $daysActive = (int)(strtotime($nowDateStr) - strtotime($startDateStr)) / 86400;
    if ($daysActive < 0) $daysActive = 0;
    
    // Get all messages grouped by day
    $stmt = $db->prepare("
        SELECT DATE(created_at) as msg_date, COUNT(*) as cnt
        FROM messages
        WHERE sender_id = ? AND is_deleted = 0
        GROUP BY DATE(created_at)
        ORDER BY msg_date ASC
    ");
    $stmt->execute([$userId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Build day map
    $dayMap = [];
    foreach ($messages as $m) {
        $dayMap[$m['msg_date']] = (int)$m['cnt'];
    }
    
    // Build data points
    $dataPoints = [];
    $cumulative = 0;
    $cursor = strtotime($startDateStr);
    $end = strtotime($nowDateStr);
    
    while ($cursor <= $end) {
        $dateKey = date('Y-m-d', $cursor);
        $dayNum = (int)(($cursor - strtotime($startDateStr)) / 86400);
        
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
        'total_messages' => $cumulative,
        'days_active' => $daysActive,
        'data_points' => $dataPoints
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}