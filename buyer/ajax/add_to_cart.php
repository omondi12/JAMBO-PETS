<?php
// Include database connection and necessary functions
require_once '../../config/db.php';
require_once '../../includes/functions.php';

// Set error reporting for development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Start output buffering to catch any PHP errors
ob_start();

try {
    // Log the start of script execution
    error_log("Starting add_to_cart.php execution");
    
    // Check if user is logged in and is a buyer
    if (!isLoggedIn() || !isBuyer()) {
        redirect('auth/login.php');
    }

    // Verify database connection
    if (!$conn || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn ? $conn->connect_error : "Connection object not initialized"));
    }
    error_log("Database connection verified");

    // Get the user ID from session
    $userId = $_SESSION['user_id'];
    error_log("User ID retrieved: $userId");

    // Check if required parameters are provided
    if (!isset($_POST['item_type']) || !isset($_POST['item_id']) || !isset($_POST['quantity'])) {
        error_log("Missing required parameters");
        echo json_encode([
            'success' => false,
            'message' => 'Missing required parameters'
        ]);
        exit;
    }

    // Get and sanitize inputs
    $itemType = mysqli_real_escape_string($conn, $_POST['item_type']);
    $itemId = intval($_POST['item_id']);
    $quantity = intval($_POST['quantity']);
    error_log("Parameters received - Item Type: $itemType, Item ID: $itemId, Quantity: $quantity");

    // Validate item type
    if ($itemType !== 'pet' && $itemType !== 'product') {
        error_log("Invalid item type: $itemType");
        echo json_encode([
            'success' => false,
            'message' => 'Invalid item type'
        ]);
        exit;
    }

    // Validate quantity
    if ($quantity <= 0) {
        error_log("Invalid quantity: $quantity");
        echo json_encode([
            'success' => false,
            'message' => 'Quantity must be greater than zero'
        ]);
        exit;
    }

    // Check if item exists and is available
    if ($itemType === 'pet') {
        // Check if 'quantity' column exists in the pets table, if not, use a different query
        $result = $conn->query("SHOW COLUMNS FROM pets LIKE 'quantity'");
        if ($result->num_rows > 0) {
            $checkQuery = "SELECT * FROM pets WHERE pet_id = $itemId AND status = 'available' AND approval_status = 'approved'";
        } else {
            // If no quantity column, assume it's one-of-a-kind
            $checkQuery = "SELECT *, 1 as quantity FROM pets WHERE pet_id = $itemId AND status = 'available' AND approval_status = 'approved'";
        }
    } else {
        // Same check for products table
        $result = $conn->query("SHOW COLUMNS FROM products LIKE 'quantity'");
        if ($result->num_rows > 0) {
            $checkQuery = "SELECT * FROM products WHERE product_id = $itemId AND status = 'available' AND approval_status = 'approved'";
        } else {
            // If no quantity column, assume we have enough stock
            $checkQuery = "SELECT *, 999 as quantity FROM products WHERE product_id = $itemId AND status = 'available' AND approval_status = 'approved'";
        }
    }
    error_log("Check query: $checkQuery");

    $checkResult = $conn->query($checkQuery);
    
    if (!$checkResult) {
        throw new Exception("Database query failed: " . $conn->error);
    }

    if ($checkResult->num_rows === 0) {
        error_log("Item not found or not available");
        echo json_encode([
            'success' => false,
            'message' => 'Item not found or not available'
        ]);
        exit;
    }

    $item = $checkResult->fetch_assoc();
    error_log("Item found in database: " . print_r($item, true));

    // Check if requested quantity is available
    if (isset($item['quantity']) && $quantity > $item['quantity']) {
        error_log("Requested quantity exceeds available stock");
        echo json_encode([
            'success' => false,
            'message' => 'Requested quantity exceeds available stock'
        ]);
        exit;
    }

    // Check if item already exists in cart
    $checkCartQuery = "SELECT * FROM cart_items WHERE user_id = $userId AND item_type = '$itemType' AND item_id = $itemId";
    error_log("Check cart query: $checkCartQuery");
    $checkCartResult = $conn->query($checkCartQuery);
    
    if (!$checkCartResult) {
        throw new Exception("Database query failed: " . $conn->error);
    }

    // Begin transaction for data consistency
    error_log("Beginning transaction");
    if (!$conn->begin_transaction()) {
        throw new Exception("Failed to begin transaction: " . $conn->error);
    }

    if ($checkCartResult->num_rows > 0) {
        // Update existing cart item
        error_log("Item already in cart. Updating quantity...");
        $cartItem = $checkCartResult->fetch_assoc();
        $newQuantity = $cartItem['quantity'] + $quantity;
        
        if (isset($item['quantity']) && $newQuantity > $item['quantity']) {
            error_log("New quantity exceeds available stock");
            echo json_encode([
                'success' => false,
                'message' => 'Cannot add more items than available in stock'
            ]);
            $conn->rollback();
            exit;
        }
        
        // CHANGED: Using cart_item_id and we don't need updated_at as it's not in your schema
        $updateQuery = "UPDATE cart_items SET quantity = $newQuantity WHERE cart_item_id = {$cartItem['cart_item_id']}";
        error_log("Update query: $updateQuery");
        $updateResult = $conn->query($updateQuery);
        
        if (!$updateResult) {
            throw new Exception("Failed to update cart: " . $conn->error);
        }
    } else {
        // Add new cart item
        error_log("Adding new item to cart");
        
        // CHANGED: Using the actual database column structure
        // No need to specify date_added as it defaults to CURRENT_TIMESTAMP
        $insertQuery = "INSERT INTO cart_items (user_id, item_type, item_id, quantity) 
                        VALUES ($userId, '$itemType', $itemId, $quantity)";
        error_log("Insert query: $insertQuery");
        $insertResult = $conn->query($insertQuery);
        
        if (!$insertResult) {
            throw new Exception("Failed to add item to cart: " . $conn->error);
        }
    }

    // Commit the transaction
    error_log("Committing transaction");
    if (!$conn->commit()) {
        throw new Exception("Failed to commit transaction: " . $conn->error);
    }

    // Log activity
    logActivity('add_to_cart', [
        'item_type' => $itemType,
        'item_id' => $itemId,
        'quantity' => $quantity
    ]);

    error_log("Item successfully added to cart");
    echo json_encode([
        'success' => true,
        'message' => 'Item added to cart successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction if active
    error_log("ERROR: " . $e->getMessage() . " at line " . $e->getLine() . " in " . $e->getFile());
    
    if (isset($conn) && $conn->ping()) {
        error_log("Rolling back transaction");
        $conn->rollback();
    }
    
    // Log the error
    error_log("Cart Error: " . $e->getMessage());
    
    // Return error to client
    echo json_encode([
        'success' => false,
        'message' => 'There was an error adding to cart. Please try again.',
        'debug_message' => $e->getMessage() // Remove this in production
    ]);
}

// Clean up output buffer
ob_end_flush();
exit;
?>