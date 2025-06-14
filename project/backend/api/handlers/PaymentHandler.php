<?php
class PaymentHandler {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Process a payment for an order
     * In a real application, this would integrate with a payment gateway like Stripe, PayPal, etc.
     */
    public function processPayment($orderId, $paymentDetails, $userId = null, $sessionId = null) {
        // Validate order exists and belongs to the user
        $order = $this->getOrder($orderId, $userId, $sessionId);
        
        if (!$order) {
            return [
                'success' => false,
                'message' => 'Order not found or access denied'
            ];
        }
        
        // Check if payment has already been processed
        if ($order['payment_status'] === 'paid') {
            return [
                'success' => false,
                'message' => 'Payment has already been processed for this order'
            ];
        }
        
        // Validate payment details
        if (empty($paymentDetails['cardNumber']) || 
            empty($paymentDetails['cardName']) || 
            empty($paymentDetails['expiryDate']) || 
            empty($paymentDetails['cvv'])) {
            return [
                'success' => false,
                'message' => 'Incomplete payment details'
            ];
        }
        
        // Sanitize and format card details
        $cardNumber = preg_replace('/\s+/', '', $paymentDetails['cardNumber']);
        $lastFour = substr($cardNumber, -4);
        $cardType = $this->detectCardType($cardNumber);
        
        // In a real application, you would send these details to a payment processor
        // For this demo, we'll simulate a successful payment
        
        // Generate a payment ID
        $paymentId = $this->generatePaymentId();
        
        // Record the payment in the database
        $query = "INSERT INTO payments (
            payment_id, 
            order_id, 
            amount, 
            payment_method,
            card_last_four,
            card_type,
            status
        ) VALUES (?, ?, ?, ?, ?, ?, 'completed')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            'ssdsss',
            $paymentId,
            $orderId,
            $order['total'],
            $order['payment_method'],
            $lastFour,
            $cardType
        );
        
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            return [
                'success' => false,
                'message' => 'Failed to record payment'
            ];
        }
        
        // Return success response
        return [
            'success' => true,
            'payment_id' => $paymentId,
            'last_four' => $lastFour,
            'card_type' => $cardType
        ];
    }
    
    /**
     * Update order payment status
     */
    public function updateOrderPaymentStatus($orderId, $status, $paymentId = null) {
        $query = "UPDATE orders SET payment_status = ?";
        $params = [$status];
        $types = 's';
        
        if ($paymentId) {
            $query .= ", payment_id = ?";
            $params[] = $paymentId;
            $types .= 's';
        }
        
        $query .= " WHERE order_id = ?";
        $params[] = $orderId;
        $types .= 's';
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        
        return $stmt->affected_rows > 0;
    }
    
    /**
     * Get order details
     */
    private function getOrder($orderId, $userId = null, $sessionId = null) {
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
        
        return $result->fetch_assoc();
    }
    
    /**
     * Generate a unique payment ID
     */
    private function generatePaymentId() {
        $prefix = 'PAY';
        $timestamp = time();
        $random = mt_rand(1000, 9999);
        return $prefix . $timestamp . $random;
    }
    
    /**
     * Detect card type based on card number
     */
    private function detectCardType($cardNumber) {
        // Remove spaces and non-numeric characters
        $cardNumber = preg_replace('/\D/', '', $cardNumber);
        
        // Check card type based on prefix
        if (preg_match('/^4/', $cardNumber)) {
            return 'visa';
        } else if (preg_match('/^5[1-5]/', $cardNumber)) {
            return 'mastercard';
        } else if (preg_match('/^3[47]/', $cardNumber)) {
            return 'amex';
        } else if (preg_match('/^6(?:011|5)/', $cardNumber)) {
            return 'discover';
        } else if (preg_match('/^(?:2131|1800|35)/', $cardNumber)) {
            return 'jcb';
        } else if (preg_match('/^3(?:0[0-5]|[68])/', $cardNumber)) {
            return 'diners';
        } else if (preg_match('/^5019/', $cardNumber)) {
            return 'meeza'; // Egyptian payment card
        } else {
            return 'unknown';
        }
    }
}
?>
