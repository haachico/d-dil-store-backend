<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require __DIR__ . '/db_config.php';
require __DIR__ . '/Procucts.php';
require __DIR__ . '/Auth.php';  // ← Add this


$request = $_GET['request'] ?? '';
$parts = explode('/', trim($request, '/'));

$class = $parts[0] ?? '';
$method = $parts[1] ?? '';

try {
    // AUTH ROUTES
    if ($class == 'Auth' && $method == 'register') {
        $data = json_decode(file_get_contents('php://input'), true);
        $auth = new Auth($conn);  // ← Create Auth instance
        $result = $auth->register(
            $data['email'],
            $data['password'],
            $data['firstName'] ?? '',
            $data['lastName'] ?? ''
        );
        echo json_encode($result);
    }
    
    else if ($class == 'Auth' && $method == 'login') {
        $data = json_decode(file_get_contents('php://input'), true);
        $auth = new Auth($conn);  // ← Create Auth instance
        $result = $auth->login($data['email'], $data['password']);
        echo json_encode($result);
    }
    
    // PRODUCTS ROUTES
    else if ($class == 'Products' && $method == 'getAllProducts') {
        $api = new Products($conn);
        $products = $api->getAllProducts();
        echo json_encode(['success' => true, 'data' => $products]);
    } 

    else if ($class == 'Products' && $method == 'addWishlistItem') {
        $data = json_decode(file_get_contents('php://input'), true);
        $api = new Products($conn);
        $result = $api->addWishlistItem($data['userId'], $data['productId']);
        echo json_encode($result);
    }   

    else if ($class == 'Products' && $method == 'getWishListITems') {
        $data = json_decode(file_get_contents('php://input'), true);

        $api = new Products($conn);
        $result = $api->getWishListITems($data['userId']);
        echo json_encode($result);
    }
    
    else if ($class == 'Products' && $method == 'removeWishlistItem') {
        $data = json_decode(file_get_contents('php://input'), true);
        $api = new Products($conn);
        $result = $api->removeWishlistItem($data['userId'], $data['productId']);
        echo json_encode($result);
    }
    else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>