<?php
require_once 'config/config.php';

$database = new Database();
$db = $database->getConnection();

// Get restaurant ID
$restaurant_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($restaurant_id <= 0) {
    header('Location: restaurants.php');
    exit();
}

// Get restaurant details
$query = "SELECT * FROM restaurants WHERE id = :id AND status = 'active'";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $restaurant_id);
$stmt->execute();
$restaurant = $stmt->fetch();

if (!$restaurant) {
    header('Location: restaurants.php');
    exit();
}

// Get menu items for this restaurant
$query = "SELECT mi.*, c.name as category_name 
          FROM menu_items mi 
          JOIN menu_categories c ON mi.category_id = c.id 
          WHERE mi.restaurant_id = :restaurant_id AND mi.is_available = 1 
          ORDER BY c.sort_order, mi.sort_order, mi.name";
$stmt = $db->prepare($query);
$stmt->bindParam(':restaurant_id', $restaurant_id);
$stmt->execute();
$menu_items = $stmt->fetchAll();

// Group menu items by category
$menu_by_category = [];
foreach ($menu_items as $item) {
    $menu_by_category[$item['category_name']][] = $item;
}

// Get restaurant images
$query = "SELECT * FROM restaurant_images 
          WHERE restaurant_id = :restaurant_id 
          ORDER BY sort_order, created_at";
$stmt = $db->prepare($query);
$stmt->bindParam(':restaurant_id', $restaurant_id);
$stmt->execute();
$restaurant_images = $stmt->fetchAll();

// Get related restaurants (same location, different restaurant)
$query = "SELECT * FROM restaurants 
          WHERE location = :location AND id != :id AND status = 'active' 
          ORDER BY rating DESC LIMIT 3";
$stmt = $db->prepare($query);
$stmt->bindParam(':location', $restaurant['location']);
$stmt->bindParam(':id', $restaurant_id);
$stmt->execute();
$related_restaurants = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($restaurant['name']); ?> - Restaurant Finder</title>
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
        
        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
        }
        
        .navbar {
            background: white;
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
        
        .hero-section {
            position: relative;
            height: 60vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            margin-top: 76px;
        }
        
        .hero-content h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .hero-content p {
            font-size: 1.3rem;
            opacity: 0.9;
        }
        
        .restaurant-info {
            background: white;
            border-radius: 15px;
            padding: 40px;
            margin: -50px 0 40px;
            position: relative;
            z-index: 10;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            padding: 20px;
            background: var(--bg-light);
            border-radius: 10px;
        }
        
        .info-icon {
            width: 60px;
            height: 60px;
            background: var(--secondary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 20px;
            flex-shrink: 0;
        }
        
        .info-content h4 {
            margin-bottom: 5px;
            color: var(--text-dark);
        }
        
        .info-content p {
            margin: 0;
            color: var(--text-light);
        }
        
        .rating-section {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .rating-stars {
            font-size: 2rem;
            color: var(--accent-color);
            margin-bottom: 10px;
        }
        
        .rating-number {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin-bottom: 30px;
            color: var(--text-dark);
            text-align: center;
        }
        
        .menu-section {
            background: white;
            border-radius: 15px;
            padding: 40px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .menu-category {
            margin-bottom: 40px;
        }
        
        .category-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid var(--secondary-color);
        }
        
        .menu-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 20px 0;
            border-bottom: 1px solid #eee;
        }
        
        .menu-item:last-child {
            border-bottom: none;
        }
        
        .menu-item-info h5 {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 5px;
        }
        
        .menu-item-info p {
            color: var(--text-light);
            margin: 0;
            font-size: 0.9rem;
        }
        
        .menu-item-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-left: 20px;
            flex-shrink: 0;
        }
        
        .map-section {
            background: white;
            border-radius: 15px;
            padding: 40px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .map-container {
            height: 400px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .map-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        .related-restaurants {
            background: white;
            border-radius: 15px;
            padding: 40px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .related-card {
            background: var(--bg-light);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .related-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .gallery-section {
            background: white;
            border-radius: 15px;
            padding: 40px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .gallery-item:hover {
            transform: scale(1.05);
        }
        
        .gallery-item img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: all 0.3s ease;
        }
        
        .gallery-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }
        
        .gallery-overlay i {
            color: white;
            font-size: 2rem;
        }
        
        .modal-dialog {
            max-width: 90vw;
            max-height: 90vh;
        }
        
        .modal-body {
            padding: 0;
            text-align: center;
        }
        
        .modal-body img {
            max-width: 100%;
            max-height: 80vh;
            object-fit: contain;
        }
        
        .related-card h5 {
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-dark);
        }
        
        .related-card p {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .related-rating {
            color: var(--accent-color);
            margin-bottom: 15px;
        }
        
        .btn-view {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .btn-view:hover {
            background: #34495e;
            color: white;
        }
        
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 20px;
        }
        
        .breadcrumb-item a {
            color: var(--secondary-color);
            text-decoration: none;
        }
        
        .breadcrumb-item.active {
            color: var(--text-light);
        }
        
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .restaurant-info {
                margin: -30px 0 30px;
                padding: 30px 20px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .menu-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .menu-item-price {
                margin-left: 0;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-utensils me-2"></i>Restaurant Finder
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="restaurants.php">Restaurants</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#search">Search</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/login.php">Admin</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section" <?php if ($restaurant['image_url']): ?>style="background-image: linear-gradient(135deg, rgba(44, 62, 80, 0.8) 0%, rgba(231, 76, 60, 0.6) 100%), url('<?php echo htmlspecialchars($restaurant['image_url']); ?>'); background-size: cover; background-position: center;"<?php endif; ?>>
        <div class="hero-content">
            <h1><?php echo htmlspecialchars($restaurant['name']); ?></h1>
            <p><?php echo htmlspecialchars($restaurant['location']); ?></p>
        </div>
    </section>

    <!-- Restaurant Info -->
    <div class="container">
        <div class="restaurant-info">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="restaurants.php">Restaurants</a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($restaurant['name']); ?></li>
                </ol>
            </nav>

            <!-- Rating Section -->
            <div class="rating-section">
                <div class="rating-stars">
                    <?php echo getStars($restaurant['rating']); ?>
                </div>
                <div class="rating-number"><?php echo formatRating($restaurant['rating']); ?> / 5.0</div>
            </div>

            <!-- Info Grid -->
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="info-content">
                        <h4>Address</h4>
                        <p><?php echo htmlspecialchars($restaurant['address']); ?></p>
                    </div>
                </div>
                
                <?php if ($restaurant['phone']): ?>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="info-content">
                        <h4>Phone</h4>
                        <p><a href="tel:<?php echo htmlspecialchars($restaurant['phone']); ?>"><?php echo htmlspecialchars($restaurant['phone']); ?></a></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($restaurant['email']): ?>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="info-content">
                        <h4>Email</h4>
                        <p><a href="mailto:<?php echo htmlspecialchars($restaurant['email']); ?>"><?php echo htmlspecialchars($restaurant['email']); ?></a></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($restaurant['opening_hours']): ?>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="info-content">
                        <h4>Opening Hours</h4>
                        <p><?php echo htmlspecialchars($restaurant['opening_hours']); ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Description -->
            <?php if ($restaurant['description']): ?>
            <div class="row">
                <div class="col-12">
                    <h4>About <?php echo htmlspecialchars($restaurant['name']); ?></h4>
                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($restaurant['description'])); ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Restaurant Gallery -->
        <?php if (!empty($restaurant_images) || $restaurant['image_url']): ?>
        <div class="gallery-section">
            <h2 class="section-title">Restaurant Gallery</h2>
            <div class="row">
                <?php if ($restaurant['image_url']): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="gallery-item" data-bs-toggle="modal" data-bs-target="#imageModal" onclick="showImage('<?php echo htmlspecialchars($restaurant['image_url']); ?>', '<?php echo htmlspecialchars($restaurant['name']); ?>')">
                            <img src="<?php echo htmlspecialchars($restaurant['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($restaurant['name']); ?>" 
                                 class="img-fluid rounded">
                            <div class="gallery-overlay">
                                <i class="fas fa-search-plus"></i>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php foreach ($restaurant_images as $image): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="gallery-item" data-bs-toggle="modal" data-bs-target="#imageModal" onclick="showImage('<?php echo htmlspecialchars($image['image_url']); ?>', '<?php echo htmlspecialchars($image['alt_text'] ?: $restaurant['name']); ?>')">
                            <img src="<?php echo htmlspecialchars($image['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($image['alt_text'] ?: $restaurant['name']); ?>" 
                                 class="img-fluid rounded">
                            <div class="gallery-overlay">
                                <i class="fas fa-search-plus"></i>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Menu Section -->
        <?php if (!empty($menu_by_category)): ?>
        <div class="menu-section">
            <h2 class="section-title">Menu</h2>
            <?php foreach ($menu_by_category as $category_name => $items): ?>
                <div class="menu-category">
                    <h3 class="category-title"><?php echo htmlspecialchars($category_name); ?></h3>
                    <?php foreach ($items as $item): ?>
                        <div class="menu-item">
                            <div class="menu-item-info">
                                <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                                <?php if ($item['description']): ?>
                                    <p><?php echo htmlspecialchars($item['description']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="menu-item-price">
                                <?php echo formatPrice($item['price']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Map Section -->
        <div class="map-section">
            <h2 class="section-title">Location</h2>
            <div class="map-container">
                <iframe src="https://www.google.com/maps?q=<?php echo urlencode($restaurant['address']); ?>&output=embed" 
                        style="border:0; height:400px; width:100%; border-radius:10px;" 
                        allowfullscreen="" 
                        loading="lazy">
                </iframe>
            </div>
            <div class="mt-3">
                <p class="text-center">
                    <i class="fas fa-map-marker-alt me-2"></i>
                    <strong><?php echo htmlspecialchars($restaurant['address']); ?></strong>
                </p>
                <?php if ($restaurant['latitude'] && $restaurant['longitude']): ?>
                    <p class="text-center text-muted small">
                        Coordinates: <?php echo $restaurant['latitude']; ?>, <?php echo $restaurant['longitude']; ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Related Restaurants -->
        <?php if (!empty($related_restaurants)): ?>
        <div class="related-restaurants">
            <h2 class="section-title">More Restaurants in <?php echo htmlspecialchars($restaurant['location']); ?></h2>
            <div class="row">
                <?php foreach ($related_restaurants as $related): ?>
                    <div class="col-md-4 mb-4">
                        <div class="related-card">
                            <h5><?php echo htmlspecialchars($related['name']); ?></h5>
                            <p><?php echo htmlspecialchars(substr($related['description'], 0, 80)) . '...'; ?></p>
                            <div class="related-rating">
                                <?php echo getStars($related['rating']); ?>
                                <span class="ms-2"><?php echo formatRating($related['rating']); ?></span>
                            </div>
                            <a href="restaurant.php?id=<?php echo $related['id']; ?>" class="btn-view">
                                View Details
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalTitle">Restaurant Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <img id="modalImage" src="" alt="" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showImage(imageUrl, imageTitle) {
            document.getElementById('modalImage').src = imageUrl;
            document.getElementById('modalImage').alt = imageTitle;
            document.getElementById('imageModalTitle').textContent = imageTitle;
        }
    </script>
</body>
</html>
