<?php
$pageTitle = 'Login';
include 'includes/header.php';

// If already logged in, redirect
if (isLoggedIn()) {
    redirect(SITE_URL . '/profile.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('danger', 'Invalid request');
    } else {
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        
        if (empty($email) || empty($password)) {
            setFlashMessage('danger', 'Please fill in all fields');
        } else {
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && verifyPassword($password, $user['password'])) {
                if ($user['is_active'] == 0) {
                    setFlashMessage('warning', 'Please activate your account first. Check your email.');
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_name'] = $user['full_name'];
                    regenerateSession();
                    
                    setFlashMessage('success', 'Welcome back, ' . $user['full_name'] . '!');
                    redirect(isset($_GET['redirect']) ? $_GET['redirect'] : SITE_URL);
                }
            } else {
                setFlashMessage('danger', 'Invalid email or password');
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
                    <h3 class="text-center mb-4 text-white fw-bold"><i class="bi bi-person-circle text-primary-purple"></i> Nexus <span class="text-primary-purple">Login</span></h3>
                    
                    <form method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="mb-3">
                            <label class="form-label text-white small text-uppercase fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control bg-dark border-glass text-white" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-white small text-uppercase fw-bold">Password</label>
                            <input type="password" name="password" class="form-control bg-dark border-glass text-white" required>
                        </div>
                        
                        <div class="mb-3 d-flex justify-content-between">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember">
                                <label class="form-check-label text-white" for="remember">Remember me</label>
                            </div>
                            <a href="<?php echo SITE_URL; ?>/forgot-password.php" class="text-primary-purple">Forgot Password?</a>
                        </div>
                        
                        <button type="submit" name="login" class="btn btn-primary w-100 mb-3 rounded-pill shadow-glow">
                            <i class="bi bi-box-arrow-in-right"></i> Enter Nexus
                        </button>
                        
                        <p class="text-center mb-0 text-muted">
                            Don't have an account? <a href="<?php echo SITE_URL; ?>/register.php" class="text-primary-purple fw-bold">Register here</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
