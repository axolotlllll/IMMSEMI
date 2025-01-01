<?php
require_once '../auth/check_auth.php';
require_once '../auth/config.php';
requireAdmin();

// Get messages with user information
$sql = "SELECT m.*, u.username 
        FROM Messages m 
        JOIN Users u ON m.user_id = u.user_id 
        ORDER BY m.date_sent DESC";
$messages = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Bookstore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include 'admin_navbar.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">User Messages</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>User</th>
                                        <th>Message</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($message = $messages->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo date('Y-m-d H:i:s', strtotime($message['date_sent'])); ?></td>
                                            <td><?php echo htmlspecialchars($message['username']); ?></td>
                                            <td><?php echo nl2br(htmlspecialchars($message['message'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" 
                                                        data-bs-target="#replyModal<?php echo $message['message_id']; ?>">
                                                    <i class="bi bi-reply"></i> Reply
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Reply Modal -->
                                        <div class="modal fade" id="replyModal<?php echo $message['message_id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Reply to <?php echo htmlspecialchars($message['username']); ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST" action="send_reply.php">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="user_id" value="<?php echo $message['user_id']; ?>">
                                                            <input type="hidden" name="original_message_id" value="<?php echo $message['message_id']; ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Original Message:</label>
                                                                <div class="border rounded p-2 bg-light">
                                                                    <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label for="reply" class="form-label">Your Reply:</label>
                                                                <textarea class="form-control" name="reply" rows="4" required></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-primary">Send Reply</button>
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
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
