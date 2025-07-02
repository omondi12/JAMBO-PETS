<?php
/**
 * Database Connection Configuration
 * 
 * This file manages the connection to the MySQL database for Jambo Pets
 */

// Database connection parameters
define('DB_HOST', 'localhost');      // Database host (usually localhost)
define('DB_USER', 'root');           // Database username 
define('DB_PASS', '');               // Database password (blank for default XAMPP setup)
define('DB_NAME', 'jambo_pets');     // Database name

// Create a global connection variable
$conn = null;

// Immediately establish a connection when this file is included
function connectDB() {
    global $conn;
    
    // Only create a new connection if one doesn't already exist
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        // Set charset to ensure proper handling of all characters
        $conn->set_charset("utf8mb4");
    }
    
    return $conn;
}

// Initialize the connection when the file is included
$conn = connectDB();

// The rest of your helper functions remain unchanged
// Helper function to execute queries with error handling
function executeQuery($sql, $params = []) {
    $conn = connectDB();
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }
    
    // If we have parameters, bind them
    if (!empty($params)) {
        $types = '';
        $bindParams = [];
        
        // Build the types string (s for string, i for integer, d for double, b for blob)
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } elseif (is_string($param)) {
                $types .= 's';
            } else {
                $types .= 'b'; // Default to blob
            }
            $bindParams[] = $param;
        }
        
        // Create array with references for bind_param
        $bindParamsRef = [];
        $bindParamsRef[] = &$types;
        
        foreach ($bindParams as $key => $value) {
            $bindParamsRef[] = &$bindParams[$key];
        }
        
        // Call bind_param with the array of references
        call_user_func_array([$stmt, 'bind_param'], $bindParamsRef);
    }
    
    // Execute the statement
    if (!$stmt->execute()) {
        die("Query execution failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $stmt->close();
    
    return $result;
}

// Function to get a single row
function fetchRow($sql, $params = []) {
    $result = executeQuery($sql, $params);
    return $result->fetch_assoc();
}

// Function to get multiple rows
function fetchAll($sql, $params = []) {
    $result = executeQuery($sql, $params);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to insert data and return inserted ID
function insert($sql, $params = []) {
    $conn = connectDB();
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }
    
    // If we have parameters, bind them
    if (!empty($params)) {
        $types = '';
        $bindParams = [];
        
        // Build the types string
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } elseif (is_string($param)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
            $bindParams[] = $param;
        }
        
        // Create array with references for bind_param
        $bindParamsRef = [];
        $bindParamsRef[] = &$types;
        
        foreach ($bindParams as $key => $value) {
            $bindParamsRef[] = &$bindParams[$key];
        }
        
        // Call bind_param with the array of references
        call_user_func_array([$stmt, 'bind_param'], $bindParamsRef);
    }
    
    // Execute the statement
    if (!$stmt->execute()) {
        die("Query execution failed: " . $stmt->error);
    }
    
    $lastId = $conn->insert_id;
    $stmt->close();
    
    return $lastId;
}

// Function to update data and return number of affected rows
function update($sql, $params = []) {
    $conn = connectDB();
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }
    
    // If we have parameters, bind them
    if (!empty($params)) {
        $types = '';
        $bindParams = [];
        
        // Build the types string
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } elseif (is_string($param)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
            $bindParams[] = $param;
        }
        
        // Create array with references for bind_param
        $bindParamsRef = [];
        $bindParamsRef[] = &$types;
        
        foreach ($bindParams as $key => $value) {
            $bindParamsRef[] = &$bindParams[$key];
        }
        
        // Call bind_param with the array of references
        call_user_func_array([$stmt, 'bind_param'], $bindParamsRef);
    }
    
    // Execute the statement
    if (!$stmt->execute()) {
        die("Query execution failed: " . $stmt->error);
    }
    
    $affectedRows = $stmt->affected_rows;
    $stmt->close();
    
    return $affectedRows;
}

// Function to delete data and return number of affected rows
function delete($sql, $params = []) {
    return update($sql, $params); // Delete uses the same logic as update
}