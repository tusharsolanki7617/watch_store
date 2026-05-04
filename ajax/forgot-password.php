<?php
require_once '../includes/config.php';
require_once '../includes/mailer.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit();
}

$email = sanitizeInput($_POST['email'] ?? '');

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Please enter your email address']);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT id, full_name FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        // Technically for security we shouldn't reveal if user exists, but keeping original logic
        echo json_encode(['success' => false, 'message' => 'We couldn\'t find an active account with that email address.']);
        exit();
    }

    $otp = rand(100000, 999999);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+30 minutes'));

    $stmt = $conn->prepare("INSERT INTO password_resets (email, otp, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$email, $otp, $expiresAt]);

    $_SESSION['reset_email'] = $email;

    $response = json_encode([
        'success' => true,
        'message' => 'An OTP has been sent to your email address.',
        'redirect' => SITE_URL . '/reset-password.php'
    ]);

    // Send response instantly to client
    echo $response;

    if (ob_get_level() > 0) {
        ob_end_flush();
    }
    flush();
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }

    // 2. Spawn a background CLI process to send the email (Mac/Linux compatible)
    $phpBin = '/Applications/XAMPP/xamppfiles/bin/php'; 
    $script  = realpath(__DIR__ . '/../cli/send_email.php');
    $args    = escapeshellarg('password_reset')
             . ' ' . escapeshellarg($email)
             . ' ' . escapeshellarg($user['full_name'])
             . ' ' . escapeshellarg($otp);

    // Run the script in the background using '&' for Unix-like systems
    $cmd = "{$phpBin} \"{$script}\" {$args} > /dev/null 2>&1 &";
    exec($cmd);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
}
?>
