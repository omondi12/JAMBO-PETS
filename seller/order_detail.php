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

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$order_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// First, let's check what tables exist and get the seller_id
try {
    // Try to get seller_id - adjust this query based on your actual table structure
    $sql = "SELECT seller_id FROM seller_profiles WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $seller_data = $result->fetch_assoc();

    if (!$seller_data) {
        throw new Exception("Seller profile not found");
    }
    
    $seller_id = $seller_data['seller_id'];

} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}

// Get basic order information first
try {
    $sql = "SELECT * FROM orders WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    if (!$order) {
        $_SESSION['error'] = "Order not found.";
        header('Location: dashboard.php');
        exit();
    }

} catch (Exception $e) {
    die("Database Error getting order: " . $e->getMessage());
}

// Get buyer information
try {
    $sql = "SELECT full_name, email, phone FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $order['buyer_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $buyer = $result->fetch_assoc();

} catch (Exception $e) {
    // If we can't get buyer info, set defaults
    $buyer = ['full_name' => 'Unknown', 'email' => 'Unknown', 'phone' => 'Unknown'];
}

// Get order items for this seller
try {
    $sql = "SELECT * FROM order_items WHERE order_id = ? AND seller_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ii", $order_id, $seller_id);
    $stmt->execute();
    $order_items_result = $stmt->get_result();
    
    // Convert to array for easier handling
    $order_items = [];
    while ($row = $order_items_result->fetch_assoc()) {
        $order_items[] = $row;
    }

} catch (Exception $e) {
    die("Database Error getting order items: " . $e->getMessage());
}

// Check if this seller has any items in this order
if (empty($order_items)) {
    $_SESSION['error'] = "You don't have any items in this order.";
    header('Location: dashboard.php');
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $order_item_id = $_POST['order_item_id'];
    
    // Validate status
    $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (in_array($new_status, $valid_statuses)) {
        try {
            $sql = "UPDATE order_items SET status = ? WHERE order_item_id = ? AND seller_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("sii", $new_status, $order_item_id, $seller_id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Order item status updated successfully.";
            } else {
                $_SESSION['error'] = "Failed to update order item status.";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Database Error: " . $e->getMessage();
        }
        
        // Redirect to prevent form resubmission
        header("Location: order_detail.php?id=" . $order_id);
        exit();
    }
}

// Function to get item details
function getItemDetails($conn, $item_type, $item_id) {
    $details = ['name' => 'Unknown Item', 'detail' => '', 'description' => ''];
    
    try {
        if ($item_type == 'pet') {
            $sql = "SELECT name, breed as detail, age as description FROM pets WHERE pet_id = ?";
        } else {
            $sql = "SELECT name, category as detail, description FROM products WHERE product_id = ?";
        }
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $item_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $details = [
                    'name' => $row['name'] ?: 'Unknown Item',
                    'detail' => $row['detail'] ?: '',
                    'description' => $row['description'] ?: ''
                ];
            }
        }
    } catch (Exception $e) {
        // Return defaults if query fails
    }
    
    return $details;
}

// Function to get item image
function getItemImage($conn, $item_type, $item_id) {
    try {
        $sql = "SELECT image_path FROM images WHERE item_type = ? AND item_id = ? AND is_primary = 1 LIMIT 1";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("si", $item_type, $item_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($image = $result->fetch_assoc()) {
                return $image['image_path'];
            }
        }
    } catch (Exception $e) {
        // Return default image if query fails
    }
    return '../assets/images/no-image.jpg';
}

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
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="orders.php">Orders</a></li>
                    <li class="breadcrumb-item active">Order #<?php echo $order['order_id']; ?></li>
                </ol>
            </nav>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>

            <h1>Order Details #<?php echo $order['order_id']; ?></h1>

            <!-- Order Information -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5>Order Information</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Order Date:</strong> <?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></p>
                            <p><strong>Order Status:</strong> 
                                <span class="badge badge-<?php 
                                    switch ($order['status']) {
                                        case 'pending': echo 'warning'; break;
                                        case 'processing': echo 'info'; break;
                                        case 'completed': echo 'success'; break;
                                        case 'cancelled': echo 'danger'; break;
                                        default: echo 'secondary';
                                    }
                                ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </p>
                            <p><strong>Total Amount:</strong> KES <?php echo number_format($order['total_amount'], 2); ?></p>
                            <p><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method'] ?: 'Not specified'); ?></p>
                            <p><strong>Payment Status:</strong> 
                                <span class="badge badge-<?php 
                                    switch ($order['payment_status']) {
                                        case 'paid': echo 'success'; break;
                                        case 'pending': echo 'warning'; break;
                                        case 'failed': echo 'danger'; break;
                                        default: echo 'secondary';
                                    }
                                ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </p>
                            <?php if (!empty($order['transaction_reference'])): ?>
                                <p><strong>Transaction Reference:</strong> <?php echo $order['transaction_reference']; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5>Customer Information</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($buyer['full_name']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($buyer['email']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($buyer['phone'] ?: $order['contact_phone'] ?: 'Not provided'); ?></p>
                            <?php if (!empty($order['shipping_address'])): ?>
                                <p><strong>Shipping Address:</strong><br>
                                    <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($order['shipping_county'])): ?>
                                <p><strong>County:</strong> <?php echo htmlspecialchars($order['shipping_county']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5>Your Items in this Order</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($order_items)): ?>
                        <?php 
                        $seller_total = 0;
                        foreach ($order_items as $item): 
                            $seller_total += $item['subtotal'];
                            $item_details = getItemDetails($conn, $item['item_type'], $item['item_id']);
                            $item_image = getItemImage($conn, $item['item_type'], $item['item_id']);
                        ?>
                            <div class="row border-bottom py-3">
                                <div class="col-md-2">
                                    <img src="<?php echo $item_image; ?>" alt="<?php echo htmlspecialchars($item_details['name']); ?>" 
                                         class="img-fluid rounded" style="max-height: 80px; object-fit: cover;">
                                </div>
                                <div class="col-md-4">
                                    <h6><?php echo htmlspecialchars($item_details['name']); ?></h6>
                                    <small class="text-muted">
                                        <?php echo ucfirst($item['item_type']); ?>
                                        <?php if (!empty($item_details['detail'])): ?>
                                            - <?php echo htmlspecialchars($item_details['detail']); ?>
                                        <?php endif; ?>
                                    </small>
                                    <?php if (!empty($item_details['description']) && $item['item_type'] == 'pet'): ?>
                                        <br><small class="text-muted">Age: <?php echo htmlspecialchars($item_details['description']); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-2">
                                    <strong>Qty: <?php echo $item['quantity']; ?></strong><br>
                                    <small>KES <?php echo number_format($item['price_per_unit'], 2); ?> each</small>
                                </div>
                                <div class="col-md-2">
                                    <strong>KES <?php echo number_format($item['subtotal'], 2); ?></strong>
                                </div>
                                <div class="col-md-2">
                                    <span class="badge badge-<?php 
                                        switch ($item['status']) {
                                            case 'pending': echo 'warning'; break;
                                            case 'processing': echo 'info'; break;
                                            case 'shipped': echo 'primary'; break;
                                            case 'delivered': echo 'success'; break;
                                            case 'cancelled': echo 'danger'; break;
                                            default: echo 'secondary';
                                        }
                                    ?> mb-2">
                                        <?php echo ucfirst($item['status']); ?>
                                    </span>
                                    
                                    <!-- Status Update Form -->
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="order_item_id" value="<?php echo $item['order_item_id']; ?>">
                                        <select name="status" class="form-control form-control-sm mb-1" 
                                                onchange="if(confirm('Update status for this item?')) this.form.submit();">
                                            <option value="pending" <?php echo $item['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="processing" <?php echo $item['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="shipped" <?php echo $item['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="delivered" <?php echo $item['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="cancelled" <?php echo $item['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Seller Total -->
                        <div class="row mt-3">
                            <div class="col-md-8 text-right">
                                <strong>Your Total from this Order:</strong>
                            </div>
                            <div class="col-md-4">
                                <strong>KES <?php echo number_format($seller_total, 2); ?></strong>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-center">No items found for this order.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-4">
                <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
                <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>