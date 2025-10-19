-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 19, 2025 at 11:29 AM
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
-- Database: `starroofing_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) DEFAULT 2,
  `account_status` enum('active','inactive','suspended') DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `email`, `password`, `role_id`, `account_status`, `last_login`, `created_at`, `updated_at`) VALUES
(7, 'ajmacaraig19@gmail.com', '$2y$10$vpvhTBLnHY84MDjuEUlAQ.XWjiJ2MW8iUUxsJB0kzF97DtmQU3H/G', 2, 'active', '2025-10-19 05:15:22', '2025-09-12 11:34:43', '2025-10-19 05:15:22'),
(8, 'ajmacaraig20@gmail.com', '$2y$10$0xiOQg8aTUFKO9g/vajF.u5fM.nD9vMHXLqLPLoCia/HEzwR8XqAe', 1, 'active', '2025-10-19 07:32:20', '2025-09-12 11:45:37', '2025-10-19 07:32:20'),
(9, 'ajmacaraig18@gmail.com', '$2y$10$TRYyLzGYJgBEC7JTpo5qD.8IOknPQ/Nhpa04gkhQmrHFe02.P4mLu', 2, 'active', '2025-10-08 06:52:34', '2025-09-15 09:34:45', '2025-10-12 07:29:51'),
(10, '57842022@holycross.edu.ph', '$2y$10$VbwIwOWnPmhDvFsPwccoaOBzujWxl9waRxoJJbhXxMZpWPIR/Mmlu', 2, 'active', NULL, '2025-09-16 11:56:11', '2025-09-16 11:59:57'),
(13, 'admin@gmail.com', '$2y$10$d7Cg4ccEJ1OLypxRhgg3rutDJYaVZwUrCcpzEv0vkIwH0Ddl.wp3a', 1, 'active', '2025-10-19 04:26:59', '2025-10-09 04:09:49', '2025-10-19 04:26:59');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_code` varchar(20) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_code`, `category_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'design', 'Design & Construction', 'Design and construction services', '2025-09-14 11:46:09', '2025-09-14 11:46:09'),
(2, 'roofing', 'All Kinds of Roofing', 'Various roofing materials and services', '2025-09-14 11:46:09', '2025-09-14 11:46:09'),
(3, 'steel', 'Steel Truss', 'Steel truss products and installation', '2025-09-14 11:46:09', '2025-09-14 11:46:09'),
(4, 'upvc', 'uPVC Windows', 'uPVC window products', '2025-09-14 11:46:09', '2025-09-14 11:46:09'),
(5, 'glass', 'Glass & Aluminum', 'Glass and aluminum products', '2025-09-14 11:46:09', '2025-09-14 11:46:09'),
(6, 'cabinet', 'Modular Cabinet', 'Modular cabinet solutions', '2025-09-14 11:46:09', '2025-09-14 11:46:09'),
(7, 'doors', 'Combi/Blind & Roll Up Doors', 'Various door types', '2025-09-14 11:46:09', '2025-09-14 11:46:09');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `position` varchar(50) NOT NULL,
  `department` varchar(50) NOT NULL,
  `hire_date` date NOT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `first_name`, `last_name`, `email`, `phone`, `position`, `department`, `hire_date`, `salary`, `status`, `image_path`, `created_at`, `updated_at`, `is_archived`) VALUES
(1, 'archie', 'ramirez', 'chiechie@gmail.com', '09627646372', '69', 'Sales', '2025-09-17', 45345.00, 'active', 'uploads/employees/68cff607c1937.jpg', '2025-09-21 12:56:39', '2025-09-21 13:04:05', 0),
(2, 'Joshua', 'Docena', 'pogiako@gmaill.com', '09728374893', 'Architect', 'Roofing', '2025-09-27', 213123.00, 'active', 'uploads/employees/68d8299b2fef9.png', '2025-09-27 18:14:51', '2025-10-01 09:34:53', 0);

-- --------------------------------------------------------

--
-- Table structure for table `inquiries`
--

CREATE TABLE `inquiries` (
  `id` int(11) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `inquiry_type` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `attempt_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `success` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `reset_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`reset_id`, `email`, `token`, `expires_at`, `created_at`, `used`) VALUES
(9, 'ajmacaraig18@gmail.com', '526025', '2025-10-12 09:12:42', '2025-10-12 07:07:42', 1),
(14, 'ajmacaraig20@gmail.com', '233195', '2025-10-16 21:06:26', '2025-10-16 19:01:26', 1),
(15, 'ajmacaraig19@gmail.com', '188928', '2025-10-17 20:21:55', '2025-10-17 18:16:55', 1);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `unit` varchar(20) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `model_path` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_archived` tinyint(1) DEFAULT 0,
  `meshy_task_id` varchar(255) DEFAULT NULL,
  `model_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `name`, `description`, `price`, `stock_quantity`, `unit`, `image_path`, `model_path`, `created_by`, `created_at`, `updated_at`, `is_archived`, `meshy_task_id`, `model_url`) VALUES
(1, 2, 'Roofing', 'Bubong', 1000.00, 75, 'piece', 'https://www.google.com/url?sa=i&url=https%3A%2F%2Fwww.ugc.ph%2Fproduct%2Fduratile%2F&psig=AOvVaw3X_uMdXyDZnuuECXufnzjQ&ust=1757939065451000&source=images&cd=vfe&opi=89978449&ved=0CBUQjRxqFwoTCOCD-L6f2I8DFQAAAAAdAAAAABAL', NULL, 8, '2025-09-14 12:25:05', '2025-09-14 15:43:06', 1, NULL, NULL),
(2, 4, 'Aluminum Window', 'Aluminum, Glass, Window', 300.00, 1, 'meter', 'uploads/products/1757905503_wp6786949.jpg', 'uploads/3dmodels/japanese_pagoda_tower.glb', 8, '2025-09-14 13:07:13', '2025-10-19 08:23:03', 0, NULL, NULL),
(5, 4, 'Tempered Window', 'Water Proof', 6000.00, 25, 'piece', 'uploads/products/1757904798_images.jpg', NULL, 8, '2025-09-15 02:53:18', '2025-09-15 02:53:18', 0, NULL, NULL),
(6, 6, 'Cabinet', 'Water Proof', 600.00, 50, 'set', 'uploads/products/1757905716_1722505054150.jpeg', NULL, 8, '2025-09-15 03:08:36', '2025-09-15 03:08:36', 0, NULL, NULL),
(7, 5, 'glass', 'heat proof', 500.00, 3, 'set', 'uploads/products/1757983610_10619874-the-light-trails-on-the-modern-building-background-in-shanghai-china-.jpg', NULL, 8, '2025-09-16 00:46:50', '2025-09-16 00:46:50', 0, NULL, NULL),
(8, 1, 'Bungalow', 'good quality', 10000000.00, 67, 'set', 'uploads/products/1757983690_Infrastructure.jpg', NULL, 8, '2025-09-16 00:48:10', '2025-10-07 09:29:22', 0, NULL, NULL),
(9, 3, 'box', 'bakal', 800.00, 25, 'sqm', 'uploads/products/1757983757_images.jpg', NULL, 8, '2025-09-16 00:49:17', '2025-10-07 09:32:42', 1, NULL, NULL),
(10, 7, 'Doors', 'high quality', 10000.00, 100, 'piece', 'uploads/products/1757984677_1750499159122.jpeg', NULL, 8, '2025-09-16 01:04:37', '2025-09-18 07:17:02', 1, NULL, NULL),
(11, 1, 'Different kinds of construction', 'Limited products, must buy', 5000.00, 64, 'sad', 'uploads/products/1757985178_3124496.jpg', NULL, 8, '2025-09-16 01:12:58', '2025-09-16 07:39:51', 1, NULL, NULL),
(12, 5, 'Baso', 'sdsad', 400.00, 60, 'KG', NULL, NULL, 8, '2025-09-18 07:07:58', '2025-09-18 07:09:42', 1, NULL, NULL),
(13, 1, 'Ordinary House', 'Just an ordinary house design', 1500000.00, 5, 'set', 'uploads/products/1758984396_hiraganadakuon.gif', NULL, 8, '2025-09-27 14:46:36', '2025-09-27 17:19:58', 1, NULL, NULL),
(14, 5, 'Gate', '', 50000.00, 30, 'set', 'uploads/products/1759905769_purok 3.png', NULL, 8, '2025-10-08 06:42:49', '2025-10-17 02:11:11', 0, NULL, NULL),
(15, 5, 'Gate', '', 50000.00, 10, 'set', 'uploads/products/1759905994_purok 3.png', NULL, 8, '2025-10-08 06:46:34', '2025-10-08 06:48:49', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `role_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`, `role_description`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Can manage content and users but with some restrictions', '2025-09-10 10:16:40', '2025-09-12 11:32:29'),
(2, 'user', 'Regular user with limited access', '2025-09-10 10:16:40', '2025-09-12 11:32:34');

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `contact_number` varchar(15) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `region_code` varchar(10) DEFAULT NULL,
  `region_name` varchar(100) DEFAULT NULL,
  `province_code` varchar(10) DEFAULT NULL,
  `province_name` varchar(100) DEFAULT NULL,
  `city_code` varchar(10) DEFAULT NULL,
  `city_name` varchar(100) DEFAULT NULL,
  `barangay_code` varchar(10) DEFAULT NULL,
  `barangay_name` varchar(100) DEFAULT NULL,
  `street` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`id`, `account_id`, `first_name`, `last_name`, `middle_name`, `birthdate`, `contact_number`, `gender`, `region_code`, `region_name`, `province_code`, `province_name`, `city_code`, `city_name`, `barangay_code`, `barangay_name`, `street`, `created_at`, `updated_at`) VALUES
(2, 7, 'aj', 'lin', 'mac', '2025-09-02', '091287382173721', 'male', '12', 'Region XII (SOCCSKSARGEN)', '1247', 'Cotabato (North Cotabato)', '124711', 'Pigkawayan', '124711015', 'Kimarayang', 'bahay', '2025-09-12 11:34:43', '2025-09-12 11:43:27'),
(3, 8, 'Ajey', 'Linsangan', 'M', '2025-09-01', '09127312983', 'male', '10', 'Region X (Northern Mindanao)', '1018', 'Camiguin', '101802', 'Guinsiliban', '101802003', 'Cantaan', 'haha', '2025-09-12 11:45:37', '2025-10-08 06:15:57'),
(4, 9, 'haha', 'hah', 'hah', '2025-09-15', '080282', 'female', '17', 'Region IV-B (MIMAROPA)', '1752', 'Oriental Mindoro', '175210', 'Pola', '175210011', 'Malibago', 'haha', '2025-09-15 09:34:45', '2025-09-15 09:34:45'),
(5, 10, 'Alvin', 'Bayabos', 'S', '2025-01-14', '09871123213', 'male', '13', 'National Capital Region (NCR)', '1376', 'Ncr, Fourth District', '137603', 'City Of Muntinlupa', '137603002', 'Bayanan', 'bahay', '2025-09-16 11:56:11', '2025-10-15 06:08:57');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_code` (`category_code`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `inquiries`
--
ALTER TABLE `inquiries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`attempt_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`reset_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_id` (`account_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `inquiries`
--
ALTER TABLE `inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `attempt_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `reset_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `accounts_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
