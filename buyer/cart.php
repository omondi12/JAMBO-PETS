<?php
// Start the session
session_start();

// Include database connection
require_once '../config/db.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'buyer') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle removal from cart
if (isset($_GET['remove']) && isset($_GET['id'])) {
    $cart_id = $_GET['id'];
    
    $stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_item_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cart_id, $user_id);
    $stmt->execute();
    
    // Set success message
    $_SESSION['success_msg'] = "Item removed from cart successfully!";
    header("Location: cart.php");
    exit();
}

// Handle move to wishlist
if (isset($_GET['move_to_wishlist']) && isset($_GET['id'])) {
    $cart_id = $_GET['id'];
    
    // Get item details
    $stmt = $conn->prepare("SELECT item_type, item_id FROM cart_items WHERE cart_item_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cart_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $item_type = $row['item_type'];
        $item_id = $row['item_id'];
        
        // Check if item already exists in wishlist
        $check_stmt = $conn->prepare("SELECT * FROM wishlist_items WHERE user_id = ? AND item_type = ? AND item_id = ?");
        $check_stmt->bind_param("isi", $user_id, $item_type, $item_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows == 0) {
            // Add to wishlist
            $add_stmt = $conn->prepare("INSERT INTO wishlist_items (user_id, item_type, item_id) VALUES (?, ?, ?)");
            $add_stmt->bind_param("isi", $user_id, $item_type, $item_id);
            $add_stmt->execute();
        }
        
        // Remove from cart
        $delete_stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_item_id = ? AND user_id = ?");
        $delete_stmt->bind_param("ii", $cart_id, $user_id);
        $delete_stmt->execute();
        
        // Set success message
        $_SESSION['success_msg'] = "Item moved to wishlist successfully!";
    }
    
    header("Location: cart.php");
    exit();
}

// Handle quantity update
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $cart_id => $qty) {
        if ($qty < 1) $qty = 1;
        
        $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ? AND user_id = ?");
        $stmt->bind_param("iii", $qty, $cart_id, $user_id);
        $stmt->execute();
    }
    
    $_SESSION['success_msg'] = "Cart updated successfully!";
    header("Location: cart.php");
    exit();
}

// Handle checkout action
if (isset($_POST['checkout'])) {
    // Check if the orders table exists first (for error prevention)
    $check_table = $conn->query("SHOW TABLES LIKE 'orders'");
    
    if ($check_table->num_rows > 0) {
        // Create order - Fixed column names to match database schema
        $stmt = $conn->prepare("INSERT INTO orders (buyer_id, status, payment_method, total_amount) VALUES (?, 'pending', ?, ?)");
        if ($stmt === false) {
            $_SESSION['error_msg'] = "Error creating order: " . $conn->error;
        } else {
            $payment_method = $_POST['payment_method'];
            $total_amount = $_POST['total_amount'];
            $stmt->bind_param("isd", $user_id, $payment_method, $total_amount);
            $stmt->execute();
            $order_id = $conn->insert_id;
            
            // Add order items if order_items table exists
            $check_items_table = $conn->query("SHOW TABLES LIKE 'order_items'");
            
            if ($check_items_table->num_rows > 0) {
                $cart_stmt = $conn->prepare("SELECT * FROM cart_items WHERE user_id = ?");
                if ($cart_stmt) {
                    $cart_stmt->bind_param("i", $user_id);
                    $cart_stmt->execute();
                    $cart_result = $cart_stmt->get_result();
                    
                    while ($item = $cart_result->fetch_assoc()) {
                        // Get price and seller ID based on item type
                        if ($item['item_type'] == 'pet') {
                            $price_stmt = $conn->prepare("SELECT price, seller_id FROM pets WHERE pet_id = ?");
                        } else {
                            $price_stmt = $conn->prepare("SELECT price, seller_id FROM products WHERE product_id = ?");
                        }
                        
                        if ($price_stmt) {
                            $price_stmt->bind_param("i", $item['item_id']);
                            $price_stmt->execute();
                            $price_result = $price_stmt->get_result();
                            $price_row = $price_result->fetch_assoc();
                            $price = $price_row['price'];
                            $seller_id = $price_row['seller_id'];
                            $subtotal = $price * $item['quantity'];
                            
                            // Insert order item - Make sure to include all required fields from order_items table
                            $order_item_stmt = $conn->prepare("INSERT INTO order_items (order_id, item_type, item_id, seller_id, quantity, price_per_unit, subtotal, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
                            if ($order_item_stmt) {
                                $order_item_stmt->bind_param("isiiidd", $order_id, $item['item_type'], $item['item_id'], $seller_id, $item['quantity'], $price, $subtotal);
                                $order_item_stmt->execute();
                            }
                        }
                    }
                }
            }
            
            // Clear cart
            $clear_stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
            if ($clear_stmt) {
                $clear_stmt->bind_param("i", $user_id);
                $clear_stmt->execute();
            }
            
            // Redirect to confirmation page
            $_SESSION['order_id'] = $order_id;
            header("Location: order_confirmation.php");
            exit();
        }
    } else {
        // Orders table doesn't exist yet - set a friendly message
        $_SESSION['error_msg'] = "Checkout functionality is still under development. Your cart has been saved.";
    }
    
    header("Location: cart.php");
    exit();
}

// Fetch cart items with images from the images table
$query = "SELECT c.*, 
          CASE 
              WHEN c.item_type = 'pet' THEN p.name
              WHEN c.item_type = 'product' THEN pr.name
          END AS item_name,
          CASE 
              WHEN c.item_type = 'pet' THEN p.price
              WHEN c.item_type = 'product' THEN pr.price
          END AS item_price,
          (SELECT image_path FROM images WHERE item_type = c.item_type AND item_id = c.item_id AND is_primary = 1 LIMIT 1) AS primary_image,
          (SELECT image_path FROM images WHERE item_type = c.item_type AND item_id = c.item_id LIMIT 1) AS fallback_image
          FROM cart_items c
          LEFT JOIN pets p ON c.item_id = p.pet_id AND c.item_type = 'pet'
          LEFT JOIN products pr ON c.item_id = pr.product_id AND c.item_type = 'product'
          WHERE c.user_id = ?";

$stmt = $conn->prepare($query);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);

// Calculate cart totals
$subtotal = 0;
$shipping = 150; // Default shipping cost in KSh
$discount = 0;

foreach ($cart_items as $item) {
    $subtotal += $item['item_price'] * $item['quantity'];
}

$total = $subtotal + $shipping - $discount;

$userId = $_SESSION['user_id'];

// Get wishlist count
$wishlistQuery = "SELECT COUNT(*) as count FROM wishlist_items WHERE user_id = $userId";
$wishlistResult = $conn->query($wishlistQuery);
$wishlistCount = $wishlistResult->fetch_assoc()['count'];

// Get cart count
$cartQuery = "SELECT COUNT(*) as count FROM cart_items WHERE user_id = $userId";
$cartResult = $conn->query($cartQuery);
$cartCount = $cartResult->fetch_assoc()['count'];

// Include header
$page_title = "Shopping Cart";
include_once '../includes/header.php';
?>

<div class="container py-5">
    <div class="row">

    <?php include_once 'sidebar.php'; ?>

        <div class="col-lg-9">
            <h2 class="mb-4">Shopping Cart</h2>
            
            <?php if (isset($_SESSION['success_msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                        echo $_SESSION['success_msg']; 
                        unset($_SESSION['success_msg']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_msg'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                        echo $_SESSION['error_msg']; 
                        unset($_SESSION['error_msg']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (empty($cart_items)): ?>
                <div class="alert alert-info">
                    Your cart is empty. <a href="browse.php">Continue shopping</a>
                </div>
            <?php else: ?>
                <form method="post" action="">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php 
                                                // Use primary image if available, fallback image as second choice
                                                $image_path = !empty($item['primary_image']) ? $item['primary_image'] : 
                                                            (!empty($item['fallback_image']) ? $item['fallback_image'] : '');
                                                
                                                if (!empty($image_path)): 
                                                ?>
                                                    <img src="../<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($item['item_name']); ?>" class="img-thumbnail me-3" style="width: 80px; height: 80px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-light d-flex align-items-center justify-content-center me-3" style="width: 80px; height: 80px;">
                                                        <span class="text-muted">No image</span>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($item['item_name']); ?></h6>
                                                    <small class="text-muted"><?php echo ucfirst($item['item_type']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>KSh <?php echo number_format($item['item_price'], 2); ?></td>
                                        <td>
                                            <input type="number" name="quantity[<?php echo $item['cart_item_id']; ?>]" class="form-control form-control-sm" style="width: 70px;" min="1" value="<?php echo $item['quantity']; ?>">
                                        </td>
                                        <td>KSh <?php echo number_format($item['item_price'] * $item['quantity'], 2); ?></td>
                                        <td>
                                            <a href="cart.php?move_to_wishlist=1&id=<?php echo $item['cart_item_id']; ?>" class="btn btn-sm btn-outline-primary me-1">Move to Wishlist</a>
                                            <a href="cart.php?remove=1&id=<?php echo $item['cart_item_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to remove this item from your cart?')">Remove</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <button type="submit" name="update_cart" class="btn btn-outline-primary">Update Cart</button>
                                <a href="browse.php" class="btn btn-primary ms-2">Continue Shopping</a>
                            </div>
                            
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Payment Method</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="payment_method" id="mpesa" value="mpesa" checked>
                                        <label class="form-check-label" for="mpesa">M-Pesa</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="payment_method" id="pesapal" value="pesapal">
                                        <label class="form-check-label" for="pesapal">PesaPal</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="cash" value="cash_on_delivery">
                                        <label class="form-check-label" for="cash">Cash on Delivery</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Order Summary</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal:</span>
                                        <span>KSh <?php echo number_format($subtotal, 2); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Shipping:</span>
                                        <span>KSh <?php echo number_format($shipping, 2); ?></span>
                                    </div>
                                    <?php if ($discount > 0): ?>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Discount:</span>
                                        <span>-KSh <?php echo number_format($discount, 2); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <hr>
                                    <div class="d-flex justify-content-between fw-bold">
                                        <span>Total:</span>
                                        <span>KSh <?php echo number_format($total, 2); ?></span>
                                    </div>
                                    
                                    <input type="hidden" name="total_amount" value="<?php echo $total; ?>">
                                    
                                    <div class="mt-3">
                                        <button type="submit" name="checkout" class="btn btn-success w-100">Proceed to Checkout</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>