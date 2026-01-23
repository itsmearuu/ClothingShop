<?php
// admin/index.php
session_start();
require_once '../config.php';
require_once '../session.php';

// Check if user is admin - redirect to login with return URL
if (!isLoggedIn()) {
    header('Location: ../login.php?redirect=admin/index.php');
    exit();
}

if (!isAdmin()) {
    header('Location: ../index.php');
    exit();
}

// Get statistics
$stats = [];

// Total users
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
$stats['users'] = $result->fetch_assoc()['count'];

// Total products
$result = $conn->query("SELECT COUNT(*) as count FROM products");
$stats['products'] = $result->fetch_assoc()['count'];

// Total orders
$result = $conn->query("SELECT COUNT(*) as count FROM orders");
$stats['orders'] = $result->fetch_assoc()['count'];

// Total revenue
$result = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid'");
$stats['revenue'] = $result->fetch_assoc()['total'] ?? 0;

// Recent orders
$recentOrders = $conn->query("
    SELECT o.*, u.firstName, u.lastName 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.createdAt DESC 
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ClothingShop</title>
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
                    <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                    <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card stat-users">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo number_format($stats['users']); ?></h3>
                                <p>Total Users</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card stat-products">
                            <div class="stat-icon">
                                <i class="fas fa-box"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo number_format($stats['products']); ?></h3>
                                <p>Total Products</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card stat-orders">
                            <div class="stat-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo number_format($stats['orders']); ?></h3>
                                <p>Total Orders</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card stat-revenue">
                            <div class="stat-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="stat-content">
                                <h3>$<?php echo number_format($stats['revenue'], 2); ?></h3>
                                <p>Total Revenue</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="card admin-card">
                    <div class="card-header">
                        <h5><i class="fas fa-list"></i> Recent Orders</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recentOrders->num_rows > 0): ?>
                                        <?php while($order = $recentOrders->fetch_assoc()): ?>
                                            <tr>
                                                <td>#<?php echo $order['id']; ?></td>
                                                <td><?php echo htmlspecialchars($order['firstName'] . ' ' . $order['lastName']); ?></td>
                                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                                <td><span class="badge bg-info"><?php echo strtoupper($order['payment_method']); ?></span></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $order['order_status'] === 'delivered' ? 'success' : 
                                                            ($order['order_status'] === 'cancelled' ? 'danger' : 'warning'); 
                                                    ?>">
                                                        <?php echo ucfirst($order['order_status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($order['createdAt'])); ?></td>
                                                <td>
                                                    <a href="orders.php?view=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No orders yet</td>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>