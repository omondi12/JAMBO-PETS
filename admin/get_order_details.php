<?php
// Include database connection
require_once '../config/db.php';

// Check if user is admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(403);
    echo "Access denied";
    exit;
}

// Get order ID from request
$order_id = filter_input(INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT);

if (!$order_id) {
    echo "Invalid order ID";
    exit;
}

try {
    // Get order details
    $order_query = "SELECT o.*, u.first_name, u.last_name, u.email, u.phone 
                    FROM orders o
                    JOIN users u ON o.buyer_id = u.user_id
                    WHERE o.order_id = ?";
    
    $stmt = $conn->prepare($order_query);
    if ($stmt === false) {
        throw new Exception("Failed to prepare order query: " . $conn->error);
    }
    
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo "Order not found";
        exit;
    }
    
    $order = $result->fetch_assoc();
    
    // Get order items
    $items_query = "SELECT oi.*, 
                    CASE 
                        WHEN oi.item_type = 'pet' THEN p.name
                        WHEN oi.item_type = 'product' THEN pr.name
                    END AS item_name,
                    u.first_name AS seller_first_name, 
                    u.last_name AS seller_last_name,
                    sp.business_name,
                    (SELECT image_path FROM images WHERE item_type = oi.item_type AND item_id = oi.item_id AND is_primary = 1 LIMIT 1) AS image_path
                    FROM order_items oi
                    LEFT JOIN pets p ON oi.item_type = 'pet' AND oi.item_id = p.pet_id
                    LEFT JOIN products pr ON oi.item_type = 'product' AND oi.item_id = pr.product_id
                    LEFT JOIN seller_profiles sp ON oi.seller_id = sp.seller_id
                    LEFT JOIN users u ON sp.user_id = u.user_id
                    WHERE oi.order_id = ?";
    
    $items_stmt = $conn->prepare($items_query);
    if ($items_stmt === false) {
        throw new Exception("Failed to prepare items query: " . $conn->error);
    }
    
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    
    $items = [];
    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }
    
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}
?>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="m-0">Order Information</h6>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-4 fw-bold">Order ID:</div>
                    <div class="col-8">#<?php echo $order['order_id']; ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-4 fw-bold">Order Date:</div>
                    <div class="col-8"><?php echo date('d M Y H:i', strtotime($order['order_date'])); ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-4 fw-bold">Status:</div>
                    <div class="col-8">
                        <?php if ($order['status'] === 'pending'): ?>
                            <span class="badge bg-warning text-dark">Pending</span>
                        <?php elseif ($order['status'] === 'processing'): ?>
                            <span class="badge bg-info">Processing</span>
                        <?php elseif ($order['status'] === 'completed'): ?>
                            <span class="badge bg-success">Completed</span>
                        <?php elseif ($order['status'] === 'cancelled'): ?>
                            <span class="badge bg-danger">Cancelled</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-4 fw-bold">Total Amount:</div>
                    <div class="col-8">KES <?php echo number_format($order['total_amount'], 2); ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-4 fw-bold">Payment Method:</div>
                    <div class="col-8"><?php echo ucfirst($order['payment_method']); ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-4 fw-bold">Payment Status:</div>
                    <div class="col-8">
                        <?php if ($order['payment_status'] === 'pending'): ?>
                            <span class="badge bg-warning text-dark">Pending</span>
                        <?php elseif ($order['payment_status'] === 'paid'): ?>
                            <span class="badge bg-success">Paid</span>
                        <?php elseif ($order['payment_status'] === 'failed'): ?>
                            <span class="badge bg-danger">Failed</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!empty($order['transaction_reference'])): ?>
                <div class="row mb-2">
                    <div class="col-4 fw-bold">Transaction Ref:</div>
                    <div class="col-8"><?php echo htmlspecialchars($order['transaction_reference']); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="m-0">Customer Information</h6>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-4 fw-bold">Name:</div>
                    <div class="col-8"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-4 fw-bold">Email:</div>
                    <div class="col-8"><?php echo htmlspecialchars($order['email']); ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-4 fw-bold">Phone:</div>
                    <div class="col-8"><?php echo htmlspecialchars(isset($order['contact_phone']) && !empty($order['contact_phone']) ? $order['contact_phone'] : $order['phone']); ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-4 fw-bold">Shipping Address:</div>
                    <div class="col-8"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-4 fw-bold">County:</div>
                    <div class="col-8"><?php echo htmlspecialchars($order['shipping_county']); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h6 class="m-0">Order Items</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width: 50px;">Image</th>
                        <th>Item</th>
                        <th>Type</th>
                        <th>Seller</th>
                        <th style="width: 100px;">Price</th>
                        <th style="width: 80px;">Qty</th>
                        <th style="width: 100px;">Subtotal</th>
                        <th style="width: 120px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <?php if (!empty($item['image_path'])): ?>
                                    <img src="<?php echo '../' . $item['image_path']; ?>" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;" alt="Item image">
                                <?php else: ?>
                                    <div class="bg-light text-center" style="width: 50px; height: 50px; line-height: 50px;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                            <td><?php echo ucfirst($item['item_type']); ?></td>
                            <td>
                                <?php if (!empty($item['business_name'])): ?>
                                    <?php echo htmlspecialchars($item['business_name']); ?>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($item['seller_first_name'] . ' ' . $item['seller_last_name']); ?>
                                <?php endif; ?>
                            </td>
                            <td>KES <?php echo number_format($item['price_per_unit'], 2); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>KES <?php echo number_format($item['subtotal'], 2); ?></td>
                            <td>
                                <?php if ($item['status'] === 'pending'): ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php elseif ($item['status'] === 'processing'): ?>
                                    <span class="badge bg-info">Processing</span>
                                <?php elseif ($item['status'] === 'shipped'): ?>
                                    <span class="badge bg-primary">Shipped</span>
                                <?php elseif ($item['status'] === 'delivered'): ?>
                                    <span class="badge bg-success">Delivered</span>
                                <?php elseif ($item['status'] === 'cancelled'): ?>
                                    <span class="badge bg-danger">Cancelled</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" class="text-end fw-bold">Total:</td>
                        <td colspan="2" class="fw-bold">KES <?php echo number_format($order['total_amount'], 2); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>