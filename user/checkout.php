<?php
session_start();
require_once '../auth/check_auth.php';
require_once '../auth/config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        $_SESSION['error'] = "Your cart is empty";
        header("Location: cart.php");
        exit();
    }

    try {
        // Start transaction
        $conn->begin_transaction();

        foreach ($_SESSION['cart'] as $book_id => $quantity) {
            // Check stock availability and get book price
            $stmt = $conn->prepare("SELECT stock_quantity, price FROM Books WHERE book_id = ? FOR UPDATE");
            $stmt->bind_param("i", $book_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $book = $result->fetch_assoc();

            if (!$book || $book['stock_quantity'] < $quantity) {
                throw new Exception("Insufficient stock for some items");
            }

            // Calculate total price for this item
            $total_price = $quantity * $book['price'];

            // Update stock
            $stmt = $conn->prepare("UPDATE Books SET stock_quantity = stock_quantity - ? WHERE book_id = ?");
            $stmt->bind_param("ii", $quantity, $book_id);
            $stmt->execute();

            // Create sale record with total price
            $stmt = $conn->prepare("INSERT INTO Sales (user_id, book_id, quantity, total_price, sale_date) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("iiid", $_SESSION['user_id'], $book_id, $quantity, $total_price);
            $stmt->execute();
        }

        // Commit transaction
        $conn->commit();

        // Clear cart
        unset($_SESSION['cart']);
        $_SESSION['success'] = "Order placed successfully!";
        header("Location: orders.php");
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
        header("Location: cart.php");
        exit();
    }
}

// Invalid request
header("Location: cart.php");
exit();
?>
