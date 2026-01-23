<?php
// order_success.php
session_start();
require_once 'config.php';
require_once 'session.php';

requireLogin();

// Get order ID
$orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if (!$orderId) {
    header('Location: index.php');
    exit();
}

// Get order details
$stmt = $conn->prepare("
    SELECT o.*, u.firstName, u.lastName, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->bind_param("ii", $orderId, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: index.php');
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
    <title>Order Confirmation - ClothingShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .success-container {
            max-width: 900px;
            margin: 60px auto;
            padding: 0 20px;
        }
        
        .success-header {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            padding: 40px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .success-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: scaleIn 0.5s ease;
        }
        
        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }
        
        .success-header h1 {
            margin: 0 0 10px 0;
            font-weight: 700;
        }
        
        .success-header p {
            margin: 0;
            font-size: 18px;
            opacity: 0.9;
        }
        
        .order-card {
            background: #393E46;
            border: 1px solid #4a5159;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 20px;
        }
        
        .order-card h3 {
            color: #948979;
            margin-bottom: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .info-item {
            padding: 15px;
            background: rgba(148, 137, 121, 0.05);
            border-radius: 8px;
        }
        
        .info-label {
            color: #948979;
            font-size: 14px;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .info-value {
            color: #DFD0B8;
            font-size: 16px;
        }
        
        .order-item {
            display: flex;
            gap: 15px;
            padding: 15px;
            background: rgba(148, 137, 121, 0.05);
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .order-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .order-item-details {
            flex: 1;
        }
        
        .order-item-name {
            color: #DFD0B8;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .order-item-info {
            color: #948979;
            font-size: 14px;
        }
        
        .order-item-price {
            color: #2ecc71;
            font-weight: 700;
            font-size: 18px;
        }
        
        .order-total {
            background: rgba(148, 137, 121, 0.1);
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #948979;
            margin-top: 20px;
        }
        
        .order-total-row {
            display: flex;
            justify-content: space-between;
            font-size: 24px;
            font-weight: 700;
        }
        
        .order-total-label {
            color: #948979;
        }
        
        .order-total-value {
            color: #2ecc71;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .order-item {
                flex-direction: column;
            }
            
            .action-buttons {
                flex-direction: column;
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

    <div class="success-container">
        <div class="success-header">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Order Placed Successfully!</h1>
            <p>Thank you for your order. Your order has been received and is being processed.</p>
        </div>

        <!-- Order Information -->
        <div class="order-card">
            <h3><i class="fas fa-info-circle"></i> Order Information</h3>
            
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Order Number</div>
                    <div class="info-value">#<?php echo $order['id']; ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Order Date</div>
                    <div class="info-value"><?php echo date('F d, Y - h:i A', strtotime($order['createdAt'])); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Payment Method</div>
                    <div class="info-value">
                        <span class="badge bg-info"><?php echo strtoupper($order['payment_method']); ?></span>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Payment Status</div>
                    <div class="info-value">
                        <span class="badge bg-<?php echo $order['payment_status'] === 'paid' ? 'success' : 'warning'; ?>">
                            <?php echo ucfirst($order['payment_status']); ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Shipping Address</div>
                <div class="info-value"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></div>
            </div>
            
            <div class="info-item" style="margin-top: 15px;">
                <div class="info-label">Contact Number</div>
                <div class="info-value"><?php echo htmlspecialchars($order['contact_number']); ?></div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="order-card">
            <h3><i class="fas fa-shopping-bag"></i> Order Items</h3>
            
            <?php while($item = $orderItems->fetch_assoc()): ?>
                <div class="order-item">
                    <?php if ($item['image']): ?>
                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="Product">
                    <?php else: ?>
                        <div style="width: 80px; height: 80px; background: #2c3137; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #948979;">
                            <i class="fas fa-image fa-2x"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="order-item-details">
                        <div class="order-item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                        <div class="order-item-info">
                            Size: <?php echo htmlspecialchars($item['size'] ?? 'N/A'); ?> | 
                            Quantity: <?php echo $item['quantity']; ?>
                        </div>
                        <div class="order-item-info">
                            $<?php echo number_format($item['price'], 2); ?> × <?php echo $item['quantity']; ?>
                        </div>
                    </div>
                    
                    <div class="order-item-price">
                        $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                    </div>
                </div>
            <?php endwhile; ?>
            
            <div class="order-total">
                <div class="order-total-row">
                    <span class="order-total-label">Total Amount:</span>
                    <span class="order-total-value">$<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>
        </div>

        <!-- Next Steps -->
        <div class="order-card">
            <h3><i class="fas fa-clipboard-list"></i> What's Next?</h3>
            <ul style="color: #DFD0B8; line-height: 2;">
                <li>You will receive an email confirmation shortly at <strong style="color: #948979;"><?php echo htmlspecialchars($order['email']); ?></strong></li>
                <li>We will process your order and prepare it for shipment</li>
                <?php if ($order['payment_method'] === 'cod'): ?>
                    <li>Please have the exact amount ready when your order arrives</li>
                <?php endif; ?>
                <li>You will be notified when your order is shipped</li>
                <li>Estimated delivery: 3-5 business days</li>
            </ul>
        </div>

        <div class="action-buttons">
            <a href="index.php" class="btn btn-primary btn-lg">
                <i class="fas fa-home"></i> Back to Home
            </a>
            <a href="products.php" class="btn btn-success btn-lg">
                <i class="fas fa-shopping-cart"></i> Continue Shopping
            </a>
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
    <script>
        // Clear cart on successful order
        localStorage.removeItem('cart');
        
        // Update cart count
        document.addEventListener('DOMContentLoaded', function() {
            const cartCountEl = document.getElementById('cart-count');
            if (cartCountEl) {
                cartCountEl.textContent = '0';
            }
        });
    </script>
</body>
</html>