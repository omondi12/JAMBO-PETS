<?php
// Initialize the session
session_start();

// Include database connection
require_once "config/db.php";

// Set page title
$page_title = "Contact Us";

// Process form submission
$message = '';
$message_class = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_contact'])) {
    // Get form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message_content = trim($_POST['message']);
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($subject) || empty($message_content)) {
        $message = "Please fill in all fields";
        $message_class = "alert-danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address";
        $message_class = "alert-danger";
    } else {
        // Insert into contact table
        $sender_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;
        
        $stmt = $conn->prepare("INSERT INTO contact (name, email, subject, message, sender_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $name, $email, $subject, $message_content, $sender_id);
        
        if ($stmt->execute()) {
            $message = "Your message has been sent successfully. We will get back to you soon!";
            $message_class = "alert-success";
            
            // Clear form data
            $name = $email = $subject = $message_content = '';
            
            // You can also add email notification to admin here
        } else {
            $message = "Error sending message. Please try again later.";
            $message_class = "alert-danger";
        }
    }
}

// Include header
include_once "includes/header.php";
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-body">
                    <h1 class="text-center mb-4">Contact Us</h1>
                    
                    <?php if (!empty($message)): ?>
                        <div class="alert <?php echo $message_class; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row mb-4">
                        <div class="col-md-6 mb-4 mb-md-0">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-info-circle text-primary mr-2"></i>Contact Information</h5>
                                    <hr>
                                    <p><i class="fas fa-map-marker-alt text-primary mr-2"></i> Address:<br>
                                    Jambo Pets Headquarters<br>
                                    Westlands Business Park<br>
                                    Nairobi, Kenya</p>
                                    
                                    <p><i class="fas fa-phone text-primary mr-2"></i> Phone:<br>
                                    +254 700 123 456</p>
                                    
                                    <p><i class="fas fa-envelope text-primary mr-2"></i> Email:<br>
                                    support@jambopets.co.ke</p>
                                    
                                    <div class="mt-4">
                                        <h6>Follow Us:</h6>
                                        <div class="social-icons">
                                            <a href="#" class="text-primary mr-3"><i class="fab fa-facebook-f fa-lg"></i></a>
                                            <a href="#" class="text-info mr-3"><i class="fab fa-twitter fa-lg"></i></a>
                                            <a href="#" class="text-danger mr-3"><i class="fab fa-instagram fa-lg"></i></a>
                                            <a href="#" class="text-success"><i class="fab fa-whatsapp fa-lg"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-clock text-primary mr-2"></i>Operation Hours</h5>
                                    <hr>
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <div class="d-flex justify-content-between">
                                                <span>Monday - Friday:</span>
                                                <span>8:00 AM - 6:00 PM</span>
                                            </div>
                                        </li>
                                        <li class="mb-2">
                                            <div class="d-flex justify-content-between">
                                                <span>Saturday:</span>
                                                <span>9:00 AM - 4:00 PM</span>
                                            </div>
                                        </li>
                                        <li class="mb-2">
                                            <div class="d-flex justify-content-between">
                                                <span>Sunday:</span>
                                                <span>Closed</span>
                                            </div>
                                        </li>
                                    </ul>
                                    
                                    <div class="alert alert-info mt-3">
                                        <p class="mb-0"><small>Online platform available 24/7. Customer support responses may be delayed outside business hours.</small></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-body">
                            <h3 class="card-title">Send Us a Message</h3>
                            <p class="card-text">We'd love to hear from you! Fill out the form below and our team will get back to you as soon as possible.</p>
                            
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                <div class="form-group">
                                    <label for="name">Full Name*</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email Address*</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="subject">Subject*</label>
                                    <input type="text" class="form-control" id="subject" name="subject" value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="message">Message*</label>
                                    <textarea class="form-control" id="message" name="message" rows="5" required><?php echo isset($message_content) ? htmlspecialchars($message_content) : ''; ?></textarea>
                                </div>
                                
                                <button type="submit" name="submit_contact" class="btn btn-primary">Send Message</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Google Map (Optional) -->
    <div class="row mt-5 mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-body p-0">
                    <h3 class="card-title p-3 mb-0">Find Us</h3>
                    <div class="embed-responsive embed-responsive-16by9">
                        <iframe class="embed-responsive-item" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15955.277444276714!2d36.80943441633912!3d-1.2640355805981946!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182f173c0a1d9bed%3A0xb9f95d9cee8f63c1!2sWestlands%2C%20Nairobi!5e0!3m2!1sen!2ske!4v1714509217840!5m2!1sen!2ske" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- FAQs Section -->
    <div class="row mt-5 mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-body">
                    <h3 class="text-center mb-4">Frequently Asked Questions</h3>
                    
                    <div class="accordion" id="contactFaqAccordion">
                        <div class="card">
                            <div class="card-header" id="headingOne">
                                <h2 class="mb-0">
                                    <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        How quickly will I receive a response?
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#contactFaqAccordion">
                                <div class="card-body">
                                    We aim to respond to all inquiries within 24-48 hours during business days. For urgent matters, please call our customer support line directly.
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header" id="headingTwo">
                                <h2 class="mb-0">
                                    <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        Can I visit your office in person?
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#contactFaqAccordion">
                                <div class="card-body">
                                    Yes, you're welcome to visit our office during business hours. However, we recommend scheduling an appointment in advance to ensure we can provide you with the best assistance.
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header" id="headingThree">
                                <h2 class="mb-0">
                                    <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                        I'm having technical issues with the website. What should I do?
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#contactFaqAccordion">
                                <div class="card-body">
                                    Please use the contact form above and select "Technical Support" as your subject. Provide as much detail as possible about the issue you're experiencing, including screenshots if applicable. Our tech team will assist you promptly.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once "includes/footer.php"; ?>