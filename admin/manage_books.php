<?php
require_once '../auth/check_auth.php';
require_once '../auth/config.php';
requireAdmin();

$success_message = '';
$error_message = '';

// Handle Book Actions (Create, Update, Delete)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $title = $conn->real_escape_string($_POST['title']);
                $author = $conn->real_escape_string($_POST['author']);
                $category_id = $_POST['category_id'];
                $description = $conn->real_escape_string($_POST['description']);
                $price = $_POST['price'];
                $stock_quantity = $_POST['stock_quantity'];

                $sql = "INSERT INTO Books (title, author, category_id, description, price, stock_quantity) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssisdi", $title, $author, $category_id, $description, $price, $stock_quantity);
                
                if ($stmt->execute()) {
                    $success_message = "Book added successfully!";
                } else {
                    $error_message = "Error adding book.";
                }
                break;

            case 'edit':
                $book_id = $_POST['book_id'];
                $title = $conn->real_escape_string($_POST['title']);
                $author = $conn->real_escape_string($_POST['author']);
                $category_id = $_POST['category_id'];
                $description = $conn->real_escape_string($_POST['description']);
                $price = $_POST['price'];
                $stock_quantity = $_POST['stock_quantity'];

                $sql = "UPDATE Books SET title = ?, author = ?, category_id = ?, 
                        description = ?, price = ?, stock_quantity = ? WHERE book_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssisdii", $title, $author, $category_id, $description, $price, $stock_quantity, $book_id);
                
                if ($stmt->execute()) {
                    $success_message = "Book updated successfully!";
                } else {
                    $error_message = "Error updating book.";
                }
                break;

            case 'delete':
                $book_id = $_POST['book_id'];
                // Check if book has any sales
                $check_sql = "SELECT COUNT(*) as count FROM Sales WHERE book_id = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("i", $book_id);
                $check_stmt->execute();
                $result = $check_stmt->get_result();
                $row = $result->fetch_assoc();
                
                if ($row['count'] > 0) {
                    $error_message = "Cannot delete book. It has associated sales records.";
                } else {
                    $sql = "DELETE FROM Books WHERE book_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $book_id);
                    if ($stmt->execute()) {
                        $success_message = "Book deleted successfully!";
                    } else {
                        $error_message = "Error deleting book.";
                    }
                }
                break;
        }
    }
}

// Get filter parameters
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$stock_filter = isset($_GET['stock']) ? $_GET['stock'] : '';

// Build the query
$query = "SELECT b.*, c.category_name 
          FROM Books b 
          LEFT JOIN Categories c ON b.category_id = c.category_id 
          WHERE 1=1";

if ($category_filter) {
    $query .= " AND b.category_id = " . intval($category_filter);
}

if ($stock_filter === 'out') {
    $query .= " AND b.stock_quantity = 0";
} elseif ($stock_filter === 'low') {
    $query .= " AND b.stock_quantity < 10 AND b.stock_quantity > 0";
}

$query .= " ORDER BY b.title";

// Fetch books
$books = $conn->query($query);

// Fetch categories for dropdown
$categories = $conn->query("SELECT * FROM Categories ORDER BY category_name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books - Bookstore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include 'admin_navbar.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Manage Books</h5>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBookModal">
                            Add New Book
                        </button>
                    </div>
                    <div class="card-body">
                        <?php if ($success_message): ?>
                            <div class="alert alert-success"><?php echo $success_message; ?></div>
                        <?php endif; ?>
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>

                        <!-- Filters -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <form method="GET" class="d-flex gap-2">
                                    <select name="category" class="form-select">
                                        <option value="">All Categories</option>
                                        <?php while ($category = $categories->fetch_assoc()): ?>
                                            <option value="<?php echo $category['category_id']; ?>" 
                                                    <?php echo $category_filter == $category['category_id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['category_name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                    <select name="stock" class="form-select">
                                        <option value="">All Stock Levels</option>
                                        <option value="out" <?php echo $stock_filter === 'out' ? 'selected' : ''; ?>>Out of Stock</option>
                                        <option value="low" <?php echo $stock_filter === 'low' ? 'selected' : ''; ?>>Low Stock</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                </form>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($book = $books->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $book['book_id']; ?></td>
                                            <td><?php echo htmlspecialchars($book['title']); ?></td>
                                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                                            <td><?php echo htmlspecialchars($book['category_name']); ?></td>
                                            <td>$<?php echo number_format($book['price'], 2); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $book['stock_quantity'] == 0 ? 'danger' : ($book['stock_quantity'] < 10 ? 'warning' : 'success'); ?>">
                                                    <?php echo $book['stock_quantity']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewBookModal<?php echo $book['book_id']; ?>">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editBookModal<?php echo $book['book_id']; ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteBookModal<?php echo $book['book_id']; ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- View Modal -->
                                        <div class="modal fade" id="viewBookModal<?php echo $book['book_id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Book Details</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <dl class="row">
                                                            <dt class="col-sm-3">Title</dt>
                                                            <dd class="col-sm-9"><?php echo htmlspecialchars($book['title']); ?></dd>

                                                            <dt class="col-sm-3">Author</dt>
                                                            <dd class="col-sm-9"><?php echo htmlspecialchars($book['author']); ?></dd>

                                                            <dt class="col-sm-3">Category</dt>
                                                            <dd class="col-sm-9"><?php echo htmlspecialchars($book['category_name']); ?></dd>

                                                            <dt class="col-sm-3">Price</dt>
                                                            <dd class="col-sm-9">$<?php echo number_format($book['price'], 2); ?></dd>

                                                            <dt class="col-sm-3">Stock</dt>
                                                            <dd class="col-sm-9"><?php echo $book['stock_quantity']; ?></dd>

                                                            <dt class="col-sm-3">Description</dt>
                                                            <dd class="col-sm-9"><?php echo nl2br(htmlspecialchars($book['description'])); ?></dd>

                                                            <dt class="col-sm-3">Added</dt>
                                                            <dd class="col-sm-9"><?php echo date('Y-m-d H:i:s', strtotime($book['date_added'])); ?></dd>
                                                        </dl>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editBookModal<?php echo $book['book_id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Book</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="action" value="edit">
                                                            <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Title</label>
                                                                <input type="text" class="form-control" name="title" 
                                                                       value="<?php echo htmlspecialchars($book['title']); ?>" required>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Author</label>
                                                                <input type="text" class="form-control" name="author" 
                                                                       value="<?php echo htmlspecialchars($book['author']); ?>" required>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Category</label>
                                                                <select class="form-select" name="category_id" required>
                                                                    <?php 
                                                                    $categories->data_seek(0);
                                                                    while ($category = $categories->fetch_assoc()): 
                                                                    ?>
                                                                        <option value="<?php echo $category['category_id']; ?>" 
                                                                                <?php echo $book['category_id'] == $category['category_id'] ? 'selected' : ''; ?>>
                                                                            <?php echo htmlspecialchars($category['category_name']); ?>
                                                                        </option>
                                                                    <?php endwhile; ?>
                                                                </select>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Description</label>
                                                                <textarea class="form-control" name="description" rows="3" required><?php echo htmlspecialchars($book['description']); ?></textarea>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Price</label>
                                                                <input type="number" class="form-control" name="price" 
                                                                       value="<?php echo $book['price']; ?>" step="0.01" min="0" required>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Stock Quantity</label>
                                                                <input type="number" class="form-control" name="stock_quantity" 
                                                                       value="<?php echo $book['stock_quantity']; ?>" min="0" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="deleteBookModal<?php echo $book['book_id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Delete Book</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Are you sure you want to delete "<?php echo htmlspecialchars($book['title']); ?>"?</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <form method="POST">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger">Delete</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Book Modal -->
    <div class="modal fade" id="addBookModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Author</label>
                            <input type="text" class="form-control" name="author" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php 
                                $categories->data_seek(0);
                                while ($category = $categories->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $category['category_id']; ?>">
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" class="form-control" name="price" step="0.01" min="0" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Stock Quantity</label>
                            <input type="number" class="form-control" name="stock_quantity" min="0" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Book</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
