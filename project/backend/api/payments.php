<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/api_response.php';
require_once __DIR__ . '/handlers/PaymentHandler.php';

// Set CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

// Get request data
$data = json_decode(file_get_contents('php://input'), true) ?? [];

// Get user ID from session or token (in a real app, you'd validate a JWT or session)
$userId = $data['user_id'] ?? null;
$sessionId = $_COOKIE['session_id'] ?? session_id();

try {
    $db = getDBConnection();
    $paymentHandler = new PaymentHandler($db);
    
    // Process payment
    if (empty($data['order_id'])) {
        sendError('Order ID is required', 400);
    }
    
    if (empty($data['payment_details'])) {
        sendError('Payment details are required', 400);
    }
    
    $orderId = $data['order_id'];
    $paymentDetails = $data['payment_details'];
    
    // Process the payment
    $paymentResult = $paymentHandler->processPayment($orderId, $paymentDetails, $userId, $sessionId);
    
    if (!$paymentResult['success']) {
        sendError($paymentResult['message'] ?? 'Payment processing failed', 400);
    }
    
    // Update order status
    $paymentHandler->updateOrderPaymentStatus($orderId, 'paid', $paymentResult['payment_id']);
    
    // Return payment confirmation
    sendResponse([
        'message' => 'Payment processed successfully',
        'payment_id' => $paymentResult['payment_id'],
        'last_four' => $paymentResult['last_four'] ?? null,
        'card_type' => $paymentResult['card_type'] ?? null
    ]);
    
} catch (Exception $e) {
    sendError('Server error: ' . $e->getMessage(), 500);
} finally {
    if (isset($db)) {
        $db->close();
    }
}
?>
