<?php
require_once '../auth/config.php';

// Drop existing Sales table if it exists
$conn->query("DROP TABLE IF EXISTS Sales");

// Create new Sales table with all required columns
$create_table_sql = "CREATE TABLE Sales (
    sale_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    sale_date DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (book_id) REFERENCES Books(book_id)
)";

if ($conn->query($create_table_sql)) {
    echo "Successfully created Sales table with all required columns.";
} else {
    echo "Error creating Sales table: " . $conn->error;
}

$conn->close();
?>
