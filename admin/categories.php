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
        $sort_order = intval($_POST['sort_order']);
        
        if (empty($name)) {
            $error = 'Please fill in the category name.';
        } else {
            try {
                if ($action == 'add') {
                    $query = "INSERT INTO menu_categories (name, description, sort_order) VALUES (:name, :description, :sort_order)";
                } else {
                    $id = intval($_POST['id']);
                    $query = "UPDATE menu_categories SET name = :name, description = :description, sort_order = :sort_order WHERE id = :id";
                }
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':sort_order', $sort_order);
                
                if ($action == 'edit') {
                    $stmt->bindParam(':id', $id);
                }
                
                $stmt->execute();
                $message = $action == 'add' ? 'Category added successfully!' : 'Category updated successfully!';
                
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    } elseif ($action == 'delete') {
        $id = intval($_POST['id']);
        try {
            // Check if category is being used
            $query = "SELECT COUNT(*) as count FROM menu_items WHERE category_id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                $error = 'Cannot delete category. It is being used by ' . $result['count'] . ' menu item(s).';
            } else {
                $query = "DELETE FROM menu_categories WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $message = 'Category deleted successfully!';
            }
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

// Get categories
$query = "SELECT c.*, COUNT(mi.id) as item_count 
          FROM menu_categories c 
          LEFT JOIN menu_items mi ON c.id = mi.category_id 
          GROUP BY c.id 
          ORDER BY c.sort_order, c.name";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll();

// Get category for editing
$edit_category = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "SELECT * FROM menu_categories WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $edit_category = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management - Admin</title>
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
                            <a class="nav-link" href="menus.php">
                                <i class="fas fa-book-open me-2"></i>Menus
                            </a>
                            <a class="nav-link active" href="categories.php">
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
                        <h2>Category Management</h2>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
                            <i class="fas fa-plus me-2"></i>Add Category
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
                    
                    <!-- Categories Table -->
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Sort Order</th>
                                            <th>Category Name</th>
                                            <th>Description</th>
                                            <th>Menu Items</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($categories as $category): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-secondary"><?php echo $category['sort_order']; ?></span>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                                </td>
                                                <td>
                                                    <?php if ($category['description']): ?>
                                                        <?php echo htmlspecialchars(substr($category['description'], 0, 50)) . '...'; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">No description</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo $category['item_count']; ?> items</span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo date('M j, Y', strtotime($category['created_at'])); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <?php if ($category['item_count'] == 0): ?>
                                                        <button class="btn btn-sm btn-outline-danger" 
                                                                onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-outline-secondary" disabled 
                                                                title="Cannot delete - category is in use">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
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
    
    <!-- Category Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Add Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="add">
                        <input type="hidden" name="id" id="categoryId">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                   min="0" value="0">
                            <div class="form-text">Lower numbers appear first in menus</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Category</button>
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
                        <p>Are you sure you want to delete the category "<span id="deleteName"></span>"?</p>
                        <p class="text-danger"><strong>This action cannot be undone!</strong></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editCategory(category) {
            document.getElementById('modalTitle').textContent = 'Edit Category';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('categoryId').value = category.id;
            document.getElementById('name').value = category.name;
            document.getElementById('description').value = category.description;
            document.getElementById('sort_order').value = category.sort_order;
            
            new bootstrap.Modal(document.getElementById('categoryModal')).show();
        }
        
        function deleteCategory(id, name) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteName').textContent = name;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
        
        // Reset form when modal is closed
        document.getElementById('categoryModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('modalTitle').textContent = 'Add Category';
            document.getElementById('formAction').value = 'add';
            document.getElementById('categoryId').value = '';
            document.querySelector('#categoryModal form').reset();
        });
    </script>
</body>
</html>
