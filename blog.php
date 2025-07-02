<?php
// Include database connection first
require_once 'config/db.php';
require_once 'includes/functions.php';

// Set page title
$pageTitle = "Pet Care Blog";
$pageDescription = "Expert advice, tips, and articles about pet care, training, health, and more for pet owners in Kenya.";

// Add debug mode - set to true to enable detailed error messages
$debugMode = false;

// Function to handle and display errors based on debug mode
function handleError($message, $sqlError = "") {
    global $debugMode;
    if ($debugMode) {
        return "<div class='alert alert-danger'>Error: $message" . 
               ($sqlError ? "<br>SQL Error: $sqlError" : "") . "</div>";
    } else {
        return "<div class='alert alert-info'>No blog posts available at the moment.</div>";
    }
}

// Include header
require_once 'includes/header.php';

// Ensure $conn is available
if (!isset($conn) || $conn === null) {
    die(showError("Database connection failed. Please check your configuration."));
}

// Log page visit
logActivity('blog');

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userType = $isLoggedIn ? $_SESSION['user_type'] : '';
$userName = $isLoggedIn ? $_SESSION['first_name'] : '';

// Initialize variables for pagination
$resultsPerPage = 9;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;
$offset = ($currentPage - 1) * $resultsPerPage;

// Handle category filter
$categoryFilter = "";
$selectedCategory = "";
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $selectedCategory = $conn->real_escape_string($_GET['category']);
    $categoryFilter = " AND categories LIKE '%$selectedCategory%' ";
}

// Fetch blog categories
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

// Count total number of published blog posts
try {
    $countQuery = "SELECT COUNT(*) as total FROM blog_posts WHERE status = 'published' $categoryFilter";
    $countResult = $conn->query($countQuery);
    $totalPosts = $countResult->fetch_assoc()['total'];
    $totalPages = ceil($totalPosts / $resultsPerPage);
} catch (Exception $e) {
    echo handleError("Error counting blog posts", $e->getMessage());
    $totalPosts = 0;
    $totalPages = 1;
}

// Fetch blog posts with pagination
try {
    $blogPostsQuery = "SELECT 
                           bp.*,
                           u.first_name,
                           u.last_name
                       FROM 
                           blog_posts bp
                       JOIN 
                           users u ON bp.admin_id = u.user_id
                       WHERE 
                           bp.status = 'published'
                           $categoryFilter
                       ORDER BY 
                           bp.published_date DESC
                       LIMIT 
                           $offset, $resultsPerPage";
    
    $blogPostsResult = $conn->query($blogPostsQuery);
    if ($blogPostsResult === false) {
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    echo handleError("Error loading blog posts", $e->getMessage());
    $blogPostsResult = false;
}

// Fetch featured blog posts for the sidebar
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
                          ORDER BY 
                              views DESC 
                          LIMIT 5";
    
    $featuredPostsResult = $conn->query($featuredPostsQuery);
    if ($featuredPostsResult === false) {
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    echo handleError("Error loading featured posts", $e->getMessage());
    $featuredPostsResult = false;
}

// Update view count for page load
try {
    // We'll implement view counting in the blog-post.php page later
} catch (Exception $e) {
    if ($debugMode) {
        echo "<!-- Debug: Error updating view count: " . $e->getMessage() . " -->";
    }
}
?>

<!-- Hero Section -->
<section class="hero-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold">Pet Care Blog</h1>
                <p class="lead mb-0">Expert advice, tips, and guides for pet owners in Kenya</p>
            </div>
        </div>
    </div>
</section>

<!-- Blog Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Category Filter -->
                <div class="mb-4">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="fw-bold me-2">Filter by:</span>
                        <a href="<?php echo BASE_URL; ?>blog.php" class="btn btn-sm <?php echo empty($selectedCategory) ? 'btn-primary' : 'btn-outline-primary'; ?>">All</a>
                        
                        <?php 
                        if ($categoriesResult && $categoriesResult->num_rows > 0) {
                            while($categoryRow = $categoriesResult->fetch_assoc()): 
                                $category = trim($categoryRow['category']);
                                if (empty($category)) continue;
                                $isActive = $selectedCategory === $category;
                        ?>
                            <a href="<?php echo BASE_URL; ?>blog.php?category=<?php echo urlencode($category); ?>" class="btn btn-sm <?php echo $isActive ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                <?php echo $category; ?>
                            </a>
                        <?php 
                            endwhile;
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Blog Posts -->
                <div class="row">
                    <?php 
                    if($blogPostsResult && $blogPostsResult->num_rows > 0) {
                        while($post = $blogPostsResult->fetch_assoc()):
                            $postImage = $post['featured_image'] ? BASE_URL . $post['featured_image'] : BASE_URL . 'assets/images/blog-placeholder.jpg';
                            $publishDate = date('M d, Y', strtotime($post['published_date']));
                            $excerpt = substr(strip_tags($post['content']), 0, 150) . '...';
                            
                            // Get categories as an array
                            $postCategories = !empty($post['categories']) ? explode(',', $post['categories']) : [];
                    ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <img src="<?php echo $postImage; ?>" class="card-img-top" alt="<?php echo $post['title']; ?>" style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <p class="card-text text-muted small mb-2">
                                        <i class="far fa-calendar-alt me-1"></i> <?php echo $publishDate; ?>
                                        <span class="ms-3"><i class="far fa-eye me-1"></i> <?php echo $post['views']; ?></span>
                                    </p>
                                    <h5 class="card-title"><?php echo $post['title']; ?></h5>
                                    
                                    <?php if(!empty($postCategories)): ?>
                                    <div class="mb-2">
                                        <?php foreach($postCategories as $cat): ?>
                                            <?php if(trim($cat)): ?>
                                            <a href="<?php echo BASE_URL; ?>blog.php?category=<?php echo urlencode(trim($cat)); ?>" class="badge bg-secondary text-decoration-none me-1">
                                                <?php echo trim($cat); ?>
                                            </a>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <p class="card-text"><?php echo $excerpt; ?></p>
                                </div>
                                <div class="card-footer bg-white border-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">By <?php echo $post['first_name'] . ' ' . $post['last_name']; ?></small>
                                        <a href="<?php echo BASE_URL; ?>blog-post.php?id=<?php echo $post['post_id']; ?>" class="btn btn-link p-0">Read More <i class="fas fa-arrow-right ms-1"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php 
                        endwhile;
                    } else {
                        echo '<div class="col-12"><div class="alert alert-info">No blog posts found. ' . 
                            (!empty($selectedCategory) ? 'Try selecting a different category or view all posts.' : '') . '</div></div>';
                    }
                    ?>
                </div>
                
                <!-- Pagination -->
                <?php if($totalPages > 1): ?>
                <nav aria-label="Blog pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo BASE_URL; ?>blog.php?page=<?php echo $currentPage - 1; ?><?php echo !empty($selectedCategory) ? '&category=' . urlencode($selectedCategory) : ''; ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        
                        <?php
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $currentPage + 2);
                        
                        if ($startPage > 1) {
                            echo '<li class="page-item"><a class="page-link" href="' . BASE_URL . 'blog.php?page=1' . (!empty($selectedCategory) ? '&category=' . urlencode($selectedCategory) : '') . '">1</a></li>';
                            if ($startPage > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }
                        
                        for ($i = $startPage; $i <= $endPage; $i++) {
                            echo '<li class="page-item ' . ($i == $currentPage ? 'active' : '') . '">
                                  <a class="page-link" href="' . BASE_URL . 'blog.php?page=' . $i . (!empty($selectedCategory) ? '&category=' . urlencode($selectedCategory) : '') . '">' . $i . '</a>
                                  </li>';
                        }
                        
                        if ($endPage < $totalPages) {
                            if ($endPage < $totalPages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="' . BASE_URL . 'blog.php?page=' . $totalPages . (!empty($selectedCategory) ? '&category=' . urlencode($selectedCategory) : '') . '">' . $totalPages . '</a></li>';
                        }
                        ?>
                        
                        <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo BASE_URL; ?>blog.php?page=<?php echo $currentPage + 1; ?><?php echo !empty($selectedCategory) ? '&category=' . urlencode($selectedCategory) : ''; ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
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
                            if ($categoriesResult) {
                                // Reset the pointer to the beginning
                                $categoriesResult->data_seek(0);
                                if ($categoriesResult->num_rows > 0) {
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

<?php
// Include footer
require_once 'includes/footer.php';
?>