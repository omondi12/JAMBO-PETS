<?php
// Include database connection first
require_once 'config/db.php';
require_once 'includes/functions.php';

// Set page title
$pageTitle = "Home";
$pageDescription = "Kenya's premier online marketplace for pets and pet products. Find dogs, cats, birds, and more from trusted breeders and sellers.";

 
$debugMode = true;

// Function to handle and display errors based on debug mode
function handleError($message, $sqlError = "") {
    global $debugMode;
    if ($debugMode) {
        return "<div class='alert alert-danger'>Error: $message" . 
               ($sqlError ? "<br>SQL Error: $sqlError" : "") . "</div>";
    } else {
        return "<div class='alert alert-info'>No items available at the moment.</div>";
    }
}

 
require_once 'includes/header.php';

// Ensure $conn is available
if (!isset($conn) || $conn === null) {
    die(showError("Database connection failed. Please check your configuration."));
}

// Log page visit
logActivity('homepage');

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userType = $isLoggedIn ? $_SESSION['user_type'] : '';
$userName = $isLoggedIn ? $_SESSION['first_name'] : '';


// IMPROVED: Fetch featured pets with better error handling
$featuredPetsQuery = "SELECT p.*, i.image_path, u.county, sp.business_name, sp.rating 
                     FROM pets p 
                     LEFT JOIN users u ON p.seller_id = u.user_id 
                     LEFT JOIN seller_profiles sp ON p.seller_id = sp.seller_id 
                     LEFT JOIN (
                         SELECT item_id, image_path 
                         FROM images 
                         WHERE item_type = 'pet' AND is_primary = 1
                     ) i ON p.pet_id = i.item_id 
                     WHERE p.featured = 1 
                     AND p.status = 'available' 
                     AND p.approval_status = 'approved' 
                     ORDER BY p.created_at DESC LIMIT 6";

// Debug: Print the query if in debug mode
if ($debugMode) {
    echo "<!-- Debug: Featured Pets Query: " . htmlspecialchars($featuredPetsQuery) . " -->";
}

// Initialize result variables
$featuredPetsResult = false;
$featuredProductsResult = false;
$categoriesResult = false;
$blogPostsResult = false;

try {
    $featuredPetsResult = $conn->query($featuredPetsQuery);
    if ($featuredPetsResult === false) {
        throw new Exception($conn->error);
    }
    
    // Debug: Count results
    if ($debugMode) {
        echo "<!-- Debug: Found " . $featuredPetsResult->num_rows . " featured pets -->";
    }
    
} catch (Exception $e) {
    echo handleError("Error loading featured pets", $e->getMessage());
    // No need to create an empty result set - we'll handle null checks later
}

// IMPROVED: Fetch featured products with better error handling
$featuredProductsQuery = "SELECT p.*, i.image_path, u.county, sp.business_name, sp.rating 
                         FROM products p 
                         LEFT JOIN users u ON p.seller_id = u.user_id 
                         LEFT JOIN seller_profiles sp ON p.seller_id = sp.seller_id 
                         LEFT JOIN (
                             SELECT item_id, image_path 
                             FROM images 
                             WHERE item_type = 'product' AND is_primary = 1
                         ) i ON p.product_id = i.item_id 
                         WHERE p.featured = 1 
                         AND p.status = 'available' 
                         AND p.approval_status = 'approved' 
                         ORDER BY p.created_at DESC LIMIT 6";

// Debug: Print the query if in debug mode
if ($debugMode) {
    echo "<!-- Debug: Featured Products Query: " . htmlspecialchars($featuredProductsQuery) . " -->";
}

try {
    $featuredProductsResult = $conn->query($featuredProductsQuery);
    if ($featuredProductsResult === false) {
        throw new Exception($conn->error);
    }
    
    // Debug: Count results
    if ($debugMode) {
        echo "<!-- Debug: Found " . $featuredProductsResult->num_rows . " featured products -->";
    }
    
} catch (Exception $e) {
    echo handleError("Error loading featured products", $e->getMessage());
    // No need to create an empty result set - we'll handle null checks later
}

// IMPROVED: Add direct database check for featured items
if ($debugMode) {
    try {
        // Check if any pets are marked as featured
        $checkFeaturedQuery = "SELECT COUNT(*) AS featured_count FROM pets WHERE featured = 1";
        $featuredCount = $conn->query($checkFeaturedQuery);
        if ($featuredCount !== false) {
            $row = $featuredCount->fetch_assoc();
            echo "<!-- Debug: Total pets marked as featured in database: " . $row['featured_count'] . " -->";
        }
        
        // Check if any featured pets are available and approved
        $checkAvailableQuery = "SELECT COUNT(*) AS available_count FROM pets 
                               WHERE featured = 1 
                               AND status = 'available' 
                               AND approval_status = 'approved'";
        $availableCount = $conn->query($checkAvailableQuery);
        if ($availableCount !== false) {
            $row = $availableCount->fetch_assoc();
            echo "<!-- Debug: Featured pets that are available and approved: " . $row['available_count'] . " -->";
        }
        
        // Check similar counts for products
        $checkFeaturedProductsQuery = "SELECT COUNT(*) AS featured_count FROM products WHERE featured = 1";
        $featuredProductsCount = $conn->query($checkFeaturedProductsQuery);
        if ($featuredProductsCount !== false) {
            $row = $featuredProductsCount->fetch_assoc();
            echo "<!-- Debug: Total products marked as featured in database: " . $row['featured_count'] . " -->";
        }
        
        $checkAvailableProductsQuery = "SELECT COUNT(*) AS available_count FROM products 
                                       WHERE featured = 1 
                                       AND status = 'available' 
                                       AND approval_status = 'approved'";
        $availableProductsCount = $conn->query($checkAvailableProductsQuery);
        if ($availableProductsCount !== false) {
            $row = $availableProductsCount->fetch_assoc();
            echo "<!-- Debug: Featured products that are available and approved: " . $row['available_count'] . " -->";
        }
    } catch (Exception $e) {
        echo "<!-- Debug: Error checking featured items directly: " . $e->getMessage() . " -->";
    }
}

// Fetch pet categories
$categoriesQuery = "SELECT * FROM categories WHERE parent_id IS NULL AND is_active = 1 ORDER BY name ASC";
try {
    $categoriesResult = $conn->query($categoriesQuery);
    if ($categoriesResult === false) {
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    echo handleError("Error loading categories", $e->getMessage());
    // No need to create an empty result set
}

// Fetch recent blog posts
$blogPostsQuery = "SELECT * FROM blog_posts WHERE status = 'published' ORDER BY published_date DESC LIMIT 3";
try {
    $blogPostsResult = $conn->query($blogPostsQuery);
    if ($blogPostsResult === false) {
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    echo handleError("Error loading blog posts", $e->getMessage());
    // No need to create an empty result set
}
?>
<style>
    h2 {
  font-size: 2.5rem;
  color: var(--text-dark);
  position: relative;
}

h2::after {
  content: '';
  position: absolute;
  bottom: -8px;
  left: 0;
  width: 100%;
  height: 4px;
  background: var(--gradient-primary);
  border-radius: 2px;
}

.text-center h2::after {
  left: 50%;
  transform: translateX(-50%);
}
</style>
<!-- Hero Section -->
<section class="hero-section bg-primary text-white py-5 shadow">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Find Your Perfect Pet in Kenya</h1>
                <p class="lead mb-4">Jambo Pets connects you with trusted breeders and sellers across Kenya. Browse pets, accessories, and more!</p>
                <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                    <a href="<?php echo BASE_URL; ?>buyer/browse.php" class="btn btn-light btn-lg px-4 me-md-2">Browse Pets</a>
                    <a href="<?php echo BASE_URL; ?>buyer/browse.php?type=product" class="btn btn-outline-light btn-lg px-4">Shop Products</a>
                </div>
            </div>
            <div class="col-lg-6 mt-5 mt-lg-0">
                <img src="<?php echo BASE_URL; ?>uploads/pets/1746600173_doberman.jpeg" alt="Happy pets" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</section>

<!-- Search Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="card shadow border-0">
            <div class="card-body p-4">
                <h2 class="text-center mb-4">Find Pets Near You</h2>
                <form action="<?php echo BASE_URL; ?>buyer/browse.php" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <select name="category" class="form-select form-select-lg">
                            <option value="">All Pet Types</option>
                            <?php 
                            if ($categoriesResult && $categoriesResult->num_rows > 0) {
                                while($category = $categoriesResult->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $category['category_id']; ?>"><?php echo $category['name']; ?></option>
                            <?php 
                                endwhile;
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select name="county" class="form-select form-select-lg">
                            <option value="">All Counties</option>
                            <?php
                            try {
                                $countiesQuery = "SELECT DISTINCT county FROM users WHERE county IS NOT NULL ORDER BY county ASC";
                                $countiesResult = $conn->query($countiesQuery);
                                if ($countiesResult !== false) {
                                    while($county = $countiesResult->fetch_assoc()):
                                    ?>
                                        <option value="<?php echo $county['county']; ?>"><?php echo $county['county']; ?></option>
                                    <?php 
                                    endwhile;
                                }
                            } catch (Exception $e) {
                                if ($debugMode) {
                                    echo "<!-- Debug: Error loading counties: " . $e->getMessage() . " -->";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary btn-lg w-100">Search</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Featured Pets Section -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Featured Pets</h2>
            <a href="<?php echo BASE_URL; ?>buyer/browse.php" class="btn btn-outline-primary">View All</a>
        </div>
        
        <div class="row">
            <?php 
            if($featuredPetsResult && $featuredPetsResult->num_rows > 0) {
                while($pet = $featuredPetsResult->fetch_assoc()):
                    $imagePath = $pet['image_path'] ? $pet['image_path'] : BASE_URL . 'assets/images/pet-placeholder.jpg';
            ?>
                <div class="col-md-4 col-lg-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <img src="<?php echo $imagePath; ?>" class="card-img-top" alt="<?php echo $pet['name']; ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $pet['name']; ?></h5>
                            <p class="card-text mb-1">
                                <span class="badge bg-info"><?php echo $pet['breed']; ?></span>
                                <span class="badge bg-secondary"><?php echo $pet['gender']; ?></span>
                                <span class="badge bg-success"><?php echo $pet['age']; ?></span>
                            </p>
                            <p class="card-text text-muted small mb-2">
                                <i class="fas fa-map-marker-alt"></i> <?php echo $pet['county'] ?: 'Location not specified'; ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-primary"><?php echo formatPrice($pet['price']); ?></span>
                                <a href="<?php echo BASE_URL; ?>buyer/pet.php?id=<?php echo $pet['pet_id']; ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php 
                endwhile;
            } else {
                if ($debugMode) {
                    echo '<div class="col-12"><div class="alert alert-warning">No featured pets found. Make sure you have pets that are:<br>
                        1. Marked as featured = 1<br>
                        2. With status = "available"<br>
                        3. With approval_status = "approved"</div></div>';
                } else {
                    echo '<div class="col-12"><div class="alert alert-info">No featured pets available at the moment.</div></div>';
                }
            }
            ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Featured Products</h2>
            <a href="<?php echo BASE_URL; ?>buyer/browse.php?type=product" class="btn btn-outline-primary">View All</a>
        </div>
        
        <div class="row">
            <?php 
            if($featuredProductsResult && $featuredProductsResult->num_rows > 0) {
                while($product = $featuredProductsResult->fetch_assoc()):
                    $imagePath = $product['image_path'] ?  $product['image_path'] : BASE_URL . 'assets/images/product-placeholder.jpg';
            ?>
                <div class="col-md-4 col-lg-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <img src="<?php echo $imagePath; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $product['name']; ?></h5>
                            <p class="card-text text-muted small mb-2">
                                <i class="fas fa-map-marker-alt"></i> <?php echo $product['county'] ?: 'Location not specified'; ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-primary"><?php echo formatPrice($product['price']); ?></span>
                                <a href="<?php echo BASE_URL; ?>buyer/product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php 
                endwhile;
            } else {
                if ($debugMode) {
                    echo '<div class="col-12"><div class="alert alert-warning">No featured products found. Make sure you have products that are:<br>
                        1. Marked as featured = 1<br>
                        2. With status = "available"<br>
                        3. With approval_status = "approved"</div></div>';
                } else {
                    echo '<div class="col-12"><div class="alert alert-info">No featured products available at the moment.</div></div>';
                }
            }
            ?>
        </div>
    </div>
</section>

<!-- Rest of the page content remains the same -->
<!-- How It Works Section -->
<!-- Fixed condition: Show this section ONLY when a user is NOT logged in -->
<?php if(!$isLoggedIn): ?>
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">How It Works</h2>
        
        <div class="row g-4">
            <!-- For Buyers -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h3 class="card-title">For Pet Buyers</h3>
                        <div class="d-flex align-items-start mb-4">
                            <div class="bg-primary text-white rounded-circle p-3 me-3">
                                <i class="fas fa-search"></i>
                            </div>
                            <div>
                                <h5>1. Browse & Find</h5>
                                <p class="text-muted">Search pets by type, location, and price to find your perfect companion.</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-start mb-4">
                            <div class="bg-primary text-white rounded-circle p-3 me-3">
                                <i class="fas fa-comments"></i>
                            </div>
                            <div>
                                <h5>2. Contact Seller</h5>
                                <p class="text-muted">Connect directly with trusted breeders and sellers.</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-start">
                            <div class="bg-primary text-white rounded-circle p-3 me-3">
                                <i class="fas fa-home"></i>
                            </div>
                            <div>
                                <h5>3. Welcome Home</h5>
                                <p class="text-muted">Finalize the purchase and welcome your new pet home!</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 p-4">
                        <a href="<?php echo BASE_URL; ?>auth/register.php" class="btn btn-primary w-100">Register as a Buyer</a>
                    </div>
                </div>
            </div>
            
            <!-- For Sellers -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h3 class="card-title">For Pet Sellers</h3>
                        <div class="d-flex align-items-start mb-4">
                            <div class="bg-success text-white rounded-circle p-3 me-3">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div>
                                <h5>1. Create Account</h5>
                                <p class="text-muted">Sign up as a seller and verify your profile.</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-start mb-4">
                            <div class="bg-success text-white rounded-circle p-3 me-3">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <div>
                                <h5>2. List Your Pets</h5>
                                <p class="text-muted">Add detailed information and photos of your pets or products.</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-start">
                            <div class="bg-success text-white rounded-circle p-3 me-3">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <div>
                                <h5>3. Connect with Buyers</h5>
                                <p class="text-muted">Receive inquiries and sell to responsible pet owners.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 p-4">
                        <a href="<?php echo BASE_URL; ?>auth/register.php?type=seller" class="btn btn-success w-100">Register as a Seller</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
<!-- Blog Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Pet Care Tips</h2>
            <a href="<?php echo BASE_URL; ?>blog.php" class="btn btn-outline-primary">Read All Articles</a>
        </div>
        
        <div class="row">
            <?php 
            if($blogPostsResult && $blogPostsResult->num_rows > 0) {
                while($post = $blogPostsResult->fetch_assoc()):
                    $postImage = $post['featured_image'] ? BASE_URL . $post['featured_image'] : BASE_URL . 'assets/images/blog-placeholder.jpg';
                    $publishDate = date('M d, Y', strtotime($post['published_date']));
                    $excerpt = substr(strip_tags($post['content']), 0, 120) . '...';
            ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <img src="<?php echo $postImage; ?>" class="card-img-top" alt="<?php echo $post['title']; ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <p class="card-text text-muted small mb-2"><?php echo $publishDate; ?></p>
                            <h5 class="card-title"><?php echo $post['title']; ?></h5>
                            <p class="card-text"><?php echo $excerpt; ?></p>
                        </div>
                        <div class="card-footer bg-white border-0">
                            <a href="<?php echo BASE_URL; ?>blog-post.php?id=<?php echo $post['post_id']; ?>" class="btn btn-link p-0">Read More <i class="fas fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            <?php 
                endwhile;
            } else {
                echo '<div class="col-12"><div class="alert alert-info">No blog posts available at the moment.</div></div>';
            }
            ?>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">What Our Users Say</h2>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="card-text mb-4">"I found my perfect German Shepherd puppy through Jambo Pets. The process was so easy and the seller was verified which gave me peace of mind."</p>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">JM</div>
                            <div>
                                <h6 class="mb-0">James Mwangi</h6>
                                <small class="text-muted">Nairobi</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="card-text mb-4">"As a breeder, Jambo Pets has helped me connect with serious buyers. The platform is easy to use and I've sold all my puppies within weeks!"</p>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">WK</div>
                            <div>
                                <h6 class="mb-0">Wangari Kamau</h6>
                                <small class="text-muted">Nakuru</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star-half-alt text-warning"></i>
                        </div>
                        <p class="card-text mb-4">"The variety of pet accessories on Jambo Pets is amazing! I found everything I needed for my new kitten in one place with great prices."</p>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">LO</div>
                            <div>
                                <h6 class="mb-0">Lucy Odhiambo</h6>
                                <small class="text-muted">Mombasa</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
 
<section class="hero-section py-5 bg-primary text-white">
    <div class="container text-center">
        <h2 class="mb-4">Ready to Find Your Perfect Pet?</h2>
        <p class="lead mb-4">Join thousands of happy pet owners across Kenya who found their companions through Jambo Pets.</p>
        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
            <a href="<?php echo BASE_URL; ?>buyer/browse.php" class="btn btn-light btn-lg px-4 me-sm-3">Browse Pets</a>
            <?php if(!$isLoggedIn): ?>
            <a href="<?php echo BASE_URL; ?>auth/register.php" class="btn btn-outline-light btn-lg px-4">Sign Up Now</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
// Include footer
require_once 'includes/footer.php';
?>
