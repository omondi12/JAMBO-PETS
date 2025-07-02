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

// Get cart count
$cartQuery = "SELECT COUNT(*) as count FROM cart_items WHERE user_id = $userId";
$cartResult = $conn->query($cartQuery);
$cartCount = $cartResult->fetch_assoc()['count'];

echo json_encode([
    'count' => $cartCount
]);
exit;
?>