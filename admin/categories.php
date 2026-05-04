<?php
require_once '../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('../admin/login.php');
}

$admin = getCurrentAdmin($conn);

// Handle Form Submission (Add/Edit/Delete)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_category'])) {
        $name = sanitizeInput($_POST['name']);
        $description = sanitizeInput($_POST['description']);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $slug = generateSlug($name);
        
        try {
            $stmt = $conn->prepare("INSERT INTO categories (name, slug, description, is_active) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $slug, $description, $isActive]);
            setFlashMessage('success', 'Category added successfully!');
        } catch (PDOException $e) {
            setFlashMessage('danger', 'Error adding category: ' . $e->getMessage());
        }
        redirect('categories.php');
    }
    
    if (isset($_POST['update_category'])) {
        $id = (int)$_POST['category_id'];
        $name = sanitizeInput($_POST['name']);
        $description = sanitizeInput($_POST['description']);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $slug = generateSlug($name); // Re-generate slug (optional)
        
        try {
            $stmt = $conn->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$name, $slug, $description, $isActive, $id]);
            setFlashMessage('success', 'Category updated successfully!');
        } catch (PDOException $e) {
            setFlashMessage('danger', 'Error updating category: ' . $e->getMessage());
        }
        redirect('categories.php');
    }
    
    if (isset($_POST['delete_category'])) {
        $id = (int)$_POST['category_id'];
        
        // Check if products exist in category
        $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            setFlashMessage('warning', 'Cannot delete category containing products.');
        } else {
            try {
                $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->execute([$id]);
                setFlashMessage('success', 'Category deleted successfully!');
            } catch (PDOException $e) {
                setFlashMessage('danger', 'Error deleting category: ' . $e->getMessage());
            }
        }
        redirect('categories.php');
    }
}

// Get Categories
$stmt = $conn->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

$pageTitle = 'Manage Categories';
include 'includes/header.php';
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-white">Collection <span class="text-primary-purple">Categories</span></h2>
    </div>

    <div class="row g-4">
        <!-- Category List -->
        <div class="col-md-8">
            <div class="card border-0 shadow-lg">
                <div class="card-header py-4 border-0">
                    <h5 class="mb-0 fw-bold small text-uppercase text-primary-purple"><i class="bi bi-collection me-2"></i>Existing Collections</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th class="ps-4">Collection Name</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th class="pe-4 text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-white"><?php echo escapeOutput($category['name']); ?></td>
                                    <td class="text-muted small"><?php echo truncateText(escapeOutput($category['description']), 60); ?></td>
                                    <td>
                                        <?php if ($category['is_active']): ?>
                                            <span class="badge bg-success shadow-green rounded-pill px-3">Public</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary rounded-pill px-3">Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="btn-group shadow-sm rounded-pill overflow-hidden">
                                            <button class="btn btn-dark btn-sm px-3 btn-edit border-end" 
                                                    style="border-color: var(--border-color) !important;"
                                                    data-id="<?php echo $category['id']; ?>"
                                                    data-name="<?php echo escapeOutput($category['name']); ?>"
                                                    data-description="<?php echo escapeOutput($category['description']); ?>"
                                                    data-active="<?php echo $category['is_active']; ?>"
                                                    data-bs-toggle="modal" data-bs-target="#editCategoryModal">
                                                <i class="bi bi-pencil-square" style="color: var(--accent-purple);"></i>
                                            </button>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Remove this collection?');">
                                                <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                                <button type="submit" name="delete_category" class="btn btn-dark btn-sm px-3">
                                                    <i class="bi bi-trash3 text-danger"></i>
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
        </div>
        
        <!-- Add Category Form -->
        <div class="col-md-4">
            <div class="card border-0 shadow-lg">
                <div class="card-header py-4 border-0">
                    <h5 class="mb-0 fw-bold small text-uppercase text-primary-purple">Create Collection</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" id="addCategoryForm">
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted text-uppercase">Collection Name</label>
                            <input type="text" name="name" class="form-control border-0 bg-dark rounded-pill px-3 py-2" placeholder="e.g. Chronograph" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted text-uppercase">Description</label>
                            <textarea name="description" class="form-control border-0 bg-dark px-3 py-3 rounded-4" rows="3" placeholder="Describe this category..."></textarea>
                        </div>
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="new_is_active" checked>
                                <label class="form-check-label small fw-bold text-muted" for="new_is_active">Publish Immediately</label>
                            </div>
                        </div>
                        <button type="submit" name="add_category" class="btn btn-primary w-100 rounded-pill py-2">
                            <i class="bi bi-plus-lg me-2"></i>Add Category
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" id="editCategoryForm">
                    <input type="hidden" name="category_id" id="edit_category_id">
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active">
                            <label class="form-check-label" for="edit_is_active">Active</label>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_category" class="btn btn-primary">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Handle Edit Modal
    $('.btn-edit').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var desc = $(this).data('description');
        var active = $(this).data('active');
        $('#edit_category_id').val(id);
        $('#edit_name').val(name);
        $('#edit_description').val(desc);
        $('#edit_is_active').prop('checked', active == 1);
    });

    var shakeOnInvalid = {
        errorPlacement: function (error, element) { error.insertAfter(element); },
        invalidHandler: function (e, validator) {
            $(validator.currentForm).addClass('form-shake');
            setTimeout(function () { $(validator.currentForm).removeClass('form-shake'); }, 500);
        }
    };

    $('#addCategoryForm').validate($.extend({
        rules:    { name: { required: true, minlength: 2 } },
        messages: { name: { required: 'Category name is required', minlength: 'At least 2 characters' } }
    }, shakeOnInvalid));

    $('#editCategoryForm').validate($.extend({
        rules:    { name: { required: true, minlength: 2 } },
        messages: { name: { required: 'Category name is required', minlength: 'At least 2 characters' } }
    }, shakeOnInvalid));
});
</script>
