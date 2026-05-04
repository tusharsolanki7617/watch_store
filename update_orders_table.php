<?php
require_once 'includes/config.php';

// Fix for XAMPP macOS socket issue in CLI
$socket = '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock';
$dsn = "mysql:unix_socket=$socket;dbname=" . DB_NAME . ";charset=utf8mb4";
$conn = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

try {
    $conn->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS razorpay_order_id VARCHAR(255) DEFAULT NULL AFTER coupon_code");
    $conn->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS razorpay_payment_id VARCHAR(255) DEFAULT NULL AFTER razorpay_order_id");
    $conn->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS razorpay_signature VARCHAR(255) DEFAULT NULL AFTER razorpay_payment_id");
    echo "Database updated successfully with Razorpay columns.\n";
} catch (PDOException $e) {
    echo "Database update failed: " . $e->getMessage() . "\n";
}
