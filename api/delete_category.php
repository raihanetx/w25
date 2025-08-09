<?php
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');
error_log("--- New Request to delete_category.php ---");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$json_payload = file_get_contents('php://input');
error_log("Payload: " . $json_payload);
$request_data = json_decode($json_payload, true);

if (json_last_error() !== JSON_ERROR_NONE || empty($request_data['id'])) {
    http_response_code(400);
    $error_message = 'Invalid request. JSON error: ' . json_last_error_msg() . ' or missing id.';
    error_log($error_message);
    echo json_encode(['success' => false, 'message' => $error_message]);
    exit;
}

$category_id_to_delete = $request_data['id'];
error_log("Attempting to delete category with ID: " . $category_id_to_delete);
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
    $error_message = 'Error reading categories file: ' . json_last_error_msg();
    error_log($error_message);
    echo json_encode(['success' => false, 'message' => $error_message]);
    exit;
}

$category_found = false;
$initial_count = count($categories);
$updated_categories = array_filter($categories, function($category) use ($category_id_to_delete, &$category_found) {
    if ($category['id'] === $category_id_to_delete) {
        $category_found = true;
        return false; // Exclude this category
    }
    return true; // Keep this category
});

if (!$category_found) {
    http_response_code(404);
    error_log("Category with ID " . $category_id_to_delete . " not found for deletion.");
    echo json_encode(['success' => false, 'message' => 'Category with the given ID not found.']);
    exit;
}

error_log("Category found. Initial count: " . $initial_count . ". New count: " . count($updated_categories));

$json_to_save = json_encode(array_values($updated_categories), JSON_PRETTY_PRINT);
error_log("JSON to be saved after deletion: " . $json_to_save);

if (file_put_contents($categories_file, $json_to_save)) {
    error_log("Successfully wrote to categories file after deletion.");
    echo json_encode(['success' => true, 'message' => 'Category deleted successfully.']);
} else {
    http_response_code(500);
    $error_message = 'Failed to write updated categories file after deletion.';
    error_log($error_message);
    echo json_encode(['success' => false, 'message' => $error_message]);
}
?>
