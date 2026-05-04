<?php
require_once '../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('../admin/login.php');
}

$admin = getCurrentAdmin($conn);

// Handle Actions (Approve/Delete)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $reviewId = (int)$_GET['id'];

    if ($action == 'approve') {
        $stmt = $conn->prepare("UPDATE reviews SET is_approved = 1 WHERE id = ?");
        if ($stmt->execute([$reviewId])) {
            setFlashMessage('success', 'Review approved successfully.');
        }
    } elseif ($action == 'delete') {
        $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
        if ($stmt->execute([$reviewId])) {
            setFlashMessage('success', 'Review deleted successfully.');
        }
    }
    header("Location: reviews.php");
    exit();
}

// Get reviews with user and product names
$reviews = [];
try {
    $stmt = $conn->query("
        SELECT r.*, u.full_name as user_name, p.name as product_name 
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        JOIN products p ON r.product_id = p.id
        ORDER BY r.created_at DESC
    ");
    $reviews = $stmt->fetchAll();
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Table 'watch_store.reviews' doesn't exist") !== false) {
        setFlashMessage('danger', 'The "reviews" table is missing. Please run the SQL in database/database.sql to create it.');
    } else {
        setFlashMessage('danger', 'Database error: ' . $e->getMessage());
    }
}

$pageTitle = 'Manage Reviews';
include 'includes/header.php';
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-white">Customer <span class="text-primary-purple">Feedback</span></h2>
    </div>

    <div class="card border-0 shadow-lg">
        <div class="card-header py-4 border-0">
            <h5 class="mb-0 fw-bold small text-uppercase text-primary-purple"><i class="bi bi-star me-2"></i>Verified Reviews</h5>
        </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Product</th>
                        <th>User</th>
                        <th>Rating</th>
                        <th>Review</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reviews)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No reviews found.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                    <tr>
                        <td><small><?php echo date('d M Y', strtotime($review['created_at'])); ?></small></td>
                        <td>
                            <a href="<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $review['product_id']; ?>" target="_blank" class="text-decoration-none text-white fw-bold">
                                <?php echo truncateText(escapeOutput($review['product_name']), 30); ?>
                            </a>
                        </td>
                        <td class="text-muted"><?php echo escapeOutput($review['user_name']); ?></td>
                        <td>
                            <div class="text-warning">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star<?php echo $i <= $review['rating'] ? '-fill' : ''; ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($review['title']): ?>
                                <strong><?php echo escapeOutput($review['title']); ?></strong><br>
                            <?php endif; ?>
                            <small class="text-muted"><?php echo truncateText(escapeOutput($review['comment']), 50); ?></small>
                        </td>
                        <td>
                            <?php if ($review['is_approved']): ?>
                                <span class="badge bg-success">Approved</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group shadow-sm rounded-pill overflow-hidden">
                                <?php if (!$review['is_approved']): ?>
                                <a href="reviews.php?action=approve&id=<?php echo $review['id']; ?>" class="btn btn-dark btn-sm px-3" title="Approve">
                                    <i class="bi bi-check-lg text-success"></i>
                                </a>
                                <?php endif; ?>
                                <a href="reviews.php?action=delete&id=<?php echo $review['id']; ?>" class="btn btn-dark btn-sm px-3 <?php echo !$review['is_approved'] ? 'border-start' : ''; ?>" style="border-color: var(--border-color) !important;" title="Delete" onclick="return confirm('Are you sure you want to delete this review?')">
                                    <i class="bi bi-trash text-danger"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
