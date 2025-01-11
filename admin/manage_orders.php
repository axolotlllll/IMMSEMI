<?php
require_once '../auth/check_auth.php';
require_once '../auth/config.php';
requireAdmin();

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_order'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['order_status'];
    $payment_status = $_POST['payment_status'];
    
    try {
        $conn->begin_transaction();
        
        // Update order status
        $stmt = $conn->prepare("UPDATE orders SET order_status = ?, payment_status = ? WHERE order_id = ?");
        $stmt->bind_param("ssi", $new_status, $payment_status, $order_id);
        $stmt->execute();

        // If order is marked as completed and paid, add to sales
        if ($new_status == 'completed' && $payment_status == 'paid') {
            // Get order details
            $stmt = $conn->prepare("
                SELECT od.*, o.user_id, o.order_date 
                FROM order_details od
                JOIN orders o ON od.order_id = o.order_id
                WHERE od.order_id = ?
            ");
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $result = $stmt->get_result();

            // Insert each item into sales
            while ($item = $result->fetch_assoc()) {
                $stmt = $conn->prepare("
                    INSERT INTO sales (user_id, book_id, quantity, total_price, sale_date) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("iiids", 
                    $item['user_id'],
                    $item['book_id'],
                    $item['quantity'],
                    $item['subtotal'],
                    $item['order_date']
                );
                $stmt->execute();
            }
        }

        $conn->commit();
        $_SESSION['success'] = "Order status updated successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error updating order: " . $e->getMessage();
    }
    
    header("Location: manage_orders.php");
    exit();
}

// Get all orders with user and book details
$query = "
    SELECT o.*, u.username,
           COUNT(od.order_detail_id) as item_count
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    JOIN order_details od ON o.order_id = od.order_id
    GROUP BY o.order_id
    ORDER BY o.order_date DESC
";
$orders = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include 'admin_navbar.php'; ?>

    <div class="container mt-4">
        <h2>Manage Orders</h2>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $order['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></td>
                            <td><?php echo $order['item_count']; ?> items</td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $order['order_status'] == 'completed' ? 'success' : 
                                        ($order['order_status'] == 'cancelled' ? 'danger' : 'warning'); 
                                ?>">
                                    <?php echo ucfirst($order['order_status']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $order['payment_status'] == 'paid' ? 'success' : 
                                        ($order['payment_status'] == 'failed' ? 'danger' : 'warning'); 
                                ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#orderModal<?php echo $order['order_id']; ?>">
                                    Update Status
                                </button>
                            </td>
                        </tr>

                        <!-- Modal for updating order status -->
                        <div class="modal fade" id="orderModal<?php echo $order['order_id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Update Order #<?php echo $order['order_id']; ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Order Status</label>
                                                <select name="order_status" class="form-select">
                                                    <option value="pending" <?php echo $order['order_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="processing" <?php echo $order['order_status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                    <option value="completed" <?php echo $order['order_status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                    <option value="cancelled" <?php echo $order['order_status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Payment Status</label>
                                                <select name="payment_status" class="form-select">
                                                    <option value="pending" <?php echo $order['payment_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="paid" <?php echo $order['payment_status'] == 'paid' ? 'selected' : ''; ?>>Paid</option>
                                                    <option value="failed" <?php echo $order['payment_status'] == 'failed' ? 'selected' : ''; ?>>Failed</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" name="update_order" class="btn btn-primary">Update Status</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
