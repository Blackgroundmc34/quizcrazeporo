<?php
ob_start();
require_once 'config.php'; 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$response = ['success' => false, 'message' => 'Invalid request.'];

try {
    $data = json_decode(file_get_contents("php://input"));

    if (empty($data->examName)) {
        throw new Exception("Exam name is required.", 400);
    }

    $conn = getDbConnection();
    
    // User ID is optional for now, but you can add it later
    $userId = isset($data->userId) ? $data->userId : null;
    $details = isset($data->details) ? $data->details : '';

    $stmt = $conn->prepare("INSERT INTO exam_requests (user_id, exam_name, details) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $userId, $data->examName, $details);
    
    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'Your request has been submitted successfully!'];
    } else {
        throw new Exception("Failed to submit your request. Please try again.", 500);
    }
    
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    ob_clean();
    $code = $e->getCode() > 0 ? $e->getCode() : 500;
    http_response_code($code);
    $response['message'] = "Server Error: " . $e->getMessage();
}

ob_end_clean();
echo json_encode($response);
?>