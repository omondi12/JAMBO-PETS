<?php
 
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define base URL for the site
define('BASE_URL', 'http://localhost/Jambo Pets/');

// Include utility functions
require_once __DIR__ . '/functions.php';

// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userType = $isLoggedIn ? $_SESSION['user_type'] : '';
$userName = $isLoggedIn ? $_SESSION['first_name'] : '';

// Note: No HTML output yet, ensuring all redirects can happen first

// Get admin user data
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE user_id = ? AND user_type = 'admin'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Get notification counts
$notifications = [];
$total_notifications = 0;

// 1. Count pending pet listings
$pet_query = "SELECT COUNT(*) as count FROM pets WHERE approval_status = 'pending'";
$pet_result = $conn->query($pet_query);
$pending_pets = $pet_result->fetch_assoc()['count'];

// 2. Count pending product listings
$product_query = "SELECT COUNT(*) as count FROM products WHERE approval_status = 'pending'";
$product_result = $conn->query($product_query);
$pending_products = $product_result->fetch_assoc()['count'];

// Total pending listings
$pending_listings = $pending_pets + $pending_products;

// 3. Count new orders (orders placed today or in last 24 hours)
$order_query = "SELECT COUNT(*) as count FROM orders WHERE order_date >= DATE_SUB(NOW(), INTERVAL 1 DAY) AND status = 'pending'";
$order_result = $conn->query($order_query);
$new_orders = $order_result->fetch_assoc()['count'];

// 4. Count new user registrations (users registered today or in last 24 hours)
$user_query = "SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) AND user_type != 'admin'";
$user_result = $conn->query($user_query);
$new_users = $user_result->fetch_assoc()['count'];

// Build notifications array
if ($pending_listings > 0) {
    $notifications[] = [
        'message' => $pending_listings . ' listing' . ($pending_listings > 1 ? 's' : '') . ' require approval',
        'link' => 'approvals.php',
        'icon' => 'fas fa-check-circle',
        'time' => 'Now'
    ];
}

if ($new_orders > 0) {
    $notifications[] = [
        'message' => $new_orders . ' new order' . ($new_orders > 1 ? 's' : '') . ' placed',
        'link' => 'orders.php',
        'icon' => 'fas fa-shopping-cart',
        'time' => 'Today'
    ];
}

if ($new_users > 0) {
    $notifications[] = [
        'message' => $new_users . ' new user' . ($new_users > 1 ? 's' : '') . ' registered',
        'link' => 'users.php',
        'icon' => 'fas fa-user-plus',
        'time' => 'Today'
    ];
}

// Calculate total notifications
$total_notifications = count($notifications);

// Get recent notifications with more details for dropdown
$recent_notifications = [];

// Get 3 most recent pending listings
if ($pending_listings > 0) {
    $recent_listings_query = "
        (SELECT 'pet' as type, pet_id as id, name, created_at, seller_id 
         FROM pets WHERE approval_status = 'pending' 
         ORDER BY created_at DESC LIMIT 2)
        UNION ALL
        (SELECT 'product' as type, product_id as id, name, created_at, seller_id 
         FROM products WHERE approval_status = 'pending' 
         ORDER BY created_at DESC LIMIT 2)
        ORDER BY created_at DESC LIMIT 3";
    
    $recent_listings_result = $conn->query($recent_listings_query);
    while ($row = $recent_listings_result->fetch_assoc()) {
        $recent_notifications[] = [
            'message' => ucfirst($row['type']) . ' "' . substr($row['name'], 0, 30) . (strlen($row['name']) > 30 ? '...' : '') . '" needs approval',
            'link' => 'approvals.php',
            'icon' => 'fas fa-check-circle text-warning',
            'time' => time_ago($row['created_at'])
        ];
    }
}

// Get 2 most recent orders
if ($new_orders > 0) {
    $recent_orders_query = "
        SELECT o.order_id, o.total_amount, o.order_date, u.first_name, u.last_name 
        FROM orders o 
        JOIN users u ON o.buyer_id = u.user_id 
        WHERE o.order_date >= DATE_SUB(NOW(), INTERVAL 1 DAY) AND o.status = 'pending'
        ORDER BY o.order_date DESC LIMIT 2";
    
    $recent_orders_result = $conn->query($recent_orders_query);
    while ($row = $recent_orders_result->fetch_assoc()) {
        $recent_notifications[] = [
            'message' => 'New order #' . $row['order_id'] . ' from ' . $row['first_name'] . ' ' . $row['last_name'],
            'link' => 'orders.php?id=' . $row['order_id'],
            'icon' => 'fas fa-shopping-cart text-success',
            'time' => time_ago($row['order_date'])
        ];
    }
}

// Function to calculate time ago
function time_ago($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) {
        return 'Just now';
    } elseif ($time < 3600) {
        $minutes = floor($time / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($time < 86400) {
        $hours = floor($time / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } else {
        $days = floor($time / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jambo Pets - Admin Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #28a745;
            --secondary-color: #6c757d;
            --accent-color: #ffc107;
            --dark-color: #343a40;
            --light-color: #f8f9fa;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
        }
        
        .sidebar {
            background-color: var(--dark-color);
            height: 100vh;
            position: fixed;
            z-index: 100;
            overflow-y: auto;
            overflow-x: hidden;
        }
        
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background-color: rgba(255,255,255,0.2);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background-color: rgba(255,255,255,0.3);
        }
        
        .sidebar .logo {
            padding: 20px 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar .logo img {
            max-width: 100%;
            height: auto;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.7);
            padding: 12px 20px;
            border-radius: 5px;
            margin: 5px 15px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255,255,255,0.1);
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .content-wrapper {
            margin-left: 250px;
            padding: 20px;
        }
        
        .navbar {
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            padding: 0.5rem 1rem;
        }
        
        .navbar .dropdown-menu {
            right: 0;
            left: auto;
            min-width: 350px;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .notification-item {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f3f4;
            transition: background-color 0.2s;
        }
        
        .notification-item:hover {
            background-color: #f8f9fa;
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            margin-right: 12px;
        }
        
        .notification-content {
            flex: 1;
        }
        
        .notification-message {
            font-size: 14px;
            margin-bottom: 4px;
            color: #333;
        }
        
        .notification-time {
            font-size: 12px;
            color: #6c757d;
        }
        
        .notification-badge {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-weight: 600;
        }
        
        .stats-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }
        
        .stats-card .icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 30px;
            color: rgba(0,0,0,0.1);
        }
        
        .stats-card h5 {
            font-weight: 600;
            color: var(--secondary-color);
        }
        
        .stats-card h2 {
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stats-card p {
            margin-bottom: 0;
            color: var(--secondary-color);
        }
        
        .table th {
            font-weight: 600;
            border-top: none;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        
        .badge-pending {
            background-color: #ffc107;
            color: #212529;
        }
        
        .badge-approved {
            background-color: #28a745;
            color: #fff;
        }
        
        .badge-rejected {
            background-color: #dc3545;
            color: #fff;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                min-height: auto;
            }
            
            .content-wrapper {
                margin-left: 0;
            }
            
            .navbar .dropdown-menu {
                min-width: 300px;
            }
        }
    
    .lightbox {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(5px);
    }

    .lightbox-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        max-width: 90%;
        max-height: 90%;
        text-align: center;
    }

    .lightbox img {
        max-width: 100%;
        max-height: 80vh;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
    }

    .lightbox-close {
        position: absolute;
        top: 20px;
        right: 35px;
        color: #fff;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
        transition: color 0.3s ease;
        z-index: 10000;
    }

    .lightbox-close:hover,
    .lightbox-close:focus {
        color: #ccc;
    }

    .profile-img-clickable {
        cursor: pointer;
        transition: transform 0.2s ease;
    }

    .profile-img-clickable:hover {
        transform: scale(1.05);
    }

    </style>
</head>
<body>
  <!-- Sidebar -->
    <div class="sidebar bg-dark text-white" style="width: 250px;">
        <div class="logo">
            <h4 class="text-center text-white">🐾 Jambo Pets</h4>
            <p class="text-center text-white-50 small">Admin Dashboard</p>
        </div>
        <div class="mt-4">
            <div class="text-white-50 small px-4 mb-2">MAIN MENU</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" href="users.php">
                        <i class="fas fa-users"></i> Users
                        <?php if ($new_users > 0): ?>
                            <span class="badge bg-danger rounded-pill ms-auto"><?php echo $new_users; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'listings.php' ? 'active' : ''; ?>" href="listings.php">
                        <i class="fas fa-list"></i> Listings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>" href="orders.php">
                        <i class="fas fa-shopping-cart"></i> Orders
                        <?php if ($new_orders > 0): ?>
                            <span class="badge bg-danger rounded-pill ms-auto"><?php echo $new_orders; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'approvals.php' ? 'active' : ''; ?>" href="approvals.php">
                        <i class="fas fa-check-circle"></i> Approvals
                        <?php if ($pending_listings > 0): ?>
                            <span class="badge bg-warning rounded-pill ms-auto"><?php echo $pending_listings; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : ''; ?>" href="contact_messages.php">
                        <i class="fas fa-envelope"></i>Contact Messages
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'blog.php' ? 'active' : ''; ?>" href="blog.php">
                        <i class="fas fa-blog"></i> Blog Posts
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'analytics.php' ? 'active' : ''; ?>" href="analytics.php">
                        <i class="fas fa-chart-bar"></i> Analytics
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>" href="reports.php">
                        <i class="fas fa-file-alt"></i> Reports
                    </a>
                </li>
            </ul>
            
            <div class="text-white-50 small px-4 mb-2 mt-4">SYSTEM</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>" href="settings.php">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../auth/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
    
    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white rounded mb-4">
            <button class="navbar-toggler" type="button">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown mt-3">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            <?php if ($total_notifications > 0): ?>
                                <span class="badge bg-danger rounded-pill notification-badge"><?php echo $total_notifications; ?></span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li class="dropdown-header d-flex align-items-center justify-content-between">
                                <span><strong>Notifications</strong></span>
                                <?php if ($total_notifications > 0): ?>
                                    <span class="badge bg-primary rounded-pill"><?php echo $total_notifications; ?></span>
                                <?php endif; ?>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            
                            <?php if (empty($recent_notifications)): ?>
                                <li class="notification-item text-center py-4">
                                    <i class="fas fa-bell-slash text-muted mb-2" style="font-size: 24px;"></i>
                                    <p class="text-muted mb-0">No new notifications</p>
                                </li>
                            <?php else: ?>
                                <?php foreach ($recent_notifications as $notification): ?>
                                    <li>
                                        <a class="dropdown-item notification-item p-0" href="<?php echo $notification['link']; ?>">
                                            <div class="d-flex align-items-start">
                                                <div class="notification-icon">
                                                    <i class="<?php echo $notification['icon']; ?>"></i>
                                                </div>
                                                <div class="notification-content">
                                                    <div class="notification-message">
                                                        <?php echo $notification['message']; ?>
                                                    </div>
                                                    <div class="notification-time">
                                                        <?php echo $notification['time']; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                                
                                <?php if (count($recent_notifications) < $total_notifications): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li class="text-center py-2">
                                        <small class="text-muted">
                                            <?php echo ($total_notifications - count($recent_notifications)); ?> more notifications
                                        </small>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                             
                                <li><hr class="dropdown-divider"></li>
                                <li class="text-center py-2">
                                    <a href="notifications.php" class="btn btn-sm btn-outline-primary">View All</a>
                                </li>
                             
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="<?php echo '../uploads/' . $admin['profile_image']; ?>" alt="Admin Profile" class="rounded-circle img-fluid mb-3" style="width: 50px; height: 50px; object-fit: cover;">
                            <?php echo isset($admin['name']) ? $admin['name'] : 'Admin'; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="container-fluid">