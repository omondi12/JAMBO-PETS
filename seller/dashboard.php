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

// Get seller ID from the session
$user_id = $_SESSION['user_id'];

// Get seller profile information
$sql = "SELECT u.*, sp.* FROM users u 
        INNER JOIN seller_profiles sp ON u.user_id = sp.user_id 
        WHERE u.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$seller = $result->fetch_assoc();
$seller_id = $seller['seller_id'];

// Get total number of active pet listings
$sql = "SELECT COUNT(*) as total_pets FROM pets WHERE seller_id = ? AND status != 'inactive'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$total_pets = $result->fetch_assoc()['total_pets'];

// Get total number of active product listings
$sql = "SELECT COUNT(*) as total_products FROM products WHERE seller_id = ? AND status != 'inactive'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$total_products = $result->fetch_assoc()['total_products'];

// Get pending orders count
$sql = "SELECT COUNT(*) as pending_orders FROM order_items 
        WHERE seller_id = ? AND status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$pending_orders = $result->fetch_assoc()['pending_orders'];

// Get total sales amount
$sql = "SELECT SUM(subtotal) as total_sales FROM order_items 
        WHERE seller_id = ? AND status != 'cancelled'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$total_sales = $result->fetch_assoc()['total_sales'] ?: 0;

// Get recent orders (last 5)
$sql = "SELECT oi.*, o.order_date, o.status as order_status, 
        CASE WHEN oi.item_type = 'pet' THEN p.name WHEN oi.item_type = 'product' THEN pr.name END as item_name
        FROM order_items oi
        INNER JOIN orders o ON oi.order_id = o.order_id
        LEFT JOIN pets p ON oi.item_type = 'pet' AND oi.item_id = p.pet_id
        LEFT JOIN products pr ON oi.item_type = 'product' AND oi.item_id = pr.product_id
        WHERE oi.seller_id = ?
        ORDER BY o.order_date DESC
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$recent_orders = $stmt->get_result();

// Get unread messages count
$sql = "SELECT COUNT(*) as unread_messages FROM messages 
        WHERE receiver_id = ? AND read_status = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$unread_messages = $result->fetch_assoc()['unread_messages'];

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
            <h1>Seller Dashboard</h1>
            <div class="alert alert-<?php echo ($seller['verification_status'] == 'verified') ? 'success' : 'warning'; ?>">
                Verification Status: <?php echo ucfirst($seller['verification_status']); ?>
                <?php if ($seller['verification_status'] != 'verified'): ?>
                    <p>Your account is pending verification. Some features may be limited until verification is complete.</p>
                <?php endif; ?>
            </div>

            <!-- Stats summary -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Active Pets</h5>
                            <p class="card-text display-4"><?php echo $total_pets; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Active Products</h5>
                            <p class="card-text display-4"><?php echo $total_products; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5 class="card-title">Pending Orders</h5>
                            <p class="card-text display-4"><?php echo $pending_orders; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title">Total Sales</h5>
                            <p class="card-text">KES <?php echo number_format($total_sales, 2); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent orders -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5>Recent Orders</h5>
                </div>
                <div class="card-body">
                    <?php if ($recent_orders->num_rows > 0): ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Item</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $order['order_id']; ?></td>
                                        <td><?php echo $order['item_name']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                        <td>KES <?php echo number_format($order['subtotal'], 2); ?></td>
                                        <td>
                                            <span class="badge badge-<?php 
                                                switch ($order['status']) {
                                                    case 'pending': echo 'warning'; break;
                                                    case 'processing': echo 'info'; break;
                                                    case 'shipped': echo 'primary'; break;
                                                    case 'delivered': echo 'success'; break;
                                                    case 'cancelled': echo 'danger'; break;
                                                    default: echo 'secondary';
                                                }
                                            ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="order_detail.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-primary">View</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-center">No recent orders found.</p>
                    <?php endif; ?>
                    <div class="text-right">
                        <a href="orders.php" class="btn btn-outline-primary">View All Orders</a>
                    </div>
                </div>
            </div>

            <!-- Quick actions -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="add_pet.php" class="btn btn-primary mb-2">Add New Pet</a>
                                <a href="add_product.php" class="btn btn-success mb-2">Add New Product</a>
                                <a href="messages.php" class="btn btn-info">
                                    Messages
                                    <?php if ($unread_messages > 0): ?>
                                        <span class="badge badge-light"><?php echo $unread_messages; ?></span>
                                    <?php endif; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5>Account Information</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Business Name:</strong> <?php echo $seller['business_name'] ?: 'Not set'; ?></p>
                            <p><strong>Email:</strong> <?php echo $seller['email']; ?></p>
                            <p><strong>Phone:</strong> <?php echo $seller['phone']; ?></p>
                            <p><strong>County:</strong> <?php echo $seller['county'] ?: 'Not set'; ?></p>
                            <p><strong>Rating:</strong> 
                                <?php 
                                for ($i = 1; $i <= 5; $i++) {
                                    echo ($i <= round($seller['rating'])) ? '★' : '☆';
                                }
                                echo " (" . number_format($seller['rating'], 1) . ")";
                                ?>
                            </p>
                            <a href="profile.php" class="btn btn-outline-secondary">Edit Profile</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>