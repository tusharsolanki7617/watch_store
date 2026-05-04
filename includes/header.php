<?php
require_once __DIR__ . '/config.php';
$cartCount = getCartCount($conn);
$currentUser = isLoggedIn() ? getCurrentUser($conn) : null;
$stmt = $conn->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo isset($pageDescription) ? escapeOutput($pageDescription) : 'Premium watch store offering luxury, sports, and smart watches'; ?>">
    <title><?php echo isset($pageTitle) ? escapeOutput($pageTitle) . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>

    <!-- DNS Prefetch & Preconnect for CDN speed -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://code.jquery.com" crossorigin>
    <link rel="dns-prefetch" href="https://code.jquery.com">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Custom CSS (preloaded for speed) -->
    <link rel="preload" href="<?php echo SITE_URL; ?>/assets/css/style.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css"></noscript>

    <script>
        const SITE_URL = '<?php echo SITE_URL; ?>';
        const CSRF_TOKEN = '<?php echo generateCSRFToken(); ?>';
    </script>
    <?php if (isset($customCSS)) echo $customCSS; ?>
</head>
<body>

    <!-- Top Bar -->
    <div class="bg-dark py-2 text-white small" style="font-size:13px;">
        <div class="container d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-none d-md-flex align-items-center gap-2">
                <i class="bi bi-envelope me-1"></i> <?php echo SITE_EMAIL; ?>
                <span class="mx-2 text-white-50">|</span>
                <i class="bi bi-telephone me-1"></i> <?php echo getSiteSetting($conn, 'site_phone'); ?>
            </div>
            <div class="d-flex align-items-center gap-3 ms-auto">
                <?php if ($currentUser): ?>
                    <a href="<?php echo SITE_URL; ?>/profile.php" class="text-primary-purple text-decoration-none">
                        <i class="bi bi-person-circle me-1"></i><?php echo explode(' ', $currentUser['full_name'])[0]; ?>
                    </a>
                    <span class="text-primary-purple">|</span>
                    <a href="<?php echo SITE_URL; ?>/logout.php" class="text-primary-purple text-decoration-none">Logout</a>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/login.php" class="text-primary-purple text-decoration-none"><i class="bi bi-box-arrow-in-right me-1"></i>Login</a>
                    <span class="text-primary-purple">|</span>
                    <a href="<?php echo SITE_URL; ?>/register.php" class="text-primary-purple text-decoration-none"><i class="bi bi-person-plus me-1"></i>Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Glass Header -->
    <header class="main-header">
        <nav class="navbar navbar-expand-lg" aria-label="Main navigation">
            <div class="container">

                <!-- Brand -->
                <a class="navbar-brand" href="<?php echo SITE_URL; ?>">
                    <i class="bi bi-watch me-1 text-primary-purple"></i>AETERNA<span>NEXUS</span>
                </a>

                <!-- Mobile: Cart + Hamburger -->
                <div class="d-flex align-items-center gap-2 ms-auto d-lg-none">
                    <a href="<?php echo SITE_URL; ?>/cart.php" class="btn btn-primary btn-sm rounded-pill px-3 py-2 position-relative" aria-label="Cart">
                        <i class="bi bi-cart3"></i>
                        <?php if ($cartCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:10px;"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                    <button class="navbar-toggler-custom" type="button"
                            data-bs-toggle="collapse" data-bs-target="#navbarNav"
                            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>

                <!-- Collapsible Content -->
                <div class="collapse navbar-collapse" id="navbarNav">

                    <!-- Nav Links -->
                    <ul class="navbar-nav mx-auto">
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>">Home</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="catDrop" role="button" data-bs-toggle="dropdown" aria-expanded="false">Shop</a>
                            <ul class="dropdown-menu shadow-lg border-0 bg-dark" aria-labelledby="catDrop" style="background-color: #000 !important;">
                                <li><a class="dropdown-item py-2 text-white" href="<?php echo SITE_URL; ?>/products.php">All Watches</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <?php foreach ($categories as $category): ?>
                                    <li>
                                        <a class="dropdown-item py-2 text-white bg-dark" href="<?php echo SITE_URL; ?>/products.php?category=<?php echo $category['id']; ?>">
                                            <?php echo escapeOutput($category['name']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/about.php">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/contact.php">Contact</a>
                        </li>
                        <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'my-orders.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/my-orders.php">My Orders</a>
                        </li>
                        <?php endif; ?>
                    </ul>

                    <!-- Search + Cart (Desktop / stacked mobile) -->
                    <div class="navbar-actions d-flex align-items-center gap-3 mt-3 mt-lg-0">
                        <div class="search-box position-relative flex-grow-1 flex-lg-grow-0">
                            <input type="text"
                                   class="form-control form-control-sm border border-white bg-dark px-3 py-2 rounded-pill text-white w-100"
                                   id="searchInput" placeholder="Search watches..."
                                   style="min-width:140px; max-width:200px;"
                                   autocomplete="off">
                            <div id="searchResults" class="glass-card mt-2 shadow-lg"
                                 style="position:absolute; left:0; right:0; display:none; z-index:1050;"></div>
                        </div>
                        <!-- User Menu Dropdown -->
                        <?php if (isLoggedIn()): ?>
                        <div class="dropdown">
                            <button class="btn btn-primary btn-sm rounded-pill d-flex align-items-center gap-2 px-3 py-2 shadow-glow" 
                                    type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i>
                                <span class="d-none d-md-inline"><?php echo explode(' ', $currentUser['full_name'])[0]; ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end glass-card border-glass shadow-lg mt-2 bg-dark" aria-labelledby="userMenu" style="background-color: #000 !important;">
                                <li>
                                    <div class="dropdown-header text-muted small text-uppercase fw-bold">Account Nexus</div>
                                </li>
                                <li><a class="dropdown-item py-2 text-white" href="<?php echo SITE_URL; ?>/profile.php"><i class="bi bi-person me-2"></i> My Profile</a></li>
                                <li><a class="dropdown-item py-2 text-white" href="<?php echo SITE_URL; ?>/my-orders.php"><i class="bi bi-bag me-2"></i> My Orders</a></li>
                                <li><hr class="dropdown-divider border-glass"></li>
                                <li><a class="dropdown-item py-2 text-danger" href="<?php echo SITE_URL; ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                            </ul>
                        </div>
                        <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>/login.php" class="btn btn-outline-primary btn-sm rounded-pill px-4 py-2">
                            <i class="bi bi-door-open me-1"></i> Login
                        </a>
                        <?php endif; ?>

                        <!-- Cart btn — visible only on desktop; mobile cart is in toggler row -->
                        <a href="<?php echo SITE_URL; ?>/cart.php"
                           class="btn btn-primary btn-sm rounded-pill position-relative px-3 py-2 d-none d-lg-inline-flex align-items-center gap-1"
                           aria-label="Cart">
                            <i class="bi bi-cart3"></i>
                            <?php if ($cartCount > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger shadow-sm border border-2 border-purple" style="font-size:10px;"><?php echo $cartCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </div>

                </div><!-- /.navbar-collapse -->
            </div><!-- /.container -->
        </nav>
    </header>

    <!-- Flash Messages -->
    <?php $flash = getFlashMessage(); if ($flash): ?>
    <div class="container mt-4 animate-fadeIn">
        <div class="alert alert-<?php echo $flash['type']; ?> glass-card border-0 shadow-sm alert-dismissible fade show">
            <div class="d-flex align-items-center">
                <i class="bi bi-<?php echo $flash['type'] == 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2 h5 mb-0"></i>
                <div><?php echo escapeOutput($flash['message']); ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>

    <main class="fade-in">
