<?php
require_once '../auth/check_auth.php';
require_once '../auth/config.php';
requireLogin();

// Get recent books
$recent_books = $conn->query("
    SELECT b.*, c.category_name 
    FROM Books b 
    JOIN Categories c ON b.category_id = c.category_id 
    WHERE b.stock_quantity > 0 
    ORDER BY b.date_added DESC 
    LIMIT 6
");

// Get user's recent orders
$stmt = $conn->prepare("
    SELECT s.*, b.title, b.author, b.price
    FROM Sales s
    JOIN Books b ON s.book_id = b.book_id
    WHERE s.user_id = ?
    ORDER BY s.sale_date DESC
    LIMIT 5
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$recent_orders = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Bookstore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .feature-card {
            transition: transform 0.2s;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .back-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'user_navbar.php'; ?>

    <div class="container mt-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h2 class="card-title">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
                        <p class="card-text">Explore our collection of books and find your next favorite read.</p>
                        <a href="books.php" class="btn btn-light">Browse Books</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-book display-4 text-primary mb-3"></i>
                        <h5 class="card-title">Browse Books</h5>
                        <p class="card-text">Explore our vast collection of books across various categories.</p>
                        <a href="books.php" class="btn btn-outline-primary">View Books</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-cart display-4 text-success mb-3"></i>
                        <h5 class="card-title">Shopping Cart</h5>
                        <p class="card-text">View and manage items in your shopping cart.</p>
                        <a href="cart.php" class="btn btn-outline-success">View Cart</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-clock-history display-4 text-info mb-3"></i>
                        <h5 class="card-title">Order History</h5>
                        <p class="card-text">Track and view your previous orders.</p>
                        <a href="orders.php" class="btn btn-outline-info">View Orders</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Books -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recently Added Books</h5>
                <a href="books.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <?php while ($book = $recent_books->fetch_assoc()): ?>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h6>
                                <p class="card-text">
                                    <small class="text-muted">By <?php echo htmlspecialchars($book['author']); ?></small><br>
                                    <small class="text-muted">Category: <?php echo htmlspecialchars($book['category_name']); ?></small>
                                </p>
                                <p class="card-text"><strong>$<?php echo number_format($book['price'], 2); ?></strong></p>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="books.php" class="btn btn-sm btn-primary w-100">View Details</a>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Orders</h5>
                <a href="orders.php" class="btn btn-sm btn-primary">View All Orders</a>
            </div>
            <div class="card-body">
                <?php if ($recent_orders->num_rows == 0): ?>
                    <p class="text-muted">No orders yet. Start shopping!</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Book</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($order['sale_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($order['title']); ?></td>
                                    <td><?php echo $order['quantity']; ?></td>
                                    <td>$<?php echo number_format($order['price'] * $order['quantity'], 2); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Back Button -->
        <a href="javascript:history.back()" class="btn btn-primary rounded-circle back-button">
            <i class="bi bi-arrow-left"></i>
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
