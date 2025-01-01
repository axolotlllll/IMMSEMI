<?php
require_once '../auth/check_auth.php';
require_once '../auth/config.php';
requireAdmin();

// Get total counts for dashboard
$total_books = $conn->query("SELECT COUNT(*) as count FROM Books")->fetch_assoc()['count'];
$total_users = $conn->query("SELECT COUNT(*) as count FROM Users WHERE user_type = 'user'")->fetch_assoc()['count'];
$total_categories = $conn->query("SELECT COUNT(*) as count FROM Categories")->fetch_assoc()['count'];
$total_sales = $conn->query("SELECT COUNT(*) as count FROM Sales")->fetch_assoc()['count'];

// Get recent sales
$recent_sales = $conn->query("
    SELECT s.sale_id, s.quantity, s.total_price, s.sale_date, b.title, u.username
    FROM Sales s
    JOIN Books b ON s.book_id = b.book_id
    JOIN Users u ON s.user_id = u.user_id
    ORDER BY s.sale_date DESC
    LIMIT 5
");

// Get low stock books
$low_stock_books = $conn->query("
    SELECT *
    FROM Books
    WHERE stock_quantity < 10
    ORDER BY stock_quantity ASC
    LIMIT 5
");

// Get latest registered users
$latest_users = $conn->query("
    SELECT user_id, username, email, date_created
    FROM Users
    WHERE user_type = 'user'
    ORDER BY date_created DESC
    LIMIT 5
");

// Get unread messages count - safely check if replied column exists
$unread_messages = 0;
$check_column = $conn->query("SHOW COLUMNS FROM Messages LIKE 'replied'");
if ($check_column->num_rows > 0) {
    $result = $conn->query("SELECT COUNT(*) as count FROM Messages WHERE replied = 0");
    $unread_messages = $result->fetch_assoc()['count'];
} else {
    $result = $conn->query("SELECT COUNT(*) as count FROM Messages");
    $unread_messages = $result->fetch_assoc()['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Bookstore</title>
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
    <?php include 'admin_navbar.php'; ?>

    <div class="container mt-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h2 class="card-title">Welcome, Admin!</h2>
                        <p class="card-text">Manage your bookstore system from this dashboard.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card feature-card text-white bg-primary h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-book display-4 mb-2"></i>
                        <h5 class="card-title">Total Books</h5>
                        <p class="card-text display-6"><?php echo $total_books; ?></p>
                        <a href="manage_books.php" class="btn btn-light">Manage Books</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card feature-card text-white bg-success h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-people display-4 mb-2"></i>
                        <h5 class="card-title">Total Users</h5>
                        <p class="card-text display-6"><?php echo $total_users; ?></p>
                        <a href="#" class="btn btn-light">View Users</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card feature-card text-white bg-info h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-grid display-4 mb-2"></i>
                        <h5 class="card-title">Categories</h5>
                        <p class="card-text display-6"><?php echo $total_categories; ?></p>
                        <a href="manage_categories.php" class="btn btn-light">Manage Categories</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card feature-card text-white bg-warning h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-cart-check display-4 mb-2"></i>
                        <h5 class="card-title">Total Sales</h5>
                        <p class="card-text display-6"><?php echo $total_sales; ?></p>
                        <a href="manage_sales.php" class="btn btn-light">View Sales</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-plus-circle display-4 text-primary mb-3"></i>
                        <h5 class="card-title">Add New Book</h5>
                        <a href="manage_books.php" class="btn btn-outline-primary">Add Book</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-folder-plus display-4 text-success mb-3"></i>
                        <h5 class="card-title">Add Category</h5>
                        <a href="manage_categories.php" class="btn btn-outline-success">Add Category</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-graph-up display-4 text-info mb-3"></i>
                        <h5 class="card-title">Sales Report</h5>
                        <a href="sales_reports.php" class="btn btn-outline-info">View Reports</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-envelope display-4 text-warning mb-3"></i>
                        <h5 class="card-title">Messages</h5>
                        <?php if ($unread_messages > 0): ?>
                            <span class="badge bg-danger mb-2"><?php echo $unread_messages; ?> unread</span>
                        <?php endif; ?>
                        <br>
                        <a href="messages.php" class="btn btn-outline-warning">View Messages</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Sales -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Sales</h5>
                        <a href="manage_sales.php" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Book</th>
                                        <th>Customer</th>
                                        <th>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($sale = $recent_sales->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($sale['sale_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($sale['title']); ?></td>
                                        <td><?php echo htmlspecialchars($sale['username']); ?></td>
                                        <td><?php echo $sale['quantity']; ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Low Stock Alert -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Low Stock Alert</h5>
                        <a href="manage_books.php" class="btn btn-sm btn-primary">Manage Stock</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Book</th>
                                        <th>Stock</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($book = $low_stock_books->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $book['stock_quantity'] == 0 ? 'danger' : 'warning'; ?>">
                                                <?php echo $book['stock_quantity']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="manage_books.php" class="btn btn-sm btn-primary">Update Stock</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
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
