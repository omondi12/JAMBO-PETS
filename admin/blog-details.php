<?php
// Include database connection
require_once 'config/db.php';

// Check if post ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: blog.php');
    exit;
}

$post_id = $_GET['id'];

// Get the blog post data
$query = "SELECT b.*, u.first_name, u.last_name FROM blog_posts b 
          INNER JOIN users u ON b.admin_id = u.user_id 
          WHERE b.post_id = ? AND b.status = 'published'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // Post not found or not published
    header('Location: blog.php');
    exit;
}

$post = $result->fetch_assoc();

// Update view count
$update_query = "UPDATE blog_posts SET views = views + 1 WHERE post_id = ?";
$stmt = $conn->prepare($update_query);
$stmt->bind_param("i", $post_id);
$stmt->execute();

// Get related posts
$related_query = "SELECT post_id, title, featured_image, published_date FROM blog_posts 
                  WHERE status = 'published' AND post_id != ? 
                  ORDER BY published_date DESC LIMIT 3";
$stmt = $conn->prepare($related_query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$related_result = $stmt->get_result();
$related_posts = [];

if ($related_result->num_rows > 0) {
    while ($row = $related_result->fetch_assoc()) {
        $related_posts[] = $row;
    }
}

// Include header
$page_title = $post['title'] . " - Jambo Pets Blog";
include '../includes/admin_header.php';
?>

<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="blog.php">Blog</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($post['title']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <article class="blog-post">
                <h1 class="mb-4"><?php echo htmlspecialchars($post['title']); ?></h1>
                
                <div class="d-flex align-items-center mb-4">
                    <div class="me-3">
                        <img src="uploads/default-profile.png" alt="Author" class="rounded-circle" width="40" height="40">
                    </div>
                    <div>
                        <p class="mb-0">
                            By <strong><?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?></strong>
                            <span class="mx-2">|</span>
                            <span><?php echo date('F d, Y', strtotime($post['published_date'])); ?></span>
                            <span class="mx-2">|</span>
                            <span><i class="fas fa-eye"></i> <?php echo $post['views']; ?> views</span>
                        </p>
                    </div>
                </div>
                
                <?php if (!empty($post['featured_image'])): ?>
                <div class="featured-image mb-4">
                    <img src="<?php echo $post['featured_image']; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="img-fluid rounded">
                </div>
                <?php endif; ?>
                
                <div class="blog-content">
                    <?php echo $post['content']; ?>
                </div>
                
                <div class="mt-5 pt-4 border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted">Share:</span>
                            <a href="https://facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="social-share ms-2">
                                <i class="fab fa-facebook"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($post['title']); ?>" target="_blank" class="social-share ms-2">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="mailto:?subject=<?php echo urlencode($post['title']); ?>&body=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" class="social-share ms-2">
                                <i class="fas fa-envelope"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </article>
        </div>
        
        <div class="col-lg-4">
            <div class="sidebar-box p-4 bg-light rounded">
                <h4 class="mb-4">Related Posts</h4>
                
                <?php if (empty($related_posts)): ?>
                    <p>No related posts found.</p>
                <?php else: ?>
                    <?php foreach ($related_posts as $related): ?>
                        <div class="related-post mb-4">
                            <div class="row g-0">
                                <?php if (!empty($related['featured_image'])): ?>
                                <div class="col-4">
                                    <img src="<?php echo $related['featured_image']; ?>" alt="<?php echo htmlspecialchars($related['title']); ?>" class="img-fluid rounded">
                                </div>
                                <div class="col-8 ps-3">
                                <?php else: ?>
                                <div class="col-12">
                                <?php endif; ?>
                                    <h6 class="mb-1"><a href="blog-details.php?id=<?php echo $related['post_id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($related['title']); ?></a></h6>
                                    <small class="text-muted"><?php echo date('M d, Y', strtotime($related['published_date'])); ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="sidebar-box p-4 bg-light rounded mt-4">
                <h4 class="mb-4">Categories</h4>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#" class="text-decoration-none">Pets</a></li>
                    <li class="mb-2"><a href="#" class="text-decoration-none">Pet Care</a></li>
                    <li class="mb-2"><a href="#" class="text-decoration-none">Pet Health</a></li>
                    <li class="mb-2"><a href="#" class="text-decoration-none">Pet Adoption</a></li>
                    <li class="mb-2"><a href="#" class="text-decoration-none">Pet Training</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include '../includes/admin_footer.php';
?>