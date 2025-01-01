<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /IMSEMI/auth/login.php");
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header("Location: /IMSEMI/user/dashboard.php");
        exit();
    }
}
?>
