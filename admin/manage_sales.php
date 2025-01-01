<?php
require_once '../auth/check_auth.php';
require_once '../auth/config.php';
requireAdmin();

// Get filter parameters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Base query
$sql = "SELECT 
            s.sale_id,
            s.quantity,
            s.total_price,
            s.sale_date,
            b.title,
            b.author,
            u.username
        FROM Sales s
        JOIN Books b ON s.book_id = b.book_id
        JOIN Users u ON s.user_id = u.user_id
        WHERE s.sale_date BETWEEN ? AND ?";

$params = [$start_date . ' 00:00:00', $end_date . ' 23:59:59'];
$types = "ss";

// Add search condition if search term is provided
if (!empty($search)) {
    $sql .= " AND (b.title LIKE ? OR b.author LIKE ? OR u.username LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
    $types .= "sss";
}

$sql .= " ORDER BY s.sale_date DESC";

// Prepare and execute the query
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Calculate totals
$total_sql = "SELECT 
                COUNT(*) as total_sales,
                SUM(s.quantity) as total_items,
                SUM(s.total_price) as total_revenue
            FROM Sales s
            WHERE s.sale_date BETWEEN ? AND ?";

$total_stmt = $conn->prepare($total_sql);
$total_stmt->bind_param("ss", $params[0], $params[1]);
$total_stmt->execute();
$totals = $total_stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Sales - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include 'admin_navbar.php'; ?>

    <div class="container mt-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Manage Sales</h5>
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="bi bi-printer"></i> Print Report
                </button>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <form method="GET" class="row mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by title, author, or username" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary d-block w-100">Apply Filters</button>
                    </div>
                </form>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h6 class="card-title">Total Sales</h6>
                                <h3 class="mb-0"><?php echo number_format($totals['total_sales']); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6 class="card-title">Total Items Sold</h6>
                                <h3 class="mb-0"><?php echo number_format($totals['total_items']); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h6 class="card-title">Total Revenue</h6>
                                <h3 class="mb-0">₱<?php echo number_format($totals['total_revenue'], 2); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sales Table -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Sale ID</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Book Title</th>
                                <th>Author</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['sale_id']; ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($row['sale_date'])); ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['author']); ?></td>
                                <td><?php echo number_format($row['quantity']); ?></td>
                                <td>₱<?php echo number_format($row['total_price'], 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
