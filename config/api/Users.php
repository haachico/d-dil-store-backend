<?php

require_once 'db_config.php';

class Users {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }


    public function saveAddress($userId, $firstName, $lastName, $city, $state, $address, $contactNo, $pincode) {
        $query = "INSERT INTO addresses (user_id, first_name, last_name, city, state, address, contact_no, pin_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("isssssss", $userId, $firstName, $lastName, $city, $state, $address, $contactNo, $pincode);
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Address saved successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to save address'];
        }
    }

}