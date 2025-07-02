<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
    header('Location: ../auth/login.php');
    exit();
}

// Get seller information
$user_id = $_SESSION['user_id'];
$seller_query = "SELECT s.*, u.first_name, u.last_name FROM seller_profiles s 
                JOIN users u ON s.user_id = u.user_id 
                WHERE s.user_id = ?";
$stmt = $conn->prepare($seller_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$seller_result = $stmt->get_result();
$seller = $seller_result->fetch_assoc();
$seller_id = $seller['seller_id'];

// Get date ranges for filtering
$time_period = isset($_GET['period']) ? $_GET['period'] : 'all';
$current_date = date('Y-m-d');

switch ($time_period) {
    case 'today':
        $start_date = $current_date;
        $date_condition = "DATE(o.order_date) = '$start_date'";
        $views_date_condition = "DATE(a.visit_timestamp) = '$start_date'";
        $period_text = "Today";
        break;
    case 'week':
        $start_date = date('Y-m-d', strtotime('-7 days'));
        $date_condition = "DATE(o.order_date) BETWEEN '$start_date' AND '$current_date'";
        $views_date_condition = "DATE(a.visit_timestamp) BETWEEN '$start_date' AND '$current_date'";
        $period_text = "Last 7 days";
        break;
    case 'month':
        $start_date = date('Y-m-d', strtotime('-30 days'));
        $date_condition = "DATE(o.order_date) BETWEEN '$start_date' AND '$current_date'";
        $views_date_condition = "DATE(a.visit_timestamp) BETWEEN '$start_date' AND '$current_date'";
        $period_text = "Last 30 days";
        break;
    default:
        $date_condition = "1=1"; // All time
        $views_date_condition = "1=1";
        $period_text = "All time";
        break;
}

// Get total sales amount
$sales_query = "SELECT SUM(oi.subtotal) as total_sales, COUNT(DISTINCT o.order_id) as order_count 
                FROM order_items oi 
                JOIN orders o ON oi.order_id = o.order_id 
                WHERE oi.seller_id = ? AND $date_condition";
$stmt = $conn->prepare($sales_query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$sales_result = $stmt->get_result();
$sales_data = $sales_result->fetch_assoc();

// Get pet and product listings counts
$listings_query = "SELECT 
                    (SELECT COUNT(*) FROM pets WHERE seller_id = ? AND status != 'inactive') as active_pets,
                    (SELECT COUNT(*) FROM products WHERE seller_id = ? AND status != 'inactive') as active_products,
                    (SELECT COUNT(*) FROM pets WHERE seller_id = ? AND status = 'sold') as sold_pets,
                    (SELECT COUNT(*) FROM products WHERE seller_id = ? AND status = 'out_of_stock') as sold_products";
$stmt = $conn->prepare($listings_query);
$stmt->bind_param("iiii", $seller_id, $seller_id, $seller_id, $seller_id);
$stmt->execute();
$listings_result = $stmt->get_result();
$listings_data = $listings_result->fetch_assoc();

// Get total views
$views_query = "SELECT 
                SUM(CASE WHEN a.item_type = 'pet' THEN 1 ELSE 0 END) as pet_views,
                SUM(CASE WHEN a.item_type = 'product' THEN 1 ELSE 0 END) as product_views
                FROM analytics a
                JOIN pets p ON (a.item_type = 'pet' AND a.item_id = p.pet_id)
                LEFT JOIN products pr ON (a.item_type = 'product' AND a.item_id = pr.product_id)
                WHERE (p.seller_id = ? OR pr.seller_id = ?) 
                AND $views_date_condition";
$stmt = $conn->prepare($views_query);
$stmt->bind_param("ii", $seller_id, $seller_id);
$stmt->execute();
$views_result = $stmt->get_result();
$views_data = $views_result->fetch_assoc();

// Get top selling pets
$top_pets_query = "SELECT p.pet_id, p.name, p.breed, p.price, p.views, COUNT(oi.order_item_id) as sales_count, 
                  SUM(oi.subtotal) as revenue
                  FROM pets p
                  LEFT JOIN order_items oi ON p.pet_id = oi.item_id AND oi.item_type = 'pet'
                  LEFT JOIN orders o ON oi.order_id = o.order_id
                  WHERE p.seller_id = ? AND ($date_condition OR o.order_id IS NULL)
                  GROUP BY p.pet_id
                  ORDER BY sales_count DESC, p.views DESC
                  LIMIT 5";
$stmt = $conn->prepare($top_pets_query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$top_pets_result = $stmt->get_result();

// Get top selling products
$top_products_query = "SELECT pr.product_id, pr.name, pr.price, pr.views, COUNT(oi.order_item_id) as sales_count, 
                      SUM(oi.subtotal) as revenue
                      FROM products pr
                      LEFT JOIN order_items oi ON pr.product_id = oi.item_id AND oi.item_type = 'product'
                      LEFT JOIN orders o ON oi.order_id = o.order_id
                      WHERE pr.seller_id = ? AND ($date_condition OR o.order_id IS NULL)
                      GROUP BY pr.product_id
                      ORDER BY sales_count DESC, pr.views DESC
                      LIMIT 5";
$stmt = $conn->prepare($top_products_query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$top_products_result = $stmt->get_result();

// Get monthly sales for chart (last 6 months)
$months_query = "SELECT 
                DATE_FORMAT(o.order_date, '%b %Y') as month,
                SUM(oi.subtotal) as monthly_sales
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.order_id
                WHERE oi.seller_id = ?
                AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(o.order_date, '%Y-%m')
                ORDER BY o.order_date ASC";
$stmt = $conn->prepare($months_query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$monthly_sales_result = $stmt->get_result();

// Format monthly sales data for chart
$months = [];
$sales_values = [];
while ($row = $monthly_sales_result->fetch_assoc()) {
    $months[] = $row['month'];
    $sales_values[] = $row['monthly_sales'];
}

// Get seller rating
$rating_query = "SELECT AVG(rating) as avg_rating, COUNT(*) as review_count
                FROM reviews
                WHERE item_type = 'seller' AND item_id = ?";
$stmt = $conn->prepare($rating_query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$rating_result = $stmt->get_result();
$rating_data = $rating_result->fetch_assoc();

// Get status of orders
$order_status_query = "SELECT 
                      oi.status,
                      COUNT(*) as count
                      FROM order_items oi
                      JOIN orders o ON oi.order_id = o.order_id
                      WHERE oi.seller_id = ? AND $date_condition
                      GROUP BY oi.status";
$stmt = $conn->prepare($order_status_query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$order_status_result = $stmt->get_result();

// Format order status data
$order_statuses = ['pending' => 0, 'processing' => 0, 'shipped' => 0, 'delivered' => 0, 'cancelled' => 0];
while ($row = $order_status_result->fetch_assoc()) {
    $order_statuses[$row['status']] = $row['count'];
}

// Page title
$page_title = "Sales & Analytics";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JamboPets - <?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <?php include_once '../includes/header.php'; ?>

    <div class="container-fluid">
        <div class="row">
        <div class="col-md-3">
            <!-- Seller sidebar -->
            <?php include_once 'seller_sidebar.php'; ?>
        </div>
            <!-- Main Content -->
            <main class="col-md-9 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?php echo $page_title; ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="?period=today" class="btn btn-sm btn-outline-secondary <?php echo $time_period == 'today' ? 'active' : ''; ?>">Today</a>
                            <a href="?period=week" class="btn btn-sm btn-outline-secondary <?php echo $time_period == 'week' ? 'active' : ''; ?>">Week</a>
                            <a href="?period=month" class="btn btn-sm btn-outline-secondary <?php echo $time_period == 'month' ? 'active' : ''; ?>">Month</a>
                            <a href="?period=all" class="btn btn-sm btn-outline-secondary <?php echo $time_period == 'all' ? 'active' : ''; ?>">All Time</a>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    Showing statistics for: <strong><?php echo $period_text; ?></strong>
                </div>

                <!-- Dashboard Cards -->
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h5 class="card-title">Total Sales</h5>
                                <h2 class="card-text">KSh <?php echo number_format($sales_data['total_sales'] ?? 0, 2); ?></h2>
                                <p class="card-text"><small>From <?php echo $sales_data['order_count'] ?? 0; ?> orders</small></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Active Listings</h5>
                                <h2 class="card-text"><?php echo ($listings_data['active_pets'] ?? 0) + ($listings_data['active_products'] ?? 0); ?></h2>
                                <p class="card-text">
                                    <small>
                                        <?php echo $listings_data['active_pets'] ?? 0; ?> Pets, 
                                        <?php echo $listings_data['active_products'] ?? 0; ?> Products
                                    </small>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <h5 class="card-title">Total Views</h5>
                                <h2 class="card-text"><?php echo ($views_data['pet_views'] ?? 0) + ($views_data['product_views'] ?? 0); ?></h2>
                                <p class="card-text">
                                    <small>
                                        <?php echo $views_data['pet_views'] ?? 0; ?> Pet views, 
                                        <?php echo $views_data['product_views'] ?? 0; ?> Product views
                                    </small>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">Seller Rating</h5>
                                <h2 class="card-text">
                                    <?php 
                                    $rating = $rating_data['avg_rating'] ?? 0;
                                    echo number_format($rating, 1);
                                    ?> / 5.0
                                </h2>
                                <p class="card-text">
                                    <small>
                                        <?php 
                                        for($i = 1; $i <= 5; $i++) {
                                            echo $i <= round($rating) ? '★' : '☆';
                                        }
                                        echo " (" . ($rating_data['review_count'] ?? 0) . " reviews)";
                                        ?>
                                    </small>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sales Chart -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Monthly Sales (Last 6 Months)</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="salesChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Order Status</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="orderStatusChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Selling Items -->
                <div class="row">
                    <!-- Top Pets -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Top Selling Pets</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-sm">
                                        <thead>
                                            <tr>
                                                <th>Pet Name</th>
                                                <th>Breed</th>
                                                <th>Price (KSh)</th>
                                                <th>Views</th>
                                                <th>Sold</th>
                                                <th>Revenue</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($top_pets_result->num_rows > 0): ?>
                                                <?php while ($pet = $top_pets_result->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($pet['name']); ?></td>
                                                        <td><?php echo htmlspecialchars($pet['breed']); ?></td>
                                                        <td><?php echo number_format($pet['price'], 2); ?></td>
                                                        <td><?php echo $pet['views']; ?></td>
                                                        <td><?php echo $pet['sales_count']; ?></td>
                                                        <td><?php echo number_format($pet['revenue'] ?? 0, 2); ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="text-center">No pet sales data available</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Products -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Top Selling Products</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-sm">
                                        <thead>
                                            <tr>
                                                <th>Product Name</th>
                                                <th>Price (KSh)</th>
                                                <th>Views</th>
                                                <th>Sold</th>
                                                <th>Revenue</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($top_products_result->num_rows > 0): ?>
                                                <?php while ($product = $top_products_result->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                                        <td><?php echo number_format($product['price'], 2); ?></td>
                                                        <td><?php echo $product['views']; ?></td>
                                                        <td><?php echo $product['sales_count']; ?></td>
                                                        <td><?php echo number_format($product['revenue'] ?? 0, 2); ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center">No product sales data available</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Statistics -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Sales Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="border p-3 text-center">
                                            <h6>Pets Sold</h6>
                                            <h3><?php echo $listings_data['sold_pets'] ?? 0; ?></h3>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="border p-3 text-center">
                                            <h6>Products Sold</h6>
                                            <h3><?php echo $listings_data['sold_products'] ?? 0; ?></h3>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="border p-3 text-center">
                                            <h6>Completed Orders</h6>
                                            <h3><?php echo $order_statuses['delivered'] ?? 0; ?></h3>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="border p-3 text-center">
                                            <h6>Pending Orders</h6>
                                            <h3><?php echo ($order_statuses['pending'] ?? 0) + ($order_statuses['processing'] ?? 0); ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <?php include_once '../includes/footer.php'; ?>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sales Chart
        const months = <?php echo json_encode($months); ?>;
        const salesData = <?php echo json_encode($sales_values); ?>;
        
        const salesChart = new Chart(document.getElementById('salesChart'), {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Monthly Sales (KSh)',
                    data: salesData,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'KSh ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Order Status Chart
        const orderStatusChart = new Chart(document.getElementById('orderStatusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'],
                datasets: [{
                    data: [
                        <?php echo $order_statuses['pending'] ?? 0; ?>,
                        <?php echo $order_statuses['processing'] ?? 0; ?>,
                        <?php echo $order_statuses['shipped'] ?? 0; ?>,
                        <?php echo $order_statuses['delivered'] ?? 0; ?>,
                        <?php echo $order_statuses['cancelled'] ?? 0; ?>
                    ],
                    backgroundColor: [
                        '#FFC107', // yellow for pending
                        '#17A2B8', // info blue for processing
                        '#007BFF', // primary blue for shipped
                        '#28A745', // green for delivered
                        '#DC3545'  // red for cancelled
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>

</html>