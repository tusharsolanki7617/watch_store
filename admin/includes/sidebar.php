<div class="sidebar-overlay"></div>
<div id="sidebar" class="sidebar p-3 border-end border-purple" style="background: var(--sidebar-dark);">
    <button class="sidebar-close" aria-label="Close sidebar"><i class="bi bi-x"></i></button>
    <div class="sidebar-brand mb-2 p-3 text-center">
        <h3 class="mb-0 fw-bold text-primary-purple animate-pulse"><i class="bi bi-lightning-charge-fill me-2"></i>AETERNA</h3>
        <p class="text-muted text-uppercase mb-0 mt-1" style="font-size: 0.65rem; letter-spacing: 2px;">Administrative Nexus</p>
    </div>

    <!-- Admin Profile Overview -->
    <div class="sidebar-user-panel px-3 mb-4 text-center">
        <div class="position-relative d-inline-block mb-2">
            <?php if ($admin['profile_image']): ?>
                <img src="<?php echo SITE_URL; ?>/uploads/admins/<?php echo $admin['profile_image']; ?>" 
                     class="rounded-circle shadow-glow pulse-purple border border-purple" 
                     style="width: 65px; height: 65px; object-fit: cover;">
            <?php else: ?>
                <div class="rounded-circle bg-dark d-flex align-items-center justify-content-center border border-purple shadow-glow pulse-purple" 
                     style="width: 65px; height: 65px;">
                    <i class="bi bi-person-gear text-primary-purple" style="font-size: 1.5rem;"></i>
                </div>
            <?php endif; ?>
            <span class="position-absolute bottom-0 end-0 bg-success border border-dark rounded-circle p-1" style="width:12px; height:12px;"></span>
        </div>
        <h6 class="text-white mb-0 fw-bold small"><?php echo escapeOutput($admin['full_name']); ?></h6>
        <span class="text-muted" style="font-size: 0.6rem; letter-spacing: 1px;">NEXUS OPERATOR</span>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item mb-2">
            <a class="nav-link rounded-pill <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active shadow-lg' : 'text-muted'; ?>" 
               href="<?php echo SITE_URL; ?>/admin/index.php">
                <i class="bi bi-grid-1x2 me-2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link rounded-pill <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' || basename($_SERVER['PHP_SELF']) == 'add-product.php' || basename($_SERVER['PHP_SELF']) == 'edit-product.php' ? 'active shadow-lg' : 'text-muted'; ?>" 
               href="<?php echo SITE_URL; ?>/admin/products.php">
                <i class="bi bi-watch me-2"></i> Product
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link rounded-pill <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active shadow-lg' : 'text-muted'; ?>" 
               href="<?php echo SITE_URL; ?>/admin/categories.php">
                <i class="bi bi-layers me-2"></i> Collections
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link rounded-pill <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active shadow-lg' : 'text-muted'; ?>" 
               href="<?php echo SITE_URL; ?>/admin/orders.php">
                <i class="bi bi-cart-check me-2"></i> Order
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link rounded-pill <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active shadow-lg' : 'text-muted'; ?>" 
               href="<?php echo SITE_URL; ?>/admin/users.php">
                <i class="bi bi-people me-2"></i> Client 
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link rounded-pill <?php echo basename($_SERVER['PHP_SELF']) == 'coupons.php' ? 'active shadow-lg' : 'text-muted'; ?>" 
               href="<?php echo SITE_URL; ?>/admin/coupons.php">
                <i class="bi bi-ticket-perforated me-2"></i> Coupons
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link rounded-pill <?php echo basename($_SERVER['PHP_SELF']) == 'reviews.php' ? 'active shadow-lg' : 'text-muted'; ?>" 
               href="<?php echo SITE_URL; ?>/admin/reviews.php">
                <i class="bi bi-star me-2"></i> Feedback
                <?php
                try {
                    $pendingReviewsStmt = $conn->query("SELECT COUNT(*) FROM reviews WHERE is_approved = 0");
                    $pendingCount = $pendingReviewsStmt->fetchColumn();
                    if ($pendingCount > 0):
                    ?>
                    <span class="badge bg-primary-purple rounded-pill float-end shadow-glow pulse-purple"><?php echo $pendingCount; ?></span>
                    <?php 
                    endif;
                } catch (Exception $e) {}
                ?>
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link rounded-pill <?php echo basename($_SERVER['PHP_SELF']) == 'contact-messages.php' || basename($_SERVER['PHP_SELF']) == 'view-message.php' ? 'active shadow-lg' : 'text-muted'; ?>" 
               href="<?php echo SITE_URL; ?>/admin/contact-messages.php">
                <i class="bi bi-envelope me-2"></i>Contact us
                <?php
                $unreadStmt = $conn->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0");
                $unreadCount = $unreadStmt->fetchColumn();
                if ($unreadCount > 0):
                ?>
                <span class="badge bg-danger rounded-pill float-end shadow-glow animate-pulse"><?php echo $unreadCount; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="my-3 px-3"><hr class="border-purple opacity-25"></li>
        <li class="nav-item mb-2">
            <a class="nav-link rounded-pill text-muted" href="<?php echo SITE_URL; ?>" target="_blank">
                <i class="bi bi-globe me-2"></i> website
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link rounded-pill <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active shadow-lg' : 'text-muted'; ?>" 
               href="<?php echo SITE_URL; ?>/admin/profile.php">
                <i class="bi bi-person-circle me-2"></i> Admin Profile
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link rounded-pill text-danger" href="<?php echo SITE_URL; ?>/admin/logout.php">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
        </li>
       
    </ul>
</div>
