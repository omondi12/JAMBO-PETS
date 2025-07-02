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
?>
<?php
 
 
require_once '../includes/functions.php';

 

// Get some quick stats for the dashboard
$stats = [];

// Total users
$query = "SELECT COUNT(*) AS total_users FROM users";
$result = $conn->query($query);
$stats['total_users'] = $result->fetch_assoc()['total_users'];

// Total sellers
$query = "SELECT COUNT(*) AS total_sellers FROM seller_profiles";
$result = $conn->query($query);
$stats['total_sellers'] = $result->fetch_assoc()['total_sellers'];

// Total buyers
$query = "SELECT COUNT(*) AS total_buyers FROM users WHERE user_type = 'buyer'";
$result = $conn->query($query);
$stats['total_buyers'] = $result->fetch_assoc()['total_buyers'];

// Total pets
$query = "SELECT COUNT(*) AS total_pets FROM pets";
$result = $conn->query($query);
$stats['total_pets'] = $result->fetch_assoc()['total_pets'];

// Total products
$query = "SELECT COUNT(*) AS total_products FROM products";
$result = $conn->query($query);
$stats['total_products'] = $result->fetch_assoc()['total_products'];

// Pending approvals (pets + products)
$query = "SELECT COUNT(*) AS pending_pets FROM pets WHERE approval_status = 'pending'";
$result = $conn->query($query);
$stats['pending_pets'] = $result->fetch_assoc()['pending_pets'];

$query = "SELECT COUNT(*) AS pending_products FROM products WHERE approval_status = 'pending'";
$result = $conn->query($query);
$stats['pending_products'] = $result->fetch_assoc()['pending_products'];

$stats['total_pending'] = $stats['pending_pets'] + $stats['pending_products'];

// Total orders
$query = "SELECT COUNT(*) AS total_orders FROM orders";
$result = $conn->query($query);
$stats['total_orders'] = $result->fetch_assoc()['total_orders'];

// Recent users
$query = "SELECT * FROM users ORDER BY created_at DESC LIMIT 5";
$recent_users = $conn->query($query);

// Recent listings
$query = "SELECT p.pet_id, p.name, p.price, p.approval_status, p.created_at, u.first_name, u.last_name 
          FROM pets p 
          JOIN seller_profiles s ON p.seller_id = s.seller_id 
          JOIN users u ON s.user_id = u.user_id 
          ORDER BY p.created_at DESC LIMIT 5";
$recent_listings = $conn->query($query);

// Get monthly sales data for the chart (last 6 months)
$sales_query = "SELECT 
                    DATE_FORMAT(order_date, '%Y-%m') AS month,
                    COUNT(*) AS order_count,
                    SUM(total_amount) AS revenue
                FROM orders
                WHERE order_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(order_date, '%Y-%m')
                ORDER BY month ASC";
$sales_data = $conn->query($sales_query);
$monthly_sales = [];
$monthly_orders = [];
$labels = [];

while ($row = $sales_data->fetch_assoc()) {
    $labels[] = date('M Y', strtotime($row['month'] . '-01'));
    $monthly_sales[] = $row['revenue'];
    $monthly_orders[] = $row['order_count'];
}

// Set up data for passing to JavaScript
$labels_json = json_encode($labels);
$monthly_sales_json = json_encode($monthly_sales);

$page_title = "Admin Dashboard - Jambo Pets";
include_once '../includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Admin Dashboard</h1>
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-download fa-sm text-white-50"></i> Generate Report
        </a>
    </div>

    <!-- Stats Cards Row -->
    <div class="row">
        <!-- Users Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_users']; ?></div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span class="text-success mr-2"><i class="fas fa-user"></i></span>
                                <span>Buyers: <?php echo $stats['total_buyers']; ?> | Sellers: <?php echo $stats['total_sellers']; ?></span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Listings Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Listings</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['total_pets'] + $stats['total_products']; ?>
                            </div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span class="text-success mr-2"><i class="fas fa-paw"></i></span>
                                <span>Pets: <?php echo $stats['total_pets']; ?> | Products: <?php echo $stats['total_products']; ?></span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tags fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Orders
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_orders']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Approvals Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Approvals</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_pending']; ?></div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span class="text-warning mr-2"><i class="fas fa-exclamation-triangle"></i></span>
                                <span>Pets: <?php echo $stats['pending_pets']; ?> | Products: <?php echo $stats['pending_products']; ?></span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Sales Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Sales Overview</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                            aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Export Options:</div>
                            <a class="dropdown-item" href="#">Export as CSV</a>
                            <a class="dropdown-item" href="#">Export as PDF</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#">View Details</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="myAreaChart" data-labels='<?php echo $labels_json; ?>' data-sales='<?php echo $monthly_sales_json; ?>'></canvas>
                    </div>
                </div>

            </div>
        </div>

        <!-- County Distribution Pie Chart -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">User Distribution by County</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                            aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Export Options:</div>
                            <a class="dropdown-item" href="#">Export as CSV</a>
                            <a class="dropdown-item" href="#">Export as PDF</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#">View Details</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="myPieChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="mr-2">
                            <i class="fas fa-circle text-primary"></i> Nairobi
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-success"></i> Mombasa
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-info"></i> Other Counties
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Recent Users -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Users</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Email</th>
                                    <th>Joined</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = $recent_users->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></td>
                                    <td><span class="badge badge-<?php echo $user['user_type'] == 'seller' ? 'success' : ($user['user_type'] == 'admin' ? 'danger' : 'primary'); ?>"><?php echo ucfirst($user['user_type']); ?></span></td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <?php if ($user['status'] == 'active'): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php elseif ($user['status'] == 'inactive'): ?>
                                            <span class="badge badge-secondary">Inactive</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Suspended</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="users.php" class="btn btn-sm btn-primary">View All Users</a>
                </div>
            </div>
        </div>

        <!-- Recent Listings -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Pet Listings</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Pet Name</th>
                                    <th>Price (KSh)</th>
                                    <th>Seller</th>
                                    <th>Listed On</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($listing = $recent_listings->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $listing['name']; ?></td>
                                    <td><?php echo number_format($listing['price'], 2); ?></td>
                                    <td><?php echo $listing['first_name'] . ' ' . $listing['last_name']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($listing['created_at'])); ?></td>
                                    <td>
                                        <?php if ($listing['approval_status'] == 'approved'): ?>
                                            <span class="badge badge-success">Approved</span>
                                        <?php elseif ($listing['approval_status'] == 'pending'): ?>
                                            <span class="badge badge-warning">Pending</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="listings.php" class="btn btn-sm btn-primary">View All Listings</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Page level custom scripts -->
<script>
// Set new default font family and font color to mimic Bootstrap's default styling
Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
Chart.defaults.global.defaultFontColor = '#858796';

// Area Chart Example
var ctx = document.getElementById("myAreaChart");
var myLineChart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: <?php echo json_encode($labels); ?>,
    datasets: [{
      label: "Revenue",
      lineTension: 0.3,
      backgroundColor: "rgba(78, 115, 223, 0.05)",
      borderColor: "rgba(78, 115, 223, 1)",
      pointRadius: 3,
      pointBackgroundColor: "rgba(78, 115, 223, 1)",
      pointBorderColor: "rgba(78, 115, 223, 1)",
      pointHoverRadius: 3,
      pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
      pointHoverBorderColor: "rgba(78, 115, 223, 1)",
      pointHitRadius: 10,
      pointBorderWidth: 2,
      data: <?php echo json_encode($monthly_sales); ?>,
    }],
  },
  options: {
    maintainAspectRatio: false,
    layout: {
      padding: {
        left: 10,
        right: 25,
        top: 25,
        bottom: 0
      }
    },
    scales: {
      xAxes: [{
        time: {
          unit: 'date'
        },
        gridLines: {
          display: false,
          drawBorder: false
        },
        ticks: {
          maxTicksLimit: 7
        }
      }],
      yAxes: [{
        ticks: {
          maxTicksLimit: 5,
          padding: 10,
          // Include a dollar sign in the ticks
          callback: function(value, index, values) {
            return 'KSh ' + number_format(value);
          }
        },
        gridLines: {
          color: "rgb(234, 236, 244)",
          zeroLineColor: "rgb(234, 236, 244)",
          drawBorder: false,
          borderDash: [2],
          zeroLineBorderDash: [2]
        }
      }],
    },
    legend: {
      display: false
    },
    tooltips: {
      backgroundColor: "rgb(255,255,255)",
      bodyFontColor: "#858796",
      titleMarginBottom: 10,
      titleFontColor: '#6e707e',
      titleFontSize: 14,
      borderColor: '#dddfeb',
      borderWidth: 1,
      xPadding: 15,
      yPadding: 15,
      displayColors: false,
      intersect: false,
      mode: 'index',
      caretPadding: 10,
      callbacks: {
        label: function(tooltipItem, chart) {
          var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
          return datasetLabel + ': KSh ' + number_format(tooltipItem.yLabel);
        }
      }
    }
  }
});

// Pie Chart Example
var ctx = document.getElementById("myPieChart");
var myPieChart = new Chart(ctx, {
  type: 'doughnut',
  data: {
    labels: ["Nairobi", "Mombasa", "Other Counties"],
    datasets: [{
      data: [55, 30, 15],
      backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc'],
      hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf'],
      hoverBorderColor: "rgba(234, 236, 244, 1)",
    }],
  },
  options: {
    maintainAspectRatio: false,
    tooltips: {
      backgroundColor: "rgb(255,255,255)",
      bodyFontColor: "#858796",
      borderColor: '#dddfeb',
      borderWidth: 1,
      xPadding: 15,
      yPadding: 15,
      displayColors: false,
      caretPadding: 10,
    },
    legend: {
      display: false
    },
    cutoutPercentage: 80,
  },
});

// Format number to have commas for thousands
function number_format(number, decimals, dec_point, thousands_sep) {
  number = (number + '').replace(',', '').replace(' ', '');
  var n = !isFinite(+number) ? 0 : +number,
    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
    s = '',
    toFixedFix = function(n, prec) {
      var k = Math.pow(10, prec);
      return '' + Math.round(n * k) / k;
    };
  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
  if (s[0].length > 3) {
    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
  }
  if ((s[1] || '').length < prec) {
    s[1] = s[1] || '';
    s[1] += new Array(prec - s[1].length + 1).join('0');
  }
  return s.join(dec);
}
</script>

<?php include_once '../includes/admin_footer.php'; ?>