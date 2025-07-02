<?php
// Set page title based on what's being browsed
$type = isset($_GET['type']) && $_GET['type'] === 'product' ? 'product' : 'pet';
$pageTitle = $type === 'product' ? "Shop Products" : "Browse Pets";

// Include header
require_once '../includes/header.php';

// Include database connection
require_once '../config/db.php';

// Check if user is logged in and is a buyer
if (!isLoggedIn() || !isBuyer()) {
    redirect('auth/login.php');
}

// Get user data
$userId = $_SESSION['user_id'];
$userStmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();
$userStmt->close();

// Get wishlist items for this user
$wishlistStmt = $conn->prepare("SELECT item_id, item_type FROM wishlist_items WHERE user_id = ?");
$wishlistStmt->bind_param("i", $userId);
$wishlistStmt->execute();
$wishlistResult = $wishlistStmt->get_result();
$wishlistItems = [];
while ($wishlistItem = $wishlistResult->fetch_assoc()) {
    $key = $wishlistItem['item_type'] . '_' . $wishlistItem['item_id'];
    $wishlistItems[$key] = true;
}
$wishlistStmt->close();

// Get cart count
$cartStmt = $conn->prepare("SELECT COUNT(*) as count FROM cart_items WHERE user_id = ?");
$cartStmt->bind_param("i", $userId);
$cartStmt->execute();
$cartResult = $cartStmt->get_result();
$cartCount = $cartResult->fetch_assoc()['count'];
$cartStmt->close();

// Get wishlist count
$wishlistCountStmt = $conn->prepare("SELECT COUNT(*) as count FROM wishlist_items WHERE user_id = ?");
$wishlistCountStmt->bind_param("i", $userId);
$wishlistCountStmt->execute();
$wishlistCountResult = $wishlistCountStmt->get_result();
$wishlistCount = $wishlistCountResult->fetch_assoc()['count'];
$wishlistCountStmt->close();

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$itemsPerPage = 12;
$offset = ($page - 1) * $itemsPerPage;

// Filtering - sanitize inputs
$category = isset($_GET['category']) ? intval($_GET['category']) : 0;
$county = isset($_GET['county']) ? trim($_GET['county']) : '';
$minPrice = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? max(0, floatval($_GET['min_price'])) : 0;
$maxPrice = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? floatval($_GET['max_price']) : 1000000;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get all categories
$categoriesQuery = "SELECT * FROM categories WHERE is_active = TRUE ORDER BY name";
$categoriesResult = $conn->query($categoriesQuery);

// Get all counties
$countiesQuery = "SELECT DISTINCT county_name FROM counties ORDER BY county_name";
$countiesResult = $conn->query($countiesQuery);

// Build base queries and parameters
$params = [];
$types = "";

if ($type === 'product') {
    $baseQuery = "FROM products p
                  JOIN categories c ON p.category_id = c.category_id
                  JOIN seller_profiles s ON p.seller_id = s.seller_id
                  JOIN users u ON s.user_id = u.user_id
                  WHERE p.status = 'available' AND p.approval_status = 'approved'";
    
    $selectQuery = "SELECT p.*, c.name as category_name, u.county,
                   (SELECT image_path FROM images WHERE item_type = 'product' AND item_id = p.product_id AND is_primary = 1 LIMIT 1) as image,
                   s.business_name, s.rating " . $baseQuery;
    
    $countQuery = "SELECT COUNT(*) as total " . $baseQuery;
} else {
    $baseQuery = "FROM pets p
                  JOIN categories c ON p.category_id = c.category_id
                  JOIN seller_profiles s ON p.seller_id = s.seller_id
                  JOIN users u ON s.user_id = u.user_id
                  WHERE p.status = 'available' AND p.approval_status = 'approved'";
    
    $selectQuery = "SELECT p.*, c.name as category_name, u.county,
                   (SELECT image_path FROM images WHERE item_type = 'pet' AND item_id = p.pet_id AND is_primary = 1 LIMIT 1) as image,
                   s.business_name, s.rating " . $baseQuery;
    
    $countQuery = "SELECT COUNT(*) as total " . $baseQuery;
}

// Add filters
if ($category > 0) {
    $selectQuery .= " AND p.category_id = ?";
    $countQuery .= " AND p.category_id = ?";
    $params[] = $category;
    $types .= "i";
}

if (!empty($county)) {
    $selectQuery .= " AND u.county = ?";
    $countQuery .= " AND u.county = ?";
    $params[] = $county;
    $types .= "s";
}

if ($minPrice > 0) {
    $selectQuery .= " AND p.price >= ?";
    $countQuery .= " AND p.price >= ?";
    $params[] = $minPrice;
    $types .= "d";
}

if ($maxPrice < 1000000) {
    $selectQuery .= " AND p.price <= ?";
    $countQuery .= " AND p.price <= ?";
    $params[] = $maxPrice;
    $types .= "d";
}

if (!empty($search)) {
    if ($type === 'product') {
        $selectQuery .= " AND (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)";
        $countQuery .= " AND (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)";
    } else {
        $selectQuery .= " AND (p.name LIKE ? OR p.breed LIKE ? OR p.description LIKE ? OR c.name LIKE ?)";
        $countQuery .= " AND (p.name LIKE ? OR p.breed LIKE ? OR p.description LIKE ? OR c.name LIKE ?)";
    }
    
    $searchTerm = '%' . $search . '%';
    if ($type === 'product') {
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        $types .= "sss";
    } else {
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        $types .= "ssss";
    }
}

// Add ordering and pagination to select query
$selectQuery .= " ORDER BY p.featured DESC, p.created_at DESC LIMIT ?, ?";
$selectParams = array_merge($params, [$offset, $itemsPerPage]);
$selectTypes = $types . "ii";

// Execute count query
$countStmt = $conn->prepare($countQuery);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $itemsPerPage);
$countStmt->close();

// Execute select query
$stmt = $conn->prepare($selectQuery);
if (!empty($selectParams)) {
    $stmt->bind_param($selectTypes, ...$selectParams);
}
$stmt->execute();
$result = $stmt->get_result();

// Debug information (remove in production)
if (isset($_GET['debug'])) {
    echo "<div class='alert alert-info'>";
    echo "<strong>Debug Info:</strong><br>";
    echo "Query: " . htmlspecialchars($selectQuery) . "<br>";
    echo "Params: " . json_encode($selectParams) . "<br>";
    echo "Types: " . $selectTypes . "<br>";
    echo "Total Results: " . $totalRows . "<br>";
    echo "</div>";
}

// Log activity
if (function_exists('logActivity')) {
    logActivity($type === 'product' ? 'browse_products' : 'browse_pets', [
        'category_id' => $category,
        'county' => $county,
        'search' => $search
    ]);
}
?>
  <style>
    .lightbox {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(5px);
    }

    .lightbox-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        max-width: 90%;
        max-height: 90%;
        text-align: center;
    }

    .lightbox img {
        max-width: 100%;
        max-height: 80vh;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
    }

    .lightbox-close {
        position: absolute;
        top: 20px;
        right: 35px;
        color: #fff;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
        transition: color 0.3s ease;
        z-index: 10000;
    }

    .lightbox-close:hover,
    .lightbox-close:focus {
        color: #ccc;
    }

    .profile-img-clickable {
        cursor: pointer;
        transition: transform 0.2s ease;
    }

    .profile-img-clickable:hover {
        transform: scale(1.05);
    }
</style>
<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                   <div class="d-flex align-items-center mb-4">
                    <?php if (!empty($user['profile_image'])): ?>
                        <img src="<?php echo BASE_URL . 'uploads/' . $user['profile_image']; ?>" 
                            alt="Profile" 
                            class="rounded-circle me-3 profile-img-clickable" 
                            width="60" 
                            height="60"
                            onclick="openLightbox('<?php echo BASE_URL . 'uploads/' . $user['profile_image']; ?>', '<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>')">
                    <?php else: ?>
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                            <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h5 class="mb-0"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></h5>
                        <p class="text-muted mb-0">Buyer</p>
                    </div>
                </div>
                    
                    <div class="list-group list-group-flush">
                        <a href="<?php echo BASE_URL; ?>buyer/dashboard.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                        <a href="<?php echo BASE_URL; ?>buyer/browse.php" class="list-group-item list-group-item-action <?php echo $type === 'pet' ? 'active' : ''; ?>">
                            <i class="fas fa-search me-2"></i> Browse Pets
                        </a>
                        <a href="<?php echo BASE_URL; ?>buyer/browse.php?type=product" class="list-group-item list-group-item-action <?php echo $type === 'product' ? 'active' : ''; ?>">
                            <i class="fas fa-shopping-bag me-2"></i> Shop Products
                        </a>
                        <a href="<?php echo BASE_URL; ?>buyer/wishlist.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-heart me-2"></i> My Wishlist
                            <?php if ($wishlistCount > 0): ?>
                                <span class="badge bg-primary rounded-pill float-end"><?php echo $wishlistCount; ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="<?php echo BASE_URL; ?>buyer/cart.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-shopping-cart me-2"></i> My Cart
                            <?php if ($cartCount > 0): ?>
                                <span class="badge bg-primary rounded-pill float-end"><?php echo $cartCount; ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="<?php echo BASE_URL; ?>buyer/orders.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-clipboard-list me-2"></i> My Orders
                        </a>
                        <a href="<?php echo BASE_URL; ?>buyer/messages.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-envelope me-2"></i> Messages
                        </a>
                        <a href="<?php echo BASE_URL; ?>buyer/profile.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-user-cog me-2"></i> My Profile
                        </a>
                        <a href="<?php echo BASE_URL; ?>auth/logout.php" class="list-group-item list-group-item-action text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
            <!-- Lightbox Modal -->
<div id="profileLightbox" class="lightbox">
    <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
    <div class="lightbox-content">
        <img id="lightboxImage" src="" alt="Profile Picture">
        <div class="mt-3">
            <h5 id="lightboxTitle" class="text-white"></h5>
        </div>
    </div>
</div>
 
              <!-- Filters -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">Filters</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo BASE_URL; ?>buyer/browse.php" method="GET">
                        <?php if ($type === 'product'): ?>
                            <input type="hidden" name="type" value="product">
                        <?php endif; ?>
                        
                        <!-- Search -->
                        <div class="mb-3">
                            <label for="search" class="form-label">Search</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="search" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                        
                        <!-- Category -->
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="0">All Categories</option>
                                <?php 
                                // Reset pointer for categories
                                $categoriesResult->data_seek(0);
                                while ($cat = $categoriesResult->fetch_assoc()): ?>
                                    <option value="<?php echo $cat['category_id']; ?>" <?php echo $category == $cat['category_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <!-- County -->
                        <div class="mb-3">
                            <label for="county" class="form-label">County</label>
                            <select class="form-select" id="county" name="county">
                                <option value="">All Counties</option>
                                <?php 
                                // Reset pointer for counties
                                if ($countiesResult) {
                                    $countiesResult->data_seek(0);
                                    while ($c = $countiesResult->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($c['county_name']); ?>" <?php echo $county == $c['county_name'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($c['county_name']); ?>
                                        </option>
                                    <?php endwhile;
                                } ?>
                            </select>
                        </div>
                        
                        <!-- Price Range -->
                        <div class="mb-3">
                            <label class="form-label">Price Range (KES)</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="number" class="form-control" name="min_price" placeholder="Min" value="<?php echo $minPrice > 0 ? $minPrice : ''; ?>" min="0" step="0.01">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control" name="max_price" placeholder="Max" value="<?php echo $maxPrice < 1000000 ? $maxPrice : ''; ?>" min="0" step="0.01">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                        
                        <a href="<?php echo BASE_URL; ?>buyer/browse.php<?php echo $type === 'product' ? '?type=product' : ''; ?>" class="btn btn-outline-secondary w-100 mt-2">Clear Filters</a>
                    </form>
                </div>
            </div>
        </div>
        
        
        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Page Header -->
            <div class="card border-0 bg-primary text-white shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1"><?php echo $type === 'product' ? 'Shop Products' : 'Browse Pets'; ?></h2>
                            <p class="mb-0"><?php echo $totalRows; ?> <?php echo $type === 'product' ? 'products' : 'pets'; ?> found</p>
                        </div>
                        <?php if ($type === 'product'): ?>
                            <a href="<?php echo BASE_URL; ?>buyer/browse.php" class="btn btn-light">Browse Pets</a>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>buyer/browse.php?type=product" class="btn btn-light">Shop Products</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Items Grid -->
            <div class="row">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($item = $result->fetch_assoc()): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 border-0 shadow-sm position-relative">
                                <!-- Wishlist Button -->
                                <?php
                                $itemKey = $type . '_' . ($type === 'product' ? $item['product_id'] : $item['pet_id']);
                                $inWishlist = isset($wishlistItems[$itemKey]);
                                ?>
                                <button type="button" 
                                        class="btn btn-sm position-absolute top-0 end-0 m-2 wishlist-btn" 
                                        data-type="<?php echo $type; ?>" 
                                        data-id="<?php echo $type === 'product' ? $item['product_id'] : $item['pet_id']; ?>"
                                        data-in-wishlist="<?php echo $inWishlist ? 'true' : 'false'; ?>">
                                    <i class="<?php echo $inWishlist ? 'fas' : 'far'; ?> fa-heart text-danger"></i>
                                </button>
                                
                                <!-- Item Image -->
                                <div class="position-relative">
                                    <?php if (!empty($item['image'])): ?>
                                        <img src="<?php echo '../' . $item['image']; ?>" class="card-img-top" alt="<?php echo $item['name']; ?>" style="height: 200px; object-fit: cover;">
                                    <?php else: ?>
                                        <img src="<?php echo BASE_URL; ?>assets/images/<?php echo $type; ?>-placeholder.jpg" class="card-img-top" alt="<?php echo $type; ?> placeholder" style="height: 200px; object-fit: cover;">
                                    <?php endif; ?>
                                    <span class="position-absolute top-0 start-0 badge bg-primary m-2"><?php echo $item['category_name']; ?></span>
                                    <?php if ($item['featured']): ?>
                                        <span class="position-absolute bottom-0 start-0 badge bg-warning m-2">Featured</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $item['name']; ?></h5>
                                    
                                    <?php if ($type === 'pet'): ?>
                                        <p class="card-text text-muted mb-2">
                                            <?php echo $item['breed']; ?> · <?php echo $item['age']; ?> · <?php echo ucfirst($item['gender']); ?>
                                        </p>
                                    <?php else: ?>
                                        <p class="card-text text-muted mb-2">
                                            <?php echo substr($item['description'], 0, 60); ?>...
                                        </p>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold text-primary">KES <?php echo number_format($item['price'], 2); ?></span>
                                        <span class="text-muted small">
                                            <i class="fas fa-map-marker-alt"></i> <?php echo $item['county']; ?>
                                        </span>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="text-muted small"><?php echo $item['business_name']; ?></span>
                                            <div class="text-warning">
                                                <?php
                                                $rating = $item['rating'];
                                                for ($i = 1; $i <= 5; $i++) {
                                                    if ($i <= $rating) {
                                                        echo '<i class="fas fa-star small"></i>';
                                                    } elseif ($i - 0.5 <= $rating) {
                                                        echo '<i class="fas fa-star-half-alt small"></i>';
                                                    } else {
                                                        echo '<i class="far fa-star small"></i>';
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <a href="<?php echo BASE_URL; ?>buyer/<?php echo $type; ?>.php?id=<?php echo $type === 'product' ? $item['product_id'] : $item['pet_id']; ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-5">
                                <img src="<?php echo BASE_URL; ?>uploads/logo/logo5.png" alt="No results" class="img-fluid mb-3" style="max-width: 150px;">
                                <h5>No <?php echo $type === 'product' ? 'products' : 'pets'; ?> found</h5>
                                <p class="text-muted">Try adjusting your filters or search criteria</p>
                                <a href="<?php echo BASE_URL; ?>buyer/browse.php<?php echo $type === 'product' ? '?type=product' : ''; ?>" class="btn btn-primary">Clear Filters</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo getPageUrl($page - 1); ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <?php
                        $startPage = max(1, min($page - 2, $totalPages - 4));
                        $endPage = min($totalPages, max(5, $page + 2));
                        
                        for ($i = $startPage; $i <= $endPage; $i++):
                        ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo getPageUrl($i); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo getPageUrl($page + 1); ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Helper function to generate pagination URLs with current filters
function getPageUrl($pageNum) {
    $params = $_GET;
    $params['page'] = $pageNum;
    return '?' . http_build_query($params);
}
?>

<!-- Wishlist Toggle Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const wishlistBtns = document.querySelectorAll('.wishlist-btn');
    
    wishlistBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const itemType = this.getAttribute('data-type');
            const itemId = this.getAttribute('data-id');
            const inWishlist = this.getAttribute('data-in-wishlist') === 'true';
            const icon = this.querySelector('i');
            
            // AJAX call to add/remove from wishlist
            fetch('<?php echo BASE_URL; ?>ajax/toggle_wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `item_type=${itemType}&item_id=${itemId}&action=${inWishlist ? 'remove' : 'add'}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Toggle wishlist status
                    if (inWishlist) {
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        this.setAttribute('data-in-wishlist', 'false');
                    } else {
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        this.setAttribute('data-in-wishlist', 'true');
                    }
                    
                    // Update wishlist count in sidebar if needed
                    const wishlistCountBadge = document.querySelector('a[href*="wishlist.php"] .badge');
                    if (wishlistCountBadge) {
                        let count = parseInt(wishlistCountBadge.textContent || '0');
                        count = inWishlist ? count - 1 : count + 1;
                        
                        if (count > 0) {
                            wishlistCountBadge.textContent = count;
                            wishlistCountBadge.classList.remove('d-none');
                        } else {
                            wishlistCountBadge.classList.add('d-none');
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error toggling wishlist:', error);
            });
        });
    });
});
</script>
<script>
function openLightbox(imageSrc, userName) {
    console.log('Opening lightbox with:', imageSrc, userName);
    
    const lightbox = document.getElementById('profileLightbox');
    const lightboxImage = document.getElementById('lightboxImage');
    const lightboxTitle = document.getElementById('lightboxTitle');
    
    if (!lightbox || !lightboxImage || !lightboxTitle) {
        console.error('Lightbox elements not found');
        return;
    }
    
    lightboxImage.src = imageSrc;
    lightboxImage.alt = userName + "'s Profile Picture";
    lightboxTitle.textContent = userName + "'s Profile Picture";
    lightbox.style.display = 'block';
    
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    const lightbox = document.getElementById('profileLightbox');
    if (lightbox) {
        lightbox.style.display = 'none';
    }
    document.body.style.overflow = 'auto';
}

// Close lightbox when clicking on the backdrop
document.addEventListener('DOMContentLoaded', function() {
    const lightbox = document.getElementById('profileLightbox');
    const lightboxContent = document.querySelector('.lightbox-content');
    
    if (lightbox) {
        lightbox.addEventListener('click', function(event) {
            if (event.target === this) {
                closeLightbox();
            }
        });
    }
    
    if (lightboxContent) {
        lightboxContent.addEventListener('click', function(event) {
            event.stopPropagation();
        });
    }
});

// Close lightbox with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeLightbox();
    }
});
</script>

<?php
// Include footer
require_once '../includes/footer.php';
?>