<?php
// Set page title
$pageTitle = "Product Details";

// Include header
require_once '../includes/header.php';

// Include database connection
require_once '../config/db.php';

// Check if user is logged in and is a buyer
if (!isLoggedIn() || !isBuyer()) {
    redirect('auth/login.php');
}

// Check if pet ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlashMessage('error', 'Invalid product ID');
    redirect('buyer/browse.php');
}

$productId = intval($_GET['id']);
$userId = $_SESSION['user_id'];

// Get product data
$productQuery = "SELECT p.*, c.name as category_name, s.seller_id, s.business_name, s.rating, 
            u.county, u.user_id as seller_user_id
            FROM products p
            JOIN categories c ON p.category_id = c.category_id
            JOIN seller_profiles s ON p.seller_id = s.seller_id
            JOIN users u ON s.user_id = u.user_id
            WHERE p.product_id = $productId AND p.approval_status = 'approved'";
$productResult = $conn->query($productQuery);

// Check if product exists
if ($productResult->num_rows === 0) {
    setFlashMessage('error', 'Product not found or not approved');
    redirect('buyer/browse.php');
}

$product = $productResult->fetch_assoc();

// Check if in wishlist
$wishlistQuery = "SELECT * FROM wishlist_items WHERE user_id = $userId AND item_type = 'product' AND item_id = $productId";
$wishlistResult = $conn->query($wishlistQuery);
$inWishlist = $wishlistResult->num_rows > 0;

// Check if in cart
$cartQuery = "SELECT * FROM cart_items WHERE user_id = $userId AND item_type = 'product' AND item_id = $productId";
$cartResult = $conn->query($cartQuery);
$inCart = $cartResult->num_rows > 0;

// Get pet images
$imagesQuery = "SELECT * FROM images WHERE item_type = 'pet' AND item_id = $productId ORDER BY is_primary DESC";
$imagesResult = $conn->query($imagesQuery);

// Get seller's other pets
$otherPetsQuery = "SELECT p.*, 
                  (SELECT image_path FROM images WHERE item_type = 'pet' AND item_id = p.pet_id AND is_primary = 1 LIMIT 1) as image
                  FROM pets p
                  WHERE p.seller_id = {$product['seller_id']} 
                  AND p.pet_id != $productId 
                  AND p.status = 'available' 
                  AND p.approval_status = 'approved'
                  LIMIT 3";
$otherPetsResult = $conn->query($otherPetsQuery);

// Get related pets (same category)
$relatedPetsQuery = "SELECT p.*, 
                    (SELECT image_path FROM images WHERE item_type = 'pet' AND item_id = p.pet_id AND is_primary = 1 LIMIT 1) as image
                    FROM pets p
                    WHERE p.category_id = {$product['category_id']} 
                    AND p.pet_id != $productId 
                    AND p.status = 'available' 
                    AND p.approval_status = 'approved'
                    LIMIT 3";
$relatedPetsResult = $conn->query($relatedPetsQuery);

// Get reviews for this pet
$reviewsQuery = "SELECT r.*, u.first_name, u.last_name, u.profile_image
                FROM reviews r
                JOIN users u ON r.user_id = u.user_id
                WHERE r.item_type = 'product' AND r.item_id = $productId AND r.status = 'approved'
                ORDER BY r.created_at DESC";
$reviewsResult = $conn->query($reviewsQuery);

// Calculate average rating
$avgRatingQuery = "SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
                  FROM reviews 
                  WHERE item_type = 'product' AND item_id = $productId AND status = 'approved'";
$avgRatingResult = $conn->query($avgRatingQuery);
$ratingData = $avgRatingResult->fetch_assoc();
$avgRating = $ratingData['avg_rating'] ? round($ratingData['avg_rating'], 1) : 0;
$reviewCount = $ratingData['review_count'];

// Check if user has already reviewed this pet
$userReviewQuery = "SELECT * FROM reviews WHERE user_id = $userId AND item_type = 'product' AND item_id = $productId";
$userReviewResult = $conn->query($userReviewQuery);
$hasReviewed = $userReviewResult->num_rows > 0;

// Increment view count
$updateViewsQuery = "UPDATE products SET views = views + 1 WHERE product_id = $productId";
$conn->query($updateViewsQuery);

// Log activity
logActivity('view_product', ['product_id' => $productId]);

$userId = $_SESSION['user_id'];

// Get wishlist count
$wishlistQuery = "SELECT COUNT(*) as count FROM wishlist_items WHERE user_id = $userId";
$wishlistResult = $conn->query($wishlistQuery);
$wishlistCount = $wishlistResult->fetch_assoc()['count'];

// Get cart count
$cartQuery = "SELECT COUNT(*) as count FROM cart_items WHERE user_id = $userId";
$cartResult = $conn->query($cartQuery);
$cartCount = $cartResult->fetch_assoc()['count'];


?>

<div class="container py-5">
    <div class="row">
    <?php include_once 'sidebar.php';?>
        <!-- Pet Details -->
        <div class="col-lg-9">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>buyer/dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>buyer/browse.php">Browse Pets</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo $product['name']; ?></li>
                </ol>
            </nav>
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-0">
                    <div class="row g-0">
                        <!-- Product Images Gallery -->
                        <div class="col-md-6">
                            <?php if ($imagesResult->num_rows > 0): ?>
                                <div id="petImagesCarousel" class="carousel slide" data-bs-ride="carousel">
                                    <div class="carousel-inner">
                                        <?php 
                                        $active = true;
                                        while ($image = $imagesResult->fetch_assoc()): 
                                        ?>
                                            <div class="carousel-item <?php echo $active ? 'active' : ''; ?>">
                                                <img src="<?php echo  '../' . $image['image_path']; ?>" class="d-block w-100" alt="<?php echo $product['name']; ?>" style="height: 400px; object-fit: cover;">
                                            </div>
                                            <?php $active = false; ?>
                                        <?php endwhile; ?>
                                    </div>
                                    <?php if ($imagesResult->num_rows > 1): ?>
                                        <button class="carousel-control-prev" type="button" data-bs-target="#petImagesCarousel" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#petImagesCarousel" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <img src="<?php echo BASE_URL; ?>assets/images/pet-placeholder.jpg" class="img-fluid w-100" alt="<?php echo $product['name']; ?>" style="height: 400px; object-fit: cover;">
                            <?php endif; ?>
                        </div>
                        
                        <!-- Product Details -->
                        <div class="col-md-6">
                            <div class="p-4">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge bg-primary mb-2"><?php echo $product['category_name']; ?></span>
                                        <?php if ($product['featured']): ?>
                                            <span class="badge bg-warning mb-2">Featured</span>
                                        <?php endif; ?>
                                        <h2><?php echo $product['name']; ?></h2>
                                    </div>
                                    <button class="btn btn-sm <?php echo $inWishlist ? 'btn-danger' : 'btn-outline-danger'; ?>" id="wishlistBtn" data-id="<?php echo $productId; ?>">
                                        <i class="<?php echo $inWishlist ? 'fas' : 'far'; ?> fa-heart"></i>
                                    </button>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="text-warning me-2">
                                            <?php
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $avgRating) {
                                                    echo '<i class="fas fa-star"></i>';
                                                } elseif ($i - 0.5 <= $avgRating) {
                                                    echo '<i class="fas fa-star-half-alt"></i>';
                                                } else {
                                                    echo '<i class="far fa-star"></i>';
                                                }
                                            }
                                            ?>
                                        </div>
                                        <span><?php echo $avgRating; ?>/5 (<?php echo $reviewCount; ?> reviews)</span>
                                    </div>
                                    <p class="text-muted">
                                        <i class="fas fa-map-marker-alt"></i> <?php echo $product['county']; ?> &nbsp;|&nbsp;
                                        <i class="fas fa-eye"></i> <?php echo $product['views']; ?> views
                                    </p>
                                </div>
                                
                                <h4 class="text-primary mb-3">KES <?php echo number_format($product['price'], 2); ?></h4>
                                
                                <div class="mb-3">
                                    <div class="row">                                         
                                        <div class="col-6 mb-3">
                                            <p class="text-muted mb-1">Quantity Available</p>
                                            <p class="fw-bold"><?php echo $product['stock_quantity']; ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2 mb-3">
                                    <?php if ($inCart): ?>
                                        <a href="<?php echo BASE_URL; ?>buyer/cart.php" class="btn btn-success">
                                            <i class="fas fa-check me-2"></i> Already in Cart - View Cart
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-primary" id="addToCartBtn" data-id="<?php echo $productId; ?>">
                                            <i class="fas fa-shopping-cart me-2"></i> Add to Cart
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button class="btn btn-outline-primary" id="contactSellerBtn" data-id="<?php echo $product['seller_user_id']; ?>">
                                        <i class="fas fa-envelope me-2"></i> Contact Seller
                                    </button>
                                </div>
                                
                                <div class="seller-info p-3 bg-light rounded">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="text-primary me-3">
                                            <i class="fas fa-store fa-2x"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0"><?php echo $product['business_name']; ?></h6>
                                            <div class="text-warning">
                                                <?php
                                                $sellerRating = $product['rating'];
                                                for ($i = 1; $i <= 5; $i++) {
                                                    if ($i <= $sellerRating) {
                                                        echo '<i class="fas fa-star small"></i>';
                                                    } elseif ($i - 0.5 <= $sellerRating) {
                                                        echo '<i class="fas fa-star-half-alt small"></i>';
                                                    } else {
                                                        echo '<i class="far fa-star small"></i>';
                                                    }
                                                }
                                                ?>
                                                <span class="ms-1"><?php echo number_format($sellerRating, 1); ?></span>
                                            </div>
                                        </div>
                                        <a href="<?php echo BASE_URL; ?>buyer/seller_profile.php?id=<?php echo $product['seller_id']; ?>" class="btn btn-sm btn-outline-primary ms-auto">View Profile</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Pet Description -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Description</h5>
                </div>
                <div class="card-body">
                    <p><?php echo nl2br($product['description']); ?></p>
                </div>
            </div>
            
            <!-- Reviews Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Reviews (<?php echo $reviewCount; ?>)</h5>
                    <?php if (!$hasReviewed): ?>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addReviewModal">
                            <i class="fas fa-star me-1"></i> Write a Review
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if ($reviewsResult->num_rows > 0): ?>
                        <?php while ($review = $reviewsResult->fetch_assoc()): ?>
                            <div class="d-flex mb-4">
                                <div class="flex-shrink-0">
                                    <?php if ($review['profile_image']): ?>
                                        <img src="<?php echo BASE_URL . 'uploads/' . $review['profile_image']; ?>" class="rounded-circle" width="50" height="50" alt="<?php echo $review['first_name']; ?>">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <?php echo strtoupper(substr($review['first_name'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="mb-0"><?php echo $review['first_name'] . ' ' . $review['last_name']; ?></h6>
                                            <div class="text-warning mb-1">
                                                <?php
                                                for ($i = 1; $i <= 5; $i++) {
                                                    if ($i <= $review['rating']) {
                                                        echo '<i class="fas fa-star small"></i>';
                                                    } else {
                                                        echo '<i class="far fa-star small"></i>';
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <small class="text-muted"><?php echo date('M j, Y', strtotime($review['created_at'])); ?></small>
                                    </div>
                                    <p class="mb-0"><?php echo $review['comment']; ?></p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="far fa-comment-dots fa-3x text-muted mb-3"></i>
                            <p>No reviews yet. Be the first to review this product!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
      <!-- Sidebar -->
        <div class="col-lg-3">
            <!-- Seller's Other Pets -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">More from this Seller</h5>
                </div>
                <div class="card-body">
                    <?php if ($otherPetsResult->num_rows > 0): ?>
                        <?php while ($otherPet = $otherPetsResult->fetch_assoc()): ?>
                            <div class="card mb-3 border-0 shadow-sm">
                                <div class="row g-0">
                                    <div class="col-4">
                                        <?php if ($otherPet['image']): ?>
                                            <img src="<?php echo BASE_URL . $otherPet['image']; ?>" class="img-fluid rounded-start" alt="<?php echo $otherPet['name']; ?>" style="height: 80px; object-fit: cover;">
                                        <?php else: ?>
                                            <img src="<?php echo BASE_URL; ?>assets/images/pet-placeholder.jpg" class="img-fluid rounded-start" alt="<?php echo $otherPet['name']; ?>" style="height: 80px; object-fit: cover;">
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-8">
                                        <div class="card-body p-2">
                                            <h6 class="card-title mb-1"><?php echo $otherPet['name']; ?></h6>
                                            <p class="card-text mb-1"><small>KES <?php echo number_format($otherPet['price'], 2); ?></small></p>
                                            <a href="<?php echo BASE_URL; ?>buyer/pet.php?id=<?php echo $otherPet['pet_id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted">No other pets from this seller.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Related Pets -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Similar Pets</h5>
                </div>
                <div class="card-body">
                    <?php if ($relatedPetsResult->num_rows > 0): ?>
                        <?php while ($relatedPet = $relatedPetsResult->fetch_assoc()): ?>
                            <div class="card mb-3 border-0 shadow-sm">
                                <div class="row g-0">
                                    <div class="col-4">
                                        <?php if ($relatedPet['image']): ?>
                                            <img src="<?php echo BASE_URL . 'uploads/' . $relatedPet['image']; ?>" class="img-fluid rounded-start" alt="<?php echo $relatedPet['name']; ?>" style="height: 80px; object-fit: cover;">
                                        <?php else: ?>
                                            <img src="<?php echo BASE_URL; ?>assets/images/pet-placeholder.jpg" class="img-fluid rounded-start" alt="<?php echo $relatedPet['name']; ?>" style="height: 80px; object-fit: cover;">
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-8">
                                        <div class="card-body p-2">
                                            <h6 class="card-title mb-1"><?php echo $relatedPet['name']; ?></h6>
                                            <p class="card-text mb-1"><small>KES <?php echo number_format($relatedPet['price'], 2); ?></small></p>
                                            <a href="<?php echo BASE_URL; ?>buyer/pet.php?id=<?php echo $relatedPet['pet_id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted">No similar pets found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Review Modal -->
<?php if (!$hasReviewed): ?>
<div class="modal fade" id="addReviewModal" tabindex="-1" aria-labelledby="addReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addReviewModalLabel">Write a Review for <?php echo $product['name']; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reviewForm" action="<?php echo BASE_URL; ?>buyer/submit_review.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="item_type" value="product">
                    <input type="hidden" name="item_id" value="<?php echo $productId; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Rating</label>
                        <div class="rating-stars mb-2">
                            <i class="far fa-star fa-2x rating-star" data-value="1"></i>
                            <i class="far fa-star fa-2x rating-star" data-value="2"></i>
                            <i class="far fa-star fa-2x rating-star" data-value="3"></i>
                            <i class="far fa-star fa-2x rating-star" data-value="4"></i>
                            <i class="far fa-star fa-2x rating-star" data-value="5"></i>
                        </div>
                        <input type="hidden" name="rating" id="ratingValue" value="">
                    </div>
                    
                    <div class="mb-3">
                        <label for="reviewComment" class="form-label">Your Review</label>
                        <textarea class="form-control" id="reviewComment" name="comment" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Contact Seller Modal -->
<div class="modal fade" id="contactSellerModal" tabindex="-1" aria-labelledby="contactSellerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contactSellerModalLabel">Message to Seller</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="messageForm" action="<?php echo BASE_URL; ?>buyer/send_message.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="receiver_id" id="receiverId" value="">
                    <input type="hidden" name="related_to_item_type" value="product">
                    <input type="hidden" name="related_to_item_id" value="<?php echo $productId; ?>">
                    
                    <div class="mb-3">
                        <label for="messageSubject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="messageSubject" name="subject" value="Inquiry about <?php echo $product['name']; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="messageContent" class="form-label">Message</label>
                        <textarea class="form-control" id="messageContent" name="message" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
// Make sure jQuery is properly loaded before executing any code
document.addEventListener('DOMContentLoaded', function() {
    // Check if jQuery is loaded
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded! Please add jQuery to your footer.php');
        alert('Error: jQuery is not loaded. Please contact the administrator.');
        return;
    }
    
    // Now proceed with jQuery code
    $(document).ready(function() {
        // Make sure we have a toast container
        if ($('#toastContainer').length === 0) {
            $('body').append('<div id="toastContainer" aria-live="polite" aria-atomic="true" class="position-fixed top-0 end-0 p-3" style="z-index:11000;"></div>');
        }
        
        // Add to Cart
        $('#addToCartBtn').click(function() {
            const productId = $(this).data('id');
            const $button = $(this);
            
            // Disable button to prevent multiple clicks
            $button.prop('disabled', true);
            
            // Debug - check if we're getting the correct product ID
            console.log('Adding product ID to cart:', productId);
            
            $.ajax({
                url: '../buyer/ajax/add_to_cart.php', // Use absolute path from root
                type: 'POST',
                data: {
                    item_type: 'product', // CHANGED FROM 'pet' to 'product'
                    item_id: productId,
                    quantity: 1
                },
                success: function(response) {
                    console.log('Cart response:', response); // Debug
                    try {
                        const result = JSON.parse(response);
                        if (result.success) {
                            $button.html('<i class="fas fa-check me-2"></i> Added to Cart');
                            $button.removeClass('btn-primary').addClass('btn-success');
                            
                            // Update cart count in navbar
                            updateCartCount();
                            
                            // Show success toast
                            showToast('Success', 'Product added to your cart successfully', 'success');
                            
                            // Redirect to cart after 1 second
                            setTimeout(function() {
                                window.location.href = 'cart.php';
                            }, 1000);
                        } else {
                            showToast('Error', result.message || 'Error adding to cart', 'error');
                            $button.prop('disabled', false);
                        }
                    } catch (e) {
                        console.error('Invalid JSON response:', response);
                        showToast('Error', 'There was an error adding to cart. Please try again.', 'error');
                        $button.prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error adding to cart:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                    showToast('Error', 'There was an error adding to cart. Please try again.', 'error');
                    $button.prop('disabled', false);
                }
            });
        });
        
        // Add to Wishlist
        $('#wishlistBtn').click(function() {
            const productId = $(this).data('id');
            const $button = $(this);
            
            // Debug - check if we're getting the correct product ID
            console.log('Toggling wishlist for product ID:', productId);
            
            // Disable button temporarily to prevent multiple clicks
            $button.prop('disabled', true);
            
            $.ajax({
                url: '../buyer/ajax/toggle_wishlist.php', // Use absolute path from root
                type: 'POST',
                data: {
                    item_type: 'product', // CHANGED FROM 'pet' to 'product'
                    item_id: productId
                },
                success: function(response) {
                    console.log('Wishlist response:', response); // Debug
                    try {
                        const result = JSON.parse(response);
                        if (result.success) {
                            if (result.action === 'added') {
                                $button.removeClass('btn-outline-danger').addClass('btn-danger');
                                $button.find('i').removeClass('far').addClass('fas');
                                showToast('Success', 'Added to your wishlist', 'success');
                            } else {
                                $button.removeClass('btn-danger').addClass('btn-outline-danger');
                                $button.find('i').removeClass('fas').addClass('far');
                                showToast('Success', 'Removed from your wishlist', 'success');
                            }
                            
                            // Update wishlist count in navbar
                            updateWishlistCount();
                        } else {
                            showToast('Error', result.message || 'Error updating wishlist', 'error');
                        }
                    } catch (e) {
                        console.error('Invalid JSON response:', response);
                        showToast('Error', 'There was an error updating your wishlist. Please try again.', 'error');
                    }
                    // Re-enable button
                    setTimeout(function() {
                        $button.prop('disabled', false);
                    }, 500);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error updating wishlist:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                    showToast('Error', 'There was an error updating your wishlist. Please try again.', 'error');
                    $button.prop('disabled', false);
                }
            });
        });
        
        // Rating stars in review form
        $('.rating-star').click(function() {
            const value = $(this).data('value');
            $('#ratingValue').val(value);
            resetStars(value);
        });
        
        // Rating stars hover effect
        $('.rating-star').hover(
            function() {
                const value = $(this).data('value');
                resetStarsHover(value);
            },
            function() {
                const selected = $('#ratingValue').val();
                resetStars(selected);
            }
        );
        
        // Form submission for review
        $('#reviewForm').submit(function(e) {
            if ($('#ratingValue').val() === '') {
                e.preventDefault();
                showToast('Error', 'Please select a rating', 'error');
                return false;
            }
        });
        
        // Contact seller button
        $('#contactSellerBtn').click(function() {
            const sellerId = $(this).data('id');
            $('#receiverId').val(sellerId);
            $('#contactSellerModal').modal('show');
        });
    });
});

// Function to reset stars display based on selected rating
function resetStarsHover(value) {
    value = parseInt(value) || 0;
    for (let i = 1; i <= 5; i++) {
        if (i <= value) {
            $('.rating-star[data-value="' + i + '"]').removeClass('far').addClass('fas text-warning');
        } else {
            $('.rating-star[data-value="' + i + '"]').removeClass('fas text-warning').addClass('far');
        }
    }
}

// Function to reset stars display based on selected rating
function resetStars(selected) {
    selected = parseInt(selected) || 0;
    for (let i = 1; i <= 5; i++) {
        if (i <= selected) {
            $('.rating-star[data-value="' + i + '"]').removeClass('far').addClass('fas text-warning');
        } else {
            $('.rating-star[data-value="' + i + '"]').removeClass('fas text-warning').addClass('far');
        }
    }
}

// Function to update cart count
function updateCartCount() {
    $.ajax({
        url: '../buyer/ajax/get_cart_count.php', // Use absolute path from root
        type: 'GET',
        success: function(response) {
            console.log('Cart count response:', response); // Debug
            try {
                const result = JSON.parse(response);
                $('.cart-count').text(result.count);
            } catch (e) {
                console.error('Invalid JSON response:', response);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error updating cart count:', error);
        }
    });
}

// Function to update wishlist count
function updateWishlistCount() {
    $.ajax({
        url: '../buyer/ajax/get_wishlist_count.php', // Use absolute path from root
        type: 'GET',
        success: function(response) {
            console.log('Wishlist count response:', response); // Debug
            try {
                const result = JSON.parse(response);
                $('.wishlist-count').text(result.count);
            } catch (e) {
                console.error('Invalid JSON response:', response);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error updating wishlist count:', error);
        }
    });
}

// Function to show toast notifications
function showToast(title, message, type) {
    const toastClass = type === 'success' ? 'bg-success' : 'bg-danger';
    const toast = `
        <div class="toast align-items-center ${toastClass} text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <strong>${title}:</strong> ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    $('#toastContainer').append(toast);
    const toastElement = $('.toast').last();
    
    // Check if Bootstrap Toast object exists
    if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
        const bsToast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: 3000
        });
        bsToast.show();
    } else {
        // Fallback if Bootstrap JS is not properly loaded
        console.warn('Bootstrap JS not detected, using fallback toast display');
        toastElement.css({
            'display': 'block', 
            'opacity': '1',
            'position': 'relative',
            'margin-bottom': '10px'
        });
        setTimeout(function() {
            toastElement.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }
    
    // Remove toast from DOM after it's hidden
    toastElement.on('hidden.bs.toast', function() {
        $(this).remove();
    });
}
</script>
   


<!-- Toast container -->
<div id="toastContainer" aria-live="polite" aria-atomic="true" class="position-fixed top-0 end-0 p-3"></div>

<?php require_once '../includes/footer.php'; ?>

