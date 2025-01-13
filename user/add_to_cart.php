<?php
session_start();
require_once '../auth/check_auth.php';
require_once '../auth/config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $book_id = (int)$_POST['book_id'];
    $quantity = (int)$_POST['quantity'];
    $user_id = $_SESSION['user_id'];

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

    try {
        // Start transaction
        $conn->begin_transaction();

        // Check if item already exists in cart
        $stmt = $conn->prepare("SELECT quantity FROM cart_items WHERE user_id = ? AND book_id = ?");
        $stmt->bind_param("ii", $user_id, $book_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $existing_item = $result->fetch_assoc();

        if ($existing_item) {
            // Update existing cart item
            $new_quantity = $existing_item['quantity'] + $quantity;
            $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND book_id = ?");
            $stmt->bind_param("iii", $new_quantity, $user_id, $book_id);
            $stmt->execute();
        } else {
            // Add new cart item
            $stmt = $conn->prepare("INSERT INTO cart_items (user_id, book_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $user_id, $book_id, $quantity);
            $stmt->execute();
        }

        // Update session cart to match database
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        $_SESSION['cart'][$book_id] = $existing_item ? $new_quantity : $quantity;

        // Commit transaction
        $conn->commit();
        $_SESSION['success'] = "Added to cart successfully";
        header("Location: books.php");
        exit();

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $_SESSION['error'] = "Error adding to cart: " . $e->getMessage();
        header("Location: books.php");
        exit();
    }
}

// Invalid request
header("Location: books.php");
exit();
?>
