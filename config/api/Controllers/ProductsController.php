<?php

require_once __DIR__ . '/../Models/Products.php';

class ProductsController {
    private $productsModel;

    public function __construct($dbConnection) {
        $this->productsModel = new Products($dbConnection);
    }

    public function getAllProducts() {
        $products = $this->productsModel->getAllProducts();
        return ['success' => true, 'data' => $products];
    }

    public function getProductsByCategory() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['categoryId'])) {
            return ['success' => false, 'message' => 'Category ID is required'];
        }

        $products = $this->productsModel->getProductsByCategory($data['categoryId']);
        return ['success' => true, 'data' => $products];
    }

    public function getProductDetails() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['productId'])) {
            return ['success' => false, 'message' => 'Product ID is required'];
        }

        $product = $this->productsModel->getProductDetails($data['productId']);
        
        if ($product) {
            return ['success' => true, 'data' => $product];
        } else {
            return ['success' => false, 'message' => 'Product not found'];
        }
    }

    public function addWishlistItem($userId) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['productId'])) {
            return ['success' => false, 'message' => 'Product ID is required'];
        }

        $result = $this->productsModel->addWishlistItem($userId, $data['productId']);
        return $result;
    }

    public function getWishlistItems($userId) {
        $result = $this->productsModel->getWishlistItems($userId);
        return $result;
    }

    public function removeWishlistItem($userId) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['productId'])) {
            return ['success' => false, 'message' => 'Product ID is required'];
        }

        $result = $this->productsModel->removeWishlistItem($userId, $data['productId']);
        return $result;
    }

    public function addCartItem($userId) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['productId'])) {
            return ['success' => false, 'message' => 'Product ID is required'];
        }

        $result = $this->productsModel->addCartItem($userId, $data['productId'], 1);
        return $result;
    }

    public function getCartItems($userId) {
        $result = $this->productsModel->getCartItems($userId);
        return $result;
    }

    public function removeCartItem($userId) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['productId'])) {
            return ['success' => false, 'message' => 'Product ID is required'];
        }

        $result = $this->productsModel->removeCartItem($userId, $data['productId']);
        return $result;
    }

    public function updateCartItemQuantity($userId) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['productId']) || !isset($data['quantity'])) {
            return ['success' => false, 'message' => 'Product ID and quantity are required'];
        }

        $result = $this->productsModel->updateCartItemQuantity($userId, $data['productId'], $data['quantity']);
        return $result;
    }
}

?>
