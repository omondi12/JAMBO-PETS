<?php
/**
 * PesaPal Return Handler (pesapal_return.php)
 * This file handles when users are redirected back from PesaPal after payment
 */

session_start();
require_once '../config/db.php';

// Get parameters from URL
$order_tracking_id = $_GET['OrderTrackingId'] ?? null;
$order_merchant_reference = $_GET['OrderMerchantReference'] ?? null;

if (!$order_tracking_id) {
    $_SESSION['error_msg'] = "Invalid payment return. Please contact support.";
    header("Location: orders.php");
    exit();
}

// Log the return for debugging
$log_file = "pesapal_return_log.txt";
file_put_contents($log_file, "\n\n" . date("Y-m-d H:i:s") . " - PesaPal Return\n", FILE_APPEND);
file_put_contents($log_file, "Tracking ID: $order_tracking_id\n", FILE_APPEND);
file_put_contents($log_file, "Merchant Ref: $order_merchant_reference\n", FILE_APPEND);

// Include PesaPal functions
include_once 'pesapal_functions.php';

// Check payment status
$status_response = checkPesapalTransactionStatus($order_tracking_id);

if ($status_response) {
    $payment_status = $status_response['status_code'] ?? null;
    $payment_message = $status_response['description'] ?? '';
    
    file_put_contents($log_file, "Status: $payment_status, Message: $payment_message\n", FILE_APPEND);
    
    // Find the order and payment
    $stmt = $conn->prepare("SELECT p.*, o.order_id FROM payments p 
                           JOIN orders o ON p.order_id = o.order_id 
                           WHERE p.checkout_request_id = ?");
    $stmt->bind_param("s", $order_tracking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($payment = $result->fetch_assoc()) {
        $order_id = $payment['order_id'];
        
        switch ($payment_status) {
            case 1: // Completed
                $_SESSION['success_msg'] = "Payment completed successfully! Your order has been confirmed.";
                header("Location: order_success.php?order_id=" . $order_id);
                break;
                
            case 2: // Failed
                $_SESSION['error_msg'] = "Payment failed: " . $payment_message;
                header("Location: retry_payment.php?order_id=" . $order_id);
                break;
                
            case 0: // Pending
                $_SESSION['info_msg'] = "Payment is still being processed. You will be notified once confirmed.";
                header("Location: orders.php");
                break;
                
            default:
                $_SESSION['info_msg'] = "Payment status is being verified. Please check your orders page.";
                header("Location: orders.php");
        }
    } else {
        $_SESSION['error_msg'] = "Order not found. Please contact support.";
        header("Location: orders.php");
    }
} else {
    $_SESSION['error_msg'] = "Unable to verify payment status. Please contact support.";
    header("Location: orders.php");
}

exit();
?>