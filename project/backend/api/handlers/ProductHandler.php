<?php
class ProductHandler {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all products with optional filters
    public function getProducts($filters = []) {
        $query = "SELECT p.*, 
                 (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
                 FROM products p WHERE p.is_active = 1";
        
        $params = [];
        $types = '';
        
        // Apply filters
        if (!empty($filters['category_id'])) {
            $query .= " AND p.category_id = ?";
            $params[] = $filters['category_id'];
            $types .= 'i';
        }
        
        if (!empty($filters['style'])) {
            $query .= " AND p.style_type = ?";
            $params[] = $filters['style'];
            $types .= 's';
        }
        
        if (!empty($filters['featured'])) {
            $query .= " AND p.is_featured = 1";
        }
        
        // Add sorting
        $query .= " ORDER BY p.created_at DESC";
        
        // Add pagination
        $page = $filters['page'] ?? 1;
        $perPage = $filters['per_page'] ?? 12;
        $offset = ($page - 1) * $perPage;
        $query .= " LIMIT ?, ?";
        $params[] = $offset;
        $params[] = $perPage;
        $types .= 'ii';
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
        
        // Get variants and images for each product
        foreach ($products as &$product) {
            $product['variants'] = $this->getProductVariants($product['id']);
            $product['images'] = $this->getProductImages($product['id']);
            $product['styles'] = $this->getProductStyles($product['id']);
        }
        
        return $products;
    }
    
    // Get single product by ID or slug
    public function getProduct($identifier, $bySlug = false) {
        $field = $bySlug ? 'slug' : 'id';
        $query = "SELECT * FROM products WHERE $field = ? AND is_active = 1 LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($bySlug ? 's' : 'i', $identifier);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        $product = $result->fetch_assoc();
        $product['variants'] = $this->getProductVariants($product['id']);
        $product['images'] = $this->getProductImages($product['id']);
        $product['styles'] = $this->getProductStyles($product['id']);
        
        return $product;
    }
    
    // Get product variants
    private function getProductVariants($productId) {
        $query = "SELECT * FROM product_variants WHERE product_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get product images
    private function getProductImages($productId) {
        $query = "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get product styles
    private function getProductStyles($productId) {
        $query = "SELECT style_name, match_score FROM product_styles WHERE product_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get products by style (for recommendations)
    public function getProductsByStyle($style, $limit = 4, $excludeProductId = null) {
        $query = "SELECT p.*, 
                 (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
                 ps.match_score
                 FROM products p
                 JOIN product_styles ps ON p.id = ps.product_id
                 WHERE p.is_active = 1 AND ps.style_name = ?";
        
        if ($excludeProductId) {
            $query .= " AND p.id != ?";
        }
        
        $query .= " ORDER BY ps.match_score DESC, p.created_at DESC LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        
        if ($excludeProductId) {
            $stmt->bind_param('sii', $style, $excludeProductId, $limit);
        } else {
            $stmt->bind_param('si', $style, $limit);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>
