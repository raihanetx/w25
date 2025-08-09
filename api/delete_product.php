<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$json_payload = file_get_contents('php://input');
$request_data = json_decode($json_payload, true);

if (json_last_error() !== JSON_ERROR_NONE || empty($request_data['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request. Missing product id.']);
    exit;
}

$product_id_to_delete = $request_data['id'];
$products_file = __DIR__ . '/products.json';

if (!file_exists($products_file)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Products file not found.']);
    exit;
}

$file_content = file_get_contents($products_file);
$products = json_decode($file_content, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error reading products file.']);
    exit;
}

$product_found = false;
$updated_products = [];
foreach ($products as $product) {
    if ($product['id'] == $product_id_to_delete) {
        $product_found = true;
    } else {
        $updated_products[] = $product;
    }
}

if (!$product_found) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Product with the given ID not found.']);
    exit;
}

$json_to_save = json_encode(array_values($updated_products), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

if (file_put_contents($products_file, $json_to_save)) {
    echo json_encode(['success' => true, 'message' => 'Product deleted successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to write updated products file.']);
}
?>
