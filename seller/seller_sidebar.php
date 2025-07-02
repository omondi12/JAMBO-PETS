<style>
    /* Sidebar Navigation Styling - Jambo Pets */

/* CSS Variables for sidebar theming */
:root {
  --primary-color: #2563eb;
  --primary-dark: #1d4ed8;
  --primary-light: #3b82f6;
  --secondary-color: #10b981;
  --accent-color: #f59e0b;
  --text-dark: #1f2937;
  --text-light: #6b7280;
  --bg-light: #f8fafc;
  --bg-white: #ffffff;
  --border-color: #e5e7eb;
  --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  --gradient-secondary: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

/* Sidebar Container */
.list-group {
  border-radius: 15px;
  overflow: hidden;
  box-shadow: var(--shadow-md);
  background: var(--bg-white);
  border: none;
  margin-bottom: 1.5rem;
}

/* Profile Section at Top */
.d-flex.align-items-center.mb-4 {
  padding: 1.5rem;
  background: linear-gradient(135deg, rgba(37, 99, 235, 0.05), rgba(37, 99, 235, 0.1));
  border-bottom: 1px solid var(--border-color);
  margin-bottom: 0 !important;
}

/* Profile Image Styling */
.profile-img-clickable {
  border: 3px solid var(--primary-color);
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: var(--shadow-sm);
}

.profile-img-clickable:hover {
  transform: scale(1.05);
  box-shadow: var(--shadow-md);
  border-color: var(--primary-dark);
}

/* Profile Avatar (when no image) */
.bg-primary.text-white.rounded-circle {
  background: var(--gradient-primary) !important;
  font-weight: 600;
  font-size: 1.2rem;
  box-shadow: var(--shadow-sm);
  transition: all 0.3s ease;
}

.bg-primary.text-white.rounded-circle:hover {
  transform: scale(1.05);
  box-shadow: var(--shadow-md);
}

/* Profile Name and Role */
.d-flex.align-items-center.mb-4 h5 {
  color: var(--text-dark);
  font-weight: 600;
  margin-bottom: 0.25rem;
  font-size: 1.1rem;
}

.d-flex.align-items-center.mb-4 p.text-muted {
  color: var(--text-light) !important;
  font-size: 0.9rem;
  font-weight: 500;
  margin-bottom: 0;
}

/* Navigation Links */
.list-group-item {
  border: none;
  padding: 1rem 1.5rem;
  background: transparent;
  color: var(--text-dark);
  font-weight: 500;
  transition: all 0.3s ease;
  position: relative;
  text-decoration: none;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.list-group-item:hover {
  background: linear-gradient(135deg, rgba(37, 99, 235, 0.08), rgba(37, 99, 235, 0.12));
  color: var(--primary-color);
  transform: translateX(5px);
  text-decoration: none;
}

/* Active State */
.list-group-item.active {
  background: var(--gradient-primary) !important;
  color: white !important;
  font-weight: 600;
  border: none;
  position: relative;
  z-index: 1;
}

.list-group-item.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  height: 100%;
  width: 4px;
  background: white;
  border-radius: 0 2px 2px 0;
}

.list-group-item.active:hover {
  transform: none;
  background: var(--gradient-primary) !important;
}

/* Icons in Navigation */
.list-group-item i {
  width: 20px;
  margin-right: 0.75rem;
  font-size: 1.1rem;
  transition: all 0.3s ease;
}

.list-group-item:hover i {
  transform: scale(1.1);
}

.list-group-item.active i {
  color: white;
}

/* Badges for Notifications */
.list-group-item .badge {
  border-radius: 20px;
  padding: 4px 8px;
  font-weight: 600;
  font-size: 0.7rem;
  min-width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.list-group-item .badge.badge-warning {
  background: linear-gradient(135deg, #f59e0b, #fbbf24);
  color: white;
  box-shadow: 0 2px 4px rgba(245, 158, 11, 0.3);
}

.list-group-item .badge.badge-danger {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
}

.list-group-item:hover .badge {
  transform: scale(1.1);
}

/* Logout Link Special Styling */
.list-group-item.text-danger {
  color: #dc3545 !important;
  border-top: 1px solid var(--border-color);
  margin-top: 0.5rem;
}

.list-group-item.text-danger:hover {
  background: linear-gradient(135deg, rgba(220, 53, 69, 0.08), rgba(220, 53, 69, 0.12));
  color: #dc3545 !important;
}

.list-group-item.text-danger i {
  color: #dc3545;
}

/* Seller Tips Card */
.card.mb-4 {
  border: none;
  border-radius: 15px;
  overflow: hidden;
  background: var(--bg-white);
  box-shadow: var(--shadow-sm);
  transition: all 0.3s ease;
}

.card.mb-4:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

/* Card Header */
.card-header.bg-light {
  background: linear-gradient(135deg, rgba(248, 250, 252, 0.8), rgba(241, 245, 249, 0.8)) !important;
  border-bottom: 1px solid var(--border-color);
  padding: 1rem 1.25rem;
  border-radius: 15px 15px 0 0 !important;
}

.card-header.bg-light h5 {
  color: var(--text-dark);
  font-weight: 600;
  margin: 0;
  font-size: 1rem;
}

/* Card Body */
.card-body {
  padding: 1.25rem;
}

.card-body ul {
  margin: 0;
  padding-left: 1.25rem;
}

.card-body ul.small {
  font-size: 0.875rem;
  line-height: 1.5;
}

.card-body ul li {
  color: var(--text-light);
  margin-bottom: 0.5rem;
  transition: all 0.3s ease;
  position: relative;
}

.card-body ul li:hover {
  color: var(--primary-color);
  transform: translateX(3px);
}

.card-body ul li::marker {
  color: var(--primary-color);
}

/* Responsive Design */
@media (max-width: 768px) {
  .list-group-item {
    padding: 0.875rem 1.25rem;
    font-size: 0.9rem;
  }
  
  .d-flex.align-items-center.mb-4 {
    padding: 1.25rem;
  }
  
  .profile-img-clickable,
  .bg-primary.text-white.rounded-circle {
    width: 50px !important;
    height: 50px !important;
  }
  
  .bg-primary.text-white.rounded-circle {
    font-size: 1rem;
  }
  
  .list-group-item i {
    margin-right: 0.5rem;
    font-size: 1rem;
  }
  
  .card-body {
    padding: 1rem;
  }
}

@media (max-width: 576px) {
  .list-group-item {
    padding: 0.75rem 1rem;
  }
  
  .d-flex.align-items-center.mb-4 {
    padding: 1rem;
  }
  
  .list-group-item:hover {
    transform: translateX(3px);
  }
}

/* Animation for sidebar loading */
@keyframes slideInLeft {
  from {
    opacity: 0;
    transform: translateX(-20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.list-group {
  animation: slideInLeft 0.5s ease-out;
}

.list-group-item {
  animation: slideInLeft 0.6s ease-out forwards;
}

.list-group-item:nth-child(1) { animation-delay: 0.1s; }
.list-group-item:nth-child(2) { animation-delay: 0.15s; }
.list-group-item:nth-child(3) { animation-delay: 0.2s; }
.list-group-item:nth-child(4) { animation-delay: 0.25s; }
.list-group-item:nth-child(5) { animation-delay: 0.3s; }
.list-group-item:nth-child(6) { animation-delay: 0.35s; }
.list-group-item:nth-child(7) { animation-delay: 0.4s; }
.list-group-item:nth-child(8) { animation-delay: 0.45s; }
.list-group-item:nth-child(9) { animation-delay: 0.5s; }
.list-group-item:nth-child(10) { animation-delay: 0.55s; }

/* Float classes for badges */
.float-right {
  float: right !important;
}

/* Ensure proper spacing */
.mb-4 {
  margin-bottom: 1.5rem !important;
}

/* Focus states for accessibility */
.list-group-item:focus {
  outline: 2px solid var(--primary-color);
  outline-offset: 2px;
  z-index: 2;
}

/* Smooth transitions for all interactive elements */
* {
  transition: all 0.3s ease;
}
</style><div class="list-group mb-4">
<div class="d-flex align-items-center mb-4">
    <?php if (!empty($user['profile_image'])): ?>
        <img src="<?php echo BASE_URL . 'uploads/' . $user['profile_image']; ?>" 
             alt="Profile" 
             class="rounded-circle me-3 profile-img-clickable" 
             width="60" 
             height="60"
             onclick="openLightbox('<?php echo BASE_URL . 'uploads/' . $user['profile_image']; ?>', '<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'], ENT_QUOTES); ?>')">
    <?php else: ?>
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
            <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
        </div>
    <?php endif; ?>
    <div>
        <h5 class="mb-0"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></h5>
        <p class="text-muted mb-0">Seller</p>
    </div>
</div>
    <a href="dashboard.php" class="list-group-item list-group-item-action <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
        <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
    </a>
    <a href="add_pet.php" class="list-group-item list-group-item-action <?php echo (basename($_SERVER['PHP_SELF']) == 'add_pet.php') ? 'active' : ''; ?>">
        <i class="fas fa-paw mr-2"></i> Add Pet
    </a>
    <a href="manage_pets.php" class="list-group-item list-group-item-action <?php echo (basename($_SERVER['PHP_SELF']) == 'manage_pets.php') ? 'active' : ''; ?>">
        <i class="fas fa-list mr-2"></i> Manage Pets
    </a>
    <a href="add_product.php" class="list-group-item list-group-item-action <?php echo (basename($_SERVER['PHP_SELF']) == 'add_product.php') ? 'active' : ''; ?>">
        <i class="fas fa-box mr-2"></i> Add Product
    </a>
    <a href="manage_products.php" class="list-group-item list-group-item-action <?php echo (basename($_SERVER['PHP_SELF']) == 'manage_products.php') ? 'active' : ''; ?>">
        <i class="fas fa-boxes mr-2"></i> Manage Products
    </a>
    <a href="orders.php" class="list-group-item list-group-item-action <?php echo (basename($_SERVER['PHP_SELF']) == 'orders.php') ? 'active' : ''; ?>">
        <i class="fas fa-shopping-cart mr-2"></i> Orders
        <?php 
        // Display pending orders count if available
        if (isset($pending_orders) && $pending_orders > 0): 
        ?>
            <span class="badge badge-warning float-right"><?php echo $pending_orders; ?></span>
        <?php endif; ?>
    </a>
    <a href="messages.php" class="list-group-item list-group-item-action <?php echo (basename($_SERVER['PHP_SELF']) == 'messages.php') ? 'active' : ''; ?>">
        <i class="fas fa-envelope mr-2"></i> Messages
        <?php 
        // Display unread messages count if available
        if (isset($unread_messages) && $unread_messages > 0): 
        ?>
            <span class="badge badge-danger float-right"><?php echo $unread_messages; ?></span>
        <?php endif; ?>
    </a>
    <a href="profile.php" class="list-group-item list-group-item-action <?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : ''; ?>">
        <i class="fas fa-user-edit mr-2"></i> Edit Profile
    </a>
    <a href="stats.php" class="list-group-item list-group-item-action <?php echo (basename($_SERVER['PHP_SELF']) == 'stats.php') ? 'active' : ''; ?>">
        <i class="fas fa-chart-bar mr-2"></i> Statistics
    </a>
    <a href="../auth/logout.php" class="list-group-item list-group-item-action text-danger">
        <i class="fas fa-sign-out-alt mr-2"></i> Logout
    </a>
</div>

<div class="card mb-4">
    <div class="card-header bg-light">
        <h5>Seller Tips</h5>
    </div>
    <div class="card-body">
        <ul class="small">
            <li>Clear photos attract more buyers</li>
            <li>Detailed descriptions help sell faster</li>
            <li>Respond to messages promptly</li>
            <li>Always update pet availability</li>
        </ul>
    </div>
</div>