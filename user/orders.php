<?php
require_once '../auth/check_auth.php';
require_once '../auth/config.php';
requireLogin();

// Get user's orders
$stmt = $conn->prepare("
    SELECT o.*, od.quantity, od.unit_price, od.subtotal, b.title, b.author
    FROM orders o
    JOIN order_details od ON o.order_id = od.order_id
    JOIN Books b ON od.book_id = b.book_id
    WHERE o.user_id = ?
    ORDER BY o.order_date DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$order_details = $stmt->get_result();

// Group orders by order_id
$orders = [];
while ($row = $order_details->fetch_assoc()) {
    if (!isset($orders[$row['order_id']])) {
        $orders[$row['order_id']] = [
            'order_id' => $row['order_id'],
            'order_date' => $row['order_date'],
            'order_status' => $row['order_status'],
            'payment_status' => $row['payment_status'],
            'total_amount' => $row['total_amount'],
            'items' => []
        ];
    }
    $orders[$row['order_id']]['items'][] = [
        'title' => $row['title'],
        'author' => $row['author'],
        'quantity' => $row['quantity'],
        'unit_price' => $row['unit_price'],
        'subtotal' => $row['subtotal']
    ];
}
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

        <?php if (empty($orders)): ?>
            <div class="alert alert-info">You haven't placed any orders yet. <a href="books.php">Start shopping</a></div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="row">
                            <div class="col">
                                <h5 class="mb-0">Order #<?php echo $order['order_id']; ?></h5>
                                <small class="text-muted">Placed on <?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></small>
                            </div>
                            <div class="col text-end">
                                <span class="badge bg-<?php echo $order['order_status'] == 'completed' ? 'success' : ($order['order_status'] == 'cancelled' ? 'danger' : 'warning'); ?>">
                                    <?php echo ucfirst($order['order_status']); ?>
                                </span>
                                <span class="badge bg-<?php echo $order['payment_status'] == 'paid' ? 'success' : ($order['payment_status'] == 'failed' ? 'danger' : 'warning'); ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Book</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order['items'] as $item): ?>
                                    <tr>
                                        <td>
                                            <h6 class="mb-0"><?php echo htmlspecialchars($item['title']); ?></h6>
                                            <small class="text-muted">By <?php echo htmlspecialchars($item['author']); ?></small>
                                        </td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>$<?php echo number_format($item['unit_price'], 2); ?></td>
                                        <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                        <td><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
