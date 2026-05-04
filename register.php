<?php
$pageTitle = 'Register';
include 'includes/header.php';
require_once 'includes/mailer.php';

if (isLoggedIn()) {
    redirect(SITE_URL);
}

if (isLoggedIn()) {
    redirect(SITE_URL);
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="glass-card shadow-lg mb-5">
                <div class="card-body p-5">
                    <h3 class="text-center mb-4 text-white fw-bold"><i class="bi bi-person-plus text-primary-purple"></i> Create <span class="text-primary-purple">Account</span></h3>
                    
                    <!-- Alert for AJAX responses -->
                    <div id="registerAlert" class="alert d-none"></div>
                    
                    <form id="registerForm" class="ajax-form needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-white small text-uppercase fw-bold">Full Name</label>
                                <input type="text" name="full_name" class="form-control bg-dark border-glass text-white" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white small text-uppercase fw-bold">Phone Number</label>
                                <input type="tel" name="phone" class="form-control bg-dark border-glass text-white" placeholder="+91">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white small text-uppercase fw-bold">Shipping Address</label>
                            <textarea name="address" class="form-control bg-dark border-glass text-white" rows="2" placeholder="Street, City, State, ZIP"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-white small text-uppercase fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control bg-dark border-glass text-white" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-white small text-uppercase fw-bold">Password</label>
                            <input type="password" name="password" class="form-control bg-dark border-glass text-white" required minlength="8">
                            <small class="text-muted italic">Min 8 chars, 1 uppercase, 1 lowercase, 1 number</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-white small text-uppercase fw-bold">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control bg-dark border-glass text-white" required>
                        </div>
                        
                        <button type="submit" name="register" class="btn btn-primary w-100 mb-3 rounded-pill shadow-glow">
                            <i class="bi bi-person-check"></i> Join Nexus
                        </button>
                        
                        <p class="text-center mb-0 text-muted">
                            Already have an account? <a href="<?php echo SITE_URL; ?>/login.php" class="text-primary-purple fw-bold">Login here</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
