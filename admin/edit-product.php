<?php
require_once '../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('../admin/login.php');
}

$admin = getCurrentAdmin($conn);

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId <= 0) {
    redirect('../admin/products.php');
}

// Get Product
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    setFlashMessage('danger', 'Product not found.');
    redirect('../admin/products.php');
}

// Get Categories
$stmt = $conn->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");
$categories = $stmt->fetchAll();

// Get Product Images
$stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, display_order ASC");
$stmt->execute([$productId]);
$images = $stmt->fetchAll();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {
    $name = sanitizeInput($_POST['name']);
    $categoryId = (int)$_POST['category_id'];
    $brand = sanitizeInput($_POST['brand']);
    $modelNumber = sanitizeInput($_POST['model_number']);
    $description = sanitizeInput($_POST['description']);
    $price = (float)$_POST['price'];
    $discountPrice = !empty($_POST['discount_price']) ? (float)$_POST['discount_price'] : null;
    $stock = (int)$_POST['stock'];
    $movementType = sanitizeInput($_POST['movement_type']);
    $caseMaterial = sanitizeInput($_POST['case_material']);
    $strapMaterial = sanitizeInput($_POST['strap_material']);
    $waterResistance = sanitizeInput($_POST['water_resistance']);
    $warranty = sanitizeInput($_POST['warranty']);
    $features = sanitizeInput($_POST['features']);
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    // Update slug only if name changed
    $slug = $product['slug'];
    if ($name !== $product['name']) {
        $slug = generateSlug($name);
    }
    
    // Transaction
    try {
        $conn->beginTransaction();
        
        $stmt = $conn->prepare("
            UPDATE products SET 
            category_id = ?, name = ?, slug = ?, brand = ?, model_number = ?, 
            description = ?, price = ?, discount_price = ?, stock = ?, 
            movement_type = ?, case_material = ?, strap_material = ?, 
            water_resistance = ?, warranty = ?, features = ?, is_featured = ?, is_active = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $categoryId, $name, $slug, $brand, $modelNumber, $description,
            $price, $discountPrice, $stock, $movementType, $caseMaterial,
            $strapMaterial, $waterResistance, $warranty, $features,
            $isFeatured, $isActive, $productId
        ]);
        
        // Handle New Images
        if (!empty($_FILES['images']['name'][0])) {
            $uploadDir = PRODUCT_IMAGE_PATH;
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
            
            // Check if any existing images
            $orderStart = count($images);
            $hasPrimary = false;
            foreach ($images as $img) {
                if ($img['is_primary']) $hasPrimary = true;
            }
            
            foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $fileName = $_FILES['images']['name'][$key];
                    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    
                    if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])) {
                        $newFileName = uniqid() . '_' . time() . '.' . $fileExt;
                        $destination = $uploadDir . '/' . $newFileName;
                        
                        if (move_uploaded_file($tmpName, $destination)) {
                            // First new image is primary ONLY if no existing primary exists
                            $isPrimary = (!$hasPrimary && $key == 0) ? 1 : 0;
                            if ($isPrimary) $hasPrimary = true;
                            
                            $stmt = $conn->prepare("
                                INSERT INTO product_images (product_id, image_path, is_primary, display_order)
                                VALUES (?, ?, ?, ?)
                            ");
                            $stmt->execute([$productId, $newFileName, $isPrimary, $orderStart++]);
                        }
                    }
                }
            }
        }
        
        // Set Primary Image (Radio Button)
        if (isset($_POST['primary_image'])) {
            $primaryImageId = (int)$_POST['primary_image'];
            // Reset all
            $conn->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?")->execute([$productId]);
            // Set new primary
            $conn->prepare("UPDATE product_images SET is_primary = 1 WHERE id = ?")->execute([$primaryImageId]);
        }
        
        // Delete Images (Checkboxes)
        if (isset($_POST['delete_images'])) {
            foreach ($_POST['delete_images'] as $imgId) {
                $stmt = $conn->prepare("SELECT image_path FROM product_images WHERE id = ?");
                $stmt->execute([$imgId]);
                $img = $stmt->fetch();
                
                if ($img) {
                    $filePath = PRODUCT_IMAGE_PATH . '/' . $img['image_path'];
                    if (file_exists($filePath)) unlink($filePath);
                    
                    $conn->prepare("DELETE FROM product_images WHERE id = ?")->execute([$imgId]);
                }
            }
        }
        
        $conn->commit();
        setFlashMessage('success', 'Product updated successfully!');
        // Refresh
        header("Location: edit-product.php?id=$productId");
        exit();
        
    } catch (Exception $e) {
        $conn->rollBack();
        setFlashMessage('danger', 'Error updating product: ' . $e->getMessage());
    }
}

$pageTitle = 'Edit Product';
include 'includes/header.php';
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between align-items-center mb-4 text-white">
        <h2 class="fw-bold">Refine <span class="text-primary-purple">Timepiece</span></h2>
    </div>

    <div class="card border-0 shadow-lg">
    <div class="card-body p-4">
        <form method="POST" enctype="multipart/form-data" id="editProductForm">
            <div class="row">
                <!-- Basic Information -->
                <div class="col-md-8">
                    <h5 class="mb-4 fw-bold text-primary-purple"><i class="bi bi-info-circle me-2"></i>Basic Information</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">Product Name *</label>
                        <input type="text" name="name" class="form-control" value="<?php echo escapeOutput($product['name']); ?>" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category *</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo $category['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo escapeOutput($category['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Brand *</label>
                            <input type="text" name="brand" class="form-control" value="<?php echo escapeOutput($product['brand']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Model Number</label>
                        <input type="text" name="model_number" class="form-control" value="<?php echo escapeOutput($product['model_number']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description *</label>
                        <textarea name="description" class="form-control" rows="4" required><?php echo escapeOutput($product['description']); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Key Features (one per line)</label>
                        <textarea name="features" class="form-control" rows="3"><?php echo escapeOutput($product['features']); ?></textarea>
                    </div>
                </div>
                
                <!-- Pricing & Stock -->
                <div class="col-md-4">
                    <h5 class="mb-4 fw-bold text-primary-purple"><i class="bi bi-tag me-2"></i>Pricing & Stock</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">Price (₹) *</label>
                        <input type="number" name="price" class="form-control" step="0.01" min="0" value="<?php echo $product['price']; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Discount Price (₹)</label>
                        <input type="number" name="discount_price" class="form-control" step="0.01" min="0" value="<?php echo $product['discount_price']; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Stock Quantity *</label>
                        <input type="number" name="stock" class="form-control" min="0" value="<?php echo $product['stock']; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" <?php echo $product['is_featured'] ? 'checked' : ''; ?>>
                            <label class="form-check-label small fw-bold text-muted" for="is_featured">Feature in Showroom</label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?php echo $product['is_active'] ? 'checked' : ''; ?>>
                            <label class="form-check-label small fw-bold text-muted" for="is_active">Live Status</label>
                        </div>
                    </div>
                </div>
            </div>
            
            <hr>
            
            <!-- Specifications -->
            <h5 class="mb-4 fw-bold text-primary-purple"><i class="bi bi-gear me-2"></i>Technical Specifications</h5>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Movement Type *</label>
                    <select name="movement_type" class="form-select" required>
                        <?php 
                        $movements = ['Quartz', 'Automatic', 'Mechanical', 'Smart'];
                        foreach ($movements as $mov): 
                        ?>
                        <option value="<?php echo $mov; ?>" <?php echo $mov == $product['movement_type'] ? 'selected' : ''; ?>><?php echo $mov; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Case Material</label>
                    <input type="text" name="case_material" class="form-control" value="<?php echo escapeOutput($product['case_material']); ?>">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Strap Material</label>
                    <input type="text" name="strap_material" class="form-control" value="<?php echo escapeOutput($product['strap_material']); ?>">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Water Resistance</label>
                    <input type="text" name="water_resistance" class="form-control" value="<?php echo escapeOutput($product['water_resistance']); ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Warranty Information</label>
                    <input type="text" name="warranty" class="form-control" value="<?php echo escapeOutput($product['warranty']); ?>">
                </div>
            </div>
            
            <hr class="my-5">
            
            <!-- Images -->
            <h5 class="mb-3">Product Images</h5>
            
            <?php if (!empty($images)): ?>
            <div class="row mb-4">
                <?php foreach ($images as $img): ?>
                <div class="col-md-2 mb-3 text-center">
                    <div class="card h-100">
                        <img src="<?php echo SITE_URL; ?>/uploads/products/<?php echo $img['image_path']; ?>" class="card-img-top" style="height: 100px; object-fit: cover;">
                        <div class="card-body p-2">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="primary_image" value="<?php echo $img['id']; ?>" <?php echo $img['is_primary'] ? 'checked' : ''; ?>>
                                <label class="form-check-label small">Primary</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="delete_images[]" value="<?php echo $img['id']; ?>">
                                <label class="form-check-label small text-danger">Delete</label>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <div class="mb-3">
                <label class="form-label">Upload New Images</label>
                <input type="file" name="images[]" class="form-control" multiple accept="image/*">
            </div>
            
            <hr>
            
            <div class="d-flex gap-2">
                <button type="submit" name="update_product" class="btn btn-primary rounded-pill px-4">
                    <i class="bi bi-save me-2"></i>Update Product
                </button>
                <a href="<?php echo SITE_URL; ?>/admin/products.php" class="btn btn-secondary rounded-pill px-4">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
$(document).ready(function () {

    $.validator.addMethod('lessThanPrice', function (value, element) {
        var price = parseFloat($('input[name="price"]').val()) || 0;
        return !value || parseFloat(value) < price;
    }, 'Discount price must be less than the regular price.');

    $('#editProductForm').validate({
        rules: {
            name:           { required: true, minlength: 2 },
            category_id:    { required: true },
            brand:          { required: true, minlength: 2 },
            description:    { required: true, minlength: 10 },
            price:          { required: true, number: true, min: 1 },
            discount_price: { number: true, min: 0, lessThanPrice: true },
            stock:          { required: true, digits: true, min: 0 }
        },
        messages: {
            name:           { required: 'Product name is required', minlength: 'At least 2 characters' },
            category_id:    { required: 'Please select a category' },
            brand:          { required: 'Brand is required' },
            description:    { required: 'Description is required', minlength: 'At least 10 characters' },
            price:          { required: 'Price is required', number: 'Enter a valid number', min: 'Price must be greater than 0' },
            discount_price: { number: 'Enter a valid number', min: 'Cannot be negative' },
            stock:          { required: 'Stock is required', digits: 'Must be a whole number', min: 'Cannot be negative' }
        },
        errorPlacement: function (error, element) {
            error.insertAfter(element);
        },
        invalidHandler: function (e, validator) {
            var form = $(validator.currentForm);
            form.addClass('form-shake');
            setTimeout(function () { form.removeClass('form-shake'); }, 500);
            var firstError = $(validator.errorList[0].element);
            $('html, body').animate({ scrollTop: firstError.offset().top - 100 }, 300);
        }
    });
});
</script>
