<?php
require_once '../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('../admin/login.php');
}

$admin = getCurrentAdmin($conn);

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $fullName = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    try {
        $conn->beginTransaction();
        
        // Update basic info
        $sql = "UPDATE admins SET full_name = ?, email = ? WHERE id = ?";
        $params = [$fullName, $email, $admin['id']];
        
        // Update Password if provided
        if (!empty($newPassword)) {
            if ($newPassword !== $confirmPassword) {
                throw new Exception("Passwords do not match.");
            }
            if (strlen($newPassword) < 6) {
                throw new Exception("Password must be at least 6 characters.");
            }
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $sql = "UPDATE admins SET full_name = ?, email = ?, password = ? WHERE id = ?";
            $params = [$fullName, $email, $hashedPassword, $admin['id']];
        } else {
            $stmt = $conn->prepare("UPDATE admins SET full_name = ?, email = ? WHERE id = ?");
            $params = [$fullName, $email, $admin['id']];
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        // Handle Profile Image
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = UPLOAD_PATH . '/admins';
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
            
            $file = $_FILES['profile_image'];
            $fileName = $file['name'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])) {
                $newFileName = 'admin_' . $admin['id'] . '_' . time() . '.' . $fileExt;
                $destination = $uploadDir . '/' . $newFileName;
                
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    // Delete old image if exists
                    if ($admin['profile_image'] && file_exists($uploadDir . '/' . $admin['profile_image'])) {
                        unlink($uploadDir . '/' . $admin['profile_image']);
                    }
                    
                    $stmt = $conn->prepare("UPDATE admins SET profile_image = ? WHERE id = ?");
                    $stmt->execute([$newFileName, $admin['id']]);
                }
            } else {
                throw new Exception("Invalid image format. Only JPG, PNG, GIF allowed.");
            }
        }
        
        $conn->commit();
        setFlashMessage('success', 'Profile updated successfully!');
        
        // Update session name if changed
        $_SESSION['admin_name'] = $fullName;
        
        redirect('profile.php');
        
    } catch (Exception $e) {
        $conn->rollBack();
        setFlashMessage('danger', 'Error updating profile: ' . $e->getMessage());
    }
}

$pageTitle = 'Admin Profile';
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-lg">
            <div class="card-header py-4 border-0">
                <h5 class="mb-0 fw-bold text-primary-purple"><i class="bi bi-person-gear me-2"></i>Security & Identity</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" id="profileForm">
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <?php if ($admin['profile_image']): ?>
                                <img src="<?php echo SITE_URL; ?>/uploads/admins/<?php echo $admin['profile_image']; ?>" class="rounded-circle border" style="width: 120px; height: 120px; object-fit: cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-dark d-flex align-items-center justify-content-center border border-purple" style="width: 120px; height: 120px; font-size: 3rem; color: var(--primary-purple);">
                                    <i class="bi bi-person"></i>
                                </div>
                            <?php endif; ?>
                            <label for="profile_image" class="position-absolute bottom-0 end-0 bg-dark text-primary-purple rounded-circle p-2 shadow-sm border border-purple" style="cursor: pointer;">
                                <i class="bi bi-camera"></i>
                            </label>
                            <input type="file" name="profile_image" id="profile_image" class="d-none" accept="image/*" onchange="previewImage(this)">
                        </div>
                        <p class="text-muted small mt-2">Click icon to change photo</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="full_name" class="form-control" value="<?php echo escapeOutput($admin['full_name']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address *</label>
                            <input type="email" name="email" class="form-control" value="<?php echo escapeOutput($admin['email']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="<?php echo escapeOutput($admin['username']); ?>" disabled readonly style="opacity: 0.6;">
                        <div class="form-text small opacity-50">Username cannot be changed.</div>
                    </div>
                    
                    <hr class="my-4 opacity-50">
                    <h6 class="mb-4 fw-bold small text-uppercase text-muted">Security Settings</h6>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" id="new_password" class="form-control" minlength="6" placeholder="Leave blank to keep current">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Repeat new password">
                        </div>
                    </div>
                    
                    <div class="d-grid mt-4">
                        <button type="submit" name="update_profile" class="btn btn-primary rounded-pill py-2">
                            <i class="bi bi-shield-check me-2"></i>Update Nexus Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var $img = $(input).parent().find('img');
            var $placeholder = $(input).parent().find('.rounded-circle.bg-dark');
            if ($img.length) {
                $img.attr('src', e.target.result);
            } else if ($placeholder.length) {
                $placeholder.replaceWith('<img src="' + e.target.result + '" class="rounded-circle border border-purple" style="width: 120px; height: 120px; object-fit: cover;">');
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

$(document).ready(function () {

    // Custom: image must be jpg/png/gif
    $.validator.addMethod('imageExt', function (value, element) {
        return !value || /\.(jpg|jpeg|png|gif)$/i.test(value);
    }, 'Only JPG, PNG or GIF images are allowed.');

    $('#profileForm').validate({
        rules: {
            full_name:        { required: true, minlength: 3 },
            email:            { required: true, email: true },
            new_password:     { minlength: 6 },
            confirm_password: { equalTo: '#new_password' },
            profile_image:    { imageExt: true }
        },
        messages: {
            full_name:        { required: 'Full name is required', minlength: 'At least 3 characters' },
            email:            { required: 'Email is required', email: 'Enter a valid email address' },
            new_password:     { minlength: 'Password must be at least 6 characters' },
            confirm_password: { equalTo: 'Passwords do not match' },
            profile_image:    { imageExt: 'Only JPG, PNG or GIF allowed' }
        },
        errorPlacement: function (error, element) {
            error.insertAfter(element);
        },
        invalidHandler: function (e, validator) {
            $(validator.currentForm).addClass('form-shake');
            setTimeout(function () { $(validator.currentForm).removeClass('form-shake'); }, 500);
        }
    });
});
</script>


<?php include 'includes/footer.php'; ?>
