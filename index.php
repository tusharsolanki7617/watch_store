<?php
$pageTitle = 'Home';
$pageDescription = 'Discover premium watches - luxury timepieces, smart watches, and sports watches from top brands';

include 'includes/header.php';

// Get featured products
$stmt = $conn->query("SELECT p.*, c.name as category_name,
                      (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
                      FROM products p
                      JOIN categories c ON p.category_id = c.id
                      WHERE p.is_featured = 1 AND p.is_active = 1
                      LIMIT 8");
$featuredProducts = $stmt->fetchAll();

// Get new arrivals
$stmt = $conn->query("SELECT p.*, c.name as category_name,
                      (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
                      FROM products p
                      JOIN categories c ON p.category_id = c.id
                      WHERE p.is_active = 1
                      ORDER BY p.created_at DESC
                      LIMIT 8");
$newArrivals = $stmt->fetchAll();
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
        <div class="col-lg-6 mb-5 mb-lg-0 fade-in-up">
            <h1 class="hero-title text-white">The <span class="text-primary-purple">Ethereal</span> Collection</h1>
            <p class="hero-subtitle">Discover timless craftsmanship and futuristic precision. Where luxury meets the cosmic horizon.</p>
            <div class="d-flex gap-3">
                <a href="products.php" class="btn btn-primary">Explore Nexus</a>
                <a href="about.php" class="btn btn-outline-primary rounded-pill">Our Legacy</a>
            </div>
        </div>
            <div class="col-lg-6 text-center mt-5 mt-lg-0">
                <div class="hero-img-wrapper position-relative animate-float">
                    <img src="<?php echo SITE_URL; ?>/assets/images/1.webp" alt="Luxury Watch" class="img-fluid hero-watch-img" style="filter: drop-shadow(0 0 30px rgba(157,78,221,0.4));">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-5 reveal">
    <div class="container">
        <div class="text-center mb-5 reveal-stagger">
            <h2 class="section-title">Nexus <span>Collections</span></h2>
            <p class="text-white">Explore our curated interstellar archives</p>
        </div>
        
        <div class="row g-4 reveal-stagger">
            <?php foreach ($categories as $category): ?>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <a href="<?php echo SITE_URL; ?>/products.php?category=<?php echo $category['id']; ?>" class="text-decoration-none">
                    <div class="glass-card h-100 p-4 text-center glow-border">
                        <div class="category-icon mb-3">
                            <i class="bi bi-watch display-5 text-primary-purple"></i>
                        </div>
                        <h5 class="card-title fw-bold mb-2"><?php echo escapeOutput($category['name']); ?></h5>
                        <?php if ($category['description']): ?>
                        <p class="card-text text-white small mb-0"><?php echo truncateText(escapeOutput($category['description']), 60); ?></p>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<?php if (!empty($featuredProducts)): ?>
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5 reveal">
            <h2 class="section-title">Featured <span class="text-primary-purple">Artefacts</span></h2>
            <p class="text-muted">Masterpieces selected for the modern visionary.</p>
        </div>
        
        <div class="row g-4 reveal-stagger">
            <?php foreach ($featuredProducts as $product): 
                $rating = getProductRating($conn, $product['id']);
                $finalPrice = getFinalPrice($product);
                $discount = calculateDiscountPercentage($product['price'], $finalPrice);
            ?>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="product-card">
                    <div class="product-image">
                        <a href="<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $product['id']; ?>">
                            <?php if ($product['image']): ?>
                            <img src="<?php echo SITE_URL; ?>/uploads/products/<?php echo $product['image']; ?>" alt="<?php echo escapeOutput($product['name']); ?>" loading="lazy">
                            <?php else: ?>
                            <img src="<?php echo SITE_URL; ?>/assets/images/placeholder.jpg" alt="<?php echo escapeOutput($product['name']); ?>">
                            <?php endif; ?>
                        </a>
                        <?php if ($discount > 0): ?>
                        <span class="product-badge"><?php echo $discount; ?>% OFF</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-body text-center">
                        <div class="product-category small text-uppercase fw-bold text-primary-purple mb-1"><?php echo escapeOutput($product['category_name']); ?></div>
                        <h6 class="product-title fw-bold mb-2">
                            <a href="<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $product['id']; ?>"><?php echo truncateText(escapeOutput($product['name']), 40); ?></a>
                        </h6>
                        <div class="product-rating small mb-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star<?php echo $i <= $rating['average'] ? '-fill' : ''; ?> text-warning"></i>
                            <?php endfor; ?>
                        </div>
                        <div class="product-price mb-3">
                            <span class="h5 fw-bold text-primary-purple mb-0"><?php echo formatPrice($finalPrice); ?></span>
                            <?php if ($finalPrice < $product['price']): ?>
                            <span class="text-muted text-decoration-line-through small ms-2"><?php echo formatPrice($product['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        <button class="btn btn-primary btn-sm w-100 btn-add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                            <i class="bi bi-cart-plus me-1"></i> Add to Cart
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- New Arrivals -->
<?php if (!empty($newArrivals)): ?>
<section class="py-5 reveal">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">New <span>Arrivals</span></h2>
            <p class="section-subtitle">Latest additions to our collection</p>
        </div>
        
        <div class="row g-4">
            <?php foreach (array_slice($newArrivals, 0, 4) as $product): 
                $rating = getProductRating($conn, $product['id']);
                $finalPrice = getFinalPrice($product);
            ?>
            <div class="col-lg-3 col-md-6">
                <div class="product-card">
                    <div class="product-image">
                        <a href="<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $product['id']; ?>">
                            <?php if ($product['image']): ?>
                            <img src="<?php echo SITE_URL; ?>/uploads/products/<?php echo $product['image']; ?>" alt="<?php echo escapeOutput($product['name']); ?>" loading="lazy">
                            <?php else: ?>
                            <img src="<?php echo SITE_URL; ?>/assets/images/placeholder.jpg" alt="<?php echo escapeOutput($product['name']); ?>">
                            <?php endif; ?>
                        </a>
                        <span class="product-badge">NEW</span>
                    </div>
                    <div class="product-body text-center">
                        <h6 class="product-title fw-bold mb-2">
                            <a href="<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $product['id']; ?>"><?php echo truncateText(escapeOutput($product['name']), 40); ?></a>
                        </h6>
                        <div class="h5 fw-bold text-primary-purple mb-0"><?php echo formatPrice($finalPrice); ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Services Section -->
<section class="py-5 bg-dark text-white reveal">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-lg-3 col-md-6">
                <div class="service-icon mb-3">
                    <i class="bi bi-truck display-4 text-primary-purple"></i>
                </div>
                <h5 class="fw-bold">Free Shipping</h5>
                <p class="text-muted small">On orders over ₹<?php echo getSiteSetting($conn, 'free_shipping_min'); ?></p>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="service-icon mb-3">
                    <i class="bi bi-arrow-clockwise display-4 text-primary-purple"></i>
                </div>
                <h5 class="fw-bold">7-Day Returns</h5>
                <p class="text-muted small">Easy hassle-free returns</p>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="service-icon mb-3">
                    <i class="bi bi-shield-check display-4 text-primary-purple"></i>
                </div>
                <h5 class="fw-bold">Secure Payments</h5>
                <p class="text-muted small">100% encrypted checkout</p>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="service-icon mb-3">
                    <i class="bi bi-headset display-4 text-primary-purple"></i>
                </div>
                <h5 class="fw-bold">24/7 Support</h5>
                <p class="text-muted small">Dedicated support team</p>
            </div>
        </div>
    </div>
</section>

<!-- Advanced Scroll Reveal Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.reveal, .reveal-stagger').forEach(el => observer.observe(el));
});
</script>

<?php include 'includes/footer.php'; ?>
