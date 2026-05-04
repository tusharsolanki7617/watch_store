<?php
require_once 'includes/config.php';

// The password we want to reset to
$username = 'admin';
$password = 'Admin@123';
$hashedPassword = hashPassword($password);

try {
    // 1. Delete existing admin to start fresh
    $stmt = $conn->prepare("DELETE FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    
    // 2. Create new admin
    $stmt = $conn->prepare("INSERT INTO admins (username, email, password, full_name) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $username,
        'admin@watchstore.com',
        $hashedPassword,
        'System Administrator'
    ]);
    
    echo '<div style="font-family: Arial; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; background: #f9f9f9; text-align: center;">';
    echo '<h1 style="color: #28a745;">✅ Success!</h1>';
    echo '<p>The admin account has been reset successfully.</p>';
    echo '<hr>';
    echo '<p><strong>Username:</strong> admin</p>';
    echo '<p><strong>Password:</strong> Admin@123</p>';
    echo '<hr>';
    echo '<a href="admin/login.php" style="display: inline-block; background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">Go to Admin Login</a>';
    echo '</div>';
    
} catch (PDOException $e) {
    echo '<div style="font-family: Arial; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #f5c6cb; border-radius: 10px; background: #f8d7da; color: #721c24; text-align: center;">';
    echo '<h1>❌ Error</h1>';
    echo '<p>Failed to reset admin account.</p>';
    echo '<p>Error: ' . $e->getMessage() . '</p>';
    echo '</div>';
}
?>
