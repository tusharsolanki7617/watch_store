<?php
require_once '../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('../admin/login.php');
}

// Get product ID
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId <= 0) {
    redirect('../admin/products.php');
}

$stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
if ($stmt->execute([$productId])) {
    // Delete related images from database
    $stmt = $conn->prepare("SELECT image_path FROM product_images WHERE product_id = ?");
    $stmt->execute([$productId]);
    $images = $stmt->fetchAll();
    
    // Delete image files
    foreach ($images as $image) {
        $filePath = PRODUCT_IMAGE_PATH . '/' . $image['image_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    
    // Delete image records
    $stmt = $conn->prepare("DELETE FROM product_images WHERE product_id = ?");
    $stmt->execute([$productId]);
    
    setFlashMessage('success', 'Product deleted successfully!');
} else {
    setFlashMessage('danger', 'Failed to delete product.');
}

redirect('../admin/products.php');
?>
