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
 
// Include the header
require_once '../includes/admin_header.php';


// Initialize messages array
$messages = [];

// Settings Update Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Determine which form was submitted
    if (isset($_POST['update_platform_settings'])) {
        // Handle Platform Settings Update
        $site_name = mysqli_real_escape_string($conn, $_POST['site_name']);
        $contact_email = mysqli_real_escape_string($conn, $_POST['contact_email']);
        $contact_phone = mysqli_real_escape_string($conn, $_POST['contact_phone']);
        $contact_address = mysqli_real_escape_string($conn, $_POST['contact_address']);
        $facebook_link = mysqli_real_escape_string($conn, $_POST['facebook_link']);
        $twitter_link = mysqli_real_escape_string($conn, $_POST['twitter_link']);
        $instagram_link = mysqli_real_escape_string($conn, $_POST['instagram_link']);

        // Handle logo upload if a file was selected
        if (!empty($_FILES['site_logo']['name'])) {
            $upload_dir = '../uploads/logo/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION);
            $file_name = 'site_logo_' . time() . '.' . $file_extension;
            $target_file = $upload_dir . $file_name;
            
            // Check file type
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array(strtolower($file_extension), $allowed_types)) {
                if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $target_file)) {
                    // Update the logo path in settings
                    $logo_path = 'uploads/logo/' . $file_name;
                    $update_logo = "UPDATE settings SET value = ? WHERE setting_key = 'site_logo'";
                    $stmt = $conn->prepare($update_logo);
                    $stmt->bind_param("s", $logo_path);
                    $stmt->execute();
                } else {
                    $messages[] = ['type' => 'danger', 'message' => 'Failed to upload logo.'];
                }
            } else {
                $messages[] = ['type' => 'danger', 'message' => 'Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.'];
            }
        }

        // Update each setting in the database
        $settings = [
            'site_name' => $site_name,
            'contact_email' => $contact_email,
            'contact_phone' => $contact_phone,
            'contact_address' => $contact_address,
            'facebook_link' => $facebook_link,
            'twitter_link' => $twitter_link,
            'instagram_link' => $instagram_link
        ];

        foreach ($settings as $key => $value) {
            $update_query = "UPDATE settings SET value = ? WHERE setting_key = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ss", $value, $key);
            $stmt->execute();
        }

        $messages[] = ['type' => 'success', 'message' => 'Platform settings updated successfully!'];
        
        // Log the action
        logAdminActivity($conn, $_SESSION['user_id'], 'Updated platform settings');

    } elseif (isset($_POST['update_payment_settings'])) {
        // Handle Payment Settings Update
        $mpesa_consumer_key = mysqli_real_escape_string($conn, $_POST['mpesa_consumer_key']);
        $mpesa_consumer_secret = mysqli_real_escape_string($conn, $_POST['mpesa_consumer_secret']);
        $mpesa_shortcode = mysqli_real_escape_string($conn, $_POST['mpesa_shortcode']);
        $mpesa_passkey = mysqli_real_escape_string($conn, $_POST['mpesa_passkey']);
        
        $pesapal_consumer_key = mysqli_real_escape_string($conn, $_POST['pesapal_consumer_key']);
        $pesapal_consumer_secret = mysqli_real_escape_string($conn, $_POST['pesapal_consumer_secret']);
        $pesapal_shortcode = mysqli_real_escape_string($conn, $_POST['pesapal_shortcode']);
        $pesapal_passkey = mysqli_real_escape_string($conn, $_POST['pesapal_passkey']);

        // Update payment settings
        $payment_settings = [
            'mpesa_consumer_key' => $mpesa_consumer_key,
            'mpesa_consumer_secret' => $mpesa_consumer_secret,
            'mpesa_shortcode' => $mpesa_shortcode,
            'mpesa_passkey' => $mpesa_passkey,
            'pesapal_consumer_key' => $pesapal_consumer_key,
            'pesapal_consumer_secret' => $pesapal_consumer_secret,
            'pesapal_shortcode' => $pesapal_shortcode,
            'pesapal_passkey' => $pesapal_passkey
        ];

        foreach ($payment_settings as $key => $value) {
            $update_query = "UPDATE settings SET value = ? WHERE setting_key = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ss", $value, $key);
            $stmt->execute();
        }

        $messages[] = ['type' => 'success', 'message' => 'Payment settings updated successfully!'];
        
        // Log the action
        logAdminActivity($conn, $_SESSION['user_id'], 'Updated payment integration settings');
    }
}

// Database Backup
if (isset($_POST['backup_database'])) {
    $backup_dir = '../backups/';
    
    // Create directory if it doesn't exist
    if (!file_exists($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }
    
    $backup_file = $backup_dir . 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    
    // Get database credentials from config
    $db_host = 'localhost'; // Update with your actual host
    $db_user = 'root'; // Update with your actual username
    $db_pass = ''; // Update with your actual password
    $db_name = 'jambo_pets'; // Update with your actual database name
    
    // Execute mysqldump command
    $command = "mysqldump --opt -h $db_host -u $db_user";
    if ($db_pass) {
        $command .= " -p$db_pass";
    }
    $command .= " $db_name > $backup_file";
    
    system($command, $return_var);
    
    if ($return_var === 0) {
        $messages[] = ['type' => 'success', 'message' => 'Database backup created successfully!'];
        
        // Log the action
        logAdminActivity($conn, $_SESSION['user_id'], 'Created database backup');
    } else {
        $messages[] = ['type' => 'danger', 'message' => 'Failed to create database backup.'];
    }
}

// Database Restore
if (isset($_POST['restore_database']) && !empty($_FILES['backup_file']['name'])) {
    $file_tmp = $_FILES['backup_file']['tmp_name'];
    $file_name = $_FILES['backup_file']['name'];
    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
    
    if ($file_extension === 'sql') {
        // Get database credentials from config
        $db_host = 'localhost'; // Update with your actual host
        $db_user = 'root'; // Update with your actual username
        $db_pass = ''; // Update with your actual password
        $db_name = 'jambo_pets'; // Update with your actual database name
        
        // Execute mysql command to restore
        $command = "mysql -h $db_host -u $db_user";
        if ($db_pass) {
            $command .= " -p$db_pass";
        }
        $command .= " $db_name < $file_tmp";
        
        system($command, $return_var);
        
        if ($return_var === 0) {
            $messages[] = ['type' => 'success', 'message' => 'Database restored successfully!'];
            
            // Log the action
            logAdminActivity($conn, $_SESSION['user_id'], 'Restored database from backup');
        } else {
            $messages[] = ['type' => 'danger', 'message' => 'Failed to restore database.'];
        }
    } else {
        $messages[] = ['type' => 'danger', 'message' => 'Invalid file type. Only SQL files are allowed.'];
    }
}

// Fetch current settings
$settings = [];
$settings_query = "SELECT setting_key, value FROM settings";
$settings_result = $conn->query($settings_query);

if ($settings_result && $settings_result->num_rows > 0) {
    while ($row = $settings_result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['value'];
    }
}

// Fetch audit logs (recent admin activities)
$audit_logs = [];
$audit_query = "SELECT al.*, u.first_name, u.last_name 
                FROM admin_logs al 
                JOIN users u ON al.user_id = u.user_id 
                ORDER BY al.created_at DESC 
                LIMIT 50";
$audit_result = $conn->query($audit_query);

if ($audit_result && $audit_result->num_rows > 0) {
    while ($row = $audit_result->fetch_assoc()) {
        $audit_logs[] = $row;
    }
}

// Function to log admin activity
function logAdminActivity($conn, $user_id, $action) {
    $action = mysqli_real_escape_string($conn, $action);
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    $log_query = "INSERT INTO admin_logs (user_id, action, ip_address) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($log_query);
    $stmt->bind_param("iss", $user_id, $action, $ip_address);
    $stmt->execute();
}
?>

<!-- Main content -->
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">System Settings</h1>
    
    <!-- Display messages -->
    <?php foreach ($messages as $msg): ?>
        <div class="alert alert-<?php echo $msg['type']; ?> alert-dismissible fade show" role="alert">
            <?php echo $msg['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endforeach; ?>
    
    <div class="row">
        <div class="col-lg-12">
            <!-- Settings Tabs -->
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="settingsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="platform-tab" data-bs-toggle="tab" data-bs-target="#platform" type="button" role="tab" aria-controls="platform" aria-selected="true">
                                <i class="fas fa-globe me-2"></i>Platform Settings
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="payment-tab" data-bs-toggle="tab" data-bs-target="#payment" type="button" role="tab" aria-controls="payment" aria-selected="false">
                                <i class="fas fa-key me-2"></i>Payment Integration
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="database-tab" data-bs-toggle="tab" data-bs-target="#database" type="button" role="tab" aria-controls="database" aria-selected="false">
                                <i class="fas fa-database me-2"></i>Database Management
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs" type="button" role="tab" aria-controls="logs" aria-selected="false">
                                <i class="fas fa-clipboard-list me-2"></i>Audit Logs
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="settingsTabsContent">
                        <!-- Platform Settings Tab -->
                        <div class="tab-pane fade show active" id="platform" role="tabpanel" aria-labelledby="platform-tab">
                            <form method="POST" action="" enctype="multipart/form-data">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="site_name" class="form-label">Site Name</label>
                                            <input type="text" class="form-control" id="site_name" name="site_name" value="<?php echo isset($settings['site_name']) ? htmlspecialchars($settings['site_name']) : 'Jambo Pets'; ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="site_logo" class="form-label">Site Logo</label>
                                            <?php if (isset($settings['site_logo']) && !empty($settings['site_logo'])): ?>
                                                <div class="mb-2">
                                                    <img src="<?php echo '../' . $settings['site_logo']; ?>" alt="Site Logo" class="img-thumbnail" style="max-height: 100px;">
                                                </div>
                                            <?php endif; ?>
                                            <input type="file" class="form-control" id="site_logo" name="site_logo">
                                            <small class="form-text text-muted">Recommended size: 200x50 pixels</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="contact_email" class="form-label">Contact Email</label>
                                            <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?php echo isset($settings['contact_email']) ? htmlspecialchars($settings['contact_email']) : ''; ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="contact_phone" class="form-label">Contact Phone</label>
                                            <input type="text" class="form-control" id="contact_phone" name="contact_phone" value="<?php echo isset($settings['contact_phone']) ? htmlspecialchars($settings['contact_phone']) : ''; ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="contact_address" class="form-label">Contact Address</label>
                                            <textarea class="form-control" id="contact_address" name="contact_address" rows="3"><?php echo isset($settings['contact_address']) ? htmlspecialchars($settings['contact_address']) : ''; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5>Social Media Links</h5>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="facebook_link" class="form-label">
                                                <i class="fab fa-facebook text-primary me-1"></i> Facebook
                                            </label>
                                            <input type="url" class="form-control" id="facebook_link" name="facebook_link" value="<?php echo isset($settings['facebook_link']) ? htmlspecialchars($settings['facebook_link']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="twitter_link" class="form-label">
                                                <i class="fab fa-twitter text-info me-1"></i> Twitter
                                            </label>
                                            <input type="url" class="form-control" id="twitter_link" name="twitter_link" value="<?php echo isset($settings['twitter_link']) ? htmlspecialchars($settings['twitter_link']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="instagram_link" class="form-label">
                                                <i class="fab fa-instagram text-danger me-1"></i> Instagram
                                            </label>
                                            <input type="url" class="form-control" id="instagram_link" name="instagram_link" value="<?php echo isset($settings['instagram_link']) ? htmlspecialchars($settings['instagram_link']) : ''; ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end">
                                    <button type="submit" name="update_platform_settings" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Save Platform Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Payment Integration Tab -->
                        <div class="tab-pane fade" id="payment" role="tabpanel" aria-labelledby="payment-tab">
                            <form method="POST" action="">
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5><i class="fas fa-mobile-alt me-2"></i>M-Pesa Integration</h5>
                                        <hr>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="mpesa_consumer_key" class="form-label">Consumer Key</label>
                                            <input type="text" class="form-control" id="mpesa_consumer_key" name="mpesa_consumer_key" value="<?php echo isset($settings['mpesa_consumer_key']) ? htmlspecialchars($settings['mpesa_consumer_key']) : ''; ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="mpesa_consumer_secret" class="form-label">Consumer Secret</label>
                                            <input type="password" class="form-control" id="mpesa_consumer_secret" name="mpesa_consumer_secret" value="<?php echo isset($settings['mpesa_consumer_secret']) ? htmlspecialchars($settings['mpesa_consumer_secret']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="mpesa_shortcode" class="form-label">Shortcode</label>
                                            <input type="text" class="form-control" id="mpesa_shortcode" name="mpesa_shortcode" value="<?php echo isset($settings['mpesa_shortcode']) ? htmlspecialchars($settings['mpesa_shortcode']) : ''; ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="mpesa_passkey" class="form-label">Passkey</label>
                                            <input type="password" class="form-control" id="mpesa_passkey" name="mpesa_passkey" value="<?php echo isset($settings['mpesa_passkey']) ? htmlspecialchars($settings['mpesa_passkey']) : ''; ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5><i class="fas fa-credit-card me-2"></i>PesaPal Integration</h5>
                                        <hr>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="pesapal_consumer_key" class="form-label">Consumer Key</label>
                                            <input type="text" class="form-control" id="pesapal_consumer_key" name="pesapal_consumer_key" value="<?php echo isset($settings['pesapal_consumer_key']) ? htmlspecialchars($settings['pesapal_consumer_key']) : ''; ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="pesapal_consumer_secret" class="form-label">Consumer Secret</label>
                                            <input type="password" class="form-control" id="pesapal_consumer_secret" name="pesapal_consumer_secret" value="<?php echo isset($settings['pesapal_consumer_secret']) ? htmlspecialchars($settings['pesapal_consumer_secret']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="pesapal_shortcode" class="form-label">Shortcode</label>
                                            <input type="text" class="form-control" id="pesapal_shortcode" name="pesapal_shortcode" value="<?php echo isset($settings['pesapal_shortcode']) ? htmlspecialchars($settings['pesapal_shortcode']) : ''; ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="pesapal_passkey" class="form-label">Passkey</label>
                                            <input type="password" class="form-control" id="pesapal_passkey" name="pesapal_passkey" value="<?php echo isset($settings['pesapal_passkey']) ? htmlspecialchars($settings['pesapal_passkey']) : ''; ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i> 
                                    These keys are sensitive. Make sure your system is secure and these values are properly encrypted in the database.
                                </div>
                                
                                <div class="d-flex justify-content-end">
                                    <button type="submit" name="update_payment_settings" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Save Payment Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Database Management Tab -->
                        <div class="tab-pane fade" id="database" role="tabpanel" aria-labelledby="database-tab">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="mb-0"><i class="fas fa-download me-2"></i>Backup Database</h5>
                                        </div>
                                        <div class="card-body">
                                            <p>Create a backup of your current database. This will save all your data including users, listings, orders, and system settings.</p>
                                            <form method="POST" action="">
                                                <button type="submit" name="backup_database" class="btn btn-primary">
                                                    <i class="fas fa-download me-1"></i> Create Backup
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="mb-0"><i class="fas fa-upload me-2"></i>Restore Database</h5>
                                        </div>
                                        <div class="card-body">
                                            <p>Restore your database from a previous backup file. Warning: This will overwrite your current data.</p>
                                            <form method="POST" action="" enctype="multipart/form-data">
                                                <div class="mb-3">
                                                    <label for="backup_file" class="form-label">Select SQL Backup File</label>
                                                    <input type="file" class="form-control" id="backup_file" name="backup_file" required>
                                                </div>
                                                <button type="submit" name="restore_database" class="btn btn-danger" onclick="return confirm('Warning: This will overwrite your current database. Are you sure you want to continue?');">
                                                    <i class="fas fa-upload me-1"></i> Restore Backup
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Backup History</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Filename</th>
                                                    <th>Size</th>
                                                    <th>Date Created</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $backup_dir = '../backups/';
                                                if (file_exists($backup_dir)) {
                                                    $backup_files = glob($backup_dir . '*.sql');
                                                    rsort($backup_files); // Sort by newest first
                                                    
                                                    if (count($backup_files) > 0) {
                                                        foreach ($backup_files as $file) {
                                                            $filename = basename($file);
                                                            $filesize = round(filesize($file) / (1024 * 1024), 2); // Size in MB
                                                            $filetime = date("F d, Y h:i A", filemtime($file));
                                                            
                                                            echo "<tr>";
                                                            echo "<td>$filename</td>";
                                                            echo "<td>{$filesize} MB</td>";
                                                            echo "<td>$filetime</td>";
                                                            echo "<td>
                                                                <a href='$file' class='btn btn-sm btn-primary' download><i class='fas fa-download'></i> Download</a>
                                                                <a href='#' class='btn btn-sm btn-danger' onclick='deleteBackup(\"$filename\")'><i class='fas fa-trash'></i> Delete</a>
                                                            </td>";
                                                            echo "</tr>";
                                                        }
                                                    } else {
                                                        echo "<tr><td colspan='4' class='text-center'>No backup files found.</td></tr>";
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='4' class='text-center'>Backup directory not found.</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Audit Logs Tab -->
                        <div class="tab-pane fade" id="logs" role="tabpanel" aria-labelledby="logs-tab">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Admin Activity Logs</h5>
                                    <div>
                                        <button class="btn btn-sm btn-outline-primary" id="refresh-logs">
                                            <i class="fas fa-sync-alt"></i> Refresh
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary" id="export-logs">
                                            <i class="fas fa-file-export"></i> Export
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="logs-table">
                                            <thead>
                                                <tr>
                                                    <th>Admin</th>
                                                    <th>Action</th>
                                                    <th>IP Address</th>
                                                    <th>Date/Time</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($audit_logs as $log): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($log['first_name'] . ' ' . $log['last_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($log['action']); ?></td>
                                                    <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                                    <td><?php echo date('M d, Y h:i A', strtotime($log['created_at'])); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                                
                                                <?php if (empty($audit_logs)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">No activity logs found.</td>
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
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Settings Page -->
<script>
    // Toggle password visibility
    document.addEventListener('DOMContentLoaded', function() {
        // Add event listeners for password visibility toggle
        const passwordFields = document.querySelectorAll('input[type="password"]');
        passwordFields.forEach(field => {
            const id = field.id;
            const parentDiv = field.parentNode;
            
            // Create toggle button
            const toggleBtn = document.createElement('button');
            toggleBtn.type = 'button';
            toggleBtn.className = 'btn btn-outline-secondary btn-sm position-absolute end-0 top-50 translate-middle-y me-2';
            toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';
            toggleBtn.style.zIndex = '10';
            toggleBtn.onclick = function() {
                if (field.type === 'password') {
                    field.type = 'text';
                    this.innerHTML = '<i class="fas fa-eye-slash"></i>';
                } else {
                    field.type = 'password';
                    this.innerHTML = '<i class="fas fa-eye"></i>';
                }
            };
            
            // Make parent position relative for absolute positioning
            parentDiv.style.position = 'relative';
            
            // Add button to DOM
            parentDiv.appendChild(toggleBtn);
        });
        
        // Refresh logs functionality
        document.getElementById('refresh-logs').addEventListener('click', function() {
            window.location.reload();
        });
        
        // Export logs to CSV
        document.getElementById('export-logs').addEventListener('click', function() {
            const table = document.getElementById('logs-table');
            const rows = table.querySelectorAll('tr');
            let csv = [];
            
            // Add headers
            const headers = [];
            table.querySelectorAll('thead th').forEach(th => {
                headers.push(th.textContent.trim());
            });
            csv.push(headers.join(','));
            
            // Add rows
            table.querySelectorAll('tbody tr').forEach(tr => {
                const row = [];
                tr.querySelectorAll('td').forEach(td => {
                    // Escape commas and quotes in the content
                    let content = td.textContent.trim().replace(/"/g, '""');
                    if (content.includes(',')) {
                        content = `"${content}"`;
                    }
                    row.push(content);
                });
                csv.push(row.join(','));
            });
            
            // Download CSV file
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            
            link.setAttribute('href', url);
            link.setAttribute('download', 'admin_activity_logs.csv');
            link.style.visibility = 'hidden';
            
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
        
        // Function to delete backup
        window.deleteBackup = function(filename) {
            if (confirm('Are you sure you want to delete this backup?')) {
                // Send AJAX request to delete file
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'delete_backup.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (this.status === 200) {
                        alert('Backup deleted successfully!');
                        // Refresh the page to update backup list
                        window.location.reload();
                    } else {
                        alert('Failed to delete backup. ' + this.responseText);
                    }
                };
                xhr.send('filename=' + encodeURIComponent(filename));
            }
        };
    });
    </script>

    <?php include_once '../includes/admin_footer.php'; ?>