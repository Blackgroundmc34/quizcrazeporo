<?php
ob_start();
require_once  'config.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$response = ['success' => false, 'message' => 'Invalid request.'];

try {
    $data = json_decode(file_get_contents("php://input"));

    if (empty($data->email) || empty($data->password)) {
        throw new Exception("Email and password are required.", 400);
    }

    $conn = getDbConnection();
    
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $data->email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($data->password, $user['password'])) {
            // In a real app, you would generate a token here (e.g., JWT)
            $response = [
                'success' => true, 
                'message' => 'Login successful!',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username']
                ]
            ];
        } else {
            throw new Exception("Invalid credentials.", 401);
        }
    } else {
        throw new Exception("Invalid credentials.", 401);
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