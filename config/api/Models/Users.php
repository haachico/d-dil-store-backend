<?php

require_once __DIR__ . '/../db_config.php';

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

    public function getAddresses($userId) {
        $query = "SELECT * FROM addresses WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $addresses = [];
        while ($row = $result->fetch_assoc()) {
            $addresses[] = $row;
        }
        return $addresses;
    }

    public function setDefaultAddress($userId, $addressId) {
        $query1 = "UPDATE addresses SET is_default = 0 WHERE user_id = ?";
        $stmt1 = $this->conn->prepare($query1);
        $stmt1->bind_param("i", $userId);
        $stmt1->execute();
        
        $query2 = "UPDATE addresses SET is_default = 1 WHERE id = ? AND user_id = ?";
        $stmt2 = $this->conn->prepare($query2);
        $stmt2->bind_param("ii", $addressId, $userId);
        if ($stmt2->execute()) {
            return ['success' => true, 'message' => 'Default address updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to update default address'];
        }
    }
}

?>
