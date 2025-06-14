<?php
class CartHandler {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get or create cart for user/session
    public function getOrCreateCart($userId = null, $sessionId = null) {
        if (!$userId && !$sessionId) {
            throw new Exception('User ID or Session ID is required');
        }

        // Try to find existing cart
        $query = "SELECT * FROM carts WHERE ";
        $params = [];
        $types = '';
        
        if ($userId) {
            $query .= "user_id = ? ";
            $params[] = $userId;
            $types .= 'i';
        } else {
            $query .= "session_id = ? ";
            $params[] = $sessionId;
            $types .= 's';
        }
        
        $query .= "ORDER BY created_at DESC LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Return existing cart if found
        if ($result->num_rows > 0) {
            $cart = $result->fetch_assoc();
            $cart['items'] = $this->getCartItems($cart['id']);
            return $cart;
        }
        
        // Create new cart
        $query = "INSERT INTO carts (user_id, session_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('is', $userId, $sessionId);
        $stmt->execute();
        
        $cartId = $this->conn->insert_id;
        
        return [
            'id' => $cartId,
            'user_id' => $userId,
            'session_id' => $sessionId,
            'items' => []
        ];
    }

    // Get cart items
    public function getCartItems($cartId) {
        $query = "SELECT ci.*, p.name as product_name, p.price, pv.value as variant_value, pv.name as variant_name
                 FROM cart_items ci
                 JOIN products p ON ci.product_id = p.id
                 LEFT JOIN product_variants pv ON ci.variant_id = pv.id
                 WHERE ci.cart_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $cartId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $row['total_price'] = $row['price'] * $row['quantity'];
            $items[] = $row;
        }
        
        return $items;
    }

    // Add item to cart
    public function addToCart($cartId, $productId, $variantId = null, $quantity = 1) {
        // Check if item already exists in cart
        $query = "SELECT * FROM cart_items 
                 WHERE cart_id = ? AND product_id = ? AND (variant_id = ? OR (variant_id IS NULL AND ? IS NULL))";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('iiii', $cartId, $productId, $variantId, $variantId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update quantity if item exists
            $item = $result->fetch_assoc();
            $newQuantity = $item['quantity'] + $quantity;
            
            $query = "UPDATE cart_items SET quantity = ? WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('ii', $newQuantity, $item['id']);
            $stmt->execute();
            
            return $this->getCartItems($cartId);
        }
        
        // Add new item to cart
        $query = "INSERT INTO cart_items (cart_id, product_id, variant_id, quantity) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('iiii', $cartId, $productId, $variantId, $quantity);
        $stmt->execute();
        
        return $this->getCartItems($cartId);
    }

    // Update cart item quantity
    public function updateCartItem($cartId, $itemId, $quantity) {
        if ($quantity <= 0) {
            return $this->removeFromCart($cartId, $itemId);
        }
        
        $query = "UPDATE cart_items SET quantity = ? WHERE id = ? AND cart_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('iii', $quantity, $itemId, $cartId);
        $stmt->execute();
        
        return $this->getCartItems($cartId);
    }

    // Remove item from cart
    public function removeFromCart($cartId, $itemId) {
        $query = "DELETE FROM cart_items WHERE id = ? AND cart_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $itemId, $cartId);
        $stmt->execute();
        
        return $this->getCartItems($cartId);
    }

    // Clear cart
    public function clearCart($cartId) {
        $query = "DELETE FROM cart_items WHERE cart_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $cartId);
        $stmt->execute();
        
        return [];
    }

    // Calculate cart totals
    public function calculateTotals($cartId) {
        $items = $this->getCartItems($cartId);
        
        $subtotal = 0;
        $itemCount = 0;
        
        foreach ($items as $item) {
            $itemTotal = $item['price'] * $item['quantity'];
            $subtotal += $itemTotal;
            $itemCount += $item['quantity'];
        }
        
        // For demo purposes - in a real app, calculate tax and shipping based on address
        $taxRate = 0.08; // 8% tax
        $shipping = $subtotal > 0 ? 5.99 : 0; // Flat rate shipping
        $tax = $subtotal * $taxRate;
        $total = $subtotal + $tax + $shipping;
        
        return [
            'subtotal' => number_format($subtotal, 2, '.', ''),
            'tax' => number_format($tax, 2, '.', ''),
            'shipping' => number_format($shipping, 2, '.', ''),
            'total' => number_format($total, 2, '.', ''),
            'item_count' => $itemCount
        ];
    }
}
?>
