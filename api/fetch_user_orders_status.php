<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$json_payload = file_get_contents('php://input');
$request_data = json_decode($json_payload, true);

if (json_last_error() !== JSON_ERROR_NONE || empty($request_data['order_ids']) || !is_array($request_data['order_ids'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request. Expecting an array of order_ids.']);
    exit;
}

$orders_dir = 'orders';
$statuses = [];

foreach ($request_data['order_ids'] as $order_id) {
    $sanitized_id = preg_replace('/[^a-zA-Z0-9-]/', '', $order_id);
    $filepath = $orders_dir . '/' . $sanitized_id . '.json';

    if (file_exists($filepath)) {
        $file_content = file_get_contents($filepath);
        $order_data = json_decode($file_content, true);

        if (json_last_error() === JSON_ERROR_NONE && isset($order_data['status'])) {
            $status_info = [
                'id' => $sanitized_id,
                'status' => $order_data['status'],
                'timestamp' => $order_data['timestamp'] ?? $order_data['received_at'] ?? null
            ];
            if (isset($order_data['confirmed_at'])) {
                $status_info['confirmed_at'] = $order_data['confirmed_at'];
            }
             if (isset($order_data['cancelled_at'])) {
                $status_info['cancelled_at'] = $order_data['cancelled_at'];
            }
            $statuses[] = $status_info;
        }
    }
}

echo json_encode(['success' => true, 'orders' => $statuses]);
?>
