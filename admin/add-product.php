<?php
require_once '../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('../admin/login.php');
}

$admin = getCurrentAdmin($conn);

// Get categories for dropdown
$stmt = $conn->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");
$categories = $stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
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
    
    // Generate slug
    $slug = generateSlug($name);
    
    // Insert product
    $stmt = $conn->prepare("
        INSERT INTO products (category_id, name, slug, brand, model_number, description, 
                            price, discount_price, stock, movement_type, case_material, 
                            strap_material, water_resistance, warranty, features, 
                            is_featured, is_active)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    if ($stmt->execute([$categoryId, $name, $slug, $brand, $modelNumber, $description,
                       $price, $discountPrice, $stock, $movementType, $caseMaterial,
                       $strapMaterial, $waterResistance, $warranty, $features,
                       $isFeatured, $isActive])) {
        
        $productId = $conn->lastInsertId();
        
        // Handle image uploads
        if (!empty($_FILES['images']['name'][0])) {
            $uploadDir = PRODUCT_IMAGE_PATH;
            
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $isPrimary = true;
            $displayOrder = 0;
            
            foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $fileName = $_FILES['images']['name'][$key];
                    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    
                    // Validate image
                    if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])) {
                        $newFileName = uniqid() . '_' . time() . '.' . $fileExt;
                        $destination = $uploadDir . '/' . $newFileName;
                        
                        if (move_uploaded_file($tmpName, $destination)) {
                            $stmt = $conn->prepare("
                                INSERT INTO product_images (product_id, image_path, is_primary, display_order)
                                VALUES (?, ?, ?, ?)
                            ");
                            $stmt->execute([$productId, $newFileName, $isPrimary ? 1 : 0, $displayOrder]);
                            
                            $isPrimary = false;
                            $displayOrder++;
                        }
                    }
                }
            }
        }
        
        setFlashMessage('success', 'Product added successfully!');
        redirect('../admin/products.php');
    } else {
        setFlashMessage('danger', 'Failed to add product. Please try again.');
    }
}

$pageTitle = 'Add Product';
include 'includes/header.php';
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between align-items-center mb-4 text-white">
        <h2 class="fw-bold">Forge <span class="text-primary-purple">New Timepiece</span></h2>
    </div>

    <div class="card border-0 shadow-lg">
    <div class="card-body p-4">
        <form method="POST" enctype="multipart/form-data" id="addProductForm">
            <div class="row">
                <!-- Basic Information -->
                <div class="col-md-8">
                    <h5 class="mb-4 fw-bold text-primary-purple"><i class="bi bi-info-circle me-2"></i>Basic Information</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">Product Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter timepiece name" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category *</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo escapeOutput($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Brand *</label>
                            <input type="text" name="brand" class="form-control" placeholder="e.g. Rolex, Omega" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Model Number</label>
                        <input type="text" name="model_number" class="form-control" placeholder="Reference ID">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description *</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Detail the craftsmanship..." required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Key Features (one per line)</label>
                        <textarea name="features" class="form-control" rows="3" placeholder="Sapphire Crystal, 40mm Case..."></textarea>
                    </div>
                </div>
                                
                <!-- Pricing & Stock -->
                <div class="col-md-4">
                    <h5 class="mb-4 fw-bold text-primary-purple"><i class="bi bi-tag me-2"></i>Pricing & Stock</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">Price (₹) *</label>
                        <input type="number" name="price" class="form-control" step="0.01" min="0" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Discount Price (₹)</label>
                        <input type="number" name="discount_price" class="form-control" step="0.01" min="0">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Stock Quantity *</label>
                        <input type="number" name="stock" class="form-control" min="0" required>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured">
                            <label class="form-check-label small fw-bold text-muted" for="is_featured">Feature in Showroom</label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                            <label class="form-check-label small fw-bold text-muted" for="is_active">Live Status</label>
                        </div>
                    </div>
                </div>
            </div>
                            
                            <hr>
                            
                            <!-- Specifications -->
            <!-- Specifications -->
            <h5 class="mb-4 fw-bold text-primary-purple"><i class="bi bi-gear me-2"></i>Technical Specifications</h5>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Movement Type *</label>
                    <select name="movement_type" class="form-select" required>
                        <option value="Quartz">Quartz</option>
                        <option value="Automatic">Automatic</option>
                        <option value="Mechanical">Mechanical</option>
                        <option value="Smart">Smart</option>
                    </select>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Case Material</label>
                    <input type="text" name="case_material" class="form-control" placeholder="e.g. Stainless Steel">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Strap Material</label>
                    <input type="text" name="strap_material" class="form-control" placeholder="e.g. Leather, Oyster">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Water Resistance</label>
                    <input type="text" name="water_resistance" class="form-control" placeholder="e.g. 100m / 10 Bar">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Warranty Information</label>
                    <input type="text" name="warranty" class="form-control" placeholder="e.g. 2 Years International">
                </div>
            </div>
            
            <hr class="my-5">
            
            <!-- Images -->
            <h5 class="mb-4 fw-bold text-primary-purple"><i class="bi bi-images me-2"></i>Product Gallery</h5>
            <div class="mb-3">
                <label class="form-label">Upload High-Res Media (Max 5)</label>
                <div class="bg-dark p-4 rounded-4 text-center border border-dashed border-purple">
                    <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                    <div class="form-text mt-2">Recommended resolution: 1200x1200px. First image sets the primary view.</div>
                </div>
            </div>
                            
                            <hr>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" name="add_product" class="btn btn-primary rounded-pill px-4">
                                    <i class="bi bi-save me-2"></i>Add Product
                                </button>
                                <a href="<?php echo SITE_URL; ?>/admin/products.php" class="btn btn-secondary rounded-pill px-4">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>

<script>
$(document).ready(function () {

    // Custom method: discount must be less than price
    $.validator.addMethod('lessThanPrice', function (value, element) {
        var price = parseFloat($('input[name="price"]').val()) || 0;
        return !value || parseFloat(value) < price;
    }, 'Discount price must be less than the regular price.');

    $('#addProductForm').validate({
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
            // Scroll to first error
            var firstError = $(validator.errorList[0].element);
            $('html, body').animate({ scrollTop: firstError.offset().top - 100 }, 300);
        }
    });
});
</script>
