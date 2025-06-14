<?php
require_once __DIR__ . '/CartHandler.php';

class OrderHandler {
    private $conn;
    private $cartHandler;

    public function __construct($db) {
        $this->conn = $db;
        $this->cartHandler = new CartHandler($db);
    }

    /**
     * Create a new order from the user's cart
     */
    public function createOrderFromCart($userId, $sessionId, $shippingAddress, $paymentMethod, $shippingMethod = 'standard') {
        // Get the cart
        $cart = $this->cartHandler->getOrCreateCart($userId, $sessionId);
        $cartId = $cart['id'];
        
        // Get cart items
        $cartItems = $this->cartHandler->getCartItems($cartId);
        
        // Check if cart is empty
        if (empty($cartItems)) {
            return null;
        }
        
        // Calculate totals
        $totals = $this->cartHandler->calculateTotals($cartId);
        
        // Start transaction
        $this->conn->begin_transaction();
        
        try {
            // Generate unique order ID
            $orderId = $this->generateOrderId();
            
            // Create order record
            $query = "INSERT INTO orders (
                order_id, 
                user_id, 
                session_id, 
                subtotal, 
                shipping_cost, 
                tax, 
                total, 
                shipping_method,
                payment_method,
                payment_status,
                order_status,
                first_name,
                last_name,
                email,
                phone,
                address,
                city,
                postal_code,
                country
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'processing', ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param(
                'ssidddssssssssssss',
                $orderId,
                $userId,
                $sessionId,
                $totals['subtotal'],
                $totals['shipping_cost'],
                $totals['tax'],
                $totals['total'],
                $shippingMethod,
                $paymentMethod,
                $shippingAddress['firstName'],
                $shippingAddress['lastName'],
                $shippingAddress['email'],
                $shippingAddress['phone'],
                $shippingAddress['address'],
                $shippingAddress['city'],
                $shippingAddress['postalCode'],
                $shippingAddress['country']
            );
            
            $stmt->execute();
            
            if ($stmt->affected_rows === 0) {
                throw new Exception("Failed to create order");
            }
            
            // Insert order items
            foreach ($cartItems as $item) {
                $query = "INSERT INTO order_items (
                    order_id, 
                    product_id, 
                    variant_id, 
                    quantity, 
                    price, 
                    name,
                    image_url
                ) VALUES (?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param(
                    'siidsss',
                    $orderId,
                    $item['product_id'],
                    $item['variant_id'],
                    $item['quantity'],
                    $item['price'],
                    $item['name'],
                    $item['image_url']
                );
                
                $stmt->execute();
                
                if ($stmt->affected_rows === 0) {
                    throw new Exception("Failed to create order item");
                }
            }
            
            // Clear the cart after successful order creation
            $this->cartHandler->clearCart($cartId);
            
            // Commit transaction
            $this->conn->commit();
            
            // Return the created order
            return $this->getOrder($orderId, $userId, $sessionId);
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();
            throw $e;
        }
    }
    
    /**
     * Get order details by order ID
     */
    public function getOrder($orderId, $userId = null, $sessionId = null) {
        // Build query with security check
        $query = "SELECT * FROM orders WHERE order_id = ?";
        $params = [$orderId];
        $types = 's';
        
        // Add security check if user ID or session ID is provided
        if ($userId || $sessionId) {
            $query .= " AND (";
            if ($userId) {
                $query .= "user_id = ?";
                $params[] = $userId;
                $types .= 's';
            }
            
            if ($userId && $sessionId) {
                $query .= " OR ";
            }
            
            if ($sessionId) {
                $query .= "session_id = ?";
                $params[] = $sessionId;
                $types .= 's';
            }
            
            $query .= ")";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        $order = $result->fetch_assoc();
        
        // Get order items
        $order['items'] = $this->getOrderItems($orderId);
        
        return $order;
    }
    
    /**
     * Get order items
     */
    private function getOrderItems($orderId) {
        $query = "SELECT * FROM order_items WHERE order_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get user orders
     */
    public function getUserOrders($userId, $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        $query = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT ?, ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('sii', $userId, $offset, $perPage);
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = $result->fetch_all(MYSQLI_ASSOC);
        
        // Get items for each order
        foreach ($orders as &$order) {
            $order['items'] = $this->getOrderItems($order['order_id']);
        }
        
        return $orders;
    }
    
    /**
     * Generate a unique order ID
     */
    private function generateOrderId() {
        $prefix = 'ORD';
        $timestamp = time();
        $random = mt_rand(1000, 9999);
        return $prefix . $timestamp . $random;
    }
    
    /**
     * Get order status
     */
    public function getOrderStatus($orderId, $userId = null, $sessionId = null) {
        $query = "SELECT order_id, order_status, payment_status, created_at, updated_at FROM orders WHERE order_id = ?";
        $params = [$orderId];
        $types = 's';
        
        // Add security check if user ID or session ID is provided
        if ($userId || $sessionId) {
            $query .= " AND (";
            if ($userId) {
                $query .= "user_id = ?";
                $params[] = $userId;
                $types .= 's';
            }
            
            if ($userId && $sessionId) {
                $query .= " OR ";
            }
            
            if ($sessionId) {
                $query .= "session_id = ?";
                $params[] = $sessionId;
                $types .= 's';
            }
            
            $query .= ")";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        return $result->fetch_assoc();
    }
    
    /**
     * Update order status
     */
    public function updateOrderStatus($orderId, $status) {
        $validStatuses = ['processing', 'shipped', 'delivered', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            throw new Exception("Invalid order status");
        }
        
        $query = "UPDATE orders SET order_status = ?, updated_at = NOW() WHERE order_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ss', $status, $orderId);
        $stmt->execute();
        
        return $stmt->affected_rows > 0;
    }
}
?>
