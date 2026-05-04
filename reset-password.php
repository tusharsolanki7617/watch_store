<?php
$pageTitle = 'Reset Password';
include 'includes/header.php';

// If already logged in, redirect
if (isLoggedIn()) {
    redirect(SITE_URL . '/profile.php');
}

// Check if email is in session
if (!isset($_SESSION['reset_email'])) {
    redirect(SITE_URL . '/forgot-password.php');
}

$email = $_SESSION['reset_email'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])) {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('danger', 'Invalid request');
    } else {
        $otp = sanitizeInput($_POST['otp']);
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if (empty($otp) || empty($newPassword) || empty($confirmPassword)) {
            setFlashMessage('danger', 'Please fill in all fields');
        } elseif ($newPassword !== $confirmPassword) {
            setFlashMessage('danger', 'Passwords do not match');
        } elseif (strlen($newPassword) < 8) {
            setFlashMessage('danger', 'Password must be at least 8 characters');
        } else {
            // Verify OTP
            $stmt = $conn->prepare("
                SELECT * FROM password_resets 
                WHERE email = ? AND otp = ? AND is_used = 0 AND expires_at > NOW() 
                ORDER BY created_at DESC LIMIT 1
            ");
            $stmt->execute([$email, $otp]);
            $resetRequest = $stmt->fetch();
            
            if ($resetRequest) {
                // Update password
                $hashedPassword = hashPassword($newPassword);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->execute([$hashedPassword, $email]);
                
                // Mark OTP as used
                $stmt = $conn->prepare("UPDATE password_resets SET is_used = 1 WHERE id = ?");
                $stmt->execute([$resetRequest['id']]);
                
                // Clear session
                unset($_SESSION['reset_email']);
                
                setFlashMessage('success', 'Your password has been reset successfully! You can now login with your new password.');
                redirect(SITE_URL . '/login.php');
            } else {
                setFlashMessage('danger', 'Invalid or expired OTP. Please try again.');
            }
        }
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="glass-card shadow-lg mb-5">
                <div class="card-body p-5">
                    <h3 class="text-center mb-4 text-white fw-bold"><i class="bi bi-key text-primary-purple"></i> Reset <span class="text-primary-purple">Credentials</span></h3>
                    <p class="text-muted text-center mb-4">Enter the 6-digit OTP sent to <strong class="text-white"><?php echo escapeOutput($email); ?></strong> and your new password.</p>
                    
                    <form method="POST" id="resetPasswordForm" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="mb-3">
                            <label class="form-label text-muted small text-uppercase fw-bold">OTP (6-digit)</label>
                            <input type="text" name="otp" class="form-control text-center py-2 fs-4 bg-dark border-glass text-primary-purple" placeholder="000000" maxlength="6" pattern="\d{6}" required>
                            <div class="form-text text-muted italic">Check your email for the verification code.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted small text-uppercase fw-bold">New Password</label>
                            <input type="password" name="new_password" id="new_password" class="form-control bg-dark border-glass text-white" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label text-muted small text-uppercase fw-bold">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control bg-dark border-glass text-white" required>
                        </div>
                        
                        <button type="submit" name="reset_password" class="btn btn-primary w-100 mb-3 rounded-pill shadow-glow">
                            <i class="bi bi-check2-circle"></i> Verify & Reset
                        </button>
                        
                        <div class="text-center">
                            <p class="mb-0 text-muted small">Didn't receive OTP? <a href="<?php echo SITE_URL; ?>/forgot-password.php" class="text-primary-purple fw-bold">Resend</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
