<?php
require_once 'includes/config.php';

if (isset($_GET['token'])) {
    $token = sanitizeInput($_GET['token']);
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE activation_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if ($user) {
        if ($user['is_active'] == 1) {
            setFlashMessage('info', 'Your account is already activated.');
        } else {
            $stmt = $conn->prepare("UPDATE users SET is_active = 1, activation_token = NULL WHERE id = ?");
            if ($stmt->execute([$user['id']])) {
                setFlashMessage('success', 'Account activated successfully! You can now login.');
            } else {
                setFlashMessage('danger', 'Activation failed. Please try again.');
            }
        }
    } else {
        setFlashMessage('danger', 'Invalid activation link.');
    }
} else {
    setFlashMessage('danger', 'Invalid request.');
}

redirect(SITE_URL . '/login.php');
?>
