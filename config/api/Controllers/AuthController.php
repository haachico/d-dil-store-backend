<?php

require_once __DIR__ . '/../Models/Auth.php';

class AuthController {
    private $authModel;

    public function __construct($dbConnection) {
        $this->authModel = new Auth($dbConnection);
    }

    public function register() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['email']) || !isset($data['password'])) {
            return ['success' => false, 'message' => 'Email and password are required'];
        }

        $result = $this->authModel->register(
            $data['email'],
            $data['password'],
            $data['firstName'] ?? '',
            $data['lastName'] ?? ''
        );

        return $result;
    }

    public function login() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['email']) || !isset($data['password'])) {
            return ['success' => false, 'message' => 'Email and password are required'];
        }

        $result = $this->authModel->login($data['email'], $data['password']);

        return $result;
    }
}

?>
