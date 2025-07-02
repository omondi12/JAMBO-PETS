<?php
ob_start();

/**
 * Redirect user to a different page
 * This will work even if content has been output because we're using output buffering
 */
function redirect($page) {
    header("Location: " . BASE_URL . $page);
    exit;
}
/**
 * Utility functions for Jambo Pets
 * 
 * Contains helper functions used throughout the application
 */

 /**
 * Sanitizes input data to prevent XSS attacks
 * 
 * @param string $data Input to sanitize
 * @return string Sanitized input
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validate email format
 * 
 * @param string $email Email address to validate
 * @return bool True if valid, false otherwise
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate a random string of specified length
 * 
 * @param int $length Length of random string
 * @return string Random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $randomString;
}

/**
 * Format price with Kenyan Shilling symbol
 * 
 * @param float $price Price to format
 * @return string Formatted price
 */
function formatPrice($price) {
    return 'KSh ' . number_format($price, 2);
}

/**
 * Check if user is logged in
 * 
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is a specific type
 * 
 * @param string $type User type to check
 * @return bool True if user is of specified type, false otherwise
 */
function isUserType($type) {
    return isLoggedIn() && $_SESSION['user_type'] == $type;
}

/**
 * Check if user is an admin
 * 
 * @return bool True if admin, false otherwise
 */
function isAdmin() {
    return isUserType('admin');
}

/**
 * Check if user is a seller
 * 
 * @return bool True if seller, false otherwise
 */
function isSeller() {
    return isUserType('seller');
}

/**
 * Check if user is a buyer
 * 
 * @return bool True if buyer, false otherwise
 */
function isBuyer() {
    return isUserType('buyer');
}

/**
 * Redirect to specified page
 * 
 * @param string $page Page to redirect to
 * @return void
 */


/**
 * Display error message
 * 
 * @param string $message Error message to display
 * @return string Formatted error message
 */
function showError($message) {
    return '<div class="alert alert-danger" role="alert">' . $message . '</div>';
}

/**
 * Display success message
 * 
 * @param string $message Success message to display
 * @return string Formatted success message
 */
function showSuccess($message) {
    return '<div class="alert alert-success" role="alert">' . $message . '</div>';
}

/**
 * Display info message
 * 
 * @param string $message Info message to display
 * @return string Formatted info message
 */
function showInfo($message) {
    return '<div class="alert alert-info" role="alert">' . $message . '</div>';
}

/**
 * Upload image to server
 * 
 * @param array $file File data from $_FILES
 * @param string $targetDir Directory to upload to
 * @param string $fileNamePrefix Prefix for file name
 * @return string|bool File path on success, false on failure
 */
function uploadImage($file, $targetDir, $fileNamePrefix = '') {
    // Check if file was uploaded without errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Get file info
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    
    // Get file extension
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Allowed extensions
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    // Check extension
    if (!in_array($fileExt, $allowedExtensions)) {
        return false;
    }
    
    // Check file size (max 5MB)
    if ($fileSize > 5000000) {
        return false;
    }
    
    // Create unique file name
    $newFileName = $fileNamePrefix . '_' . time() . '_' . generateRandomString(8) . '.' . $fileExt;
    $targetFilePath = $targetDir . $newFileName;
    
    // Upload file
    if (move_uploaded_file($fileTmpName, $targetFilePath)) {
        return $newFileName;
    } else {
        return false;
    }
}

/**
 * Calculate age from date
 * 
 * @param string $date Date in Y-m-d format
 * @return string Formatted age
 */
function calculateAge($date) {
    $birthDate = new DateTime($date);
    $currentDate = new DateTime();
    $interval = $birthDate->diff($currentDate);
    
    if ($interval->y > 0) {
        return $interval->y . ' year' . ($interval->y > 1 ? 's' : '');
    } elseif ($interval->m > 0) {
        return $interval->m . ' month' . ($interval->m > 1 ? 's' : '');
    } else {
        return $interval->d . ' day' . ($interval->d > 1 ? 's' : '');
    }
}

// Include database connection if not already included
if (!isset($conn)) {
    require_once __DIR__ . '/../config/db.php';
}

// ... [rest of your existing functions] ...

/**
 * Log user activity
 * 
 * @param string $page Page visited
 * @param array|string $action Action performed
 * @param string|array $itemType Type of item (pet, product, blog, other) or additional data as array
 * @param int $itemId ID of item
 * @return bool True on success, false on failure
 */
function logActivity($page, $action = null, $itemType = null, $itemId = null) {
    global $conn;
    
    // Make sure database connection is available
    if (!isset($conn) || $conn === null) {
        // Try to reconnect if connection is not available
        require_once __DIR__ . '/../config/db.php';
        
        // If still not available, log error and return
        if (!isset($conn) || $conn === null) {
            error_log("Database connection not available in logActivity function");
            return false;
        }
    }
    
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    
    // Convert action to JSON string if it's an array
    if (is_array($action)) {
        $action = json_encode($action);
    }
    
    // Convert itemType to JSON string if it's an array
    if (is_array($itemType)) {
        $itemType = json_encode($itemType);
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO analytics (user_id, page_visited, action_type, item_type, item_id, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            error_log("Prepare failed: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("isssiss", $userId, $page, $action, $itemType, $itemId, $ipAddress, $userAgent);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    } catch (Exception $e) {
        error_log("Error in logActivity: " . $e->getMessage());
        return false;
    }
}
 
// Include the database connection
require_once __DIR__ . '/../config/db.php';

/**
 * Set flash message to be displayed on the next page load
 * @param string $message Message to display
 * @param string $type Type of alert (success, danger, warning, info)
 */
function setFlashMessage($message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

 

/**
 * Get count of items in the user's wishlist
 * @return int Number of items in wishlist
 */
function getWishlistCount() {
    // If user is not logged in, return 0
    if (!isset($_SESSION['user_id'])) {
        return 0;
    }
    
    global $conn;
    $userId = $_SESSION['user_id'];
    
    $sql = "SELECT COUNT(*) as count FROM wishlist_items WHERE user_id = ?";
    $result = executeQuery($sql, [$userId]);
    $row = $result->fetch_assoc();
    
    return $row['count'] ?? 0;
}

/**
 * Get count of items in the user's shopping cart
 * @return int Number of items in cart
 */
function getCartCount() {
    // If user is not logged in, return 0
    if (!isset($_SESSION['user_id'])) {
        return 0;
    }
    
    global $conn;
    $userId = $_SESSION['user_id'];
    
    $sql = "SELECT SUM(quantity) as count FROM cart_items WHERE user_id = ?";
    $result = executeQuery($sql, [$userId]);
    $row = $result->fetch_assoc();
    
    // If there are no items or NULL result, return 0
    return $row['count'] ?? 0;
}

/**
 * Check if an item is in the user's wishlist
 * @param string $itemType Type of item ('pet' or 'product')
 * @param int $itemId ID of the item
 * @return bool True if item is in wishlist, false otherwise
 */
function isInWishlist($itemType, $itemId) {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    global $conn;
    $userId = $_SESSION['user_id'];
    
    $sql = "SELECT COUNT(*) as count FROM wishlist_items 
            WHERE user_id = ? AND item_type = ? AND item_id = ?";
    
    $result = executeQuery($sql, [$userId, $itemType, $itemId]);
    $row = $result->fetch_assoc();
    
    return $row['count'] > 0;
}

/**
 * Check if an item is in the user's cart
 * @param string $itemType Type of item ('pet' or 'product')
 * @param int $itemId ID of the item
 * @return bool True if item is in cart, false otherwise
 */
function isInCart($itemType, $itemId) {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    global $conn;
    $userId = $_SESSION['user_id'];
    
    $sql = "SELECT COUNT(*) as count FROM cart_items 
            WHERE user_id = ? AND item_type = ? AND item_id = ?";
    
    $result = executeQuery($sql, [$userId, $itemType, $itemId]);
    $row = $result->fetch_assoc();
    
    return $row['count'] > 0;
}

/**
 * Get all counties for location filter
 * @return array List of counties
 */
function getAllCounties() {
    global $conn;
    
    $sql = "SELECT * FROM counties ORDER BY county_name ASC";
    $result = executeQuery($sql);
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get all categories
 * @param int|null $parentId Parent category ID for subcategories, null for top-level categories
 * @return array List of categories
 */
function getCategories($parentId = null) {
    global $conn;
    
    if ($parentId === null) {
        $sql = "SELECT * FROM categories WHERE parent_id IS NULL AND is_active = 1 ORDER BY name ASC";
        $result = executeQuery($sql);
    } else {
        $sql = "SELECT * FROM categories WHERE parent_id = ? AND is_active = 1 ORDER BY name ASC";
        $result = executeQuery($sql, [$parentId]);
    }
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get category name by ID
 * @param int $categoryId Category ID
 * @return string Category name
 */
function getCategoryName($categoryId) {
    global $conn;
    
    $sql = "SELECT name FROM categories WHERE category_id = ?";
    $result = executeQuery($sql, [$categoryId]);
    $row = $result->fetch_assoc();
    
    return $row['name'] ?? 'Unknown Category';
}

/**
 * Get primary image for a pet or product
 * @param string $itemType Type of item ('pet' or 'product')
 * @param int $itemId ID of the item
 * @return string Image path or default image if not found
 */
function getPrimaryImage($itemType, $itemId) {
    global $conn;
    
    // First try to get primary image
    $sql = "SELECT image_path FROM images 
            WHERE item_type = ? AND item_id = ? AND is_primary = 1 
            LIMIT 1";
    
    $result = executeQuery($sql, [$itemType, $itemId]);
    $row = $result->fetch_assoc();
    
    if ($row) {
        return $row['image_path'];
    }
    
    // If no primary image, get any image
    $sql = "SELECT image_path FROM images 
            WHERE item_type = ? AND item_id = ? 
            LIMIT 1";
    
    $result = executeQuery($sql, [$itemType, $itemId]);
    $row = $result->fetch_assoc();
    
    if ($row) {
        return $row['image_path'];
    }
    
    // Default image based on type
    if ($itemType == 'pet') {
        return BASE_URL . 'assets/images/default-pet.jpg';
    } else {
        return BASE_URL . 'assets/images/default-product.jpg';
    }
}

/**
 * Get seller information by seller ID
 * @param int $sellerId Seller ID
 * @return array Seller information
 */
function getSellerInfo($sellerId) {
    global $conn;
    
    $sql = "SELECT s.*, u.first_name, u.last_name, u.county, u.email, u.phone 
            FROM seller_profiles s
            JOIN users u ON s.user_id = u.user_id
            WHERE s.seller_id = ?";
    
    $result = executeQuery($sql, [$sellerId]);
    return $result->fetch_assoc();
}

/**
 * Record a page view in analytics
 * @param string $page Page visited
 * @param string $action Action type (optional)
 * @param string $itemType Item type ('pet', 'product', 'blog', 'other')
 * @param int $itemId Item ID
 */
function recordAnalytics($page, $action = null, $itemType = 'other', $itemId = null) {
    global $conn;
    
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    $sql = "INSERT INTO analytics (user_id, page_visited, action_type, item_type, item_id, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    executeQuery($sql, [$userId, $page, $action, $itemType, $itemId, $ipAddress, $userAgent]);
}
/**
 * Converts a timestamp into a relative time string (e.g., "5 minutes ago", "2 days ago")
 * 
 * @param string|DateTime $timestamp The timestamp to convert
 * @param boolean $full Whether to show the full date/time difference or just the most significant part
 * @return string The relative time string
 */
function time_elapsed_string($timestamp, $full = false) {
    if (!is_numeric($timestamp)) {
        $timestamp = strtotime($timestamp);
    }
    
    $now = time();
    $diff = $now - $timestamp;
    
    if ($diff < 0) {
        return 'Just now';
    }
    
    // Time units in seconds
    $time_units = array(
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    );
    
    // For "full" display, we'll use an array to collect all parts
    $parts = array();
    
    foreach ($time_units as $seconds => $unit) {
        // Calculate the amount of this time unit
        $count = floor($diff / $seconds);
        
        // If there's at least one of this unit
        if ($count > 0) {
            // Remove the counted time from the difference
            $diff -= $count * $seconds;
            
            // Add this unit to our parts array
            $parts[] = $count . ' ' . $unit . ($count > 1 ? 's' : '');
            
            // If we're not showing the full string, we can stop at the first non-zero unit
            if (!$full) {
                break;
            }
        }
    }
    
    // For recent times (less than 1 minute)
    if (empty($parts)) {
        return 'Just now';
    }
    
    // Join all parts with commas and "and" for the last part
    if ($full && count($parts) > 1) {
        $last_part = array_pop($parts);
        $string = implode(', ', $parts) . ' and ' . $last_part;
    } else {
        $string = $parts[0];
    }
    
    return $string . ' ago';
}
 
