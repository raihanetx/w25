<?php
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');
error_log("--- New Request to add_category.php ---");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$json_payload = file_get_contents('php://input');
error_log("Payload: " . $json_payload);
$new_category_data = json_decode($json_payload, true);

if (json_last_error() !== JSON_ERROR_NONE || empty($new_category_data['name']) || empty($new_category_data['icon'])) {
    http_response_code(400);
    $error_message = 'Invalid request. JSON error: ' . json_last_error_msg() . ' or missing name/icon.';
    error_log($error_message);
    echo json_encode(['success' => false, 'message' => $error_message]);
    exit;
}
error_log("Decoded data: " . print_r($new_category_data, true));

$categories_file = __DIR__ . '/categories.json';
$categories = [];

if (file_exists($categories_file)) {
    $file_content = file_get_contents($categories_file);
    error_log("Existing categories file content: " . $file_content);
    $categories = json_decode($file_content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(500);
        $error_message = 'Error reading existing categories file: ' . json_last_error_msg();
        error_log($error_message);
        echo json_encode(['success' => false, 'message' => $error_message]);
        exit;
    }
} else {
    error_log("Categories file does not exist. Creating a new one.");
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
error_log("New category data to be added: " . print_r($new_category, true));

$categories[] = $new_category;

$json_to_save = json_encode($categories, JSON_PRETTY_PRINT);
error_log("JSON to be saved: " . $json_to_save);

if (file_put_contents($categories_file, $json_to_save)) {
    error_log("Successfully wrote to categories file.");
    echo json_encode(['success' => true, 'message' => 'Category added successfully.', 'newCategory' => $new_category]);
} else {
    http_response_code(500);
    $error_message = 'Failed to write updated categories file. Check permissions for file and directory.';
    error_log($error_message);
    echo json_encode(['success' => false, 'message' => $error_message]);
}
?>
