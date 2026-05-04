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

$fullName = sanitizeInput($_POST['full_name'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$phone = sanitizeInput($_POST['phone'] ?? '');
$address = sanitizeInput($_POST['address'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if (empty($fullName) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

if (!isValidEmail($email)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit();
}

if ($password !== $confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
    exit();
}

if (!isValidPassword($password)) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters with uppercase, lowercase, and number']);
    exit();
}

try {
    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit();
    }

    $hashedPassword = hashPassword($password);
    $activationToken = generateRandomToken();

    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, phone, address, activation_token) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$fullName, $email, $hashedPassword, $phone, $address, $activationToken])) {
        $response = json_encode([
            'success' => true, 
            'message' => 'Registration successful! Please check your email to activate your account.',
            'redirect' => SITE_URL . '/login.php'
        ]);

        // 1. Send the success response to the browser immediately
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
        $args    = escapeshellarg('activation')
                 . ' ' . escapeshellarg($email)
                 . ' ' . escapeshellarg($fullName)
                 . ' ' . escapeshellarg($activationToken);

        // Run the script in the background using '&' for Unix-like systems
        $cmd = "{$phpBin} \"{$script}\" {$args} > /dev/null 2>&1 &";
        exec($cmd);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
