<?php

require_once __DIR__ . '/../Models/Orders.php';

class OrdersController {
    private $ordersModel;

    public function __construct($dbConnection) {
        $this->ordersModel = new Orders($dbConnection);
    }

    public function createOrder($userId) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['items']) || !isset($data['totalAmount'])) {
            return ['success' => false, 'message' => 'Items and total amount are required'];
        }

        $result = $this->ordersModel->createOrder(
            $userId,
            $data['items'],
            $data['totalAmount'],
            $data['addressId']
        );

        return $result;
    }

    public function getOrder($userId) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['orderId'])) {
            return ['success' => false, 'message' => 'Order ID is required'];
        }

        $result = $this->ordersModel->getOrder($data['orderId']);
        return $result;
    }

    public function getUserOrders($userId) {
        $result = $this->ordersModel->getUserOrders($userId);
        return $result;
    }

    public function updateOrderStatus($userId) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['orderId']) || !isset($data['status'])) {
            return ['success' => false, 'message' => 'Order ID and status are required'];
        }

        $result = $this->ordersModel->updateOrderStatus($data['orderId'], $data['status']);
        return $result;
    }

    public function cancelOrder($userId) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['orderId'])) {
            return ['success' => false, 'message' => 'Order ID is required'];
        }

        $result = $this->ordersModel->cancelOrder($data['orderId']);
        return $result;
    }
}

?>
