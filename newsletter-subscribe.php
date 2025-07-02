<?php
// Include database connection first
require_once 'config/db.php';
require_once 'includes/functions.php';

// Set page title
$pageTitle = "Newsletter Subscription";
$pageDescription = "Subscribe to our pet care newsletter for expert tips and advice.";

// Add debug mode - set to false in production
$debugMode = false;

// Function to handle and display errors based on debug mode
function handleError($message, $sqlError = "") {
    global $debugMode;
    if ($debugMode) {
        return "<div class='alert alert-danger'>Error: $message" . 
               ($sqlError ? "<br>SQL Error: $sqlError" : "") . "</div>";
    } else {
        return "<div class='alert alert-danger'>An error occurred. Please try again later.</div>";
    }
}

// Initialize message variables
$successMessage = '';
$errorMessage = '';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate email
    if (isset($_POST['email']) && !empty($_POST['email'])) {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        
        // Check if email is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = "Please enter a valid email address.";
        } else {
            try {
                // Check if email already exists in database
                $checkEmailQuery = "SELECT * FROM newsletter_subscribers WHERE email = ?";
                $checkStmt = $conn->prepare($checkEmailQuery);
                $checkStmt->bind_param("s", $email);
                $checkStmt->execute();
                $result = $checkStmt->get_result();
                
                if ($result->num_rows > 0) {
                    // Email already subscribed
                    $successMessage = "You're already subscribed to our newsletter!";
                } else {
                    // Insert new subscriber
                    $insertQuery = "INSERT INTO newsletter_subscribers (email, subscription_date, status) VALUES (?, NOW(), 'active')";
                    $stmt = $conn->prepare($insertQuery);
                    $stmt->bind_param("s", $email);
                    
                    if ($stmt->execute()) {
                        // Log activity
                        logActivity('newsletter_subscribe', null, ['email' => $email]);
                        
                        $successMessage = "Thank you for subscribing to our newsletter!";
                        
                        // Optional: Send confirmation email
                        // sendSubscriptionConfirmation($email);
                    } else {
                        throw new Exception($stmt->error);
                    }
                }
            } catch (Exception $e) {
                $errorMessage = handleError("Unable to process your subscription", $e->getMessage());
            }
        }
    } else {
        $errorMessage = "Email address is required.";
    }
}

// Include header
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold">Newsletter Subscription</h1>
                <p class="lead mb-0">Get the latest pet care tips and updates delivered to your inbox</p>
            </div>
        </div>
    </div>
</section>

<!-- Subscription Form Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <?php if (!empty($successMessage)): ?>
                            <div class="alert alert-success text-center mb-4">
                                <i class="fas fa-check-circle me-2"></i> <?php echo $successMessage; ?>
                            </div>
                            <div class="text-center mt-4">
                                <a href="<?php echo BASE_URL; ?>blog.php" class="btn btn-primary">Return to Blog</a>
                            </div>
                        <?php else: ?>
                            <?php if (!empty($errorMessage)): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo $errorMessage; ?>
                                </div>
                            <?php endif; ?>
                            
                            <h4 class="card-title text-center mb-4">Subscribe to Our Newsletter</h4>
                            <p class="text-center text-muted mb-4">Join our community and receive the latest pet care tips, special offers, and updates directly to your inbox.</p>
                            
                            <form action="<?php echo BASE_URL; ?>newsletter-subscribe.php" method="POST">
                                <div class="mb-4">
                                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder="your@email.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                </div>
                                
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="checkbox" id="agree_terms" name="agree_terms" required>
                                    <label class="form-check-label" for="agree_terms">
                                        I agree to receive email newsletters and accept the <a href="<?php echo BASE_URL; ?>privacy-policy.php" target="_blank">Privacy Policy</a>
                                    </label>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">Subscribe Now</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="mt-4 text-center">
                    <p class="text-muted">
                        <i class="fas fa-shield-alt me-1"></i> Your information is secure and will never be shared with third parties.
                    </p>
                    <?php if (empty($successMessage)): ?>
                        <p><a href="<?php echo BASE_URL; ?>blog.php" class="text-decoration-none">Back to Blog</a></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="row mt-5 pt-4 border-top">
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="text-center">
                    <i class="fas fa-envelope-open-text fa-3x text-primary mb-3"></i>
                    <h5>Exclusive Content</h5>
                    <p class="text-muted">Get access to exclusive pet care tips and guides not available on our website.</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="text-center">
                    <i class="fas fa-tag fa-3x text-primary mb-3"></i>
                    <h5>Special Offers</h5>
                    <p class="text-muted">Be the first to know about special promotions and discounts on pet products.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="text-center">
                    <i class="fas fa-bell fa-3x text-primary mb-3"></i>
                    <h5>Event Updates</h5>
                    <p class="text-muted">Stay informed about pet adoption events and workshops in your area.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Frequently Asked Questions</h2>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="subscriptionFaqs">
                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                How often will I receive newsletters?
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#subscriptionFaqs">
                            <div class="accordion-body">
                                Our newsletter is sent out twice a month, typically on the 1st and 15th. Occasionally, we may send special editions for important announcements or exclusive promotions.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Can I unsubscribe at any time?
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#subscriptionFaqs">
                            <div class="accordion-body">
                                Yes, absolutely! Every newsletter we send includes an unsubscribe link at the bottom. You can click this link to instantly unsubscribe. We respect your inbox and make it easy to opt out whenever you wish.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                What type of content will I receive?
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#subscriptionFaqs">
                            <div class="accordion-body">
                                Our newsletter contains a variety of pet care content including training tips, health advice, nutrition information, product recommendations, adoption stories, and seasonal pet care guides specific to the Kenyan context.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0 shadow-sm">
                        <h2 class="accordion-header" id="headingFour">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                Is my information secure?
                            </button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#subscriptionFaqs">
                            <div class="accordion-body">
                                Yes, we take data privacy very seriously. We never share your personal information with third parties without your consent. All email addresses are stored securely in our database with industry-standard protection measures in place.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h2 class="mb-4">Explore Our Pet Care Resources</h2>
                <p class="lead mb-4">While you wait for your first newsletter, check out our blog and pet marketplace.</p>
                <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                    <a href="<?php echo BASE_URL; ?>blog.php" class="btn btn-primary btn-lg px-4 me-sm-3">Read Our Blog</a>
                    <a href="<?php echo BASE_URL; ?>buyer/browse.php" class="btn btn-outline-primary btn-lg px-4">Browse Pets</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
require_once 'includes/footer.php';
?>