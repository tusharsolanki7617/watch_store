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

$code = isset($_POST['coupon_code']) ? sanitizeInput($_POST['coupon_code']) : '';
$cartTotal = calculateCartTotal($conn);

if (empty($code)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a coupon code']);
    exit;
}

$result = applyCoupon($conn, $code, $cartTotal);

if ($result['success']) {
    $_SESSION['coupon'] = $result['coupon'];
    echo json_encode(['success' => true, 'discount' => $result['discount']]);
} else {
    echo json_encode($result);
}
?>
