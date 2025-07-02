<?php
/**
 * M-Pesa API Credentials Test
 * Use this script to test your M-Pesa API credentials
 */

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create a log file
$log_file = fopen("mpesa_cred_test.txt", "a");
fwrite($log_file, "\n\n" . date("Y-m-d H:i:s") . " - TESTING API CREDENTIALS\n");

// These should match the credentials in your STK push function
$consumer_key = "a1g13RUKXvoZs3O7AjZfzkDxjATHBGz3GULHjz8WCBaiJm61"; 
$consumer_secret = "kUSWCRMn7qIMTfsYUtmGCcbjzvL4wGPFajDsoz53REXyVTp8aItPDN7qCwu8ZxYO";

fwrite($log_file, "Testing with consumer key: " . substr($consumer_key, 0, 5) . "...\n");

// Test getting access token
$token_url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
$token_credentials = base64_encode($consumer_key . ':' . $consumer_secret);

$token_curl = curl_init();
curl_setopt($token_curl, CURLOPT_URL, $token_url);
curl_setopt($token_curl, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . $token_credentials));
curl_setopt($token_curl, CURLOPT_HEADER, false);
curl_setopt($token_curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($token_curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($token_curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($token_curl, CURLOPT_VERBOSE, true);

// For debugging
$verbose = fopen('php://temp', 'w+');
curl_setopt($token_curl, CURLOPT_STDERR, $verbose);

// Execute request
$token_response = curl_exec($token_curl);
$token_info = curl_getinfo($token_curl);

// Get verbose output
rewind($verbose);
$verboseLog = stream_get_contents($verbose);

// Log request details
fwrite($log_file, "API Request URL: " . $token_url . "\n");
fwrite($log_file, "HTTP Status: " . $token_info['http_code'] . "\n");
fwrite($log_file, "Total Time: " . $token_info['total_time'] . " seconds\n");

if (curl_errno($token_curl)) {
    fwrite($log_file, "CURL ERROR: " . curl_error($token_curl) . "\n");
    $result = [
        'status' => 'error',
        'message' => curl_error($token_curl)
    ];
} else {
    fwrite($log_file, "Response: " . $token_response . "\n");
    
    $token_result = json_decode($token_response);
    
    if (isset($token_result->access_token)) {
        fwrite($log_file, "SUCCESS: Access token received!\n");
        fwrite($log_file, "Token: " . substr($token_result->access_token, 0, 10) . "...\n");
        $result = [
            'status' => 'success',
            'token' => $token_result->access_token,
            'expires_in' => $token_result->expires_in ?? 'unknown'
        ];
    } else {
        fwrite($log_file, "ERROR: Failed to get access token\n");
        if (isset($token_result->errorCode)) {
            fwrite($log_file, "Error Code: " . $token_result->errorCode . "\n");
            fwrite($log_file, "Error Message: " . ($token_result->errorMessage ?? 'Unknown') . "\n");
        }
        $result = [
            'status' => 'error',
            'message' => 'Failed to get access token',
            'response' => $token_response
        ];
    }
}

// Log verbose output for debugging
fwrite($log_file, "\nDetailed Request Log:\n" . $verboseLog . "\n");

curl_close($token_curl);
fclose($log_file);

// Display results
header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);
?>