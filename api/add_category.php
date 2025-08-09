<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$json_payload = file_get_contents('php://input');
$new_category_data = json_decode($json_payload, true);

if (json_last_error() !== JSON_ERROR_NONE || empty($new_category_data['name']) || empty($new_category_data['icon'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request. Missing name or icon.']);
    exit;
}

$categories_file = __DIR__ . '/categories.json';
$categories = [];

if (file_exists($categories_file)) {
    $file_content = file_get_contents($categories_file);
    $categories = json_decode($file_content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error reading existing categories file.']);
        exit;
    }
}

// Generate a simple slug-based ID from the name
$new_id = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $new_category_data['name']));

// Check if ID already exists
$id_exists = false;
foreach ($categories as $category) {
    if ($category['id'] === $new_id) {
        $id_exists = true;
        break;
    }
}
if ($id_exists) {
    // If ID exists, append a random number to make it unique
    $new_id = $new_id . '_' . rand(100, 999);
}


$new_category = [
    'id' => $new_id,
    'name' => htmlspecialchars($new_category_data['name']),
    'icon' => htmlspecialchars($new_category_data['icon']) // Basic sanitization
];

$categories[] = $new_category;

$json_to_save = json_encode($categories, JSON_PRETTY_PRINT);

if (file_put_contents($categories_file, $json_to_save)) {
    echo json_encode(['success' => true, 'message' => 'Category added successfully.', 'newCategory' => $new_category]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save updated categories file.']);
}
?>
