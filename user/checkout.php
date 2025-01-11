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

        // Calculate total amount
        $total_amount = 0;
        foreach ($_SESSION['cart'] as $book_id => $quantity) {
            $stmt = $conn->prepare("SELECT price FROM Books WHERE book_id = ?");
            $stmt->bind_param("i", $book_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $book = $result->fetch_assoc();
            $total_amount += $quantity * $book['price'];
        }

        // Create order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, order_status, payment_status) VALUES (?, ?, 'pending', 'pending')");
        $stmt->bind_param("id", $_SESSION['user_id'], $total_amount);
        $stmt->execute();
        $order_id = $conn->insert_id;

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

            // Calculate subtotal for this item
            $subtotal = $quantity * $book['price'];

            // Create order detail record
            $stmt = $conn->prepare("INSERT INTO order_details (order_id, book_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iiidd", $order_id, $book_id, $quantity, $book['price'], $subtotal);
            $stmt->execute();

            // Update stock
            $stmt = $conn->prepare("UPDATE Books SET stock_quantity = stock_quantity - ? WHERE book_id = ?");
            $stmt->bind_param("ii", $quantity, $book_id);
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
