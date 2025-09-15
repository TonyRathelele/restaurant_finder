<?php
require_once '../config/config.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();

$message = '';
$error = '';

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'];
    
    if ($action == 'add' || $action == 'edit') {
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        $location = sanitize($_POST['location']);
        $address = sanitize($_POST['address']);
        $phone = sanitize($_POST['phone']);
        $email = sanitize($_POST['email']);
        $opening_hours = sanitize($_POST['opening_hours']);
        $rating = floatval($_POST['rating']);
        $latitude = floatval($_POST['latitude']);
        $longitude = floatval($_POST['longitude']);
        $status = sanitize($_POST['status']);
        $image_url = sanitize($_POST['image_url']);
        $video_url = sanitize($_POST['video_url']);
        
        // Handle image upload
        $uploaded_image_url = '';
        if (isset($_FILES['restaurant_image']) && $_FILES['restaurant_image']['error'] == 0) {
            $upload_dir = '../uploads/restaurants/';
            $file_extension = strtolower(pathinfo($_FILES['restaurant_image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                $filename = uniqid() . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['restaurant_image']['tmp_name'], $upload_path)) {
                    $uploaded_image_url = 'uploads/restaurants/' . $filename;
                } else {
                    $error = 'Failed to upload image.';
                }
            } else {
                $error = 'Invalid file type. Please upload JPG, PNG, or GIF images only.';
            }
        }
        
        // Use uploaded image if available, otherwise use URL
        $final_image_url = !empty($uploaded_image_url) ? $uploaded_image_url : $image_url;
        
        if (empty($name) || empty($location) || empty($address)) {
            $error = 'Please fill in all required fields.';
        } else {
            try {
                if ($action == 'add') {
                    $query = "INSERT INTO restaurants (name, description, location, address, phone, email, opening_hours, rating, latitude, longitude, status, image_url, video_url) 
                              VALUES (:name, :description, :location, :address, :phone, :email, :opening_hours, :rating, :latitude, :longitude, :status, :image_url, :video_url)";
                } else {
                    $id = intval($_POST['id']);
                    $query = "UPDATE restaurants SET name = :name, description = :description, location = :location, 
                              address = :address, phone = :phone, email = :email, opening_hours = :opening_hours, 
                              rating = :rating, latitude = :latitude, longitude = :longitude, status = :status, 
                              image_url = :image_url, video_url = :video_url 
                              WHERE id = :id";
                }
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':location', $location);
                $stmt->bindParam(':address', $address);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':opening_hours', $opening_hours);
                $stmt->bindParam(':rating', $rating);
                $stmt->bindParam(':latitude', $latitude);
                $stmt->bindParam(':longitude', $longitude);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':image_url', $final_image_url);
                $stmt->bindParam(':video_url', $video_url);
                
                if ($action == 'edit') {
                    $stmt->bindParam(':id', $id);
                }
                
                $stmt->execute();
                $message = $action == 'add' ? 'Restaurant added successfully!' : 'Restaurant updated successfully!';
                
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    } elseif ($action == 'delete') {
        $id = intval($_POST['id']);
        try {
            $query = "DELETE FROM restaurants WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $message = 'Restaurant deleted successfully!';
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

// Get restaurants
$query = "SELECT * FROM restaurants ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$restaurants = $stmt->fetchAll();

// Get restaurant for editing
$edit_restaurant = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "SELECT * FROM restaurants WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $edit_restaurant = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Management - Admin</title>
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
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                            <a class="nav-link active" href="restaurants.php">
                                <i class="fas fa-store me-2"></i>Restaurants
                            </a>
                            <a class="nav-link" href="menus.php">
                                <i class="fas fa-book-open me-2"></i>Menus
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
                        <h2>Restaurant Management</h2>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#restaurantModal">
                            <i class="fas fa-plus me-2"></i>Add Restaurant
                        </button>
                    </div>
                    
                    <!-- Messages -->
                    <?php if ($message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Restaurants Table -->
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Location</th>
                                            <th>Phone</th>
                                            <th>Rating</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($restaurants as $restaurant): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($restaurant['name']); ?></strong>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars(substr($restaurant['description'], 0, 50)) . '...'; ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($restaurant['location']); ?></td>
                                                <td><?php echo htmlspecialchars($restaurant['phone']); ?></td>
                                                <td>
                                                    <div class="text-warning">
                                                        <?php echo getStars($restaurant['rating']); ?>
                                                        <small class="text-muted ms-1"><?php echo formatRating($restaurant['rating']); ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $restaurant['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                        <?php echo ucfirst($restaurant['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="editRestaurant(<?php echo htmlspecialchars(json_encode($restaurant)); ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteRestaurant(<?php echo $restaurant['id']; ?>, '<?php echo htmlspecialchars($restaurant['name']); ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Restaurant Modal -->
    <div class="modal fade" id="restaurantModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Add Restaurant</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="add">
                        <input type="hidden" name="id" id="restaurantId">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Restaurant Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Location *</label>
                                <input type="text" class="form-control" id="location" name="location" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Address *</label>
                            <textarea class="form-control" id="address" name="address" rows="2" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="opening_hours" class="form-label">Opening Hours</label>
                            <input type="text" class="form-control" id="opening_hours" name="opening_hours" 
                                   placeholder="e.g., Mon-Sun: 12:00-22:00">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="rating" class="form-label">Rating</label>
                                <input type="number" class="form-control" id="rating" name="rating" 
                                       min="0" max="5" step="0.1" value="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="latitude" class="form-label">Latitude</label>
                                <input type="number" class="form-control" id="latitude" name="latitude" 
                                       step="any" placeholder="e.g., -26.1467">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="longitude" class="form-label">Longitude</label>
                                <input type="number" class="form-control" id="longitude" name="longitude" 
                                       step="any" placeholder="e.g., 28.0436">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="image_url" class="form-label">Main Image URL</label>
                                <input type="url" class="form-control" id="image_url" name="image_url" 
                                       placeholder="https://example.com/image.jpg">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="video_url" class="form-label">Video URL</label>
                                <input type="url" class="form-control" id="video_url" name="video_url" 
                                       placeholder="https://example.com/video.mp4">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="restaurant_image" class="form-label">Upload Restaurant Image</label>
                            <input type="file" class="form-control" id="restaurant_image" name="restaurant_image" 
                                   accept="image/*">
                            <div class="form-text">Upload a main image for the restaurant (JPG, PNG, GIF)</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Restaurant</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteId">
                        <p>Are you sure you want to delete the restaurant "<span id="deleteName"></span>"?</p>
                        <p class="text-danger"><strong>This action cannot be undone!</strong></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Restaurant</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editRestaurant(restaurant) {
            document.getElementById('modalTitle').textContent = 'Edit Restaurant';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('restaurantId').value = restaurant.id;
            document.getElementById('name').value = restaurant.name;
            document.getElementById('description').value = restaurant.description;
            document.getElementById('location').value = restaurant.location;
            document.getElementById('address').value = restaurant.address;
            document.getElementById('phone').value = restaurant.phone;
            document.getElementById('email').value = restaurant.email;
            document.getElementById('opening_hours').value = restaurant.opening_hours;
            document.getElementById('rating').value = restaurant.rating;
            document.getElementById('latitude').value = restaurant.latitude;
            document.getElementById('longitude').value = restaurant.longitude;
            document.getElementById('status').value = restaurant.status;
            document.getElementById('image_url').value = restaurant.image_url || '';
            document.getElementById('video_url').value = restaurant.video_url || '';
            
            new bootstrap.Modal(document.getElementById('restaurantModal')).show();
        }
        
        function deleteRestaurant(id, name) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteName').textContent = name;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
        
        // Reset form when modal is closed
        document.getElementById('restaurantModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('modalTitle').textContent = 'Add Restaurant';
            document.getElementById('formAction').value = 'add';
            document.getElementById('restaurantId').value = '';
            document.querySelector('#restaurantModal form').reset();
        });
    </script>
</body>
</html>
