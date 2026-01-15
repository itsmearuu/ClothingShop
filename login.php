<?php
/**
 * LOGIN PAGE - login.php
 * Displays login and registration forms
 * Features:
 * - Login form with email/password
 * - Registration form with new user fields
 * - Error handling and form validation display
 * - Toggle between login and register forms
 */

session_start();

// Retrieve any error messages from session (set by login-register.php handler)
$errors = [
    'login' => $_SESSION['login_error'] ?? '',
    'register' => $_SESSION['register_error'] ?? ''
];

// Determine which form was active when redirected back (login or register)
$activeform = $_SESSION['active_form'] ?? 'login';

// Clear session variables after retrieving them
session_unset();

/**
 * HELPER FUNCTION: showError()
 * Displays error message in styled HTML alert box
 * @param string $error - Error message to display
 * @return string - HTML formatted error message or empty string
 */
function showError($error){
    return !empty($error) ? "<p style='font-size: 16px; background-color: rgba(220, 53, 69, 0.2); color: #dc3545; padding: 12px; border-radius: 6px; margin-bottom: 20px; text-align: center; border: 1px solid #dc3545;'>$error</p>" : '';
}

/**
 * HELPER FUNCTION: isActiveForm()
 * Determines if a form tab should be displayed
 * @param string $formName - Name of the form (login or register)
 * @param string $activeform - Currently active form
 * @return string - 'active' class or empty string
 */
function isActiveForm($formName, $activeform){
    return $formName === $activeform ? 'active' : '';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ClothingShop</title>

    <!-- Fonts & icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Oswald:wght@200..700&family=Poppins:wght@400;500;800&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">

    <style>
        /* Login page styling */
        body { 
            background-color: #222831;
            color: #DFD0B8;
        }
        
        .login-container {
            background-color: #222831;
        }
        
        .form-box {
            background-color: #393E46;
            border: 1px solid #4a5159;
            color: #DFD0B8;
        }
        
        .form-box input,
        .form-box select {
            background-color: #2c3137;
            color: #DFD0B8;
            border: 1px solid #4a5159;
        }
        
        .form-box input::placeholder {
            color: #948979;
        }
        
        .form-box button {
            background: linear-gradient(135deg, #948979 0%, #7a6e60 100%);
            color: #222831;
            font-weight: bold;
        }
        
        .form-box button:hover {
            background: linear-gradient(135deg, #a39a8a 0%, #8a7d70 100%);
        }
        
        .form-box h2 {
            color: #DFD0B8;
        }
        
        .form-box a {
            color: #948979;
        }
        
        .form-box a:hover {
            color: #DFD0B8;
        }
    </style>
</head>
<body>

    <header>
        <div class="mylogo">CLOTHINGSHOP</div>
        <nav>
            <a href="index.php">HOME</a>
            <a href="">PRODUCTS</a>
            <a href="">ABOUT</a>
            <a href="">CONTACT</a>
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

    <div class="container login-container">
        <div class="form-box <?= isActiveForm('login', $activeform); ?>" id="login-form">
            <form action="login-register.php" method="post">
                <h2>Login</h2>
                <?= showError($errors['login']); ?>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Login</button>
                <p>Don't have an account? <a href="#" onclick="showForm('register-form')">Register</a></p> 
            </form>
        </div>
        <div class="form-box <?= isActiveForm('register', $activeform); ?>" id="register-form">
            <form action="login-register.php" method="post">
                <h2>Register</h2>
                <?= showError($errors['register']); ?>
                <input type="text" name="firstName" placeholder="First Name" required>
                <input type="text" name="middleName" placeholder="Middle Name">
                <input type="text" name="lastName" placeholder="Last Name" required>
                <input type="date" name="birthday" placeholder="Birthday" required>
                <select name="gender" required>
                    <option value="">--Select Gender--</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="register">Register</button>
                <p>Already have an account? <a href="#" onclick="showForm('login-form')">Login</a></p> 
            </form>
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
                        <a href="">PRODUCTS</a>
                        <a href="">ABOUT</a>
                        <a href="">CONTACT</a>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>