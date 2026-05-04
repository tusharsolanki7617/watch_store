<?php
$pageTitle = 'Checkout';
include 'includes/header.php';
require_once 'includes/mailer.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    setFlashMessage('info', 'Please login to complete your purchase');
    redirect(SITE_URL . '/login.php');
}

// Get cart items
$cartItems = getCartItems($conn);
$isRazorpayFlow = isset($_GET['razorpay']) && isset($_SESSION['pending_razorpay']);

if (empty($cartItems) && !$isRazorpayFlow) {
    redirect(SITE_URL . '/cart.php');
}

// Calculate totals
$subtotal = 0;
foreach ($cartItems as $item) {
    if ($item['discount_price'] > 0) {
        $subtotal += $item['discount_price'] * $item['quantity'];
    } else {
        $subtotal += $item['price'] * $item['quantity'];
    }
}

// Discount from coupon
$discount = isset($_SESSION['discount_amount']) ? $_SESSION['discount_amount'] : 0;
$couponCode = isset($_SESSION['coupon_code']) ? $_SESSION['coupon_code'] : null;

// Tax (Example: 18% GST)
$tax = ($subtotal - $discount) * 0.18;

// Shipping (Free if over 5000)
$minFreeShipping = getSiteSetting($conn, 'free_shipping_min');
$shipping = ($subtotal - $discount) >= $minFreeShipping ? 0 : 150;

$total = ($subtotal - $discount) + $tax + $shipping;

$user = getCurrentUser($conn);

// Handle Checkout Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('danger', 'Invalid request');
    } else {
        $fullName = sanitizeInput($_POST['full_name']);
        $email = sanitizeInput($_POST['email']);
        $phone = sanitizeInput($_POST['phone']);
        $address = sanitizeInput($_POST['address']);
        $city = sanitizeInput($_POST['city']);
        $state = sanitizeInput($_POST['state']);
        $zip = sanitizeInput($_POST['zip']);
        $paymentMethod = sanitizeInput($_POST['payment_method']); // COD or Online
        
        if (empty($fullName) || empty($email) || empty($phone) || empty($address) || empty($city) || empty($zip)) {
            setFlashMessage('danger', 'Please fill in all required fields');
        } else {
            try {
                $conn->beginTransaction();
                
                // 1. Create Order
                $orderNumber = 'ORD-' . strtoupper(uniqid());
                $fullAddress = "$address, $city, $state - $zip";
                
                $stmt = $conn->prepare("INSERT INTO orders (
                    user_id, order_number, full_name, email, phone, 
                    shipping_address, billing_address, city, state, zip_code,
                    subtotal, discount, tax, total, payment_method, 
                    payment_status, order_status, coupon_code
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $paymentStatus = 'Pending'; // All payments start as pending
                
                $stmt->execute([
                    $user['id'], $orderNumber, $fullName, $email, $phone,
                    $fullAddress, $fullAddress, $city, $state, $zip,
                    $subtotal, $discount, $tax, $total, $paymentMethod,
                    $paymentStatus, 'Pending', $couponCode
                ]);
                
                $orderId = $conn->lastInsertId();
                
                // 2. Add Order Items
                $stmtItem = $conn->prepare("INSERT INTO order_items (
                    order_id, product_id, product_name, quantity, price, total
                ) VALUES (?, ?, ?, ?, ?, ?)");
                
                foreach ($cartItems as $item) {
                    $price = $item['discount_price'] > 0 ? $item['discount_price'] : $item['price'];
                    $itemTotal = $price * $item['quantity'];
                    
                    $stmtItem->execute([
                        $orderId, $item['product_id'], $item['name'], 
                        $item['quantity'], $price, $itemTotal
                    ]);
                    
                    // Update Stock
                    $stmtStock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                    $stmtStock->execute([$item['quantity'], $item['product_id']]);
                }
                
                // 3. Clear Cart
                $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
                $stmt->execute([$user['id']]);
                unset($_SESSION['cart']); // Clear session cart too if synced
                unset($_SESSION['coupon_code']);
                unset($_SESSION['discount_amount']);
                
                $conn->commit();
                
                // --- Razorpay Integration ---
                if ($paymentMethod == 'Online') {
                    // Create Razorpay Order via REST API
                    $api_url    = "https://api.razorpay.com/v1/orders";
                    $key_id     = RAZORPAY_KEY_ID;
                    $key_secret = RAZORPAY_KEY_SECRET;

                    $data = json_encode([
                        'amount'          => (int) round($total * 100), // In paise
                        'currency'        => 'INR',
                        'receipt'         => $orderNumber,
                        'payment_capture' => 1
                    ]);

                    $ch = curl_init($api_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    curl_setopt($ch, CURLOPT_USERPWD, "$key_id:$key_secret");
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($data)
                    ]);
                    // Allow self-signed certs on localhost dev environment
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

                    $response  = curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $curl_err  = curl_error($ch);
                    curl_close($ch);

                    if ($http_code == 200) {
                        $razorpayOrder   = json_decode($response, true);
                        $razorpayOrderId = $razorpayOrder['id'];

                        // Update Local Order with Razorpay Order ID
                        $stmt = $conn->prepare("UPDATE orders SET razorpay_order_id = ?, payment_status = 'Pending' WHERE id = ?");
                        $stmt->execute([$razorpayOrderId, $orderId]);

                        // Store info in session for the frontend popup
                        $_SESSION['pending_razorpay'] = [
                            'order_id'  => $razorpayOrderId,
                            'amount'    => $total,
                            'name'      => $fullName,
                            'email'     => $email,
                            'phone'     => $phone,
                            'order_num' => $orderNumber
                        ];

                        // PRG: redirect to self to open the Razorpay popup
                        redirect($_SERVER['PHP_SELF'] . "?razorpay=1");
                    } else {
                        $errMsg = $curl_err ?: $response;
                        throw new Exception("Razorpay API error (HTTP $http_code): " . $errMsg);
                    }
                }
                // --- End Razorpay ---
                
                // 4. Send Confirmation Email
                $orderDetails = [
                    'items' => $cartItems,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'total' => $total,
                    'payment_method' => $paymentMethod,
                    'shipping_address' => $fullAddress
                ];
                
                if ($paymentMethod == 'COD') {
                    sendOrderConfirmationEmail($email, $fullName, $orderNumber, $orderDetails);
                    redirect(SITE_URL . "/order-success.php?id=$orderNumber");
                }
                
            } catch (Exception $e) {
                $conn->rollBack();
                setFlashMessage('danger', 'Order failed: ' . $e->getMessage());
            }
        }
    }
}
?>

<div class="container my-5 fade-in-up">
    <h2 class="mb-4 text-white">Secure <span class="text-primary-purple">Checkout</span></h2>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="glass-card shadow-lg mb-4">
                <div class="p-4 border-bottom border-glass">
                    <h5 class="mb-0 text-white fw-bold">Shipping Information</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" id="checkoutForm">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label text-muted small text-uppercase fw-bold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="full_name" class="form-control bg-dark border-glass text-white" value="<?php echo escapeOutput($user['full_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase fw-bold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control bg-dark border-glass text-white" value="<?php echo escapeOutput($user['email']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase fw-bold">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" name="phone" class="form-control bg-dark border-glass text-white" value="<?php echo escapeOutput($user['phone'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted small text-uppercase fw-bold">Address <span class="text-danger">*</span></label>
                            <textarea name="address" class="form-control bg-dark border-glass text-white" rows="2" required></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted small text-uppercase fw-bold">City <span class="text-danger">*</span></label>
                                <input type="text" name="city" class="form-control bg-dark border-glass text-white" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small text-uppercase fw-bold">State <span class="text-danger">*</span></label>
                                <input type="text" name="state" class="form-control bg-dark border-glass text-white" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small text-uppercase fw-bold">Zip Code <span class="text-danger">*</span></label>
                                <input type="text" name="zip" class="form-control bg-dark border-glass text-white" required>
                            </div>
                        </div>
                        
                        <h5 class="mt-4 mb-3 text-white fw-bold">Select Payment Protocol</h5>
                        <div class="payment-methods-grid mb-4">
                            <!-- COD Option -->
                            <div class="payment-card mb-3 p-3 rounded" id="card-cod" onclick="selectPayment('cod')">
                                <div class="d-flex align-items-center">
                                    <div class="payment-radio-custom me-3" id="radio-cod-dot"></div>
                                    <input type="radio" name="payment_method" id="cod" value="COD" checked class="d-none">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 text-white">Cash on Delivery (COD)</h6>
                                        <small class="text-white-50">Settlement upon physical delivery</small>
                                    </div>
                                    <i class="bi bi-truck text-muted fs-4"></i>
                                </div>
                            </div>
                            
                            <!-- Online Option -->
                            <div class="payment-card p-3 rounded" id="card-online" onclick="selectPayment('online')">
                                <div class="d-flex align-items-center">
                                    <div class="payment-radio-custom me-3" id="radio-online-dot"></div>
                                    <input type="radio" name="payment_method" id="online" value="Online" class="d-none">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 text-white">Razorpay Secure Protocol</h6>
                                        <small class="text-white-50">Cards, UPI, Netbanking & Wallets</small>
                                    </div>
                                    <img src="https://razorpay.com/assets/razorpay-glyph.svg" alt="Razorpay" style="height: 22px; filter: brightness(0) invert(1);">
                                </div>
                            </div>
                        </div>

                        <style>
                            .payment-card {
                                border: 1px solid rgba(157, 78, 221, 0.2);
                                background: rgba(255, 255, 255, 0.03);
                                cursor: pointer;
                                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                                position: relative;
                                overflow: hidden;
                            }
                            .payment-card:hover {
                                background: rgba(157, 78, 221, 0.08);
                                border-color: rgba(157, 78, 221, 0.5);
                            }
                            .payment-card.active {
                                border-color: var(--primary-purple);
                                background: rgba(157, 78, 221, 0.1);
                                box-shadow: 0 0 15px rgba(157, 78, 221, 0.2);
                            }
                            .payment-radio-custom {
                                width: 22px;
                                height: 22px;
                                border: 2px solid rgba(255, 255, 255, 0.3);
                                border-radius: 50%;
                                position: relative;
                                transition: all 0.3s ease;
                            }
                            .payment-card.active .payment-radio-custom {
                                border-color: var(--primary-purple);
                            }
                            .payment-card.active .payment-radio-custom::after {
                                content: '';
                                width: 12px;
                                height: 12px;
                                background: var(--primary-purple);
                                border-radius: 50%;
                                position: absolute;
                                top: 50%;
                                left: 50%;
                                transform: translate(-50%, -50%);
                                box-shadow: 0 0 10px var(--primary-purple);
                            }
                        </style>

                        <script>
                        function selectPayment(id) {
                            document.querySelectorAll('.payment-card').forEach(c => c.classList.remove('active'));
                            document.getElementById('card-' + id).classList.add('active');
                            document.getElementById(id).checked = true;
                        }
                        // Initialize first card
                        document.addEventListener('DOMContentLoaded', () => selectPayment('cod'));
                        </script>
                        
                        <button type="submit" name="place_order" class="btn btn-primary btn-lg w-100 rounded-pill mt-4">
                            <i class="bi bi-box-seam me-2"></i> Confirm & Place Order
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="glass-card shadow-lg mb-4">
                <div class="p-4 border-bottom border-glass">
                    <h5 class="mb-0 text-white fw-bold">Order Summary</h5>
                </div>
                <div class="card-body p-4">
                    <div class="checkout-items mb-3" style="max-height: 300px; overflow-y: auto;">
                        <?php foreach ($cartItems as $item): 
                            $price = $item['discount_price'] > 0 ? $item['discount_price'] : $item['price'];
                        ?>
                        <div class="d-flex align-items-center mb-3">
                            <img src="<?php echo SITE_URL; ?>/uploads/products/<?php echo $item['image']; ?>" 
                                 alt="<?php echo escapeOutput($item['name']); ?>" 
                                 class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                            <div class="ms-3 flex-grow-1">
                                <h6 class="mb-0 small"><?php echo truncateText(escapeOutput($item['name']), 30); ?></h6>
                                <small class="text-muted"><?php echo $item['quantity']; ?> x <?php echo formatPrice($price); ?></small>
                            </div>
                            <div class="text-end">
                                <span class="small fw-bold"><?php echo formatPrice($price * $item['quantity']); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span><?php echo formatPrice($subtotal); ?></span>
                    </div>
                    <?php if ($discount > 0): ?>
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>Discount (<?php echo $couponCode; ?>)</span>
                        <span>-<?php echo formatPrice($discount); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax (18%)</span>
                        <span><?php echo formatPrice($tax); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Shipping</span>
                        <span><?php echo $shipping > 0 ? formatPrice($shipping) : '<span class="text-success">Free</span>'; ?></span>
                    </div>
                    <hr class="border-glass">
                    <div class="d-flex justify-content-between mb-0">
                        <h5 class="mb-0 text-white">Total</h5>
                        <h4 class="text-primary-purple mb-0 fw-bold"><?php echo formatPrice($total); ?></h4>
                    </div>
                </div>
            </div>
            
            <div class="glass-card shadow-lg mt-4 border-glass">
                <div class="card-body py-3">
                    <p class="small text-muted mb-0"><i class="bi bi-shield-lock text-success-green me-2"></i> SSL Secured Payment Protocol</p>
                </div>
            </div>

        </div>
    </div>
</div>

<?php
// ── Inject Razorpay checkout popup via $customJS (rendered inside </body> by footer.php) ──
if (isset($_GET['razorpay']) && isset($_SESSION['pending_razorpay'])) {
    $rzp = $_SESSION['pending_razorpay'];
    ob_start();
?>
<!-- Hidden form: securely POST Razorpay tokens to verify-payment.php -->
<form id="rzp-verify-form" method="POST" action="verify-payment.php" style="display:none;">
    <input type="hidden" name="razorpay_order_id"  id="rzp_oid">
    <input type="hidden" name="razorpay_payment_id" id="rzp_pid">
    <input type="hidden" name="razorpay_signature"  id="rzp_sig">
</form>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
(function openRazorpay() {
    // Wait until Razorpay SDK is ready
    if (typeof Razorpay === 'undefined') {
        return setTimeout(openRazorpay, 100);
    }
    var options = {
        key         : '<?php echo RAZORPAY_KEY_ID; ?>',
        amount      : <?php echo (int) round($rzp['amount'] * 100); ?>,
        currency    : 'INR',
        name        : '<?php echo addslashes(htmlspecialchars(SITE_NAME)); ?>',
        description : 'Premium Timepiece Acquisition',
        order_id    : '<?php echo $rzp['order_id']; ?>',
        handler     : function(response) {
            document.getElementById('rzp_oid').value = response.razorpay_order_id;
            document.getElementById('rzp_pid').value = response.razorpay_payment_id;
            document.getElementById('rzp_sig').value = response.razorpay_signature;
            document.getElementById('rzp-verify-form').submit();
        },
        prefill : {
            name    : '<?php echo addslashes(htmlspecialchars($rzp['name'])); ?>',
            email   : '<?php echo addslashes(htmlspecialchars($rzp['email'])); ?>',
            contact : '<?php echo addslashes($rzp['phone']); ?>'
        },
        theme  : { color: '#9d4edd' },
        modal  : {
            ondismiss: function() {
                window.location.href = 'checkout.php?error=payment_cancelled';
            }
        }
    };
    var rzp1 = new Razorpay(options);
    rzp1.open();
})();
</script>
<?php
    $customJS = ob_get_clean();
}
?>

<?php include 'includes/footer.php'; ?>

