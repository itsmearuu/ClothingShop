<?php
session_start();
require_once 'session.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - ClothingShop</title>

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
        <div class="mylogo" onclick="window.location.href='index.php'">CLOTHINGSHOP</div>
        <nav>
            <a href="index.php">HOME</a>
            <a href="about.php">ABOUT</a>
            <a href="products.php">PRODUCTS</a>
            <a href="contact.php">CONTACT</a>
        </nav>

        <div class="logsign">
            <?php if(isLoggedIn()): ?>
                <a href="profile.php"><i class="fa-regular fa-user"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?></a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php"><i class="fa-jelly-fill fa-regular fa-user"></i></a>
            <?php endif; ?>
            <a href="cart.php" id="cart-link"><i class="fa-solid fa-cart-shopping"></i><span id="cart-badge" class="cart-badge">0</span></a>
        </div>
    </header>

    <!-- ABOUT HEADER -->
    <div class="about-header">
        <h1>About ClothingShop</h1>
        <p>Your destination for trendy, comfortable, and quality fashion</p>
    </div>

    <!-- ABOUT CONTENT -->
    <div class="container about-container">
        <!-- ABOUT US SECTION -->
        <section class="about-section">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2>Who We Are</h2>
                    <p>Welcome to ClothingShop, your trusted online destination for modern fashion and style. We are passionate about bringing you the latest trends, timeless classics, and comfortable everyday wear that makes you feel confident and fabulous.</p>
                    <p>Founded with a mission to make quality fashion accessible to everyone, ClothingShop offers a carefully curated collection of clothing and accessories. Whether you're looking for casual wear, professional attire, or the perfect outfit for a special occasion, we have something for everyone.</p>
                    <p>Our commitment to excellence means we handpick every item in our collection to ensure quality, style, and value. We believe that fashion should be fun, affordable, and inclusive for all.</p>
                </div>
                <div class="col-lg-6 about-image">
                    <div class="about-placeholder">
                        <i class="fa-solid fa-shirt"></i>
                    </div>
                </div>
            </div>
        </section>

        <!-- SERVICES SECTION -->
        <section class="services-section">
            <h2>Our Services</h2>
            <p class="services-intro">We offer a range of services to enhance your shopping experience</p>
            
            <div class="row services-grid">
                <!-- SERVICE 1 -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fa-solid fa-truck-fast"></i>
                        </div>
                        <h3>Fast & Free Shipping</h3>
                        <p>We offer quick and reliable shipping to get your orders to you in no time. Enjoy free shipping on orders over a certain amount, making your shopping experience even more rewarding.</p>
                    </div>
                </div>

                <!-- SERVICE 2 -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fa-solid fa-rotation"></i>
                        </div>
                        <h3>Easy Returns & Exchanges</h3>
                        <p>Not satisfied with your purchase? No problem! We offer hassle-free returns and exchanges within 30 days. Your satisfaction is our priority, and we want you to love what you buy.</p>
                    </div>
                </div>

                <!-- SERVICE 3 -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fa-solid fa-headset"></i>
                        </div>
                        <h3>24/7 Customer Support</h3>
                        <p>Have questions or need assistance? Our dedicated customer support team is available around the clock to help you. Contact us via email, phone, or live chat for quick and friendly service.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- VALUES SECTION -->
        <section class="values-section">
            <h2>Why Choose ClothingShop?</h2>
            <div class="row values-grid">
                <div class="col-md-3 col-sm-6 value-item">
                    <div class="value-icon">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <h4>Quality Products</h4>
                    <p>Carefully selected items that meet our high standards</p>
                </div>
                <div class="col-md-3 col-sm-6 value-item">
                    <div class="value-icon">
                        <i class="fa-solid fa-tag"></i>
                    </div>
                    <h4>Affordable Prices</h4>
                    <p>Great fashion doesn't have to break the bank</p>
                </div>
                <div class="col-md-3 col-sm-6 value-item">
                    <div class="value-icon">
                        <i class="fa-solid fa-heart"></i>
                    </div>
                    <h4>Customer Focused</h4>
                    <p>Your satisfaction is at the heart of everything we do</p>
                </div>
                <div class="col-md-3 col-sm-6 value-item">
                    <div class="value-icon">
                        <i class="fa-solid fa-star"></i>
                    </div>
                    <h4>Trend Setting</h4>
                    <p>Always bringing the latest fashion trends to you</p>
                </div>
            </div>
        </section>

        <!-- CTA SECTION -->
        <section class="cta-section">
            <h2>Ready to Shop?</h2>
            <p>Explore our amazing collection of clothing and find your perfect style</p>
            <a href="products.php" class="btn btn-primary btn-lg">Browse Our Products</a>
        </section>
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
    <script>
        // Update cart badge
        window.addEventListener('load', function() {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            const cartCount = cart.reduce((total, item) => total + (parseInt(item.qty||item.quantity||1)||0), 0);
            const el = document.getElementById('cart-badge');
            if(el) el.textContent = cartCount;
        });
    </script>

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
