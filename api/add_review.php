<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$json_payload = file_get_contents('php://input');
$review_data = json_decode($json_payload, true);

if (json_last_error() !== JSON_ERROR_NONE || empty($review_data['productId']) || empty($review_data['author']) || empty($review_data['rating']) || empty($review_data['text'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request. Missing required review data.']);
    exit;
}

$products_file = __DIR__ . '/products.json';
$products = [];

if (file_exists($products_file)) {
    $file_content = file_get_contents($products_file);
    if (!empty($file_content)) {
        $products = json_decode($file_content, true);
        if (!is_array($products)) {
            $products = [];
        }
    }
}

$product_found = false;
foreach ($products as $key => $product) {
    if ($product['id'] == $review_data['productId']) {
        if (!isset($products[$key]['reviews']) || !is_array($products[$key]['reviews'])) {
            $products[$key]['reviews'] = [];
        }

        $new_review = [
            'id' => time(), // Simple unique ID
            'author' => htmlspecialchars($review_data['author']),
            'rating' => intval($review_data['rating']),
            'text' => htmlspecialchars($review_data['text']),
            'date' => date('Y-m-d'),
            'status' => 'pending',
            'featured' => false
        ];

        array_unshift($products[$key]['reviews'], $new_review);
        $product_found = true;
        break;
    }
}

if (!$product_found) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Product not found.']);
    exit;
}

$json_to_save = json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

if (file_put_contents($products_file, $json_to_save)) {
    echo json_encode(['success' => true, 'message' => 'Review submitted for approval.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save review.']);
}
?>
