<?php
$pageTitle = 'Forgot Password';
include 'includes/header.php';

// If already logged in, redirect
if (isLoggedIn()) {
    redirect(SITE_URL . '/profile.php');
}
// AJAX handler now manages password resets
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="glass-card shadow-lg mb-5">
                <div class="card-body p-5">
                    <h3 class="text-center mb-4 text-white fw-bold"><i class="bi bi-shield-lock text-primary-purple"></i> Nexus <span class="text-primary-purple">Recovery</span></h3>
                    <p class="text-white text-center mb-4">Enter your registered email address and we'll send you an OTP to reset your password.</p>
                    
                    <!-- Alert for AJAX responses -->
                    <div id="registerAlert" class="alert d-none"></div>
                    
                    <form method="POST" id="forgotPasswordForm" class="ajax-form needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="mb-4">
                            <label class="form-label text-white small text-uppercase fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control bg-dark border-glass text-white" placeholder="example@nexus.com" required>
                        </div>
                        
                        <button type="submit" name="forgot_password" class="btn btn-primary w-100 mb-3 rounded-pill shadow-glow">
                            <i class="bi bi-send-fill me-2"></i> Request OTP
                        </button>
                        
                        <div class="text-center">
                            <a href="<?php echo SITE_URL; ?>/login.php" class="text-decoration-none text-primary-purple">
                                <i class="bi bi-arrow-left"></i> Back to Login
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
