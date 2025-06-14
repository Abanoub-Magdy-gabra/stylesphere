<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/api_response.php';
require_once __DIR__ . '/handlers/CartHandler.php';

// Set CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
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

// Set session cookie if not set
if (!isset($_COOKIE['session_id'])) {
    setcookie('session_id', $sessionId, time() + (86400 * 30), "/"); // 30 days
}

try {
    $db = getDBConnection();
    $cartHandler = new CartHandler($db);
    
    // Get or create cart
    $cart = $cartHandler->getOrCreateCart($userId, $sessionId);
    $cartId = $cart['id'];
    
    // Handle different HTTP methods
    switch ($method) {
        case 'GET':
            // Get cart with items and totals
            $cart['items'] = $cartHandler->getCartItems($cartId);
            $cart['totals'] = $cartHandler->calculateTotals($cartId);
            sendResponse(['cart' => $cart]);
            break;
            
        case 'POST':
            // Add item to cart
            if (empty($data['product_id'])) {
                sendError('Product ID is required', 400);
            }
            
            $productId = $data['product_id'];
            $variantId = $data['variant_id'] ?? null;
            $quantity = max(1, $data['quantity'] ?? 1);
            
            $items = $cartHandler->addToCart($cartId, $productId, $variantId, $quantity);
            $totals = $cartHandler->calculateTotals($cartId);
            
            sendResponse([
                'message' => 'Item added to cart',
                'items' => $items,
                'totals' => $totals
            ], 201);
            break;
            
        case 'PUT':
            // Update cart item quantity
            if (empty($data['item_id']) || !isset($data['quantity'])) {
                sendError('Item ID and quantity are required', 400);
            }
            
            $itemId = $data['item_id'];
            $quantity = max(0, (int)$data['quantity']);
            
            if ($quantity === 0) {
                $items = $cartHandler->removeFromCart($cartId, $itemId);
                $message = 'Item removed from cart';
            } else {
                $items = $cartHandler->updateCartItem($cartId, $itemId, $quantity);
                $message = 'Cart updated';
            }
            
            $totals = $cartHandler->calculateTotals($cartId);
            
            sendResponse([
                'message' => $message,
                'items' => $items,
                'totals' => $totals
            ]);
            break;
            
        case 'DELETE':
            // Remove item from cart or clear cart
            if (isset($_GET['item_id'])) {
                $items = $cartHandler->removeFromCart($cartId, $_GET['item_id']);
                $message = 'Item removed from cart';
            } else {
                $items = $cartHandler->clearCart($cartId);
                $message = 'Cart cleared';
            }
            
            $totals = $cartHandler->calculateTotals($cartId);
            
            sendResponse([
                'message' => $message,
                'items' => $items,
                'totals' => $totals
            ]);
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
