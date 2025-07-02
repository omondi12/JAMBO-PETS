<?php
// Start the session
session_start();

// Include database connection
require_once '../config/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'seller') {
    header('Location: ../auth/login.php');
    exit();
}

// Get seller ID
$user_id = $_SESSION['user_id'];
$sql = "SELECT seller_id FROM seller_profiles WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$seller = $result->fetch_assoc();
$seller_id = $seller['seller_id'];

// Initialize variables
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = [];
$success_message = '';

// Check if product exists and belongs to this seller
$sql = "SELECT * FROM products WHERE product_id = ? AND seller_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $product_id, $seller_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // Product doesn't exist or doesn't belong to this seller
    header('Location: manage_products.php?error=not_found');
    exit();
}

$product = $result->fetch_assoc();

// Fetch product images
$sql = "SELECT * FROM images WHERE item_type = 'product' AND item_id = ? ORDER BY is_primary DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$images_result = $stmt->get_result();
$images = [];
while ($image = $images_result->fetch_assoc()) {
    $images[] = $image;
}

// Fetch all categories
$sql = "SELECT * FROM categories ORDER BY name";
$categories = $conn->query($sql);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate input
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $category_id = (int)$_POST['category_id'];
    $price = (float)$_POST['price'];
    $stock_quantity = (int)$_POST['stock_quantity'];
    $status = $_POST['status'];
    
    // Basic validation
    if (empty($name)) {
        $errors[] = "Product name is required.";
    }
    
    if (empty($description)) {
        $errors[] = "Product description is required.";
    }
    
    if ($category_id <= 0) {
        $errors[] = "Please select a valid category.";
    }
    
    if ($price <= 0) {
        $errors[] = "Price must be greater than zero.";
    }
    
    if ($stock_quantity < 0) {
        $errors[] = "Stock quantity cannot be negative.";
    }
    
    // If no errors, update the product
    if (empty($errors)) {
        $sql = "UPDATE products SET 
                name = ?,
                description = ?,
                category_id = ?,
                price = ?,
                stock_quantity = ?,
                status = ?,
                updated_at = NOW()
                WHERE product_id = ? AND seller_id = ?";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssidisii", $name, $description, $category_id, $price, $stock_quantity, $status, $product_id, $seller_id);
        
        if ($stmt->execute()) {
            // Process images
            $upload_dir = '../uploads/products/';
            
            // Make sure upload directory exists
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Handle new image uploads
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                $total_files = count($_FILES['images']['name']);
                
                for ($i = 0; $i < $total_files; $i++) {
                    if ($_FILES['images']['error'][$i] == 0) {
                        $temp_name = $_FILES['images']['tmp_name'][$i];
                        $file_name = basename($_FILES['images']['name'][$i]);
                        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                        
                        // Check if it's a valid image
                        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                        if (in_array($file_ext, $allowed_types)) {
                            // Generate unique filename
                            $new_file_name = uniqid('product_') . '.' . $file_ext;
                            $destination = $upload_dir . $new_file_name;
                            
                            if (move_uploaded_file($temp_name, $destination)) {
                                // Save image record to database
                                $is_primary = (isset($_POST['primary_image']) && $_POST['primary_image'] == $i) ? 1 : 0;
                                $relative_path = 'uploads/products/' . $new_file_name;
                                
                                // If this is primary, set all other images to non-primary
                                if ($is_primary) {
                                    $update_sql = "UPDATE images SET is_primary = 0 WHERE item_type = 'product' AND item_id = ?";
                                    $update_stmt = $conn->prepare($update_sql);
                                    $update_stmt->bind_param("i", $product_id);
                                    $update_stmt->execute();
                                }
                                
                                $img_sql = "INSERT INTO images (item_type, item_id, image_path, is_primary) VALUES (?, ?, ?, ?)";
                                $img_stmt = $conn->prepare($img_sql);
                                $item_type = 'product';
                                $img_stmt->bind_param("sisi", $item_type, $product_id, $relative_path, $is_primary);
                                $img_stmt->execute();
                            } else {
                                $errors[] = "Failed to upload image: " . $file_name;
                            }
                        } else {
                            $errors[] = "Invalid file type. Only JPG, JPEG, PNG and GIF are allowed.";
                        }
                    }
                }
            }
            
            // Handle primary image selection if no new images uploaded
            if (isset($_POST['existing_primary_image']) && !empty($_POST['existing_primary_image'])) {
                $primary_image_id = (int)$_POST['existing_primary_image'];
                
                // Set all images to non-primary first
                $update_sql = "UPDATE images SET is_primary = 0 WHERE item_type = 'product' AND item_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("i", $product_id);
                $update_stmt->execute();
                
                // Set selected image to primary
                $primary_sql = "UPDATE images SET is_primary = 1 WHERE image_id = ? AND item_type = 'product' AND item_id = ?";
                $primary_stmt = $conn->prepare($primary_sql);
                $primary_stmt->bind_param("ii", $primary_image_id, $product_id);
                $primary_stmt->execute();
            }
            
            // Handle image deletions
            if (isset($_POST['delete_images']) && is_array($_POST['delete_images'])) {
                foreach ($_POST['delete_images'] as $image_id) {
                    // Get image path before deleting
                    $img_sql = "SELECT image_path FROM images WHERE image_id = ? AND item_type = 'product' AND item_id = ?";
                    $img_stmt = $conn->prepare($img_sql);
                    $img_stmt->bind_param("ii", $image_id, $product_id);
                    $img_stmt->execute();
                    $img_result = $img_stmt->get_result();
                    
                    if ($img_row = $img_result->fetch_assoc()) {
                        $file_path = '../' . $img_row['image_path'];
                        
                        // Delete from database
                        $del_sql = "DELETE FROM images WHERE image_id = ? AND item_type = 'product' AND item_id = ?";
                        $del_stmt = $conn->prepare($del_sql);
                        $del_stmt->bind_param("ii", $image_id, $product_id);
                        $del_stmt->execute();
                        
                        // Delete file from server
                        if (file_exists($file_path)) {
                            unlink($file_path);
                        }
                    }
                }
            }
            
            $success_message = "Product has been updated successfully!";
            
            // Refresh product and images data
            $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();
            
            $stmt = $conn->prepare("SELECT * FROM images WHERE item_type = 'product' AND item_id = ? ORDER BY is_primary DESC");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $images_result = $stmt->get_result();
            $images = [];
            while ($image = $images_result->fetch_assoc()) {
                $images[] = $image;
            }
        } else {
            $errors[] = "Failed to update product. Please try again.";
        }
    }
}

// Include header
include_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <!-- Seller sidebar -->
            <?php include_once 'seller_sidebar.php'; ?>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4>Edit Product</h4>
                    <a href="manage_products.php" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Products
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $product_id); ?>" method="post" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="name">Product Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="6" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="category_id">Category</label>
                                            <select class="form-control" id="category_id" name="category_id" required>
                                                <option value="">Select Category</option>
                                                <?php while ($category = $categories->fetch_assoc()): ?>
                                                    <option value="<?php echo $category['category_id']; ?>" <?php echo ($category['category_id'] == $product['category_id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($category['name']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="status">Status</label>
                                            <select class="form-control" id="status" name="status">
                                                <option value="available" <?php echo ($product['status'] == 'available') ? 'selected' : ''; ?>>Available</option>
                                                <option value="out_of_stock" <?php echo ($product['status'] == 'out_of_stock') ? 'selected' : ''; ?>>Out of Stock</option>
                                                <option value="inactive" <?php echo ($product['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="price">Price (Kshs)</label>
                                            <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="stock_quantity">Stock Quantity</label>
                                            <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" min="0" value="<?php echo htmlspecialchars($product['stock_quantity']); ?>" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        Current Images
                                    </div>
                                    <div class="card-body">
                                        <?php if (count($images) > 0): ?>
                                            <div class="form-group">
                                                <label>Select Primary Image</label>
                                                <?php foreach ($images as $image): ?>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="radio" name="existing_primary_image" id="img_<?php echo $image['image_id']; ?>" value="<?php echo $image['image_id']; ?>" <?php echo ($image['is_primary'] == 1) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label d-flex align-items-center" for="img_<?php echo $image['image_id']; ?>">
                                                            <img src="<?php echo htmlspecialchars('../' . $image['image_path']); ?>" alt="Product Image" class="img-thumbnail mr-2" style="width: 60px; height: 60px; object-fit: cover;">
                                                            <?php echo ($image['is_primary'] == 1) ? '<span class="badge badge-success ml-1">Primary</span>' : ''; ?>
                                                            <div class="custom-control custom-checkbox ml-2">
                                                                <input type="checkbox" class="custom-control-input" id="delete_<?php echo $image['image_id']; ?>" name="delete_images[]" value="<?php echo $image['image_id']; ?>">
                                                                <label class="custom-control-label text-danger" for="delete_<?php echo $image['image_id']; ?>">Delete</label>
                                                            </div>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <p class="text-muted">No images uploaded yet.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="card">
                                    <div class="card-header bg-light">
                                        Upload New Images
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="images">Add Images</label>
                                            <input type="file" class="form-control-file" id="images" name="images[]" multiple accept="image/*">
                                            <small class="form-text text-muted">You can select multiple images to upload. Supported formats: JPG, JPEG, PNG, GIF.</small>
                                        </div>
                                        
                                        <div class="form-group" id="new_images_preview" style="display: none;">
                                            <label>Set Primary Image</label>
                                            <div id="new_images_container" class="d-flex flex-wrap"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Product
                            </button>
                            <a href="manage_products.php" class="btn btn-secondary ml-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Preview new images before upload
document.getElementById('images').addEventListener('change', function(e) {
    const files = e.target.files;
    const container = document.getElementById('new_images_container');
    const preview = document.getElementById('new_images_preview');
    
    if (files.length > 0) {
        preview.style.display = 'block';
        container.innerHTML = '';
        
        for (let i = 0; i < files.length; i++) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.classList.add('mr-2', 'mb-2', 'position-relative');
                
                const img = document.createElement('img');
                img.src = e.target.result;
                img.classList.add('img-thumbnail');
                img.style.width = '80px';
                img.style.height = '80px';
                img.style.objectFit = 'cover';
                
                const radio = document.createElement('div');
                radio.classList.add('form-check', 'mt-1');
                
                const input = document.createElement('input');
                input.type = 'radio';
                input.name = 'primary_image';
                input.value = i;
                input.id = 'new_img_' + i;
                input.classList.add('form-check-input');
                
                const label = document.createElement('label');
                label.htmlFor = 'new_img_' + i;
                label.classList.add('form-check-label');
                label.textContent = 'Set as primary';
                
                radio.appendChild(input);
                radio.appendChild(label);
                
                div.appendChild(img);
                div.appendChild(radio);
                
                container.appendChild(div);
            }
            
            reader.readAsDataURL(files[i]);
        }
    } else {
        preview.style.display = 'none';
    }
});
</script>

<?php include_once '../includes/footer.php'; ?>