<?php
ob_start();
require_once 'config.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$response = ['success' => false, 'message' => 'Invalid request.'];

try {
    $data = json_decode(file_get_contents("php://input"));

    if (empty($data->username) || empty($data->email) || empty($data->password)) {
        throw new Exception("All fields are required.", 400);
    }

    $conn = getDbConnection();
    
    // Check if user already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $data->username, $data->email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        throw new Exception("Username or email already exists.", 409);
    }
    $stmt->close();

    // Insert new user
    $hashed_password = password_hash($data->password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $data->username, $data->email, $hashed_password);
    
    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'Registration successful!'];
    } else {
        throw new Exception("Registration failed. Please try again.", 500);
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