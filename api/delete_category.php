<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$json_payload = file_get_contents('php://input');
$request_data = json_decode($json_payload, true);

if (json_last_error() !== JSON_ERROR_NONE || empty($request_data['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request. Missing category id.']);
    exit;
}

$category_id_to_delete = $request_data['id'];
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
$updated_categories = [];
foreach ($categories as $category) {
    if ($category['id'] === $category_id_to_delete) {
        $category_found = true;
    } else {
        $updated_categories[] = $category;
    }
}

if (!$category_found) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Category with the given ID not found.']);
    exit;
}

$json_to_save = json_encode(array_values($updated_categories), JSON_PRETTY_PRINT);

if (file_put_contents($categories_file, $json_to_save)) {
    echo json_encode(['success' => true, 'message' => 'Category deleted successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to write updated categories file.']);
}
?>
