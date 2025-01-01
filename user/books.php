<?php
require_once '../auth/check_auth.php';
require_once '../auth/config.php';
requireLogin();

// Get all categories for the filter
$categories = $conn->query("SELECT * FROM Categories ORDER BY category_name");

// Handle search and filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Build the query based on search and filter
$query = "SELECT b.*, c.category_name 
          FROM Books b 
          JOIN Categories c ON b.category_id = c.category_id 
          WHERE 1=1";

if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $query .= " AND (b.title LIKE '%$search%' OR b.author LIKE '%$search%')";
}

if ($category_id > 0) {
    $query .= " AND b.category_id = $category_id";
}

$query .= " ORDER BY b.title";
$books = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Books - Bookstore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .book-card {
            transition: transform 0.2s;
            height: 100%;
            cursor: pointer;
        }
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .out-of-stock {
            opacity: 0.7;
        }
        .out-of-stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1;
        }
        .back-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }
        .book-description {
            white-space: pre-line;
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'user_navbar.php'; ?>

    <div class="container mt-4">
        <!-- Search and Filter Section -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Search by title or author..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" name="category" onchange="this.form.submit()">
                            <option value="0">All Categories</option>
                            <?php while ($category = $categories->fetch_assoc()): ?>
                                <option value="<?php echo $category['category_id']; ?>"
                                        <?php echo $category_id == $category['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <?php if (!empty($search) || $category_id > 0): ?>
                        <div class="col-md-2">
                            <a href="books.php" class="btn btn-outline-secondary w-100">Clear Filters</a>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Books Grid -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if ($books->num_rows > 0): ?>
                <?php while ($book = $books->fetch_assoc()): ?>
                    <div class="col">
                        <div class="card book-card <?php echo $book['stock_quantity'] <= 0 ? 'out-of-stock' : ''; ?>" 
                             onclick="showBookDetails(<?php echo htmlspecialchars(json_encode($book)); ?>)">
                            <?php if ($book['stock_quantity'] <= 0): ?>
                                <div class="out-of-stock-badge">
                                    <span class="badge bg-danger">Out of Stock</span>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                                <h6 class="card-subtitle mb-2 text-muted">By <?php echo htmlspecialchars($book['author']); ?></h6>
                                <p class="card-text">
                                    <small class="text-muted"><?php echo htmlspecialchars($book['category_name']); ?></small><br>
                                    <strong class="text-primary">₱<?php echo number_format($book['price'], 2); ?></strong>
                                </p>
                                <p class="card-text"><?php echo htmlspecialchars(substr($book['description'], 0, 100)) . '...'; ?></p>
                                
                                <?php if ($book['stock_quantity'] > 0): ?>
                                    <form action="add_to_cart.php" method="POST" class="d-flex gap-2" onclick="event.stopPropagation();">
                                        <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                        <input type="number" name="quantity" value="1" min="1" 
                                               max="<?php echo $book['stock_quantity']; ?>" 
                                               class="form-control" style="width: 100px;">
                                        <button type="submit" class="btn btn-primary flex-grow-1">
                                            <i class="bi bi-cart-plus"></i> Add to Cart
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-secondary w-100" disabled>
                                        <i class="bi bi-x-circle"></i> Out of Stock
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        No books found matching your criteria. Try adjusting your search or filters.
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Back Button -->
        <a href="javascript:history.back()" class="btn btn-primary rounded-circle back-button">
            <i class="bi bi-arrow-left"></i>
        </a>
    </div>

    <!-- Book Details Modal -->
    <div class="modal fade" id="bookDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="text-muted author"></h6>
                            <p class="category mb-2"></p>
                            <div class="description book-description mb-3"></div>
                            <div class="stock-info mb-3"></div>
                            <div class="price h4 text-primary mb-3"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="cart-form"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showBookDetails(book) {
            const modal = new bootstrap.Modal(document.getElementById('bookDetailsModal'));
            const modalTitle = document.querySelector('#bookDetailsModal .modal-title');
            const modalAuthor = document.querySelector('#bookDetailsModal .author');
            const modalCategory = document.querySelector('#bookDetailsModal .category');
            const modalDescription = document.querySelector('#bookDetailsModal .description');
            const modalStockInfo = document.querySelector('#bookDetailsModal .stock-info');
            const modalPrice = document.querySelector('#bookDetailsModal .price');
            const modalCartForm = document.querySelector('#bookDetailsModal .cart-form');

            modalTitle.textContent = book.title;
            modalAuthor.textContent = 'By ' + book.author;
            modalCategory.innerHTML = '<small class="text-muted">Category: ' + book.category_name + '</small>';
            modalDescription.textContent = book.description;
            modalPrice.textContent = '₱' + parseFloat(book.price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

            if (book.stock_quantity > 0) {
                modalStockInfo.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> In Stock</span> (' + book.stock_quantity + ' available)';
                modalCartForm.innerHTML = `
                    <form action="add_to_cart.php" method="POST" class="d-flex flex-column gap-2">
                        <input type="hidden" name="book_id" value="${book.book_id}">
                        <input type="number" name="quantity" value="1" min="1" max="${book.stock_quantity}" 
                               class="form-control" style="width: 100%;">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-cart-plus"></i> Add to Cart
                        </button>
                    </form>
                `;
            } else {
                modalStockInfo.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle"></i> Out of Stock</span>';
                modalCartForm.innerHTML = `
                    <button class="btn btn-secondary w-100" disabled>
                        <i class="bi bi-x-circle"></i> Out of Stock
                    </button>
                `;
            }

            modal.show();
        }
    </script>
</body>
</html>
