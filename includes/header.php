<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define base URL for the site
define('BASE_URL', 'http://localhost/JamboPets/');

// Include utility functions
require_once __DIR__ . '/functions.php';

// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userType = $isLoggedIn ? $_SESSION['user_type'] : '';
$userName = $isLoggedIn ? $_SESSION['first_name'] : '';

// Get user data only if logged in
$user = null;
if ($isLoggedIn) {
    $userId = $_SESSION['user_id'];
    $userQuery = "SELECT * FROM users WHERE user_id = $userId";
    $userResult = $conn->query($userQuery);
    
    // Check if query was successful and returned data
    if ($userResult && $userResult->num_rows > 0) {
        $user = $userResult->fetch_assoc();
    } else {
        // Handle case where user ID doesn't exist in database
        // This could happen if user was deleted but session still exists
        session_destroy();
        header('Location: ' . BASE_URL . 'auth/login.php');
        exit;
    }
}

// Get essential site settings
$settingsQuery = "SELECT setting_key, value FROM settings 
                 WHERE setting_key IN ('site_name','contact_phone', 'site_logo', 'contact_email', 
                 'contact_address', 'facebook_link', 'twitter_link', 'instagram_link')";
$settingsResult = $conn->query($settingsQuery);

// Store settings in an associative array
$settings = [];
if ($settingsResult->num_rows > 0) {
    while ($row = $settingsResult->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['value'];
    }
}

// Now you can use these variables directly:
$siteName = $settings['site_name'] ?? '';
$siteLogo = $settings['site_logo'] ?? '';
$contactPhone = $settings['contact_phone'];
$contactEmail = $settings['contact_email'] ?? '';
$contactAddress = $settings['contact_address'] ?? '';
$facebookLink = $settings['facebook_link'] ?? '';
$twitterLink = $settings['twitter_link'] ?? '';
$instagramLink = $settings['instagram_link'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - Jambo Pets' : 'Jambo Pets - Kenya\'s Pet Marketplace'; ?></title>
    
     <!-- Favicon - Enhanced with multiple formats -->
     <link rel="icon" href="<?php echo BASE_URL; ?>uploads/logo/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo BASE_URL; ?>uploads/logo/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo BASE_URL; ?>uploads/logo/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo BASE_URL; ?>uploads/logo/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo BASE_URL; ?>uploads/logo/favicon-16x16.png">
    
    <!-- Force favicon refresh with version parameter -->
    <link rel="icon" href="<?php echo BASE_URL; ?>uploads/logo/favicon.ico?v=<?php echo time(); ?>" type="image/x-icon">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    
    <!-- Loading Spinner CSS -->
    <style>
        /* Loading Spinner Container */
        #loading-spinner {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.3s;
        }
        
        /* Spinner Animation */
        .spinner-logo {
            animation: spin 2s infinite linear;
            width: 120px;
            height: auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Hide spinner once page is loaded */
        .loaded #loading-spinner {
            opacity: 0;
            visibility: hidden;
        }
        .hero-section{
            border-radius: 10px;
        }
        /* Lightbox Styles */

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
    /* Top bar enhancement */
.top-bar {
  background: var(--gradient-primary) !important;
  font-size: 0.9rem;
  z-index: 1040;
  position: relative;
}

.top-bar a {
  color: white !important;
  text-decoration: none;
  transition: all 0.3s ease;
}

.top-bar a:hover {
  color: rgba(255, 255, 255, 0.8) !important;
  text-decoration: underline;
}
    </style>
    <style>
    h2 {
  font-size: 2.5rem;
  color: var(--text-dark);
  position: relative;
}

h2::after {
  content: '';
  position: absolute;
  bottom: -8px;
  left: 0;
  width: 100%;
  height: 4px;
  background: var(--gradient-primary);
  border-radius: 2px;
}

.text-center h2::after {
  left: 50%;
  transform: translateX(-50%);
}
/* Enhanced Hero Section */
.hero-section {
  background: var(--gradient-primary);
  min-height: 80vh;
  display: flex;
  align-items: center;
  position: relative;
  overflow: hidden;
}

.hero-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="rgba(255,255,255,0.1)"><polygon points="1000,100 1000,0 0,100"/></svg>');
  background-size: cover;
  background-position: bottom;
}

.hero-section .container {
  position: relative;
  z-index: 2;
}

.hero-section h1 {
  color: white;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
  animation: fadeInUp 1s ease-out;
}

.hero-section .lead {
  font-size: 1.3rem;
  font-weight: 400;
  color: rgba(255, 255, 255, 0.9);
  animation: fadeInUp 1s ease-out 0.2s both;
}

.hero-section img {
  border-radius: 20px;
  box-shadow: var(--shadow-xl);
  animation: fadeInRight 1s ease-out 0.4s both;
  transition: transform 0.3s ease;
}

.hero-section img:hover {
  transform: scale(1.05);
}

</style>
    <?php if(isset($extraCSS)) echo $extraCSS; ?>
</head>
<body>
    
    <!-- Loading Spinner 
    <div id="loading-spinner">
        <img src="<?php echo BASE_URL; ?>uploads/logo/logo5.png" alt="Loading..." class="spinner-logo">
    </div>
-->
    <!-- Top Bar -->
    <div class="top-bar py-2 bg-primary text-white">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <span><i class="fas fa-phone me-2"></i><?php echo $contactPhone?></span>
                    <span class="ms-3"><i class="fas fa-envelope me-2"></i> <?php echo $contactEmail?></span>
                </div>
                <div class="col-md-6 text-end">
                    <?php if($isLoggedIn): ?>
                        <span class="me-3">Welcome, <?php echo htmlspecialchars($userName); ?>!</span>
                        <a href="<?php echo BASE_URL; ?>auth/logout.php" class="text-white"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>auth/login.php" class="text-white me-3"><i class="fas fa-sign-in-alt me-1"></i> Login</a>
                        <a href="<?php echo BASE_URL; ?>auth/register.php" class="text-white"><i class="fas fa-user-plus me-1"></i> Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                <img src="<?php echo BASE_URL; ?><?php echo $siteLogo?>" alt="Jambo Pets Logo" height="50" class="mb-3">                 
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            Pets
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>buyer/browse.php?category=1">Dogs</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>buyer/browse.php?category=2">Cats</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>buyer/browse.php?category=3">Birds</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>buyer/browse.php?category=4">Fish</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>buyer/browse.php?category=5">Small Pets</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>buyer/browse.php">All Pets</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="productsDropdown" role="button" data-bs-toggle="dropdown">
                            Products
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>buyer/browse.php?category=7">Pet Food</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>buyer/browse.php?category=8">Accessories</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>buyer/browse.php?category=9">Grooming</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>buyer/browse.php?type=product">All Products</a></li>
                        </ul>
                    </li>
                    <?php if($isLoggedIn && $userType == 'seller'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($current_page, 'seller/') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>seller/dashboard.php">Seller Dashboard</a>
                    </li>
                    <?php endif; ?>
                    <?php if($isLoggedIn && $userType == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($current_page, 'admin/') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/index.php">Admin Dashboard</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'about.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>about.php">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'contact.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>contact.php">Contact</a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center" >
                    <form class="d-flex me-2" action="<?php echo BASE_URL; ?>buyer/browse.php" method="GET">
                        <input class="form-control me-2" type="search" name="search" placeholder="Search pets & products..." aria-label="Search">
                        <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
                    </form>
                    
                    <?php if($isLoggedIn && $userType == 'buyer'): ?>
                    <a href="<?php echo BASE_URL; ?>buyer/wishlist.php" class="btn btn-link position-relative me-2" style="font-size: 1.9rem;">
                        <i class="fas fa-heart fs-5"></i>
                        <span class="position-absolute top-0 start translate-middle badge rounded-pill bg-danger mt-3" style="font-size: 0.5rem;">
                            <?php echo getWishlistCount(); ?>
                        </span>
                    </a>
                    
                    <a href="<?php echo BASE_URL; ?>buyer/cart.php" class="btn btn-link position-relative" style="font-size: 1.9rem;">
                        <i class="fas fa-shopping-cart fs-5"></i>
                        <span class="position-absolute top-0 start translate-middle badge rounded-pill bg-danger mt-3" style="font-size: 0.5rem;">
                            <?php echo getCartCount(); ?>
                        </span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <main class="container py-4">
        <?php if(isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['flash_type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['flash_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['flash_message']); unset($_SESSION['flash_type']); ?>
        <?php endif; ?>
        
    <!-- JavaScript to handle the loading spinner -->
    <script>
        // Show spinner when page starts loading
        document.addEventListener('DOMContentLoaded', function() {
            // Record when the spinner started showing
            const spinnerStartTime = new Date().getTime();
            
            // Function to hide spinner after ensuring minimum display time
            const hideSpinner = function() {
                const currentTime = new Date().getTime();
                const elapsedTime = currentTime - spinnerStartTime;
                const minDisplayTime = 2000; // 3 seconds minimum display time
                
                if (elapsedTime >= minDisplayTime) {
                    // If 3 seconds have passed, hide immediately
                    document.body.classList.add('loaded');
                    
                    // Remove spinner completely after animation completes
                    setTimeout(function() {
                        const spinner = document.getElementById('loading-spinner');
                        if (spinner) {
                            spinner.style.display = 'none';
                        }
                    }, 300); // Match this with your CSS transition time
                } else {
                    // If less than 3 seconds have passed, wait for the remainder
                    const remainingTime = minDisplayTime - elapsedTime;
                    setTimeout(function() {
                        document.body.classList.add('loaded');
                        
                        // Remove spinner completely after animation completes
                        setTimeout(function() {
                            const spinner = document.getElementById('loading-spinner');
                            if (spinner) {
                                spinner.style.display = 'none';
                            }
                        }, 300); // Match this with your CSS transition time
                    }, remainingTime);
                }
            };
            
            // Hide spinner once the page is fully loaded (but respect minimum time)
            window.addEventListener('load', hideSpinner);
            
            // Also hide spinner if page takes too long to load (fallback)
            setTimeout(function() {
                hideSpinner();
            }, 8000); // Fallback timeout (8 seconds total)
        });
        
        // Show spinner when navigating to new pages
        document.addEventListener('click', function(e) {
            // Check if clicking on a link that loads a new page (not # links or external links)
            const target = e.target.closest('a');
            if (target && 
                target.href && 
                !target.href.startsWith('#') && 
                target.href.includes(window.location.hostname) && 
                !target.target && 
                !e.ctrlKey && 
                !e.metaKey) {
                
                // Show spinner
                document.getElementById('loading-spinner').style.opacity = '1';
                document.getElementById('loading-spinner').style.visibility = 'visible';
            }
        });
    </script>