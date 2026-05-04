<?php
require_once '../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('../admin/login.php');
}

$admin = getCurrentAdmin($conn);
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($orderId <= 0) {
    redirect('orders.php');
}

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $newStatus = sanitizeInput($_POST['order_status']);
    $paymentStatus = sanitizeInput($_POST['payment_status']);
    
    // Fetch old status before update for email comparison
    $stmt = $conn->prepare("SELECT email, full_name, order_number, order_status FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $orderInfo = $stmt->fetch();

    try {
        $stmt = $conn->prepare("UPDATE orders SET order_status = ?, payment_status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $paymentStatus, $orderId]);
        
        // Send email notification if status actually changed
        if ($orderInfo && $orderInfo['order_status'] !== $newStatus) {
            $phpBin = '/Applications/XAMPP/xamppfiles/bin/php';
            $script = realpath(__DIR__ . '/../cli/send_email.php');
            $args = escapeshellarg('order_status')
                  . ' ' . escapeshellarg($orderInfo['email'])
                  . ' ' . escapeshellarg($orderInfo['full_name'])
                  . ' ' . escapeshellarg($orderInfo['order_number'])
                  . ' ' . escapeshellarg($newStatus);
            
            $cmd = "{$phpBin} \"{$script}\" {$args} > /dev/null 2>&1 &";
            exec($cmd);
        }
        
        setFlashMessage('success', 'Order status updated successfully.');
        // Refresh to show updated data
        header("Location: order-detail.php?id=$orderId");
        exit();
    } catch (PDOException $e) {
        setFlashMessage('danger', 'Error updating order: ' . $e->getMessage());
    }
}

// Get Order Details
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    setFlashMessage('danger', 'Order not found.');
    redirect('orders.php');
}

// Get Order Items
$stmt = $conn->prepare("
    SELECT oi.*, p.image_path as image
    FROM order_items oi
    LEFT JOIN (
        SELECT product_id, MAX(image_path) as image_path 
        FROM product_images 
        WHERE is_primary = 1 
        GROUP BY product_id
    ) p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

$pageTitle = 'Order Details #' . $order['order_number'];
include 'includes/header.php';
?>

<style>
    .step-progress {
        display: flex;
        justify-content: space-between;
        margin-bottom: 2rem;
        position: relative;
    }
    .step-progress::before {
        content: '';
        position: absolute;
        top: 15px;
        left: 0;
        width: 100%;
        height: 2px;
        background: var(--border-color);
        z-index: 0;
    }
    .step-item {
        position: relative;
        z-index: 1;
        text-align: center;
        background: var(--card-bg);
        padding: 0 10px;
    }
    .step-icon {
        width: 32px;
        height: 32px;
        background: var(--dark-bg);
        color: var(--text-muted);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 5px;
        border: 1px solid var(--border-color);
    }
    .step-item.active .step-icon {
        background: var(--primary-purple);
        color: white;
        border-color: var(--primary-purple);
        box-shadow: var(--purple-glow);
    }
    .step-item.completed .step-icon {
        background: #06d6a0;
        color: white;
        border-color: #06d6a0;
    }
</style>

<div class="row">
    <div class="col-lg-8">
        <!-- Order Items -->
        <div class="card mb-4 border-0">
            <div class="card-header py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-box-seam me-2"></i>Ordered Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($item['image']): ?>
                                            <img src="<?php echo SITE_URL; ?>/uploads/products/<?php echo $item['image']; ?>" class="rounded me-2 border border-purple" style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="rounded me-2 bg-dark d-flex align-items-center justify-content-center border border-purple" style="width: 50px; height: 50px;">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <h6 class="mb-0 text-white"><?php echo escapeOutput($item['product_name']); ?></h6>
                                            <small class="text-muted">ID: <?php echo $item['product_id']; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo formatPrice($item['price']); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td class="text-end"><?php echo formatPrice($item['total']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="border-top">
                            <tr>
                                <td colspan="3" class="text-end">Subtotal:</td>
                                <td class="text-end"><?php echo formatPrice($order['subtotal']); ?></td>
                            </tr>
                            <?php if ($order['discount'] > 0): ?>
                            <tr>
                                <td colspan="3" class="text-end text-success">Discount:</td>
                                <td class="text-end text-success">-<?php echo formatPrice($order['discount']); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td colspan="3" class="text-end">Tax:</td>
                                <td class="text-end"><?php echo formatPrice($order['tax']); ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end">Shipping:</td>
                                <td class="text-end"><?php echo formatPrice($order['total'] - $order['subtotal'] + $order['discount'] - $order['tax']); ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end fw-bold fs-5">Total:</td>
                                <td class="text-end fw-bold fs-5 text-primary"><?php echo formatPrice($order['total']); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Update Status -->
        <div class="card mb-4 border-0">
            <div class="card-header py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-arrow-repeat me-2"></i>Update Status</h5>
            </div>
            <div class="card-body">
                <form method="POST" id="orderStatusForm">
                    <div class="mb-3">
                        <label class="form-label">Order Status</label>
                        <select name="order_status" id="order_status" class="form-select">
                            <?php 
                            $statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
                            foreach ($statuses as $s): 
                            ?>
                            <option value="<?php echo $s; ?>" <?php echo $order['order_status'] == $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Status</label>
                        <select name="payment_status" id="payment_status" class="form-select">
                            <?php 
                            $pStatuses = ['Pending', 'Completed', 'Failed'];
                            foreach ($pStatuses as $s): 
                            ?>
                            <option value="<?php echo $s; ?>" <?php echo $order['payment_status'] == $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="btn btn-primary w-100">Update Order</button>
                </form>
            </div>
        </div>

        <?php if ($order['payment_method'] == 'Online' && $order['razorpay_order_id']): ?>
        <!-- Razorpay Details -->
        <div class="card mb-4 border-0 border-start border-4 border-primary">
            <div class="card-header py-3">
                <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-credit-card me-2"></i>Razorpay Payment</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <label class="small text-muted text-uppercase d-block">Razorpay Order ID</label>
                    <code class="text-dark fw-bold"><?php echo $order['razorpay_order_id']; ?></code>
                </div>
                <?php if ($order['razorpay_payment_id']): ?>
                <div class="mb-0">
                    <label class="small text-muted text-uppercase d-block">Razorpay Payment ID</label>
                    <code class="text-success fw-bold"><?php echo $order['razorpay_payment_id']; ?></code>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Customer Info -->
        <div class="card mb-4 border-0">
            <div class="card-header py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-person me-2"></i>Customer Info</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="small text-muted text-uppercase">Contact</h6>
                    <p class="mb-0 fw-bold"><?php echo escapeOutput($order['full_name']); ?></p>
                    <p class="mb-0"><a href="mailto:<?php echo escapeOutput($order['email']); ?>" class="text-decoration-none"><?php echo escapeOutput($order['email']); ?></a></p>
                    <p class="mb-0"><?php echo escapeOutput($order['phone']); ?></p>
                </div>
                <hr>
                <div class="mb-3">
                    <h6 class="small text-muted text-uppercase">Shipping Address</h6>
                    <p class="mb-0"><?php echo nl2br(escapeOutput($order['shipping_address'])); ?></p>
                </div>
                <?php if ($order['order_notes']): ?>
                <hr>
                <div>
                    <h6 class="small text-muted text-uppercase">Notes</h6>
                    <p class="mb-0 fst-italic"><?php echo nl2br(escapeOutput($order['order_notes'])); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
$(document).ready(function () {
    $('#orderStatusForm').validate({
        rules: {
            order_status:   { required: true },
            payment_status: { required: true }
        },
        messages: {
            order_status:   { required: 'Please select an order status' },
            payment_status: { required: 'Please select a payment status' }
        },
        errorPlacement: function (error, element) {
            error.insertAfter(element);
        },
        invalidHandler: function (e, validator) {
            $(validator.currentForm).addClass('form-shake');
            setTimeout(function () { $(validator.currentForm).removeClass('form-shake'); }, 500);
        }
    });
});
</script>
