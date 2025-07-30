<?php
ob_start();
require_once 'config.php'; 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-cache, must-revalidate");

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

try {
    if (!isset($_GET['exam_id']) || !is_numeric($_GET['exam_id'])) {
        throw new Exception("A valid exam_id is required.", 400);
    }
    $exam_id = (int)$_GET['exam_id'];
    $conn = getDbConnection();

    $stmt = $conn->prepare("
        SELECT 
            q.id AS question_id,
            q.question_text,
            a.answer_text,
            a.is_correct
        FROM questions q
        JOIN answers a ON q.id = a.question_id
        WHERE q.topic_id = ?
        ORDER BY q.id, a.id
    ");

    if (!$stmt) throw new Exception("Database query preparation failed.", 500);

    $stmt->bind_param("i", $exam_id);
    if (!$stmt->execute()) throw new Exception("Database query execution failed.", 500);

    $result = $stmt->get_result();
    $questions_map = [];
    while ($row = $result->fetch_assoc()) {
        $qid = $row['question_id'];
        if (!isset($questions_map[$qid])) {
            $questions_map[$qid] = [
                'question' => strip_tags($row['question_text']), // Strip HTML from question
                'options'  => [],
                'answer'   => ''
            ];
        }
        $answer_text = strip_tags($row['answer_text']); // Strip HTML from answer
        $questions_map[$qid]['options'][] = $answer_text;
        if ($row['is_correct']) {
            $questions_map[$qid]['answer'] = $answer_text;
        }
    }
    
    $stmt->close();
    $conn->close();

    $questions = array_values($questions_map);
    $response = ['success' => true, 'data' => $questions];

} catch (Exception $e) {
    ob_clean(); 
    $code = $e->getCode() > 0 ? $e->getCode() : 500;
    http_response_code($code);
    $response['message'] = "Server Error: " . $e->getMessage();
}

ob_end_clean();
echo json_encode($response);
?>