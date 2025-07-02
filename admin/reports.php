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
// Include header
require_once '../includes/admin_header.php';


// Get date range for filtering
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'sales';

// Function to get sales data by date range
function getSalesData($conn, $start_date, $end_date) {
    $query = "SELECT 
                DATE(o.created_at) as order_date,
                SUM(od.quantity * od.price) as total_sales,
                COUNT(DISTINCT o.order_id) as order_count
              FROM 
                orders o
              JOIN 
                order_details od ON o.order_id = od.order_id
              WHERE 
                o.status != 'cancelled' AND
                DATE(o.created_at) BETWEEN ? AND ?
              GROUP BY 
                DATE(o.created_at)
              ORDER BY 
                order_date";
    
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("SQL Error in getSalesData: " . $conn->error);
        return [];
    }
    
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = array();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    return $data;
}

// Function to get top selling products
function getTopSellingProducts($conn, $start_date, $end_date, $limit = 10) {
    $query = "SELECT 
                p.product_id,
                p.title,
                SUM(od.quantity) as total_quantity,
                SUM(od.quantity * od.price) as total_revenue,
                u.first_name AS seller_name
              FROM 
                order_details od
              JOIN 
                orders o ON od.order_id = o.order_id
              JOIN 
                products p ON od.product_id = p.product_id
              JOIN 
                users u ON p.seller_id = u.user_id
              WHERE 
                o.status != 'cancelled' AND
                DATE(o.created_at) BETWEEN ? AND ?
              GROUP BY 
                p.product_id
              ORDER BY 
                total_quantity DESC
              LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $start_date, $end_date, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = array();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    return $data;
}

// Function to get top sellers
function getTopSellers($conn, $start_date, $end_date, $limit = 10) {
    $query = "SELECT 
                u.user_id,
                u.first_name,
                u.last_name,
                COUNT(DISTINCT o.order_id) as order_count,
                SUM(od.quantity * od.price) as total_sales,
                COUNT(DISTINCT p.product_id) as product_count
              FROM 
                users u
              JOIN 
                products p ON u.user_id = p.seller_id
              JOIN 
                order_details od ON p.product_id = od.product_id
              JOIN 
                orders o ON od.order_id = o.order_id
              WHERE 
                u.user_type = 'seller' AND
                o.status != 'cancelled' AND
                DATE(o.created_at) BETWEEN ? AND ?
              GROUP BY 
                u.user_id
              ORDER BY 
                total_sales DESC
              LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $start_date, $end_date, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = array();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    return $data;
}

// Function to get user registration data
function getUserRegistrationData($conn, $start_date, $end_date) {
    $query = "SELECT 
                DATE(created_at) as reg_date,
                COUNT(CASE WHEN user_type = 'buyer' THEN 1 END) as buyer_count,
                COUNT(CASE WHEN user_type = 'seller' THEN 1 END) as seller_count
              FROM 
                users
              WHERE 
                DATE(created_at) BETWEEN ? AND ?
              GROUP BY 
                DATE(created_at)
              ORDER BY 
                reg_date";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = array();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    return $data;
}

// Function to get listing approval stats
function getListingApprovalStats($conn, $start_date, $end_date) {
    $query = "SELECT 
                DATE(created_at) as listing_date,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
                COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_count,
                COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_count
              FROM 
                products
              WHERE 
                DATE(created_at) BETWEEN ? AND ?
              GROUP BY 
                DATE(created_at)
              ORDER BY 
                listing_date";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = array();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    return $data;
}

// Function to get revenue by category
function getRevenueByCategory($conn, $start_date, $end_date) {
    $query = "SELECT 
                c.name as category_name,
                SUM(od.quantity * od.price) as total_revenue,
                COUNT(DISTINCT od.order_id) as order_count
              FROM 
                order_details od
              JOIN 
                products p ON od.product_id = p.product_id
              JOIN 
                categories c ON p.category_id = c.category_id
              JOIN 
                orders o ON od.order_id = o.order_id
              WHERE 
                o.status != 'cancelled' AND
                DATE(o.created_at) BETWEEN ? AND ?
              GROUP BY 
                c.category_id
              ORDER BY 
                total_revenue DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = array();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    return $data;
}

// Get sales summary
$total_sales_query = "SELECT 
                        SUM(od.quantity * od.price) as total_sales,
                        COUNT(DISTINCT o.order_id) as total_orders,
                        COUNT(DISTINCT o.buyer_id) as unique_buyers
                      FROM 
                        orders o
                      JOIN 
                        order_details od ON o.order_id = od.order_id
                      WHERE 
                        o.status != 'cancelled' AND
                        DATE(o.created_at) BETWEEN ? AND ?";

$stmt = $conn->prepare($total_sales_query);
if ($stmt === false) {
    // Handle error - log or display
    echo "Error in sales summary query: " . $conn->error;
    $sales_summary = ['total_sales' => 0, 'total_orders' => 0, 'unique_buyers' => 0];
} else {
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $sales_summary = $stmt->get_result()->fetch_assoc();
}

// Get user summary
$user_summary_query = "SELECT 
                         COUNT(CASE WHEN user_type = 'buyer' THEN 1 END) as total_buyers,
                         COUNT(CASE WHEN user_type = 'seller' THEN 1 END) as total_sellers,
                         COUNT(CASE WHEN DATE(created_at) BETWEEN ? AND ? THEN 1 END) as new_users
                       FROM 
                         users";

$stmt = $conn->prepare($user_summary_query);
if ($stmt === false) {
    // Handle error - log or display
    echo "Error in user summary query: " . $conn->error;
    $user_summary = ['total_buyers' => 0, 'total_sellers' => 0, 'new_users' => 0];
} else {
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $user_summary = $stmt->get_result()->fetch_assoc();
}

// Get product summary
$product_summary_query = "SELECT 
                            COUNT(*) as total_products,
                            COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_products,
                            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_products,
                            COUNT(CASE WHEN DATE(created_at) BETWEEN ? AND ? THEN 1 END) as new_products
                          FROM 
                            products";

$stmt = $conn->prepare($product_summary_query);
if ($stmt === false) {
    // Handle error - log or display
    echo "Error in product summary query: " . $conn->error;
    $product_summary = ['total_products' => 0, 'approved_products' => 0, 'pending_products' => 0, 'new_products' => 0];
} else {
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $product_summary = $stmt->get_result()->fetch_assoc();
}

// Fetch data based on report type
switch ($report_type) {
    case 'sales':
        $sales_data = getSalesData($conn, $start_date, $end_date);
        $chart_data_labels = [];
        $chart_data_values = [];
        
        foreach ($sales_data as $day) {
            $chart_data_labels[] = date('M d', strtotime($day['order_date']));
            $chart_data_values[] = $day['total_sales'];
        }
        break;
        
    case 'products':
        $top_products = getTopSellingProducts($conn, $start_date, $end_date);
        $chart_data_labels = [];
        $chart_data_values = [];
        
        foreach ($top_products as $product) {
            $chart_data_labels[] = $product['title'];
            $chart_data_values[] = $product['total_quantity'];
        }
        break;
        
    case 'sellers':
        $top_sellers = getTopSellers($conn, $start_date, $end_date);
        $chart_data_labels = [];
        $chart_data_values = [];
        
        foreach ($top_sellers as $seller) {
            $chart_data_labels[] = $seller['first_name'] . ' ' . $seller['last_name'];
            $chart_data_values[] = $seller['total_sales'];
        }
        break;
        
    case 'users':
        $user_reg_data = getUserRegistrationData($conn, $start_date, $end_date);
        $chart_data_labels = [];
        $chart_data_buyers = [];
        $chart_data_sellers = [];
        
        foreach ($user_reg_data as $day) {
            $chart_data_labels[] = date('M d', strtotime($day['reg_date']));
            $chart_data_buyers[] = $day['buyer_count'];
            $chart_data_sellers[] = $day['seller_count'];
        }
        break;
        
    case 'listings':
        $listing_data = getListingApprovalStats($conn, $start_date, $end_date);
        $chart_data_labels = [];
        $chart_data_pending = [];
        $chart_data_approved = [];
        $chart_data_rejected = [];
        
        foreach ($listing_data as $day) {
            $chart_data_labels[] = date('M d', strtotime($day['listing_date']));
            $chart_data_pending[] = $day['pending_count'];
            $chart_data_approved[] = $day['approved_count'];
            $chart_data_rejected[] = $day['rejected_count'];
        }
        break;
        
    case 'categories':
        $category_data = getRevenueByCategory($conn, $start_date, $end_date);
        $chart_data_labels = [];
        $chart_data_values = [];
        
        foreach ($category_data as $category) {
            $chart_data_labels[] = $category['category_name'];
            $chart_data_values[] = $category['total_revenue'];
        }
        break;
}
?>

<h1 class="mb-4">Reports & Analytics</h1>

<!-- Date Range Filter -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Filter Reports</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="reports.php" class="row g-3">
            <div class="col-md-3">
                <label for="report_type" class="form-label">Report Type</label>
                <select class="form-select" id="report_type" name="report_type">
                    <option value="sales" <?php echo $report_type == 'sales' ? 'selected' : ''; ?>>Sales Report</option>
                    <option value="products" <?php echo $report_type == 'products' ? 'selected' : ''; ?>>Top Products</option>
                    <option value="sellers" <?php echo $report_type == 'sellers' ? 'selected' : ''; ?>>Top Sellers</option>
                    <option value="users" <?php echo $report_type == 'users' ? 'selected' : ''; ?>>User Registrations</option>
                    <option value="listings" <?php echo $report_type == 'listings' ? 'selected' : ''; ?>>Listing Approvals</option>
                    <option value="categories" <?php echo $report_type == 'categories' ? 'selected' : ''; ?>>Category Performance</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row">
    <div class="col-md-4">
        <div class="stats-card">
            <i class="fas fa-shopping-cart icon"></i>
            <h5>Sales Summary</h5>
            <h2><?php echo number_format($sales_summary['total_sales'] ?? 0, 2); ?> KES</h2>
            <p><?php echo number_format($sales_summary['total_orders'] ?? 0); ?> orders from <?php echo number_format($sales_summary['unique_buyers'] ?? 0); ?> buyers</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card">
            <i class="fas fa-users icon"></i>
            <h5>User Summary</h5>
            <h2><?php echo number_format($user_summary['total_buyers'] + $user_summary['total_sellers']); ?></h2>
            <p><?php echo number_format($user_summary['total_buyers']); ?> buyers, <?php echo number_format($user_summary['total_sellers']); ?> sellers, <?php echo number_format($user_summary['new_users']); ?> new</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card">
            <i class="fas fa-list icon"></i>
            <h5>Products Summary</h5>
            <h2><?php echo number_format($product_summary['total_products']); ?></h2>
            <p><?php echo number_format($product_summary['approved_products']); ?> approved, <?php echo number_format($product_summary['pending_products']); ?> pending, <?php echo number_format($product_summary['new_products']); ?> new</p>
        </div>
    </div>
</div>

<!-- Chart -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <?php
            switch ($report_type) {
                case 'sales':
                    echo 'Sales Trend';
                    break;
                case 'products':
                    echo 'Top Selling Products';
                    break;
                case 'sellers':
                    echo 'Top Performing Sellers';
                    break;
                case 'users':
                    echo 'User Registration Trend';
                    break;
                case 'listings':
                    echo 'Listing Approval Statistics';
                    break;
                case 'categories':
                    echo 'Revenue by Category';
                    break;
            }
            ?>
        </h5>
    </div>
    <div class="card-body">
        <canvas id="reportChart" style="width: 100%; height: 400px;"></canvas>
    </div>
</div>

<!-- Detailed Report Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Detailed Report</h5>
    </div>
    <div class="card-body">
        <?php if ($report_type == 'sales'): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Orders</th>
                            <th>Sales (KES)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sales_data as $day): ?>
                            <tr>
                                <td><?php echo date('Y-m-d', strtotime($day['order_date'])); ?></td>
                                <td><?php echo number_format($day['order_count']); ?></td>
                                <td><?php echo number_format($day['total_sales'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($report_type == 'products'): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Seller</th>
                            <th>Units Sold</th>
                            <th>Revenue (KES)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['title']); ?></td>
                                <td><?php echo htmlspecialchars($product['seller_name']); ?></td>
                                <td><?php echo number_format($product['total_quantity']); ?></td>
                                <td><?php echo number_format($product['total_revenue'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($report_type == 'sellers'): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Seller</th>
                            <th>Products</th>
                            <th>Orders</th>
                            <th>Revenue (KES)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_sellers as $seller): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($seller['first_name'] . ' ' . $seller['last_name']); ?></td>
                                <td><?php echo number_format($seller['product_count']); ?></td>
                                <td><?php echo number_format($seller['order_count']); ?></td>
                                <td><?php echo number_format($seller['total_sales'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($report_type == 'users'): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>New Buyers</th>
                            <th>New Sellers</th>
                            <th>Total New Users</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($user_reg_data as $day): ?>
                            <tr>
                                <td><?php echo date('Y-m-d', strtotime($day['reg_date'])); ?></td>
                                <td><?php echo number_format($day['buyer_count']); ?></td>
                                <td><?php echo number_format($day['seller_count']); ?></td>
                                <td><?php echo number_format($day['buyer_count'] + $day['seller_count']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($report_type == 'listings'): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Pending</th>
                            <th>Approved</th>
                            <th>Rejected</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listing_data as $day): ?>
                            <tr>
                                <td><?php echo date('Y-m-d', strtotime($day['listing_date'])); ?></td>
                                <td><?php echo number_format($day['pending_count']); ?></td>
                                <td><?php echo number_format($day['approved_count']); ?></td>
                                <td><?php echo number_format($day['rejected_count']); ?></td>
                                <td><?php echo number_format($day['pending_count'] + $day['approved_count'] + $day['rejected_count']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($report_type == 'categories'): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Orders</th>
                            <th>Revenue (KES)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($category_data as $category): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                                <td><?php echo number_format($category['order_count']); ?></td>
                                <td><?php echo number_format($category['total_revenue'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Export Options -->
<div class="mt-4 d-flex justify-content-end">
    <button class="btn btn-outline-primary me-2" onclick="printReport()">
        <i class="fas fa-print me-2"></i> Print
    </button>
    <button class="btn btn-outline-success me-2" onclick="exportExcel()">
        <i class="fas fa-file-excel me-2"></i> Export Excel
    </button>
    <button class="btn btn-outline-danger" onclick="exportPDF()">
        <i class="fas fa-file-pdf me-2"></i> Export PDF
    </button>
</div>

<!-- Chart JS Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('reportChart').getContext('2d');
    
    <?php if ($report_type == 'sales' || $report_type == 'products' || $report_type == 'sellers' || $report_type == 'categories'): ?>
        // Bar chart for sales, products, sellers, categories
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($chart_data_labels); ?>,
                datasets: [{
                    label: '<?php echo $report_type == 'sales' ? 'Sales (KES)' : ($report_type == 'products' ? 'Units Sold' : ($report_type == 'sellers' ? 'Revenue (KES)' : 'Revenue (KES)')); ?>',
                    data: <?php echo json_encode($chart_data_values); ?>,
                    backgroundColor: 'rgba(40, 167, 69, 0.7)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '<?php echo $report_type == 'products' ? '' : 'KES '; ?>' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });
    <?php elseif ($report_type == 'users'): ?>
        // Line chart for user registrations
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chart_data_labels); ?>,
                datasets: [{
                    label: 'Buyers',
                    data: <?php echo json_encode($chart_data_buyers); ?>,
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    borderColor: 'rgba(0, 123, 255, 1)',
                    borderWidth: 2,
                    tension: 0.1,
                    fill: true
                }, {
                    label: 'Sellers',
                    data: <?php echo json_encode($chart_data_sellers); ?>,
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 2,
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
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
                    }
                }
            }
        });
    <?php elseif ($report_type == 'listings'): ?>
        // Stacked bar chart for listing approvals
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($chart_data_labels); ?>,
                datasets: [{
                    label: 'Pending',
                    data: <?php echo json_encode($chart_data_pending); ?>,
                    backgroundColor: 'rgba(255, 193, 7, 0.7)',
                    borderColor: 'rgba(255, 193, 7, 1)',
                    borderWidth: 1
                }, {
                    label: 'Approved',
                    data: <?php echo json_encode($chart_data_approved); ?>,
                    backgroundColor: 'rgba(40, 167, 69, 0.7)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1
                }, {
                    label: 'Rejected',
                    data: <?php echo json_encode($chart_data_rejected); ?>,
                    backgroundColor: 'rgba(220, 53, 69, 0.7)',
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        stacked: true
                    },
                    y: {
                        stacked: true,
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
                    }
                }
            }
        });
    <?php endif; ?>
});

// Print function
function printReport() {
    window.print();
}

// Excel export function (simplified - would need server-side processing)
function exportExcel() {
    alert('This would generate an Excel file in a production environment.');
    // In a real implementation, you would make an AJAX call to a PHP script
    // that generates the Excel file using a library like PhpSpreadsheet
}

// PDF export function (simplified - would need server-side processing)
function exportPDF() {
    alert('This would generate a PDF file in a production environment.');
    // In a real implementation, you would make an AJAX call to a PHP script
    // that generates the PDF file using a library like FPDF or TCPDF
}
</script>

<?php
// Include footer
require_once '../includes/admin_footer.php';
?>