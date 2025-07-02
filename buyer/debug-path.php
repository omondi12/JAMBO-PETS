<?php
// Place this file in the same directory as wishlist.php to debug paths

// Debug information
echo "<h1>Image Path Debugging</h1>";

// Get the document root
echo "<h2>Server Information</h2>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Script Path: " . __FILE__ . "</p>";
echo "<p>Current Directory: " . getcwd() . "</p>";

// Check if the uploads directories exist
echo "<h2>Directory Checks</h2>";
$petsPath = "../uploads/pets";
$productsPath = "../uploads/images";

echo "<p>Checking if '$petsPath' exists: " . (is_dir($petsPath) ? "Yes" : "No") . "</p>";
echo "<p>Checking if '$productsPath' exists: " . (is_dir($productsPath) ? "Yes" : "No") . "</p>";

// If the pets directory exists, list its contents
if (is_dir($petsPath)) {
    echo "<h2>Contents of $petsPath</h2>";
    echo "<ul>";
    $files = scandir($petsPath);
    foreach ($files as $file) {
        if ($file != "." && $file != "..") {
            echo "<li>$file";
            echo " (Size: " . filesize("$petsPath/$file") . " bytes)";
            echo " (Image: <img src='$petsPath/$file' width='100' height='100' alt='$file'>)";
            echo "</li>";
        }
    }
    echo "</ul>";
}

// Get the database connection (assuming it's available in your environment)
require_once '../config/db.php';
if (isset($conn) && !$conn->connect_error) {
    echo "<h2>Database Information</h2>";
    
    // Check the structure of the pets table
    $result = $conn->query("SHOW COLUMNS FROM pets");
    if ($result) {
        echo "<h3>Pets Table Structure</h3>";
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td></tr>";
        }
        echo "</table>";
    }
    
    // Check some pet records to see image column values
    $result = $conn->query("SELECT pet_id, name, " . 
                           "(SELECT column_name FROM information_schema.columns WHERE table_name = 'pets' AND column_name LIKE '%image%' OR column_name LIKE '%photo%' LIMIT 1) as image_column " .
                           "FROM pets LIMIT 5");
    if ($result) {
        echo "<h3>Sample Pet Records</h3>";
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Name</th><th>Image Column</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>{$row['pet_id']}</td><td>{$row['name']}</td><td>{$row['image_column']}</td></tr>";
        }
        echo "</table>";
    }
}
?>