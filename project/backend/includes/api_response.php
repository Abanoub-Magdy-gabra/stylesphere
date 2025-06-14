<?php
header('Content-Type: application/json');

function sendResponse($data = null, $status = 200, $message = 'Success') {
    http_response_code($status);
    echo json_encode([
        'status' => $status < 400 ? 'success' : 'error',
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

function sendError($message = 'An error occurred', $status = 500) {
    sendResponse(null, $status, $message);
}
?>
