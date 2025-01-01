<?php
require_once '../auth/config.php';

// Check if the replied column exists
$check_column = $conn->query("SHOW COLUMNS FROM Messages LIKE 'replied'");
if ($check_column->num_rows == 0) {
    // Add the replied column if it doesn't exist
    $sql = "ALTER TABLE Messages ADD COLUMN replied TINYINT(1) NOT NULL DEFAULT 0";
    if ($conn->query($sql)) {
        echo "Successfully added 'replied' column to Messages table.";
    } else {
        echo "Error adding column: " . $conn->error;
    }
} else {
    echo "'replied' column already exists in Messages table.";
}

$conn->close();
?>
