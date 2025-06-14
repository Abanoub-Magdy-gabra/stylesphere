<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/api_response.php';

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
    $conn = getDBConnection();
    
    // Get category by ID or slug
    if (isset($_GET['id']) || isset($_GET['slug'])) {
        $field = isset($_GET['id']) ? 'id' : 'slug';
        $value = $_GET[$field];
        
        $query = "SELECT * FROM categories WHERE $field = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param($field === 'id' ? 'i' : 's', $value);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            sendError('Category not found', 404);
        }
        
        $category = $result->fetch_assoc();
        
        // Get subcategories if any
        $subQuery = "SELECT * FROM categories WHERE parent_id = ?";
        $subStmt = $conn->prepare($subQuery);
        $subStmt->bind_param('i', $category['id']);
        $subStmt->execute();
        $subResult = $subStmt->get_result();
        $category['subcategories'] = $subResult->fetch_all(MYSQLI_ASSOC);
        
        sendResponse(['category' => $category]);
    } 
    // Get all top-level categories
    else {
        $query = "SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name ASC";
        $result = $conn->query($query);
        $categories = $result->fetch_all(MYSQLI_ASSOC);
        
        // Get subcategories for each category
        foreach ($categories as &$category) {
            $subQuery = "SELECT * FROM categories WHERE parent_id = ?";
            $subStmt = $conn->prepare($subQuery);
            $subStmt->bind_param('i', $category['id']);
            $subStmt->execute();
            $subResult = $subStmt->get_result();
            $category['subcategories'] = $subResult->fetch_all(MYSQLI_ASSOC);
        }
        
        sendResponse(['categories' => $categories]);
    }
    
} catch (Exception $e) {
    sendError('Server error: ' . $e->getMessage(), 500);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
