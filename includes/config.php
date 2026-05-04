<?php
/**
 * Database Configuration
 * Watch Store E-Commerce Website
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'watch_store');

// Site Configuration
define('SITE_URL', 'http://localhost/website_main');
define('SITE_NAME', 'Watch Store');
define('SITE_EMAIL', 'info@watchstore.com');

// Directory Paths
define('BASE_PATH', __DIR__ . '/..');
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('PRODUCT_IMAGE_PATH', UPLOAD_PATH . '/products');
define('PROFILE_IMAGE_PATH', UPLOAD_PATH . '/profiles');

// Email Configuration (SMTP)
// Email Configuration (SMTP)
define('USE_SMTP', true); // Set to true to use SMTP, false for PHP mail()
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587); // STARTTLS is often more compatible than port 465
define('SMTP_USERNAME', 'tushar09250@gmail.com');
define('SMTP_PASSWORD', 'szky czuu hnqs yaec');
define('SMTP_FROM_EMAIL', 'tushar09250@gmail.com');
define('SMTP_FROM_NAME', SITE_NAME);

// Security
define('HASH_COST', 10);
define('SESSION_LIFETIME', 3600); // 1 hour

// Pagination
define('PRODUCTS_PER_PAGE', 12);
define('ORDERS_PER_PAGE', 10);

// Razorpay Configuration
define('RAZORPAY_KEY_ID', 'rzp_test_SdEyBo4NhPbn0H');
define('RAZORPAY_KEY_SECRET', 'duaB0tBwiHfDmn673K6WRAPQ');

// Database Connection
try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// Autoload required files
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/security.php';

