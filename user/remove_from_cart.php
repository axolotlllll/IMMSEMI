<?php
session_start();
require_once '../auth/check_auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_id'])) {
    $book_id = (int)$_POST['book_id'];
    
    if (isset($_SESSION['cart'][$book_id])) {
        unset($_SESSION['cart'][$book_id]);
        $_SESSION['success'] = "Item removed from cart";
    }
}

header("Location: cart.php");
exit();
?>
