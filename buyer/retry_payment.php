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

$user_id = $_SESSION['user_id'];

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    $_SESSION['error_msg'] = "Invalid order request!";
    header("Location: orders.php");
    exit();
}

$order_id = $_GET['order_id'];

// Get order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND buyer_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error_msg'] = "Order not found!";
    header("Location: orders.php");
    exit();
}

$order = $result->fetch_assoc();

// Check if there's an existing payment that's already completed
$payment_check = $conn->prepare("SELECT * FROM payments WHERE order_id = ? AND status = 'completed'");
$payment_check->bind_param("i", $order_id);
$payment_check->execute();
$payment_result = $payment_check->get_result();

if ($payment_result->num_rows > 0) {
    $_SESSION['error_msg'] = "This order has already been paid for!";
    header("Location: order_details.php?id=" . $order_id);
    exit();
}

// Get user details for payment
$user_stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

// Determine which payment method to use
$payment_method = isset($_GET['method']) ? $_GET['method'] : $order['payment_method'];

// Validate payment method
if (!in_array($payment_method, ['mpesa', 'pesapal', 'cash_on_delivery'])) {
    $payment_method = 'mpesa'; // Default to M-Pesa if invalid
}

// Check if Cash on Delivery is allowed for this user
if ($payment_method == 'cash_on_delivery') {
    // Check if user has successfully completed at least 4 orders with online payment
    $cod_check_stmt = $conn->prepare("SELECT COUNT(*) as completed_orders FROM orders o 
                                     JOIN payments p ON o.order_id = p.order_id
                                     WHERE o.buyer_id = ? AND p.status = 'completed' 
                                     AND p.payment_method IN ('mpesa', 'pesapal')");
    $cod_check_stmt->bind_param("i", $user_id);
    $cod_check_stmt->execute();
    $cod_result = $cod_check_stmt->get_result();
    $cod_count = $cod_result->fetch_assoc()['completed_orders'];
    
    if ($cod_count < 4) {
        $payment_method = 'mpesa'; // Default to M-Pesa if COD not allowed
        $_SESSION['error_msg'] = "Cash on Delivery is only available for customers who have completed at least 4 successful online payments.";
    }
}

// Update order payment method if it has changed
if ($payment_method != $order['payment_method']) {
    $update_method = $conn->prepare("UPDATE orders SET payment_method = ? WHERE order_id = ?");
    $update_method->bind_param("si", $payment_method, $order_id);
    $update_method->execute();
}

// Clear previous payment attempts for this order that are not completed
$clear_payments = $conn->prepare("UPDATE payments SET status = 'cancelled' WHERE order_id = ? AND status = 'pending'");
$clear_payments->bind_param("i", $order_id);
$clear_payments->execute();

// Create new payment record
$payment_stmt = $conn->prepare("INSERT INTO payments (order_id, payment_method, amount, status) VALUES (?, ?, ?, 'pending')");
$payment_stmt->bind_param("isd", $order_id, $payment_method, $order['total_amount']);
$payment_stmt->execute();
$payment_id = $conn->insert_id;

// Process payment based on method
if ($payment_method == 'mpesa') {
    // Process M-Pesa payment
    $mpesa_response = initiateMpesaSTKPush($user['phone_number'], $order['total_amount'], $order_id, $payment_id);
    
    if (isset($mpesa_response['CheckoutRequestID'])) {
        $checkout_id = $mpesa_response['CheckoutRequestID'];
        $stmt = $conn->prepare("UPDATE payments SET checkout_request_id = ? WHERE payment_id = ?");
        $stmt->bind_param("si", $checkout_id, $payment_id);
        $stmt->execute();
    }
} elseif ($payment_method == 'pesapal') {
    // Generate PesaPal reference
    $reference = "PesaPal_" . $order_id . "_" . time();
    $stmt = $conn->prepare("UPDATE payments SET reference = ? WHERE payment_id = ?");
    $stmt->bind_param("si", $reference, $payment_id);
    $stmt->execute();
    
    // Generate PesaPal payment URL
    $pesapal_url = generatePesapalPaymentURL($order_id, $user['email'], $user['phone_number'], $order['total_amount'], $payment_id);
}

// Store payment ID in session
$_SESSION['payment_id'] = $payment_id;
$_SESSION['payment_initiated'] = true;

/**
 * Function to initiate M-Pesa STK Push
 */
function initiateMpesaSTKPush($phone, $amount, $order_id, $payment_id) {
    // Remove any country code and ensure phone number format
    $phone = preg_replace('/^(?:\+254|254|0)/', '', $phone);
    $phone = '254' . $phone;
    
    // M-Pesa API credentials
    $consumer_key = "YOUR_CONSUMER_KEY"; // Replace with your actual credentials
    $consumer_secret = "YOUR_CONSUMER_SECRET";
    $shortcode = "YOUR_SHORTCODE"; // Business shortcode
    $passkey = "YOUR_PASSKEY"; // Online passkey
    $callback_url = "https://yourwebsite.com/api/mpesa_callback.php";
    
    // Prepare STK Push parameters
    $timestamp = date('YmdHis');
    $password = base64_encode($shortcode . $passkey . $timestamp);
    $transaction_desc = "Payment for Order #" . $order_id;
    $account_reference = "Order #" . $order_id;
    
    // API endpoint - use live or sandbox URL based on environment
    $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
    
    // Get access token
    $token_url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
    $token_credentials = base64_encode($consumer_key . ':' . $consumer_secret);
    
    $token_curl = curl_init();
    curl_setopt($token_curl, CURLOPT_URL, $token_url);
    curl_setopt($token_curl, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . $token_credentials));
    curl_setopt($token_curl, CURLOPT_HEADER, false);
    curl_setopt($token_curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($token_curl, CURLOPT_SSL_VERIFYPEER, false);
    
    $token_response = curl_exec($token_curl);
    curl_close($token_curl);
    
    $token_result = json_decode($token_response);
    $access_token = $token_result->access_token;
    
    // STK Push data
    $stk_data = array(
        'BusinessShortCode' => $shortcode,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => ceil($amount), // Round up to nearest whole number
        'PartyA' => $phone,
        'PartyB' => $shortcode,
        'PhoneNumber' => $phone,
        'CallBackURL' => $callback_url,
        'AccountReference' => $account_reference,
        'TransactionDesc' => $transaction_desc,
        'Remark' => 'Payment for order'
    );
    
    // Initialize cURL
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    ));
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($stk_data));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($curl);
    
    if (curl_errno($curl)) {
        $response = array(
            'status' => 'error',
            'message' => curl_error($curl)
        );
    } else {
        $response = json_decode($response, true);
    }
    
    curl_close($curl);
    return $response;
}

/**
 * Function to generate PesaPal payment URL
 */
function generatePesapalPaymentURL($order_id, $email, $phone, $amount, $payment_id) {
    // PesaPal API credentials
    $consumer_key = "YOUR_PESAPAL_CONSUMER_KEY"; // Replace with your actual credentials
    $consumer_secret = "YOUR_PESAPAL_CONSUMER_SECRET";
    
    // Base URL for PesaPal - use sandbox or live based on environment
    $pesapal_url = "https://www.pesapal.com/API/PostPesapalDirectOrderV4";
    
    // Your callback URL
    $callback_url = "https://yourwebsite.com/buyer/pesapal_callback.php";
    
    // Prepare OAuth parameters
    $oauth_nonce = md5(mt_rand());
    $oauth_timestamp = time();
    $oauth_signature_method = "HMAC-SHA1";
    $oauth_version = "1.0";
    
    // Create XML order data
    $post_xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
                <PesapalDirectOrderInfo xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" 
                xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" 
                Amount=\"".$amount."\" 
                Description=\"Payment for Order #".$order_id."\" 
                Type=\"MERCHANT\" 
                Reference=\"".$order_id."\" 
                FirstName=\"".$email."\" 
                LastName=\"\" 
                Email=\"".$email."\" 
                PhoneNumber=\"".$phone."\" 
                xmlns=\"http://www.pesapal.com\" />";
    
    // URL encode the XML
    $post_xml = urlencode($post_xml);
    
    // Prepare signature base string
    $signature_string = "GET&".urlencode($pesapal_url)."&".
                      urlencode("oauth_callback=".urlencode($callback_url).
                      "&oauth_consumer_key=".$consumer_key.
                      "&oauth_nonce=".$oauth_nonce.
                      "&oauth_signature_method=".$oauth_signature_method.
                      "&oauth_timestamp=".$oauth_timestamp.
                      "&oauth_version=".$oauth_version.
                      "&pesapal_request_data=".$post_xml);
    
    // Generate signature
    $signature = urlencode(base64_encode(hash_hmac("sha1", $signature_string, $consumer_secret."&", true)));
    
    // Build final URL
    $iframe_src = $pesapal_url."?pesapal_request_data=".$post_xml."&oauth_signature=".$signature.
                "&oauth_signature_method=".$oauth_signature_method."&oauth_version=".$oauth_version.
                "&oauth_consumer_key=".$consumer_key."&oauth_nonce=".$oauth_nonce.
                "&oauth_timestamp=".$oauth_timestamp."&oauth_callback=".urlencode($callback_url);
    
    return $iframe_src;
}

// Redirect to appropriate page
header("Location: check_payment.php?payment_id=" . $payment_id);
exit();
?>