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
?><?php
 
require_once '../includes/functions.php';
 

// Handle user status changes if form is submitted
if (isset($_POST['update_status'])) {
    $user_id = $_POST['user_id'];
    $new_status = $_POST['status'];
    
    $query = "UPDATE users SET status = ? WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $new_status, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_msg'] = "User status updated successfully!";
    } else {
        $_SESSION['error_msg'] = "Error updating user status!";
    }
    
    // Redirect to prevent form resubmission
    header('Location: users.php');
    exit();
}

// Delete user if requested
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $user_id = $_GET['delete'];
    
    // First check if this is the last admin
    $check_query = "SELECT COUNT(*) as admin_count FROM users WHERE user_type = 'admin'";
    $check_result = $conn->query($check_query);
    $admin_count = $check_result->fetch_assoc()['admin_count'];
    
    $user_query = "SELECT user_type FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user['user_type'] == 'admin' && $admin_count <= 1) {
        $_SESSION['error_msg'] = "Cannot delete the last admin account!";
    } else {
        // Delete the user
        $delete_query = "DELETE FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "User deleted successfully!";
        } else {
            $_SESSION['error_msg'] = "Error deleting user!";
        }
    }
    
    // Redirect to prevent accidental refreshes from deleting more users
    header('Location: users.php');
    exit();
}

// Set up pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Get search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$user_type_filter = isset($_GET['user_type']) ? $_GET['user_type'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build the query with potential filters
$query = "SELECT * FROM users WHERE 1=1";
$count_query = "SELECT COUNT(*) as total FROM users WHERE 1=1";

$params = [];
$types = "";

if (!empty($search)) {
    $search_term = "%$search%";
    $query .= " AND (email LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR phone LIKE ?)";
    $count_query .= " AND (email LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR phone LIKE ?)";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ssss";
}

if (!empty($user_type_filter)) {
    $query .= " AND user_type = ?";
    $count_query .= " AND user_type = ?";
    $params[] = $user_type_filter;
    $types .= "s";
}

if (!empty($status_filter)) {
    $query .= " AND status = ?";
    $count_query .= " AND status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

// Add pagination
$query .= " ORDER BY created_at DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $records_per_page;
$types .= "ii";

// Debug output (remove in production)
// echo "<pre>Query: $query<br>";
// echo "Types: $types<br>";
// print_r($params);
// echo "</pre>";

// Prepare and execute the count query
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $count_types = substr($types, 0, -2); // Remove the "ii" for pagination
    $count_params = array_slice($params, 0, -2); // Remove offset and limit
    
    if (!empty($count_params)) {
        $count_stmt->bind_param($count_types, ...$count_params);
    }
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Prepare and execute the main query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Store users array for later use in modals
$users = [];
while ($user = $result->fetch_assoc()) {
    $users[] = $user;
}

$page_title = "Manage Users - Admin Dashboard";
include_once '../includes/admin_header.php';
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Users</h1>
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-download fa-sm text-white-50"></i> Export User List
        </a>
    </div>

    <!-- Display success/error messages if any -->
    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success_msg']; 
            unset($_SESSION['success_msg']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['error_msg']; 
            unset($_SESSION['error_msg']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Search and Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Search & Filter Users</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="users.php" class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <select name="user_type" class="form-select">
                        <option value="">All User Types</option>
                        <option value="buyer" <?php echo $user_type_filter == 'buyer' ? 'selected' : ''; ?>>Buyers</option>
                        <option value="seller" <?php echo $user_type_filter == 'seller' ? 'selected' : ''; ?>>Sellers</option>
                        <option value="admin" <?php echo $user_type_filter == 'admin' ? 'selected' : ''; ?>>Admins</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        <option value="suspended" <?php echo $status_filter == 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <?php if (!empty($search) || !empty($user_type_filter) || !empty($status_filter)): ?>
                        <a href="users.php" class="btn btn-secondary ms-2">Clear Filters</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">User Accounts (<?php echo $total_records; ?> total)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="usersTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Type</th>
                            <th>County</th>
                            <th>Joined</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['user_id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                    <td>
                                        <?php if ($user['user_type'] == 'buyer'): ?>
                                            <span class="badge bg-primary text-white">Buyer</span>
                                        <?php elseif ($user['user_type'] == 'seller'): ?>
                                            <span class="badge bg-success text-white">Seller</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger text-white">Admin</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo !empty($user['county']) ? htmlspecialchars($user['county']) : 'N/A'; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <?php if ($user['status'] == 'active'): ?>
                                            <span class="badge bg-success text-white">Active</span>
                                        <?php elseif ($user['status'] == 'inactive'): ?>
                                            <span class="badge bg-secondary text-white">Inactive</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger text-white">Suspended</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#viewUserModal<?php echo $user['user_id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#changeStatusModal<?php echo $user['user_id']; ?>">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                            <?php if ($user['user_type'] != 'admin' || $_SESSION['user_id'] != $user['user_id']): ?>
                                                <a href="users.php?delete=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">No users found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($user_type_filter) ? '&user_type=' . urlencode($user_type_filter) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($user_type_filter) ? '&user_type=' . urlencode($user_type_filter) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($user_type_filter) ? '&user_type=' . urlencode($user_type_filter) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- User Modals -->
<?php foreach ($users as $user): ?>
    <!-- View User Modal -->
    <div class="modal fade" id="viewUserModal<?php echo $user['user_id']; ?>" tabindex="-1" aria-labelledby="viewUserModalLabel<?php echo $user['user_id']; ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewUserModalLabel<?php echo $user['user_id']; ?>">User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            <?php if (!empty($user['profile_image'])): ?>
                                <img src="<?php echo '../uploads/' . $user['profile_image']; ?>" 
                                    class="img-fluid rounded-circle profile-img-clickable" 
                                    style="width: 150px; height: 150px; object-fit: cover;"
                                    onclick="openLightbox('<?php echo '../uploads/' . $user['profile_image']; ?>', '<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'], ENT_QUOTES); ?>')">
                            <?php else: ?>
                                <img src="../assets/images/default-profile.png" class="img-fluid rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <h4><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                            <p class="text-muted">
                                <?php if ($user['user_type'] == 'buyer'): ?>
                                    <span class="badge bg-primary text-white">Buyer</span>
                                <?php elseif ($user['user_type'] == 'seller'): ?>
                                    <span class="badge bg-success text-white">Seller</span>
                                <?php else: ?>
                                    <span class="badge bg-danger text-white">Admin</span>
                                <?php endif; ?>
                                
                                <?php if ($user['status'] == 'active'): ?>
                                    <span class="badge bg-success text-white">Active</span>
                                <?php elseif ($user['status'] == 'inactive'): ?>
                                    <span class="badge bg-secondary text-white">Inactive</span>
                                <?php else: ?>
                                    <span class="badge bg-danger text-white">Suspended</span>
                                <?php endif; ?>
                            </p>
                            <p><i class="fas fa-envelope me-2"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                            <p><i class="fas fa-phone me-2"></i> <?php echo htmlspecialchars($user['phone']); ?></p>
                            <?php if (!empty($user['county'])): ?>
                                <p><i class="fas fa-map-marker-alt me-2"></i> <?php echo htmlspecialchars($user['county']); ?></p>
                            <?php endif; ?>
                            <p><i class="fas fa-clock me-2"></i> Joined: <?php echo date('F d, Y', strtotime($user['created_at'])); ?></p>
                        </div>
                    </div>
                    
                    <?php if (!empty($user['address'])): ?>
                        <div class="mt-3">
                            <h5>Address</h5>
                            <p><?php echo nl2br(htmlspecialchars($user['address'])); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($user['user_type'] == 'seller'): 
                        // Get seller profile details
                        $seller_query = "SELECT * FROM seller_profiles WHERE user_id = ?";
                        $seller_stmt = $conn->prepare($seller_query);
                        $seller_stmt->bind_param("i", $user['user_id']);
                        $seller_stmt->execute();
                        $seller_result = $seller_stmt->get_result();
                        $seller = $seller_result->fetch_assoc();
                        
                        if ($seller): ?>
                            <div class="mt-3">
                                <h5>Seller Information</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Business Name:</strong> <?php echo !empty($seller['business_name']) ? htmlspecialchars($seller['business_name']) : 'N/A'; ?></p>
                                        <p><strong>ID Number:</strong> <?php echo !empty($seller['id_number']) ? htmlspecialchars($seller['id_number']) : 'N/A'; ?></p>
                                        <p><strong>Verification Status:</strong> 
                                            <?php if ($seller['verification_status'] == 'verified'): ?>
                                                <span class="badge bg-success text-white">Verified</span>
                                            <?php elseif ($seller['verification_status'] == 'pending'): ?>
                                                <span class="badge bg-warning text-dark">Pending Verification</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger text-white">Rejected</span>
                                            <?php endif; ?>
                                        </p>
                                        <p><strong>Rating:</strong> <?php echo $seller['rating']; ?> / 5</p>
                                    </div>
                                    <div class="col-md-6">
                                        <?php if (!empty($seller['business_description'])): ?>
                                            <p><strong>Business Description:</strong><br>
                                            <?php echo nl2br(htmlspecialchars($seller['business_description'])); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Status Modal -->
    <div class="modal fade" id="changeStatusModal<?php echo $user['user_id']; ?>" tabindex="-1" aria-labelledby="changeStatusModalLabel<?php echo $user['user_id']; ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changeStatusModalLabel<?php echo $user['user_id']; ?>">Change User Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="users.php">
                    <div class="modal-body">
                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                        <div class="mb-3">
                            <label for="status<?php echo $user['user_id']; ?>" class="form-label">Select New Status:</label>
                            <select name="status" id="status<?php echo $user['user_id']; ?>" class="form-select">
                                <option value="active" <?php echo $user['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $user['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                <option value="suspended" <?php echo $user['status'] == 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php include_once '../includes/admin_footer.php'; ?>