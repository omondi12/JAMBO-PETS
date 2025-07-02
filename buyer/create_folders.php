<?php
// Script to create the necessary folder structure
$root_dir = realpath(__DIR__ . '/..');  // Assuming this script is in the same directory as wishlist.php

// Check if uploads directory exists, if not create it
$uploads_dir = $root_dir . '/uploads';
if (!file_exists($uploads_dir)) {
    mkdir($uploads_dir, 0755, true);
    echo "Created uploads directory<br>";
}

// Check if uploads/images directory exists, if not create it
$images_dir = $uploads_dir . '/images';
if (!file_exists($images_dir)) {
    mkdir($images_dir, 0755, true);
    echo "Created images directory<br>";
}

// Check if uploads/pets directory exists, if not create it
$pets_dir = $uploads_dir . '/pets';
if (!file_exists($pets_dir)) {
    mkdir($pets_dir, 0755, true);
    echo "Created pets directory<br>";
}

// Make sure the directories are writable
chmod($uploads_dir, 0755);
chmod($images_dir, 0755);
chmod($pets_dir, 0755);

echo "Directory structure created successfully!<br>";
echo "Path to uploads: " . $uploads_dir . "<br>";
echo "Path to images: " . $images_dir . "<br>";
echo "Path to pets: " . $pets_dir . "<br>";
?>