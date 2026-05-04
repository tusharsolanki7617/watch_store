<?php
require_once '../includes/config.php';
require_once '../includes/mailer.php';

// Disconnect the client immediately
ignore_user_abort(true);
set_time_limit(0);
header("Connection: close");
ob_start();
echo "Process started";
header("Content-Length: " . ob_get_length());
ob_end_flush();
flush();
if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
}

if (isset($_POST['is_background_process']) && $_POST['is_background_process'] === 'true') {
    $email = $_POST['email'] ?? '';
    $name = $_POST['name'] ?? '';
    $token = $_POST['token'] ?? '';
    
    if ($email && $name && $token) {
        sendActivationEmail($email, $name, $token);
    }
}
