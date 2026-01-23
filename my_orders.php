<?php
// my_orders.php
session_start();
require_once 'config.php';
require_once 'session.php';

requireLogin();

$userId = $_SESSION['user_id'];

// Get user's orders
$orders = $conn->query("
    SELECT o.*, 
           COUNT(oi.id) as item_count 
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    WHERE o.user_id = $userId 
    GROUP BY o.id 
    ORDER BY o.createdAt DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - ClothingShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .orders-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .page-title {
            color: #DFD0B8;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .order-card {
            background: #393E46;
            border: 1px solid #4a5159;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .order-card:hover {
            border-color: #948979;
            box-shadow: 0 4px 15px rgba(148, 137, 121, 0.2);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #4a5159;
        }
        
        .order-id {
            color: #948979;
            font-size: 20px;
            font-weight: 700;
        }
        
        .order-date {
            color: #948979;
            font-size: 14px;
        }
        
        .order-body {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .order-info-item {
            padding: 12px;
            background: rgba(148, 137, 121, 0.05);
            border-radius: 6px;
        }
        
        .order-info-label {
            color: #948979;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .order-info-value {
            color: #DFD0B8;
            font-size: 16px;
            font-weight: 600;
        }
        
        .order-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #4a5159;
        }
        
        .order-total {
            color: #2ecc71;
            font-size: 24px;
            font-weight: 700;
        }
        
        .empty-orders {
            text-align: center;
            padding: 60px 20px;
            background: #393E46;
            border-radius: 10px;
            border: 1px solid #4a5159;
        }
        
        .empty-orders i {
            font-size: 80px;
            color: #948979;
            opacity: 0.3;
            margin-bottom: 20px;
        }
        
        .empty-orders h3 {
            color: #DFD0B8;
            margin-bottom: 15px;
        }
        
        .empty-orders p {
            color: #948979;
            margin-bottom: 25px;
        }
    </style>
</head>
<body>
    <header>
        <div class="mylogo" onclick="window.location.href='index.php'">CLOTHING<span style="color: #ff9d00">SHOP</span></div>
        <nav>
            <a href="index.php">HOME</a>
            <a href="about.php">ABOUT</a>
            <a href="products.php">PRODUCTS</a>
            <a href="contact.php">CONTACT</a>
        </nav>

        <div class="logsign">
            <a href="profile.php"><i class="fa-regular fa-user"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?></a>
            <?php if(isAdmin()): ?>
                <a href="admin/index.php"><i class="fas fa-tachometer-alt"></i> Admin</a>
            <?php endif; ?>
            <a href="logout.php">Logout</a>
            <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i><span id="cart-count" class="cart-badge">0</span></a>
        </div>
    </header>

    <div class="orders-container">
        <h1 class="page-title">
            <i class="fas fa-shopping-bag"></i> My Orders
        </h1>

        <?php if ($orders->num_rows > 0): ?>
            <?php while($order = $orders->fetch_assoc()): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div class="order-id">Order #<?php echo $order['id']; ?></div>
                            <div class="order-date">
                                <i class="far fa-calendar"></i> 
                                <?php echo date('F d, Y - h:i A', strtotime($order['createdAt'])); ?>
                            </div>
                        </div>
                        <div>
                            <span class="badge bg-<?php 
                                echo $order['order_status'] === 'delivered' ? 'success' : 
                                    ($order['order_status'] === 'cancelled' ? 'danger' : 'warning'); 
                            ?>" style="font-size: 14px; padding: 8px 15px;">
                                <?php echo ucfirst($order['order_status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="order-body">
                        <div class="order-info-item">
                            <div class="order-info-label">Payment Method</div>
                            <div class="order-info-value">
                                <span class="badge bg-info"><?php echo strtoupper($order['payment_method']); ?></span>
                            </div>
                        </div>
                        
                        <div class="order-info-item">
                            <div class="order-info-label">Payment Status</div>
                            <div class="order-info-value">
                                <span class="badge bg-<?php echo $order['payment_status'] === 'paid' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="order-info-item">
                            <div class="order-info-label">Total Items</div>
                            <div class="order-info-value"><?php echo $order['item_count']; ?> item(s)</div>
                        </div>
                        
                        <div class="order-info-item">
                            <div class="order-info-label">Order Total</div>
                            <div class="order-info-value" style="color: #2ecc71;">
                                $<?php echo number_format($order['total_amount'], 2); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="order-footer">
                        <div class="order-total">
                            Total: $<?php echo number_format($order['total_amount'], 2); ?>
                        </div>
                        <div>
                            <a href="order_details.php?order_id=<?php echo $order['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-orders">
                <i class="fas fa-shopping-bag"></i>
                <h3>No Orders Yet</h3>
                <p>You haven't placed any orders yet. Start shopping to see your orders here!</p>
                <a href="products.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-cart"></i> Start Shopping
                </a>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <div class="container">
            <div class="row">
                <div class="col">
                    <p class="Clo">CLOTHINGSHOP</p>
                    <p>Empowering customers with choice, confidence, and convenience—ClothingShop is your trusted destination for modern online shopping.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>