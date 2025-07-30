<?php
ob_start();
// Assumes config.php is one level up from the 'api' folder
require_once 'config.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-cache, must-revalidate");

// Helper function to create "time ago" strings
function time_ago($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year', 'm' => 'month', 'w' => 'week', 'd' => 'day',
        'h' => 'hour', 'i' => 'minute', 's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}


$response = ['success' => false, 'message' => 'An unknown error occurred.'];

try {
    $conn = getDbConnection();

    // Fetch the 5 most recent activities
    $stmt = $conn->prepare("
        SELECT id, activity_text, activity_timestamp 
        FROM user_activity 
        ORDER BY activity_timestamp DESC 
        LIMIT 5
    ");

    if (!$stmt) {
        throw new Exception("Database query preparation failed.", 500);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    
    $activities = [];
    while ($row = $result->fetch_assoc()) {
        $activities[] = [
            'id'   => $row['id'],
            'text' => $row['activity_text'],
            'time' => time_ago($row['activity_timestamp']) // Use the helper to format time
        ];
    }
    
    $stmt->close();
    $conn->close();

    $response = ['success' => true, 'data' => $activities];

} catch (Exception $e) {
    ob_clean();
    $code = $e->getCode() > 0 ? $e->getCode() : 500;
    http_response_code($code);
    $response['message'] = "Server Error: " . $e->getMessage();
}

ob_end_clean();
echo json_encode($response);
?>