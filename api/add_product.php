<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$json_payload = file_get_contents('php://input');
$new_product_data = json_decode($json_payload, true);

if (json_last_error() !== JSON_ERROR_NONE || empty($new_product_data['name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON or missing product name.']);
    exit;
}

$products_file = __DIR__ . '/products.json';
$products = [];

if (file_exists($products_file)) {
    $file_content = file_get_contents($products_file);
    $products = json_decode($file_content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error reading existing products file.']);
        exit;
    }
}

// Generate a new ID (simple approach: find max ID and add 1)
$max_id = 0;
foreach ($products as $product) {
    if ($product['id'] > $max_id) {
        $max_id = $product['id'];
    }
}
$new_product_data['id'] = $max_id + 1;

// Basic sanitization
foreach ($new_product_data as $key => $value) {
    if (is_string($value)) {
        $new_product_data[$key] = htmlspecialchars($value);
    }
}

$products[] = $new_product_data;

$json_to_save = json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

if (file_put_contents($products_file, $json_to_save)) {
    echo json_encode(['success' => true, 'message' => 'Product added successfully.', 'newProduct' => $new_product_data]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save updated products file.']);
}
?>
