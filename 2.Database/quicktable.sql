-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 18, 2025 at 05:43 AM
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
-- Database: `quicktable`
--

-- --------------------------------------------------------

--
-- Table structure for table `appearance`
--

CREATE TABLE `appearance` (
  `id` int(11) NOT NULL,
  `sr_no` int(11) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `Department` varchar(255) DEFAULT NULL,
  `status` enum('Present','Absent') NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appearance`
--

INSERT INTO `appearance` (`id`, `sr_no`, `full_name`, `position`, `Department`, `status`, `date`) VALUES
(11, 2, 'keyur', 'owner', 'main', 'Present', '2025-03-17'),
(12, 1, 'udaybediya', 'owner', 'main', 'Present', '2025-03-17');

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `sr_no` int(11) NOT NULL,
  `Username` varchar(50) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `position` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Gender` enum('Male','Female') DEFAULT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Department` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`sr_no`, `Username`, `Password`, `full_name`, `position`, `address`, `date_of_birth`, `Email`, `Gender`, `Phone`, `Department`) VALUES
(1, 'Uk_18', '123', 'udaybediya', 'owner', 'rajkot', '2025-03-16', 'udaybediya19@gmail.com', 'Male', '8866473303', 'main'),
(2, 'Ak_18', '123', 'keyur', 'owner', 'rajkot', '2005-01-01', 'udaybediya19@gmail.com', 'Male', '8866473303', 'main');

-- --------------------------------------------------------

--
-- Table structure for table `item`
--

CREATE TABLE `item` (
  `sr_no` int(11) NOT NULL,
  `item_name` varchar(50) NOT NULL,
  `item_price` int(11) NOT NULL,
  `item_type` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item`
--

INSERT INTO `item` (`sr_no`, `item_name`, `item_price`, `item_type`) VALUES
(1, 'Paper Dosa', 80, 'SOUTH INDIAN'),
(2, 'Masala Dosa', 100, 'SOUTH INDIAN'),
(3, 'Garlic Dosa', 100, 'SOUTH INDIAN'),
(4, 'Mysore Masala Dosa', 110, 'SOUTH INDIAN'),
(5, 'Onion Dosa', 110, 'SOUTH INDIAN'),
(6, 'Cheese Dosa', 130, 'SOUTH INDIAN'),
(7, 'Spring Roll Dosa', 150, 'SOUTH INDIAN'),
(8, 'Schezwan Dosa', 150, 'SOUTH INDIAN'),
(9, 'Indian Bhaji Dosa', 160, 'SOUTH INDIAN'),
(10, 'Paneer Dosa', 180, 'SOUTH INDIAN'),
(11, 'Paneer Handi', 180, 'PUNJABI'),
(12, 'Paneer Kadai', 180, 'PUNJABI'),
(13, 'Paneer Toofani', 200, 'PUNJABI'),
(14, 'Paneer Tikka Masala', 220, 'PUNJABI'),
(15, 'Paneer Butter Masala', 220, 'PUNJABI'),
(16, 'Paneer Palak', 220, 'PUNJABI'),
(17, 'Paneer Bhurji', 220, 'PUNJABI'),
(18, 'Paneer Angara', 230, 'PUNJABI'),
(19, 'Paneer Pasanda', 230, 'PUNJABI'),
(20, 'Panjabi fix thali', 250, 'PUNJABI'),
(21, 'Rajawadi Undhiyu', 100, 'GUJARATI'),
(22, 'SevTameta', 80, 'GUJARATI'),
(23, 'Bharela Ringana', 80, 'GUJARATI'),
(24, 'Bhindi Masala', 70, 'GUJARATI'),
(25, 'Rajawadi Dhokli', 90, 'GUJARATI'),
(26, 'Puri Sak', 100, 'GUJARATI'),
(27, 'Aloo Bhindi', 70, 'GUJARATI'),
(28, 'Kadhi-Khichadi', 60, 'GUJARATI'),
(29, 'Dal-Bhat', 80, 'GUJARATI'),
(30, 'Gujarati fix thali', 200, 'GUJARATI'),
(31, 'Plain Nan', 40, 'ROTIES & TANDURI'),
(32, 'Butter Nan', 50, 'ROTIES & TANDURI'),
(33, 'Plain Roti', 25, 'ROTIES & TANDURI'),
(34, 'Butter Roti', 30, 'ROTIES & TANDURI'),
(35, 'Tanduri Roti', 40, 'ROTIES & TANDURI'),
(36, 'Plain Paratha', 40, 'ROTIES & TANDURI'),
(37, 'Lachha Paratha', 50, 'ROTIES & TANDURI'),
(38, 'Bajara No Rotlo', 30, 'ROTIES & TANDURI'),
(39, 'Puri', 10, 'ROTIES & TANDURI'),
(40, 'Regular Chhas', 40, 'BEVERAGES'),
(41, 'Masala Chhas', 45, 'BEVERAGES'),
(42, 'Plain Curd', 50, 'BEVERAGES'),
(43, 'Cold-Drinks', 40, 'BEVERAGES'),
(44, 'Mineral Water', 20, 'BEVERAGES'),
(45, 'Green Salad', 80, 'SALAD’S & PAPAD'),
(46, 'Kachumber Salad', 100, 'SALAD’S & PAPAD'),
(47, 'Vegetable Salad', 100, 'SALAD’S & PAPAD'),
(48, 'Roasted Papad', 20, 'SALAD’S & PAPAD'),
(49, 'Fry Papad', 30, 'SALAD’S & PAPAD'),
(50, 'Masala Papad', 50, 'SALAD’S & PAPAD');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `sr_no` int(11) NOT NULL,
  `table_no` varchar(50) NOT NULL,
  `token_number` varchar(50) NOT NULL,
  `item_name` varchar(100) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `item_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` varchar(20) DEFAULT NULL,
  `bill` varchar(255) NOT NULL,
  `order_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `table_status`
--

CREATE TABLE `table_status` (
  `id` int(11) NOT NULL,
  `table_no` varchar(10) NOT NULL,
  `status` enum('occupied','unoccupied') NOT NULL DEFAULT 'unoccupied'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `table_status`
--

INSERT INTO `table_status` (`id`, `table_no`, `status`) VALUES
(1, 'Table-1', 'unoccupied'),
(2, 'Table-2', 'unoccupied'),
(3, 'Table-3', 'unoccupied'),
(4, 'Table-4', 'unoccupied'),
(5, 'Table-5', 'unoccupied'),
(6, 'Table-6', 'unoccupied'),
(7, 'Table-7', 'unoccupied'),
(8, 'Table-8', 'unoccupied'),
(9, 'Table-9', 'unoccupied'),
(10, 'Table-10', 'unoccupied');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appearance`
--
ALTER TABLE `appearance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sr_no` (`sr_no`,`date`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`sr_no`),
  ADD UNIQUE KEY `Username` (`Username`);

--
-- Indexes for table `item`
--
ALTER TABLE `item`
  ADD PRIMARY KEY (`sr_no`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`sr_no`);

--
-- Indexes for table `table_status`
--
ALTER TABLE `table_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `table_no` (`table_no`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appearance`
--
ALTER TABLE `appearance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `sr_no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `item`
--
ALTER TABLE `item`
  MODIFY `sr_no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `sr_no` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `table_status`
--
ALTER TABLE `table_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
