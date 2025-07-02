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
// Include admin header
require_once '../includes/admin_header.php';
 

// Process message status updates
if (isset($_POST['update_status'])) {
    $message_id = $_POST['message_id'];
    $new_status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE contact SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $message_id);
    
    if ($stmt->execute()) {
        // Success message
        $status_msg = "Message status updated successfully.";
        $status_class = "alert-success";
    } else {
        // Error message
        $status_msg = "Error updating message status.";
        $status_class = "alert-danger";
    }
    $stmt->close();
}

// Process message deletion
if (isset($_POST['delete_message'])) {
    $message_id = $_POST['message_id'];
    
    $stmt = $conn->prepare("DELETE FROM contact WHERE id = ?");
    $stmt->bind_param("i", $message_id);
    
    if ($stmt->execute()) {
        // Success message
        $status_msg = "Message deleted successfully.";
        $status_class = "alert-success";
    } else {
        // Error message
        $status_msg = "Error deleting message.";
        $status_class = "alert-danger";
    }
    $stmt->close();
}

// Get message details if viewing a specific message
$message_details = null;
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $message_id = $_GET['view'];
    
    // Mark message as read if it's unread
    $stmt = $conn->prepare("UPDATE contact SET status = 'read' WHERE id = ? AND status = 'unread'");
    $stmt->bind_param("i", $message_id);
    $stmt->execute();
    $stmt->close();
    
    // Get message details
    $stmt = $conn->prepare("
        SELECT c.*, u.first_name, u.last_name, u.email as user_email 
        FROM contact c 
        LEFT JOIN users u ON c.sender_id = u.user_id 
        WHERE c.id = ?
    ");
    $stmt->bind_param("i", $message_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $message_details = $result->fetch_assoc();
    }
    $stmt->close();
}

// Handle search and filtering
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build query based on filters
$query = "
    SELECT c.*, u.first_name, u.last_name, u.email as user_email 
    FROM contact c 
    LEFT JOIN users u ON c.sender_id = u.user_id 
    WHERE 1=1
";

// Add search condition
if (!empty($search)) {
    $search_term = "%$search%";
    $query .= " AND (c.name LIKE ? OR c.email LIKE ? OR c.subject LIKE ?)";
}

// Add status filter
if ($status_filter !== 'all') {
    $query .= " AND c.status = ?";
}

$query .= " ORDER BY c.created_at DESC";

// Prepare and execute query
$stmt = $conn->prepare($query);

// Bind parameters based on filters
if (!empty($search) && $status_filter !== 'all') {
    $stmt->bind_param("ssss", $search_term, $search_term, $search_term, $status_filter);
} elseif (!empty($search)) {
    $stmt->bind_param("sss", $search_term, $search_term, $search_term);
} elseif ($status_filter !== 'all') {
    $stmt->bind_param("s", $status_filter);
}

$stmt->execute();
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get counts for different status types
$stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM contact GROUP BY status");
$stmt->execute();
$status_counts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Format counts into an associative array
$counts = [
    'all' => 0,
    'unread' => 0,
    'read' => 0,
    'responded' => 0
];

foreach ($status_counts as $count) {
    $counts[$count['status']] = $count['count'];
    $counts['all'] += $count['count'];
}

// Close database connection
$conn->close();
?>

<!-- Page content -->
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Message Management</h1>
    
    <?php if (isset($status_msg)): ?>
    <div class="alert <?php echo $status_class; ?> alert-dismissible fade show" role="alert">
        <?php echo $status_msg; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h5>Total Messages</h5>
                <h2><?php echo $counts['all']; ?></h2>
                <p>All contact messages</p>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="icon">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                <h5>Unread</h5>
                <h2><?php echo $counts['unread']; ?></h2>
                <p>Messages pending review</p>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="icon">
                    <i class="fas fa-eye"></i>
                </div>
                <h5>Read</h5>
                <h2><?php echo $counts['read']; ?></h2>
                <p>Messages viewed</p>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="icon">
                    <i class="fas fa-reply"></i>
                </div>
                <h5>Responded</h5>
                <h2><?php echo $counts['responded']; ?></h2>
                <p>Completed inquiries</p>
            </div>
        </div>
    </div>
    
    <?php if ($message_details): ?>
    <!-- Message Detail View -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Message Details</h5>
            <a href="contact_messages.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <h6>From</h6>
                    <p>
                        <?php echo htmlspecialchars($message_details['name']); ?>
                        <?php if ($message_details['sender_id']): ?>
                            (Registered User: <?php echo htmlspecialchars($message_details['first_name'] . ' ' . $message_details['last_name']); ?>)
                        <?php endif; ?>
                    </p>
                    <h6>Email</h6>
                    <p><a href="mailto:<?php echo htmlspecialchars($message_details['email']); ?>"><?php echo htmlspecialchars($message_details['email']); ?></a></p>
                </div>
                <div class="col-md-6">
                    <h6>Subject</h6>
                    <p><?php echo htmlspecialchars($message_details['subject']); ?></p>
                    <h6>Date Received</h6>
                    <p><?php echo date('F j, Y g:i A', strtotime($message_details['created_at'])); ?></p>
                    <h6>Status</h6>
                    <p>
                        <span class="badge <?php 
                            echo $message_details['status'] === 'unread' ? 'bg-danger' : 
                                ($message_details['status'] === 'read' ? 'bg-warning' : 'bg-success'); 
                        ?>">
                            <?php echo ucfirst($message_details['status']); ?>
                        </span>
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <h6>Message</h6>
                    <div class="card bg-light p-3 mb-4">
                        <?php echo nl2br(htmlspecialchars($message_details['message'])); ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <form method="post" class="d-inline">
                        <input type="hidden" name="message_id" value="<?php echo $message_details['id']; ?>">
                        <div class="input-group">
                            <select name="status" class="form-select">
                                <option value="unread" <?php echo $message_details['status'] === 'unread' ? 'selected' : ''; ?>>Unread</option>
                                <option value="read" <?php echo $message_details['status'] === 'read' ? 'selected' : ''; ?>>Read</option>
                                <option value="responded" <?php echo $message_details['status'] === 'responded' ? 'selected' : ''; ?>>Responded</option>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                        </div>
                    </form>
                </div>
                <div class="col-md-6 text-end">
                    <a href="mailto:<?php echo htmlspecialchars($message_details['email']); ?>?subject=Re: <?php echo htmlspecialchars($message_details['subject']); ?>" class="btn btn-success me-2">
                        <i class="fas fa-reply me-1"></i> Reply via Email
                    </a>
                    <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this message?');">
                        <input type="hidden" name="message_id" value="<?php echo $message_details['id']; ?>">
                        <button type="submit" name="delete_message" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Message List View -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Contact Messages</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-8">
                    <form method="get" class="d-flex">
                        <input type="text" name="search" class="form-control me-2" placeholder="Search by name, email or subject..." value="<?php echo htmlspecialchars($search); ?>">
                        <select name="status" class="form-select me-2" style="width: auto;">
                            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                            <option value="unread" <?php echo $status_filter === 'unread' ? 'selected' : ''; ?>>Unread</option>
                            <option value="read" <?php echo $status_filter === 'read' ? 'selected' : ''; ?>>Read</option>
                            <option value="responded" <?php echo $status_filter === 'responded' ? 'selected' : ''; ?>>Responded</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <?php if (!empty($search) || $status_filter !== 'all'): ?>
                        <a href="contact_messages.php" class="btn btn-outline-secondary ms-2">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($messages) > 0): ?>
                            <?php foreach ($messages as $message): ?>
                                <tr class="<?php echo $message['status'] === 'unread' ? 'table-info' : ''; ?>">
                                    <td><?php echo $message['id']; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($message['name']); ?>
                                        <?php if ($message['sender_id']): ?>
                                            <span class="badge bg-secondary">Registered</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($message['email']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($message['subject'], 0, 30)) . (strlen($message['subject']) > 30 ? '...' : ''); ?></td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($message['created_at'])); ?></td>
                                    <td>
                                        <span class="badge <?php 
                                            echo $message['status'] === 'unread' ? 'bg-danger' : 
                                                ($message['status'] === 'read' ? 'bg-warning' : 'bg-success'); 
                                        ?>">
                                            <?php echo ucfirst($message['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="contact_messages.php?view=<?php echo $message['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>?subject=Re: <?php echo htmlspecialchars($message['subject']); ?>" class="btn btn-sm btn-success">
                                            <i class="fas fa-reply"></i>
                                        </a>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this message?');">
                                            <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                            <button type="submit" name="delete_message" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No messages found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Include admin footer -->
<?php require_once '../includes/admin_footer.php'; ?>