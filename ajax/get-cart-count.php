<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

$count = getCartCount($conn);

echo json_encode([
    'success' => true,
    'count' => $count
]);
?>
