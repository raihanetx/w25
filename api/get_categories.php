<?php
header('Content-Type: application/json');

$categories_file = __DIR__ . '/categories.json';

if (file_exists($categories_file)) {
    $file_content = file_get_contents($categories_file);
    // No need to decode and re-encode if we're just passing it through
    echo $file_content;
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Categories file not found.']);
}
?>
