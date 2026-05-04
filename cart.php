<?php
$pageTitle = 'Shopping Cart';
include 'includes/header.php';

$cartItems = getCartItems($conn);
$subtotal = 0;
$discount = 0;
$tax = 0;

// Calculate subtotal
foreach ($cartItems as $item) {
    $price = $item['discount_price'] ?? $item['price'];
    $subtotal += $price * $item['quantity'];
}

// Apply coupon if exists
if (isset($_SESSION['coupon'])) {
    $coupon = $_SESSION['coupon'];
    if ($coupon['discount_type'] === 'percentage') {
        $discount = ($subtotal * $coupon['discount_value']) / 100;
        if (isset($coupon['max_discount']) && $discount > $coupon['max_discount']) {
            $discount = $coupon['max_discount'];
        }
    } else {
        $discount = $coupon['discount_value'];
    }
}

$total = $subtotal - $discount + $tax;
?>

<div class="container my-5 fade-in-up">
    <h2 class="mb-4 text-white"><i class="bi bi-cart3 text-primary-purple"></i> Shopping <span class="text-primary-purple">Cart</span></h2>
    
    <?php if (!empty($cartItems)): ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="glass-card shadow-lg mb-4">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr class="border-glass">
                                    <th class="text-muted small text-uppercase">Product</th>
                                    <th class="text-muted small text-uppercase">Price</th>
                                    <th class="text-muted small text-uppercase">Quantity</th>
                                    <th class="text-muted small text-uppercase">Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cartItems as $item): 
                                    $price = $item['discount_price'] ?? $item['price'];
                                    $itemTotal = $price * $item['quantity'];
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($item['image']): ?>
                                            <img src="<?php echo SITE_URL; ?>/uploads/products/<?php echo $item['image']; ?>" 
                                                 style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;" class="me-3">
                                            <?php endif; ?>
                                            <div>
                                                <strong><?php echo escapeOutput($item['name']); ?></strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo formatPrice($price); ?></td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm cart-quantity" 
                                               style="width: 80px;" value="<?php echo $item['quantity']; ?>" 
                                               min="1" max="<?php echo $item['stock']; ?>" 
                                               data-cart-id="<?php echo $item['id']; ?>">
                                    </td>
                                    <td><strong><?php echo formatPrice($itemTotal); ?></strong></td>
                                    <td>
                                        <button class="btn btn-sm btn-danger btn-remove-cart" data-cart-id="<?php echo $item['id']; ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="glass-card shadow-lg">
                <div class="p-4 border-bottom border-glass">
                    <h5 class="mb-0 text-white fw-bold">Order Summary</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <strong><?php echo formatPrice($subtotal); ?></strong>
                    </div>
                    <?php if ($discount > 0): ?>
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>Discount:</span>
                        <strong>-<?php echo formatPrice($discount); ?></strong>
                    </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Tax:</span>
                        <strong><?php echo formatPrice($tax); ?></strong>
                    </div>
                    <hr class="border-glass">
                    <div class="d-flex justify-content-between mb-4">
                        <h5 class="text-white">Total:</h5>
                        <h4 class="text-primary-purple fw-bold"><?php echo formatPrice($total); ?></h4>
                    </div>
                    
                    <!-- Coupon -->
                    <div class="mb-3">
                        <label class="form-label small">Have a coupon?</label>
                        <div class="input-group">
                            <input type="text" id="couponCode" class="form-control bg-dark border border-white text-white" placeholder="Enter code">
                            <button class="btn btn-outline-primary" id="applyCouponBtn">Apply</button>
                        </div>
                    </div>
                    
                    <a href="<?php echo SITE_URL; ?>/checkout.php" class="btn btn-primary w-100 btn-lg rounded-pill mb-3">
                        <i class="bi bi-credit-card me-2"></i> Proceed to Checkout
                    </a>
                    <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-outline-primary w-100 rounded-pill">
                        <i class="bi bi-arrow-left me-2"></i> Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="glass-card p-5 text-center reveal shadow-glow">
        <i class="bi bi-cart-x display-1 text-muted mb-3 d-block"></i>
        <h4 class="text-white">Your cart is empty</h4>
        <p class="text-muted">Explore our collection to add your first masterpiece.</p>
        <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary rounded-pill mt-3">Explore Nexus</a>
    </div>

    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
