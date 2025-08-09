<?php
header('Content-Type: application/json');

// Basic security check - ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// Get the raw POST data
$json_payload = file_get_contents('php://input');
$order_data = json_decode($json_payload, true);

// --- Basic Validation ---
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON payload']);
    exit;
}

if (empty($order_data['id']) || empty($order_data['customer']) || empty($order_data['items']) || !isset($order_data['totalAmount'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required order data.']);
    exit;
}
// --- End Validation ---

$order_id = preg_replace('/[^a-zA-Z0-9-]/', '', $order_data['id']); // Sanitize ID
$directory = 'orders';
$filepath = $directory . '/' . $order_id . '.json';

if (!is_dir($directory)) {
    if (!mkdir($directory, 0755, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create orders directory.']);
        exit;
    }
}

// Add server-side timestamp and ensure status is set
$order_data['received_at'] = date('c');
if (!isset($order_data['status'])) {
    $order_data['status'] = 'Pending';
}

$json_to_save = json_encode($order_data, JSON_PRETTY_PRINT);

if (file_put_contents($filepath, $json_to_save)) {
    echo json_encode(['success' => true, 'message' => 'Order saved successfully.', 'orderId' => $order_id]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to write order to file.']);
}
?>
