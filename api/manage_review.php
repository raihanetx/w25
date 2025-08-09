<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$json_payload = file_get_contents('php://input');
$request_data = json_decode($json_payload, true);

if (json_last_error() !== JSON_ERROR_NONE || empty($request_data['productId']) || empty($request_data['reviewId']) || empty($request_data['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request. Missing required parameters.']);
    exit;
}

$product_id = $request_data['productId'];
$review_id = $request_data['reviewId'];
$action = $request_data['action'];

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
$review_found = false;

foreach ($products as $product_key => &$product) {
    if ($product['id'] == $product_id) {
        $product_found = true;
        if (isset($product['reviews']) && is_array($product['reviews'])) {
            foreach ($product['reviews'] as $review_key => &$review) {
                if ($review['id'] == $review_id) {
                    $review_found = true;
                    switch ($action) {
                        case 'approve':
                            $review['status'] = 'approved';
                            break;
                        case 'delete':
                            array_splice($product['reviews'], $review_key, 1);
                            break;
                        case 'feature':
                            $review['featured'] = true;
                            break;
                        case 'unfeature':
                            $review['featured'] = false;
                            break;
                        case 'edit':
                            if (isset($request_data['text'])) {
                                $review['text'] = htmlspecialchars($request_data['text']);
                            }
                            if (isset($request_data['rating'])) {
                                $review['rating'] = intval($request_data['rating']);
                            }
                            break;
                        default:
                            http_response_code(400);
                            echo json_encode(['success' => false, 'message' => 'Invalid action.']);
                            exit;
                    }
                    break; // review found, no need to loop further
                }
            }
        }
        break; // product found
    }
}
unset($product); // break the reference with the last element
unset($review);

if (!$product_found || !$review_found) {
    http_response_code(404);
    $message = !$product_found ? 'Product not found.' : 'Review not found.';
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

$json_to_save = json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

if (file_put_contents($products_file, $json_to_save)) {
    echo json_encode(['success' => true, 'message' => 'Review action completed successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save changes.']);
}
?>
