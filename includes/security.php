<?php
/**
 * Security Functions
 * Watch Store E-Commerce Website
 */

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Hash password using bcrypt
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate random token
 */
function generateRandomToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Generate OTP
 */
function generateOTP($length = 6) {
    $digits = '0123456789';
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= $digits[random_int(0, 9)];
    }
    return $otp;
}

/**
 * Validate email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 * Minimum 8 characters, at least one uppercase, one lowercase, one number
 */
function isValidPassword($password) {
    return strlen($password) >= 8 
           && preg_match('/[A-Z]/', $password) 
           && preg_match('/[a-z]/', $password) 
           && preg_match('/[0-9]/', $password);
}

/**
 * Prevent XSS attacks
 */
function escapeOutput($data) {
    if ($data === null) {
        return '';
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Check session timeout
 */
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity'])) {
        $inactiveTime = time() - $_SESSION['last_activity'];
        if ($inactiveTime > SESSION_LIFETIME) {
            session_unset();
            session_destroy();
            return true;
        }
    }
    $_SESSION['last_activity'] = time();
    return false;
}

/**
 * Regenerate session ID
 */
function regenerateSession() {
    session_regenerate_id(true);
}

/**
 * Rate limiting check (simple implementation)
 */
function checkRateLimit($key, $maxAttempts = 5, $timeWindow = 300) {
    if (!isset($_SESSION['rate_limit'])) {
        $_SESSION['rate_limit'] = [];
    }
    
    $currentTime = time();
    
    // Clean up old attempts
    if (isset($_SESSION['rate_limit'][$key])) {
        $_SESSION['rate_limit'][$key] = array_filter(
            $_SESSION['rate_limit'][$key],
            function($timestamp) use ($currentTime, $timeWindow) {
                return ($currentTime - $timestamp) < $timeWindow;
            }
        );
    } else {
        $_SESSION['rate_limit'][$key] = [];
    }
    
    // Check if limit exceeded
    if (count($_SESSION['rate_limit'][$key]) >= $maxAttempts) {
        return false;
    }
    
    // Add current attempt
    $_SESSION['rate_limit'][$key][] = $currentTime;
    return true;
}

/**
 * Validate file upload security
 */
function isValidUpload($file, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif']) {
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return false;
    }
    
    // Check file size (max 5MB)
    if ($file['size'] > 5242880) {
        return false;
    }
    
    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return false;
    }
    
    return true;
}

/**
 * Secure redirect
 */
function secureRedirect($url, $allowedDomains = []) {
    // If internal redirect (starts with /), allow it
    if (strpos($url, '/') === 0) {
        redirect($url);
        return;
    }
    
    // Parse URL
    $parsedUrl = parse_url($url);
    
    // Check if domain is allowed
    if (isset($parsedUrl['host'])) {
        $allowedDomains[] = $_SERVER['HTTP_HOST'];
        if (!in_array($parsedUrl['host'], $allowedDomains)) {
            redirect('/');
            return;
        }
    }
    
    redirect($url);
}

/**
 * Prevent SQL injection by using prepared statements
 * This is a reminder function - always use PDO prepared statements
 */
function prepareQuery($conn, $sql, $params = []) {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Log security event (optional - for monitoring)
 */
function logSecurityEvent($event, $details = []) {
    $logFile = BASE_PATH . '/logs/security.log';
    $logDir = dirname($logFile);
    
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logEntry = date('Y-m-d H:i:s') . ' - ' . $event . ' - ' . json_encode($details) . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}
?>
