<?php

/*

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Vært: 127.0.0.1:3306
-- Genereringstid: 03. 02 2026 kl. 19:47:37
-- Serverversion: 9.1.0
-- PHP-version: 8.3.14

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
  `acquisition_status` enum('In Hand','Pre-order','Shipped','Paid','Backordered','Customs') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'In Hand',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `acquisition_status` enum('In Hand','Pre-order','Shipped','Paid','Backordered','Customs') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `entertainment_sources`
--

DROP TABLE IF EXISTS `entertainment_sources`;
CREATE TABLE IF NOT EXISTS `entertainment_sources` (
  `id` int NOT NULL AUTO_INCREMENT,
  `universe_id` int NOT NULL COMMENT 'Fx Star Wars universe ID',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Fx The Empire Strikes Back',
  `type` enum('Movie','Series','Game','Book','Comic','Other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Other',
  `release_year` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `universe_id` (`universe_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Begrænsninger for dumpede tabeller
--

--
-- Begrænsninger for tabel `entertainment_sources`
--
ALTER TABLE `entertainment_sources`
  ADD CONSTRAINT `fk_ent_source_universe` FOREIGN KEY (`universe_id`) REFERENCES `universes` (`id`) ON DELETE CASCADE;

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