<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

$query = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode(['success' => false, 'results' => []]);
    exit;
}

$searchTerm = "%$query%";
$stmt = $conn->prepare("
    SELECT p.id, p.name, p.price, p.discount_price,
    (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
    FROM products p
    WHERE p.is_active = 1 AND (p.name LIKE ? OR p.brand LIKE ?)
    LIMIT 10
");
$stmt->execute([$searchTerm, $searchTerm]);
$results = $stmt->fetchAll();

echo json_encode(['success' => true, 'results' => $results]);
?>
