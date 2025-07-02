<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../auth/login.php?message=You must be logged in as admin to access this page');
    exit();
}

// Connect to database
require_once '../config/db.php';

// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);

// Get admin role
$user_id = $_SESSION['user_id'];
$role_query = "SELECT admin_role FROM admin_roles WHERE user_id = ?";
$role_stmt = $conn->prepare($role_query);
$role_stmt->bind_param("i", $user_id);
$role_stmt->execute();
$role_result = $role_stmt->get_result();
$admin_role_data = $role_result->fetch_assoc();
$admin_role = $admin_role_data ? $admin_role_data['admin_role'] : 'user'; // Default to user role if not found

// Define access permissions for each role
$role_permissions = [
    'master' => ['index.php', 'users.php', 'listings.php', 'orders.php', 'approvals.php', 'contact_messages.php', 'analytics.php', 'reports.php', 'profile.php', 'settings.php', 'edit_admin.php', 'admin_actions.php'],
    'product' => ['index.php', 'listings.php', 'orders.php', 'profile.php', 'settings.php'],
    'user' => ['index.php', 'users.php', 'approvals.php', 'contact_messages.php', 'profile.php', 'settings.php']
];

// Check if admin has permission to access this page
if (!in_array($current_page, $role_permissions[$admin_role])) {
    header('Location: index.php?message=You do not have permission to access this page');
    exit();
}

// Close the statement
$role_stmt->close();
?><?php

require_once '../includes/functions.php';


// Set the time period for filtering
$time_period = isset($_GET['period']) ? $_GET['period'] : 'month';

// Get current date for calculations
$current_date = date('Y-m-d');

// Calculate date range based on time period
switch ($time_period) {
    case 'week':
        $start_date = date('Y-m-d', strtotime('-7 days'));
        $period_label = 'Last 7 Days';
        break;
    case 'month':
        $start_date = date('Y-m-d', strtotime('-30 days'));
        $period_label = 'Last 30 Days';
        break;
    case 'year':
        $start_date = date('Y-m-d', strtotime('-1 year'));
        $period_label = 'Last Year';
        break;
    case 'all':
        $start_date = '2000-01-01'; // A date far enough in the past
        $period_label = 'All Time';
        break;
    default:
        $start_date = date('Y-m-d', strtotime('-30 days'));
        $period_label = 'Last 30 Days';
}

// 1. User Statistics
// Count total users
$users_query = "SELECT 
                    COUNT(*) AS total_users,
                    SUM(CASE WHEN user_type = 'buyer' THEN 1 ELSE 0 END) AS buyers,
                    SUM(CASE WHEN user_type = 'seller' THEN 1 ELSE 0 END) AS sellers,
                    COUNT(CASE WHEN created_at >= ? THEN 1 ELSE NULL END) AS new_users
                FROM users";
$stmt = $conn->prepare($users_query);
$stmt->bind_param("s", $start_date);
$stmt->execute();
$users_result = $stmt->get_result()->fetch_assoc();

// Get user registrations by date for chart
$user_registrations_query = "SELECT 
                                DATE(created_at) AS registration_date,
                                COUNT(*) AS count
                            FROM users
                            WHERE created_at >= ?
                            GROUP BY DATE(created_at)
                            ORDER BY registration_date";
$stmt = $conn->prepare($user_registrations_query);
$stmt->bind_param("s", $start_date);
$stmt->execute();
$user_registrations_result = $stmt->get_result();

$user_registration_dates = [];
$user_registration_counts = [];
while ($row = $user_registrations_result->fetch_assoc()) {
    $user_registration_dates[] = $row['registration_date'];
    $user_registration_counts[] = $row['count'];
}

// 2. Listing Statistics
// Count total listings
$listings_query = "SELECT 
                      (SELECT COUNT(*) FROM pets WHERE created_at >= ?) AS total_pets,
                      (SELECT COUNT(*) FROM products WHERE created_at >= ?) AS total_products,
                      (SELECT COUNT(*) FROM pets WHERE approval_status = 'pending' AND created_at >= ?) AS pending_pets,
                      (SELECT COUNT(*) FROM products WHERE approval_status = 'pending' AND created_at >= ?) AS pending_products
                  ";
$stmt = $conn->prepare($listings_query);
$stmt->bind_param("ssss", $start_date, $start_date, $start_date, $start_date);
$stmt->execute();
$listings_result = $stmt->get_result()->fetch_assoc();

// Get listings by category
$category_query = "SELECT 
                      c.name AS category_name,
                      COUNT(p.pet_id) AS pet_count,
                      COUNT(pr.product_id) AS product_count
                   FROM categories c
                   LEFT JOIN pets p ON c.category_id = p.category_id AND p.created_at >= ?
                   LEFT JOIN products pr ON c.category_id = pr.category_id AND pr.created_at >= ?
                   GROUP BY c.category_id
                   ORDER BY (COUNT(p.pet_id) + COUNT(pr.product_id)) DESC
                   LIMIT 10";
$stmt = $conn->prepare($category_query);
$stmt->bind_param("ss", $start_date, $start_date);
$stmt->execute();
$category_result = $stmt->get_result();

// 3. Sales Statistics
$sales_query = "SELECT 
                   COUNT(*) AS total_orders,
                   SUM(total_amount) AS total_sales,
                   AVG(total_amount) AS average_order_value,
                   (SELECT COUNT(*) FROM orders WHERE status = 'completed' AND order_date >= ?) AS completed_orders,
                   (SELECT COUNT(*) FROM orders WHERE payment_status = 'paid' AND order_date >= ?) AS paid_orders
                FROM orders
                WHERE order_date >= ?";
$stmt = $conn->prepare($sales_query);
$stmt->bind_param("sss", $start_date, $start_date, $start_date);
$stmt->execute();
$sales_result = $stmt->get_result()->fetch_assoc();

// Get daily/monthly sales for chart
$sales_chart_query = "";
if ($time_period == 'week' || $time_period == 'month') {
    // Daily sales for week/month
    $sales_chart_query = "SELECT 
                            DATE(order_date) AS sale_date,
                            COUNT(*) AS order_count,
                            SUM(total_amount) AS daily_sales
                          FROM orders
                          WHERE order_date >= ?
                          GROUP BY DATE(order_date)
                          ORDER BY sale_date";
} else {
    // Monthly sales for year/all time
    $sales_chart_query = "SELECT 
                            DATE_FORMAT(order_date, '%Y-%m') AS sale_month,
                            COUNT(*) AS order_count,
                            SUM(total_amount) AS monthly_sales
                          FROM orders
                          WHERE order_date >= ?
                          GROUP BY DATE_FORMAT(order_date, '%Y-%m')
                          ORDER BY sale_month";
}

$stmt = $conn->prepare($sales_chart_query);
$stmt->bind_param("s", $start_date);
$stmt->execute();
$sales_chart_result = $stmt->get_result();

$sales_dates = [];
$sales_amounts = [];
$sales_counts = [];
while ($row = $sales_chart_result->fetch_assoc()) {
    $sales_dates[] = isset($row['sale_date']) ? $row['sale_date'] : $row['sale_month'];
    $sales_amounts[] = $row['daily_sales'] ?? $row['monthly_sales'];
    $sales_counts[] = $row['order_count'];
}

// 4. Most Popular Listings
$popular_pets_query = "SELECT 
                          p.pet_id, p.name, p.breed, p.price, p.views,
                          c.name AS category_name,
                          CONCAT(u.first_name, ' ', u.last_name) AS seller_name
                       FROM pets p
                       JOIN categories c ON p.category_id = c.category_id
                       JOIN seller_profiles sp ON p.seller_id = sp.seller_id
                       JOIN users u ON sp.user_id = u.user_id
                       WHERE p.created_at >= ?
                       ORDER BY p.views DESC
                       LIMIT 5";
$stmt = $conn->prepare($popular_pets_query);
$stmt->bind_param("s", $start_date);
$stmt->execute();
$popular_pets_result = $stmt->get_result();

$popular_products_query = "SELECT 
                             pr.product_id, pr.name, pr.price, pr.views,
                             c.name AS category_name,
                             CONCAT(u.first_name, ' ', u.last_name) AS seller_name
                          FROM products pr
                          JOIN categories c ON pr.category_id = c.category_id
                          JOIN seller_profiles sp ON pr.seller_id = sp.seller_id
                          JOIN users u ON sp.user_id = u.user_id
                          WHERE pr.created_at >= ?
                          ORDER BY pr.views DESC
                          LIMIT 5";
$stmt = $conn->prepare($popular_products_query);
$stmt->bind_param("s", $start_date);
$stmt->execute();
$popular_products_result = $stmt->get_result();

// 5. County-based Statistics
$county_users_query = "SELECT 
                         county,
                         COUNT(*) AS user_count
                      FROM users
                      WHERE county IS NOT NULL AND county != '' AND created_at >= ?
                      GROUP BY county
                      ORDER BY user_count DESC";
$stmt = $conn->prepare($county_users_query);
$stmt->bind_param("s", $start_date);
$stmt->execute();
$county_users_result = $stmt->get_result();

// Include the header
$page_title = "Analytics Dashboard | Jambo Pets Admin";
include_once '../includes/admin_header.php';
?>

<div class="container-fluid py-4">
    <!-- Time period filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="mb-0">Analytics Dashboard - <?= $period_label ?></h5>
                        </div>
                        <div class="col-md-4">
                            <form method="GET" class="d-flex justify-content-end">
                                <select class="form-select me-2" name="period" style="max-width: 200px;" onchange="this.form.submit()">
                                    <option value="week" <?= $time_period == 'week' ? 'selected' : '' ?>>Last 7 Days</option>
                                    <option value="month" <?= $time_period == 'month' ? 'selected' : '' ?>>Last 30 Days</option>
                                    <option value="year" <?= $time_period == 'year' ? 'selected' : '' ?>>Last Year</option>
                                    <option value="all" <?= $time_period == 'all' ? 'selected' : '' ?>>All Time</option>
                                </select>
                                <button type="submit" class="btn btn-primary">Apply</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Users</p>
                                <h5 class="font-weight-bolder mb-0">
                                    <?= number_format($users_result['total_users']) ?>
                                    <?php if ($users_result['new_users'] > 0): ?>
                                        <span class="text-success text-sm font-weight-bolder">
                                            +<?= number_format($users_result['new_users']) ?> new
                                        </span>
                                    <?php endif; ?>
                                </h5>
                                <p class="mb-0 text-sm">
                                    <span class="text-primary"><?= number_format($users_result['buyers']) ?></span> Buyers, 
                                    <span class="text-info"><?= number_format($users_result['sellers']) ?></span> Sellers
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                <i class="fas fa-users text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Listings</p>
                                <h5 class="font-weight-bolder mb-0">
                                    <?= number_format($listings_result['total_pets'] + $listings_result['total_products']) ?>
                                </h5>
                                <p class="mb-0 text-sm">
                                    <span class="text-primary"><?= number_format($listings_result['total_pets']) ?></span> Pets, 
                                    <span class="text-info"><?= number_format($listings_result['total_products']) ?></span> Products
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                <i class="fas fa-paw text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Sales</p>
                                <h5 class="font-weight-bolder mb-0">
                                    KSh <?= number_format($sales_result['total_sales'] ?? 0, 2) ?>
                                </h5>
                                <p class="mb-0 text-sm">
                                    <span class="text-success"><?= number_format($sales_result['total_orders'] ?? 0) ?></span> Orders,
                                    <span class="text-info">KSh <?= number_format($sales_result['average_order_value'] ?? 0, 2) ?></span> Avg
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle">
                                <i class="fas fa-money-bill text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Pending Review</p>
                                <h5 class="font-weight-bolder mb-0">
                                    <?= number_format($listings_result['pending_pets'] + $listings_result['pending_products']) ?>
                                </h5>
                                <p class="mb-0 text-sm">
                                    <span class="text-primary"><?= number_format($listings_result['pending_pets']) ?></span> Pets, 
                                    <span class="text-info"><?= number_format($listings_result['pending_products']) ?></span> Products
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                <i class="fas fa-clipboard-check text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- User Registrations Chart -->
        <div class="col-lg-6 mb-lg-0 mb-4">
            <div class="card">
                <div class="card-header pb-0 p-3">
                    <div class="d-flex justify-content-between">
                        <h6 class="mb-2">User Registrations</h6>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="chart">
                        <canvas id="userRegistrationsChart" class="chart-canvas" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sales Chart -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header pb-0 p-3">
                    <div class="d-flex justify-content-between">
                        <h6 class="mb-2">Sales Overview</h6>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="chart">
                        <canvas id="salesChart" class="chart-canvas" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category and County Statistics -->
    <div class="row mb-4">
        <!-- Categories Breakdown -->
        <div class="col-lg-6 mb-lg-0 mb-4">
            <div class="card">
                <div class="card-header pb-0 p-3">
                    <h6 class="mb-0">Categories Breakdown</h6>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Category</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Pet Listings</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Product Listings</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($category_result->num_rows > 0): ?>
                                    <?php while($category = $category_result->fetch_assoc()): ?>
                                        <?php $total = $category['pet_count'] + $category['product_count']; ?>
                                        <?php if($total > 0): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex px-2 py-1">
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm"><?= htmlspecialchars($category['category_name']) ?></h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="text-sm font-weight-bold mb-0"><?= number_format($category['pet_count']) ?></p>
                                                </td>
                                                <td>
                                                    <p class="text-sm font-weight-bold mb-0"><?= number_format($category['product_count']) ?></p>
                                                </td>
                                                <td>
                                                    <p class="text-sm font-weight-bold mb-0"><?= number_format($total) ?></p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No category data available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- County User Distribution -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header pb-0 p-3">
                    <h6 class="mb-0">User Distribution by County</h6>
                </div>
                <div class="card-body p-3">
                    <div class="chart">
                        <canvas id="countyChart" class="chart-canvas" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Most Popular Listings -->
    <div class="row">
        <!-- Popular Pets -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header pb-0 p-3">
                    <h6 class="mb-0">Top Viewed Pets</h6>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Pet</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Category</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Price</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Views</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($popular_pets_result->num_rows > 0): ?>
                                    <?php while($pet = $popular_pets_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm"><?= htmlspecialchars($pet['name']) ?></h6>
                                                        <p class="text-xs text-secondary mb-0">
                                                            <?= htmlspecialchars($pet['breed']) ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0"><?= htmlspecialchars($pet['category_name']) ?></p>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0">KSh <?= number_format($pet['price'], 2) ?></p>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0"><?= number_format($pet['views']) ?></p>
                                            </td>
                                            <td>
                                                <a href="../buyer/pet.php?id=<?= $pet['pet_id'] ?>" class="btn btn-sm btn-info" target="_blank">View</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No pet data available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Popular Products -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header pb-0 p-3">
                    <h6 class="mb-0">Top Viewed Products</h6>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Product</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Category</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Price</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Views</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($popular_products_result->num_rows > 0): ?>
                                    <?php while($product = $popular_products_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm"><?= htmlspecialchars($product['name']) ?></h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0"><?= htmlspecialchars($product['category_name']) ?></p>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0">KSh <?= number_format($product['price'], 2) ?></p>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0"><?= number_format($product['views']) ?></p>
                                            </td>
                                            <td>
                                                <a href="../buyer/product.php?id=<?= $product['product_id'] ?>" class="btn btn-sm btn-info" target="_blank">View</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No product data available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js script for data visualization -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Prepare user registration chart data
    const userRegistrationDates = <?= json_encode($user_registration_dates) ?>;
    const userRegistrationCounts = <?= json_encode($user_registration_counts) ?>;
    
    // Prepare sales chart data
    const salesDates = <?= json_encode($sales_dates) ?>;
    const salesAmounts = <?= json_encode($sales_amounts) ?>;
    const salesCounts = <?= json_encode($sales_counts) ?>;
    
    // Prepare county data
    const countyData = [];
    const countyLabels = [];
    const countyColors = [
        'rgba(75, 192, 192, 0.6)',
        'rgba(54, 162, 235, 0.6)',
        'rgba(153, 102, 255, 0.6)',
        'rgba(255, 99, 132, 0.6)',
        'rgba(255, 159, 64, 0.6)',
        'rgba(255, 206, 86, 0.6)',
        'rgba(199, 199, 199, 0.6)',
        'rgba(83, 102, 255, 0.6)',
        'rgba(255, 99, 255, 0.6)',
        'rgba(54, 162, 86, 0.6)'
    ];
    
    <?php
    $county_count = 0;
    while ($county = $county_users_result->fetch_assoc()) {
        if ($county_count < 10) { // Only show top 10 counties
            echo "countyLabels.push('" . addslashes($county['county']) . "');\n";
            echo "countyData.push(" . $county['user_count'] . ");\n";
            $county_count++;
        }
    }
    ?>

    // User registrations chart
const userRegistrationsChart = new Chart(
    document.getElementById('userRegistrationsChart').getContext('2d'),
    {
        type: 'line',
        data: {
            labels: userRegistrationDates,
            datasets: [
                {
                    label: 'New Users',
                    data: userRegistrationCounts,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    pointRadius: 3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            }
        }
    }
);

// Sales chart
const salesChart = new Chart(
    document.getElementById('salesChart').getContext('2d'),
    {
        type: 'bar',
        data: {
            labels: salesDates,
            datasets: [
                {
                    label: 'Sales Amount (KSh)',
                    data: salesAmounts,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    label: 'Order Count',
                    data: salesCounts,
                    type: 'line',
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    yAxisID: 'y1',
                    pointRadius: 3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Sales Amount (KSh)'
                    }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    },
                    title: {
                        display: true,
                        text: 'Order Count'
                    },
                    ticks: {
                        precision: 0
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            }
        }
    }
);

// County distribution chart
const countyChart = new Chart(
    document.getElementById('countyChart').getContext('2d'),
    {
        type: 'pie',
        data: {
            labels: countyLabels,
            datasets: [
                {
                    data: countyData,
                    backgroundColor: countyColors,
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 12
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((acc, val) => acc + val, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    }
);
</script>

<?php include_once '../includes/admin_footer.php'; ?>