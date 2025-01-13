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
        header("Location: cart.php");
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
        header("Location: cart.php");
        exit();
    }

    try {
        // Start transaction
        $conn->begin_transaction();

        // First check if item exists in cart
        $stmt = $conn->prepare("SELECT cart_item_id FROM cart_items WHERE user_id = ? AND book_id = ?");
        $stmt->bind_param("ii", $user_id, $book_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Item exists, update quantity
            $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND book_id = ?");
            $stmt->bind_param("iii", $quantity, $user_id, $book_id);
            $stmt->execute();
        } else {
            // Item doesn't exist, insert new
            $stmt = $conn->prepare("INSERT INTO cart_items (user_id, book_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $user_id, $book_id, $quantity);
            $stmt->execute();
        }

        // Update session cart
        $_SESSION['cart'][$book_id] = $quantity;

        // Commit transaction
        $conn->commit();
        $_SESSION['success'] = "Cart updated successfully";

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $_SESSION['error'] = "Error updating cart: " . $e->getMessage();
    }
}

header("Location: cart.php");
exit();
?>
