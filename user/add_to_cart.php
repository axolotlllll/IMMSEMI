<?php
session_start();
require_once '../auth/check_auth.php';
require_once '../auth/config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $book_id = (int)$_POST['book_id'];
    $quantity = (int)$_POST['quantity'];

    // Validate quantity
    if ($quantity <= 0) {
        $_SESSION['error'] = "Invalid quantity";
        header("Location: books.php");
        exit();
    }

    // Check stock availability
    $stmt = $conn->prepare("SELECT stock_quantity FROM Books WHERE book_id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();

    if (!$book || $book['stock_quantity'] < $quantity) {
        $_SESSION['error'] = "Insufficient stock";
        header("Location: books.php");
        exit();
    }

    // Initialize cart if it doesn't exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add or update cart item
    if (isset($_SESSION['cart'][$book_id])) {
        $_SESSION['cart'][$book_id] += $quantity;
    } else {
        $_SESSION['cart'][$book_id] = $quantity;
    }

    $_SESSION['success'] = "Added to cart successfully";
    header("Location: books.php");
    exit();
}

// Invalid request
header("Location: books.php");
exit();
?>
