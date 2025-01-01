<?php
require_once '../auth/config.php';

// Drop the existing Sales table
$conn->query("DROP TABLE IF EXISTS Sales");

// Create the Sales table with proper structure
$create_table_sql = "CREATE TABLE Sales (
    sale_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    sale_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (book_id) REFERENCES Books(book_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($create_table_sql)) {
    echo "Successfully recreated Sales table with proper structure.<br>";
    
    // Show the current table structure
    $result = $conn->query("DESCRIBE Sales");
    echo "<br>Current Sales table structure:<br>";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "<br>";
    }
} else {
    echo "Error creating Sales table: " . $conn->error;
}

$conn->close();
?>
