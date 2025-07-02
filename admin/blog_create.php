<?php
// Include database connection and header
require_once '../config/db.php';
require_once '../includes/admin_header.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

$admin_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $title = trim($_POST['title']);
    $content = $_POST['content'];
    $status = $_POST['status'];
    
    // Validate form data
    if (empty($title) || empty($content)) {
        $error = "Title and content are required fields.";
    } else {
        // Handle file upload if image is provided
        $featured_image = '';
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['size'] > 0) {
            $upload_dir = '../uploads/blog/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_ext = pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION);
            $file_name = 'blog_' . time() . '.' . $file_ext;
            $target_file = $upload_dir . $file_name;
            
            // Check if file is an actual image
            $check = getimagesize($_FILES['featured_image']['tmp_name']);
            if ($check === false) {
                $error = "File is not an image.";
            } else {
                if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_file)) {
                    $featured_image = 'uploads/blog/' . $file_name;
                } else {
                    $error = "Failed to upload image.";
                }
            }
        }
        
        if (empty($error)) {
            try {
                // Insert blog post into database
                $query = "INSERT INTO blog_posts (admin_id, title, content, featured_image, status) 
                          VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("issss", $admin_id, $title, $content, $featured_image, $status);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Blog post created successfully.";
                    header('Location: blog.php');
                    exit;
                } else {
                    $error = "Error creating blog post: " . $conn->error;
                }
            } catch (Exception $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create New Blog Post</h1>
        <a href="blog.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Blog Posts
        </a>
    </div>

    <div id="error-container">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">New Blog Post</h5>
        </div>
        <div class="card-body">
            <form id="blogForm" action="blog_create.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="title" class="form-label">Title *</label>
                    <input type="text" class="form-control" id="title" name="title" required 
                           value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                </div>
                
                <div class="mb-3">
                    <label for="featured_image" class="form-label">Featured Image</label>
                    <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/*">
                    <small class="text-muted">Recommended size: 1200x630 pixels</small>
                </div>
                
                <div class="mb-3">
                    <label for="content" class="form-label">Content *</label>
                    <textarea class="form-control" id="content" name="content" rows="10" required><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-control" id="status" name="status">
                        <option value="draft" <?php echo (isset($_POST['status']) && $_POST['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                        <option value="published" <?php echo (isset($_POST['status']) && $_POST['status'] == 'published') ? 'selected' : ''; ?>>Published</option>
                    </select>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="reset" class="btn btn-light me-md-2">Clear</button>
                    <button type="submit" id="submitBtn" class="btn btn-primary">Create Post</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include TinyMCE for rich text editing -->
<script src="https://cdn.tiny.cloud/1/qqbmt2o17xf0y1cpchuum84c79xyqo0cky49ovadodp0iyad/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize TinyMCE
        tinymce.init({
            selector: '#content',
            height: 500,
            plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
            // Add this to ensure content is updated in the form element
            setup: function(editor) {
                editor.on('change', function() {
                    editor.save();
                });
            }
        });

        // Add form submission handling
        document.getElementById('blogForm').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            
            // Make sure TinyMCE content is synchronized
            if (tinymce.get('content')) {
                tinymce.get('content').save();
            }
            
            const content = document.getElementById('content').value.trim();
            
            // Client-side validation
            if (title === '' || content === '') {
                e.preventDefault();
                
                const errorDiv = document.getElementById('error-container');
                errorDiv.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Title and content are required fields.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                
                return false;
            }
            
            // Disable the submit button to prevent multiple submissions
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtn').innerHTML = 'Creating Post...';
            
            // Let the form submit
            return true;
        });
    });
</script>

<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>