<?php
// Set the content type to JSON and allow cross-origin requests
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// 1. Include the centralized configuration file
require_once 'config.php';

// 2. Use the new function to get the database connection
$conn = getDbConnection();

// --- Fetch topics grouped by categories ---
$query = "
    SELECT
        c.id AS category_id,
        c.category_name,
        c.category_slug,
        t.id AS topic_id,
        t.topic_name,
        t.topic_slug
    FROM categories c
    LEFT JOIN topics t ON t.category_id = c.id
    ORDER BY c.category_name, t.topic_name";

$result = $conn->query($query);

if (!$result) {
    error_log("Query failed: " . $conn->error);
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Failed to fetch data from database."]);
    exit();
}

// Organize topics by category
$categories = [];
while ($row = $result->fetch_assoc()) {
    $category_id = $row['category_id'];
    
    // Initialize category array if it doesn't exist
    if (!isset($categories[$category_id])) {
        $categories[$category_id] = [
            'id' => $category_id,
            'name' => $row['category_name'],
            'slug' => $row['category_slug'],
            'topics' => [] // Use 'topics' to match app expectation
        ];
    }

    // Add topic only if it exists
    if ($row['topic_id'] !== null) {
        $categories[$category_id]['topics'][] = [
            'id' => $row['topic_id'],
            'name' => $row['topic_name'],
            'slug' => $row['topic_slug']
        ];
    }
}

$conn->close();

// Prepare the final JSON response
$response_data = [
    "success" => true,
    "data" => array_values($categories) // Re-index array for correct JSON format
];

echo json_encode($response_data);

?>