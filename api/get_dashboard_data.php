<?php
// api/get_dashboard_data.php

// Allow cross-origin requests from any origin (for development)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database configuration
// Assuming config.php is one level up from the 'api' directory
require_once 'config.php';

$response = [
    'success' => false,
    'data' => [],
    'message' => ''
];

try {
    // Establish database connection
    $conn = getDbConnection();

    // Get total number of exams (topics)
    $sql_total_exams = "SELECT COUNT(id) AS total_exams FROM topics";
    $result_exams = $conn->query($sql_total_exams);
    $total_exams = 0;
    if ($result_exams && $row = $result_exams->fetch_assoc()) {
        $total_exams = (int)$row['total_exams'];
    }

    // Get total number of categories
    $sql_total_categories = "SELECT COUNT(id) AS total_categories FROM categories";
    $result_categories = $conn->query($sql_total_categories);
    $total_categories = 0;
    if ($result_categories && $row = $result_categories->fetch_assoc()) {
        $total_categories = (int)$row['total_categories'];
    }

    // Placeholder for practice tests count (e.g., total questions available for practice)
    // You might adjust this based on how you define "practice tests" in your data
    $sql_total_questions = "SELECT COUNT(id) AS total_questions FROM questions";
    $result_questions = $conn->query($sql_total_questions);
    $total_practice_questions = 0;
    if ($result_questions && $row = $result_questions->fetch_assoc()) {
        $total_practice_questions = (int)$row['total_questions'];
    }

    // Close the database connection
    $conn->close();

    $response['success'] = true;
    $response['data'] = [
        'totalExams' => $total_exams,
        'totalCategories' => $total_categories,
        'practiceTestsCount' => $total_practice_questions, // Using total questions as a proxy
        'userScore' => 'N/A', // Placeholder, will need user authentication/tracking for real data
    ];
    $response['message'] = 'Dashboard data fetched successfully.';

} catch (Exception $e) {
    error_log("Error in get_dashboard_data.php: " . $e->getMessage());
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
?>
