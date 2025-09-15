-- Restaurant Web App Database Schema
CREATE DATABASE IF NOT EXISTS restaurant_finder;
USE restaurant_finder;

-- Admin users table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Restaurants table
CREATE TABLE restaurants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    location VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    opening_hours TEXT,
    image_url VARCHAR(255),
    video_url VARCHAR(255),
    rating DECIMAL(3,2) DEFAULT 0.00,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Menu categories table
CREATE TABLE menu_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Menu items table
CREATE TABLE menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(255),
    is_available BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES menu_categories(id) ON DELETE CASCADE
);

-- Restaurant images table (for multiple images per restaurant)
CREATE TABLE restaurant_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255),
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
);

-- Insert default admin user (password: admin123)
INSERT INTO admins (username, password, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@restaurant.com');

-- Insert default menu categories
INSERT INTO menu_categories (name, description, sort_order) VALUES 
('Starters', 'Appetizers and small plates', 1),
('Main Course', 'Main dishes and entrees', 2),
('Desserts', 'Sweet treats and desserts', 3),
('Drinks', 'Beverages and cocktails', 4);

-- Insert sample restaurants with high-quality Unsplash images
INSERT INTO restaurants (name, description, location, address, phone, email, opening_hours, rating, latitude, longitude, image_url) VALUES 
('Marble Restaurant', 'Experience fine casual flavour in the Mother City. Set in an impressive timber-clad building at the V&A Waterfront, Marble Cape Town celebrates South African culinary culture.', 'Rosebank', 'Shop 153, Level 1, Rosebank Mall, Cnr Bath & Tyrwhitt Ave, Rosebank, Johannesburg', '+27 11 447 2332', 'info@marble.co.za', 'Mon-Sun: 12:00-22:00', 4.5, -26.1467, 28.0436, 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80'),
('The Test Kitchen', 'Contemporary South African cuisine with a focus on local ingredients and innovative techniques.', 'Woodstock', '375 Albert Rd, Woodstock, Cape Town', '+27 21 447 2337', 'info@thetestkitchen.co.za', 'Tue-Sat: 18:30-21:30', 4.8, -33.9249, 18.4241, 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?ixlib=rb-4.0.3&auto=format&fit=crop&w=2074&q=80'),
('La Colombe', 'Award-winning restaurant offering modern South African cuisine with stunning mountain views.', 'Constantia', 'Silvermist Wine Estate, Constantia Nek, Cape Town', '+27 21 794 2390', 'info@lacolombe.co.za', 'Tue-Sun: 12:00-14:00, 19:00-21:00', 4.7, -34.0167, 18.4167, 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80'),
('Nobu Cape Town', 'World-renowned Japanese-Peruvian fusion cuisine in an elegant waterfront setting.', 'V&A Waterfront', 'V&A Waterfront, Cape Town', '+27 21 431 4511', 'info@nobu.co.za', 'Mon-Sun: 12:00-23:00', 4.6, -33.9048, 18.4187, 'https://images.unsplash.com/photo-1551218808-94e220e084d2?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80'),
('Greenhouse Restaurant', 'Fine dining with a focus on sustainable, locally-sourced ingredients in a beautiful garden setting.', 'Constantia', 'The Cellars-Hohenort Hotel, Constantia', '+27 21 794 2137', 'info@greenhouse.co.za', 'Tue-Sat: 19:00-22:00', 4.9, -34.0167, 18.4167, 'https://images.unsplash.com/photo-1559339352-11d035aa65de?ixlib=rb-4.0.3&auto=format&fit=crop&w=2074&q=80'),
('The Pot Luck Club', 'Trendy rooftop restaurant offering innovative tapas-style dishes with panoramic city views.', 'Woodstock', 'The Old Biscuit Mill, Woodstock', '+27 21 447 0804', 'info@potluckclub.co.za', 'Tue-Sat: 18:30-22:00', 4.4, -33.9249, 18.4241, 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?ixlib=rb-4.0.3&auto=format&fit=crop&w=2074&q=80');

-- Insert sample menu items with food images from Unsplash
INSERT INTO menu_items (restaurant_id, category_id, name, description, price, image_url) VALUES 
-- Marble Restaurant Menu Items
(1, 1, 'Beef Carpaccio', 'Thinly sliced raw beef with rocket, parmesan, and truffle oil', 180.00, 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80'),
(1, 1, 'Oysters', 'Fresh oysters with mignonette sauce', 25.00, 'https://images.unsplash.com/photo-1551218808-94e220e084d2?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80'),
(1, 2, 'Ribeye Steak', '300g grass-fed ribeye with chimichurri and roasted vegetables', 450.00, 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80'),
(1, 2, 'Lamb Rack', 'Herb-crusted lamb rack with ratatouille', 420.00, 'https://images.unsplash.com/photo-1559339352-11d035aa65de?ixlib=rb-4.0.3&auto=format&fit=crop&w=2074&q=80'),
(1, 3, 'Chocolate Fondant', 'Warm chocolate fondant with vanilla ice cream', 120.00, 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?ixlib=rb-4.0.3&auto=format&fit=crop&w=2074&q=80'),
(1, 3, 'Tiramisu', 'Classic Italian dessert with coffee and mascarpone', 95.00, 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80'),
(1, 4, 'Craft Beer', 'Selection of local craft beers', 45.00, 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80'),
(1, 4, 'Wine Selection', 'Curated selection of South African wines', 80.00, 'https://images.unsplash.com/photo-1551218808-94e220e084d2?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80'),

-- Additional menu items for other restaurants
(2, 1, 'Tuna Tartare', 'Fresh yellowfin tuna with avocado and citrus dressing', 220.00, 'https://images.unsplash.com/photo-1559339352-11d035aa65de?ixlib=rb-4.0.3&auto=format&fit=crop&w=2074&q=80'),
(2, 2, 'Duck Breast', 'Pan-seared duck breast with cherry reduction', 380.00, 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?ixlib=rb-4.0.3&auto=format&fit=crop&w=2074&q=80'),
(3, 1, 'Lobster Bisque', 'Rich and creamy lobster soup with cognac', 180.00, 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80'),
(3, 2, 'Wagyu Beef', 'Premium wagyu beef with truffle butter', 650.00, 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80'),
(4, 1, 'Sashimi Platter', 'Fresh selection of premium sashimi', 320.00, 'https://images.unsplash.com/photo-1551218808-94e220e084d2?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80'),
(4, 2, 'Black Cod Miso', 'Signature black cod with miso marinade', 420.00, 'https://images.unsplash.com/photo-1559339352-11d035aa65de?ixlib=rb-4.0.3&auto=format&fit=crop&w=2074&q=80'),
(5, 1, 'Garden Salad', 'Fresh seasonal vegetables from our garden', 120.00, 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?ixlib=rb-4.0.3&auto=format&fit=crop&w=2074&q=80'),
(5, 2, 'Sustainable Fish', 'Line-caught fish with seasonal accompaniments', 280.00, 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80'),
(6, 1, 'Tapas Selection', 'Chef\'s selection of Spanish-inspired small plates', 180.00, 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80'),
(6, 2, 'Rooftop Burger', 'Wagyu beef burger with truffle aioli', 220.00, 'https://images.unsplash.com/photo-1551218808-94e220e084d2?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');

-- Insert sample restaurant images with diverse Unsplash photos
INSERT INTO restaurant_images (restaurant_id, image_url, alt_text, sort_order) VALUES 
-- Marble Restaurant Images
(1, 'https://images.unsplash.com/photo-1551218808-94e220e084d2?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80', 'Marble Restaurant Elegant Interior', 1),
(1, 'https://images.unsplash.com/photo-1559339352-11d035aa65de?ixlib=rb-4.0.3&auto=format&fit=crop&w=2074&q=80', 'Marble Restaurant Dining Area', 2),
(1, 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?ixlib=rb-4.0.3&auto=format&fit=crop&w=2074&q=80', 'Marble Restaurant Modern Kitchen', 3),
(1, 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80', 'Marble Restaurant Wine Bar', 4),

-- The Test Kitchen Images
(2, 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80', 'The Test Kitchen Contemporary Interior', 1),
(2, 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80', 'The Test Kitchen Bar Area', 2),
(2, 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80', 'The Test Kitchen Open Kitchen', 3),

-- La Colombe Images
(3, 'https://images.unsplash.com/photo-1559339352-11d035aa65de?ixlib=rb-4.0.3&auto=format&fit=crop&w=2074&q=80', 'La Colombe Fine Dining Room', 1),
(3, 'https://images.unsplash.com/photo-1551218808-94e220e084d2?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80', 'La Colombe Wine Cellar', 2),
(3, 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80', 'La Colombe Mountain View Terrace', 3),

-- Nobu Cape Town Images
(4, 'https://images.unsplash.com/photo-1551218808-94e220e084d2?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80', 'Nobu Cape Town Waterfront View', 1),
(4, 'https://images.unsplash.com/photo-1559339352-11d035aa65de?ixlib=rb-4.0.3&auto=format&fit=crop&w=2074&q=80', 'Nobu Cape Town Sushi Bar', 2),
(4, 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?ixlib=rb-4.0.3&auto=format&fit=crop&w=2074&q=80', 'Nobu Cape Town Modern Interior', 3),

-- Greenhouse Restaurant Images
(5, 'https://images.unsplash.com/photo-1559339352-11d035aa65de?ixlib=rb-4.0.3&auto=format&fit=crop&w=2074&q=80', 'Greenhouse Restaurant Garden Setting', 1),
(5, 'https://images.unsplash.com/photo-1551218808-94e220e084d2?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80', 'Greenhouse Restaurant Conservatory', 2),
(5, 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80', 'Greenhouse Restaurant Sustainable Dining', 3),

-- The Pot Luck Club Images
(6, 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?ixlib=rb-4.0.3&auto=format&fit=crop&w=2074&q=80', 'The Pot Luck Club Rooftop View', 1),
(6, 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80', 'The Pot Luck Club Trendy Interior', 2),
(6, 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80', 'The Pot Luck Club Tapas Bar', 3);
