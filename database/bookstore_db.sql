-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 11, 2025 at 05:40 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bookstore_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `book_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`book_id`, `category_id`, `title`, `author`, `description`, `price`, `stock_quantity`, `date_added`) VALUES
(2, 2, 'tt', 'aa', 'adsadasdsad asd sad sad sad sa dsadaasdsadasdasd asd sad sad asd sad sad asd sa', 22.00, 3, '2025-01-01 05:51:26'),
(3, 3, 'The Silent Waters', 'Brooke Hamilton', 'A gripping tale of love, loss, and redemption, set against the serene backdrop of a small coastal town', 10.00, 10, '2025-01-01 06:57:35'),
(4, 4, 'Whispers of the Wind', 'Ava Green', 'A young sorcerer must unravel the ancient mystery of a forgotten prophecy that could change the fate of the world.', 10.00, 10, '2025-01-01 06:57:55'),
(5, 13, 'The Coded Heart', ' Joshua Matthews', 'A computer hacker finds himself entangled in a global conspiracy after uncovering a coded message linked to a string of high-profile murders.', 12.00, 10, '2025-01-01 06:58:26'),
(6, 6, 'The Last Garden', 'Elena Rivera', 'In a post-apocalyptic world, the last surviving humans must fight to preserve the last remnant of Earth\\\'s once-thriving ecosystems.', 13.00, 10, '2025-01-01 06:58:58'),
(7, 7, 'Shadows on the Edge', 'Daniel Frost', 'Detective Adrian Blake must solve a decades-old cold case involving a missing person and a web of dark secrets in a quiet suburban town.', 10.00, 10, '2025-01-01 06:59:18'),
(8, 8, ' Beneath the Moonlight', 'Sarah Brooks', 'A chance meeting under the full moon leads two strangers to an unexpected romance, filled with passion, suspense, and heartbreak.', 11.00, 7, '2025-01-01 06:59:42'),
(9, 9, 'Echoes of the Past', 'Marcus West', 'A historical drama following two families whose fates intertwine across centuries, revealing secrets buried deep within their shared ancestry.', 11.00, 9, '2025-01-01 07:00:10'),
(10, 10, ' The Enchanted Compass', 'Isabella Quinn', 'A treasure hunter embarks on a journey to find a mystical artifact that could alter the course of history, battling mythical creatures and ancient curses along the way.', 11.00, 10, '2025-01-01 07:01:16'),
(11, 11, ' Journey to the Stars', 'Leo Armstrong', 'In a world where space exploration is the norm, a young astronaut embarks on a daring mission to explore a newly discovered galaxy, encountering life beyond Earth.', 10.00, 9, '2025-01-01 07:01:35'),
(12, 12, 'The Art of War Revisited', 'Richard Huang', 'A modern interpretation of Sun Tzu\\\'s The Art of War, this book applies ancient strategies to today\\\'s business, politics, and leadership challenges.', 11.00, 9, '2025-01-01 07:02:02'),
(15, 15, 'tetetete', 'etete', 'tetete', 22.00, 100, '2025-01-11 16:17:58');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `date_created`) VALUES
(2, 'cc1', '2025-01-01 05:50:34'),
(3, 'Fiction', '2025-01-01 06:55:47'),
(4, 'Fantasy', '2025-01-01 06:55:52'),
(6, 'Sci-Fi', '2025-01-01 06:56:04'),
(7, 'Mystery', '2025-01-01 06:56:10'),
(8, 'Romance', '2025-01-01 06:56:15'),
(9, 'Historical Fiction', '2025-01-01 06:56:20'),
(10, 'Adventure', '2025-01-01 06:56:25'),
(11, 'Science Fiction', '2025-01-01 06:56:30'),
(12, 'Non-Fiction', '2025-01-01 06:56:35'),
(13, 'Thriller', '2025-01-01 06:57:07'),
(15, 'tetete', '2025-01-11 16:17:34');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `date_sent` timestamp NOT NULL DEFAULT current_timestamp(),
  `replied` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `user_id`, `message`, `date_sent`, `replied`) VALUES
(1, 5, 'asdasdwa', '2025-01-01 06:01:08', 0),
(2, 7, 'yoyo', '2025-01-01 06:18:05', 0),
(3, 7, 'yyeee', '2025-01-01 07:04:41', 0),
(4, 9, 'heyy\\r\\n', '2025-01-11 08:11:05', 0);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `order_status` enum('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending',
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `shipping_address` text DEFAULT NULL,
  `payment_status` enum('pending','paid','failed') NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `order_date`, `order_status`, `total_amount`, `shipping_address`, `payment_status`, `payment_method`) VALUES
(1, 7, '2025-01-11 15:30:32', 'pending', 22.00, NULL, 'pending', NULL),
(2, 7, '2025-01-11 16:19:02', 'completed', 2684.00, NULL, 'paid', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `order_detail_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`order_detail_id`, `order_id`, `book_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, 8, 2, 11.00, 22.00),
(2, 2, 15, 122, 22.00, 2684.00);

-- --------------------------------------------------------

--
-- Table structure for table `saledetails`
--

CREATE TABLE `saledetails` (
  `detail_id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `sale_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sale_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`sale_id`, `user_id`, `book_id`, `quantity`, `total_price`, `sale_date`) VALUES
(1, 7, 2, 6, 132.00, '2025-01-01 14:49:01'),
(2, 7, 2, 1, 22.00, '2025-01-01 14:49:06'),
(3, 7, 12, 1, 11.00, '2025-01-01 15:04:49'),
(4, 7, 11, 1, 10.00, '2025-01-01 15:04:49'),
(5, 7, 9, 1, 11.00, '2025-01-01 15:09:20'),
(6, 9, 8, 1, 11.00, '2025-01-11 16:10:58'),
(7, 7, 15, 122, 2684.00, '2025-01-12 00:19:02');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` varchar(10) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `user_type`, `date_created`) VALUES
(4, 'wawa', '', 'wawa', 'admin', '2025-01-01 05:46:24'),
(7, 'wewe', 'wewe@gail.com', 'wewewewe', 'user', '2025-01-01 06:15:52'),
(8, 'adad', 'adad@adad.com', 'adadadad', 'user', '2025-01-01 07:15:30'),
(9, 'wqwq', 'wqwq@wqwq.com', 'wqwqwqwq', 'user', '2025-01-11 08:09:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`book_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`order_detail_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `saledetails`
--
ALTER TABLE `saledetails`
  ADD PRIMARY KEY (`detail_id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`sale_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `order_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `saledetails`
--
ALTER TABLE `saledetails`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`);

--
-- Constraints for table `saledetails`
--
ALTER TABLE `saledetails`
  ADD CONSTRAINT `saledetails_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`),
  ADD CONSTRAINT `saledetails_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`);

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
