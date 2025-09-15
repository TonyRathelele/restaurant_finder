<?php
require_once '../config/config.php';

// Check if admin is logged in
requireLogin();

$database = new Database();
$db = $database->getConnection();

$message = '';
$error = '';

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'];
    
    if ($action == 'add_image') {
        $restaurant_id = intval($_POST['restaurant_id']);
        $image_url = sanitize($_POST['image_url']);
        $alt_text = sanitize($_POST['alt_text']);
        $sort_order = intval($_POST['sort_order']);
        
        // Handle file upload
        $uploaded_image_url = '';
        if (isset($_FILES['gallery_image']) && $_FILES['gallery_image']['error'] == 0) {
            $upload_dir = '../' . GALLERY_IMAGES_PATH;
            $file_extension = strtolower(pathinfo($_FILES['gallery_image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                $filename = uniqid() . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['gallery_image']['tmp_name'], $upload_path)) {
                    $uploaded_image_url = GALLERY_IMAGES_PATH . $filename;
                } else {
                    $error = 'Failed to upload image.';
                }
            } else {
                $error = 'Invalid file type. Please upload JPG, PNG, or GIF images only.';
            }
        }
        
        // Use uploaded image if available, otherwise use URL
        $final_image_url = !empty($uploaded_image_url) ? $uploaded_image_url : $image_url;
        
        if (empty($final_image_url) || empty($alt_text)) {
            $error = 'Please provide image URL and alt text.';
        } else {
            try {
                $query = "INSERT INTO restaurant_images (restaurant_id, image_url, alt_text, sort_order) 
                          VALUES (:restaurant_id, :image_url, :alt_text, :sort_order)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':restaurant_id', $restaurant_id);
                $stmt->bindParam(':image_url', $final_image_url);
                $stmt->bindParam(':alt_text', $alt_text);
                $stmt->bindParam(':sort_order', $sort_order);
                $stmt->execute();
                $message = 'Gallery image added successfully!';
            } catch (Exception $e) {
                $error = 'Failed to add gallery image.';
            }
        }
    }
    
    if ($action == 'edit_image') {
        $id = intval($_POST['id']);
        $image_url = sanitize($_POST['image_url']);
        $alt_text = sanitize($_POST['alt_text']);
        $sort_order = intval($_POST['sort_order']);
        
        // Handle file upload
        $uploaded_image_url = '';
        if (isset($_FILES['gallery_image']) && $_FILES['gallery_image']['error'] == 0) {
            $upload_dir = '../' . GALLERY_IMAGES_PATH;
            $file_extension = strtolower(pathinfo($_FILES['gallery_image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                $filename = uniqid() . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['gallery_image']['tmp_name'], $upload_path)) {
                    $uploaded_image_url = GALLERY_IMAGES_PATH . $filename;
                } else {
                    $error = 'Failed to upload image.';
                }
            } else {
                $error = 'Invalid file type. Please upload JPG, PNG, or GIF images only.';
            }
        }
        
        // Use uploaded image if available, otherwise use URL
        $final_image_url = !empty($uploaded_image_url) ? $uploaded_image_url : $image_url;
        
        if (empty($final_image_url) || empty($alt_text)) {
            $error = 'Please provide image URL and alt text.';
        } else {
            try {
                $query = "UPDATE restaurant_images SET image_url = :image_url, alt_text = :alt_text, sort_order = :sort_order WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':image_url', $final_image_url);
                $stmt->bindParam(':alt_text', $alt_text);
                $stmt->bindParam(':sort_order', $sort_order);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $message = 'Gallery image updated successfully!';
            } catch (Exception $e) {
                $error = 'Failed to update gallery image.';
            }
        }
    }
    
    if ($action == 'delete_image') {
        $id = intval($_POST['id']);
        try {
            $query = "DELETE FROM restaurant_images WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $message = 'Gallery image deleted successfully!';
        } catch (Exception $e) {
            $error = 'Failed to delete gallery image.';
        }
    }
}

// Get restaurants for dropdown
$restaurants_query = "SELECT id, name FROM restaurants ORDER BY name";
$restaurants_stmt = $db->prepare($restaurants_query);
$restaurants_stmt->execute();
$restaurants = $restaurants_stmt->fetchAll();

// Get gallery images with restaurant names
$gallery_query = "SELECT ri.*, r.name as restaurant_name 
                  FROM restaurant_images ri 
                  JOIN restaurants r ON ri.restaurant_id = r.id 
                  ORDER BY r.name, ri.sort_order, ri.id";
$gallery_stmt = $db->prepare($gallery_query);
$gallery_stmt->execute();
$gallery_images = $gallery_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Management - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gallery-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        .gallery-card {
            transition: transform 0.2s;
        }
        .gallery-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-utensils me-2"></i>Restaurant Admin
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="restaurants.php">Restaurants</a>
                <a class="nav-link" href="menus.php">Menus</a>
                <a class="nav-link active" href="gallery.php">Gallery</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-images me-2"></i>Gallery Management</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addImageModal">
                        <i class="fas fa-plus me-2"></i>Add Gallery Image
                    </button>
                </div>
                
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
                
                <!-- Debug Info -->
                <div class="alert alert-info">
                    <strong>Debug Info:</strong> Found <?php echo count($gallery_images); ?> gallery images in database.
                </div>
                
                <!-- Gallery Images Grid -->
                <div class="row">
                    <?php foreach ($gallery_images as $image): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="card gallery-card h-100">
                                <img src="<?php echo (strpos($image['image_url'], 'http') === 0) ? htmlspecialchars($image['image_url']) : '../' . htmlspecialchars($image['image_url']); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($image['alt_text']); ?>"
                                     style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h6 class="card-title"><?php echo htmlspecialchars($image['alt_text']); ?></h6>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            <i class="fas fa-store me-1"></i><?php echo htmlspecialchars($image['restaurant_name']); ?>
                                        </small>
                                    </p>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            <i class="fas fa-sort me-1"></i>Order: <?php echo $image['sort_order']; ?>
                                        </small>
                                    </p>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            <i class="fas fa-link me-1"></i>URL: <?php echo htmlspecialchars($image['image_url']); ?>
                                        </small>
                                    </p>
                                </div>
                                <div class="card-footer">
                                    <button class="btn btn-sm btn-outline-primary" 
                                            onclick="editImage(<?php echo htmlspecialchars(json_encode($image)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" 
                                            onclick="deleteImage(<?php echo $image['id']; ?>, '<?php echo htmlspecialchars($image['alt_text']); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (empty($gallery_images)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-images fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No gallery images found</h4>
                        <p class="text-muted">Add some images to get started!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Add Image Modal -->
    <div class="modal fade" id="addImageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Gallery Image</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_image">
                        
                        <div class="mb-3">
                            <label for="restaurant_id" class="form-label">Restaurant *</label>
                            <select class="form-select" id="restaurant_id" name="restaurant_id" required>
                                <option value="">Select Restaurant</option>
                                <?php foreach ($restaurants as $restaurant): ?>
                                    <option value="<?php echo $restaurant['id']; ?>">
                                        <?php echo htmlspecialchars($restaurant['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="image_url" class="form-label">Image URL</label>
                                <input type="url" class="form-control" id="image_url" name="image_url" 
                                       placeholder="https://example.com/image.jpg">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                       min="0" value="0">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="gallery_image" class="form-label">Upload Image</label>
                            <input type="file" class="form-control" id="gallery_image" name="gallery_image" 
                                   accept="image/*">
                            <div class="form-text">Upload an image file (JPG, PNG, GIF) or provide a URL above</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="alt_text" class="form-label">Alt Text *</label>
                            <input type="text" class="form-control" id="alt_text" name="alt_text" 
                                   placeholder="Describe the image" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Image</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Image Modal -->
    <div class="modal fade" id="editImageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Gallery Image</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_image">
                        <input type="hidden" name="id" id="editId">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_image_url" class="form-label">Image URL</label>
                                <input type="url" class="form-control" id="edit_image_url" name="image_url" 
                                       placeholder="https://example.com/image.jpg">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control" id="edit_sort_order" name="sort_order" 
                                       min="0" value="0">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_gallery_image" class="form-label">Upload New Image</label>
                            <input type="file" class="form-control" id="edit_gallery_image" name="gallery_image" 
                                   accept="image/*">
                            <div class="form-text">Upload a new image file to replace the current one</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_alt_text" class="form-label">Alt Text *</label>
                            <input type="text" class="form-control" id="edit_alt_text" name="alt_text" 
                                   placeholder="Describe the image" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Image</button>
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
                        <input type="hidden" name="action" value="delete_image">
                        <input type="hidden" name="id" id="deleteId">
                        <p>Are you sure you want to delete the image "<span id="deleteName"></span>"?</p>
                        <p class="text-danger"><strong>This action cannot be undone.</strong></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Image</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editImage(image) {
            document.getElementById('editId').value = image.id;
            document.getElementById('edit_image_url').value = image.image_url || '';
            document.getElementById('edit_alt_text').value = image.alt_text || '';
            document.getElementById('edit_sort_order').value = image.sort_order || 0;
            new bootstrap.Modal(document.getElementById('editImageModal')).show();
        }
        
        function deleteImage(id, name) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteName').textContent = name;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
        
        // Reset forms when modals are closed
        document.getElementById('addImageModal').addEventListener('hidden.bs.modal', function () {
            this.querySelector('form').reset();
        });
        
        document.getElementById('editImageModal').addEventListener('hidden.bs.modal', function () {
            this.querySelector('form').reset();
        });
    </script>
</body>
</html>
