<?php
require_once '../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('../admin/login.php');
}

$admin = getCurrentAdmin($conn);

// Get Orders
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$sql = "SELECT o.*, u.full_name as user_name FROM orders o JOIN users u ON o.user_id = u.id";
$params = [];
$conditions = [];

if ($search) {
    $conditions[] = "(o.order_number LIKE ? OR o.full_name LIKE ? OR o.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status) {
    $conditions[] = "o.order_status = ?";
    $params[] = $status;
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY o.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$pageTitle = 'Manage Orders';
include 'includes/header.php';
?>

<div class="card mb-4 border-0 shadow-lg">
    <div class="card-header py-4 border-0">
        <h5 class="mb-4 fw-bold small text-uppercase text-primary-purple"><i class="bi bi-funnel me-2"></i>Filter Stream</h5>
        <form class="row g-3" method="GET">
            <div class="col-md-4">
                <input type="search" name="search" class="form-control" placeholder="Search by Order ID, Name, Email" value="<?php echo escapeOutput($search); ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <?php 
                    $statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
                    foreach ($statuses as $s): 
                    ?>
                    <option value="<?php echo $s; ?>" <?php echo $status == $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4">No orders found.</td>
                    </tr>
                    <?php endif; ?>
                    
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td class="fw-bold text-white">#<?php echo escapeOutput($order['order_number']); ?></td>
                        <td>
                            <div class="text-white fw-bold"><?php echo escapeOutput($order['full_name']); ?></div>
                            <small class="text-muted"><?php echo escapeOutput($order['email']); ?></small>
                        </td>
                        <td class="text-muted small"><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                        <td class="fw-bold" style="color: var(--primary-purple);"><?php echo formatPrice($order['total']); ?></td>
                        <td class="small text-muted">
                            <?php echo $order['payment_method']; ?><br>
                            <span class="badge bg-<?php echo $order['payment_status'] == 'Completed' ? 'success shadow-green' : ($order['payment_status'] == 'Failed' ? 'danger' : 'warning'); ?> rounded-pill"><?php echo $order['payment_status']; ?></span>
                        </td>
                        <td>
                            <?php 
                            $statusClass = 'secondary';
                            switch($order['order_status']) {
                                case 'Pending': $statusClass = 'warning'; break;
                                case 'Processing': $statusClass = 'info'; break;
                                case 'Shipped': $statusClass = 'primary'; break;
                                case 'Delivered': $statusClass = 'success'; break;
                                case 'Cancelled': $statusClass = 'danger'; break;
                            }
                            ?>
                            <span class="badge bg-<?php echo $statusClass; ?> <?php echo $order['order_status'] == 'Delivered' ? 'shadow-green' : ''; ?>"><?php echo $order['order_status']; ?></span>
                        </td>
                        <td>
                            <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
