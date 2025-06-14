<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/api_response.php';
require_once __DIR__ . '/handlers/ProductHandler.php';

// Set CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed', 405);
}

try {
    $db = getDBConnection();
    $productHandler = new ProductHandler($db);
    
    // Get product by ID or slug
    if (isset($_GET['id'])) {
        $product = $productHandler->getProduct($_GET['id'], false);
        if (!$product) {
            sendError('Product not found', 404);
        }
        
        // Get recommended products based on style
        $recommendations = [];
        if (!empty($product['styles'])) {
            $primaryStyle = $product['styles'][0]['style_name'];
            $recommendations = $productHandler->getProductsByStyle($primaryStyle, 4, $product['id']);
        }
        
        sendResponse([
            'product' => $product,
            'recommendations' => $recommendations
        ]);
    } 
    // Get products with filters
    else {
        $filters = [
            'category_id' => $_GET['category_id'] ?? null,
            'style' => $_GET['style'] ?? null,
            'featured' => isset($_GET['featured']) ? (bool)$_GET['featured'] : null,
            'page' => max(1, (int)($_GET['page'] ?? 1)),
            'per_page' => max(1, min(50, (int)($_GET['per_page'] ?? 12)))
        ];
        
        $products = $productHandler->getProducts($filters);
        sendResponse(['products' => $products]);
    }
    
} catch (Exception $e) {
    sendError('Server error: ' . $e->getMessage(), 500);
} finally {
    if (isset($db)) {
        $db->close();
    }
}
?>
