<?php

require_once __DIR__ . '/../Models/Users.php';

class UsersController {
    private $usersModel;

    public function __construct($dbConnection) {
        $this->usersModel = new Users($dbConnection);
    }

    public function saveAddress($userId) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['firstName']) || !isset($data['address'])) {
            return ['success' => false, 'message' => 'Required fields are missing'];
        }

        $result = $this->usersModel->saveAddress(
            $userId,
            $data['firstName'],
            $data['lastName'],
            $data['city'],
            $data['state'],
            $data['address'],
            $data['contactNo'],
            $data['pincode']
        );

        return $result;
    }

    public function getAddresses($userId) {
        $addresses = $this->usersModel->getAddresses($userId);
        return ['success' => true, 'data' => $addresses];
    }

    public function setDefaultAddress($userId) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['addressId'])) {
            return ['success' => false, 'message' => 'Address ID is required'];
        }

        $result = $this->usersModel->setDefaultAddress($userId, $data['addressId']);
        return $result;
    }
}

?>
