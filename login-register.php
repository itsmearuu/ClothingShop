<?php
/**
 * LOGIN-REGISTER HANDLER
 * Handles user registration and login functionality
 * - Registration: Collects user data, validates email, initiates 2FA verification
 * - Login: Authenticates user credentials with brute force protection
 */

require_once 'session.php';
require_once 'config.php';

/**
 * REGISTRATION HANDLER
 * Processes user registration form submission
 * Steps:
 * 1. Collect form data (name, email, password, birthday, gender)
 * 2. Check if email already exists
 * 3. Generate verification code and send email
 * 4. Store pending registration in session for verification
 */
if (isset($_POST['register'])) {
    // Retrieve registration form data
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $middleName = $_POST['middleName'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $birthday = $_POST['birthday'];
    $gender = $_POST['gender'];

    // Check if email already exists in database (prevent duplicate accounts)
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $checkEmail = $stmt->get_result();
    if ($checkEmail->num_rows > 0) {
        $_SESSION['register_error'] = "Email already exists.";
        $_SESSION['active_form'] = 'register';
    } else {
        // start 2FA email verification: store pending registration in session and send code
        $code = rand(100000,999999);
        $_SESSION['pending_registration'] = [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'middleName' => $middleName,
            'email' => $email,
            'password' => $password,
            'birthday' => $birthday,
            'gender' => $gender,
            'code' => (string)$code,
            'expires' => time() + 15*60 // 15 minutes
        ];

        // try to send email with code
        $subject = "Your ClothingShop verification code";
        $message = "Your verification code is: $code\nThis code expires in 15 minutes.";
        $headers = "From: no-reply@localhost" . "\r\n" .
                   "Reply-To: no-reply@localhost" . "\r\n" .
                   "X-Mailer: PHP/" . phpversion();

        // mail may not be configured on local dev; if mail fails we'll still proceed to verification page and show code there for testing
        @mail($email, $subject, $message, $headers);
        $_SESSION['register_info'] = 'A verification code was sent to your email. If you do not receive it, the code will be shown on the next screen for testing.';
        header('Location: verify.php');
        exit();
    }

    header("Location: login.php");
    exit();
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    // failed login protection: 5 attempts -> 1 minute timeout (per email, stored in session)
    if (!isset($_SESSION['failed_login'])) {
        $_SESSION['failed_login'] = [];
    }
    if (!isset($_SESSION['failed_login'][$email])) {
        $_SESSION['failed_login'][$email] = ['count' => 0, 'last' => 0];
    }

    $now = time();
    $entry = &$_SESSION['failed_login'][$email];
    $maxFailed = 5;
    $lockoutSeconds = 60; // 1 minute

    if ($entry['count'] >= $maxFailed && ($now - $entry['last']) < $lockoutSeconds) {
        $remaining = $lockoutSeconds - ($now - $entry['last']);
        $_SESSION['login_error'] = "Too many failed login attempts. Try again in {$remaining} seconds.";
        $_SESSION['active_form'] = 'login';
        header("Location: login.php");
        exit();
    }

    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // successful login: reset failed attempts for this email
            unset($_SESSION['failed_login'][$email]);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['firstName'] . ' ' . $user['lastName'];

            // Redirect to homepage
            header("Location: index.php");
            exit();
        }
    }

    // login failed: increment counter and record time
    $entry['count'] = ($entry['count'] ?? 0) + 1;
    $entry['last'] = $now;
    $_SESSION['login_error'] = "Invalid email or password.";
    $_SESSION['active_form'] = 'login';
    header("Location: login.php");
    exit();
}
?>