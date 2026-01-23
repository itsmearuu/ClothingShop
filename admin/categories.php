<?php
// admin/[filename].php (UPDATE THE TOP PART)
session_start();
require_once '../config.php';
require_once '../session.php';

// Check if user is admin - redirect to login with return URL
if (!isLoggedIn()) {
    $current_page = basename($_SERVER['PHP_SELF']);
    header('Location: ../login.php?redirect=admin/' . $current_page);
    exit();
}

if (!isAdmin()) {
    header('Location: ../index.php');
    exit();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM categories WHERE id = $id");
    header('Location: categories.php?success=deleted');
    exit();
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    
    if ($id > 0) {
        $sql = "UPDATE categories SET name = '$name', description = '$description' WHERE id = $id";
    } else {
        $sql = "INSERT INTO categories (name, description) VALUES ('$name', '$description')";
    }
    
    if ($conn->query($sql)) {
        header('Location: categories.php?success=' . ($id > 0 ? 'updated' : 'added'));
        exit();
    }
}

$categories = $conn->query("SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin-styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="container-fluid">
                <div class="page-header">
                    <h1><i class="fas fa-tags"></i> Categories Management</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="resetForm()">
                        <i class="fas fa-plus"></i> Add New Category
                    </button>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i>
                        <?php 
                            if ($_GET['success'] === 'added') echo 'Category added successfully!';
                            if ($_GET['success'] === 'updated') echo 'Category updated successfully!';
                            if ($_GET['success'] === 'deleted') echo 'Category deleted successfully!';
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card admin-card">
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($cat = $categories->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $cat['id']; ?></td>
                                        <td class="highlight-text"><?php echo htmlspecialchars($cat['name']); ?></td>
                                        <td><?php echo htmlspecialchars($cat['description']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($cat['createdAt'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick='editCategory(<?php echo json_encode($cat); ?>)'>
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteCategory(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars($cat['name']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
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
                        <h5 class="modal-title" id="modalTitle">Add New Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="categoryId">
                        
                        <div class="mb-3">
                            <label class="form-label highlight-label">Category Name *</label>
                            <input type="text" class="form-control" name="name" id="categoryName" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="categoryDescription" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function resetForm() {
            document.getElementById('categoryId').value = '';
            document.getElementById('categoryName').value = '';
            document.getElementById('categoryDescription').value = '';
            document.getElementById('modalTitle').textContent = 'Add New Category';
        }
        
        function editCategory(category) {
            document.getElementById('categoryId').value = category.id;
            document.getElementById('categoryName').value = category.name;
            document.getElementById('categoryDescription').value = category.description || '';
            document.getElementById('modalTitle').textContent = 'Edit Category';
            new bootstrap.Modal(document.getElementById('categoryModal')).show();
        }
        
        function deleteCategory(id, name) {
            if (confirm(`Delete category "${name}"?`)) {
                window.location.href = `categories.php?delete=${id}`;
            }
        }
    </script>
</body>
</html>