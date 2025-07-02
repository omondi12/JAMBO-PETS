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

// Check if seller profile exists
if ($result->num_rows == 0) {
    header('Location: profile.php?msg=complete_profile');
    exit();
}

$seller = $result->fetch_assoc();
$seller_id = $seller['seller_id'];

// Check if form was submitted
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = trim($_POST['name']);
    $category_id = (int)$_POST['category_id'];
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $stock_quantity = (int)$_POST['stock_quantity'];
    $featured = isset($_POST['featured']) ? 1 : 0; // Process featured checkbox
    
    // Validate form data
    if (empty($name) || empty($description) || $price <= 0 || $category_id <= 0 || $stock_quantity < 0) {
        $error_message = "Please fill in all required fields correctly.";
    } else {
        // Insert product into database
        $sql = "INSERT INTO products (seller_id, category_id, name, description, price, stock_quantity, featured) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iissdii", $seller_id, $category_id, $name, $description, $price, $stock_quantity, $featured);
        
        if ($stmt->execute()) {
            $product_id = $stmt->insert_id;
            
            // Process image uploads
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                $upload_dir = "../uploads/products/";
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                // Process each uploaded image
                $total_files = count($_FILES['images']['name']);
                
                for ($i = 0; $i < $total_files; $i++) {
                    // Check if the file was uploaded without errors
                    if ($_FILES['images']['error'][$i] == 0) {
                        $temp_file = $_FILES['images']['tmp_name'][$i];
                        $file_name = time() . '_' . $_FILES['images']['name'][$i];
                        $target_file = $upload_dir . $file_name;
                        
                        // Move uploaded file
                        if (move_uploaded_file($temp_file, $target_file)) {
                            // Insert image record into database
                            $is_primary = ($i == 0) ? 1 : 0; // First image is primary
                            $image_path = "uploads/products/" . $file_name;
                            
                            $sql = "INSERT INTO images (item_type, item_id, image_path, is_primary) 
                                    VALUES ('product', ?, ?, ?)";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("isi", $product_id, $image_path, $is_primary);
                            $stmt->execute();
                        }
                    }
                }
            }
            
            $success_message = "Product listing added successfully! It will be reviewed by our team before becoming visible.";
        } else {
            $error_message = "Error adding product listing. Please try again.";
        }
    }
}

// Get product categories for dropdown
$sql = "SELECT category_id, name FROM categories WHERE is_active = 1 ORDER BY name";
$categories = $conn->query($sql);

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
                <div class="card-header bg-success text-white">
                    <h4>Add New Product Listing</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success">
                            <?php echo $success_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger">
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">
                        <div class="form-group row">
                            <label for="name" class="col-sm-3 col-form-label">Product Name *</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="category" class="col-sm-3 col-form-label">Category *</label>
                            <div class="col-sm-9">
                                <select class="form-control" id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php while ($category = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $category['category_id']; ?>">
                                            <?php echo $category['name']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="description" class="col-sm-3 col-form-label">Description *</label>
                            <div class="col-sm-9">
                                <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
                                <small class="form-text text-muted">
                                    Provide detailed information about the product, including features, specifications, usage instructions, etc.
                                </small>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="price" class="col-sm-3 col-form-label">Price (KES) *</label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" required>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="stock_quantity" class="col-sm-3 col-form-label">Stock Quantity *</label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" min="0" value="1" required>
                                <small class="form-text text-muted">
                                    Number of units available for sale
                                </small>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="images" class="col-sm-3 col-form-label">Images *</label>
                            <div class="col-sm-9">
                                <input type="file" class="form-control-file" id="images" name="images[]" accept="image/*" multiple required>
                                <small class="form-text text-muted">
                                    Upload at least one clear image of the product. First image will be the main display image.
                                    Maximum 5 images. Supported formats: JPG, PNG, GIF (Max size: 2MB each).
                                </small>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <div class="col-sm-3 col-form-label">Featured Product</div>
                            <div class="col-sm-9">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="featured" name="featured">
                                    <label class="form-check-label" for="featured">
                                        Mark as featured product
                                    </label>
                                    <small class="form-text text-muted">
                                        Featured products get more visibility on the marketplace homepage.
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <div class="col-sm-9 offset-sm-3">
                                <button type="submit" class="btn btn-success">Submit Listing</button>
                                <button type="reset" class="btn btn-secondary">Reset Form</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer">
                    <small class="text-muted">
                        * Required fields. All listings are subject to review before being published on the marketplace.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>