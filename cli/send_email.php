<?php
/**
 * CLI Email Sender
 * Called as a detached background process by the web server.
 * Usage: php send_email.php <type> <email> <name> <token>
 */

// Ensure this is only run from CLI
if (php_sapi_name() !== 'cli') {
    exit('CLI only');
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/mailer.php';

$type  = $argv[1] ?? '';
$email = $argv[2] ?? '';
$name  = $argv[3] ?? '';
$token = $argv[4] ?? '';

if (empty($email) || empty($type)) {
    exit('Missing arguments');
}

$status = false;
$logFile = __DIR__ . '/../logs/email_error.log';
$logDir = dirname($logFile);
if (!file_exists($logDir)) mkdir($logDir, 0755, true);

switch ($type) {
    case 'activation':
        $status = sendActivationEmail($email, $name, $token);
        break;
    case 'password_reset':
        $status = sendPasswordResetEmail($email, $name, $token);
        break;
    case 'order_status':
        $status = sendOrderStatusUpdateEmail($email, $name, $token, $argv[5] ?? '');
        break;
    default:
        $msg = date('Y-m-d H:i:s') . " - Error: Unknown type '$type' for $email\n";
        file_put_contents($logFile, $msg, FILE_APPEND);
        exit('Unknown type');
}

if (!$status) {
    $msg = date('Y-m-d H:i:s') . " - Failed to send $type email to $email\n";
    file_put_contents($logFile, $msg, FILE_APPEND);
} else {
    // Optional: Log success for debugging
    // $msg = date('Y-m-d H:i:s') . " - Successfully sent $type email to $email\n";
    // file_put_contents($logFile, $msg, FILE_APPEND);
}
