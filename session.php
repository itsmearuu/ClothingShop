<?php
// session.php

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_email']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ../index.php');
        exit();
    }
}

// Function to check if user can edit profile
function canEditProfile($profileUserId) {
    if (!isLoggedIn()) {
        return false;
    }
    
    // Admins can edit any profile
    if (isAdmin()) {
        return true;
    }
    
    // Users can only edit their own profile
    return $_SESSION['user_id'] == $profileUserId;
}
?>