<?php
$pageTitle = 'My Profile';
include 'includes/header.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    redirect(SITE_URL . '/login.php');
}

$user = getCurrentUser($conn);
$userId = $user['id'];
$message = '';
$messageType = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $fullName = sanitizeInput($_POST['full_name']);
        $phone = sanitizeInput($_POST['phone']);
        $address = sanitizeInput($_POST['address'] ?? '');
        
        if (empty($fullName)) {
            $message = 'Full Name is required.';
            $messageType = 'danger';
        } else {
            // Update basic info
            try {
                $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?");
                $stmt->execute([$fullName, $phone, $address, $userId]);
                
                // Handle Profile Image Upload
                if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    $fileType = $_FILES['profile_image']['type'];
                    $fileSize = $_FILES['profile_image']['size'];
                    
                    if (!in_array($fileType, $allowedTypes)) {
                        $message = 'Only JPG, PNG, and GIF files are allowed.';
                        $messageType = 'danger';
                    } elseif ($fileSize > 2 * 1024 * 1024) { // 2MB
                        $message = 'File size must be less than 2MB.';
                        $messageType = 'danger';
                    } else {
                        // Create directory if not exists
                        if (!file_exists(PROFILE_IMAGE_PATH)) {
                            mkdir(PROFILE_IMAGE_PATH, 0777, true);
                        }
                        
                        // Generate unique filename
                        $extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                        $filename = 'user_' . $userId . '_' . time() . '.' . $extension;
                        $targetPath = PROFILE_IMAGE_PATH . '/' . $filename;
                        
                        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
                            // Delete old image if exists
                            if ($user['profile_image'] && file_exists(PROFILE_IMAGE_PATH . '/' . $user['profile_image'])) {
                                unlink(PROFILE_IMAGE_PATH . '/' . $user['profile_image']);
                            }
                            
                            // Update database
                            $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                            $stmt->execute([$filename, $userId]);
                            
                            // Update session info if needed
                             $_SESSION['user_name'] = $fullName;
                        } else {
                            $message = 'Failed to upload image.';
                            $messageType = 'danger';
                        }
                    }
                }
                
                if (empty($message)) {
                    $message = 'Profile updated successfully!';
                    $messageType = 'success';
                    // Refresh user data
                    $user = getCurrentUser($conn);
                }
            } catch (PDOException $e) {
                $message = 'Database error: ' . $e->getMessage();
                $messageType = 'danger';
            }
        }
    }

    // Handle Password Update
    if (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $message = 'All password fields are required.';
            $messageType = 'danger';
        } elseif (!verifyPassword($currentPassword, $user['password'])) {
            $message = 'Current password is incorrect.';
            $messageType = 'danger';
        } elseif ($newPassword !== $confirmPassword) {
            $message = 'New passwords do not match.';
            $messageType = 'danger';
        } elseif (!isValidPassword($newPassword)) {
            $message = 'New password is too weak. Must be 8+ chars with uppercase, lowercase, and numeric.';
            $messageType = 'danger';
        } else {
            try {
                $newHash = hashPassword($newPassword);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$newHash, $userId]);
                
                $message = 'Password updated successfully!';
                $messageType = 'success';
                
                // Refresh user data
                $user = getCurrentUser($conn);
            } catch (PDOException $e) {
                $message = 'Database error: ' . $e->getMessage();
                $messageType = 'danger';
            }
        }
    }
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-3 mb-4">
            <!-- Nexus Sidebar -->
            <div class="glass-card shadow-lg text-center p-4 reveal">
                <div class="profile-image-container mb-3 mx-auto shadow-glow pulse-purple rounded-circle" style="width: 100px; height: 100px; position: relative;">
                    <?php if ($user['profile_image']): ?>
                        <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo $user['profile_image']; ?>" 
                             alt="Profile" 
                             class="rounded-circle img-thumbnail w-100 h-100" 
                             style="object-fit: cover;">
                    <?php else: ?>
                        <div class="rounded-circle bg-dark d-flex align-items-center justify-content-center w-100 h-100 border border-glass">
                            <i class="bi bi-person text-muted display-1"></i>
                        </div>
                    <?php endif; ?>
                    
                    <label for="profile_image_input" class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle" style="width: 35px; height: 35px; padding: 0; line-height: 35px;" title="Change Photo">
                        <i class="bi bi-camera"></i>
                    </label>
                </div>
                
                <h4 class="text-white fw-bold"><?php echo escapeOutput($user['full_name']); ?></h4>
                <p class="text-muted mb-1"><?php echo escapeOutput($user['email']); ?></p>
                <span class="badge bg-success shadow-green">Active Member</span>
                
                <hr class="my-4">
                
                <div class="d-grid gap-2 text-start mt-4">
                    <a href="<?php echo SITE_URL; ?>/profile.php" class="btn btn-outline-primary text-start active shadow-glow border-0"><i class="bi bi-person me-2"></i> My Profile</a>
                    <a href="<?php echo SITE_URL; ?>/logout.php" class="btn btn-outline-danger text-start border-0"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
                </div>
            </div>
        </div>
        
        <div class="col-lg-9">
            <div class="glass-card shadow-lg reveal">
                <div class="p-4 border-bottom border-glass">
                    <h5 class="mb-0 text-white fw-bold"><i class="bi bi-person-lines-fill text-primary-purple me-2"></i> User <span class="text-primary-purple">Profile</span></h5>
                </div>
                <div class="card-body p-4">
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data">
                        <!-- Hidden file input triggered by the camera icon -->
                        <input type="file" name="profile_image" id="profile_image_input" class="d-none" accept="image/*" onchange="previewImage(this)">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase fw-bold">Full Name</label>
                                <input type="text" name="full_name" class="form-control bg-dark border-glass text-white" value="<?php echo escapeOutput($user['full_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase fw-bold">Phone Number</label>
                                <input type="tel" name="phone" class="form-control bg-dark border-glass text-white" value="<?php echo escapeOutput($user['phone'] ?? ''); ?>" placeholder="+91">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted small text-uppercase fw-bold">Email Address</label>
                            <input type="email" class="form-control bg-dark border-glass text-muted" value="<?php echo escapeOutput($user['email']); ?>" readonly disabled>
                            <small class="text-muted italic">Email cannot be changed.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted small text-uppercase fw-bold">Shipping Address</label>
                            <textarea name="address" class="form-control bg-dark border-glass text-white" rows="3" placeholder="Enter your default shipping address..."><?php echo escapeOutput($user['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="text-end mt-4">
                            <button type="submit" name="update_profile" class="btn btn-primary px-5 rounded-pill shadow-glow">
                                <i class="bi bi-save me-2"></i> Save Changes
                            </button>
                        </div>
                    </form>

                    <hr class="my-4 border-glass">

                    <div class="p-3 bg-deep-dark rounded border border-glass mb-4 reveal">
                        <h6 class="text-white fw-bold mb-3"><i class="bi bi-shield-lock text-primary-purple me-2"></i> Security <span class="text-primary-purple">Nexus</span></h6>
                        
                        <form method="POST" action="profile.php">
                            <div class="mb-3">
                                <label class="form-label text-muted small text-uppercase fw-bold">Current Password</label>
                                <input type="password" name="current_password" class="form-control bg-dark border-glass text-white" required>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small text-uppercase fw-bold">New Password</label>
                                    <input type="password" name="new_password" class="form-control bg-dark border-glass text-white" required minlength="8">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small text-uppercase fw-bold">Confirm Password</label>
                                    <input type="password" name="confirm_password" class="form-control bg-dark border-glass text-white" required>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" name="change_password" class="btn btn-outline-primary rounded-pill px-4">
                                    <i class="bi bi-key me-2"></i> Update Password Protocol
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity / Stats can go here -->
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const observerOptions = {
        threshold: 0.05,
        rootMargin: '0px 0px -20px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
            }
        });
    }, observerOptions);

    const revealElements = document.querySelectorAll('.reveal, .reveal-stagger');
    revealElements.forEach(el => observer.observe(el));

    // Visibility Safety Fallback
    setTimeout(() => {
        revealElements.forEach(el => {
            if (!el.classList.contains('active')) {
                el.classList.add('active');
            }
        });
    }, 1000);
});

function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        
        reader.onload = function(e) {
            document.querySelector('.profile-image-container img').src = e.target.result;
        }
        
        reader.readAsDataURL(input.files[0]);
        const btn = document.querySelector('button[name="update_profile"]');
        btn.innerHTML = '<i class="bi bi-save-fill"></i> Save New Photo';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-primary');
    }
}
</script>

<?php include 'includes/footer.php'; ?>
