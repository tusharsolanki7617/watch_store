<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to submit a review.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit();
}

$userId = $_SESSION['user_id'];
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$title = sanitizeInput($_POST['title'] ?? '');
$comment = sanitizeInput($_POST['comment'] ?? '');

if ($productId <= 0 || $rating < 1 || $rating > 5 || empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'Please provide all required fields.']);
    exit();
}

try {
    // Check if user already reviewed this product
    $stmt = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'You have already reviewed this product.']);
        exit();
    }

    // Insert review (Requires admin moderation)
    $stmt = $conn->prepare("INSERT INTO reviews (product_id, user_id, rating, title, comment, is_approved) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$productId, $userId, $rating, $title, $comment, 0]);

    echo json_encode([
        'success' => true, 
        'message' => 'Thank you! Your review has been submitted and is awaiting moderation.'
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
