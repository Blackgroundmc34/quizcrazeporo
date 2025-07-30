<?php
ob_start();
require_once __DIR__ . '/../config.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$response = ['success' => false, 'message' => 'Invalid request.'];

try {
    $data = json_decode(file_get_contents("php://input"));

    if (empty($data->userId) || empty($data->newPassword)) {
        throw new Exception("User ID and new password are required.", 400);
    }

    $conn = getDbConnection();
    $hashed_password = password_hash($data->newPassword, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $data->userId);
    
    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'Password updated successfully!'];
    } else {
        throw new Exception("Failed to update password.", 500);
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