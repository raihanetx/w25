<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$json_payload = file_get_contents('php://input');
$request_data = json_decode($json_payload, true);

if (json_last_error() !== JSON_ERROR_NONE || empty($request_data['coupon_code'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request. Missing coupon code.']);
    exit;
}

$coupon_code = strtoupper(trim($request_data['coupon_code']));
$coupons_file = __DIR__ . '/coupons.json';

if (!file_exists($coupons_file)) {
    echo json_encode(['success' => false, 'message' => 'Invalid coupon code.']);
    exit;
}

$file_content = file_get_contents($coupons_file);
$coupons = json_decode($file_content, true);

// Ensure coupons is an array
if (!is_array($coupons)) {
    $coupons = [];
}

$coupon_found = null;
foreach ($coupons as $coupon) {
    if (isset($coupon['code']) && strtoupper($coupon['code']) === $coupon_code) {
        $coupon_found = $coupon;
        break;
    }
}

if (!$coupon_found) {
    echo json_encode(['success' => false, 'message' => 'Invalid or expired coupon code.']);
    exit;
}

// Check expiry date
if (!empty($coupon_found['expiry_date'])) {
    $expiry_date = new DateTime($coupon_found['expiry_date']);
    $current_date = new DateTime();
    if ($current_date > $expiry_date) {
        echo json_encode(['success' => false, 'message' => 'This coupon has expired.']);
        exit;
    }
}

// Check usage limit
if (isset($coupon_found['usage_limit']) && $coupon_found['usage_limit'] > 0) {
    if (!isset($coupon_found['times_used']) || $coupon_found['times_used'] >= $coupon_found['usage_limit']) {
        echo json_encode(['success' => false, 'message' => 'This coupon has reached its usage limit.']);
        exit;
    }
}

// If we get here, the coupon is valid.
// In a real application, we would also need to increment the 'times_used' count
// when an order is successfully placed, but for now, this validation is sufficient.

echo json_encode([
    'success' => true,
    'message' => 'Coupon applied successfully!',
    'discount_type' => isset($coupon_found['discount_type']) ? $coupon_found['discount_type'] : 'fixed', // 'percentage' or 'fixed'
    'discount_value' => isset($coupon_found['discount_value']) ? $coupon_found['discount_value'] : 0,
    'code' => $coupon_found['code']
]);

?>
