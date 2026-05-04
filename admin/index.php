<?php
require_once '../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('../admin/login.php');
}

$admin = getCurrentAdmin($conn);

// Get statistics
$stats = [];

$stmt = $conn->query("SELECT COUNT(*) as total FROM products WHERE is_active = 1");
$stats['products'] = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE is_active = 1");
$stats['users'] = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM orders");
$stats['orders'] = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COALESCE(SUM(total), 0) as revenue FROM orders WHERE order_status != 'Cancelled'");
$stats['revenue'] = $stmt->fetch()['revenue'];

// Recent orders
$stmt = $conn->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10");
$recentOrders = $stmt->fetchAll();

$pageTitle = 'Dashboard';
include 'includes/header.php';
?>

<div class="fade-in-up">
    <h2 class="fw-bold mb-4 text-white">System <span class="text-primary-purple">Intelligence</span></h2>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-5">
    <div class="col-md-3 fade-in-up stagger-1">
        <div class="card stat-card border-0 shadow-lg">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-uppercase small fw-bold text-muted mb-0">Collections</h6>
                    <div class="stat-icon bg-dark rounded-circle p-2 border border-purple shadow-sm">
                        <i class="bi bi-box h4 mb-0 text-primary-purple"></i>
                    </div>
                </div>
                <h2 class="fw-bold mb-0 text-white"><?php echo $stats['products']; ?></h2>
                <div class="text-success small mt-2">
                    <i class="bi bi-arrow-up-short"></i> Active Watch Assets
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 fade-in-up stagger-2">
        <div class="card stat-card border-0 shadow-lg">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-uppercase small fw-bold text-muted mb-0">Clients</h6>
                    <div class="stat-icon bg-dark rounded-circle p-2 border border-purple">
                        <i class="bi bi-people h4 mb-0 text-primary-purple"></i>
                    </div>
                </div>
                <h2 class="fw-bold mb-0 text-white"><?php echo $stats['users']; ?></h2>
                <div class="text-primary-purple small mt-2">
                    <i class="bi bi-shield-check"></i> Verified Members
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 fade-in-up stagger-3">
        <div class="card stat-card border-0 shadow-lg">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-uppercase small fw-bold text-muted mb-0">Orders</h6>
                    <div class="stat-icon bg-dark rounded-circle p-2 border border-purple">
                        <i class="bi bi-cart-check h4 mb-0 text-primary-purple"></i>
                    </div>
                </div>
                <h2 class="fw-bold mb-0 text-white"><?php echo $stats['orders']; ?></h2>
                <div class="text-warning small mt-2">
                    <i class="bi bi-clock-history"></i> Recent Transactions
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 fade-in-up stagger-4">
        <div class="card stat-card border-0 shadow-lg">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-uppercase small fw-bold text-muted mb-0">Revenue</h6>
                    <div class="stat-icon bg-dark rounded-circle p-2 border border-purple">
                        <i class="bi bi-wallet2 h4 mb-0 text-primary-purple"></i>
                    </div>
                </div>
                <h2 class="fw-bold mb-0 text-white"><?php echo formatPrice($stats['revenue']); ?></h2>
                <div class="text-success small mt-2 fw-bold">
                    <i class="bi bi-graph-up"></i> Total Earnings
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="card fade-in-up stagger-4 border-0 shadow-lg overflow-hidden">
    <div class="card-header py-4 border-0">
        <h5 class="mb-0 fw-bold small text-uppercase text-primary-purple"><i class="bi bi-clock-history me-2"></i>Live Transaction Feed</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="ps-4 py-3 border-0">Order ID</th>
                        <th class="py-3 border-0">Customer</th>
                        <th class="py-3 border-0">Total</th>
                        <th class="py-3 border-0">Status</th>
                        <th class="pe-4 py-3 border-0">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentOrders)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No recent orders discovered yet.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($recentOrders as $order): ?>
                    <tr class="transition-all" style="cursor: pointer" onclick="window.location.href='order-detail.php?id=<?php echo $order['id']; ?>'">
                        <td class="ps-4 fw-bold text-white">#<?php echo escapeOutput($order['order_number']); ?></td>
                        <td class="small text-muted"><?php echo escapeOutput($order['full_name']); ?></td>
                        <td class="fw-bold text-primary-purple"><?php echo formatPrice($order['total']); ?></td>
                        <td>
                            <?php if ($order['order_status'] == 'Delivered'): ?>
                                <span class="badge bg-success shadow-green rounded-pill px-3">Delivered</span>
                            <?php elseif ($order['order_status'] == 'Pending'): ?>
                                <span class="badge bg-warning rounded-pill px-3">Pending</span>
                            <?php elseif ($order['order_status'] == 'Cancelled'): ?>
                                <span class="badge bg-danger rounded-pill px-3">Cancelled</span>
                            <?php else: ?>
                                <span class="badge bg-secondary rounded-pill px-3"><?php echo $order['order_status']; ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="pe-4 text-muted small"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
