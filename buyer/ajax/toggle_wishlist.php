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
    error_log("Starting toggle_wishlist.php execution");
    
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
    if (!isset($_POST['item_type']) || !isset($_POST['item_id'])) {
        error_log("Missing required parameters: item_type or item_id");
        echo json_encode([
            'success' => false,
            'message' => 'Missing required parameters'
        ]);
        exit;
    }

    // Get and sanitize inputs
    $itemType = mysqli_real_escape_string($conn, $_POST['item_type']);
    $itemId = intval($_POST['item_id']);
    error_log("Parameters received - Item Type: $itemType, Item ID: $itemId");

    // Validate item type
    if ($itemType !== 'pet' && $itemType !== 'product') {
        error_log("Invalid item type: $itemType");
        echo json_encode([
            'success' => false,
            'message' => 'Invalid item type'
        ]);
        exit;
    }

    // Check if item exists
    if ($itemType === 'pet') {
        $checkQuery = "SELECT * FROM pets WHERE pet_id = $itemId";
    } else {
        $checkQuery = "SELECT * FROM products WHERE product_id = $itemId";
    }
    error_log("Check query: $checkQuery");

    $checkResult = $conn->query($checkQuery);
    
    if (!$checkResult) {
        throw new Exception("Database query failed: " . $conn->error);
    }

    if ($checkResult->num_rows === 0) {
        error_log("Item not found in database");
        echo json_encode([
            'success' => false,
            'message' => 'Item not found'
        ]);
        exit;
    }
    error_log("Item found in database");

    // Begin transaction for data consistency
    error_log("Beginning transaction");
    if (!$conn->begin_transaction()) {
        throw new Exception("Failed to begin transaction: " . $conn->error);
    }

    // Check if item is already in wishlist
    // CHANGED: Using the correct column names from the actual database
    $checkWishlistQuery = "SELECT * FROM wishlist_items WHERE user_id = $userId AND item_type = '$itemType' AND item_id = $itemId";
    error_log("Check wishlist query: $checkWishlistQuery");
    $checkWishlistResult = $conn->query($checkWishlistQuery);
    
    if (!$checkWishlistResult) {
        throw new Exception("Wishlist query failed: " . $conn->error);
    }

    if ($checkWishlistResult->num_rows > 0) {
        // Remove from wishlist
        error_log("Item found in wishlist. Removing...");
        $wishlistItem = $checkWishlistResult->fetch_assoc();
        
        // CHANGED: Using wishlist_id instead of wishlist_item_id
        $deleteQuery = "DELETE FROM wishlist_items WHERE wishlist_id = {$wishlistItem['wishlist_id']}";
        error_log("Delete query: $deleteQuery");
        $deleteResult = $conn->query($deleteQuery);
        
        if (!$deleteResult) {
            throw new Exception("Failed to remove item from wishlist: " . $conn->error);
        }
        
        // Commit the transaction
        error_log("Committing transaction");
        if (!$conn->commit()) {
            throw new Exception("Failed to commit transaction: " . $conn->error);
        }
        
        // Log activity
        logActivity('remove_from_wishlist', [
            'item_type' => $itemType,
            'item_id' => $itemId
        ]);
        
        error_log("Item successfully removed from wishlist");
        echo json_encode([
            'success' => true,
            'action' => 'removed',
            'message' => 'Item removed from wishlist'
        ]);
    } else {
        // Add to wishlist
        error_log("Item not in wishlist. Adding...");
        
        // CHANGED: Using the actual database column structure
        // No need to specify date_added as it defaults to CURRENT_TIMESTAMP
        $insertQuery = "INSERT INTO wishlist_items (user_id, item_type, item_id) 
                        VALUES ($userId, '$itemType', $itemId)";
        error_log("Insert query: $insertQuery");
        $insertResult = $conn->query($insertQuery);
        
        if (!$insertResult) {
            throw new Exception("Failed to add item to wishlist: " . $conn->error);
        }
        
        // Commit the transaction
        error_log("Committing transaction");
        if (!$conn->commit()) {
            throw new Exception("Failed to commit transaction: " . $conn->error);
        }
        
        // Log activity
        logActivity('add_to_wishlist', [
            'item_type' => $itemType,
            'item_id' => $itemId
        ]);
        
        error_log("Item successfully added to wishlist");
        echo json_encode([
            'success' => true,
            'action' => 'added',
            'message' => 'Item added to wishlist'
        ]);
    }

} catch (Exception $e) {
    // Rollback transaction if active
    error_log("ERROR: " . $e->getMessage() . " at line " . $e->getLine() . " in " . $e->getFile());
    
    if (isset($conn) && $conn->ping()) {
        error_log("Rolling back transaction");
        $conn->rollback();
    }
    
    // Log the error
    error_log("Wishlist Error: " . $e->getMessage());
    
    // Return error to client
    echo json_encode([
        'success' => false,
        'message' => 'There was an error updating your wishlist. Please try again.',
        'debug_message' => $e->getMessage() // Remove this in production
    ]);
}

// Clean up output buffer
ob_end_flush();
exit;
?>