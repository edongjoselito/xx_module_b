-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 21, 2026 at 11:34 AM
-- Server version: 8.4.7
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `xx_module_b`
--

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int NOT NULL,
  `company_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_address` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_telephone` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_mobile` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_mobile` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_deactivated` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `company_name`, `company_address`, `company_telephone`, `company_email`, `owner_name`, `owner_mobile`, `owner_email`, `contact_name`, `contact_mobile`, `contact_email`, `is_deactivated`, `created_at`, `updated_at`) VALUES
(1, 'Euro Expo', 'Boulevard de l\'Europe, 69680 Chassieu, France', '+33 1 41 56 78 00', 'mail.customerservice.hdq@example.com', 'Benjamin Smith', '+33 6 12 34 56 78', 'b.smith@example.com', 'Marie Dubois', '+33 6 98 76 54 32', 'm.dubois@example.com', 0, '2026-02-21 07:01:01', '2026-02-21 07:01:01'),
(2, 'asdffjkdfj', 'kdfjadksfj', '09889dafs', 'ksdfadkfdsjkfds@gmail.com', 'owerkdnf', 'ddk', 'adf@domain.com', 'dfad', 'dfadf', 'dfdf@domain.com', 0, '2026-02-21 04:58:40', '2026-02-21 05:09:54');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `company_id` int NOT NULL,
  `gtin` varchar(14) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_fr` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_en` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_fr` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_of_origin` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gross_weight` decimal(10,3) NOT NULL DEFAULT '0.000',
  `net_weight` decimal(10,3) NOT NULL DEFAULT '0.000',
  `weight_unit` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'g',
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_hidden` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `company_id`, `gtin`, `name_en`, `name_fr`, `description_en`, `description_fr`, `brand`, `country_of_origin`, `gross_weight`, `net_weight`, `weight_unit`, `image_path`, `is_hidden`, `created_at`, `updated_at`) VALUES
(1, 1, '03000123456789', 'Organic Apple Juice', 'Jus de pomme biologique', 'Our organic apple juice is pressed from 100% fresh organic apples, with no added sugars or preservatives. Rich in vitamin C and antioxidants, it\'s an ideal choice for your daily healthy diet.', 'Notre jus de pomme biologique est pressé à partir de 100% de pommes biologiques fraîches, sans sucre ajouté ni conservateurs. Riche en vitamine C et en antioxydants, c\'est le choix idéal pour votre alimentation quotidienne saine.', 'Green Orchard', 'France', 1.100, 1.000, 'L', NULL, 0, '2026-02-21 07:01:01', '2026-02-21 07:01:01'),
(2, 1, '23232', 'dsfasdfds', 'dsaffdfdsfds', 'sdfasdfasdf', 'sdfsadf', 'dfasdf', 'sdfdsfds', 0.000, 0.000, 'g', NULL, 0, '2026-02-21 05:46:32', '2026-02-21 05:46:32'),
(3, 1, '3434343', 'dsfsdfsdf', 'dfasdf', 'sfsdf', 'dsfds', 'sdfdsa', 'sdfdsf', 0.000, 0.000, 'g', NULL, 0, '2026-02-21 06:53:27', '2026-02-21 06:53:27'),
(4, 1, '1234567890121', 'sfadsf', 'sdfdas', 'sdfdsaf', 'dsfadsf', 'dsfads', 'dsfasdfd', 0.000, 0.000, 'g', NULL, 0, '2026-02-21 07:07:46', '2026-02-21 07:07:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_products_gtin` (`gtin`),
  ADD KEY `fk_products_company` (`company_id`),
  ADD KEY `idx_products_gtin` (`gtin`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
