<?php
// paypal_payment.php
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
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $orderId, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: index.php');
    exit();
}

// Handle PayPal return
if (isset($_GET['payment']) && $_GET['payment'] === 'success') {
    $transactionId = $_GET['transaction_id'] ?? 'PAYPAL_' . time();
    
    // Update order status
    $stmt = $conn->prepare("UPDATE orders SET payment_status = 'paid', paypal_transaction_id = ?, order_status = 'processing' WHERE id = ?");
    $stmt->bind_param("si", $transactionId, $orderId);
    $stmt->execute();
    
    // Clear cart
    echo "<script>localStorage.removeItem('cart');</script>";
    
    header('Location: order_success.php?order_id=' . $orderId);
    exit();
}

if (isset($_GET['payment']) && $_GET['payment'] === 'cancel') {
    // Update order status to failed
    $stmt = $conn->prepare("UPDATE orders SET payment_status = 'failed', order_status = 'cancelled' WHERE id = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    
    $error = "Payment was cancelled. Your order has been cancelled.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayPal Payment - ClothingShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .payment-container {
            max-width: 600px;
            margin: 60px auto;
            padding: 0 20px;
        }
        
        .payment-card {
            background: #393E46;
            border: 1px solid #4a5159;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
        }
        
        .payment-icon {
            font-size: 80px;
            color: #0070ba;
            margin-bottom: 20px;
        }
        
        .payment-card h2 {
            color: #DFD0B8;
            margin-bottom: 15px;
        }
        
        .payment-card p {
            color: #948979;
            margin-bottom: 25px;
        }
        
        .order-details {
            background: rgba(148, 137, 121, 0.1);
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
            text-align: left;
        }
        
        .order-details-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #4a5159;
        }
        
        .order-details-row:last-child {
            border-bottom: none;
            font-weight: 700;
            font-size: 20px;
            color: #948979;
            margin-top: 10px;
            padding-top: 15px;
            border-top: 2px solid #948979;
        }
        
        .paypal-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 25px;
        }
        
        .btn-paypal {
            background: #0070ba;
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-paypal:hover {
            background: #005a9c;
            transform: translateY(-2px);
        }
        
        .important-note {
            background: rgba(231, 76, 60, 0.1);
            border-left: 4px solid #e74c3c;
            padding: 15px 20px;
            border-radius: 6px;
            margin-top: 25px;
            text-align: left;
        }
        
        .important-note strong {
            color: #e74c3c;
            font-weight: 700;
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
            <a href="logout.php">Logout</a>
            <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i><span id="cart-count" class="cart-badge">0</span></a>
        </div>
    </header>

    <div class="payment-container">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
            <div class="text-center">
                <a href="index.php" class="btn btn-primary">Return to Home</a>
            </div>
        <?php else: ?>
            <div class="payment-card">
                <div class="payment-icon">
                    <i class="fab fa-paypal"></i>
                </div>
                
                <h2>Complete Your Payment</h2>
                <p>You will be redirected to PayPal to complete your payment securely.</p>
                
                <div class="order-details">
                    <div class="order-details-row">
                        <span style="color: #948979;">Order ID:</span>
                        <span style="color: #DFD0B8; font-weight: 600;">#<?php echo $order['id']; ?></span>
                    </div>
                    <div class="order-details-row">
                        <span style="color: #948979;">Payment Method:</span>
                        <span style="color: #DFD0B8;">PayPal</span>
                    </div>
                    <div class="order-details-row">
                        <span style="color: #948979;">Total Amount:</span>
                        <span style="color: #2ecc71; font-weight: 700; font-size: 24px;">$<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                </div>
                
                <div class="important-note">
                    <strong><i class="fas fa-info-circle"></i> Note:</strong> This is a demo payment page. In production, you would integrate with PayPal's actual API for real payments.
                </div>
                
                <div class="paypal-buttons">
                    <button class="btn-paypal" onclick="simulatePayPalSuccess()">
                        <i class="fab fa-paypal"></i> Pay with PayPal
                    </button>
                    <a href="paypal_payment.php?order_id=<?php echo $orderId; ?>&payment=cancel" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
                
                <p style="margin-top: 20px; font-size: 12px; color: #948979;">
                    <i class="fas fa-lock"></i> Your payment information is secure and encrypted
                </p>
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
    <script>
        function simulatePayPalSuccess() {
            // Simulate PayPal payment processing
            const transactionId = 'PAYPAL_DEMO_' + Date.now();
            
            // Show loading
            const btn = event.target;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            btn.disabled = true;
            
            // Simulate processing delay
            setTimeout(() => {
                window.location.href = 'paypal_payment.php?order_id=<?php echo $orderId; ?>&payment=success&transaction_id=' + transactionId;
            }, 2000);
        }
    </script>
</body>
</html>