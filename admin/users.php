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

// Handle role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $userId = intval($_POST['user_id']);
    $role = $_POST['role'];
    
    // Prevent admin from removing their own admin role
    if ($userId != $_SESSION['user_id']) {
        $conn->query("UPDATE users SET role = '$role' WHERE id = $userId");
        header('Location: users.php?success=updated');
        exit();
    } else {
        $error = "You cannot change your own role!";
    }
}

// Handle delete user
if (isset($_GET['delete'])) {
    $userId = intval($_GET['delete']);
    
    // Prevent admin from deleting themselves
    if ($userId != $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE id = $userId");
        header('Location: users.php?success=deleted');
        exit();
    } else {
        $error = "You cannot delete your own account!";
    }
}

$users = $conn->query("SELECT * FROM users ORDER BY createdAt DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - Admin</title>
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
                    <h1><i class="fas fa-users"></i> Users Management</h1>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i>
                        <?php 
                            if ($_GET['success'] === 'updated') echo 'User updated successfully!';
                            if ($_GET['success'] === 'deleted') echo 'User deleted successfully!';
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card admin-card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Profile</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Gender</th>
                                        <th>Birthday</th>
                                        <th>Role</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($user = $users->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td>
                                                <?php if ($user['profile_picture']): ?>
                                                    <img src="../<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                                                         alt="Profile" class="profile-thumbnail">
                                                <?php else: ?>
                                                    <div class="no-profile-pic">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="highlight-text">
                                                <?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['gender']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($user['birthday'])); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>">
                                                    <?php echo ucfirst($user['role']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($user['createdAt'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info" onclick="editUserRole(<?php echo $user['id']; ?>, '<?php echo $user['role']; ?>', '<?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?>')">
                                                    <i class="fas fa-user-shield"></i>
                                                </button>
                                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
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
    </div>

    <!-- Role Update Modal -->
    <div class="modal fade" id="roleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Update User Role</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="userId">
                        <p>Update role for <strong class="highlight-text" id="userName"></strong></p>
                        <div class="mb-3">
                            <label class="form-label highlight-label">Select Role</label>
                            <select name="role" id="userRole" class="form-control" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_role" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editUserRole(id, role, name) {
            document.getElementById('userId').value = id;
            document.getElementById('userRole').value = role;
            document.getElementById('userName').textContent = name;
            new bootstrap.Modal(document.getElementById('roleModal')).show();
        }
        
        function deleteUser(id, name) {
            if (confirm(`Are you sure you want to delete user "${name}"?`)) {
                window.location.href = `users.php?delete=${id}`;
            }
        }
    </script>
</body>
</html>