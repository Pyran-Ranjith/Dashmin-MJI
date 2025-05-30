-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 20, 2025 at 04:33 PM
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
-- Database: `chtgpt_dspare_dparts_dmanagement_new1`
--

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
(2, 'Toyota', 'active');

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
(1, 'John', 'Doe', 'john.doe@example.com', '1234567890', '1234 Elm St, Springfield', '2024-10-15 04:39:16', 'active'),
(3, 'Ranjith', 'Jayawardana', 'ranjithimas@gmail.com', '3445667', '25 Hilevel Road. Kottawa', '0000-00-00 00:00:00', 'inactive');

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
(3, 'INV-5301', 1, '2024-10-15', 0.00, 'active'),
(4, 'INV-4134', 1, '2024-10-15', 0.00, 'active'),
(5, 'INV-6670', 1, '2024-10-15', 3150.00, 'active'),
(6, 'INV-4135', 1, '2024-10-15', 3150.00, 'active'),
(7, 'INV-1200', 1, '2024-10-31', 150.00, 'active'),
(8, 'INV-9167', 1, '2024-11-01', 150.00, 'active'),
(9, 'INV-9571', 1, '2024-11-01', 150.00, 'active'),
(10, 'INV-4009', 1, '2024-11-01', 150.00, 'active'),
(11, 'INV-2169', 1, '2024-12-19', 150.00, 'active'),
(12, 'INV-6101', 1, '2024-12-19', 150.00, 'active'),
(13, 'INV-2453', 3, '2024-12-24', 3000.00, 'active');

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
(1, 5, 1, 5, 30.00, 150.00, 0, 'active'),
(2, 5, 3, 1, 3000.00, 3000.00, 0, 'active'),
(3, 6, 1, 5, 30.00, 150.00, 0, 'active'),
(4, 6, 3, 1, 3000.00, 3000.00, 0, 'active'),
(5, 9, 1, 5, 30.00, 150.00, 1, 'active'),
(6, 10, 1, 5, 30.00, 150.00, 1, 'active'),
(7, 11, 1, 5, 30.00, 150.00, 1, 'active'),
(8, 12, 1, 5, 30.00, 150.00, 1, 'active'),
(9, 13, 4, 1, 3000.00, 3000.00, 0, 'active');

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
  `menu_name` varchar(50) NOT NULL,
  `menu_link` varchar(100) NOT NULL DEFAULT '#',
  `menu_table` varchar(30) NOT NULL,
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_options`
--

INSERT INTO `menu_options` (`id`, `menu_name`, `menu_link`, `menu_table`, `flag`) VALUES
(1, 'Dashboard', 'dashboard.php', '', 'active'),
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
(22, 'Mange Sales-Test', 'manage_sales_test.php', '', 'active'),
(23, 'Batch copy files src to destination fold', 'batch_copy_files_src_to_destination_folder.php', '', 'active'),
(24, 'Maintain MT Src File List', 'maintain_src_file_list.php', '', 'active'),
(25, 'Maintain 0104 multyDropDownMenue.php', 'manage_0104_MultyDropDownMenue.php', '', 'active'),
(26, 'Index', 'index.php', '', 'active'),
(27, 'Admin dashboard', 'admin_dashboard.php', '', 'active'),
(28, 'Login', 'login.php', '', 'active'),
(29, 'Admin dashboard', 'admin_dashboard.php', '', 'inactive'),
(30, 'Batch copy_files_src_to_destination_folder', 'batch_copy_files_src_to_destination_folder.php', '', 'active');

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
(10, 'Hide', 'active');

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
(1, 'ALLION/PREMEIO', 'active'),
(2, 'AURIS', 'active'),
(3, 'AVANZA', 'active');

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
(5, 'Master', 'active');

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
(3, 1, 3, 3, 2, 'active'),
(4, 2, 1, 1, 1, 'active'),
(5, 2, 3, 3, 2, 'active'),
(6, 5, 1, 1, 1, 'active'),
(7, 5, 2, 2, 2, 'active'),
(8, 5, 3, 3, 2, 'active'),
(9, 5, 4, 100, 6, 'active'),
(10, 5, 5, 100, 6, 'active'),
(11, 5, 6, 100, 2, 'active'),
(12, 5, 7, 100, 2, 'active'),
(13, 5, 8, 100, 2, 'active'),
(14, 5, 9, 100, 2, 'active'),
(15, 5, 10, 5, 0, 'active'),
(17, 3, 1, 2, 1, 'active'),
(18, 2, 6, 2, 6, 'active'),
(19, 3, 10, 1, 8, 'active'),
(20, 2, 10, 1, 8, 'active'),
(21, 5, 10, 100, 8, 'active'),
(22, 5, 11, 6, 2, 'active'),
(23, 2, 1, 0, 0, 'active'),
(24, 5, 12, 7, 2, 'active'),
(25, 5, 13, 8, 8, 'active'),
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
(38, 2, 25, 20, 2, 'active'),
(39, 5, 26, 21, 2, 'active'),
(40, 1, 27, 22, 2, 'active'),
(41, 5, 25, 1, 6, 'active');

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
(20, 1, 2, NULL, 0.00, '2025-01-07', NULL, 3, 'active');

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
(1, 'README-This.md', 'inactive');

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
(40, 'chtgpt_dspare_dparts_dmanagement_new1.sql', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `stocks`
--

CREATE TABLE `stocks` (
  `id` int(11) NOT NULL,
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
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stocks`
--

INSERT INTO `stocks` (`id`, `part_number`, `description`, `image`, `category_id`, `cost`, `selling_price`, `stock_quantity`, `supplier_id`, `model_id`, `created_at`, `flag`) VALUES
(1, '9340012/13', 'NZT 260 - 2008/2020 K\'lassic Japan', '', 2, 34460.00, 44798.00, 100, 1, 1, '2024-11-01 08:12:16', 'active'),
(5, '339066/67', 'ZRE 152 - 2007 Up', '', 2, 51430.00, 66859.00, 50, 1, 2, '2024-11-04 08:17:52', 'active'),
(8, '3330110/11', 'F652 1.5L - 2013 Up', '', 2, 36985.00, 48080.50, 100, 1, 3, '2025-01-07 00:22:45', 'active');

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
(1, 'KEYB Shock absorber', 'keyb@keyb@co.com.com', 'active'),
(2, 'AURIS', 'auris@auris.com', 'inactive'),
(4, 'AVANZA', 'avanza@avanza.com', 'inactive');

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
(6, 1, 1, 50, 1500.00, '2024-09-15', 'active'),
(7, 2, 5, 10, 100.00, '2024-11-05', 'active'),
(8, 2, 1, 10, 9000.00, '2024-12-24', 'active');

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
  `flag` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role_id`, `flag`) VALUES
(1, 'admin', 'admin@namaratne.com', 'pwadmin', 1, 'active'),
(2, 'manager', 'manager@namaratne.com', 'pwmanager', 2, 'active'),
(3, 'employee', 'employeee@namaratne.com', 'pwemployeee', 3, 'active'),
(10, 'master', 'master@gmail.com', 'pwmaster', 5, 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `stocks`
--
ALTER TABLE `stocks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `part_number` (`part_number`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `model_id` (`model_id`);

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
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `menu_options`
--
ALTER TABLE `menu_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `menu_tags`
--
ALTER TABLE `menu_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `models`
--
ALTER TABLE `models`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `role_menu_options`
--
ALTER TABLE `role_menu_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `sales1`
--
ALTER TABLE `sales1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `src_file_list`
--
ALTER TABLE `src_file_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `src_file_list_backup1`
--
ALTER TABLE `src_file_list_backup1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `stocks`
--
ALTER TABLE `stocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `supplier_purchases`
--
ALTER TABLE `supplier_purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Constraints for table `stocks`
--
ALTER TABLE `stocks`
  ADD CONSTRAINT `fk_role` FOREIGN KEY (`model_id`) REFERENCES `models` (`id`),
  ADD CONSTRAINT `stocks_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `stocks_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `stocks_ibfk_3` FOREIGN KEY (`model_id`) REFERENCES `models` (`id`);

--
-- Constraints for table `supplier_purchases`
--
ALTER TABLE `supplier_purchases`
  ADD CONSTRAINT `supplier_purchases_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `supplier_purchases_ibfk_2` FOREIGN KEY (`part_id`) REFERENCES `stocks` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
