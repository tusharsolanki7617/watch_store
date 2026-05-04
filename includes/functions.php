<?php
/**
 * Core Utility Functions
 * Watch Store E-Commerce Website
 */

/**
 * Redirect to a new page
 */
function redirect($url) {
    if (!headers_sent()) {
        header("Location: " . $url);
        exit();
    } else {
        echo '<script>window.location.href="' . $url . '";</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=' . $url . '"></noscript>';
        exit();
    }
}

/**
 * Display flash message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Get current user data
 */
function getCurrentUser($conn) {
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Get current admin data
 */
function getCurrentAdmin($conn) {
    if (!isAdminLoggedIn()) {
        return null;
    }
    
    $stmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch();
}

/**
 * Generate unique slug
 */
function generateSlug($string) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
    return $slug;
}

/**
 * Generate random order number
 */
function generateOrderNumber() {
    return 'ORD' . date('Ymd') . strtoupper(substr(uniqid(), -6));
}

/**
 * Format price in Indian Rupees
 */
function formatPrice($price) {
    return '₹' . number_format($price, 2);
}

/**
 * Calculate discount percentage
 */
function calculateDiscountPercentage($original, $discounted) {
    if ($original <= 0) return 0;
    return round((($original - $discounted) / $original) * 100);
}

/**
 * Get product final price
 */
function getFinalPrice($product) {
    if ($product['discount_price'] && $product['discount_price'] < $product['price']) {
        return $product['discount_price'];
    }
    return $product['price'];
}

/**
 * Handle file upload
 */
function uploadFile($file, $directory, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif']) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $fileName = $file['name'];
    $fileTmp = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Validate file type
    if (!in_array($fileExt, $allowedTypes)) {
        return false;
    }
    
    // Validate file size (max 5MB)
    if ($fileSize > 5242880) {
        return false;
    }
    
    // Create directory if it doesn't exist
    if (!file_exists($directory)) {
        mkdir($directory, 0777, true);
    }
    
    // Generate unique filename
    $newFileName = uniqid() . '_' . time() . '.' . $fileExt;
    $destination = $directory . '/' . $newFileName;
    
    if (move_uploaded_file($fileTmp, $destination)) {
        return $newFileName;
    }
    
    return false;
}

/**
 * Delete file
 */
function deleteFile($filePath) {
    if (file_exists($filePath)) {
        return unlink($filePath);
    }
    return false;
}

/**
 * Get product rating average
 */
function getProductRating($conn, $productId) {
    $stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
                           FROM reviews WHERE product_id = ? AND is_approved = 1");
    $stmt->execute([$productId]);
    $result = $stmt->fetch();
    
    return [
        'average' => $result['avg_rating'] ? round($result['avg_rating'], 1) : 0,
        'count' => $result['review_count']
    ];
}

/**
 * Get cart count for user
 */
function getCartCount($conn, $userId = null) {
    if (!$userId && isLoggedIn()) {
        $userId = $_SESSION['user_id'];
    }
    
    if (!$userId) {
        return isset($_SESSION['guest_cart']) ? count($_SESSION['guest_cart']) : 0;
    }
    
    $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    
    return $result['total'] ?? 0;
}

/**
 * Get cart items
 */
function getCartItems($conn, $userId = null) {
    if (!$userId && isLoggedIn()) {
        $userId = $_SESSION['user_id'];
    }
    
    if (!$userId) {
        // Get guest cart from session
        $guestCart = $_SESSION['guest_cart'] ?? [];
        
        if (empty($guestCart)) {
            return [];
        }
        
        // Fetch product details for guest cart items
        $productIds = array_column($guestCart, 'product_id');
        $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
        
        $stmt = $conn->prepare("
            SELECT p.id, p.name, p.price, p.discount_price, p.stock,
                   (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
            FROM products p
            WHERE p.id IN ($placeholders)
        ");
        $stmt->execute($productIds);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Merge cart quantities with product details
        $cartItems = [];
        foreach ($guestCart as $cartItem) {
            foreach ($products as $product) {
                if ($product['id'] == $cartItem['product_id']) {
                    $cartItems[] = array_merge($product, ['quantity' => $cartItem['quantity']]);
                    break;
                }
            }
        }
        
        return $cartItems;
    }
    
    $stmt = $conn->prepare("
        SELECT c.*, p.name, p.price, p.discount_price, p.stock, 
               (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

/**
 * Calculate cart total
 */
function calculateCartTotal($conn, $userId = null) {
    $items = getCartItems($conn, $userId);
    $total = 0;
    
    foreach ($items as $item) {
        $price = $item['discount_price'] ?? $item['price'];
        $total += $price * $item['quantity'];
    }
    
    return $total;
}

/**
 * Apply coupon discount
 */
function applyCoupon($conn, $code, $subtotal) {
    $stmt = $conn->prepare("
        SELECT * FROM coupons 
        WHERE code = ? AND is_active = 1 
        AND (expiry_date IS NULL OR expiry_date >= CURDATE())
        AND (usage_limit IS NULL OR used_count < usage_limit)
    ");
    $stmt->execute([$code]);
    $coupon = $stmt->fetch();
    
    if (!$coupon) {
        return ['success' => false, 'message' => 'Invalid or expired coupon code'];
    }
    
    if ($subtotal < $coupon['min_order_amount']) {
        return ['success' => false, 'message' => 'Minimum order amount not met'];
    }
    
    $discount = 0;
    if ($coupon['discount_type'] === 'percentage') {
        $discount = ($subtotal * $coupon['discount_value']) / 100;
        if ($coupon['max_discount'] && $discount > $coupon['max_discount']) {
            $discount = $coupon['max_discount'];
        }
    } else {
        $discount = $coupon['discount_value'];
    }
    
    return [
        'success' => true,
        'discount' => $discount,
        'coupon' => $coupon
    ];
}

/**
 * Time ago function
 */
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    if ($difference < 60) {
        return $difference . ' seconds ago';
    } elseif ($difference < 3600) {
        return floor($difference / 60) . ' minutes ago';
    } elseif ($difference < 86400) {
        return floor($difference / 3600) . ' hours ago';
    } elseif ($difference < 2592000) {
        return floor($difference / 86400) . ' days ago';
    } else {
        return date('d M Y', $timestamp);
    }
}

/**
 * Truncate text
 */
function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

/**
 * Get site setting
 */
function getSiteSetting($conn, $key) {
    $stmt = $conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    return $result ? $result['setting_value'] : null;
}

/**
 * Update site setting
 */
function updateSiteSetting($conn, $key, $value) {
    $stmt = $conn->prepare("
        INSERT INTO site_settings (setting_key, setting_value) 
        VALUES (?, ?) 
        ON DUPLICATE KEY UPDATE setting_value = ?
    ");
    return $stmt->execute([$key, $value, $value]);
}
?>
