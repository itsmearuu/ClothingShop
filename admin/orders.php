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

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = intval($_POST['order_id']);
    $orderStatus = $conn->real_escape_string($_POST['order_status']);
    $paymentStatus = $conn->real_escape_string($_POST['payment_status']);
    
    $sql = "UPDATE orders SET order_status = '$orderStatus', payment_status = '$paymentStatus' WHERE id = $orderId";
    
    if ($conn->query($sql)) {
        header('Location: orders.php?success=updated');
        exit();
    }
}

// Get order details if viewing
$viewOrder = null;
if (isset($_GET['view'])) {
    $orderId = intval($_GET['view']);
    $result = $conn->query("
        SELECT o.*, u.firstName, u.lastName, u.email 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id = $orderId
    ");
    $viewOrder = $result->fetch_assoc();
    
    // Get order items
    if ($viewOrder) {
        $orderItems = $conn->query("
            SELECT oi.*, p.image 
            FROM order_items oi 
            LEFT JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = $orderId
        ");
    }
}

// Get all orders
$orders = $conn->query("
    SELECT o.*, u.firstName, u.lastName 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.createdAt DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - Admin</title>
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
                    <h1><i class="fas fa-shopping-cart"></i> Orders Management</h1>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> Order updated successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($viewOrder): ?>
                    <!-- Order Details View -->
                    <div class="card admin-card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5><i class="fas fa-file-invoice"></i> Order #<?php echo $viewOrder['id']; ?> Details</h5>
                            <a href="orders.php" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back to Orders
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="highlight-label">Customer Information</h6>
                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($viewOrder['firstName'] . ' ' . $viewOrder['lastName']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($viewOrder['email']); ?></p>
                                    <p><strong>Contact:</strong> <?php echo htmlspecialchars($viewOrder['contact_number']); ?></p>
                                    <p><strong>Shipping Address:</strong><br><?php echo nl2br(htmlspecialchars($viewOrder['shipping_address'])); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="highlight-label">Order Information</h6>
                                    <p><strong>Order Date:</strong> <?php echo date('M d, Y H:i', strtotime($viewOrder['createdAt'])); ?></p>
                                    <p><strong>Payment Method:</strong> 
                                        <span class="badge bg-info"><?php echo strtoupper($viewOrder['payment_method']); ?></span>
                                    </p>
                                    <p><strong>Payment Status:</strong> 
                                        <span class="badge bg-<?php 
                                            echo $viewOrder['payment_status'] === 'paid' ? 'success' : 
                                                ($viewOrder['payment_status'] === 'failed' ? 'danger' : 'warning'); 
                                        ?>">
                                            <?php echo ucfirst($viewOrder['payment_status']); ?>
                                        </span>
                                    </p>
                                    <p><strong>Order Status:</strong> 
                                        <span class="badge bg-<?php 
                                            echo $viewOrder['order_status'] === 'delivered' ? 'success' : 
                                                ($viewOrder['order_status'] === 'cancelled' ? 'danger' : 'warning'); 
                                        ?>">
                                            <?php echo ucfirst($viewOrder['order_status']); ?>
                                        </span>
                                    </p>
                                    <?php if ($viewOrder['paypal_transaction_id']): ?>
                                        <p><strong>PayPal Transaction ID:</strong> <?php echo htmlspecialchars($viewOrder['paypal_transaction_id']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <hr>

                            <h6 class="highlight-label">Order Items</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Product</th>
                                            <th>Size</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($item = $orderItems->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <?php if ($item['image']): ?>
                                                        <img src="../<?php echo htmlspecialchars($item['image']); ?>" 
                                                             alt="Product" class="product-thumbnail">
                                                    <?php else: ?>
                                                        <div class="no-image-small">No Image</div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                                <td><?php echo htmlspecialchars($item['size'] ?? 'N/A'); ?></td>
                                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td class="highlight-price">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                        <tr class="table-active">
                                            <td colspan="5" class="text-end"><strong>Total:</strong></td>
                                            <td class="highlight-price"><strong>$<?php echo number_format($viewOrder['total_amount'], 2); ?></strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <hr>

                            <h6 class="highlight-label">Update Order Status</h6>
                            <form method="POST" class="row g-3">
                                <input type="hidden" name="order_id" value="<?php echo $viewOrder['id']; ?>">
                                <div class="col-md-4">
                                    <label class="form-label">Order Status</label>
                                    <select name="order_status" class="form-control" required>
                                        <option value="pending" <?php echo $viewOrder['order_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="processing" <?php echo $viewOrder['order_status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="shipped" <?php echo $viewOrder['order_status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                        <option value="delivered" <?php echo $viewOrder['order_status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="cancelled" <?php echo $viewOrder['order_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Payment Status</label>
                                    <select name="payment_status" class="form-control" required>
                                        <option value="pending" <?php echo $viewOrder['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="paid" <?php echo $viewOrder['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                        <option value="failed" <?php echo $viewOrder['payment_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" name="update_status" class="btn btn-primary w-100">
                                        <i class="fas fa-save"></i> Update Status
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Orders List -->
                    <div class="card admin-card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Customer</th>
                                            <th>Total Amount</th>
                                            <th>Payment Method</th>
                                            <th>Payment Status</th>
                                            <th>Order Status</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($orders->num_rows > 0): ?>
                                            <?php while($order = $orders->fetch_assoc()): ?>
                                                <tr>
                                                    <td class="highlight-text">#<?php echo $order['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($order['firstName'] . ' ' . $order['lastName']); ?></td>
                                                    <td class="highlight-price">$<?php echo number_format($order['total_amount'], 2); ?></td>
                                                    <td><span class="badge bg-info"><?php echo strtoupper($order['payment_method']); ?></span></td>
                                                    <td>
                                                        <span class="badge bg-<?php 
                                                            echo $order['payment_status'] === 'paid' ? 'success' : 
                                                                ($order['payment_status'] === 'failed' ? 'danger' : 'warning'); 
                                                        ?>">
                                                            <?php echo ucfirst($order['payment_status']); ?>
                                                        </span>
                                                    </td>
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
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center">No orders found</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>