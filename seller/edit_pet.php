<?php
// Start the session
session_start();

// Include database connection
require_once '../config/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'seller') {
    header('Location: ../auth/login.php');
    exit();
}

// Get seller ID
$user_id = $_SESSION['user_id'];
$sql = "SELECT seller_id FROM seller_profiles WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$seller = $result->fetch_assoc();
$seller_id = $seller['seller_id'];

// Check if pet ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: manage_pets.php');
    exit();
}

$pet_id = (int)$_GET['id'];

// Verify pet belongs to seller
$sql = "SELECT p.*, c.name as category_name 
        FROM pets p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        WHERE p.pet_id = ? AND p.seller_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $pet_id, $seller_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Pet doesn't exist or doesn't belong to this seller
    header('Location: manage_pets.php');
    exit();
}

$pet = $result->fetch_assoc();

// Get pet images
$sql = "SELECT * FROM images WHERE item_type = 'pet' AND item_id = ? ORDER BY is_primary DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pet_id);
$stmt->execute();
$images = $stmt->get_result();

// Get all categories for dropdown
$sql = "SELECT * FROM categories WHERE parent_id IS NULL OR parent_id = 0 ORDER BY name";
$stmt = $conn->prepare($sql);
$stmt->execute();
$categories = $stmt->get_result();

// Initialize error array
$errors = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    $name = trim($_POST['name']);
    $category_id = (int)$_POST['category_id'];
    $breed = trim($_POST['breed']);
    $age = trim($_POST['age']);
    $gender = $_POST['gender'];
    $price = (float)$_POST['price'];
    $description = trim($_POST['description']);
    $quantity = (int)$_POST['quantity'];
    $status = $_POST['status'];

    // Validation
    if (empty($name)) {
        $errors[] = "Pet name is required";
    }

    if ($category_id <= 0) {
        $errors[] = "Please select a valid category";
    }

    if ($price <= 0) {
        $errors[] = "Price must be greater than zero";
    }

    if ($quantity <= 0) {
        $errors[] = "Quantity must be at least 1";
    }

    if (empty($description)) {
        $errors[] = "Description is required";
    }

    // If no errors, update pet listing
    if (empty($errors)) {
        $sql = "UPDATE pets SET 
                name = ?, 
                category_id = ?, 
                breed = ?, 
                age = ?, 
                gender = ?, 
                description = ?, 
                price = ?, 
                quantity = ?, 
                status = ?,
                approval_status = 'pending' 
                WHERE pet_id = ? AND seller_id = ?";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sissssdisii", 
            $name, 
            $category_id, 
            $breed, 
            $age, 
            $gender, 
            $description, 
            $price, 
            $quantity, 
            $status, 
            $pet_id, 
            $seller_id
        );
        
        if ($stmt->execute()) {
            // Handle image uploads
            $upload_dir = '../uploads/pets/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Check if any images were uploaded
            if (!empty($_FILES['images']['name'][0])) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                $maxFileSize = 5 * 1024 * 1024; // 5MB
                
                // Count existing images
                $existing_images_count = $images->num_rows;
                
                // Loop through each uploaded file
                for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
                    // Check if we reached the maximum number of images (5)
                    if ($existing_images_count + $i >= 5) {
                        break;
                    }
                    
                    if ($_FILES['images']['error'][$i] === 0) {
                        $fileType = $_FILES['images']['type'][$i];
                        $fileSize = $_FILES['images']['size'][$i];
                        
                        // Validate file type
                        if (!in_array($fileType, $allowedTypes)) {
                            $errors[] = "File type not allowed: " . $_FILES['images']['name'][$i];
                            continue;
                        }
                        
                        // Validate file size
                        if ($fileSize > $maxFileSize) {
                            $errors[] = "File too large: " . $_FILES['images']['name'][$i];
                            continue;
                        }
                        
                        // Generate unique filename
                        $filename = uniqid('pet_') . '_' . $pet_id . '_' . $_FILES['images']['name'][$i];
                        $targetFile = $upload_dir . $filename;
                        
                        // Move uploaded file
                        if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $targetFile)) {
                            // Set as primary if it's the first image and no primary exists
                            $is_primary = 0;
                            if ($i === 0 && $existing_images_count === 0) {
                                $is_primary = 1;
                            }
                            
                            // Save to database
                            $image_path = 'uploads/pets/' . $filename;
                            $sql = "INSERT INTO images (item_type, item_id, image_path, is_primary) VALUES ('pet', ?, ?, ?)";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("isi", $pet_id, $image_path, $is_primary);
                            $stmt->execute();
                        } else {
                            $errors[] = "Failed to upload: " . $_FILES['images']['name'][$i];
                        }
                    }
                }
            }
            
            // Handle primary image selection
            if (isset($_POST['primary_image']) && !empty($_POST['primary_image'])) {
                // Reset all images to non-primary
                $sql = "UPDATE images SET is_primary = 0 WHERE item_type = 'pet' AND item_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $pet_id);
                $stmt->execute();
                
                // Set the selected image as primary
                $primary_image_id = (int)$_POST['primary_image'];
                $sql = "UPDATE images SET is_primary = 1 WHERE image_id = ? AND item_type = 'pet' AND item_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $primary_image_id, $pet_id);
                $stmt->execute();
            }
            
            // Handle image deletions
            if (isset($_POST['delete_images']) && is_array($_POST['delete_images'])) {
                foreach ($_POST['delete_images'] as $image_id) {
                    // Get image path
                    $sql = "SELECT image_path, is_primary FROM images WHERE image_id = ? AND item_type = 'pet' AND item_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ii", $image_id, $pet_id);
                    $stmt->execute();
                    $image_result = $stmt->get_result();
                    
                    if ($image_result->num_rows > 0) {
                        $image_data = $image_result->fetch_assoc();
                        $was_primary = $image_data['is_primary'];
                        
                        // Delete from database
                        $sql = "DELETE FROM images WHERE image_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $image_id);
                        $stmt->execute();
                        
                        // Delete file from server
                        if (file_exists('../' . $image_data['image_path'])) {
                            unlink('../' . $image_data['image_path']);
                        }
                        
                        // If this was the primary image, set the first remaining image as primary
                        if ($was_primary) {
                            $sql = "UPDATE images SET is_primary = 1 WHERE item_type = 'pet' AND item_id = ? LIMIT 1";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $pet_id);
                            $stmt->execute();
                        }
                    }
                }
            }
            
            // Redirect with success message
            header('Location: manage_pets.php?status=success&action=edit');
            exit();
        } else {
            $errors[] = "Failed to update pet listing: " . $conn->error;
        }
    }
    
    // If there were errors, we'll continue to the form with error messages
}

// Include header
include_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <!-- Seller sidebar -->
            <?php include_once 'seller_sidebar.php'; ?>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4>Edit Pet Listing</h4>
                </div>
                <div class="card-body">
                    <!-- Display errors if any -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $pet_id); ?>" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name">Pet Name*</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($pet['name']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="category_id">Category*</label>
                                    <select class="form-control" id="category_id" name="category_id" required>
                                        <option value="">-- Select Category --</option>
                                        <?php while ($category = $categories->fetch_assoc()): ?>
                                            <option value="<?php echo $category['category_id']; ?>" <?php echo ($pet['category_id'] == $category['category_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="breed">Breed</label>
                                    <input type="text" class="form-control" id="breed" name="breed" value="<?php echo htmlspecialchars($pet['breed']); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="age">Age</label>
                                    <input type="text" class="form-control" id="age" name="age" value="<?php echo htmlspecialchars($pet['age']); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="gender">Gender</label>
                                    <select class="form-control" id="gender" name="gender">
                                        <option value="male" <?php echo ($pet['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                                        <option value="female" <?php echo ($pet['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                                        <option value="unknown" <?php echo ($pet['gender'] == 'unknown') ? 'selected' : ''; ?>>Unknown</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="price">Price (KES)*</label>
                                    <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo $pet['price']; ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="quantity">Quantity*</label>
                                    <input type="number" class="form-control" id="quantity" name="quantity" value="<?php echo $pet['quantity']; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="status">Status*</label>
                                    <select class="form-control" id="status" name="status" required>
                                        <option value="available" <?php echo ($pet['status'] == 'available') ? 'selected' : ''; ?>>Available</option>
                                        <option value="sold" <?php echo ($pet['status'] == 'sold') ? 'selected' : ''; ?>>Sold</option>
                                        <option value="pending" <?php echo ($pet['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="inactive" <?php echo ($pet['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="description">Description*</label>
                            <textarea class="form-control" id="description" name="description" rows="6" required><?php echo htmlspecialchars($pet['description']); ?></textarea>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label>Current Images</label>
                            <?php if ($images->num_rows > 0): ?>
                                <div class="row">
                                    <?php while ($image = $images->fetch_assoc()): ?>
                                        <div class="col-md-3 mb-3">
                                            <div class="card">
                                                <img src="../<?php echo $image['image_path']; ?>" class="card-img-top" alt="Pet Image">
                                                <div class="card-body">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="primary_image" id="img_<?php echo $image['image_id']; ?>" 
                                                               value="<?php echo $image['image_id']; ?>" <?php echo ($image['is_primary'] == 1) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="img_<?php echo $image['image_id']; ?>">
                                                            Primary Image
                                                        </label>
                                                    </div>
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" name="delete_images[]" 
                                                               id="del_<?php echo $image['image_id']; ?>" value="<?php echo $image['image_id']; ?>">
                                                        <label class="form-check-label text-danger" for="del_<?php echo $image['image_id']; ?>">
                                                            Delete Image
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No images uploaded yet.</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="images">Upload New Images</label>
                            <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/jpeg, image/png, image/jpg">
                            <small class="form-text text-muted">
                                You can upload up to 5 images. Maximum file size: 5MB. Allowed types: JPG, JPEG, PNG.
                                <?php 
                                $remaining = 5 - $images->num_rows;
                                echo "Remaining slots: $remaining";
                                ?>
                            </small>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Note: After editing, your pet listing will be set to "Pending Approval" status and will need to be reviewed by an administrator before it appears in search results.
                        </div>
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">Update Pet Listing</button>
                            <a href="manage_pets.php" class="btn btn-secondary ml-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Preview images before upload
document.getElementById('images').addEventListener('change', function(event) {
    // Remove existing previews
    const previewContainer = document.getElementById('image-previews');
    if (previewContainer) {
        previewContainer.remove();
    }
    
    // Create preview container
    const newPreviewContainer = document.createElement('div');
    newPreviewContainer.id = 'image-previews';
    newPreviewContainer.className = 'row mt-3';
    
    this.parentNode.appendChild(newPreviewContainer);
    
    // Create previews
    for (let i = 0; i < this.files.length; i++) {
        const file = this.files[i];
        if (!file.type.match('image.*')) {
            continue;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const col = document.createElement('div');
            col.className = 'col-md-3 mb-3';
            
            const card = document.createElement('div');
            card.className = 'card';
            
            const img = document.createElement('img');
            img.className = 'card-img-top';
            img.src = e.target.result;
            img.alt = 'Preview';
            
            const cardBody = document.createElement('div');
            cardBody.className = 'card-body';
            cardBody.innerHTML = '<small>' + file.name + '</small>';
            
            card.appendChild(img);
            card.appendChild(cardBody);
            col.appendChild(card);
            newPreviewContainer.appendChild(col);
        };
        
        reader.readAsDataURL(file);
    }
});
</script>

<?php include_once '../includes/footer.php'; ?>