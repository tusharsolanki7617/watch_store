<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false]);
    exit;
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit();
}

$cartId = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;

if (!isLoggedIn() || $cartId <= 0) {
    echo json_encode(['success' => false]);
    exit;
}

$stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
if ($stmt->execute([$cartId, $_SESSION['user_id']])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>
