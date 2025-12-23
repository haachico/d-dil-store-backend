<?php
header('Content-Type: application/json');
require __DIR__ . '/db_config.php';
require __DIR__ . '/Procucts.php';

$request = $_GET['request'] ?? '';
$parts = explode('/', trim($request, '/'));

$class = $parts[0] ?? '';
$method = $parts[1] ?? '';

try {
    if ($class == 'Products' && $method == 'getAllProcuts') {
        $api = new Products($conn);
        $products = $api->getAllProducts();
        
        http_response_code(200);
        echo json_encode(['success' => true, 'data' => $products]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
