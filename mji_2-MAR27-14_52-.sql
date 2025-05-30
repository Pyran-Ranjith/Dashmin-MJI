-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 27, 2025 at 10:21 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12
SET FOREIGN_KEY_CHECKS = 0;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mji_2`
--

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` int(11) NOT NULL,
  `brand_name` varchar(255) NOT NULL,
  `flag` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `brand_name`, `flag`, `created_at`, `updated_at`) VALUES
(1, 'KEYB', 'active', '2025-03-21 06:42:34', '2025-03-23 00:56:02'),
(2, 'EEP', 'active', '2025-03-21 06:44:34', '2025-03-23 00:56:20');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category_name`, `flag`) VALUES
(1, 'Honda', 'active'),
(2, 'Toyota', 'active'),
(3, 'Nisan', 'active'),
(6, 'Mitsubishi', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `catogory_id` int(10) DEFAULT NULL,
  `model_id` int(11) DEFAULT NULL,
  `stock_id` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `course` varchar(50) NOT NULL,
  `subscription` enum('active','inactive') NOT NULL DEFAULT 'inactive',
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `user_id`, `catogory_id`, `model_id`, `stock_id`, `date`, `course`, `subscription`, `flag`) VALUES
(6, 1, 0, NULL, 1, '2024-09-15', '', 'inactive', 'active'),
(7, 2, 0, NULL, 5, '2024-11-05', '', 'inactive', 'active'),
(8, 2, 0, NULL, 1, '2024-12-24', '', 'inactive', 'active'),
(9, 12, 0, NULL, 9, '2025-02-10', '', 'active', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `first_name`, `last_name`, `email`, `phone`, `address`, `created_at`, `flag`) VALUES
(1, 'Kamal', 'Fonseka', 'kamalfonseka@google.com', '1234567890', '305/1 Samagi Mawtha, Polonaruwa', '2024-10-15 04:39:16', 'active'),
(3, 'Ranjith', 'Jayawardana', 'ranjithimas@gmail.com', '3445667', '25 Hilevel Road. Kottawa', '0000-00-00 00:00:00', 'inactive'),
(4, 'Nethmi', 'Sansala', 'nethmisansala@gmail.com', '3445667', '25 Hilevel Road. Kottawa', '0000-00-00 00:00:00', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `issue_date` date NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `invoice_number`, `customer_id`, `issue_date`, `total_price`, `flag`) VALUES
(1, 'INV-5498', 1, '2025-03-27', 1000.00, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `stock_id` int(11) NOT NULL,
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoice_items`
--

INSERT INTO `invoice_items` (`id`, `invoice_id`, `sale_id`, `quantity`, `unit_price`, `total_price`, `stock_id`, `flag`) VALUES
(1, 1, 28, 1, 1000.00, 1000.00, 0, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `language`
--

CREATE TABLE `language` (
  `id` int(11) NOT NULL,
  `lan_group` varchar(10) NOT NULL,
  `lan_tag` varchar(50) NOT NULL,
  `lan_english` varchar(50) NOT NULL,
  `lan_sinhala` varchar(50) NOT NULL,
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `language`
--

INSERT INTO `language` (`id`, `lan_group`, `lan_tag`, `lan_english`, `lan_sinhala`, `flag`) VALUES
(1, 'head', 'DASHMIN - Bootstrap Admin Template', 'DASHMIN - Bootstrap Admin Template', 'DASHMIN - Bootstrap Admin Template', 'active'),
(2, '', 'Manage', 'Manage', 'කළමනාකරණය', 'active'),
(3, '', '', 'Employee', '0', 'active'),
(4, '', '', 'Staff', '0', 'active'),
(5, '', '', 'Master', '0', 'active'),
(15, '', '', 'User', '0', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `location_name` varchar(100) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `location_name`, `address`, `city`, `state`, `country`, `flag`, `created_at`, `updated_at`) VALUES
(1, 'Main ', '113 Panchikawatha Road', 'Colombo-10', 'Western Province', 'Sri Lanka', 'active', '2025-03-20 05:06:35', '2025-03-20 05:11:25'),
(2, 'Home', '113 Mahawatha Road', 'Malambe', 'Western Province', 'Sri Lanka', 'active', '2025-03-20 05:10:05', '2025-03-20 05:10:05');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `menu_name` varchar(255) NOT NULL,
  `menu_link` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `menu_name`, `menu_link`) VALUES
(1, 'Dashboard', 'dashboard.php'),
(2, 'Reports', 'reports.php'),
(3, 'Manage Users', 'manage_users.php'),
(4, 'Maintain Equipment', 'maintain_equipment.php');

-- --------------------------------------------------------

--
-- Table structure for table `menu_options`
--

CREATE TABLE `menu_options` (
  `id` int(11) NOT NULL,
  `menu_name` varchar(75) NOT NULL,
  `menu_link` varchar(75) NOT NULL DEFAULT '#',
  `menu_table` varchar(30) NOT NULL,
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_options`
--

INSERT INTO `menu_options` (`id`, `menu_name`, `menu_link`, `menu_table`, `flag`) VALUES
(1, 'Dashboard', 'dashboard1.php', '', 'active'),
(2, 'Manage Customers', 'manage_customers.php', 'customers', 'active'),
(3, 'Manage Invoices', 'manage_invoices.php', 'invoices', 'active'),
(4, 'Maintain Menu Options', 'manage_menuoptions.php', 'menuoptions', 'active'),
(5, 'Maintain Role Menu Options', 'manage_role_menu_options-2.php', 'role_menu_options', 'active'),
(6, 'Maintain Roles', 'manage_roles.php', 'roles', 'active'),
(7, 'Manage Sales', 'manage_sales.php', 'sales', 'active'),
(8, 'Manage Stocks', 'manage_stock.php', 'stock', 'active'),
(9, 'Manage Supplier Purchases', 'manage_supplier_purchases.php', 'supplier_purchases', 'active'),
(10, 'Reports Generate Reports', 'generate_reports.php', '', 'active'),
(11, 'Manage Suppliers ', 'manage_suppliers.php', 'suppliers', 'active'),
(12, 'Manage Users', 'manage_users.php', 'users', 'active'),
(13, 'Reports CategoriesERROR', 'manage_categories.php', 'categories', 'active'),
(14, 'Manage Categories', 'manage_categories.php', 'categories', 'active'),
(15, 'Maintain Menu Tags', 'manage_menutags.php', 'menutags', 'active'),
(17, 'Manage Models', 'manage_models.php', 'models', 'active'),
(20, 'Maitain  Inactive Records', 'manage_inactive_records.php', '', 'active'),
(21, 'Maintain Active/Inactive Flags', 'update_inactive_records.php?table_name=customers', '', 'active'),
(22, 'Mange Invoice-Test', 'invoice@gpt.php?id=25.php', '', 'active'),
(23, 'Batch copy files src to destination folder', 'batch_copy_files_src_to_destination_folder.php', '', 'active'),
(24, 'Maintain MT Src File List', 'maintain_src_file_list.php', '', 'active'),
(25, 'Maintain 0104 multyDropDownMenue.php', 'manage_0104_MultyDropDownMenue.php', '', 'active'),
(26, 'Index', 'index.php', '', 'active'),
(27, 'Admin dashboard', 'admin_dashboard.php', '', 'active'),
(28, 'Login', 'login.php', '', 'active'),
(29, 'Admin dashboard', 'admin_dashboard.php', '', 'inactive'),
(30, 'Batch copy_files_src_to_destination_folder', 'batch_copy_files_src_to_destination_folder.php', '', 'active'),
(31, 'Resources images_shock-absorbers.jpg', 'images_shock-absorbers.jpg', '', 'active'),
(32, 'Resources images_nmc-award.jpg', 'images_nmc-award.jpg', '', 'active'),
(33, 'Resources images_other-brands.jpg', 'images_other-brands.jpg', '', 'active'),
(34, 'Resources images_cab-1536x480.jpg', 'images_cab-1536x480.jpg', '', 'active'),
(35, 'Maintain chtgpt_dspare_dparts_dmanagement_new1.sql', 'chtgpt_dspare_dparts_dmanagement_new1.sql', '', 'active'),
(36, 'Batch batch_compare_files_infolder_with_table.php', 'batch_compare_files_infolder_with_table.php', '', 'active'),
(37, 'Batch batch_compair_table_and_folder_list_difference', 'batch_compair_table_and_folder_list_difference.php', '', 'active'),
(38, 'Maintain 00_Pgm_Test', '00_Pgm_Test.php', '', 'active'),
(39, 'Batch batch_copy_files_src_to_destination_folder_test', 'batch_copy_files_src_to_destination_folder_test.php', '', 'active'),
(40, 'Batch -------------------------------', '--------------------', '', 'active'),
(41, 'Batch batch_copy_files_src_to_destination_folder_prod', 'batch_copy_files_src_to_destination_folder_prod.php', '', 'active'),
(42, 'Resources nmc-I.ico', 'nmc-I.ico', '', 'active'),
(43, 'Resources nmc-II.ico', 'nmc-II.ico', '', 'active'),
(44, 'Resources nmc.ico', 'nmc.ico', '', 'active'),
(45, 'Setting Show/Hide Images', '', '', 'active'),
(46, 'index1.php', 'index1.php', '', 'active'),
(47, 'manage_role_menu_options-2', 'manage_role_menu_options-2.php', '', 'active'),
(48, 'about-us4.jpg', 'about-us4.jpg', '', 'active'),
(49, 'contact_us.png', 'contact_us.png', '', 'active'),
(50, 'kyb1.jpg', 'kyb1.jpg', '', 'active'),
(51, 'ultra-pic1.jpg', 'ultra-pic1.jpg', '', 'active'),
(52, 'AGX.jpg', 'AGX.jpg', '', 'active'),
(53, 'flyer1.jpg', 'flyer1.jpg', '', 'active'),
(54, 'flyer2.jpg', 'flyer2.jpg', '', 'active'),
(55, 'flyer3.jpg', 'flyer3.jpg', '', 'active'),
(56, 'flyer4.jpg', 'flyer4.jpg', '', 'active'),
(57, 'Products_&_Services_NMC_files.png', 'Products_&_Services_NMC_files.png', '', 'active'),
(58, 'EXCEL-G.jpg', 'EXCEL-G.jpg', '', 'active'),
(59, 'gas-a-just.jpg', 'gas-a-just.jpg', '', 'active'),
(60, 'team-1.jpg', 'team-1.jpg', '', 'active'),
(61, 'team-2.jpg', 'team-2.jpg', '', 'active'),
(62, 'team-3.jpg', 'team-3.jpg', '', 'active'),
(63, 'team-4.jpg', 'team-4.jpg', '', 'active'),
(64, 'About_us0.png', 'About_us0.png', '', 'inactive'),
(65, 'about_us0.png', 'about_us0.png', '', 'active'),
(66, 'about-us1.jpg', 'about-us1.jpg', '', 'active'),
(67, 'about-us2.jpg', 'about-us2.jpg', '', 'active'),
(68, 'about-us3.jpg', 'about-us3.jpg', '', 'active'),
(69, 'about-us4.jpg', 'about-us4.jpg', '', 'active'),
(70, 'language.php', 'language.php', '', 'active'),
(71, 'Invoice-Test', 'invoice@gpt.php?id=25', '', 'active'),
(72, 'Manage Orders', 'manage_orders.php', '', 'active'),
(73, 'Reports Sales', 'report_sales.php', '', 'active'),
(74, 'Reports Stocks', 'report_stocks.php', '', 'active'),
(75, 'Reports Supplier purchases', 'report_supplier_purchases.php', '', 'active'),
(76, 'Reports Orders', 'report_orders.php', '', 'active'),
(77, 'Maintain Role Crud Permissions', 'maintain_role_crud.php', '', 'active'),
(78, 'Manage Locations', 'manage_locations.php', '', 'active'),
(79, 'Manage Brands', 'manage_brands.php', '', 'active'),
(80, 'Manage Types', 'manage_types.php', '', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `menu_tags`
--

CREATE TABLE `menu_tags` (
  `id` int(11) NOT NULL,
  `tag_name` varchar(20) NOT NULL DEFAULT '#',
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_tags`
--

INSERT INTO `menu_tags` (`id`, `tag_name`, `flag`) VALUES
(1, 'Dashboard', 'active'),
(2, 'Manage', 'active'),
(6, 'Maintain', 'active'),
(7, 'Int', 'active'),
(8, 'Reports', 'active'),
(9, 'Batch', 'active'),
(10, 'Hide', 'active'),
(11, 'Resources', 'active'),
(12, 'Setting', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `models`
--

CREATE TABLE `models` (
  `id` int(11) NOT NULL,
  `model_name` varchar(50) NOT NULL,
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `models`
--

INSERT INTO `models` (`id`, `model_name`, `flag`) VALUES
(1, 'ALLION/PREMIO', 'active'),
(2, 'AURIS', 'active'),
(3, 'AVANZA', 'inactive'),
(6, 'AVANZA', 'active'),
(7, 'Lancer', 'active'),
(8, 'Corolla', 'active'),
(9, 'Prius/Hybride', 'active'),
(10, 'Insight', 'active'),
(11, 'jazz/Fit-Gd1', 'active'),
(12, 'SUNNY/PULSAR', 'active'),
(13, 'AQUA/PRIUS C', 'active'),
(14, 'AD VAN', 'active'),
(15, 'SUNNY', 'active'),
(16, 'AXIO', 'active'),
(17, 'YARIS/VOIS', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `part_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `status` enum('pending','fulfilled','canceled') NOT NULL DEFAULT 'pending',
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `flag`) VALUES
(1, 'Admin', 'active'),
(2, 'Manager', 'active'),
(3, 'Employee', 'active'),
(4, 'Staff', 'active'),
(5, 'Master', 'active'),
(15, 'User', 'active'),
(16, 'RoleTest', 'inactive');

-- --------------------------------------------------------

--
-- Table structure for table `role_crud`
--

CREATE TABLE `role_crud` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `flag_create` enum('active','inactive') NOT NULL DEFAULT 'inactive',
  `flag_read` enum('active','inactive') NOT NULL DEFAULT 'inactive',
  `flag_update` enum('active','inactive') NOT NULL DEFAULT 'inactive',
  `flag_delete` enum('active','inactive') NOT NULL DEFAULT 'inactive',
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_crud`
--

INSERT INTO `role_crud` (`id`, `role_id`, `flag_create`, `flag_read`, `flag_update`, `flag_delete`, `flag`, `created_at`, `updated_at`) VALUES
(1, 5, 'active', 'active', 'active', 'active', 'active', '2025-03-19 07:03:24', '2025-03-19 07:04:58'),
(2, 1, 'active', 'active', 'active', 'inactive', 'active', '2025-03-19 07:03:24', '2025-03-19 10:23:40');

-- --------------------------------------------------------

--
-- Table structure for table `role_menu_options`
--

CREATE TABLE `role_menu_options` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `menu_option_id` int(11) NOT NULL,
  `roleorder` int(11) NOT NULL,
  `menu_tag_id` int(11) NOT NULL,
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_menu_options`
--

INSERT INTO `role_menu_options` (`id`, `role_id`, `menu_option_id`, `roleorder`, `menu_tag_id`, `flag`) VALUES
(1, 1, 1, 1, 1, 'active'),
(2, 1, 2, 2, 2, 'active'),
(3, 1, 8, 3, 2, 'active'),
(4, 2, 1, 1, 1, 'active'),
(5, 2, 3, 2, 2, 'active'),
(6, 5, 1, 1, 1, 'active'),
(7, 5, 2, 1, 2, 'active'),
(8, 5, 3, 2, 2, 'active'),
(9, 5, 4, 100, 6, 'active'),
(10, 5, 5, 100, 6, 'active'),
(11, 5, 6, 3, 2, 'active'),
(12, 5, 7, 4, 2, 'active'),
(13, 5, 8, 5, 2, 'active'),
(14, 5, 9, 8, 2, 'active'),
(15, 5, 10, 5, 0, 'active'),
(17, 3, 1, 2, 1, 'active'),
(18, 2, 6, 2, 6, 'active'),
(19, 3, 10, 1, 8, 'active'),
(20, 2, 10, 1, 8, 'inactive'),
(21, 5, 10, 100, 8, 'active'),
(22, 5, 11, 6, 2, 'active'),
(23, 2, 1, 0, 0, 'active'),
(24, 5, 12, 7, 2, 'active'),
(25, 5, 13, 108, 8, 'active'),
(26, 5, 14, 9, 2, 'active'),
(27, 5, 15, 10, 6, 'active'),
(28, 5, 17, 11, 2, 'active'),
(29, 1, 0, 111, 1, 'active'),
(30, 1, 0, 22222222, 1, 'active'),
(31, 1, 0, 33333, 1, 'active'),
(32, 1, 1, 99888, 1, 'inactive'),
(33, 5, 20, 15, 6, 'active'),
(34, 5, 21, 16, 6, 'active'),
(35, 5, 22, 17, 2, 'active'),
(36, 5, 23, 18, 9, 'active'),
(37, 5, 24, 19, 2, 'active'),
(38, 2, 25, 20, 2, 'inactive'),
(39, 5, 26, 21, 2, 'active'),
(40, 1, 17, 4, 2, 'active'),
(41, 5, 25, 1, 6, 'inactive'),
(42, 5, 31, 1, 11, 'active'),
(43, 5, 32, 2, 11, 'active'),
(44, 5, 33, 3, 11, 'active'),
(45, 5, 34, 4, 11, 'active'),
(46, 5, 35, 20, 6, 'active'),
(47, 5, 36, 20, 9, 'active'),
(48, 5, 37, 10, 9, 'active'),
(49, 5, 38, 1, 6, 'active'),
(50, 5, 39, 4, 9, 'active'),
(51, 5, 41, 5, 9, 'active'),
(52, 5, 45, 1, 12, 'active'),
(53, 5, 47, 10, 6, 'inactive'),
(54, 5, 46, 10, 2, 'active'),
(55, 5, 48, 200, 11, 'active'),
(56, 5, 70, 100, 6, 'active'),
(57, 15, 2, 1, 2, 'inactive'),
(58, 1, 7, 5, 2, 'active'),
(59, 1, 11, 6, 2, 'active'),
(60, 1, 9, 7, 2, 'active'),
(61, 1, 3, 8, 2, 'active'),
(62, 5, 71, 12, 2, 'active'),
(63, 1, 14, 9, 2, 'active'),
(64, 5, 72, 13, 2, 'active'),
(65, 5, 74, 1, 8, 'active'),
(66, 5, 73, 2, 8, 'active'),
(67, 5, 75, 3, 8, 'active'),
(68, 5, 76, 4, 8, 'active'),
(69, 1, 74, 1, 8, 'active'),
(70, 1, 73, 2, 8, 'active'),
(71, 1, 75, 3, 8, 'active'),
(72, 5, 77, 25, 6, 'active'),
(73, 5, 78, 23, 2, 'active'),
(74, 1, 78, 10, 2, 'active'),
(75, 5, 79, 24, 2, 'active'),
(76, 1, 79, 12, 2, 'active'),
(77, 5, 80, 25, 2, 'active'),
(78, 1, 80, 13, 2, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `stock_id` int(11) DEFAULT NULL,
  `quantity_sold` int(11) DEFAULT NULL,
  `selling_price` decimal(10,2) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `sale_date` date DEFAULT NULL,
  `customer_contact` varchar(100) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `stock_id`, `quantity_sold`, `selling_price`, `total_price`, `sale_date`, `customer_contact`, `customer_id`, `flag`) VALUES
(25, 1, 50, 800.00, 1000.00, '2025-03-06', NULL, 3, 'inactive'),
(26, 5, 51, NULL, 1000.01, '2025-03-06', NULL, 1, 'inactive'),
(27, 5, 9, NULL, 100.00, '2025-03-07', NULL, 1, 'inactive'),
(28, 10, 1, NULL, 1000.00, '2025-03-27', NULL, 1, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `sales1`
--

CREATE TABLE `sales1` (
  `id` int(11) NOT NULL,
  `stock_id` int(11) DEFAULT NULL,
  `quantity_sold` int(11) DEFAULT NULL,
  `selling_price` decimal(10,2) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `sale_date` date DEFAULT NULL,
  `customer_contact` varchar(100) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales1`
--

INSERT INTO `sales1` (`id`, `stock_id`, `quantity_sold`, `selling_price`, `total_price`, `sale_date`, `customer_contact`, `customer_id`, `flag`) VALUES
(1, 1, 5, 30.00, 150.00, '2024-09-15', 'john.doe@example.com', 1, 'active'),
(3, 4, 1, 2600.00, 3000.00, '2024-10-15', 'Customer Contact-2', NULL, 'active'),
(4, 4, 1, 2600.00, 3000.00, '2024-10-15', 'Customer Contact-2', NULL, 'active'),
(5, 4, 1, 2600.00, 3000.00, '2024-10-15', 'Customer Contact-2', NULL, 'active'),
(6, 1, 0, NULL, 0.00, '2025-01-07', NULL, 3, 'active'),
(7, 1, 0, NULL, 0.00, '2025-01-07', NULL, 3, 'active'),
(8, 1, 0, NULL, 0.00, '2025-01-07', NULL, 3, 'active'),
(9, 1, 0, NULL, 0.00, '2025-01-07', NULL, 3, 'active'),
(10, 1, 0, NULL, 0.00, '2025-01-07', NULL, 3, 'active'),
(11, 1, 0, NULL, 0.00, '2025-01-07', NULL, 3, 'active'),
(12, 1, 0, NULL, 0.00, '2025-01-07', NULL, 3, 'active'),
(13, 1, 0, NULL, 0.00, '2025-01-07', NULL, 3, 'active'),
(14, 1, 0, NULL, 0.00, '2025-01-07', NULL, 3, 'active'),
(15, 1, 0, NULL, 0.00, '2025-01-07', NULL, 3, 'active'),
(16, 1, 2, NULL, 0.00, '2025-01-07', NULL, 3, 'active'),
(17, 1, 0, NULL, 0.00, '2025-01-07', NULL, 3, 'active'),
(18, 1, 0, NULL, 0.00, '2025-01-07', NULL, 3, 'active'),
(19, 1, 2, NULL, 0.00, '2025-01-07', NULL, 3, 'active'),
(20, 1, 2, NULL, 0.00, '2025-01-07', NULL, 3, 'active'),
(21, NULL, 99999, NULL, NULL, '2025-01-07', NULL, NULL, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `sfl_batch_transfer_all`
--

CREATE TABLE `sfl_batch_transfer_all` (
  `id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sfl_batch_transfer_all`
--

INSERT INTO `sfl_batch_transfer_all` (`id`, `file_name`, `flag`) VALUES
(1, 'css/*.*', 'active'),
(2, 'img/*.*', 'active'),
(3, 'lib/*.*', 'active'),
(4, '404.php_', 'active'),
(5, 'batch_compair_table_and_folder_list_difference.php', 'active'),
(6, 'batch_compare_files_infolder_with_table.php', 'active'),
(7, 'batch_copy_files_src_to_destination_folder_prod.php', 'active'),
(8, 'batch_copy_files_src_to_destination_folder.php', 'active'),
(9, 'dashboard.php', 'active'),
(10, 'language.php', 'active'),
(11, 'manage_customers.php', 'active'),
(12, 'manage_invoices.php', 'active'),
(13, 'manage_menuoptions.php', 'active'),
(14, 'manage_role_menu_options-2.php', 'active'),
(15, 'manage_roles.php', 'active'),
(16, 'manage_sales.php', 'active'),
(17, 'manage_stock.php', 'active'),
(18, 'manage_supplier_purchases.php', 'active'),
(19, 'generate_reports.php', 'active'),
(20, 'manage_suppliers.php', 'active'),
(21, 'manage_users.php', 'active'),
(22, 'manage_categories.php', 'active'),
(23, 'manage_menutags.php', 'active'),
(24, 'manage_models.php', 'active'),
(25, 'manage_inactive_records.php', 'active'),
(26, 'manage_sales_test.php', 'active'),
(27, 'batch_copy_files_src_to_destination_folder.php', 'active'),
(28, 'maintain_src_file_list.php', 'active'),
(29, 'manage_0104_MultyDropDownMenue.php', 'active'),
(30, 'index.php', 'active'),
(31, 'admin_dashboard.php', 'active'),
(32, 'login.php', 'active'),
(33, 'logout.php', 'active'),
(34, 'header.php', 'active'),
(35, 'style.css', 'active'),
(36, 'view_invoice.php', 'active'),
(37, 'footer.php', 'active'),
(38, 'db.php', 'active'),
(39, 'READ-ME.txt', 'active'),
(40, 'chtgpt_dspare_dparts_dmanagement_new1.sql', 'active'),
(41, 'images_cab-1536x480.jpg', 'active'),
(42, 'images_shock-absorbers.jpg', 'active'),
(43, 'images_nmc-award.jpg', 'active'),
(44, 'images_other-brands.jpg', 'active'),
(45, 'parameters.php', 'active'),
(46, 'db.php', 'active'),
(47, 'nmc-I.ico', 'active'),
(48, 'nmc-II.ico', 'active'),
(49, 'nmc.ico', 'active'),
(50, 'README-This.md', 'active'),
(51, 'nmc.jpg', 'active'),
(52, 'about.php', 'active'),
(53, 'contact.php', 'active'),
(54, 'differentiation.php', 'active'),
(55, 'products_&_services.php', 'active'),
(56, 'About_us0.png', 'active'),
(57, 'about-us1.jpg', 'active'),
(58, 'contact_us.png', 'active'),
(59, 'about-us2.jpg', 'active'),
(60, 'about-us3.jpg', 'active'),
(61, 'about-us4.jpg', 'active'),
(62, 'Products_&_Services_NMC_files.png', 'active'),
(63, 'kyb1.jpg', 'active'),
(64, 'EXCEL-G.jpg', 'active'),
(65, 'gas-a-just.jpg', 'active'),
(66, 'ultra-pic1.jpg', 'active'),
(67, 'AGV.jpg', 'active'),
(68, 'flyer1.jpg', 'active'),
(69, 'flyer2.jpg', 'active'),
(70, 'flyer2.jpg', 'active'),
(71, 'flyer3.jpg', 'active'),
(72, 'flyer4.jpg', 'active'),
(73, 'differentiation.png', 'active'),
(74, 'about-us1.jpg', 'active'),
(75, 'about-us2.jpg', 'active'),
(76, 'about-us3.jpg', 'active'),
(77, 'about-us4.jpg', 'active'),
(78, 'AGX.jpg', 'active'),
(79, 'footer1.php', 'active'),
(80, 'index11.php', 'active'),
(81, 'dashboard1.php', 'active'),
(82, 'sfl_batch_transfer_all.php', 'active'),
(83, 'header1_sidebar1.php', 'active'),
(84, 'header1_navbar1.php', 'active'),
(85, 'view_invoice.php', 'active'),
(86, 'dashboard1.php', 'active'),
(87, 'index11.php', 'active'),
(88, 'index1.php', 'active'),
(89, 'js/*.*', 'active'),
(90, 'manage_orders.php', 'active'),
(91, 'report_orders.php', 'active'),
(92, 'report_sales.php', 'active'),
(93, 'report_stocks.php', 'active'),
(94, 'report_supplier_purchases.php', 'active'),
(96, 'manage_types.php', 'active'),
(97, 'manage_brands.php', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `sfl_batch_transfer_chargpt`
--

CREATE TABLE `sfl_batch_transfer_chargpt` (
  `id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sfl_batch_transfer_chargpt`
--

INSERT INTO `sfl_batch_transfer_chargpt` (`id`, `file_name`, `flag`) VALUES
(9, 'dashboard.php', 'active'),
(11, 'manage_customers.php', 'active'),
(12, 'manage_invoices.php', 'active'),
(13, 'manage_menuoptions.php', 'active'),
(14, 'manage_role_menu_options-2.php', 'active'),
(15, 'manage_roles.php', 'active'),
(16, 'manage_sales.php', 'active'),
(17, 'manage_stock.php', 'active'),
(18, 'manage_supplier_purchases.php', 'active'),
(19, 'generate_reports.php', 'active'),
(20, 'manage_suppliers.php', 'active'),
(21, 'manage_users.php', 'active'),
(22, 'manage_categories.php', 'active'),
(23, 'manage_menutags.php', 'active'),
(24, 'manage_models.php', 'active'),
(25, 'manage_inactive_records.php', 'active'),
(26, 'manage_sales_test.php', 'active'),
(27, 'batch_copy_files_src_to_destination_folder.php', 'active'),
(28, 'maintain_src_file_list.php', 'active'),
(29, 'manage_0104_MultyDropDownMenue.php', 'active'),
(30, 'index.php', 'active'),
(31, 'admin_dashboard.php', 'active'),
(32, 'login.php', 'active'),
(33, 'logout.php', 'active'),
(34, 'header.php', 'active'),
(35, 'style.css', 'active'),
(36, 'view_invoice.php', 'active'),
(37, 'footer.php', 'active'),
(38, 'db.php', 'active'),
(39, 'READ-ME.txt', 'active'),
(40, 'chtgpt_dspare_dparts_dmanagement_new1.sql', 'active'),
(41, 'images_cab-1536x480.jpg', 'active'),
(42, 'images_shock-absorbers.jpg', 'active'),
(43, 'images_nmc-award.jpg', 'active'),
(44, 'images_other-brands.jpg', 'active'),
(45, 'parameters.php', 'active'),
(46, 'db.php', 'active'),
(47, 'nmc-I.ico', 'active'),
(48, 'nmc-II.ico', 'active'),
(49, 'nmc.ico', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `sfl_batch_transfer_remote_nonroot`
--

CREATE TABLE `sfl_batch_transfer_remote_nonroot` (
  `id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sfl_batch_transfer_remote_nonroot`
--

INSERT INTO `sfl_batch_transfer_remote_nonroot` (`id`, `file_name`, `flag`) VALUES
(1, 'css/*.*', 'active'),
(2, 'img/*.*', 'active'),
(3, 'lib/*.*', 'active'),
(4, '404.php_', 'active'),
(5, 'batch_compair_table_and_folder_list_difference.php', 'active'),
(6, 'batch_compare_files_infolder_with_table.php', 'active'),
(7, 'batch_copy_files_src_to_destination_folder_prod.php', 'active'),
(8, 'batch_copy_files_src_to_destination_folder.php', 'active'),
(9, 'index1.php', 'active'),
(10, 'language.php', 'active'),
(11, 'header1_navbar1.php', 'active'),
(12, 'header1_sidebar1.php', 'active'),
(13, 'js/*.*', 'active'),
(14, 'nmc.jpg', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `sfl_batch_transfer_remote_root`
--

CREATE TABLE `sfl_batch_transfer_remote_root` (
  `id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sfl_batch_transfer_remote_root`
--

INSERT INTO `sfl_batch_transfer_remote_root` (`id`, `file_name`, `flag`) VALUES
(1, 'test_transfer.php', 'inactive'),
(9, 'dashboard.php', 'active'),
(11, 'manage_customers.php', 'active'),
(12, 'manage_invoices.php', 'active'),
(13, 'manage_menuoptions.php', 'active'),
(14, 'manage_role_menu_options-2.php', 'active'),
(15, 'manage_roles.php', 'active'),
(16, 'manage_sales.php', 'active'),
(17, 'manage_stock.php', 'active'),
(18, 'manage_supplier_purchases.php', 'active'),
(19, 'generate_reports.php', 'active'),
(20, 'manage_suppliers.php', 'active'),
(21, 'manage_users.php', 'active'),
(22, 'manage_categories.php', 'active'),
(23, 'manage_menutags.php', 'active'),
(24, 'manage_models.php', 'active'),
(25, 'manage_inactive_records.php', 'active'),
(26, 'manage_sales_test.php', 'active'),
(27, 'batch_copy_files_src_to_destination_folder.php', 'active'),
(28, 'maintain_src_file_list.php', 'active'),
(29, 'manage_0104_MultyDropDownMenue.php', 'active'),
(30, 'index.php', 'active'),
(31, 'admin_dashboard.php', 'active'),
(32, 'login.php', 'active'),
(33, 'logout.php', 'active'),
(34, 'header.php', 'active'),
(35, 'style.css', 'active'),
(36, 'view_invoice.php', 'active'),
(37, 'footer.php', 'active'),
(38, 'db.php', 'active'),
(39, 'README-This.md', 'active'),
(40, 'chtgpt_dspare_dparts_dmanagement_new1.sql', 'active'),
(41, 'images_cab-1536x480.jpg', 'active'),
(42, 'images_shock-absorbers.jpg', 'active'),
(43, 'images_nmc-award.jpg', 'active'),
(44, 'images_other-brands.jpg', 'active'),
(45, 'parameters.php', 'active'),
(46, 'db.php', 'active'),
(47, 'nmc-I.ico', 'active'),
(48, 'nmc-II.ico', 'active'),
(49, 'nmc.ico', 'active'),
(50, 'About_us0.png', 'active'),
(51, 'about-us1.jpg', 'active'),
(52, 'about-us3.jpg', 'active'),
(53, 'about-us4.jpg', 'active'),
(54, 'about.php', 'active'),
(55, 'AGX.jpg', 'active'),
(56, 'contact_us.png', 'active'),
(57, 'contact-2.php', 'active'),
(58, 'contact.php', 'active'),
(59, 'differentiation.php', 'active'),
(60, 'differentiation.png', 'active'),
(61, 'en_US.svg', 'active'),
(62, 'EXCEL-G.jpg', 'active'),
(63, 'flyer1.jpg', 'active'),
(64, 'flyer2.jpg', 'active'),
(65, 'flyer3.jpg', 'active'),
(66, 'flyer4.jpg', 'active'),
(67, 'footer1.php', 'active'),
(68, 'gas-a-just.jpg', 'active'),
(69, 'header1_navbar1.php', 'active'),
(70, 'header1_sidebar1.php', 'active'),
(71, 'icon_Visit_us_en_US.png', 'active'),
(72, 'images_cab-1536x480.jpg', 'active'),
(73, 'images_nmc-award.jpg', 'active'),
(74, 'images_other-brands.jpg', 'active'),
(75, 'images_shock-absorbers.jpg', 'active'),
(76, 'index1.php', 'active'),
(77, 'KYB.png', 'active'),
(78, 'kyb1.jpg', 'active'),
(79, 'language.php', 'active'),
(80, 'nmc.jpg', 'active'),
(81, 'outline.php', 'active'),
(82, 'Products_&_Services_NMC_files.png', 'active'),
(83, 'products_&_services.php', 'active'),
(84, 'READ-ME.txt', 'active'),
(85, 'set_language.php', 'active'),
(86, 'signin.html', 'active'),
(87, 'signup.html', 'active'),
(88, 'subject_content.php', 'active'),
(89, 'table.html', 'active'),
(90, 'ultra-pic1.jpg', 'active'),
(91, 'css/*.*', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `src_file_list`
--

CREATE TABLE `src_file_list` (
  `id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `src_file_list`
--

INSERT INTO `src_file_list` (`id`, `file_name`, `flag`) VALUES
(2, 'dashboard.php', 'active'),
(3, 'manage_customers.php', 'active'),
(4, 'manage_invoices.php', 'active'),
(5, 'manage_menuoptions.php', 'active'),
(6, 'manage_role_menu_options-2.php', 'active'),
(7, 'manage_roles.php', 'active'),
(8, 'manage_sales.php', 'active'),
(9, 'manage_stock.php', 'active'),
(10, 'manage_supplier_purchases.php', 'active'),
(11, 'generate_reports.php', 'active'),
(12, 'manage_suppliers.php', 'active'),
(13, 'manage_users.php', 'active'),
(14, 'manage_categories.php', 'active'),
(15, 'manage_menutags.php', 'active'),
(16, 'manage_models.php', 'active'),
(17, 'manage_inactive_records.php', 'active'),
(18, 'manage_sales_test.php', 'active'),
(19, 'batch_copy_files_src_to_destination_folder.php', 'active'),
(20, 'maintain_src_file_list.php', 'active'),
(21, 'manage_0104_MultyDropDownMenue.php', 'active'),
(22, 'index.php', 'active'),
(23, 'admin_dashboard.php', 'active'),
(24, 'login.php', 'active'),
(25, 'logout.php', 'active'),
(26, 'header.php', 'active'),
(27, 'style.css', 'active'),
(28, 'view_invoice.php', 'active'),
(29, 'footer.php', 'active'),
(30, 'db.php', 'active'),
(31, 'README-This.md', 'active'),
(32, 'chtgpt_dspare_dparts_dmanagement_new1.sql', 'active'),
(33, 'images_cab-1536x480.jpg', 'active'),
(34, 'images_shock-absorbers.jpg', 'active'),
(35, 'images_nmc-award.jpg', 'active'),
(36, 'images_other-brands.jpg', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `src_file_list_backup1`
--

CREATE TABLE `src_file_list_backup1` (
  `id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `src_file_list_backup1`
--

INSERT INTO `src_file_list_backup1` (`id`, `file_name`, `flag`) VALUES
(1, 'test_transfer.php', 'inactive'),
(9, 'dashboard.php', 'active'),
(11, 'manage_customers.php', 'active'),
(12, 'manage_invoices.php', 'active'),
(13, 'manage_menuoptions.php', 'active'),
(14, 'manage_role_menu_options-2.php', 'active'),
(15, 'manage_roles.php', 'active'),
(16, 'manage_sales.php', 'active'),
(17, 'manage_stock.php', 'active'),
(18, 'manage_supplier_purchases.php', 'active'),
(19, 'generate_reports.php', 'active'),
(20, 'manage_suppliers.php', 'active'),
(21, 'manage_users.php', 'active'),
(22, 'manage_categories.php', 'active'),
(23, 'manage_menutags.php', 'active'),
(24, 'manage_models.php', 'active'),
(25, 'manage_inactive_records.php', 'active'),
(26, 'manage_sales_test.php', 'active'),
(27, 'batch_copy_files_src_to_destination_folder.php', 'active'),
(28, 'maintain_src_file_list.php', 'active'),
(29, 'manage_0104_MultyDropDownMenue.php', 'active'),
(30, 'index.php', 'active'),
(31, 'admin_dashboard.php', 'active'),
(32, 'login.php', 'active'),
(33, 'logout.php', 'active'),
(34, 'header.php', 'active'),
(35, 'style.css', 'active'),
(36, 'view_invoice.php', 'active'),
(37, 'footer.php', 'active'),
(38, 'db.php', 'active'),
(39, 'README-This.md', 'active'),
(40, 'chtgpt_dspare_dparts_dmanagement_new1.sql', 'active'),
(41, 'images_cab-1536x480.jpg', 'active'),
(42, 'images_shock-absorbers.jpg', 'active'),
(43, 'images_nmc-award.jpg', 'active'),
(44, 'images_other-brands.jpg', 'active'),
(45, 'parameters.php', 'active'),
(46, 'db.php', 'active'),
(47, 'nmc-I.ico', 'active'),
(48, 'nmc-II.ico', 'active'),
(49, 'nmc.ico', 'active'),
(50, 'About_us0.png', 'active'),
(51, 'about-us1.jpg', 'active'),
(52, 'about-us3.jpg', 'active'),
(53, 'about-us4.jpg', 'active'),
(54, 'about.php', 'active'),
(55, 'AGX.jpg', 'active'),
(56, 'contact_us.png', 'active'),
(57, 'contact-2.php', 'active'),
(58, 'contact.php', 'active'),
(59, 'differentiation.php', 'active'),
(60, 'differentiation.png', 'active'),
(61, 'en_US.svg', 'active'),
(62, 'EXCEL-G.jpg', 'active'),
(63, 'flyer1.jpg', 'active'),
(64, 'flyer2.jpg', 'active'),
(65, 'flyer3.jpg', 'active'),
(66, 'flyer4.jpg', 'active'),
(67, 'footer1.php', 'active'),
(68, 'gas-a-just.jpg', 'active'),
(69, 'header1_navbar1.php', 'active'),
(70, 'header1_sidebar1.php', 'active'),
(71, 'icon_Visit_us_en_US.png', 'active'),
(72, 'images_cab-1536x480.jpg', 'active'),
(73, 'images_nmc-award.jpg', 'active'),
(74, 'images_other-brands.jpg', 'active'),
(75, 'images_shock-absorbers.jpg', 'active'),
(76, 'index1.php', 'active'),
(77, 'KYB.png', 'active'),
(78, 'kyb1.jpg', 'active'),
(79, 'language.php', 'active'),
(80, 'nmc.jpg', 'active'),
(81, 'outline.php', 'active'),
(82, 'Products_&_Services_NMC_files.png', 'active'),
(83, 'products_&_services.php', 'active'),
(84, 'READ-ME.txt', 'active'),
(85, 'set_language.php', 'active'),
(86, 'signin.html', 'active'),
(87, 'signup.html', 'active'),
(88, 'subject_content.php', 'active'),
(89, 'table.html', 'active'),
(90, 'ultra-pic1.jpg', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `src_file_list_backup1_manual_transfer`
--

CREATE TABLE `src_file_list_backup1_manual_transfer` (
  `id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `src_file_list_backup1_manual_transfer`
--

INSERT INTO `src_file_list_backup1_manual_transfer` (`id`, `file_name`, `flag`) VALUES
(1, 'aatmp/*.*', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `src_file_list_backup2`
--

CREATE TABLE `src_file_list_backup2` (
  `id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `src_file_list_backup2`
--

INSERT INTO `src_file_list_backup2` (`id`, `file_name`, `flag`) VALUES
(1, 'test_transfer.php', 'inactive'),
(9, 'dashboard.php', 'active'),
(11, 'manage_customers.php', 'active'),
(12, 'manage_invoices.php', 'active'),
(13, 'manage_menuoptions.php', 'active'),
(14, 'manage_role_menu_options-2.php', 'active'),
(15, 'manage_roles.php', 'active'),
(16, 'manage_sales.php', 'active'),
(17, 'manage_stock.php', 'active'),
(18, 'manage_supplier_purchases.php', 'active'),
(19, 'generate_reports.php', 'active'),
(20, 'manage_suppliers.php', 'active'),
(21, 'manage_users.php', 'active'),
(22, 'manage_categories.php', 'active'),
(23, 'manage_menutags.php', 'active'),
(24, 'manage_models.php', 'active'),
(25, 'manage_inactive_records.php', 'active'),
(26, 'manage_sales_test.php', 'active'),
(27, 'batch_copy_files_src_to_destination_folder.php', 'active'),
(28, 'maintain_src_file_list.php', 'active'),
(29, 'manage_0104_MultyDropDownMenue.php', 'active'),
(30, 'index.php', 'active'),
(31, 'admin_dashboard.php', 'active'),
(32, 'login.php', 'active'),
(33, 'logout.php', 'active'),
(34, 'header.php', 'active'),
(35, 'style.css', 'active'),
(36, 'view_invoice.php', 'active'),
(37, 'footer.php', 'active'),
(38, 'db.php', 'active'),
(39, 'README-This.md', 'active'),
(40, 'chtgpt_dspare_dparts_dmanagement_new1.sql', 'active'),
(41, 'images_cab-1536x480.jpg', 'active'),
(42, 'images_shock-absorbers.jpg', 'active'),
(43, 'images_nmc-award.jpg', 'active'),
(44, 'images_other-brands.jpg', 'active'),
(45, 'parameters.php', 'active'),
(46, 'db.php', 'active'),
(47, 'nmc-I.ico', 'active'),
(48, 'nmc-II.ico', 'active'),
(49, 'nmc.ico', 'active'),
(50, 'About_us0.png', 'active'),
(51, 'about-us1.jpg', 'active'),
(52, 'about-us3.jpg', 'active'),
(53, 'about-us4.jpg', 'active'),
(54, 'about.php', 'active'),
(55, 'AGX.jpg', 'active'),
(56, 'contact_us.png', 'active'),
(57, 'contact-2.php', 'active'),
(58, 'contact.php', 'active'),
(59, 'differentiation.php', 'active'),
(60, 'differentiation.png', 'active'),
(61, 'en_US.svg', 'active'),
(62, 'EXCEL-G.jpg', 'active'),
(63, 'flyer1.jpg', 'active'),
(64, 'flyer2.jpg', 'active'),
(65, 'flyer3.jpg', 'active'),
(66, 'flyer4.jpg', 'active'),
(67, 'footer1.php', 'active'),
(68, 'gas-a-just.jpg', 'active'),
(69, 'header1_navbar1.php', 'active'),
(70, 'header1_sidebar1.php', 'active'),
(71, 'icon_Visit_us_en_US.png', 'active'),
(72, 'images_cab-1536x480.jpg', 'active'),
(73, 'images_nmc-award.jpg', 'active'),
(74, 'images_other-brands.jpg', 'active'),
(75, 'images_shock-absorbers.jpg', 'active'),
(76, 'index1.php', 'active'),
(77, 'KYB.png', 'active'),
(78, 'kyb1.jpg', 'active'),
(79, 'language.php', 'active'),
(80, 'nmc.jpg', 'active'),
(81, 'outline.php', 'active'),
(82, 'Products_&_Services_NMC_files.png', 'active'),
(83, 'products_&_services.php', 'active'),
(84, 'READ-ME.txt', 'active'),
(85, 'set_language.php', 'active'),
(86, 'signin.html', 'active'),
(87, 'signup.html', 'active'),
(88, 'subject_content.php', 'active'),
(89, 'table.html', 'active'),
(90, 'ultra-pic1.jpg', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `src_file_list_folder`
--

CREATE TABLE `src_file_list_folder` (
  `id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `src_file_list_folder`
--

INSERT INTO `src_file_list_folder` (`id`, `file_name`, `flag`) VALUES
(14374, 'css/*.*', 'active'),
(14375, 'img/*.*', 'active'),
(14376, 'lib/*.*', 'active'),
(14377, '404.php_', 'active'),
(14378, 'batch_compair_table_and_folder_list_difference.php', 'active'),
(14379, 'batch_compare_files_infolder_with_table.php', 'active'),
(14380, 'batch_copy_files_src_to_destination_folder_prod.php', 'active'),
(14381, 'batch_copy_files_src_to_destination_folder.php', 'active'),
(14382, 'dashboard.php', 'active'),
(14383, 'language.php', 'active'),
(14384, 'manage_customers.php', 'active'),
(14385, 'manage_invoices.php', 'active'),
(14386, 'manage_menuoptions.php', 'active'),
(14387, 'manage_role_menu_options-2.php', 'active'),
(14388, 'manage_roles.php', 'active'),
(14389, 'manage_sales.php', 'active'),
(14390, 'manage_stock.php', 'active'),
(14391, 'manage_supplier_purchases.php', 'active'),
(14392, 'generate_reports.php', 'active'),
(14393, 'manage_suppliers.php', 'active'),
(14394, 'manage_users.php', 'active'),
(14395, 'manage_categories.php', 'active'),
(14396, 'manage_menutags.php', 'active'),
(14397, 'manage_models.php', 'active'),
(14398, 'manage_inactive_records.php', 'active'),
(14399, 'manage_sales_test.php', 'active'),
(14400, 'batch_copy_files_src_to_destination_folder.php', 'active'),
(14401, 'maintain_src_file_list.php', 'active'),
(14402, 'manage_0104_MultyDropDownMenue.php Not Found', 'active'),
(14403, 'index.php', 'active'),
(14404, 'admin_dashboard.php', 'active'),
(14405, 'login.php', 'active'),
(14406, 'logout.php', 'active'),
(14407, 'header.php', 'active'),
(14408, 'style.css', 'active'),
(14409, 'view_invoice.php', 'active'),
(14410, 'footer.php', 'active'),
(14411, 'db.php', 'active'),
(14412, 'READ-ME.txt', 'active'),
(14413, 'chtgpt_dspare_dparts_dmanagement_new1.sql', 'active'),
(14414, 'images_cab-1536x480.jpg', 'active'),
(14415, 'images_shock-absorbers.jpg', 'active'),
(14416, 'images_nmc-award.jpg', 'active'),
(14417, 'images_other-brands.jpg', 'active'),
(14418, 'parameters.php', 'active'),
(14419, 'db.php', 'active'),
(14420, 'nmc-I.ico', 'active'),
(14421, 'nmc-II.ico', 'active'),
(14422, 'nmc.ico', 'active'),
(14423, 'README-This.md', 'active'),
(14424, 'nmc.jpg', 'active'),
(14425, 'about.php', 'active'),
(14426, 'contact.php', 'active'),
(14427, 'differentiation.php', 'active'),
(14428, 'products_&_services.php', 'active'),
(14429, 'About_us0.png', 'active'),
(14430, 'about-us1.jpg', 'active'),
(14431, 'contact_us.png', 'active'),
(14432, 'about-us2.jpg Not Found', 'active'),
(14433, 'about-us3.jpg', 'active'),
(14434, 'about-us4.jpg', 'active'),
(14435, 'Products_&_Services_NMC_files.png', 'active'),
(14436, 'kyb1.jpg', 'active'),
(14437, 'EXCEL-G.jpg', 'active'),
(14438, 'gas-a-just.jpg', 'active'),
(14439, 'ultra-pic1.jpg', 'active'),
(14440, 'AGV.jpg Not Found', 'active'),
(14441, 'flyer1.jpg', 'active'),
(14442, 'flyer2.jpg', 'active'),
(14443, 'flyer2.jpg', 'active'),
(14444, 'flyer3.jpg', 'active'),
(14445, 'flyer4.jpg', 'active'),
(14446, 'differentiation.png', 'active'),
(14447, 'about-us1.jpg', 'active'),
(14448, 'about-us2.jpg Not Found', 'active'),
(14449, 'about-us3.jpg', 'active'),
(14450, 'about-us4.jpg', 'active'),
(14451, 'AGX.jpg', 'active'),
(14452, 'footer1.php', 'active'),
(14453, 'index11.php', 'active'),
(14454, 'dashboard1.php', 'active'),
(14455, 'sfl_batch_transfer_all.php Not Found', 'active'),
(14456, 'header1_sidebar1.php', 'active'),
(14457, 'header1_navbar1.php', 'active'),
(14458, 'view_invoice.php', 'active'),
(14459, 'dashboard1.php', 'active'),
(14460, 'index11.php', 'active'),
(14461, 'index1.php', 'active'),
(14462, 'js/*.*', 'active'),
(14463, 'manage_orders.php', 'active'),
(14464, 'report_orders.php', 'active'),
(14465, 'report_sales.php', 'active'),
(14466, 'report_stocks.php', 'active'),
(14467, 'report_supplier_purchases.php', 'active'),
(14468, 'manage_types.php', 'active'),
(14469, 'manage_brands.php', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `src_file_list_test_table_1`
--

CREATE TABLE `src_file_list_test_table_1` (
  `id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `src_file_list_test_table_1`
--

INSERT INTO `src_file_list_test_table_1` (`id`, `file_name`, `flag`) VALUES
(1, 'dashboard.php', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `src_file_list_test_table_2`
--

CREATE TABLE `src_file_list_test_table_2` (
  `id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `src_file_list_test_table_2`
--

INSERT INTO `src_file_list_test_table_2` (`id`, `file_name`, `flag`) VALUES
(5, 'dashboard.php', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `stocks`
--

CREATE TABLE `stocks` (
  `id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `part_number` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `selling_price` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `model_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active',
  `brand_id` int(11) DEFAULT NULL,
  `type_id` int(11) NOT NULL DEFAULT 3,
  `pricelistpage` int(11) NOT NULL,
  `oem_number` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stocks`
--

INSERT INTO `stocks` (`id`, `location_id`, `part_number`, `description`, `image`, `category_id`, `cost`, `selling_price`, `stock_quantity`, `supplier_id`, `model_id`, `created_at`, `flag`, `brand_id`, `type_id`, `pricelistpage`, `oem_number`) VALUES
(10, 2, '333124', 'Mitsubishi Lancer ', '', 6, 34835.00, 35835.00, 9, 2, 1, '2025-03-20 06:53:14', 'active', 2, 3, 0, ''),
(11, 2, '333125', 'Mitsubishi Lancer LEFT', '', 6, 34835.00, 35835.00, 10, 2, 7, '2025-03-20 06:53:14', 'active', 2, 3, 0, ''),
(12, 2, '339114', 'Toyota Corolla ', '', 2, 37255.00, 38255.00, 10, 2, 8, '2025-03-20 09:52:05', 'active', 2, 3, 3, ''),
(13, 2, '339115', 'Toyota Corolla ', '', 2, 37255.00, 38255.00, 10, 2, 8, '2025-03-20 09:52:05', 'active', 2, 3, 3, ''),
(14, 2, '339242', 'HYBRIDE NHW30 -2009 (F)', '', 2, 49375.00, 50375.00, 10, 2, 1, '2025-03-20 10:05:49', 'active', 2, 3, 6, ''),
(15, 2, '339243', 'HYBRIDE NHW30 -2009 (F)', '', 2, 49375.00, 50375.00, 10, 2, 1, '2025-03-20 10:05:49', 'active', 2, 3, 6, ''),
(18, 2, '349035', 'HYBRIDE NHW30 -2009 (R)', '', 2, 27370.00, 28370.00, 10, 2, 9, '2025-03-20 10:18:19', 'active', 2, 3, 6, ''),
(19, 2, '333381', 'Lancer CS 1A, CS 3A 2000/2004 (F)', '', 6, 42060.00, 42060.00, 10, 2, 7, '2025-03-20 10:37:34', 'active', 2, 3, 23, ''),
(21, 2, '348016', 'Hybrid (R)', '', 1, 27370.00, 27370.00, 0, 2, 10, '2025-03-20 10:47:59', 'active', 2, 3, 40, ''),
(23, 2, '333331', 'JAZZ/FIT-GD1/2 2WD - 2001 Up (F)', '', 1, 44710.00, 0.00, 0, 2, 11, '2025-03-24 05:44:54', 'active', 2, 3, 40, ''),
(24, 2, '333332', 'JAZZ/FIT-GD1/2 2WD - 2001 Up (F)', '', 1, 44710.00, 0.00, 0, 2, 11, '2025-03-24 05:44:54', 'active', 2, 3, 40, ''),
(25, 2, '333238', 'B 14 97/99, SN 15, R 11 5 (INCH) - 97/20 (F)', '', 3, 40857.00, 40857.00, 0, 2, 12, '2025-03-24 06:03:24', 'active', 2, 3, 18, ''),
(26, 2, '333239', 'B 14 97/99, SN 15, R 11 5 (INCH) - 97/20 (F)', '', 3, 40857.00, 40857.00, 0, 2, 12, '2025-03-24 06:03:24', 'active', 2, 3, 18, ''),
(28, 2, '348001', 'HYBRID - NUP 10 (R)', '', 2, 24950.00, 24950.00, 0, 2, 13, '2025-03-24 06:30:58', 'active', 2, 3, 1, ''),
(29, 2, '333114', 'M (F)', '', 2, 27375.00, 27375.00, 0, 2, 8, '2025-03-24 06:39:14', 'active', 2, 3, 4, ''),
(30, 2, '333115', 'M (F)', '', 2, 27375.00, 27375.00, 0, 2, 8, '2025-03-24 06:39:14', 'active', 2, 3, 4, ''),
(32, 2, '333116', 'CE100 (R)', '', 2, 40985.00, 40985.00, 0, 2, 8, '2025-03-24 06:46:34', 'active', 2, 3, 4, ''),
(33, 2, '333117', 'CE100 (R)', '', 2, 40985.00, 40985.00, 0, 2, 8, '2025-03-24 06:46:34', 'active', 2, 3, 4, ''),
(34, 2, '333308', 'Wing Road Y11   09/2004     (F)', '', 3, 34830.00, 34830.00, 0, 2, 14, '2025-03-24 07:59:34', 'active', 2, 3, 15, ''),
(35, 2, '333309', 'Wing Road Y11   09/2004     (F)', '', 3, 34830.00, 34830.00, 0, 2, 14, '2025-03-24 07:59:34', 'active', 2, 3, 15, ''),
(36, 2, '341279', 'B15 - (4WD) (F)', '', 3, 29420.00, 29420.00, 0, 2, 12, '2025-03-24 08:09:20', 'active', 2, 3, 19, ''),
(37, 2, '333455', 'NZE 141 - DOMESTIC (F)', '', 2, 45275.00, 45275.00, 0, 2, 16, '2025-03-24 08:16:34', 'active', 2, 3, 1, ''),
(38, 2, '333456', 'NZE 141 - DOMESTIC (F)', '', 2, 45275.00, 45275.00, 0, 2, 16, '2025-03-24 08:16:34', 'active', 2, 3, 1, ''),
(39, 2, '333386', 'NTZ 249, ZZT 240 - 2001 / 03 (F)', '', 2, 28954.00, 28954.00, 0, 2, 1, '2025-03-24 08:23:14', 'active', 2, 3, 1, ''),
(40, 2, '333387', 'NTZ 249, ZZT 240 - 2001 / 03 (F)', '', 2, 28954.00, 28954.00, 0, 2, 1, '2025-03-24 08:23:14', 'active', 2, 3, 1, ''),
(42, 2, 'SM7101', 'YARIS (F)', '', 2, 7790.68, 7790.68, 0, 2, 17, '2025-03-24 08:23:14', 'active', 2, 1, 1, '48609-12420'),
(43, 2, 'BS4002', 'YARIS / VIOS NCP91, 92, 93 BUMP WITH COVER ASSAY', '', 2, 1872.03, 1872.03, 0, 2, 17, '2025-03-27 05:38:39', 'active', 2, 2, 1, '48750-0D060');

-- --------------------------------------------------------

--
-- Table structure for table `subscription`
--

CREATE TABLE `subscription` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `stock_id` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `subscription` enum('active','inactive') NOT NULL DEFAULT 'inactive',
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscription`
--

INSERT INTO `subscription` (`id`, `user_id`, `stock_id`, `date`, `subscription`, `flag`) VALUES
(6, 1, 1, '2024-09-15', 'inactive', 'active'),
(7, 2, 5, '2024-11-05', 'inactive', 'active'),
(8, 2, 1, '2024-12-24', 'inactive', 'active'),
(9, 12, 9, '2025-02-10', 'active', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `supplier_name` varchar(100) NOT NULL,
  `contact_info` varchar(255) DEFAULT NULL,
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `supplier_name`, `contact_info`, `flag`) VALUES
(1, 'Anura Trading Co.', 'info@anura-group.com', 'active'),
(2, 'Douglas & sons', 'dsl@dougls.lk', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_purchases`
--

CREATE TABLE `supplier_purchases` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `part_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `purchase_date` date NOT NULL,
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier_purchases`
--

INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `part_id`, `quantity`, `cost`, `purchase_date`, `flag`) VALUES
(6, 1, 1, 501, 1500.00, '2024-09-15', 'active'),
(7, 2, 5, 10, 100.00, '2024-11-05', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `types`
--

CREATE TABLE `types` (
  `id` int(11) NOT NULL,
  `type_name` varchar(25) NOT NULL,
  `flag` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `types`
--

INSERT INTO `types` (`id`, `type_name`, `flag`, `created_at`, `updated_at`) VALUES
(1, 'KYB STRUT MOUNT', 'active', '2025-03-26 10:13:25', '2025-03-26 10:13:25'),
(2, 'RUBBER PARTS', 'active', '2025-03-26 10:13:25', '2025-03-26 10:13:25'),
(3, 'Unspecified', 'active', '2025-03-27 04:54:10', '2025-03-27 04:54:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `img` varchar(30) NOT NULL DEFAULT 'shantha2.jpg',
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role_id`, `img`, `flag`) VALUES
(1, 'admin', 'admin@gmail.com', 'pwadmin', 1, 'Yashmila-2.jpg', 'active'),
(10, 'master', 'master@gmail.com', 'pwmaster', 5, 'user-default.png', 'active'),
(11, 'yashmila', 'yashmila@gmail.com', 'pwyashmila', 1, 'Yashmila-2.jpg', 'active'),
(12, 'saman', 'saman@gmail.com', 'pwsaman', 3, 'Yashmila-2.jpg', 'active'),
(13, 'dammika', 'dammika@gmail.com', 'pwdammika', 1, 'dammika.png', 'active'),
(14, 'shantha', 'shantha@gmail.com', 'pwshantha', 1, 'Shantha-2.png', 'active'),
(16, 'kasun', 'kasun@gmail.com', '$2y$10$ecTu4F/fIfXsPGQaC32n2uP8dm0JEAnzOWKqmFVlTHwYhXtacH6Wy', 1, 'shantha2.jpg', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`user_id`),
  ADD KEY `part_id` (`stock_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `sale_id` (`sale_id`);

--
-- Indexes for table `language`
--
ALTER TABLE `language`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu_options`
--
ALTER TABLE `menu_options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu_tags`
--
ALTER TABLE `menu_tags`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `models`
--
ALTER TABLE `models`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `part_id` (`part_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_crud`
--
ALTER TABLE `role_crud`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `role_menu_options`
--
ALTER TABLE `role_menu_options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `sales_ibfk_1` (`stock_id`);

--
-- Indexes for table `sales1`
--
ALTER TABLE `sales1`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `sales_ibfk_1` (`stock_id`);

--
-- Indexes for table `sfl_batch_transfer_all`
--
ALTER TABLE `sfl_batch_transfer_all`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sfl_batch_transfer_chargpt`
--
ALTER TABLE `sfl_batch_transfer_chargpt`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sfl_batch_transfer_remote_nonroot`
--
ALTER TABLE `sfl_batch_transfer_remote_nonroot`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sfl_batch_transfer_remote_root`
--
ALTER TABLE `sfl_batch_transfer_remote_root`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `src_file_list`
--
ALTER TABLE `src_file_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `src_file_list_backup1`
--
ALTER TABLE `src_file_list_backup1`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `src_file_list_backup1_manual_transfer`
--
ALTER TABLE `src_file_list_backup1_manual_transfer`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `src_file_list_backup2`
--
ALTER TABLE `src_file_list_backup2`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `src_file_list_folder`
--
ALTER TABLE `src_file_list_folder`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `src_file_list_test_table_1`
--
ALTER TABLE `src_file_list_test_table_1`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `src_file_list_test_table_2`
--
ALTER TABLE `src_file_list_test_table_2`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stocks`
--
ALTER TABLE `stocks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `part_number` (`part_number`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `model_id` (`model_id`),
  ADD KEY `fk_stocks_location` (`location_id`),
  ADD KEY `fk_stocks_types` (`type_id`);

--
-- Indexes for table `subscription`
--
ALTER TABLE `subscription`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`user_id`),
  ADD KEY `part_id` (`stock_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supplier_purchases`
--
ALTER TABLE `supplier_purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `part_id` (`part_id`);

--
-- Indexes for table `types`
--
ALTER TABLE `types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `language`
--
ALTER TABLE `language`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `menu_options`
--
ALTER TABLE `menu_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `menu_tags`
--
ALTER TABLE `menu_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `models`
--
ALTER TABLE `models`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `role_crud`
--
ALTER TABLE `role_crud`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `role_menu_options`
--
ALTER TABLE `role_menu_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `sales1`
--
ALTER TABLE `sales1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `sfl_batch_transfer_all`
--
ALTER TABLE `sfl_batch_transfer_all`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT for table `sfl_batch_transfer_chargpt`
--
ALTER TABLE `sfl_batch_transfer_chargpt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `sfl_batch_transfer_remote_nonroot`
--
ALTER TABLE `sfl_batch_transfer_remote_nonroot`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `sfl_batch_transfer_remote_root`
--
ALTER TABLE `sfl_batch_transfer_remote_root`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `src_file_list`
--
ALTER TABLE `src_file_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `src_file_list_backup1`
--
ALTER TABLE `src_file_list_backup1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `src_file_list_backup1_manual_transfer`
--
ALTER TABLE `src_file_list_backup1_manual_transfer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `src_file_list_backup2`
--
ALTER TABLE `src_file_list_backup2`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `src_file_list_folder`
--
ALTER TABLE `src_file_list_folder`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14470;

--
-- AUTO_INCREMENT for table `src_file_list_test_table_1`
--
ALTER TABLE `src_file_list_test_table_1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `src_file_list_test_table_2`
--
ALTER TABLE `src_file_list_test_table_2`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `stocks`
--
ALTER TABLE `stocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `subscription`
--
ALTER TABLE `subscription`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `supplier_purchases`
--
ALTER TABLE `supplier_purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `types`
--
ALTER TABLE `types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`),
  ADD CONSTRAINT `invoice_items_ibfk_2` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`part_id`) REFERENCES `stocks` (`id`);

--
-- Constraints for table `role_crud`
--
ALTER TABLE `role_crud`
  ADD CONSTRAINT `role_crud_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Constraints for table `stocks`
--
ALTER TABLE `stocks`
  ADD CONSTRAINT `fk_stocks_types` FOREIGN KEY (`type_id`) REFERENCES `types` (`id`);

--
-- Constraints for table `supplier_purchases`
--
ALTER TABLE `supplier_purchases`
  ADD CONSTRAINT `supplier_purchases_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `supplier_purchases_ibfk_2` FOREIGN KEY (`part_id`) REFERENCES `stocks` (`id`);
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
