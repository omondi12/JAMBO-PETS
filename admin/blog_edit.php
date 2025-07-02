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

// Check if post ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: blog.php');
    exit;
}

$post_id = $_GET['id'];

// Get the blog post data
$query = "SELECT * FROM blog_posts WHERE post_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // Post not found
    header('Location: blog.php');
    exit;
}

$post = $result->fetch_assoc();

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
        // Handle file upload if new image is provided
        $featured_image = $post['featured_image']; // Keep existing image by default
        
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
                    // If there was an old image, delete it (optional)
                    if (!empty($post['featured_image']) && file_exists('../' . $post['featured_image'])) {
                        unlink('../' . $post['featured_image']);
                    }
                    
                    $featured_image = 'uploads/blog/' . $file_name;
                } else {
                    $error = "Failed to upload image.";
                }
            }
        }
        
        if (empty($error)) {
            // Update blog post in database
            $query = "UPDATE blog_posts SET title = ?, content = ?, featured_image = ?, status = ? 
                      WHERE post_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssi", $title, $content, $featured_image, $status, $post_id);
            
            if ($stmt->execute()) {
                $success = "Blog post updated successfully.";
                
                // Refresh post data after update
                $query = "SELECT * FROM blog_posts WHERE post_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $post_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $post = $result->fetch_assoc();
            } else {
                $error = "Error updating blog post: " . $conn->error;
            }
        }
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Blog Post</h1>
        <a href="blog.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Blog Posts
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Edit Blog Post</h5>
        </div>
        <div class="card-body">
            <form action="blog_edit.php?id=<?php echo $post_id; ?>" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="title" class="form-label">Title *</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="featured_image" class="form-label">Featured Image</label>
                    <?php if (!empty($post['featured_image'])): ?>
                        <div class="mb-2">
                            <img src="../<?php echo $post['featured_image']; ?>" alt="Featured Image" class="img-thumbnail" style="max-height: 200px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/*">
                    <small class="text-muted">Leave empty to keep current image. Recommended size: 1200x630 pixels</small>
                </div>
                
                <div class="mb-3">
                    <label for="content" class="form-label">Content *</label>
                    <textarea class="form-control" id="content" name="content" rows="10" required><?php echo htmlspecialchars($post['content']); ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-control" id="status" name="status">
                        <option value="draft" <?php echo $post['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                        <option value="published" <?php echo $post['status'] == 'published' ? 'selected' : ''; ?>>Published</option>
                    </select>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="blog.php" class="btn btn-light me-md-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Post</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include TinyMCE for rich text editing -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#content',
        height: 500,
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
        toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
    });
</script>

<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>