<?php
// Set page title
$pageTitle = "My Orders";

// Include header
require_once '../includes/header.php';

// Include database connection
require_once '../config/db.php';

// Check if user is logged in and is a buyer
if (!isLoggedIn() || !isBuyer()) {
    redirect('auth/login.php');
}

$userId = $_SESSION['user_id'];

// Get orders for this buyer
$ordersQuery = "SELECT o.*, COUNT(oi.order_item_id) as item_count 
                FROM orders o 
                JOIN order_items oi ON o.order_id = oi.order_id 
                WHERE o.buyer_id = $userId 
                GROUP BY o.order_id 
                ORDER BY o.order_date DESC";
$ordersResult = $conn->query($ordersQuery);

// Handle filter by status if provided
$statusFilter = '';
if (isset($_GET['status']) && in_array($_GET['status'], ['pending', 'processing', 'completed', 'cancelled'])) {
    $status = $conn->real_escape_string($_GET['status']);
    $statusFilter = "AND o.status = '$status'";
    $ordersQuery = "SELECT o.*, COUNT(oi.order_item_id) as item_count 
                    FROM orders o 
                    JOIN order_items oi ON o.order_id = oi.order_id 
                    WHERE o.buyer_id = $userId $statusFilter
                    GROUP BY o.order_id 
                    ORDER BY o.order_date DESC";
    $ordersResult = $conn->query($ordersQuery);
}

// Get wishlist count
$wishlistQuery = "SELECT COUNT(*) as count FROM wishlist_items WHERE user_id = $userId";
$wishlistResult = $conn->query($wishlistQuery);
$wishlistCount = $wishlistResult->fetch_assoc()['count'];

// Get cart count
$cartQuery = "SELECT COUNT(*) as count FROM cart_items WHERE user_id = $userId";
$cartResult = $conn->query($cartQuery);
$cartCount = $cartResult->fetch_assoc()['count'];

// Get user data
$userId = $_SESSION['user_id'];
$userQuery = "SELECT * FROM users WHERE user_id = $userId";
$userResult = $conn->query($userQuery);
$user = $userResult->fetch_assoc();

?>

<div class="container py-5">
    <div class="row">
        <!-- Include Sidebar -->
        <?php include_once 'sidebar.php'; ?>
        
        <!-- Orders List -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0">My Orders</h3>
                
                <!-- Status Filter -->
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" id="statusFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php 
                        if (isset($_GET['status'])) {
                            echo ucfirst($_GET['status']);
                        } else {
                            echo 'All Orders';
                        }
                        ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="statusFilterDropdown">
                        <li><a class="dropdown-item <?php echo !isset($_GET['status']) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>buyer/orders.php">All Orders</a></li>
                        <li><a class="dropdown-item <?php echo isset($_GET['status']) && $_GET['status'] == 'pending' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>buyer/orders.php?status=pending">Pending</a></li>
                        <li><a class="dropdown-item <?php echo isset($_GET['status']) && $_GET['status'] == 'processing' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>buyer/orders.php?status=processing">Processing</a></li>
                        <li><a class="dropdown-item <?php echo isset($_GET['status']) && $_GET['status'] == 'completed' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>buyer/orders.php?status=completed">Completed</a></li>
                        <li><a class="dropdown-item <?php echo isset($_GET['status']) && $_GET['status'] == 'cancelled' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>buyer/orders.php?status=cancelled">Cancelled</a></li>
                    </ul>
                </div>
            </div>
            
            <?php if ($ordersResult->num_rows > 0): ?>
                <div class="accordion" id="ordersAccordion">
                    <?php while ($order = $ordersResult->fetch_assoc()): ?>
                        <?php
                        // Get order items for this order
                        $orderItemsQuery = "SELECT oi.*, 
                                            CASE 
                                                WHEN oi.item_type = 'pet' THEN (SELECT name FROM pets WHERE pet_id = oi.item_id)
                                                WHEN oi.item_type = 'product' THEN (SELECT name FROM products WHERE product_id = oi.item_id)
                                            END as item_name,
                                            CASE 
                                                WHEN oi.item_type = 'pet' THEN (SELECT business_name FROM seller_profiles WHERE seller_id = oi.seller_id)
                                                WHEN oi.item_type = 'product' THEN (SELECT business_name FROM seller_profiles WHERE seller_id = oi.seller_id)
                                            END as seller_name,
                                            (SELECT 
                                                CASE 
                                                    WHEN oi.item_type = 'pet' THEN (SELECT image_path FROM images WHERE item_type = 'pet' AND item_id = oi.item_id AND is_primary = 1 LIMIT 1)
                                                    WHEN oi.item_type = 'product' THEN (SELECT image_path FROM images WHERE item_type = 'product' AND item_id = oi.item_id AND is_primary = 1 LIMIT 1)
                                                END
                                            ) as image_path
                                            FROM order_items oi
                                            WHERE oi.order_id = {$order['order_id']}
                                            ORDER BY oi.seller_id";
                        $orderItemsResult = $conn->query($orderItemsQuery);
                        
                        // Get status label class
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
                        
                        // Get payment status label class
                        $paymentStatusClass = '';
                        switch ($order['payment_status']) {
                            case 'pending':
                                $paymentStatusClass = 'bg-warning';
                                break;
                            case 'paid':
                                $paymentStatusClass = 'bg-success';
                                break;
                            case 'failed':
                                $paymentStatusClass = 'bg-danger';
                                break;
                        }
                        ?>
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white" id="heading<?php echo $order['order_id']; ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0">
                                            <button class="btn btn-link text-decoration-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $order['order_id']; ?>" aria-expanded="false" aria-controls="collapse<?php echo $order['order_id']; ?>">
                                                Order #<?php echo $order['order_id']; ?> 
                                                <span class="badge <?php echo $statusClass; ?> ms-2"><?php echo ucfirst($order['status']); ?></span>
                                            </button>
                                        </h5>
                                        <small class="text-muted">
                                            Placed on <?php echo date('M j, Y, g:i A', strtotime($order['order_date'])); ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="d-block">KES <?php echo number_format($order['total_amount'], 2); ?></span>
                                        <small class="badge <?php echo $paymentStatusClass; ?>"><?php echo ucfirst($order['payment_status']); ?></small>
                                    </div>
                                </div>
                            </div>

                            <div id="collapse<?php echo $order['order_id']; ?>" class="collapse" aria-labelledby="heading<?php echo $order['order_id']; ?>" data-bs-parent="#ordersAccordion">
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <h6>Order Information</h6>
                                            <table class="table table-borderless table-sm">
                                                <tbody>
                                                    <tr>
                                                        <td class="text-muted">Order ID:</td>
                                                        <td>#<?php echo $order['order_id']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Order Date:</td>
                                                        <td><?php echo date('M j, Y, g:i A', strtotime($order['order_date'])); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Order Status:</td>
                                                        <td><span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Payment Method:</td>
                                                        <td><?php echo str_replace('_', ' ', ucfirst($order['payment_method'])); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Payment Status:</td>
                                                        <td><span class="badge <?php echo $paymentStatusClass; ?>"><?php echo ucfirst($order['payment_status']); ?></span></td>
                                                    </tr>
                                                    <?php if ($order['transaction_reference']): ?>
                                                    <tr>
                                                        <td class="text-muted">Transaction Ref:</td>
                                                        <td><?php echo $order['transaction_reference']; ?></td>
                                                    </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Shipping Information</h6>
                                            <table class="table table-borderless table-sm">
                                                <tbody>
                                                    <tr>
                                                        <td class="text-muted">Address:</td>
                                                        <td><?php echo $order['shipping_address']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">County:</td>
                                                        <td><?php echo $order['shipping_county']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Contact:</td>
                                                        <td><?php echo $order['contact_phone']; ?></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    
                                    <h6>Order Items</h6>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th>Seller</th>
                                                    <th>Price</th>
                                                    <th>Quantity</th>
                                                    <th>Subtotal</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $currentSellerId = null;
                                                $sellerItemCount = 0;
                                                while ($item = $orderItemsResult->fetch_assoc()): 
                                                    // Item status class
                                                    $itemStatusClass = '';
                                                    switch ($item['status']) {
                                                        case 'pending':
                                                            $itemStatusClass = 'bg-warning';
                                                            break;
                                                        case 'processing':
                                                            $itemStatusClass = 'bg-info';
                                                            break;
                                                        case 'shipped':
                                                            $itemStatusClass = 'bg-primary';
                                                            break;
                                                        case 'delivered':
                                                            $itemStatusClass = 'bg-success';
                                                            break;
                                                        case 'cancelled':
                                                            $itemStatusClass = 'bg-danger';
                                                            break;
                                                    }
                                                    
                                                    // Check if this is a new seller group
                                                    if ($currentSellerId !== $item['seller_id']) {
                                                        $currentSellerId = $item['seller_id'];
                                                        $sellerItemCount++;
                                                    }
                                                ?>
                                                <tr>
                                                    <td class="align-middle">
                                                        <div class="d-flex align-items-center">
                                                            <?php if ($item['image_path']): ?>
                                                                <img src="<?php echo BASE_URL . '' . $item['image_path']; ?>" alt="<?php echo $item['item_name']; ?>" class="rounded" width="50" height="50" style="object-fit: cover;">
                                                            <?php else: ?>
                                                                <div class="bg-light rounded" style="width: 50px; height: 50px;"></div>
                                                            <?php endif; ?>
                                                            <div class="ms-3">
                                                                <p class="mb-0 fw-medium"><?php echo $item['item_name']; ?></p>
                                                                <small class="text-muted"><?php echo ucfirst($item['item_type']); ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle"><?php echo $item['seller_name']; ?></td>
                                                    <td class="align-middle">KES <?php echo number_format($item['price_per_unit'], 2); ?></td>
                                                    <td class="align-middle"><?php echo $item['quantity']; ?></td>
                                                    <td class="align-middle">KES <?php echo number_format($item['subtotal'], 2); ?></td>
                                                    <td class="align-middle"><span class="badge <?php echo $itemStatusClass; ?>"><?php echo ucfirst($item['status']); ?></span></td>
                                                    <td class="align-middle">
                                                        <?php if ($item['status'] === 'delivered' && $order['status'] === 'completed'): ?>
                                                            <?php
                                                            // Check if user has already reviewed this item
                                                            $reviewCheckQuery = "SELECT * FROM reviews WHERE user_id = $userId AND item_type = '{$item['item_type']}' AND item_id = {$item['item_id']}";
                                                            $reviewCheckResult = $conn->query($reviewCheckQuery);
                                                            $hasReviewed = $reviewCheckResult->num_rows > 0;
                                                            ?>
                                                            <?php if (!$hasReviewed): ?>
                                                                <button class="btn btn-sm btn-outline-primary write-review-btn" data-item-type="<?php echo $item['item_type']; ?>" data-item-id="<?php echo $item['item_id']; ?>" data-item-name="<?php echo $item['item_name']; ?>">
                                                                    <i class="fas fa-star me-1"></i> Review
                                                                </button>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary">Reviewed</span>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                        
                                                        <button class="btn btn-sm btn-outline-info contact-seller-btn" data-seller-id="<?php echo $item['seller_id']; ?>" data-order-id="<?php echo $order['order_id']; ?>" data-item-name="<?php echo $item['item_name']; ?>">
                                                            <i class="fas fa-envelope me-1"></i> Contact
                                                        </button>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="4" class="text-end fw-bold">Total Amount:</td>
                                                    <td colspan="3" class="fw-bold">KES <?php echo number_format($order['total_amount'], 2); ?></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between mt-3">
                                        <?php if ($order['status'] === 'pending' && $order['payment_status'] === 'pending'): ?>
                                            <button class="btn btn-primary pay-now-btn" data-order-id="<?php echo $order['order_id']; ?>" data-amount="<?php echo $order['total_amount']; ?>">
                                                <i class="fas fa-credit-card me-2"></i> Complete Payment
                                            </button>
                                            
                                            <button class="btn btn-outline-danger cancel-order-btn" data-order-id="<?php echo $order['order_id']; ?>">
                                                <i class="fas fa-times me-2"></i> Cancel Order
                                            </button>
                                        <?php elseif ($order['status'] === 'processing' || $order['status'] === 'completed'): ?>
                                            <a href="<?php echo BASE_URL; ?>buyer/invoice.php?id=<?php echo $order['order_id']; ?>" class="btn btn-outline-primary" target="_blank">
                                                <i class="fas fa-file-invoice me-2"></i> View Invoice
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
                        <h4>No Orders Yet</h4>
                        <p class="text-muted">You haven't placed any orders yet.</p>
                        <a href="<?php echo BASE_URL; ?>buyer/browse.php" class="btn btn-primary mt-3">
                            <i class="fas fa-search me-2"></i> Browse Pets
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Write Review Modal -->
<div class="modal fade" id="writeReviewModal" tabindex="-1" aria-labelledby="writeReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="writeReviewModalLabel">Write a Review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reviewForm" action="<?php echo BASE_URL; ?>buyer/submit_review.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="item_type" id="reviewItemType" value="">
                    <input type="hidden" name="item_id" id="reviewItemId" value="">
                    
                    <div class="mb-3">
                        <label class="form-label">Rating for <span id="reviewItemName"></span></label>
                        <div class="rating-stars mb-2">
                            <i class="far fa-star fa-2x rating-star" data-value="1"></i>
                            <i class="far fa-star fa-2x rating-star" data-value="2"></i>
                            <i class="far fa-star fa-2x rating-star" data-value="3"></i>
                            <i class="far fa-star fa-2x rating-star" data-value="4"></i>
                            <i class="far fa-star fa-2x rating-star" data-value="5"></i>
                        </div>
                        <input type="hidden" name="rating" id="ratingValue" value="">
                    </div>
                    
                    <div class="mb-3">
                        <label for="reviewComment" class="form-label">Your Review</label>
                        <textarea class="form-control" id="reviewComment" name="comment" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Contact Seller Modal -->
<div class="modal fade" id="contactSellerModal" tabindex="-1" aria-labelledby="contactSellerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contactSellerModalLabel">Message to Seller</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="messageForm" action="<?php echo BASE_URL; ?>buyer/send_message.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="receiver_id" id="receiverId" value="">
                    <input type="hidden" name="related_to_item_type" value="order">
                    <input type="hidden" name="related_to_item_id" id="relatedOrderId" value="">
                    
                    <div class="mb-3">
                        <label for="messageSubject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="messageSubject" name="subject" value="" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="messageContent" class="form-label">Message</label>
                        <textarea class="form-control" id="messageContent" name="message" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Order Confirmation Modal -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelOrderModalLabel">Cancel Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cancelOrderForm" action="<?php echo BASE_URL; ?>buyer/cancel_order.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="order_id" id="cancelOrderId" value="">
                    <p>Are you sure you want to cancel this order? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep Order</button>
                    <button type="submit" class="btn btn-danger">Yes, Cancel Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Complete Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="paymentForm" action="<?php echo BASE_URL; ?>buyer/process_payment.php" method="post">
                    <input type="hidden" name="order_id" id="paymentOrderId" value="">
                    <input type="hidden" name="amount" id="paymentAmount" value="">
                    
                    <div class="mb-4">
                        <h6>Payment Amount</h6>
                        <h3 class="text-primary">KES <span id="displayAmount"></span></h3>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="payment_method" id="payMpesa" value="mpesa" checked>
                            <label class="form-check-label" for="payMpesa">
                                <img src="<?php echo BASE_URL; ?>assets/img/mpesa-logo.png" alt="M-Pesa" height="20" class="me-2"> M-Pesa
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="payment_method" id="payPesapal" value="pesapal">
                            <label class="form-check-label" for="payPesapal">
                                <img src="<?php echo BASE_URL; ?>assets/img/pesapal-logo.png" alt="Pesapal" height="20" class="me-2"> Pesapal
                            </label>
                        </div>
                    </div>
                    
                    <div id="mpesaPaymentForm">
                        <div class="mb-3">
                            <label for="mpesaPhone" class="form-label">M-Pesa Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text">+254</span>
                                <input type="text" class="form-control" id="mpesaPhone" name="phone" placeholder="7XXXXXXXX" maxlength="9" pattern="[0-9]{9}" required>
                            </div>
                            <div class="form-text">Please enter your M-Pesa registered phone number without the country code.</div>
                        </div>
                    </div>
                    
                    <div id="pesapalPaymentForm" style="display: none;">
                        <p class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            You will be redirected to Pesapal to complete your payment securely.
                        </p>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">Complete Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Custom CSS for star rating -->
<style>
    .rating-stars {
        color: #ffc107;
        cursor: pointer;
    }
    .rating-stars i {
        margin-right: 5px;
    }
    .rating-stars i.fas {
        color: #ffc107;
    }
    .rating-stars i.far {
        color: #e4e5e9;
    }
    .rating-stars i:hover {
        transform: scale(1.1);
    }
</style>
 

<!-- JavaScript for handling modals and form functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Write Review Modal Functionality
    const writeReviewBtns = document.querySelectorAll('.write-review-btn');
    writeReviewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const itemType = this.getAttribute('data-item-type');
            const itemId = this.getAttribute('data-item-id');
            const itemName = this.getAttribute('data-item-name');
            
            document.getElementById('reviewItemType').value = itemType;
            document.getElementById('reviewItemId').value = itemId;
            document.getElementById('reviewItemName').textContent = itemName;
            
            // Reset the stars
            document.querySelectorAll('.rating-star').forEach(star => {
                star.classList.remove('fas');
                star.classList.add('far');
            });
            document.getElementById('ratingValue').value = '';
            
            const reviewModal = new bootstrap.Modal(document.getElementById('writeReviewModal'));
            reviewModal.show();
        });
    });
    
    // Star Rating Functionality
    const ratingStars = document.querySelectorAll('.rating-star');
    ratingStars.forEach(star => {
        star.addEventListener('click', function() {
            const value = parseInt(this.getAttribute('data-value'));
            document.getElementById('ratingValue').value = value;
            
            // Update star display
            ratingStars.forEach(s => {
                const starValue = parseInt(s.getAttribute('data-value'));
                if (starValue <= value) {
                    s.classList.remove('far');
                    s.classList.add('fas');
                } else {
                    s.classList.remove('fas');
                    s.classList.add('far');
                }
            });
        });
    });
    
    // Contact Seller Modal Functionality
    const contactSellerBtns = document.querySelectorAll('.contact-seller-btn');
    contactSellerBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const sellerId = this.getAttribute('data-seller-id');
            const orderId = this.getAttribute('data-order-id');
            const itemName = this.getAttribute('data-item-name');
            
            document.getElementById('receiverId').value = sellerId;
            document.getElementById('relatedOrderId').value = orderId;
            document.getElementById('messageSubject').value = `Order #${orderId} - ${itemName}`;
            
            const contactModal = new bootstrap.Modal(document.getElementById('contactSellerModal'));
            contactModal.show();
        });
    });
    
    // Cancel Order Modal Functionality
    const cancelOrderBtns = document.querySelectorAll('.cancel-order-btn');
    cancelOrderBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order-id');
            document.getElementById('cancelOrderId').value = orderId;
            
            const cancelModal = new bootstrap.Modal(document.getElementById('cancelOrderModal'));
            cancelModal.show();
        });
    });
    
    // Payment Modal Functionality
    const payNowBtns = document.querySelectorAll('.pay-now-btn');
    payNowBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order-id');
            const amount = this.getAttribute('data-amount');
            
            document.getElementById('paymentOrderId').value = orderId;
            document.getElementById('paymentAmount').value = amount;
            document.getElementById('displayAmount').textContent = parseFloat(amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            
            const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
            paymentModal.show();
        });
    });
    
    // Toggle between payment methods
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'mpesa') {
                document.getElementById('mpesaPaymentForm').style.display = 'block';
                document.getElementById('pesapalPaymentForm').style.display = 'none';
            } else if (this.value === 'pesapal') {
                document.getElementById('mpesaPaymentForm').style.display = 'none';
                document.getElementById('pesapalPaymentForm').style.display = 'block';
            }
        });
    });
});
</script>

<?php
// Include footer
require_once '../includes/footer.php';
?>