<?php

require_once __DIR__ . '/../db_config.php';

class Orders {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    // Create a new order with items
    public function createOrder($userId, $items, $totalAmount, $addressId) {
        // Generate unique order number
        $orderNumber = 'ORD-' . time() . '-' . rand(1000, 9999);
        
        $query = "INSERT INTO orders (user_id, order_number, total_amount, address_id, status) 
                  VALUES (?, ?, ?, ?, 'pending')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("isdi", $userId, $orderNumber, $totalAmount, $addressId);
        
        if ($stmt->execute()) {
            $orderId = $this->conn->insert_id;
            
            // Insert order items
            $itemsInserted = $this->insertOrderItems($orderId, $items);
            
            if ($itemsInserted) {
                return [
                    'success' => true,
                    'message' => 'Order created successfully',
                    'orderId' => $orderId,
                    'orderNumber' => $orderNumber,
                    'totalAmount' => $totalAmount
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to add items to order'];
            }
        } else {
            return ['success' => false, 'message' => 'Failed to create order'];
        }
    }

    // Insert order items
    private function insertOrderItems($orderId, $items) {
        $query = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        
        foreach ($items as $item) {
            $productId = $item['productId'];
            $quantity = $item['quantity'];
            $price = $item['price'];
            
            $stmt->bind_param("iiii", $orderId, $productId, $quantity, $price);
            
            if (!$stmt->execute()) {
                return false;
            }
        }
        
        return true;
    }

    // Get order details with items
    public function getOrder($orderId) {
        $query = "SELECT * FROM orders WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $order = $result->fetch_assoc();
            
            // Get order items
            $order['items'] = $this->getOrderItems($orderId);
            
            return ['success' => true, 'data' => $order];
        } else {
            return ['success' => false, 'message' => 'Order not found'];
        }
    }

    // Get order items
    private function getOrderItems($orderId) {
        $query = "SELECT oi.*, p.product_name, p.image_url FROM order_items oi 
                  JOIN products p ON oi.product_id = p.id 
                  WHERE oi.order_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        
        return $items;
    }

    // Get user orders
    public function getUserOrders($userId) {
        $query = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $row['items'] = $this->getOrderItems($row['id']);
            $orders[] = $row;
        }
        
        return ['success' => true, 'data' => $orders];
    }

    // Update order status
    public function updateOrderStatus($orderId, $status) {
        $validStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'paid'];
        
        if (!in_array($status, $validStatuses)) {
            return ['success' => false, 'message' => 'Invalid order status'];
        }
        
        $query = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $status, $orderId);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Order status updated'];
        } else {
            return ['success' => false, 'message' => 'Failed to update order status'];
        }
    }

    // Cancel order
    public function cancelOrder($orderId) {
        return $this->updateOrderStatus($orderId, 'cancelled');
    }

    // Get order by order number
    public function getOrderByNumber($orderNumber) {
        $query = "SELECT * FROM orders WHERE order_number = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $orderNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $order = $result->fetch_assoc();
            $order['items'] = $this->getOrderItems($order['id']);
            return ['success' => true, 'data' => $order];
        } else {
            return ['success' => false, 'message' => 'Order not found'];
        }
    }
}

?>
