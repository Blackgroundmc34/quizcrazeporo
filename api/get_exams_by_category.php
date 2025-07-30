<?php
// Set headers for JSON response and allow cross-origin requests
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Include the centralized configuration file
require_once 'config.php';
$conn = getDbConnection();

// Query to get all categories and their corresponding topics
$query = "
    SELECT
        c.id AS category_id,
        c.category_name,
        c.category_slug,
        t.id AS topic_id,
        t.topic_name,
        t.topic_slug
    FROM categories c
    LEFT JOIN topics t ON c.id = t.category_id
    ORDER BY c.category_name, t.topic_name";

$result = $conn->query($query);

if (!$result) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database query failed."]);
    exit();
}

$categories = [];
while ($row = $result->fetch_assoc()) {
    $category_id = $row['category_id'];
    
    // Initialize the category if it's the first time we've seen it
    if (!isset($categories[$category_id])) {
        $categories[$category_id] = [
            'id'     => $category_id,
            'name'   => $row['category_name'],
            'slug'   => $row['category_slug'],
            'topics' => [] // Array for topics
        ];
    }

    // If a topic exists, add it to the category's topic list
    if ($row['topic_id'] !== null) {
        $categories[$category_id]['topics'][] = [
            'id'   => $row['topic_id'],
            'name' => $row['topic_name'],
            'slug' => $row['topic_slug']
        ];
    }
}
$conn->close();

// Prepare the final JSON response with the direct list of categories
$response_data = [
    "success" => true,
    "data"    => array_values($categories) // Return the direct array of categories
];

echo json_encode($response_data);
?>