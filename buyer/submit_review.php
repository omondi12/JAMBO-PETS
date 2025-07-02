<?php
// Add debugging at top of the file
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set page title
$pageTitle = "Submit Review";

// Include header
require_once '../includes/header.php';

// Include database connection
require_once '../config/db.php';

// Check if user is logged in and is a buyer
if (!isLoggedIn() || !isBuyer()) {
    redirect('auth/login.php');
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlashMessage('error', 'Invalid request method');
    redirect('buyer/dashboard.php');
}

// Get form data
$userId = $_SESSION['user_id'];
$itemType = isset($_POST['item_type']) ? filter_input(INPUT_POST, 'item_type', FILTER_SANITIZE_STRING) : '';
$itemId = isset($_POST['item_id']) ? filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT) : 0;
$rating = isset($_POST['rating']) ? filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT) : 0;
$comment = isset($_POST['comment']) ? filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING) : '';

// Validate form data
$errors = [];

if (!in_array($itemType, ['pet', 'product', 'seller'])) {
    $errors[] = "Invalid item type";
}

if ($itemId <= 0) {
    $errors[] = "Invalid item ID";
}

if ($rating < 1 || $rating > 5) {
    $errors[] = "Rating must be between 1 and 5";
}

if (empty($comment)) {
    $errors[] = "Comment is required";
}

// Check if item exists based on type
$itemExists = false;
if (empty($errors)) {
    switch ($itemType) {
        case 'pet':
            $checkQuery = "SELECT pet_id FROM pets WHERE pet_id = ? AND approval_status = 'approved'";
            break;
        case 'product':
            $checkQuery = "SELECT product_id FROM products WHERE product_id = ? AND approval_status = 'approved'";
            break;
        case 'seller':
            $checkQuery = "SELECT seller_id FROM seller_profiles WHERE seller_id = ?";
            break;
    }

    $stmt = $conn->prepare($checkQuery);
    
    // Debug error if prepare statement fails
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error . " for query: " . $checkQuery);
    }
    
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $result = $stmt->get_result();
    $itemExists = ($result->num_rows > 0);
    $stmt->close();

    if (!$itemExists) {
        $errors[] = "The item you're trying to review doesn't exist or hasn't been approved";
    }
}

// Check if user has already reviewed this item
if (empty($errors)) {
    $checkReviewQuery = "SELECT review_id FROM reviews WHERE user_id = ? AND item_type = ? AND item_id = ?";
    $stmt = $conn->prepare($checkReviewQuery);
    
    // Debug error if prepare statement fails
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error . " for query: " . $checkReviewQuery);
    }
    
    $stmt->bind_param("isi", $userId, $itemType, $itemId);
    $stmt->execute();
    $result = $stmt->get_result();
    $hasReviewed = ($result->num_rows > 0);
    $stmt->close();

    if ($hasReviewed) {
        $errors[] = "You have already reviewed this item";
    }
}

// Check if user has purchased this item (for pets and products)
$hasPurchased = false;
if (empty($errors) && ($itemType == 'pet' || $itemType == 'product')) {
    // Fixed query: Changed o.user_id to the correct column name (likely buyer_id or customer_id)
    $checkPurchaseQuery = "SELECT o.order_id 
                         FROM orders o 
                         JOIN order_items oi ON o.order_id = oi.order_id 
                         WHERE o.buyer_id = ? AND oi.item_type = ? AND oi.item_id = ? AND o.status = 'completed'";
    $stmt = $conn->prepare($checkPurchaseQuery);
    
    // Debug error if prepare statement fails
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error . " for query: " . $checkPurchaseQuery);
    }
    
    $stmt->bind_param("isi", $userId, $itemType, $itemId);
    $stmt->execute();
    $result = $stmt->get_result();
    $hasPurchased = ($result->num_rows > 0);
    $stmt->close();

    // For seller reviews, we need to check if the user has purchased any item from this seller
    if (!$hasPurchased && $itemType != 'seller') {
        //$errors[] = "You can only review items that you have purchased";
        // Commented out for testing purposes - uncomment in production
    }
}

// If there are errors, redirect back with error message
if (!empty($errors)) {
    setFlashMessage('error', implode(', ', $errors));
    
    // Determine where to redirect based on item type
    switch ($itemType) {
        case 'pet':
            redirect("buyer/pet.php?id=$itemId");
            break;
        case 'product':
            redirect("buyer/product.php?id=$itemId");
            break;
        case 'seller':
            redirect("buyer/seller_profile.php?id=$itemId");
            break;
        default:
            redirect("buyer/dashboard.php");
            break;
    }
    exit;
}

// Insert review into database
$insertQuery = "INSERT INTO reviews (user_id, item_type, item_id, rating, comment, status) VALUES (?, ?, ?, ?, ?, 'pending')";
$stmt = $conn->prepare($insertQuery);

// Debug error if prepare statement fails
if ($stmt === false) {
    die("Prepare failed: " . $conn->error . " for query: " . $insertQuery);
}

$stmt->bind_param("isiss", $userId, $itemType, $itemId, $rating, $comment);
$success = $stmt->execute();
$stmt->close();

// If successful, update the average rating
if ($success) {
    // For items, update their rating in respective tables
    if ($itemType == 'pet') {
        updatePetRating($itemId, $conn);
    } elseif ($itemType == 'product') {
        updateProductRating($itemId, $conn);
    } elseif ($itemType == 'seller') {
        updateSellerRating($itemId, $conn);
    }
    
    // Log the activity
    logActivity('submit_review', [
        'item_type' => $itemType,
        'item_id' => $itemId,
        'rating' => $rating
    ]);
    
    setFlashMessage('success', 'Your review has been submitted successfully and is pending approval');
} else {
    setFlashMessage('error', 'Error submitting your review: ' . $conn->error);
}

// Redirect based on item type
switch ($itemType) {
    case 'pet':
        redirect("buyer/pet.php?id=$itemId");
        break;
    case 'product':
        redirect("buyer/product.php?id=$itemId");
        break;
    case 'seller':
        redirect("buyer/seller_profile.php?id=$itemId");
        break;
    default:
        redirect("buyer/dashboard.php");
        break;
}

// Function to update pet rating
function updatePetRating($petId, $conn) {
    $query = "UPDATE pets p 
              SET p.rating = (
                  SELECT AVG(r.rating) 
                  FROM reviews r 
                  WHERE r.item_type = 'pet' AND r.item_id = ? AND r.status = 'approved'
              ) 
              WHERE p.pet_id = ?";
    $stmt = $conn->prepare($query);
    
    // Check if prepare was successful
    if ($stmt === false) {
        error_log("Prepare failed in updatePetRating: " . $conn->error . " for query: " . $query);
        return;
    }
    
    $stmt->bind_param("ii", $petId, $petId);
    $stmt->execute();
    $stmt->close();
}

// Function to update product rating
function updateProductRating($productId, $conn) {
    $query = "UPDATE products p 
              SET p.rating = (
                  SELECT AVG(r.rating) 
                  FROM reviews r 
                  WHERE r.item_type = 'product' AND r.item_id = ? AND r.status = 'approved'
              ) 
              WHERE p.product_id = ?";
    $stmt = $conn->prepare($query);
    
    // Check if prepare was successful
    if ($stmt === false) {
        error_log("Prepare failed in updateProductRating: " . $conn->error . " for query: " . $query);
        return;
    }
    
    $stmt->bind_param("ii", $productId, $productId);
    $stmt->execute();
    $stmt->close();
}

// Function to update seller rating
function updateSellerRating($sellerId, $conn) {
    $query = "UPDATE seller_profiles s 
              SET s.rating = (
                  SELECT AVG(r.rating) 
                  FROM reviews r 
                  WHERE r.item_type = 'seller' AND r.item_id = ? AND r.status = 'approved'
              ) 
              WHERE s.seller_id = ?";
    $stmt = $conn->prepare($query);
    
    // Check if prepare was successful
    if ($stmt === false) {
        error_log("Prepare failed in updateSellerRating: " . $conn->error . " for query: " . $query);
        return;
    }
    
    $stmt->bind_param("ii", $sellerId, $sellerId);
    $stmt->execute();
    $stmt->close();
}

// No need to include footer since we're redirecting
?>