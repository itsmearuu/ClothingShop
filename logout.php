<?php
// logout.php
session_start();

// Store if user was admin before logout
$wasAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

// Destroy all session data
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy the session
session_destroy();

// Clear any output buffers
if (ob_get_level()) {
    ob_end_clean();
}

// Redirect to appropriate page
if ($wasAdmin) {
    header('Location: login.php');
} else {
    header('Location: index.php');
}
exit();
?>