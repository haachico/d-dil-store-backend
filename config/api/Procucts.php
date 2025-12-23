<?php

require_once 'db_config.php';

class Products {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function getAllProducts() {
        $query = "SELECT * FROM products";
        $result = $this->conn->query($query);
        $products = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }

        return $products;
    }
}