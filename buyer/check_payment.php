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

// Check if payment_id is provided
if (!isset($_GET['payment_id'])) {
    $_SESSION['error_msg'] = "Invalid payment request!";
    header("Location: orders.php");
    exit();
}

$payment_id = $_GET['payment_id'];

// Get payment details
$stmt = $conn->prepare("SELECT p.*, o.order_id, o.buyer_id, o.total_amount, o.payment_method 
                       FROM payments p 
                       JOIN orders o ON p.order_id = o.order_id 
                       WHERE p.payment_id = ?");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $payment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error_msg'] = "Payment not found!";
    header("Location: orders.php");
    exit();
}

$payment = $result->fetch_assoc();

// Verify it's the user's payment
if ($payment['buyer_id'] != $user_id) {
    $_SESSION['error_msg'] = "You don't have permission to view this payment!";
    header("Location: orders.php");
    exit();
}

/**
 * Function to check M-Pesa STK Push status
 */
function checkMpesaStatus($checkout_request_id) {
    // M-Pesa API credentials
    $consumer_key = "A515Xe5bGW2GZHc9aKGwJ5uTSYWHbP6MsoZdgvr648KV93nT"; // Replace with your actual credentials
    $consumer_secret = "kpBMbQ7NfkAajJXdD5qWS5ZDSld0AhmiL2ePVGNa9fmVGitSCHPNVf2YDI9x3cQ2";
    $shortcode = "174379"; // Business shortcode
    $passkey = "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919"; // Online passkey
    
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
    
    if (!$token_result || !isset($token_result->access_token)) {
        return array(
            'status' => 'error',
            'message' => 'Failed to get access token'
        );
    }
    
    $access_token = $token_result->access_token;
    
    // Prepare query parameters
    $timestamp = date('YmdHis');
    $password = base64_encode($shortcode . $passkey . $timestamp);
    
    // API endpoint
    $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query';
    
    // Query data
    $query_data = array(
        'BusinessShortCode' => $shortcode,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'CheckoutRequestID' => $checkout_request_id
    );
    
    // Initialize cURL
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    ));
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($query_data));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($curl);
    
    if (curl_errno($curl)) {
        $status = array(
            'status' => 'error',
            'message' => curl_error($curl)
        );
    } else {
        $result = json_decode($response, true);
        
        if (isset($result['ResultCode'])) {
            if ($result['ResultCode'] == '0') {
                $status = array(
                    'status' => 'success',
                    'transaction_id' => $result['MpesaReceiptNumber'] ?? $checkout_request_id
                );
            } else {
                $status = array(
                    'status' => 'failed',
                    'message' => $result['ResultDesc'] ?? 'Payment processing failed'
                );
            }
        } else {
            $status = array(
                'status' => 'pending',
                'message' => 'Payment status unknown'
            );
        }
    }
    
    curl_close($curl);
    return $status;
}

/**
 * Function to check PesaPal payment status
 */
function checkPesapalStatus($reference) {
    // PesaPal API credentials - REPLACE WITH YOUR ACTUAL CREDENTIALS
    $consumer_key = " 3ETXpqtFJ1TeDQmtFlyFZAAw/ozBtjtG"; 
    $consumer_secret = " mQBifgVR4iCnOKpwQ0Ln1advb1o=";
    
    // Check if credentials are set
    if ($consumer_key === " 3ETXpqtFJ1TeDQmtFlyFZAAw/ozBtjtG" || $consumer_secret === " mQBifgVR4iCnOKpwQ0Ln1advb1o=") {
        return array(
            'status' => 'PENDING',
            'message' => 'PesaPal credentials not configured'
        );
    }
    
    // Prepare OAuth parameters
    $oauth_nonce = md5(mt_rand());
    $oauth_timestamp = time();
    $oauth_signature_method = "HMAC-SHA1";
    $oauth_version = "1.0";
    
    // Base URL for PesaPal query API
    $query_url = "https://www.pesapal.com/API/QueryPaymentStatus";
    
    // Prepare signature base string
    $signature_string = "GET&".urlencode($query_url)."&".
                      urlencode("oauth_consumer_key=".$consumer_key.
                      "&oauth_nonce=".$oauth_nonce.
                      "&oauth_signature_method=".$oauth_signature_method.
                      "&oauth_timestamp=".$oauth_timestamp.
                      "&oauth_version=".$oauth_version.
                      "&pesapal_merchant_reference=".$reference);
    
    // Generate signature
    $signature = urlencode(base64_encode(hash_hmac("sha1", $signature_string, $consumer_secret."&", true)));
    
    // Build authorization header
    $auth_header = "OAuth oauth_consumer_key=\"".$consumer_key."\", ".
                 "oauth_nonce=\"".$oauth_nonce."\", ".
                 "oauth_signature=\"".$signature."\", ".
                 "oauth_signature_method=\"".$oauth_signature_method."\", ".
                 "oauth_timestamp=\"".$oauth_timestamp."\", ".
                 "oauth_version=\"".$oauth_version."\"";
    
    // Build final URL
    $final_url = $query_url."?pesapal_merchant_reference=".$reference;
    
    // Initialize cURL
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $final_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: '.$auth_header));
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    // Log the response for debugging
    error_log("PesaPal Response: " . $response);
    error_log("HTTP Code: " . $http_code);
    
    if (curl_errno($curl)) {
        $status = array(
            'status' => 'PENDING',
            'message' => curl_error($curl)
        );
    } else {
        // Check if response is XML by looking for < character
        if (strpos($response, '<') === 0) {
            // Parse XML response
            $xml = simplexml_load_string($response);
            
            if ($xml !== false) {
                $status = array(
                    'status' => (string)$xml->status,
                    'transaction_id' => (string)$xml->pesapal_transaction_tracking_id
                );
            } else {
                $status = array(
                    'status' => 'PENDING',
                    'message' => 'Unable to parse XML response'
                );
            }
        } else {
            // Handle non-XML responses (like error messages)
            if (strpos($response, 'consumer_key_unknown') !== false) {
                $status = array(
                    'status' => 'ERROR',
                    'message' => 'Invalid PesaPal consumer key'
                );
            } elseif (strpos($response, 'PENDING') !== false) {
                $status = array(
                    'status' => 'PENDING',
                    'message' => 'Payment is still pending'
                );
            } elseif (strpos($response, 'COMPLETED') !== false) {
                $status = array(
                    'status' => 'COMPLETED',
                    'message' => 'Payment completed successfully'
                );
            } elseif (strpos($response, 'FAILED') !== false) {
                $status = array(
                    'status' => 'FAILED',
                    'message' => 'Payment failed'
                );
            } else {
                $status = array(
                    'status' => 'PENDING',
                    'message' => 'Unknown response: ' . $response
                );
            }
        }
    }
    
    curl_close($curl);
    return $status;
}

$payment_status = 'pending';
$payment_message = '';

// Check payment status based on payment method
if ($payment['payment_method'] == 'mpesa') {
    // Check M-Pesa payment status
    if (!empty($payment['checkout_request_id'])) {
        $mpesa_status = checkMpesaStatus($payment['checkout_request_id']);
        
        if ($mpesa_status['status'] == 'success') {
            $payment_status = 'completed';
            $payment_message = 'Payment received successfully!';
            
            // Update payment status in database
            $update_stmt = $conn->prepare("UPDATE payments SET status = 'completed', transaction_id = ?, updated_at = NOW() WHERE payment_id = ?");
            if ($update_stmt) {
                $mpesa_transaction_id = $mpesa_status['transaction_id'];
                $update_stmt->bind_param("si", $mpesa_transaction_id, $payment_id);
                $update_stmt->execute();
                $update_stmt->close();
            }
            
            // Update order status
            $order_update = $conn->prepare("UPDATE orders SET status = 'paid' WHERE order_id = ?");
            if ($order_update) {
                $order_update->bind_param("i", $payment['order_id']);
                $order_update->execute();
                $order_update->close();
            }
            
            // Update order items status
            $items_update = $conn->prepare("UPDATE order_items SET status = 'processing' WHERE order_id = ?");
            if ($items_update) {
                $items_update->bind_param("i", $payment['order_id']);
                $items_update->execute();
                $items_update->close();
            }
        } elseif ($mpesa_status['status'] == 'failed') {
            $payment_status = 'failed';
            $payment_message = 'Payment failed: ' . $mpesa_status['message'];
            
            // Update payment status in database
            $update_stmt = $conn->prepare("UPDATE payments SET status = 'failed', notes = ?, updated_at = NOW() WHERE payment_id = ?");
            if ($update_stmt) {
                $update_stmt->bind_param("si", $mpesa_status['message'], $payment_id);
                $update_stmt->execute();
                $update_stmt->close();
            } else {
                error_log("Failed to prepare update statement: " . $conn->error);
            }
        } else {
            $payment_status = 'pending';
            $payment_message = 'Payment is still being processed. Please wait.';
        }
    }
} elseif ($payment['payment_method'] == 'pesapal') {
    // Check PesaPal payment status
    if (!empty($payment['merchant_reference'])) {
        $pesapal_status = checkPesapalStatus($payment['merchant_reference']);
        
        if ($pesapal_status['status'] == 'COMPLETED') {
            $payment_status = 'completed';
            $payment_message = 'Payment received successfully!';
            
            // Update payment status in database
            $update_stmt = $conn->prepare("UPDATE payments SET status = 'completed', transaction_id = ?, updated_at = NOW() WHERE payment_id = ?");
            if ($update_stmt) {
                $pesapal_transaction_id = $pesapal_status['transaction_id'] ?? $payment['merchant_reference'];
                $update_stmt->bind_param("si", $pesapal_transaction_id, $payment_id);
                $update_stmt->execute();
                $update_stmt->close();
            }
            
            // Update order status
            $order_update = $conn->prepare("UPDATE orders SET status = 'paid' WHERE order_id = ?");
            if ($order_update) {
                $order_update->bind_param("i", $payment['order_id']);
                $order_update->execute();
                $order_update->close();
            }
            
            // Update order items status
            $items_update = $conn->prepare("UPDATE order_items SET status = 'processing' WHERE order_id = ?");
            if ($items_update) {
                $items_update->bind_param("i", $payment['order_id']);
                $items_update->execute();
                $items_update->close();
            }
        } elseif ($pesapal_status['status'] == 'FAILED') {
            $payment_status = 'failed';
            $payment_message = 'Payment failed: ' . ($pesapal_status['message'] ?? 'Transaction failed');
            
            // Update payment status in database
            $update_stmt = $conn->prepare("UPDATE payments SET status = 'failed', notes = ?, updated_at = NOW() WHERE payment_id = ?");
            if ($update_stmt) {
                $failure_message = $pesapal_status['message'] ?? 'Transaction failed';
                $update_stmt->bind_param("si", $failure_message, $payment_id);
                $update_stmt->execute();
                $update_stmt->close();
            }
        } else {
            $payment_status = 'pending';
            $payment_message = $pesapal_status['message'] ?? 'Payment is still being processed. Please wait.';
        }
    }
}

$userId = $_SESSION['user_id'];

// Get wishlist count
$wishlistQuery = "SELECT COUNT(*) as count FROM wishlist_items WHERE user_id = ?";
$wishlistStmt = $conn->prepare($wishlistQuery);
$wishlistStmt->bind_param("i", $userId);
$wishlistStmt->execute();
$wishlistResult = $wishlistStmt->get_result();
$wishlistCount = $wishlistResult->fetch_assoc()['count'];
$wishlistStmt->close();

// Get cart count
$cartQuery = "SELECT COUNT(*) as count FROM cart_items WHERE user_id = ?";
$cartStmt = $conn->prepare($cartQuery);
$cartStmt->bind_param("i", $userId);
$cartStmt->execute();
$cartResult = $cartStmt->get_result();
$cartCount = $cartResult->fetch_assoc()['count'];
$cartStmt->close();

// Page title and header
$page_title = "Payment Status";
include_once '../includes/header.php';
?>
<div class="container py-5">
    <div class="row">
        <?php include_once 'sidebar.php'; ?>
        
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <?php if ($payment_status == 'completed'): ?>
                            <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                            <h2>Payment Successful!</h2>
                        <?php elseif ($payment_status == 'failed'): ?>
                            <i class="fas fa-times-circle text-danger fa-4x mb-3"></i>
                            <h2>Payment Failed</h2>
                        <?php else: ?>
                            <i class="fas fa-spinner fa-spin text-primary fa-4x mb-3"></i>
                            <h2>Processing Payment</h2>
                        <?php endif; ?>
                        <p class="lead"><?php echo htmlspecialchars($payment_message); ?></p>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Payment Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Order ID:</strong> #<?php echo htmlspecialchars($payment['order_id']); ?></p>
                                    <p><strong>Payment Method:</strong> <?php echo htmlspecialchars(ucfirst($payment['payment_method'])); ?></p>
                                    <p><strong>Amount:</strong> KSh <?php echo number_format($payment['amount'], 2); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Payment ID:</strong> #<?php echo htmlspecialchars($payment['payment_id']); ?></p>
                                    <p><strong>Status:</strong> 
                                        <?php if ($payment_status == 'completed'): ?>
                                            <span class="badge bg-success">Completed</span>
                                        <?php elseif ($payment_status == 'failed'): ?>
                                            <span class="badge bg-danger">Failed</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php endif; ?>
                                    </p>
                                    <p><strong>Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($payment['created_at'])); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <?php if ($payment_status == 'completed'): ?>
                            <a href="order_details.php?id=<?php echo $payment['order_id']; ?>" class="btn btn-primary">View Order Details</a>
                        <?php elseif ($payment_status == 'failed'): ?>
                            <a href="retry_payment.php?order_id=<?php echo $payment['order_id']; ?>" class="btn btn-primary">Retry Payment</a>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <p>This page will refresh automatically in 15 seconds to check for payment updates.</p>
                            </div>
                            <a href="orders.php" class="btn btn-outline-primary me-2">View All Orders</a>
                            <a href="check_payment.php?payment_id=<?php echo $payment_id; ?>" class="btn btn-primary">Refresh Status</a>
                            <script>
                                setTimeout(function() {
                                    window.location.reload();
                                }, 15000);
                            </script>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>