<?php

require_once 'db_config.php';

class Payments {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    // Create a payment record for an order
    public function createPayment($orderId, $amount, $currency = 'INR') {
        $razorpayOrderId = $this->generateRazorpayOrderId();
        
        $query = "INSERT INTO payments (order_id, razorpay_order_id, amount, currency, status) 
                  VALUES (?, ?, ?, ?, 'created')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("isds", $orderId, $razorpayOrderId, $amount, $currency);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Payment record created',
                'razorpay_order_id' => $razorpayOrderId,
                'amount' => $amount,
                'currency' => $currency
            ];
        } else {
            return ['success' => false, 'message' => 'Failed to create payment record'];
        }
    }

    // Verify and update payment status after successful transaction
    public function verifyPayment($orderId, $razorpayPaymentId, $razorpayOrderId, $signature) {
        // In a real scenario, you would verify the signature with Razorpay
        // For fake implementation, we'll just mark it as successful
        
        $query = "UPDATE payments SET razorpay_payment_id = ?, status = 'captured' 
                  WHERE order_id = ? AND razorpay_order_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sis", $razorpayPaymentId, $orderId, $razorpayOrderId);
        
        if ($stmt->execute()) {
            // Also update order status to paid
            $this->updateOrderStatus($orderId, 'paid');
            
            return ['success' => true, 'message' => 'Payment verified and captured'];
        } else {
            return ['success' => false, 'message' => 'Failed to verify payment'];
        }
    }

    // Get payment details for an order
    public function getPaymentByOrderId($orderId) {
        $query = "SELECT * FROM payments WHERE order_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return ['success' => true, 'data' => $result->fetch_assoc()];
        } else {
            return ['success' => false, 'message' => 'Payment not found'];
        }
    }

    // Handle payment failure
    public function failPayment($orderId, $reason = 'Payment failed') {
        $query = "UPDATE payments SET status = 'failed' WHERE order_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $orderId);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Payment marked as failed'];
        } else {
            return ['success' => false, 'message' => 'Failed to update payment status'];
        }
    }

    // Get payment history for a user
    public function getUserPaymentHistory($userId) {
        $query = "SELECT p.*, o.order_number, o.total_amount FROM payments p 
                  JOIN orders o ON p.order_id = o.id 
                  WHERE o.user_id = ? 
                  ORDER BY p.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $payments = [];
        
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }
        
        return ['success' => true, 'data' => $payments];
    }

    // Update order status
    private function updateOrderStatus($orderId, $status) {
        $query = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $status, $orderId);
        return $stmt->execute();
    }

    // Generate a fake Razorpay Order ID (for testing)
    private function generateRazorpayOrderId() {
        return 'order_' . uniqid(rand(), true);
    }
}

?>
