<?php
session_start();
require_once 'session.php';

$formSubmitted = false;
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $subject = htmlspecialchars(trim($_POST['subject'] ?? ''));
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));

    // Basic validation
    if (!empty($name) && !empty($email) && !empty($subject) && !empty($message)) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Here you would typically send an email or save to database
            // For now, we'll just show a success message
            $formSubmitted = true;
            $successMessage = "Thank you for your message! We'll get back to you soon.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - ClothingShop</title>

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
            <?php if(isLoggedIn()): ?>
                <a href="profile.php"><i class="fa-regular fa-user"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?></a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php"><i class="fa-jelly-fill fa-regular fa-user"></i></a>
            <?php endif; ?>
            <a href="cart.php" id="cart-link"><i class="fa-solid fa-cart-shopping"></i><span id="cart-badge" class="cart-badge">0</span></a>
        </div>
    </header>

    <!-- CONTACT HEADER -->
    <div class="contact-header">
        <h1>Contact Us</h1>
        <p>We'd love to hear from you. Get in touch with us today!</p>
    </div>

    <!-- CONTACT CONTENT -->
    <div class="container contact-container">
        <div class="row">
            <!-- CONTACT FORM -->
            <div class="col-lg-7">
                <div class="contact-form-section">
                    <h2>Send us a Message</h2>
                    <p>Fill out the form below and we'll get back to you as soon as possible.</p>

                    <?php if ($formSubmitted): ?>
                        <div class="alert alert-success">
                            <i class="fa-solid fa-check-circle"></i> <?= $successMessage ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="contact-form">
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="Your Full Name" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="Your Email" required>
                        </div>

                        <div class="form-group">
                            <label for="subject">Subject *</label>
                            <input type="text" id="subject" name="subject" class="form-control" placeholder="Subject" required>
                        </div>

                        <div class="form-group">
                            <label for="message">Message *</label>
                            <textarea id="message" name="message" class="form-control" placeholder="Your Message" rows="6" required></textarea>
                        </div>

                        <button type="submit" class="btn-submit">Send Message</button>
                    </form>
                </div>
            </div>

            <!-- CONTACT INFO -->
            <div class="col-lg-5">
                <div class="contact-info-section">
                    <h2>Contact Information</h2>
                    <p>Reach out to us through any of these channels</p>

                    <!-- ADDRESS -->
                    <div class="contact-info-item">
                        <div class="info-icon">
                            <i class="fa-solid fa-map-pin"></i>
                        </div>
                        <div class="info-content">
                            <h4>Address</h4>
                            <p>123 Fashion Street<br>Manila, Philippines 1000<br>Metro Manila</p>
                        </div>
                    </div>

                    <!-- PHONE -->
                    <div class="contact-info-item">
                        <div class="info-icon">
                            <i class="fa-solid fa-phone"></i>
                        </div>
                        <div class="info-content">
                            <h4>Phone</h4>
                            <p><a href="tel:+639026488930">+63 902 6488 930</a><br><a href="tel:+639156789012">+63 915 6789 012</a></p>
                        </div>
                    </div>

                    <!-- EMAIL -->
                    <div class="contact-info-item">
                        <div class="info-icon">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <div class="info-content">
                            <h4>Email</h4>
                            <p><a href="mailto:contact@clothingshop.com">contact@clothingshop.com</a><br><a href="mailto:support@clothingshop.com">support@clothingshop.com</a></p>
                        </div>
                    </div>

                    <!-- BUSINESS HOURS -->
                    <div class="contact-info-item">
                        <div class="info-icon">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <div class="info-content">
                            <h4>Business Hours</h4>
                            <p>Monday - Friday: 9:00 AM - 6:00 PM<br>Saturday: 10:00 AM - 4:00 PM<br>Sunday: Closed</p>
                        </div>
                    </div>

                    <!-- SOCIAL MEDIA -->
                    <div class="contact-info-item">
                        <div class="info-icon">
                            <i class="fa-solid fa-share-nodes"></i>
                        </div>
                        <div class="info-content">
                            <h4>Follow Us</h4>
                            <div class="social-links">
                                <a href="#" title="Facebook"><i class="fa-brands fa-facebook"></i></a>
                                <a href="#" title="Instagram"><i class="fa-brands fa-instagram"></i></a>
                                <a href="#" title="Twitter"><i class="fa-brands fa-twitter"></i></a>
                                <a href="#" title="TikTok"><i class="fa-brands fa-tiktok"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MAP SECTION -->
        <div class="map-section">
            <h2>Find Us</h2>
            <div class="map-container">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3861.1476697834453!2d120.97355542346898!3d14.599512285784308!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397b7f9d3e9d3d1%3A0x8e8e8e8e8e8e8e8e!2sManila%2C%20Metro%20Manila!5e0!3m2!1sen!2sph!4v1234567890" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>

        <!-- FAQ SECTION -->
        <div class="faq-section">
            <h2>Frequently Asked Questions</h2>
            <div class="row">
                <div class="col-md-6">
                    <div class="faq-item">
                        <h4>How do I track my order?</h4>
                        <p>Once your order is shipped, you'll receive a tracking number via email. You can use this number to track your package on our website or the carrier's website.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="faq-item">
                        <h4>What's your return policy?</h4>
                        <p>We offer 30-day returns on all items. The product must be unused and in original condition. Simply contact us to start the return process.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="faq-item">
                        <h4>How long does shipping take?</h4>
                        <p>Standard shipping typically takes 5-7 business days. Express shipping options are available at checkout for faster delivery.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="faq-item">
                        <h4>Do you offer international shipping?</h4>
                        <p>Yes! We ship to selected countries. Check our shipping page to see if we deliver to your location.</p>
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

        // Clear success message after 5 seconds
        window.addEventListener('load', function() {
            const alert = document.querySelector('.alert-success');
            if (alert) {
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 5000);
            }
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
            background: linear-gradient(135deg, #ff9900 0%, #7a6e60 100%);
            color: #222831;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
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
