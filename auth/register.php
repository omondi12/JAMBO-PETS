<?php
// Set page title
$pageTitle = "Register";

// Include header
require_once '../includes/header.php';

// Include database connection
require_once '../config/db.php';

// Include PHPMailer
require_once '../PHPMailer/src/PHPMailer.php';
require_once '../PHPMailer/src/Exception.php';
require_once '../PHPMailer/src/SMTP.php';

// Initialize variables
$firstName = '';
$lastName = '';
$email = '';
$phone = '';
$password = '';
$confirmPassword = '';
$county = '';
$address = '';
$userType = isset($_GET['type']) && $_GET['type'] === 'seller' ? 'seller' : 'buyer';
$businessName = '';
$businessDescription = '';
$idNumber = '';
$error = '';
$success = '';

// Get counties from the database
$countiesQuery = "SELECT * FROM counties ORDER BY county_name";
$countiesResult = $conn->query($countiesQuery);

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

// Function to send welcome email to buyers
function sendBuyerWelcomeEmail($userEmail, $firstName, $lastName, $conn) {
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
    $mail->Subject = "Welcome to $siteName - Account Created Successfully!";
    
    // Email body with logo and styling
    $logoHtml = '';
    if ($siteLogo) {
        $logoUrl = "https://" . $_SERVER['HTTP_HOST'] ."/". "JamboPets/" . $siteLogo;
        $logoHtml = "<img src='$logoUrl' alt='$siteName Logo' style='max-width: 200px; height: auto; margin-bottom: 20px;'>";
    }
    
    $mail->Body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;'>
        <div style='background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
            <div style='text-align: center; margin-bottom: 30px;'>
                $logoHtml
                <h1 style='color: #2c3e50; margin: 0;'>Welcome to $siteName!</h1>
            </div>
            
            <div style='color: #333; line-height: 1.6;'>
                <p>Dear <strong>$firstName $lastName</strong>,</p>
                
                <p>🎉 <strong>Congratulations!</strong> Your buyer account has been successfully created on $siteName.</p>
                
                <p>You can now:</p>
                <ul style='color: #555; padding-left: 20px;'>
                    <li>Browse our wide selection of pet products</li>
                    <li>Purchase items from verified sellers</li>
                    <li>Track your orders and delivery status</li>
                    <li>Leave reviews and ratings</li>
                    <li>Manage your account and preferences</li>
                </ul>
                
                <div style='background-color: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <p style='margin: 0; color: #2d5a2d;'><strong>🐾 Start Shopping:</strong> Explore our categories and find everything your pet needs!</p>
                </div>
                
                <p>If you have any questions or need assistance, please don't hesitate to contact our support team at <a href='mailto:$contactEmail' style='color: #3498db;'>$contactEmail</a>.</p>
                
                <p>Thank you for joining the $siteName family!</p>
                
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

// Function to send account creation email to sellers
function sendSellerAccountEmail($userEmail, $firstName, $lastName, $businessName, $conn) {
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
    $mail->Subject = "Seller Account Created - Under Review | $siteName";
    
    // Email body with logo and styling
    $logoHtml = '';
    if ($siteLogo) {
        $logoUrl = "https://" . $_SERVER['HTTP_HOST'] ."/". "JamboPets/" . $siteLogo;
        $logoHtml = "<img src='$logoUrl' alt='$siteName Logo' style='max-width: 200px; height: auto; margin-bottom: 20px;'>";
    }
    
    $mail->Body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;'>
        <div style='background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
            <div style='text-align: center; margin-bottom: 30px;'>
                $logoHtml
                <h1 style='color: #2c3e50; margin: 0;'>Seller Account Created!</h1>
            </div>
            
            <div style='color: #333; line-height: 1.6;'>
                <p>Dear <strong>$firstName $lastName</strong>,</p>
                
                <p>Thank you for registering as a seller on <strong>$siteName</strong>! Your seller account for <strong>\"$businessName\"</strong> has been successfully created.</p>
                
                <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107;'>
                    <h3 style='color: #856404; margin: 0 0 10px 0;'>📋 Account Under Review</h3>
                    <p style='margin: 0; color: #856404;'>Your account and verification documents are currently being reviewed by our team. This process typically takes 1-3 business days.</p>
                </div>
                
                <p><strong>What happens next:</strong></p>
                <ol style='color: #555; padding-left: 20px;'>
                    <li>Our team will verify your identity documents</li>
                    <li>We'll review your business information</li>
                    <li>Once approved, you'll receive a verification email</li>
                    <li>You can then start listing and selling your products</li>
                </ol>
                
                <div style='background-color: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <p style='margin: 0; color: #2d5a2d;'><strong>🎯 Get Ready:</strong> While waiting for approval, you can prepare your product listings and photos!</p>
                </div>
                
                <p><strong>Account Details:</strong></p>
                <ul style='color: #555; background-color: #f8f9fa; padding: 15px; border-radius: 5px;'>
                    <li><strong>Name:</strong> $firstName $lastName</li>
                    <li><strong>Business:</strong> $businessName</li>
                    <li><strong>Email:</strong> $userEmail</li>
                    <li><strong>Status:</strong> Under Review</li>
                </ul>
                
                <p>If you have any questions during the review process, please contact our seller support team at <a href='mailto:$contactEmail' style='color: #3498db;'>$contactEmail</a>.</p>
                
                <p>We're excited to have you join our community of pet product sellers!</p>
                
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

// Process registration form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $firstName = sanitize($_POST['first_name']);
    $lastName = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $county = isset($_POST['county']) ? sanitize($_POST['county']) : '';
    $address = isset($_POST['address']) ? sanitize($_POST['address']) : '';
    $userType = $_POST['user_type'];
    
    // Get seller specific data if applicable
    if ($userType === 'seller') {
        $businessName = sanitize($_POST['business_name']);
        $businessDescription = sanitize($_POST['business_description']);
        $idNumber = sanitize($_POST['id_number']);
        
        // Handle ID images for sellers
        $idFrontImage = '';
        $idBackImage = '';
        $idSelfieImage = '';
        
        // Check if we have image data from webcam
        if (!empty($_POST['id_front_image']) && !empty($_POST['id_back_image']) && !empty($_POST['id_selfie_image'])) {
            // Create uploads directory if it doesn't exist
            $uploadDir = '../uploads/id_images/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Process ID front image
            $idFrontImageData = $_POST['id_front_image'];
            $idFrontImageData = str_replace('data:image/png;base64,', '', $idFrontImageData);
            $idFrontImageData = str_replace('data:image/jpeg;base64,', '', $idFrontImageData);
            $idFrontImageData = str_replace(' ', '+', $idFrontImageData);
            $idFrontImageData = base64_decode($idFrontImageData);
            $idFrontImageFileName = uniqid('id_front_') . '.jpg';
            file_put_contents($uploadDir . $idFrontImageFileName, $idFrontImageData);
            $idFrontImage = 'uploads/id_images/' . $idFrontImageFileName;
            
            // Process ID back image
            $idBackImageData = $_POST['id_back_image'];
            $idBackImageData = str_replace('data:image/png;base64,', '', $idBackImageData);
            $idBackImageData = str_replace('data:image/jpeg;base64,', '', $idBackImageData);
            $idBackImageData = str_replace(' ', '+', $idBackImageData);
            $idBackImageData = base64_decode($idBackImageData);
            $idBackImageFileName = uniqid('id_back_') . '.jpg';
            file_put_contents($uploadDir . $idBackImageFileName, $idBackImageData);
            $idBackImage = 'uploads/id_images/' . $idBackImageFileName;
            
            // Process ID selfie image
            $idSelfieImageData = $_POST['id_selfie_image'];
            $idSelfieImageData = str_replace('data:image/png;base64,', '', $idSelfieImageData);
            $idSelfieImageData = str_replace('data:image/jpeg;base64,', '', $idSelfieImageData);
            $idSelfieImageData = str_replace(' ', '+', $idSelfieImageData);
            $idSelfieImageData = base64_decode($idSelfieImageData);
            $idSelfieImageFileName = uniqid('id_selfie_') . '.jpg';
            file_put_contents($uploadDir . $idSelfieImageFileName, $idSelfieImageData);
            $idSelfieImage = 'uploads/id_images/' . $idSelfieImageFileName;
        }
    }
    
    // Validate form data
    if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($password) || empty($confirmPassword)) {
        $error = "Please fill in all required fields.";
    } elseif (!isValidEmail($email)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } elseif ($userType === 'seller' && (empty($businessName) || empty($idNumber))) {
        $error = "Business name and ID number are required for sellers.";
    } elseif ($userType === 'seller' && (empty($idFrontImage) || empty($idBackImage) || empty($idSelfieImage))) {
        $error = "Please capture all required ID verification images.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email already exists. Please use a different email or login.";
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Insert user
                $stmt = $conn->prepare("INSERT INTO users (email, password, first_name, last_name, phone, user_type, county, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssss", $email, $hashedPassword, $firstName, $lastName, $phone, $userType, $county, $address);
                $stmt->execute();
                $userId = $conn->insert_id;
                
                // Insert seller profile if user is a seller
                if ($userType === 'seller') {
                    $stmt = $conn->prepare("INSERT INTO seller_profiles (seller_id, user_id, business_name, business_description, id_number, id_front_image, id_back_image, id_selfie_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("iissssss", $userId, $userId, $businessName, $businessDescription, $idNumber, $idFrontImage, $idBackImage, $idSelfieImage);
                    $stmt->execute();
                }
                
                // Commit transaction
                $conn->commit();
                
                // Send appropriate welcome email
                $emailSent = false;
                if ($userType === 'buyer') {
                    $emailSent = sendBuyerWelcomeEmail($email, $firstName, $lastName, $conn);
                } else if ($userType === 'seller') {
                    $emailSent = sendSellerAccountEmail($email, $firstName, $lastName, $businessName, $conn);
                }
                
                if ($emailSent) {
                    $success = "Registration successful! A welcome email has been sent to your email address. You can now login.";
                } else {
                    $success = "Registration successful! You can now login. (Note: Welcome email could not be sent)";
                }
                
                // Clear form data
                $firstName = '';
                $lastName = '';
                $email = '';
                $phone = '';
                $county = '';
                $address = '';
                $businessName = '';
                $businessDescription = '';
                $idNumber = '';
                
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                $error = "Registration failed. Please try again later.";
                error_log("Registration error: " . $e->getMessage());
            }
        }
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold">Create Your Account</h2>
                        <p class="text-muted">Join Jambo Pets as a <?php echo ucfirst($userType); ?></p>
                    </div>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="registrationForm">
                        <!-- User Type Selection -->
                        <div class="mb-4">
                            <label class="form-label">I want to register as:</label>
                            <div class="d-flex">
                                <div class="form-check me-4">
                                    <input class="form-check-input" type="radio" name="user_type" id="type_buyer" value="buyer" <?php echo $userType === 'buyer' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="type_buyer">
                                        Buyer
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="user_type" id="type_seller" value="seller" <?php echo $userType === 'seller' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="type_seller">
                                        Seller
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- First Name -->
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $firstName; ?>" required>
                            </div>
                            
                            <!-- Last Name -->
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $lastName; ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>" required>
                            </div>
                            
                            <!-- Phone -->
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $phone; ?>" placeholder="e.g. 0712345678" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Password -->
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Min. 8 characters" required>
                            </div>
                            
                            <!-- Confirm Password -->
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- County -->
                            <div class="col-md-6 mb-3">
                                <label for="county" class="form-label">County</label>
                                <select class="form-select" id="county" name="county">
                                    <option value="">Select County</option>
                                    <?php 
                                    if ($countiesResult->num_rows > 0) {
                                        while($countyRow = $countiesResult->fetch_assoc()) {
                                            $selected = $county === $countyRow['county_name'] ? 'selected' : '';
                                            echo '<option value="' . $countyRow['county_name'] . '" ' . $selected . '>' . $countyRow['county_name'] . '</option>';
                                        }
                                    } else {
                                        // If no counties in database, add a few major ones
                                        $majorCounties = ['Nairobi', 'Mombasa', 'Kisumu', 'Nakuru', 'Eldoret'];
                                        foreach ($majorCounties as $majorCounty) {
                                            $selected = $county === $majorCounty ? 'selected' : '';
                                            echo '<option value="' . $majorCounty . '" ' . $selected . '>' . $majorCounty . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <!-- Address -->
                            <div class="col-md-6 mb-3">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" name="address" value="<?php echo $address; ?>">
                            </div>
                        </div>
                        
                        <!-- Seller specific fields -->
                        <div id="seller_fields" class="<?php echo $userType === 'seller' ? '' : 'd-none'; ?>">
                            <hr>
                            <h5 class="mb-3">Seller Information</h5>
                            
                            <!-- Business Name -->
                            <div class="mb-3">
                                <label for="business_name" class="form-label">Business Name</label>
                                <input type="text" class="form-control" id="business_name" name="business_name" value="<?php echo $businessName; ?>">
                            </div>
                            
                            <!-- Business Description -->
                            <div class="mb-3">
                                <label for="business_description" class="form-label">Business Description</label>
                                <textarea class="form-control" id="business_description" name="business_description" rows="3"><?php echo $businessDescription; ?></textarea>
                            </div>
                            
                            <!-- ID Number -->
                            <div class="mb-3">
                                <label for="id_number" class="form-label">National ID / Passport Number</label>
                                <input type="text" class="form-control" id="id_number" name="id_number" value="<?php echo $idNumber; ?>">
                                <small class="text-muted">This is for verification purposes only and will not be shared.</small>
                            </div>
                            
                            <!-- ID Verification Section -->
                            <div class="mb-4 mt-4">
                                <h5 class="mb-3">ID Verification</h5>
                                <p class="text-muted mb-4">Please capture clear images of your ID card and a selfie for verification.</p>
                                
                                <!-- ID Front Image -->
                                <div class="mb-4">
                                    <label class="form-label">Front of ID Card/Passport</label>
                                    <div class="id-capture-container">
                                        <div class="text-center mb-2">
                                            <video id="idFrontVideo" class="w-100 border rounded" style="max-height: 300px; display: none;"></video>
                                            <canvas id="idFrontCanvas" class="w-100 border rounded mb-2" style="max-height: 300px; display: none;"></canvas>
                                            <img id="idFrontPreview" class="w-100 border rounded mb-2" style="max-height: 300px; display: none;">
                                        </div>
                                        <div class="d-flex justify-content-center">
                                            <button type="button" id="startIdFrontBtn" class="btn btn-outline-primary me-2">Start Camera</button>
                                            <button type="button" id="captureIdFrontBtn" class="btn btn-primary me-2" style="display: none;">Capture</button>
                                            <button type="button" id="retakeIdFrontBtn" class="btn btn-outline-secondary" style="display: none;">Retake</button>
                                        </div>
                                        <input type="hidden" name="id_front_image" id="id_front_image">
                                    </div>
                                </div>
                                
                                <!-- ID Back Image -->
                                <div class="mb-4">
                                    <label class="form-label">Back of ID Card/Passport</label>
                                    <div class="id-capture-container">
                                        <div class="text-center mb-2">
                                            <video id="idBackVideo" class="w-100 border rounded" style="max-height: 300px; display: none;"></video>
                                            <canvas id="idBackCanvas" class="w-100 border rounded mb-2" style="max-height: 300px; display: none;"></canvas>
                                            <img id="idBackPreview" class="w-100 border rounded mb-2" style="max-height: 300px; display: none;">
                                        </div>
                                        <div class="d-flex justify-content-center">
                                            <button type="button" id="startIdBackBtn" class="btn btn-outline-primary me-2">Start Camera</button>
                                            <button type="button" id="captureIdBackBtn" class="btn btn-primary me-2" style="display: none;">Capture</button>
                                            <button type="button" id="retakeIdBackBtn" class="btn btn-outline-secondary" style="display: none;">Retake</button>
                                        </div>
                                        <input type="hidden" name="id_back_image" id="id_back_image">
                                    </div>
                                </div>
                                
                                <!-- Selfie with ID -->
                                <div class="mb-4">
                                    <label class="form-label">Selfie with ID Card/Passport</label>
                                    <div class="selfie-capture-container">
                                        <div class="text-center mb-2">
                                            <video id="selfieVideo" class="w-100 border rounded" style="max-height: 300px; display: none;"></video>
                                            <canvas id="selfieCanvas" class="w-100 border rounded mb-2" style="max-height: 300px; display: none;"></canvas>
                                            <img id="selfiePreview" class="w-100 border rounded mb-2" style="max-height: 300px; display: none;">
                                        </div>
                                        <div class="d-flex justify-content-center">
                                            <button type="button" id="startSelfieBtn" class="btn btn-outline-primary me-2">Start Camera</button>
                                            <button type="button" id="captureSelfieBtn" class="btn btn-primary me-2" style="display: none;">Capture</button>
                                            <button type="button" id="retakeSelfieBtn" class="btn btn-outline-secondary" style="display: none;">Retake</button>
                                        </div>
                                        <div class="text-center mt-2">
                                            <small class="text-muted">Hold your ID card next to your face when taking the selfie.</small>
                                        </div>
                                        <input type="hidden" name="id_selfie_image" id="id_selfie_image">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="agree_terms" required>
                            <label class="form-check-label" for="agree_terms">
                                I agree to the <a href="<?php echo BASE_URL; ?>terms.php" target="_blank">Terms & Conditions</a> and <a href="<?php echo BASE_URL; ?>privacy-policy.php" target="_blank">Privacy Policy</a>
                            </label>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">Create Account</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p>Already have an account? <a href="<?php echo BASE_URL; ?>auth/login.php" class="text-primary">Login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Show/hide seller fields based on user type selection
document.addEventListener('DOMContentLoaded', function() {
    const buyerRadio = document.getElementById('type_buyer');
    const sellerRadio = document.getElementById('type_seller');
    const sellerFields = document.getElementById('seller_fields');
    const registrationForm = document.getElementById('registrationForm');
    const submitBtn = document.getElementById('submitBtn');
    
    // Elements for ID front capture
    const idFrontVideo = document.getElementById('idFrontVideo');
    const idFrontCanvas = document.getElementById('idFrontCanvas');
    const idFrontPreview = document.getElementById('idFrontPreview');
    const startIdFrontBtn = document.getElementById('startIdFrontBtn');
    const captureIdFrontBtn = document.getElementById('captureIdFrontBtn');
    const retakeIdFrontBtn = document.getElementById('retakeIdFrontBtn');
    const idFrontImageInput = document.getElementById('id_front_image');
    
    // Elements for ID back capture
    const idBackVideo = document.getElementById('idBackVideo');
    const idBackCanvas = document.getElementById('idBackCanvas');
    const idBackPreview = document.getElementById('idBackPreview');
    const startIdBackBtn = document.getElementById('startIdBackBtn');
    const captureIdBackBtn = document.getElementById('captureIdBackBtn');
    const retakeIdBackBtn = document.getElementById('retakeIdBackBtn');
    const idBackImageInput = document.getElementById('id_back_image');
    
    // Elements for selfie capture
    const selfieVideo = document.getElementById('selfieVideo');
    const selfieCanvas = document.getElementById('selfieCanvas');
    const selfiePreview = document.getElementById('selfiePreview');
    const startSelfieBtn = document.getElementById('startSelfieBtn');
    const captureSelfieBtn = document.getElementById('captureSelfieBtn');
    const retakeSelfieBtn = document.getElementById('retakeSelfieBtn');
    const selfieImageInput = document.getElementById('id_selfie_image');
    
    // Stream variables
    let idFrontStream = null;
    let idBackStream = null;
    let selfieStream = null;
    
    // Function to start camera for ID front
    startIdFrontBtn.addEventListener('click', function() {
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
            .then(function(stream) {
                idFrontStream = stream;
                idFrontVideo.srcObject = stream;
                idFrontVideo.play();
                idFrontVideo.style.display = 'block';
                startIdFrontBtn.style.display = 'none';
                captureIdFrontBtn.style.display = 'inline-block';
            })
            .catch(function(err) {
                console.error("Error accessing the camera: ", err);
                alert("Could not access the camera. Please make sure you have granted permission.");
            });
    });
    
    // Function to capture ID front
    captureIdFrontBtn.addEventListener('click', function() {
        // Set canvas dimensions to match video
        idFrontCanvas.width = idFrontVideo.videoWidth;
        idFrontCanvas.height = idFrontVideo.videoHeight;
        
        // Draw video frame to canvas
        const context = idFrontCanvas.getContext('2d');
        context.drawImage(idFrontVideo, 0, 0, idFrontCanvas.width, idFrontCanvas.height);
        
        // Convert canvas to data URL and set as preview
        const imageDataUrl = idFrontCanvas.toDataURL('image/jpeg');
        idFrontPreview.src = imageDataUrl;
        
        // Update hidden input
        idFrontImageInput.value = imageDataUrl;
        
        // Show preview and retake button, hide video and capture button
        idFrontPreview.style.display = 'block';
        retakeIdFrontBtn.style.display = 'inline-block';
        idFrontVideo.style.display = 'none';
        captureIdFrontBtn.style.display = 'none';
        
        // Stop camera stream
        if (idFrontStream) {
            idFrontStream.getTracks().forEach(track => track.stop());
        }
    });
    
    // Function to retake ID front
    retakeIdFrontBtn.addEventListener('click', function() {
        // Hide preview and retake button
        idFrontPreview.style.display = 'none';
        retakeIdFrontBtn.style.display = 'none';
        
        // Show start button
        startIdFrontBtn.style.display = 'inline-block';
        
        // Clear hidden input
        idFrontImageInput.value = '';
    });
    
    // Function to start camera for ID back
    startIdBackBtn.addEventListener('click', function() {
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
            .then(function(stream) {
                idBackStream = stream;
                idBackVideo.srcObject = stream;
                idBackVideo.play();
                idBackVideo.style.display = 'block';
                startIdBackBtn.style.display = 'none';
                captureIdBackBtn.style.display = 'inline-block';
            })
            .catch(function(err) {
                console.error("Error accessing the camera: ", err);
                alert("Could not access the camera. Please make sure you have granted permission.");
            });
    });
    
    // Function to capture ID back
    captureIdBackBtn.addEventListener('click', function() {
        // Set canvas dimensions to match video
        idBackCanvas.width = idBackVideo.videoWidth;
        idBackCanvas.height = idBackVideo.videoHeight;
        
        // Draw video frame to canvas
        const context = idBackCanvas.getContext('2d');
        context.drawImage(idBackVideo, 0, 0, idBackCanvas.width, idBackCanvas.height);
        
        // Convert canvas to data URL and set as preview
        const imageDataUrl = idBackCanvas.toDataURL('image/jpeg');
        idBackPreview.src = imageDataUrl;
        
        // Update hidden input
        idBackImageInput.value = imageDataUrl;
        
        // Show preview and retake button, hide video and capture button
        idBackPreview.style.display = 'block';
        retakeIdBackBtn.style.display = 'inline-block';
        idBackVideo.style.display = 'none';
        captureIdBackBtn.style.display = 'none';
        
        // Stop camera stream
        if (idBackStream) {
            idBackStream.getTracks().forEach(track => track.stop());
        }
    });
    
    // Function to retake ID back
    retakeIdBackBtn.addEventListener('click', function() {
        // Hide preview and retake button
        idBackPreview.style.display = 'none';
        retakeIdBackBtn.style.display = 'none';
        
        // Show start button
        startIdBackBtn.style.display = 'inline-block';
        
        // Clear hidden input
        idBackImageInput.value = '';
    });
    
    // Function to start camera for selfie
    startSelfieBtn.addEventListener('click', function() {
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } })
            .then(function(stream) {
                selfieStream = stream;
                selfieVideo.srcObject = stream;
                selfieVideo.play();
                selfieVideo.style.display = 'block';
                startSelfieBtn.style.display = 'none';
                captureSelfieBtn.style.display = 'inline-block';
            })
            .catch(function(err) {
                console.error("Error accessing the camera: ", err);
                alert("Could not access the camera. Please make sure you have granted permission.");
            });
    });
    
    // Function to capture selfie
    captureSelfieBtn.addEventListener('click', function() {
        // Set canvas dimensions to match video
        selfieCanvas.width = selfieVideo.videoWidth;
        selfieCanvas.height = selfieVideo.videoHeight;
        
        // Draw video frame to canvas
        const context = selfieCanvas.getContext('2d');
        context.drawImage(selfieVideo, 0, 0, selfieCanvas.width, selfieCanvas.height);
        
        // Convert canvas to data URL and set as preview
        const imageDataUrl = selfieCanvas.toDataURL('image/jpeg');
        selfiePreview.src = imageDataUrl;
        
        // Update hidden input
        selfieImageInput.value = imageDataUrl;
        
        // Show preview and retake button, hide video and capture button
        selfiePreview.style.display = 'block';
        retakeSelfieBtn.style.display = 'inline-block';
        selfieVideo.style.display = 'none';
        captureSelfieBtn.style.display = 'none';
        
        // Stop camera stream
        if (selfieStream) {
            selfieStream.getTracks().forEach(track => track.stop());
        }
    });
    
    // Function to retake selfie
    retakeSelfieBtn.addEventListener('click', function() {
        // Hide preview and retake button
        selfiePreview.style.display = 'none';
        retakeSelfieBtn.style.display = 'none';
        
        // Show start button
        startSelfieBtn.style.display = 'inline-block';
        
        // Clear hidden input
        selfieImageInput.value = '';
    });
    
    // Show/hide seller fields based on user type selection
    buyerRadio.addEventListener('change', function() {
        if (this.checked) {
            sellerFields.classList.add('d-none');
        }
    });
    
    sellerRadio.addEventListener('change', function() {
        if (this.checked) {
            sellerFields.classList.remove('d-none');
        }
    });
    
    // Form validation before submission
    registrationForm.addEventListener('submit', function(event) {
        // Prevent default form submission initially
        event.preventDefault();
        
        // Basic validation
        let hasError = false;
        let errorMessage = '';
        
        // Get form values
        const firstName = document.getElementById('first_name').value.trim();
        const lastName = document.getElementById('last_name').value.trim();
        const email = document.getElementById('email').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const agreeTerms = document.getElementById('agree_terms').checked;
        const userType = document.querySelector('input[name="user_type"]:checked').value;
        
        // Basic validation
        if (!firstName || !lastName || !email || !phone || !password || !confirmPassword) {
            hasError = true;
            errorMessage = 'Please fill in all required fields.';
        } else if (!validateEmail(email)) {
            hasError = true;
            errorMessage = 'Please enter a valid email address.';
        } else if (password.length < 8) {
            hasError = true;
            errorMessage = 'Password must be at least 8 characters long.';
        } else if (password !== confirmPassword) {
            hasError = true;
            errorMessage = 'Passwords do not match.';
        } else if (!agreeTerms) {
            hasError = true;
            errorMessage = 'You must agree to the Terms & Conditions and Privacy Policy.';
        }
        
        // Additional validation for sellers
        if (userType === 'seller' && !hasError) {
            const businessName = document.getElementById('business_name').value.trim();
            const idNumber = document.getElementById('id_number').value.trim();
            const idFrontImage = document.getElementById('id_front_image').value;
            const idBackImage = document.getElementById('id_back_image').value;
            const idSelfieImage = document.getElementById('id_selfie_image').value;
            
            if (!businessName || !idNumber) {
                hasError = true;
                errorMessage = 'Business name and ID number are required for sellers.';
            } else if (!idFrontImage || !idBackImage || !idSelfieImage) {
                hasError = true;
                errorMessage = 'Please capture all required ID verification images.';
            }
        }
        
        // Display error or submit form
        if (hasError) {
            // Display error message
            alert(errorMessage);
        } else {
            // Submit form if everything is valid
            this.submit();
        }
    });
    
    // Email validation function
    function validateEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }
    
    // Phone number validation (Kenyan format)
    function validatePhone(phone) {
        const re = /^(0|\+254|254)[7|1][0-9]{8}$/;
        return re.test(phone);
    }
    
    // Initialize UI based on selected user type on page load
    if (sellerRadio.checked) {
        sellerFields.classList.remove('d-none');
    } else {
        sellerFields.classList.add('d-none');
    }
    
    // Clean up function to stop all streams when leaving the page
    window.addEventListener('beforeunload', function() {
        // Stop all camera streams
        if (idFrontStream) {
            idFrontStream.getTracks().forEach(track => track.stop());
        }
        if (idBackStream) {
            idBackStream.getTracks().forEach(track => track.stop());
        }
        if (selfieStream) {
            selfieStream.getTracks().forEach(track => track.stop());
        }
    });
});
</script>