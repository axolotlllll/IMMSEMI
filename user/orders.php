<?php
require_once '../auth/check_auth.php';
require_once '../auth/config.php';
requireLogin();

// Get user's orders
$stmt = $conn->prepare("
    SELECT s.*, b.title, b.author, b.price
    FROM Sales s
    JOIN Books b ON s.book_id = b.book_id
    WHERE s.user_id = ?
    ORDER BY s.sale_date DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$orders = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Bookstore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'user_navbar.php'; ?>

    <div class="container mt-4">
        <h2>My Orders</h2>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if ($orders->num_rows == 0): ?>
            <div class="alert alert-info">You haven't placed any orders yet. <a href="books.php">Start shopping</a></div>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order Date</th>
                                    <th>Book</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($order = $orders->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('M d, Y H:i', strtotime($order['sale_date'])); ?></td>
                                    <td>
                                        <h6 class="mb-0"><?php echo htmlspecialchars($order['title']); ?></h6>
                                        <small class="text-muted">By <?php echo htmlspecialchars($order['author']); ?></small>
                                    </td>
                                    <td><?php echo $order['quantity']; ?></td>
                                    <td>$<?php echo number_format($order['price'], 2); ?></td>
                                    <td>$<?php echo number_format($order['price'] * $order['quantity'], 2); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
