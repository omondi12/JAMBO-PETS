<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include utility functions
require_once '../includes/functions.php';

// Log activity
if (isset($_SESSION['user_id'])) {
    require_once '../config/db.php';
    logActivity('logout', 'User logged out');
}

// Destroy session
session_unset();
session_destroy();

// Redirect to homepage
header("Location: " . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../index.php'));
exit;
?>
