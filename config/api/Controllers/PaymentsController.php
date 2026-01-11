<?php

require_once __DIR__ . '/../Models/Payments.php';

class PaymentsController {
    private $paymentsModel;

    public function __construct($dbConnection) {
        $this->paymentsModel = new Payments($dbConnection);
    }

    public function createPayment($userId) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['orderId']) || !isset($data['amount'])) {
            return ['success' => false, 'message' => 'Order ID and amount are required'];
        }

        $result = $this->paymentsModel->createPayment(
            $data['orderId'],
            $data['amount'],
            $data['currency'] ?? 'INR'
        );

        return $result;
    }

    public function verifyPayment($userId) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['orderId']) || !isset($data['razorpayPaymentId'])) {
            return ['success' => false, 'message' => 'Required fields are missing'];
        }

        $result = $this->paymentsModel->verifyPayment(
            $data['orderId'],
            $data['razorpayPaymentId'],
            $data['razorpayOrderId'],
            $data['signature']
        );

        return $result;
    }

    public function getPaymentStatus($userId) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['orderId'])) {
            return ['success' => false, 'message' => 'Order ID is required'];
        }

        $result = $this->paymentsModel->getPaymentByOrderId($data['orderId']);
        return $result;
    }

    public function getUserPaymentHistory($userId) {
        $result = $this->paymentsModel->getUserPaymentHistory($userId);
        return $result;
    }
}

?>
