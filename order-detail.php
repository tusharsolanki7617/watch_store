<?php
$pageTitle = 'Order Details';
include 'includes/header.php';

if (!isLoggedIn()) {
    redirect(SITE_URL . '/login.php');
}

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user = getCurrentUser($conn);

if ($orderId <= 0) {
    redirect('my-orders.php');
}

// Get Order Info (Ensure it belongs to user)
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $user['id']]);
$order = $stmt->fetch();

if (!$order) {
    setFlashMessage('danger', 'Order not found.');
    redirect('my-orders.php');
}

// Get Order Items
$stmt = $conn->prepare("
    SELECT oi.*, p.image 
    FROM order_items oi
    LEFT JOIN (
        SELECT product_id, MAX(image_path) as image 
        FROM product_images 
        WHERE is_primary = 1 
        GROUP BY product_id
    ) p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();
?>

<div class="container my-5">
    <div class="mb-4 reveal">
        <a href="my-orders.php" class="text-decoration-none text-primary-purple fw-bold"><i class="bi bi-arrow-left"></i> Return to Stream</a>
    </div>

    <div class="row">
        <div class="col-lg-8 reveal">
            <div class="glass-card shadow-lg mb-4 border-glass">
                <div class="card-header bg-deep-dark border-glass py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white fw-bold">Nexus Manifest <span class="text-primary-purple">#<?php echo escapeOutput($order['order_number']); ?></span></h5>
                    <span class="badge rounded-pill <?php 
                        echo $order['order_status'] == 'Delivered' ? 'glass-success' : 
                            ($order['order_status'] == 'Cancelled' ? 'glass-danger' : 'glass-warning'); 
                        ?> ps-3 pe-3 py-2">
                        <?php echo $order['order_status']; ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle text-white border-0">
                            <thead>
                                <tr class="text-muted small text-uppercase">
                                    <th class="border-glass">Timepiece</th>
                                    <th class="border-glass">Price</th>
                                    <th class="border-glass">Qty</th>
                                    <th class="text-end border-glass">Nexus Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($item['image']): ?>
                                                <div class="img-zoom-container rounded border border-glass">
                                                    <img src="<?php echo SITE_URL; ?>/uploads/products/<?php echo $item['image']; ?>" alt="" style="width: 50px; height: 50px; object-fit: cover;">
                                                </div>
                                            <?php else: ?>
                                                <div class="rounded bg-dark d-flex align-items-center justify-content-center border border-glass" style="width: 50px; height: 50px;">
                                                    <i class="bi bi-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            <span class="ms-3 fw-bold"><?php echo escapeOutput($item['product_name']); ?></span>
                                        </div>
                                    </td>
                                    <td class="text-muted"><?php echo formatPrice($item['price']); ?></td>
                                    <td class="text-muted"><?php echo $item['quantity']; ?></td>
                                    <td class="text-end fw-bold text-white"><?php echo formatPrice($item['total']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="border-glass">
                                <tr class="border-0">
                                    <td colspan="3" class="text-end text-muted border-0">Nexus Subtotal:</td>
                                    <td class="text-end text-white border-0"><?php echo formatPrice($order['subtotal']); ?></td>
                                </tr>
                                <?php if ($order['discount'] > 0): ?>
                                <tr class="border-0">
                                    <td colspan="3" class="text-end text-success-green border-0">Nexus Discount:</td>
                                    <td class="text-end text-success-green border-0">-<?php echo formatPrice($order['discount']); ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr class="border-0">
                                    <td colspan="3" class="text-end text-muted border-0">Nexus Tax:</td>
                                    <td class="text-end text-white border-0"><?php echo formatPrice($order['tax']); ?></td>
                                </tr>
                                <tr class="border-0">
                                    <td colspan="3" class="text-end text-muted border-0">Quantum Shipping:</td>
                                    <td class="text-end text-white border-0"><?php echo formatPrice($order['total'] - $order['subtotal'] + $order['discount'] - $order['tax']); ?></td>
                                </tr>
                                <tr class="fw-bold fs-5 border-0">
                                    <td colspan="3" class="text-end border-0 text-white">Total Nexus Value:</td>
                                    <td class="text-end text-primary-purple border-0 shadow-glow" style="text-shadow: 0 0 10px rgba(157, 78, 221, 0.5);"><?php echo formatPrice($order['total']); ?></td>
                                </tr>

                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 reveal">
            <div class="glass-card shadow-lg mb-4 border-glass">
                <div class="card-header bg-deep-dark border-glass py-3">
                    <h5 class="mb-0 text-white fw-bold">Logistics & Billing</h5>
                </div>
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Destination Nexus</h6>
                    <p class="mb-3 text-white">
                        <strong class="text-primary-purple"><?php echo escapeOutput($order['full_name']); ?></strong><br>
                        <span class="text-muted"><?php echo nl2br(escapeOutput($order['shipping_address'])); ?></span><br>
                        <span class="text-muted">Comm: <?php echo escapeOutput($order['phone']); ?></span>
                    </p>
                    
                    <h6 class="text-muted text-uppercase small fw-bold">Credit Protocol</h6>
                    <p class="mb-1 text-white"><?php echo $order['payment_method']; ?></p>
                    <p class="mb-2 text-muted">Protocol Status: <span class="badge rounded-pill glass-secondary text-white"><?php echo $order['payment_status']; ?></span></p>
                    
                    <?php if ($order['payment_method'] == 'Online' && $order['razorpay_payment_id']): ?>
                    <div class="mt-3 p-2 rounded bg-deep-dark border border-glass">
                        <small class="text-muted d-block text-uppercase" style="font-size: 0.6rem;">Nexus Transaction ID</small>
                        <code class="text-primary-purple small fw-bold"><?php echo $order['razorpay_payment_id']; ?></code>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="glass-card shadow-lg border-glass">
                 <div class="card-body">
                     <h6 class="mb-3 text-white fw-bold">Need Operator Assistance?</h6>
                     <a href="contact.php" class="btn btn-outline-primary w-100 rounded-pill">Contact Nexus Support</a>
                 </div>
            </div>
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
