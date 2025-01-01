<?php
require_once '../auth/config.php';

// Add total_price column if it doesn't exist
$check_column = $conn->query("SHOW COLUMNS FROM Sales LIKE 'total_price'");
if ($check_column->num_rows == 0) {
    $sql = "ALTER TABLE Sales ADD COLUMN total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00";
    if ($conn->query($sql)) {
        echo "Successfully added 'total_price' column to Sales table.<br>";
        
        // Update existing sales records
        $update_sql = "UPDATE Sales s 
                      JOIN Books b ON s.book_id = b.book_id 
                      SET s.total_price = s.quantity * b.price 
                      WHERE s.total_price = 0";
        if ($conn->query($update_sql)) {
            echo "Successfully updated existing sales records with total prices.";
        } else {
            echo "Error updating sales records: " . $conn->error;
        }
    } else {
        echo "Error adding column: " . $conn->error;
    }
} else {
    echo "'total_price' column already exists in Sales table.";
}

$conn->close();
?>
