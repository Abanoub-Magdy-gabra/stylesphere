<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/api_response.php';
require_once __DIR__ . '/handlers/OrderHandler.php';

// Set CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
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
            // Get order details
            if (isset($_GET['order_id'])) {
                $order = $orderHandler->getOrder($_GET['order_id'], $userId, $sessionId);
                
                if (!$order) {
                    sendError('Order not found', 404);
                }
                
                sendResponse(['order' => $order]);
            } 
            // Get user orders
            else if ($userId) {
                $page = max(1, (int)($_GET['page'] ?? 1));
                $perPage = max(1, min(50, (int)($_GET['per_page'] ?? 10)));
                
                $orders = $orderHandler->getUserOrders($userId, $page, $perPage);
                sendResponse(['orders' => $orders]);
            } 
            else {
                sendError('Order ID or user ID is required', 400);
            }
            break;
            
        case 'POST':
            // Create new order
            if (empty($data['shipping_address']) || empty($data['payment_method'])) {
                sendError('Shipping address and payment method are required', 400);
            }
            
            $shippingAddress = $data['shipping_address'];
            $paymentMethod = $data['payment_method'];
            $shippingMethod = $data['shipping_method'] ?? 'standard';
            
            // Create order from cart
            $order = $orderHandler->createOrderFromCart($userId, $sessionId, $shippingAddress, $paymentMethod, $shippingMethod);
            
            if (!$order) {
                sendError('Failed to create order. Cart may be empty.', 400);
            }
            
            sendResponse([
                'message' => 'Order created successfully',
                'order' => $order
            ], 201);
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
