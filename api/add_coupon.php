<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$json_payload = file_get_contents('php://input');
$new_coupon_data = json_decode($json_payload, true);

if (json_last_error() !== JSON_ERROR_NONE || empty($new_coupon_data['code'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON or missing coupon code.']);
    exit;
}

$coupons_file = __DIR__ . '/coupons.json';
$coupons = [];

if (file_exists($coupons_file)) {
    $file_content = file_get_contents($coupons_file);
    if (!empty($file_content)) {
        $coupons = json_decode($file_content, true);
    }
}

// Ensure coupons is an array
if (!is_array($coupons)) {
    $coupons = [];
}

// Generate a new ID
$max_id = 0;
foreach ($coupons as $coupon) {
    if (isset($coupon['id']) && $coupon['id'] > $max_id) {
        $max_id = $coupon['id'];
    }
}
$new_coupon_data['id'] = $max_id + 1;

// Add created_at timestamp and initialize times_used
$new_coupon_data['created_at'] = date('Y-m-d H:i:s');
$new_coupon_data['times_used'] = isset($new_coupon_data['times_used']) ? $new_coupon_data['times_used'] : 0;


$coupons[] = $new_coupon_data;

$json_to_save = json_encode($coupons, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

if (file_put_contents($coupons_file, $json_to_save)) {
    echo json_encode(['success' => true, 'message' => 'Coupon added successfully.', 'newCoupon' => $new_coupon_data]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save coupons file.']);
}
?>
