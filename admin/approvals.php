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

// Include PHPMailer
require_once '../PHPMailer/src/PHPMailer.php';
require_once '../PHPMailer/src/Exception.php';
require_once '../PHPMailer/src/SMTP.php';

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

// Function to get site settings
function getSiteSetting($conn, $key) {
    $stmt = $conn->prepare("SELECT value FROM settings WHERE setting_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['value'];
    }
    return null;
}

// Function to send seller approval email
function sendSellerApprovalEmail($userEmail, $firstName, $lastName, $businessName, $verificationNotes, $conn) {
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'muranga.community.projects@gmail.com'; // Replace with your email
    $mail->Password = 'nxcikdztzvpayjvc'; // Replace with your app password
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Get site settings
    $siteName = getSiteSetting($conn, 'site_name') ?: 'Jambo Pets';
    $siteLogo = getSiteSetting($conn, 'site_logo');
    $contactEmail = getSiteSetting($conn, 'contact_email') ?: 'support@jambopets.com';

    $mail->setFrom($contactEmail, $siteName);
    $mail->addAddress($userEmail);

    $mail->isHTML(true);
    $mail->Subject = "🎉 Seller Account Approved - Start Selling Now! | $siteName";
    
    // Email body with logo and styling
    $logoHtml = '';
    if ($siteLogo) {
        $logoUrl = "https://" . $_SERVER['HTTP_HOST'] . "/" . $siteLogo;
        $logoHtml = "<img src='$logoUrl' alt='$siteName Logo' style='max-width: 200px; height: auto; margin-bottom: 20px;'>";
    }

    $notesSection = '';
    if (!empty($verificationNotes)) {
        $notesSection = "
        <div style='background-color: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #2196f3;'>
            <h3 style='color: #1565c0; margin: 0 0 10px 0;'>📝 Admin Notes</h3>
            <p style='margin: 0; color: #1565c0;'>$verificationNotes</p>
        </div>";
    }
    
    $mail->Body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;'>
        <div style='background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
            <div style='text-align: center; margin-bottom: 30px;'>
                $logoHtml
                <h1 style='color: #2c3e50; margin: 0;'>🎉 Congratulations!</h1>
                <h2 style='color: #27ae60; margin: 10px 0 0 0;'>Your Seller Account is Approved!</h2>
            </div>
            
            <div style='color: #333; line-height: 1.6;'>
                <p>Dear <strong>$firstName $lastName</strong>,</p>
                
                <div style='background-color: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #c3e6cb;'>
                    <p style='margin: 0; color: #155724; font-size: 16px; text-align: center;'>
                        ✅ <strong>Your seller account for \"$businessName\" has been successfully verified and approved!</strong>
                    </p>
                </div>
                
                $notesSection
                
                <p><strong>🚀 You can now start selling on $siteName!</strong></p>
                
                <p><strong>What you can do now:</strong></p>
                <ol style='color: #555; padding-left: 20px;'>
                    <li><strong>Add Products:</strong> Upload your pet products with high-quality photos</li>
                    <li><strong>Set Prices:</strong> Configure competitive pricing for your items</li>
                    <li><strong>Manage Inventory:</strong> Keep track of your stock levels</li>
                    <li><strong>Process Orders:</strong> Handle customer orders and payments</li>
                    <li><strong>Communicate:</strong> Respond to customer inquiries promptly</li>
                    <li><strong>Track Performance:</strong> Monitor your sales and customer feedback</li>
                </ol>
                
                <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107;'>
                    <h3 style='color: #856404; margin: 0 0 10px 0;'>💡 Pro Tips for Success</h3>
                    <ul style='margin: 0; color: #856404; padding-left: 20px;'>
                        <li>Use clear, high-quality product photos</li>
                        <li>Write detailed product descriptions</li>
                        <li>Respond to customer messages quickly</li>
                        <li>Keep your inventory updated</li>
                        <li>Provide excellent customer service</li>
                    </ul>
                </div>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='https://" . $_SERVER['HTTP_HOST'] . "/seller/dashboard.php' style='background-color: #27ae60; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>
                        🏪 Go to Seller Dashboard
                    </a>
                </div>
                
                <div style='background-color: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <p style='margin: 0; color: #2d5a2d;'><strong>🎯 Ready to Start?</strong> Log in to your seller dashboard and add your first product!</p>
                </div>
                
                <p>If you need any assistance or have questions about selling on our platform, please don't hesitate to contact our seller support team at <a href='mailto:$contactEmail' style='color: #3498db;'>$contactEmail</a>.</p>
                
                <p>Welcome to the $siteName seller community! We're excited to see your business grow.</p>
                
                <p style='margin-top: 30px;'>
                    Best regards,<br>
                    <strong>The $siteName Team</strong>
                </p>
            </div>
            
            <div style='border-top: 1px solid #eee; margin-top: 30px; padding-top: 20px; text-align: center; color: #777; font-size: 12px;'>
                <p>This email was sent from $siteName. Please do not reply to this email.</p>
            </div>
        </div>
    </div>";

    return $mail->send();
}

// Function to send seller rejection email
function sendSellerRejectionEmail($userEmail, $firstName, $lastName, $businessName, $verificationNotes, $conn) {
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'muranga.community.projects@gmail.com'; // Replace with your email
    $mail->Password = 'nxcikdztzvpayjvc'; // Replace with your app password
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Get site settings
    $siteName = getSiteSetting($conn, 'site_name') ?: 'Jambo Pets';
    $siteLogo = getSiteSetting($conn, 'site_logo');
    $contactEmail = getSiteSetting($conn, 'contact_email') ?: 'support@jambopets.com';

    $mail->setFrom($contactEmail, $siteName);
    $mail->addAddress($userEmail);

    $mail->isHTML(true);
    $mail->Subject = "Seller Account Application Update | $siteName";
    
    // Email body with logo and styling
    $logoHtml = '';
    if ($siteLogo) {
        $logoUrl = "https://" . $_SERVER['HTTP_HOST'] . "/" . $siteLogo;
        $logoHtml = "<img src='$logoUrl' alt='$siteName Logo' style='max-width: 200px; height: auto; margin-bottom: 20px;'>";
    }

    $notesSection = '';
    if (!empty($verificationNotes)) {
        $notesSection = "
        <div style='background-color: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #dc3545;'>
            <h3 style='color: #721c24; margin: 0 0 10px 0;'>📝 Review Notes</h3>
            <p style='margin: 0; color: #721c24;'>$verificationNotes</p>
        </div>";
    }
    
    $mail->Body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;'>
        <div style='background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
            <div style='text-align: center; margin-bottom: 30px;'>
                $logoHtml
                <h1 style='color: #2c3e50; margin: 0;'>Seller Account Application Update</h1>
            </div>
            
            <div style='color: #333; line-height: 1.6;'>
                <p>Dear <strong>$firstName $lastName</strong>,</p>
                
                <p>Thank you for your interest in becoming a seller on <strong>$siteName</strong>.</p>
                
                <div style='background-color: #f8d7da; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #f5c6cb;'>
                    <p style='margin: 0; color: #721c24; font-size: 16px; text-align: center;'>
                        ❌ <strong>Unfortunately, your seller account application for \"$businessName\" has not been approved at this time.</strong>
                    </p>
                </div>
                
                $notesSection
                
                <div style='background-color: #e2e3e5; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <h3 style='color: #383d41; margin: 0 0 10px 0;'>🔄 What's Next?</h3>
                    <ul style='margin: 0; color: #383d41; padding-left: 20px;'>
                        <li>Review the feedback provided above</li>
                        <li>Address any issues mentioned in the review notes</li>
                        <li>You may reapply with updated information</li>
                        <li>Contact our support team if you have questions</li>
                    </ul>
                </div>
                
                <p>We encourage you to address any concerns and reapply when you're ready. Our goal is to maintain a high-quality marketplace for all our users.</p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='https://" . $_SERVER['HTTP_HOST'] . "/auth/register.php?type=seller' style='background-color: #6c757d; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>
                        🔄 Reapply as Seller
                    </a>
                </div>
                
                <p>If you have any questions about this decision or need clarification on the requirements, please contact our support team at <a href='mailto:$contactEmail' style='color: #3498db;'>$contactEmail</a>.</p>
                
                <p>Thank you for your understanding.</p>
                
                <p style='margin-top: 30px;'>
                    Best regards,<br>
                    <strong>The $siteName Team</strong>
                </p>
            </div>
            
            <div style='border-top: 1px solid #eee; margin-top: 30px; padding-top: 20px; text-align: center; color: #777; font-size: 12px;'>
                <p>This email was sent from $siteName. Please do not reply to this email.</p>
            </div>
        </div>
    </div>";

    return $mail->send();
}

require_once '../includes/admin_header.php';

// Handle approval/rejection actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['seller_id'])) {
        $seller_id = mysqli_real_escape_string($conn, $_POST['seller_id']);
        $action = $_POST['action'];
        $verification_notes = isset($_POST['verification_notes']) ? mysqli_real_escape_string($conn, $_POST['verification_notes']) : '';
        
        // Get seller details for email
        $sellerQuery = "SELECT s.*, u.first_name, u.last_name, u.email FROM seller_profiles s JOIN users u ON s.user_id = u.user_id WHERE s.seller_id = ?";
        $sellerStmt = $conn->prepare($sellerQuery);
        $sellerStmt->bind_param("i", $seller_id);
        $sellerStmt->execute();
        $sellerResult = $sellerStmt->get_result();
        $sellerData = $sellerResult->fetch_assoc();
        
        if ($action === 'approve') {
            // Update seller verification status to verified
            $updateQuery = "UPDATE seller_profiles SET verification_status = 'verified', verification_notes = ? WHERE seller_id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("si", $verification_notes, $seller_id);
            
            if ($stmt->execute()) {
                // Send approval email
                $emailSent = false;
                if ($sellerData) {
                    $emailSent = sendSellerApprovalEmail(
                        $sellerData['email'], 
                        $sellerData['first_name'], 
                        $sellerData['last_name'], 
                        $sellerData['business_name'], 
                        $verification_notes, 
                        $conn
                    );
                }
                
                // Set success message
                if ($emailSent) {
                    $success_message = "Seller #" . $seller_id . " has been approved successfully! Approval email sent.";
                } else {
                    $success_message = "Seller #" . $seller_id . " has been approved successfully! (Note: Email notification could not be sent)";
                }
            } else {
                $error_message = "Error approving seller. Please try again.";
            }
            
        } elseif ($action === 'reject') {
            // Update seller verification status to rejected
            $updateQuery = "UPDATE seller_profiles SET verification_status = 'rejected', verification_notes = ? WHERE seller_id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("si", $verification_notes, $seller_id);
            
            if ($stmt->execute()) {
                // Send rejection email
                $emailSent = false;
                if ($sellerData) {
                    $emailSent = sendSellerRejectionEmail(
                        $sellerData['email'], 
                        $sellerData['first_name'], 
                        $sellerData['last_name'], 
                        $sellerData['business_name'], 
                        $verification_notes, 
                        $conn
                    );
                }
                
                // Set error message
                if ($emailSent) {
                    $error_message = "Seller #" . $seller_id . " has been rejected. Notification email sent.";
                } else {
                    $error_message = "Seller #" . $seller_id . " has been rejected. (Note: Email notification could not be sent)";
                }
            } else {
                $error_message = "Error rejecting seller. Please try again.";
            }
        }
        
        $sellerStmt->close();
        if (isset($stmt)) {
            $stmt->close();
        }
    }
}

// Get filter status from query parameter, default to 'pending'
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'pending';

// Prepare query based on status filter
if ($status_filter === 'all') {
    $query = "SELECT s.*, u.first_name, u.last_name, u.email, u.phone, u.county, u.address, u.created_at 
              FROM seller_profiles s 
              JOIN users u ON s.user_id = u.user_id 
              ORDER BY s.verification_status = 'pending' DESC, u.created_at DESC";
} else {
    $query = "SELECT s.*, u.first_name, u.last_name, u.email, u.phone, u.county, u.address, u.created_at 
              FROM seller_profiles s 
              JOIN users u ON s.user_id = u.user_id 
              WHERE s.verification_status = ?
              ORDER BY u.created_at DESC";
}

// Execute query
$stmt = $conn->prepare($query);
if ($status_filter !== 'all') {
    $stmt->bind_param("s", $status_filter);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Seller Approval Management</h1>
    </div>

    <!-- Status Filter Tabs -->
    <div class="card mb-4">
        <div class="card-body p-0">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link <?php echo $status_filter == 'pending' ? 'active' : ''; ?>" href="?status=pending">
                        Pending
                        <?php
                        // Count pending approvals
                        $pendingQuery = "SELECT COUNT(*) as count FROM seller_profiles WHERE verification_status = 'pending'";
                        $pendingResult = $conn->query($pendingQuery);
                        $pendingCount = $pendingResult->fetch_assoc()['count'];
                        if ($pendingCount > 0) {
                            echo '<span class="badge bg-warning text-dark ms-1">' . $pendingCount . '</span>';
                        }
                        ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status_filter == 'verified' ? 'active' : ''; ?>" href="?status=verified">Verified</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status_filter == 'rejected' ? 'active' : ''; ?>" href="?status=rejected">Rejected</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status_filter == 'all' ? 'active' : ''; ?>" href="?status=all">All</a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Sellers List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <?php 
                switch ($status_filter) {
                    case 'pending':
                        echo 'Pending Seller Approvals';
                        break;
                    case 'verified':
                        echo 'Verified Sellers';
                        break;
                    case 'rejected':
                        echo 'Rejected Sellers';
                        break;
                    default:
                        echo 'All Sellers';
                }
                ?>
            </h6>
        </div>
        <div class="card-body">
            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Seller ID</th>
                                <th>Name</th>
                                <th>Business Name</th>
                                <th>Location</th>
                                <th>Contact</th>
                                <th>Submitted</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($seller = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php echo $seller['seller_id']; ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($seller['first_name'] . ' ' . $seller['last_name']); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($seller['business_name'] ?? 'N/A'); ?>
                                    </td>
                                    <td>
                                        <?php echo $seller['county']; ?>
                                        <?php echo !empty($seller['address']) ? '<br><small class="text-muted">' . htmlspecialchars($seller['address']) . '</small>' : ''; ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($seller['email']); ?>
                                        <?php echo !empty($seller['phone']) ? '<br>' . htmlspecialchars($seller['phone']) : ''; ?>
                                    </td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($seller['created_at'])); ?>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo date('h:i A', strtotime($seller['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($seller['verification_status'] === 'pending'): ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php elseif ($seller['verification_status'] === 'verified'): ?>
                                            <span class="badge bg-success">Verified</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewSellerModal<?php echo $seller['seller_id']; ?>">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <?php if ($seller['verification_status'] === 'pending'): ?>
                                            <button type="button" class="btn btn-sm btn-success mt-1" data-bs-toggle="modal" data-bs-target="#approveModal<?php echo $seller['seller_id']; ?>">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger mt-1" data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo $seller['seller_id']; ?>">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                
                                <!-- View Seller Modal -->
                                <div class="modal fade" id="viewSellerModal<?php echo $seller['seller_id']; ?>" tabindex="-1" aria-labelledby="viewSellerModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="viewSellerModalLabel">Seller Details: <?php echo htmlspecialchars($seller['first_name'] . ' ' . $seller['last_name']); ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h6 class="fw-bold">Personal Information</h6>
                                                        <p>
                                                            <strong>Full Name:</strong> <?php echo htmlspecialchars($seller['first_name'] . ' ' . $seller['last_name']); ?><br>
                                                            <strong>Email:</strong> <?php echo htmlspecialchars($seller['email']); ?><br>
                                                            <strong>Phone:</strong> <?php echo htmlspecialchars($seller['phone']); ?><br>
                                                            <strong>ID Number:</strong> <?php echo htmlspecialchars($seller['id_number'] ?? 'Not provided'); ?><br>
                                                            <strong>County:</strong> <?php echo htmlspecialchars($seller['county']); ?><br>
                                                            <strong>Address:</strong> <?php echo htmlspecialchars($seller['address'] ?? 'Not provided'); ?><br>
                                                            <strong>Joined:</strong> <?php echo date('M d, Y', strtotime($seller['created_at'])); ?>
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6 class="fw-bold">Business Information</h6>
                                                        <p>
                                                            <strong>Business Name:</strong> <?php echo htmlspecialchars($seller['business_name'] ?? 'Not provided'); ?><br>
                                                            <strong>Business Description:</strong><br>
                                                            <?php echo !empty($seller['business_description']) ? nl2br(htmlspecialchars($seller['business_description'])) : 'Not provided'; ?>
                                                        </p>
                                                    </div>
                                                </div>
                                                
                                                <hr>
                                                
                                                <div class="row">
                                                    <div class="col-12">
                                                        <h6 class="fw-bold">ID Verification</h6>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <p class="mb-1">ID Front</p>
                                                        <?php if (!empty($seller['id_front_image'])): ?>
                                                            <img src="<?php echo BASE_URL . $seller['id_front_image']; ?>" class="img-thumbnail" alt="ID Front">
                                                        <?php else: ?>
                                                            <div class="alert alert-warning small">ID front image not uploaded</div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <p class="mb-1">ID Back</p>
                                                        <?php if (!empty($seller['id_back_image'])): ?>
                                                            <img src="<?php echo BASE_URL . $seller['id_back_image']; ?>" class="img-thumbnail" alt="ID Back">
                                                        <?php else: ?>
                                                            <div class="alert alert-warning small">ID back image not uploaded</div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <p class="mb-1">Selfie with ID</p>
                                                        <?php if (!empty($seller['id_selfie_image'])): ?>
                                                            <img src="<?php echo BASE_URL . $seller['id_selfie_image']; ?>" class="img-thumbnail" alt="Selfie with ID">
                                                        <?php else: ?>
                                                            <div class="alert alert-warning small">Selfie with ID not uploaded</div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                
                                                <?php if (!empty($seller['verification_notes'])): ?>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-12">
                                                        <h6 class="fw-bold">Verification Notes</h6>
                                                        <p><?php echo nl2br(htmlspecialchars($seller['verification_notes'])); ?></p>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <?php if ($seller['verification_status'] === 'pending'): ?>
                                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal<?php echo $seller['seller_id']; ?>" data-bs-dismiss="modal">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo $seller['seller_id']; ?>" data-bs-dismiss="modal">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Approve Seller Modal -->
                                <div class="modal fade" id="approveModal<?php echo $seller['seller_id']; ?>" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="approveModalLabel">Approve Seller</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form method="POST" action="">
                                                <div class="modal-body">
                                                    <p>Are you sure you want to approve this seller?</p>
                                                    <p><strong>Seller:</strong> <?php echo htmlspecialchars($seller['first_name'] . ' ' . $seller['last_name']); ?> (ID: <?php echo $seller['seller_id']; ?>)</p>
                                                    <p><strong>Business:</strong> <?php echo htmlspecialchars($seller['business_name'] ?? 'N/A'); ?></p>
                                                    
                                                    <div class="mb-3">
                                                        <label for="verification_notes" class="form-label">Notes (Optional)</label>
                                                        <textarea class="form-control" id="verification_notes" name="verification_notes" rows="3" placeholder="Add any notes for this verification..."></textarea>
                                                    </div>
                                                    
                                                    <input type="hidden" name="seller_id" value="<?php echo $seller['seller_id']; ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success">Approve</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Reject Seller Modal -->
                                <div class="modal fade" id="rejectModal<?php echo $seller['seller_id']; ?>" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="rejectModalLabel">Reject Seller</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form method="POST" action="">
                                                <div class="modal-body">
                                                    <p>Are you sure you want to reject this seller?</p>
                                                    <p><strong>Seller:</strong> <?php echo htmlspecialchars($seller['first_name'] . ' ' . $seller['last_name']); ?> (ID: <?php echo $seller['seller_id']; ?>)</p>
                                                    <p><strong>Business:</strong> <?php echo htmlspecialchars($seller['business_name'] ?? 'N/A'); ?></p>
                                                    
                                                    <div class="mb-3">
                                                        <label for="verification_notes" class="form-label">Rejection Reason (Required)</label>
                                                        <textarea class="form-control" id="verification_notes" name="verification_notes" rows="3" placeholder="Please provide a reason for rejection..." required></textarea>
                                                    </div>
                                                    
                                                    <input type="hidden" name="seller_id" value="<?php echo $seller['seller_id']; ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger">Reject</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <img src="<?php echo BASE_URL; ?>assets/images/no-data.svg" alt="No data" style="width: 120px; opacity: 0.7;">
                    <p class="mt-3 text-muted">
                        <?php 
                        switch ($status_filter) {
                            case 'pending':
                                echo 'No pending seller applications found.';
                                break;
                            case 'verified':
                                echo 'No verified sellers found.';
                                break;
                            case 'rejected':
                                echo 'No rejected sellers found.';
                                break;
                            default:
                                echo 'No sellers found.';
                        }
                        ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_header.php';?>

<!-- Bootstrap & jQuery Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</div>
<!-- End of Content Wrapper -->

</body>
</html>