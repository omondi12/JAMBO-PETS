<?php
// Include database connection and necessary functions
require_once '../../config/db.php';
require_once '../../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'count' => 0
    ]);
    exit;
}

// Get the user ID from session
$userId = $_SESSION['user_id'];

// Get wishlist count
$wishlistQuery = "SELECT COUNT(*) as count FROM wishlist_items WHERE user_id = $userId";
$wishlistResult = $conn->query($wishlistQuery);
$wishlistCount = $wishlistResult->fetch_assoc()['count'];

echo json_encode([
    'count' => $wishlistCount
]);
exit;
?>