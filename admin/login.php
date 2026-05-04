<?php
require_once '../includes/config.php';

// If already logged in, redirect
if (isAdminLoggedIn()) {
    redirect('../admin/index.php');
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $admin = $stmt->fetch();
        
        if ($admin && verifyPassword($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_name'] = $admin['full_name'];
            regenerateSession();
            
            redirect('../admin/index.php');
        } else {
            $error = 'Invalid credentials';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/admin/assets/css/style.css">
</head>
<body class="bg-dark">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5">
                <div class="card shadow-lg border-0" style="background: #11111d; border-radius: 20px;">
                    <div class="card-body p-5">
                        <div class="text-center mb-4 animate-float">
                            <i class="bi bi-shield-lock display-3" style="color: var(--primary-purple); text-shadow: 0 0 20px rgba(157, 78, 221, 0.4);"></i>
                            <h3 class="mt-3 fw-bold text-white">Administrative <span class="text-primary-purple">Nexus</span></h3>
                            <p class="text-muted small text-uppercase letter-spacing-2"><?php echo SITE_NAME; ?></p>
                        </div>
                        
                        <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Identification</label>
                                <input type="text" name="username" class="form-control border-0 bg-dark rounded-pill px-4 py-2 text-white" placeholder="Username or Email" required>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Security Key</label>
                                <input type="password" name="password" class="form-control border-0 bg-dark rounded-pill px-4 py-2 text-white" placeholder="••••••••" required>
                            </div>
                            
                            <button type="submit" name="login" class="btn btn-primary w-100 rounded-pill py-3 fw-bold text-uppercase border-0 mt-2 shadow-glow">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Access Workspace
                            </button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <a href="<?php echo SITE_URL; ?>" class="text-decoration-none">
                                <i class="bi bi-arrow-left"></i> Back to Website
                            </a>
                        </div>
                    </div>
                </div>
                <p class="text-center text-muted mt-3">
                    <small>Default credentials: admin / Admin@123</small>
                </p>
            </div>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    <script>
    $(document).ready(function () {
        $('form').validate({
            rules: {
                username: { required: true, minlength: 3 },
                password: { required: true, minlength: 4 }
            },
            messages: {
                username: { required: 'Please enter your username or email', minlength: 'Minimum 3 characters' },
                password: { required: 'Please enter your password', minlength: 'Minimum 4 characters' }
            },
            errorPlacement: function (error, element) {
                error.insertAfter(element);
            },
            invalidHandler: function (e, validator) {
                var form = $(validator.currentForm);
                form.addClass('form-shake');
                setTimeout(function () { form.removeClass('form-shake'); }, 500);
            }
        });
    });
    </script>
</body>
</html>
