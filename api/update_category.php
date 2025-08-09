<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$json_payload = file_get_contents('php://input');
$update_data = json_decode($json_payload, true);

if (json_last_error() !== JSON_ERROR_NONE || empty($update_data['id']) || empty($update_data['name']) || empty($update_data['icon'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request. Missing id, name, or icon.']);
    exit;
}

$category_id = $update_data['id'];
$categories_file = __DIR__ . '/categories.json';

if (!file_exists($categories_file)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Categories file not found.']);
    exit;
}

$file_content = file_get_contents($categories_file);
$categories = json_decode($file_content, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error reading categories file.']);
    exit;
}

$category_found = false;
foreach ($categories as $key => $category) {
    if ($category['id'] === $category_id) {
        $categories[$key]['name'] = htmlspecialchars($update_data['name']);
        $categories[$key]['icon'] = htmlspecialchars($update_data['icon']);
        $category_found = true;
        break;
    }
}

if (!$category_found) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Category with the given ID not found.']);
    exit;
}

$json_to_save = json_encode($categories, JSON_PRETTY_PRINT);

if (file_put_contents($categories_file, $json_to_save)) {
    echo json_encode(['success' => true, 'message' => 'Category updated successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to write updated categories file.']);
}
?>
