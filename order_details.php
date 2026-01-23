<?php
// order_details.php
session_start();
require_once 'config.php';
require_once 'session.php';

requireLogin();

$orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$userId = $_SESSION['user_id'];

// Get order details
$stmt = $conn->prepare("
    SELECT o.* 
    FROM orders o 
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->bind_param("ii", $orderId, $userId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: my_orders.php');
    exit();
}

// Get order items
$orderItems = $conn->query("
    SELECT oi.*, p.image 
    FROM order_items oi 
    LEFT JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = $orderId
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - ClothingShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .order-details-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .page-title {
            color: #DFD0B8;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .details-card {
            background: #393E46;
            border: 1px solid #4a5159;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 20px;
        }
        
        .details-card h3 {
            color: #948979;
            margin-bottom: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .status-timeline {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            position: relative;
        }
        
        .status-timeline::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 3px;
            background: #4a5159;
            z-index: 0;
        }
        
        .timeline-item {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 1;
        }
        
        .timeline-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #4a5159;
            color: #948979;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            transition: all 0.3s;
        }
        
        .timeline-item.active .timeline-icon {
            background: #948979;
            color: #222831;
            box-shadow: 0 0 20px rgba(148, 137, 121, 0.5);
        }
        
        .timeline-item.completed .timeline-icon {
            background: #2ecc71;
            color: white;
        }
        
        .timeline-label {
            color: #948979;
            font-size: 12px;
            font-weight: 600;
        }
        
        .timeline-item.active .timeline-label,
        .timeline-item.completed .timeline-label {
            color: #DFD0B8;
        }
        
        .info-section {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .info-box {
            padding: 15px;
            background: rgba(148, 137, 121, 0.05);
            border-radius: 8px;
        }
        
        .info-label {
            color: #948979;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .info-value {
            color: #DFD0B8;
            font-size: 16px;
        }
        
        .order-item-row {
            display: flex;
            gap: 15px;
            padding: 15px;
            background: rgba(148, 137, 121, 0.05);
            border-radius: 8px;
            margin-bottom: 15px;
            align-items: center;
        }
        
        .order-item-row img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .order-item-info {
            flex: 1;
        }
        
        .order-item-name {
            color: #DFD0B8;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .order-item-meta {
            color: #948979;
            font-size: 14px;
        }
        
        .order-item-price {
            color: #2ecc71;
            font-weight: 700;
            font-size: 18px;
        }
        
        .order-summary {
            background: rgba(148, 137, 121, 0.1);
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #948979;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #4a5159;
        }
        
        .summary-row:last-child {
            border-bottom: none;
            font-size: 24px;
            font-weight: 700;
            color: #948979;
            margin-top: 10px;
            padding-top: 15px;
            border-top: 2px solid #948979;
        }
        
        @media (max-width: 768px) {
            .info-section {
                grid-template-columns: 1fr;
            }
            
            .status-timeline {
                flex-direction: column;
                gap: 20px;
            }
            
            .status-timeline::before {
                display: none;
            }
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

    <div class="order-details-container">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-file-invoice"></i> Order #<?php echo $order['id']; ?>
            </h1>
            <a href="my_orders.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
        </div>

        <!-- Order Status Timeline -->
        <div class="details-card">
            <h3><i class="fas fa-chart-line"></i> Order Status</h3>
            
            <div class="status-timeline">
                <div class="timeline-item <?php echo in_array($order['order_status'], ['pending', 'processing', 'shipped', 'delivered']) ? 'completed' : ''; ?>">
                    <div class="timeline-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="timeline-label">Order Placed</div>
                </div>
                
                <div class="timeline-item <?php echo in_array($order['order_status'], ['processing', 'shipped', 'delivered']) ? 'completed' : ($order['order_status'] === 'pending' ? 'active' : ''); ?>">
                    <div class="timeline-icon">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div class="timeline-label">Processing</div>
                </div>
                
                <div class="timeline-item <?php echo in_array($order['order_status'], ['shipped', 'delivered']) ? 'completed' : ($order['order_status'] === 'processing' ? 'active' : ''); ?>">
                    <div class="timeline-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <div class="timeline-label">Shipped</div>
                </div>
                
                <div class="timeline-item <?php echo $order['order_status'] === 'delivered' ? 'completed' : ($order['order_status'] === 'shipped' ? 'active' : ''); ?>">
                    <div class="timeline-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="timeline-label">Delivered</div>
                </div>
            </div>
        </div>

        <!-- Order Information -->
        <div class="details-card">
            <h3><i class="fas fa-info-circle"></i> Order Information</h3>
            
            <div class="info-section">
                <div class="info-box">
                    <div class="info-label">Order Date</div>
                    <div class="info-value"><?php echo date('F d, Y - h:i A', strtotime($order['createdAt'])); ?></div>
                </div>
                
                <div class="info-box">
                    <div class="info-label">Order Status</div>
                    <div class="info-value">
                        <span class="badge bg-<?php 
                            echo $order['order_status'] === 'delivered' ? 'success' : 
                                ($order['order_status'] === 'cancelled' ? 'danger' : 'warning'); 
                        ?>">
                            <?php echo ucfirst($order['order_status']); ?>
                        </span>
                    </div>
                </div>
                
                <div class="info-box">
                    <div class="info-label">Payment Method</div>
                    <div class="info-value">
                        <span class="badge bg-info"><?php echo strtoupper($order['payment_method']); ?></span>
                    </div>
                </div>
                
                <div class="info-box">
                    <div class="info-label">Payment Status</div>
                    <div class="info-value">
                        <span class="badge bg-<?php echo $order['payment_status'] === 'paid' ? 'success' : 'warning'; ?>">
                            <?php echo ucfirst($order['payment_status']); ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="info-box">
                <div class="info-label">Shipping Address</div>
                <div class="info-value"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></div>
            </div>
            
            <div class="info-box" style="margin-top: 15px;">
                <div class="info-label">Contact Number</div>
                <div class="info-value"><?php echo htmlspecialchars($order['contact_number']); ?></div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="details-card">
            <h3><i class="fas fa-shopping-bag"></i> Order Items</h3>
            
            <?php while($item = $orderItems->fetch_assoc()): ?>
                <div class="order-item-row">
                    <?php if ($item['image']): ?>
                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="Product">
                    <?php else: ?>
                        <div style="width: 80px; height: 80px; background: #2c3137; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #948979;">
                            <i class="fas fa-image fa-2x"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="order-item-info">
                        <div class="order-item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                        <div class="order-item-meta">
                            Size: <?php echo htmlspecialchars($item['size'] ?? 'N/A'); ?> | 
                            Quantity: <?php echo $item['quantity']; ?>
                        </div>
                        <div class="order-item-meta">
                            $<?php echo number_format($item['price'], 2); ?> × <?php echo $item['quantity']; ?>
                        </div>
                    </div>
                    
                    <div class="order-item-price">
                        $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                    </div>
                </div>
            <?php endwhile; ?>
            
            <div class="order-summary">
                <div class="summary-row">
                    <span style="color: #948979; font-weight: 700; font-size: 18px;">TOTAL AMOUNT:</span>
                    <span style="color: #2ecc71; font-size: 28px;">$<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>
        </div>
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