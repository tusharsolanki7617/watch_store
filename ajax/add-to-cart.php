<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit();
}

$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;

if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

// Check if product exists and has stock
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

if ($product['stock'] < $quantity) {
    echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
    exit;
}

if (isLoggedIn()) {
    // Add to database cart
    $userId = $_SESSION['user_id'];
    
    // Check if already in cart
    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update quantity
        $newQuantity = $existing['quantity'] + $quantity;
        if ($newQuantity > $product['stock']) {
            echo json_encode(['success' => false, 'message' => 'Cannot exceed available stock']);
            exit;
        }
        
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->execute([$newQuantity, $existing['id']]);
    } else {
        // Insert new item
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $productId, $quantity]);
    }
} else {
    // Add to session cart for guests
    if (!isset($_SESSION['guest_cart'])) {
        $_SESSION['guest_cart'] = [];
    }
    
    if (isset($_SESSION['guest_cart'][$productId])) {
        $_SESSION['guest_cart'][$productId]['quantity'] += $quantity;
    } else {
        $_SESSION['guest_cart'][$productId] = [
            'product_id' => $productId,
            'quantity' => $quantity
        ];
    }
}

echo json_encode(['success' => true, 'message' => 'Product added to cart']);
?>
