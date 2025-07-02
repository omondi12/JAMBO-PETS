<?php
// Initialize the session
session_start();

// Include database connection
require_once "../config/db.php";
require_once "../includes/functions.php";

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] != "seller") {
    header("location: ../auth/login.php");
    exit;
}

// Get the seller ID
$user_id = $_SESSION["user_id"];

// Get the seller profile information
$stmt = $conn->prepare("SELECT seller_id FROM seller_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Redirect if seller profile doesn't exist
    header("location: ../auth/login.php");
    exit;
}

$seller_data = $result->fetch_assoc();
$seller_id = $seller_data["seller_id"];

// Filter orders by status if provided
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$status_condition = '';

if (!empty($status_filter) && in_array($status_filter, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])) {
    $status_condition = " AND oi.status = '$status_filter'";
}

// Get orders for the seller - IMPROVED QUERY
// First get all order_items for this seller
$query = "SELECT DISTINCT o.order_id, o.order_date, o.total_amount as order_total, o.status as order_status, 
          o.payment_status, o.payment_method, u.first_name, u.last_name, u.phone, u.county, u.address
          FROM order_items oi
          JOIN orders o ON oi.order_id = o.order_id
          JOIN users u ON o.buyer_id = u.user_id
          WHERE oi.seller_id = ? $status_condition
          ORDER BY o.order_date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$orders_result = $stmt->get_result();

// Handle status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $order_item_id = $_POST['order_item_id'];
    $new_status = $_POST['new_status'];
    
    $update_stmt = $conn->prepare("UPDATE order_items SET status = ? WHERE order_item_id = ? AND seller_id = ?");
    $update_stmt->bind_param("sii", $new_status, $order_item_id, $seller_id);
    
    if ($update_stmt->execute()) {
        $_SESSION['success_msg'] = "Order status updated successfully!";
    } else {
        $_SESSION['error_msg'] = "Error updating order status.";
    }
    
    // Redirect to refresh the page and avoid form resubmission
    header("Location: orders.php" . (!empty($status_filter) ? "?status=$status_filter" : ""));
    exit;
}

$page_title = "Manage Orders";
include_once "../includes/header.php";

// Helper functions
function getOrderStatusBadgeClass($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'processing':
            return 'info';
        case 'shipped':
            return 'primary';
        case 'delivered':
            return 'success';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}

function getPaymentStatusBadgeClass($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'paid':
            return 'success';
        case 'failed':
            return 'danger';
        default:
            return 'secondary';
    }
}

// Store orders in array for later use in modals
$orders_array = [];
if ($orders_result->num_rows > 0) {
    while ($order = $orders_result->fetch_assoc()) {
        $orders_array[] = $order;
    }
}
?>

<style>
.modal-backdrop {
    z-index: 1040;
}

.modal {
    z-index: 1050;
}

.modal select {
    -webkit-appearance: menulist;
    -moz-appearance: menulist;
    appearance: menulist;
}

.view-order-details {
    cursor: pointer;
}

.update-status-btn {
    cursor: pointer;
}

/* Ensure form controls are properly styled */
.modal .form-control {
    display: block;
    width: 100%;
    height: calc(1.5em + .75rem + 2px);
    padding: .375rem .75rem;
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    border-radius: .25rem;
}

.accordion .card {
    border: 1px solid #dee2e6;
}

.accordion .card-header {
    background-color: #f8f9fa;
}

.btn-link {
    text-decoration: none;
}

.btn-link:hover {
    text-decoration: none;
}
</style>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
                <!-- Seller sidebar -->
                <?php include_once 'seller_sidebar.php'; ?>
            </div>
            <div class="col-lg-9">
                <h1 class="mt-4 mb-4">Manage Orders</h1>
        
                <?php if (isset($_SESSION['success_msg'])): ?>
                    <div class="alert alert-success">
                        <?php 
                            echo $_SESSION['success_msg']; 
                            unset($_SESSION['success_msg']);
                        ?>
                    </div>
                <?php endif; ?>
        
                <?php if (isset($_SESSION['error_msg'])): ?>
                    <div class="alert alert-danger">
                        <?php 
                            echo $_SESSION['error_msg']; 
                            unset($_SESSION['error_msg']);
                        ?>
                    </div>
                <?php endif; ?>

                <!-- Filter options -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Filter Orders</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <form action="" method="get" class="form-inline">
                                    <div class="form-group mr-2">
                                        <label for="status" class="mr-2">Status:</label>
                                        <select name="status" id="status" class="form-control" onchange="this.form.submit()">
                                            <option value="">All Orders</option>
                                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="processing" <?php echo $status_filter == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="shipped" <?php echo $status_filter == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="delivered" <?php echo $status_filter == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (count($orders_array) > 0): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Orders</h5>
                        </div>
                        <div class="card-body">
                            <div class="accordion" id="ordersAccordion">
                                <?php foreach ($orders_array as $order): ?>
                                    <div class="card mb-2">
                                        <div class="card-header" id="heading<?php echo $order['order_id']; ?>">
                                            <div class="row">
                                                <div class="col-md-10">
                                                    <button class="btn btn-link text-left" type="button" data-toggle="collapse" 
                                                            data-target="#collapse<?php echo $order['order_id']; ?>" 
                                                            aria-expanded="false" aria-controls="collapse<?php echo $order['order_id']; ?>">
                                                        <strong>Order #<?php echo $order['order_id']; ?></strong> - 
                                                        <?php echo date('M d, Y', strtotime($order['order_date'])); ?> - 
                                                        <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?> - 
                                                        KSh <?php echo number_format($order['order_total'], 2); ?> - 
                                                        <span class="badge badge-<?php echo getOrderStatusBadgeClass($order['order_status']); ?>">
                                                            <?php echo ucfirst($order['order_status']); ?>
                                                        </span>
                                                    </button>
                                                </div>
                                                <div class="col-md-2 text-right">
                                                     <button class="btn btn-link text-left bg-info btn-sm" type="button" data-toggle="collapse" 
                                                            data-target="#collapse<?php echo $order['order_id']; ?>" 
                                                            aria-expanded="false" aria-controls="collapse<?php echo $order['order_id']; ?>">
                                                        <i class="fa fa-eye"></i> View
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="collapse<?php echo $order['order_id']; ?>" class="collapse" 
                                             aria-labelledby="heading<?php echo $order['order_id']; ?>" 
                                             data-parent="#ordersAccordion">
                                            <div class="card-body">
                                                <!-- Customer Information -->
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <h6>Customer Information</h6>
                                                        <p><strong>Name:</strong> <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
                                                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                                                        <p><strong>County:</strong> <?php echo htmlspecialchars($order['county']); ?></p>
                                                        <p><strong>Address:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6>Order Information</h6>
                                                        <p><strong>Order Date:</strong> <?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></p>
                                                        <p><strong>Order Status:</strong> <?php echo ucfirst($order['order_status']); ?></p>
                                                        <p><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></p>
                                                        <p><strong>Payment Status:</strong> <?php echo ucfirst($order['payment_status']); ?></p>
                                                    </div>
                                                </div>
                                        
                                                <!-- Order Items Table -->
                                                <h6>Order Items</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-striped table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Item</th>
                                                                <th>Type</th>
                                                                <th>Quantity</th>
                                                                <th>Price</th>
                                                                <th>Subtotal</th>
                                                                <th>Status</th>
                                                                <th>Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php 
                                                            // Query to get all items for this specific order that belong to this seller
                                                            $items_query = "SELECT oi.order_item_id, oi.item_type, oi.item_id, oi.quantity, 
                                                                           oi.price_per_unit, oi.subtotal, oi.status
                                                                           FROM order_items oi
                                                                           WHERE oi.order_id = ? AND oi.seller_id = ?";
                                                            $items_stmt = $conn->prepare($items_query);
                                                            $items_stmt->bind_param("ii", $order['order_id'], $seller_id);
                                                            $items_stmt->execute();
                                                            $items_result = $items_stmt->get_result();
                                                    
                                                            while ($item = $items_result->fetch_assoc()):
                                                            ?>
                                                            <tr>
                                                                <td>
                                                                    <?php 
                                                                    if ($item['item_type'] == 'pet') {
                                                                        // Get pet details
                                                                        $pet_stmt = $conn->prepare("SELECT name, breed FROM pets WHERE pet_id = ?");
                                                                        $pet_stmt->bind_param("i", $item['item_id']);
                                                                        $pet_stmt->execute();
                                                                        $pet_result = $pet_stmt->get_result();
                                                                        if ($pet_data = $pet_result->fetch_assoc()) {
                                                                            echo htmlspecialchars($pet_data['name']) . " (" . htmlspecialchars($pet_data['breed']) . ")";
                                                                        } else {
                                                                            echo "Pet #" . $item['item_id'];
                                                                        }
                                                                    } else {
                                                                        // Get product details
                                                                        $product_stmt = $conn->prepare("SELECT name FROM products WHERE product_id = ?");
                                                                        $product_stmt->bind_param("i", $item['item_id']);
                                                                        $product_stmt->execute();
                                                                        $product_result = $product_stmt->get_result();
                                                                        if ($product_data = $product_result->fetch_assoc()) {
                                                                            echo htmlspecialchars($product_data['name']);
                                                                        } else {
                                                                            echo "Product #" . $item['item_id'];
                                                                        }
                                                                    }
                                                                    ?>
                                                                </td>
                                                                <td><?php echo ucfirst($item['item_type']); ?></td>
                                                                <td><?php echo $item['quantity']; ?></td>
                                                                <td>KSh <?php echo number_format($item['price_per_unit'], 2); ?></td>
                                                                <td>KSh <?php echo number_format($item['subtotal'], 2); ?></td>
                                                                <td>
                                                                    <span class="badge badge-<?php echo getOrderStatusBadgeClass($item['status']); ?>">
                                                                        <?php echo ucfirst($item['status']); ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <button type="button" class="btn btn-sm btn-primary update-status-btn" 
                                                                            data-toggle="modal" 
                                                                            data-target="#updateStatusModal<?php echo $item['order_item_id']; ?>"
                                                                            data-current-status="<?php echo $item['status']; ?>">
                                                                        <i class="fa fa-edit"></i> Update
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                            <?php endwhile; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <p>No orders found<?php echo !empty($status_filter) ? " with status '$status_filter'" : ""; ?>.</p>
                    </div>
                <?php endif; ?>
            </div> 
        </div>
    </div>
</div>

<!-- Status Update Modals -->
<?php 
foreach ($orders_array as $order): 
    // Get items for this order again for modals
    $items_query = "SELECT oi.order_item_id, oi.item_type, oi.item_id, oi.quantity, 
                   oi.price_per_unit, oi.subtotal, oi.status
                   FROM order_items oi
                   WHERE oi.order_id = ? AND oi.seller_id = ?";
    $items_stmt = $conn->prepare($items_query);
    $items_stmt->bind_param("ii", $order['order_id'], $seller_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    
    while ($item = $items_result->fetch_assoc()):
?>
<div class="modal fade" id="updateStatusModal<?php echo $item['order_item_id']; ?>" 
     tabindex="-1" role="dialog" aria-labelledby="updateStatusModalLabel<?php echo $item['order_item_id']; ?>" 
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateStatusModalLabel<?php echo $item['order_item_id']; ?>">
                    Update Item Status
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="post" class="status-update-form">
                <div class="modal-body">
                    <input type="hidden" name="order_item_id" value="<?php echo $item['order_item_id']; ?>">
                    
                    <div class="form-group">
                        <label for="new_status_<?php echo $item['order_item_id']; ?>">Status:</label>
                        <select name="new_status" 
                                id="new_status_<?php echo $item['order_item_id']; ?>" 
                                class="form-control" 
                                required>
                            <option value="pending" <?php echo $item['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo $item['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="shipped" <?php echo $item['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="delivered" <?php echo $item['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo $item['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <small>Current Status: <strong><?php echo ucfirst($item['status']); ?></strong></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php 
    endwhile; 
endforeach; 
?>

<script>
$(document).ready(function() {
    // Fix for View button - Make it expand/collapse the order details
    $('.view-order-details').on('click', function(e) {
        e.preventDefault();
        var orderId = $(this).data('order-id');
        var collapseTarget = '#collapse' + orderId;
        
        // Toggle the collapse
        $(collapseTarget).collapse('toggle');
        
        // Update button text based on state
        var button = $(this);
        $(collapseTarget).on('shown.bs.collapse', function() {
            button.html('<i class="fa fa-eye-slash"></i> Hide');
        });
        
        $(collapseTarget).on('hidden.bs.collapse', function() {
            button.html('<i class="fa fa-eye"></i> View');
        });
    });
    
    // Ensure modals work properly
    $('.modal').on('shown.bs.modal', function() {
        // Focus on the select element when modal opens
        $(this).find('select[name="new_status"]').focus();
        
        // Ensure the select dropdown is properly initialized
        $(this).find('select[name="new_status"]').prop('disabled', false);
    });
    
    // Handle modal opening
    $('.update-status-btn').on('click', function() {
        var currentStatus = $(this).data('current-status');
        console.log('Opening modal for status:', currentStatus);
    });
    
    // Handle form submission with confirmation
    $('.status-update-form').on('submit', function(e) {
        var newStatus = $(this).find('select[name="new_status"]').val();
        if (!confirm('Are you sure you want to update the status to "' + newStatus + '"?')) {
            e.preventDefault();
            return false;
        }
    });
    
    // Debug information
    console.log('jQuery loaded:', typeof jQuery !== 'undefined');
    console.log('Bootstrap loaded:', typeof $.fn.modal !== 'undefined');
    console.log('View buttons found:', $('.view-order-details').length);
    console.log('Modals found:', $('.modal').length);
    console.log('Update buttons found:', $('.update-status-btn').length);
});
</script>

<?php include_once "../includes/footer.php"; ?>