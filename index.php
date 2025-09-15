<?php
require_once 'config/config.php';

$database = new Database();
$db = $database->getConnection();

// Get featured restaurants
$query = "SELECT * FROM restaurants WHERE status = 'active' ORDER BY rating DESC LIMIT 6";
$stmt = $db->prepare($query);
$stmt->execute();
$featured_restaurants = $stmt->fetchAll();

// Get all locations for filter
$query = "SELECT DISTINCT location FROM restaurants WHERE status = 'active' ORDER BY location";
$stmt = $db->prepare($query);
$stmt->execute();
$locations = $stmt->fetchAll();

// Get popular menu items for suggestions
$query = "SELECT mi.*, r.name as restaurant_name, c.name as category_name 
          FROM menu_items mi 
          JOIN restaurants r ON mi.restaurant_id = r.id 
          JOIN menu_categories c ON mi.category_id = c.id 
          WHERE r.status = 'active' AND mi.is_available = 1 
          ORDER BY mi.price DESC 
          LIMIT 6";
$stmt = $db->prepare($query);
$stmt->execute();
$popular_menu_items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Finder - Discover Amazing Dining Experiences</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #e74c3c;
            --accent-color: #f39c12;
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --bg-light: #f8f9fa;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
        }
        
        .hero-section {
            position: relative;
            height: 100vh;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .hero-video-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -2;
        }
        
        .hero-video {
            position: absolute;
            top: 50%;
            left: 50%;
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            transform: translate(-50%, -50%);
            z-index: -1;
        }
        
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: -1;
        }
        
        .hero-content {
            text-align: center;
            color: white;
            z-index: 1;
            max-width: 800px;
            padding: 0 20px;
        }
        
        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }
        
        .hero-subtitle {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .hero-cta {
            display: inline-block;
            background: var(--secondary-color);
            color: white;
            padding: 15px 40px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }
        
        .hero-cta:hover {
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
            color: white;
        }
        
        .section {
            padding: 80px 0;
        }
        
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            text-align: center;
            margin-bottom: 3rem;
            color: var(--text-dark);
        }
        
        .restaurant-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .restaurant-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .restaurant-image {
            height: 250px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }
        
        .restaurant-info {
            padding: 25px;
        }
        
        .restaurant-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--text-dark);
        }
        
        .restaurant-location {
            color: var(--text-light);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .restaurant-location i {
            margin-right: 8px;
            color: var(--secondary-color);
        }
        
        .restaurant-rating {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .stars {
            color: var(--accent-color);
            margin-right: 10px;
        }
        
        .restaurant-description {
            color: var(--text-light);
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .btn-view {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 25px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .btn-view:hover {
            background: #34495e;
            color: white;
            transform: translateY(-2px);
        }
        
        .popular-menu-section {
            background: var(--bg-light);
            padding: 80px 0;
        }
        
        .menu-item-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .menu-item-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .menu-item-image {
            height: 200px;
            overflow: hidden;
            position: relative;
        }
        
        .menu-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .menu-item-image .no-image {
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
        }
        
        .menu-item-info {
            padding: 25px;
        }
        
        .menu-item-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 10px;
        }
        
        .menu-item-restaurant {
            color: var(--text-light);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .menu-item-restaurant i {
            margin-right: 8px;
            color: var(--secondary-color);
        }
        
        .menu-item-category {
            margin-bottom: 15px;
        }
        
        .menu-item-description {
            color: var(--text-light);
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .menu-item-price {
            color: var(--secondary-color);
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .search-section {
            background: var(--bg-light);
            padding: 60px 0;
        }
        
        .search-box {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 1rem;
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(231, 76, 60, 0.25);
        }
        
        .btn-search {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-search:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }
        
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color) !important;
        }
        
        .nav-link {
            color: var(--text-dark) !important;
            font-weight: 500;
            margin: 0 10px;
            transition: color 0.3s ease;
        }
        
        .nav-link:hover {
            color: var(--secondary-color) !important;
        }
        
        .footer {
            background: var(--primary-color);
            color: white;
            padding: 40px 0 20px;
        }
        
        .footer h5 {
            font-family: 'Playfair Display', serif;
            margin-bottom: 20px;
        }
        
        .footer a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer a:hover {
            color: white;
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.2rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-utensils me-2"></i>Restaurant Finder
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#restaurants">Restaurants</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#search">Search</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/login.php">Admin</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="hero-video-container">
            <iframe class="hero-video" 
                    src="https://www.youtube.com/embed/fTD-f-vm73o?autoplay=1&mute=1&loop=1&playlist=fTD-f-vm73o&controls=0&showinfo=0&rel=0&modestbranding=1&iv_load_policy=3&fs=0&disablekb=1&start=0" 
                    frameborder="0" 
                    allow="autoplay; encrypted-media" 
                    allowfullscreen>
            </iframe>
        </div>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1 class="hero-title">Discover Amazing Dining</h1>
            <p class="hero-subtitle">Find the perfect restaurant for every occasion in South Africa's finest locations</p>
            <a href="#restaurants" class="hero-cta">
                <i class="fas fa-search me-2"></i>Explore Restaurants
            </a>
        </div>
    </section>

    <!-- Search Section -->
    <section id="search" class="search-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="search-box">
                        <h3 class="text-center mb-4">Find Your Perfect Restaurant</h3>
                        <form action="restaurants.php" method="GET">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <input type="text" class="form-control" name="search" placeholder="Search restaurants, cuisine, or menu items...">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <select class="form-control" name="location">
                                        <option value="">All Locations</option>
                                        <?php foreach ($locations as $location): ?>
                                            <option value="<?php echo htmlspecialchars($location['location']); ?>">
                                                <?php echo htmlspecialchars($location['location']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <button type="submit" class="btn btn-search w-100">
                                        <i class="fas fa-search me-2"></i>Search
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Menu Items -->
    <?php if (!empty($popular_menu_items)): ?>
    <section class="popular-menu-section">
        <div class="container">
            <h2 class="section-title">Popular Menu Items</h2>
            <p class="text-center text-muted mb-5">Discover some of our most popular dishes</p>
            <div class="row">
                <?php foreach ($popular_menu_items as $item): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="menu-item-card">
                            <div class="menu-item-image">
                                <?php if ($item['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                         class="img-fluid">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-utensils"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="menu-item-info">
                                <h5 class="menu-item-name"><?php echo htmlspecialchars($item['name']); ?></h5>
                                <p class="menu-item-restaurant">
                                    <i class="fas fa-store me-1"></i>
                                    <?php echo htmlspecialchars($item['restaurant_name']); ?>
                                </p>
                                <p class="menu-item-category">
                                    <span class="badge bg-info"><?php echo htmlspecialchars($item['category_name']); ?></span>
                                </p>
                                <?php if ($item['description']): ?>
                                    <p class="menu-item-description"><?php echo htmlspecialchars(substr($item['description'], 0, 80)) . '...'; ?></p>
                                <?php endif; ?>
                                <div class="menu-item-price">
                                    <strong><?php echo formatPrice($item['price']); ?></strong>
                                </div>
                                <a href="restaurant.php?id=<?php echo $item['restaurant_id']; ?>" class="btn-view">
                                    <i class="fas fa-eye me-2"></i>View Restaurant
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="restaurants.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-search me-2"></i>Search All Menu Items
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Featured Restaurants -->
    <section id="restaurants" class="section">
        <div class="container">
            <h2 class="section-title">Featured Restaurants</h2>
            <div class="row">
                <?php foreach ($featured_restaurants as $restaurant): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="restaurant-card">
                            <div class="restaurant-image" <?php if ($restaurant['image_url']): ?>style="background-image: url('<?php echo htmlspecialchars($restaurant['image_url']); ?>'); background-size: cover; background-position: center;"<?php endif; ?>>
                                <?php if (!$restaurant['image_url']): ?>
                                    <i class="fas fa-utensils"></i>
                                <?php endif; ?>
                            </div>
                            <div class="restaurant-info">
                                <h3 class="restaurant-name"><?php echo htmlspecialchars($restaurant['name']); ?></h3>
                                <div class="restaurant-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($restaurant['location']); ?>
                                </div>
                                <div class="restaurant-rating">
                                    <div class="stars">
                                        <?php echo getStars($restaurant['rating']); ?>
                                    </div>
                                    <span class="text-muted"><?php echo formatRating($restaurant['rating']); ?></span>
                                </div>
                                <p class="restaurant-description">
                                    <?php echo htmlspecialchars(substr($restaurant['description'], 0, 120)) . '...'; ?>
                                </p>
                                <a href="restaurant.php?id=<?php echo $restaurant['id']; ?>" class="btn-view">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-5">
                <a href="restaurants.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-list me-2"></i>View All Restaurants
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5><i class="fas fa-utensils me-2"></i>Restaurant Finder</h5>
                    <p>Discover the best dining experiences across South Africa. From fine dining to casual eateries, find your perfect restaurant match.</p>
                </div>
                <div class="col-lg-2 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#restaurants">Restaurants</a></li>
                        <li><a href="#search">Search</a></li>
                        <li><a href="admin/login.php">Admin</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 mb-4">
                    <h5>Popular Locations</h5>
                    <ul class="list-unstyled">
                        <?php foreach (array_slice($locations, 0, 4) as $location): ?>
                            <li><a href="restaurants.php?location=<?php echo urlencode($location['location']); ?>">
                                <?php echo htmlspecialchars($location['location']); ?>
                            </a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="col-lg-3 mb-4">
                    <h5>Contact Info</h5>
                    <p><i class="fas fa-envelope me-2"></i>info@restaurantfinder.co.za</p>
                    <p><i class="fas fa-phone me-2"></i>+27 11 123 4567</p>
                    <div class="mt-3">
                        <a href="#" class="me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; 2024 Restaurant Finder. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="#" class="me-3">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add scroll effect to navbar
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
            }
        });
    </script>
</body>
</html>
