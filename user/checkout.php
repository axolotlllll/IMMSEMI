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

        // Get cart items from database to ensure latest state
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("
            SELECT ci.book_id, ci.quantity, b.price, b.stock_quantity, b.title 
            FROM cart_items ci
            JOIN books b ON ci.book_id = b.book_id
            WHERE ci.user_id = ?
            FOR UPDATE
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Your cart is empty");
        }

        $total_amount = 0;
        $order_items = [];

        // Verify stock and calculate total
        while ($item = $result->fetch_assoc()) {
            if ($item['stock_quantity'] < $item['quantity']) {
                throw new Exception("Insufficient stock for {$item['title']}");
            }
            $subtotal = $item['quantity'] * $item['price'];
            $total_amount += $subtotal;
            $order_items[] = $item;
        }

        // Create order
        $stmt = $conn->prepare("
            INSERT INTO orders (user_id, total_amount, order_status, payment_status) 
            VALUES (?, ?, 'pending', 'pending')
        ");
        $stmt->bind_param("id", $user_id, $total_amount);
        $stmt->execute();
        $order_id = $conn->insert_id;

        // Create order details and update stock
        $stmt_detail = $conn->prepare("
            INSERT INTO order_details (order_id, book_id, quantity, unit_price, subtotal) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt_stock = $conn->prepare("
            UPDATE books 
            SET stock_quantity = stock_quantity - ? 
            WHERE book_id = ?
        ");

        foreach ($order_items as $item) {
            $subtotal = $item['quantity'] * $item['price'];
            
            // Insert order detail
            $stmt_detail->bind_param("iiidd", $order_id, $item['book_id'], $item['quantity'], $item['price'], $subtotal);
            $stmt_detail->execute();

            // Update stock
            $stmt_stock->bind_param("ii", $item['quantity'], $item['book_id']);
            $stmt_stock->execute();
        }

        // Clear cart items from database
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Clear session cart
        unset($_SESSION['cart']);

        // Commit transaction
        $conn->commit();

        $_SESSION['success'] = "Order placed successfully!";
        header("Location: checkout_success.php");
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error'] = "Checkout failed: " . $e->getMessage();
        header("Location: cart.php");
        exit();
    }
}

// Redirect if accessed directly without POST
$_SESSION['error'] = "Invalid request";
header("Location: cart.php");
exit();
?>
