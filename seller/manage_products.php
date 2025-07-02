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

// Process product status changes
if (isset($_GET['action']) && isset($_GET['product_id'])) {
    $product_id = (int)$_GET['product_id'];
    $action = $_GET['action'];
    
    // Verify product belongs to seller
    $sql = "SELECT * FROM products WHERE product_id = ? AND seller_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $product_id, $seller_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        if ($action == 'deactivate') {
            $sql = "UPDATE products SET status = 'inactive' WHERE product_id = ?";
        } elseif ($action == 'activate') {
            $sql = "UPDATE products SET status = 'available' WHERE product_id = ?";
        } elseif ($action == 'delete') {
            // First delete associated images
            $sql = "DELETE FROM images WHERE item_type = 'product' AND item_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            
            // Then delete the product listing
            $sql = "DELETE FROM products WHERE product_id = ?";
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        
        // Redirect to avoid form resubmission
        header('Location: manage_products.php?status=success&action=' . $action);
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
          (SELECT image_path FROM images WHERE item_type = 'product' AND item_id = p.product_id AND is_primary = 1 LIMIT 1) as image
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.category_id
          WHERE p.seller_id = ?";

$params = [$seller_id];
$param_types = "i";

// Add search filter if provided
if (!empty($search)) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= "ss";
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
$products = $stmt->get_result();

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
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h4>Manage Product Listings</h4>
                    <a href="add_product.php" class="btn btn-light btn-sm">
                        <i class="fas fa-plus"></i> Add New Product
                    </a>
                </div>
                <div class="card-body">
                    <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                        <div class="alert alert-success">
                            <?php 
                            $action = $_GET['action'] ?? '';
                            switch ($action) {
                                case 'activate':
                                    echo "Product listing has been activated successfully.";
                                    break;
                                case 'deactivate':
                                    echo "Product listing has been deactivated successfully.";
                                    break;
                                case 'delete':
                                    echo "Product listing has been deleted successfully.";
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
                                    <input type="text" class="form-control" placeholder="Search products..." name="search" value="<?php echo htmlspecialchars($search); ?>">
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
                                        <option value="out_of_stock" <?php echo ($status_filter == 'out_of_stock') ? 'selected' : ''; ?>>Out of Stock</option>
                                        <option value="inactive" <?php echo ($status_filter == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="submit">Filter</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <?php if ($products->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($product = $products->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <?php if ($product['image']): ?>
                                                    <img src="<?php echo htmlspecialchars('../'. $product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" width="50" height="50" class="img-thumbnail">
                                                <?php else: ?>
                                                    <img src="../assets/images/no-image.png" alt="No Image" width="50" height="50" class="img-thumbnail">
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                            <td>Kshs <?php echo number_format($product['price'], 2); ?></td>
                                            <td><?php echo $product['stock_quantity']; ?></td>
                                            <td>
                                                <?php 
                                                $status_class = '';
                                                switch ($product['status']) {
                                                    case 'available':
                                                        $status_class = 'badge-success';
                                                        break;
                                                    case 'out_of_stock':
                                                        $status_class = 'badge-warning';
                                                        break;
                                                    case 'inactive':
                                                        $status_class = 'badge-secondary';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst(str_replace('_', ' ', $product['status'])); ?></span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="edit_product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if ($product['status'] == 'inactive'): ?>
                                                        <a href="manage_products.php?action=activate&product_id=<?php echo $product['product_id']; ?>" class="btn btn-success" title="Activate" onclick="return confirm('Are you sure you want to activate this product?');">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="manage_products.php?action=deactivate&product_id=<?php echo $product['product_id']; ?>" class="btn btn-warning" title="Deactivate" onclick="return confirm('Are you sure you want to deactivate this product?');">
                                                            <i class="fas fa-pause"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="manage_products.php?action=delete&product_id=<?php echo $product['product_id']; ?>" class="btn btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this product? This action cannot be undone.');">
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
                        <nav aria-label="Product pagination">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">Previous</a>
                                </li>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <div class="alert alert-info">
                            <p class="mb-0">No products found. <?php echo !empty($search) || !empty($status_filter) ? 'Try adjusting your search criteria.' : ''; ?></p>
                        </div>
                        <div class="text-center mt-4">
                            <a href="add_product.php" class="btn btn-success">
                                <i class="fas fa-plus"></i> Add Your First Product
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>