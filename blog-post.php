<?php
// Include database connection first
require_once 'config/db.php';
require_once 'includes/functions.php';

// Add debug mode - set to true to enable detailed error messages
$debugMode = false;

// Function to handle and display errors based on debug mode
function handleError($message, $sqlError = "") {
    global $debugMode;
    if ($debugMode) {
        return "<div class='alert alert-danger'>Error: $message" . 
               ($sqlError ? "<br>SQL Error: $sqlError" : "") . "</div>";
    } else {
        return "<div class='alert alert-info'>The requested blog post could not be found.</div>";
    }
}

// Check if post ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Redirect to blog list if no ID is provided
    header("Location: blog.php");
    exit();
}

$postId = (int)$_GET['id'];

// Ensure $conn is available
if (!isset($conn) || $conn === null) {
    die(showError("Database connection failed. Please check your configuration."));
}

// Log page visit
logActivity('blog_post', $postId);

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userType = $isLoggedIn ? $_SESSION['user_type'] : '';
$userName = $isLoggedIn ? $_SESSION['first_name'] : '';

// Fetch blog post
try {
    $postQuery = "SELECT 
                    bp.*,
                    u.first_name,
                    u.last_name,
                    u.profile_image
                FROM 
                    blog_posts bp
                JOIN 
                    users u ON bp.admin_id = u.user_id
                WHERE 
                    bp.post_id = ? 
                    AND bp.status = 'published'";
    
    $stmt = $conn->prepare($postQuery);
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $postResult = $stmt->get_result();
    
    if ($postResult->num_rows === 0) {
        // Post not found or not published
        require_once 'includes/header.php';
        echo "<div class='container py-5'>";
        echo handleError("Blog post not found");
        echo "<div class='text-center mt-4'><a href='blog.php' class='btn btn-primary'>Back to Blog</a></div>";
        echo "</div>";
        require_once 'includes/footer.php';
        exit();
    }
    
    $post = $postResult->fetch_assoc();
    
    // Update page title with blog post title
    $pageTitle = $post['title'] . " - Pet Care Blog";
    $pageDescription = substr(strip_tags($post['content']), 0, 160) . '...';
    
    // Include header after setting page title
    require_once 'includes/header.php';
    
    // Get categories as an array
    $postCategories = !empty($post['categories']) ? explode(',', $post['categories']) : [];
    
    // Format published date
    $publishDate = date('M d, Y', strtotime($post['published_date']));
    
    // Update view count
    $updateViewsQuery = "UPDATE blog_posts SET views = views + 1 WHERE post_id = ?";
    $stmt = $conn->prepare($updateViewsQuery);
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    
    // Increment the views count in the current post data (for display purposes)
    $post['views']++;
    
} catch (Exception $e) {
    require_once 'includes/header.php';
    echo "<div class='container py-5'>";
    echo handleError("Error loading blog post", $e->getMessage());
    echo "<div class='text-center mt-4'><a href='blog.php' class='btn btn-primary'>Back to Blog</a></div>";
    echo "</div>";
    require_once 'includes/footer.php';
    exit();
}

// Fetch featured blog posts for the sidebar (excluding current post)
try {
    $featuredPostsQuery = "SELECT 
                            post_id, 
                            title, 
                            featured_image, 
                            published_date,
                            views
                        FROM 
                            blog_posts 
                        WHERE 
                            status = 'published' 
                            AND post_id != ?
                        ORDER BY 
                            views DESC 
                        LIMIT 5";
    
    $stmt = $conn->prepare($featuredPostsQuery);
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $featuredPostsResult = $stmt->get_result();
} catch (Exception $e) {
    echo handleError("Error loading featured posts", $e->getMessage());
    $featuredPostsResult = false;
}

// Fetch related posts based on categories
try {
    $relatedPosts = [];
    
    if (!empty($postCategories)) {
        $categoryConditions = [];
        foreach ($postCategories as $category) {
            if (trim($category)) {
                $categoryConditions[] = "categories LIKE '%" . $conn->real_escape_string(trim($category)) . "%'";
            }
        }
        
        if (!empty($categoryConditions)) {
            $categoryConditionsSQL = implode(' OR ', $categoryConditions);
            
            $relatedPostsQuery = "SELECT 
                                    post_id, 
                                    title, 
                                    featured_image, 
                                    published_date
                                FROM 
                                    blog_posts 
                                WHERE 
                                    status = 'published' 
                                    AND post_id != ?
                                    AND ($categoryConditionsSQL)
                                ORDER BY 
                                    published_date DESC 
                                LIMIT 3";
            
            $stmt = $conn->prepare($relatedPostsQuery);
            $stmt->bind_param("i", $postId);
            $stmt->execute();
            $relatedPostsResult = $stmt->get_result();
            
            while ($related = $relatedPostsResult->fetch_assoc()) {
                $relatedPosts[] = $related;
            }
        }
    }
} catch (Exception $e) {
    if ($debugMode) {
        echo "<!-- Debug: Error loading related posts: " . $e->getMessage() . " -->";
    }
    $relatedPosts = [];
}

// Fetch all blog categories for the sidebar
try {
    $categoriesQuery = "SELECT DISTINCT SUBSTRING_INDEX(SUBSTRING_INDEX(categories, ',', numbers.n), ',', -1) as category
                        FROM blog_posts
                        CROSS JOIN (
                            SELECT 1 as n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5
                        ) numbers
                        WHERE 
                            CHAR_LENGTH(categories) - CHAR_LENGTH(REPLACE(categories, ',', '')) >= numbers.n - 1
                            AND status = 'published'
                        ORDER BY category ASC";
    $categoriesResult = $conn->query($categoriesQuery);
} catch (Exception $e) {
    echo handleError("Error loading categories", $e->getMessage());
    $categoriesResult = false;
}

// Prepare author information
$authorImage = $post['profile_image'] ? BASE_URL . 'uploads/' . $post['profile_image'] : BASE_URL . 'assets/images/profile-placeholder.jpg';
$authorName = $post['first_name'] . ' ' . $post['last_name'];

// Prepare featured image
$featuredImage = $post['featured_image'] ? BASE_URL . $post['featured_image'] : BASE_URL . 'assets/images/blog-placeholder.jpg';
?>

<!-- Blog Post Detail -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Back to Blog Link -->
                <div class="mb-4">
                    <a href="<?php echo BASE_URL; ?>blog.php" class="text-decoration-none">
                        <i class="fas fa-arrow-left me-2"></i> Back to Blog
                    </a>
                </div>
                
                <!-- Blog Post -->
                <article class="blog-post">
                    <!-- Post Header -->
                    <header class="mb-4">
                        <h1 class="display-5 fw-bold mb-3"><?php echo $post['title']; ?></h1>
                        
                        <div class="d-flex flex-wrap align-items-center mb-4">
                            <div class="d-flex align-items-center me-4">
                                <img src="<?php echo $authorImage; ?>" alt="<?php echo $authorName; ?>" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                <span>By <?php echo $authorName; ?></span>
                            </div>
                            <div class="me-4">
                                <i class="far fa-calendar-alt me-1"></i> <?php echo $publishDate; ?>
                            </div>
                            <div>
                                <i class="far fa-eye me-1"></i> <?php echo number_format($post['views']); ?> views
                            </div>
                        </div>
                        
                        <?php if(!empty($postCategories)): ?>
                        <div class="mb-4">
                            <?php foreach($postCategories as $cat): ?>
                                <?php if(trim($cat)): ?>
                                <a href="<?php echo BASE_URL; ?>blog.php?category=<?php echo urlencode(trim($cat)); ?>" class="badge bg-secondary text-decoration-none me-1 mb-1">
                                    <?php echo trim($cat); ?>
                                </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </header>
                    
                    <!-- Featured Image -->
                    <div class="mb-4">
                        <img src="<?php echo $featuredImage; ?>" alt="<?php echo $post['title']; ?>" class="img-fluid rounded shadow" style="max-height: 500px; width: 100%; object-fit: cover;">
                    </div>
                    
                    <!-- Post Content -->
                    <div class="blog-content mb-5">
                        <?php echo $post['content']; ?>
                    </div>
                    
                    <!-- Social Share -->
                    <div class="card border-0 shadow-sm p-4 mb-5">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0 me-3">Share this post:</h5>
                            <div class="d-flex gap-2">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(BASE_URL . 'blog-post.php?id=' . $postId); ?>" target="_blank" class="btn btn-outline-primary" aria-label="Share on Facebook">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($post['title']); ?>&url=<?php echo urlencode(BASE_URL . 'blog-post.php?id=' . $postId); ?>" target="_blank" class="btn btn-outline-info" aria-label="Share on Twitter">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="https://wa.me/?text=<?php echo urlencode($post['title'] . ' - ' . BASE_URL . 'blog-post.php?id=' . $postId); ?>" target="_blank" class="btn btn-outline-success" aria-label="Share on WhatsApp">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                                <a href="mailto:?subject=<?php echo urlencode($post['title']); ?>&body=<?php echo urlencode("Check out this blog post: " . BASE_URL . 'blog-post.php?id=' . $postId); ?>" class="btn btn-outline-secondary" aria-label="Share via Email">
                                    <i class="fas fa-envelope"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Related Posts -->
                    <?php if (!empty($relatedPosts)): ?>
                    <div class="related-posts mb-5">
                        <h3 class="fw-bold mb-4">Related Posts</h3>
                        <div class="row">
                            <?php foreach ($relatedPosts as $related): 
                                $relatedImage = $related['featured_image'] ? BASE_URL . $related['featured_image'] : BASE_URL . 'assets/images/blog-placeholder.jpg';
                                $relatedDate = date('M d, Y', strtotime($related['published_date']));
                            ?>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 border-0 shadow-sm">
                                    <img src="<?php echo $relatedImage; ?>" class="card-img-top" alt="<?php echo $related['title']; ?>" style="height: 150px; object-fit: cover;">
                                    <div class="card-body">
                                        <p class="text-muted small mb-2">
                                            <i class="far fa-calendar-alt me-1"></i> <?php echo $relatedDate; ?>
                                        </p>
                                        <h5 class="card-title h6"><?php echo $related['title']; ?></h5>
                                        <a href="<?php echo BASE_URL; ?>blog-post.php?id=<?php echo $related['post_id']; ?>" class="btn btn-link p-0">Read More <i class="fas fa-arrow-right ms-1"></i></a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </article>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4 mt-5 mt-lg-0">
                <!-- Search -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Search Articles</h5>
                        <form action="<?php echo BASE_URL; ?>blog-search.php" method="GET">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search..." name="q" required>
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Popular Posts -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Popular Posts</h5>
                        
                        <?php 
                        if($featuredPostsResult && $featuredPostsResult->num_rows > 0) {
                            while($featuredPost = $featuredPostsResult->fetch_assoc()):
                                $featuredImage = $featuredPost['featured_image'] ? BASE_URL . $featuredPost['featured_image'] : BASE_URL . 'assets/images/blog-placeholder.jpg';
                                $featuredDate = date('M d, Y', strtotime($featuredPost['published_date']));
                        ?>
                        <div class="d-flex mb-3 pb-3 border-bottom">
                            <img src="<?php echo $featuredImage; ?>" alt="<?php echo $featuredPost['title']; ?>" class="rounded me-3" style="width: 80px; height: 60px; object-fit: cover;">
                            <div>
                                <h6 class="mb-1">
                                    <a href="<?php echo BASE_URL; ?>blog-post.php?id=<?php echo $featuredPost['post_id']; ?>" class="text-decoration-none text-dark"><?php echo $featuredPost['title']; ?></a>
                                </h6>
                                <p class="text-muted small mb-0">
                                    <i class="far fa-calendar-alt me-1"></i> <?php echo $featuredDate; ?>
                                    <span class="ms-2"><i class="far fa-eye me-1"></i> <?php echo $featuredPost['views']; ?></span>
                                </p>
                            </div>
                        </div>
                        <?php 
                            endwhile;
                        } else {
                            echo '<p class="text-muted">No popular posts available yet.</p>';
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Categories -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Categories</h5>
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <?php 
                            if ($categoriesResult && $categoriesResult->num_rows > 0) {
                                while($categoryRow = $categoriesResult->fetch_assoc()): 
                                    $category = trim($categoryRow['category']);
                                    if (empty($category)) continue;
                            ?>
                                <a href="<?php echo BASE_URL; ?>blog.php?category=<?php echo urlencode($category); ?>" class="btn btn-sm btn-outline-secondary mb-1">
                                    <?php echo $category; ?>
                                </a>
                            <?php 
                                endwhile;
                            } else {
                                echo '<p class="text-muted">No categories available.</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
                
                <!-- Subscribe -->
                <div class="card border-0 shadow-sm mb-4 bg-light">
                    <div class="card-body text-center p-4">
                        <h5 class="card-title">Subscribe to Our Newsletter</h5>
                        <p class="card-text">Get the latest pet care tips and updates delivered to your inbox.</p>
                        <form action="<?php echo BASE_URL; ?>newsletter-subscribe.php" method="POST" class="mt-3">
                            <div class="mb-3">
                                <input type="email" class="form-control" placeholder="Your Email Address" name="email" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Subscribe</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-light">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h2 class="mb-4">Looking for a New Pet?</h2>
                <p class="lead mb-4">Browse our selection of pets from trusted breeders and sellers across Kenya.</p>
                <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                    <a href="<?php echo BASE_URL; ?>buyer/browse.php" class="btn btn-primary btn-lg px-4 me-sm-3">Browse Pets</a>
                    <a href="<?php echo BASE_URL; ?>buyer/browse.php?type=product" class="btn btn-outline-primary btn-lg px-4">Shop Products</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Update blog_posts table schema to add categories column -->
<?php
// NOTE: You would typically run this in a database migration script, not on a live page.
// This is just for showing the schema change needed.
if ($debugMode) {
    try {
        $alterTableSQL = "ALTER TABLE blog_posts ADD COLUMN IF NOT EXISTS categories VARCHAR(255) DEFAULT NULL AFTER content";
        //$conn->query($alterTableSQL);
        echo "<!-- Debug: Table blog_posts updated to include categories column -->";
    } catch (Exception $e) {
        echo "<!-- Debug: Error updating table schema: " . $e->getMessage() . " -->";
    }
}
?>

<?php
// Include footer
require_once 'includes/footer.php';
?>