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
        $restaurant_id = intval($_POST['restaurant_id']);
        $category_id = intval($_POST['category_id']);
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        $price = floatval($_POST['price']);
        $is_available = isset($_POST['is_available']) ? 1 : 0;
        $sort_order = intval($_POST['sort_order']);
        $image_url = sanitize($_POST['image_url']);
        
        // Handle image upload
        $uploaded_image_url = '';
        if (isset($_FILES['menu_image']) && $_FILES['menu_image']['error'] == 0) {
            $upload_dir = '../uploads/menu/';
            $file_extension = strtolower(pathinfo($_FILES['menu_image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                $filename = uniqid() . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['menu_image']['tmp_name'], $upload_path)) {
                    $uploaded_image_url = 'uploads/menu/' . $filename;
                } else {
                    $error = 'Failed to upload image.';
                }
            } else {
                $error = 'Invalid file type. Please upload JPG, PNG, or GIF images only.';
            }
        }
        
        // Use uploaded image if available, otherwise use URL
        $final_image_url = !empty($uploaded_image_url) ? $uploaded_image_url : $image_url;
        
        if (empty($name) || $price <= 0 || $restaurant_id <= 0 || $category_id <= 0) {
            $error = 'Please fill in all required fields with valid values.';
        } else {
            try {
                if ($action == 'add') {
                    $query = "INSERT INTO menu_items (restaurant_id, category_id, name, description, price, is_available, sort_order, image_url) 
                              VALUES (:restaurant_id, :category_id, :name, :description, :price, :is_available, :sort_order, :image_url)";
                } else {
                    $id = intval($_POST['id']);
                    $query = "UPDATE menu_items SET restaurant_id = :restaurant_id, category_id = :category_id, 
                              name = :name, description = :description, price = :price, is_available = :is_available, 
                              sort_order = :sort_order, image_url = :image_url WHERE id = :id";
                }
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(':restaurant_id', $restaurant_id);
                $stmt->bindParam(':category_id', $category_id);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':price', $price);
                $stmt->bindParam(':is_available', $is_available);
                $stmt->bindParam(':sort_order', $sort_order);
                $stmt->bindParam(':image_url', $final_image_url);
                
                if ($action == 'edit') {
                    $stmt->bindParam(':id', $id);
                }
                
                $stmt->execute();
                $message = $action == 'add' ? 'Menu item added successfully!' : 'Menu item updated successfully!';
                
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    } elseif ($action == 'delete') {
        $id = intval($_POST['id']);
        try {
            $query = "DELETE FROM menu_items WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $message = 'Menu item deleted successfully!';
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

// Get menu items with restaurant and category names
$query = "SELECT mi.*, r.name as restaurant_name, c.name as category_name 
          FROM menu_items mi 
          JOIN restaurants r ON mi.restaurant_id = r.id 
          JOIN menu_categories c ON mi.category_id = c.id 
          ORDER BY mi.restaurant_id, mi.category_id, mi.sort_order, mi.name";
$stmt = $db->prepare($query);
$stmt->execute();
$menu_items = $stmt->fetchAll();

// Get restaurants for dropdown
$query = "SELECT * FROM restaurants WHERE status = 'active' ORDER BY name";
$stmt = $db->prepare($query);
$stmt->execute();
$restaurants = $stmt->fetchAll();

// Get categories for dropdown
$query = "SELECT * FROM menu_categories ORDER BY sort_order, name";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll();

// Get menu item for editing
$edit_item = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "SELECT * FROM menu_items WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $edit_item = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Management - Admin</title>
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
                            <a class="nav-link" href="restaurants.php">
                                <i class="fas fa-store me-2"></i>Restaurants
                            </a>
                            <a class="nav-link active" href="menus.php">
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
                        <h2>Menu Management</h2>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#menuModal">
                            <i class="fas fa-plus me-2"></i>Add Menu Item
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
                    
                    <!-- Menu Items Table -->
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Item Name</th>
                                            <th>Restaurant</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>Available</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($menu_items as $item): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                                    <?php if ($item['description']): ?>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars(substr($item['description'], 0, 50)) . '...'; ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($item['restaurant_name']); ?></td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo htmlspecialchars($item['category_name']); ?></span>
                                                </td>
                                                <td><strong><?php echo formatPrice($item['price']); ?></strong></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $item['is_available'] ? 'success' : 'secondary'; ?>">
                                                        <?php echo $item['is_available'] ? 'Available' : 'Unavailable'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="editMenuItem(<?php echo htmlspecialchars(json_encode($item)); ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteMenuItem(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['name']); ?>')">
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
    
    <!-- Menu Item Modal -->
    <div class="modal fade" id="menuModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Add Menu Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="add">
                        <input type="hidden" name="id" id="menuItemId">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
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
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">Category *</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Item Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Price (R) *</label>
                                <input type="number" class="form-control" id="price" name="price" 
                                       min="0" step="0.01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                       min="0" value="0">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image_url" class="form-label">Image URL</label>
                            <input type="url" class="form-control" id="image_url" name="image_url" 
                                   placeholder="https://example.com/image.jpg">
                        </div>
                        
                        <div class="mb-3">
                            <label for="menu_image" class="form-label">Upload Menu Item Image</label>
                            <input type="file" class="form-control" id="menu_image" name="menu_image" 
                                   accept="image/*">
                            <div class="form-text">Upload an image for this menu item (JPG, PNG, GIF)</div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_available" name="is_available" checked>
                                <label class="form-check-label" for="is_available">
                                    Available for ordering
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Menu Item</button>
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
                        <p>Are you sure you want to delete the menu item "<span id="deleteName"></span>"?</p>
                        <p class="text-danger"><strong>This action cannot be undone!</strong></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Menu Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editMenuItem(item) {
            document.getElementById('modalTitle').textContent = 'Edit Menu Item';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('menuItemId').value = item.id;
            document.getElementById('restaurant_id').value = item.restaurant_id;
            document.getElementById('category_id').value = item.category_id;
            document.getElementById('name').value = item.name;
            document.getElementById('description').value = item.description;
            document.getElementById('price').value = item.price;
            document.getElementById('sort_order').value = item.sort_order;
            document.getElementById('is_available').checked = item.is_available == 1;
            document.getElementById('image_url').value = item.image_url || '';
            
            new bootstrap.Modal(document.getElementById('menuModal')).show();
        }
        
        function deleteMenuItem(id, name) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteName').textContent = name;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
        
        // Reset form when modal is closed
        document.getElementById('menuModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('modalTitle').textContent = 'Add Menu Item';
            document.getElementById('formAction').value = 'add';
            document.getElementById('menuItemId').value = '';
            document.querySelector('#menuModal form').reset();
            document.getElementById('is_available').checked = true;
        });
    </script>
</body>
</html>
