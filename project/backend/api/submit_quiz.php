<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/api_response.php';

// Set CORS headers to allow requests from your frontend
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (empty($input['answers'])) {
    sendError('No answers provided', 400);
}

try {
    $conn = getDBConnection();
    
    // Example: Save quiz results to database
    $stmt = $conn->prepare("INSERT INTO quiz_results (user_id, answers, created_at) VALUES (?, ?, NOW())");
    $user_id = $input['user_id'] ?? null;
    $answers_json = json_encode($input['answers']);
    
    $stmt->bind_param("is", $user_id, $answers_json);
    
    if ($stmt->execute()) {
        $result_id = $conn->insert_id;
        sendResponse(['result_id' => $result_id], 201, 'Quiz submitted successfully');
    } else {
        sendError('Failed to save quiz results', 500);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    sendError('Database error: ' . $e->getMessage(), 500);
}
?>
