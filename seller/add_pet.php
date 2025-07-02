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
    $breed = trim($_POST['breed']);
    $age = trim($_POST['age']);
    $gender = $_POST['gender'];
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $quantity = (int)$_POST['quantity'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    // Validate form data
    if (empty($name) || empty($description) || $price <= 0 || $category_id <= 0) {
        $error_message = "Please fill in all required fields.";
    } else {
        // Insert pet into database
        $sql = "INSERT INTO pets (seller_id, category_id, name, breed, age, gender, description, price, quantity, featured) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iissssidii", $seller_id, $category_id, $name, $breed, $age, $gender, $description, $price, $quantity, $featured);
        
        if ($stmt->execute()) {
            $pet_id = $stmt->insert_id;
            
            // Process image uploads
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                $upload_dir = "../uploads/pets/";
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
                            $image_path = "uploads/pets/" . $file_name;
                            
                            $sql = "INSERT INTO images (item_type, item_id, image_path, is_primary) 
                                    VALUES ('pet', ?, ?, ?)";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("isi", $pet_id, $image_path, $is_primary);
                            $stmt->execute();
                        }
                    }
                }
            }
            
            $success_message = "Pet listing added successfully! It will be reviewed by our team before becoming visible.";
        } else {
            $error_message = "Error adding pet listing. Please try again.";
        }
    }
}

// Get pet categories for dropdown
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
                <div class="card-header bg-primary text-white">
                    <h4>Add New Pet Listing</h4>
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
                            <label for="name" class="col-sm-3 col-form-label">Pet Name *</label>
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
                            <label for="breed" class="col-sm-3 col-form-label">Breed</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="breed" name="breed">
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="age" class="col-sm-3 col-form-label">Age</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="age" name="age" placeholder="e.g. 2 months, 1 year">
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="gender" class="col-sm-3 col-form-label">Gender</label>
                            <div class="col-sm-9">
                                <select class="form-control" id="gender" name="gender">
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="unknown">Unknown</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="description" class="col-sm-3 col-form-label">Description *</label>
                            <div class="col-sm-9">
                                <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
                                <small class="form-text text-muted">
                                    Provide detailed information about the pet, including temperament, health status, vaccinations, etc.
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
                            <label for="quantity" class="col-sm-3 col-form-label">Quantity *</label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="1" required>
                                <small class="form-text text-muted">
                                    Number of pets available (if selling multiple identical pets, e.g., puppies from same litter)
                                </small>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="images" class="col-sm-3 col-form-label">Images *</label>
                            <div class="col-sm-9">
                                <input type="file" class="form-control-file" id="images" name="images[]" accept="image/*" multiple required>
                                <small class="form-text text-muted">
                                    Upload at least one clear image of the pet. First image will be the main display image.
                                    Maximum 5 images. Supported formats: JPG, PNG, GIF (Max size: 2MB each).
                                </small>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="featured" class="col-sm-3 col-form-label">Featured Listing</label>
                            <div class="col-sm-9">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="featured" name="featured">
                                    <label class="form-check-label" for="featured">Make this listing featured</label>
                                    <small class="form-text text-muted">
                                        Featured listings are highlighted on the homepage and may attract more buyers.
                                        Note: Featured listings may require additional review before approval.
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <div class="col-sm-9 offset-sm-3">
                                <button type="submit" class="btn btn-primary">Submit Listing</button>
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