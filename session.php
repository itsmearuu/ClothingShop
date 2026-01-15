<?php
// Central session handling: start session, inactivity timeout, helpers
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$inactive = 30 * 60; // 30 minutes inactivity timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive)) {
    // timeout: clear session
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['timeout_message'] = 'Session timed out due to inactivity.';
}
$_SESSION['last_activity'] = time();

function isLoggedIn(){
    return !empty($_SESSION['user_id']);
}

function requireLogin($redirect = true){
    if (!isLoggedIn()){
        if ($redirect){ header('Location: login.php'); exit(); }
        return false;
    }
    return true;
}

?>
