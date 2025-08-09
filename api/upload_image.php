<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

if (empty($_FILES['productImage'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No image file uploaded.']);
    exit;
}

$target_dir = __DIR__ . '/../product_images/';
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0755, true);
}

$image_file = $_FILES['productImage'];
$image_name = basename($image_file['name']);
$sanitized_image_name = preg_replace('/[^a-zA-Z0-9._-]/', '', $image_name);
$target_file = $target_dir . $sanitized_image_name;
$image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

// Check if image file is a actual image or fake image
$check = getimagesize($image_file['tmp_name']);
if($check === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File is not an image.']);
    exit;
}

// Check file size (e.g., 5MB limit)
if ($image_file['size'] > 5000000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Sorry, your file is too large.']);
    exit;
}

// Allow certain file formats
$allowed_formats = ['jpg', 'png', 'jpeg', 'gif'];
if(!in_array($image_file_type, $allowed_formats)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.']);
    exit;
}

// Check if file already exists, if so, add a timestamp to make it unique
if (file_exists($target_file)) {
    $sanitized_image_name = pathinfo($sanitized_image_name, PATHINFO_FILENAME) . '_' . time() . '.' . $image_file_type;
    $target_file = $target_dir . $sanitized_image_name;
}

if (move_uploaded_file($image_file['tmp_name'], $target_file)) {
    // Return the relative path to be stored in products.json
    $relative_path = 'product_images/' . $sanitized_image_name;
    echo json_encode(['success' => true, 'filePath' => $relative_path]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Sorry, there was an error uploading your file.']);
}
?>
