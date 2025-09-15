<?php
require_once '../config/config.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats = [];

// Total restaurants
$query = "SELECT COUNT(*) as total FROM restaurants WHERE status = 'active'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['restaurants'] = $stmt->fetch()['total'];

// Total menu items
$query = "SELECT COUNT(*) as total FROM menu_items mi 
          JOIN restaurants r ON mi.restaurant_id = r.id 
          WHERE r.status = 'active' AND mi.is_available = 1";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['menu_items'] = $stmt->fetch()['total'];

// Total gallery images
$query = "SELECT COUNT(*) as total FROM restaurant_images ri 
          JOIN restaurants r ON ri.restaurant_id = r.id 
          WHERE r.status = 'active'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['gallery_images'] = $stmt->fetch()['total'];

// Recent restaurants
$query = "SELECT * FROM restaurants ORDER BY created_at DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_restaurants = $stmt->fetchAll();

// Top rated restaurants
$query = "SELECT * FROM restaurants WHERE status = 'active' ORDER BY rating DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$top_rated = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Restaurant Finder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 0;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .stat-card .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar">
                    <div class="p-3">
                        <h4 class="text-white mb-4">
                            <i class="fas fa-utensils me-2"></i>Restaurant Admin
                        </h4>
                        <nav class="nav flex-column">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                            <a class="nav-link" href="restaurants.php">
                                <i class="fas fa-store me-2"></i>Restaurants
                            </a>
                            <a class="nav-link" href="menus.php">
                                <i class="fas fa-book-open me-2"></i>Menus
                            </a>
                            <a class="nav-link" href="gallery.php">
                                <i class="fas fa-images me-2"></i>Gallery
                            </a>
                            <a class="nav-link" href="categories.php">
                                <i class="fas fa-tags me-2"></i>Categories
                            </a>
                            <a class="nav-link" href="../index.php" target="_blank">
                                <i class="fas fa-external-link-alt me-2"></i>View Website
                            </a>
                            <hr class="text-white-50">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </nav>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="p-4">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Dashboard</h2>
                        <div class="text-muted">
                            Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!
                        </div>
                    </div>
                    
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-6 col-lg-3">
                            <div class="stat-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0"><?php echo $stats['restaurants']; ?></h3>
                                        <p class="mb-0">Active Restaurants</p>
                                    </div>
                                    <i class="fas fa-store stat-icon"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="stat-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0"><?php echo $stats['menu_items']; ?></h3>
                                        <p class="mb-0">Menu Items</p>
                                    </div>
                                    <i class="fas fa-book-open stat-icon"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="stat-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0"><?php echo $stats['gallery_images']; ?></h3>
                                        <p class="mb-0">Gallery Images</p>
                                    </div>
                                    <i class="fas fa-images stat-icon"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="stat-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0">4.7</h3>
                                        <p class="mb-0">Avg Rating</p>
                                    </div>
                                    <i class="fas fa-star stat-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Content Rows -->
                    <div class="row">
                        <!-- Recent Restaurants -->
                        <div class="col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Restaurants</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($recent_restaurants)): ?>
                                        <p class="text-muted">No restaurants found.</p>
                                    <?php else: ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach ($recent_restaurants as $restaurant): ?>
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($restaurant['name']); ?></h6>
                                                        <small class="text-muted"><?php echo htmlspecialchars($restaurant['location']); ?></small>
                                                    </div>
                                                    <span class="badge bg-<?php echo $restaurant['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                        <?php echo ucfirst($restaurant['status']); ?>
                                                    </span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Top Rated Restaurants -->
                        <div class="col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-star me-2"></i>Top Rated Restaurants</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($top_rated)): ?>
                                        <p class="text-muted">No restaurants found.</p>
                                    <?php else: ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach ($top_rated as $restaurant): ?>
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($restaurant['name']); ?></h6>
                                                        <small class="text-muted"><?php echo htmlspecialchars($restaurant['location']); ?></small>
                                                    </div>
                                                    <div class="text-warning">
                                                        <?php echo getStars($restaurant['rating']); ?>
                                                        <small class="text-muted ms-1"><?php echo formatRating($restaurant['rating']); ?></small>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-2">
                                            <a href="restaurants.php?action=add" class="btn btn-primary w-100">
                                                <i class="fas fa-plus me-2"></i>Add Restaurant
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <a href="menus.php?action=add" class="btn btn-success w-100">
                                                <i class="fas fa-plus me-2"></i>Add Menu Item
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <a href="gallery.php" class="btn btn-info w-100">
                                                <i class="fas fa-images me-2"></i>Manage Gallery
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <a href="categories.php" class="btn btn-secondary w-100">
                                                <i class="fas fa-tags me-2"></i>Manage Categories
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <a href="../index.php" target="_blank" class="btn btn-warning w-100">
                                                <i class="fas fa-external-link-alt me-2"></i>View Website
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
