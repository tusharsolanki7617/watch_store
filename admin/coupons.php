<?php
require_once '../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('../admin/login.php');
}

$admin = getCurrentAdmin($conn);

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['save_coupon'])) {
        $id = isset($_POST['coupon_id']) ? (int)$_POST['coupon_id'] : 0;
        $code = strtoupper(sanitizeInput($_POST['code']));
        $description = sanitizeInput($_POST['description']);
        $discountType = sanitizeInput($_POST['discount_type']);
        $discountValue = (float)$_POST['discount_value'];
        $minOrderAmount = !empty($_POST['min_order_amount']) ? (float)$_POST['min_order_amount'] : 0;
        $maxDiscount = !empty($_POST['max_discount']) ? (float)$_POST['max_discount'] : null;
        $usageLimit = !empty($_POST['usage_limit']) ? (int)$_POST['usage_limit'] : null;
        $startDate = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        $expiryDate = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        try {
            if ($id > 0) {
                // Update
                $sql = "UPDATE coupons SET 
                        code = ?, description = ?, discount_type = ?, discount_value = ?, 
                        min_order_amount = ?, max_discount = ?, usage_limit = ?, 
                        start_date = ?, expiry_date = ?, is_active = ? 
                        WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    $code, $description, $discountType, $discountValue, 
                    $minOrderAmount, $maxDiscount, $usageLimit, 
                    $startDate, $expiryDate, $isActive, $id
                ]);
                setFlashMessage('success', 'Coupon updated successfully!');
            } else {
                // Insert
                // Check if code exists
                $stmt = $conn->prepare("SELECT COUNT(*) FROM coupons WHERE code = ?");
                $stmt->execute([$code]);
                if ($stmt->fetchColumn() > 0) {
                    throw new Exception("Coupon code '$code' already exists.");
                }
                
                $sql = "INSERT INTO coupons (
                        code, description, discount_type, discount_value, 
                        min_order_amount, max_discount, usage_limit, 
                        start_date, expiry_date, is_active
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    $code, $description, $discountType, $discountValue, 
                    $minOrderAmount, $maxDiscount, $usageLimit, 
                    $startDate, $expiryDate, $isActive
                ]);
                setFlashMessage('success', 'Coupon created successfully!');
            }
        } catch (Exception $e) {
            setFlashMessage('danger', 'Error saving coupon: ' . $e->getMessage());
        }
        redirect('coupons.php');
    }
    
    if (isset($_POST['delete_coupon'])) {
        $id = (int)$_POST['coupon_id'];
        try {
            $stmt = $conn->prepare("DELETE FROM coupons WHERE id = ?");
            $stmt->execute([$id]);
            setFlashMessage('success', 'Coupon deleted successfully!');
        } catch (PDOException $e) {
            setFlashMessage('danger', 'Error deleting coupon: ' . $e->getMessage());
        }
        redirect('coupons.php');
    }
}

// Get Coupons
$stmt = $conn->query("SELECT * FROM coupons ORDER BY created_at DESC");
$coupons = $stmt->fetchAll();

$pageTitle = 'Manage Coupons';
include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-white fw-bold">Incentive <span class="text-primary-purple">Nexus</span></h2>
    <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#couponModal" onclick="resetCouponForm()">
        <i class="bi bi-plus-circle me-2"></i>New Voucher
    </button>
</div>

<div class="card border-0 shadow-lg">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Discount</th>
                        <th>Min. Order</th>
                        <th>Usage</th>
                        <th>Expiry</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coupons as $coupon): ?>
                    <tr>
                        <td class="ps-4"><strong class="text-white"><?php echo escapeOutput($coupon['code']); ?></strong></td>
                        <td>
                            <?php 
                            if ($coupon['discount_type'] == 'percentage') {
                                echo $coupon['discount_value'] . '%';
                            } else {
                                echo formatPrice($coupon['discount_value']);
                            }
                            ?>
                        </td>
                        <td><?php echo formatPrice($coupon['min_order_amount']); ?></td>
                        <td><?php echo $coupon['used_count'] . ' / ' . ($coupon['usage_limit'] ?: '∞'); ?></td>
                        <td><?php echo $coupon['expiry_date'] ? date('M d, Y', strtotime($coupon['expiry_date'])) : 'Never'; ?></td>
                        <td>
                            <?php if ($coupon['is_active']): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary rounded-pill px-3">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="pe-4">
                            <div class="btn-group shadow-sm rounded-pill overflow-hidden">
                                <button class="btn btn-dark btn-sm px-3 btn-edit-coupon" 
                                        style="border-color: var(--border-color) !important;"
                                        data-coupon='<?php echo htmlspecialchars(json_encode($coupon), ENT_QUOTES, 'UTF-8'); ?>'>
                                    <i class="bi bi-pencil" style="color: var(--accent-purple);"></i>
                                </button>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete this coupon?');">
                                    <input type="hidden" name="coupon_id" value="<?php echo $coupon['id']; ?>">
                                    <button type="submit" name="delete_coupon" class="btn btn-dark btn-sm px-3">
                                        <i class="bi bi-trash text-danger"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Coupon Modal -->
<div class="modal fade" id="couponModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="couponForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Coupon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="coupon_id" id="coupon_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Coupon Code *</label>
                            <input type="text" name="code" id="code" class="form-control" required placeholder="e.g. SAVE20">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Discount Type *</label>
                            <select name="discount_type" id="discount_type" class="form-select" required>
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount (₹)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Discount Value *</label>
                            <input type="number" name="discount_value" id="discount_value" class="form-control" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Min. Order Amount</label>
                            <input type="number" name="min_order_amount" id="min_order_amount" class="form-control" step="0.01" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Max Discount (for %)</label>
                            <input type="number" name="max_discount" id="max_discount" class="form-control" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Usage Limit</label>
                            <input type="number" name="usage_limit" id="usage_limit" class="form-control" placeholder="Blank for unlimited">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Expiry Date</label>
                            <input type="date" name="expiry_date" id="expiry_date" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                                <label class="form-check-label" for="is_active">Is Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_coupon" class="btn btn-primary" id="saveBtn">Create Coupon</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    function resetCouponForm() {
        $('#couponForm')[0].reset();
        $('#coupon_id').val('');
        $('#modalTitle').text('Add Coupon');
        $('#saveBtn').text('Create Coupon').attr('name', 'save_coupon'); // Changed to save_coupon
        $('#is_active').prop('checked', true);
    }

    function editCoupon(coupon) {
        $('#couponModal').modal('show');
        $('#modalTitle').text('Edit Coupon');
        $('#saveBtn').text('Update Coupon').attr('name', 'save_coupon'); // Changed to save_coupon
        
        $('#coupon_id').val(coupon.id);
        $('#code').val(coupon.code);
        $('#discount_type').val(coupon.discount_type);
        $('#discount_value').val(coupon.discount_value);
        $('#min_order_amount').val(coupon.min_order_amount);
        $('#max_discount').val(coupon.max_discount);
        $('#usage_limit').val(coupon.usage_limit);
        $('#expiry_date').val(coupon.expiry_date);
        $('#description').val(coupon.description);
        $('#is_active').prop('checked', coupon.is_active == 1);
    }
    
    $(document).ready(function() {
        $('.btn-edit-coupon').on('click', function() {
            var coupon = $(this).data('coupon');
            editCoupon(coupon);
        });

        $('#couponForm').validate({
            rules: {
                code:             { required: true, minlength: 2 },
                discount_value:   { required: true, number: true, min: 0 },
                min_order_amount: { number: true, min: 0 },
                max_discount:     { number: true, min: 0 },
                usage_limit:      { digits: true, min: 1 }
            },
            messages: {
                code:           { required: 'Coupon code is required', minlength: 'At least 2 characters' },
                discount_value: { required: 'Discount value is required', number: 'Must be a number', min: 'Cannot be negative' },
                usage_limit:    { digits: 'Must be a whole number', min: 'Must be at least 1' }
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
