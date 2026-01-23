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
        $products = array_slice($data['products']['clothes'], 0, 8);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <!-- Oswaldfont -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Oswald:wght@200..700&family=Poppins:wght@400;500;800&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <!-- fontawesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
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
            <?php require_once 'session.php'; ?>
            <?php if(isLoggedIn()): ?>
                <a href="profile.php"><i class="fa-regular fa-user"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?></a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php"><i class="fa-jelly-fill fa-regular fa-user"></i></a>
            <?php endif; ?>
            <a href="cart.php" id="cart-link"><i class="fa-solid fa-cart-shopping"></i><span id="cart-count" class="cart-badge">0</span></a>
        </div>

    </header>

    <!-- CAROUSEL -->
    <div class="wallpapercarousel carousel-container">
        <div id="carouselSample" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">

            <!-- Carousel Items -->
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="images/clo123.avif" class="d-block w-100" alt="Fashion Collection">
                    <div class="carousel-overlay"></div>
                    <div class="carousel-caption-container">
                        <h2 class="carousel-main-title">Welcome to ClothingShop</h2>
                        <p class="carousel-subtitle">Discover the latest trends in fashion and style</p>
                        <a href="products.php" class="carousel-btn">Shop Now</a>
                    </div>
                </div>

                <div class="carousel-item">
                    <img src="images/hand-drawn-fashion-shop-pattern-background_23-2150842416.avif" class="d-block w-100" alt="New Arrivals">
                    <div class="carousel-overlay"></div>
                    <div class="carousel-caption-container">
                        <h2 class="carousel-main-title">New Arrivals</h2>
                        <p class="carousel-subtitle">Check out our latest collection of premium clothing</p>
                        <a href="products.php" class="carousel-btn">Explore Collection</a>
                    </div>
                </div>

                <div class="carousel-item">
                    <img src="images/shopper.avif" class="d-block w-100" alt="Special Offer">
                    <div class="carousel-overlay"></div>
                    <div class="carousel-caption-container">
                        <h2 class="carousel-main-title">Exclusive Offers</h2>
                        <p class="carousel-subtitle">Get up to 50% off on selected items this season</p>
                        <a href="products.php" class="carousel-btn">View Deals</a>
                    </div>
                </div>
            </div>

            <!-- Carousel Controls -->
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselSample" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselSample" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
    <!-- /CAROUSEL -->


    <!-- CARDS -->
    <div class="container">
        <p>LATEST <span>COLLECTIONS</span> <br>Step into style with our latest collection! Trendy, comfy, and ready to wear — find the perfect look that turns everyday moments into fashion statements.</p>
        <div class="row">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $index => $product): ?>
                    <?php if ($index > 0 && $index % 4 == 0): ?>
                        </div>
                        <div class="row">
                    <?php endif; ?>
                    <div class="col">
                        <div class="card product-card" 
                             data-title="<?= htmlspecialchars($product['prodName']) ?>" 
                             data-price="<?= $product['price'] ?>" 
                             data-img="<?= htmlspecialchars($product['photo1']) ?>" 
                             data-desc="<?= htmlspecialchars($product['description']) ?>">
                            <img src="<?= htmlspecialchars($product['photo1']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['prodName']) ?>">
                            <div class="card-body">
                                <p class="card-text"><?= htmlspecialchars($product['prodName']) ?></p>
                                <p>$<?= number_format($product['price'], 2) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <!-- /CARDS -->
    
        <!-- Product Detail Modal -->
        <div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="productModalLabel">Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="modal-product">
                            <img id="modal-product-img" src="" alt="" />
                            <div class="modal-product-info">
                                <h3 id="modal-product-title"></h3>
                                <p id="modal-product-desc"></p>
                                                                <p class="modal-price">Price: $<span id="modal-product-price"></span></p>

                                                                <div class="size-selector">
                                                                        <label for="sizes">Size:</label>
                                                                        <div class="sizes">
                                                                                <button type="button" class="size-btn" data-size="S">S</button>
                                                                                <button type="button" class="size-btn" data-size="M">M</button>
                                                                                <button type="button" class="size-btn" data-size="L">L</button>
                                                                                <button type="button" class="size-btn" data-size="XL">XL</button>
                                                                        </div>
                                                                </div>

                                                                <div class="modal-actions">
                                                                    <button id="add-to-cart-btn" class="btn btn-primary">Add to cart</button>
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
                    <p class="Clo">CLOTHINGSHOP</p>
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
                    <p>+63 902 6488 930</p>
                    <p>contact@ClothingShop.com</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Go to Top Button -->
    <button id="goToTopBtn" class="go-to-top-btn" title="Go to top">
        <i class="fas fa-chevron-up"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
    
    <style>
        /* Go to Top Button Styles */
        .go-to-top-btn {
            display: none;
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #948979 0%, #7a6e60 100%);
            color: #222831;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            font-weight: bold;
            z-index: 999;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        }

        .go-to-top-btn:hover {
            background: linear-gradient(135deg, #a39a8a 0%, #8a7d70 100%);
            box-shadow: 0 6px 16px rgba(148, 137, 121, 0.4);
            transform: translateY(-3px);
        }

        .go-to-top-btn:active {
            transform: translateY(-1px);
        }

        /* Show the button when scrolled down */
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

        /* Mobile responsive */
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
