<?php
$pageTitle = 'Shop';
include 'includes/header.php';

// Get filters from URL
$categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$searchQuery = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$sortBy = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'newest';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * PRODUCTS_PER_PAGE;

// Build query
$where = ["p.is_active = 1"];
$params = [];

if ($categoryFilter > 0) {
    $where[] = "p.category_id = ?";
    $params[] = $categoryFilter;
}

if ($searchQuery) {
    $where[] = "(p.name LIKE ? OR p.brand LIKE ? OR p.description LIKE ?)";
    $searchTerm = "%$searchQuery%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$whereClause = implode(" AND ", $where);

// Sorting
$orderBy = match($sortBy) {
    'price_low' => 'COALESCE(p.discount_price, p.price) ASC',
    'price_high' => 'COALESCE(p.discount_price, p.price) DESC',
    'popularity' => 'p.views DESC',
    default => 'p.created_at DESC'
};

// Get total count
$countStmt = $conn->prepare("SELECT COUNT(*) as total FROM products p WHERE $whereClause");
$countStmt->execute($params);
$totalProducts = $countStmt->fetch()['total'];
$totalPages = ceil($totalProducts / PRODUCTS_PER_PAGE);

// Get products
$stmt = $conn->prepare("
    SELECT p.*, c.name as category_name,
    (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE $whereClause
    ORDER BY $orderBy
    LIMIT " . PRODUCTS_PER_PAGE . " OFFSET $offset
");
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for filter
$categoriesStmt = $conn->query("SELECT * FROM categories WHERE is_active = 1");
$allCategories = $categoriesStmt->fetchAll();
?>

<div class="container my-5 fade-in-up">
    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="glass-card p-4 shadow-lg sticky-top" style="top: 100px;">
                <h5 class="fw-bold mb-4"><i class="bi bi-funnel text-primary-purple me-2"></i>Filters</h5>
                <form method="GET" action="">
                    <!-- Category Filter -->
                    <div class="mb-4">
                        <label class="form-label fw-bold mb-2">Category</label>
                        <div class="cat-filter-list">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="category" value="0" id="catAll" <?php echo $categoryFilter == 0 ? 'checked' : ''; ?>>
                                <label class="form-check-label small" for="catAll">All Collections</label>
                            </div>
                            <?php foreach ($allCategories as $cat): ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="category" value="<?php echo $cat['id']; ?>" id="cat<?php echo $cat['id']; ?>" <?php echo $categoryFilter == $cat['id'] ? 'checked' : ''; ?>>
                                <label class="form-check-label small" for="cat<?php echo $cat['id']; ?>"><?php echo escapeOutput($cat['name']); ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Apply Filters</button>
                    <?php if ($categoryFilter || $searchQuery): ?>
                        <a href="products.php" class="btn btn-link w-100 mt-2 text-muted small text-decoration-none">Clear All</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="col-lg-9">
            <!-- Toolbar -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                <div>
                    <h2 class="fw-bold mb-0 text-white">Shop <span class="text-primary-purple">Watches</span></h2>
                    <p class="text-muted small mb-0">Discover <?php echo $totalProducts; ?> exclusive timepieces</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small d-none d-sm-inline">Sort by:</span>
                    <select class="form-select form-select-sm border-0 bg-light rounded-pill px-3" style="width: 160px;" id="sortSelect" onchange="window.location='?<?php echo http_build_query(array_merge($_GET, ['sort' => ''])); ?>'+this.value">
                        <option value="newest" <?php echo $sortBy == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="price_low" <?php echo $sortBy == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sortBy == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="popularity" <?php echo $sortBy == 'popularity' ? 'selected' : ''; ?>>Most Popular</option>
                    </select>
                </div>
            </div>
            
            <!-- Products -->
            <?php if (!empty($products)): ?>
            <div class="row g-4 reveal-stagger">
                <?php foreach ($products as $product): 
                    $rating = getProductRating($conn, $product['id']);
                    $finalPrice = getFinalPrice($product);
                    $discount = calculateDiscountPercentage($product['price'], $finalPrice);
                ?>
                <div class="col-lg-4 col-md-6 fade-in-up">
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
                                <a href="<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $product['id']; ?>" class="text-white">
                                    <?php echo truncateText(escapeOutput($product['name']), 40); ?>
                                </a>
                            </h6>
                            <div class="product-rating small mb-2 text-warning">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star<?php echo $i <= $rating['average'] ? '-fill' : ''; ?>"></i>
                                <?php endfor; ?>
                                <span class="text-muted ms-1">(<?php echo $rating['count']; ?>)</span>
                            </div>
                            <div class="product-price mb-3">
                                <span class="h5 fw-bold text-white mb-0"><?php echo formatPrice($finalPrice); ?></span>
                                <?php if ($finalPrice < $product['price']): ?>
                                <span class="text-muted text-decoration-line-through small ms-2"><?php echo formatPrice($product['price']); ?></span>
                                <?php endif; ?>
                            </div>
                            <button class="btn btn-primary btn-sm w-100 rounded-pill btn-add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-5">
                <ul class="pagination justify-content-center border-0">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>"><i class="bi bi-chevron-left"></i></a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>"><i class="bi bi-chevron-right"></i></a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="glass-card p-5 text-center mt-4">
                <i class="bi bi-search display-3 text-muted mb-3 d-block"></i>
                <h4 class="fw-bold">No watches found</h4>
                <p class="text-muted">Try adjusting your filters or search query to find what you're looking for.</p>
                <a href="products.php" class="btn btn-primary rounded-pill mt-2">Clear Filters</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
