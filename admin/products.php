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

// Handle Delete Product
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Get image path before deleting
    $result = $conn->query("SELECT image FROM products WHERE id = $id");
    if ($result && $row = $result->fetch_assoc()) {
        if ($row['image'] && file_exists('../' . $row['image'])) {
            unlink('../' . $row['image']);
        }
    }
    
    $conn->query("DELETE FROM products WHERE id = $id");
    header('Location: products.php?success=deleted');
    exit();
}

// Handle Add/Edit Product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category = $conn->real_escape_string($_POST['category']);
    $status = $_POST['status'];
    
    // Handle image upload
    $imagePath = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $uploadDir = '../uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imagePath = 'uploads/products/' . $fileName;
            
            // Delete old image if updating
            if ($id > 0) {
                $result = $conn->query("SELECT image FROM products WHERE id = $id");
                if ($result && $row = $result->fetch_assoc()) {
                    if ($row['image'] && file_exists('../' . $row['image'])) {
                        unlink('../' . $row['image']);
                    }
                }
            }
        } else {
            $error = "Failed to upload image";
        }
    }
    
    if ($id > 0) {
        // Update existing product
        $sql = "UPDATE products SET 
                name = '$name', 
                description = '$description', 
                price = $price, 
                stock = $stock, 
                category = '$category', 
                status = '$status'";
        
        if ($imagePath) {
            $sql .= ", image = '$imagePath'";
        }
        
        $sql .= " WHERE id = $id";
    } else {
        // Insert new product
        $sql = "INSERT INTO products (name, description, price, stock, category, image, status) 
                VALUES ('$name', '$description', $price, $stock, '$category', '$imagePath', '$status')";
    }
    
    if ($conn->query($sql)) {
        header('Location: products.php?success=' . ($id > 0 ? 'updated' : 'added'));
        exit();
    } else {
        $error = "Error: " . $conn->error;
    }
}

// Get all products
$products = $conn->query("SELECT * FROM products ORDER BY createdAt DESC");

// Get categories for dropdown
$categories = $conn->query("SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management - Admin</title>
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
                    <h1><i class="fas fa-box"></i> Products Management</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal" onclick="resetForm()">
                        <i class="fas fa-plus"></i> Add New Product
                    </button>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i>
                        <?php 
                            if ($_GET['success'] === 'added') echo 'Product added successfully!';
                            if ($_GET['success'] === 'updated') echo 'Product updated successfully!';
                            if ($_GET['success'] === 'deleted') echo 'Product deleted successfully!';
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Products Table -->
                <div class="card admin-card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($products->num_rows > 0): ?>
                                        <?php while($product = $products->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $product['id']; ?></td>
                                                <td>
                                                    <?php if ($product['image'] && file_exists('../' . $product['image'])): ?>
                                                        <img src="../<?php echo htmlspecialchars($product['image']); ?>" 
                                                             alt="Product" class="product-thumbnail">
                                                    <?php else: ?>
                                                        <div class="no-image">No Image</div>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="highlight-text"><?php echo htmlspecialchars($product['name']); ?></td>
                                                <td><?php echo htmlspecialchars($product['category']); ?></td>
                                                <td class="highlight-price">$<?php echo number_format($product['price'], 2); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $product['stock'] > 10 ? 'success' : ($product['stock'] > 0 ? 'warning' : 'danger'); ?>">
                                                        <?php echo $product['stock']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $product['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                        <?php echo ucfirst($product['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-info" onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">No products found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Modal -->
    <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Add New Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="productId">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label highlight-label">Product Name *</label>
                                <input type="text" class="form-control" name="name" id="productName" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label highlight-label">Category *</label>
                                <select class="form-control" name="category" id="productCategory" required>
                                    <option value="">Select Category</option>
                                    <?php 
                                    $categories->data_seek(0);
                                    while($cat = $categories->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo htmlspecialchars($cat['name']); ?>">
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <textarea class="form-control" name="description" id="productDescription" rows="3" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label highlight-label">Price ($) *</label>
                                <input type="number" class="form-control" name="price" id="productPrice" step="0.01" min="0" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label highlight-label">Stock *</label>
                                <input type="number" class="form-control" name="stock" id="productStock" min="0" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status *</label>
                                <select class="form-control" name="status" id="productStatus" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Product Image</label>
                            <input type="file" class="form-control" name="image" id="productImage" accept="image/*">
                            <small class="text-muted">Leave empty to keep existing image</small>
                            <div id="currentImage"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function resetForm() {
            document.getElementById('productId').value = '';
            document.getElementById('productName').value = '';
            document.getElementById('productCategory').value = '';
            document.getElementById('productDescription').value = '';
            document.getElementById('productPrice').value = '';
            document.getElementById('productStock').value = '';
            document.getElementById('productStatus').value = 'active';
            document.getElementById('productImage').value = '';
            document.getElementById('currentImage').innerHTML = '';
            document.getElementById('modalTitle').textContent = 'Add New Product';
        }
        
        function editProduct(product) {
            document.getElementById('productId').value = product.id;
            document.getElementById('productName').value = product.name;
            document.getElementById('productCategory').value = product.category;
            document.getElementById('productDescription').value = product.description;
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productStock').value = product.stock;
            document.getElementById('productStatus').value = product.status;
            
            if (product.image) {
                document.getElementById('currentImage').innerHTML = 
                    `<div class="mt-2"><img src="../${product.image}" style="max-width: 150px; border-radius: 6px;"/></div>`;
            }
            
            document.getElementById('modalTitle').textContent = 'Edit Product';
            new bootstrap.Modal(document.getElementById('productModal')).show();
        }
        
        function deleteProduct(id, name) {
            if (confirm(`Are you sure you want to delete "${name}"?`)) {
                window.location.href = `products.php?delete=${id}`;
            }
        }
    </script>
</body>
</html>