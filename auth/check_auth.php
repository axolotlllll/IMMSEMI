<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "Please login to continue";
        header("Location: ../auth/login.php");
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
        $_SESSION['error'] = "Access denied. Admin privileges required.";
        header("Location: ../auth/login.php");
        exit();
    }
}
?>
