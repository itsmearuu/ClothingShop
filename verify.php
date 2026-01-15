<?php
/**
 * EMAIL VERIFICATION PAGE - verify.php
 * Handles 2FA email verification during registration
 * Steps:
 * 1. Display verification form
 * 2. User enters 6-digit code received via email
 * 3. Code is validated against session data
 * 4. If valid, user data is inserted into database
 * 5. User redirected to login page
 */

require_once 'session.php';
require_once 'config.php';

// Retrieve pending registration data from session
$pending = $_SESSION['pending_registration'] ?? null;
$message = $_SESSION['register_info'] ?? '';

// If no pending registration, redirect back to login
if (!$pending) {
    $_SESSION['register_error'] = 'No pending registration found. Please register first.';
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /**
     * RESEND CODE HANDLER
     * Allows user to request a new verification code
     * Includes 60-second cooldown to prevent spam
     */
    if (isset($_POST['resend_code'])){
        $now = time();
        $last = $pending['last_sent'] ?? 0;
        $wait = 60; // seconds cooldown before allowing resend
        
        // Check if user is within cooldown period
        if ($now - $last < $wait){
            $_SESSION['verify_error'] = 'Please wait '.($wait - ($now - $last)).' seconds before resending.';
            header('Location: verify.php'); 
            exit();
        }
        
        // Generate new verification code
        $code = rand(100000,999999);
        $_SESSION['pending_registration']['code'] = (string)$code;
        $_SESSION['pending_registration']['expires'] = $now + 15*60;
        $_SESSION['pending_registration']['last_sent'] = $now;
        
        // Send verification email with new code
        $email = $pending['email'];
        $subject = "Your ClothingShop verification code";
        $message = "Your verification code is: $code\nThis code expires in 15 minutes.";
        $headers = "From: no-reply@localhost" . "\r\n" .
                   "Reply-To: no-reply@localhost" . "\r\n" .
                   "X-Mailer: PHP/" . phpversion();
        @mail($email, $subject, $message, $headers);
        $_SESSION['register_info'] = 'A new verification code was sent to your email.';
        header('Location: verify.php');
        exit();
    }

    /**
     * CODE VERIFICATION HANDLER
     * Validates the 6-digit code entered by user
     * If valid and not expired, inserts user into database
     */
    $code = trim($_POST['code'] ?? '');
    if ($code === $pending['code'] && time() <= $pending['expires']) {
        // Code is valid - extract and sanitize user data for database insertion
        $firstName = $conn->real_escape_string($pending['firstName']);
        $lastName = $conn->real_escape_string($pending['lastName']);
        $middleName = $conn->real_escape_string($pending['middleName']);
        $email = $conn->real_escape_string($pending['email']);
        $password = $conn->real_escape_string($pending['password']);
        $birthday = $conn->real_escape_string($pending['birthday']);
        $gender = $conn->real_escape_string($pending['gender']);

        // Insert new user into database with current timestamp as createdAt
        $conn->query("INSERT INTO users (firstName, lastName, middleName, email, password, birthday, gender, createdAt) VALUES ('$firstName', '$lastName', '$middleName', '$email', '$password', '$birthday', '$gender', NOW())");
        
        // Clear pending registration from session
        unset($_SESSION['pending_registration']);
        $_SESSION['register_success'] = 'Registration complete. You can now log in.';
        header('Location: login.php');
        exit();
    } else {
        // Code is invalid or expired
        $_SESSION['verify_error'] = 'Invalid or expired code.';
        header('Location: verify.php');
        exit();
    }
}

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Verify - ClothingShop</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #222831;
            color: #DFD0B8;
        }
        
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            background-color: #222831;
        }
        
        .form-box {
            background: #393E46;
            padding: 40px;
            border-radius: 10px;
            border: 1px solid #4a5159;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 100%;
        }
        
        .form-box h2 {
            color: #DFD0B8;
            margin-bottom: 20px;
        }
        
        .form-box p {
            color: #DFD0B8;
            margin-bottom: 15px;
        }
        
        .form-box input {
            background-color: #2c3137;
            color: #DFD0B8;
            border: 1px solid #4a5159;
        }
        
        .form-box input:focus {
            border-color: #948979;
            box-shadow: 0 0 5px rgba(148, 137, 121, 0.3);
        }
        
        .form-note {
            color: #666;
            font-size: 14px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #948979 0%, #7a6e60 100%) !important;
            border: none;
            color: #222831 !important;
            font-weight: bold;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #a39a8a 0%, #8a7d70 100%) !important;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="form-box">
            <h2>Verify your email</h2>
            <?php if(!empty($message)) echo "<p class='form-note'>".htmlspecialchars($message)."</p>"; ?>
            <?php if(!empty($_SESSION['verify_error'])){ echo "<p style='color:#a42834'>".htmlspecialchars($_SESSION['verify_error'])."</p>"; unset($_SESSION['verify_error']); } ?>
            <form method="post">
                <p>Enter the 6-digit code sent to <strong><?php echo htmlspecialchars($pending['email']); ?></strong></p>
                <input type="text" name="code" class="form-control" placeholder="123456" required maxlength="6">
                <div class="mt-3">
                    <button class="btn btn-primary">Verify</button>
                    <a href="login.php" class="btn btn-secondary ms-2">Cancel</a>
                </div>
            </form>
            <?php
            // For local development, show the code on screen if mail may not work
            echo "<p class='form-note mt-3'>If you did not receive the email, use this test code: <strong>".htmlspecialchars($pending['code'])."</strong></p>";
            ?>
        </div>
    </div>
</body>
</html>
