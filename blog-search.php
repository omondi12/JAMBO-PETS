<?php
// Include database connection first
require_once 'config/db.php';
require_once 'includes/functions.php';

// Check if search query exists
if (!isset($_GET['q']) || empty($_GET['q'])) {
    header("Location: " . BASE_URL . "blog.php");
    exit;
}

// Get search query
$searchQuery = trim($_GET['q']);
$searchQueryEscaped = $conn->real_escape_string($searchQuery);

// Set page title
$pageTitle = "Search Results: " . htmlspecialchars($searchQuery);
$pageDescription = "Search results for '" . htmlspecialchars($searchQuery) . "' on our Pet Care Blog.";

// Add debug mode - set to true to enable detailed error messages
$debugMode = true; // Changed to true to help diagnose issues

// Function to handle and display errors based on debug mode
function handleError($message, $sqlError = "") {
    global $debugMode;
    if ($debugMode) {
        return "<div class='alert alert-danger'>Error: $message" . 
               ($sqlError ? "<br>SQL Error: $sqlError" : "") . "</div>";
    } else {
        return "<div class='alert alert-info'>No search results available at the moment.</div>";
    }
}

// Include header
require_once 'includes/header.php';

// Log search activity
logActivity('blog_search', ['query' => $searchQuery]);

// Initialize variables for pagination
$resultsPerPage = 9;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;
$offset = ($currentPage - 1) * $resultsPerPage;

// Handle category filter alongside search
$categoryFilter = "";
$selectedCategory = "";
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $selectedCategory = $conn->real_escape_string($_GET['category']);
    $categoryFilter = " AND categories LIKE '%$selectedCategory%' ";
}

// Count total number of search results
try {
    $countQuery = "SELECT COUNT(*) as total 
                   FROM blog_posts 
                   WHERE status = 'published' 
                   AND (title LIKE '%$searchQueryEscaped%' OR content LIKE '%$searchQueryEscaped%' OR categories LIKE '%$searchQueryEscaped%')
                   $categoryFilter";
    $countResult = $conn->query($countQuery);
    
    // Check if query was successful before attempting to fetch results
    if ($countResult === false) {
        throw new Exception($conn->error);
    }
    
    $totalPosts = $countResult->fetch_assoc()['total'];
    $totalPages = ceil($totalPosts / $resultsPerPage);
} catch (Exception $e) {
    echo handleError("Error counting search results", $e->getMessage());
    $totalPosts = 0;
    $totalPages = 1;
}

// Fetch blog posts matching the search query with pagination
try {
    $searchPostsQuery = "SELECT 
                             bp.*,
                             u.first_name,
                             u.last_name
                         FROM 
                             blog_posts bp
                         JOIN 
                             users u ON bp.admin_id = u.user_id
                         WHERE 
                             bp.status = 'published'
                             AND (bp.title LIKE '%$searchQueryEscaped%' OR bp.content LIKE '%$searchQueryEscaped%' OR bp.categories LIKE '%$searchQueryEscaped%')
                             $categoryFilter
                         ORDER BY 
                             bp.published_date DESC
                         LIMIT 
                             $offset, $resultsPerPage";
    
    $searchPostsResult = $conn->query($searchPostsQuery);
    if ($searchPostsResult === false) {
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    echo handleError("Error loading search results", $e->getMessage());
    $searchPostsResult = false;
}

// Fetch categories for filter options
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
    if ($categoriesResult === false) {
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    echo handleError("Error loading categories", $e->getMessage());
    $categoriesResult = false;
}

// Fetch popular blog posts for the sidebar
try {
    $popularPostsQuery = "SELECT 
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
    
    $popularPostsResult = $conn->query($popularPostsQuery);
    if ($popularPostsResult === false) {
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    echo handleError("Error loading popular posts", $e->getMessage());
    $popularPostsResult = false;
}
?>

<!-- Hero Section -->
<section class="bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="fw-bold">Search Results</h1>
                <p class="lead mb-0">Showing results for: "<?php echo htmlspecialchars($searchQuery); ?>"</p>
            </div>
        </div>
    </div>
</section>

<!-- Search Results Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Search Form -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form action="<?php echo BASE_URL; ?>blog-search.php" method="GET" class="d-flex">
                            <input type="text" class="form-control" placeholder="Search..." name="q" value="<?php echo htmlspecialchars($searchQuery); ?>" required>
                            <button class="btn btn-primary ms-2" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Search Stats -->
                <div class="mb-4">
                    <h5>Found <?php echo $totalPosts; ?> result<?php echo $totalPosts != 1 ? 's' : ''; ?></h5>
                    
                    <?php if(!empty($selectedCategory)): ?>
                    <div class="d-flex align-items-center mt-2">
                        <span class="me-2">Filtered by category:</span>
                        <span class="badge bg-secondary me-2"><?php echo $selectedCategory; ?></span>
                        <a href="<?php echo BASE_URL; ?>blog-search.php?q=<?php echo urlencode($searchQuery); ?>" class="text-decoration-none small">
                            <i class="fas fa-times me-1"></i>Remove filter
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Category Filter -->
                <?php if(!empty($selectedCategory)): ?>
                <!-- Category filter is active, show nothing here -->
                <?php else: ?>
                <div class="mb-4">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="fw-bold me-2">Filter by category:</span>
                        
                        <?php 
                        if ($categoriesResult && $categoriesResult->num_rows > 0) {
                            while($categoryRow = $categoriesResult->fetch_assoc()): 
                                $category = trim($categoryRow['category']);
                                if (empty($category)) continue;
                        ?>
                            <a href="<?php echo BASE_URL; ?>blog-search.php?q=<?php echo urlencode($searchQuery); ?>&category=<?php echo urlencode($category); ?>" class="btn btn-sm btn-outline-primary">
                                <?php echo $category; ?>
                            </a>
                        <?php 
                            endwhile;
                        }
                        ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Blog Posts Results -->
                <div class="row">
                    <?php 
                    if($searchPostsResult && $searchPostsResult->num_rows > 0) {
                        while($post = $searchPostsResult->fetch_assoc()):
                            $postImage = $post['featured_image'] ? BASE_URL . $post['featured_image'] : BASE_URL . 'assets/images/blog-placeholder.jpg';
                            $publishDate = date('M d, Y', strtotime($post['published_date']));
                            $excerpt = substr(strip_tags($post['content']), 0, 150) . '...';
                            
                            // Get categories as an array
                            $postCategories = !empty($post['categories']) ? explode(',', $post['categories']) : [];
                            
                            // Highlight search terms in title and excerpt
                            $highlightedTitle = preg_replace('/(' . preg_quote($searchQuery, '/') . ')/i', '<mark>$1</mark>', $post['title']);
                            $highlightedExcerpt = preg_replace('/(' . preg_quote($searchQuery, '/') . ')/i', '<mark>$1</mark>', $excerpt);
                    ?>
                        <div class="col-md-6 col-lg-6 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="row g-0">
                                    <div class="col-4">
                                        <img src="<?php echo $postImage; ?>" class="img-fluid rounded-start h-100" alt="<?php echo $post['title']; ?>" style="object-fit: cover;">
                                    </div>
                                    <div class="col-8">
                                        <div class="card-body">
                                            <p class="card-text text-muted small mb-2">
                                                <i class="far fa-calendar-alt me-1"></i> <?php echo $publishDate; ?>
                                                <span class="ms-2"><i class="far fa-eye me-1"></i> <?php echo $post['views']; ?></span>
                                            </p>
                                            <h5 class="card-title"><?php echo $highlightedTitle; ?></h5>
                                            
                                            <?php if(!empty($postCategories)): ?>
                                            <div class="mb-2">
                                                <?php foreach($postCategories as $cat): ?>
                                                    <?php if(trim($cat)): ?>
                                                    <a href="<?php echo BASE_URL; ?>blog-search.php?q=<?php echo urlencode($searchQuery); ?>&category=<?php echo urlencode(trim($cat)); ?>" class="badge bg-secondary text-decoration-none me-1">
                                                        <?php echo trim($cat); ?>
                                                    </a>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <p class="card-text small"><?php echo $highlightedExcerpt; ?></p>
                                            <a href="<?php echo BASE_URL; ?>blog-post.php?id=<?php echo $post['post_id']; ?>" class="btn btn-link p-0">Read More <i class="fas fa-arrow-right ms-1"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php 
                        endwhile;
                    } else {
                        echo '<div class="col-12">';
                        echo '<div class="alert alert-info">';
                        echo 'No results found for "<strong>' . htmlspecialchars($searchQuery) . '</strong>"';
                        if (!empty($selectedCategory)) {
                            echo ' in category "<strong>' . htmlspecialchars($selectedCategory) . '</strong>"';
                        }
                        echo '.<br>';
                        echo 'Try different keywords or <a href="' . BASE_URL . 'blog.php">browse all blog posts</a>.';
                        echo '</div>';
                        echo '</div>';
                    }
                    ?>
                </div>
                
                <!-- Pagination -->
                <?php if($totalPages > 1): ?>
                <nav aria-label="Search results pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo BASE_URL; ?>blog-search.php?q=<?php echo urlencode($searchQuery); ?>&page=<?php echo $currentPage - 1; ?><?php echo !empty($selectedCategory) ? '&category=' . urlencode($selectedCategory) : ''; ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        
                        <?php
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $currentPage + 2);
                        
                        if ($startPage > 1) {
                            echo '<li class="page-item"><a class="page-link" href="' . BASE_URL . 'blog-search.php?q=' . urlencode($searchQuery) . '&page=1' . (!empty($selectedCategory) ? '&category=' . urlencode($selectedCategory) : '') . '">1</a></li>';
                            if ($startPage > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }
                        
                        for ($i = $startPage; $i <= $endPage; $i++) {
                            echo '<li class="page-item ' . ($i == $currentPage ? 'active' : '') . '">
                                  <a class="page-link" href="' . BASE_URL . 'blog-search.php?q=' . urlencode($searchQuery) . '&page=' . $i . (!empty($selectedCategory) ? '&category=' . urlencode($selectedCategory) : '') . '">' . $i . '</a>
                                  </li>';
                        }
                        
                        if ($endPage < $totalPages) {
                            if ($endPage < $totalPages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="' . BASE_URL . 'blog-search.php?q=' . urlencode($searchQuery) . '&page=' . $totalPages . (!empty($selectedCategory) ? '&category=' . urlencode($selectedCategory) : '') . '">' . $totalPages . '</a></li>';
                        }
                        ?>
                        
                        <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo BASE_URL; ?>blog-search.php?q=<?php echo urlencode($searchQuery); ?>&page=<?php echo $currentPage + 1; ?><?php echo !empty($selectedCategory) ? '&category=' . urlencode($selectedCategory) : ''; ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
                
                <!-- Return to Blog -->
                <div class="text-center mt-4">
                    <a href="<?php echo BASE_URL; ?>blog.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Blog
                    </a>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4 mt-5 mt-lg-0">
                <!-- Search -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Refine Your Search</h5>
                        <form action="<?php echo BASE_URL; ?>blog-search.php" method="GET">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search..." name="q" value="<?php echo htmlspecialchars($searchQuery); ?>" required>
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
                        if($popularPostsResult && $popularPostsResult->num_rows > 0) {
                            while($popularPost = $popularPostsResult->fetch_assoc()):
                                $popularImage = $popularPost['featured_image'] ? BASE_URL . $popularPost['featured_image'] : BASE_URL . 'assets/images/blog-placeholder.jpg';
                                $popularDate = date('M d, Y', strtotime($popularPost['published_date']));
                        ?>
                        <div class="d-flex mb-3 pb-3 border-bottom">
                            <img src="<?php echo $popularImage; ?>" alt="<?php echo $popularPost['title']; ?>" class="rounded me-3" style="width: 80px; height: 60px; object-fit: cover;">
                            <div>
                                <h6 class="mb-1">
                                    <a href="<?php echo BASE_URL; ?>blog-post.php?id=<?php echo $popularPost['post_id']; ?>" class="text-decoration-none text-dark"><?php echo $popularPost['title']; ?></a>
                                </h6>
                                <p class="text-muted small mb-0">
                                    <i class="far fa-calendar-alt me-1"></i> <?php echo $popularDate; ?>
                                    <span class="ms-2"><i class="far fa-eye me-1"></i> <?php echo $popularPost['views']; ?></span>
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
                                // Reset the pointer to the beginning
                                $categoriesResult->data_seek(0);
                                if ($categoriesResult->num_rows > 0) {
                                    while($categoryRow = $categoriesResult->fetch_assoc()): 
                                        $category = trim($categoryRow['category']);
                                        if (empty($category)) continue;
                                        $isActive = $selectedCategory === $category;
                            ?>
                                <a href="<?php echo BASE_URL; ?>blog-search.php?q=<?php echo urlencode($searchQuery); ?>&category=<?php echo urlencode($category); ?>" 
                                   class="btn btn-sm <?php echo $isActive ? 'btn-secondary' : 'btn-outline-secondary'; ?> mb-1">
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

<!-- Related Content -->
<section class="py-5 bg-light">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h2 class="mb-4">Explore Our Pet Care Resources</h2>
                <p class="lead mb-4">Find more helpful articles, guides, and resources for pet owners in Kenya.</p>
                <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                    <a href="<?php echo BASE_URL; ?>blog.php" class="btn btn-primary btn-lg px-4 me-sm-3">Browse Blog</a>
                    <a href="<?php echo BASE_URL; ?>buyer/browse.php" class="btn btn-outline-primary btn-lg px-4">Shop Pets & Products</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
require_once 'includes/footer.php';
?>