<?php
ob_start();
require_once 'config.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$response = ['success' => false, 'message' => 'Invalid request.'];

try {
    if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
        throw new Exception("User ID is required.", 400);
    }
    $conn = getDbConnection();
    
    $stmt = $conn->prepare("SELECT id, username, email, phone_number FROM users WHERE id = ?");
    $stmt->bind_param("i", $_GET['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        $response = ['success' => true, 'user' => $user];
    } else {
        throw new Exception("User not found.", 404);
    }
    
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    ob_clean();
    $code = $e->getCode() > 0 ? $e->getCode() : 500;
    http_response_code($code);
    $response['message'] = $e->getMessage();
}

ob_end_clean();
echo json_encode($response);
?>