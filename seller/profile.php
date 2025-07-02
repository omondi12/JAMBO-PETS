<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'seller') {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get seller details from users table
$user_query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Get seller profile details
$seller_query = "SELECT * FROM seller_profiles WHERE user_id = ?";
$stmt = $conn->prepare($seller_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$seller_result = $stmt->get_result();
$seller = $seller_result->fetch_assoc();

$seller_id = $seller['seller_id'];

// Get seller's overall rating
$rating_query = "SELECT AVG(rating) as average_rating, COUNT(*) as total_reviews 
                FROM reviews 
                WHERE item_type = 'seller' AND item_id = ?";
$stmt = $conn->prepare($rating_query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$rating_result = $stmt->get_result();
$rating_data = $rating_result->fetch_assoc();

// Get counties for dropdown
$counties_query = "SELECT * FROM counties ORDER BY county_name ASC";
$counties_result = $conn->query($counties_query);

// Handle profile update form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Basic profile update
    if (isset($_POST['update_profile'])) {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $county = trim($_POST['county']);
        $address = trim($_POST['address']);
        $business_name = trim($_POST['business_name']);
        $business_description = trim($_POST['business_description']);
        $id_number = trim($_POST['id_number']);
        
        // Validate required fields
        if (empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
            $error_message = "Please fill in all required fields.";
        } else {
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Update user details
                $update_user_query = "UPDATE users SET 
                                    first_name = ?, 
                                    last_name = ?, 
                                    email = ?, 
                                    phone = ?, 
                                    county = ?, 
                                    address = ? 
                                    WHERE user_id = ?";
                $stmt = $conn->prepare($update_user_query);
                $stmt->bind_param("ssssssi", $first_name, $last_name, $email, $phone, $county, $address, $user_id);
                $stmt->execute();
                
                // Update seller profile details
                $update_seller_query = "UPDATE seller_profiles SET 
                                      business_name = ?, 
                                      business_description = ?, 
                                      id_number = ? 
                                      WHERE user_id = ?";
                $stmt = $conn->prepare($update_seller_query);
                $stmt->bind_param("sssi", $business_name, $business_description, $id_number, $user_id);
                $stmt->execute();
                
                // Commit transaction
                $conn->commit();
                
                $success_message = "Profile updated successfully!";
                
                // Refresh user and seller data
                $stmt = $conn->prepare($user_query);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $user_result = $stmt->get_result();
                $user = $user_result->fetch_assoc();
                
                $stmt = $conn->prepare($seller_query);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $seller_result = $stmt->get_result();
                $seller = $seller_result->fetch_assoc();
            } catch (Exception $e) {
                // Rollback on error
                $conn->rollback();
                $error_message = "Error updating profile: " . $e->getMessage();
            }
        }
    }
    
    // Password update
    if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            // Check if new passwords match
            if ($new_password === $confirm_password) {
                // Check password strength
                if (strlen($new_password) >= 8) {
                    // Hash new password and update
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    $update_password_query = "UPDATE users SET password = ? WHERE user_id = ?";
                    $stmt = $conn->prepare($update_password_query);
                    $stmt->bind_param("si", $hashed_password, $user_id);
                    
                    if ($stmt->execute()) {
                        $success_message = "Password updated successfully!";
                    } else {
                        $error_message = "Error updating password. Please try again.";
                    }
                } else {
                    $error_message = "Password must be at least 8 characters long.";
                }
            } else {
                $error_message = "New passwords do not match.";
            }
        } else {
            $error_message = "Current password is incorrect.";
        }
    }
    
    // Profile image update
    if (isset($_POST['update_image'])) {
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
            $max_size = 2 * 1024 * 1024; // 2MB
            
            if (in_array($_FILES['profile_image']['type'], $allowed_types) && $_FILES['profile_image']['size'] <= $max_size) {
                $file_name = 'seller_' . $user_id . '_' . time() . '_' . $_FILES['profile_image']['name'];
                $upload_dir = '../uploads/';
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $upload_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                    // Update profile image in database
                    $update_image_query = "UPDATE users SET profile_image = ? WHERE user_id = ?";
                    $stmt = $conn->prepare($update_image_query);
                    $stmt->bind_param("si", $file_name, $user_id);
                    
                    if ($stmt->execute()) {
                        // Delete old profile image if it exists
                        if (!empty($user['profile_image']) && file_exists($upload_dir . $user['profile_image'])) {
                            unlink($upload_dir . $user['profile_image']);
                        }
                        
                        $success_message = "Profile image updated successfully!";
                        
                        // Refresh user data
                        $stmt = $conn->prepare($user_query);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $user_result = $stmt->get_result();
                        $user = $user_result->fetch_assoc();
                    } else {
                        $error_message = "Error updating profile image in database.";
                    }
                } else {
                    $error_message = "Error uploading image. Please try again.";
                }
            } else {
                $error_message = "Invalid file. Please upload a JPG or PNG image under 2MB.";
            }
        } else {
            $error_message = "Please select an image to upload.";
        }
    }
}

// Include header
include_once '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
    <div class="col-md-3">
            <!-- Seller sidebar -->
            <?php include_once 'seller_sidebar.php'; ?>
        </div>
        
        <main class="col-md-9 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Seller Profile</h1>
            </div>
            
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <!-- Profile Summary Card -->
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                           <div class="mb-3">
                                <?php if ($user['profile_image']): ?>
                                    <img src="../uploads/<?php echo $user['profile_image']; ?>" 
                                        alt="Profile" 
                                        class="rounded-circle img-thumbnail profile-img-clickable" 
                                        style="width: 150px; height: 150px; object-fit: cover;"
                                        onclick="openLightbox('../uploads/<?php echo $user['profile_image']; ?>', '<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'], ENT_QUOTES); ?>')">
                                <?php else: ?>
                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center mx-auto" 
                                        style="width: 150px; height: 150px;">
                                        <h1><?php echo substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1); ?></h1>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <h4><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></h4>
                            <p class="text-muted mb-1">
                                <?php echo !empty($seller['business_name']) ? $seller['business_name'] : "Seller"; ?>
                            </p>
                            
                            <!-- Verification Badge -->
                            <div class="mb-2">
                                <?php if ($seller['verification_status'] == 'verified'): ?>
                                    <span class="badge bg-success">Verified Seller</span>
                                <?php elseif ($seller['verification_status'] == 'pending'): ?>
                                    <span class="badge bg-warning text-dark">Verification Pending</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Not Verified</span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Rating -->
                            <div class="mb-2">
                                <?php
                                $average_rating = round($rating_data['average_rating'] ?? 0, 1);
                                $total_reviews = $rating_data['total_reviews'] ?? 0;
                                ?>
                                <div class="d-flex justify-content-center align-items-center">
                                    <div class="me-2">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= $average_rating): ?>
                                                <i class="bi bi-star-fill text-warning"></i>
                                            <?php elseif ($i - 0.5 <= $average_rating): ?>
                                                <i class="bi bi-star-half text-warning"></i>
                                            <?php else: ?>
                                                <i class="bi bi-star text-warning"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                    <span><?php echo $average_rating; ?> (<?php echo $total_reviews; ?> reviews)</span>
                                </div>
                            </div>
                            
                            <div class="list-group list-group-flush text-start mt-3">
                                <div class="list-group-item">
                                    <i class="bi bi-envelope me-2"></i> <?php echo $user['email']; ?>
                                </div>
                                <div class="list-group-item">
                                    <i class="bi bi-telephone me-2"></i> <?php echo $user['phone']; ?>
                                </div>
                                <?php if ($user['county']): ?>
                                    <div class="list-group-item">
                                        <i class="bi bi-geo-alt me-2"></i> <?php echo $user['county']; ?> County
                                    </div>
                                <?php endif; ?>
                                <div class="list-group-item">
                                    <i class="bi bi-calendar-check me-2"></i> Member since <?php echo date('M Y', strtotime($user['created_at'])); ?>
                                </div>
                            </div>
                            
                            <!-- Upload Profile Image Form -->
                            <form method="post" enctype="multipart/form-data" class="mt-3">
                                <div class="mb-3">
                                    <label for="profile_image" class="form-label">Update Profile Image</label>
                                    <input class="form-control" type="file" id="profile_image" name="profile_image" accept="image/jpeg, image/png">
                                    <div class="form-text">Max size: 2MB. JPG or PNG only.</div>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" name="update_image" class="btn btn-outline-primary">Upload Image</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Profile Editing Forms -->
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Edit Profile</h5>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="first_name" class="form-label">First Name *</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" 
                                               value="<?php echo $user['first_name']; ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="last_name" class="form-label">Last Name *</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" 
                                               value="<?php echo $user['last_name']; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo $user['email']; ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Phone Number *</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="<?php echo $user['phone']; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="business_name" class="form-label">Business Name</label>
                                    <input type="text" class="form-control" id="business_name" name="business_name" 
                                           value="<?php echo $seller['business_name'] ?? ''; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="business_description" class="form-label">Business Description</label>
                                    <textarea class="form-control" id="business_description" name="business_description" 
                                              rows="3"><?php echo $seller['business_description'] ?? ''; ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="id_number" class="form-label">ID Number</label>
                                    <input type="text" class="form-control" id="id_number" name="id_number" 
                                           value="<?php echo $seller['id_number'] ?? ''; ?>">
                                    <div class="form-text">Required for seller verification.</div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="county" class="form-label">County</label>
                                        <select class="form-select" id="county" name="county">
                                            <option value="">Select County</option>
                                            <?php while ($county = $counties_result->fetch_assoc()): ?>
                                                <option value="<?php echo $county['county_name']; ?>" 
                                                        <?php echo ($user['county'] == $county['county_name']) ? 'selected' : ''; ?>>
                                                    <?php echo $county['county_name']; ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" name="address" 
                                              rows="2"><?php echo $user['address'] ?? ''; ?></textarea>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Change Password Form -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Change Password</h5>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password *</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password *</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    <div class="form-text">Password must be at least 8 characters long.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password *</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" name="update_password" class="btn btn-warning">Update Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Seller Statistics Section -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Your Account Statistics</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php
                                // Get total number of pets listed
                                $pets_query = "SELECT COUNT(*) as total_pets FROM pets WHERE seller_id = ?";
                                $stmt = $conn->prepare($pets_query);
                                $stmt->bind_param("i", $seller_id);
                                $stmt->execute();
                                $pets_result = $stmt->get_result();
                                $total_pets = $pets_result->fetch_assoc()['total_pets'];
                                
                                // Get total number of products listed
                                $products_query = "SELECT COUNT(*) as total_products FROM products WHERE seller_id = ?";
                                $stmt = $conn->prepare($products_query);
                                $stmt->bind_param("i", $seller_id);
                                $stmt->execute();
                                $products_result = $stmt->get_result();
                                $total_products = $products_result->fetch_assoc()['total_products'];
                                
                                // Get total number of orders
                                $orders_query = "SELECT COUNT(DISTINCT order_id) as total_orders FROM order_items WHERE seller_id = ?";
                                $stmt = $conn->prepare($orders_query);
                                $stmt->bind_param("i", $seller_id);
                                $stmt->execute();
                                $orders_result = $stmt->get_result();
                                $total_orders = $orders_result->fetch_assoc()['total_orders'];
                                
                                // Get total number of reviews
                                $reviews_query = "SELECT COUNT(*) as total_reviews FROM reviews WHERE item_type = 'seller' AND item_id = ?";
                                $stmt = $conn->prepare($reviews_query);
                                $stmt->bind_param("i", $seller_id);
                                $stmt->execute();
                                $reviews_result = $stmt->get_result();
                                $total_reviews = $reviews_result->fetch_assoc()['total_reviews'];
                                ?>
                                
                                <div class="col-md-3 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h3 class="text-primary"><?php echo $total_pets; ?></h3>
                                            <p class="mb-0">Pets Listed</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h3 class="text-success"><?php echo $total_products; ?></h3>
                                            <p class="mb-0">Products Listed</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h3 class="text-info"><?php echo $total_orders; ?></h3>
                                            <p class="mb-0">Total Orders</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h3 class="text-warning"><?php echo $total_reviews; ?></h3>
                                            <p class="mb-0">Reviews Received</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Preview image before upload
document.getElementById('profile_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const imgElements = document.querySelectorAll('.img-thumbnail');
            if (imgElements.length > 0) {
                imgElements[0].src = event.target.result;
            } else {
                // If no image exists yet, replace the placeholder
                const placeholderDiv = document.querySelector('.rounded-circle.bg-secondary');
                if (placeholderDiv) {
                    const parent = placeholderDiv.parentNode;
                    placeholderDiv.remove();
                    
                    const img = document.createElement('img');
                    img.src = event.target.result;
                    img.alt = 'Profile';
                    img.classList.add('rounded-circle', 'img-thumbnail');
                    img.style.width = '150px';
                    img.style.height = '150px';
                    img.style.objectFit = 'cover';
                    parent.prepend(img);
                }
            }
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php include_once '../includes/footer.php'; ?>