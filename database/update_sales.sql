-- Add total_price column to Sales table if it doesn't exist
ALTER TABLE Sales ADD COLUMN IF NOT EXISTS total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00;

-- Update existing sales records with correct total price
UPDATE Sales s 
JOIN Books b ON s.book_id = b.book_id 
SET s.total_price = s.quantity * b.price 
WHERE s.total_price = 0;
