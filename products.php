<?php
session_start();
require_once 'session.php';

// Load products from JSON
$jsonFile = 'product.json';
$products = [];

if (file_exists($jsonFile)) {
    $jsonContent = file_get_contents($jsonFile);
    $data = json_decode($jsonContent, true);
    if (isset($data['products']['clothes'])) {
        $products = $data['products']['clothes'];
    }
}

// Handle filtering/search
$filteredProducts = $products;
$searchTerm = $_GET['search'] ?? '';
$sortBy = $_GET['sort'] ?? 'name';

if (!empty($searchTerm)) {
    $searchTerm = strtolower(trim($searchTerm));
    $filteredProducts = array_filter($products, function($product) use ($searchTerm) {
        return stripos($product['prodName'], $searchTerm) !== false || 
               stripos($product['description'], $searchTerm) !== false;
    });
}

// Sort products
if ($sortBy === 'price-low') {
    usort($filteredProducts, fn($a, $b) => $a['price'] - $b['price']);
} elseif ($sortBy === 'price-high') {
    usort($filteredProducts, fn($a, $b) => $b['price'] - $a['price']);
} else {
    usort($filteredProducts, fn($a, $b) => strcmp($a['prodName'], $b['prodName']));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - ClothingShop</title>

    <!-- Fonts & icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Oswald:wght@200..700&family=Poppins:wght@400;500;800&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <div class="mylogo" onclick="window.location.href='index.php'">CLOTHING<span style="color: #ff9d00">SHOP</span></div>
        <nav>
            <a href="index.php" class="<?php echo (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'active' : ''; ?>">HOME</a>
            <a href="about.php" class="<?php echo (basename($_SERVER['PHP_SELF']) === 'about.php') ? 'active' : ''; ?>">ABOUT</a>
            <a href="products.php" class="<?php echo (basename($_SERVER['PHP_SELF']) === 'products.php') ? 'active' : ''; ?>">PRODUCTS</a>
            <a href="contact.php" class="<?php echo (basename($_SERVER['PHP_SELF']) === 'contact.php') ? 'active' : ''; ?>">CONTACT</a>
        </nav>

        <div class="logsign">
    <?php require_once 'session.php'; ?>
    <?php if(isLoggedIn()): ?>
        <a href="profile.php"><i class="fa-regular fa-user"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?></a>
        <?php if(isAdmin()): ?>
            <a href="admin/index.php"><i class="fas fa-tachometer-alt"></i> Admin</a>
        <?php endif; ?>
        <a href="logout.php">Logout</a>
    <?php else: ?>
        <a href="login.php"><i class="fa-jelly-fill fa-regular fa-user"></i></a>
    <?php endif; ?>
    <a href="cart.php" id="cart-link"><i class="fa-solid fa-cart-shopping"></i><span id="cart-count" class="cart-badge">0</span></a>
</div>
    </header>

    <!-- PRODUCTS PAGE HEADER -->
    <div class="products-header">
        <h1>Our Products</h1>
        <p>Discover our amazing collection of clothing and fashion items</p>
    </div>

    <!-- PRODUCTS SECTION -->
    <div class="container products-container">
        <div class="row">
            <!-- SIDEBAR FILTERS -->
            <div class="col-lg-3 col-md-4 mb-4">
                <div class="filters-section">
                    <h5>Filters</h5>
                    
                    <!-- SEARCH -->
                    <div class="filter-group">
                        <label>Search Products</label>
                        <form method="GET" class="search-form">
                            <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($searchTerm) ?>" class="form-control">
                            <button type="submit" class="btn-search"><i class="fa-solid fa-magnifying-glass"></i></button>
                        </form>
                    </div>

                    <!-- SORT -->
                    <div class="filter-group">
                        <label>Sort By</label>
                        <form method="GET" class="sort-form">
                            <select name="sort" class="form-control" onchange="this.form.submit()">
                                <option value="name" <?= $sortBy === 'name' ? 'selected' : '' ?>>Product Name</option>
                                <option value="price-low" <?= $sortBy === 'price-low' ? 'selected' : '' ?>>Price: Low to High</option>
                                <option value="price-high" <?= $sortBy === 'price-high' ? 'selected' : '' ?>>Price: High to Low</option>
                            </select>
                            <?php if (!empty($searchTerm)): ?>
                                <input type="hidden" name="search" value="<?= htmlspecialchars($searchTerm) ?>">
                            <?php endif; ?>
                        </form>
                    </div>

                    <!-- CLEAR FILTERS -->
                    <?php if (!empty($searchTerm) || $sortBy !== 'name'): ?>
                        <a href="products.php" class="btn-clear-filters">Clear Filters</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- PRODUCTS GRID -->
            <div class="col-lg-9 col-md-8">
                <?php if (empty($filteredProducts)): ?>
                    <div class="no-products">
                        <p><i class="fa-solid fa-inbox"></i></p>
                        <p>No products found</p>
                        <a href="products.php" class="btn btn-primary">View All Products</a>
                    </div>
                <?php else: ?>
                    <div class="products-count">
                        <p>Showing <?= count($filteredProducts) ?> product<?= count($filteredProducts) !== 1 ? 's' : '' ?></p>
                    </div>
                    <div class="row">
                        <?php foreach ($filteredProducts as $product): ?>
                            <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                                <div class="card product-card clickable-card" 
                                     data-title="<?= htmlspecialchars($product['prodName']) ?>" 
                                     data-price="<?= $product['price'] ?>" 
                                     data-img="<?= htmlspecialchars($product['photo1']) ?>" 
                                     data-desc="<?= htmlspecialchars($product['description']) ?>"
                                     data-id="<?= htmlspecialchars($product['id']) ?>">
                                    <img src="<?= htmlspecialchars($product['photo1']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['prodName']) ?>">
                                    <div class="card-body">
                                        <p class="card-title"><?= htmlspecialchars($product['prodName']) ?></p>
                                        <p class="card-price">$<?= number_format($product['price'], 2) ?></p>
                                        <p class="card-desc"><?= htmlspecialchars(substr($product['description'], 0, 50)) ?>...</p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- PRODUCT DETAILS MODAL -->
    <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="modal-product">
                        <img id="productModalImg" src="" alt="">
                        <div class="modal-product-info">
                            <p id="productModalDesc"></p>
                            <p class="modal-price">Price: $<span id="productModalPrice"></span></p>
                            <div class="size-selector">
                                <label>Select Size:</label>
                                <div class="sizes">
                                    <button class="size-btn" data-size="XS">XS</button>
                                    <button class="size-btn" data-size="S">S</button>
                                    <button class="size-btn" data-size="M">M</button>
                                    <button class="size-btn" data-size="L">L</button>
                                    <button class="size-btn" data-size="XL">XL</button>
                                    <button class="size-btn" data-size="XXL">XXL</button>
                                </div>
                            </div>
                            <div class="modal-actions">
                                <button class="btn btn-primary" id="addToCartBtn">Add to Cart</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col">
                    <p class="Clo">CLOTHING<span style="color: #ff9d00">SHOP</span></p>
                    <p>Empowering customers with choice, confidence, and convenience—ClothingShop is your trusted destination for modern online shopping.</p>
                </div>
                <div class="col">
                    <p class="Com">COMPANY</p>
                    <div class="footer-links">
                        <a href="index.php">HOME</a>
                        <a href="about.php">ABOUT</a>
                        <a href="products.php">PRODUCTS</a>
                        <a href="contact.php">CONTACT</a>
                    </div>
                </div>
                <div class="col">
                    <p class="Git">GET IN TOUCH</p>
                    <p>+63 902 6488 930 <br> +63 915 6789 012 <br>contact@clothingshop.com<br>support@clothingshop.com</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
    <script>
        let currentProduct = {};

        // Toast notification function
        function showNotification(message, type = 'success') {
            const toastHTML = `
                <div class="toast-notification ${type}">
                    <i class="fa-solid fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                    <span>${message}</span>
                </div>
            `;
            
            const container = document.body;
            const toast = document.createElement('div');
            toast.innerHTML = toastHTML;
            container.appendChild(toast);
            
            // Trigger animation
            setTimeout(() => {
                const notif = container.querySelector('.toast-notification');
                if (notif) notif.classList.add('show');
            }, 10);
            
            // Remove after 3 seconds
            setTimeout(() => {
                const notif = container.querySelector('.toast-notification');
                if (notif) {
                    notif.classList.remove('show');
                    setTimeout(() => notif.remove(), 300);
                }
            }, 3000);
        }

        let productModalInstance = null;

        function viewProductDetails(name, img, price, desc) {
            currentProduct = { name, img, price, desc };
            document.getElementById('productModalTitle').textContent = name;
            document.getElementById('productModalImg').src = img;
            document.getElementById('productModalPrice').textContent = parseFloat(price).toFixed(2);
            document.getElementById('productModalDesc').textContent = desc;
            
            // Reset size selection
            document.querySelectorAll('.size-btn').forEach(btn => btn.classList.remove('active'));
            
            // Show modal
            if (!productModalInstance) {
                productModalInstance = new bootstrap.Modal(document.getElementById('productModal'));
            }
            productModalInstance.show();
        }

        function closeProductModal() {
            if (productModalInstance) {
                productModalInstance.hide();
                // Ensure backdrop is removed
                setTimeout(() => {
                    document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
                    document.body.classList.remove('modal-open');
                }, 150);
            }
        }

        // Handle modal hide event to clean up
        document.getElementById('productModal').addEventListener('hidden.bs.modal', function() {
            document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = 'auto';
        });

        // Handle product card clicks
        document.addEventListener('click', function(e) {
            if (e.target.closest('.clickable-card')) {
                const card = e.target.closest('.clickable-card');
                const name = card.dataset.title;
                const img = card.dataset.img;
                const price = card.dataset.price;
                const desc = card.dataset.desc;
                viewProductDetails(name, img, price, desc);
            }
        });

        // Size selector functionality
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('size-btn')) {
                document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');
            }
        });

        // Add to Cart button
        document.getElementById('addToCartBtn').addEventListener('click', function() {
            const selectedSize = document.querySelector('.size-btn.active');
            if (!selectedSize) {
                showNotification('Please select a size', 'error');
                return;
            }

            const cartItem = {
                name: currentProduct.name,
                price: currentProduct.price,
                img: currentProduct.img,
                size: selectedSize.dataset.size,
                quantity: 1
            };

            // Get existing cart or create new one
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            
            // Check if item already exists
            const existingItem = cart.find(item => item.name === cartItem.name && item.size === cartItem.size);
            if (existingItem) {
                existingItem.quantity++;
            } else {
                cart.push(cartItem);
            }

            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();
            showNotification('Product added to cart successfully!', 'success');
            closeProductModal();
        });

        // Update cart count on page load
        function updateCartCount() {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            const cartCount = cart.reduce((total, item) => total + (parseInt(item.qty||item.quantity||1)||0), 0);
            const el = document.getElementById('cart-count');
            if(el) el.textContent = cartCount;
        }

        // Initialize cart count on page load
        window.addEventListener('load', updateCartCount);
    </script>

    <!-- Go to Top Button -->
    <button id="goToTopBtn" class="go-to-top-btn" title="Go to top">
        <i class="fas fa-chevron-up"></i>
    </button>

    <style>
        /* Go to Top Button Styles */
        .go-to-top-btn {
            display: none;
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #ff9900 0%, #7a6e60 100%);
            color: #222831;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            font-weight: bold;
            z-index: 999;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(148, 137, 121, 0.3);
        }

        .go-to-top-btn:hover {
            background: linear-gradient(135deg, #ffe2c2 0%, #8a7d70 100%);
            box-shadow: 0 6px 16px rgba(148, 137, 121, 0.4);
            transform: translateY(-3px);
        }

        .go-to-top-btn:active {
            transform: translateY(-1px);
        }

        .go-to-top-btn.show {
            display: block;
            animation: slideInUp 0.3s ease;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .go-to-top-btn {
                bottom: 20px;
                right: 20px;
                width: 45px;
                height: 45px;
                font-size: 16px;
            }
        }
    </style>

    <script>
        /**
         * GO TO TOP BUTTON
         * Shows/hides button based on scroll position
         * Smoothly scrolls to top when clicked
         */
        document.addEventListener('DOMContentLoaded', function() {
            const goToTopBtn = document.getElementById('goToTopBtn');
            
            // Show button when user scrolls down 300px
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    goToTopBtn.classList.add('show');
                } else {
                    goToTopBtn.classList.remove('show');
                }
            });
            
            // Scroll to top smoothly when button is clicked
            goToTopBtn.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>
