<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/api_response.php';
require_once __DIR__ . '/handlers/OrderHandler.php';

// Set CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get request data
$data = json_decode(file_get_contents('php://input'), true) ?? [];

// Get user ID from session or token (in a real app, you'd validate a JWT or session)
$userId = $_GET['user_id'] ?? $data['user_id'] ?? null;
$sessionId = $_COOKIE['session_id'] ?? session_id();

try {
    $db = getDBConnection();
    $orderHandler = new OrderHandler($db);
    
    switch ($method) {
        case 'GET':
            // Check order status
            if (empty($_GET['order_id'])) {
                sendError('Order ID is required', 400);
            }
            
            $orderId = $_GET['order_id'];
            $orderStatus = $orderHandler->getOrderStatus($orderId, $userId, $sessionId);
            
            if (!$orderStatus) {
                sendError('Order not found or access denied', 404);
            }
            
            sendResponse(['status' => $orderStatus]);
            break;
            
        case 'PUT':
            // Update order status (admin only in a real app)
            if (empty($data['order_id']) || empty($data['status'])) {
                sendError('Order ID and status are required', 400);
            }
            
            // In a real app, you would check if the user has admin privileges
            // For this demo, we'll allow any update
            $orderId = $data['order_id'];
            $status = $data['status'];
            
            $success = $orderHandler->updateOrderStatus($orderId, $status);
            
            if (!$success) {
                sendError('Failed to update order status', 400);
            }
            
            sendResponse(['message' => 'Order status updated successfully']);
            break;
            
        default:
            sendError('Method not allowed', 405);
    }
    
} catch (Exception $e) {
    sendError('Server error: ' . $e->getMessage(), 500);
} finally {
    if (isset($db)) {
        $db->close();
    }
}
?>
