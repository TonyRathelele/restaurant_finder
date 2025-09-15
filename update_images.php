<?php
// Database Image Update Utility
// This script helps you update existing images in the database

require_once 'config/database.php';

// Function to update restaurant images
function updateRestaurantImage($db, $restaurant_id, $new_image_url) {
    try {
        $query = "UPDATE restaurants SET image_url = :image_url WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':image_url', $new_image_url);
        $stmt->bindParam(':id', $restaurant_id);
        $stmt->execute();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Function to update menu item images
function updateMenuItemImage($db, $menu_item_id, $new_image_url) {
    try {
        $query = "UPDATE menu_items SET image_url = :image_url WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':image_url', $new_image_url);
        $stmt->bindParam(':id', $menu_item_id);
        $stmt->execute();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Function to get all restaurants
function getAllRestaurants($db) {
    $query = "SELECT id, name, image_url FROM restaurants ORDER BY name";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Function to get all menu items
function getAllMenuItems($db) {
    $query = "SELECT mi.id, mi.name, mi.image_url, r.name as restaurant_name 
              FROM menu_items mi 
              JOIN restaurants r ON mi.restaurant_id = r.id 
              ORDER BY r.name, mi.name";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Handle form submissions
$message = '';
$error = '';

if ($_POST) {
    $action = $_POST['action'];
    
    if ($action == 'update_restaurant') {
        $restaurant_id = intval($_POST['restaurant_id']);
        $new_image_url = trim($_POST['new_image_url']);
        
        if (empty($new_image_url)) {
            $error = 'Please enter a valid image URL.';
        } else {
            if (updateRestaurantImage($db, $restaurant_id, $new_image_url)) {
                $message = 'Restaurant image updated successfully!';
            } else {
                $error = 'Failed to update restaurant image.';
            }
        }
    }
    
    if ($action == 'update_menu_item') {
        $menu_item_id = intval($_POST['menu_item_id']);
        $new_image_url = trim($_POST['new_image_url']);
        
        if (empty($new_image_url)) {
            $error = 'Please enter a valid image URL.';
        } else {
            if (updateMenuItemImage($db, $menu_item_id, $new_image_url)) {
                $message = 'Menu item image updated successfully!';
            } else {
                $error = 'Failed to update menu item image.';
            }
        }
    }
}

// Get current data
$restaurants = getAllRestaurants($db);
$menu_items = getAllMenuItems($db);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Database Images</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <h1 class="mb-4">
                    <i class="fas fa-images me-2"></i>Update Database Images
                </h1>
                
                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Restaurant Images Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3><i class="fas fa-store me-2"></i>Restaurant Images</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Restaurant</th>
                                        <th>Current Image</th>
                                        <th>New Image URL</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($restaurants as $restaurant): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($restaurant['name']); ?></strong>
                                            </td>
                                            <td>
                                                <?php if ($restaurant['image_url']): ?>
                                                    <img src="<?php echo htmlspecialchars($restaurant['image_url']); ?>" 
                                                         alt="Current Image" 
                                                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($restaurant['image_url']); ?></small>
                                                <?php else: ?>
                                                    <span class="text-muted">No image</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="POST" class="d-flex">
                                                    <input type="hidden" name="action" value="update_restaurant">
                                                    <input type="hidden" name="restaurant_id" value="<?php echo $restaurant['id']; ?>">
                                                    <input type="url" name="new_image_url" class="form-control me-2" 
                                                           placeholder="https://example.com/new-image.jpg" required>
                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-save"></i> Update
                                                    </button>
                                                </form>
                                            </td>
                                            <td>
                                                <a href="restaurant.php?id=<?php echo $restaurant['id']; ?>" 
                                                   class="btn btn-outline-info btn-sm" target="_blank">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Menu Items Images Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3><i class="fas fa-utensils me-2"></i>Menu Item Images</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Menu Item</th>
                                        <th>Restaurant</th>
                                        <th>Current Image</th>
                                        <th>New Image URL</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($menu_items as $item): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars($item['restaurant_name']); ?></small>
                                            </td>
                                            <td>
                                                <?php if ($item['image_url']): ?>
                                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                                         alt="Current Image" 
                                                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($item['image_url']); ?></small>
                                                <?php else: ?>
                                                    <span class="text-muted">No image</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="POST" class="d-flex">
                                                    <input type="hidden" name="action" value="update_menu_item">
                                                    <input type="hidden" name="menu_item_id" value="<?php echo $item['id']; ?>">
                                                    <input type="url" name="new_image_url" class="form-control me-2" 
                                                           placeholder="https://example.com/new-image.jpg" required>
                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-save"></i> Update
                                                    </button>
                                                </form>
                                            </td>
                                            <td>
                                                <a href="restaurants.php" 
                                                   class="btn btn-outline-info btn-sm" target="_blank">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Update Section -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-magic me-2"></i>Quick Image Updates</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Popular Image Sources:</h5>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-link me-2"></i><strong>Unsplash:</strong> https://images.unsplash.com/photo-...</li>
                                    <li><i class="fas fa-link me-2"></i><strong>Pexels:</strong> https://images.pexels.com/photos/...</li>
                                    <li><i class="fas fa-link me-2"></i><strong>Pixabay:</strong> https://cdn.pixabay.com/photo/...</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5>Tips:</h5>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-info-circle me-2 text-info"></i>Use high-quality images (at least 800x600px)</li>
                                    <li><i class="fas fa-info-circle me-2 text-info"></i>Ensure images are publicly accessible</li>
                                    <li><i class="fas fa-info-circle me-2 text-info"></i>Use HTTPS URLs for better security</li>
                                    <li><i class="fas fa-info-circle me-2 text-info"></i>Test URLs before updating</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <a href="admin/dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Admin Dashboard
                    </a>
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i>View Frontend
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
