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

  public function addWishlistItem($userId, $productId) {
    $query = "INSERT INTO wishlist_items (user_id, product_id) VALUES (?, ?)";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("ii", $userId, $productId);
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Product added to wishlist'];
    } else {
        return ['success' => false, 'message' => 'Failed to add product to wishlist'];
    }
}

public function removeWishlistItem($userId, $productId) {
    $query = "DELETE FROM wishlist_items WHERE user_id = ? AND product_id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("ii", $userId, $productId);
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Product removed from wishlist'];
    } else {
        return ['success' => false, 'message' => 'Failed to remove product from wishlist'];
    }
}

 public function getWishlistItems($userId) {
    try {

        $query = "SELECT p.* FROM products as p
                  JOIN wishlist_items as w ON p.id = w.product_id
                  WHERE w.user_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $wishlistItems = [];
        while ($row = $result->fetch_assoc()) {
            $wishlistItems[] = $row;
        }
        return ['success' => true, 'data' => $wishlistItems];           

    }
    catch (Exception $e) {
        return ['success' => false, 'message' => 'Error retrieving wishlist items'];
    }
 }


 public function addCartItem($userId, $productId, $quantity) {
 
    try {
   $query = "INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("iii", $userId, $productId, $quantity);
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Product added to cart'];
    } else {
        return ['success' => false, 'message' => 'Failed to add product to cart'];
    }
    }
    catch( Exception $e) {
        return ['success' => false, 'message' => 'Error adding to cart'];
    }
 }

    public function getCartItems($userId) {
        try {

            $query = "SELECT p.*, c.quantity FROM products as p
                      JOIN cart_items as c ON p.id = c.product_id
                      WHERE c.user_id = ?";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $cartItems = [];
            while ($row = $result->fetch_assoc()) {
                $cartItems[] = $row;
            }
            return ['success' => true, 'data' => $cartItems];           

        }
        catch (Exception $e) {
            return ['success' => false, 'message' => 'Error retrieving cart items'];
        }
    }

    public function removeCartItem($userId, $productId) {
        $query = "DELETE FROM cart_items WHERE user_id = ? AND product_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $userId, $productId);
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Product removed from cart'];
        } else {
            return ['success' => false, 'message' => 'Failed to remove product from cart'];
        }
    }

    public function updateCartItemQuantity($userId, $productId, $quantity) {
        $query = "UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iii", $quantity, $userId, $productId);
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Cart item quantity updated'];
        } else {
            return ['success' => false, 'message' => 'Failed to update cart item quantity'];
        }
    }

}