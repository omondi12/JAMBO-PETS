<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection and functions
require_once '../config/db.php';
require_once '../includes/functions.php';

// Set page title
$pageTitle = "Login";

// Include header
require_once '../includes/header.php';

// Initialize variables
$email = '';
$password = '';
$error = '';
$success = '';
$redirect_url = ''; // New variable to store redirect URL for JavaScript

// Check if user is already logged in
if (isLoggedIn()) {
    // Set redirect URL based on user type
    if (isAdmin()) {
        $redirect_url = BASE_URL . 'admin/index.php';
    } elseif (isSeller()) {
        $redirect_url = BASE_URL . 'seller/dashboard.php';
    } else {
        $redirect_url = BASE_URL . 'buyer/dashboard.php';
    }
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    // Validate form data
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } elseif (!isValidEmail($email)) {
        $error = "Please enter a valid email address.";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT user_id, email, password, first_name, last_name, user_type, status FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Check if account is active
            if ($user['status'] !== 'active') {
                $error = "Your account is not active. Please contact support.";
            } else {
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Password is correct, set session variables
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name'] = $user['last_name'];
                    $_SESSION['user_type'] = $user['user_type'];
                    
                    // If seller, get seller_id
                    if ($user['user_type'] === 'seller') {
                        $sellerStmt = $conn->prepare("SELECT seller_id FROM seller_profiles WHERE user_id = ?");
                        $sellerStmt->bind_param("i", $user['user_id']);
                        $sellerStmt->execute();
                        $sellerResult = $sellerStmt->get_result();
                        
                        if ($sellerResult->num_rows === 1) {
                            $seller = $sellerResult->fetch_assoc();
                            $_SESSION['seller_id'] = $seller['seller_id'];
                        }
                    }
                    
                    // Log activity
                    logActivity('login', 'User logged in');
                    
                    // Set redirect URL based on user type
                    if ($user['user_type'] === 'admin') {
                        $redirect_url = BASE_URL . 'admin/index.php';
                    } elseif ($user['user_type'] === 'seller') {
                        $redirect_url = BASE_URL . 'seller/dashboard.php';
                    } else {
                        $redirect_url = BASE_URL . 'buyer/dashboard.php';
                    }
                } else {
                    $error = "Invalid email or password.";
                }
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<?php if (!empty($redirect_url)): ?>
<script>
    // Redirect using JavaScript instead of PHP header()
    window.location.href = "<?php echo $redirect_url; ?>";
</script>
<?php endif; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold">Welcome Back</h2>
                        <p class="text-muted">Sign in to your Jambo Pets account</p>
                    </div>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder="Enter your email" value="<?php echo $email; ?>" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="Enter your password" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Sign In</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p class="mb-0">Don't have an account? <a href="<?php echo BASE_URL; ?>auth/register.php" class="text-primary">Register</a></p>
                        <p class="mt-2"><a href="<?php echo BASE_URL; ?>auth/forgot-password.php" class="text-muted">Forgot Password?</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once '../includes/footer.php';
?>