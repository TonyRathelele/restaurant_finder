<?php
require_once 'config/config.php';

$database = new Database();
$db = $database->getConnection();

// Get filter parameters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$location = isset($_GET['location']) ? sanitize($_GET['location']) : '';
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'rating';

// Build query to search both restaurants and menu items
if (!empty($search)) {
    // Search in restaurants and menu items
    $query = "SELECT DISTINCT r.*, 
              CASE 
                  WHEN r.name LIKE :search OR r.description LIKE :search THEN 'restaurant'
                  WHEN mi.name LIKE :search OR mi.description LIKE :search THEN 'menu'
                  ELSE 'restaurant'
              END as match_type
              FROM restaurants r
              LEFT JOIN menu_items mi ON r.id = mi.restaurant_id AND mi.is_available = 1
              WHERE r.status = 'active' 
              AND (r.name LIKE :search OR r.description LIKE :search 
                   OR mi.name LIKE :search OR mi.description LIKE :search)";
    $params = [':search' => '%' . $search . '%'];
} else {
    // No search - show all restaurants
    $query = "SELECT *, 'restaurant' as match_type FROM restaurants WHERE status = 'active'";
    $params = [];
}

if (!empty($location)) {
    if (!empty($search)) {
        $query .= " AND r.location = :location";
    } else {
        $query .= " AND location = :location";
    }
    $params[':location'] = $location;
}

// Add sorting
switch ($sort) {
    case 'name':
        $query .= " ORDER BY name ASC";
        break;
    case 'location':
        $query .= " ORDER BY location ASC, name ASC";
        break;
    case 'rating':
    default:
        $query .= " ORDER BY rating DESC, name ASC";
        break;
}

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$restaurants = $stmt->fetchAll();

// Get matching menu items for search results
$matching_menu_items = [];
if (!empty($search)) {
    $menu_query = "SELECT mi.*, r.name as restaurant_name, c.name as category_name
                   FROM menu_items mi
                   JOIN restaurants r ON mi.restaurant_id = r.id
                   JOIN menu_categories c ON mi.category_id = c.id
                   WHERE r.status = 'active' AND mi.is_available = 1
                   AND (mi.name LIKE :search OR mi.description LIKE :search)";
    $menu_stmt = $db->prepare($menu_query);
    $menu_stmt->bindValue(':search', '%' . $search . '%');
    $menu_stmt->execute();
    $matching_menu_items = $menu_stmt->fetchAll();
}

// Get all locations for filter
$query = "SELECT DISTINCT location FROM restaurants WHERE status = 'active' ORDER BY location";
$stmt = $db->prepare($query);
$stmt->execute();
$locations = $stmt->fetchAll();

// Get total count
$total_restaurants = count($restaurants);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Directory - Restaurant Finder</title>
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
            background: var(--bg-light);
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
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 80px 0 60px;
            margin-top: 76px;
        }
        
        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .page-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .filter-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin: -30px 0 40px;
            position: relative;
            z-index: 10;
        }
        
        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 1rem;
        }
        
        .form-control:focus, .form-select:focus {
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
        
        .btn-clear {
            background: var(--text-light);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-clear:hover {
            background: #5d6d7e;
            color: white;
        }
        
        .results-header {
            display: flex;
            justify-content-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .results-count {
            color: var(--text-light);
            font-size: 1.1rem;
        }
        
        .sort-dropdown {
            min-width: 200px;
        }
        
        .restaurant-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
            margin-bottom: 30px;
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
            position: relative;
        }
        
        .restaurant-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--secondary-color);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
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
        
        .restaurant-details {
            display: flex;
            justify-content-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .restaurant-phone {
            color: var(--text-light);
            font-size: 0.9rem;
        }
        
        .restaurant-phone i {
            margin-right: 5px;
            color: var(--secondary-color);
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
        
        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-light);
        }
        
        .no-results i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: var(--text-light);
        }
        
        .pagination {
            justify-content: center;
            margin-top: 40px;
        }
        
        .page-link {
            color: var(--primary-color);
            border: 2px solid #e9ecef;
            margin: 0 5px;
            border-radius: 10px;
            padding: 10px 15px;
        }
        
        .page-link:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
            color: white;
        }
        
        .page-item.active .page-link {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .menu-results-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .menu-item-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .menu-item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
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
            padding: 20px;
        }
        
        .menu-item-name {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
        }
        
        .menu-item-restaurant {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 8px;
        }
        
        .menu-item-category {
            margin-bottom: 10px;
        }
        
        .menu-item-description {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 15px;
            line-height: 1.4;
        }
        
        .menu-item-price {
            color: var(--secondary-color);
            font-size: 1.2rem;
            margin-bottom: 15px;
        }
        
        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }
            
            .filter-section {
                margin: -20px 0 30px;
                padding: 20px;
            }
            
            .results-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .sort-dropdown {
                width: 100%;
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
                        <a class="nav-link active" href="restaurants.php">Restaurants</a>
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

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="page-title">Restaurant Directory</h1>
                    <p class="page-subtitle">Discover amazing dining experiences across South Africa</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Filter Section -->
    <div class="container">
        <div class="filter-section">
            <form method="GET" action="restaurants.php">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Search restaurants, cuisine, or menu items...">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="location" class="form-label">Location</label>
                        <select class="form-select" id="location" name="location">
                            <option value="">All Locations</option>
                            <?php foreach ($locations as $loc): ?>
                                <option value="<?php echo htmlspecialchars($loc['location']); ?>" 
                                        <?php echo $location == $loc['location'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($loc['location']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="sort" class="form-label">Sort By</label>
                        <select class="form-select" id="sort" name="sort">
                            <option value="rating" <?php echo $sort == 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                            <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Name A-Z</option>
                            <option value="location" <?php echo $sort == 'location' ? 'selected' : ''; ?>>Location</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3 d-flex align-items-end">
                        <div class="d-grid gap-2 w-100">
                            <button type="submit" class="btn btn-search">
                                <i class="fas fa-search me-1"></i>Search
                            </button>
                            <a href="restaurants.php" class="btn btn-clear">
                                <i class="fas fa-times me-1"></i>Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results Header -->
        <div class="results-header">
            <div class="results-count">
                <i class="fas fa-utensils me-2"></i>
                <?php echo $total_restaurants; ?> restaurant<?php echo $total_restaurants != 1 ? 's' : ''; ?> found
                <?php if (!empty($matching_menu_items)): ?>
                    <span class="text-muted">and <?php echo count($matching_menu_items); ?> menu item<?php echo count($matching_menu_items) != 1 ? 's' : ''; ?></span>
                <?php endif; ?>
                <?php if (!empty($search) || !empty($location)): ?>
                    <span class="text-muted">
                        <?php if (!empty($search)): ?>
                            for "<?php echo htmlspecialchars($search); ?>"
                        <?php endif; ?>
                        <?php if (!empty($location)): ?>
                            in <?php echo htmlspecialchars($location); ?>
                        <?php endif; ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Matching Menu Items Section -->
        <?php if (!empty($matching_menu_items)): ?>
        <div class="menu-results-section">
            <h3 class="mb-4">
                <i class="fas fa-utensils me-2"></i>Matching Menu Items
                <span class="badge bg-primary ms-2"><?php echo count($matching_menu_items); ?></span>
            </h3>
            <div class="row">
                <?php foreach ($matching_menu_items as $item): ?>
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
                                <a href="restaurant.php?id=<?php echo $item['restaurant_id']; ?>" class="btn btn-sm btn-outline-primary">
                                    View Restaurant
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Restaurants Grid -->
        <?php if (empty($restaurants)): ?>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h3>No restaurants found</h3>
                <p>Try adjusting your search criteria or browse all restaurants.</p>
                <a href="restaurants.php" class="btn btn-primary">
                    <i class="fas fa-list me-2"></i>View All Restaurants
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($restaurants as $restaurant): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="restaurant-card">
                            <div class="restaurant-image" <?php if ($restaurant['image_url']): ?>style="background-image: url('<?php echo htmlspecialchars($restaurant['image_url']); ?>'); background-size: cover; background-position: center;"<?php endif; ?>>
                                <?php if (!$restaurant['image_url']): ?>
                                    <i class="fas fa-utensils"></i>
                                <?php endif; ?>
                                <div class="restaurant-badge">
                                    <?php echo htmlspecialchars($restaurant['location']); ?>
                                </div>
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
                                <div class="restaurant-details">
                                    <?php if ($restaurant['phone']): ?>
                                        <div class="restaurant-phone">
                                            <i class="fas fa-phone"></i>
                                            <?php echo htmlspecialchars($restaurant['phone']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <a href="restaurant.php?id=<?php echo $restaurant['id']; ?>" class="btn-view">
                                    <i class="fas fa-eye me-2"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-submit form when sort changes
        document.getElementById('sort').addEventListener('change', function() {
            this.form.submit();
        });
        
        // Auto-submit form when location changes
        document.getElementById('location').addEventListener('change', function() {
            this.form.submit();
        });
    </script>
</body>
</html>
