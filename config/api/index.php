<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/db_config.php';
require_once __DIR__ . '/Procucts.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/JwtHelper.php';

// Helper function to get token from request
function getTokenFromRequest() {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        $parts = explode(' ', $headers['Authorization']);
        if (count($parts) == 2 && $parts[0] == 'Bearer') {
            return $parts[1];
        }
    }
    return null;
}

// Helper function to verify token
function requireAuth() {
    $token = getTokenFromRequest();
    $jwtHelper = new JwtHelper();
    $verified = $jwtHelper->verifyToken($token);
    
    if (!$verified['valid']) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized: ' . $verified['message']]);
        exit();
    }
    
    return $verified;
}

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
        $user = requireAuth();  // Check token
        $data = json_decode(file_get_contents('php://input'), true);
        $api = new Products($conn);
        $result = $api->addWishlistItem($user['userId'], $data['productId']);
        echo json_encode($result);
    }   

    else if ($class == 'Products' && $method == 'getWishlistItems') {
        $user = requireAuth();  // Check token
        $api = new Products($conn);
        $result = $api->getWishlistItems($user['userId']);
        echo json_encode($result);
    }
    
    else if ($class == 'Products' && $method == 'removeWishlistItem') {
      $user = requireAuth();  // Check token
        $data = json_decode(file_get_contents('php://input'), true);
        $api = new Products($conn);
        $result = $api->removeWishlistItem($user['userId'], $data['productId']);
        echo json_encode($result);
    }

    else if ($class == 'Products' && $method == 'addCartItem') {
        $data = json_decode(file_get_contents('php://input'), true);
        $user = requireAuth();  // Check token
        $api = new Products($conn);
        $result = $api->addCartItem($user['userId'], $data['productId'], 1);
        echo json_encode($result);
    }

    else if ($class == 'Products' && $method == 'getCartItems') {
        $user = requireAuth();  // Check token

        $api = new Products($conn);
        $result = $api->getCartItems($user['userId']);
        echo json_encode($result);
    }

     else if ($class == 'Products' && $method == 'removeCartItem') {
        $user = requireAuth();  // Check token
        $data = json_decode(file_get_contents('php://input'), true);
        $api = new Products($conn);
        $result = $api->removeCartItem($user['userId'], $data['productId']);
        echo json_encode($result);
    }

     else if ($class == 'Products' && $method == 'updateCartItemQuantity') {
        $user = requireAuth();  // Check token
        $data = json_decode(file_get_contents('php://input'), true);
        $api = new Products($conn);
        $result = $api->updateCartItemQuantity($user['userId'], $data['productId'], $data['quantity']);
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