<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../auth/login.php?message=You must be logged in as admin to access this page');
    exit();
}

// Connect to database
require_once '../config/db.php';

// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);

// Get admin role
$user_id = $_SESSION['user_id'];
$role_query = "SELECT admin_role FROM admin_roles WHERE user_id = ?";
$role_stmt = $conn->prepare($role_query);
$role_stmt->bind_param("i", $user_id);
$role_stmt->execute();
$role_result = $role_stmt->get_result();
$admin_role_data = $role_result->fetch_assoc();
$admin_role = $admin_role_data ? $admin_role_data['admin_role'] : 'user'; // Default to user role if not found

// Define access permissions for each role
$role_permissions = [
    'master' => ['index.php', 'users.php', 'listings.php', 'orders.php', 'approvals.php', 'contact_messages.php', 'analytics.php', 'reports.php', 'profile.php', 'settings.php', 'edit_admin.php', 'admin_actions.php'],
    'product' => ['index.php', 'listings.php', 'orders.php', 'profile.php', 'settings.php'],
    'user' => ['index.php', 'users.php', 'approvals.php', 'contact_messages.php', 'profile.php', 'settings.php']
];

// Check if admin has permission to access this page
if (!in_array($current_page, $role_permissions[$admin_role])) {
    header('Location: index.php?message=You do not have permission to access this page');
    exit();
}

// Close the statement
$role_stmt->close();

// Include header which has session management and authentication
require_once '../includes/admin_header.php';
require_once '../includes/functions.php';

 
// Get admin user data
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE user_id = ? AND user_type = 'admin'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Get admin role from admin_roles table (we'll create this table structure)
$role_query = "SELECT admin_role FROM admin_roles WHERE user_id = ?";
$role_stmt = $conn->prepare($role_query);
$role_stmt->bind_param("i", $user_id);
$role_stmt->execute();
$role_result = $role_stmt->get_result();
$admin_role_data = $role_result->fetch_assoc();
$admin_role = $admin_role_data ? $admin_role_data['admin_role'] : 'unknown';

// Handle form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Get form data
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $county = trim($_POST['county']);
        $address = trim($_POST['address']);
        
        // Validate data
        if (empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
            $error_message = "Please fill all required fields.";
        } else {
            // Check if email already exists (excluding current user)
            $check_email = "SELECT user_id FROM users WHERE email = ? AND user_id != ?";
            $check_stmt = $conn->prepare($check_email);
            $check_stmt->bind_param("si", $email, $user_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $error_message = "Email address already in use by another account.";
            } else {
                // Handle profile image upload
                $profile_image = $admin['profile_image']; // Default to current image
                
                if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
                    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                    $filename = $_FILES['profile_image']['name'];
                    $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    
                    if (in_array($file_ext, $allowed)) {
                        // Create unique filename
                        $new_filename = uniqid('profile_') . '.' . $file_ext;
                        $upload_dir = '../uploads/';
                        
                        // Create directory if it doesn't exist
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        
                        $destination = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $destination)) {
                            // Delete old profile image if it exists
                            if (!empty($admin['profile_image']) && file_exists('../' . $admin['profile_image'])) {
                                unlink('../' . $admin['profile_image']);
                            }
                            
                            $profile_image = '' . $new_filename;
                        } else {
                            $error_message = "Failed to upload image.";
                        }
                    } else {
                        $error_message = "Invalid file format. Please upload JPG, JPEG, PNG or GIF.";
                    }
                }
                
                // If no errors, update profile
                if (empty($error_message)) {
                    $update_query = "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, 
                                    county = ?, address = ?, profile_image = ? WHERE user_id = ?";
                    $update_stmt = $conn->prepare($update_query);
                    $update_stmt->bind_param("sssssssi", $first_name, $last_name, $email, $phone, 
                                            $county, $address, $profile_image, $user_id);
                    
                    if ($update_stmt->execute()) {
                        $success_message = "Profile updated successfully!";
                        // Update session variables
                        $_SESSION['first_name'] = $first_name;
                        $_SESSION['email'] = $email;
                        
                        // Refresh admin data
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $admin = $result->fetch_assoc();
                    } else {
                        $error_message = "Error updating profile: " . $conn->error;
                    }
                }
            }
        }
    } elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate passwords
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_message = "All password fields are required.";
        } elseif ($new_password !== $confirm_password) {
            $error_message = "New passwords do not match.";
        } elseif (strlen($new_password) < 8) {
            $error_message = "Password must be at least 8 characters long.";
        } else {
            // Verify current password
            if (password_verify($current_password, $admin['password'])) {
                // Hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update password in database
                $update_password = "UPDATE users SET password = ? WHERE user_id = ?";
                $password_stmt = $conn->prepare($update_password);
                $password_stmt->bind_param("si", $hashed_password, $user_id);
                
                if ($password_stmt->execute()) {
                    $success_message = "Password changed successfully!";
                } else {
                    $error_message = "Error changing password: " . $conn->error;
                }
            } else {
                $error_message = "Current password is incorrect.";
            }
        }
    }
}

// Map admin roles to readable names
$admin_role_names = [
    'master' => 'Master Admin (Full Access)',
    'product' => 'Product Manager',
    'user' => 'User Manager'
];

// Define access permissions for each role
$role_permissions = [
    'master' => ['All pages and features'],
    'product' => ['Dashboard', 'Listings', 'Orders'],
    'user' => ['Dashboard', 'Contact Messages', 'Approvals', 'Users']
];

?>

<!-- Main Content -->
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Admin Profile</h1>
    
    <!-- Display Messages -->
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Profile Information Card -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Profile Information</h5>
                </div>
                <div class="card-body text-center">
                    <?php if (!empty($admin['profile_image'])): ?>
                        <img src="<?php echo '../uploads/' . $admin['profile_image']; ?>" 
                            alt="Admin Profile" 
                            class="rounded-circle img-fluid mb-3 profile-img-clickable" 
                            style="width: 150px; height: 150px; object-fit: cover;"
                            onclick="openLightbox('<?php echo '../uploads/' . $admin['profile_image']; ?>', '<?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name'], ENT_QUOTES); ?>')">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/150" alt="Admin Profile" class="rounded-circle img-fluid mb-3">
                    <?php endif; ?>
                    
                    <h5 class="my-3"><?php echo $admin['first_name'] . ' ' . $admin['last_name']; ?></h5>
                    <p class="text-muted mb-1"><?php echo isset($admin_role_names[$admin_role]) ? $admin_role_names[$admin_role] : 'Admin'; ?></p>
                    <p class="text-muted mb-4"><?php echo $admin['county'] ? $admin['county'] : 'Location not specified'; ?></p>
                    
                    <div class="d-flex justify-content-center mb-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            Edit Profile
                        </button>
                        <button type="button" class="btn btn-outline-primary ms-1" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                            Change Password
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Admin Role Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Admin Role</h5>
                </div>
                <div class="card-body">
                    <p><strong>Role:</strong> <?php echo isset($admin_role_names[$admin_role]) ? $admin_role_names[$admin_role] : 'Unknown'; ?></p>
                    <p><strong>Access Permissions:</strong></p>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($role_permissions[$admin_role] ?? ['Unknown permissions'] as $permission): ?>
                            <li class="list-group-item"><?php echo $permission; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Profile Details Card -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Account Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-3">
                            <p class="mb-0">Full Name</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0"><?php echo $admin['first_name'] . ' ' . $admin['last_name']; ?></p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-3">
                            <p class="mb-0">Email</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0"><?php echo $admin['email']; ?></p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-3">
                            <p class="mb-0">Phone</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0"><?php echo $admin['phone']; ?></p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-3">
                            <p class="mb-0">County</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0"><?php echo $admin['county'] ? $admin['county'] : 'Not specified'; ?></p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-3">
                            <p class="mb-0">Address</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0"><?php echo $admin['address'] ? $admin['address'] : 'Not specified'; ?></p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-3">
                            <p class="mb-0">Account Status</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="mb-0">
                                <span class="badge <?php echo $admin['status'] === 'active' ? 'bg-success' : ($admin['status'] === 'inactive' ? 'bg-warning' : 'bg-danger'); ?>">
                                    <?php echo ucfirst($admin['status']); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-3">
                            <p class="mb-0">Created On</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0"><?php echo date('F j, Y', strtotime($admin['created_at'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if ($admin_role === 'master'): ?>
            <!-- Admin Management (Only for Master Admin) -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Admin Management</h5>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                        <i class="fas fa-plus"></i> Add New Admin
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="adminDataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Fetch all admin users for master admin
                                $admin_query = "SELECT u.user_id, u.first_name, u.last_name, u.email, u.status, ar.admin_role 
                                                FROM users u 
                                                LEFT JOIN admin_roles ar ON u.user_id = ar.user_id 
                                                WHERE u.user_type = 'admin'";
                                $admin_result = $conn->query($admin_query);
                                
                                while ($admin_user = $admin_result->fetch_assoc()):
                                    $role_name = isset($admin_role_names[$admin_user['admin_role']]) ? 
                                                $admin_role_names[$admin_user['admin_role']] : 'Regular Admin';
                                ?>
                                <tr>
                                    <td><?php echo $admin_user['first_name'] . ' ' . $admin_user['last_name']; ?></td>
                                    <td><?php echo $admin_user['email']; ?></td>
                                    <td><?php echo $role_name; ?></td>
                                    <td>
                                        <span class="badge <?php echo $admin_user['status'] === 'active' ? 'bg-success' : ($admin_user['status'] === 'inactive' ? 'bg-warning' : 'bg-danger'); ?>">
                                            <?php echo ucfirst($admin_user['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-info edit-admin" data-admin-id="<?php echo $admin_user['user_id']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($admin_user['user_id'] != $user_id): // Prevent self-deletion ?>
                                        <a href="#" class="btn btn-sm btn-danger delete-admin" data-admin-id="<?php echo $admin_user['user_id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $admin['first_name']; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $admin['last_name']; ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $admin['email']; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone *</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $admin['phone']; ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="county" class="form-label">County</label>
                            <input type="text" class="form-control" id="county" name="county" value="<?php echo $admin['county']; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="profile_image" class="form-label">Profile Image</label>
                            <input type="file" class="form-control" id="profile_image" name="profile_image">
                            <small class="text-muted">Leave empty to keep current image</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo $admin['address']; ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password *</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password *</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <small class="text-muted">Password must be at least 8 characters long</small>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password *</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($admin_role === 'master'): ?>
<!-- Add Admin Modal -->
<div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAdminModalLabel">Add New Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addAdminForm" action="admin_actions.php" method="POST">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="new_first_name" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="new_first_name" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="new_last_name" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="new_last_name" name="last_name" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="new_email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="new_email" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="new_phone" class="form-label">Phone *</label>
                            <input type="text" class="form-control" id="new_phone" name="phone" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="new_password" class="form-label">Password *</label>
                            <input type="password" class="form-control" id="new_password" name="password" required>
                        </div>
                        <div class="col-md-6">
                            <label for="new_admin_role" class="form-label">Admin Role *</label>
                            <select class="form-select" id="new_admin_role" name="admin_role" required>
                                <option value="">Select Role</option>
                                <option value="master">Master Admin (Full Access)</option>
                                <option value="product">Product Manager</option>
                                <option value="user">User Manager</option>
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="action" value="add_admin">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

 

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($admin_role === 'master'): ?>
    // For master admin - handle admin CRUD operations
    const deleteButtons = document.querySelectorAll('.delete-admin');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const adminId = this.getAttribute('data-admin-id');
            
            if (confirm('Are you sure you want to delete this admin? This action cannot be undone.')) {
                // Send delete request
                const formData = new FormData();
                formData.append('action', 'delete_admin');
                formData.append('admin_id', adminId);
                
                fetch('admin_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Admin deleted successfully!');
                        // Reload page to reflect changes
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            }
        });
    });
    
    // Edit admin functionality would go here (for simplicity, we'll reload the page)
    const editButtons = document.querySelectorAll('.edit-admin');
    editButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const adminId = this.getAttribute('data-admin-id');
            window.location.href = `edit_admin.php?id=${adminId}`;
        });
    });
    <?php endif; ?>
});
</script>

<?php
// Close all prepared statements and database connection
$stmt->close();
$role_stmt->close();
if (isset($check_stmt)) $check_stmt->close();
if (isset($update_stmt)) $update_stmt->close();
if (isset($password_stmt)) $password_stmt->close();
$conn->close();

// Include admin footer
require_once '../includes/admin_footer.php';
?>