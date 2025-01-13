<?php
session_start();
require_once '../auth/check_auth.php';
require_once '../auth/config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_id'])) {
    $book_id = (int)$_POST['book_id'];
    $user_id = $_SESSION['user_id'];
    
    try {
        // Start transaction
        $conn->begin_transaction();

        // Remove item from database
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ? AND book_id = ?");
        $stmt->bind_param("ii", $user_id, $book_id);
        $stmt->execute();

        // Remove from session
        if (isset($_SESSION['cart'][$book_id])) {
            unset($_SESSION['cart'][$book_id]);
        }

        // Commit transaction
        $conn->commit();
        $_SESSION['success'] = "Item removed from cart";

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $_SESSION['error'] = "Error removing item: " . $e->getMessage();
    }
}

header("Location: cart.php");
exit();
?>
