<?php
header('Content-Type: application/json');

$orders_dir = 'orders';
$orders = [];

if (!is_dir($orders_dir)) {
    echo json_encode(['success' => true, 'orders' => []]);
    exit;
}

$files = glob($orders_dir . '/*.json');

foreach ($files as $file) {
    $file_content = file_get_contents($file);
    $order_data = json_decode($file_content, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $orders[] = $order_data;
    }
}

// Sort orders by received_at timestamp, descending
usort($orders, function($a, $b) {
    $time_a = isset($a['received_at']) ? strtotime($a['received_at']) : 0;
    $time_b = isset($b['received_at']) ? strtotime($b['received_at']) : 0;
    return $time_b - $time_a;
});

echo json_encode(['success' => true, 'orders' => $orders]);
?>
