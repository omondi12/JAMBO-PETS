<?php
// Set page title
$pageTitle = "Buyer Dashboard";
require_once '../includes/functions.php';
// Include header
require_once '../includes/header.php';

// Include database connection
require_once '../config/db.php';

// Check if user is logged in and is a buyer
if (!isLoggedIn() || !isBuyer()) {
    redirect('auth/login.php');
}

// Get user data
$userId = $_SESSION['user_id'];
$userQuery = "SELECT * FROM users WHERE user_id = $userId";
$userResult = $conn->query($userQuery);
$user = $userResult->fetch_assoc();

// Get recent orders
$ordersQuery = "SELECT o.*, COUNT(oi.order_item_id) as item_count 
               FROM orders o 
               JOIN order_items oi ON o.order_id = oi.order_id 
               WHERE o.buyer_id = $userId 
               GROUP BY o.order_id 
               ORDER BY o.order_date DESC 
               LIMIT 5";
$ordersResult = $conn->query($ordersQuery);

// Get wishlist count
$wishlistQuery = "SELECT COUNT(*) as count FROM wishlist_items WHERE user_id = $userId";
$wishlistResult = $conn->query($wishlistQuery);
$wishlistCount = $wishlistResult->fetch_assoc()['count'];

// Get cart count
$cartQuery = "SELECT COUNT(*) as count FROM cart_items WHERE user_id = $userId";
$cartResult = $conn->query($cartQuery);
$cartCount = $cartResult->fetch_assoc()['count'];

// Log activity
logActivity('buyer_dashboard');
?>

<div class="container py-5">
    <di class="row">
   <?php include_once 'sidebar.php';?>
        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Welcome Banner -->
            <div class="card border-0 bg-primary text-white shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="mb-2">Welcome, <?php echo $user['first_name']; ?>!</h2>
                    <p class="mb-0">Find your perfect pet or shop for pet products on Jambo Pets.</p>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="fas fa-clipboard-list text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Orders</h6>
                                    <h3 class="mb-0">
                                        <?php
                                        $totalOrdersQuery = "SELECT COUNT(*) as count FROM orders WHERE buyer_id = $userId";
                                        $totalOrdersResult = $conn->query($totalOrdersQuery);
                                        echo $totalOrdersResult->fetch_assoc()['count'];
                                        ?>
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="fas fa-heart text-warning"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Wishlist</h6>
                                    <h3 class="mb-0"><?php echo $wishlistCount; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="bg-success bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="fas fa-shopping-cart text-success"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Cart Items</h6>
                                    <h3 class="mb-0"><?php echo $cartCount; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Orders</h5>
                        <a href="<?php echo BASE_URL; ?>buyer/orders.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($ordersResult->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($order = $ordersResult->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?php echo $order['order_id']; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                            <td><?php echo $order['item_count']; ?></td>
                                            <td>KES <?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td>
                                                <?php 
                                                $statusClass = '';
                                                switch ($order['status']) {
                                                    case 'pending':
                                                        $statusClass = 'bg-warning';
                                                        break;
                                                    case 'processing':
                                                        $statusClass = 'bg-info';
                                                        break;
                                                    case 'completed':
                                                        $statusClass = 'bg-success';
                                                        break;
                                                    case 'cancelled':
                                                        $statusClass = 'bg-danger';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($order['status']); ?></span>
                                            </td>
                                            <td>
                                                <a href="<?php echo BASE_URL; ?>buyer/order_details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-outline-primary">Details</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <img src="<?php echo BASE_URL; ?>assets/images/empty-order.svg" alt="No Orders" class="img-fluid mb-3" style="max-width: 150px;">
                            <h5>No Orders Yet</h5>
                            <p class="text-muted">You haven't placed any orders yet. Start shopping to find your perfect pet or product!</p>
                            <a href="<?php echo BASE_URL; ?>buyer/browse.php" class="btn btn-primary">Browse Pets</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Featured Pets -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">Featured Pets</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php
                        $featuredPetsQuery = "SELECT p.*, c.name as category_name, 
                                             (SELECT image_path FROM images WHERE item_type = 'pet' AND item_id = p.pet_id AND is_primary = 1 LIMIT 1) as image 
                                             FROM pets p 
                                             JOIN categories c ON p.category_id = c.category_id 
                                             WHERE p.status = 'available' AND p.featured = 1 AND p.approval_status = 'approved' 
                                             LIMIT 3";
                        $featuredPetsResult = $conn->query($featuredPetsQuery);
                        
                        if ($featuredPetsResult->num_rows > 0) {
                            while ($pet = $featuredPetsResult->fetch_assoc()) {
                                ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="position-relative">
                                            <?php if (!empty($pet['image'])): ?>
                                                <img src="<?php echo BASE_URL . '/' . $pet['image']; ?>" class="card-img-top" alt="<?php echo $pet['name']; ?>" style="height: 180px; object-fit: cover;">
                                            <?php else: ?>
                                                <img src="<?php echo BASE_URL; ?>assets/images/pet-placeholder.jpg" class="card-img-top" alt="Pet placeholder" style="height: 180px; object-fit: cover;">
                                            <?php endif; ?>
                                            <span class="position-absolute top-0 start-0 badge bg-primary m-2"><?php echo $pet['category_name']; ?></span>
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo $pet['name']; ?></h5>
                                            <p class="card-text text-muted mb-2"><?php echo $pet['breed']; ?> · <?php echo $pet['age']; ?> · <?php echo ucfirst($pet['gender']); ?></p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold text-primary">KES <?php echo number_format($pet['price'], 2); ?></span>
                                                <a href="<?php echo BASE_URL; ?>buyer/pet.php?id=<?php echo $pet['pet_id']; ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            ?>
                            <div class="col-12 text-center py-4">
                                <p class="text-muted">No featured pets available at the moment.</p>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <div class="text-center mt-3">
                        <a href="<?php echo BASE_URL; ?>buyer/browse.php" class="btn btn-primary">Browse All Pets</a>
                    </div>
                </div>
            </div>
            
            <!-- Featured Products -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">Featured Products</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php
                        $featuredProductsQuery = "SELECT p.*, c.name as category_name, 
                                                (SELECT image_path FROM images WHERE item_type = 'product' AND item_id = p.product_id AND is_primary = 1 LIMIT 1) as image 
                                                FROM products p 
                                                JOIN categories c ON p.category_id = c.category_id 
                                                WHERE p.status = 'available' AND p.featured = 1 AND p.approval_status = 'approved' 
                                                LIMIT 3";
                        $featuredProductsResult = $conn->query($featuredProductsQuery);
                        
                        if ($featuredProductsResult->num_rows > 0) {
                            while ($product = $featuredProductsResult->fetch_assoc()) {
                                ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="position-relative">
                                            <?php if (!empty($product['image'])): ?>
                                                <img src="<?php echo BASE_URL . '/' . $product['image']; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>" style="height: 180px; object-fit: cover;">
                                            <?php else: ?>
                                                <img src="<?php echo BASE_URL; ?>assets/images/product-placeholder.jpg" class="card-img-top" alt="Product placeholder" style="height: 180px; object-fit: cover;">
                                            <?php endif; ?>
                                            <span class="position-absolute top-0 start-0 badge bg-info m-2"><?php echo $product['category_name']; ?></span>
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo $product['name']; ?></h5>
                                            <p class="card-text text-muted mb-2"><?php echo substr($product['description'], 0, 60); ?>...</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold text-primary">KES <?php echo number_format($product['price'], 2); ?></span>
                                                <a href="<?php echo BASE_URL; ?>buyer/product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            ?>
                            <div class="col-12 text-center py-4">
                                <p class="text-muted">No featured products available at the moment.</p>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <div class="text-center mt-3">
                        <a href="<?php echo BASE_URL; ?>buyer/browse.php?type=product" class="btn btn-primary">Browse All Products</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once '../includes/footer.php';
?>