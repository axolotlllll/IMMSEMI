<?php
require_once '../auth/check_auth.php';
require_once '../auth/config.php';
requireAdmin();

// Get filter parameters
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'daily';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] . ' 00:00:00' : date('Y-m-d 00:00:00', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] . ' 23:59:59' : date('Y-m-d 23:59:59');

// Build the query based on report type
switch ($report_type) {
    case 'yearly':
        $group_by = "YEAR(s.sale_date)";
        $date_format = "%Y";
        break;
    case 'monthly':
        $group_by = "YEAR(s.sale_date), MONTH(s.sale_date)";
        $date_format = "%Y-%m";
        break;
    default: // daily
        $group_by = "DATE(s.sale_date)";
        $date_format = "%Y-%m-%d";
        break;
}

// Detailed sales report query
$sql = "SELECT 
            DATE_FORMAT(s.sale_date, '$date_format') as sale_period,
            COUNT(DISTINCT s.sale_id) as num_transactions,
            SUM(s.quantity) as total_quantity,
            SUM(s.total_price) as total_revenue
        FROM Sales s
        WHERE s.sale_date BETWEEN ? AND ?
        GROUP BY DATE_FORMAT(s.sale_date, '$date_format')
        ORDER BY sale_period DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$sales = $stmt->get_result();

// Calculate summary statistics
$summary_sql = "SELECT 
                    COUNT(DISTINCT sale_id) as total_transactions,
                    SUM(quantity) as total_books_sold,
                    SUM(total_price) as total_revenue,
                    AVG(total_price) as avg_transaction_value
                FROM Sales
                WHERE sale_date BETWEEN ? AND ?";

$summary_stmt = $conn->prepare($summary_sql);
$summary_stmt->bind_param("ss", $start_date, $end_date);
$summary_stmt->execute();
$summary = $summary_stmt->get_result()->fetch_assoc();

// Top selling books
$top_books_sql = "SELECT 
                    b.title,
                    b.author,
                    SUM(s.quantity) as total_sold,
                    SUM(s.total_price) as revenue
                FROM Sales s
                JOIN Books b ON s.book_id = b.book_id
                WHERE s.sale_date BETWEEN ? AND ?
                GROUP BY b.book_id, b.title, b.author
                ORDER BY total_sold DESC
                LIMIT 5";

$top_books_stmt = $conn->prepare($top_books_sql);
$top_books_stmt->bind_param("ss", $start_date, $end_date);
$top_books_stmt->execute();
$top_books = $top_books_stmt->get_result();

// Sales by category
$category_sql = "SELECT 
                    c.category_name,
                    SUM(s.quantity) as total_sold,
                    SUM(s.total_price) as revenue
                FROM Sales s
                JOIN Books b ON s.book_id = b.book_id
                JOIN Categories c ON b.category_id = c.category_id
                WHERE s.sale_date BETWEEN ? AND ?
                GROUP BY c.category_id, c.category_name
                ORDER BY revenue DESC";

$category_stmt = $conn->prepare($category_sql);
$category_stmt->bind_param("ss", $start_date, $end_date);
$category_stmt->execute();
$category_sales = $category_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Reports - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            .card {
                border: none !important;
            }
        }
        .summary-card {
            transition: all 0.3s ease;
        }
        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include 'admin_navbar.php'; ?>

    <div class="container mt-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Sales Reports</h5>
                <button onclick="window.print()" class="btn btn-primary no-print">
                    <i class="bi bi-printer"></i> Print Report
                </button>
            </div>
            <div class="card-body">
                <!-- Report Filters -->
                <form method="GET" class="row mb-4 no-print">
                    <div class="col-md-3">
                        <label class="form-label">Report Type</label>
                        <select name="report_type" class="form-select">
                            <option value="daily" <?php echo $report_type == 'daily' ? 'selected' : ''; ?>>Daily</option>
                            <option value="monthly" <?php echo $report_type == 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                            <option value="yearly" <?php echo $report_type == 'yearly' ? 'selected' : ''; ?>>Yearly</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="<?php echo substr($start_date, 0, 10); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="<?php echo substr($end_date, 0, 10); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary d-block w-100">Generate Report</button>
                    </div>
                </form>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card summary-card bg-primary text-white">
                            <div class="card-body">
                                <h6 class="card-title">Total Transactions</h6>
                                <h3 class="mb-0"><?php echo number_format($summary['total_transactions']); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card summary-card bg-success text-white">
                            <div class="card-body">
                                <h6 class="card-title">Total Books Sold</h6>
                                <h3 class="mb-0"><?php echo number_format($summary['total_books_sold']); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card summary-card bg-info text-white">
                            <div class="card-body">
                                <h6 class="card-title">Total Revenue</h6>
                                <h3 class="mb-0">₱<?php echo number_format($summary['total_revenue'], 2); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card summary-card bg-warning text-white">
                            <div class="card-body">
                                <h6 class="card-title">Avg. Transaction Value</h6>
                                <h3 class="mb-0">₱<?php echo number_format($summary['avg_transaction_value'], 2); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sales Over Time -->
                <div class="table-responsive mb-4">
                    <h5>Sales Over Time</h5>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th>Transactions</th>
                                <th>Books Sold</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $sales->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['sale_period']; ?></td>
                                <td><?php echo number_format($row['num_transactions']); ?></td>
                                <td><?php echo number_format($row['total_quantity']); ?></td>
                                <td>₱<?php echo number_format($row['total_revenue'], 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Top Books and Category Analysis -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <h5>Top Selling Books</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Copies Sold</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($book = $top_books->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                                        <td><?php echo number_format($book['total_sold']); ?></td>
                                        <td>₱<?php echo number_format($book['revenue'], 2); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <h5>Sales by Category</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Books Sold</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($category = $category_sales->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                                        <td><?php echo number_format($category['total_sold']); ?></td>
                                        <td>₱<?php echo number_format($category['revenue'], 2); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
