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
require_once __DIR__ . '/JwtHelper.php';
require_once __DIR__ . '/Controllers/AuthController.php';
require_once __DIR__ . '/Controllers/ProductsController.php';
require_once __DIR__ . '/Controllers/UsersController.php';
require_once __DIR__ . '/Controllers/OrdersController.php';
require_once __DIR__ . '/Controllers/PaymentsController.php';

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
    if ($class == 'Auth') {
        $controller = new AuthController($conn);
        
        if ($method == 'register') {
            $result = $controller->register();
            echo json_encode($result);
        } elseif ($method == 'login') {
            $result = $controller->login();
            echo json_encode($result);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
        }
    }
    
    // PRODUCTS ROUTES
    else if ($class == 'Products') {
        $controller = new ProductsController($conn);
        
        // Public endpoints (no auth required)
        if ($method == 'getAllProducts') {
            $result = $controller->getAllProducts();
            echo json_encode($result);
        } elseif ($method == 'getProductsByCategory') {
            $result = $controller->getProductsByCategory();
            echo json_encode($result);
        } elseif ($method == 'getProductDetails') {
            $result = $controller->getProductDetails();
            echo json_encode($result);
        }
        // Protected endpoints (auth required)
        else {
            $user = requireAuth();  // Check token
            
            switch ($method) {
                case 'addWishlistItem':
                    $result = $controller->addWishlistItem($user['userId']);
                    break;
                case 'getWishlistItems':
                    $result = $controller->getWishlistItems($user['userId']);
                    break;
                case 'removeWishlistItem':
                    $result = $controller->removeWishlistItem($user['userId']);
                    break;
                case 'addCartItem':
                    $result = $controller->addCartItem($user['userId']);
                    break;
                case 'getCartItems':
                    $result = $controller->getCartItems($user['userId']);
                    break;
                case 'removeCartItem':
                    $result = $controller->removeCartItem($user['userId']);
                    break;
                case 'updateCartItemQuantity':
                    $result = $controller->updateCartItemQuantity($user['userId']);
                    break;
                default:
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
                    exit();
            }
            
            echo json_encode($result);
        }
    }
    
    // USERS ROUTES
    else if ($class == 'Users') {
        $user = requireAuth();  // Check token
        $controller = new UsersController($conn);
        
        switch ($method) {
            case 'saveAddress':
                $result = $controller->saveAddress($user['userId']);
                break;
            case 'getAddresses':
                $result = $controller->getAddresses($user['userId']);
                break;
            case 'setDefaultAddress':
                $result = $controller->setDefaultAddress($user['userId']);
                break;
            default:
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
                exit();
        }
        
        echo json_encode($result);
    }
    
    // ORDERS ROUTES
    else if ($class == 'orders') {
        $user = requireAuth();  // Check token
        $controller = new OrdersController($conn);
        
        switch ($method) {
            case 'createOrder':
                $result = $controller->createOrder($user['userId']);
                break;
            case 'getOrder':
                $result = $controller->getOrder($user['userId']);
                break;
            case 'getUserOrders':
                $result = $controller->getUserOrders($user['userId']);
                break;
            case 'updateOrderStatus':
                $result = $controller->updateOrderStatus($user['userId']);
                break;
            case 'cancelOrder':
                $result = $controller->cancelOrder($user['userId']);
                break;
            default:
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
                exit();
        }
        
        echo json_encode($result);
    }
    
    // PAYMENTS ROUTES
    else if ($class == 'payments') {
        $user = requireAuth();  // Check token
        $controller = new PaymentsController($conn);
        
        switch ($method) {
            case 'createPayment':
                $result = $controller->createPayment($user['userId']);
                break;
            case 'verifyPayment':
                $result = $controller->verifyPayment($user['userId']);
                break;
            case 'getPaymentStatus':
                $result = $controller->getPaymentStatus($user['userId']);
                break;
            case 'getUserPaymentHistory':
                $result = $controller->getUserPaymentHistory($user['userId']);
                break;
            default:
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
                exit();
        }
        
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

