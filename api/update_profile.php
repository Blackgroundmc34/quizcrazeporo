<?php
ob_start();
require_once 'config.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$response = ['success' => false, 'message' => 'Invalid request.'];

try {
    $data = json_decode(file_get_contents("php://input"));

    if (empty($data->userId) || empty($data->username)) {
        throw new Exception("User ID and username are required.", 400);
    }

    $conn = getDbConnection();
    
    $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
    $stmt->bind_param("si", $data->username, $data->userId);
    
    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'Profile updated successfully!'];
    } else {
        throw new Exception("Failed to update profile.", 500);
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