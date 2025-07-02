<?php
// Include the admin header
include '../includes/admin_header.php';

// Check if user is admin
if (!$isLoggedIn || $userType !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Handle mark as read action
if (isset($_POST['mark_read']) && isset($_POST['notification_id'])) {
    $notification_id = intval($_POST['notification_id']);
    // In a real application, you'd update a notifications table
    // For now, we'll just show a success message
    $success_message = "Notification marked as read.";
}

// Handle mark all as read action
if (isset($_POST['mark_all_read'])) {
    // In a real application, you'd update all notifications for this admin
    $success_message = "All notifications marked as read.";
}

// Pagination settings
$notifications_per_page = 10;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $notifications_per_page;

// Filter settings
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get all notifications with detailed information
$all_notifications = [];

// 1. Get pending pet listings with details - FIXED VERSION
$pet_query = "SELECT p.pet_id, p.name, p.created_at, p.price, p.breed, c.name as category, u.first_name, u.last_name, u.email 
              FROM pets p 
              JOIN users u ON p.seller_id = u.user_id 
              JOIN seller_profiles sp ON u.user_id = sp.user_id
              JOIN categories c ON p.category_id = c.category_id
              WHERE p.approval_status = 'pending'";

if ($search) {
    $search_escaped = $conn->real_escape_string($search);
    $pet_query .= " AND (p.name LIKE '%$search_escaped%' OR p.breed LIKE '%$search_escaped%' OR c.name LIKE '%$search_escaped%' OR u.first_name LIKE '%$search_escaped%' OR u.last_name LIKE '%$search_escaped%')";
}

$pet_query .= " ORDER BY p.created_at DESC";
$pet_result = $conn->query($pet_query);

// Check if query was successful
if ($pet_result === false) {
    echo "Error in pet query: " . $conn->error;
    exit();
}

while ($row = $pet_result->fetch_assoc()) {
    $all_notifications[] = [
        'id' => 'pet_' . $row['pet_id'],
        'type' => 'pending_pet',
        'title' => 'Pet Listing Approval Required',
        'message' => 'Pet "' . $row['name'] . '" (' . $row['breed'] . ') by ' . $row['first_name'] . ' ' . $row['last_name'] . ' requires approval',
        'created_at' => $row['created_at'],
        'icon' => 'fas fa-paw',
        'color' => 'warning',
        'link' => 'approvals.php?type=pets&id=' . $row['pet_id'],
        'details' => [
            'Pet Name' => $row['name'],
            'Breed' => $row['breed'],
            'Category' => $row['category'],
            'Price' => 'KSh ' . number_format($row['price'], 2),
            'Seller' => $row['first_name'] . ' ' . $row['last_name'],
            'Contact' => $row['email']
        ]
    ];
}

// 2. Get pending product listings with details - FIXED VERSION
$product_query = "SELECT p.product_id, p.name, p.created_at, p.price, c.name as category, u.first_name, u.last_name, u.email 
                  FROM products p 
                  JOIN users u ON p.seller_id = u.user_id 
                  JOIN seller_profiles sp ON u.user_id = sp.user_id
                  JOIN categories c ON p.category_id = c.category_id
                  WHERE p.approval_status = 'pending'";

if ($search) {
    $search_escaped = $conn->real_escape_string($search);
    $product_query .= " AND (p.name LIKE '%$search_escaped%' OR c.name LIKE '%$search_escaped%' OR u.first_name LIKE '%$search_escaped%' OR u.last_name LIKE '%$search_escaped%')";
}

$product_query .= " ORDER BY p.created_at DESC";
$product_result = $conn->query($product_query);

// Check if query was successful
if ($product_result === false) {
    echo "Error in product query: " . $conn->error;
    exit();
}

while ($row = $product_result->fetch_assoc()) {
    $all_notifications[] = [
        'id' => 'product_' . $row['product_id'],
        'type' => 'pending_product',
        'title' => 'Product Listing Approval Required',
        'message' => 'Product "' . $row['name'] . '" (' . $row['category'] . ') by ' . $row['first_name'] . ' ' . $row['last_name'] . ' requires approval',
        'created_at' => $row['created_at'],
        'icon' => 'fas fa-box',
        'color' => 'warning',
        'link' => 'approvals.php?type=products&id=' . $row['product_id'],
        'details' => [
            'Product Name' => $row['name'],
            'Category' => $row['category'],
            'Price' => 'KSh ' . number_format($row['price'], 2),
            'Seller' => $row['first_name'] . ' ' . $row['last_name'],
            'Contact' => $row['email']
        ]
    ];
}

// 3. Get new orders
$order_query = "SELECT o.order_id, o.total_amount, o.order_date, o.status, u.first_name, u.last_name, u.email,
                       COUNT(oi.order_item_id) as item_count
                FROM orders o 
                JOIN users u ON o.buyer_id = u.user_id 
                LEFT JOIN order_items oi ON o.order_id = oi.order_id
                WHERE o.order_date >= DATE_SUB(NOW(), INTERVAL 1 DAY) AND o.status = 'pending'";

if ($search) {
    $search_escaped = $conn->real_escape_string($search);
    $order_query .= " AND (u.first_name LIKE '%$search_escaped%' OR u.last_name LIKE '%$search_escaped%' OR o.order_id LIKE '%$search_escaped%')";
}

$order_query .= " GROUP BY o.order_id ORDER BY o.order_date DESC";
$order_result = $conn->query($order_query);

// Check if query was successful
if ($order_result === false) {
    echo "Error in order query: " . $conn->error;
    exit();
}

while ($row = $order_result->fetch_assoc()) {
    $all_notifications[] = [
        'id' => 'order_' . $row['order_id'],
        'type' => 'new_order',
        'title' => 'New Order Received',
        'message' => 'Order #' . $row['order_id'] . ' from ' . $row['first_name'] . ' ' . $row['last_name'] . ' for KSh ' . number_format($row['total_amount'], 2),
        'created_at' => $row['order_date'],
        'icon' => 'fas fa-shopping-cart',
        'color' => 'success',
        'link' => 'orders.php?id=' . $row['order_id'],
        'details' => [
            'Order ID' => '#' . $row['order_id'],
            'Customer' => $row['first_name'] . ' ' . $row['last_name'],
            'Contact' => $row['email'],
            'Total Amount' => 'KSh ' . number_format($row['total_amount'], 2),
            'Items' => $row['item_count'] . ' item(s)',
            'Status' => ucfirst($row['status'])
        ]
    ];
}

// 4. Get new user registrations
$user_query = "SELECT user_id, first_name, last_name, email, user_type, created_at 
               FROM users 
               WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) AND user_type != 'admin'";

if ($search) {
    $search_escaped = $conn->real_escape_string($search);
    $user_query .= " AND (first_name LIKE '%$search_escaped%' OR last_name LIKE '%$search_escaped%' OR email LIKE '%$search_escaped%')";
}

$user_query .= " ORDER BY created_at DESC";
$user_result = $conn->query($user_query);

// Check if query was successful
if ($user_result === false) {
    echo "Error in user query: " . $conn->error;
    exit();
}

while ($row = $user_result->fetch_assoc()) {
    $all_notifications[] = [
        'id' => 'user_' . $row['user_id'],
        'type' => 'new_user',
        'title' => 'New User Registration',
        'message' => $row['first_name'] . ' ' . $row['last_name'] . ' has registered as a ' . $row['user_type'],
        'created_at' => $row['created_at'],
        'icon' => 'fas fa-user-plus',
        'color' => 'info',
        'link' => 'users.php?id=' . $row['user_id'],
        'details' => [
            'Name' => $row['first_name'] . ' ' . $row['last_name'],
            'Email' => $row['email'],
            'User Type' => ucfirst($row['user_type']),
            'Registration Date' => date('M d, Y H:i', strtotime($row['created_at']))
        ]
    ];
}

// Sort all notifications by date
usort($all_notifications, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

// Apply filter
if ($filter !== 'all') {
    $all_notifications = array_filter($all_notifications, function($notification) use ($filter) {
        return $notification['type'] === $filter;
    });
}

// Calculate pagination
$total_notifications = count($all_notifications);
$total_pages = ceil($total_notifications / $notifications_per_page);

// Get notifications for current page
$notifications_for_page = array_slice($all_notifications, $offset, $notifications_per_page);

// Time ago function (duplicate from header, but needed here)
function time_agos($datetime) {
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
<style>
.notification-card {
    transition: all 0.3s ease;
    border-left: 4px solid #dee2e6;
}

.notification-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.notification-card.warning {
    border-left-color: #ffc107;
}

.notification-card.success {
    border-left-color: #28a745;
}

.notification-card.info {
    border-left-color: #17a2b8;
}

.notification-card.danger {
    border-left-color: #dc3545;
}

.notification-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.notification-icon.warning {
    background-color: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}

.notification-icon.success {
    background-color: rgba(40, 167, 69, 0.1);
    color: #28a745;
}

.notification-icon.info {
    background-color: rgba(23, 162, 184, 0.1);
    color: #17a2b8;
}

.notification-icon.danger {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.filter-tabs .nav-link {
    border-radius: 20px;
    margin-right: 10px;
    color: #6c757d;
    border: 1px solid #dee2e6;
}

.filter-tabs .nav-link.active {
    background-color: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.notification-details {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-top: 10px;
}

.notification-details dt {
    color: #495057;
    font-weight: 600;
}

.notification-details dd {
    color: #6c757d;
    margin-bottom: 8px;
}

.search-box {
    border-radius: 25px;
    border: 1px solid #dee2e6;
    padding: 8px 20px;
}

.search-box:focus {
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    border-color: var(--primary-color);
}
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0"><i class="fas fa-bell me-2"></i>Notifications</h2>
        <p class="text-muted mb-0">Manage all your admin notifications</p>
    </div>
    <div class="d-flex gap-2">
        <?php if ($total_notifications > 0): ?>
            <form method="POST" class="d-inline">
                <button type="submit" name="mark_all_read" class="btn btn-outline-primary">
                    <i class="fas fa-check-double me-1"></i>Mark All Read
                </button>
            </form>
        <?php endif; ?>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
        </a>
    </div>
</div>

<?php if (isset($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card bg-warning text-white">
            <div class="icon"><i class="fas fa-clock"></i></div>
            <h5>Pending Approvals</h5>
            <h2><?php echo $pending_listings; ?></h2>
            <p>Require immediate attention</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card bg-success text-white">
            <div class="icon"><i class="fas fa-shopping-cart"></i></div>
            <h5>New Orders</h5>
            <h2><?php echo $new_orders; ?></h2>
            <p>In the last 24 hours</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card bg-info text-white">
            <div class="icon"><i class="fas fa-user-plus"></i></div>
            <h5>New Users</h5>
            <h2><?php echo $new_users; ?></h2>
            <p>Recently registered</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card bg-primary text-white">
            <div class="icon"><i class="fas fa-bell"></i></div>
            <h5>Total Notifications</h5>
            <h2><?php echo $total_notifications; ?></h2>
            <p>All active notifications</p>
        </div>
    </div>
</div>

<!-- Filters and Search -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <ul class="nav filter-tabs">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $filter === 'all' ? 'active' : ''; ?>" 
                           href="?filter=all<?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                            All (<?php echo count($all_notifications); ?>)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $filter === 'pending_pet' ? 'active' : ''; ?>" 
                           href="?filter=pending_pet<?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                            Pet Approvals
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $filter === 'pending_product' ? 'active' : ''; ?>" 
                           href="?filter=pending_product<?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                            Product Approvals
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $filter === 'new_order' ? 'active' : ''; ?>" 
                           href="?filter=new_order<?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                            New Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $filter === 'new_user' ? 'active' : ''; ?>" 
                           href="?filter=new_user<?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                            New Users
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-md-4">
                <form method="GET" class="d-flex">
                    <input type="hidden" name="filter" value="<?php echo $filter; ?>">
                    <input type="text" name="search" class="form-control search-box" 
                           placeholder="Search notifications..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-outline-primary ms-2">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Notifications List -->
<?php if (empty($notifications_for_page)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-bell-slash text-muted mb-3" style="font-size: 48px;"></i>
            <h5 class="text-muted">No notifications found</h5>
            <p class="text-muted">
                <?php if ($search): ?>
                    No notifications match your search criteria.
                <?php elseif ($filter !== 'all'): ?>
                    No notifications in this category.
                <?php else: ?>
                    You're all caught up! No new notifications at this time.
                <?php endif; ?>
            </p>
            <?php if ($search || $filter !== 'all'): ?>
                <a href="notifications.php" class="btn btn-primary">View All Notifications</a>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <?php foreach ($notifications_for_page as $notification): ?>
        <div class="card notification-card <?php echo $notification['color']; ?> mb-3">
            <div class="card-body">
                <div class="d-flex align-items-start">
                    <div class="notification-icon <?php echo $notification['color']; ?> me-3">
                        <i class="<?php echo $notification['icon']; ?>"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-1 fw-bold"><?php echo $notification['title']; ?></h6>
                                <p class="mb-1 text-muted"><?php echo $notification['message']; ?></p>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    <?php echo time_agos($notification['created_at']); ?>
                                </small>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="<?php echo $notification['link']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>View
                                </a>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                    <button type="submit" name="mark_read" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-check me-1"></i>Mark Read
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Collapsible Details -->
                        <div class="mt-2">
                            <button class="btn btn-sm btn-link p-0 text-decoration-none" type="button" 
                                    data-bs-toggle="collapse" data-bs-target="#details-<?php echo $notification['id']; ?>" 
                                    aria-expanded="false">
                                <i class="fas fa-chevron-down me-1"></i>Show Details
                            </button>
                        </div>
                        
                        <div class="collapse" id="details-<?php echo $notification['id']; ?>">
                            <div class="notification-details">
                                <dl class="row mb-0">
                                    <?php foreach ($notification['details'] as $key => $value): ?>
                                        <dt class="col-sm-3"><?php echo $key; ?>:</dt>
                                        <dd class="col-sm-9"><?php echo $value; ?></dd>
                                    <?php endforeach; ?>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Notifications pagination">
            <ul class="pagination justify-content-center">
                <?php if ($current_page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?>&filter=<?php echo $filter; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                    <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?>&filter=<?php echo $filter; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>

        <div class="text-center text-muted mt-3">
            Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $notifications_per_page, $total_notifications); ?> 
            of <?php echo $total_notifications; ?> notifications
        </div>
    <?php endif; ?>
<?php endif; ?>

<script>
// Auto-refresh notifications every 30 seconds
setInterval(function() {
    // Only refresh if no search or filter is active and user is on first page
    const urlParams = new URLSearchParams(window.location.search);
    if (!urlParams.get('search') && urlParams.get('filter') === 'all' && 
        (!urlParams.get('page') || urlParams.get('page') === '1')) {
        location.reload();
    }
}, 30000);

// Add smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Show/hide chevron icon based on collapse state
document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(button => {
    button.addEventListener('click', function() {
        const icon = this.querySelector('i');
        const isExpanded = this.getAttribute('aria-expanded') === 'true';
        
        if (isExpanded) {
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
            this.innerHTML = this.innerHTML.replace('Show Details', 'Hide Details');
        } else {
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
            this.innerHTML = this.innerHTML.replace('Hide Details', 'Show Details');
        }
    });
});
</script>

<?php
 
 include '../includes/admin_footer.php';
?>

<!-- Bootstrap JS -->
 
</body>
</html>