<?php
/**
 * M-Pesa STK Push Test Script
 * Use this script to test your M-Pesa integration
 */

// Include database connection and functions
require_once '../config/db.php';
require_once '../includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create a log file specifically for this test
$test_log = fopen("mpesa_test_log.txt", "a");
fwrite($test_log, "\n\n" . date("Y-m-d H:i:s") . " - STARTING MPESA TEST\n");

// Test function
function testMpesaConnection() {
    global $test_log;
    
    // Test if we can reach Safaricom API
    $test_url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
    $ch = curl_init($test_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $result = curl_exec($ch);
    $info = curl_getinfo($ch);
    
    fwrite($test_log, "Safaricom API Connection Test: " . ($result !== false ? "SUCCESS" : "FAILED") . "\n");
    fwrite($test_log, "HTTP Status: " . $info['http_code'] . "\n");
    
    if (curl_errno($ch)) {
        fwrite($test_log, "cURL Error: " . curl_error($ch) . "\n");
    }
    
    curl_close($ch);
    
    return [
        'status' => $result !== false ? 'success' : 'failed',
        'http_code' => $info['http_code']
    ];
}

// Test ngrok connection
function testNgrokConnection() {
    global $test_log;
    
    $ngrok_base_url = "https://1e59-41-139-186-177.ngrok-free.app";
    $callback_url = $ngrok_base_url . "/JamboPets/buyer/mpesa_callback.php";
    $ch = curl_init($callback_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_exec($ch);
    $error = curl_errno($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    
    fwrite($test_log, "ngrok Connection Test: " . ($error ? "FAILED" : "SUCCESS") . "\n");
    fwrite($test_log, "HTTP Status: " . $info['http_code'] . "\n");
    
    return [
        'status' => $error ? 'failed' : 'success',
        'http_code' => $info['http_code']
    ];
}

// Test database connection
function testDatabaseConnection() {
    global $conn, $test_log;
    
    if (!$conn) {
        fwrite($test_log, "Database Connection: FAILED\n");
        fwrite($test_log, "Error: " . mysqli_connect_error() . "\n");
        return [
            'status' => 'failed',
            'message' => mysqli_connect_error()
        ];
    }
    
    // Try a simple query
    $result = $conn->query("SELECT 1");
    if ($result) {
        fwrite($test_log, "Database Connection: SUCCESS\n");
        return [
            'status' => 'success',
            'message' => 'Connected to database successfully'
        ];
    } else {
        fwrite($test_log, "Database Connection: FAILED\n");
        fwrite($test_log, "Error: " . $conn->error . "\n");
        return [
            'status' => 'failed',
            'message' => $conn->error
        ];
    }
}

// Get file permissions
function getFilePermissions() {
    global $test_log;
    
    $current_dir = __DIR__;
    $log_file = $current_dir . "/mpesa_stk_log.txt";
    $callback_file = $current_dir . "/mpesa_callback_log.txt";
    
    $log_writable = is_writable($current_dir) ? "Yes" : "No";
    $stk_exists = file_exists($log_file) ? "Yes" : "No";
    $stk_writable = file_exists($log_file) && is_writable($log_file) ? "Yes" : "No";
    $callback_exists = file_exists($callback_file) ? "Yes" : "No";
    $callback_writable = file_exists($callback_file) && is_writable($callback_file) ? "Yes" : "No";
    
    fwrite($test_log, "Directory Writable: $log_writable\n");
    fwrite($test_log, "STK Log Exists: $stk_exists, Writable: $stk_writable\n");
    fwrite($test_log, "Callback Log Exists: $callback_exists, Writable: $callback_writable\n");
    
    return [
        'directory_writable' => $log_writable,
        'stk_log_exists' => $stk_exists,
        'stk_log_writable' => $stk_writable,
        'callback_log_exists' => $callback_exists,
        'callback_log_writable' => $callback_writable
    ];
}

// Manually test an STK push using a test phone number
// Note: This will actually trigger an STK push to this number
function testStkPush($phone = "254700000000", $amount = 1) {
    global $conn, $test_log;
    
    // Modified: Create a test order and payment record matching the actual table structure
    fwrite($test_log, "Attempting to create test order with phone: $phone, amount: $amount\n");
    
    // First, check if there's a valid user in the users table
    $user_check = $conn->query("SELECT user_id FROM users LIMIT 1");
    if ($user_check && $user_check->num_rows > 0) {
        $user = $user_check->fetch_assoc();
        $buyer_id = $user['user_id'];
        fwrite($test_log, "Using existing user_id: $buyer_id for test\n");
    } else {
        // No users found, we need to handle this case
        fwrite($test_log, "No users found in the database. Cannot proceed with test.\n");
        return [
            'status' => 'error',
            'message' => 'No users found in the database. Please create a user account first.'
        ];
    }
    
    // First, check if the SQL is valid by printing it to the log
    // Updated SQL to match your orders table schema and use a valid buyer_id
    $order_sql = "INSERT INTO orders (buyer_id, total_amount, status, payment_status, order_date, payment_method) 
                 VALUES (?, ?, 'pending', 'pending', NOW(), 'mpesa')";
    fwrite($test_log, "Order SQL: $order_sql\n");
    
    // Prepare statement with error checking
    $stmt = $conn->prepare($order_sql);
    if ($stmt === false) {
        fwrite($test_log, "Error preparing order statement: " . $conn->error . "\n");
        return [
            'status' => 'error',
            'message' => 'Failed to prepare order statement: ' . $conn->error
        ];
    }
    
    // Bind and execute with error checking
    if (!$stmt->bind_param("id", $buyer_id, $amount)) {
        fwrite($test_log, "Error binding parameters for order statement: " . $stmt->error . "\n");
        return [
            'status' => 'error',
            'message' => 'Failed to bind order parameters: ' . $stmt->error
        ];
    }
    
    if (!$stmt->execute()) {
        fwrite($test_log, "Error executing order statement: " . $stmt->error . "\n");
        return [
            'status' => 'error',
            'message' => 'Failed to execute order statement: ' . $stmt->error
        ];
    }
    
    $order_id = $conn->insert_id;
    $stmt->close();
    fwrite($test_log, "Successfully created order ID: $order_id\n");
    
    // Updated SQL to match your payments table schema
    $payment_sql = "INSERT INTO payments (order_id, amount, status, payment_method, created_at) 
                   VALUES (?, ?, 'pending', 'mpesa', NOW())";
    fwrite($test_log, "Payment SQL: $payment_sql\n");
    
    // Prepare statement with error checking
    $stmt = $conn->prepare($payment_sql);
    if ($stmt === false) {
        fwrite($test_log, "Error preparing payment statement: " . $conn->error . "\n");
        return [
            'status' => 'error',
            'message' => 'Failed to prepare payment statement: ' . $conn->error
        ];
    }
    
    // Bind and execute with error checking
    if (!$stmt->bind_param("id", $order_id, $amount)) {
        fwrite($test_log, "Error binding parameters for payment statement: " . $stmt->error . "\n");
        return [
            'status' => 'error',
            'message' => 'Failed to bind payment parameters: ' . $stmt->error
        ];
    }
    
    if (!$stmt->execute()) {
        fwrite($test_log, "Error executing payment statement: " . $stmt->error . "\n");
        return [
            'status' => 'error',
            'message' => 'Failed to execute payment statement: ' . $stmt->error
        ];
    }
    
    $payment_id = $conn->insert_id;
    $stmt->close();
    fwrite($test_log, "Successfully created payment ID: $payment_id\n");
    
    // Now try the STK push
    // Check if the function exists
    if (!function_exists('initiateMpesaSTKPush')) {
        fwrite($test_log, "ERROR: initiateMpesaSTKPush function not found\n");
        return [
            'status' => 'error',
            'message' => 'M-Pesa STK Push function not found'
        ];
    }
    
    // Try to initiate STK push
    fwrite($test_log, "Attempting to initiate STK push\n");
    $result = initiateMpesaSTKPush($phone, $amount, $order_id, $payment_id);
    
    fwrite($test_log, "STK Push Result: " . print_r($result, true) . "\n");
    
    return [
        'status' => isset($result['CheckoutRequestID']) ? 'success' : 'failed',
        'order_id' => $order_id,
        'payment_id' => $payment_id,
        'result' => $result
    ];
}

// Run the tests
$api_test = testMpesaConnection();
$ngrok_test = testNgrokConnection();
$db_test = testDatabaseConnection();
$file_permissions = getFilePermissions();

// Only run STK push test if user confirms via form submission
$stk_test_result = null;
if (isset($_POST['test_stk']) && $_POST['test_stk'] == 'yes') {
    $phone = isset($_POST['phone']) ? $_POST['phone'] : '254700000000';
    $amount = isset($_POST['amount']) ? (int)$_POST['amount'] : 1;
    
    // Validate phone
    if (strlen($phone) < 10 || !is_numeric(preg_replace('/^(?:\+254|254|0)/', '', $phone))) {
        $stk_test_result = [
            'status' => 'error',
            'message' => 'Invalid phone number format'
        ];
    } else {
        $stk_test_result = testStkPush($phone, $amount);
    }
}

fclose($test_log);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M-Pesa Integration Test</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        h1, h2 { color: #333; }
        .test-container { margin-bottom: 30px; }
        .success { color: green; font-weight: bold; }
        .failed { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="number"] { padding: 8px; width: 300px; }
        button { padding: 10px 15px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        pre { background-color: #f5f5f5; padding: 10px; overflow: auto; }
    </style>
</head>
<body>
    <h1>M-Pesa Integration Test Results</h1>
    
    <div class="test-container">
        <h2>Connection Tests</h2>
        <table>
            <tr>
                <th>Test</th>
                <th>Status</th>
                <th>Details</th>
            </tr>
            <tr>
                <td>Safaricom API Connection</td>
                <td class="<?php echo $api_test['status'] == 'success' ? 'success' : 'failed'; ?>">
                    <?php echo strtoupper($api_test['status']); ?>
                </td>
                <td>HTTP Status: <?php echo $api_test['http_code']; ?></td>
            </tr>
            <tr>
                <td>ngrok Connection</td>
                <td class="<?php echo $ngrok_test['status'] == 'success' ? 'success' : 'failed'; ?>">
                    <?php echo strtoupper($ngrok_test['status']); ?>
                </td>
                <td>HTTP Status: <?php echo $ngrok_test['http_code']; ?></td>
            </tr>
            <tr>
                <td>Database Connection</td>
                <td class="<?php echo $db_test['status'] == 'success' ? 'success' : 'failed'; ?>">
                    <?php echo strtoupper($db_test['status']); ?>
                </td>
                <td><?php echo $db_test['message']; ?></td>
            </tr>
        </table>
    </div>
    
    <div class="test-container">
        <h2>File Permissions</h2>
        <table>
            <tr>
                <th>Check</th>
                <th>Status</th>
            </tr>
            <tr>
                <td>Directory Writable</td>
                <td class="<?php echo $file_permissions['directory_writable'] == 'Yes' ? 'success' : 'warning'; ?>">
                    <?php echo $file_permissions['directory_writable']; ?>
                </td>
            </tr>
            <tr>
                <td>STK Log File Exists</td>
                <td class="<?php echo $file_permissions['stk_log_exists'] == 'Yes' ? 'success' : 'warning'; ?>">
                    <?php echo $file_permissions['stk_log_exists']; ?>
                </td>
            </tr>
            <tr>
                <td>STK Log File Writable</td>
                <td class="<?php echo $file_permissions['stk_log_writable'] == 'Yes' ? 'success' : 'warning'; ?>">
                    <?php echo $file_permissions['stk_log_writable']; ?>
                </td>
            </tr>
            <tr>
                <td>Callback Log File Exists</td>
                <td class="<?php echo $file_permissions['callback_log_exists'] == 'Yes' ? 'success' : 'warning'; ?>">
                    <?php echo $file_permissions['callback_log_exists']; ?>
                </td>
            </tr>
            <tr>
                <td>Callback Log File Writable</td>
                <td class="<?php echo $file_permissions['callback_log_writable'] == 'Yes' ? 'success' : 'warning'; ?>">
                    <?php echo $file_permissions['callback_log_writable']; ?>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="test-container">
        <h2>Test M-Pesa STK Push</h2>
        <p class="warning">Warning: This will send an actual STK push to the phone number you provide!</p>
        
        <?php 
        // Check if there are users in the database before showing the form
        $user_check = $conn->query("SELECT user_id FROM users LIMIT 1");
        if ($user_check && $user_check->num_rows > 0):
        ?>
        
        <form method="post" action="">
            <div class="form-group">
                <label for="phone">Phone Number (Format: 254XXXXXXXXX):</label>
                <input type="text" id="phone" name="phone" value="254700000000" required>
            </div>
            
            <div class="form-group">
                <label for="amount">Amount (KES - use 1 for testing):</label>
                <input type="number" id="amount" name="amount" value="1" min="1" max="100" required>
            </div>
            
            <div class="form-group">
                <input type="hidden" name="test_stk" value="yes">
                <button type="submit">Test STK Push</button>
            </div>
        </form>
        
        <?php else: ?>
            <div class="warning">
                <p>No users found in the database. Please create a user account before testing M-Pesa integration.</p>
            </div>
        <?php endif; ?>
        
        <?php if ($stk_test_result): ?>
        <h3>STK Push Test Result</h3>
        <p>Status: <span class="<?php echo $stk_test_result['status'] == 'success' ? 'success' : 'failed'; ?>">
            <?php echo strtoupper($stk_test_result['status']); ?>
        </span></p>
        
        <?php if (isset($stk_test_result['order_id'])): ?>
        <p>Test Order ID: <?php echo $stk_test_result['order_id']; ?></p>
        <p>Test Payment ID: <?php echo $stk_test_result['payment_id']; ?></p>
        <?php endif; ?>
        
        <h4>Response Details:</h4>
        <pre><?php print_r($stk_test_result['result'] ?? $stk_test_result['message']); ?></pre>
        <?php endif; ?>
    </div>
    
    <div class="test-container">
        <h2>Troubleshooting Tips</h2>
        <ul>
            <li>Make sure your ngrok tunnel is running and the URL is updated in your code</li>
            <li>Check that you've registered the correct callback URL in the Safaricom Developer Portal</li>
            <li>Verify your Consumer Key and Secret are correctly entered in the code</li>
            <li>Ensure your test phone number is registered with M-Pesa</li>
            <li>For sandbox testing, use a small amount (1-10 KES)</li>
            <li>Check the log files for detailed error messages</li>
        </ul>
    </div>
</body>
</html>