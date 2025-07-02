<?php
/**
 * PesaPal Callback Handler (pesapal_callback.php)
 * This file handles the IPN (Instant Payment Notification) from PesaPal
 */

// Start session and include necessary files
session_start();
require_once '../config/db.php';

// Log callback for debugging
$log_file = "pesapal_callback_log.txt";
file_put_contents($log_file, "\n\n" . date("Y-m-d H:i:s") . " - PesaPal Callback Received\n", FILE_APPEND);
file_put_contents($log_file, "GET Data: " . print_r($_GET, true) . "\n", FILE_APPEND);
file_put_contents($log_file, "POST Data: " . print_r($_POST, true) . "\n", FILE_APPEND);

// Get the order tracking ID from the callback
$order_tracking_id = $_GET['OrderTrackingId'] ?? $_POST['OrderTrackingId'] ?? null;
$order_merchant_reference = $_GET['OrderMerchantReference'] ?? $_POST['OrderMerchantReference'] ?? null;

if (!$order_tracking_id) {
    file_put_contents($log_file, "ERROR: No OrderTrackingId provided\n", FILE_APPEND);
    http_response_code(400);
    exit('Missing OrderTrackingId');
}

file_put_contents($log_file, "Processing Order Tracking ID: $order_tracking_id\n", FILE_APPEND);

// Include the PesaPal functions
include_once 'pesapal_functions.php'; // The file with updated PesaPal functions

// Check transaction status
$status_response = checkPesapalTransactionStatus($order_tracking_id);

if ($status_response) {
    file_put_contents($log_file, "Status Response: " . json_encode($status_response) . "\n", FILE_APPEND);
    
    $payment_status = $status_response['status_code'] ?? null;
    $payment_message = $status_response['description'] ?? '';
    
    // Find the payment record
    $stmt = $conn->prepare("SELECT p.*, o.order_id FROM payments p 
                           JOIN orders o ON p.order_id = o.order_id 
                           WHERE p.checkout_request_id = ?");
    $stmt->bind_param("s", $order_tracking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($payment = $result->fetch_assoc()) {
        $payment_id = $payment['payment_id'];
        $order_id = $payment['order_id'];
        
        file_put_contents($log_file, "Found Payment ID: $payment_id, Order ID: $order_id\n", FILE_APPEND);
        
        // Update payment status based on PesaPal response
        switch ($payment_status) {
            case 1: // Completed
                $update_stmt = $conn->prepare("UPDATE payments SET status = 'completed', notes = ?, updated_at = NOW() WHERE payment_id = ?");
                $update_stmt->bind_param("si", $payment_message, $payment_id);
                $update_stmt->execute();
                
                // Update order status
                $order_stmt = $conn->prepare("UPDATE orders SET status = 'confirmed', payment_status = 'paid' WHERE order_id = ?");
                $order_stmt->bind_param("i", $order_id);
                $order_stmt->execute();
                
                file_put_contents($log_file, "Payment completed successfully\n", FILE_APPEND);
                break;
                
            case 2: // Failed
                $update_stmt = $conn->prepare("UPDATE payments SET status = 'failed', notes = ?, updated_at = NOW() WHERE payment_id = ?");
                $update_stmt->bind_param("si", $payment_message, $payment_id);
                $update_stmt->execute();
                
                file_put_contents($log_file, "Payment failed: $payment_message\n", FILE_APPEND);
                break;
                
            case 3: // Reversed
                $update_stmt = $conn->prepare("UPDATE payments SET status = 'refunded', notes = ?, updated_at = NOW() WHERE payment_id = ?");
                $update_stmt->bind_param("si", $payment_message, $payment_id);
                $update_stmt->execute();
                
                file_put_contents($log_file, "Payment reversed: $payment_message\n", FILE_APPEND);
                break;
                
            default:
                file_put_contents($log_file, "Unknown payment status: $payment_status\n", FILE_APPEND);
        }
    } else {
        file_put_contents($log_file, "ERROR: Payment record not found for tracking ID: $order_tracking_id\n", FILE_APPEND);
    }
} else {
    file_put_contents($log_file, "ERROR: Could not get status from PesaPal\n", FILE_APPEND);
}

// Respond to PesaPal
http_response_code(200);
echo "OK";
?>