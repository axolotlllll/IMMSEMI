<?php
require_once '../auth/check_auth.php';
require_once '../auth/config.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $reply = $conn->real_escape_string($_POST['reply']);
    $original_message_id = $_POST['original_message_id'];

    // Insert the reply as a new message from admin
    $sql = "INSERT INTO Messages (user_id, message, date_sent) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $_SESSION['user_id'], $reply);
    
    if ($stmt->execute()) {
        // Update the original message to mark it as replied
        $update_sql = "UPDATE Messages SET replied = 1 WHERE message_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $original_message_id);
        $update_stmt->execute();

        header("Location: messages.php");
        exit();
    } else {
        echo "Error sending reply.";
    }
}
?>
