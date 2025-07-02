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

// Handle removal from wishlist
if (isset($_GET['remove']) && isset($_GET['type']) && isset($_GET['id'])) {
    $item_id = $_GET['id'];
    $item_type = $_GET['type'];
    
    $stmt = $conn->prepare("DELETE FROM wishlist_items WHERE user_id = ? AND item_type = ? AND item_id = ?");
    $stmt->bind_param("isi", $user_id, $item_type, $item_id);
    $stmt->execute();
    
    // Set success message
    $_SESSION['success_msg'] = "Item removed from wishlist successfully!";
    header("Location: wishlist.php");
    exit();
}

// Handle add to cart action
if (isset($_GET['add_to_cart']) && isset($_GET['type']) && isset($_GET['id'])) {
    $item_id = $_GET['id'];
    $item_type = $_GET['type'];
    
    // Check if item already exists in cart
    $check_stmt = $conn->prepare("SELECT * FROM cart_items WHERE user_id = ? AND item_type = ? AND item_id = ?");
    $check_stmt->bind_param("isi", $user_id, $item_type, $item_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Item exists, update quantity
        $stmt = $conn->prepare("UPDATE cart_items SET quantity = quantity + 1 WHERE user_id = ? AND item_type = ? AND item_id = ?");
        $stmt->bind_param("isi", $user_id, $item_type, $item_id);
    } else {
        // Item doesn't exist, insert new
        $stmt = $conn->prepare("INSERT INTO cart_items (user_id, item_type, item_id, quantity) VALUES (?, ?, ?, 1)");
        $stmt->bind_param("isi", $user_id, $item_type, $item_id);
    }
    $stmt->execute();
    
    // Remove from wishlist
    $delete_stmt = $conn->prepare("DELETE FROM wishlist_items WHERE user_id = ? AND item_type = ? AND item_id = ?");
    $delete_stmt->bind_param("isi", $user_id, $item_type, $item_id);
    $delete_stmt->execute();
    
    // Set success message
    $_SESSION['success_msg'] = "Item added to cart and removed from wishlist!";
    header("Location: wishlist.php");
    exit();
}

// Fetch wishlist items with a cleaner, more direct approach
$wishlist_items = [];

// First get all wishlist item IDs and types
$base_query = "SELECT * FROM wishlist_items WHERE user_id = ?";
$stmt = $conn->prepare($base_query);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$wishlist_result = $stmt->get_result();

// Process each wishlist item separately based on its type
while ($wishlist_item = $wishlist_result->fetch_assoc()) {
    $item_id = $wishlist_item['item_id'];
    $item_type = $wishlist_item['item_type'];
    $item_data = ['id' => $item_id, 'type' => $item_type, 'date_added' => $wishlist_item['date_added']];
    
    // Get item details based on type
    if ($item_type == 'pet') {
        // Get pet details
        $pet_query = "SELECT name, price FROM pets WHERE pet_id = ?";
        $pet_stmt = $conn->prepare($pet_query);
        if ($pet_stmt === false) {
            error_log("Error preparing pet query: " . $conn->error);
            continue; // Skip this item and move to the next
        }
        
        $pet_stmt->bind_param("i", $item_id);
        $pet_stmt->execute();
        $pet_result = $pet_stmt->get_result();
        
        if ($pet_result->num_rows > 0) {
            $pet_data = $pet_result->fetch_assoc();
            $item_data['name'] = $pet_data['name'];
            $item_data['price'] = $pet_data['price'];
            
            // Get pet image
            $pet_img_query = "SELECT image_path FROM images WHERE item_id = ? AND item_type = 'pet' ORDER BY image_id ASC LIMIT 1";
            $pet_img_stmt = $conn->prepare($pet_img_query);
            if ($pet_img_stmt === false) {
                error_log("Error preparing pet image query: " . $conn->error);
                // Continue with the item, just without an image
            } else {
                $pet_img_stmt->bind_param("i", $item_id);
                $pet_img_stmt->execute();
                $pet_img_result = $pet_img_stmt->get_result();
                
                if ($pet_img_result->num_rows > 0) {
                    $pet_img_data = $pet_img_result->fetch_assoc();
                    $item_data['image'] = $pet_img_data['image_path'];
                    $item_data['image_type'] = 'path';
                }
                $pet_img_stmt->close();
            }
            
            $wishlist_items[] = $item_data;
        }
        $pet_stmt->close();
    } else if ($item_type == 'product') {
        // Get product details
        // Check if the 'image' column exists in the products table
        $check_column_query = "SHOW COLUMNS FROM products LIKE 'image'";
        $check_column_result = $conn->query($check_column_query);
        $image_column_exists = $check_column_result->num_rows > 0;
        
        // Prepare the products query based on column existence
        if ($image_column_exists) {
            $product_query = "SELECT name, price, image FROM products WHERE product_id = ?";
        } else {
            $product_query = "SELECT name, price FROM products WHERE product_id = ?";
        }
        
        $product_stmt = $conn->prepare($product_query);
        if ($product_stmt === false) {
            error_log("Error preparing product query: " . $conn->error);
            continue; // Skip this item and move to the next
        }
        
        $product_stmt->bind_param("i", $item_id);
        $product_stmt->execute();
        $product_result = $product_stmt->get_result();
        
        if ($product_result->num_rows > 0) {
            $product_data = $product_result->fetch_assoc();
            $item_data['name'] = $product_data['name'];
            $item_data['price'] = $product_data['price'];
            
            // Check if product has direct image (if the column exists)
            if ($image_column_exists && !empty($product_data['image'])) {
                $item_data['image'] = $product_data['image'];
                $item_data['image_type'] = 'direct';
            } else {
                // Try to get image from images table
                $product_img_query = "SELECT image_path FROM images WHERE item_id = ? AND item_type = 'product' ORDER BY image_id ASC LIMIT 1";
                $product_img_stmt = $conn->prepare($product_img_query);
                if ($product_img_stmt === false) {
                    error_log("Error preparing product image query: " . $conn->error);
                    // Continue with the item, just without an image
                } else {
                    $product_img_stmt->bind_param("i", $item_id);
                    $product_img_stmt->execute();
                    $product_img_result = $product_img_stmt->get_result();
                    
                    if ($product_img_result->num_rows > 0) {
                        $product_img_data = $product_img_result->fetch_assoc();
                        $item_data['image'] = $product_img_data['image_path'];
                        $item_data['image_type'] = 'path';
                    }
                    $product_img_stmt->close();
                }
            }
            
            $wishlist_items[] = $item_data;
        }
        $product_stmt->close();
    }
}

// Log the final wishlist items for debugging
error_log("Final wishlist items: " . print_r($wishlist_items, true));

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
$page_title = "My Wishlist";
include_once '../includes/header.php';
?>

<div class="container py-5">
    <div class="row">

    <?php include_once 'sidebar.php'; ?>

        <div class="col-lg-9">
            <h2 class="mb-4">My Wishlist</h2>
            
            <?php if (isset($_SESSION['success_msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                        echo $_SESSION['success_msg']; 
                        unset($_SESSION['success_msg']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (empty($wishlist_items)): ?>
                <div class="alert alert-info">
                    Your wishlist is empty. <a href="browse.php">Continue shopping</a>
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php foreach ($wishlist_items as $item): ?>
                        <div class="col">
                            <div class="card h-100">
                                <?php 
                                // Debug information
                                echo "<!-- Item: {$item['name']}, Type: {$item['type']}, ID: {$item['id']} -->";
                                
                                if (isset($item['image']) && !empty($item['image'])): 
                                    // Determine the correct image path
                                    if (isset($item['image_type']) && $item['image_type'] == 'direct' && $item['type'] == 'product') {
                                        $image_path = '../uploads/products/' . $item['image'];
                                    } else {
                                        // For path-based images (both pets and products)
                                        $image_path = strpos($item['image'], '../') === 0 ? 
                                                    $item['image'] : 
                                                    '../' . $item['image'];
                                    }
                                ?>
                                    <img src="<?php echo $image_path; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['name']); ?>" style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <span class="text-muted">No image available</span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($item['name']); ?></h5>
                                    <p class="card-text">
                                        <strong>Price:</strong> KSh <?php echo number_format($item['price'], 2); ?><br>
                                        <strong>Type:</strong> <?php echo ucfirst($item['type']); ?><br>
                                        <small class="text-muted">Added on: <?php echo date('M d, Y', strtotime($item['date_added'])); ?></small>
                                    </p>
                                </div>
                                <div class="card-footer bg-white border-top-0">
                                    <div class="d-flex justify-content-between">
                                        <?php 
                                        // Generate the correct view details link based on item type
                                        if ($item['type'] == 'pet') {
                                            $details_url = "pet.php?id=" . $item['id'];
                                        } else if ($item['type'] == 'product') {
                                            $details_url = "product.php?id=" . $item['id'];
                                        }
                                        ?>
                                        <a href="<?php echo $details_url; ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                                        <a href="wishlist.php?add_to_cart=1&type=<?php echo $item['type']; ?>&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-success">Add to Cart</a>
                                        <a href="wishlist.php?remove=1&type=<?php echo $item['type']; ?>&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to remove this item from your wishlist?')">Remove</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-4">
                    <a href="browse.php" class="btn btn-primary">Continue Shopping</a>
                    <a href="cart.php" class="btn btn-outline-primary">View Cart</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>