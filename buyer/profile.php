<?php
// Start the session
session_start();

require_once '../config/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a buyer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'buyer') {
    // Redirect to login page if not logged in or not a buyer
    header("Location: ../auth/login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$success_message = '';
$error_message = '';
$page_title = 'Edit your profile';

// Handle profile update form submission
if (isset($_POST['update_profile'])) {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $county = mysqli_real_escape_string($conn, $_POST['county'] ?? '');
    $address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');
    
    // Update user profile
    $updateQuery = "UPDATE users SET 
                    first_name = '$first_name', 
                    last_name = '$last_name', 
                    email = '$email', 
                    phone = '$phone', 
                    county = '$county', 
                    address = '$address' 
                    WHERE user_id = $userId";
    
    if ($conn->query($updateQuery)) {
        $success_message = "Profile updated successfully!";
    } else {
        $error_message = "Failed to update profile: " . $conn->error;
    }
}

// Handle password change form submission
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate passwords match
    if ($new_password !== $confirm_password) {
        $error_message = "New passwords do not match!";
    } else {
        // Get current password from database
        $passQuery = "SELECT password FROM users WHERE user_id = $userId";
        $passResult = $conn->query($passQuery);
        $userData = $passResult->fetch_assoc();
        
        // Verify current password
        if (password_verify($current_password, $userData['password'])) {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $updatePassQuery = "UPDATE users SET password = '$hashed_password' WHERE user_id = $userId";
            if ($conn->query($updatePassQuery)) {
                $success_message = "Password changed successfully!";
            } else {
                $error_message = "Failed to change password: " . $conn->error;
            }
        } else {
            $error_message = "Current password is incorrect!";
        }
    }
}

// Handle profile photo upload
if (isset($_POST['upload_photo'])) {
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['profile_image']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $file_name = time() . '_' . $_FILES['profile_image']['name'];
            $upload_dir = '../uploads/';
            $upload_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                // Update user profile with new image
                $updateImageQuery = "UPDATE users SET profile_image = '$file_name' WHERE user_id = $userId";
                if ($conn->query($updateImageQuery)) {
                    $success_message = "Profile photo updated successfully!";
                } else {
                    $error_message = "Failed to update profile photo in database: " . $conn->error;
                }
            } else {
                $error_message = "Failed to upload image file!";
            }
        } else {
            $error_message = "Invalid file type. Please upload a JPEG, PNG, or GIF image.";
        }
    } else {
        $error_message = "Please select an image to upload.";
    }
}

// Get user data
$userQuery = "SELECT * FROM users WHERE user_id = $userId";
$userResult = $conn->query($userQuery);
$user_data = $userResult->fetch_assoc();

// Get order count
$orderStmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE buyer_id = ?");
$orderStmt->bind_param("i", $userId);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();
$orderRow = $orderResult->fetch_assoc();
$order_count = $orderRow['count'];

// Get wishlist count
$wishlistStmt = $conn->prepare("SELECT COUNT(*) as count FROM wishlist_items WHERE user_id = ?");
$wishlistStmt->bind_param("i", $userId);
$wishlistStmt->execute();
$wishlistResult = $wishlistStmt->get_result();
$wishlistRow = $wishlistResult->fetch_assoc();
$wishlist_count = $wishlistRow['count'];

// Get cart count
$cartQuery = "SELECT COUNT(*) as count FROM cart_items WHERE user_id = $userId";
$cartResult = $conn->query($cartQuery);
$cartCount = $cartResult->fetch_assoc()['count'];

// Get counties list
$countiesQuery = "SELECT * FROM counties ORDER BY county_name";
$countiesResult = $conn->query($countiesQuery);
$counties = [];
while ($county = $countiesResult->fetch_assoc()) {
    $counties[] = $county;
}

// Get wishlist count
$wishlistQuery = "SELECT COUNT(*) as count FROM wishlist_items WHERE user_id = $userId";
$wishlistResult = $conn->query($wishlistQuery);
$wishlistCount = $wishlistResult->fetch_assoc()['count'];
// Include header
include_once '../includes/header.php';
?>

<div class="container my-4">
    <div class="row">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        
        
        <!-- Main Content -->
        <div class="col-md-9">
            <!-- Alert Messages -->
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success_message; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error_message; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0 text-dark"><?php echo $page_title; ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Profile Photo Section -->
                        <div class="col-md-3 text-center mb-4">
                           <div class="profile-image-container mb-3">
                            <?php if (!empty($user_data['profile_image']) && file_exists('../uploads/' . $user_data['profile_image'])): ?>
                                <img src="../uploads/<?php echo $user_data['profile_image']; ?>" 
                                    alt="Profile Image" 
                                    class="img-thumbnail rounded-circle profile-image profile-img-clickable"
                                    onclick="openLightbox('../uploads/<?php echo $user_data['profile_image']; ?>', '<?php echo htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name'], ENT_QUOTES); ?>')">
                            <?php else: ?>
                                <div class="default-profile-image rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center">
                                    <?php echo strtoupper(substr($user_data['first_name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                            
                            <form method="post" enctype="multipart/form-data" class="mb-4">
                                <div class="custom-file mb-2">
                                    <input type="file" class="custom-file-input" id="profile_image" name="profile_image" required>
                                    <label class="custom-file-label" for="profile_image">Choose file</label>
                                </div>
                                <button type="submit" name="upload_photo" class="btn btn-outline-primary btn-sm btn-block">
                                    <i class="fas fa-camera"></i> Update Photo
                                </button>
                            </form>
                            
                            <div class="user-stats">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="stat-box p-2 border rounded">
                                            <h5><?php echo $order_count; ?></h5>
                                            <small class="text-muted">Orders</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-box p-2 border rounded">
                                            <h5><?php echo $wishlist_count; ?></h5>
                                            <small class="text-muted">Wishlist</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Profile Details Section -->
                        <div class="col-md-9">
                            <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="profile-tab" data-toggle="tab" href="#profile" role="tab">
                                        <i class="fas fa-user"></i> Profile Details
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="password-tab" data-toggle="tab" href="#password" role="tab">
                                        <i class="fas fa-lock"></i> Change Password
                                    </a>
                                </li>
                            </ul>
                            
                            <div class="tab-content p-3 border border-top-0 rounded-bottom" id="profileTabsContent">
                                <!-- Profile Tab -->
                                <div class="tab-pane fade show active" id="profile" role="tabpanel">
                                    <form method="post" action="">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="first_name">First Name</label>
                                                    <input type="text" class="form-control" id="first_name" name="first_name" 
                                                           value="<?php echo htmlspecialchars($user_data['first_name']); ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="last_name">Last Name</label>
                                                    <input type="text" class="form-control" id="last_name" name="last_name" 
                                                           value="<?php echo htmlspecialchars($user_data['last_name']); ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="email">Email Address</label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="phone">Phone Number</label>
                                            <input type="tel" class="form-control" id="phone" name="phone" 
                                                   value="<?php echo htmlspecialchars($user_data['phone']); ?>" required>
                                            <small class="form-text text-muted">Format: 07XXXXXXXX or +254XXXXXXXXX</small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="county">County</label>
                                            <select class="form-control" id="county" name="county">
                                                <option value="">-- Select County --</option>
                                                <?php foreach ($counties as $county): ?>
                                                    <option value="<?php echo htmlspecialchars($county['county_name']); ?>" 
                                                        <?php echo ($user_data['county'] == $county['county_name']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($county['county_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="address">Shipping Address</label>
                                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($user_data['address'] ?? ''); ?></textarea>
                                            <small class="form-text text-muted">This address will be used for shipping your orders.</small>
                                        </div>
                                        
                                        <button type="submit" name="update_profile" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Save Changes
                                        </button>
                                    </form>
                                </div>
                                
                                <!-- Password Tab -->
                                <div class="tab-pane fade" id="password" role="tabpanel">
                                    <form method="post" action="">
                                        <div class="form-group">
                                            <label for="current_password">Current Password</label>
                                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="new_password">New Password</label>
                                            <input type="password" class="form-control" id="new_password" name="new_password" 
                                                   minlength="8" required>
                                            <small class="form-text text-muted">Password must be at least 8 characters long.</small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="confirm_password">Confirm New Password</label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                                   minlength="8" required>
                                        </div>
                                        
                                        <button type="submit" name="change_password" class="btn btn-primary">
                                            <i class="fas fa-key"></i> Change Password
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Account Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Account Type:</strong> <?php echo ucfirst($user_data['user_type']); ?></p>
                            <p><strong>Account Status:</strong> 
                                <span class="badge badge-<?php echo ($user_data['status'] == 'active') ? 'success' : 'danger'; ?>">
                                    <?php echo ucfirst($user_data['status']); ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Member Since:</strong> <?php echo date('F d, Y', strtotime($user_data['created_at'])); ?></p>
                            <p><strong>Last Updated:</strong> <?php echo date('F d, Y', strtotime($user_data['updated_at'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .profile-image-container {
        width: 150px;
        height: 150px;
        margin: 0 auto;
        overflow: hidden;
    }
    
    .profile-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .default-profile-image {
        width: 150px;
        height: 150px;
        font-size: 60px;
        margin: 0 auto;
    }
    
    .stat-box {
        background-color: #f8f9fa;
        text-align: center;
    }
    
    .stat-box h5 {
        margin-bottom: 0;
        font-weight: bold;
    }
</style>

<script>
    // Display selected filename in the custom file input
    document.querySelector('.custom-file-input').addEventListener('change', function(e) {
        const fileName = e.target.files[0].name;
        const nextSibling = e.target.nextElementSibling;
        nextSibling.innerText = fileName;
    });
</script>

<?php include_once '../includes/footer.php'; ?>