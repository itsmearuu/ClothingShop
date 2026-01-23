<?php
// login.php
session_start();
require_once 'config.php';

// If already logged in, redirect based on role
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        header('Location: admin/index.php');
    } else {
        header('Location: index.php');
    }
    exit();
}

// Store the intended destination
$redirect_to = $_GET['redirect'] ?? 'index.php';

$error = '';
$success = '';

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['firstName'];
            $_SESSION['user_role'] = $user['role']; // IMPORTANT: Set the role
            
            // Redirect based on role or intended destination
            if ($user['role'] === 'admin') {
                // Check if they were trying to access admin area
                if (strpos($redirect_to, 'admin') !== false) {
                    header('Location: ' . $redirect_to);
                } else {
                    header('Location: admin/index.php');
                }
            } else {
                // Regular user
                if ($redirect_to === 'index.php' || strpos($redirect_to, 'admin') !== false) {
                    header('Location: index.php');
                } else {
                    header('Location: ' . $redirect_to);
                }
            }
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "No account found with this email!";
    }
}

// Handle Registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $firstName = $conn->real_escape_string($_POST['firstName']);
    $lastName = $conn->real_escape_string($_POST['lastName']);
    $middleName = $conn->real_escape_string($_POST['middleName']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $birthday = $conn->real_escape_string($_POST['birthday']);
    $gender = $conn->real_escape_string($_POST['gender']);
    
    // Check if email already exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $error = "Email already registered!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (firstName, lastName, middleName, email, password, birthday, gender, role) VALUES (?, ?, ?, ?, ?, ?, ?, 'user')");
        $stmt->bind_param("sssssss", $firstName, $lastName, $middleName, $email, $password, $birthday, $gender);
        
        if ($stmt->execute()) {
            $success = "Registration successful! Please login.";
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Register - ClothingShop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <a href="login.php"><i class="fa-regular fa-user"></i></a>
            <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i><span id="cart-count" class="cart-badge">0</span></a>
        </div>
    </header>

    <div class="login-container">
        <?php if ($error): ?>
            <div class="alert alert-danger" style="max-width: 480px; margin: 0 auto 20px;">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success" style="max-width: 480px; margin: 0 auto 20px;">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <div class="form-box active" id="loginForm">
            <h2>Login</h2>
            <p>Welcome back! Please login to your account.</p>
            
            <form method="POST" action="login.php<?php echo !empty($redirect_to) ? '?redirect=' . urlencode($redirect_to) : ''; ?>">
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Login</button>
            </form>
            
            <p class="form-note">Don't have an account? <a href="#" onclick="toggleForms(); return false;">Register here</a></p>
        </div>

        <!-- Register Form -->
        <div class="form-box" id="registerForm">
            <h2>Register</h2>
            <p>Create a new account to get started.</p>
            
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <input type="text" name="firstName" placeholder="First Name" required>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="lastName" placeholder="Last Name" required>
                    </div>
                </div>
                
                <input type="text" name="middleName" placeholder="Middle Name">
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="password" name="password" placeholder="Password" minlength="6" required>
                <input type="date" name="birthday" required>
                
                <select name="gender" required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
                
                <button type="submit" name="register">Register</button>
            </form>
            
            <p class="form-note">Already have an account? <a href="#" onclick="toggleForms(); return false;">Login here</a></p>
        </div>
    </div>

    <footer>
        <div class="container">
            <div class="row">
                <div class="col">
                    <p class="Clo">CLOTHING<span style="color: #ff9d00">SHOP</span></p>
                    <p>Empowering customers with choice, confidence, and convenience—ClothingShop is your trusted destination for modern online shopping.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
    <script>
        function toggleForms() {
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            
            loginForm.classList.toggle('active');
            registerForm.classList.toggle('active');
        }
    </script>
</body>
</html>