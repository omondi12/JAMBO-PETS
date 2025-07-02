<?php
/**
 * Order Success Page (order_success.php)
 * Displays successful order completion
 */

session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'buyer') {
    header("Location: ../auth/login.php");
    exit();
}

$order_id = $_GET['order_id'] ?? null;
if (!$order_id) {
    header("Location: orders.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get order details
$stmt = $conn->prepare("SELECT o.*, p.status as payment_status, p.payment_method 
                       FROM orders o 
                       JOIN payments p ON o.order_id = p.order_id 
                       WHERE o.order_id = ? AND o.buyer_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error_msg'] = "Order not found!";
    header("Location: orders.php");
    exit();
}

$order = $result->fetch_assoc();

// Get order items
$items_stmt = $conn->prepare("SELECT oi.*, 
                             CASE 
                                 WHEN oi.item_type = 'pet' THEN p.name
                                 WHEN oi.item_type = 'product' THEN pr.name
                             END AS item_name,
                             (SELECT image_path FROM images WHERE item_type = oi.item_type AND item_id = oi.item_id AND is_primary = 1 LIMIT 1) AS primary_image
                             FROM order_items oi
                             LEFT JOIN pets p ON oi.item_id = p.pet_id AND oi.item_type = 'pet'
                             LEFT JOIN products pr ON oi.item_id = pr.product_id AND oi.item_type = 'product'
                             WHERE oi.order_id = ?");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$order_items = $items_result->fetch_all(MYSQLI_ASSOC);

// Get user counts for header
$userId = $_SESSION['user_id'];
$wishlistQuery = "SELECT COUNT(*) as count FROM wishlist_items WHERE user_id = $userId";
$wishlistResult = $conn->query($wishlistQuery);
$wishlistCount = $wishlistResult->fetch_assoc()['count'];

$cartQuery = "SELECT COUNT(*) as count FROM cart_items WHERE user_id = $userId";
$cartResult = $conn->query($cartQuery);
$cartCount = $cartResult->fetch_assoc()['count'];

$page_title = "Order Successful";
include_once '../includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <?php include_once 'sidebar.php'; ?>
        
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="fas fa-check-circle text-success fa-5x mb-3"></i>
                        <h1 class="text-success">Payment Successful!</h1>
                        <p class="lead">Thank you for your purchase. Your order has been confirmed.</p>
                    </div>
                    
                    <?php if (isset($_SESSION['success_msg'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php 
                                echo $_SESSION['success_msg']; 
                                unset($_SESSION['success_msg']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">Order Details</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Order ID:</strong> #<?php echo $order_id; ?></p>
                                    <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($order['order_date'])); ?></p>
                                    <p><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
                                    <p><strong>Payment Status:</strong> <span class="badge bg-success">Completed</span></p>
                                    <p><strong>Order Status:</strong> <span class="badge bg-primary">Confirmed</span></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">What's Next?</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-envelope text-primary me-2"></i> Order confirmation email sent</li>
                                        <li><i class="fas fa-box text-warning me-2"></i> Your order is being prepared</li>
                                        <li><i class="fas fa-truck text-info me-2"></i> You'll receive shipping updates</li>
                                        <li><i class="fas fa-star text-success me-2"></i> Don't forget to rate your purchase</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Order Summary</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order_items as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($item['primary_image'])): ?>
                                                        <img src="../<?php echo htmlspecialchars($item['primary_image']); ?>" alt="<?php echo htmlspecialchars($item['item_name']); ?>" class="img-thumbnail me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="bg-light d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                                            <i class="fas fa-image text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <h6 class="mb-0"><?php echo htmlspecialchars($item['item_name']); ?></h6>
                                                        <small class="text-muted"><?php echo ucfirst($item['item_type']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>KSh <?php echo number_format($item['price_per_unit'], 2); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td class="text-end">KSh <?php echo number_format($item['subtotal'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                        <td class="text-end"><strong>KSh <?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <a href="orders.php" class="btn btn-outline-primary me-2">
                            <i class="fas fa-list me-1"></i> View All Orders
                        </a>
                        <a href="browse.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag me-1"></i> Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>