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

 

// Handle listing status changes
if (isset($_POST['update_approval'])) {
    $item_type = $_POST['item_type'];
    $item_id = $_POST['item_id'];
    $new_status = $_POST['approval_status'];
    
    if ($item_type == 'pet') {
        $query = "UPDATE pets SET approval_status = ? WHERE pet_id = ?";
    } else {
        $query = "UPDATE products SET approval_status = ? WHERE product_id = ?";
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $new_status, $item_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_msg'] = "Listing approval status updated successfully!";
    } else {
        $_SESSION['error_msg'] = "Error updating listing status!";
    }
    
    // Redirect to prevent form resubmission
    header('Location: listings.php' . (isset($_GET['tab']) ? '?tab=' . $_GET['tab'] : ''));
    exit();
}

// Delete listing if requested
if (isset($_GET['delete']) && isset($_GET['type'])) {
    $item_id = $_GET['delete'];
    $item_type = $_GET['type'];
    
    if ($item_type == 'pet') {
        // First delete associated images
        $delete_images = "DELETE FROM images WHERE item_type = 'pet' AND item_id = ?";
        $stmt = $conn->prepare($delete_images);
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        
        // Then delete the pet listing
        $delete_query = "DELETE FROM pets WHERE pet_id = ?";
    } else {
        // First delete associated images
        $delete_images = "DELETE FROM images WHERE item_type = 'product' AND item_id = ?";
        $stmt = $conn->prepare($delete_images);
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        
        // Then delete the product listing
        $delete_query = "DELETE FROM products WHERE product_id = ?";
    }
    
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $item_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_msg'] = ucfirst($item_type) . " listing deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Error deleting " . $item_type . " listing!";
    }
    
    // Redirect to prevent accidental refreshes
    header('Location: listings.php' . (isset($_GET['tab']) ? '?tab=' . $_GET['tab'] : ''));
    exit();
}

// Set active tab
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'pets';

// Set up pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Get search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$approval_filter = isset($_GET['approval']) ? $_GET['approval'] : '';
$featured_filter = isset($_GET['featured']) ? $_GET['featured'] : '';

// Get all categories for the filter dropdown
$categories_query = "SELECT category_id, name FROM categories WHERE parent_id IS NULL ORDER BY name";
$categories_result = $conn->query($categories_query);

// Get pet listings
function get_pet_listings($conn, $search, $category_filter, $status_filter, $approval_filter, $featured_filter, $offset, $records_per_page) {
    // Build the query with potential filters
    $query = "SELECT p.*, c.name as category_name, CONCAT(u.first_name, ' ', u.last_name) as seller_name, sp.business_name, u.email as seller_email, COUNT(i.image_id) as image_count, p.featured
              FROM pets p
              JOIN categories c ON p.category_id = c.category_id
              JOIN seller_profiles sp ON p.seller_id = sp.seller_id
              JOIN users u ON sp.user_id = u.user_id
              LEFT JOIN images i ON i.item_type = 'pet' AND i.item_id = p.pet_id
              WHERE 1=1";
    
    $count_query = "SELECT COUNT(*) as total FROM pets p
                    JOIN categories c ON p.category_id = c.category_id
                    JOIN seller_profiles sp ON p.seller_id = sp.seller_id
                    JOIN users u ON sp.user_id = u.user_id
                    WHERE 1=1";
    
    $params = [];
    $types = "";
    
    // Add search filter
    if (!empty($search)) {
        $query .= " AND (p.name LIKE ? OR p.breed LIKE ? OR p.description LIKE ?)";
        $count_query .= " AND (p.name LIKE ? OR p.breed LIKE ? OR p.description LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= "sss";
        
    }
    
    // Add category filter
    if (!empty($category_filter)) {
        $query .= " AND p.category_id = ?";
        $count_query .= " AND p.category_id = ?";
        $params[] = $category_filter;
        $types .= "i";
    }
    
    // Add status filter
    if (!empty($status_filter)) {
        $query .= " AND p.status = ?";
        $count_query .= " AND p.status = ?";
        $params[] = $status_filter;
        $types .= "s";
    }
    
    // Add approval filter
    if (!empty($approval_filter)) {
        $query .= " AND p.approval_status = ?";
        $count_query .= " AND p.approval_status = ?";
        $params[] = $approval_filter;
        $types .= "s";
    }

       // Add featured filter
    if ($featured_filter !== '') {
        $query .= " AND p.featured = ?";
        $count_query .= " AND p.featured = ?";
        $params[] = (bool)$featured_filter;
        $types .= "i";
    }
    
    // Group by to avoid duplicate rows due to multiple images
    $query .= " GROUP BY p.pet_id ORDER BY p.created_at DESC LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $records_per_page;
    $types .= "ii";
    
    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Count total records for pagination
    $count_stmt = $conn->prepare($count_query);
    if (!empty($params)) {
        // Remove the last two parameters (offset and limit) for count query
        array_pop($params);
        array_pop($params);
        $count_types = substr($types, 0, -2);
        if (!empty($count_types)) {
            $count_stmt->bind_param($count_types, ...$params);
        }
    }
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_records = $count_result->fetch_assoc()['total'];
    
    return [
        'listings' => $result,
        'total' => $total_records
    ];
}

// Get product listings
function get_product_listings($conn, $search, $category_filter, $status_filter, $approval_filter, $featured_filter, $offset, $records_per_page) {
    // Build the query with potential filters
    $query = "SELECT p.*, c.name as category_name, CONCAT(u.first_name, ' ', u.last_name) as seller_name, sp.business_name, u.email as seller_email, COUNT(i.image_id) as image_count, p.featured
              FROM products p
              JOIN categories c ON p.category_id = c.category_id
              JOIN seller_profiles sp ON p.seller_id = sp.seller_id
              JOIN users u ON sp.user_id = u.user_id
              LEFT JOIN images i ON i.item_type = 'product' AND i.item_id = p.product_id
              WHERE 1=1";
    
    $count_query = "SELECT COUNT(*) as total FROM products p
                    JOIN categories c ON p.category_id = c.category_id
                    JOIN seller_profiles sp ON p.seller_id = sp.seller_id
                    JOIN users u ON sp.user_id = u.user_id
                    WHERE 1=1";
    
    $params = [];
    $types = "";
    
    // Add search filter
    if (!empty($search)) {
        $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $count_query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= "ss";
    }
    
    // Add category filter
    if (!empty($category_filter)) {
        $query .= " AND p.category_id = ?";
        $count_query .= " AND p.category_id = ?";
        $params[] = $category_filter;
        $types .= "i";
    }
    
    // Add status filter
    if (!empty($status_filter)) {
        $query .= " AND p.status = ?";
        $count_query .= " AND p.status = ?";
        $params[] = $status_filter;
        $types .= "s";
    }
    
    // Add approval filter
    if (!empty($approval_filter)) {
        $query .= " AND p.approval_status = ?";
        $count_query .= " AND p.approval_status = ?";
        $params[] = $approval_filter;
        $types .= "s";
    }

      // Add featured filter
    if ($featured_filter !== '') {
        $query .= " AND p.featured = ?";
        $count_query .= " AND p.featured = ?";
        $params[] = (bool)$featured_filter;
        $types .= "i";
    }
        
    // Group by to avoid duplicate rows due to multiple images
    $query .= " GROUP BY p.product_id ORDER BY p.created_at DESC LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $records_per_page;
    $types .= "ii";
    
    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Count total records for pagination
    $count_stmt = $conn->prepare($count_query);
    if (!empty($params)) {
        // Remove the last two parameters (offset and limit) for count query
        array_pop($params);
        array_pop($params);
        $count_types = substr($types, 0, -2);
        if (!empty($count_types)) {
            $count_stmt->bind_param($count_types, ...$params);
        }
    }
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_records = $count_result->fetch_assoc()['total'];
    
    return [
        'listings' => $result,
        'total' => $total_records
    ];
}

// Get listings based on active tab
if ($active_tab == 'pets') {
    $listings_data = get_pet_listings($conn, $search, $category_filter, $status_filter, $approval_filter, $featured_filter, $offset, $records_per_page);
    $listings = $listings_data['listings'];
    $total_records = $listings_data['total'];
} else {
    $listings_data = get_product_listings($conn, $search, $category_filter, $status_filter, $approval_filter, $featured_filter, $offset, $records_per_page);
    $listings = $listings_data['listings'];
    $total_records = $listings_data['total'];
}

// Calculate total pages for pagination
$total_pages = ceil($total_records / $records_per_page);

// Include the header
$page_title = "Manage Listings | Jambo Pets Admin";
include_once '../includes/admin_header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>Manage Listings</h6>
                </div>
                
                <div class="card-body">
                    <!-- Display success/error messages -->
                    <?php if(isset($_SESSION['success_msg'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $_SESSION['success_msg'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['success_msg']); ?>
                    <?php endif; ?>
                    
                    <?php if(isset($_SESSION['error_msg'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $_SESSION['error_msg'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error_msg']); ?>
                    <?php endif; ?>
                    
                    <!-- Tabs for pets and products -->
                    <ul class="nav nav-tabs mb-3" id="listingsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link <?= $active_tab == 'pets' ? 'active' : '' ?>" href="?tab=pets">Pet Listings</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link <?= $active_tab == 'products' ? 'active' : '' ?>" href="?tab=products">Product Listings</a>
                        </li>
                    </ul>
                    
                    <!-- Search and filter form -->
                    <form method="GET" class="row g-3 mb-4">
                        <input type="hidden" name="tab" value="<?= $active_tab ?>">
                        
                        <div class="col-md-3">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search listings..." name="search" value="<?= htmlspecialchars($search) ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <select class="form-select" name="category">
                                <option value="">All Categories</option>
                                <?php while($category = $categories_result->fetch_assoc()): ?>
                                    <option value="<?= $category['category_id'] ?>" <?= $category_filter == $category['category_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <select class="form-select" name="status">
                                <option value="">All Status</option>
                                <?php if($active_tab == 'pets'): ?>
                                    <option value="available" <?= $status_filter == 'available' ? 'selected' : '' ?>>Available</option>
                                    <option value="sold" <?= $status_filter == 'sold' ? 'selected' : '' ?>>Sold</option>
                                    <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="inactive" <?= $status_filter == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                <?php else: ?>
                                    <option value="available" <?= $status_filter == 'available' ? 'selected' : '' ?>>Available</option>
                                    <option value="out_of_stock" <?= $status_filter == 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
                                    <option value="inactive" <?= $status_filter == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <select class="form-select" name="approval">
                                <option value="">All Approval Status</option>
                                <option value="pending" <?= $approval_filter == 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="approved" <?= $approval_filter == 'approved' ? 'selected' : '' ?>>Approved</option>
                                <option value="rejected" <?= $approval_filter == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="featured">
                                <option value="">All Listings</option>
                                <option value="1" <?= isset($_GET['featured']) && $_GET['featured'] == '1' ? 'selected' : '' ?>>Featured Only</option>
                                <option value="0" <?= isset($_GET['featured']) && $_GET['featured'] == '0' ? 'selected' : '' ?>>Regular Only</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="?tab=<?= $active_tab ?>" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                    
                    <!-- Listings table -->
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Details</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Seller</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Approval</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Featured</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Price</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date Listed</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($listings->num_rows > 0): ?>
                                    <?php while($item = $listings->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm"><?= htmlspecialchars($item['name']) ?></h6>
                                                        <p class="text-xs text-secondary mb-0">
                                                            <?= htmlspecialchars($item['category_name']) ?>
                                                            <?php if($active_tab == 'pets' && !empty($item['breed'])): ?>
                                                                - <?= htmlspecialchars($item['breed']) ?>
                                                            <?php endif; ?>
                                                        </p>
                                                        <p class="text-xs text-muted mb-0">
                                                            <?= $item['image_count'] ?> Image(s)
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($item['seller_name']) ?></p>
                                                <p class="text-xs text-secondary mb-0">
                                                    <?= !empty($item['business_name']) ? htmlspecialchars($item['business_name']) : 'Individual Seller' ?>
                                                </p>
                                                <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($item['seller_email']) ?></p>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <?php 
                                                $status_class = '';
                                                if($active_tab == 'pets') {
                                                    switch($item['status']) {
                                                        case 'available':
                                                            $status_class = 'bg-success';
                                                            break;
                                                        case 'sold':
                                                            $status_class = 'bg-danger';
                                                            break;
                                                        case 'pending':
                                                            $status_class = 'bg-warning';
                                                            break;
                                                        case 'inactive':
                                                            $status_class = 'bg-secondary';
                                                            break;
                                                    }
                                                } else {
                                                    switch($item['status']) {
                                                        case 'available':
                                                            $status_class = 'bg-success';
                                                            break;
                                                        case 'out_of_stock':
                                                            $status_class = 'bg-warning';
                                                            break;
                                                        case 'inactive':
                                                            $status_class = 'bg-secondary';
                                                            break;
                                                    }
                                                }
                                                ?>
                                                <span class="badge <?= $status_class ?>"><?= ucfirst($item['status']) ?></span>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <?php 
                                                $approval_class = '';
                                                switch($item['approval_status']) {
                                                    case 'pending':
                                                        $approval_class = 'bg-warning';
                                                        break;
                                                    case 'approved':
                                                        $approval_class = 'bg-success';
                                                        break;
                                                    case 'rejected':
                                                        $approval_class = 'bg-danger';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?= $approval_class ?>"><?= ucfirst($item['approval_status']) ?></span>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <?php if($item['featured']): ?>
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="fas fa-star"></i> Featured
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-dark">Regular</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-secondary text-xs font-weight-bold">KSh <?= number_format($item['price'], 2) ?></span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-secondary text-xs font-weight-bold">
                                                    <?= date('M d, Y', strtotime($item['created_at'])) ?>
                                                </span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <div class="btn-group" role="group">
                                                    <!-- View button -->
                                                    <?php if($active_tab == 'pets'): ?>
                                                       <button type="button" class="btn btn-sm btn-info" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#viewModal<?= $active_tab == 'pets' ? $item['pet_id'] : $item['product_id'] ?>" 
                                                                title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <a href="../buyer/product.php?id=<?= $item['product_id'] ?>" class="btn btn-sm btn-info" target="_blank" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Update approval status -->
                                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" 
                                                            data-bs-target="#approvalModal<?= $active_tab == 'pets' ? $item['pet_id'] : $item['product_id'] ?>" title="Change Approval Status">
                                                        <i class="fas fa-check-circle"></i>
                                                    </button>
                                                    
                                                    <!-- Delete button -->
                                                    <a href="?delete=<?= $active_tab == 'pets' ? $item['pet_id'] : $item['product_id'] ?>&type=<?= $active_tab == 'pets' ? 'pet' : 'product' ?>" 
                                                       class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this listing?')" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                                
                                                <!-- Approval Modal -->
                                                <div class="modal fade" id="approvalModal<?= $active_tab == 'pets' ? $item['pet_id'] : $item['product_id'] ?>" tabindex="-1" 
                                                     aria-labelledby="approvalModalLabel<?= $active_tab == 'pets' ? $item['pet_id'] : $item['product_id'] ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="approvalModalLabel<?= $active_tab == 'pets' ? $item['pet_id'] : $item['product_id'] ?>">
                                                                    Update Approval Status
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form method="POST">
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="item_type" value="<?= $active_tab == 'pets' ? 'pet' : 'product' ?>">
                                                                    <input type="hidden" name="item_id" value="<?= $active_tab == 'pets' ? $item['pet_id'] : $item['product_id'] ?>">
                                                                    
                                                                    <div class="mb-3">
                                                                        <label for="approval_status<?= $active_tab == 'pets' ? $item['pet_id'] : $item['product_id'] ?>" class="form-label">
                                                                            Approval Status
                                                                        </label>
                                                                        <select class="form-select" id="approval_status<?= $active_tab == 'pets' ? $item['pet_id'] : $item['product_id'] ?>" 
                                                                                name="approval_status" required>
                                                                            <option value="pending" <?= $item['approval_status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                                                            <option value="approved" <?= $item['approval_status'] == 'approved' ? 'selected' : '' ?>>Approved</option>
                                                                            <option value="rejected" <?= $item['approval_status'] == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                    <button type="submit" name="update_approval" class="btn btn-primary">Save changes</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">No listings found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
<?php 
// Reset the listings result to fetch images for each item
if ($active_tab == 'pets') {
    $listings_data = get_pet_listings($conn, $search, $category_filter, $status_filter, $approval_filter, $featured_filter, $offset, $records_per_page);
    $listings_for_modal = $listings_data['listings'];
} else {
    $listings_data = get_product_listings($conn, $search, $category_filter, $status_filter, $approval_filter, $featured_filter, $offset, $records_per_page);
    $listings_for_modal = $listings_data['listings'];
}

// Generate modals for each item
while($modal_item = $listings_for_modal->fetch_assoc()): 
    // Get status classes (copy from your existing code)
    $status_class = '';
    if($active_tab == 'pets') {
        switch($modal_item['status']) {
            case 'available': $status_class = 'bg-success'; break;
            case 'sold': $status_class = 'bg-danger'; break;
            case 'pending': $status_class = 'bg-warning'; break;
            case 'inactive': $status_class = 'bg-secondary'; break;
        }
    } else {
        switch($modal_item['status']) {
            case 'available': $status_class = 'bg-success'; break;
            case 'out_of_stock': $status_class = 'bg-warning'; break;
            case 'inactive': $status_class = 'bg-secondary'; break;
        }
    }
    
    $approval_class = '';
    switch($modal_item['approval_status']) {
        case 'pending': $approval_class = 'bg-warning'; break;
        case 'approved': $approval_class = 'bg-success'; break;
        case 'rejected': $approval_class = 'bg-danger'; break;
    }
    
    // Get images for this item
    $item_id = $active_tab == 'pets' ? $modal_item['pet_id'] : $modal_item['product_id'];
    $item_type = $active_tab == 'pets' ? 'pet' : 'product';
    $images_query = "SELECT image_path FROM images WHERE item_type = ? AND item_id = ? ORDER BY is_primary DESC";
    $images_stmt = $conn->prepare($images_query);
    $images_stmt->bind_param("si", $item_type, $item_id);
    $images_stmt->execute();
    $images_result = $images_stmt->get_result();
?>

<!-- View Modal -->
<div class="modal fade" id="viewModal<?= $item_id ?>" tabindex="-1" 
     aria-labelledby="viewModalLabel<?= $item_id ?>" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewModalLabel<?= $item_id ?>">
                    <?= $active_tab == 'pets' ? 'Pet' : 'Product' ?> Details - <?= htmlspecialchars($modal_item['name']) ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Images Column -->
                    <div class="col-md-6">
                        <?php if($images_result->num_rows > 0): ?>
                            <div id="carousel<?= $item_id ?>" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner">
                                    <?php 
                                    $first = true;
                                    while($image = $images_result->fetch_assoc()): 
                                    ?>
                                        <div class="carousel-item <?= $first ? 'active' : '' ?>">
                                            <img src="../<?= htmlspecialchars($image['image_path']) ?>" 
                                                 class="d-block w-100" style="height: 300px; object-fit: cover;" 
                                                 alt="<?= htmlspecialchars($modal_item['name']) ?>">
                                        </div>
                                        <?php $first = false; ?>
                                    <?php endwhile; ?>
                                </div>
                                <?php if($modal_item['image_count'] > 1): ?>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#carousel<?= $item_id ?>" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#carousel<?= $item_id ?>" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Next</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center p-4 bg-light rounded">
                                <i class="fas fa-image fa-3x text-muted mb-2"></i>
                                <p class="text-muted">No images available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Details Column -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <strong>Name:</strong> <?= htmlspecialchars($modal_item['name']) ?>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Category:</strong> <?= htmlspecialchars($modal_item['category_name']) ?>
                        </div>
                        
                        <?php if($active_tab == 'pets'): ?>
                            <div class="mb-3">
                                <strong>Breed:</strong> <?= htmlspecialchars($modal_item['breed'] ?? 'Not specified') ?>
                            </div>
                            <?php if(isset($modal_item['age'])): ?>
                            <div class="mb-3">
                                <strong>Age:</strong> <?= htmlspecialchars($modal_item['age'] ?? 'Not specified') ?>
                            </div>
                            <?php endif; ?>
                            <?php if(isset($modal_item['gender'])): ?>
                            <div class="mb-3">
                                <strong>Gender:</strong> <?= htmlspecialchars($modal_item['gender'] ?? 'Not specified') ?>
                            </div>
                            <?php endif; ?>
                            <?php if(isset($modal_item['vaccination_status'])): ?>
                            <div class="mb-3">
                                <strong>Vaccination Status:</strong> 
                                <span class="badge <?= $modal_item['vaccination_status'] ? 'bg-success' : 'bg-warning' ?>">
                                    <?= $modal_item['vaccination_status'] ? 'Vaccinated' : 'Not Vaccinated' ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <strong>Price:</strong> <span class="text-success fw-bold">KSh <?= number_format($modal_item['price'], 2) ?></span>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Status:</strong> 
                            <span class="badge <?= $status_class ?>"><?= ucfirst($modal_item['status']) ?></span>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Approval Status:</strong> 
                            <span class="badge <?= $approval_class ?>"><?= ucfirst($modal_item['approval_status']) ?></span>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Featured:</strong> 
                            <?php if($modal_item['featured']): ?>
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-star"></i> Featured
                                </span>
                            <?php else: ?>
                                <span class="badge bg-light text-dark">Regular</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Date Listed:</strong> <?= date('M d, Y H:i', strtotime($modal_item['created_at'])) ?>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Seller:</strong> <?= htmlspecialchars($modal_item['seller_name']) ?>
                            <?php if(!empty($modal_item['business_name'])): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($modal_item['business_name']) ?></small>
                            <?php endif; ?>
                            <br><small class="text-muted"><?= htmlspecialchars($modal_item['seller_email']) ?></small>
                        </div>
                    </div>
                </div>
                
                <!-- Description -->
                <div class="mt-3">
                    <strong>Description:</strong>
                    <div class="mt-2 p-3 bg-light rounded">
                        <?= nl2br(htmlspecialchars($modal_item['description'] ?? 'No description provided')) ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="../buyer/<?= $active_tab == 'pets' ? 'pet' : 'product' ?>.php?id=<?= $item_id ?>" 
                   class="btn btn-primary" target="_blank">View on Site</a>
            </div>
        </div>
    </div>
</div>

<?php endwhile; ?>

                    <!-- Pagination -->
                    <?php if($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?tab=<?= $active_tab ?>&page=<?= $i ?>&search=<?= htmlspecialchars($search) ?>&category=<?= $category_filter ?>&status=<?= $status_filter ?>&approval=<?= $approval_filter ?>&featured=<?= $featured_filter ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include_once '../includes/admin_footer.php'; ?>