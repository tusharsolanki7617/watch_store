<?php
$pageTitle = 'My Orders';
include 'includes/header.php';

if (!isLoggedIn()) {
    redirect(SITE_URL . '/login.php');
}

$user = getCurrentUser($conn);
$userId = $user['id'];

// Get Orders with Item counts (Refined to avoid GROUP BY issues on some MySQL versions)
$stmt = $conn->prepare("
    SELECT *, 
    (SELECT SUM(quantity) FROM order_items WHERE order_id = orders.id) as item_count 
    FROM orders 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <h4 class="mb-4 text-white fw-bold reveal"><i class="bi bi-bag-check text-primary-purple me-2"></i> Order <span class="text-primary-purple">Stream</span></h4>
            
            <?php if (empty($orders)): ?>
                <div class="glass-card p-5 text-center reveal">
                    <i class="bi bi-cart-x display-1 text-muted mb-3 d-block"></i>
                    <p class="text-muted">You haven't placed any orders yet.</p>
                    <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary rounded-pill">Start Your Journey</a>
                </div>
            <?php else: ?>
                <div class="glass-card shadow-lg border-glass overflow-hidden reveal">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-white border-0">
                            <thead class="bg-deep-dark border-glass">
                                <tr class="text-muted small text-uppercase">
                                    <th class="ps-4 border-0">Order ID</th>
                                    <th class="border-0">Date</th>
                                    <th class="border-0">Status</th>
                                    <th class="border-0">Total</th>
                                    <th class="border-0">Items</th>
                                    <th class="text-end pe-4 border-0">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-bold text-primary-purple">#<?php echo $order['order_number']; ?></span>
                                    </td>
                                    <td class="text-muted small"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <?php 
                                        $statusClass = 'glass-secondary';
                                        switch($order['order_status']) {
                                            case 'Pending': $statusClass = 'glass-warning'; break;
                                            case 'Processing': $statusClass = 'glass-info'; break;
                                            case 'Shipped': $statusClass = 'glass-primary'; break;
                                            case 'Delivered': $statusClass = 'glass-success'; break;
                                            case 'Cancelled': $statusClass = 'glass-danger'; break;
                                        }
                                        ?>
                                        <span class="badge rounded-pill <?php echo $statusClass; ?> px-3"><?php echo $order['order_status']; ?></span>
                                    </td>
                                    <td class="fw-bold text-success-green"><?php echo formatPrice($order['total']); ?></td>
                                    <td class="text-muted"><?php echo $order['item_count']; ?> units</td>
                                    <td class="text-end pe-4">
                                        <a href="<?php echo SITE_URL; ?>/order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">Review</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Scroll Reveal Script -->
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
</script>

<?php include 'includes/footer.php'; ?>
