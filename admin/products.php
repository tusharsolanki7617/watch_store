<?php
require_once '../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('../admin/login.php');
}

$admin = getCurrentAdmin($conn);

// Get all products with category names
$stmt = $conn->query("
    SELECT p.*, c.name as category_name,
    (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
    FROM products p
    JOIN categories c ON p.category_id = c.id
    ORDER BY p.created_at DESC
");
$products = $stmt->fetchAll();

$pageTitle = 'Manage Products';
include 'includes/header.php';
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-white">Product <span class="text-primary-purple">Inventory</span></h2>
        <a href="<?php echo SITE_URL; ?>/admin/add-product.php" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-plus-lg me-2"></i>New Timepiece
        </a>
    </div>

    <div class="card border-0 shadow-lg">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="ps-4 py-3">Item</th>
                            <th class="py-3">Details</th>
                            <th class="py-3">Price</th>
                            <th class="py-3">Stock</th>
                            <th class="py-3">Status</th>
                            <th class="pe-4 py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr class="transition-all">
                            <td class="ps-4">
                                <?php if ($product['image']): ?>
                                <img src="<?php echo SITE_URL; ?>/uploads/products/<?php echo $product['image']; ?>" 
                                     class="rounded shadow-sm"
                                     style="width: 60px; height: 60px; object-fit: cover; border: 2px solid var(--border-color);">
                                <?php else: ?>
                                <div class="bg-dark rounded d-flex align-items-center justify-content-center text-muted border border-purple" style="width: 60px; height: 60px;">
                                    <i class="bi bi-image"></i>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-bold text-white"><?php echo escapeOutput($product['name']); ?></div>
                                <div class="small text-muted"><?php echo escapeOutput($product['category_name']); ?></div>
                            </td>
                            <td>
                                <div class="fw-bold" style="color: var(--primary-purple);"><?php echo formatPrice(getFinalPrice($product)); ?></div>
                                <?php if (getFinalPrice($product) < $product['price']): ?>
                                    <div class="small text-muted text-decoration-line-through"><?php echo formatPrice($product['price']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($product['stock'] > 10): ?>
                                    <span class="badge bg-success rounded-pill px-3"><?php echo $product['stock']; ?></span>
                                <?php elseif ($product['stock'] > 0): ?>
                                    <span class="badge bg-warning rounded-pill px-3"><?php echo $product['stock']; ?></span>
                                <?php else: ?>
                                    <span class="badge bg-danger rounded-pill px-3">Stock Out</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($product['is_active']): ?>
                                    <span class="text-success small fw-bold"><i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i>Live</span>
                                <?php else: ?>
                                    <span class="text-muted small fw-bold"><i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i>Hidden</span>
                                <?php endif; ?>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="btn-group shadow-sm rounded-pill overflow-hidden">
                                    <a href="<?php echo SITE_URL; ?>/admin/edit-product.php?id=<?php echo $product['id']; ?>" class="btn btn-dark btn-sm px-3 border-end" style="border-color: var(--border-color) !important;">
                                        <i class="bi bi-pencil-square" style="color: var(--accent-purple);"></i>
                                    </a>
                                    <a href="<?php echo SITE_URL; ?>/admin/delete-product.php?id=<?php echo $product['id']; ?>" 
                                       class="btn btn-dark btn-sm px-3" 
                                       onclick="return confirm('Archive this timepiece from the inventory?')">
                                        <i class="bi bi-trash3 text-danger"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
