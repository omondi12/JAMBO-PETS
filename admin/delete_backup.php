<?php
// Include database connection
require_once '../config/db.php';

// Check if user is logged in as admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    http_response_code(403);
    echo "Access denied";
    exit;
}

// Check if filename is provided
if (!isset($_POST['filename']) || empty($_POST['filename'])) {
    http_response_code(400);
    echo "Filename is required";
    exit;
}

// Sanitize the filename to prevent directory traversal
$filename = basename($_POST['filename']);

// Validate that we only delete SQL files
if (!preg_match('/^backup_[\d-]+_[\d-]+\.sql$/', $filename)) {
    http_response_code(400);
    echo "Invalid backup filename format";
    exit;
}

// Path to backup file
$backup_file = "../backups/" . $filename;

// Check if file exists
if (!file_exists($backup_file)) {
    http_response_code(404);
    echo "Backup file not found";
    exit;
}

// Try to delete the file
if (unlink($backup_file)) {
    // Log the action
    $user_id = $_SESSION['user_id'];
    $action = "Deleted backup file: " . $filename;
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    $log_query = "INSERT INTO admin_logs (user_id, action, ip_address) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($log_query);
    $stmt->bind_param("iss", $user_id, $action, $ip_address);
    $stmt->execute();
    
    echo "Backup deleted successfully";
} else {
    http_response_code(500);
    echo "Failed to delete backup file";
}
?>