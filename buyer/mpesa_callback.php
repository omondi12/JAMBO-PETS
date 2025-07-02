/**
 * Create a new mpesa_callback.php file to handle callbacks
 */
function createMpesaCallbackFile() {
    $callback_file = "../buyer/mpesa_callback.php";
    $callback_content = '<?php
// Start the session
session_start();

// Include database connection
require_once "../config/db.php";

// Set up log file
$log_dir = "../logs";
if (!file_exists($log_dir)) {
    mkdir($log_dir, 0755, true);
}

$log_file = fopen($log_dir . "/mpesa_callback_log.txt", "a");
$timestamp = date("Y-m-d H:i:s");
fwrite($log_file, "\n\n$timestamp - M-PESA CALLBACK RECEIVED\n");

// Get the response data
$callbackJSONData = file_get_contents("php://input");
fwrite($log_file, "Callback data: " . $callbackJSONData . "\n");

// Decode the JSON response
$callbackData = json_decode($callbackJSONData, true);

// Check if the response is valid
if (!is_array($callbackData)) {
    fwrite($log_file, "ERROR: Invalid callback data format\n");
    fclose($log_file);
    header("Content-Type: application/json");
    echo json_encode(["ResultCode" => "1", "ResultDesc" => "Invalid data format"]);
    exit();
}

// Extract the callback metadata
if (isset($callbackData["Body"]) && isset($callbackData["Body"]["stkCallback"])) {
    $stkCallback = $callbackData["Body"]["stkCallback"];
    $ResultCode = $stkCallback["ResultCode"];
    $ResultDesc = $stkCallback["ResultDesc"];
    $CheckoutRequestID = $stkCallback["CheckoutRequestID"];
    
    fwrite($log_file, "Result Code: $ResultCode\n");
    fwrite($log_file, "Result Description: $ResultDesc\n");
    fwrite($log_file, "Checkout Request ID: $CheckoutRequestID\n");
    
    // Get payment record using CheckoutRequestID
    $stmt = $conn->prepare("SELECT * FROM payments WHERE checkout_request_id = ?");
    $stmt->bind_param("s", $CheckoutRequestID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $payment = $result->fetch_assoc();
        $payment_id = $payment["payment_id"];
        $order_id = $payment["order_id"];
        
        fwrite($log_file, "Found payment record: Payment ID $payment_id, Order ID $order_id\n");
        
        // Update payment status based on result code
        if ($ResultCode == 0) {
            // Transaction was successful
            $payment_status = "completed";
            $notes = "Payment successful via M-Pesa";
            
            // Extract transaction details if available
            if (isset($stkCallback["CallbackMetadata"]) && isset($stkCallback["CallbackMetadata"]["Item"])) {
                $metadata = $stkCallback["CallbackMetadata"]["Item"];
                $mpesa_receipt = "";
                $transaction_amount = "";
                $transaction_date = "";
                $phone_number = "";
                
                foreach ($metadata as $item) {
                    if ($item["Name"] == "MpesaReceiptNumber") {
                        $mpesa_receipt = $item["Value"];
                    } else if ($item["Name"] == "Amount") {
                        $transaction_amount = $item["Value"];
                    } else if ($item["Name"] == "TransactionDate") {
                        $transaction_date = $item["Value"];
                    } else if ($item["Name"] == "PhoneNumber") {
                        $phone_number = $item["Value"];
                    }
                }
                
                fwrite($log_file, "Transaction details - Receipt: $mpesa_receipt, Amount: $transaction_amount, Date: $transaction_date, Phone: $phone_number\n");
                
                // Update payment with transaction details
                $stmt = $conn->prepare("UPDATE payments SET status = ?, notes = ?, transaction_id = ?, transaction_date = STR_TO_DATE(?, \'%Y%m%d%H%i%s\') WHERE payment_id = ?");
                $stmt->bind_param("ssssi", $payment_status, $notes, $mpesa_receipt, $transaction_date, $payment_id);
            } else {
                // Update payment status only
                $stmt = $conn->prepare("UPDATE payments SET status = ?, notes = ? WHERE payment_id = ?");
                $stmt->bind_param("ssi", $payment_status, $notes, $payment_id);
            }
        } else {
            // Transaction failed
            $payment_status = "failed";
            $notes = "Payment failed: " . $ResultDesc;
            
            $stmt = $conn->prepare("UPDATE payments SET status = ?, notes = ? WHERE payment_id = ?");
            $stmt->bind_param("ssi", $payment_status, $notes, $payment_id);
        }
        
        // Execute the update
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            fwrite($log_file, "Payment record updated successfully to status: $payment_status\n");
            
            // If payment is completed, update the order status
            if ($payment_status == "completed") {
                $order_stmt = $conn->prepare("UPDATE orders SET status = \'processing\' WHERE order_id = ?");
                $order_stmt->bind_param("i", $order_id);
                $order_stmt->execute();
                
                if ($order_stmt->affected_rows > 0) {
                    fwrite($log_file, "Order status updated to processing\n");
                } else {
                    fwrite($log_file, "Failed to update order status: " . $conn->error . "\n");
                }
                
                $order_stmt->close();
            }
        } else {
            fwrite($log_file, "Failed to update payment record: " . $conn->error . "\n");
        }
        
        $stmt->close();
    } else {
        fwrite($log_file, "ERROR: No payment record found for CheckoutRequestID: $CheckoutRequestID\n");
    }
} else {
    fwrite($log_file, "ERROR: Invalid callback structure\n");
}

// Always respond with success to M-Pesa
fclose($log_file);
header("Content-Type: application/json");
echo json_encode(["ResultCode" => "0", "ResultDesc" => "Callback received successfully"]);
?>';

    file_put_contents($callback_file, $callback_content);
    chmod($callback_file, 0644);
    
    return true;
}
