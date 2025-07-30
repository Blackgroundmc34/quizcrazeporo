<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'config.php';

$conn = getDbConnection();

$query = "
    SELECT 
        c.category_name,
        t.id AS topic_id,
        t.topic_name
    FROM topics t
    JOIN categories c ON t.category_id = c.id
    ORDER BY c.category_name, t.topic_name ASC
";

$result = $conn->query($query);

$grouped_exams = [];
while ($row = $result->fetch_assoc()) {
    $category_name = $row['category_name'];
    
    if (!isset($grouped_exams[$category_name])) {
        // Determine the icon for the whole category
        $icon_name = 'albums-outline'; // Default icon
        $lower_category = strtolower($category_name);

        if (strpos($lower_category, 'google') !== false) {
            $icon_name = 'logo-google';
        } elseif (strpos($lower_category, 'aws') !== false) {
            $icon_name = 'logo-amazon';
        } elseif (strpos($lower_category, 'microsoft') !== false) {
            $icon_name = 'logo-microsoft';
        } elseif (strpos($lower_category, 'ibm') !== false) {
            $icon_name = 'logo-codepen';
        } elseif (strpos($lower_category, 'oracle') !== false) {
            $icon_name = 'server-outline';
        }

        $grouped_exams[$category_name] = [
            'name' => $category_name,
            'icon' => $icon_name, // Add category icon
            'exams' => []
        ];
    }
    
    $grouped_exams[$category_name]['exams'][] = [
        'id' => $row['topic_id'],
        'name' => $row['topic_name']
    ];
}

$conn->close();

$response = [
    'success' => true,
    'data' => array_values($grouped_exams)
];

echo json_encode($response);
?>