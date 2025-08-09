<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$json_payload = file_get_contents('php://input');
$update_data = json_decode($json_payload, true);

if (json_last_error() !== JSON_ERROR_NONE || empty($update_data['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request. Missing coupon id.']);
    exit;
}

$coupon_id = $update_data['id'];
$coupons_file = __DIR__ . '/coupons.json';

if (!file_exists($coupons_file)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Coupons file not found.']);
    exit;
}

$file_content = file_get_contents($coupons_file);
$coupons = json_decode($file_content, true);

// Ensure coupons is an array
if (!is_array($coupons)) {
    $coupons = [];
}

$coupon_found = false;
foreach ($coupons as $key => $coupon) {
    if (isset($coupon['id']) && $coupon['id'] == $coupon_id) {
        // Update all fields provided in the request
        foreach ($update_data as $update_key => $update_value) {
            $coupons[$key][$update_key] = $update_value;
        }
        $coupon_found = true;
        break;
    }
}

if (!$coupon_found) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Coupon with the given ID not found.']);
    exit;
}

$json_to_save = json_encode($coupons, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

if (file_put_contents($coupons_file, $json_to_save)) {
    echo json_encode(['success' => true, 'message' => 'Coupon updated successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to write updated coupons file.']);
}
?>
