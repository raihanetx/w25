<?php
header('Content-Type: application/json');

$products_file = __DIR__ . '/products.json';

if (!file_exists($products_file)) {
    http_response_code(404);
    echo json_encode([]);
    exit;
}

$file_content = file_get_contents($products_file);
$products = json_decode($file_content, true);

if (!is_array($products)) {
    $products = [];
}

// Check for the 'view' query parameter
$view = isset($_GET['view']) ? $_GET['view'] : 'public';

if ($view === 'public') {
    foreach ($products as $product_key => &$product) {
        if (isset($product['reviews']) && is_array($product['reviews'])) {
            $approved_reviews = [];
            foreach ($product['reviews'] as $review) {
                if (isset($review['status']) && $review['status'] === 'approved') {
                    $approved_reviews[] = $review;
                }
            }
            $product['reviews'] = $approved_reviews;
        }
    }
    unset($product);
}

// For 'admin' view or any other value, we return all reviews, so no filtering is needed.

echo json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
