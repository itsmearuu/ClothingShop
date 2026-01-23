<?php
// checkout.php
session_start();
require_once 'config.php';
require_once 'session.php';

requireLogin();

// Get user data
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shippingAddress = $conn->real_escape_string($_POST['shipping_address']);
    $contactNumber = $conn->real_escape_string($_POST['contact_number']);
    $paymentMethod = $_POST['payment_method'];
    $cartData = json_decode($_POST['cart_data'], true);
    
    if (empty($cartData)) {
        $error = "Your cart is empty!";
    } else {
        // Calculate total
        $totalAmount = 0;
        foreach ($cartData as $item) {
            $totalAmount += ($item['price'] ?? 0) * ($item['qty'] ?? $item['quantity'] ?? 1);
        }
        
        // Insert order
        $paymentStatus = ($paymentMethod === 'cod') ? 'pending' : 'pending';
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, payment_method, payment_status, order_status, shipping_address, contact_number) VALUES (?, ?, ?, ?, 'pending', ?, ?)");
        $stmt->bind_param("idssss", $userId, $totalAmount, $paymentMethod, $paymentStatus, $shippingAddress, $contactNumber);
        
        if ($stmt->execute()) {
            $orderId = $conn->insert_id;
            
            // Insert order items
            foreach ($cartData as $item) {
                $productId = intval($item['id'] ?? 0);
                $productName = $conn->real_escape_string($item['name'] ?? $item['title'] ?? 'Unknown Product');
                $price = floatval($item['price'] ?? 0);
                $quantity = intval($item['qty'] ?? $item['quantity'] ?? 1);
                $size = $conn->real_escape_string($item['size'] ?? '');
                
                // Get product ID from products table if exists, otherwise use 0
                $productCheckStmt = $conn->prepare("SELECT id FROM products WHERE name = ? LIMIT 1");
                $productCheckStmt->bind_param("s", $productName);
                $productCheckStmt->execute();
                $productCheckResult = $productCheckStmt->get_result();
                
                if ($productCheckResult->num_rows > 0) {
                    $productRow = $productCheckResult->fetch_assoc();
                    $productId = $productRow['id'];
                } else {
                    // Create a temporary product entry
                    $tempStmt = $conn->prepare("INSERT INTO products (name, description, price, stock, category, status) VALUES (?, 'Imported from JSON', ?, 0, 'General', 'active')");
                    $tempDesc = "Imported product";
                    $tempStmt->bind_param("sd", $productName, $price);
                    $tempStmt->execute();
                    $productId = $conn->insert_id;
                }
                
                $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price, size) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iisids", $orderId, $productId, $productName, $quantity, $price, $size);
                $stmt->execute();
            }
            
            // Redirect based on payment method
            if ($paymentMethod === 'paypal') {
                // Redirect to PayPal payment page
                header("Location: paypal_payment.php?order_id=$orderId");
                exit();
            } else {
                // COD - Order placed successfully
                header("Location: order_success.php?order_id=$orderId");
                exit();
            }
        } else {
            $error = "Error placing order. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - ClothingShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .checkout-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .checkout-card {
            background: #393E46;
            border: 1px solid #4a5159;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 20px;
        }
        
        .checkout-card h3 {
            color: #948979;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .payment-method {
            border: 2px solid #4a5159;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .payment-method:hover {
            border-color: #948979;
            background: rgba(148, 137, 121, 0.05);
        }
        
        .payment-method.active {
            border-color: #948979;
            background: rgba(148, 137, 121, 0.1);
        }
        
        .payment-method input[type="radio"] {
            width: 20px;
            height: 20px;
            accent-color: #948979;
        }
        
        .payment-icon {
            font-size: 32px;
            color: #948979;
        }
        
        .payment-info h5 {
            color: #DFD0B8;
            margin: 0 0 5px 0;
            font-weight: 600;
        }
        
        .payment-info p {
            color: #948979;
            margin: 0;
            font-size: 14px;
        }
        
        .order-summary-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #4a5159;
        }
        
        .order-summary-item:last-child {
            border-bottom: none;
        }
        
        .order-total {
            font-size: 24px;
            font-weight: 700;
            color: #948979;
        }
        
        .important-note {
            background: rgba(148, 137, 121, 0.1);
            border-left: 4px solid #948979;
            padding: 15px 20px;
            border-radius: 6px;
            margin-top: 20px;
        }
        
        .important-note strong {
            color: #948979;
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

    <div class="checkout-container">
        <h2 style="color: #DFD0B8; margin-bottom: 30px;">
            <i class="fas fa-shopping-bag"></i> Checkout
        </h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="checkoutForm">
            <div class="row">
                <div class="col-md-7">
                    <!-- Shipping Information -->
                    <div class="checkout-card">
                        <h3><i class="fas fa-shipping-fast"></i> Shipping Information</h3>
                        
                        <div class="mb-3">
                            <label class="form-label" style="color: #948979; font-weight: 600;">Full Name</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?>" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label" style="color: #948979; font-weight: 600;">Contact Number *</label>
                            <input type="tel" class="form-control" name="contact_number" placeholder="+63 XXX XXX XXXX" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label" style="color: #948979; font-weight: 600;">Shipping Address *</label>
                            <textarea class="form-control" name="shipping_address" rows="4" placeholder="Enter your complete shipping address" required></textarea>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="checkout-card">
                        <h3><i class="fas fa-credit-card"></i> Payment Method</h3>
                    <div class="payment-method" onclick="selectPayment('cod')">
                        <input type="radio" name="payment_method" value="cod" id="cod" required>
                        <div class="payment-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="payment-info">
                            <h5>Cash on Delivery (COD)</h5>
                            <p>Pay when you receive your order</p>
                        </div>
                    </div>
                    
                    <div class="payment-method" onclick="selectPayment('paypal')">
                        <input type="radio" name="payment_method" value="paypal" id="paypal" required>
                        <div class="payment-icon">
                            <i class="fab fa-paypal"></i>
                        </div>
                        <div class="payment-info">
                            <h5>PayPal</h5>
                            <p>Pay securely with your PayPal account</p>
                        </div>
                    </div>
                    
                    <div class="important-note">
                        <strong><i class="fas fa-exclamation-triangle"></i> Important:</strong> Please review your order carefully before placing it. Orders cannot be cancelled once confirmed.
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <!-- Order Summary -->
                <div class="checkout-card">
                    <h3><i class="fas fa-list-alt"></i> Order Summary</h3>
                    
                    <div id="orderSummary">
                        <p style="color: #948979; text-align: center;">Loading cart items...</p>
                    </div>
                    
                    <input type="hidden" name="cart_data" id="cartData">
                    
                    <button type="submit" class="btn btn-primary w-100 mt-3" style="padding: 15px; font-size: 18px; font-weight: 700;">
                        <i class="fas fa-check-circle"></i> Place Order
                    </button>
                </div>
            </div>
        </div>
    </form>
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
    function selectPayment(method) {
        document.querySelectorAll('.payment-method').forEach(el => el.classList.remove('active'));
        document.getElementById(method).checked = true;
        document.getElementById(method).closest('.payment-method').classList.add('active');
    }
    
    function loadOrderSummary() {
        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const summaryEl = document.getElementById('orderSummary');
        
        if (cart.length === 0) {
            summaryEl.innerHTML = '<p style="color: #948979; text-align: center;">Your cart is empty</p>';
            return;
        }
        
        let html = '';
        let total = 0;
        
        cart.forEach(item => {
            const price = parseFloat(item.price || 0);
            const quantity = parseInt(item.qty || item.quantity || 1);
            const subtotal = price * quantity;
            total += subtotal;
            
            html += `
                <div class="order-summary-item">
                    <div>
                        <div style="color: #DFD0B8; font-weight: 600;">${item.name || item.title}</div>
                        <div style="color: #948979; font-size: 14px;">Size: ${item.size || 'N/A'} | Qty: ${quantity}</div>
                    </div>
                    <div style="color: #DFD0B8; font-weight: 600;">$${subtotal.toFixed(2)}</div>
                </div>
            `;
        });
        
        html += `
            <div class="order-summary-item" style="margin-top: 15px; padding-top: 15px; border-top: 2px solid #948979;">
                <div style="color: #948979; font-weight: 700; font-size: 18px;">TOTAL:</div>
                <div class="order-total">$${total.toFixed(2)}</div>
            </div>
        `;
        
        summaryEl.innerHTML = html;
        document.getElementById('cartData').value = JSON.stringify(cart);
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        loadOrderSummary();
        updateCartCount();
    });
</script>
</body>
</html>