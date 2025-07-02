<?php
// Start the session
session_start();

// Include database connection
require_once 'config/db.php';
require_once 'includes/functions.php';

// Check if user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'seller') {
    header('Location: ../auth/login.php');
    exit();
}

// Get order ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

echo "<h2>Debug Information</h2>";
echo "Order ID: " . $order_id . "<br>";
echo "User ID: " . $user_id . "<br>";

// Check if seller_profiles table exists and get seller_id
echo "<h3>1. Checking seller_profiles table...</h3>";
$sql = "SHOW TABLES LIKE 'seller_profiles'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    echo "✓ seller_profiles table exists<br>";
    
    // Get seller ID
    $sql = "SELECT seller_id FROM seller_profiles WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing seller query: " . $conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $seller_data = $result->fetch_assoc();
    
    if ($seller_data) {
        $seller_id = $seller_data['seller_id'];
        echo "✓ Seller ID found: " . $seller_id . "<br>";
    } else {
        echo "✗ No seller profile found for user ID: " . $user_id . "<br>";
        exit();
    }
} else {
    echo "✗ seller_profiles table does not exist<br>";
    exit();
}

// Check if orders table exists
echo "<h3>2. Checking orders table...</h3>";
$sql = "SHOW TABLES LIKE 'orders'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    echo "✓ orders table exists<br>";
    
    // Get order details
    $sql = "SELECT o.*, u.username, u.email, u.phone as buyer_phone 
            FROM orders o
            INNER JOIN users u ON o.buyer_id = u.user_id
            WHERE o.order_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing order query: " . $conn->error);
    }
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    
    if ($order) {
        echo "✓ Order found: #" . $order['order_id'] . "<br>";
        echo "✓ Buyer: " . $order['username'] . "<br>";
    } else {
        echo "✗ Order not found with ID: " . $order_id . "<br>";
        exit();
    }
} else {
    echo "✗ orders table does not exist<br>";
    exit();
}

// Check if order_items table exists
echo "<h3>3. Checking order_items table...</h3>";
$sql = "SHOW TABLES LIKE 'order_items'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    echo "✓ order_items table exists<br>";
    
    // Get order items for this seller
    $sql = "SELECT * FROM order_items WHERE order_id = ? AND seller_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing order items query: " . $conn->error);
    }
    $stmt->bind_param("ii", $order_id, $seller_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "✓ Order items found: " . $result->num_rows . " items<br>";
        while ($item = $result->fetch_assoc()) {
            echo "- Item ID: " . $item['item_id'] . ", Type: " . $item['item_type'] . ", Status: " . $item['status'] . "<br>";
        }
    } else {
        echo "✗ No order items found for order ID: " . $order_id . " and seller ID: " . $seller_id . "<br>";
    }
} else {
    echo "✗ order_items table does not exist<br>";
    exit();
}

// Check if pets table exists
echo "<h3>4. Checking pets table...</h3>";
$sql = "SHOW TABLES LIKE 'pets'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    echo "✓ pets table exists<br>";
} else {
    echo "✗ pets table does not exist<br>";
}

// Check if products table exists
echo "<h3>5. Checking products table...</h3>";
$sql = "SHOW TABLES LIKE 'products'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    echo "✓ products table exists<br>";
} else {
    echo "✗ products table does not exist<br>";
}

// Check if images table exists
echo "<h3>6. Checking images table...</h3>";
$sql = "SHOW TABLES LIKE 'images'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    echo "✓ images table exists<br>";
} else {
    echo "✗ images table does not exist<br>";
}

echo "<h3>All checks completed!</h3>";
echo "<a href='dashboard.php'>Back to Dashboard</a>";
?>