-- Create database
CREATE DATABASE jambo_pets;
USE jambo_pets;

-- Users table (common fields for all user types)
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    user_type ENUM('buyer', 'seller', 'admin') NOT NULL,
    profile_image VARCHAR(255),
    county VARCHAR(50),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active'
);

-- Seller profiles (extends users table for sellers only)
CREATE TABLE seller_profiles (
    seller_id INT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    business_name VARCHAR(100),
    business_description TEXT,
    verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    id_number VARCHAR(20),
    rating DECIMAL(3,2) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Pet categories
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    parent_id INT,
    image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (parent_id) REFERENCES categories(category_id) ON DELETE SET NULL
);

-- Pet listings
CREATE TABLE pets (
    pet_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    breed VARCHAR(100),
    age VARCHAR(50),
    gender ENUM('male', 'female', 'unknown'),
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    quantity INT DEFAULT 1,
    status ENUM('available', 'sold', 'pending', 'inactive') DEFAULT 'available',
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    views INT DEFAULT 0,
    FOREIGN KEY (seller_id) REFERENCES seller_profiles(seller_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE
);

-- Product listings (for pet accessories, food, etc.)
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    status ENUM('available', 'out_of_stock', 'inactive') DEFAULT 'available',
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    views INT DEFAULT 0,
    FOREIGN KEY (seller_id) REFERENCES seller_profiles(seller_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE
);

-- Images for pets and products
CREATE TABLE images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    item_type ENUM('pet', 'product') NOT NULL,
    item_id INT NOT NULL, -- pet_id or product_id
    image_path VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cart items
CREATE TABLE cart_items (
    cart_item_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_type ENUM('pet', 'product') NOT NULL,
    item_id INT NOT NULL, -- pet_id or product_id
    quantity INT NOT NULL DEFAULT 1,
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Wishlist items
CREATE TABLE wishlist_items (
    wishlist_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_type ENUM('pet', 'product') NOT NULL,
    item_id INT NOT NULL, -- pet_id or product_id
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Orders
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    payment_method ENUM('mpesa', 'credit_card', 'cash_on_delivery', 'pesapal'),
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    shipping_address TEXT,
    shipping_county VARCHAR(50),
    contact_phone VARCHAR(15),
    transaction_reference VARCHAR(100),
    FOREIGN KEY (buyer_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Order items
CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    item_type ENUM('pet', 'product') NOT NULL,
    item_id INT NOT NULL, -- pet_id or product_id
    seller_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price_per_unit DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES seller_profiles(seller_id) ON DELETE CASCADE
);

-- Reviews
CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_type ENUM('pet', 'product', 'seller') NOT NULL,
    item_id INT NOT NULL, -- pet_id, product_id, or seller_id
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Messages
CREATE TABLE messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    subject VARCHAR(100),
    message TEXT NOT NULL,
    related_to_item_type ENUM('pet', 'product', 'order', 'general') DEFAULT 'general',
    related_to_item_id INT,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_status BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Blog articles
CREATE TABLE blog_posts (
    post_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    featured_image VARCHAR(255),
    published_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('draft', 'published') DEFAULT 'draft',
    views INT DEFAULT 0,
    FOREIGN KEY (admin_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Counties in Kenya (for filtering)
CREATE TABLE counties (
    county_id INT AUTO_INCREMENT PRIMARY KEY,
    county_name VARCHAR(50) NOT NULL,
    region VARCHAR(50)
);

-- Analytics table for tracking user activity
CREATE TABLE analytics (
    analytics_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    page_visited VARCHAR(255) NOT NULL,
    action_type VARCHAR(50),
    item_type ENUM('pet', 'product', 'blog', 'other'),
    item_id INT,
    visit_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(50),
    user_agent TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Insert some initial data

-- Add admin user
INSERT INTO users (email, password, first_name, last_name, phone, user_type)
VALUES ('admin@jambopets.com', '$2y$10$somehashedpassword', 'Admin', 'User', '0700000000', 'admin');

-- Add counties
INSERT INTO counties (county_name, region) VALUES 
('Nairobi', 'Nairobi'),
('Mombasa', 'Coast'),
('Kisumu', 'Nyanza'),
('Nakuru', 'Rift Valley'),
('Eldoret', 'Rift Valley'),
('Machakos', 'Eastern'),
('Nyeri', 'Central'),
('Kakamega', 'Western'),
('Garissa', 'North Eastern'),
('Lamu', 'Coast');

-- Add pet categories
INSERT INTO categories (name, description) VALUES 
('Dogs', 'All dog breeds and puppies'),
('Cats', 'All cat breeds and kittens'),
('Birds', 'Pet birds including parrots, finches, and more'),
('Fish', 'Aquarium fish'),
('Small Pets', 'Hamsters, rabbits, guinea pigs, and other small animals'),
('Reptiles', 'Snakes, lizards, turtles and more'),
('Pet Food', 'Food for all types of pets'),
('Pet Accessories', 'Toys, beds, cages and more'),
('Grooming', 'Pet grooming products');

-- Add sub-categories for dogs
INSERT INTO categories (name, description, parent_id) VALUES 
('German Shepherd', 'German Shepherd dogs and puppies', 1),
('Rottweiler', 'Rottweiler dogs and puppies', 1),
('Labrador', 'Labrador Retrievers', 1),
('Poodle', 'Poodles of all sizes', 1),
('Local Breeds', 'Mixed-breed and indigenous dogs', 1);

-- Create payments table
CREATE TABLE payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    payment_method ENUM('mpesa', 'pesapal', 'cash_on_delivery') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'completed', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    checkout_request_id VARCHAR(100) NULL,
    reference VARCHAR(100) NULL,
    transaction_code VARCHAR(50) NULL,
    payment_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
);

CREATE TABLE contact (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    sender_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('unread', 'read', 'responded') DEFAULT 'unread',
    FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Create admin_roles table
CREATE TABLE admin_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    admin_role ENUM('master', 'product', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Settings table SQL structure
CREATE TABLE settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Admin logs table SQL structure
CREATE TABLE admin_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action TEXT NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Insert initial settings values
INSERT INTO settings (setting_key, value, description) VALUES
-- Platform Settings
('site_name', 'Jambo Pets', 'Website name'),
('site_logo', 'uploads/logo/default-logo.png', 'Website logo path'),
('contact_email', 'info@jambopets.co.ke', 'Contact email address'),
('contact_phone', '+254700000000', 'Contact phone number'),
('contact_address', 'Nairobi, Kenya', 'Physical address'),
('facebook_link', 'https://facebook.com/jambopets', 'Facebook page URL'),
('twitter_link', 'https://twitter.com/jambopets', 'Twitter profile URL'),
('instagram_link', 'https://instagram.com/jambopets', 'Instagram profile URL'),

-- M-Pesa Integration Settings
('mpesa_consumer_key', '', 'M-Pesa API Consumer Key'),
('mpesa_consumer_secret', '', 'M-Pesa API Consumer Secret'),
('mpesa_shortcode', '', 'M-Pesa Business Shortcode'),
('mpesa_passkey', '', 'M-Pesa API Passkey'),

-- PesaPal Integration Settings
('pesapal_consumer_key', '', 'PesaPal API Consumer Key'),
('pesapal_consumer_secret', '', 'PesaPal API Consumer Secret'),
('pesapal_shortcode', '', 'PesaPal Business Shortcode'),
('pesapal_passkey', '', 'PesaPal API Passkey'),

-- Additional Platform Settings
('commission_rate', '10', 'Platform commission percentage on sales'),
('maintenance_mode', '0', 'Site maintenance mode (0=off, 1=on)'),
('max_listing_images', '5', 'Maximum number of images per listing'),
('enable_seller_verification', '1', 'Require seller verification (0=off, 1=on)'),
('enable_email_notifications', '1', 'Enable email notifications (0=off, 1=on)'),
('enable_sms_notifications', '0', 'Enable SMS notifications (0=off, 1=on)'),
('currency_symbol', 'KSh', 'Currency symbol'),
('currency_code', 'KES', 'Currency code');

-- Newsletter subscribers table
CREATE TABLE newsletter_subscribers (
    subscriber_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    subscription_date DATETIME NOT NULL,
    status ENUM('active', 'unsubscribed', 'bounced') DEFAULT 'active',
    interests VARCHAR(255),
    confirmation_token VARCHAR(64),
    confirmed BOOLEAN DEFAULT FALSE,
    unsubscribe_token VARCHAR(64),
    last_email_sent DATETIME,
    source VARCHAR(100) COMMENT 'Where the subscription originated from',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (email),
    INDEX (status)
);

-- Optional: Newsletter campaign tracking table
CREATE TABLE newsletter_campaigns (
    campaign_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    sent_date DATETIME,
    status ENUM('draft', 'scheduled', 'sent', 'cancelled') DEFAULT 'draft',
    recipient_count INT DEFAULT 0,
    open_count INT DEFAULT 0,
    click_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Optional: Email tracking table for advanced analytics
CREATE TABLE newsletter_tracking (
    tracking_id INT AUTO_INCREMENT PRIMARY KEY,
    subscriber_id INT NOT NULL,
    campaign_id INT NOT NULL,
    opened BOOLEAN DEFAULT FALSE,
    opened_at DATETIME,
    clicked BOOLEAN DEFAULT FALSE,
    clicked_at DATETIME,
    click_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subscriber_id) REFERENCES newsletter_subscribers(subscriber_id) ON DELETE CASCADE,
    FOREIGN KEY (campaign_id) REFERENCES newsletter_campaigns(campaign_id) ON DELETE CASCADE,
    INDEX (subscriber_id, campaign_id)
);