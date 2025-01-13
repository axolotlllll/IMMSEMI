<?php
require_once '../auth/check_auth.php';
require_once '../auth/config.php';
requireLogin();

// Sync cart with database
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT ci.book_id, ci.quantity, b.*
    FROM cart_items ci
    JOIN books b ON ci.book_id = b.book_id
    WHERE ci.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Reset session cart and rebuild from database
$_SESSION['cart'] = [];
$cart_items = [];
$total = 0;

while ($item = $result->fetch_assoc()) {
    $_SESSION['cart'][$item['book_id']] = $item['quantity'];
    $subtotal = $item['price'] * $item['quantity'];
    $total += $subtotal;
    
    $cart_items[] = [
        'book' => [
            'book_id' => $item['book_id'],
            'title' => $item['title'],
            'author' => $item['author'],
            'price' => $item['price'],
            'stock_quantity' => $item['stock_quantity']
        ],
        'quantity' => $item['quantity'],
        'subtotal' => $subtotal
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Bookstore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'user_navbar.php'; ?>

    <div class="container mt-4">
        <h2>Shopping Cart</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (empty($cart_items)): ?>
            <div class="alert alert-info">Your cart is empty. <a href="books.php">Continue shopping</a></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Book</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td>
                                    <h5 class="mb-0"><?php echo htmlspecialchars($item['book']['title']); ?></h5>
                                    <small class="text-muted">By <?php echo htmlspecialchars($item['book']['author']); ?></small>
                                </td>
                                <td>$<?php echo number_format($item['book']['price'], 2); ?></td>
                                <td>
                                    <form action="update_cart.php" method="POST" class="d-flex align-items-center" style="max-width: 150px;">
                                        <input type="hidden" name="book_id" value="<?php echo $item['book']['book_id']; ?>">
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                               min="1" max="<?php echo $item['book']['stock_quantity']; ?>" 
                                               class="form-control form-control-sm">
                                        <button type="submit" class="btn btn-sm btn-outline-primary ms-2">Update</button>
                                    </form>
                                </td>
                                <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                                <td>
                                    <form action="remove_from_cart.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="book_id" value="<?php echo $item['book']['book_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td><strong>$<?php echo number_format($total, 2); ?></strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="text-end mt-3">
                <a href="books.php" class="btn btn-secondary">Continue Shopping</a>
                <form action="checkout.php" method="POST" style="display: inline;">
                    <button type="submit" class="btn btn-primary">Proceed to Checkout</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
