<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$json_payload = file_get_contents('php://input');
$request_data = json_decode($json_payload, true);

if (json_last_error() !== JSON_ERROR_NONE || empty($request_data['orderId']) || empty($request_data['status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request. Missing orderId or status.']);
    exit;
}

$order_id = preg_replace('/[^a-zA-Z0-9-]/', '', $request_data['orderId']);
$new_status = htmlspecialchars($request_data['status']);
$valid_statuses = ['Pending', 'Confirmed', 'Cancelled'];

if (!in_array($new_status, $valid_statuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status value.']);
    exit;
}

$filepath = 'orders/' . $order_id . '.json';

if (!file_exists($filepath)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Order not found.']);
    exit;
}

$file_content = file_get_contents($filepath);
$order_data = json_decode($file_content, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error reading order file.']);
    exit;
}

$order_data['status'] = $new_status;

// Optionally, add timestamps for status changes
if ($new_status === 'Confirmed') {
    $order_data['confirmed_at'] = date('c');
} elseif ($new_status === 'Cancelled') {
    $order_data['cancelled_at'] = date('c');
}


$json_to_save = json_encode($order_data, JSON_PRETTY_PRINT);

if (file_put_contents($filepath, $json_to_save)) {
    echo json_encode(['success' => true, 'message' => 'Order status updated successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to write updated order to file.']);
}
?>
