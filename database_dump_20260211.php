<?php

/*

-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Vært: 127.0.0.1:3306
-- Genereringstid: 11. 02 2026 kl. 13:16:41
-- Serverversion: 8.4.7
-- PHP-version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `toy_collection_db`
--

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `collection_toys`
--

DROP TABLE IF EXISTS `collection_toys`;
CREATE TABLE IF NOT EXISTS `collection_toys` (
  `id` int NOT NULL AUTO_INCREMENT,
  `master_toy_id` int NOT NULL,
  `date_added` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `acquisition_status` enum('Arrived','Ordered','Pre-ordered') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Arrived',
  `expected_arrival_date` date DEFAULT NULL,
  `source_id` int DEFAULT NULL,
  `storage_id` int DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `purchase_price` decimal(10,2) DEFAULT NULL,
  `current_value` decimal(10,2) DEFAULT NULL,
  `personal_toy_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `condition` enum('Mint','Near Mint','Good','Fair','Poor','Broken') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Good',
  `user_comments` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `completeness_grade` enum('Complete','Incomplete','Sealed','Custom') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Incomplete',
  `is_loose` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `master_toy_id` (`master_toy_id`),
  KEY `idx_parent_source` (`source_id`),
  KEY `idx_parent_storage` (`storage_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `collection_toys`
--

INSERT INTO `collection_toys` (`id`, `master_toy_id`, `date_added`, `acquisition_status`, `expected_arrival_date`, `source_id`, `storage_id`, `purchase_date`, `purchase_price`, `current_value`, `personal_toy_id`, `condition`, `user_comments`, `completeness_grade`, `is_loose`) VALUES
(1, 11, '2026-01-31 17:28:24', 'Arrived', NULL, NULL, NULL, '2026-01-31', NULL, NULL, NULL, 'Good', NULL, 'Complete', 1),
(2, 10, '2026-01-31 17:36:45', 'Arrived', NULL, NULL, NULL, '2026-01-31', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(3, 10, '2026-01-31 19:43:28', 'Arrived', NULL, NULL, NULL, '2026-01-31', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(4, 10, '2026-01-31 19:44:03', 'Pre-ordered', NULL, NULL, NULL, '2026-01-31', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(6, 10, '2026-01-31 20:03:01', 'Arrived', NULL, NULL, NULL, '2026-01-31', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(7, 10, '2026-01-31 20:24:25', 'Arrived', NULL, NULL, NULL, '2026-01-31', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(8, 10, '2026-01-31 20:26:25', 'Ordered', NULL, NULL, NULL, '2026-01-31', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(10, 10, '2026-01-31 20:34:19', 'Arrived', NULL, NULL, NULL, '2026-01-31', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(11, 10, '2026-01-31 20:38:57', 'Arrived', NULL, NULL, NULL, '2026-01-31', NULL, NULL, NULL, 'Mint', NULL, NULL, 1),
(12, 10, '2026-01-31 20:54:04', 'Ordered', NULL, NULL, NULL, '2026-01-31', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(13, 10, '2026-01-31 20:54:59', 'Arrived', NULL, NULL, NULL, '2026-01-31', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(14, 10, '2026-01-31 21:01:04', 'Arrived', NULL, NULL, NULL, '2026-01-31', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(15, 10, '2026-01-31 21:22:16', 'Arrived', NULL, NULL, NULL, '2026-01-31', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(16, 10, '2026-02-01 07:30:59', 'Pre-ordered', NULL, NULL, 3, '2026-02-01', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(17, 2, '2026-02-01 07:58:25', 'Arrived', NULL, NULL, NULL, '2026-02-01', NULL, NULL, 'K0078', NULL, NULL, NULL, 1),
(18, 1, '2026-02-01 08:07:09', 'Ordered', NULL, 5, 3, '2026-01-28', 109.75, NULL, '0007', 'Mint', 'A note', 'Complete', 0),
(19, 4, '2026-02-01 20:10:28', 'Arrived', NULL, NULL, 3, '2026-02-01', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(20, 7, '2026-02-05 15:58:44', 'Pre-ordered', NULL, 5, NULL, '2026-02-04', 5010.00, NULL, '78944', 'Near Mint', NULL, 'Incomplete', 0),
(21, 5, '2026-02-08 16:52:32', 'Arrived', NULL, NULL, NULL, '2026-02-08', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(22, 6, '2026-02-08 17:18:15', 'Arrived', NULL, NULL, NULL, '2026-02-08', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(23, 24, '2026-02-08 17:27:00', 'Arrived', NULL, NULL, NULL, '2026-02-08', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(24, 23, '2026-02-08 17:27:47', 'Arrived', NULL, NULL, NULL, '2026-02-08', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(25, 7, '2026-02-09 09:30:12', 'Arrived', NULL, NULL, NULL, '2026-02-09', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(26, 5, '2026-02-09 09:36:56', 'Arrived', NULL, NULL, NULL, '2026-02-09', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(27, 10, '2026-02-09 09:43:41', 'Arrived', NULL, NULL, NULL, '2026-02-09', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(28, 10, '2026-02-09 09:46:39', 'Arrived', NULL, NULL, NULL, '2026-02-09', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(29, 4, '2026-02-09 10:31:17', 'Arrived', NULL, NULL, NULL, '2026-02-09', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(30, 10, '2026-02-09 10:58:39', 'Arrived', NULL, NULL, NULL, '2026-02-09', NULL, NULL, NULL, NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `collection_toy_items`
--

DROP TABLE IF EXISTS `collection_toy_items`;
CREATE TABLE IF NOT EXISTS `collection_toy_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `collection_toy_id` int NOT NULL,
  `master_toy_item_id` int NOT NULL,
  `quantity_owned` int DEFAULT '0',
  `condition` enum('Mint','Near Mint','Good','Fair','Poor','Broken') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_reproduction` enum('Original','Reproduction','Unknown') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_loose` tinyint(1) DEFAULT '1',
  `personal_item_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `acquisition_status` enum('Arrived','Ordered','Pre-ordered') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expected_arrival_date` date DEFAULT NULL,
  `source_id` int DEFAULT NULL,
  `storage_id` int DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `purchase_price` decimal(10,2) DEFAULT NULL,
  `current_value` decimal(10,2) DEFAULT NULL,
  `notes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_comments` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `collection_toy_id` (`collection_toy_id`),
  KEY `master_toy_item_id` (`master_toy_item_id`),
  KEY `source_id` (`source_id`),
  KEY `storage_id` (`storage_id`)
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `collection_toy_items`
--

INSERT INTO `collection_toy_items` (`id`, `collection_toy_id`, `master_toy_item_id`, `quantity_owned`, `condition`, `is_reproduction`, `is_loose`, `personal_item_id`, `acquisition_status`, `expected_arrival_date`, `source_id`, `storage_id`, `purchase_date`, `purchase_price`, `current_value`, `notes`, `user_comments`) VALUES
(3, 4, 21, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 8, 18, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 10, 129, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 10, 131, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 11, 20, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 12, 22, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 13, 21, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 14, 19, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 15, 20, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 16, 19, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 16, 21, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(23, 18, 1, 1, 'Mint', 'Original', 0, '0007', '', '2026-01-31', 5, NULL, '2026-01-26', 50.85, NULL, NULL, 'Repro of the old Kenner version'),
(24, 19, 96, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(25, 19, 98, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(27, 20, 91, 1, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(28, 2, 129, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(29, 3, 131, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(30, 6, 129, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(31, 6, 131, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(32, 6, 132, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(33, 6, 135, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(34, 6, 135, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(35, 6, 133, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(36, 6, 130, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(45, 7, 130, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(47, 7, 131, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(48, 7, 132, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(49, 7, 133, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(50, 21, 27, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(51, 22, 28, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(52, 23, 86, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(53, 24, 85, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(54, 20, 90, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(55, 23, 87, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(56, 17, 24, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(57, 17, 39, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(58, 20, 91, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(59, 25, 89, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(60, 25, 91, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(61, 25, 90, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(62, 26, 27, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(63, 26, 57, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(64, 27, 129, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(65, 27, 131, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(66, 27, 132, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(67, 27, 135, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(68, 27, 133, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(69, 27, 130, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(70, 28, 129, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(71, 28, 131, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(72, 28, 132, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(73, 28, 135, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(74, 28, 133, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(75, 28, 130, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(76, 29, 96, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(77, 29, 98, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(78, 29, 97, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(79, 29, 136, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(80, 30, 129, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(81, 30, 131, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(82, 30, 132, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(83, 30, 135, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(84, 30, 133, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(85, 30, 130, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `collection_toy_item_media_map`
--

DROP TABLE IF EXISTS `collection_toy_item_media_map`;
CREATE TABLE IF NOT EXISTS `collection_toy_item_media_map` (
  `collection_toy_item_id` int NOT NULL,
  `media_file_id` int NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `sort_order` int DEFAULT '0',
  `is_main` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`collection_toy_item_id`,`media_file_id`),
  KEY `media_file_id` (`media_file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `collection_toy_item_media_map`
--

INSERT INTO `collection_toy_item_media_map` (`collection_toy_item_id`, `media_file_id`, `is_primary`, `sort_order`, `is_main`) VALUES
(23, 2, 0, 0, 1),
(24, 5, 0, 0, 1),
(25, 6, 0, 0, 1),
(27, 15, 0, 0, 1);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `collection_toy_media_map`
--

DROP TABLE IF EXISTS `collection_toy_media_map`;
CREATE TABLE IF NOT EXISTS `collection_toy_media_map` (
  `collection_toy_id` int NOT NULL,
  `media_file_id` int NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `sort_order` int DEFAULT '0',
  `is_main` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`collection_toy_id`,`media_file_id`),
  KEY `media_file_id` (`media_file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `collection_toy_media_map`
--

INSERT INTO `collection_toy_media_map` (`collection_toy_id`, `media_file_id`, `is_primary`, `sort_order`, `is_main`) VALUES
(2, 17, 0, 0, 1),
(3, 18, 0, 0, 1),
(4, 19, 0, 0, 1),
(6, 21, 0, 0, 1),
(10, 29, 0, 0, 1),
(17, 28, 0, 0, 1),
(18, 3, 0, 0, 1),
(19, 4, 0, 0, 0),
(19, 7, 0, 0, 1),
(19, 9, 0, 0, 0),
(20, 13, 0, 0, 1),
(20, 16, 0, 0, 0),
(23, 27, 0, 0, 1),
(25, 39, 0, 0, 1),
(28, 40, 0, 0, 1),
(29, 41, 0, 0, 1),
(30, 42, 0, 0, 1);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `entertainment_sources`
--

DROP TABLE IF EXISTS `entertainment_sources`;
CREATE TABLE IF NOT EXISTS `entertainment_sources` (
  `id` int NOT NULL AUTO_INCREMENT,
  `universe_id` int NOT NULL COMMENT 'Fx Star Wars universe ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Fx The Empire Strikes Back',
  `type` enum('Movie','Series','Game','Book','Comic','Other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Other',
  `release_year` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `universe_id` (`universe_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `entertainment_sources`
--

INSERT INTO `entertainment_sources` (`id`, `universe_id`, `name`, `type`, `release_year`) VALUES
(5, 1, 'Star Wars', 'Movie', 1977),
(6, 1, 'The Empire Strikes Back', 'Movie', 1980),
(8, 1, 'The Book of Boba Fett', 'Series', 2024),
(10, 1, 'Return of the Jedi', 'Movie', 1983);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `import_items`
--

DROP TABLE IF EXISTS `import_items`;
CREATE TABLE IF NOT EXISTS `import_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `source_id` int NOT NULL,
  `master_toy_id` int DEFAULT NULL,
  `external_id` varchar(100) NOT NULL,
  `external_url` varchar(255) NOT NULL,
  `last_imported_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_import_item` (`source_id`,`external_id`),
  KEY `fk_import_item_toy` (`master_toy_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Data dump for tabellen `import_items`
--

INSERT INTO `import_items` (`id`, `source_id`, `master_toy_id`, `external_id`, `external_url`, `last_imported_at`, `created_at`) VALUES
(1, 1, 28, '1988', 'https://galacticfigures.com/figureDetails.aspx?id=1988', '2026-02-10 15:42:03', '2026-02-10 15:38:18'),
(2, 1, 2, '1949', 'https://galacticfigures.com/figureDetails.aspx?id=1949', '2026-02-10 15:42:51', '2026-02-10 15:42:51'),
(4, 1, 29, '1935', 'https://galacticfigures.com/figureDetails.aspx?id=1935', '2026-02-10 16:09:46', '2026-02-10 16:09:46');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `import_logs`
--

DROP TABLE IF EXISTS `import_logs`;
CREATE TABLE IF NOT EXISTS `import_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `import_item_id` int DEFAULT NULL,
  `source_id` int NOT NULL,
  `action` varchar(20) NOT NULL,
  `message` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_import_logs_item` (`import_item_id`),
  KEY `fk_import_log_source` (`source_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Data dump for tabellen `import_logs`
--

INSERT INTO `import_logs` (`id`, `import_item_id`, `source_id`, `action`, `message`, `created_at`) VALUES
(1, NULL, 1, 'ERROR', 'Failed to import C-3PO: SQLSTATE[HY093]: Invalid parameter number: parameter was not defined', '2026-02-10 15:25:05'),
(2, NULL, 1, 'ERROR', 'Failed to import C-3PO: SQLSTATE[HY093]: Invalid parameter number: parameter was not defined', '2026-02-10 15:30:56'),
(3, NULL, 1, 'ERROR', 'Failed to import C-3PO: SQLSTATE[HY093]: Invalid parameter number: parameter was not defined', '2026-02-10 15:31:36'),
(4, NULL, 1, 'ERROR', 'Failed to import C-3PO: SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: a foreign key constraint fails (`toy_collection_db`.`master_toys`, CONSTRAINT `fk_toy_ent_source` FOREIGN KEY (`entertainment_source_id`) REFERENCES `entertainment_sources` (`id`) ON DELETE SET NULL)', '2026-02-10 15:34:10'),
(5, NULL, 1, 'ERROR', 'Failed to import C-3PO: SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: a foreign key constraint fails (`toy_collection_db`.`master_toys`, CONSTRAINT `fk_toy_ent_source` FOREIGN KEY (`entertainment_source_id`) REFERENCES `entertainment_sources` (`id`) ON DELETE SET NULL)', '2026-02-10 15:34:26'),
(6, NULL, 1, 'ERROR', 'Failed to import C-3PO: SQLSTATE[23000]: Integrity constraint violation: 1048 Column \'line_id\' cannot be null', '2026-02-10 15:36:15'),
(7, 1, 1, 'CREATED', 'Imported via Frontend', '2026-02-10 15:38:18'),
(8, 1, 1, 'UPDATED', 'Imported via Frontend', '2026-02-10 15:42:03'),
(9, 2, 1, 'UPDATED', 'Imported via Frontend', '2026-02-10 15:42:51'),
(10, NULL, 1, 'ERROR', 'Failed to import Death Star Droid: SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: a foreign key constraint fails (`toy_collection_db`.`import_items`, CONSTRAINT `fk_import_item_toy` FOREIGN KEY (`master_toy_id`) REFERENCES `master_toys` (`id`) ON DELETE CASCADE)', '2026-02-10 16:03:37'),
(11, 4, 1, 'CREATED', 'Imported via Frontend', '2026-02-10 16:09:46');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `import_sources`
--

DROP TABLE IF EXISTS `import_sources`;
CREATE TABLE IF NOT EXISTS `import_sources` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `base_url` varchar(255) NOT NULL,
  `driver_class` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Data dump for tabellen `import_sources`
--

INSERT INTO `import_sources` (`id`, `name`, `slug`, `base_url`, `driver_class`, `is_active`, `created_at`) VALUES
(1, 'Galactic Figures', 'galactic_figures', 'https://galacticfigures.com', 'CollectionApp\\Modules\\Importer\\Drivers\\GalacticFiguresDriver', 1, '2026-02-10 14:45:40'),
(2, 'Action Figure 411', 'af411', 'actionfigure411.com', 'CollectionApp\\Modules\\Importer\\Drivers\\ActionFigure411Driver', 1, '2026-02-10 18:01:35'),
(3, 'Jedi Temple Archives', 'jta', 'jeditemplearchives.com', 'CollectionApp\\Modules\\Importer\\Drivers\\JediTempleArchivesDriver', 1, '2026-02-10 18:14:27'),
(4, 'The Toy Collectors Guide', 'ttcg', 'thetoycollectorsguide.com', 'CollectionApp\\Modules\\Importer\\Drivers\\TheToyCollectorsGuideDriver', 1, '2026-02-10 18:24:00'),
(5, 'Galactic Collector', 'gc', 'galacticcollector.com', 'CollectionApp\\Modules\\Importer\\Drivers\\GalacticCollectorDriver', 1, '2026-02-10 18:46:09'),
(6, 'Star Wars Collector', 'swc', 'starwarscollector.com', 'CollectionApp\\Modules\\Importer\\Drivers\\StarWarsCollectorDriver', 1, '2026-02-10 18:50:56');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `manufacturers`
--

DROP TABLE IF EXISTS `manufacturers`;
CREATE TABLE IF NOT EXISTS `manufacturers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `show_on_dashboard` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `name_2` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `manufacturers`
--

INSERT INTO `manufacturers` (`id`, `name`, `show_on_dashboard`) VALUES
(1, 'Kenner', 1),
(2, 'Hasbro', 1),
(3, 'Palitoy', 1),
(4, 'Takara', 1),
(5, 'Bandai', 1),
(6, 'Gentle Giant', 1),
(7, 'Hot Toys', 0),
(8, 'Sideshow Collectibles', 1),
(9, 'Funko', 0);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `master_toys`
--

DROP TABLE IF EXISTS `master_toys`;
CREATE TABLE IF NOT EXISTS `master_toys` (
  `id` int NOT NULL AUTO_INCREMENT,
  `line_id` int NOT NULL,
  `product_type_id` int DEFAULT NULL,
  `entertainment_source_id` int DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `release_year` int DEFAULT NULL,
  `wave_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assortment_sku` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exclusivity_note` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `line_id` (`line_id`),
  KEY `product_type_id` (`product_type_id`),
  KEY `idx_master_name` (`name`(250)),
  KEY `entertainment_source_id` (`entertainment_source_id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `master_toys`
--

INSERT INTO `master_toys` (`id`, `line_id`, `product_type_id`, `entertainment_source_id`, `name`, `release_year`, `wave_number`, `assortment_sku`, `exclusivity_note`) VALUES
(1, 1, 1, 5, 'Luke Skywalker', 1978, NULL, NULL, NULL),
(2, 1, 1, 5, 'Darth Vader', 1978, NULL, NULL, NULL),
(3, 1, 1, 6, 'Han Solo', 1978, NULL, NULL, NULL),
(4, 1, 1, 6, 'Chewbacca in Snowtrooper Uniform for Hoth', 1978, '', '', NULL),
(5, 1, 1, 5, 'Princess Leia Organa', 1978, NULL, NULL, NULL),
(6, 1, 1, 5, 'Stormtrooper', 1978, NULL, NULL, NULL),
(7, 1, 1, 6, 'Boba Fett', 1981, '', '', NULL),
(8, 1, 1, 6, 'Yoda', 1981, '', '', NULL),
(10, 3, 1, 8, 'Boba Fett (Morak)', 2023, '', '', NULL),
(23, 3, 4, 5, 'Boba Fetti - Morak 2 jo jo', 1981, '12', '23', NULL),
(24, 1, 1, NULL, 'Chewie', 1983, '', '', NULL),
(25, 6, 0, NULL, 'Luke Skywalker', NULL, '', '', NULL),
(28, 1, 1, 5, 'C-3PO', 0, '', '', NULL),
(29, 1, 1, 5, 'Death Star Droid', 0, '', '', NULL);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `master_toy_items`
--

DROP TABLE IF EXISTS `master_toy_items`;
CREATE TABLE IF NOT EXISTS `master_toy_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `master_toy_id` int NOT NULL,
  `subject_id` int NOT NULL,
  `quantity` int DEFAULT '1',
  `variant_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `master_toy_id` (`master_toy_id`),
  KEY `subject_id` (`subject_id`)
) ENGINE=InnoDB AUTO_INCREMENT=137 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `master_toy_items`
--

INSERT INTO `master_toy_items` (`id`, `master_toy_id`, `subject_id`, `quantity`, `variant_description`) VALUES
(1, 1, 73, 1, 'Tip extends'),
(23, 1, 1, 1, 'Standard'),
(24, 2, 8, 1, 'Standard'),
(25, 3, 3, 1, 'Standard'),
(27, 5, 2, 1, 'Standard'),
(28, 6, 9, 1, 'Standard'),
(38, 1, 79, 1, 'Original Cardback'),
(39, 2, 80, 1, 'Original Cardback'),
(40, 3, 81, 1, 'Original Cardback'),
(42, 5, 83, 1, 'Original Cardback'),
(43, 6, 84, 1, 'Original Cardback'),
(53, 1, 86, 1, 'Clear Bubble'),
(54, 2, 87, 1, 'Clear Bubble'),
(55, 3, 88, 1, 'Clear Bubble'),
(57, 5, 90, 1, 'Clear Bubble'),
(58, 6, 91, 1, 'Clear Bubble'),
(68, 8, 18, 1, ''),
(69, 8, 85, 1, ''),
(70, 8, 92, 1, ''),
(85, 23, 17, 1, ''),
(86, 24, 4, 1, ''),
(87, 24, 89, 1, ''),
(88, 24, 82, 1, ''),
(89, 7, 17, 1, 'Standard'),
(90, 7, 78, 1, 'Original Cardback'),
(91, 7, 77, 1, 'Clear Bubble'),
(92, 25, 1, 1, ''),
(93, 25, 86, 1, ''),
(94, 25, 79, 1, ''),
(95, 25, 46, 1, ''),
(96, 4, 4, 1, 'Standard'),
(97, 4, 82, 1, 'Original Cardback'),
(98, 4, 89, 1, 'Clear Bubble'),
(129, 10, 17, 1, 'gg'),
(130, 10, 74, 1, 'gg'),
(131, 10, 75, 1, 'gg'),
(132, 10, 76, 1, 'gg'),
(133, 10, 78, 1, 'gg'),
(135, 10, 77, 1, 'aa'),
(136, 4, 82, 1, '');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `master_toy_item_media_map`
--

DROP TABLE IF EXISTS `master_toy_item_media_map`;
CREATE TABLE IF NOT EXISTS `master_toy_item_media_map` (
  `master_toy_item_id` int NOT NULL,
  `media_file_id` int NOT NULL,
  `is_main` tinyint(1) DEFAULT '0',
  `sort_order` int DEFAULT '0',
  PRIMARY KEY (`master_toy_item_id`,`media_file_id`),
  KEY `media_file_id` (`media_file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `master_toy_item_media_map`
--

INSERT INTO `master_toy_item_media_map` (`master_toy_item_id`, `media_file_id`, `is_main`, `sort_order`) VALUES
(89, 10, 1, 0),
(90, 11, 1, 0),
(91, 12, 1, 0);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `master_toy_media_map`
--

DROP TABLE IF EXISTS `master_toy_media_map`;
CREATE TABLE IF NOT EXISTS `master_toy_media_map` (
  `master_toy_id` int NOT NULL,
  `media_file_id` int NOT NULL,
  `is_main` tinyint(1) DEFAULT '0',
  `sort_order` int DEFAULT '0',
  PRIMARY KEY (`master_toy_id`,`media_file_id`),
  KEY `media_file_id` (`media_file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `master_toy_media_map`
--

INSERT INTO `master_toy_media_map` (`master_toy_id`, `media_file_id`, `is_main`, `sort_order`) VALUES
(1, 3, 1, 0),
(2, 4, 1, 0),
(3, 38, 1, 0),
(4, 34, 0, 0),
(4, 35, 1, 0),
(5, 36, 1, 0),
(6, 37, 1, 0),
(7, 8, 1, 0),
(10, 20, 1, 0);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `media_files`
--

DROP TABLE IF EXISTS `media_files`;
CREATE TABLE IF NOT EXISTS `media_files` (
  `id` int NOT NULL AUTO_INCREMENT,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` enum('Image','PDF','Document','Video') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Image',
  `original_filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `media_files`
--

INSERT INTO `media_files` (`id`, `file_path`, `file_type`, `original_filename`, `user_comment`, `uploaded_at`) VALUES
(2, 'http://localhost/toy_collection/assets/uploads/img_697fb285c33c6.jpg', 'Image', NULL, '', '2026-02-01 20:07:33'),
(3, 'http://localhost/toy_collection/assets/uploads/img_697fb28f43333.jpg', 'Image', NULL, NULL, '2026-02-01 20:07:43'),
(4, 'http://localhost/toy_collection/assets/uploads/img_697fb34ac8344.jpg', 'Image', NULL, '', '2026-02-01 20:10:50'),
(5, 'http://localhost/toy_collection/assets/uploads/img_697fb35a20893.jpg', 'Image', NULL, '', '2026-02-01 20:11:06'),
(6, 'http://localhost/toy_collection/assets/uploads/img_697fb36321beb.jpg', 'Image', NULL, '', '2026-02-01 20:11:15'),
(7, 'http://localhost/toy_collection/assets/uploads/img_6980ba53bdcd3.jpg', 'Image', NULL, '', '2026-02-02 14:53:07'),
(8, 'http://localhost/toy_collection/assets/uploads/img_6983aa6095b8d.jpg', 'Image', NULL, NULL, '2026-02-04 20:21:52'),
(9, 'http://localhost/toy_collection/assets/uploads/img_6983aa71a34f8.jpg', 'Image', NULL, NULL, '2026-02-04 20:22:09'),
(10, 'http://localhost/toy_collection/assets/uploads/img_6983aa8287540.jpg', 'Image', NULL, NULL, '2026-02-04 20:22:26'),
(11, 'http://localhost/toy_collection/assets/uploads/img_6983aa873e1cd.jpg', 'Image', NULL, NULL, '2026-02-04 20:22:31'),
(12, 'http://localhost/toy_collection/assets/uploads/img_6983aa8c3e732.jpg', 'Image', NULL, NULL, '2026-02-04 20:22:36'),
(13, 'http://localhost/toy_collection/assets/uploads/img_6984be6d1fc07.jpg', 'Image', NULL, NULL, '2026-02-05 15:59:41'),
(15, 'http://localhost/toy_collection/assets/uploads/img_6984be7896f13.jpg', 'Image', NULL, NULL, '2026-02-05 15:59:52'),
(16, 'http://localhost/toy_collection/assets/uploads/img_6984c1a37d7ec.jpg', 'Image', NULL, NULL, '2026-02-05 16:13:23'),
(17, 'http://localhost/toy_collection/assets/uploads/img_6986169d704b4.jpg', 'Image', NULL, NULL, '2026-02-06 16:28:13'),
(18, 'http://localhost/toy_collection/assets/uploads/img_698616ac356b3.jpg', 'Image', NULL, NULL, '2026-02-06 16:28:28'),
(19, 'http://localhost/toy_collection/assets/uploads/img_698616b7b20f3.jpg', 'Image', NULL, NULL, '2026-02-06 16:28:39'),
(20, 'http://localhost/toy_collection/assets/uploads/img_698660fb2aa5f.jpg', 'Image', NULL, NULL, '2026-02-06 21:45:31'),
(21, 'http://localhost/toy_collection/assets/uploads/img_6988616b3a1f8.jpg', 'Image', NULL, '', '2026-02-08 10:11:55'),
(27, 'http://localhost/toy_collection/assets/uploads/img_6988c9d8e030b.jpg', 'Image', NULL, '', '2026-02-08 17:37:28'),
(28, 'http://localhost/toy_collection/assets/uploads/img_6988c9f9e87fe.jpg', 'Image', NULL, '', '2026-02-08 17:38:01'),
(29, 'http://localhost/toy_collection/assets/uploads/img_6988cabe4b5be.jpg', 'Image', NULL, NULL, '2026-02-08 17:41:18'),
(34, 'http://localhost/toy_collection/assets/uploads/img_6988fff3df4b7.jpg', 'Image', NULL, NULL, '2026-02-08 21:28:19'),
(35, 'http://localhost/toy_collection/assets/uploads/img_6988fffe3b83a.jpg', 'Image', NULL, '', '2026-02-08 21:28:30'),
(36, 'http://localhost/toy_collection/assets/uploads/img_698900460344d.jpg', 'Image', NULL, NULL, '2026-02-08 21:29:42'),
(37, 'http://localhost/toy_collection/assets/uploads/img_6989005460c09.jpg', 'Image', NULL, NULL, '2026-02-08 21:29:56'),
(38, 'http://localhost/toy_collection/assets/uploads/img_698900b51d28a.jpg', 'Image', NULL, NULL, '2026-02-08 21:31:33'),
(39, 'http://localhost/toy_collection/assets/uploads/img_6989a92b4fffa.jpg', 'Image', NULL, NULL, '2026-02-09 09:30:19'),
(40, 'http://localhost/toy_collection/assets/uploads/img_6989ad055f1d1.jpg', 'Image', NULL, NULL, '2026-02-09 09:46:45'),
(41, 'http://localhost/toy_collection/assets/uploads/img_6989b77b192b2.jpg', 'Image', NULL, NULL, '2026-02-09 10:31:23'),
(42, 'http://localhost/toy_collection/assets/uploads/img_6989bde67d524.jpg', 'Image', NULL, NULL, '2026-02-09 10:58:46');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `media_file_tags_map`
--

DROP TABLE IF EXISTS `media_file_tags_map`;
CREATE TABLE IF NOT EXISTS `media_file_tags_map` (
  `media_file_id` int NOT NULL,
  `tag_id` int NOT NULL,
  PRIMARY KEY (`media_file_id`,`tag_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `media_file_tags_map`
--

INSERT INTO `media_file_tags_map` (`media_file_id`, `tag_id`) VALUES
(4, 18),
(5, 18),
(21, 18),
(27, 18),
(28, 18),
(6, 27),
(6, 29);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `media_tags`
--

DROP TABLE IF EXISTS `media_tags`;
CREATE TABLE IF NOT EXISTS `media_tags` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tag_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tag_name` (`tag_name`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `media_tags`
--

INSERT INTO `media_tags` (`id`, `tag_name`) VALUES
(29, 'Accessories'),
(22, 'Box (Back)'),
(21, 'Box (Front)'),
(23, 'Box (Side)'),
(27, 'Bubble/Blister'),
(25, 'Card (Back)'),
(37, 'Card (Front)'),
(34, 'Damage Detail'),
(18, 'Figure'),
(36, 'Group Shot'),
(26, 'Insert/Tray'),
(31, 'Instructions'),
(28, 'Loose Item'),
(20, 'Playset'),
(33, 'Proof of Purchase/Points'),
(32, 'Sticker Sheet'),
(35, 'Variation Detail'),
(19, 'Vehicle'),
(30, 'Weapons');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `product_types`
--

DROP TABLE IF EXISTS `product_types`;
CREATE TABLE IF NOT EXISTS `product_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `type_name` (`type_name`),
  UNIQUE KEY `type_name_2` (`type_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `product_types`
--

INSERT INTO `product_types` (`id`, `type_name`) VALUES
(2, '3-Pack Figures'),
(4, 'Creature'),
(1, 'Single Figure'),
(3, 'Vehicle');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `sources`
--

DROP TABLE IF EXISTS `sources`;
CREATE TABLE IF NOT EXISTS `sources` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `website_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `sources`
--

INSERT INTO `sources` (`id`, `name`, `website_url`) VALUES
(1, 'Amazon.co.uk', NULL),
(2, 'Amazon.de', NULL),
(3, 'Hasbro Pulse EU', NULL),
(4, 'dba', NULL),
(5, 'Amazon.com', NULL);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `storage_units`
--

DROP TABLE IF EXISTS `storage_units`;
CREATE TABLE IF NOT EXISTS `storage_units` (
  `id` int NOT NULL AUTO_INCREMENT,
  `box_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location_room` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `box_code` (`box_code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `storage_units`
--

INSERT INTO `storage_units` (`id`, `box_code`, `description`, `location_room`, `name`) VALUES
(3, 'B00003', '', 'dsafagratar ', '4898sd');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `subjects`
--

DROP TABLE IF EXISTS `subjects`;
CREATE TABLE IF NOT EXISTS `subjects` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('Character','Vehicle','Environment','Creature','Accessory','Packaging','Paperwork') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Accessory',
  `faction` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_subject_name` (`name`(250))
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `subjects`
--

INSERT INTO `subjects` (`id`, `name`, `type`, `faction`) VALUES
(1, 'Luke Skywalker', 'Character', 'Rebel Alliance'),
(2, 'Princess Leia Organa', 'Character', 'Rebel Alliance'),
(3, 'Han Solo', 'Character', 'Rebel Alliance'),
(4, 'Chewbacca', 'Character', 'Rebel Alliance'),
(5, 'Obi-Wan Kenobi', 'Character', 'Rebel Alliance'),
(6, 'C-3PO', 'Character', 'Rebel Alliance'),
(7, 'R2-D2', 'Character', 'Rebel Alliance'),
(8, 'Darth Vader', 'Character', 'Empire'),
(9, 'Stormtrooper', 'Character', 'Empire'),
(10, 'Death Squad Commander', 'Character', 'Empire'),
(11, 'Jawa', 'Character', 'Neutral'),
(12, 'Tusken Raider (Sand People)', 'Character', 'Neutral'),
(13, 'Greedo', 'Character', 'Neutral'),
(14, 'Hammerhead', 'Character', 'Neutral'),
(15, 'Snaggletooth', 'Character', 'Neutral'),
(16, 'Walrus Man', 'Character', 'Neutral'),
(17, 'Boba Fett', 'Character', 'Neutral'),
(18, 'Yoda', 'Character', 'Rebel Alliance'),
(19, 'Lando Calrissian', 'Character', 'Rebel Alliance'),
(20, 'Bespin Security Guard', 'Character', 'Rebel Alliance'),
(21, 'Luke Skywalker (Bespin Fatigues)', 'Character', 'Rebel Alliance'),
(22, 'Han Solo (Hoth Gear)', 'Character', 'Rebel Alliance'),
(23, 'Princess Leia (Hoth Gear)', 'Character', 'Rebel Alliance'),
(24, 'Rebel Soldier (Hoth)', 'Character', 'Rebel Alliance'),
(25, 'Snowtrooper', 'Character', 'Empire'),
(26, 'AT-AT Driver', 'Character', 'Empire'),
(27, 'TIE Fighter Pilot', 'Character', 'Empire'),
(28, 'Bossk', 'Character', 'Neutral'),
(29, 'IG-88', 'Character', 'Neutral'),
(30, 'Zuckuss', 'Character', 'Neutral'),
(31, '4-LOM', 'Character', 'Neutral'),
(32, 'Dengar', 'Character', 'Neutral'),
(33, 'Luke Skywalker (Jedi Knight)', 'Character', 'Rebel Alliance'),
(34, 'Princess Leia (Boushh Disguise)', 'Character', 'Rebel Alliance'),
(35, 'Emperor Palpatine', 'Character', 'Empire'),
(36, 'Royal Guard', 'Character', 'Empire'),
(37, 'Gamorrean Guard', 'Character', 'Neutral'),
(38, 'Bib Fortuna', 'Character', 'Neutral'),
(39, 'Wicket W. Warrick', 'Character', 'Rebel Alliance'),
(40, 'Admiral Ackbar', 'Character', 'Rebel Alliance'),
(41, 'Nien Nunb', 'Character', 'Rebel Alliance'),
(42, 'Biker Scout', 'Character', 'Empire'),
(43, 'Yak Face', 'Character', 'Neutral'),
(44, 'Amanaman', 'Character', 'Neutral'),
(45, 'Imperial Gunner', 'Character', 'Empire'),
(46, 'Luke Skywalker (Stormtrooper Disguise)', 'Character', 'Rebel Alliance'),
(47, 'Rancor', 'Creature', 'Neutral'),
(48, 'Wampa', 'Creature', 'Neutral'),
(49, 'Tauntaun', 'Creature', 'Rebel Alliance'),
(50, 'Dewback', 'Creature', 'Empire'),
(51, 'X-Wing Fighter', 'Vehicle', 'Rebel Alliance'),
(52, 'TIE Fighter', 'Vehicle', 'Empire'),
(53, 'Millennium Falcon', 'Vehicle', 'Rebel Alliance'),
(54, 'Land Speeder', 'Vehicle', 'Rebel Alliance'),
(55, 'Slave I', 'Vehicle', 'Neutral'),
(56, 'Snowspeeder', 'Vehicle', 'Rebel Alliance'),
(57, 'AT-AT (All Terrain Armored Transport)', 'Vehicle', 'Empire'),
(58, 'AT-ST (Scout Walker)', 'Vehicle', 'Empire'),
(59, 'Imperial Shuttle', 'Vehicle', 'Empire'),
(60, 'TIE Interceptor', 'Vehicle', 'Empire'),
(61, 'B-Wing Fighter', 'Vehicle', 'Rebel Alliance'),
(62, 'Y-Wing Fighter', 'Vehicle', 'Rebel Alliance'),
(63, 'Speeder Bike', 'Vehicle', 'Empire'),
(64, 'Death Star Space Station', 'Environment', 'Empire'),
(65, 'Droid Factory', 'Environment', 'Neutral'),
(66, 'Land of the Jawas', 'Environment', 'Neutral'),
(67, 'Creature Cantina', 'Environment', 'Neutral'),
(68, 'Turret and Probot', 'Environment', 'Empire'),
(69, 'Dagobah Action Playset', 'Environment', 'Rebel Alliance'),
(70, 'Cloud City Playset', 'Environment', 'Neutral'),
(71, 'Jabba the Hutt Dungeon', 'Environment', 'Neutral'),
(72, 'Ewok Village', 'Environment', 'Rebel Alliance'),
(73, 'Lightsaber (Yellow)', 'Accessory', 'Rebel Alliance'),
(74, 'Boba Fett Helmet', 'Accessory', NULL),
(75, 'Boba Fett Blaster', 'Accessory', NULL),
(76, 'Boba Fett Blaster Rifle', 'Accessory', NULL),
(77, 'Boba Fett Blister Bubble', 'Packaging', NULL),
(78, 'Boba Fett Cardback', 'Packaging', NULL),
(79, 'Luke Skywalker Cardback', 'Packaging', NULL),
(80, 'Darth Vader Cardback', 'Packaging', NULL),
(81, 'Han Solo Cardback', 'Packaging', NULL),
(82, 'Chewbacca Cardback', 'Packaging', NULL),
(83, 'Princess Leia Organa Cardback', 'Packaging', NULL),
(84, 'Stormtrooper Cardback', 'Packaging', NULL),
(85, 'Yoda Cardback', 'Packaging', NULL),
(86, 'Luke Skywalker Blister Bubble', 'Packaging', NULL),
(87, 'Darth Vader Blister Bubble', 'Packaging', NULL),
(88, 'Han Solo Blister Bubble', 'Packaging', NULL),
(89, 'Chewbacca Blister Bubble', 'Packaging', NULL),
(90, 'Princess Leia Organa Blister Bubble', 'Packaging', NULL),
(91, 'Stormtrooper Blister Bubble', 'Packaging', NULL),
(92, 'Yoda Blister Bubble', 'Packaging', NULL),
(93, 'Jango Fett', 'Character', 'Neutral'),
(95, 'Jabba the Hutt', 'Character', 'Neutral'),
(96, 'Grogu', 'Character', 'Neutral'),
(97, 'Another Grogu', 'Character', NULL),
(98, 'Anakin Skywalker', 'Character', NULL);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `toy_lines`
--

DROP TABLE IF EXISTS `toy_lines`;
CREATE TABLE IF NOT EXISTS `toy_lines` (
  `id` int NOT NULL AUTO_INCREMENT,
  `universe_id` int DEFAULT NULL,
  `manufacturer_id` int DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `scale` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `era_start_year` int DEFAULT NULL,
  `show_on_dashboard` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `universe_id` (`universe_id`),
  KEY `manufacturer_id` (`manufacturer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `toy_lines`
--

INSERT INTO `toy_lines` (`id`, `universe_id`, `manufacturer_id`, `name`, `scale`, `era_start_year`, `show_on_dashboard`) VALUES
(1, 1, 1, 'Vintage Star Wars (The Original Series)', '3.75 inch', 1978, 1),
(2, 1, 2, 'Power of the Force 2', '3.75 inch', 1995, 0),
(3, 1, 2, 'The Vintage Collection (TVC)', '3.75 inch', 2010, 1),
(4, 1, 2, 'The Black Series', '6 inch', 2013, 1),
(5, 1, 2, 'Retro Collection', '3.75 inch', 2019, 1),
(6, 1, 5, 'S.H. Figuarts', '1:12', 2015, 1),
(7, 2, 2, 'A Real American Hero (ARAH)', '3.75 inch', 1982, 1),
(8, 2, 2, '25th Anniversary', '3.75 inch', 2007, 1),
(9, 2, 2, 'Classified Series', '6 inch', 2020, 1);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `universes`
--

DROP TABLE IF EXISTS `universes`;
CREATE TABLE IF NOT EXISTS `universes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int DEFAULT '99',
  `show_on_dashboard` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `name_2` (`name`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `universes`
--

INSERT INTO `universes` (`id`, `name`, `slug`, `sort_order`, `show_on_dashboard`) VALUES
(1, 'Star Wars', 'star-wars', 1, 1),
(2, 'G.I. Joe', 'gi-joe', 2, 0);

--
-- Begrænsninger for dumpede tabeller
--

--
-- Begrænsninger for tabel `entertainment_sources`
--
ALTER TABLE `entertainment_sources`
  ADD CONSTRAINT `fk_ent_source_universe` FOREIGN KEY (`universe_id`) REFERENCES `universes` (`id`) ON DELETE CASCADE;

--
-- Begrænsninger for tabel `import_items`
--
ALTER TABLE `import_items`
  ADD CONSTRAINT `fk_import_item_source` FOREIGN KEY (`source_id`) REFERENCES `import_sources` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_import_item_toy` FOREIGN KEY (`master_toy_id`) REFERENCES `master_toys` (`id`) ON DELETE CASCADE;

--
-- Begrænsninger for tabel `import_logs`
--
ALTER TABLE `import_logs`
  ADD CONSTRAINT `fk_import_log_item` FOREIGN KEY (`import_item_id`) REFERENCES `import_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_import_log_source` FOREIGN KEY (`source_id`) REFERENCES `import_sources` (`id`) ON DELETE CASCADE;

--
-- Begrænsninger for tabel `master_toys`
--
ALTER TABLE `master_toys`
  ADD CONSTRAINT `fk_toy_ent_source` FOREIGN KEY (`entertainment_source_id`) REFERENCES `entertainment_sources` (`id`) ON DELETE SET NULL;

--
-- Begrænsninger for tabel `master_toy_item_media_map`
--
ALTER TABLE `master_toy_item_media_map`
  ADD CONSTRAINT `fk_mti_item` FOREIGN KEY (`master_toy_item_id`) REFERENCES `master_toy_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mti_media` FOREIGN KEY (`media_file_id`) REFERENCES `media_files` (`id`) ON DELETE CASCADE;

--
-- Begrænsninger for tabel `master_toy_media_map`
--
ALTER TABLE `master_toy_media_map`
  ADD CONSTRAINT `fk_mt_media` FOREIGN KEY (`media_file_id`) REFERENCES `media_files` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mt_toy` FOREIGN KEY (`master_toy_id`) REFERENCES `master_toys` (`id`) ON DELETE CASCADE;
COMMIT;


*/