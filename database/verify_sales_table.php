<?php
require_once '../auth/config.php';

// Check if Sales table exists
$table_exists = $conn->query("SHOW TABLES LIKE 'Sales'");
if ($table_exists->num_rows == 0) {
    echo "Sales table does not exist. Creating it now...<br>";
    
    // Create Sales table
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
        echo "Successfully created Sales table.<br>";
    } else {
        echo "Error creating Sales table: " . $conn->error . "<br>";
    }
} else {
    echo "Sales table exists. Checking structure...<br>";
    
    // Check if total_price column exists
    $check_column = $conn->query("SHOW COLUMNS FROM Sales LIKE 'total_price'");
    if ($check_column->num_rows == 0) {
        echo "Adding total_price column...<br>";
        
        // Add total_price column
        $add_column_sql = "ALTER TABLE Sales ADD COLUMN total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00";
        if ($conn->query($add_column_sql)) {
            echo "Successfully added total_price column.<br>";
            
            // Update existing records
            $update_sql = "UPDATE Sales s 
                          JOIN Books b ON s.book_id = b.book_id 
                          SET s.total_price = s.quantity * b.price 
                          WHERE s.total_price = 0";
            if ($conn->query($update_sql)) {
                echo "Successfully updated existing sales records with total prices.<br>";
            } else {
                echo "Error updating sales records: " . $conn->error . "<br>";
            }
        } else {
            echo "Error adding total_price column: " . $conn->error . "<br>";
        }
    } else {
        echo "total_price column already exists.<br>";
    }
}

// Display current table structure
$result = $conn->query("DESCRIBE Sales");
echo "<br>Current Sales table structure:<br>";
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "<br>";
}

$conn->close();
?>
