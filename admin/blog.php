<?php
// Include database connection and header
require_once '../config/db.php';
require_once '../includes/admin_header.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Get all blog posts from the database
$query = "SELECT b.*, u.first_name, u.last_name FROM blog_posts b 
          INNER JOIN users u ON b.admin_id = u.user_id 
          ORDER BY b.published_date DESC";
$result = $conn->query($query);
$blog_posts = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $blog_posts[] = $row;
    }
}

// Handle post deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $post_id = $_GET['delete'];
    
    // Delete the post
    $delete_query = "DELETE FROM blog_posts WHERE post_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $post_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Blog post deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Error deleting blog post.";
    }
    
    // Redirect to refresh the page
    header('Location: blog.php');
    exit;
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Blog Posts Management</h1>
        <a href="blog_create.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Post
        </a>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success_message']; 
            unset($_SESSION['success_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['error_message']; 
            unset($_SESSION['error_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">All Blog Posts</h5>
        </div>
        <div class="card-body">
            <?php if (empty($blog_posts)): ?>
                <p class="text-center">No blog posts found. Start creating your first blog post!</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Views</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($blog_posts as $post): ?>
                                <tr>
                                    <td><?php echo $post['post_id']; ?></td>
                                    <td><?php echo htmlspecialchars($post['title']); ?></td>
                                    <td><?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($post['published_date'])); ?></td>
                                    <td>
                                        <?php if ($post['status'] == 'published'): ?>
                                            <span class="badge bg-success">Published</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $post['views']; ?></td>
                                    <td>
                                        <a href="../blog-details.php?id=<?php echo $post['post_id']; ?>" target="_blank" class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="blog_edit.php?id=<?php echo $post['post_id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $post['post_id']; ?>)" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function confirmDelete(postId) {
    if (confirm('Are you sure you want to delete this blog post? This action cannot be undone.')) {
        window.location.href = 'blog.php?delete=' + postId;
    }
}
</script>

<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>