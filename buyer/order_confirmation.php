<?php
// Start the session
session_start();

// Include database connection
require_once '../config/db.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'buyer') {
    header("Location: ../auth/login.php");
    exit();
}

// Check if order_id is set
if (!isset($_SESSION['order_id'])) {
    header("Location: cart.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_SESSION['order_id'];

// Get order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND buyer_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // Order not found
    $_SESSION['error_msg'] = "Order not found!";
    header("Location: cart.php");
    exit();
}

$order = $result->fetch_assoc();

// Get order items
$items_stmt = $conn->prepare("SELECT oi.*, 
                             CASE 
                                 WHEN oi.item_type = 'pet' THEN p.name
                                 WHEN oi.item_type = 'product' THEN pr.name
                             END AS item_name,
                             (SELECT image_path FROM images WHERE item_type = oi.item_type AND item_id = oi.item_id AND is_primary = 1 LIMIT 1) AS primary_image,
                             (SELECT image_path FROM images WHERE item_type = oi.item_type AND item_id = oi.item_id LIMIT 1) AS fallback_image
                             FROM order_items oi
                             LEFT JOIN pets p ON oi.item_id = p.pet_id AND oi.item_type = 'pet'
                             LEFT JOIN products pr ON oi.item_id = pr.product_id AND oi.item_type = 'product'
                             WHERE oi.order_id = ?");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$order_items = $items_result->fetch_all(MYSQLI_ASSOC);

// Get user details
$user_stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

// Check if user can use cash on delivery
$can_use_cod = false;
if ($order['payment_method'] == 'cash_on_delivery') {
    // Check if user has successfully completed at least 4 orders with online payment
    $cod_check_stmt = $conn->prepare("SELECT COUNT(*) as completed_orders FROM orders o 
                                     JOIN payments p ON o.order_id = p.order_id
                                     WHERE o.buyer_id = ? AND p.status = 'completed' 
                                     AND p.payment_method IN ('mpesa', 'pesapal')");
    $cod_check_stmt->bind_param("i", $user_id);
    $cod_check_stmt->execute();
    $cod_result = $cod_check_stmt->get_result();
    $cod_count = $cod_result->fetch_assoc()['completed_orders'];
    
    $can_use_cod = ($cod_count >= 4);
    
    if (!$can_use_cod) {
        // Update order to pending payment and change payment method to mpesa
        $update_stmt = $conn->prepare("UPDATE orders SET payment_method = 'mpesa' WHERE order_id = ?");
        $update_stmt->bind_param("i", $order_id);
        $update_stmt->execute();
        $order['payment_method'] = 'mpesa';
        
        $_SESSION['error_msg'] = "Cash on Delivery is only available for customers who have completed at least 4 successful online payments. Your payment method has been changed to M-Pesa.";
    }
}

// Handle M-Pesa STK Push
$mpesa_response = null;
$payment_error = null;

if ($order['payment_method'] == 'mpesa') {
    // Check if payment was already initiated for this order
    $existing_payment_stmt = $conn->prepare("SELECT payment_id, status, checkout_request_id FROM payments WHERE order_id = ? AND payment_method = 'mpesa' ORDER BY created_at DESC LIMIT 1");
    $existing_payment_stmt->bind_param("i", $order_id);
    $existing_payment_stmt->execute();
    $existing_payment_result = $existing_payment_stmt->get_result();
    $existing_payment = $existing_payment_result->fetch_assoc();
    $existing_payment_stmt->close();
    
    // Only initiate new payment if no pending payment exists
    if (!$existing_payment || $existing_payment['status'] == 'failed') {
        
        // Validate phone number before proceeding
        if (empty($user['phone'])) {
            $payment_error = "Phone number is required for M-Pesa payment";
        } else {
            // Create new payment record
            $payment_stmt = $conn->prepare("INSERT INTO payments (order_id, payment_method, amount, status, created_at) VALUES (?, ?, ?, 'initiated', NOW())");
            $payment_stmt->bind_param("isd", $order_id, $order['payment_method'], $order['total_amount']);
            
            if ($payment_stmt->execute()) {
                $payment_id = $conn->insert_id;
                $payment_stmt->close();
                
                // Log the payment initiation
                error_log("Initiating M-Pesa payment for Order ID: $order_id, Payment ID: $payment_id, Amount: {$order['total_amount']}, Phone: {$user['phone']}");
                
                // Initialize M-Pesa STK Push
                $mpesa_response = initiateMpesaSTKPush($user['phone'], $order['total_amount'], $order_id, $payment_id);
                
                // Handle the response
                if ($mpesa_response && isset($mpesa_response['status'])) {
                    if ($mpesa_response['status'] == 'success') {
                        // STK Push was sent successfully
                        $_SESSION['payment_initiated'] = true;
                        $_SESSION['payment_id'] = $payment_id;
                        $_SESSION['checkout_request_id'] = $mpesa_response['CheckoutRequestID'] ?? null;
                        
                        // Update order status to indicate payment is being processed
                        $order_update_stmt = $conn->prepare("UPDATE orders SET status = 'payment_pending' WHERE order_id = ?");
                        $order_update_stmt->bind_param("i", $order_id);
                        $order_update_stmt->execute();
                        $order_update_stmt->close();
                        
                        $success_message = "M-Pesa payment request sent to your phone. Please complete the payment on your mobile device.";
                        
                    } else {
                        // STK Push failed
                        $payment_error = $mpesa_response['message'] ?? 'M-Pesa payment initiation failed';
                        
                        // Update payment status to failed
                        $fail_stmt = $conn->prepare("UPDATE payments SET status = 'failed', notes = ? WHERE payment_id = ?");
                        $fail_stmt->bind_param("si", $payment_error, $payment_id);
                        $fail_stmt->execute();
                        $fail_stmt->close();
                        
                        error_log("M-Pesa STK Push failed for Payment ID: $payment_id - " . $payment_error);
                    }
                } else {
                    // Invalid response from STK Push function
                    $payment_error = "Invalid response from M-Pesa service";
                    
                    $fail_stmt = $conn->prepare("UPDATE payments SET status = 'failed', notes = ? WHERE payment_id = ?");
                    $fail_stmt->bind_param("si", $payment_error, $payment_id);
                    $fail_stmt->execute();
                    $fail_stmt->close();
                    
                    error_log("Invalid M-Pesa response for Payment ID: $payment_id");
                }
                
            } else {
                $payment_error = "Failed to create payment record: " . $conn->error;
                error_log("Failed to create payment record for Order ID: $order_id - " . $conn->error);
                $payment_stmt->close();
            }
        }
        
    } else {
        // Payment already exists
        if ($existing_payment['status'] == 'pending') {
            $_SESSION['payment_initiated'] = true;
            $_SESSION['payment_id'] = $existing_payment['payment_id'];
            $_SESSION['checkout_request_id'] = $existing_payment['checkout_request_id'];
            $success_message = "M-Pesa payment is already in progress. Please complete the payment on your mobile device.";
        } elseif ($existing_payment['status'] == 'completed') {
            $success_message = "Payment has already been completed for this order.";
        }
    }
}

// Display messages to user
if (isset($success_message)) {
    echo '<div class="alert alert-success">' . htmlspecialchars($success_message) . '</div>';
}

if ($payment_error) {
    echo '<div class="alert alert-danger">Payment Error: ' . htmlspecialchars($payment_error) . '</div>';
}

// Debug information (remove in production)
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    echo '<div class="alert alert-info">';
    echo '<h5>Debug Information:</h5>';
    echo '<p><strong>Order ID:</strong> ' . $order_id . '</p>';
    echo '<p><strong>Payment Method:</strong> ' . $order['payment_method'] . '</p>';
    echo '<p><strong>Amount:</strong> ' . $order['total_amount'] . '</p>';
    echo '<p><strong>Phone:</strong> ' . ($user['phone'] ?? 'Not set') . '</p>';
    echo '<p><strong>Payment Initiated:</strong> ' . (isset($_SESSION['payment_initiated']) ? 'Yes' : 'No') . '</p>';
    if ($mpesa_response) {
        echo '<p><strong>M-Pesa Response:</strong> ' . htmlspecialchars(json_encode($mpesa_response)) . '</p>';
    }
    echo '</div>';
}


// Improved Handle PesaPal Integration with proper error checking
$pesapal_url = null;
$pesapal_error = null;

if ($order['payment_method'] == 'pesapal') {
    // Check if payment was already initiated for this order
    $existing_payment_stmt = $conn->prepare("SELECT payment_id, status, reference FROM payments WHERE order_id = ? AND payment_method = 'pesapal' ORDER BY created_at DESC LIMIT 1");
    
    if (!$existing_payment_stmt) {
        error_log("Prepare failed for existing payment query: " . $conn->error);
        $pesapal_error = "Database error occurred";
    } else {
        $existing_payment_stmt->bind_param("i", $order_id);
        $existing_payment_stmt->execute();
        $existing_payment_result = $existing_payment_stmt->get_result();
        $existing_payment = $existing_payment_result->fetch_assoc();
        $existing_payment_stmt->close();
        
        // Only initiate new payment if no pending payment exists
        if (!$existing_payment || $existing_payment['status'] == 'failed') {
            
            // Validate required fields
            if (empty($user['email'])) {
                $pesapal_error = "Email address is required for PesaPal payment";
            } elseif (empty($user['phone'])) {
                $pesapal_error = "Phone number is required for PesaPal payment";
            } else {
                // Create new payment record
                $payment_stmt = $conn->prepare("INSERT INTO payments (order_id, payment_method, amount, status, created_at) VALUES (?, ?, ?, 'initiated', NOW())");
                
                if (!$payment_stmt) {
                    error_log("Prepare failed for payment insert: " . $conn->error);
                    $pesapal_error = "Database error occurred";
                } else {
                    $payment_stmt->bind_param("isd", $order_id, $order['payment_method'], $order['total_amount']);
                    
                    if ($payment_stmt->execute()) {
                        $payment_id = $conn->insert_id;
                        $payment_stmt->close();
                        
                        // Log the payment initiation
                        error_log("Initiating PesaPal payment for Order ID: $order_id, Payment ID: $payment_id, Amount: {$order['total_amount']}, Email: {$user['email']}");
                        
                        // Generate PesaPal payment URL
                        $pesapal_url = generatePesapalPaymentURL($order_id, $user['email'], $user['phone'], $order['total_amount'], $payment_id);
                        
                        if ($pesapal_url) {
                            // Update order status to indicate payment is being processed
                            $order_update_stmt = $conn->prepare("UPDATE orders SET status = 'payment_pending' WHERE order_id = ?");
                            
                            if (!$order_update_stmt) {
                                error_log("Prepare failed for order update: " . $conn->error);
                            } else {
                                $order_update_stmt->bind_param("i", $order_id);
                                $order_update_stmt->execute();
                                $order_update_stmt->close();
                            }
                            
                            $_SESSION['payment_initiated'] = true;
                            $_SESSION['payment_id'] = $payment_id;
                            
                            $success_message = "PesaPal payment link generated successfully. Click the button below to proceed with payment.";
                        } else {
                            $pesapal_error = "Failed to generate PesaPal payment link";
                            
                            // Update payment status to failed - FIXED VERSION
                            $fail_stmt = $conn->prepare("UPDATE payments SET status = 'failed' WHERE payment_id = ?");
                            
                            if (!$fail_stmt) {
                                error_log("Prepare failed for payment status update: " . $conn->error);
                            } else {
                                $fail_stmt->bind_param("i", $payment_id);
                                $fail_stmt->execute();
                                $fail_stmt->close();
                            }
                            
                            error_log("PesaPal payment URL generation failed for Payment ID: $payment_id");
                        }
                    } else {
                        $pesapal_error = "Failed to create payment record: " . $conn->error;
                        error_log("Failed to create payment record for Order ID: $order_id - " . $conn->error);
                        $payment_stmt->close();
                    }
                }
            }
        } else {
            // Payment already exists
            if ($existing_payment['status'] == 'pending') {
                $_SESSION['payment_initiated'] = true;
                $_SESSION['payment_id'] = $existing_payment['payment_id'];
                
                // Try to regenerate payment URL if reference exists
                if (!empty($existing_payment['reference'])) {
                    // Check transaction status first
                    $status_check = checkPesapalTransactionStatus($existing_payment['reference']);
                    if ($status_check && isset($status_check['payment_status_description'])) {
                        if ($status_check['payment_status_description'] == 'Completed') {
                            $success_message = "Payment has already been completed for this order.";
                        } else {
                            $pesapal_url = "https://cybqa.pesapal.com/pesapalv3/api/Transactions/SubmitOrderRequest?OrderTrackingId=" . $existing_payment['reference'];
                            $success_message = "PesaPal payment is already in progress. Click the button below to continue.";
                        }
                    } else {
                        // Regenerate payment URL
                        $pesapal_url = generatePesapalPaymentURL($order_id, $user['email'], $user['phone'], $order['total_amount'], $existing_payment['payment_id']);
                        $success_message = "PesaPal payment link regenerated. Click the button below to proceed.";
                    }
                } else {
                    $pesapal_url = generatePesapalPaymentURL($order_id, $user['email'], $user['phone'], $order['total_amount'], $existing_payment['payment_id']);
                    $success_message = "PesaPal payment link generated. Click the button below to proceed.";
                }
            } elseif ($existing_payment['status'] == 'completed') {
                $success_message = "Payment has already been completed for this order.";
            }
        }
    }
}

// Display PesaPal error messages
if ($pesapal_error) {
    echo '<div class="alert alert-danger">PesaPal Error: ' . htmlspecialchars($pesapal_error) . '</div>';
}


// Handle Cash on Delivery
if ($order['payment_method'] == 'cash_on_delivery' && $can_use_cod && !isset($_SESSION['payment_initiated'])) {
    // Create payment record
    $payment_stmt = $conn->prepare("INSERT INTO payments (order_id, payment_method, amount, status) VALUES (?, ?, ?, 'pending')");
    $payment_stmt->bind_param("isd", $order_id, $order['payment_method'], $order['total_amount']);
    $payment_stmt->execute();
    $payment_id = $conn->insert_id;
    
    $_SESSION['payment_initiated'] = true;
    $_SESSION['payment_id'] = $payment_id;
}

/**
 * Simplified M-Pesa STK Push function with improved approach
 */
function initiateMpesaSTKPush($phone, $amount, $order_id, $payment_id) {
    global $conn; // Ensure global connection is accessible
    
    // Set timezone
    date_default_timezone_set('Africa/Nairobi');
    
    // Create log files if they don't exist
    if (!file_exists("mpesa_stk_log.txt")) {
        touch("mpesa_stk_log.txt");
        chmod("mpesa_stk_log.txt", 0666);
    }
    
    // Start logging
    $log_file = fopen("mpesa_stk_log.txt", "a");
    fwrite($log_file, "\n\n" . date("Y-m-d H:i:s") . " - INITIATING STK PUSH\n");
    fwrite($log_file, "Phone: $phone, Amount: $amount, Order ID: $order_id, Payment ID: $payment_id\n");
    
    // Validate inputs
    if (empty($phone) || empty($amount) || empty($order_id) || empty($payment_id)) {
        fwrite($log_file, "ERROR: Missing required parameters\n");
        fclose($log_file);
        return [
            'status' => 'error',
            'message' => 'Missing required parameters'
        ];
    }
    
    // Format phone number correctly
    $phone = preg_replace('/^(?:\+254|254|0)/', '', $phone);
    $phone = preg_replace('/[^0-9]/', '', $phone);
    $phone = '254' . $phone;
    fwrite($log_file, "Formatted Phone: $phone\n");
    
    // M-Pesa API credentials
    $consumer_key = "A515Xe5bGW2GZHc9aKGwJ5uTSYWHbP6MsoZdgvr648KV93nT"; 
    $consumer_secret = "kpBMbQ7NfkAajJXdD5qWS5ZDSld0AhmiL2ePVGNa9fmVGitSCHPNVf2YDI9x3cQ2";
    $BussinessShortCode = "174379";
    $passkey = "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919";
    
    // Callback URL - update with your actual ngrok URL
    $callbackurl = "https://d523-41-139-186-177.ngrok-free.app/JamboPets/buyer/mpesa_callback.php";
    
    // Prepare STK Push parameters
    $Timestamp = date('YmdHis');
    $Password = base64_encode($BussinessShortCode . $passkey . $Timestamp);
    
    // Ensure amount is valid (minimum 1 KES)
    $amount = max(1, ceil($amount));
    
    // API endpoints
    $token_url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
    $processrequestUrl = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
    
    fwrite($log_file, "Getting access token...\n");
    
    // Get access token
    $token_curl = curl_init();
    curl_setopt($token_curl, CURLOPT_URL, $token_url);
    curl_setopt($token_curl, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . base64_encode($consumer_key . ':' . $consumer_secret)));
    curl_setopt($token_curl, CURLOPT_HEADER, false);
    curl_setopt($token_curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($token_curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($token_curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($token_curl, CURLOPT_TIMEOUT, 10);
    
    $token_response = curl_exec($token_curl);
    
    if (curl_errno($token_curl)) {
        fwrite($log_file, "TOKEN ERROR: " . curl_error($token_curl) . "\n");
        curl_close($token_curl);
        fclose($log_file);
        return [
            'status' => 'error',
            'message' => 'Failed to get access token: ' . curl_error($token_curl)
        ];
    }
    
    curl_close($token_curl);
    
    $token_result = json_decode($token_response);
    
    if (!isset($token_result->access_token)) {
        fwrite($log_file, "ERROR: Access token not received. Response: " . $token_response . "\n");
        fclose($log_file);
        return [
            'status' => 'error',
            'message' => 'Failed to get access token from response'
        ];
    }
    
    $access_token = $token_result->access_token;
    fwrite($log_file, "Access token obtained successfully\n");
    
    // Prepare STK Push data (simplified approach)
    $curl_post_data = array(
        "BusinessShortCode" => $BussinessShortCode,    
        "Password" => $Password,    
        "Timestamp" => $Timestamp,    
        "TransactionType" => "CustomerPayBillOnline",    
        "Amount" => $amount,    
        "PartyA" => $phone,    
        "PartyB" => $BussinessShortCode,    
        "PhoneNumber" => $phone,    
        "CallBackURL" => $callbackurl,    
        "AccountReference" => "Order#" . $order_id,    
        "TransactionDesc" => "Payment for Order #" . $order_id,
    );
    
    fwrite($log_file, "STK Push Data: " . json_encode($curl_post_data, JSON_PRETTY_PRINT) . "\n");
    
    // Set headers
    $stkpushheader = [
        'Content-Type:application/json', 
        'Authorization:Bearer ' . $access_token
    ];
    
    // Initialize cURL for STK push
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $processrequestUrl);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $stkpushheader);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($curl_post_data));
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    
    $curl_response = curl_exec($curl);
    
    if (curl_errno($curl)) {
        fwrite($log_file, "STK PUSH ERROR: " . curl_error($curl) . "\n");
        curl_close($curl);
        fclose($log_file);
        return [
            'status' => 'error',
            'message' => 'STK Push request failed: ' . curl_error($curl)
        ];
    }
    
    curl_close($curl);
    
    fwrite($log_file, "STK Push Response: " . $curl_response . "\n");
    
    // Process the response
    $data = json_decode($curl_response);
    
    if (!$data) {
        fwrite($log_file, "ERROR: Invalid JSON response\n");
        fclose($log_file);
        return [
            'status' => 'error',
            'message' => 'Invalid response from M-Pesa API'
        ];
    }
    
    // Check response code
    $ResponseCode = isset($data->ResponseCode) ? $data->ResponseCode : null;
    $CheckoutRequestID = isset($data->CheckoutRequestID) ? $data->CheckoutRequestID : null;
    
    if ($ResponseCode == '0' && $CheckoutRequestID) {
        // Success - update payment record
        fwrite($log_file, "STK Push successful. CheckoutRequestID: " . $CheckoutRequestID . "\n");
        
        $stmt = $conn->prepare("UPDATE payments SET checkout_request_id = ?, status = 'pending' WHERE payment_id = ?");
        if ($stmt) {
            $stmt->bind_param("si", $CheckoutRequestID, $payment_id);
            $result = $stmt->execute();
            
            if ($result) {
                fwrite($log_file, "Payment record updated successfully\n");
            } else {
                fwrite($log_file, "ERROR updating payment record: " . $conn->error . "\n");
            }
            $stmt->close();
        }
        
        fclose($log_file);
        
        return [
            'status' => 'success',
            'message' => 'STK Push sent successfully',
            'CheckoutRequestID' => $CheckoutRequestID,
            'ResponseCode' => $ResponseCode
        ];
        
    } else {
        // Error handling
        $error_message = isset($data->errorMessage) ? $data->errorMessage : 'Unknown error';
        $error_code = isset($data->errorCode) ? $data->errorCode : 'Unknown';
        
        fwrite($log_file, "STK Push failed. Error Code: $error_code, Error Message: $error_message\n");
        
        // Update payment status to failed
        $stmt = $conn->prepare("UPDATE payments SET status = 'failed', notes = ? WHERE payment_id = ?");
        if ($stmt) {
            $error_note = "STK Push failed: $error_message (Code: $error_code)";
            $stmt->bind_param("si", $error_note, $payment_id);
            $stmt->execute();
            $stmt->close();
        }
        
        fclose($log_file);
        
        return [
            'status' => 'error',
            'message' => $error_message,
            'error_code' => $error_code,
            'ResponseCode' => $ResponseCode
        ];
    }
}
 
/**
 * Updated PesaPal Integration using API v3
 * Replace the old generatePesapalPaymentURL function with these functions
 */

/**
 * Get PesaPal Access Token
 */
/**
 * Improved PesaPal Access Token function with caching
 */
function getPesapalAccessToken() {
    // Check if we have a cached token that's still valid
    $cache_file = 'pesapal_token_cache.json';
    if (file_exists($cache_file)) {
        $cache_data = json_decode(file_get_contents($cache_file), true);
        if ($cache_data && isset($cache_data['token']) && isset($cache_data['expires_at'])) {
            if (time() < $cache_data['expires_at']) {
                return $cache_data['token'];
            }
        }
    }
    
    // PesaPal API v3 credentials
    $consumer_key = "3ETXpqtFJ1TeDQmtFlyFZAAw/ozBtjtG";
    $consumer_secret = "mQBifgVR4iCnOKpwQ0Ln1advb1o=";
    
    // Use sandbox URL for testing, change to live URL for production
    $auth_url = "https://cybqa.pesapal.com/pesapalv3/api/Auth/RequestToken";
    
    $data = [
        'consumer_key' => $consumer_key,
        'consumer_secret' => $consumer_secret
    ];
    
    // Create log file for PesaPal
    if (!file_exists("pesapal_log.txt")) {
        touch("pesapal_log.txt");
        chmod("pesapal_log.txt", 0666);
    }
    
    $log_file = fopen("pesapal_log.txt", "a");
    fwrite($log_file, "\n" . date("Y-m-d H:i:s") . " - Requesting PesaPal Access Token\n");
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $auth_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    if (curl_errno($curl)) {
        fwrite($log_file, "CURL Error: " . curl_error($curl) . "\n");
        curl_close($curl);
        fclose($log_file);
        return null;
    }
    
    curl_close($curl);
    
    fwrite($log_file, "Response Code: $http_code\n");
    fwrite($log_file, "Response: $response\n");
    
    if ($http_code == 200) {
        $result = json_decode($response, true);
        if (isset($result['token'])) {
            // Cache the token (PesaPal tokens typically last 5 minutes)
            $cache_data = [
                'token' => $result['token'],
                'expires_at' => time() + 240 // Cache for 4 minutes to be safe
            ];
            file_put_contents($cache_file, json_encode($cache_data));
            
            fwrite($log_file, "Access token obtained and cached successfully\n");
            fclose($log_file);
            return $result['token'];
        }
    }
    
    fwrite($log_file, "Failed to get access token\n");
    fclose($log_file);
    error_log("PesaPal Auth Error: " . $response);
    return null;
}
/**
 * Improved IPN Registration with caching
 */
function registerPesapalIPN($access_token) {
    // Check if we have a cached IPN ID
    $cache_file = 'pesapal_ipn_cache.json';
    if (file_exists($cache_file)) {
        $cache_data = json_decode(file_get_contents($cache_file), true);
        if ($cache_data && isset($cache_data['ipn_id'])) {
            return $cache_data['ipn_id'];
        }
    }
    
    $ipn_url = "https://cybqa.pesapal.com/pesapalv3/api/URLSetup/RegisterIPN";
    
    // Your callback URL - update with your actual domain
    $notification_url = "https://d523-41-139-186-177.ngrok-free.app/JamboPets/buyer/pesapal_callback.php";
    
    $data = [
        'url' => $notification_url,
        'ipn_notification_type' => 'GET'
    ];
    
    $log_file = fopen("pesapal_log.txt", "a");
    fwrite($log_file, date("Y-m-d H:i:s") . " - Registering IPN URL: $notification_url\n");
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $ipn_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $access_token
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    if (curl_errno($curl)) {
        fwrite($log_file, "IPN Registration CURL Error: " . curl_error($curl) . "\n");
        curl_close($curl);
        fclose($log_file);
        return null;
    }
    
    curl_close($curl);
    
    fwrite($log_file, "IPN Response Code: $http_code\n");
    fwrite($log_file, "IPN Response: $response\n");
    
    if ($http_code == 200) {
        $result = json_decode($response, true);
        if (isset($result['ipn_id'])) {
            // Cache the IPN ID
            $cache_data = ['ipn_id' => $result['ipn_id']];
            file_put_contents($cache_file, json_encode($cache_data));
            
            fwrite($log_file, "IPN registered successfully: " . $result['ipn_id'] . "\n");
            fclose($log_file);
            return $result['ipn_id'];
        }
    }
    
    fwrite($log_file, "Failed to register IPN\n");
    fclose($log_file);
    error_log("PesaPal IPN Registration Error: " . $response);
    return null;
}
/**
 * Improved PesaPal Payment URL Generation
 */
function generatePesapalPaymentURL($order_id, $email, $phone, $amount, $payment_id) {
    global $conn;
    
    $log_file = fopen("pesapal_log.txt", "a");
    fwrite($log_file, "\n" . date("Y-m-d H:i:s") . " - Generating Payment URL for Order: $order_id\n");
    
    // Get access token
    $access_token = getPesapalAccessToken();
    if (!$access_token) {
        fwrite($log_file, "Failed to get access token\n");
        fclose($log_file);
        return null;
    }
    
    // Register IPN
    $ipn_id = registerPesapalIPN($access_token);
    if (!$ipn_id) {
        fwrite($log_file, "Failed to register IPN\n");
        fclose($log_file);
        return null;
    }
    
    // Submit order URL
    $submit_url = "https://cybqa.pesapal.com/pesapalv3/api/Transactions/SubmitOrderRequest";
    
    // Format phone number properly
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (substr($phone, 0, 1) == '0') {
        $phone = '254' . substr($phone, 1);
    } elseif (substr($phone, 0, 3) != '254') {
        $phone = '254' . $phone;
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        fwrite($log_file, "Invalid email format: $email\n");
        fclose($log_file);
        return null;
    }
    
    // Ensure amount is valid
    $amount = max(1, ceil($amount));
    
    // Extract name from email for billing address
    $name_parts = explode('@', $email);
    $first_name = ucfirst($name_parts[0]);
    
    // Prepare order data
    $order_data = [
        'id' => (string)$order_id,
        'currency' => 'KES',
        'amount' => (float)$amount,
        'description' => 'JamboPets Order #' . $order_id,
        'callback_url' => "https://d523-41-139-186-177.ngrok-free.app/JamboPets/buyer/pesapal_return.php?order_id=" . $order_id . "&payment_id=" . $payment_id,
        'notification_id' => $ipn_id,
        'billing_address' => [
            'email_address' => $email,
            'phone_number' => $phone,
            'country_code' => 'KE',
            'first_name' => $first_name,
            'middle_name' => '',
            'last_name' => 'Customer',
            'line_1' => 'Nairobi',
            'line_2' => '',
            'city' => 'Nairobi',
            'state' => 'Nairobi',
            'postal_code' => '00100',
            'zip_code' => '00100'
        ]
    ];
    
    fwrite($log_file, "Order Data: " . json_encode($order_data, JSON_PRETTY_PRINT) . "\n");
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $submit_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($order_data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $access_token
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    if (curl_errno($curl)) {
        fwrite($log_file, "Submit Order CURL Error: " . curl_error($curl) . "\n");
        curl_close($curl);
        fclose($log_file);
        return null;
    }
    
    curl_close($curl);
    
    fwrite($log_file, "Submit Order Response Code: $http_code\n");
    fwrite($log_file, "Submit Order Response: $response\n");
    
    if ($http_code == 200) {
        $result = json_decode($response, true);
        
        if (isset($result['redirect_url']) && isset($result['order_tracking_id'])) {
            // Update payment record with tracking ID
            $tracking_id = $result['order_tracking_id'];
            $stmt = $conn->prepare("UPDATE payments SET reference = ?, checkout_request_id = ?, status = 'pending' WHERE payment_id = ?");
            $stmt->bind_param("ssi", $tracking_id, $tracking_id, $payment_id);
            
            if ($stmt->execute()) {
                fwrite($log_file, "Payment record updated with tracking ID: $tracking_id\n");
            } else {
                fwrite($log_file, "Failed to update payment record: " . $conn->error . "\n");
            }
            $stmt->close();
            
            fclose($log_file);
            return $result['redirect_url'];
        } else {
            fwrite($log_file, "Missing redirect_url or order_tracking_id in response\n");
        }
    }
    
    fclose($log_file);
    error_log("PesaPal Submit Order Error: " . $response);
    return null;
}


/**
 * Improved PesaPal Transaction Status Check
 */
function checkPesapalTransactionStatus($order_tracking_id) {
    $access_token = getPesapalAccessToken();
    if (!$access_token) {
        return null;
    }
    
    $status_url = "https://cybqa.pesapal.com/pesapalv3/api/Transactions/GetTransactionStatus?orderTrackingId=" . $order_tracking_id;
    
    $log_file = fopen("pesapal_log.txt", "a");
    fwrite($log_file, date("Y-m-d H:i:s") . " - Checking transaction status for: $order_tracking_id\n");
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $status_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $access_token
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    if (curl_errno($curl)) {
        fwrite($log_file, "Status Check CURL Error: " . curl_error($curl) . "\n");
        curl_close($curl);
        fclose($log_file);
        return null;
    }
    
    curl_close($curl);
    
    fwrite($log_file, "Status Check Response Code: $http_code\n");
    fwrite($log_file, "Status Check Response: $response\n");
    fclose($log_file);
    
    if ($http_code == 200) {
        return json_decode($response, true);
    }
    
    return null;
}

$userId = $_SESSION['user_id'];

// Get wishlist count
$wishlistQuery = "SELECT COUNT(*) as count FROM wishlist_items WHERE user_id = $userId";
$wishlistResult = $conn->query($wishlistQuery);
$wishlistCount = $wishlistResult->fetch_assoc()['count'];

// Get cart count
$cartQuery = "SELECT COUNT(*) as count FROM cart_items WHERE user_id = $userId";
$cartResult = $conn->query($cartQuery);
$cartCount = $cartResult->fetch_assoc()['count'];

// Page title and header
$page_title = "Order Confirmation";
include_once '../includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <?php include_once 'sidebar.php'; ?>
        
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                        <h2>Thank you for your order!</h2>
                        <p class="lead">Your order #<?php echo $order_id; ?> has been placed successfully.</p>
                    </div>
                    
                    <?php if (isset($_SESSION['error_msg'])): ?>
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <?php 
                                echo $_SESSION['error_msg']; 
                                unset($_SESSION['error_msg']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Order Details</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Order ID:</strong> #<?php echo $order_id; ?></p>
                                    <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($order['order_date'])); ?></p>
                                    <p><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
                                    <p><strong>Status:</strong> <span class="badge bg-warning text-dark">Pending Payment</span></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Customer Information</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
                                    <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address'] ?? 'Not provided'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Order Summary</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order_items as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php 
                                                    // Use primary image if available, fallback image as second choice
                                                    $image_path = !empty($item['primary_image']) ? $item['primary_image'] : 
                                                                (!empty($item['fallback_image']) ? $item['fallback_image'] : '');
                                                    
                                                    if (!empty($image_path)): 
                                                    ?>
                                                        <img src="../<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($item['item_name']); ?>" class="img-thumbnail me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="bg-light d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                                            <span class="text-muted">No image</span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <h6 class="mb-0"><?php echo htmlspecialchars($item['item_name']); ?></h6>
                                                        <small class="text-muted"><?php echo ucfirst($item['item_type']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>KSh <?php echo number_format($item['price_per_unit'], 2); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td class="text-end">KSh <?php echo number_format($item['subtotal'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                        <td class="text-end">KSh <?php echo number_format($order['total_amount'] - 150, 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Shipping:</strong></td>
                                        <td class="text-end">KSh 150.00</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                        <td class="text-end"><strong>KSh <?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Payment Section -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Payment Information</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($order['payment_method'] == 'mpesa'): ?>
                                <div class="text-center py-3">
                                    <h4>M-Pesa Payment</h4>
                                    <?php if (isset($mpesa_response) && isset($mpesa_response['ResponseCode']) && $mpesa_response['ResponseCode'] == "0"): ?>
                                        <div class="alert alert-info">
                                            <p>An M-Pesa payment request has been sent to your phone number.</p>
                                            <p>Please enter your M-Pesa PIN when prompted to complete the payment.</p>
                                        </div>
                                        <div class="mt-3">
                                            <p><i class="fas fa-spinner fa-spin me-2"></i> Waiting for payment confirmation...</p>
                                            <small class="text-muted">This page will automatically refresh in 30 seconds to check the payment status.</small>
                                        </div>
                                        <script>
                                            setTimeout(function() {
                                                window.location.href = "check_payment.php?payment_id=<?php echo $_SESSION['payment_id']; ?>";
                                            }, 30000);
                                        </script>
                                    <?php elseif (isset($mpesa_response) && isset($mpesa_response['status']) && $mpesa_response['status'] == 'error'): ?>
                                        <div class="alert alert-danger">
                                            <p>There was an error initiating the M-Pesa payment: <?php echo $mpesa_response['message']; ?></p>
                                            <p>Please try again or choose a different payment method.</p>
                                        </div>
                                        <div class="mt-3">
                                            <a href="retry_payment.php?order_id=<?php echo $order_id; ?>" class="btn btn-primary">Retry Payment</a>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <p>Please check your phone for the M-Pesa STK push notification.</p>
                                            <p>Enter your M-Pesa PIN to complete the payment of KSh <?php echo number_format($order['total_amount'], 2); ?>.</p>
                                        </div>
                                        <div class="mt-3">
                                            <p><i class="fas fa-spinner fa-spin me-2"></i> Waiting for payment confirmation...</p>
                                            <small class="text-muted">This page will automatically refresh in 30 seconds to check the payment status.</small>
                                        </div>
                                        <script>
                                            setTimeout(function() {
                                                window.location.href = "check_payment.php?payment_id=<?php echo $_SESSION['payment_id']; ?>";
                                            }, 30000);
                                        </script>
                                    <?php endif; ?>
                                </div>
                                
                            <?php elseif ($order['payment_method'] == 'pesapal'): ?>
                                <div class="text-center py-3">
                                    <h4>PesaPal Payment</h4>
                                    <?php if (isset($pesapal_url)): ?>
                                        <div class="alert alert-info mb-4">
                                            <p>You will be redirected to PesaPal to complete your payment.</p>
                                        </div>
                                        <div class="d-grid gap-2 col-6 mx-auto">
                                            <a href="<?php echo $pesapal_url; ?>" class="btn btn-primary" target="_blank">Pay with PesaPal</a>
                                        </div>
                                        <div class="mt-3">
                                            <p class="text-muted">Once payment is complete, you will be redirected back to our website.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <p>Please use the link sent to your email to complete the payment.</p>
                                            <p>Or click the button below to retry the payment process.</p>
                                        </div>
                                        <div class="mt-3">
                                            <a href="retry_payment.php?order_id=<?php echo $order_id; ?>" class="btn btn-primary">Retry Payment</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                            <?php elseif ($order['payment_method'] == 'cash_on_delivery'): ?>
                                <?php if ($can_use_cod): ?>
                                    <div class="text-center py-3">
                                        <h4>Cash on Delivery</h4>
                                        <div class="alert alert-success">
                                            <p>Your Cash on Delivery order has been confirmed.</p>
                                            <p>You will be contacted shortly to confirm the delivery details.</p>
                                        </div>
                                        <p>Please have the exact amount of KSh <?php echo number_format($order['total_amount'], 2); ?> ready at the time of delivery.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning">
                                        <p>Cash on Delivery is only available for customers who have completed at least 4 successful online payments.</p>
                                        <p>Please use M-Pesa to complete this order.</p>
                                    </div>
                                    <div class="mt-3 text-center">
                                        <a href="retry_payment.php?order_id=<?php echo $order_id; ?>&method=mpesa" class="btn btn-primary">Pay with M-Pesa</a>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="orders.php" class="btn btn-outline-primary me-2">View All Orders</a>
                        <a href="browse.php" class="btn btn-primary">Continue Shopping</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// Clean up the session variables that are no longer needed
unset($_SESSION['order_id']);
include_once '../includes/footer.php'; 
?>