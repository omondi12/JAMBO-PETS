<?php
// Database connection and admin action handling script
// This file handles AJAX requests from the profile.php page

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is a master admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Include database connection
require_once '../config/db.php';

// Check if admin has master role
$user_id = $_SESSION['user_id'];
$role_query = "SELECT admin_role FROM admin_roles WHERE user_id = ?";
$role_stmt = $conn->prepare($role_query);
$role_stmt->bind_param("i", $user_id);
$role_stmt->execute();
$role_result = $role_stmt->get_result();
$admin_role_data = $role_result->fetch_assoc();
$admin_role = $admin_role_data ? $admin_role_data['admin_role'] : '';

// For actions that require master admin privileges
$master_only_actions = ['add_admin', 'delete_admin', 'edit_admin', 'get_admin_data'];

if (in_array($_POST['action'] ?? '', $master_only_actions) && $admin_role !== 'master') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'You do not have permission to perform this action']);
    exit;
}

// Process based on action parameter
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add_admin':
            addAdmin($conn);
            break;
        case 'delete_admin':
            deleteAdmin($conn);
            break;
        case 'edit_admin':
            editAdmin($conn);
            break;
        case 'get_admin_data':
            getAdminData($conn);
            break;
        default:
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No action specified']);
}

/**
 * Add a new admin user
 * 
 * @param mysqli $conn Database connection
 */
function addAdmin($conn) {
    // Validate required fields
    $required_fields = ['first_name', 'last_name', 'email', 'phone', 'password', 'admin_role'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            return;
        }
    }
    
    // Get and sanitize form data
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $admin_role = trim($_POST['admin_role']);
    
    // Validate admin role
    $valid_roles = ['master', 'product', 'user'];
    if (!in_array($admin_role, $valid_roles)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid admin role']);
        return;
    }
    
    // Check if email already exists
    $check_email = "SELECT user_id FROM users WHERE email = ?";
    $check_stmt = $conn->prepare($check_email);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Email address already in use']);
        return;
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Begin transaction for multiple operations
    $conn->begin_transaction();
    
    try {
        // Insert into users table
        $insert_user = "INSERT INTO users (email, password, first_name, last_name, phone, user_type) 
                        VALUES (?, ?, ?, ?, ?, 'admin')";
        $user_stmt = $conn->prepare($insert_user);
        $user_stmt->bind_param("sssss", $email, $hashed_password, $first_name, $last_name, $phone);
        $user_stmt->execute();
        
        // Get the new user ID
        $admin_id = $conn->insert_id;
        
        // Insert into admin_roles table
        $insert_role = "INSERT INTO admin_roles (user_id, admin_role) VALUES (?, ?)";
        $role_stmt = $conn->prepare($insert_role);
        $role_stmt->bind_param("is", $admin_id, $admin_role);
        $role_stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Admin added successfully']);
    } catch (Exception $e) {
        // Rollback in case of error
        $conn->rollback();
        
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error adding admin: ' . $e->getMessage()]);
    }
    
    // Close statements
    $check_stmt->close();
    $user_stmt->close();
    $role_stmt->close();
}

/**
 * Delete an admin user
 * 
 * @param mysqli $conn Database connection
 */
function deleteAdmin($conn) {
    // Check if admin ID is provided
    if (!isset($_POST['admin_id']) || empty($_POST['admin_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Admin ID is required']);
        return;
    }
    
    $admin_id = (int)$_POST['admin_id'];
    
    // Prevent deleting yourself
    if ($admin_id === (int)$_SESSION['user_id']) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'You cannot delete your own account']);
        return;
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Delete from admin_roles first (foreign key constraint)
        $delete_role = "DELETE FROM admin_roles WHERE user_id = ?";
        $role_stmt = $conn->prepare($delete_role);
        $role_stmt->bind_param("i", $admin_id);
        $role_stmt->execute();
        
        // Delete from users table
        $delete_user = "DELETE FROM users WHERE user_id = ? AND user_type = 'admin'";
        $user_stmt = $conn->prepare($delete_user);
        $user_stmt->bind_param("i", $admin_id);
        $user_stmt->execute();
        
        // Check if user was actually deleted
        if ($user_stmt->affected_rows === 0) {
            throw new Exception("Admin not found or could not be deleted");
        }
        
        // Commit transaction
        $conn->commit();
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Admin deleted successfully']);
    } catch (Exception $e) {
        // Rollback in case of error
        $conn->rollback();
        
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error deleting admin: ' . $e->getMessage()]);
    }
    
    // Close statements
    if (isset($role_stmt)) $role_stmt->close();
    if (isset($user_stmt)) $user_stmt->close();
}

/**
 * Edit an existing admin user
 * 
 * @param mysqli $conn Database connection
 */
function editAdmin($conn) {
    // Check if admin ID is provided
    if (!isset($_POST['admin_id']) || empty($_POST['admin_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Admin ID is required']);
        return;
    }
    
    // Validate required fields
    $required_fields = ['first_name', 'last_name', 'email', 'phone', 'admin_role', 'status'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            return;
        }
    }
    
    // Get and sanitize form data
    $admin_id = (int)$_POST['admin_id'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $admin_role = trim($_POST['admin_role']);
    $status = trim($_POST['status']);
    $reset_password = isset($_POST['reset_password']);
    
    // Validate admin role
    $valid_roles = ['master', 'product', 'user'];
    if (!in_array($admin_role, $valid_roles)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid admin role']);
        return;
    }
    
    // Validate status
    $valid_statuses = ['active', 'inactive', 'suspended'];
    if (!in_array($status, $valid_statuses)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        return;
    }
    
    // Check if email already exists (excluding current admin)
    $check_email = "SELECT user_id FROM users WHERE email = ? AND user_id != ?";
    $check_stmt = $conn->prepare($check_email);
    $check_stmt->bind_param("si", $email, $admin_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Email address already in use by another account']);
        return;
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Update users table
        $update_user = "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, status = ? WHERE user_id = ? AND user_type = 'admin'";
        $user_stmt = $conn->prepare($update_user);
        $user_stmt->bind_param("sssssi", $first_name, $last_name, $email, $phone, $status, $admin_id);
        $user_stmt->execute();
        
        // Reset password if requested
        if ($reset_password) {
            $default_password = 'password123'; // Default password for reset
            $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
            
            $update_password = "UPDATE users SET password = ? WHERE user_id = ?";
            $password_stmt = $conn->prepare($update_password);
            $password_stmt->bind_param("si", $hashed_password, $admin_id);
            $password_stmt->execute();
        }
        
        // Update admin_roles table
        // First check if role exists for this admin
        $check_role = "SELECT user_id FROM admin_roles WHERE user_id = ?";
        $role_check_stmt = $conn->prepare($check_role);
        $role_check_stmt->bind_param("i", $admin_id);
        $role_check_stmt->execute();
        $role_result = $role_check_stmt->get_result();
        
        if ($role_result->num_rows > 0) {
            // Update existing role
            $update_role = "UPDATE admin_roles SET admin_role = ? WHERE user_id = ?";
            $role_stmt = $conn->prepare($update_role);
            $role_stmt->bind_param("si", $admin_role, $admin_id);
            $role_stmt->execute();
        } else {
            // Insert new role
            $insert_role = "INSERT INTO admin_roles (user_id, admin_role) VALUES (?, ?)";
            $role_stmt = $conn->prepare($insert_role);
            $role_stmt->bind_param("is", $admin_id, $admin_role);
            $role_stmt->execute();
        }
        
        // Commit transaction
        $conn->commit();
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Admin updated successfully']);
    } catch (Exception $e) {
        // Rollback in case of error
        $conn->rollback();
        
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error updating admin: ' . $e->getMessage()]);
    }
    
    // Close statements
    $check_stmt->close();
    $user_stmt->close();
    if (isset($password_stmt)) $password_stmt->close();
    $role_check_stmt->close();
    $role_stmt->close();
}

/**
 * Get admin data for editing
 * 
 * @param mysqli $conn Database connection
 */
function getAdminData($conn) {
    // Check if admin ID is provided
    if (!isset($_POST['admin_id']) || empty($_POST['admin_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Admin ID is required']);
        return;
    }
    
    $admin_id = (int)$_POST['admin_id'];
    
    // Get admin data
    $query = "SELECT u.user_id, u.first_name, u.last_name, u.email, u.phone, u.status, ar.admin_role 
              FROM users u 
              LEFT JOIN admin_roles ar ON u.user_id = ar.user_id 
              WHERE u.user_id = ? AND u.user_type = 'admin'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Admin not found']);
        return;
    }
    
    $admin_data = $result->fetch_assoc();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'admin' => $admin_data]);
    
    $stmt->close();
}

// Close database connection
$role_stmt->close();
$conn->close();