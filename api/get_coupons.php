<?php
header('Content-Type: application/json');

$coupons_file = __DIR__ . '/coupons.json';

if (file_exists($coupons_file)) {
    $file_content = file_get_contents($coupons_file);
    $coupons = json_decode($file_content, true);

    // Ensure coupons is an array
    if (!is_array($coupons)) {
        $coupons = [];
    }

    echo json_encode($coupons);
} else {
    // If file doesn't exist, return an empty array
    echo json_encode([]);
}
?>
