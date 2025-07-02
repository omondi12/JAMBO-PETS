-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 02, 2025 at 11:48 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jambo_pets`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` text NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_logs`
--

INSERT INTO `admin_logs` (`log_id`, `user_id`, `action`, `ip_address`, `created_at`) VALUES
(1, 5, 'Updated platform settings', '::1', '2025-05-16 08:55:37'),
(2, 5, 'Updated platform settings', '::1', '2025-05-16 09:30:47'),
(3, 5, 'Updated platform settings', '::1', '2025-05-16 09:31:03'),
(4, 5, 'Updated platform settings', '::1', '2025-05-16 09:31:56'),
(5, 5, 'Updated platform settings', '::1', '2025-05-16 09:32:12');

-- --------------------------------------------------------

--
-- Table structure for table `admin_roles`
--

CREATE TABLE `admin_roles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `admin_role` enum('master','product','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_roles`
--

INSERT INTO `admin_roles` (`id`, `user_id`, `admin_role`, `created_at`, `updated_at`) VALUES
(2, 5, 'master', '2025-05-15 19:51:45', '2025-05-15 19:51:45'),
(3, 9, 'product', '2025-05-15 19:54:10', '2025-05-15 19:54:10');

-- --------------------------------------------------------

--
-- Table structure for table `analytics`
--

CREATE TABLE `analytics` (
  `analytics_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `page_visited` varchar(255) NOT NULL,
  `action_type` varchar(50) DEFAULT NULL,
  `item_type` enum('pet','product','blog','other') DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `visit_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(50) DEFAULT NULL,
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `analytics`
--

INSERT INTO `analytics` (`analytics_id`, `user_id`, `page_visited`, `action_type`, `item_type`, `item_id`, `visit_timestamp`, `ip_address`, `user_agent`) VALUES
(1, NULL, 'homepage', NULL, NULL, NULL, '2025-05-06 16:12:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(2, NULL, 'homepage', NULL, NULL, NULL, '2025-05-06 16:12:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(3, 2, 'login', 'User logged in', NULL, NULL, '2025-05-06 16:14:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(4, 2, 'logout', 'User logged out', NULL, NULL, '2025-05-06 16:16:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(5, NULL, 'homepage', NULL, NULL, NULL, '2025-05-06 16:16:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(6, NULL, 'homepage', NULL, NULL, NULL, '2025-05-06 16:33:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(7, 2, 'login', 'User logged in', NULL, NULL, '2025-05-06 16:34:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(8, 2, 'homepage', NULL, NULL, NULL, '2025-05-06 16:38:34', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(9, 2, 'browse_pets', 'Array', NULL, NULL, '2025-05-06 16:38:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(10, 2, 'browse_pets', 'Array', NULL, NULL, '2025-05-06 16:39:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(11, 2, 'browse_pets', 'Array', NULL, NULL, '2025-05-06 16:39:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(12, 2, 'browse_pets', 'Array', NULL, NULL, '2025-05-06 16:39:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(13, 2, 'logout', 'User logged out', NULL, NULL, '2025-05-06 16:39:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(14, 3, 'login', 'User logged in', NULL, NULL, '2025-05-06 16:41:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(15, 3, 'logout', 'User logged out', NULL, NULL, '2025-05-06 16:49:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(16, 3, 'login', 'User logged in', NULL, NULL, '2025-05-06 16:49:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(17, 3, 'logout', 'User logged out', NULL, NULL, '2025-05-06 16:50:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(18, 2, 'login', 'User logged in', NULL, NULL, '2025-05-06 16:50:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(19, 2, 'homepage', NULL, NULL, NULL, '2025-05-06 16:50:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(20, 2, 'browse_pets', 'Array', NULL, NULL, '2025-05-06 16:51:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(21, 2, 'logout', 'User logged out', NULL, NULL, '2025-05-06 16:51:34', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(22, NULL, 'homepage', NULL, NULL, NULL, '2025-05-06 17:44:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(23, NULL, 'homepage', NULL, NULL, NULL, '2025-05-06 17:53:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(24, 2, 'login', 'User logged in', NULL, NULL, '2025-05-06 17:53:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(25, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-06 17:57:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(26, 2, 'browse_pets', 'Array', NULL, NULL, '2025-05-06 17:57:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(27, 2, 'browse_products', 'Array', NULL, NULL, '2025-05-06 17:57:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(28, 2, 'logout', 'User logged out', NULL, NULL, '2025-05-06 17:58:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(29, 3, 'login', 'User logged in', NULL, NULL, '2025-05-06 17:59:18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(30, 3, 'logout', 'User logged out', NULL, NULL, '2025-05-06 18:18:42', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(31, 5, 'login', 'User logged in', NULL, NULL, '2025-05-06 18:21:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(32, 5, 'homepage', NULL, NULL, NULL, '2025-05-06 18:45:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(33, 5, 'logout', 'User logged out', NULL, NULL, '2025-05-06 18:45:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(34, NULL, 'homepage', NULL, NULL, NULL, '2025-05-06 18:45:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(35, 2, 'login', 'User logged in', NULL, NULL, '2025-05-06 18:46:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(36, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-06 18:46:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(37, 2, 'browse_pets', 'Array', NULL, NULL, '2025-05-06 18:46:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(38, 2, 'browse_products', 'Array', NULL, NULL, '2025-05-06 18:46:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(39, 2, 'browse_products', 'Array', NULL, NULL, '2025-05-06 18:46:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(40, 2, 'browse_products', 'Array', NULL, NULL, '2025-05-06 18:50:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(41, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-06 18:50:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(42, 2, 'browse_pets', 'Array', NULL, NULL, '2025-05-06 18:51:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(43, 2, 'browse_pets', 'Array', NULL, NULL, '2025-05-06 18:52:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(44, 2, 'browse_pets', 'Array', NULL, NULL, '2025-05-06 18:55:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(45, 2, 'browse_products', 'Array', NULL, NULL, '2025-05-06 18:55:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(46, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-06 18:55:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(47, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-06 18:58:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(48, 2, 'browse_pets', 'Array', NULL, NULL, '2025-05-06 18:58:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(49, 2, 'browse_pets', 'Array', NULL, NULL, '2025-05-06 18:58:34', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(50, 2, 'browse_pets', 'Array', NULL, NULL, '2025-05-06 18:58:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(51, 2, 'browse_pets', 'Array', NULL, NULL, '2025-05-06 18:58:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(52, 2, 'browse_pets', 'Array', NULL, NULL, '2025-05-06 19:01:33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(53, 2, 'browse_pets', 'Array', NULL, NULL, '2025-05-06 19:01:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(54, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-06 19:06:29', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(55, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-06 19:06:34', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(56, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-06 19:06:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(57, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-06 19:06:48', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(58, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-06 19:20:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(59, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-06 19:20:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(60, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-06 19:20:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(61, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-06 19:22:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(62, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-06 19:22:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(63, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-06 19:23:34', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(64, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-06 19:23:39', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(65, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-06 19:23:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(66, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-06 19:23:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(67, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-06 19:25:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(68, 2, 'homepage', NULL, NULL, NULL, '2025-05-06 19:33:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(69, 2, 'homepage', NULL, NULL, NULL, '2025-05-06 19:35:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(70, 2, 'homepage', NULL, NULL, NULL, '2025-05-06 19:35:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(71, 2, 'browse_pets', '{\"category_id\":7,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-06 19:37:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(72, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-06 19:37:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(73, 2, 'homepage', NULL, NULL, NULL, '2025-05-06 19:38:00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(74, 2, 'logout', 'User logged out', NULL, NULL, '2025-05-06 19:40:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(75, NULL, 'homepage', NULL, NULL, NULL, '2025-05-06 19:40:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(76, 3, 'login', 'User logged in', NULL, NULL, '2025-05-06 19:40:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(77, 3, 'logout', 'User logged out', NULL, NULL, '2025-05-06 20:35:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(78, 2, 'login', 'User logged in', NULL, NULL, '2025-05-06 20:49:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(79, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-06 20:49:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(80, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-06 20:50:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(81, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-06 20:50:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(82, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-06 20:50:28', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(83, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-06 20:58:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(84, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-06 21:04:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(85, 2, 'logout', 'User logged out', NULL, NULL, '2025-05-06 21:43:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(86, NULL, 'homepage', NULL, NULL, NULL, '2025-05-06 21:43:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(87, NULL, 'homepage', NULL, NULL, NULL, '2025-05-06 21:44:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(88, 2, 'login', 'User logged in', NULL, NULL, '2025-05-07 06:15:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(89, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-07 06:15:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(90, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-07 06:17:00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(91, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-07 06:23:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(92, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-07 06:27:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(93, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-07 06:27:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(94, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-07 06:35:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(95, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-07 06:35:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(96, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-07 06:36:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(97, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-07 06:37:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(98, 2, 'logout', 'User logged out', NULL, NULL, '2025-05-07 06:38:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(99, 3, 'login', 'User logged in', NULL, NULL, '2025-05-07 06:38:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(100, 3, 'logout', 'User logged out', NULL, NULL, '2025-05-07 06:43:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(101, 2, 'login', 'User logged in', NULL, NULL, '2025-05-07 06:43:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(102, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-07 06:43:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(103, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-07 06:43:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(104, 2, 'homepage', NULL, NULL, NULL, '2025-05-07 06:43:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(105, 2, 'browse_pets', '{\"category_id\":1,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-07 06:43:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(106, 2, 'browse_pets', '{\"category_id\":1,\"county\":\"\",\"search\":\"doberman\"}', NULL, NULL, '2025-05-07 06:44:09', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(107, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-07 06:56:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(108, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-07 06:56:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(109, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-07 06:56:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(110, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-07 07:00:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(111, 2, 'logout', 'User logged out', NULL, NULL, '2025-05-07 07:00:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(112, 5, 'login', 'User logged in', NULL, NULL, '2025-05-07 07:01:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(113, 5, 'logout', 'User logged out', NULL, NULL, '2025-05-07 07:07:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(114, 2, 'login', 'User logged in', NULL, NULL, '2025-05-07 07:07:39', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(115, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-07 07:07:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(116, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-07 07:07:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(117, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 07:07:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(118, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 07:09:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(119, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-07 07:15:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(120, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 07:15:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(121, 2, 'logout', 'User logged out', NULL, NULL, '2025-05-07 07:17:28', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(122, 3, 'login', 'User logged in', NULL, NULL, '2025-05-07 07:18:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(123, 3, 'logout', 'User logged out', NULL, NULL, '2025-05-07 07:20:19', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1'),
(124, 2, 'login', 'User logged in', NULL, NULL, '2025-05-07 07:21:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(125, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-07 07:21:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(126, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-07 07:21:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(127, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-07 07:22:00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(128, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 07:22:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(129, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 07:23:08', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1'),
(130, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-07 07:23:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(131, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-07 07:25:15', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1'),
(132, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-07 07:26:29', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(133, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-07 07:26:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(134, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 07:26:42', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(135, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 07:40:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(136, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 07:42:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(137, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 07:48:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(138, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 07:49:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(139, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 07:49:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(140, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 07:50:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(141, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 07:50:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(142, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 08:19:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(143, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 08:26:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(144, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 08:34:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(145, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 08:35:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(146, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 08:35:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(147, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 08:36:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(148, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 08:37:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(149, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 08:37:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(150, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 08:39:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(151, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 08:42:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(152, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 08:47:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(153, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 08:53:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(154, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 08:56:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(155, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 08:57:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(156, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 08:58:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(157, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 09:00:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(158, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 09:01:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(159, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 09:03:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(160, 2, 'logout', 'User logged out', NULL, NULL, '2025-05-07 09:04:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(161, 2, 'login', 'User logged in', NULL, NULL, '2025-05-07 09:04:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(162, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-07 09:04:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(163, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-07 09:04:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(164, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 09:04:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(165, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 09:10:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(166, 2, 'login', 'User logged in', NULL, NULL, '2025-05-07 18:51:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(167, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-07 18:51:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(168, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-07 18:51:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(169, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 18:51:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(170, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 19:03:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(171, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 19:05:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(172, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 19:07:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(173, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 19:08:00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(174, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 19:08:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(175, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 19:13:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(176, 2, 'add_to_wishlist', '{\"item_type\":\"pet\",\"item_id\":1}', NULL, NULL, '2025-05-07 19:13:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(177, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":1,\"quantity\":1}', NULL, NULL, '2025-05-07 19:13:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(178, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-07 19:14:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(179, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-07 19:25:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(180, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 19:25:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(181, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":1,\"quantity\":1}', NULL, NULL, '2025-05-07 19:25:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(182, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-07 19:25:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(183, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 19:25:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(184, 2, 'add_to_wishlist', '{\"item_type\":\"pet\",\"item_id\":1}', NULL, NULL, '2025-05-07 19:25:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(185, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-07 19:41:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(186, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 19:41:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(187, 2, 'add_to_wishlist', '{\"item_type\":\"pet\",\"item_id\":1}', NULL, NULL, '2025-05-07 19:41:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(188, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-07 19:42:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(189, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 19:44:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(190, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-07 20:14:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(191, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 20:14:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(192, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-07 20:26:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(193, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-07 21:02:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(194, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 21:02:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(195, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-07 21:24:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(196, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 21:24:28', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(197, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 21:25:00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(198, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-07 21:25:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(199, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-08 06:49:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(200, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-08 06:52:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(201, 2, 'logout', 'User logged out', NULL, NULL, '2025-05-08 07:10:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(202, 3, 'login', 'User logged in', NULL, NULL, '2025-05-08 07:11:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(203, 3, 'logout', 'User logged out', NULL, NULL, '2025-05-08 07:25:28', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(204, 2, 'login', 'User logged in', NULL, NULL, '2025-05-08 07:25:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(205, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-08 07:25:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(206, 2, 'logout', 'User logged out', NULL, NULL, '2025-05-08 07:55:09', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(207, 3, 'login', 'User logged in', NULL, NULL, '2025-05-08 07:55:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(208, 3, 'logout', 'User logged out', NULL, NULL, '2025-05-08 08:15:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(209, 2, 'login', 'User logged in', NULL, NULL, '2025-05-08 08:15:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(210, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-08 08:15:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(211, 2, 'logout', 'User logged out', NULL, NULL, '2025-05-08 08:16:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(212, 3, 'login', 'User logged in', NULL, NULL, '2025-05-08 08:16:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(213, 3, 'logout', 'User logged out', NULL, NULL, '2025-05-08 08:17:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(214, 2, 'login', 'User logged in', NULL, NULL, '2025-05-08 08:17:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(215, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-08 08:17:42', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(216, 2, 'logout', 'User logged out', NULL, NULL, '2025-05-08 08:19:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(217, 3, 'login', 'User logged in', NULL, NULL, '2025-05-08 08:19:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(218, 3, 'logout', 'User logged out', NULL, NULL, '2025-05-08 08:34:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(219, 5, 'login', 'User logged in', NULL, NULL, '2025-05-08 08:35:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(220, 5, 'logout', 'User logged out', NULL, NULL, '2025-05-08 08:36:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(221, 2, 'login', 'User logged in', NULL, NULL, '2025-05-08 08:36:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(222, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-08 08:36:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(223, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-08 08:36:42', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(224, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-08 08:47:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(225, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"doberman\"}', NULL, NULL, '2025-05-08 08:58:34', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(226, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-08 08:58:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(227, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-08 08:58:48', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(228, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"doberman\"}', NULL, NULL, '2025-05-08 08:59:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(229, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"dogs\"}', NULL, NULL, '2025-05-08 08:59:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(230, 2, 'browse_pets', '{\"category_id\":1,\"county\":\"\",\"search\":\"dogs\"}', NULL, NULL, '2025-05-08 08:59:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(231, 2, 'browse_pets', '{\"category_id\":1,\"county\":\"Kisumu\",\"search\":\"dogs\"', NULL, NULL, '2025-05-08 08:59:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(232, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-08 09:46:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(233, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-08 09:46:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(234, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-08 09:46:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(235, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-08 09:46:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(236, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-08 09:47:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(237, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-08 09:47:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(238, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-08 09:57:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(239, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-08 10:00:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(240, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-08 10:00:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(241, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-08 10:00:34', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(242, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-08 10:28:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(243, 2, 'logout', 'User logged out', NULL, NULL, '2025-05-08 10:28:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(244, 3, 'login', 'User logged in', NULL, NULL, '2025-05-08 10:28:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36');
INSERT INTO `analytics` (`analytics_id`, `user_id`, `page_visited`, `action_type`, `item_type`, `item_id`, `visit_timestamp`, `ip_address`, `user_agent`) VALUES
(245, 3, 'logout', 'User logged out', NULL, NULL, '2025-05-08 10:35:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(246, 2, 'login', 'User logged in', NULL, NULL, '2025-05-08 10:35:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(247, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-08 10:35:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(248, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-08 10:35:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(249, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-08 10:36:09', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(250, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-08 10:37:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(251, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-08 10:40:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(252, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-08 10:40:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(253, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-08 10:44:36', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1'),
(254, 2, 'view_pet', '{\"product_id\":1}', NULL, NULL, '2025-05-08 10:53:28', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(255, 2, 'view_pet', '{\"product_id\":1}', NULL, NULL, '2025-05-08 10:54:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(256, 2, 'view_pet', '{\"product_id\":1}', NULL, NULL, '2025-05-08 10:55:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(257, 2, 'remove_from_wishlist', '{\"item_type\":\"pet\",\"item_id\":1}', NULL, NULL, '2025-05-08 10:56:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(258, 2, 'add_to_wishlist', '{\"item_type\":\"pet\",\"item_id\":1}', NULL, NULL, '2025-05-08 10:56:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(259, 2, 'view_pet', '{\"product_id\":1}', NULL, NULL, '2025-05-08 10:56:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(260, 2, 'view_product', '{\"product_id\":1}', NULL, NULL, '2025-05-08 10:56:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(261, 2, 'view_product', '{\"product_id\":1}', NULL, NULL, '2025-05-08 11:13:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(262, 2, 'add_to_cart', '{\"item_type\":\"product\",\"item_id\":1,\"quantity\":1}', NULL, NULL, '2025-05-08 11:13:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(263, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-08 11:13:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(264, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-08 11:14:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(265, 2, 'view_product', '{\"product_id\":1}', NULL, NULL, '2025-05-08 11:14:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(266, 2, 'add_to_wishlist', '{\"item_type\":\"product\",\"item_id\":1}', NULL, NULL, '2025-05-08 11:14:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(267, 2, 'view_product', '{\"product_id\":1}', NULL, NULL, '2025-05-08 11:14:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(268, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-08 11:33:09', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(269, 2, 'view_product', '{\"product_id\":1}', NULL, NULL, '2025-05-08 11:33:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(270, 2, 'remove_from_wishlist', '{\"item_type\":\"product\",\"item_id\":1}', NULL, NULL, '2025-05-08 11:33:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(271, 2, 'view_product', '{\"product_id\":1}', NULL, NULL, '2025-05-08 11:33:18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(272, 2, 'add_to_wishlist', '{\"item_type\":\"product\",\"item_id\":1}', NULL, NULL, '2025-05-08 11:33:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(273, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-08 11:38:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(274, 2, 'view_product', '{\"product_id\":1}', NULL, NULL, '2025-05-08 11:38:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(275, 2, 'remove_from_wishlist', '{\"item_type\":\"product\",\"item_id\":1}', NULL, NULL, '2025-05-08 11:38:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(276, 2, 'add_to_wishlist', '{\"item_type\":\"product\",\"item_id\":1}', NULL, NULL, '2025-05-08 11:39:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(277, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-08 12:07:39', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(278, 2, 'view_product', '{\"product_id\":1}', NULL, NULL, '2025-05-08 12:07:42', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(279, 2, 'remove_from_wishlist', '{\"item_type\":\"product\",\"item_id\":1}', NULL, NULL, '2025-05-08 12:07:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(280, 2, 'add_to_wishlist', '{\"item_type\":\"product\",\"item_id\":1}', NULL, NULL, '2025-05-08 12:07:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(281, NULL, 'homepage', NULL, NULL, NULL, '2025-05-08 17:21:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(282, 2, 'login', 'User logged in', NULL, NULL, '2025-05-08 17:21:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(283, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-08 17:21:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(284, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-08 17:21:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(285, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-08 17:22:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(286, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-08 17:25:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(287, 2, 'view_product', '{\"product_id\":1}', NULL, NULL, '2025-05-08 17:26:09', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(288, 2, 'logout', 'User logged out', NULL, NULL, '2025-05-08 17:27:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(289, 2, 'login', 'User logged in', NULL, NULL, '2025-05-08 17:27:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(290, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-08 17:28:00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(291, 2, 'logout', 'User logged out', NULL, NULL, '2025-05-08 18:11:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(292, 5, 'login', 'User logged in', NULL, NULL, '2025-05-08 18:12:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(293, 5, 'logout', 'User logged out', NULL, NULL, '2025-05-08 18:14:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(294, NULL, 'homepage', NULL, NULL, NULL, '2025-05-08 18:16:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(295, NULL, 'homepage', NULL, NULL, NULL, '2025-05-08 18:16:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(296, NULL, 'homepage', NULL, NULL, NULL, '2025-05-08 18:17:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(297, NULL, 'homepage', NULL, NULL, NULL, '2025-05-08 18:18:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(298, NULL, 'homepage', NULL, NULL, NULL, '2025-05-08 18:20:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(299, 2, 'login', 'User logged in', NULL, NULL, '2025-05-08 18:22:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(300, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-08 18:22:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(301, 2, 'logout', 'User logged out', NULL, NULL, '2025-05-08 18:24:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(302, 3, 'login', 'User logged in', NULL, NULL, '2025-05-08 18:24:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(303, 3, 'logout', 'User logged out', NULL, NULL, '2025-05-08 18:28:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(304, 2, 'login', 'User logged in', NULL, NULL, '2025-05-08 18:28:50', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(305, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-08 18:28:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(306, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-08 18:28:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(307, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-08 18:45:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(308, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-08 19:05:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(309, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-08 19:05:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(310, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-08 19:06:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(311, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-08 19:06:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(312, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-08 19:07:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(313, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-08 19:07:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(314, 2, 'logout', 'User logged out', NULL, NULL, '2025-05-08 19:07:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(315, 3, 'login', 'User logged in', NULL, NULL, '2025-05-08 19:08:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(316, 3, 'homepage', NULL, NULL, NULL, '2025-05-08 19:28:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(317, 3, 'homepage', NULL, NULL, NULL, '2025-05-08 19:28:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(318, 3, 'homepage', NULL, NULL, NULL, '2025-05-08 19:29:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(319, 3, 'homepage', NULL, NULL, NULL, '2025-05-08 19:38:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(320, 3, 'homepage', NULL, NULL, NULL, '2025-05-08 19:38:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(321, 3, 'homepage', NULL, NULL, NULL, '2025-05-08 19:48:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(322, 3, 'logout', 'User logged out', NULL, NULL, '2025-05-08 19:49:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(323, NULL, 'homepage', NULL, NULL, NULL, '2025-05-08 19:49:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(324, 5, 'login', 'User logged in', NULL, NULL, '2025-05-08 19:49:39', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(325, 5, 'logout', 'User logged out', NULL, NULL, '2025-05-08 19:50:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(326, NULL, 'homepage', NULL, NULL, NULL, '2025-05-08 19:50:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(327, NULL, 'homepage', NULL, NULL, NULL, '2025-05-08 19:51:48', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(328, NULL, 'homepage', NULL, NULL, NULL, '2025-05-08 19:52:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(329, NULL, 'homepage', NULL, NULL, NULL, '2025-05-08 19:53:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(330, NULL, 'homepage', NULL, NULL, NULL, '2025-05-08 19:53:58', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1'),
(331, NULL, 'homepage', NULL, NULL, NULL, '2025-05-08 19:54:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(332, NULL, 'homepage', NULL, NULL, NULL, '2025-05-08 19:54:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(333, NULL, 'homepage', NULL, NULL, NULL, '2025-05-08 19:55:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(334, NULL, 'homepage', NULL, NULL, NULL, '2025-05-08 19:55:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(335, NULL, 'homepage', NULL, NULL, NULL, '2025-05-08 20:02:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(336, NULL, 'homepage', NULL, NULL, NULL, '2025-05-08 20:07:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(337, NULL, 'homepage', NULL, NULL, NULL, '2025-05-08 20:07:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(338, NULL, 'homepage', NULL, NULL, NULL, '2025-05-08 20:10:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(339, 2, 'login', 'User logged in', NULL, NULL, '2025-05-08 20:11:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(340, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-08 20:11:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(341, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-08 20:13:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(342, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-08 20:15:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(343, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-08 20:16:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(344, 2, 'homepage', NULL, NULL, NULL, '2025-05-08 20:16:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(345, 2, 'homepage', NULL, NULL, NULL, '2025-05-09 06:46:42', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(346, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-09 06:47:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(347, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-09 06:48:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(348, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-09 06:48:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(349, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-09 06:48:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(350, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-09 06:49:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(351, 2, 'logout', 'User logged out', NULL, NULL, '2025-05-09 06:49:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(352, 3, 'login', 'User logged in', NULL, NULL, '2025-05-09 06:49:50', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36'),
(353, 3, 'logout', 'User logged out', NULL, NULL, '2025-05-10 07:27:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(354, 2, 'login', 'User logged in', NULL, NULL, '2025-05-10 07:27:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(355, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-10 07:27:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(356, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-10 07:27:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(357, 2, 'logout', 'User logged out', NULL, NULL, '2025-05-10 07:28:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(358, 3, 'login', 'User logged in', NULL, NULL, '2025-05-10 07:28:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(359, 3, 'logout', 'User logged out', NULL, NULL, '2025-05-10 07:28:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(360, 5, 'login', 'User logged in', NULL, NULL, '2025-05-10 07:29:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(361, 5, 'logout', 'User logged out', NULL, NULL, '2025-05-10 07:29:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(362, NULL, 'homepage', NULL, NULL, NULL, '2025-05-10 07:29:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(363, 2, 'login', 'User logged in', NULL, NULL, '2025-05-10 07:30:18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(364, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-10 07:30:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(365, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-10 07:30:50', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(366, 2, 'view_pet', '{\"pet_id\":2}', NULL, NULL, '2025-05-10 07:30:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(367, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":2,\"quantity\":1}', NULL, NULL, '2025-05-10 07:30:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(368, 2, 'logout', 'User logged out', NULL, NULL, '2025-05-10 07:42:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(369, 3, 'login', 'User logged in', NULL, NULL, '2025-05-10 07:42:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(370, 3, 'logout', 'User logged out', NULL, NULL, '2025-05-10 07:43:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(371, 5, 'login', 'User logged in', NULL, NULL, '2025-05-10 07:43:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(372, 5, 'logout', 'User logged out', NULL, NULL, '2025-05-10 09:47:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(373, NULL, 'homepage', NULL, NULL, NULL, '2025-05-10 09:47:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(374, NULL, 'homepage', NULL, NULL, NULL, '2025-05-10 09:52:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(375, NULL, 'homepage', NULL, NULL, NULL, '2025-05-10 09:52:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(376, NULL, 'homepage', NULL, NULL, NULL, '2025-05-10 09:55:00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(377, 2, 'login', 'User logged in', NULL, NULL, '2025-05-10 09:55:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(378, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-10 09:55:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(379, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-12 19:36:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(380, 2, 'logout', 'User logged out', NULL, NULL, '2025-05-12 19:46:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(381, 3, 'login', 'User logged in', NULL, NULL, '2025-05-12 19:46:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(382, 3, 'logout', 'User logged out', NULL, NULL, '2025-05-12 19:47:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(383, 2, 'login', 'User logged in', NULL, NULL, '2025-05-12 19:47:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(384, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-12 19:47:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(385, 2, 'logout', 'User logged out', NULL, NULL, '2025-05-12 20:16:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(386, 3, 'login', 'User logged in', NULL, NULL, '2025-05-12 20:17:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(387, 3, 'homepage', NULL, NULL, NULL, '2025-05-13 06:35:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(388, 3, 'homepage', NULL, NULL, NULL, '2025-05-13 06:44:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(389, 3, 'homepage', NULL, NULL, NULL, '2025-05-13 06:44:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(390, 3, 'homepage', NULL, NULL, NULL, '2025-05-13 06:46:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(391, 3, 'homepage', NULL, NULL, NULL, '2025-05-13 06:49:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(392, 3, 'homepage', NULL, NULL, NULL, '2025-05-13 06:51:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(393, 3, 'homepage', NULL, NULL, NULL, '2025-05-13 06:54:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(394, 3, 'homepage', NULL, NULL, NULL, '2025-05-13 06:56:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(395, 3, 'homepage', NULL, NULL, NULL, '2025-05-13 07:03:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(396, 3, 'logout', 'User logged out', NULL, NULL, '2025-05-13 07:04:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(397, NULL, 'homepage', NULL, NULL, NULL, '2025-05-13 07:04:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(398, NULL, 'homepage', NULL, NULL, NULL, '2025-05-13 07:05:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(399, 5, 'login', 'User logged in', NULL, NULL, '2025-05-13 07:06:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(400, 5, 'logout', 'User logged out', NULL, NULL, '2025-05-13 07:22:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(401, NULL, 'homepage', NULL, NULL, NULL, '2025-05-13 07:22:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(402, NULL, 'homepage', NULL, NULL, NULL, '2025-05-13 07:24:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(403, 3, 'login', 'User logged in', NULL, NULL, '2025-05-13 07:25:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(404, 3, 'homepage', NULL, NULL, NULL, '2025-05-13 08:06:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(405, 3, 'logout', 'User logged out', NULL, NULL, '2025-05-13 08:08:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(406, 5, 'login', 'User logged in', NULL, NULL, '2025-05-13 08:08:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(407, 5, 'logout', 'User logged out', NULL, NULL, '2025-05-13 08:11:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(408, 2, 'login', 'User logged in', NULL, NULL, '2025-05-13 08:11:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(409, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-13 08:11:33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(410, 2, 'logout', 'User logged out', NULL, NULL, '2025-05-13 08:15:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(411, 5, 'login', 'User logged in', NULL, NULL, '2025-05-13 08:16:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(412, 5, 'logout', 'User logged out', NULL, NULL, '2025-05-13 08:41:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(413, 3, 'login', 'User logged in', NULL, NULL, '2025-05-13 08:41:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(414, 3, 'logout', 'User logged out', NULL, NULL, '2025-05-13 08:46:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(415, 5, 'login', 'User logged in', NULL, NULL, '2025-05-13 08:56:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(416, 5, 'logout', 'User logged out', NULL, NULL, '2025-05-13 09:15:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(417, 8, 'login', 'User logged in', NULL, NULL, '2025-05-13 09:51:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(418, 8, 'logout', 'User logged out', NULL, NULL, '2025-05-13 09:52:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(419, 5, 'login', 'User logged in', NULL, NULL, '2025-05-13 09:52:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(420, 5, 'logout', 'User logged out', NULL, NULL, '2025-05-13 09:59:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(421, 8, 'login', 'User logged in', NULL, NULL, '2025-05-13 09:59:48', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(422, 8, 'homepage', NULL, NULL, NULL, '2025-05-13 10:00:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(423, 8, 'logout', 'User logged out', NULL, NULL, '2025-05-13 11:34:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(424, NULL, 'homepage', NULL, NULL, NULL, '2025-05-13 11:34:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(425, 5, 'login', 'User logged in', NULL, NULL, '2025-05-13 11:46:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(426, 5, 'logout', 'User logged out', NULL, NULL, '2025-05-13 19:41:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(427, NULL, 'homepage', NULL, NULL, NULL, '2025-05-13 19:50:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(428, 2, 'login', 'User logged in', NULL, NULL, '2025-05-13 19:51:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(429, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-13 19:51:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(430, 2, 'homepage', NULL, NULL, NULL, '2025-05-13 19:51:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(431, 2, 'homepage', NULL, NULL, NULL, '2025-05-13 19:51:39', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(432, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-13 19:51:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(433, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-13 19:52:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(434, 2, 'homepage', NULL, NULL, NULL, '2025-05-13 20:05:28', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(435, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-13 20:05:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(436, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-14 07:17:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(437, 2, 'view_pet', '{\"pet_id\":2}', NULL, NULL, '2025-05-14 07:17:09', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(438, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":2,\"quantity\":1}', NULL, NULL, '2025-05-14 07:17:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(439, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-14 07:29:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(440, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-14 07:30:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(441, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":1,\"quantity\":1}', NULL, NULL, '2025-05-14 07:30:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(442, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-14 07:32:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(443, 2, 'view_pet', '{\"pet_id\":2}', NULL, NULL, '2025-05-14 07:32:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(444, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":2,\"quantity\":1}', NULL, NULL, '2025-05-14 07:32:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(445, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-14 07:35:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(446, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-14 07:35:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(447, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":1,\"quantity\":1}', NULL, NULL, '2025-05-14 07:35:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(448, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-14 08:06:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(449, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-14 08:06:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(450, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":1,\"quantity\":1}', NULL, NULL, '2025-05-14 08:06:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(451, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-14 10:21:29', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(452, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-14 10:21:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(453, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":1,\"quantity\":1}', NULL, NULL, '2025-05-14 10:21:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(454, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-14 10:22:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(455, 2, 'view_pet', '{\"pet_id\":2}', NULL, NULL, '2025-05-14 10:22:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(456, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":2,\"quantity\":1}', NULL, NULL, '2025-05-14 10:23:00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(457, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-14 10:26:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(458, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-14 10:26:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(459, 2, 'view_product', '{\"product_id\":2}', NULL, NULL, '2025-05-14 10:26:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(460, 2, 'add_to_cart', '{\"item_type\":\"product\",\"item_id\":2,\"quantity\":1}', NULL, NULL, '2025-05-14 10:27:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(461, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-14 10:27:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(462, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-14 10:37:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(463, 2, 'view_pet', '{\"pet_id\":2}', NULL, NULL, '2025-05-14 10:37:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(464, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":2,\"quantity\":1}', NULL, NULL, '2025-05-14 10:37:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(465, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-14 10:51:34', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(466, 2, 'view_pet', '{\"pet_id\":2}', NULL, NULL, '2025-05-14 10:51:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(467, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":2,\"quantity\":1}', NULL, NULL, '2025-05-14 10:51:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(468, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-14 11:20:18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(469, 2, 'view_pet', '{\"pet_id\":2}', NULL, NULL, '2025-05-14 11:20:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(470, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":2,\"quantity\":1}', NULL, NULL, '2025-05-14 11:20:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(471, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-14 11:23:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(472, 2, 'homepage', NULL, NULL, NULL, '2025-05-14 11:27:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(473, 2, 'homepage', NULL, NULL, NULL, '2025-05-14 11:29:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(474, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-14 11:29:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(475, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-14 11:29:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(476, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":1,\"quantity\":1}', NULL, NULL, '2025-05-14 11:29:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(477, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-14 11:31:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(478, 2, 'view_product', '{\"product_id\":2}', NULL, NULL, '2025-05-14 11:31:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(479, 2, 'add_to_cart', '{\"item_type\":\"product\",\"item_id\":2,\"quantity\":1}', NULL, NULL, '2025-05-14 11:31:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(480, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-15 06:45:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(481, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-15 06:46:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(482, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":1,\"quantity\":1}', NULL, NULL, '2025-05-15 06:46:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(483, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-15 07:01:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(484, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-15 07:01:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(485, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":1,\"quantity\":1}', NULL, NULL, '2025-05-15 07:01:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(486, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-15 07:20:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(487, 2, 'view_pet', '{\"pet_id\":2}', NULL, NULL, '2025-05-15 07:20:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(488, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":2,\"quantity\":1}', NULL, NULL, '2025-05-15 07:20:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(489, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-15 07:36:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36');
INSERT INTO `analytics` (`analytics_id`, `user_id`, `page_visited`, `action_type`, `item_type`, `item_id`, `visit_timestamp`, `ip_address`, `user_agent`) VALUES
(490, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-15 07:36:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(491, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":1,\"quantity\":1}', NULL, NULL, '2025-05-15 07:36:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(492, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-15 07:37:50', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(493, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-15 07:37:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(494, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":1,\"quantity\":1}', NULL, NULL, '2025-05-15 07:37:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(495, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-15 07:56:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(496, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-15 07:56:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(497, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":1,\"quantity\":1}', NULL, NULL, '2025-05-15 07:56:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(498, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-15 07:57:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(499, 2, 'view_product', '{\"product_id\":1}', NULL, NULL, '2025-05-15 07:57:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(500, 2, 'add_to_cart', '{\"item_type\":\"product\",\"item_id\":1,\"quantity\":1}', NULL, NULL, '2025-05-15 07:57:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(501, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-15 08:44:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(502, 2, 'view_pet', '{\"pet_id\":2}', NULL, NULL, '2025-05-15 08:44:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(503, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":2,\"quantity\":1}', NULL, NULL, '2025-05-15 08:44:34', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(504, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-15 08:53:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(505, 2, 'view_pet', '{\"pet_id\":2}', NULL, NULL, '2025-05-15 08:53:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(506, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":2,\"quantity\":1}', NULL, NULL, '2025-05-15 08:53:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(507, 2, 'logout', 'User logged out', NULL, NULL, '2025-05-15 09:10:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(508, 5, 'login', 'User logged in', NULL, NULL, '2025-05-15 09:10:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(509, 5, 'logout', 'User logged out', NULL, NULL, '2025-05-15 09:30:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(510, 5, 'login', 'User logged in', NULL, NULL, '2025-05-15 09:57:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(511, 5, 'logout', 'User logged out', NULL, NULL, '2025-05-15 19:54:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(512, 9, 'login', 'User logged in', NULL, NULL, '2025-05-15 19:55:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(513, 9, 'logout', 'User logged out', NULL, NULL, '2025-05-15 20:38:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(514, 5, 'login', 'User logged in', NULL, NULL, '2025-05-15 20:38:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(515, 5, 'logout', 'User logged out', NULL, NULL, '2025-05-16 07:01:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(516, NULL, 'homepage', NULL, NULL, NULL, '2025-05-16 07:02:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(517, 5, 'login', 'User logged in', NULL, NULL, '2025-05-16 08:46:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(518, 5, 'logout', 'User logged out', NULL, NULL, '2025-05-16 09:02:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(519, 5, 'login', 'User logged in', NULL, NULL, '2025-05-16 09:04:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(520, NULL, 'homepage', NULL, NULL, NULL, '2025-05-19 08:28:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(521, 5, 'login', 'User logged in', NULL, NULL, '2025-05-19 08:29:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(522, 5, 'logout', 'User logged out', NULL, NULL, '2025-05-19 08:48:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(523, 2, 'login', 'User logged in', NULL, NULL, '2025-05-19 08:48:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(524, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-19 08:48:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(525, 2, 'logout', 'User logged out', NULL, NULL, '2025-05-19 08:55:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(526, 5, 'login', 'User logged in', NULL, NULL, '2025-05-19 08:56:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(527, 5, 'logout', 'User logged out', NULL, NULL, '2025-05-19 10:05:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(528, 3, 'login', 'User logged in', NULL, NULL, '2025-05-19 10:05:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(529, 3, 'logout', 'User logged out', NULL, NULL, '2025-05-19 10:06:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(530, NULL, 'homepage', NULL, NULL, NULL, '2025-05-19 10:07:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(531, 2, 'login', 'User logged in', NULL, NULL, '2025-05-19 10:11:42', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(532, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-19 10:11:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(533, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-19 10:11:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(534, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-19 10:12:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(535, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-19 10:12:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(536, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-19 10:16:48', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(537, 2, 'homepage', NULL, NULL, NULL, '2025-05-19 16:01:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(538, 2, 'browse_pets', '{\"category_id\":1,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-19 16:01:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(539, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-19 16:01:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(540, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-19 16:03:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(541, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-19 20:02:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(542, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-19 20:03:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(543, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-19 20:03:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(544, 2, 'view_pet', '{\"pet_id\":2}', NULL, NULL, '2025-05-19 20:03:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(545, 2, 'submit_review', '{\"item_type\":\"pet\",\"item_id\":2,\"rating\":4}', NULL, NULL, '2025-05-19 20:15:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(546, 2, 'view_pet', '{\"pet_id\":2}', NULL, NULL, '2025-05-19 20:15:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(547, 2, 'view_pet', '{\"pet_id\":2}', NULL, NULL, '2025-05-19 20:15:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(548, 2, 'view_pet', '{\"pet_id\":2}', NULL, NULL, '2025-05-19 20:55:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(549, 2, 'view_pet', '{\"pet_id\":2}', NULL, NULL, '2025-05-20 10:37:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(550, 2, 'logout', 'User logged out', NULL, NULL, '2025-05-20 10:38:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(551, 3, 'login', 'User logged in', NULL, NULL, '2025-05-20 10:39:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(552, 3, 'logout', 'User logged out', NULL, NULL, '2025-05-20 10:39:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(553, 10, 'login', 'User logged in', NULL, NULL, '2025-05-20 10:41:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(554, 10, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-20 10:41:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(555, 10, 'view_pet', '{\"pet_id\":2}', NULL, NULL, '2025-05-20 10:41:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(556, 10, 'view_pet', '{\"pet_id\":2}', NULL, NULL, '2025-05-20 10:42:54', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1'),
(557, 10, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-20 10:43:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(558, 10, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-20 10:45:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(559, 10, 'logout', 'User logged out', NULL, NULL, '2025-05-20 10:47:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(560, 5, 'login', 'User logged in', NULL, NULL, '2025-05-20 10:48:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(561, 5, 'logout', 'User logged out', NULL, NULL, '2025-05-20 17:28:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(562, NULL, 'homepage', NULL, NULL, NULL, '2025-05-20 17:34:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(563, NULL, 'homepage', NULL, NULL, NULL, '2025-05-20 17:35:35', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1'),
(564, NULL, 'homepage', NULL, NULL, NULL, '2025-05-20 17:37:42', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(565, NULL, 'homepage', NULL, NULL, NULL, '2025-05-20 17:39:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(566, NULL, 'blog', NULL, NULL, NULL, '2025-05-20 18:02:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(567, 5, 'login', 'User logged in', NULL, NULL, '2025-05-20 18:34:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(568, 5, 'logout', 'User logged out', NULL, NULL, '2025-05-20 19:02:18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(569, NULL, 'homepage', NULL, NULL, NULL, '2025-05-20 19:02:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(570, NULL, 'blog', NULL, NULL, NULL, '2025-05-20 19:07:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(571, NULL, 'homepage', NULL, NULL, NULL, '2025-05-20 19:12:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(572, NULL, 'blog_post', '3', NULL, NULL, '2025-05-20 19:12:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(573, NULL, 'blog', NULL, NULL, NULL, '2025-05-20 19:12:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(574, NULL, 'blog', NULL, NULL, NULL, '2025-05-20 19:13:28', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(575, NULL, 'blog', NULL, NULL, NULL, '2025-05-20 19:14:47', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1'),
(576, NULL, 'blog', NULL, NULL, NULL, '2025-05-20 19:15:58', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1'),
(577, NULL, 'blog', NULL, NULL, NULL, '2025-05-20 19:17:07', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1'),
(578, NULL, 'blog', NULL, NULL, NULL, '2025-05-20 19:17:52', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1'),
(579, NULL, 'blog', NULL, NULL, NULL, '2025-05-20 19:18:34', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1'),
(580, NULL, 'blog', NULL, NULL, NULL, '2025-05-20 19:19:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(581, NULL, 'homepage', NULL, NULL, NULL, '2025-05-20 19:19:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(582, NULL, 'homepage', NULL, NULL, NULL, '2025-05-20 19:20:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(583, NULL, 'blog_post', '3', NULL, NULL, '2025-05-20 19:20:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(584, NULL, 'blog_post', '3', NULL, NULL, '2025-05-20 19:23:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(585, NULL, 'blog_post', '3', NULL, NULL, '2025-05-20 19:25:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(586, NULL, 'blog_post', '3', NULL, NULL, '2025-05-20 19:26:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(587, NULL, 'blog', NULL, NULL, NULL, '2025-05-20 19:26:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(588, NULL, 'blog', NULL, NULL, NULL, '2025-05-20 19:28:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(589, NULL, 'blog', NULL, NULL, NULL, '2025-05-20 19:29:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(590, NULL, 'homepage', NULL, NULL, NULL, '2025-05-20 19:29:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(591, NULL, 'blog_post', '3', NULL, NULL, '2025-05-20 19:29:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(592, NULL, 'blog_post', '3', NULL, NULL, '2025-05-20 19:35:39', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(593, NULL, 'blog', NULL, NULL, NULL, '2025-05-20 21:27:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(594, NULL, 'blog_search', '{\"query\":\"doberman\"}', NULL, NULL, '2025-05-20 21:33:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(595, NULL, 'blog_search', '{\"query\":\"doberman\"}', NULL, NULL, '2025-05-21 07:56:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(596, NULL, 'blog_search', '{\"query\":\"doberman\"}', NULL, NULL, '2025-05-21 08:00:09', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(597, NULL, 'blog', NULL, NULL, NULL, '2025-05-21 08:00:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(598, NULL, 'blog', NULL, NULL, NULL, '2025-05-21 08:01:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(599, NULL, 'newsletter_subscribe', NULL, '', NULL, '2025-05-21 09:32:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(600, NULL, 'blog', NULL, NULL, NULL, '2025-05-21 09:37:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(601, NULL, 'homepage', NULL, NULL, NULL, '2025-05-21 09:43:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(602, NULL, 'homepage', NULL, NULL, NULL, '2025-05-25 13:16:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(603, 2, 'login', 'User logged in', NULL, NULL, '2025-05-25 13:17:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(604, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-25 13:17:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(605, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-25 13:17:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(606, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-25 13:18:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(607, 2, 'logout', 'User logged out', NULL, NULL, '2025-05-25 13:20:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(608, 5, 'login', 'User logged in', NULL, NULL, '2025-05-27 06:55:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(609, 5, 'logout', 'User logged out', NULL, NULL, '2025-05-27 06:59:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(610, 2, 'login', 'User logged in', NULL, NULL, '2025-05-27 07:00:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(611, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-27 07:00:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(612, 2, 'view_product', '{\"product_id\":2}', NULL, NULL, '2025-05-27 07:00:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(613, 2, 'view_product', '{\"product_id\":2}', NULL, NULL, '2025-05-27 07:23:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(614, 2, 'view_product', '{\"product_id\":2}', NULL, NULL, '2025-05-27 07:28:18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(615, 2, 'view_product', '{\"product_id\":2}', NULL, NULL, '2025-05-27 07:29:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(616, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-27 07:31:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(617, 2, 'view_pet', '{\"pet_id\":2}', NULL, NULL, '2025-05-27 07:31:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(618, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-27 07:33:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(619, 2, 'view_product', '{\"product_id\":2}', NULL, NULL, '2025-05-27 07:33:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(620, 2, 'view_product', '{\"product_id\":2}', NULL, NULL, '2025-05-27 07:33:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(621, 2, 'view_product', '{\"product_id\":2}', NULL, NULL, '2025-05-27 07:35:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(622, 2, 'view_product', '{\"product_id\":2}', NULL, NULL, '2025-05-27 07:37:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(623, 2, 'view_product', '{\"product_id\":2}', NULL, NULL, '2025-05-27 07:37:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(624, 2, 'add_to_cart', '{\"item_type\":\"product\",\"item_id\":2,\"quantity\":1}', NULL, NULL, '2025-05-27 07:37:50', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(625, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-27 07:56:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(626, 2, 'view_product', '{\"product_id\":2}', NULL, NULL, '2025-05-27 07:56:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(627, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-27 07:57:18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(628, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-27 07:57:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(629, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":1,\"quantity\":1}', NULL, NULL, '2025-05-27 07:57:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(630, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-27 07:58:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(631, 2, 'view_product', '{\"product_id\":2}', NULL, NULL, '2025-05-27 07:58:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(632, 2, 'add_to_cart', '{\"item_type\":\"product\",\"item_id\":2,\"quantity\":1}', NULL, NULL, '2025-05-27 07:58:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(633, NULL, 'homepage', NULL, NULL, NULL, '2025-05-30 07:05:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(634, 2, 'login', 'User logged in', NULL, NULL, '2025-05-30 07:05:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(635, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-05-30 07:05:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(636, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-30 07:06:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(637, 2, 'view_pet', '{\"pet_id\":2}', NULL, NULL, '2025-05-30 07:06:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(638, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":2,\"quantity\":1}', NULL, NULL, '2025-05-30 07:06:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(639, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-30 07:10:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(640, 2, 'view_product', '{\"product_id\":2}', NULL, NULL, '2025-05-30 07:10:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(641, 2, 'add_to_cart', '{\"item_type\":\"product\",\"item_id\":2,\"quantity\":1}', NULL, NULL, '2025-05-30 07:10:33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(642, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-30 07:21:48', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(643, 2, 'view_pet', '{\"pet_id\":2}', NULL, NULL, '2025-05-30 07:21:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(644, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":2,\"quantity\":1}', NULL, NULL, '2025-05-30 07:21:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(645, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-30 07:26:09', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(646, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-30 07:26:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(647, 2, 'view_product', '{\"product_id\":1}', NULL, NULL, '2025-05-30 07:26:18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(648, 2, 'add_to_cart', '{\"item_type\":\"product\",\"item_id\":1,\"quantity\":1}', NULL, NULL, '2025-05-30 07:26:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(649, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-30 07:28:50', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(650, 2, 'view_pet', '{\"pet_id\":2}', NULL, NULL, '2025-05-30 07:28:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(651, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":2,\"quantity\":1}', NULL, NULL, '2025-05-30 07:28:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(652, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-30 07:32:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(653, 2, 'view_product', '{\"product_id\":2}', NULL, NULL, '2025-05-30 07:32:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(654, 2, 'view_product', '{\"product_id\":2}', NULL, NULL, '2025-05-30 07:32:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(655, 2, 'add_to_cart', '{\"item_type\":\"product\",\"item_id\":2,\"quantity\":1}', NULL, NULL, '2025-05-30 07:32:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(656, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-30 07:32:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(657, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-30 07:32:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(658, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":1,\"quantity\":1}', NULL, NULL, '2025-05-30 07:33:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(659, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-30 07:34:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(660, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-30 07:49:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(661, 2, 'view_pet', '{\"pet_id\":2}', NULL, NULL, '2025-05-30 07:49:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(662, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":2,\"quantity\":1}', NULL, NULL, '2025-05-30 07:49:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(663, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-30 08:43:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(664, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-30 08:43:29', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(665, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":1,\"quantity\":1}', NULL, NULL, '2025-05-30 08:43:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(666, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-30 08:53:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(667, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-30 08:53:28', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(668, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":1,\"quantity\":1}', NULL, NULL, '2025-05-30 08:53:34', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(669, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-30 08:53:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(670, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-05-30 08:54:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(671, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":1,\"quantity\":1}', NULL, NULL, '2025-05-30 08:54:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(672, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-30 09:13:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(673, 2, 'view_pet', '{\"pet_id\":2}', NULL, NULL, '2025-05-30 09:13:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(674, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":2,\"quantity\":1}', NULL, NULL, '2025-05-30 09:13:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(675, 2, 'browse_pets', '{\"category_id\":1,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-31 10:11:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(676, 2, 'browse_pets', '{\"category_id\":2,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-05-31 10:11:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(677, 2, 'browse_pets', '{\"category_id\":2,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-01 07:08:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(678, 2, 'view_pet', '{\"pet_id\":2}', NULL, NULL, '2025-06-01 07:09:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(679, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":2,\"quantity\":1}', NULL, NULL, '2025-06-01 07:09:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(680, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-01 07:09:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(681, 2, 'view_pet', '{\"pet_id\":2}', NULL, NULL, '2025-06-01 07:09:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(682, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":2,\"quantity\":1}', NULL, NULL, '2025-06-01 07:09:50', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(683, 2, 'logout', 'User logged out', NULL, NULL, '2025-06-01 07:11:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(684, 5, 'login', 'User logged in', NULL, NULL, '2025-06-01 07:12:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(685, 5, 'logout', 'User logged out', NULL, NULL, '2025-06-01 07:18:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(686, 5, 'login', 'User logged in', NULL, NULL, '2025-06-01 07:18:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(687, NULL, 'homepage', NULL, NULL, NULL, '2025-06-02 10:31:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(688, 2, 'login', 'User logged in', NULL, NULL, '2025-06-02 10:32:28', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(689, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-02 10:32:29', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(690, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-02 10:32:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(691, 2, 'view_product', '{\"product_id\":1}', NULL, NULL, '2025-06-02 10:32:42', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(692, 2, 'add_to_cart', '{\"item_type\":\"product\",\"item_id\":1,\"quantity\":1}', NULL, NULL, '2025-06-02 10:32:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(693, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-02 10:33:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(694, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-02 10:39:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(695, 2, 'homepage', NULL, NULL, NULL, '2025-06-02 10:40:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(696, NULL, 'blog_post', '3', NULL, NULL, '2025-06-02 10:40:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(697, 2, 'blog_search', '{\"query\":\"doberman\"}', NULL, NULL, '2025-06-02 10:42:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(698, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-02 10:42:34', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(699, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"doberman\"}', NULL, NULL, '2025-06-02 10:42:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(700, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-02 10:45:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(701, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-02 10:45:18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(702, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-02 10:45:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(703, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-02 10:45:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(704, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-02 10:45:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(705, 2, 'browse_pets', '{\"category_id\":2,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-02 10:46:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(706, 2, 'browse_pets', '{\"category_id\":2,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-02 10:53:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(707, 2, 'browse_pets', '{\"category_id\":2,\"county\":\"\",\"search\":\"doberman\"}', NULL, NULL, '2025-06-02 10:53:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(708, 2, 'browse_pets', '{\"category_id\":1,\"county\":\"\",\"search\":\"doberman\"}', NULL, NULL, '2025-06-02 10:53:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(709, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"doberman\"}', NULL, NULL, '2025-06-02 10:53:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(710, 2, 'browse_pets', '{\"category_id\":2,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-02 10:54:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(711, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"Kisumu\",\"search\":\"\"}', NULL, NULL, '2025-06-02 10:54:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(712, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-02 10:54:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(713, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-02 10:55:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(714, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-02 10:55:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(715, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-02 10:55:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(716, 2, 'browse_products', '{\"category_id\":0,\"county\":\"Kisumu\",\"search\":\"\"}', NULL, NULL, '2025-06-02 10:55:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(717, 2, 'browse_products', '{\"category_id\":2,\"county\":\"Kisumu\",\"search\":\"\"}', NULL, NULL, '2025-06-02 10:55:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(718, 2, 'browse_products', '{\"category_id\":2,\"county\":\"Kisumu\",\"search\":\"\"}', NULL, NULL, '2025-06-02 11:27:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(719, 2, 'browse_products', '{\"category_id\":2,\"county\":\"Kisumu\",\"search\":\"\"}', NULL, NULL, '2025-06-02 11:29:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(720, 2, 'browse_products', '{\"category_id\":13,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-02 11:32:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(721, 2, 'browse_products', '{\"category_id\":13,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-02 11:34:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(722, 2, 'browse_products', '{\"category_id\":13,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-02 11:36:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(723, 2, 'homepage', NULL, NULL, NULL, '2025-06-02 18:35:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(724, 2, 'homepage', NULL, NULL, NULL, '2025-06-03 08:09:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(725, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-03 08:11:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(726, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-03 08:12:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36');
INSERT INTO `analytics` (`analytics_id`, `user_id`, `page_visited`, `action_type`, `item_type`, `item_id`, `visit_timestamp`, `ip_address`, `user_agent`) VALUES
(727, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-03 08:14:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(728, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-03 08:17:48', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(729, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-03 08:20:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(730, 2, 'logout', 'User logged out', NULL, NULL, '2025-06-03 08:21:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(731, 3, 'login', 'User logged in', NULL, NULL, '2025-06-03 08:21:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(732, 3, 'logout', 'User logged out', NULL, NULL, '2025-06-03 08:34:34', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(733, 2, 'login', 'User logged in', NULL, NULL, '2025-06-03 08:34:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(734, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-03 08:34:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(735, 2, 'logout', 'User logged out', NULL, NULL, '2025-06-03 08:40:18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(736, 5, 'login', 'User logged in', NULL, NULL, '2025-06-03 08:40:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(737, 5, 'logout', 'User logged out', NULL, NULL, '2025-06-03 11:50:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'),
(738, 5, 'login', 'User logged in', NULL, NULL, '2025-06-03 18:47:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(739, 5, 'logout', 'User logged out', NULL, NULL, '2025-06-03 18:57:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(740, 5, 'login', 'User logged in', NULL, NULL, '2025-06-03 18:58:42', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(741, 5, 'logout', 'User logged out', NULL, NULL, '2025-06-04 09:02:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(742, NULL, 'homepage', NULL, NULL, NULL, '2025-06-04 09:02:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(743, NULL, 'homepage', NULL, NULL, NULL, '2025-06-04 09:11:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(744, NULL, 'homepage', NULL, NULL, NULL, '2025-06-04 09:13:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(745, NULL, 'homepage', NULL, NULL, NULL, '2025-06-04 09:14:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(746, NULL, 'homepage', NULL, NULL, NULL, '2025-06-04 09:17:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(747, NULL, 'homepage', NULL, NULL, NULL, '2025-06-04 09:17:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(748, NULL, 'homepage', NULL, NULL, NULL, '2025-06-04 09:23:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(749, 2, 'login', 'User logged in', NULL, NULL, '2025-06-04 09:23:34', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(750, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 09:23:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(751, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 09:25:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(752, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 09:26:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(753, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 09:36:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(754, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 09:38:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(755, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 09:42:34', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(756, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 09:45:42', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(757, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 09:46:18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(758, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 09:47:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(759, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 09:48:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(760, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 09:48:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(761, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 09:49:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(762, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 09:50:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(763, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 09:50:33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(764, 2, 'browse_pets', '{\"category_id\":7,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-04 09:52:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(765, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-04 09:52:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(766, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 09:54:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(767, 2, 'homepage', NULL, NULL, NULL, '2025-06-04 09:54:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(768, 2, 'browse_pets', '{\"category_id\":7,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-04 10:02:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(769, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 10:02:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(770, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 10:03:18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(771, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 10:03:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(772, 2, 'homepage', NULL, NULL, NULL, '2025-06-04 10:04:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(773, 2, 'browse_pets', '{\"category_id\":1,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-04 10:05:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(774, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-04 10:07:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(775, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 10:07:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(776, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 10:28:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(777, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 10:29:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(778, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 10:33:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(779, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 10:37:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(780, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 10:37:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(781, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 10:43:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(782, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 10:44:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(783, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 10:44:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(784, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 10:45:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(785, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 10:45:34', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(786, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 10:46:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(787, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 10:47:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(788, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 10:48:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(789, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 10:50:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(790, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 10:50:50', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(791, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 10:51:00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(792, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 10:51:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(793, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 10:51:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(794, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 10:52:09', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(795, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 10:52:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(796, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 10:52:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(797, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 10:52:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(798, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 10:58:34', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(799, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 10:59:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(800, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 10:59:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(801, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 10:59:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(802, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 11:00:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(803, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 11:00:50', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(804, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 11:01:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(805, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 11:06:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(806, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 11:06:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(807, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 11:07:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(808, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-04 11:07:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(809, 2, 'view_product', '{\"product_id\":2}', NULL, NULL, '2025-06-04 11:07:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(810, 2, 'add_to_wishlist', '{\"item_type\":\"product\",\"item_id\":2}', NULL, NULL, '2025-06-04 11:08:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(811, 2, 'view_product', '{\"product_id\":2}', NULL, NULL, '2025-06-04 11:08:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(812, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 11:09:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(813, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 11:09:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(814, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 11:10:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(815, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 11:10:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(816, 2, 'homepage', NULL, NULL, NULL, '2025-06-04 11:11:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(817, 2, 'homepage', NULL, NULL, NULL, '2025-06-04 11:11:48', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(818, 2, 'browse_pets', '{\"category_id\":7,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-04 11:16:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(819, 2, 'browse_pets', '{\"category_id\":7,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-04 11:17:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(820, 2, 'browse_pets', '{\"category_id\":7,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-04 11:24:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(821, 2, 'browse_pets', '{\"category_id\":7,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-04 11:24:42', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(822, 2, 'browse_pets', '{\"category_id\":7,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-04 11:26:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(823, 2, 'browse_pets', '{\"category_id\":7,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-04 11:27:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(824, 2, 'browse_pets', '{\"category_id\":7,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-04 11:27:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(825, 2, 'browse_pets', '{\"category_id\":7,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-04 11:28:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(826, 2, 'browse_pets', '{\"category_id\":7,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-04 11:29:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(827, 2, 'browse_pets', '{\"category_id\":7,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-04 11:29:39', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(828, 2, 'browse_pets', '{\"category_id\":7,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-04 11:31:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(829, 2, 'browse_pets', '{\"category_id\":7,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-04 11:32:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(830, 2, 'browse_pets', '{\"category_id\":7,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-04 11:32:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(831, 2, 'browse_pets', '{\"category_id\":7,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-04 11:33:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(832, 2, 'browse_pets', '{\"category_id\":7,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-04 16:37:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(833, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-04 16:37:18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(834, 2, 'homepage', NULL, NULL, NULL, '2025-06-04 16:38:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(835, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-04 16:39:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(836, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-04 16:40:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(837, 2, 'logout', 'User logged out', NULL, NULL, '2025-06-04 16:53:39', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(838, 3, 'login', 'User logged in', NULL, NULL, '2025-06-04 16:54:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(839, 3, 'logout', 'User logged out', NULL, NULL, '2025-06-05 11:59:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(840, 2, 'login', 'User logged in', NULL, NULL, '2025-06-05 11:59:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(841, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-05 11:59:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(842, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-05 12:00:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(843, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-05 12:00:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(844, 2, 'logout', 'User logged out', NULL, NULL, '2025-06-05 12:01:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(845, 3, 'login', 'User logged in', NULL, NULL, '2025-06-05 12:01:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(846, 3, 'logout', 'User logged out', NULL, NULL, '2025-06-05 12:02:39', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(847, 2, 'login', 'User logged in', NULL, NULL, '2025-06-05 12:02:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(848, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-05 12:02:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(849, 2, 'homepage', NULL, NULL, NULL, '2025-06-05 12:51:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(850, 2, 'homepage', NULL, NULL, NULL, '2025-06-05 12:54:09', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(851, 2, 'homepage', NULL, NULL, NULL, '2025-06-05 12:58:09', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(852, 2, 'homepage', NULL, NULL, NULL, '2025-06-05 13:00:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(853, 2, 'homepage', NULL, NULL, NULL, '2025-06-05 13:03:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(854, 2, 'homepage', NULL, NULL, NULL, '2025-06-05 13:04:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(855, 2, 'browse_products', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-05 13:04:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(856, 2, 'homepage', NULL, NULL, NULL, '2025-06-05 13:04:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(857, 2, 'homepage', NULL, NULL, NULL, '2025-06-05 13:05:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(858, 2, 'browse_pets', '{\"category_id\":7,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-05 13:09:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(859, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-05 13:09:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(860, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-05 13:10:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(861, 2, 'logout', 'User logged out', NULL, NULL, '2025-06-06 01:19:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(862, 2, 'login', 'User logged in', NULL, NULL, '2025-06-06 01:23:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(863, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-06 01:24:00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(864, 2, 'logout', 'User logged out', NULL, NULL, '2025-06-06 02:16:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(865, NULL, 'homepage', NULL, NULL, NULL, '2025-06-06 02:16:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(866, NULL, 'homepage', NULL, NULL, NULL, '2025-06-06 02:18:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(867, NULL, 'homepage', NULL, NULL, NULL, '2025-06-06 02:19:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(868, NULL, 'homepage', NULL, NULL, NULL, '2025-06-06 02:20:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(869, NULL, 'homepage', NULL, NULL, NULL, '2025-06-06 02:20:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(870, NULL, 'homepage', NULL, NULL, NULL, '2025-06-06 02:20:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(871, NULL, 'homepage', NULL, NULL, NULL, '2025-06-06 02:20:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(872, 2, 'login', 'User logged in', NULL, NULL, '2025-06-06 02:20:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(873, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-06 02:20:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(874, 2, 'homepage', NULL, NULL, NULL, '2025-06-06 02:21:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(875, 2, 'homepage', NULL, NULL, NULL, '2025-06-06 02:21:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(876, 2, 'browse_pets', '{\"category_id\":1,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-06 02:21:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(877, 2, 'homepage', NULL, NULL, NULL, '2025-06-06 02:23:18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(878, 2, 'homepage', NULL, NULL, NULL, '2025-06-06 02:24:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(879, 2, 'homepage', NULL, NULL, NULL, '2025-06-06 02:24:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(880, 2, 'homepage', NULL, NULL, NULL, '2025-06-06 02:26:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(881, 2, 'browse_pets', '{\"category_id\":7,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-06 02:26:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(882, 2, 'homepage', NULL, NULL, NULL, '2025-06-06 02:26:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(883, 2, 'homepage', NULL, NULL, NULL, '2025-06-06 02:28:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(884, 2, 'homepage', NULL, NULL, NULL, '2025-06-06 02:29:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(885, 2, 'homepage', NULL, NULL, NULL, '2025-06-06 02:29:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(886, 2, 'blog', NULL, NULL, NULL, '2025-06-06 02:29:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(887, 2, 'blog', NULL, NULL, NULL, '2025-06-06 02:30:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(888, 2, 'homepage', NULL, NULL, NULL, '2025-06-06 02:35:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(889, 2, 'homepage', NULL, NULL, NULL, '2025-06-06 02:36:29', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(890, 2, 'logout', 'User logged out', NULL, NULL, '2025-06-06 02:36:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(891, NULL, 'homepage', NULL, NULL, NULL, '2025-06-06 02:36:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(892, 3, 'login', 'User logged in', NULL, NULL, '2025-06-06 02:37:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(893, 3, 'logout', 'User logged out', NULL, NULL, '2025-06-06 02:43:18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(894, 2, 'login', 'User logged in', NULL, NULL, '2025-06-06 02:43:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(895, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-06 02:43:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(896, 2, 'logout', 'User logged out', NULL, NULL, '2025-06-06 02:51:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(897, 3, 'login', 'User logged in', NULL, NULL, '2025-06-06 02:51:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(898, NULL, 'homepage', NULL, NULL, NULL, '2025-06-06 16:41:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(899, 2, 'login', 'User logged in', NULL, NULL, '2025-06-06 16:41:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(900, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-06 16:41:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(901, 2, 'logout', 'User logged out', NULL, NULL, '2025-06-06 16:43:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(902, NULL, 'homepage', NULL, NULL, NULL, '2025-06-06 16:43:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(903, NULL, 'homepage', NULL, NULL, NULL, '2025-06-07 12:39:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(904, 2, 'login', 'User logged in', NULL, NULL, '2025-06-07 12:40:33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(905, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-06-07 12:40:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(906, 2, 'browse_pets', '{\"category_id\":0,\"county\":\"\",\"search\":\"\"}', NULL, NULL, '2025-06-07 12:40:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(907, 2, 'view_pet', '{\"pet_id\":1}', NULL, NULL, '2025-06-07 12:40:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(908, 2, 'add_to_cart', '{\"item_type\":\"pet\",\"item_id\":1,\"quantity\":1}', NULL, NULL, '2025-06-07 12:40:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(909, 2, 'logout', 'User logged out', NULL, NULL, '2025-06-07 12:41:42', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(910, 3, 'login', 'User logged in', NULL, NULL, '2025-06-07 12:42:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(911, 3, 'logout', 'User logged out', NULL, NULL, '2025-06-07 12:43:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(912, 5, 'login', 'User logged in', NULL, NULL, '2025-06-07 12:44:00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(913, 5, 'logout', 'User logged out', NULL, NULL, '2025-06-07 12:46:29', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(914, NULL, 'homepage', NULL, NULL, NULL, '2025-07-01 12:21:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(915, 2, 'login', 'User logged in', NULL, NULL, '2025-07-01 12:23:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(916, 2, 'buyer_dashboard', NULL, NULL, NULL, '2025-07-01 12:23:28', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(917, 2, 'logout', 'User logged out', NULL, NULL, '2025-07-01 12:24:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(918, NULL, 'homepage', NULL, NULL, NULL, '2025-07-01 12:31:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(919, NULL, 'blog', NULL, NULL, NULL, '2025-07-01 12:31:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36');

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

CREATE TABLE `blog_posts` (
  `post_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `categories` varchar(255) DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `published_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('draft','published') DEFAULT 'draft',
  `views` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blog_posts`
--

INSERT INTO `blog_posts` (`post_id`, `admin_id`, `title`, `content`, `categories`, `featured_image`, `published_date`, `status`, `views`) VALUES
(3, 5, 'Doberman Care 101: Raising a Loyal and Healthy Guardian', '<p class=\"\" data-start=\"166\" data-end=\"502\">The Doberman Pinscher, often simply called the Doberman, is a strikingly elegant and intelligent breed known for its loyalty, strength, and fearless nature. Whether you\'re a first-time Doberman owner or considering adding one to your family, understanding their care needs is essential for raising a healthy and well-adjusted companion.</p>\r\n<hr class=\"\" data-start=\"504\" data-end=\"507\">\r\n<h4 class=\"\" data-start=\"509\" data-end=\"559\"><strong data-start=\"514\" data-end=\"559\">1. Understanding the Doberman Temperament</strong></h4>\r\n<p class=\"\" data-start=\"560\" data-end=\"937\">Dobermans are known for their loyalty and protective instincts. They are naturally alert and often form strong bonds with their families. With proper training and socialization, Dobermans are gentle, affectionate, and reliable pets. However, due to their guarding nature, early exposure to people, pets, and different environments is crucial to prevent overprotective behavior.</p>\r\n<hr class=\"\" data-start=\"939\" data-end=\"942\">\r\n<h4 class=\"\" data-start=\"944\" data-end=\"974\"><strong data-start=\"949\" data-end=\"974\">2. Nutrition and Diet</strong></h4>\r\n<p class=\"\" data-start=\"975\" data-end=\"1379\">A Doberman thrives on a high-quality, well-balanced diet. Choose dog food that is rich in protein and contains healthy fats, vitamins, and minerals. Due to their active lifestyle, Dobermans need enough calories to sustain their energy levels, but avoid overfeeding to prevent obesity. Always provide fresh water and consider consulting your vet about supplements for joint health, especially as they age.</p>\r\n<hr class=\"\" data-start=\"1381\" data-end=\"1384\">\r\n<h4 class=\"\" data-start=\"1386\" data-end=\"1429\"><strong data-start=\"1391\" data-end=\"1429\">3. Exercise and Mental Stimulation</strong></h4>\r\n<p class=\"\" data-start=\"1430\" data-end=\"1767\">Dobermans are high-energy dogs that require daily physical exercise and mental stimulation. At least one to two hours of activity each day&mdash;such as walks, runs, playtime, or agility training&mdash;is ideal. Mental enrichment like puzzle toys, obedience training, and scent games can help prevent boredom, which can lead to destructive behavior.</p>\r\n<hr class=\"\" data-start=\"1769\" data-end=\"1772\">\r\n<h4 class=\"\" data-start=\"1774\" data-end=\"1800\"><strong data-start=\"1779\" data-end=\"1800\">4. Grooming Needs</strong></h4>\r\n<p class=\"\" data-start=\"1801\" data-end=\"2161\">Dobermans have short, smooth coats that are relatively low maintenance. Weekly brushing with a soft bristle brush helps keep their coat shiny and removes loose hair. Regularly check and clean their ears, trim their nails, and brush their teeth to maintain overall hygiene. They don&rsquo;t need frequent baths&mdash;only when they get particularly dirty or start to smell.</p>\r\n<hr class=\"\" data-start=\"2163\" data-end=\"2166\">\r\n<h4 class=\"\" data-start=\"2168\" data-end=\"2206\"><strong data-start=\"2173\" data-end=\"2206\">5. Health and Veterinary Care</strong></h4>\r\n<p class=\"\" data-start=\"2207\" data-end=\"2288\">Dobermans are generally healthy dogs but are prone to certain conditions such as:</p>\r\n<ul data-start=\"2289\" data-end=\"2540\">\r\n<li class=\"\" data-start=\"2289\" data-end=\"2372\">\r\n<p class=\"\" data-start=\"2291\" data-end=\"2372\"><strong data-start=\"2291\" data-end=\"2323\">Dilated Cardiomyopathy (DCM)</strong> &ndash; a serious heart condition common in the breed.</p>\r\n</li>\r\n<li class=\"\" data-start=\"2373\" data-end=\"2432\">\r\n<p class=\"\" data-start=\"2375\" data-end=\"2432\"><strong data-start=\"2375\" data-end=\"2403\">Von Willebrand\'s Disease</strong> &ndash; a blood clotting disorder.</p>\r\n</li>\r\n<li class=\"\" data-start=\"2433\" data-end=\"2480\">\r\n<p class=\"\" data-start=\"2435\" data-end=\"2480\"><strong data-start=\"2435\" data-end=\"2452\">Hip Dysplasia</strong> &ndash; especially in older dogs.</p>\r\n</li>\r\n<li class=\"\" data-start=\"2481\" data-end=\"2540\">\r\n<p class=\"\" data-start=\"2483\" data-end=\"2540\"><strong data-start=\"2483\" data-end=\"2501\">Hypothyroidism</strong> &ndash; leading to weight gain and lethargy.</p>\r\n</li>\r\n</ul>\r\n<p class=\"\" data-start=\"2542\" data-end=\"2691\">Regular veterinary checkups, vaccinations, heartworm prevention, and annual screenings are vital for early detection and management of health issues.</p>\r\n<hr class=\"\" data-start=\"2693\" data-end=\"2696\">\r\n<h4 class=\"\" data-start=\"2698\" data-end=\"2736\"><strong data-start=\"2703\" data-end=\"2736\">6. Training and Socialization</strong></h4>\r\n<p class=\"\" data-start=\"2737\" data-end=\"3022\">Training a Doberman should begin early. They are intelligent and eager to please but can be strong-willed. Use positive reinforcement methods, consistency, and patience. Early obedience classes and exposure to different environments and people will help shape a well-behaved adult dog.</p>\r\n<hr class=\"\" data-start=\"3024\" data-end=\"3027\">\r\n<h4 class=\"\" data-start=\"3029\" data-end=\"3059\"><strong data-start=\"3034\" data-end=\"3059\">7. Living Environment</strong></h4>\r\n<p class=\"\" data-start=\"3060\" data-end=\"3377\">Dobermans are indoor dogs that thrive best when they live closely with their families. They are sensitive to cold due to their short coats, so they should not be left outside for long periods, especially in cold weather. They do well in homes with a yard but can also adapt to apartment living with adequate exercise.</p>\r\n<hr class=\"\" data-start=\"3379\" data-end=\"3382\">\r\n<h3 class=\"\" data-start=\"3384\" data-end=\"3406\"><strong data-start=\"3388\" data-end=\"3406\">Final Thoughts</strong></h3>\r\n<p class=\"\" data-start=\"3407\" data-end=\"3706\">Caring for a Doberman is a rewarding experience for responsible and committed owners. With proper training, care, and affection, a Doberman becomes a loyal guardian and a loving family member. Their intelligence, elegance, and devotion make them one of the most respected breeds in the canine world.</p>', NULL, 'uploads/blog/blog_1747767724.jpeg', '2025-05-20 19:02:04', 'published', 8);

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `cart_item_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_type` enum('pet','product') NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `description`, `parent_id`, `image`, `is_active`) VALUES
(1, 'Dogs', 'All dog breeds and puppies', NULL, NULL, 1),
(2, 'Cats', 'All cat breeds and kittens', NULL, NULL, 1),
(3, 'Birds', 'Pet birds including parrots, finches, and more', NULL, NULL, 1),
(4, 'Fish', 'Aquarium fish', NULL, NULL, 1),
(5, 'Small Pets', 'Hamsters, rabbits, guinea pigs, and other small animals', NULL, NULL, 1),
(6, 'Reptiles', 'Snakes, lizards, turtles and more', NULL, NULL, 1),
(7, 'Pet Food', 'Food for all types of pets', NULL, NULL, 1),
(8, 'Pet Accessories', 'Toys, beds, cages and more', NULL, NULL, 1),
(9, 'Grooming', 'Pet grooming products', NULL, NULL, 1),
(10, 'German Shepherd', 'German Shepherd dogs and puppies', 1, NULL, 1),
(11, 'Rottweiler', 'Rottweiler dogs and puppies', 1, NULL, 1),
(12, 'Labrador', 'Labrador Retrievers', 1, NULL, 1),
(13, 'Poodle', 'Poodles of all sizes', 1, NULL, 1),
(14, 'Local Breeds', 'Mixed-breed and indigenous dogs', 1, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('unread','read','responded') DEFAULT 'unread'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact`
--

INSERT INTO `contact` (`id`, `name`, `email`, `subject`, `message`, `sender_id`, `created_at`, `status`) VALUES
(1, 'Noel Omondi Oigo', 'noelomondi015@gmail.com', 'Inquiry about Butch', 'the order is still pending', NULL, '2025-05-15 09:45:56', 'read');

-- --------------------------------------------------------

--
-- Table structure for table `counties`
--

CREATE TABLE `counties` (
  `county_id` int(11) NOT NULL,
  `county_name` varchar(50) NOT NULL,
  `region` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `counties`
--

INSERT INTO `counties` (`county_id`, `county_name`, `region`) VALUES
(1, 'Nairobi', 'Nairobi'),
(2, 'Mombasa', 'Coast'),
(3, 'Kisumu', 'Nyanza'),
(4, 'Nakuru', 'Rift Valley'),
(5, 'Eldoret', 'Rift Valley'),
(6, 'Machakos', 'Eastern'),
(7, 'Nyeri', 'Central'),
(8, 'Kakamega', 'Western'),
(9, 'Garissa', 'North Eastern'),
(10, 'Lamu', 'Coast');

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE `images` (
  `image_id` int(11) NOT NULL,
  `item_type` enum('pet','product') NOT NULL,
  `item_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `images`
--

INSERT INTO `images` (`image_id`, `item_type`, `item_id`, `image_path`, `is_primary`, `created_at`) VALUES
(1, 'pet', 1, 'uploads/pets/1746600173_doberman.jpeg', 1, '2025-05-07 06:42:53'),
(2, 'pet', 1, 'uploads/pets/1746600173_doberman2.jpeg', 0, '2025-05-07 06:42:53'),
(3, 'pet', 1, 'uploads/pets/1746600173_doberman3.jpeg', 0, '2025-05-07 06:42:53'),
(4, 'product', 1, 'uploads/products/1746693280_IMG_0637.webp', 1, '2025-05-08 08:34:40'),
(5, 'product', 1, 'uploads/products/1746693280_Royal_Canin_Medium_Adult_Gravy-0__89671.png', 0, '2025-05-08 08:34:40'),
(6, 'product', 1, 'uploads/products/1746693280_wzvwiglwha4p4filcj82.webp', 0, '2025-05-08 08:34:40'),
(7, 'pet', 2, 'uploads/pets/1746732497_Maine Coon3.avif', 1, '2025-05-08 19:28:17'),
(8, 'pet', 2, 'uploads/pets/1746732497_Maine Coon2.jpeg', 0, '2025-05-08 19:28:17'),
(9, 'pet', 2, 'uploads/pets/1746732497_Maine Coon1.jpg', 0, '2025-05-08 19:28:17'),
(10, 'product', 2, 'uploads/products/1746733066_61d1w+evo5L._AC_UL495_SR435,495_.jpg', 1, '2025-05-08 19:37:46'),
(11, 'product', 2, 'uploads/products/1746733066_67824.jpg', 0, '2025-05-08 19:37:46'),
(12, 'product', 2, 'uploads/products/1746733066_PersianKitten-9_1200x1200.webp', 0, '2025-05-08 19:37:46');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `related_to_item_type` enum('pet','product','order','general') DEFAULT 'general',
  `related_to_item_id` int(11) DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_status` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `sender_id`, `receiver_id`, `subject`, `message`, `related_to_item_type`, `related_to_item_id`, `sent_at`, `read_status`) VALUES
(1, 2, 3, 'Inquiry about Butch', 'i want him', 'general', NULL, '2025-05-08 08:15:47', 1),
(2, 3, 2, NULL, 'owkay just pay or do you prefer payment on delivery?', 'general', NULL, '2025-05-08 08:17:17', 1),
(3, 2, 3, '', 'i will pay on delivery', 'general', NULL, '2025-05-08 08:18:36', 1);

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_campaigns`
--

CREATE TABLE `newsletter_campaigns` (
  `campaign_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `sent_date` datetime DEFAULT NULL,
  `status` enum('draft','scheduled','sent','cancelled') DEFAULT 'draft',
  `recipient_count` int(11) DEFAULT 0,
  `open_count` int(11) DEFAULT 0,
  `click_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `subscriber_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `subscription_date` datetime NOT NULL,
  `status` enum('active','unsubscribed','bounced') DEFAULT 'active',
  `interests` varchar(255) DEFAULT NULL,
  `confirmation_token` varchar(64) DEFAULT NULL,
  `confirmed` tinyint(1) DEFAULT 0,
  `unsubscribe_token` varchar(64) DEFAULT NULL,
  `last_email_sent` datetime DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL COMMENT 'Where the subscription originated from',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `newsletter_subscribers`
--

INSERT INTO `newsletter_subscribers` (`subscriber_id`, `email`, `first_name`, `last_name`, `subscription_date`, `status`, `interests`, `confirmation_token`, `confirmed`, `unsubscribe_token`, `last_email_sent`, `source`, `created_at`, `updated_at`) VALUES
(1, 'noelomondi015@gmail.com', NULL, NULL, '2025-05-21 12:32:27', 'active', NULL, NULL, 0, NULL, NULL, NULL, '2025-05-21 09:32:27', '2025-05-21 09:32:27');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_tracking`
--

CREATE TABLE `newsletter_tracking` (
  `tracking_id` int(11) NOT NULL,
  `subscriber_id` int(11) NOT NULL,
  `campaign_id` int(11) NOT NULL,
  `opened` tinyint(1) DEFAULT 0,
  `opened_at` datetime DEFAULT NULL,
  `clicked` tinyint(1) DEFAULT 0,
  `clicked_at` datetime DEFAULT NULL,
  `click_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','completed','cancelled') DEFAULT 'pending',
  `payment_method` enum('mpesa','credit_card','cash_on_delivery','pesapal') DEFAULT NULL,
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `shipping_address` text DEFAULT NULL,
  `shipping_county` varchar(50) DEFAULT NULL,
  `contact_phone` varchar(15) DEFAULT NULL,
  `transaction_reference` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `buyer_id`, `order_date`, `total_amount`, `status`, `payment_method`, `payment_status`, `shipping_address`, `shipping_county`, `contact_phone`, `transaction_reference`) VALUES
(1, 2, '2025-05-08 19:05:19', 29550.00, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(2, 2, '2025-05-08 19:06:03', 29550.00, 'pending', 'cash_on_delivery', 'pending', NULL, NULL, NULL, NULL),
(3, 2, '2025-05-14 06:55:55', 10649.99, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(4, 2, '2025-05-14 07:17:18', 10649.99, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(5, 2, '2025-05-14 07:28:02', 10649.99, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(6, 2, '2025-05-14 07:30:08', 17150.00, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(7, 2, '2025-05-14 07:32:11', 10649.99, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(8, 2, '2025-05-14 07:36:00', 17150.00, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(9, 2, '2025-05-14 08:06:36', 17150.00, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(10, 2, '2025-05-14 10:21:40', 17150.00, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(11, 2, '2025-05-14 10:23:04', 10649.99, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(12, 2, '2025-05-14 10:27:05', 5850.00, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(13, 2, '2025-05-14 10:37:14', 10649.99, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(14, 2, '2025-05-14 10:51:45', 10649.99, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(15, 2, '2025-05-14 11:20:34', 10649.99, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(16, 2, '2025-05-14 11:29:40', 17150.00, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(17, 2, '2025-05-14 11:31:50', 5850.00, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(18, 2, '2025-05-15 06:46:16', 17150.00, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(19, 2, '2025-05-15 07:01:58', 17150.00, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(20, 2, '2025-05-15 07:20:43', 10649.99, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(21, 2, '2025-05-15 07:37:02', 17150.00, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(22, 2, '2025-05-15 07:37:58', 17150.00, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(23, 2, '2025-05-15 07:56:23', 17150.00, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(24, 2, '2025-05-15 07:58:01', 12550.00, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(27, 5, '2025-05-15 08:13:57', 1.00, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(28, 2, '2025-05-15 08:44:38', 10649.99, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(29, 2, '2025-05-15 08:53:52', 10649.99, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(30, 2, '2025-05-27 07:37:58', 5850.00, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(31, 2, '2025-05-27 07:57:29', 17150.00, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(32, 2, '2025-05-27 07:58:24', 5850.00, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(33, 2, '2025-05-30 07:06:12', 10649.99, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(34, 2, '2025-05-30 07:10:37', 5850.00, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(35, 2, '2025-05-30 07:22:00', 10649.99, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(36, 2, '2025-05-30 07:26:25', 12550.00, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(37, 2, '2025-05-30 07:29:04', 10649.99, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(38, 2, '2025-05-30 07:29:04', 10649.99, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(39, 2, '2025-05-30 07:32:31', 5850.00, 'pending', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(40, 2, '2025-05-30 07:32:32', 5850.00, '', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(41, 2, '2025-05-30 07:33:06', 17150.00, '', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(42, 2, '2025-05-30 07:49:27', 10649.99, 'pending', 'pesapal', 'pending', NULL, NULL, NULL, NULL),
(43, 2, '2025-05-30 08:43:40', 17150.00, 'pending', 'pesapal', 'pending', NULL, NULL, NULL, NULL),
(44, 2, '2025-05-30 08:53:39', 17150.00, '', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(45, 2, '2025-05-30 08:54:15', 17150.00, 'pending', 'pesapal', 'pending', NULL, NULL, NULL, NULL),
(46, 2, '2025-05-30 09:13:18', 10649.99, 'pending', 'pesapal', 'pending', NULL, NULL, NULL, NULL),
(47, 2, '2025-06-01 07:09:30', 10649.99, '', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(48, 2, '2025-06-01 07:09:57', 10649.99, 'pending', 'pesapal', 'pending', NULL, NULL, NULL, NULL),
(49, 2, '2025-06-02 10:32:50', 12550.00, '', 'mpesa', 'pending', NULL, NULL, NULL, NULL),
(50, 2, '2025-06-07 12:41:00', 17150.00, '', 'mpesa', 'pending', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_type` enum('pet','product') NOT NULL,
  `item_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price_per_unit` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `item_type`, `item_id`, `seller_id`, `quantity`, `price_per_unit`, `subtotal`, `status`) VALUES
(1, 1, 'pet', 1, 3, 1, 17000.00, 17000.00, 'pending'),
(2, 1, 'product', 1, 3, 1, 12400.00, 12400.00, 'pending'),
(3, 2, 'pet', 1, 3, 1, 17000.00, 17000.00, 'shipped'),
(4, 2, 'product', 1, 3, 1, 12400.00, 12400.00, 'pending'),
(5, 3, 'pet', 2, 3, 1, 10499.99, 10499.99, 'pending'),
(6, 4, 'pet', 2, 3, 1, 10499.99, 10499.99, 'pending'),
(7, 6, 'pet', 1, 3, 1, 17000.00, 17000.00, 'pending'),
(8, 7, 'pet', 2, 3, 1, 10499.99, 10499.99, 'pending'),
(9, 8, 'pet', 1, 3, 1, 17000.00, 17000.00, 'pending'),
(10, 9, 'pet', 1, 3, 1, 17000.00, 17000.00, 'pending'),
(11, 10, 'pet', 1, 3, 1, 17000.00, 17000.00, 'pending'),
(12, 11, 'pet', 2, 3, 1, 10499.99, 10499.99, 'pending'),
(13, 12, 'product', 2, 3, 1, 5700.00, 5700.00, 'pending'),
(14, 13, 'pet', 2, 3, 1, 10499.99, 10499.99, 'pending'),
(15, 14, 'pet', 2, 3, 1, 10499.99, 10499.99, 'pending'),
(16, 15, 'pet', 2, 3, 1, 10499.99, 10499.99, 'pending'),
(17, 16, 'pet', 1, 3, 1, 17000.00, 17000.00, 'pending'),
(18, 17, 'product', 2, 3, 1, 5700.00, 5700.00, 'pending'),
(19, 18, 'pet', 1, 3, 1, 17000.00, 17000.00, 'pending'),
(20, 19, 'pet', 1, 3, 1, 17000.00, 17000.00, 'pending'),
(21, 20, 'pet', 2, 3, 1, 10499.99, 10499.99, 'pending'),
(22, 21, 'pet', 1, 3, 1, 17000.00, 17000.00, 'pending'),
(23, 22, 'pet', 1, 3, 1, 17000.00, 17000.00, 'pending'),
(24, 23, 'pet', 1, 3, 1, 17000.00, 17000.00, 'pending'),
(25, 24, 'product', 1, 3, 1, 12400.00, 12400.00, 'pending'),
(26, 28, 'pet', 2, 3, 1, 10499.99, 10499.99, 'pending'),
(27, 29, 'pet', 2, 3, 1, 10499.99, 10499.99, 'pending'),
(28, 30, 'product', 2, 3, 1, 5700.00, 5700.00, 'pending'),
(29, 31, 'pet', 1, 3, 1, 17000.00, 17000.00, 'pending'),
(30, 32, 'product', 2, 3, 1, 5700.00, 5700.00, 'pending'),
(31, 33, 'pet', 2, 3, 1, 10499.99, 10499.99, 'pending'),
(32, 34, 'product', 2, 3, 1, 5700.00, 5700.00, 'pending'),
(33, 35, 'pet', 2, 3, 1, 10499.99, 10499.99, 'pending'),
(34, 36, 'product', 1, 3, 1, 12400.00, 12400.00, 'pending'),
(35, 37, 'pet', 2, 3, 1, 10499.99, 10499.99, 'pending'),
(36, 39, 'product', 2, 3, 1, 5700.00, 5700.00, 'pending'),
(37, 41, 'pet', 1, 3, 1, 17000.00, 17000.00, 'pending'),
(38, 42, 'pet', 2, 3, 1, 10499.99, 10499.99, 'pending'),
(39, 43, 'pet', 1, 3, 1, 17000.00, 17000.00, 'pending'),
(40, 44, 'pet', 1, 3, 1, 17000.00, 17000.00, 'pending'),
(41, 45, 'pet', 1, 3, 1, 17000.00, 17000.00, 'pending'),
(42, 46, 'pet', 2, 3, 1, 10499.99, 10499.99, 'pending'),
(43, 47, 'pet', 2, 3, 1, 10499.99, 10499.99, 'pending'),
(44, 48, 'pet', 2, 3, 1, 10499.99, 10499.99, 'pending'),
(45, 49, 'product', 1, 3, 1, 12400.00, 12400.00, 'processing'),
(46, 50, 'pet', 1, 3, 1, 17000.00, 17000.00, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_method` enum('mpesa','pesapal','cash_on_delivery') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `checkout_request_id` varchar(100) DEFAULT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `transaction_code` varchar(50) DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `order_id`, `payment_method`, `amount`, `status`, `checkout_request_id`, `reference`, `transaction_code`, `payment_date`, `created_at`, `updated_at`) VALUES
(1, 5, 'mpesa', 10649.99, 'pending', NULL, NULL, NULL, NULL, '2025-05-14 07:28:02', '2025-05-14 07:28:02'),
(2, 27, 'mpesa', 1.00, 'pending', NULL, NULL, NULL, NULL, '2025-05-15 08:13:57', '2025-05-15 08:13:57'),
(3, 30, 'mpesa', 5850.00, '', 'ws_CO_27052025103800352798185212', NULL, NULL, NULL, '2025-05-27 07:37:58', '2025-05-27 07:53:37'),
(4, 30, 'mpesa', 5850.00, 'pending', NULL, NULL, NULL, NULL, '2025-05-27 07:53:37', '2025-05-27 07:53:37'),
(5, 33, 'mpesa', 10649.99, '', NULL, NULL, NULL, NULL, '2025-05-30 07:06:12', '2025-05-30 07:06:43'),
(6, 33, 'mpesa', 10649.99, 'pending', NULL, NULL, NULL, NULL, '2025-05-30 07:06:43', '2025-05-30 07:06:43'),
(7, 40, 'mpesa', 5850.00, 'pending', 'ws_CO_30052025103230745798185212', NULL, NULL, NULL, '2025-05-30 07:32:32', '2025-05-30 07:32:34'),
(8, 41, 'mpesa', 17150.00, 'pending', 'ws_CO_30052025103305449798185212', NULL, NULL, NULL, '2025-05-30 07:33:06', '2025-05-30 07:33:08'),
(9, 44, 'mpesa', 17150.00, 'pending', 'ws_CO_30052025115337728798185212', NULL, NULL, NULL, '2025-05-30 08:53:39', '2025-05-30 08:53:41'),
(10, 45, 'pesapal', 17150.00, '', NULL, NULL, NULL, NULL, '2025-05-30 08:54:15', '2025-05-30 08:54:15'),
(11, 45, 'pesapal', 17150.00, 'pending', NULL, 'PesaPal_45_1748595519', NULL, NULL, '2025-05-30 08:58:39', '2025-05-30 08:58:39'),
(12, 46, 'pesapal', 10649.99, 'failed', NULL, NULL, NULL, NULL, '2025-05-30 09:13:18', '2025-05-30 09:13:19'),
(13, 47, 'mpesa', 10649.99, 'pending', 'ws_CO_01062025101136720798185212', NULL, NULL, NULL, '2025-06-01 07:09:30', '2025-06-01 07:09:32'),
(14, 48, 'pesapal', 10649.99, 'failed', NULL, NULL, NULL, NULL, '2025-06-01 07:09:57', '2025-06-01 07:09:57'),
(15, 49, 'mpesa', 12550.00, 'pending', 'ws_CO_02062025133251431798185212', NULL, NULL, NULL, '2025-06-02 10:32:50', '2025-06-02 10:32:52'),
(16, 50, 'mpesa', 17150.00, 'pending', 'ws_CO_07062025154310546798185212', NULL, NULL, NULL, '2025-06-07 12:41:00', '2025-06-07 12:41:04');

-- --------------------------------------------------------

--
-- Table structure for table `pets`
--

CREATE TABLE `pets` (
  `pet_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `breed` varchar(100) DEFAULT NULL,
  `age` varchar(50) DEFAULT NULL,
  `gender` enum('male','female','unknown') DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `status` enum('available','sold','pending','inactive') DEFAULT 'available',
  `featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approval_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `views` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pets`
--

INSERT INTO `pets` (`pet_id`, `seller_id`, `category_id`, `name`, `breed`, `age`, `gender`, `description`, `price`, `quantity`, `status`, `featured`, `created_at`, `updated_at`, `approval_status`, `views`) VALUES
(1, 3, 1, 'Butch', 'Doberman', '3months', 'male', 'The Doberman Pinscher is a powerful, intelligent, and loyal dog breed known for its sleek, athletic build and keen alertness. Originally developed in Germany in the late 19th century as a guard dog, the Doberman has become one of the most respected and admired working breeds in the world.\r\n\r\nWith its muscular body, smooth coat, and noble stance, the Doberman exudes strength and elegance. It typically stands between 24 to 28 inches tall and has a short coat that comes in colors such as black, blue, red, and fawn, often with rust-colored markings.\r\n\r\nDobermans are highly trainable, making them excellent police, military, and service dogs. They are fiercely loyal to their families and protective without being unnecessarily aggressive, which makes them superb watchdogs. They are also energetic and require regular exercise and mental stimulation.\r\n\r\nWhile they may appear intimidating, well-socialized Dobermans are affectionate, loving, and often very gentle with children and family members. With proper training and care, they make devoted and reliable companions.\r\n\r\n', 17000.00, 1, 'available', 1, '2025-05-07 06:42:53', '2025-06-07 12:40:46', 'approved', 76),
(2, 3, 2, 'Kyle', 'Maine Coon', '2 months', 'female', 'The Maine Coon is one of the largest domesticated cat breeds, known for its impressive size, tufted ears, and luxurious, water-resistant fur. This breed typically has a muscular build with a long, bushy tail and large, expressive eyes. Their coat is thick and smooth, varying in color and pattern.\r\n\r\nPersonality:\r\nMaine Coons are affectionate, intelligent, and playful. They are often described as \"dog-like\" due to their loyalty and tendency to follow their owners around. They are sociable and get along well with children and other pets.\r\n\r\nHealth & Lifespan:\r\nMaine Coons are generally healthy but can be prone to hip dysplasia and heart conditions like hypertrophic cardiomyopathy. Their average lifespan is 12–15 years.\r\n\r\nCare Requirements:\r\nRegular grooming due to their thick fur\r\n\r\nHigh-protein diet to maintain their muscular build\r\n\r\nPlenty of space to explore and climb', 10499.99, 3, 'available', 1, '2025-05-08 19:28:17', '2025-06-01 07:09:47', 'approved', 25);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `status` enum('available','out_of_stock','inactive') DEFAULT 'available',
  `featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approval_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `views` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `seller_id`, `category_id`, `name`, `description`, `price`, `stock_quantity`, `status`, `featured`, `created_at`, `updated_at`, `approval_status`, `views`) VALUES
(1, 3, 1, 'Royal Canin Medium Adult Dog Food', 'Royal Canin Medium Adult Dog Food (10kg Bag)\r\nGive your medium-sized dog the perfect balance of nutrition with Royal Canin Medium Adult Dog Food. Specially formulated for dogs aged 1 to 7 years weighing between 11kg and 25kg, this premium dog food supports healthy digestion, boosts immune strength, and promotes a shiny coat.\r\n\r\nKey Benefits:\r\n💪 Supports strong immune defenses with antioxidants and vitamins.\r\n\r\n🦴 Promotes joint and bone health with optimal calcium and phosphorus levels.\r\n\r\n🍗 High-quality protein for muscle maintenance and energy.\r\n\r\n🐕 Strengthens skin and coat health with Omega-3 and Omega-6 fatty acids.\r\n\r\n🍲 Enhanced taste to satisfy even the pickiest eaters.\r\n\r\nFeeding Instructions:\r\nFollow the feeding guidelines based on your dog\'s weight and activity level for optimal health and energy.', 12400.00, 5, 'available', 1, '2025-05-08 08:34:40', '2025-06-02 10:32:42', 'approved', 16),
(2, 3, 2, 'Royal Canin Kitten Dry Cat Food', 'Designed for kittens up to 12 months old, it supports immune system development and digestive health with a blend of antioxidants and highly digestible proteins.', 5700.00, 10, 'available', 1, '2025-05-08 19:37:46', '2025-06-04 11:08:59', 'approved', 18);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_type` enum('pet','product','seller') NOT NULL,
  `item_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `user_id`, `item_type`, `item_id`, `rating`, `comment`, `created_at`, `status`) VALUES
(1, 2, 'pet', 1, 4, 'itvery good\\r\\n', '2025-05-19 16:03:16', 'pending'),
(2, 2, 'pet', 2, 4, 'bnm', '2025-05-19 20:15:21', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `seller_profiles`
--

CREATE TABLE `seller_profiles` (
  `seller_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `business_name` varchar(100) DEFAULT NULL,
  `business_description` text DEFAULT NULL,
  `verification_status` enum('pending','verified','rejected') DEFAULT 'pending',
  `verification_notes` text DEFAULT NULL,
  `id_number` varchar(20) DEFAULT NULL,
  `id_front_image` varchar(255) DEFAULT NULL,
  `id_back_image` varchar(255) DEFAULT NULL,
  `id_selfie_image` varchar(255) DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seller_profiles`
--

INSERT INTO `seller_profiles` (`seller_id`, `user_id`, `business_name`, `business_description`, `verification_status`, `verification_notes`, `id_number`, `id_front_image`, `id_back_image`, `id_selfie_image`, `rating`) VALUES
(3, 3, 'Cool Pets', 'dfghjk', 'pending', NULL, '38940312', NULL, NULL, NULL, 0.00),
(8, 8, 'Onjiko pets', 'we sell pets', 'verified', '', '38912400', 'uploads/id_images/id_front_6823161d112d3.jpg', 'uploads/id_images/id_back_6823161d11811.jpg', 'uploads/id_images/id_selfie_6823161d11bd1.jpg', 0.00),
(17, 17, 'Pet innz', 'afghj;lkjhg', 'verified', '', '38912400', 'uploads/id_images/id_front_683f45c907625.jpg', 'uploads/id_images/id_back_683f45c907d98.jpg', 'uploads/id_images/id_selfie_683f45c90866e.jpg', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_id`, `setting_key`, `value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'Jambo Pets', 'Website name', '2025-05-16 08:50:25', '2025-05-16 08:50:25'),
(2, 'site_logo', 'uploads/logo/site_logo_1747387863.png', 'Website logo path', '2025-05-16 08:50:25', '2025-05-16 09:31:03'),
(3, 'contact_email', 'jambopets@gmail.com', 'Contact email address', '2025-05-16 08:50:25', '2025-05-16 09:31:56'),
(4, 'contact_phone', '+254798185212', 'Contact phone number', '2025-05-16 08:50:25', '2025-05-16 09:32:12'),
(5, 'contact_address', 'Komarock', 'Physical address', '2025-05-16 08:50:25', '2025-05-16 09:31:56'),
(6, 'facebook_link', '', 'Facebook page URL', '2025-05-16 08:50:25', '2025-05-16 08:55:37'),
(7, 'twitter_link', '', 'Twitter profile URL', '2025-05-16 08:50:25', '2025-05-16 08:55:37'),
(8, 'instagram_link', '', 'Instagram profile URL', '2025-05-16 08:50:25', '2025-05-16 08:55:37'),
(9, 'mpesa_consumer_key', '', 'M-Pesa API Consumer Key', '2025-05-16 08:50:25', '2025-05-16 08:50:25'),
(10, 'mpesa_consumer_secret', '', 'M-Pesa API Consumer Secret', '2025-05-16 08:50:25', '2025-05-16 08:50:25'),
(11, 'mpesa_shortcode', '', 'M-Pesa Business Shortcode', '2025-05-16 08:50:25', '2025-05-16 08:50:25'),
(12, 'mpesa_passkey', '', 'M-Pesa API Passkey', '2025-05-16 08:50:25', '2025-05-16 08:50:25'),
(13, 'pesapal_consumer_key', '', 'PesaPal API Consumer Key', '2025-05-16 08:50:25', '2025-05-16 08:50:25'),
(14, 'pesapal_consumer_secret', '', 'PesaPal API Consumer Secret', '2025-05-16 08:50:25', '2025-05-16 08:50:25'),
(15, 'pesapal_shortcode', '', 'PesaPal Business Shortcode', '2025-05-16 08:50:25', '2025-05-16 08:50:25'),
(16, 'pesapal_passkey', '', 'PesaPal API Passkey', '2025-05-16 08:50:25', '2025-05-16 08:50:25'),
(17, 'commission_rate', '10', 'Platform commission percentage on sales', '2025-05-16 08:50:25', '2025-05-16 08:50:25'),
(18, 'maintenance_mode', '0', 'Site maintenance mode (0=off, 1=on)', '2025-05-16 08:50:25', '2025-05-16 08:50:25'),
(19, 'max_listing_images', '5', 'Maximum number of images per listing', '2025-05-16 08:50:25', '2025-05-16 08:50:25'),
(20, 'enable_seller_verification', '1', 'Require seller verification (0=off, 1=on)', '2025-05-16 08:50:25', '2025-05-16 08:50:25'),
(21, 'enable_email_notifications', '1', 'Enable email notifications (0=off, 1=on)', '2025-05-16 08:50:25', '2025-05-16 08:50:25'),
(22, 'enable_sms_notifications', '0', 'Enable SMS notifications (0=off, 1=on)', '2025-05-16 08:50:25', '2025-05-16 08:50:25'),
(23, 'currency_symbol', 'KSh', 'Currency symbol', '2025-05-16 08:50:25', '2025-05-16 08:50:25'),
(24, 'currency_code', 'KES', 'Currency code', '2025-05-16 08:50:25', '2025-05-16 08:50:25');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `user_type` enum('buyer','seller','admin') NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `county` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('active','inactive','suspended') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password`, `first_name`, `last_name`, `phone`, `user_type`, `profile_image`, `county`, `address`, `created_at`, `updated_at`, `status`) VALUES
(2, 'noelomondi015@gmail.com', '$2y$10$Jwylfp4Eglk68aHjLFH.Ne5/1lWmnp1iLowGcM9jCEQ3xg2mCPx5.', 'Noel', 'Oigo', '+254798185212', 'buyer', '1747644719_best2.jpg', 'Nairobi', 'Dohnholme', '2025-05-06 16:14:28', '2025-05-19 08:51:59', 'active'),
(3, 'noeloigo@gmail.com', '$2y$10$h25yM4Kr41qmmsIB1mYDgelufFCqcD6kZgiraF3JKF4IcUBc1RByy', 'Noel', 'Oigo', '0798185212', 'seller', 'seller_3_1748939645_best3.jpg', 'Kisumu', 'Komarock', '2025-05-06 16:41:14', '2025-06-03 08:34:05', 'active'),
(5, 'admin@jambopets.com', '$2y$10$nnpzOE6CUrgCPHM.h6z/q.K3l8n8Iy0wqmxUidIUmJ1LPNjRupLHe', 'Noel', 'Oigo', '0700000000', 'admin', 'profile_682afd59bccc5.jpg', 'Nairobi', '', '2025-05-06 18:21:04', '2025-05-20 19:29:28', 'active'),
(8, 'noelomondi@gmail.com', '$2y$10$fe7laFne99Y4nUUSize62eVBz.EAbEQjpQgavQojqF64sivI3ZcQ6', 'Noel', 'Oigo', '0798185212', 'seller', NULL, 'Machakos', 'Muranga', '2025-05-13 09:51:25', '2025-05-13 09:51:25', 'active'),
(9, 'david@gmail.com', '$2y$10$Napd6gDiP163xJoqyLjxTOlPc1i55MOXUi91LzORFO2tW1nz0I5Na', 'David', 'Baraka', '0710274919', 'admin', NULL, NULL, NULL, '2025-05-15 19:54:10', '2025-05-15 19:55:39', 'active'),
(10, 'jamesoigo@gmail.com', '$2y$10$Bt4vrVWkFHcVSrmWTC5Aref7ayMc0HJruzSC0mYLQNtrCNOuDtI5q', 'James', 'Oigo', '+254713434222', 'buyer', '1747738054_IMG20231117150049.jpg', 'Nairobi', 'Kayole', '2025-05-20 10:41:17', '2025-05-20 10:47:34', 'active'),
(17, 'ictmuranga@gmail.com', '$2y$10$xS36vd32N8N2mKkFv6K.ju1SR7FGel3655ADAekA3MSReDjPhA91y', 'Noel', 'Oigo', '0798185212', 'seller', NULL, '', 'Komarock', '2025-06-03 18:58:17', '2025-06-03 18:58:17', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist_items`
--

CREATE TABLE `wishlist_items` (
  `wishlist_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_type` enum('pet','product') NOT NULL,
  `item_id` int(11) NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist_items`
--

INSERT INTO `wishlist_items` (`wishlist_id`, `user_id`, `item_type`, `item_id`, `date_added`) VALUES
(9, 2, 'product', 2, '2025-06-04 11:08:05');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `admin_roles`
--
ALTER TABLE `admin_roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `analytics`
--
ALTER TABLE `analytics`
  ADD PRIMARY KEY (`analytics_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`cart_item_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indexes for table `counties`
--
ALTER TABLE `counties`
  ADD PRIMARY KEY (`county_id`);

--
-- Indexes for table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`image_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `newsletter_campaigns`
--
ALTER TABLE `newsletter_campaigns`
  ADD PRIMARY KEY (`campaign_id`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`subscriber_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `email_2` (`email`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `newsletter_tracking`
--
ALTER TABLE `newsletter_tracking`
  ADD PRIMARY KEY (`tracking_id`),
  ADD KEY `campaign_id` (`campaign_id`),
  ADD KEY `subscriber_id` (`subscriber_id`,`campaign_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `buyer_id` (`buyer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `pets`
--
ALTER TABLE `pets`
  ADD PRIMARY KEY (`pet_id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `seller_profiles`
--
ALTER TABLE `seller_profiles`
  ADD PRIMARY KEY (`seller_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  ADD PRIMARY KEY (`wishlist_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `admin_roles`
--
ALTER TABLE `admin_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `analytics`
--
ALTER TABLE `analytics`
  MODIFY `analytics_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=920;

--
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cart_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `counties`
--
ALTER TABLE `counties`
  MODIFY `county_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `newsletter_campaigns`
--
ALTER TABLE `newsletter_campaigns`
  MODIFY `campaign_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `subscriber_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `newsletter_tracking`
--
ALTER TABLE `newsletter_tracking`
  MODIFY `tracking_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `pets`
--
ALTER TABLE `pets`
  MODIFY `pet_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  MODIFY `wishlist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_roles`
--
ALTER TABLE `admin_roles`
  ADD CONSTRAINT `admin_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `analytics`
--
ALTER TABLE `analytics`
  ADD CONSTRAINT `analytics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD CONSTRAINT `blog_posts_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL;

--
-- Constraints for table `contact`
--
ALTER TABLE `contact`
  ADD CONSTRAINT `contact_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `newsletter_tracking`
--
ALTER TABLE `newsletter_tracking`
  ADD CONSTRAINT `newsletter_tracking_ibfk_1` FOREIGN KEY (`subscriber_id`) REFERENCES `newsletter_subscribers` (`subscriber_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `newsletter_tracking_ibfk_2` FOREIGN KEY (`campaign_id`) REFERENCES `newsletter_campaigns` (`campaign_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`seller_id`) REFERENCES `seller_profiles` (`seller_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `pets`
--
ALTER TABLE `pets`
  ADD CONSTRAINT `pets_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `seller_profiles` (`seller_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pets_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `seller_profiles` (`seller_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `seller_profiles`
--
ALTER TABLE `seller_profiles`
  ADD CONSTRAINT `seller_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  ADD CONSTRAINT `wishlist_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
