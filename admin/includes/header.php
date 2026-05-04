<?php
if (!isset($admin) && isAdminLoggedIn()) {
    $admin = getCurrentAdmin($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Admin Panel</title>
    <!-- Modern Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/admin/assets/css/style.css">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-grow-1 main-content" style="min-width:0;">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg px-3 px-md-4 border-bottom border-purple"
                 style="background: var(--glass-purple); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);">
                <!-- Hamburger -->
                <button class="btn btn-dark border-purple text-primary-purple me-3 shadow-glow flex-shrink-0" id="sidebarCollapse" aria-label="Open sidebar">
                    <i class="bi bi-list"></i>
                </button>

                <!-- Page title -->
                <span class="navbar-brand mb-0 h4 fw-bold text-white text-truncate" style="max-width:calc(100vw - 180px);">
                    <?php echo $pageTitle; ?>
                </span>

                <!-- Right: operator info -->
                <div class="ms-auto d-flex align-items-center gap-2 flex-shrink-0">
                    <div class="d-lg-flex align-items-center gap-2">
                        <span class="text-muted small text-uppercase fw-bold" style="letter-spacing:1px;">Operator:</span>
                        <strong class="text-white"><?php echo escapeOutput($admin['full_name']); ?></strong>
                    </div>
                    <!-- Avatar Avatar -->
                    <div class="ms-2">
                        <?php if ($admin['profile_image']): ?>
                            <a href="<?php echo SITE_URL; ?>/admin/profile.php">
                                <img src="<?php echo SITE_URL; ?>/uploads/admins/<?php echo $admin['profile_image']; ?>" 
                                     class="rounded-circle border border-purple shadow-sm hover-scale" 
                                     style="width: 32px; height: 32px; object-fit: cover;">
                            </a>
                        <?php else: ?>
                            <a href="<?php echo SITE_URL; ?>/admin/profile.php" class="text-primary-purple h4 mb-0">
                                <i class="bi bi-person-circle"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </nav>

            <div class="p-3 p-md-4">
                <?php
                $flashMessage = getFlashMessage();
                if ($flashMessage):
                ?>
                <div class="alert alert-<?php echo $flashMessage['type']; ?> glass-card border-glass shadow-sm alert-dismissible fade show text-black mb-4">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-<?php echo $flashMessage['type'] == 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2 h5 mb-0"></i>
                        <div><?php echo escapeOutput($flashMessage['message']); ?></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
