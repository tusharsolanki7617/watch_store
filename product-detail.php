<?php
$pageTitle = 'Product Details';
include 'includes/header.php';

// Get product ID
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId <= 0) {
    redirect(SITE_URL . '/products.php');
}

// Get product details
$stmt = $conn->prepare("
    SELECT p.*, c.name as category_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.id = ? AND p.is_active = 1
");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    redirect(SITE_URL . '/products.php');
}

// Update view count
$stmt = $conn->prepare("UPDATE products SET views = views + 1 WHERE id = ?");
$stmt->execute([$productId]);

// Get product images
$stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, display_order");
$stmt->execute([$productId]);
$images = $stmt->fetchAll();

// Get product rating
$rating = getProductRating($conn, $productId);

// Get reviews (Approved reviews + Current user's pending reviews)
$userId = isLoggedIn() ? $_SESSION['user_id'] : 0;
$stmt = $conn->prepare("
    SELECT r.*, u.full_name, u.profile_image
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.product_id = ? AND (r.is_approved = 1 OR r.user_id = ?)
    ORDER BY r.created_at DESC
    LIMIT 10
");
$stmt->execute([$productId, $userId]);
$reviews = $stmt->fetchAll();

// Get related products
$stmt = $conn->prepare("
    SELECT p.*, 
    (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
    FROM products p
    WHERE p.category_id = ? AND p.id != ? AND p.is_active = 1
    ORDER BY RAND()
    LIMIT 4
");
$stmt->execute([$product['category_id'], $productId]);
$relatedProducts = $stmt->fetchAll();

$finalPrice = getFinalPrice($product);
$discount = calculateDiscountPercentage($product['price'], $finalPrice);
?>

<div class="container my-5 fade-in-up">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb glass-card p-3 rounded-pill px-4 small shadow-lg">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>" class="text-decoration-none text-muted">Home</a></li>
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/products.php" class="text-decoration-none text-muted">Shop</a></li>
            <li class="breadcrumb-item active text-primary-purple fw-bold"><?php echo escapeOutput($product['name']); ?></li>
        </ol>
    </nav>

    <div class="row g-5">
        <!-- Product Images -->
        <div class="col-lg-6 mb-4">
            <div class="product-image-gallery">
                <?php if (!empty($images)): ?>
                    <div class="main-image-wrapper glass-card p-3 mb-3 text-center overflow-hidden">
                        <img src="<?php echo SITE_URL; ?>/uploads/products/<?php echo $images[0]['image_path']; ?>" 
                             alt="<?php echo escapeOutput($product['name']); ?>" 
                             class="img-fluid hero-watch-img" 
                             style="max-height: 500px;"
                             id="mainImage">
                    </div>
                    <?php if (count($images) > 1): ?>
                    <div class="thumbnail-images d-flex gap-3 justify-content-center">
                        <?php foreach ($images as $index => $image): ?>
                        <div class="thumb-wrapper glass-card p-1 <?php echo $index === 0 ? 'active border-primary' : ''; ?>" style="cursor: pointer;" onclick="changeImage('<?php echo SITE_URL; ?>/uploads/products/<?php echo $image['image_path']; ?>', this)">
                            <img src="<?php echo SITE_URL; ?>/uploads/products/<?php echo $image['image_path']; ?>" 
                                 class="rounded" 
                                 style="width: 70px; height: 70px; object-fit: cover;">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="glass-card p-5 text-center">
                        <img src="<?php echo SITE_URL; ?>/assets/images/placeholder.jpg" alt="No image" class="img-fluid rounded opacity-50">
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Product Info -->
        <div class="col-lg-6">
            <div class="product-badge mb-3 d-inline-block" style="background: linear-gradient(135deg, var(--deep-purple), var(--primary-purple)); position: static;">
                <i class="bi bi-tag me-1 text-white"></i> <?php echo escapeOutput($product['category_name']); ?>
            </div>
            
            <h1 class="display-5 fw-bold mb-3"><?php echo escapeOutput($product['name']); ?></h1>
            
            <div class="d-flex align-items-center mb-4">
                <div class="text-warning h5 mb-0">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="bi bi-star<?php echo $i <= $rating['average'] ? '-fill' : ''; ?>"></i>
                    <?php endfor; ?>
                </div>
                <span class="ms-3 text-muted fw-bold"><?php echo number_format($rating['average'], 1); ?></span>
                <span class="mx-2 text-muted-50">|</span>
                <span class="text-muted small"><?php echo $rating['count']; ?> verified reviews</span>
            </div>

            <div class="price-section mb-4 p-4 glass-card d-inline-block shadow-glow">
                <div class="d-flex align-items-center gap-3">
                    <h2 class="fw-bold mb-0 text-white"><?php echo formatPrice($finalPrice); ?></h2>
                    <?php if ($finalPrice < $product['price']): ?>
                        <span class="text-muted text-decoration-line-through h4 mb-0"><?php echo formatPrice($product['price']); ?></span>
                        <span class="badge bg-danger rounded-pill px-3 shadow-glow"><?php echo $discount; ?>% OFF</span>
                    <?php endif; ?>
                </div>
            </div>

            <p class="lead text-muted mb-5"><?php echo nl2br(escapeOutput($product['description'])); ?></p>

            <!-- Specifications & Actions In One Section -->
            <div class="row g-4 mb-5">
                <div class="col-md-7">
                    <div class="glass-card p-4 h-100">
                        <h5 class="fw-bold mb-3 small text-uppercase tracking-wider">Specifications</h5>
                        <div class="specs-grid small">
                            <div class="row mb-2">
                                <span class="col-5 text-muted">Brand</span>
                                <span class="col-7 fw-bold"><?php echo escapeOutput($product['brand']); ?></span>
                            </div>
                            <div class="row mb-2">
                                <span class="col-5 text-muted">Movement</span>
                                <span class="col-7 fw-bold"><?php echo escapeOutput($product['movement_type']); ?></span>
                            </div>
                            <?php if ($product['water_resistance']): ?>
                            <div class="row mb-2">
                                <span class="col-5 text-muted">Water Resistance</span>
                                <span class="col-7 fw-bold"><?php echo escapeOutput($product['water_resistance']); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ($product['case_material']): ?>
                            <div class="row mb-2">
                                <span class="col-5 text-muted">Case Material</span>
                                <span class="col-7 fw-bold"><?php echo escapeOutput($product['case_material']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="glass-card p-4 h-100 border-primary">
                        <h5 class="fw-bold mb-3 small text-uppercase tracking-wider">Purchase</h5>
                        <?php if ($product['stock'] > 0): ?>
                            <div class="text-success small fw-bold mb-3 shadow-green d-inline-block px-2 rounded"><i class="bi bi-circle-fill me-2" style="font-size: 8px;"></i>In Stock (<?php echo $product['stock']; ?>)</div>
                            <div class="quantity-control mb-3">
                                <input type="number" id="quantity" class="form-control form-control-sm border-0 bg-dark rounded-pill px-3 mb-2 text-white" value="1" min="1" max="<?php echo $product['stock']; ?>">
                            </div>
                            <button class="btn btn-primary w-100 rounded-pill py-2 btn-add-to-cart" data-product-id="<?php echo $product['id']; ?>" data-quantity="1">
                                <i class="bi bi-cart-plus me-2"></i>Add to Cart
                            </button>
                        <?php else: ?>
                            <div class="text-danger small fw-bold mb-3"><i class="bi bi-circle-fill me-2" style="font-size: 8px;"></i>Currently Unavailable</div>
                            <button class="btn btn-outline-secondary w-100 rounded-pill disabled">Notify Me</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($product['features']): ?>
            <div class="glass-card p-4">
                <h5 class="fw-bold mb-3 small text-uppercase tracking-wider"><i class="bi bi-star-fill text-primary me-2"></i>Experience Highlights</h5>
                <div class="row small g-2">
                    <?php 
                    $features = explode("\n", $product['features']);
                    foreach ($features as $feature):
                        if (trim($feature)):
                    ?>
                        <div class="col-md-6 mb-1">
                            <i class="bi bi-check-lg text-primary me-2"></i><?php echo escapeOutput(trim($feature)); ?>
                        </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Reviews Section Heading (always visible) -->
    <div class="mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0 text-white">Customer <span class="text-primary-purple">Reviews</span></h3>
            <a href="#review-form-container" class="btn btn-outline-primary btn-sm rounded-pill px-4">Write Experience</a>
        </div>

        <!-- Platform Trust Banner (outside reveal so it's always visible) -->
        <div class="rounded-4 p-4 mb-5" style="border: 1px solid rgba(139,92,246,0.2); background: linear-gradient(135deg, rgba(10,10,20,0.95) 0%, rgba(30,10,60,0.7) 100%);">
            <div class="row align-items-center g-4">
                <div class="col-lg-6">
                    <p class="text-white-50 small mb-3 lh-lg">We value our customers' feedback and continuously strive to improve our services. Based on recent user ratings and reviews, our platform maintains a strong average rating, reflecting overall customer satisfaction and trust.</p>
                    <p class="text-white-50 small mb-0">Our average review score highlights the quality, reliability, and user experience we aim to deliver. We encourage all users to share their feedback, as it helps us enhance our offerings and serve you better.</p>
                </div>
                <div class="col-lg-6">
                    <div class="row g-3 text-center">
                        <!-- Average Rating Card -->
                        <div class="col-6">
                            <div class="p-3 rounded-3" style="background: rgba(255,215,0,0.08); border: 1px solid rgba(255,215,0,0.25);">
                                <div class="text-warning mb-2" style="font-size: 1.5rem; letter-spacing: 2px;">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-half"></i>
                                </div>
                                <div class="fw-bold text-white" style="font-size: 2rem; line-height:1;">4.5</div>
                                <div class="text-warning small fw-bold mt-1">/ 5.0</div>
                                <div class="text-white-50 small mt-1">⭐ Average Rating</div>
                            </div>
                        </div>
                        <!-- Total Reviews Card -->
                        <div class="col-6">
                            <div class="p-3 rounded-3" style="background: rgba(139,92,246,0.1); border: 1px solid rgba(139,92,246,0.3);">
                                <div class="mb-2" style="font-size: 1.5rem; color: #a78bfa;">
                                    <i class="bi bi-chat-quote-fill"></i>
                                </div>
                                <div class="fw-bold text-white" style="font-size: 2rem; line-height:1;">1K+</div>
                                <div class="text-white-50 small mt-1">📝 Total Reviews</div>
                                <div class="text-white-50 small mt-1">Verified Customers</div>
                            </div>
                        </div>
                        <!-- Trust Badge -->
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-center gap-3 p-2 rounded-3" style="background: rgba(34,197,94,0.05); border: 1px solid rgba(34,197,94,0.15);">
                                <i class="bi bi-shield-check text-success fs-5"></i>
                                <span class="text-white-50 small">All reviews are from verified purchases only</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- Reviews List (with scroll animation) -->
    <div class="reveal">
        
        <div class="row">
            <div class="col-lg-8">
                <?php if (!empty($reviews)): ?>
                    <div class="review-list">
                        <?php foreach ($reviews as $review): ?>
                            <div class="bg-black border-glass p-4 mb-4 review-item <?php echo !$review['is_approved'] ? 'border-warning' : ''; ?>" data-aos="fade-up">
                                <?php if (!$review['is_approved']): ?>
                                    <div class="badge bg-warning text-dark mb-3 rounded-pill px-3">Pending Moderation</div>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-glass rounded-circle p-1">
                                            <?php if ($review['profile_image']): ?>
                                                <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo $review['profile_image']; ?>" class="rounded-circle" style="width: 45px; height: 45px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                                    <?php echo strtoupper(substr($review['full_name'], 0, 1)); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-0 text-white"><?php echo escapeOutput($review['full_name']); ?></h6>
                                            <div class="text-warning small">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="bi bi-star<?php echo $i <= $review['rating'] ? '-fill' : ''; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <small class="text-muted-50 fw-bold"><?php echo timeAgo($review['created_at']); ?></small>
                                </div>
                                <?php if ($review['title']): ?>
                                    <h6 class="fw-bold text-white"><?php echo escapeOutput($review['title']); ?></h6>
                                <?php endif; ?>
                                <p class="text-white-50 mb-0 small"><?php echo escapeOutput($review['comment']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-black border-glass p-5 text-center" id="no-reviews-msg">
                        <i class="bi bi-chat-heart display-4 text-muted mb-3 d-block opacity-50"></i>
                        <p class="text-white-50 small">No one has shared their experience yet. Be the first to review!</p>
                    </div>
                <?php endif; ?>
                <div id="new-review-placeholder"></div>
            </div>
            
            <div class="col-lg-4">
                <!-- Add Review Form -->
                <div class="bg-black border-glass p-4 sticky-top" style="top: 100px;" id="review-form-container">
                    <h5 class="fw-bold mb-4 text-white">Share Your Experience</h5>
                    <?php if (isLoggedIn()): ?>
                        <form id="reviewForm" class="needs-validation" novalidate>
                            <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Rating</label>
                                <div class="rating-stars h4 text-warning" style="cursor: pointer;">
                                    <i class="bi bi-star star" data-rating="1"></i>
                                    <i class="bi bi-star star" data-rating="2"></i>
                                    <i class="bi bi-star star" data-rating="3"></i>
                                    <i class="bi bi-star star" data-rating="4"></i>
                                    <i class="bi bi-star star" data-rating="5"></i>
                                    <input type="hidden" name="rating" id="ratingValue" value="" required>
                                </div>
                                <div class="invalid-feedback">Please select a rating.</div>
                            </div>
                            <div class="mb-3">
                                <input type="text" name="title" class="form-control border-0 bg-dark rounded-pill px-3" placeholder="Review Title (Optional)">
                            </div>
                            <div class="mb-3">
                                <textarea name="comment" class="form-control border-0 bg-dark px-3 py-3 rounded-4" rows="4" required placeholder="Write your thoughts..."></textarea>
                                <div class="invalid-feedback">Share your experience with us.</div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 rounded-pill py-2" id="submitReviewBtn">
                                Post Review
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-lock display-5 text-muted mb-3 d-block"></i>
                            <p class="small text-white-50 mb-4">Sign in to share your thoughts about this timepiece.</p>
                            <a href="<?php echo SITE_URL; ?>/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn btn-outline-primary rounded-pill btn-sm px-4">Login to Review</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>  <!-- /.reveal (reviews list) -->
</div>  <!-- /.mt-5 (reviews section wrapper) -->

    <!-- Related Products -->
    <?php if (!empty($relatedProducts)): ?>
    <div class="mt-5 reveal">
        <h3 class="fw-bold mb-4">You May Also <span>Like</span></h3>
        <div class="row g-4">
            <?php foreach ($relatedProducts as $relProduct): 
                $relFinalPrice = getFinalPrice($relProduct);
            ?>
            <div class="col-lg-3 col-md-6">
                <div class="product-card">
                    <div class="product-image">
                        <a href="<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $relProduct['id']; ?>">
                            <?php if ($relProduct['image']): ?>
                                <img src="<?php echo SITE_URL; ?>/uploads/products/<?php echo $relProduct['image']; ?>" alt="<?php echo escapeOutput($relProduct['name']); ?>" loading="lazy">
                            <?php else: ?>
                                <img src="<?php echo SITE_URL; ?>/assets/images/placeholder.jpg" alt="<?php echo escapeOutput($relProduct['name']); ?>">
                            <?php endif; ?>
                        </a>
                    </div>
                    <div class="product-body text-center">
                        <h6 class="product-title fw-bold mb-2">
                            <a href="<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $relProduct['id']; ?>" class="text-white text-decoration-none">
                                <?php echo truncateText(escapeOutput($relProduct['name']), 40); ?>
                            </a>
                        </h6>
                        <div class="h5 fw-bold text-primary-purple mb-0"><?php echo formatPrice($relFinalPrice); ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function changeImage(src, el) {
    document.getElementById('mainImage').src = src;
    document.querySelectorAll('.thumb-wrapper').forEach(t => {
        t.classList.remove('active', 'border-primary');
    });
    el.classList.add('active', 'border-primary');
}

// Update add to cart quantity
document.getElementById('quantity')?.addEventListener('change', function() {
    var qty = this.value;
    document.querySelector('.btn-add-to-cart').setAttribute('data-quantity', qty);
});

// Advanced Scroll Reveal
document.addEventListener('DOMContentLoaded', function() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
});
</script>

<?php include 'includes/footer.php'; ?>
