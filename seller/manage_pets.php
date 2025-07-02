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

// Process pet status changes
if (isset($_GET['action']) && isset($_GET['pet_id'])) {
    $pet_id = (int)$_GET['pet_id'];
    $action = $_GET['action'];
    
    // Verify pet belongs to seller
    $sql = "SELECT * FROM pets WHERE pet_id = ? AND seller_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $pet_id, $seller_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        if ($action == 'deactivate') {
            $sql = "UPDATE pets SET status = 'inactive' WHERE pet_id = ?";
        } elseif ($action == 'activate') {
            $sql = "UPDATE pets SET status = 'available' WHERE pet_id = ?";
        } elseif ($action == 'delete') {
            // First delete associated images
            $sql = "DELETE FROM images WHERE item_type = 'pet' AND item_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $pet_id);
            $stmt->execute();
            
            // Then delete the pet listing
            $sql = "DELETE FROM pets WHERE pet_id = ?";
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $pet_id);
        $stmt->execute();
        
        // Redirect to avoid form resubmission
        header('Location: manage_pets.php?status=success&action=' . $action);
        exit();
    }
}

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Items per page
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build the query
$query = "SELECT p.*, c.name as category_name, 
          (SELECT image_path FROM images WHERE item_type = 'pet' AND item_id = p.pet_id AND is_primary = 1 LIMIT 1) as image
          FROM pets p 
          LEFT JOIN categories c ON p.category_id = c.category_id
          WHERE p.seller_id = ?";

$params = [$seller_id];
$param_types = "i";

// Add search filter if provided
if (!empty($search)) {
    $query .= " AND (p.name LIKE ? OR p.breed LIKE ? OR p.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= "sss";
}

// Add status filter if provided
if (!empty($status_filter)) {
    $query .= " AND p.status = ?";
    $params[] = $status_filter;
    $param_types .= "s";
}

// Count total records for pagination
$count_query = $query;
$stmt = $conn->prepare($count_query);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$total_records = $result->num_rows;
$total_pages = ceil($total_records / $limit);

// Add sorting and limit
$query .= " ORDER BY p.created_at DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $limit;
$param_types .= "ii";

// Execute the query
$stmt = $conn->prepare($query);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$pets = $stmt->get_result();

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
            <div class="card mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4>Manage Pet Listings</h4>
                    <a href="add_pet.php" class="btn btn-light btn-sm">
                        <i class="fas fa-plus"></i> Add New Pet
                    </a>
                </div>
                <div class="card-body">
                    <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                        <div class="alert alert-success">
                            <?php 
                            $action = $_GET['action'] ?? '';
                            switch ($action) {
                                case 'activate':
                                    echo "Pet listing has been activated successfully.";
                                    break;
                                case 'deactivate':
                                    echo "Pet listing has been deactivated successfully.";
                                    break;
                                case 'delete':
                                    echo "Pet listing has been deleted successfully.";
                                    break;
                                default:
                                    echo "Operation completed successfully.";
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Search and filter form -->
                    <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search pets..." name="search" value="<?php echo htmlspecialchars($search); ?>">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <select name="status" class="form-control">
                                        <option value="">All Statuses</option>
                                        <option value="available" <?php echo ($status_filter == 'available') ? 'selected' : ''; ?>>Available</option>
                                        <option value="sold" <?php echo ($status_filter == 'sold') ? 'selected' : ''; ?>>Sold</option>
                                        <option value="pending" <?php echo ($status_filter == 'pending') ? 'selected' : ''; ?>>Pending Approval</option>
                                        <option value="inactive" <?php echo ($status_filter == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="submit">Filter</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <?php if ($pets->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Views</th>
                                        <th>Date Added</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($pet = $pets->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($pet['image'])): ?>
                                                    <img src="../<?php echo $pet['image']; ?>" alt="<?php echo $pet['name']; ?>" class="img-thumbnail" style="width: 60px;">
                                                <?php else: ?>
                                                    <img src="../uploads/placeholder.jpg" alt="No Image" class="img-thumbnail" style="width: 60px;">
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($pet['name']); ?></td>
                                            <td><?php echo htmlspecialchars($pet['category_name']); ?></td>
                                            <td>KES <?php echo number_format($pet['price'], 2); ?></td>
                                            <td>
                                                <span class="badge badge-<?php 
                                                    switch ($pet['status']) {
                                                        case 'available': echo 'success'; break;
                                                        case 'sold': echo 'secondary'; break;
                                                        case 'pending': echo 'warning'; break;
                                                        case 'inactive': echo 'danger'; break;
                                                        default: echo 'primary';
                                                    }
                                                ?>">
                                                    <?php echo ucfirst($pet['status']); ?>
                                                </span>
                                                <?php if ($pet['approval_status'] != 'approved'): ?>
                                                    <span class="badge badge-info">
                                                        <?php echo ucfirst($pet['approval_status']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $pet['views']; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($pet['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="edit_pet.php?id=<?php echo $pet['pet_id']; ?>" class="btn btn-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if ($pet['status'] == 'available' || $pet['status'] == 'pending'): ?>
                                                        <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?action=deactivate&pet_id=<?php echo $pet['pet_id']; ?>" 
                                                           class="btn btn-warning" title="Deactivate"
                                                           onclick="return confirm('Are you sure you want to deactivate this pet listing?');">
                                                            <i class="fas fa-pause"></i>
                                                        </a>
                                                    <?php elseif ($pet['status'] == 'inactive'): ?>
                                                        <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?action=activate&pet_id=<?php echo $pet['pet_id']; ?>" 
                                                           class="btn btn-success" title="Activate"
                                                           onclick="return confirm('Are you sure you want to activate this pet listing?');">
                                                            <i class="fas fa-play"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?action=delete&pet_id=<?php echo $pet['pet_id']; ?>" 
                                                       class="btn btn-danger" title="Delete"
                                                       onclick="return confirm('Are you sure you want to delete this pet listing? This action cannot be undone.');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">
                                                Previous
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">
                                                Next
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            No pet listings found.
                            <?php if (!empty($search) || !empty($status_filter)): ?>
                                <a href="manage_pets.php" class="alert-link">Clear filters</a>
                            <?php else: ?>
                                <a href="add_pet.php" class="alert-link">Add your first pet listing</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>