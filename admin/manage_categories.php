<?php
require_once '../auth/check_auth.php';
require_once '../auth/config.php';
requireAdmin();

$success_message = '';
$error_message = '';

// Handle Category Actions (Create, Update, Delete)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $category_name = $conn->real_escape_string($_POST['category_name']);
                $sql = "INSERT INTO Categories (category_name) VALUES (?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $category_name);
                if ($stmt->execute()) {
                    $success_message = "Category added successfully!";
                } else {
                    $error_message = "Error adding category.";
                }
                break;

            case 'edit':
                $category_id = $_POST['category_id'];
                $category_name = $conn->real_escape_string($_POST['category_name']);
                $sql = "UPDATE Categories SET category_name = ? WHERE category_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $category_name, $category_id);
                if ($stmt->execute()) {
                    $success_message = "Category updated successfully!";
                } else {
                    $error_message = "Error updating category.";
                }
                break;

            case 'delete':
                $category_id = $_POST['category_id'];
                // Check if category has books
                $check_sql = "SELECT COUNT(*) as count FROM Books WHERE category_id = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("i", $category_id);
                $check_stmt->execute();
                $result = $check_stmt->get_result();
                $row = $result->fetch_assoc();
                
                if ($row['count'] > 0) {
                    $error_message = "Cannot delete category. It has associated books.";
                } else {
                    $sql = "DELETE FROM Categories WHERE category_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $category_id);
                    if ($stmt->execute()) {
                        $success_message = "Category deleted successfully!";
                    } else {
                        $error_message = "Error deleting category.";
                    }
                }
                break;
        }
    }
}

// Fetch all categories
$categories = $conn->query("SELECT * FROM Categories ORDER BY category_name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Bookstore Admin</title>
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
                        <h5 class="mb-0">Manage Categories</h5>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                            Add New Category
                        </button>
                    </div>
                    <div class="card-body">
                        <?php if ($success_message): ?>
                            <div class="alert alert-success"><?php echo $success_message; ?></div>
                        <?php endif; ?>
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>

                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Category Name</th>
                                    <th>Date Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($category = $categories->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $category['category_id']; ?></td>
                                        <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                                        <td><?php echo date('Y-m-d H:i:s', strtotime($category['date_created'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editCategoryModal<?php echo $category['category_id']; ?>">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteCategoryModal<?php echo $category['category_id']; ?>">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editCategoryModal<?php echo $category['category_id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Category</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="action" value="edit">
                                                        <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
                                                        <div class="mb-3">
                                                            <label for="category_name" class="form-label">Category Name</label>
                                                            <input type="text" class="form-control" name="category_name" value="<?php echo htmlspecialchars($category['category_name']); ?>" required>
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
                                    <div class="modal fade" id="deleteCategoryModal<?php echo $category['category_id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Delete Category</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to delete the category "<?php echo htmlspecialchars($category['category_name']); ?>"?</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <form method="POST">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
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

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label for="category_name" class="form-label">Category Name</label>
                            <input type="text" class="form-control" name="category_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
