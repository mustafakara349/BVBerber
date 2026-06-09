-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: localhost
-- Üretim Zamanı: 08 Haz 2026, 17:39:31
-- Sunucu sürümü: 10.4.28-MariaDB
-- PHP Sürümü: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `bvapp_db`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `appointments`
--

CREATE TABLE `appointments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `appointment_code` varchar(30) NOT NULL,
  `start_at` datetime NOT NULL,
  `end_at` datetime NOT NULL,
  `total_duration` int(11) NOT NULL,
  `subtotal` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `total_price` decimal(10,2) DEFAULT 0.00,
  `status` enum('pending','confirmed','completed','cancelled','rejected','no_show','in_progress') DEFAULT 'pending',
  `payment_status` enum('unpaid','paid','partial','refunded') DEFAULT 'unpaid',
  `payment_method` enum('cash','credit_card','bank_transfer','online') DEFAULT NULL,
  `source` enum('mobile_app','walk_in','admin_panel','phone','instagram','website') DEFAULT 'mobile_app',
  `customer_note` text DEFAULT NULL,
  `internal_note` text DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `cancelled_by` bigint(20) UNSIGNED DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `no_show` tinyint(1) DEFAULT 0,
  `reminder_sent` tinyint(1) DEFAULT 0,
  `checked_in_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `appointments`
--

INSERT INTO `appointments` (`id`, `uuid`, `branch_id`, `customer_id`, `employee_id`, `appointment_code`, `start_at`, `end_at`, `total_duration`, `subtotal`, `discount_amount`, `tax_amount`, `total_price`, `status`, `payment_status`, `payment_method`, `source`, `customer_note`, `internal_note`, `cancellation_reason`, `cancelled_by`, `cancelled_at`, `completed_at`, `no_show`, `reminder_sent`, `checked_in_at`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(7, '35a1b91a-4fdb-11f1-98d5-2e5e95d91c8b', 1, 6, 2, 'APT-2004', '2026-05-15 10:30:00', '2026-05-15 11:15:00', 45, 500.00, 50.00, 0.00, 450.00, 'cancelled', 'paid', 'credit_card', 'mobile_app', NULL, NULL, NULL, 1, '2026-05-14 22:01:10', NULL, 0, 1, NULL, 5, '2026-05-14 21:23:54', '2026-05-14 22:01:10', NULL),
(15, '35a1ca86-4fdb-11f1-98d5-2e5e95d91c8b', 1, 8, 3, 'APT-2012', '2026-05-15 15:00:00', '2026-05-15 15:45:00', 45, 600.00, 50.00, 0.00, 550.00, 'cancelled', 'paid', 'cash', 'mobile_app', NULL, NULL, NULL, 1, '2026-05-14 22:01:04', NULL, 0, 1, NULL, 5, '2026-05-14 21:23:54', '2026-05-14 22:01:04', NULL),
(19, '35a1cc52-4fdb-11f1-98d5-2e5e95d91c8b', 1, 6, 2, 'APT-2016', '2026-05-15 17:00:00', '2026-05-15 17:45:00', 45, 500.00, 50.00, 0.00, 450.00, 'cancelled', 'paid', 'credit_card', 'website', NULL, NULL, NULL, 1, '2026-05-14 22:01:02', NULL, 0, 1, NULL, 5, '2026-05-14 21:23:54', '2026-05-14 22:01:02', NULL),
(23, '35a1ce28-4fdb-11f1-98d5-2e5e95d91c8b', 1, 7, 2, 'APT-2020', '2026-05-15 19:00:00', '2026-05-15 20:30:00', 90, 1200.00, 100.00, 0.00, 1100.00, 'cancelled', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, 1, '2026-05-14 22:01:00', NULL, 0, 1, NULL, 4, '2026-05-14 21:23:54', '2026-05-14 22:01:00', NULL),
(24, '35a8df4c-4fdb-11f1-98d5-2e5e95d91c8b', 1, 6, 1, 'APT-3001', '2026-05-16 09:00:00', '2026-05-16 09:45:00', 45, 500.00, 0.00, 0.00, 500.00, 'cancelled', 'paid', 'cash', 'mobile_app', NULL, NULL, NULL, 1, '2026-05-14 22:01:00', NULL, 0, 1, NULL, 5, '2026-05-14 21:23:54', '2026-05-14 22:01:00', NULL),
(25, '35a8e1cc-4fdb-11f1-98d5-2e5e95d91c8b', 1, 7, 2, 'APT-3002', '2026-05-16 09:30:00', '2026-05-16 10:15:00', 45, 500.00, 50.00, 0.00, 450.00, 'cancelled', 'paid', 'credit_card', 'website', NULL, NULL, NULL, 1, '2026-05-14 22:00:59', NULL, 0, 1, NULL, 5, '2026-05-14 21:23:54', '2026-05-14 22:00:59', NULL),
(26, '35a8e26c-4fdb-11f1-98d5-2e5e95d91c8b', 1, 8, 3, 'APT-3003', '2026-05-16 10:00:00', '2026-05-16 11:30:00', 90, 1200.00, 0.00, 0.00, 1200.00, 'cancelled', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, 1, '2026-05-14 22:00:58', NULL, 0, 1, NULL, 4, '2026-05-14 21:23:54', '2026-05-14 22:00:58', NULL),
(27, '35a8e2ee-4fdb-11f1-98d5-2e5e95d91c8b', 1, 6, 2, 'APT-3004', '2026-05-16 11:00:00', '2026-05-16 11:30:00', 30, 300.00, 0.00, 0.00, 300.00, 'cancelled', 'unpaid', NULL, 'website', NULL, NULL, NULL, 1, '2026-05-14 22:00:57', NULL, 0, 0, NULL, 5, '2026-05-14 21:23:54', '2026-05-14 22:00:57', NULL),
(28, '35a8e370-4fdb-11f1-98d5-2e5e95d91c8b', 1, 7, 1, 'APT-3005', '2026-05-16 11:30:00', '2026-05-16 12:15:00', 45, 600.00, 0.00, 0.00, 600.00, 'cancelled', 'paid', 'credit_card', 'mobile_app', NULL, NULL, NULL, 1, '2026-05-14 22:00:42', NULL, 0, 1, NULL, 5, '2026-05-14 21:23:54', '2026-05-14 22:00:42', NULL),
(29, '35a8e3fc-4fdb-11f1-98d5-2e5e95d91c8b', 1, 8, 3, 'APT-3006', '2026-05-16 12:00:00', '2026-05-16 13:30:00', 90, 1200.00, 200.00, 0.00, 1000.00, 'cancelled', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, 1, '2026-05-14 22:00:52', NULL, 0, 1, NULL, 4, '2026-05-14 21:23:54', '2026-05-14 22:00:52', NULL),
(30, '35a8e474-4fdb-11f1-98d5-2e5e95d91c8b', 1, 6, 1, 'APT-3007', '2026-05-16 13:00:00', '2026-05-16 13:45:00', 45, 500.00, 0.00, 0.00, 500.00, 'cancelled', 'paid', 'cash', 'website', NULL, NULL, NULL, 1, '2026-05-14 22:00:50', NULL, 0, 1, NULL, 5, '2026-05-14 21:23:54', '2026-05-14 22:00:50', NULL),
(31, '35a8e4ec-4fdb-11f1-98d5-2e5e95d91c8b', 1, 7, 2, 'APT-3008', '2026-05-16 13:30:00', '2026-05-16 14:15:00', 45, 500.00, 0.00, 0.00, 500.00, 'completed', 'paid', 'credit_card', 'mobile_app', NULL, NULL, NULL, 1, '2026-05-14 22:00:48', '2026-05-18 15:14:24', 0, 1, NULL, 5, '2026-05-14 21:23:54', '2026-05-18 15:14:24', NULL),
(32, '35a8e564-4fdb-11f1-98d5-2e5e95d91c8b', 1, 8, 3, 'APT-3009', '2026-05-16 14:00:00', '2026-05-16 15:30:00', 90, 1200.00, 100.00, 0.00, 1100.00, 'cancelled', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, 1, '2026-05-14 22:00:46', NULL, 0, 1, NULL, 4, '2026-05-14 21:23:54', '2026-05-14 22:00:46', NULL),
(33, '35a8e5d2-4fdb-11f1-98d5-2e5e95d91c8b', 1, 6, 2, 'APT-3010', '2026-05-16 15:00:00', '2026-05-16 15:30:00', 30, 300.00, 0.00, 0.00, 300.00, 'completed', 'paid', 'cash', 'website', NULL, NULL, NULL, 1, '2026-05-14 22:00:44', '2026-05-18 15:14:16', 0, 0, NULL, 5, '2026-05-14 21:23:54', '2026-05-30 15:14:15', NULL),
(34, '35a8e640-4fdb-11f1-98d5-2e5e95d91c8b', 1, 7, 1, 'APT-3011', '2026-05-16 15:30:00', '2026-05-16 16:15:00', 45, 500.00, 0.00, 0.00, 500.00, 'cancelled', 'paid', 'cash', 'mobile_app', NULL, NULL, NULL, 1, '2026-05-14 22:00:39', NULL, 0, 1, NULL, 5, '2026-05-14 21:23:54', '2026-05-14 22:00:39', NULL),
(35, '35a8e6ae-4fdb-11f1-98d5-2e5e95d91c8b', 1, 8, 3, 'APT-3012', '2026-05-16 16:00:00', '2026-05-16 17:30:00', 90, 1200.00, 0.00, 0.00, 1200.00, 'cancelled', 'paid', 'credit_card', 'admin_panel', NULL, NULL, NULL, 1, '2026-05-14 22:00:37', NULL, 0, 1, NULL, 4, '2026-05-14 21:23:54', '2026-05-14 22:00:37', NULL),
(36, '35a8e726-4fdb-11f1-98d5-2e5e95d91c8b', 1, 6, 1, 'APT-3013', '2026-05-16 17:00:00', '2026-05-16 17:45:00', 45, 600.00, 50.00, 0.00, 550.00, 'cancelled', 'paid', 'cash', 'website', NULL, NULL, NULL, 1, '2026-05-14 22:00:35', NULL, 0, 1, NULL, 5, '2026-05-14 21:23:54', '2026-05-14 22:00:35', NULL),
(37, '35a8e7bc-4fdb-11f1-98d5-2e5e95d91c8b', 1, 7, 2, 'APT-3014', '2026-05-16 17:30:00', '2026-05-16 18:00:00', 30, 300.00, 0.00, 0.00, 300.00, 'cancelled', 'unpaid', NULL, 'mobile_app', NULL, NULL, NULL, 1, '2026-05-14 22:00:34', NULL, 0, 0, NULL, 5, '2026-05-14 21:23:54', '2026-05-14 22:00:34', NULL),
(38, '35a8e83e-4fdb-11f1-98d5-2e5e95d91c8b', 1, 8, 3, 'APT-3015', '2026-05-16 18:00:00', '2026-05-16 19:30:00', 90, 1200.00, 150.00, 0.00, 1050.00, 'cancelled', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, 1, '2026-05-14 22:00:33', NULL, 0, 1, NULL, 4, '2026-05-14 21:23:54', '2026-05-14 22:00:33', NULL),
(39, '35a8e8b6-4fdb-11f1-98d5-2e5e95d91c8b', 1, 6, 2, 'APT-3016', '2026-05-16 19:00:00', '2026-05-16 19:45:00', 45, 500.00, 0.00, 0.00, 500.00, 'cancelled', 'paid', 'credit_card', 'website', NULL, NULL, NULL, 1, '2026-05-14 22:00:31', NULL, 0, 1, NULL, 5, '2026-05-14 21:23:54', '2026-05-14 22:00:31', NULL),
(40, '35a8e924-4fdb-11f1-98d5-2e5e95d91c8b', 1, 7, 1, 'APT-3017', '2026-05-16 19:30:00', '2026-05-16 20:15:00', 45, 500.00, 0.00, 0.00, 500.00, 'cancelled', 'paid', 'cash', 'mobile_app', NULL, NULL, NULL, 1, '2026-05-14 22:00:30', NULL, 0, 1, NULL, 5, '2026-05-14 21:23:54', '2026-05-14 22:00:30', NULL),
(41, '35a8e992-4fdb-11f1-98d5-2e5e95d91c8b', 1, 8, 3, 'APT-3018', '2026-05-16 20:00:00', '2026-05-16 20:30:00', 30, 300.00, 0.00, 0.00, 300.00, 'cancelled', 'unpaid', NULL, 'website', NULL, NULL, NULL, 1, '2026-05-14 22:00:19', NULL, 0, 0, NULL, 5, '2026-05-14 21:23:54', '2026-05-14 22:00:19', NULL),
(42, '090073dc-d3db-4036-a813-a800f70a9e5b', 1, 9, 2, 'BV-GI0PG8QP', '2026-05-15 14:00:00', '2026-05-15 14:45:00', 45, 450.00, 0.00, 0.00, 450.00, 'cancelled', 'unpaid', NULL, 'admin_panel', 'afdmad', 'adkfna', NULL, 1, '2026-05-14 22:01:08', NULL, 0, 0, NULL, 1, '2026-05-14 18:32:28', '2026-05-14 22:01:08', NULL),
(43, '9391d3aa-9d9f-4871-8fdc-5e24a29cd1e5', 1, 6, 1, 'BV-FOYILC9Y', '2026-05-15 08:00:00', '2026-05-15 08:45:00', 45, 450.00, 0.00, 0.00, 450.00, 'cancelled', 'unpaid', NULL, 'admin_panel', NULL, NULL, NULL, 1, '2026-05-18 15:15:14', NULL, 0, 0, NULL, 1, '2026-05-14 22:20:10', '2026-05-18 15:15:14', NULL),
(44, '5143d19e-86af-4dba-9dda-f21e773b2f4f', 1, 7, 2, 'BV-MHOILQHZ', '2026-05-15 08:00:00', '2026-05-15 08:45:00', 45, 450.00, 0.00, 0.00, 450.00, 'cancelled', 'unpaid', NULL, 'admin_panel', NULL, NULL, NULL, 1, '2026-05-18 15:15:15', NULL, 0, 0, NULL, 1, '2026-05-14 22:21:10', '2026-05-18 15:15:15', NULL),
(45, '7f23292f-30bb-4307-bc05-6b70b844704d', 1, 8, 3, 'BV-KK0EAV3E', '2026-05-15 08:30:00', '2026-05-15 09:15:00', 45, 450.00, 0.00, 0.00, 450.00, 'cancelled', 'unpaid', NULL, 'admin_panel', NULL, NULL, NULL, 1, '2026-05-18 15:15:11', NULL, 0, 0, NULL, 1, '2026-05-14 22:22:30', '2026-05-18 15:15:11', NULL),
(46, 'd9b07451-df00-4742-a217-f9af1f4ded78', 1, 9, 3, 'BV-V43BVGP4', '2026-05-15 09:30:00', '2026-05-15 11:00:00', 90, 999.00, 0.00, 0.00, 999.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-18 15:14:55', 0, 0, NULL, 1, '2026-05-14 22:29:14', '2026-06-08 14:51:24', NULL),
(47, 'ec6088c4-ffdc-4dc5-917e-bcdf6a846511', 1, 9, 1, 'BV-VJ8PDIAV', '2026-05-15 10:00:00', '2026-05-15 10:30:00', 30, 300.00, 0.00, 0.00, 300.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-18 15:14:48', 0, 0, NULL, 1, '2026-05-14 22:31:57', '2026-05-30 15:00:27', NULL),
(48, 'a8dd8a9e-78d6-4daf-a5e4-5c3856e6e042', 1, 7, 1, 'BV-B9BAEMHU', '2026-05-16 19:30:00', '2026-05-16 20:00:00', 30, 350.00, 0.00, 0.00, 350.00, 'completed', 'paid', 'credit_card', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-18 20:23:26', 0, 0, NULL, 1, '2026-05-14 22:35:31', '2026-05-18 20:23:26', NULL),
(49, '27482e72-de4a-4c76-a749-853660aea318', 1, 6, 2, 'BV-9WOEHNP7', '2026-05-15 09:00:00', '2026-05-15 09:30:00', 30, 450.00, 0.00, 0.00, 450.00, 'cancelled', 'unpaid', NULL, 'admin_panel', NULL, NULL, NULL, 1, '2026-05-18 15:15:09', NULL, 0, 0, NULL, 1, '2026-05-14 23:01:22', '2026-05-18 15:15:09', NULL),
(50, 'e57a594e-546d-4ff9-88c8-0b2ac09f8cc8', 1, 6, 2, 'BV-OGCZIFWS', '2026-05-15 09:00:00', '2026-05-15 09:30:00', 30, 450.00, 0.00, 0.00, 450.00, 'cancelled', 'unpaid', NULL, 'admin_panel', NULL, NULL, NULL, 1, '2026-05-14 23:03:19', NULL, 0, 0, NULL, 1, '2026-05-14 23:02:06', '2026-05-14 23:03:19', NULL),
(51, '89624b4c-bad2-43f6-8000-884f20b18793', 1, 7, 3, 'BV-SABYUQMO', '2026-05-15 08:00:00', '2026-05-15 09:30:00', 90, 999.00, 0.00, 0.00, 999.00, 'cancelled', 'unpaid', NULL, 'admin_panel', NULL, NULL, NULL, 1, '2026-05-14 23:03:49', NULL, 0, 0, NULL, 1, '2026-05-14 23:03:44', '2026-05-14 23:03:49', NULL),
(52, 'c1713386-c6d4-4fb4-8596-2c46ee0ce92e', 1, 7, 3, 'BV-OHOIQNBJ', '2026-05-15 11:30:00', '2026-05-15 12:00:00', 30, 450.00, 0.00, 0.00, 450.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-18 15:14:39', 0, 0, NULL, 1, '2026-05-14 23:04:05', '2026-05-30 15:00:14', NULL),
(53, '10cb9bbe-21e6-4666-9761-c818f394cc0a', 1, 10, 1, 'BV-W1M8VTSA', '2026-05-18 17:30:00', '2026-05-18 18:00:00', 30, 450.00, 0.00, 0.00, 450.00, 'completed', 'paid', 'credit_card', 'admin_panel', 'yok.', 'yok.', NULL, NULL, NULL, '2026-05-18 15:02:21', 0, 0, NULL, 1, '2026-05-18 13:47:02', '2026-05-18 20:03:49', NULL),
(54, '1a85a380-f552-4697-9e74-4f22a15b9854', 1, 6, 1, 'BV-Q5RCIUMI', '2026-05-18 18:00:00', '2026-05-18 18:30:00', 30, 300.00, 0.00, 0.00, 300.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-18 15:02:44', 0, 0, NULL, 1, '2026-05-18 13:48:23', '2026-05-18 20:10:37', NULL),
(55, '95223dfe-7ecb-4570-a026-1912a23997b5', 1, 8, 3, 'BV-EQZ9ZNYW', '2026-05-18 19:30:00', '2026-05-18 20:00:00', 30, 300.00, 0.00, 0.00, 300.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-18 20:11:09', 0, 0, NULL, 1, '2026-05-18 13:50:33', '2026-05-18 20:11:25', NULL),
(56, '2b39be30-cc57-4369-adc9-8a0af2914acf', 1, 6, 1, 'BV-MWPXXPXW', '2026-05-19 10:00:00', '2026-05-19 10:30:00', 30, 450.00, 0.00, 0.00, 450.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-19 12:21:17', 0, 0, NULL, 1, '2026-05-18 20:25:36', '2026-05-19 12:21:17', NULL),
(57, 'd50a05a7-1d21-4fe3-ae4b-bd7baa691d21', 1, 7, 2, 'BV-8F4MO8ST', '2026-05-19 10:00:00', '2026-05-19 10:30:00', 30, 450.00, 0.00, 0.00, 450.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-19 12:21:21', 0, 0, NULL, 1, '2026-05-18 20:25:53', '2026-05-19 12:21:21', NULL),
(58, 'acfd5892-007c-4472-9cc3-5a96aac77eb4', 1, 8, 3, 'BV-0ZSZAVWO', '2026-05-19 10:30:00', '2026-05-19 11:30:00', 60, 750.00, 0.00, 0.00, 750.00, 'no_show', 'unpaid', NULL, 'admin_panel', NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 1, '2026-05-18 20:26:17', '2026-05-19 12:21:08', NULL),
(59, 'dc27c6eb-2566-4bcd-b964-bf96163193f8', 1, 9, 5, 'BV-SUFTOOWO', '2026-05-19 12:00:00', '2026-05-19 12:30:00', 30, 450.00, 0.00, 0.00, 450.00, 'no_show', 'unpaid', NULL, 'admin_panel', NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 1, '2026-05-18 20:26:35', '2026-05-19 12:20:57', NULL),
(60, 'af4f7f75-d880-4a64-b983-a6dff191d8cc', 1, 10, 1, 'BV-4VAF0O2R', '2026-05-19 10:30:00', '2026-05-19 12:00:00', 90, 1200.00, 0.00, 0.00, 1200.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-19 12:21:14', 0, 0, NULL, 1, '2026-05-18 20:26:54', '2026-05-19 12:21:14', NULL),
(61, 'b37c6da3-add3-437b-b469-da55067e983c', 1, 12, 5, 'BV-XCT3C2DR', '2026-05-19 12:30:00', '2026-05-19 13:00:00', 30, 250.00, 0.00, 0.00, 250.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-19 12:20:41', 0, 0, NULL, 1, '2026-05-18 20:27:13', '2026-05-19 12:20:41', NULL),
(62, '970eb490-2680-4de4-b0ce-52e2a4bef2ef', 1, 6, 5, 'BV-MGISHUK0', '2026-05-19 08:00:00', '2026-05-19 08:30:00', 30, 350.00, 0.00, 0.00, 350.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, 1, '2026-05-18 22:23:28', '2026-05-19 12:21:40', 0, 0, NULL, 1, '2026-05-18 20:27:35', '2026-05-19 12:21:40', NULL),
(63, 'fe905b73-4454-44f9-9d2f-2395cf2255b9', 1, 7, 5, 'BV-YTLF8FBY', '2026-05-19 08:30:00', '2026-05-19 09:00:00', 30, 300.00, 0.00, 0.00, 300.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-19 12:21:30', 0, 0, NULL, 1, '2026-05-18 20:27:46', '2026-05-19 12:21:30', NULL),
(64, 'b571da17-c9ad-4091-9b09-0ed70022cbc5', 1, 8, 5, 'BV-ZYVOVHSW', '2026-05-19 09:00:00', '2026-05-19 09:40:00', 40, 600.00, 0.00, 0.00, 600.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-19 12:21:26', 0, 0, NULL, 1, '2026-05-18 20:28:02', '2026-05-19 12:21:26', NULL),
(65, '82aecb0a-36c2-46c2-bfca-c44755675b3d', 1, 9, 5, 'BV-S8EXQGU0', '2026-05-19 10:00:00', '2026-05-19 11:30:00', 90, 1200.00, 0.00, 0.00, 1200.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-19 12:21:23', 0, 0, NULL, 1, '2026-05-18 20:28:17', '2026-05-19 12:21:23', NULL),
(66, 'c64c2bd5-2759-45f9-898a-95b643ad2e4d', 1, 10, 5, 'BV-VQFW3IZ3', '2026-05-19 11:30:00', '2026-05-19 13:00:00', 90, 1200.00, 0.00, 0.00, 1200.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-19 12:21:04', 0, 0, NULL, 1, '2026-05-18 20:28:34', '2026-05-19 12:21:04', NULL),
(67, '54d212b7-7ab7-40de-b7c4-af9f852acb31', 1, 12, 5, 'BV-LXOLM9SV', '2026-05-19 13:00:00', '2026-05-19 13:30:00', 30, 250.00, 0.00, 0.00, 250.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-19 12:20:36', 0, 0, NULL, 1, '2026-05-18 20:29:07', '2026-05-19 12:20:36', NULL),
(68, 'c7d551fb-25f2-47fa-96c9-a837246e901a', 1, 6, 1, 'BV-6EJB0ONG', '2026-05-19 08:00:00', '2026-05-19 08:30:00', 30, 300.00, 0.00, 0.00, 300.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-19 12:21:43', 0, 0, NULL, 1, '2026-05-18 20:29:21', '2026-05-19 12:21:43', NULL),
(69, '83b87ab5-adc2-4482-96f6-42f9aa6a3abc', 1, 7, 1, 'BV-LPUCFVYV', '2026-05-19 08:30:00', '2026-05-19 10:00:00', 90, 1200.00, 0.00, 0.00, 1200.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-19 12:21:36', 0, 0, NULL, 1, '2026-05-18 20:29:35', '2026-05-19 12:21:36', NULL),
(70, '1e7d49a3-905d-41d8-be41-76ce6ea934e4', 1, 8, 1, 'BV-2NC42NC1', '2026-05-19 12:00:00', '2026-05-19 12:30:00', 30, 250.00, 0.00, 0.00, 250.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-19 12:21:01', 0, 0, NULL, 1, '2026-05-18 20:30:15', '2026-05-19 12:21:01', NULL),
(71, 'e2968513-763f-4266-997e-a7078e405a26', 1, 10, 1, 'BV-EJ7WTTMI', '2026-05-19 12:30:00', '2026-05-19 13:00:00', 30, 450.00, 0.00, 0.00, 450.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-19 12:20:45', 0, 0, NULL, 1, '2026-05-18 20:30:27', '2026-05-19 12:20:45', NULL),
(72, '321a030e-a782-4098-817b-eea86fcaaee9', 1, 10, 3, 'BV-MFVEGME9', '2026-05-19 14:30:00', '2026-05-19 15:10:00', 40, 600.00, 0.00, 0.00, 600.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-19 12:20:33', 0, 0, NULL, 1, '2026-05-18 20:38:38', '2026-05-19 12:20:33', NULL),
(73, 'f9e7ab77-0202-4aca-bffa-51efbf012928', 1, 10, 3, 'BV-ICYB5NSS', '2026-05-20 21:00:00', '2026-05-20 21:30:00', 30, 450.00, 0.00, 0.00, 450.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-23 09:51:43', 0, 0, NULL, 1, '2026-05-18 22:27:43', '2026-05-23 09:51:43', NULL),
(74, 'ee90d462-50d3-4987-af6e-a794f930ec65', 1, 12, 5, 'BV-U8WZUDHA', '2026-05-19 15:30:00', '2026-05-19 16:00:00', 30, 250.00, 0.00, 0.00, 250.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-19 13:05:14', 0, 0, NULL, 1, '2026-05-19 12:19:58', '2026-05-19 13:05:14', NULL),
(76, '0dec307a-e2b6-4b90-926d-9029f7ca3450', 1, 6, 3, 'BV-PWZ2PLCG', '2026-05-19 16:30:00', '2026-05-19 17:00:00', 30, 450.00, 0.00, 0.00, 450.00, 'rejected', 'unpaid', NULL, 'admin_panel', NULL, NULL, 'Müşteri talebi üzerine randevu reddedildi.', 1, '2026-05-19 13:20:44', NULL, 0, 0, NULL, 1, '2026-05-19 13:06:28', '2026-05-19 13:20:44', NULL),
(77, '6859d022-574d-4a77-9c2c-2d9e79d1575f', 1, 8, 1, 'BV-IOPBFN6K', '2026-05-19 21:30:00', '2026-05-19 22:00:00', 30, 450.00, 0.00, 0.00, 450.00, 'rejected', 'unpaid', NULL, 'admin_panel', NULL, NULL, 'ahmet bey müsait değil.', 1, '2026-05-19 13:07:45', NULL, 0, 0, NULL, 1, '2026-05-19 13:07:19', '2026-05-19 13:07:45', NULL),
(78, '0cc2ffbb-c4ec-45ab-b184-e0a7309cf182', 1, 6, 1, 'BV-JOTZC65O', '2026-05-19 17:30:00', '2026-05-19 18:00:00', 30, 450.00, 0.00, 0.00, 450.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-19 14:36:56', 0, 0, NULL, 1, '2026-05-19 14:01:29', '2026-05-19 14:36:56', NULL),
(79, '82f5a709-fa21-43a5-9873-d16cc69f668f', 1, 7, 2, 'BV-K6VIKAAY', '2026-05-19 17:30:00', '2026-05-19 18:00:00', 30, 450.00, 0.00, 0.00, 450.00, 'rejected', 'unpaid', NULL, 'admin_panel', NULL, NULL, 'dükkan denetimi olacak', 1, '2026-05-19 14:25:32', NULL, 0, 0, NULL, 1, '2026-05-19 14:01:41', '2026-05-19 14:25:32', NULL),
(80, '4b1ac0d7-c33c-48b0-b034-02c81f22044a', 1, 9, 3, 'BV-TT3BJVTZ', '2026-05-19 17:30:00', '2026-05-19 18:00:00', 30, 250.00, 0.00, 0.00, 250.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-23 11:54:05', 0, 0, NULL, 1, '2026-05-19 14:01:54', '2026-05-23 11:54:19', NULL),
(81, '28b95e31-074d-4d53-87b7-e6fbb34a5347', 1, 10, 5, 'BV-MRKPXT3V', '2026-05-19 18:00:00', '2026-05-19 18:30:00', 30, 450.00, 0.00, 0.00, 450.00, 'rejected', 'unpaid', NULL, 'admin_panel', NULL, NULL, 'dükkan denetimi olacak', 1, '2026-05-19 14:35:58', NULL, 0, 0, NULL, 1, '2026-05-19 14:02:07', '2026-05-19 14:35:58', NULL),
(82, 'ee70cb1e-02c8-4213-a982-730b0b199af1', 1, 12, 5, 'BV-P63QWF3C', '2026-05-19 18:30:00', '2026-05-19 19:00:00', 30, 350.00, 0.00, 0.00, 350.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-23 09:51:47', 0, 0, NULL, 1, '2026-05-19 14:02:20', '2026-05-23 09:51:47', NULL),
(83, '2036e9de-9021-420d-8fa9-b419f2ec2849', 1, 8, 1, 'BV-WCVQVBGY', '2026-05-19 18:00:00', '2026-05-19 18:40:00', 40, 600.00, 0.00, 0.00, 600.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-23 09:51:49', 0, 0, NULL, 1, '2026-05-19 14:02:34', '2026-05-23 09:51:49', NULL),
(84, 'a80128fe-2d4d-4a05-94e6-4eb8b36732a4', 1, 6, 1, 'BV-XP9KSSWN', '2026-05-23 13:00:00', '2026-05-23 13:30:00', 30, 450.00, 0.00, 0.00, 450.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-23 11:55:34', 0, 0, NULL, 1, '2026-05-23 09:52:21', '2026-05-23 11:55:34', NULL),
(85, '90e176f7-f4fc-4cbf-99b0-93f9d6df37e2', 1, 7, 2, 'BV-P7WSF4YZ', '2026-05-23 13:30:00', '2026-05-23 14:00:00', 30, 450.00, 0.00, 0.00, 450.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-23 11:54:50', 0, 0, NULL, 1, '2026-05-23 09:52:36', '2026-05-23 11:54:50', NULL),
(86, '7e2fca39-1e5f-406d-b6e6-cab049650cd9', 1, 8, 3, 'BV-W4HNBJVV', '2026-05-23 14:00:00', '2026-05-23 14:30:00', 30, 450.00, 0.00, 0.00, 450.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-23 11:54:31', 0, 0, NULL, 1, '2026-05-23 09:53:00', '2026-05-23 11:54:31', NULL),
(87, 'fbcdb659-9b50-44c3-a1f4-d17666c5c5d7', 1, 8, 5, 'BV-Y8YDFRI0', '2026-05-23 20:00:00', '2026-05-23 20:30:00', 30, 350.00, 0.00, 0.00, 350.00, 'completed', 'paid', 'credit_card', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-23 17:09:57', 0, 0, NULL, 1, '2026-05-23 09:53:52', '2026-05-23 17:09:57', NULL),
(88, 'ccccd805-8e33-4e5b-9eee-a52bbe2b954c', 1, 9, 2, 'BV-8SB9FNX8', '2026-05-23 20:30:00', '2026-05-23 21:00:00', 30, 450.00, 0.00, 0.00, 450.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-25 10:56:44', 0, 0, NULL, 1, '2026-05-23 09:54:04', '2026-05-25 10:56:44', NULL),
(89, '70424024-f658-4eb3-bf2b-a82d9dca30e7', 1, 7, 2, 'BV-VZJS4VEW', '2026-05-23 21:00:00', '2026-05-23 21:30:00', 30, 450.00, 0.00, 0.00, 450.00, 'rejected', 'unpaid', NULL, 'admin_panel', NULL, NULL, 'Canım istemedi.', 1, '2026-05-23 09:54:57', NULL, 0, 0, NULL, 1, '2026-05-23 09:54:18', '2026-05-23 09:54:57', NULL),
(90, 'dc4273dd-b144-4801-b393-e57a0a095b21', 1, 10, 3, 'BV-JBY1DENA', '2026-05-23 18:30:00', '2026-05-23 19:00:00', 30, 450.00, 0.00, 0.00, 450.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-23 17:10:55', 0, 0, NULL, 1, '2026-05-23 11:52:22', '2026-05-23 17:10:55', NULL),
(91, '0239abf6-565f-46bd-b669-9b0c4d3382bb', 1, 7, 2, 'BV-L8GWWXPH', '2026-05-23 19:30:00', '2026-05-23 20:00:00', 30, 450.00, 0.00, 0.00, 450.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-23 17:10:08', 0, 0, NULL, 1, '2026-05-23 11:52:51', '2026-05-23 17:10:08', NULL),
(92, '64647bdb-bacb-4431-aece-c0c42686ec46', 1, 8, 2, 'BV-BZFILNCU', '2026-05-23 19:00:00', '2026-05-23 19:30:00', 30, 250.00, 0.00, 0.00, 250.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-23 17:10:50', 0, 0, NULL, 1, '2026-05-23 11:53:04', '2026-05-23 17:10:50', NULL),
(93, '781ae779-3854-4b70-b581-d80c315f0275', 1, 10, 5, 'BV-YFFI9NEF', '2026-05-23 18:30:00', '2026-05-23 19:00:00', 30, 450.00, 0.00, 0.00, 450.00, 'no_show', 'unpaid', NULL, 'admin_panel', NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 1, '2026-05-23 15:16:23', '2026-05-23 17:11:20', NULL),
(94, '983494de-889f-4a0d-aed8-cab8244b404c', 1, 7, 3, 'BV-RCQ2DFU2', '2026-05-23 21:30:00', '2026-05-23 22:00:00', 30, 300.00, 0.00, 0.00, 300.00, 'completed', 'paid', 'cash', 'admin_panel', NULL, NULL, NULL, NULL, NULL, '2026-05-25 10:56:41', 0, 0, NULL, 1, '2026-05-23 17:27:23', '2026-05-25 10:56:41', NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `appointment_services`
--

CREATE TABLE `appointment_services` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `appointment_id` bigint(20) UNSIGNED NOT NULL,
  `service_id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `duration_minutes` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `appointment_services`
--

INSERT INTO `appointment_services` (`id`, `appointment_id`, `service_id`, `employee_id`, `quantity`, `duration_minutes`, `unit_price`, `total_price`, `created_at`) VALUES
(4, 42, 1, 2, 1, 45, 450.00, 450.00, '2026-05-14 21:32:28'),
(5, 43, 1, 1, 1, 45, 450.00, 450.00, '2026-05-14 22:20:10'),
(6, 44, 1, 2, 1, 45, 450.00, 450.00, '2026-05-14 22:21:10'),
(7, 45, 1, 3, 1, 45, 450.00, 450.00, '2026-05-14 22:22:30'),
(8, 46, 3, 3, 1, 90, 999.00, 999.00, '2026-05-14 22:29:14'),
(9, 47, 2, 1, 1, 30, 300.00, 300.00, '2026-05-14 22:31:57'),
(10, 48, 5, 1, 1, 30, 350.00, 350.00, '2026-05-14 22:35:31'),
(11, 49, 1, 2, 1, 30, 450.00, 450.00, '2026-05-14 23:01:22'),
(12, 50, 1, 2, 1, 30, 450.00, 450.00, '2026-05-14 23:02:06'),
(13, 51, 3, 3, 1, 90, 999.00, 999.00, '2026-05-14 23:03:44'),
(14, 52, 1, 3, 1, 30, 450.00, 450.00, '2026-05-14 23:04:05'),
(15, 53, 1, 1, 1, 30, 450.00, 450.00, '2026-05-18 13:47:02'),
(16, 54, 2, 1, 1, 30, 300.00, 300.00, '2026-05-18 13:48:23'),
(17, 55, 2, 3, 1, 30, 300.00, 300.00, '2026-05-18 13:50:33'),
(18, 56, 1, 1, 1, 30, 450.00, 450.00, '2026-05-18 20:25:36'),
(19, 57, 1, 2, 1, 30, 450.00, 450.00, '2026-05-18 20:25:53'),
(20, 58, 1, 3, 1, 30, 450.00, 450.00, '2026-05-18 20:26:17'),
(21, 58, 2, 3, 1, 30, 300.00, 300.00, '2026-05-18 20:26:17'),
(22, 59, 1, 5, 1, 30, 450.00, 450.00, '2026-05-18 20:26:35'),
(23, 60, 3, 1, 1, 90, 1200.00, 1200.00, '2026-05-18 20:26:54'),
(24, 61, 6, 5, 1, 30, 250.00, 250.00, '2026-05-18 20:27:13'),
(25, 62, 5, 5, 1, 30, 350.00, 350.00, '2026-05-18 20:27:35'),
(26, 63, 2, 5, 1, 30, 300.00, 300.00, '2026-05-18 20:27:46'),
(27, 64, 4, 5, 1, 40, 600.00, 600.00, '2026-05-18 20:28:02'),
(28, 65, 3, 5, 1, 90, 1200.00, 1200.00, '2026-05-18 20:28:17'),
(29, 66, 3, 5, 1, 90, 1200.00, 1200.00, '2026-05-18 20:28:34'),
(30, 67, 6, 5, 1, 30, 250.00, 250.00, '2026-05-18 20:29:07'),
(31, 68, 2, 1, 1, 30, 300.00, 300.00, '2026-05-18 20:29:21'),
(32, 69, 3, 1, 1, 90, 1200.00, 1200.00, '2026-05-18 20:29:35'),
(33, 70, 6, 1, 1, 30, 250.00, 250.00, '2026-05-18 20:30:15'),
(34, 71, 1, 1, 1, 30, 450.00, 450.00, '2026-05-18 20:30:27'),
(35, 72, 4, 3, 1, 40, 600.00, 600.00, '2026-05-18 20:38:38'),
(36, 73, 1, 3, 1, 30, 450.00, 450.00, '2026-05-18 22:27:43'),
(37, 74, 6, 5, 1, 30, 250.00, 250.00, '2026-05-19 12:19:58'),
(39, 76, 1, 3, 1, 30, 450.00, 450.00, '2026-05-19 13:06:28'),
(40, 77, 1, 1, 1, 30, 450.00, 450.00, '2026-05-19 13:07:19'),
(41, 78, 1, 1, 1, 30, 450.00, 450.00, '2026-05-19 14:01:29'),
(42, 79, 1, 2, 1, 30, 450.00, 450.00, '2026-05-19 14:01:41'),
(43, 80, 6, 3, 1, 30, 250.00, 250.00, '2026-05-19 14:01:54'),
(44, 81, 1, 5, 1, 30, 450.00, 450.00, '2026-05-19 14:02:07'),
(45, 82, 5, 5, 1, 30, 350.00, 350.00, '2026-05-19 14:02:20'),
(46, 83, 4, 1, 1, 40, 600.00, 600.00, '2026-05-19 14:02:34'),
(47, 84, 1, 1, 1, 30, 450.00, 450.00, '2026-05-23 09:52:21'),
(48, 85, 1, 2, 1, 30, 450.00, 450.00, '2026-05-23 09:52:36'),
(49, 86, 1, 3, 1, 30, 450.00, 450.00, '2026-05-23 09:53:00'),
(50, 87, 5, 5, 1, 30, 350.00, 350.00, '2026-05-23 09:53:52'),
(51, 88, 1, 2, 1, 30, 450.00, 450.00, '2026-05-23 09:54:04'),
(52, 89, 1, 2, 1, 30, 450.00, 450.00, '2026-05-23 09:54:18'),
(53, 90, 1, 3, 1, 30, 450.00, 450.00, '2026-05-23 11:52:22'),
(54, 91, 1, 2, 1, 30, 450.00, 450.00, '2026-05-23 11:52:51'),
(55, 92, 6, 2, 1, 30, 250.00, 250.00, '2026-05-23 11:53:04'),
(56, 93, 1, 5, 1, 30, 450.00, 450.00, '2026-05-23 15:16:23'),
(57, 94, 2, 3, 1, 30, 300.00, 300.00, '2026-05-23 17:27:23');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `appointment_status_logs`
--

CREATE TABLE `appointment_status_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `appointment_id` bigint(20) UNSIGNED NOT NULL,
  `changed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `appointment_status_logs`
--

INSERT INTO `appointment_status_logs` (`id`, `appointment_id`, `changed_by`, `old_status`, `new_status`, `note`, `created_at`) VALUES
(6, 42, NULL, NULL, 'pending', NULL, '2026-05-14 21:32:28'),
(7, 41, 1, 'pending', 'cancelled', NULL, '2026-05-14 22:00:19'),
(8, 40, 1, 'confirmed', 'cancelled', NULL, '2026-05-14 22:00:30'),
(9, 39, 1, 'confirmed', 'cancelled', NULL, '2026-05-14 22:00:31'),
(10, 38, 1, 'confirmed', 'cancelled', NULL, '2026-05-14 22:00:33'),
(11, 37, 1, 'pending', 'cancelled', NULL, '2026-05-14 22:00:34'),
(12, 36, 1, 'confirmed', 'cancelled', NULL, '2026-05-14 22:00:35'),
(13, 35, 1, 'confirmed', 'cancelled', NULL, '2026-05-14 22:00:37'),
(14, 34, 1, 'confirmed', 'cancelled', NULL, '2026-05-14 22:00:39'),
(15, 28, 1, 'confirmed', 'cancelled', NULL, '2026-05-14 22:00:42'),
(16, 33, 1, 'pending', 'cancelled', NULL, '2026-05-14 22:00:44'),
(17, 32, 1, 'confirmed', 'cancelled', NULL, '2026-05-14 22:00:46'),
(18, 31, 1, 'confirmed', 'cancelled', NULL, '2026-05-14 22:00:48'),
(19, 30, 1, 'confirmed', 'cancelled', NULL, '2026-05-14 22:00:50'),
(20, 29, 1, 'confirmed', 'cancelled', NULL, '2026-05-14 22:00:52'),
(21, 27, 1, 'pending', 'cancelled', NULL, '2026-05-14 22:00:57'),
(22, 26, 1, 'confirmed', 'cancelled', NULL, '2026-05-14 22:00:58'),
(23, 25, 1, 'confirmed', 'cancelled', NULL, '2026-05-14 22:00:59'),
(24, 24, 1, 'confirmed', 'cancelled', NULL, '2026-05-14 22:01:00'),
(25, 23, 1, 'confirmed', 'cancelled', NULL, '2026-05-14 22:01:00'),
(26, 19, 1, 'confirmed', 'cancelled', NULL, '2026-05-14 22:01:02'),
(27, 15, 1, 'confirmed', 'cancelled', NULL, '2026-05-14 22:01:04'),
(28, 42, 1, 'pending', 'cancelled', NULL, '2026-05-14 22:01:08'),
(29, 7, 1, 'confirmed', 'cancelled', NULL, '2026-05-14 22:01:10'),
(30, 43, NULL, NULL, 'pending', NULL, '2026-05-14 22:20:10'),
(31, 44, NULL, NULL, 'pending', NULL, '2026-05-14 22:21:10'),
(32, 45, NULL, NULL, 'pending', NULL, '2026-05-14 22:22:30'),
(33, 43, 1, 'pending', 'confirmed', NULL, '2026-05-14 22:23:06'),
(34, 46, NULL, NULL, 'pending', NULL, '2026-05-14 22:29:14'),
(35, 47, NULL, NULL, 'pending', NULL, '2026-05-14 22:31:57'),
(36, 44, 1, 'pending', 'confirmed', NULL, '2026-05-14 22:34:45'),
(37, 45, 1, 'pending', 'confirmed', NULL, '2026-05-14 22:34:48'),
(38, 46, 1, 'pending', 'confirmed', NULL, '2026-05-14 22:34:50'),
(39, 47, 1, 'pending', 'confirmed', NULL, '2026-05-14 22:34:52'),
(40, 48, NULL, NULL, 'pending', NULL, '2026-05-14 22:35:31'),
(41, 48, 1, 'pending', 'confirmed', NULL, '2026-05-14 22:36:03'),
(42, 49, NULL, NULL, 'pending', NULL, '2026-05-14 23:01:22'),
(43, 49, 1, 'pending', 'rejected', NULL, '2026-05-14 23:01:45'),
(44, 50, NULL, NULL, 'pending', NULL, '2026-05-14 23:02:06'),
(45, 50, 1, 'pending', 'confirmed', NULL, '2026-05-14 23:02:20'),
(46, 50, 1, 'confirmed', 'cancelled', NULL, '2026-05-14 23:03:19'),
(47, 51, NULL, NULL, 'pending', NULL, '2026-05-14 23:03:44'),
(48, 51, 1, 'pending', 'cancelled', NULL, '2026-05-14 23:03:49'),
(49, 52, NULL, NULL, 'pending', NULL, '2026-05-14 23:04:05'),
(50, 52, 1, 'pending', 'confirmed', NULL, '2026-05-14 23:04:27'),
(51, 53, NULL, NULL, 'pending', NULL, '2026-05-18 13:47:02'),
(52, 53, 1, 'pending', 'confirmed', NULL, '2026-05-18 13:47:51'),
(53, 54, NULL, NULL, 'pending', NULL, '2026-05-18 13:48:23'),
(54, 54, 1, 'pending', 'confirmed', NULL, '2026-05-18 13:48:28'),
(55, 55, NULL, NULL, 'pending', NULL, '2026-05-18 13:50:33'),
(56, 55, 1, 'pending', 'confirmed', NULL, '2026-05-18 13:51:03'),
(57, 53, 1, 'confirmed', 'completed', NULL, '2026-05-18 15:02:21'),
(58, 54, 1, 'confirmed', 'completed', NULL, '2026-05-18 15:02:44'),
(59, 33, 1, 'cancelled', 'completed', NULL, '2026-05-18 15:14:16'),
(60, 31, 1, 'cancelled', 'completed', NULL, '2026-05-18 15:14:24'),
(61, 52, 1, 'confirmed', 'completed', NULL, '2026-05-18 15:14:39'),
(62, 47, 1, 'confirmed', 'completed', NULL, '2026-05-18 15:14:48'),
(63, 46, 1, 'confirmed', 'completed', NULL, '2026-05-18 15:14:55'),
(64, 49, 1, 'rejected', 'cancelled', NULL, '2026-05-18 15:15:09'),
(65, 45, 1, 'confirmed', 'cancelled', NULL, '2026-05-18 15:15:11'),
(66, 43, 1, 'confirmed', 'cancelled', NULL, '2026-05-18 15:15:14'),
(67, 44, 1, 'confirmed', 'cancelled', NULL, '2026-05-18 15:15:15'),
(68, 55, 1, 'confirmed', 'completed', NULL, '2026-05-18 20:11:09'),
(69, 48, 1, 'confirmed', 'completed', NULL, '2026-05-18 20:23:26'),
(70, 56, NULL, NULL, 'pending', NULL, '2026-05-18 20:25:36'),
(71, 57, NULL, NULL, 'pending', NULL, '2026-05-18 20:25:53'),
(72, 58, NULL, NULL, 'pending', NULL, '2026-05-18 20:26:17'),
(73, 59, NULL, NULL, 'pending', NULL, '2026-05-18 20:26:35'),
(74, 60, NULL, NULL, 'pending', NULL, '2026-05-18 20:26:54'),
(75, 61, NULL, NULL, 'pending', NULL, '2026-05-18 20:27:13'),
(76, 62, NULL, NULL, 'pending', NULL, '2026-05-18 20:27:35'),
(77, 63, NULL, NULL, 'pending', NULL, '2026-05-18 20:27:46'),
(78, 64, NULL, NULL, 'pending', NULL, '2026-05-18 20:28:02'),
(79, 65, NULL, NULL, 'pending', NULL, '2026-05-18 20:28:17'),
(80, 66, NULL, NULL, 'pending', NULL, '2026-05-18 20:28:34'),
(81, 67, NULL, NULL, 'pending', NULL, '2026-05-18 20:29:07'),
(82, 68, NULL, NULL, 'pending', NULL, '2026-05-18 20:29:21'),
(83, 69, NULL, NULL, 'pending', NULL, '2026-05-18 20:29:35'),
(84, 70, NULL, NULL, 'pending', NULL, '2026-05-18 20:30:15'),
(85, 71, NULL, NULL, 'pending', NULL, '2026-05-18 20:30:27'),
(86, 62, 1, 'pending', 'confirmed', NULL, '2026-05-18 20:31:23'),
(87, 68, 1, 'pending', 'confirmed', NULL, '2026-05-18 20:31:25'),
(88, 63, 1, 'pending', 'confirmed', NULL, '2026-05-18 20:31:26'),
(89, 69, 1, 'pending', 'confirmed', NULL, '2026-05-18 20:31:26'),
(90, 64, 1, 'pending', 'confirmed', NULL, '2026-05-18 20:31:27'),
(91, 56, 1, 'pending', 'confirmed', NULL, '2026-05-18 20:31:28'),
(92, 57, 1, 'pending', 'confirmed', NULL, '2026-05-18 20:31:28'),
(93, 65, 1, 'pending', 'confirmed', NULL, '2026-05-18 20:31:28'),
(94, 58, 1, 'pending', 'confirmed', NULL, '2026-05-18 20:31:29'),
(95, 60, 1, 'pending', 'confirmed', NULL, '2026-05-18 20:31:29'),
(96, 66, 1, 'pending', 'confirmed', NULL, '2026-05-18 20:31:29'),
(97, 59, 1, 'pending', 'confirmed', NULL, '2026-05-18 20:31:29'),
(98, 70, 1, 'pending', 'confirmed', NULL, '2026-05-18 20:31:30'),
(99, 61, 1, 'pending', 'confirmed', NULL, '2026-05-18 20:31:30'),
(100, 71, 1, 'pending', 'confirmed', NULL, '2026-05-18 20:31:30'),
(101, 67, 1, 'pending', 'confirmed', NULL, '2026-05-18 20:31:30'),
(102, 72, NULL, NULL, 'pending', NULL, '2026-05-18 20:38:38'),
(103, 72, 1, 'pending', 'confirmed', NULL, '2026-05-18 20:57:04'),
(104, 62, 1, 'confirmed', 'cancelled', NULL, '2026-05-18 22:23:28'),
(105, 68, 1, 'confirmed', 'completed', NULL, '2026-05-18 22:23:43'),
(106, 62, 1, 'cancelled', 'confirmed', NULL, '2026-05-18 22:26:28'),
(107, 68, 1, 'completed', 'confirmed', NULL, '2026-05-18 22:27:05'),
(108, 73, NULL, NULL, 'pending', NULL, '2026-05-18 22:27:43'),
(109, 73, 1, 'pending', 'confirmed', NULL, '2026-05-18 22:35:43'),
(110, 74, NULL, NULL, 'pending', NULL, '2026-05-19 12:19:58'),
(111, 74, 1, 'pending', 'confirmed', NULL, '2026-05-19 12:20:18'),
(112, 72, 1, 'confirmed', 'completed', NULL, '2026-05-19 12:20:33'),
(113, 67, 1, 'confirmed', 'completed', NULL, '2026-05-19 12:20:36'),
(114, 61, 1, 'confirmed', 'completed', NULL, '2026-05-19 12:20:41'),
(115, 71, 1, 'confirmed', 'completed', NULL, '2026-05-19 12:20:45'),
(116, 59, 1, 'confirmed', 'no_show', NULL, '2026-05-19 12:20:57'),
(117, 70, 1, 'confirmed', 'completed', NULL, '2026-05-19 12:21:01'),
(118, 66, 1, 'confirmed', 'completed', NULL, '2026-05-19 12:21:04'),
(119, 58, 1, 'confirmed', 'no_show', NULL, '2026-05-19 12:21:08'),
(120, 60, 1, 'confirmed', 'completed', NULL, '2026-05-19 12:21:14'),
(121, 56, 1, 'confirmed', 'completed', NULL, '2026-05-19 12:21:17'),
(122, 57, 1, 'confirmed', 'completed', NULL, '2026-05-19 12:21:21'),
(123, 65, 1, 'confirmed', 'completed', NULL, '2026-05-19 12:21:23'),
(124, 64, 1, 'confirmed', 'completed', NULL, '2026-05-19 12:21:26'),
(125, 63, 1, 'confirmed', 'completed', NULL, '2026-05-19 12:21:30'),
(126, 69, 1, 'confirmed', 'completed', NULL, '2026-05-19 12:21:36'),
(127, 62, 1, 'confirmed', 'completed', NULL, '2026-05-19 12:21:40'),
(128, 68, 1, 'confirmed', 'completed', NULL, '2026-05-19 12:21:43'),
(131, 74, 1, 'confirmed', 'completed', NULL, '2026-05-19 13:05:14'),
(132, 76, NULL, NULL, 'pending', NULL, '2026-05-19 13:06:28'),
(133, 77, NULL, NULL, 'pending', NULL, '2026-05-19 13:07:19'),
(134, 77, 1, 'pending', 'rejected', NULL, '2026-05-19 13:07:45'),
(135, 76, 1, 'pending', 'rejected', NULL, '2026-05-19 13:20:44'),
(136, 78, NULL, NULL, 'pending', NULL, '2026-05-19 14:01:29'),
(137, 79, NULL, NULL, 'pending', NULL, '2026-05-19 14:01:41'),
(138, 80, NULL, NULL, 'pending', NULL, '2026-05-19 14:01:54'),
(139, 81, NULL, NULL, 'pending', NULL, '2026-05-19 14:02:07'),
(140, 82, NULL, NULL, 'pending', NULL, '2026-05-19 14:02:20'),
(141, 83, NULL, NULL, 'pending', NULL, '2026-05-19 14:02:34'),
(142, 78, 1, 'pending', 'confirmed', NULL, '2026-05-19 14:02:44'),
(143, 79, 1, 'pending', 'rejected', NULL, '2026-05-19 14:25:32'),
(144, 81, 1, 'pending', 'rejected', NULL, '2026-05-19 14:35:58'),
(145, 83, 1, 'pending', 'confirmed', NULL, '2026-05-19 14:36:33'),
(146, 82, 1, 'pending', 'confirmed', NULL, '2026-05-19 14:36:39'),
(147, 78, 1, 'confirmed', 'completed', NULL, '2026-05-19 14:36:56'),
(148, 73, 1, 'confirmed', 'completed', NULL, '2026-05-23 09:51:43'),
(149, 82, 1, 'confirmed', 'completed', NULL, '2026-05-23 09:51:47'),
(150, 83, 1, 'confirmed', 'completed', NULL, '2026-05-23 09:51:49'),
(151, 84, NULL, NULL, 'pending', NULL, '2026-05-23 09:52:21'),
(152, 85, NULL, NULL, 'pending', NULL, '2026-05-23 09:52:36'),
(153, 86, NULL, NULL, 'pending', NULL, '2026-05-23 09:53:00'),
(154, 84, 1, 'pending', 'confirmed', NULL, '2026-05-23 09:53:20'),
(155, 85, 1, 'pending', 'confirmed', NULL, '2026-05-23 09:53:21'),
(156, 86, 1, 'pending', 'confirmed', NULL, '2026-05-23 09:53:22'),
(157, 87, NULL, NULL, 'pending', NULL, '2026-05-23 09:53:52'),
(158, 88, NULL, NULL, 'pending', NULL, '2026-05-23 09:54:04'),
(159, 89, NULL, NULL, 'pending', NULL, '2026-05-23 09:54:18'),
(160, 87, 1, 'pending', 'confirmed', NULL, '2026-05-23 09:54:39'),
(161, 89, 1, 'pending', 'rejected', NULL, '2026-05-23 09:54:57'),
(162, 88, 1, 'pending', 'confirmed', NULL, '2026-05-23 09:55:58'),
(163, 90, NULL, NULL, 'pending', NULL, '2026-05-23 11:52:22'),
(164, 90, 1, 'pending', 'confirmed', NULL, '2026-05-23 11:52:29'),
(165, 91, NULL, NULL, 'pending', NULL, '2026-05-23 11:52:51'),
(166, 92, NULL, NULL, 'pending', NULL, '2026-05-23 11:53:04'),
(167, 92, 1, 'pending', 'confirmed', NULL, '2026-05-23 11:53:07'),
(168, 91, 1, 'pending', 'confirmed', NULL, '2026-05-23 11:53:09'),
(169, 80, 1, 'pending', 'confirmed', NULL, '2026-05-23 11:53:58'),
(170, 80, 1, 'confirmed', 'completed', NULL, '2026-05-23 11:54:05'),
(171, 86, 1, 'confirmed', 'completed', NULL, '2026-05-23 11:54:31'),
(172, 85, 1, 'confirmed', 'completed', NULL, '2026-05-23 11:54:50'),
(173, 84, 1, 'confirmed', 'completed', NULL, '2026-05-23 11:55:34'),
(174, 93, NULL, NULL, 'pending', NULL, '2026-05-23 15:16:23'),
(175, 93, 1, 'pending', 'confirmed', NULL, '2026-05-23 15:17:22'),
(176, 87, 1, 'confirmed', 'completed', NULL, '2026-05-23 17:09:57'),
(177, 91, 1, 'confirmed', 'completed', NULL, '2026-05-23 17:10:08'),
(178, 92, 1, 'confirmed', 'completed', NULL, '2026-05-23 17:10:50'),
(179, 90, 1, 'confirmed', 'completed', NULL, '2026-05-23 17:10:55'),
(180, 93, 1, 'confirmed', 'no_show', NULL, '2026-05-23 17:11:20'),
(181, 94, NULL, NULL, 'pending', NULL, '2026-05-23 17:27:23'),
(182, 94, 1, 'pending', 'confirmed', NULL, '2026-05-23 17:29:09'),
(183, 94, 1, 'confirmed', 'completed', NULL, '2026-05-25 10:56:41'),
(184, 88, 1, 'confirmed', 'completed', NULL, '2026-05-25 10:56:44');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(100) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `model_type`, `model_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'create', 'appointments', 1, NULL, '{\"status\": \"confirmed\"}', '127.0.0.1', 'iPhone App', '2026-05-14 13:44:51'),
(2, 4, 'update', 'appointments', 2, '{\"status\": \"confirmed\"}', '{\"status\": \"completed\"}', '127.0.0.1', 'Admin Panel', '2026-05-14 13:44:51'),
(3, NULL, 'updated', 'App\\Models\\User', 1, '\"{\\\"phone\\\":\\\"+905551111111\\\",\\\"password\\\":\\\"e10adc3949ba59abbe56e057f20f883e\\\",\\\"updated_at\\\":\\\"2026-05-14T19:07:22.000000Z\\\"}\"', '\"{\\\"phone\\\":\\\"05554443322\\\",\\\"password\\\":\\\"$2y$12$fEkyZNWK6pFExjKlPWpgtu21TtVSe7qDGzBIAH9EnrtHG6pCL.2Zi\\\",\\\"updated_at\\\":\\\"2026-05-14 16:08:05\\\"}\"', '127.0.0.1', 'Symfony', '2026-05-14 16:08:05'),
(4, 1, 'updated', 'App\\Models\\User', 1, '\"{\\\"last_login_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T16:08:05.000000Z\\\"}\"', '\"{\\\"last_login_at\\\":\\\"2026-05-14 16:10:09\\\",\\\"updated_at\\\":\\\"2026-05-14 16:10:09\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 16:10:09'),
(5, 1, 'updated', 'App\\Models\\User', 1, '\"{\\\"last_login_at\\\":\\\"2026-05-14T16:10:09.000000Z\\\",\\\"updated_at\\\":\\\"2026-05-14T16:10:09.000000Z\\\"}\"', '\"{\\\"last_login_at\\\":\\\"2026-05-14 16:14:19\\\",\\\"updated_at\\\":\\\"2026-05-14 16:14:19\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 16:14:19'),
(6, 1, 'updated', 'App\\Models\\User', 1, '\"{\\\"last_login_at\\\":\\\"2026-05-14T16:14:19.000000Z\\\",\\\"updated_at\\\":\\\"2026-05-14T16:14:19.000000Z\\\"}\"', '\"{\\\"last_login_at\\\":\\\"2026-05-14 16:17:03\\\",\\\"updated_at\\\":\\\"2026-05-14 16:17:03\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 16:17:03'),
(7, 1, 'updated', 'App\\Models\\Appointment', 3, '\"{\\\"status\\\":\\\"pending\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T16:44:51.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-14 17:23:12\\\",\\\"updated_at\\\":\\\"2026-05-14 17:23:12\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:23:12'),
(8, 1, 'updated', 'App\\Models\\Appointment', 1, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T16:44:51.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-14 17:23:54\\\",\\\"updated_at\\\":\\\"2026-05-14 17:23:54\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:23:54'),
(9, 1, 'updated', 'App\\Models\\User', 1, '\"{\\\"last_login_at\\\":\\\"2026-05-14T16:17:03.000000Z\\\",\\\"updated_at\\\":\\\"2026-05-14T16:17:03.000000Z\\\"}\"', '\"{\\\"last_login_at\\\":\\\"2026-05-14 19:51:32\\\",\\\"updated_at\\\":\\\"2026-05-14 19:51:32\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 19:51:32'),
(10, 1, 'created', 'App\\Models\\User', 9, NULL, '\"{\\\"uuid\\\":\\\"ad3f020b-1a57-434d-8672-d5cd49c81950\\\",\\\"role_id\\\":6,\\\"first_name\\\":\\\"G\\\\u00f6rkem\\\",\\\"last_name\\\":\\\"F\\\\u0130DAN\\\",\\\"email\\\":\\\"grkmfdn55@gmail.com\\\",\\\"phone\\\":\\\"05555555555\\\",\\\"gender\\\":\\\"male\\\",\\\"birth_date\\\":\\\"2000-01-01 00:00:00\\\",\\\"password\\\":\\\"$2y$12$ruWbuIK7clDcKiPQI6b24O1J2tDOvUfObY8vaOtr.Kp8VLnOWwHLG\\\",\\\"status\\\":\\\"active\\\",\\\"updated_at\\\":\\\"2026-05-14 20:32:59\\\",\\\"created_at\\\":\\\"2026-05-14 20:32:59\\\",\\\"id\\\":9}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 20:32:59'),
(11, 1, 'updated', 'App\\Models\\Service', 3, '\"{\\\"is_active\\\":true,\\\"updated_at\\\":\\\"2026-05-14T16:44:44.000000Z\\\"}\"', '\"{\\\"is_active\\\":false,\\\"updated_at\\\":\\\"2026-05-14 20:33:55\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 20:33:55'),
(12, 1, 'updated', 'App\\Models\\Service', 3, '\"{\\\"is_active\\\":false}\"', '\"{\\\"is_active\\\":true}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 20:33:55'),
(13, 1, 'updated', 'App\\Models\\Service', 3, '\"{\\\"is_active\\\":true,\\\"updated_at\\\":\\\"2026-05-14T20:33:55.000000Z\\\"}\"', '\"{\\\"is_active\\\":false,\\\"updated_at\\\":\\\"2026-05-14 20:33:56\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 20:33:56'),
(14, 1, 'updated', 'App\\Models\\Service', 3, '\"{\\\"is_active\\\":false,\\\"updated_at\\\":\\\"2026-05-14T20:33:56.000000Z\\\"}\"', '\"{\\\"is_active\\\":true,\\\"updated_at\\\":\\\"2026-05-14 20:33:57\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 20:33:57'),
(15, 1, 'updated', 'App\\Models\\Service', 3, '\"{\\\"is_active\\\":true}\"', '\"{\\\"is_active\\\":false}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 20:33:57'),
(16, 1, 'updated', 'App\\Models\\Service', 3, '\"{\\\"is_active\\\":false,\\\"updated_at\\\":\\\"2026-05-14T20:33:57.000000Z\\\"}\"', '\"{\\\"is_active\\\":true,\\\"updated_at\\\":\\\"2026-05-14 20:33:58\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 20:33:58'),
(17, 1, 'updated', 'App\\Models\\Service', 4, '\"{\\\"is_active\\\":true,\\\"updated_at\\\":\\\"2026-05-14T16:44:44.000000Z\\\"}\"', '\"{\\\"is_active\\\":false,\\\"updated_at\\\":\\\"2026-05-14 20:34:00\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 20:34:00'),
(18, 1, 'updated', 'App\\Models\\Service', 4, '\"{\\\"is_active\\\":false,\\\"updated_at\\\":\\\"2026-05-14T20:34:00.000000Z\\\"}\"', '\"{\\\"is_active\\\":true,\\\"updated_at\\\":\\\"2026-05-14 20:34:01\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 20:34:01'),
(19, 1, 'created', 'App\\Models\\Appointment', 42, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"9\\\",\\\"employee_id\\\":\\\"2\\\",\\\"start_at\\\":\\\"2026-05-15 14:00:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":\\\"afdmad\\\",\\\"internal_note\\\":\\\"adkfna\\\",\\\"created_by\\\":1,\\\"uuid\\\":\\\"090073dc-d3db-4036-a813-a800f70a9e5b\\\",\\\"appointment_code\\\":\\\"BV-GI0PG8QP\\\",\\\"end_at\\\":\\\"2026-05-15 14:45:00\\\",\\\"total_duration\\\":45,\\\"updated_at\\\":\\\"2026-05-14 21:32:28\\\",\\\"created_at\\\":\\\"2026-05-14 21:32:28\\\",\\\"id\\\":42}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 21:32:28'),
(20, 1, 'updated', 'App\\Models\\Appointment', 42, NULL, '\"{\\\"subtotal\\\":450,\\\"total_price\\\":450}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 21:32:28'),
(21, 1, 'updated', 'App\\Models\\Service', 1, '\"{\\\"is_active\\\":true,\\\"updated_at\\\":\\\"2026-05-14T13:44:44.000000Z\\\"}\"', '\"{\\\"is_active\\\":false,\\\"updated_at\\\":\\\"2026-05-15 00:54:29\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 21:54:29'),
(22, 1, 'updated', 'App\\Models\\Service', 1, '\"{\\\"is_active\\\":false}\"', '\"{\\\"is_active\\\":true}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 21:54:29'),
(23, 1, 'updated', 'App\\Models\\Appointment', 41, '\"{\\\"status\\\":\\\"pending\\\",\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T21:23:54.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"cancelled\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-15 01:00:19\\\",\\\"updated_at\\\":\\\"2026-05-15 01:00:19\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:00:19'),
(24, 1, 'updated', 'App\\Models\\Appointment', 40, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T21:23:54.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"cancelled\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-15 01:00:30\\\",\\\"updated_at\\\":\\\"2026-05-15 01:00:30\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:00:30'),
(25, 1, 'updated', 'App\\Models\\Appointment', 39, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T21:23:54.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"cancelled\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-15 01:00:31\\\",\\\"updated_at\\\":\\\"2026-05-15 01:00:31\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:00:31'),
(26, 1, 'updated', 'App\\Models\\Appointment', 38, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T21:23:54.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"cancelled\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-15 01:00:33\\\",\\\"updated_at\\\":\\\"2026-05-15 01:00:33\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:00:33'),
(27, 1, 'updated', 'App\\Models\\Appointment', 37, '\"{\\\"status\\\":\\\"pending\\\",\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T21:23:54.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"cancelled\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-15 01:00:34\\\",\\\"updated_at\\\":\\\"2026-05-15 01:00:34\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:00:34'),
(28, 1, 'updated', 'App\\Models\\Appointment', 36, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T21:23:54.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"cancelled\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-15 01:00:35\\\",\\\"updated_at\\\":\\\"2026-05-15 01:00:35\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:00:35'),
(29, 1, 'updated', 'App\\Models\\Appointment', 35, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T21:23:54.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"cancelled\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-15 01:00:37\\\",\\\"updated_at\\\":\\\"2026-05-15 01:00:37\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:00:37'),
(30, 1, 'updated', 'App\\Models\\Appointment', 34, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T21:23:54.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"cancelled\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-15 01:00:39\\\",\\\"updated_at\\\":\\\"2026-05-15 01:00:39\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:00:39'),
(31, 1, 'updated', 'App\\Models\\Appointment', 28, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T21:23:54.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"cancelled\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-15 01:00:42\\\",\\\"updated_at\\\":\\\"2026-05-15 01:00:42\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:00:42'),
(32, 1, 'updated', 'App\\Models\\Appointment', 33, '\"{\\\"status\\\":\\\"pending\\\",\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T21:23:54.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"cancelled\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-15 01:00:44\\\",\\\"updated_at\\\":\\\"2026-05-15 01:00:44\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:00:44'),
(33, 1, 'updated', 'App\\Models\\Appointment', 32, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T21:23:54.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"cancelled\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-15 01:00:46\\\",\\\"updated_at\\\":\\\"2026-05-15 01:00:46\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:00:46'),
(34, 1, 'updated', 'App\\Models\\Appointment', 31, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T21:23:54.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"cancelled\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-15 01:00:48\\\",\\\"updated_at\\\":\\\"2026-05-15 01:00:48\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:00:48'),
(35, 1, 'updated', 'App\\Models\\Appointment', 30, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T21:23:54.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"cancelled\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-15 01:00:50\\\",\\\"updated_at\\\":\\\"2026-05-15 01:00:50\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:00:50'),
(36, 1, 'updated', 'App\\Models\\Appointment', 29, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T21:23:54.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"cancelled\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-15 01:00:52\\\",\\\"updated_at\\\":\\\"2026-05-15 01:00:52\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:00:52'),
(37, 1, 'updated', 'App\\Models\\Appointment', 27, '\"{\\\"status\\\":\\\"pending\\\",\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T21:23:54.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"cancelled\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-15 01:00:57\\\",\\\"updated_at\\\":\\\"2026-05-15 01:00:57\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:00:57'),
(38, 1, 'updated', 'App\\Models\\Appointment', 26, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T21:23:54.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"cancelled\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-15 01:00:58\\\",\\\"updated_at\\\":\\\"2026-05-15 01:00:58\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:00:58'),
(39, 1, 'updated', 'App\\Models\\Appointment', 25, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T21:23:54.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"cancelled\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-15 01:00:59\\\",\\\"updated_at\\\":\\\"2026-05-15 01:00:59\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:00:59'),
(40, 1, 'updated', 'App\\Models\\Appointment', 24, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T21:23:54.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"cancelled\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-15 01:01:00\\\",\\\"updated_at\\\":\\\"2026-05-15 01:01:00\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:01:00'),
(41, 1, 'updated', 'App\\Models\\Appointment', 23, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T21:23:54.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"cancelled\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-15 01:01:00\\\",\\\"updated_at\\\":\\\"2026-05-15 01:01:00\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:01:00'),
(42, 1, 'updated', 'App\\Models\\Appointment', 19, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T21:23:54.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"cancelled\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-15 01:01:02\\\",\\\"updated_at\\\":\\\"2026-05-15 01:01:02\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:01:02'),
(43, 1, 'updated', 'App\\Models\\Appointment', 15, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T21:23:54.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"cancelled\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-15 01:01:04\\\",\\\"updated_at\\\":\\\"2026-05-15 01:01:04\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:01:04'),
(44, 1, 'updated', 'App\\Models\\Appointment', 42, '\"{\\\"status\\\":\\\"pending\\\",\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T18:32:28.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"cancelled\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-15 01:01:08\\\",\\\"updated_at\\\":\\\"2026-05-15 01:01:08\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:01:08'),
(45, 1, 'updated', 'App\\Models\\Appointment', 7, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T21:23:54.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"cancelled\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-15 01:01:10\\\",\\\"updated_at\\\":\\\"2026-05-15 01:01:10\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:01:10'),
(46, 1, 'created', 'App\\Models\\Appointment', 43, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"6\\\",\\\"employee_id\\\":\\\"1\\\",\\\"start_at\\\":\\\"2026-05-15 08:00:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"9391d3aa-9d9f-4871-8fdc-5e24a29cd1e5\\\",\\\"appointment_code\\\":\\\"BV-FOYILC9Y\\\",\\\"end_at\\\":\\\"2026-05-15 08:45:00\\\",\\\"total_duration\\\":45,\\\"updated_at\\\":\\\"2026-05-15 01:20:10\\\",\\\"created_at\\\":\\\"2026-05-15 01:20:10\\\",\\\"id\\\":43}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:20:10'),
(47, 1, 'updated', 'App\\Models\\Appointment', 43, NULL, '\"{\\\"subtotal\\\":450,\\\"total_price\\\":450}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:20:10'),
(48, 1, 'created', 'App\\Models\\Appointment', 44, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"7\\\",\\\"employee_id\\\":\\\"2\\\",\\\"start_at\\\":\\\"2026-05-15 08:00:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"5143d19e-86af-4dba-9dda-f21e773b2f4f\\\",\\\"appointment_code\\\":\\\"BV-MHOILQHZ\\\",\\\"end_at\\\":\\\"2026-05-15 08:45:00\\\",\\\"total_duration\\\":45,\\\"updated_at\\\":\\\"2026-05-15 01:21:10\\\",\\\"created_at\\\":\\\"2026-05-15 01:21:10\\\",\\\"id\\\":44}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:21:10'),
(49, 1, 'updated', 'App\\Models\\Appointment', 44, NULL, '\"{\\\"subtotal\\\":450,\\\"total_price\\\":450}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:21:10'),
(50, 1, 'created', 'App\\Models\\Appointment', 45, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"8\\\",\\\"employee_id\\\":\\\"3\\\",\\\"start_at\\\":\\\"2026-05-15 08:30:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"7f23292f-30bb-4307-bc05-6b70b844704d\\\",\\\"appointment_code\\\":\\\"BV-KK0EAV3E\\\",\\\"end_at\\\":\\\"2026-05-15 09:15:00\\\",\\\"total_duration\\\":45,\\\"updated_at\\\":\\\"2026-05-15 01:22:30\\\",\\\"created_at\\\":\\\"2026-05-15 01:22:30\\\",\\\"id\\\":45}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:22:30'),
(51, 1, 'updated', 'App\\Models\\Appointment', 45, NULL, '\"{\\\"subtotal\\\":450,\\\"total_price\\\":450}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:22:30'),
(52, 1, 'updated', 'App\\Models\\Appointment', 43, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-14T22:20:10.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-15 01:23:06\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:23:06'),
(53, 1, 'created', 'App\\Models\\Appointment', 46, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"9\\\",\\\"employee_id\\\":\\\"3\\\",\\\"start_at\\\":\\\"2026-05-15 09:30:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"d9b07451-df00-4742-a217-f9af1f4ded78\\\",\\\"appointment_code\\\":\\\"BV-V43BVGP4\\\",\\\"end_at\\\":\\\"2026-05-15 11:00:00\\\",\\\"total_duration\\\":90,\\\"updated_at\\\":\\\"2026-05-15 01:29:14\\\",\\\"created_at\\\":\\\"2026-05-15 01:29:14\\\",\\\"id\\\":46}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:29:14'),
(54, 1, 'updated', 'App\\Models\\Appointment', 46, NULL, '\"{\\\"subtotal\\\":999,\\\"total_price\\\":999}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:29:14'),
(55, 1, 'created', 'App\\Models\\Appointment', 47, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"9\\\",\\\"employee_id\\\":\\\"1\\\",\\\"start_at\\\":\\\"2026-05-15 10:00:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"ec6088c4-ffdc-4dc5-917e-bcdf6a846511\\\",\\\"appointment_code\\\":\\\"BV-VJ8PDIAV\\\",\\\"end_at\\\":\\\"2026-05-15 10:30:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-15 01:31:57\\\",\\\"created_at\\\":\\\"2026-05-15 01:31:57\\\",\\\"id\\\":47}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:31:57'),
(56, 1, 'updated', 'App\\Models\\Appointment', 47, NULL, '\"{\\\"subtotal\\\":300,\\\"total_price\\\":300}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:31:57'),
(57, 1, 'updated', 'App\\Models\\Appointment', 44, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-14T22:21:10.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-15 01:34:45\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:34:45'),
(58, 1, 'updated', 'App\\Models\\Appointment', 45, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-14T22:22:30.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-15 01:34:48\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:34:48'),
(59, 1, 'updated', 'App\\Models\\Appointment', 46, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-14T22:29:14.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-15 01:34:50\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:34:50'),
(60, 1, 'updated', 'App\\Models\\Appointment', 47, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-14T22:31:57.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-15 01:34:52\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:34:52'),
(61, 1, 'created', 'App\\Models\\Appointment', 48, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"7\\\",\\\"employee_id\\\":\\\"1\\\",\\\"start_at\\\":\\\"2026-05-16 19:30:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"a8dd8a9e-78d6-4daf-a5e4-5c3856e6e042\\\",\\\"appointment_code\\\":\\\"BV-B9BAEMHU\\\",\\\"end_at\\\":\\\"2026-05-16 20:00:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-15 01:35:31\\\",\\\"created_at\\\":\\\"2026-05-15 01:35:31\\\",\\\"id\\\":48}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:35:31'),
(62, 1, 'updated', 'App\\Models\\Appointment', 48, NULL, '\"{\\\"subtotal\\\":350,\\\"total_price\\\":350}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:35:31'),
(63, 1, 'updated', 'App\\Models\\Appointment', 48, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-14T22:35:31.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-15 01:36:03\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 22:36:03'),
(64, 1, 'created', 'App\\Models\\Appointment', 49, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"6\\\",\\\"employee_id\\\":\\\"2\\\",\\\"start_at\\\":\\\"2026-05-15 09:00:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"27482e72-de4a-4c76-a749-853660aea318\\\",\\\"appointment_code\\\":\\\"BV-9WOEHNP7\\\",\\\"end_at\\\":\\\"2026-05-15 09:30:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-15 02:01:22\\\",\\\"created_at\\\":\\\"2026-05-15 02:01:22\\\",\\\"id\\\":49}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 23:01:22'),
(65, 1, 'updated', 'App\\Models\\Appointment', 49, NULL, '\"{\\\"subtotal\\\":450,\\\"total_price\\\":450}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 23:01:22'),
(66, 1, 'updated', 'App\\Models\\Appointment', 49, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-14T23:01:22.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"rejected\\\",\\\"updated_at\\\":\\\"2026-05-15 02:01:45\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 23:01:45'),
(67, 1, 'created', 'App\\Models\\Appointment', 50, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"6\\\",\\\"employee_id\\\":\\\"2\\\",\\\"start_at\\\":\\\"2026-05-15 09:00:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"e57a594e-546d-4ff9-88c8-0b2ac09f8cc8\\\",\\\"appointment_code\\\":\\\"BV-OGCZIFWS\\\",\\\"end_at\\\":\\\"2026-05-15 09:30:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-15 02:02:06\\\",\\\"created_at\\\":\\\"2026-05-15 02:02:06\\\",\\\"id\\\":50}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 23:02:06'),
(68, 1, 'updated', 'App\\Models\\Appointment', 50, NULL, '\"{\\\"subtotal\\\":450,\\\"total_price\\\":450}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 23:02:06'),
(69, 1, 'updated', 'App\\Models\\Appointment', 50, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-14T23:02:06.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-15 02:02:20\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 23:02:20'),
(70, 1, 'updated', 'App\\Models\\Appointment', 50, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T23:02:20.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"cancelled\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-15 02:03:19\\\",\\\"updated_at\\\":\\\"2026-05-15 02:03:19\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 23:03:19'),
(71, 1, 'created', 'App\\Models\\Appointment', 51, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"7\\\",\\\"employee_id\\\":\\\"3\\\",\\\"start_at\\\":\\\"2026-05-15 08:00:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"89624b4c-bad2-43f6-8000-884f20b18793\\\",\\\"appointment_code\\\":\\\"BV-SABYUQMO\\\",\\\"end_at\\\":\\\"2026-05-15 09:30:00\\\",\\\"total_duration\\\":90,\\\"updated_at\\\":\\\"2026-05-15 02:03:44\\\",\\\"created_at\\\":\\\"2026-05-15 02:03:44\\\",\\\"id\\\":51}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 23:03:44'),
(72, 1, 'updated', 'App\\Models\\Appointment', 51, NULL, '\"{\\\"subtotal\\\":999,\\\"total_price\\\":999}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 23:03:44'),
(73, 1, 'updated', 'App\\Models\\Appointment', 51, '\"{\\\"status\\\":\\\"pending\\\",\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T23:03:44.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"cancelled\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-15 02:03:49\\\",\\\"updated_at\\\":\\\"2026-05-15 02:03:49\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 23:03:49'),
(74, 1, 'created', 'App\\Models\\Appointment', 52, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"7\\\",\\\"employee_id\\\":\\\"3\\\",\\\"start_at\\\":\\\"2026-05-15 11:30:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"c1713386-c6d4-4fb4-8596-2c46ee0ce92e\\\",\\\"appointment_code\\\":\\\"BV-OHOIQNBJ\\\",\\\"end_at\\\":\\\"2026-05-15 12:00:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-15 02:04:05\\\",\\\"created_at\\\":\\\"2026-05-15 02:04:05\\\",\\\"id\\\":52}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 23:04:05'),
(75, 1, 'updated', 'App\\Models\\Appointment', 52, NULL, '\"{\\\"subtotal\\\":450,\\\"total_price\\\":450}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 23:04:05'),
(76, 1, 'updated', 'App\\Models\\Appointment', 52, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-14T23:04:05.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-15 02:04:27\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 23:04:27'),
(77, 1, 'updated', 'App\\Models\\User', 1, '\"{\\\"last_login_at\\\":\\\"2026-05-14T16:51:32.000000Z\\\",\\\"updated_at\\\":\\\"2026-05-14T16:51:32.000000Z\\\"}\"', '\"{\\\"last_login_at\\\":\\\"2026-05-18 16:18:55\\\",\\\"updated_at\\\":\\\"2026-05-18 16:18:55\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 13:18:55'),
(78, 1, 'updated', 'App\\Models\\User', 1, '\"{\\\"last_login_at\\\":\\\"2026-05-18T13:18:55.000000Z\\\",\\\"updated_at\\\":\\\"2026-05-18T13:18:55.000000Z\\\"}\"', '\"{\\\"last_login_at\\\":\\\"2026-05-18 16:19:13\\\",\\\"updated_at\\\":\\\"2026-05-18 16:19:13\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 13:19:13'),
(79, 1, 'created', 'App\\Models\\User', 10, NULL, '\"{\\\"uuid\\\":\\\"27e96822-8cb0-4b7a-b4e1-083f4f05d36f\\\",\\\"role_id\\\":6,\\\"first_name\\\":\\\"MUSTAFA\\\",\\\"last_name\\\":\\\"KARA\\\",\\\"email\\\":\\\"mustafakara200533@gmail.com\\\",\\\"phone\\\":\\\"5528120412\\\",\\\"gender\\\":\\\"male\\\",\\\"birth_date\\\":\\\"2005-12-04 00:00:00\\\",\\\"password\\\":\\\"$2y$12$ezy9r0EIgMECj4BxSEPWDOg2oR0BjexJWGvNNtk8nGD.9dpL.7UwS\\\",\\\"status\\\":\\\"active\\\",\\\"updated_at\\\":\\\"2026-05-18 16:26:34\\\",\\\"created_at\\\":\\\"2026-05-18 16:26:34\\\",\\\"id\\\":10}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 13:26:34'),
(80, 1, 'created', 'App\\Models\\Service', 6, NULL, '\"{\\\"name\\\":\\\"F\\\\u00f6n \\\\u00c7ekimi\\\",\\\"category_id\\\":\\\"4\\\",\\\"duration_minutes\\\":\\\"30\\\",\\\"price\\\":\\\"250\\\",\\\"discounted_price\\\":null,\\\"gender_type\\\":\\\"male\\\",\\\"description\\\":\\\"Sa\\\\u00e7a \\\\u015fekil vermek bizim i\\\\u015fimiz. Sizi biz F\\\\u00d6Nleyelim.\\\",\\\"slug\\\":\\\"fon-cekimi\\\",\\\"branch_id\\\":1,\\\"is_active\\\":true,\\\"is_popular\\\":false,\\\"is_featured\\\":false,\\\"updated_at\\\":\\\"2026-05-18 16:28:28\\\",\\\"created_at\\\":\\\"2026-05-18 16:28:28\\\",\\\"id\\\":6}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 13:28:28'),
(81, 1, 'updated', 'App\\Models\\Service', 3, '\"{\\\"is_active\\\":true,\\\"updated_at\\\":\\\"2026-05-14T17:33:58.000000Z\\\"}\"', '\"{\\\"is_active\\\":false,\\\"updated_at\\\":\\\"2026-05-18 16:31:49\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 13:31:49'),
(82, 1, 'updated', 'App\\Models\\Service', 3, '\"{\\\"is_active\\\":false,\\\"updated_at\\\":\\\"2026-05-18T13:31:49.000000Z\\\"}\"', '\"{\\\"is_active\\\":true,\\\"updated_at\\\":\\\"2026-05-18 16:31:50\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 13:31:50'),
(83, 1, 'created', 'App\\Models\\User', 11, NULL, '\"{\\\"uuid\\\":\\\"0688d9ee-eb10-43cf-8348-4a76908e380b\\\",\\\"role_id\\\":\\\"5\\\",\\\"first_name\\\":\\\"Deneme\\\",\\\"last_name\\\":\\\"\\\\u00c7al\\\\u0131\\\\u015fan\\\\u0131\\\",\\\"email\\\":\\\"deneme@mail.com\\\",\\\"phone\\\":\\\"05525525252\\\",\\\"password\\\":\\\"$2y$12$x1z6BHucOz4VL.DyyYrHFObTWuxE1Dtq7yIDNbfZacaTAkOO0Jx3a\\\",\\\"updated_at\\\":\\\"2026-05-18 16:36:45\\\",\\\"created_at\\\":\\\"2026-05-18 16:36:45\\\",\\\"id\\\":11}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 13:36:45'),
(84, 1, 'created', 'App\\Models\\Employee', 5, NULL, '\"{\\\"branch_id\\\":1,\\\"user_id\\\":11,\\\"employee_code\\\":\\\"EMP-QXOKCY\\\",\\\"title\\\":\\\"Berber\\\",\\\"hire_date\\\":\\\"2026-05-18 16:36:45\\\",\\\"salary_type\\\":\\\"fixed\\\",\\\"salary_amount\\\":\\\"10000\\\",\\\"commission_rate\\\":\\\"0\\\",\\\"is_active\\\":true,\\\"is_visible\\\":true,\\\"updated_at\\\":\\\"2026-05-18 16:36:45\\\",\\\"created_at\\\":\\\"2026-05-18 16:36:45\\\",\\\"id\\\":5}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 13:36:45'),
(85, 1, 'created', 'App\\Models\\Appointment', 53, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"10\\\",\\\"employee_id\\\":\\\"1\\\",\\\"start_at\\\":\\\"2026-05-18 17:30:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":\\\"yok.\\\",\\\"internal_note\\\":\\\"yok.\\\",\\\"created_by\\\":1,\\\"uuid\\\":\\\"10cb9bbe-21e6-4666-9761-c818f394cc0a\\\",\\\"appointment_code\\\":\\\"BV-W1M8VTSA\\\",\\\"end_at\\\":\\\"2026-05-18 18:00:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-18 16:47:02\\\",\\\"created_at\\\":\\\"2026-05-18 16:47:02\\\",\\\"id\\\":53}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 13:47:02'),
(86, 1, 'updated', 'App\\Models\\Appointment', 53, NULL, '\"{\\\"subtotal\\\":450,\\\"total_price\\\":450}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 13:47:02'),
(87, 1, 'updated', 'App\\Models\\Appointment', 53, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-18T13:47:02.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-18 16:47:51\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 13:47:51'),
(88, 1, 'created', 'App\\Models\\Appointment', 54, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"6\\\",\\\"employee_id\\\":\\\"1\\\",\\\"start_at\\\":\\\"2026-05-18 18:00:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"1a85a380-f552-4697-9e74-4f22a15b9854\\\",\\\"appointment_code\\\":\\\"BV-Q5RCIUMI\\\",\\\"end_at\\\":\\\"2026-05-18 18:30:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-18 16:48:23\\\",\\\"created_at\\\":\\\"2026-05-18 16:48:23\\\",\\\"id\\\":54}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 13:48:23'),
(89, 1, 'updated', 'App\\Models\\Appointment', 54, NULL, '\"{\\\"subtotal\\\":300,\\\"total_price\\\":300}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 13:48:23'),
(90, 1, 'updated', 'App\\Models\\Appointment', 54, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-18T13:48:23.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-18 16:48:28\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 13:48:28'),
(91, 1, 'created', 'App\\Models\\Appointment', 55, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"8\\\",\\\"employee_id\\\":\\\"3\\\",\\\"start_at\\\":\\\"2026-05-18 19:30:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"95223dfe-7ecb-4570-a026-1912a23997b5\\\",\\\"appointment_code\\\":\\\"BV-EQZ9ZNYW\\\",\\\"end_at\\\":\\\"2026-05-18 20:00:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-18 16:50:33\\\",\\\"created_at\\\":\\\"2026-05-18 16:50:33\\\",\\\"id\\\":55}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 13:50:33'),
(92, 1, 'updated', 'App\\Models\\Appointment', 55, NULL, '\"{\\\"subtotal\\\":300,\\\"total_price\\\":300}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 13:50:33'),
(93, 1, 'updated', 'App\\Models\\Appointment', 55, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-18T13:50:33.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-18 16:51:03\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 13:51:03'),
(94, 1, 'updated', 'App\\Models\\Service', 3, '\"{\\\"discounted_price\\\":\\\"999.00\\\",\\\"updated_at\\\":\\\"2026-05-18T13:31:50.000000Z\\\"}\"', '\"{\\\"discounted_price\\\":null,\\\"updated_at\\\":\\\"2026-05-18 17:44:51\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 14:44:51'),
(95, 1, 'updated', 'App\\Models\\Appointment', 53, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-18T13:47:51.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-18 18:02:21\\\",\\\"updated_at\\\":\\\"2026-05-18 18:02:21\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 15:02:21'),
(96, 1, 'updated', 'App\\Models\\Appointment', 54, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-18T13:48:28.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-18 18:02:44\\\",\\\"updated_at\\\":\\\"2026-05-18 18:02:44\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 15:02:44'),
(97, 1, 'created', 'App\\Models\\User', 12, NULL, '\"{\\\"uuid\\\":\\\"17366b11-e10f-4566-875d-2892a3fb7344\\\",\\\"role_id\\\":6,\\\"first_name\\\":\\\"Sinem\\\",\\\"last_name\\\":\\\"DURMAZ\\\",\\\"email\\\":\\\"sinem@mail.com\\\",\\\"phone\\\":\\\"05555555123\\\",\\\"gender\\\":\\\"female\\\",\\\"birth_date\\\":\\\"2005-01-17 00:00:00\\\",\\\"password\\\":\\\"$2y$12$J\\\\\\/Hk26PFda6oy9CAEtbo0OA9elHtZa86z3V8yO5HzkmH5m778hVz2\\\",\\\"status\\\":\\\"active\\\",\\\"updated_at\\\":\\\"2026-05-18 18:09:30\\\",\\\"created_at\\\":\\\"2026-05-18 18:09:30\\\",\\\"id\\\":12}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 15:09:30'),
(98, 1, 'updated', 'App\\Models\\Appointment', 33, '\"{\\\"status\\\":\\\"cancelled\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T22:00:44.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-18 18:14:16\\\",\\\"updated_at\\\":\\\"2026-05-18 18:14:16\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 15:14:16'),
(99, 1, 'updated', 'App\\Models\\Appointment', 31, '\"{\\\"status\\\":\\\"cancelled\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T22:00:48.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-18 18:14:24\\\",\\\"updated_at\\\":\\\"2026-05-18 18:14:24\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 15:14:24'),
(100, 1, 'updated', 'App\\Models\\Appointment', 52, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T23:04:27.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-18 18:14:39\\\",\\\"updated_at\\\":\\\"2026-05-18 18:14:39\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 15:14:39'),
(101, 1, 'updated', 'App\\Models\\Appointment', 47, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T22:34:52.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-18 18:14:48\\\",\\\"updated_at\\\":\\\"2026-05-18 18:14:48\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 15:14:48'),
(102, 1, 'updated', 'App\\Models\\Appointment', 46, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T22:34:50.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-18 18:14:55\\\",\\\"updated_at\\\":\\\"2026-05-18 18:14:55\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 15:14:55'),
(103, 1, 'updated', 'App\\Models\\Appointment', 49, '\"{\\\"status\\\":\\\"rejected\\\",\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T23:01:45.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"cancelled\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-18 18:15:09\\\",\\\"updated_at\\\":\\\"2026-05-18 18:15:09\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 15:15:09'),
(104, 1, 'updated', 'App\\Models\\Appointment', 45, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T22:34:48.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"cancelled\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-18 18:15:11\\\",\\\"updated_at\\\":\\\"2026-05-18 18:15:11\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 15:15:11'),
(105, 1, 'updated', 'App\\Models\\Appointment', 43, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T22:23:06.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"cancelled\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-18 18:15:14\\\",\\\"updated_at\\\":\\\"2026-05-18 18:15:14\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 15:15:14');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `model_type`, `model_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(106, 1, 'updated', 'App\\Models\\Appointment', 44, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T22:34:45.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"cancelled\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-18 18:15:15\\\",\\\"updated_at\\\":\\\"2026-05-18 18:15:15\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 15:15:15'),
(107, 1, 'updated', 'App\\Models\\User', 1, '\"{\\\"last_login_at\\\":\\\"2026-05-18T13:19:13.000000Z\\\",\\\"updated_at\\\":\\\"2026-05-18T13:19:13.000000Z\\\"}\"', '\"{\\\"last_login_at\\\":\\\"2026-05-18 22:52:37\\\",\\\"updated_at\\\":\\\"2026-05-18 22:52:37\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 19:52:37'),
(108, 1, 'updated', 'App\\Models\\Appointment', 53, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null,\\\"updated_at\\\":\\\"2026-05-18T15:02:21.000000Z\\\"}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"credit_card\\\",\\\"updated_at\\\":\\\"2026-05-18 23:03:49\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:03:49'),
(109, 1, 'updated', 'App\\Models\\Appointment', 54, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null,\\\"updated_at\\\":\\\"2026-05-18T15:02:44.000000Z\\\"}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\",\\\"updated_at\\\":\\\"2026-05-18 23:10:37\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:10:37'),
(110, 1, 'updated', 'App\\Models\\Appointment', 55, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-18T13:51:03.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-18 23:11:09\\\",\\\"updated_at\\\":\\\"2026-05-18 23:11:09\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:11:09'),
(111, 1, 'updated', 'App\\Models\\Appointment', 55, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null,\\\"updated_at\\\":\\\"2026-05-18T20:11:09.000000Z\\\"}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\",\\\"updated_at\\\":\\\"2026-05-18 23:11:14\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:11:14'),
(112, 1, 'updated', 'App\\Models\\Appointment', 55, '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"updated_at\\\":\\\"2026-05-18T20:11:14.000000Z\\\"}\"', '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"updated_at\\\":\\\"2026-05-18 23:11:22\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:11:22'),
(113, 1, 'updated', 'App\\Models\\Appointment', 55, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"updated_at\\\":\\\"2026-05-18T20:11:22.000000Z\\\"}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"updated_at\\\":\\\"2026-05-18 23:11:25\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:11:25'),
(114, 1, 'updated', 'App\\Models\\Service', 2, '\"{\\\"is_active\\\":true,\\\"updated_at\\\":\\\"2026-05-14T13:44:44.000000Z\\\"}\"', '\"{\\\"is_active\\\":false,\\\"updated_at\\\":\\\"2026-05-18 23:22:37\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:22:37'),
(115, 1, 'updated', 'App\\Models\\Service', 2, '\"{\\\"is_active\\\":false}\"', '\"{\\\"is_active\\\":true}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:22:37'),
(116, 1, 'updated', 'App\\Models\\Appointment', 48, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-14T22:36:03.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-18 23:23:26\\\",\\\"updated_at\\\":\\\"2026-05-18 23:23:26\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:23:26'),
(117, 1, 'updated', 'App\\Models\\Appointment', 48, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"credit_card\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:23:26'),
(118, 1, 'created', 'App\\Models\\Appointment', 56, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"6\\\",\\\"employee_id\\\":\\\"1\\\",\\\"start_at\\\":\\\"2026-05-19 10:00:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"2b39be30-cc57-4369-adc9-8a0af2914acf\\\",\\\"appointment_code\\\":\\\"BV-MWPXXPXW\\\",\\\"end_at\\\":\\\"2026-05-19 10:30:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-18 23:25:36\\\",\\\"created_at\\\":\\\"2026-05-18 23:25:36\\\",\\\"id\\\":56}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:25:36'),
(119, 1, 'updated', 'App\\Models\\Appointment', 56, NULL, '\"{\\\"subtotal\\\":450,\\\"total_price\\\":450}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:25:36'),
(120, 1, 'created', 'App\\Models\\Appointment', 57, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"7\\\",\\\"employee_id\\\":\\\"2\\\",\\\"start_at\\\":\\\"2026-05-19 10:00:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"d50a05a7-1d21-4fe3-ae4b-bd7baa691d21\\\",\\\"appointment_code\\\":\\\"BV-8F4MO8ST\\\",\\\"end_at\\\":\\\"2026-05-19 10:30:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-18 23:25:53\\\",\\\"created_at\\\":\\\"2026-05-18 23:25:53\\\",\\\"id\\\":57}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:25:53'),
(121, 1, 'updated', 'App\\Models\\Appointment', 57, NULL, '\"{\\\"subtotal\\\":450,\\\"total_price\\\":450}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:25:53'),
(122, 1, 'created', 'App\\Models\\Appointment', 58, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"8\\\",\\\"employee_id\\\":\\\"3\\\",\\\"start_at\\\":\\\"2026-05-19 10:30:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"acfd5892-007c-4472-9cc3-5a96aac77eb4\\\",\\\"appointment_code\\\":\\\"BV-0ZSZAVWO\\\",\\\"end_at\\\":\\\"2026-05-19 11:30:00\\\",\\\"total_duration\\\":60,\\\"updated_at\\\":\\\"2026-05-18 23:26:17\\\",\\\"created_at\\\":\\\"2026-05-18 23:26:17\\\",\\\"id\\\":58}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:26:17'),
(123, 1, 'updated', 'App\\Models\\Appointment', 58, NULL, '\"{\\\"subtotal\\\":750,\\\"total_price\\\":750}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:26:17'),
(124, 1, 'created', 'App\\Models\\Appointment', 59, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"9\\\",\\\"employee_id\\\":\\\"5\\\",\\\"start_at\\\":\\\"2026-05-19 12:00:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"dc27c6eb-2566-4bcd-b964-bf96163193f8\\\",\\\"appointment_code\\\":\\\"BV-SUFTOOWO\\\",\\\"end_at\\\":\\\"2026-05-19 12:30:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-18 23:26:35\\\",\\\"created_at\\\":\\\"2026-05-18 23:26:35\\\",\\\"id\\\":59}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:26:35'),
(125, 1, 'updated', 'App\\Models\\Appointment', 59, NULL, '\"{\\\"subtotal\\\":450,\\\"total_price\\\":450}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:26:35'),
(126, 1, 'created', 'App\\Models\\Appointment', 60, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"10\\\",\\\"employee_id\\\":\\\"1\\\",\\\"start_at\\\":\\\"2026-05-19 10:30:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"af4f7f75-d880-4a64-b983-a6dff191d8cc\\\",\\\"appointment_code\\\":\\\"BV-4VAF0O2R\\\",\\\"end_at\\\":\\\"2026-05-19 12:00:00\\\",\\\"total_duration\\\":90,\\\"updated_at\\\":\\\"2026-05-18 23:26:54\\\",\\\"created_at\\\":\\\"2026-05-18 23:26:54\\\",\\\"id\\\":60}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:26:54'),
(127, 1, 'updated', 'App\\Models\\Appointment', 60, NULL, '\"{\\\"subtotal\\\":1200,\\\"total_price\\\":1200}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:26:54'),
(128, 1, 'created', 'App\\Models\\Appointment', 61, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"12\\\",\\\"employee_id\\\":\\\"5\\\",\\\"start_at\\\":\\\"2026-05-19 12:30:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"b37c6da3-add3-437b-b469-da55067e983c\\\",\\\"appointment_code\\\":\\\"BV-XCT3C2DR\\\",\\\"end_at\\\":\\\"2026-05-19 13:00:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-18 23:27:13\\\",\\\"created_at\\\":\\\"2026-05-18 23:27:13\\\",\\\"id\\\":61}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:27:13'),
(129, 1, 'updated', 'App\\Models\\Appointment', 61, NULL, '\"{\\\"subtotal\\\":250,\\\"total_price\\\":250}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:27:13'),
(130, 1, 'created', 'App\\Models\\Appointment', 62, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"6\\\",\\\"employee_id\\\":\\\"5\\\",\\\"start_at\\\":\\\"2026-05-19 08:00:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"970eb490-2680-4de4-b0ce-52e2a4bef2ef\\\",\\\"appointment_code\\\":\\\"BV-MGISHUK0\\\",\\\"end_at\\\":\\\"2026-05-19 08:30:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-18 23:27:35\\\",\\\"created_at\\\":\\\"2026-05-18 23:27:35\\\",\\\"id\\\":62}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:27:35'),
(131, 1, 'updated', 'App\\Models\\Appointment', 62, NULL, '\"{\\\"subtotal\\\":350,\\\"total_price\\\":350}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:27:35'),
(132, 1, 'created', 'App\\Models\\Appointment', 63, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"7\\\",\\\"employee_id\\\":\\\"5\\\",\\\"start_at\\\":\\\"2026-05-19 08:30:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"fe905b73-4454-44f9-9d2f-2395cf2255b9\\\",\\\"appointment_code\\\":\\\"BV-YTLF8FBY\\\",\\\"end_at\\\":\\\"2026-05-19 09:00:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-18 23:27:46\\\",\\\"created_at\\\":\\\"2026-05-18 23:27:46\\\",\\\"id\\\":63}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:27:46'),
(133, 1, 'updated', 'App\\Models\\Appointment', 63, NULL, '\"{\\\"subtotal\\\":300,\\\"total_price\\\":300}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:27:46'),
(134, 1, 'created', 'App\\Models\\Appointment', 64, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"8\\\",\\\"employee_id\\\":\\\"5\\\",\\\"start_at\\\":\\\"2026-05-19 09:00:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"b571da17-c9ad-4091-9b09-0ed70022cbc5\\\",\\\"appointment_code\\\":\\\"BV-ZYVOVHSW\\\",\\\"end_at\\\":\\\"2026-05-19 09:40:00\\\",\\\"total_duration\\\":40,\\\"updated_at\\\":\\\"2026-05-18 23:28:02\\\",\\\"created_at\\\":\\\"2026-05-18 23:28:02\\\",\\\"id\\\":64}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:28:02'),
(135, 1, 'updated', 'App\\Models\\Appointment', 64, NULL, '\"{\\\"subtotal\\\":600,\\\"total_price\\\":600}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:28:02'),
(136, 1, 'created', 'App\\Models\\Appointment', 65, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"9\\\",\\\"employee_id\\\":\\\"5\\\",\\\"start_at\\\":\\\"2026-05-19 10:00:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"82aecb0a-36c2-46c2-bfca-c44755675b3d\\\",\\\"appointment_code\\\":\\\"BV-S8EXQGU0\\\",\\\"end_at\\\":\\\"2026-05-19 11:30:00\\\",\\\"total_duration\\\":90,\\\"updated_at\\\":\\\"2026-05-18 23:28:17\\\",\\\"created_at\\\":\\\"2026-05-18 23:28:17\\\",\\\"id\\\":65}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:28:17'),
(137, 1, 'updated', 'App\\Models\\Appointment', 65, NULL, '\"{\\\"subtotal\\\":1200,\\\"total_price\\\":1200}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:28:17'),
(138, 1, 'created', 'App\\Models\\Appointment', 66, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"10\\\",\\\"employee_id\\\":\\\"5\\\",\\\"start_at\\\":\\\"2026-05-19 11:30:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"c64c2bd5-2759-45f9-898a-95b643ad2e4d\\\",\\\"appointment_code\\\":\\\"BV-VQFW3IZ3\\\",\\\"end_at\\\":\\\"2026-05-19 13:00:00\\\",\\\"total_duration\\\":90,\\\"updated_at\\\":\\\"2026-05-18 23:28:34\\\",\\\"created_at\\\":\\\"2026-05-18 23:28:34\\\",\\\"id\\\":66}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:28:34'),
(139, 1, 'updated', 'App\\Models\\Appointment', 66, NULL, '\"{\\\"subtotal\\\":1200,\\\"total_price\\\":1200}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:28:34'),
(140, 1, 'created', 'App\\Models\\Appointment', 67, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"12\\\",\\\"employee_id\\\":\\\"5\\\",\\\"start_at\\\":\\\"2026-05-19 13:00:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"54d212b7-7ab7-40de-b7c4-af9f852acb31\\\",\\\"appointment_code\\\":\\\"BV-LXOLM9SV\\\",\\\"end_at\\\":\\\"2026-05-19 13:30:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-18 23:29:07\\\",\\\"created_at\\\":\\\"2026-05-18 23:29:07\\\",\\\"id\\\":67}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:29:07'),
(141, 1, 'updated', 'App\\Models\\Appointment', 67, NULL, '\"{\\\"subtotal\\\":250,\\\"total_price\\\":250}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:29:07'),
(142, 1, 'created', 'App\\Models\\Appointment', 68, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"6\\\",\\\"employee_id\\\":\\\"1\\\",\\\"start_at\\\":\\\"2026-05-19 08:00:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"c7d551fb-25f2-47fa-96c9-a837246e901a\\\",\\\"appointment_code\\\":\\\"BV-6EJB0ONG\\\",\\\"end_at\\\":\\\"2026-05-19 08:30:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-18 23:29:21\\\",\\\"created_at\\\":\\\"2026-05-18 23:29:21\\\",\\\"id\\\":68}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:29:21'),
(143, 1, 'updated', 'App\\Models\\Appointment', 68, NULL, '\"{\\\"subtotal\\\":300,\\\"total_price\\\":300}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:29:21'),
(144, 1, 'created', 'App\\Models\\Appointment', 69, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"7\\\",\\\"employee_id\\\":\\\"1\\\",\\\"start_at\\\":\\\"2026-05-19 08:30:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"83b87ab5-adc2-4482-96f6-42f9aa6a3abc\\\",\\\"appointment_code\\\":\\\"BV-LPUCFVYV\\\",\\\"end_at\\\":\\\"2026-05-19 10:00:00\\\",\\\"total_duration\\\":90,\\\"updated_at\\\":\\\"2026-05-18 23:29:35\\\",\\\"created_at\\\":\\\"2026-05-18 23:29:35\\\",\\\"id\\\":69}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:29:35'),
(145, 1, 'updated', 'App\\Models\\Appointment', 69, NULL, '\"{\\\"subtotal\\\":1200,\\\"total_price\\\":1200}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:29:35'),
(146, 1, 'created', 'App\\Models\\Appointment', 70, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"8\\\",\\\"employee_id\\\":\\\"1\\\",\\\"start_at\\\":\\\"2026-05-19 12:00:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"1e7d49a3-905d-41d8-be41-76ce6ea934e4\\\",\\\"appointment_code\\\":\\\"BV-2NC42NC1\\\",\\\"end_at\\\":\\\"2026-05-19 12:30:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-18 23:30:15\\\",\\\"created_at\\\":\\\"2026-05-18 23:30:15\\\",\\\"id\\\":70}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:30:15'),
(147, 1, 'updated', 'App\\Models\\Appointment', 70, NULL, '\"{\\\"subtotal\\\":250,\\\"total_price\\\":250}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:30:15'),
(148, 1, 'created', 'App\\Models\\Appointment', 71, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"10\\\",\\\"employee_id\\\":\\\"1\\\",\\\"start_at\\\":\\\"2026-05-19 12:30:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"e2968513-763f-4266-997e-a7078e405a26\\\",\\\"appointment_code\\\":\\\"BV-EJ7WTTMI\\\",\\\"end_at\\\":\\\"2026-05-19 13:00:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-18 23:30:27\\\",\\\"created_at\\\":\\\"2026-05-18 23:30:27\\\",\\\"id\\\":71}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:30:27'),
(149, 1, 'updated', 'App\\Models\\Appointment', 71, NULL, '\"{\\\"subtotal\\\":450,\\\"total_price\\\":450}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:30:27'),
(150, 1, 'updated', 'App\\Models\\Appointment', 62, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-18T20:27:35.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-18 23:31:23\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:31:23'),
(151, 1, 'updated', 'App\\Models\\Appointment', 68, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-18T20:29:21.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-18 23:31:25\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:31:25'),
(152, 1, 'updated', 'App\\Models\\Appointment', 63, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-18T20:27:46.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-18 23:31:26\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:31:26'),
(153, 1, 'updated', 'App\\Models\\Appointment', 69, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-18T20:29:35.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-18 23:31:26\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:31:26'),
(154, 1, 'updated', 'App\\Models\\Appointment', 64, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-18T20:28:02.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-18 23:31:27\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:31:27'),
(155, 1, 'updated', 'App\\Models\\Appointment', 56, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-18T20:25:36.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-18 23:31:28\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:31:28'),
(156, 1, 'updated', 'App\\Models\\Appointment', 57, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-18T20:25:53.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-18 23:31:28\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:31:28'),
(157, 1, 'updated', 'App\\Models\\Appointment', 65, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-18T20:28:17.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-18 23:31:28\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:31:28'),
(158, 1, 'updated', 'App\\Models\\Appointment', 58, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-18T20:26:17.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-18 23:31:29\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:31:29'),
(159, 1, 'updated', 'App\\Models\\Appointment', 60, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-18T20:26:54.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-18 23:31:29\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:31:29'),
(160, 1, 'updated', 'App\\Models\\Appointment', 66, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-18T20:28:34.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-18 23:31:29\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:31:29'),
(161, 1, 'updated', 'App\\Models\\Appointment', 59, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-18T20:26:35.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-18 23:31:29\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:31:29'),
(162, 1, 'updated', 'App\\Models\\Appointment', 70, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-18T20:30:15.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-18 23:31:30\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:31:30'),
(163, 1, 'updated', 'App\\Models\\Appointment', 61, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-18T20:27:13.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-18 23:31:30\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:31:30'),
(164, 1, 'updated', 'App\\Models\\Appointment', 71, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-18T20:30:27.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-18 23:31:30\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:31:30'),
(165, 1, 'updated', 'App\\Models\\Appointment', 67, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-18T20:29:07.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-18 23:31:30\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:31:30'),
(166, 1, 'created', 'App\\Models\\Appointment', 72, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"10\\\",\\\"employee_id\\\":\\\"3\\\",\\\"start_at\\\":\\\"2026-05-19 14:30:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"321a030e-a782-4098-817b-eea86fcaaee9\\\",\\\"appointment_code\\\":\\\"BV-MFVEGME9\\\",\\\"end_at\\\":\\\"2026-05-19 15:10:00\\\",\\\"total_duration\\\":40,\\\"updated_at\\\":\\\"2026-05-18 23:38:38\\\",\\\"created_at\\\":\\\"2026-05-18 23:38:38\\\",\\\"id\\\":72}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:38:38'),
(167, 1, 'updated', 'App\\Models\\Appointment', 72, NULL, '\"{\\\"subtotal\\\":600,\\\"total_price\\\":600}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:38:38'),
(168, 1, 'updated', 'App\\Models\\User', 1, '\"{\\\"last_login_at\\\":\\\"2026-05-18T19:52:37.000000Z\\\",\\\"updated_at\\\":\\\"2026-05-18T19:52:37.000000Z\\\"}\"', '\"{\\\"last_login_at\\\":\\\"2026-05-18 23:53:28\\\",\\\"updated_at\\\":\\\"2026-05-18 23:53:28\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:53:28'),
(169, 1, 'updated', 'App\\Models\\Appointment', 72, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-18T20:38:38.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-18 23:57:04\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 20:57:04'),
(170, 1, 'updated', 'App\\Models\\Appointment', 62, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-18T20:31:23.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"cancelled\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-19 01:23:28\\\",\\\"updated_at\\\":\\\"2026-05-19 01:23:28\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 22:23:28'),
(171, 1, 'updated', 'App\\Models\\Appointment', 68, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-18T20:31:25.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-19 01:23:43\\\",\\\"updated_at\\\":\\\"2026-05-19 01:23:43\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 22:23:43'),
(172, 1, 'updated', 'App\\Models\\Appointment', 62, '\"{\\\"status\\\":\\\"cancelled\\\",\\\"updated_at\\\":\\\"2026-05-18T22:23:28.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-19 01:26:28\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 22:26:28'),
(173, 1, 'updated', 'App\\Models\\Appointment', 68, '\"{\\\"status\\\":\\\"completed\\\",\\\"updated_at\\\":\\\"2026-05-18T22:23:43.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-19 01:27:05\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 22:27:05'),
(174, 1, 'created', 'App\\Models\\Appointment', 73, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"10\\\",\\\"employee_id\\\":\\\"3\\\",\\\"start_at\\\":\\\"2026-05-20 21:00:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"f9e7ab77-0202-4aca-bffa-51efbf012928\\\",\\\"appointment_code\\\":\\\"BV-ICYB5NSS\\\",\\\"end_at\\\":\\\"2026-05-20 21:30:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-19 01:27:43\\\",\\\"created_at\\\":\\\"2026-05-19 01:27:43\\\",\\\"id\\\":73}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 22:27:43'),
(175, 1, 'updated', 'App\\Models\\Appointment', 73, NULL, '\"{\\\"subtotal\\\":450,\\\"total_price\\\":450}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 22:27:43'),
(176, 1, 'updated', 'App\\Models\\Appointment', 73, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-18T22:27:43.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-19 01:35:43\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 22:35:43'),
(177, 1, 'updated', 'App\\Models\\User', 1, '\"{\\\"last_login_at\\\":\\\"2026-05-18T20:53:28.000000Z\\\",\\\"updated_at\\\":\\\"2026-05-18T20:53:28.000000Z\\\"}\"', '\"{\\\"last_login_at\\\":\\\"2026-05-19 15:18:31\\\",\\\"updated_at\\\":\\\"2026-05-19 15:18:31\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:18:31'),
(178, 1, 'created', 'App\\Models\\Appointment', 74, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"12\\\",\\\"employee_id\\\":\\\"5\\\",\\\"start_at\\\":\\\"2026-05-19 15:30:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"ee90d462-50d3-4987-af6e-a794f930ec65\\\",\\\"appointment_code\\\":\\\"BV-U8WZUDHA\\\",\\\"end_at\\\":\\\"2026-05-19 16:00:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-19 15:19:58\\\",\\\"created_at\\\":\\\"2026-05-19 15:19:58\\\",\\\"id\\\":74}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:19:58'),
(179, 1, 'updated', 'App\\Models\\Appointment', 74, NULL, '\"{\\\"subtotal\\\":250,\\\"total_price\\\":250}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:19:58'),
(180, 1, 'updated', 'App\\Models\\Appointment', 74, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-19T12:19:58.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-19 15:20:18\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:20:18'),
(181, 1, 'updated', 'App\\Models\\Appointment', 72, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-18T20:57:04.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-19 15:20:33\\\",\\\"updated_at\\\":\\\"2026-05-19 15:20:33\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:20:33'),
(182, 1, 'updated', 'App\\Models\\Appointment', 72, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:20:33'),
(183, 1, 'updated', 'App\\Models\\Appointment', 67, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-18T20:31:30.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-19 15:20:36\\\",\\\"updated_at\\\":\\\"2026-05-19 15:20:36\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:20:36'),
(184, 1, 'updated', 'App\\Models\\Appointment', 67, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:20:36'),
(185, 1, 'updated', 'App\\Models\\Appointment', 61, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-18T20:31:30.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-19 15:20:41\\\",\\\"updated_at\\\":\\\"2026-05-19 15:20:41\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:20:41'),
(186, 1, 'updated', 'App\\Models\\Appointment', 61, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:20:41'),
(187, 1, 'updated', 'App\\Models\\Appointment', 71, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-18T20:31:30.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-19 15:20:45\\\",\\\"updated_at\\\":\\\"2026-05-19 15:20:45\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:20:45'),
(188, 1, 'updated', 'App\\Models\\Appointment', 71, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:20:45'),
(189, 1, 'updated', 'App\\Models\\Appointment', 59, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"no_show\\\":false,\\\"updated_at\\\":\\\"2026-05-18T20:31:29.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"no_show\\\",\\\"no_show\\\":true,\\\"updated_at\\\":\\\"2026-05-19 15:20:57\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:20:57'),
(190, 1, 'updated', 'App\\Models\\Appointment', 70, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-18T20:31:30.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-19 15:21:01\\\",\\\"updated_at\\\":\\\"2026-05-19 15:21:01\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:21:01'),
(191, 1, 'updated', 'App\\Models\\Appointment', 70, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:21:01'),
(192, 1, 'updated', 'App\\Models\\Appointment', 66, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-18T20:31:29.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-19 15:21:04\\\",\\\"updated_at\\\":\\\"2026-05-19 15:21:04\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:21:04'),
(193, 1, 'updated', 'App\\Models\\Appointment', 66, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:21:04'),
(194, 1, 'updated', 'App\\Models\\Appointment', 58, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"no_show\\\":false,\\\"updated_at\\\":\\\"2026-05-18T20:31:29.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"no_show\\\",\\\"no_show\\\":true,\\\"updated_at\\\":\\\"2026-05-19 15:21:08\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:21:08'),
(195, 1, 'updated', 'App\\Models\\Appointment', 60, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-18T20:31:29.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-19 15:21:14\\\",\\\"updated_at\\\":\\\"2026-05-19 15:21:14\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:21:14'),
(196, 1, 'updated', 'App\\Models\\Appointment', 60, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:21:14'),
(197, 1, 'updated', 'App\\Models\\Appointment', 56, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-18T20:31:28.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-19 15:21:17\\\",\\\"updated_at\\\":\\\"2026-05-19 15:21:17\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:21:17'),
(198, 1, 'updated', 'App\\Models\\Appointment', 56, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:21:17'),
(199, 1, 'updated', 'App\\Models\\Appointment', 57, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-18T20:31:28.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-19 15:21:21\\\",\\\"updated_at\\\":\\\"2026-05-19 15:21:21\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:21:21'),
(200, 1, 'updated', 'App\\Models\\Appointment', 57, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:21:21'),
(201, 1, 'updated', 'App\\Models\\Appointment', 65, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-18T20:31:28.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-19 15:21:23\\\",\\\"updated_at\\\":\\\"2026-05-19 15:21:23\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:21:23'),
(202, 1, 'updated', 'App\\Models\\Appointment', 65, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:21:23'),
(203, 1, 'updated', 'App\\Models\\Appointment', 64, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-18T20:31:27.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-19 15:21:26\\\",\\\"updated_at\\\":\\\"2026-05-19 15:21:26\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:21:26'),
(204, 1, 'updated', 'App\\Models\\Appointment', 64, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:21:26'),
(205, 1, 'updated', 'App\\Models\\Appointment', 63, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-18T20:31:26.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-19 15:21:30\\\",\\\"updated_at\\\":\\\"2026-05-19 15:21:30\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:21:30'),
(206, 1, 'updated', 'App\\Models\\Appointment', 63, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:21:30'),
(207, 1, 'updated', 'App\\Models\\Appointment', 69, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-18T20:31:26.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-19 15:21:36\\\",\\\"updated_at\\\":\\\"2026-05-19 15:21:36\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:21:36'),
(208, 1, 'updated', 'App\\Models\\Appointment', 69, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:21:36'),
(209, 1, 'updated', 'App\\Models\\Appointment', 62, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-18T22:26:28.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-19 15:21:40\\\",\\\"updated_at\\\":\\\"2026-05-19 15:21:40\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:21:40'),
(210, 1, 'updated', 'App\\Models\\Appointment', 62, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:21:40'),
(211, 1, 'updated', 'App\\Models\\Appointment', 68, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":\\\"2026-05-18T22:23:43.000000Z\\\",\\\"updated_at\\\":\\\"2026-05-18T22:27:05.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-19 15:21:43\\\",\\\"updated_at\\\":\\\"2026-05-19 15:21:43\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:21:43'),
(212, 1, 'updated', 'App\\Models\\Appointment', 68, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:21:43'),
(213, 1, 'created', 'App\\Models\\Appointment', 75, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"7\\\",\\\"employee_id\\\":\\\"2\\\",\\\"start_at\\\":\\\"2026-05-19 16:00:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"2f04815f-4382-4df4-80fc-8ab56156248b\\\",\\\"appointment_code\\\":\\\"BV-LDUWUYBR\\\",\\\"end_at\\\":\\\"2026-05-19 16:40:00\\\",\\\"total_duration\\\":40,\\\"updated_at\\\":\\\"2026-05-19 15:45:19\\\",\\\"created_at\\\":\\\"2026-05-19 15:45:19\\\",\\\"id\\\":75}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:45:19'),
(214, 1, 'updated', 'App\\Models\\Appointment', 75, NULL, '\"{\\\"subtotal\\\":600,\\\"total_price\\\":600}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:45:19'),
(215, 1, 'updated', 'App\\Models\\Appointment', 75, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-19T12:45:19.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"rejected\\\",\\\"updated_at\\\":\\\"2026-05-19 15:45:25\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:45:25'),
(216, 1, 'updated', 'App\\Models\\User', 1, '\"{\\\"last_login_at\\\":\\\"2026-05-19T12:18:31.000000Z\\\",\\\"updated_at\\\":\\\"2026-05-19T12:18:31.000000Z\\\"}\"', '\"{\\\"last_login_at\\\":\\\"2026-05-19 15:58:18\\\",\\\"updated_at\\\":\\\"2026-05-19 15:58:18\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:58:18');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `model_type`, `model_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(217, 1, 'updated', 'App\\Models\\User', 1, '\"{\\\"last_login_at\\\":\\\"2026-05-19T12:58:18.000000Z\\\",\\\"updated_at\\\":\\\"2026-05-19T12:58:18.000000Z\\\"}\"', '\"{\\\"last_login_at\\\":\\\"2026-05-19 15:59:45\\\",\\\"updated_at\\\":\\\"2026-05-19 15:59:45\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 12:59:45'),
(218, 1, 'updated', 'App\\Models\\User', 1, '\"{\\\"last_login_at\\\":\\\"2026-05-19T12:59:45.000000Z\\\",\\\"updated_at\\\":\\\"2026-05-19T12:59:45.000000Z\\\"}\"', '\"{\\\"last_login_at\\\":\\\"2026-05-19 16:00:05\\\",\\\"updated_at\\\":\\\"2026-05-19 16:00:05\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 13:00:05'),
(219, 1, 'updated', 'App\\Models\\User', 1, '\"{\\\"last_login_at\\\":\\\"2026-05-19T13:00:05.000000Z\\\",\\\"updated_at\\\":\\\"2026-05-19T13:00:05.000000Z\\\"}\"', '\"{\\\"last_login_at\\\":\\\"2026-05-19 16:02:26\\\",\\\"updated_at\\\":\\\"2026-05-19 16:02:26\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 13:02:26'),
(220, 1, 'updated', 'App\\Models\\Appointment', 74, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-19T12:20:18.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-19 16:05:14\\\",\\\"updated_at\\\":\\\"2026-05-19 16:05:14\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 13:05:14'),
(221, 1, 'updated', 'App\\Models\\Appointment', 74, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 13:05:14'),
(222, 1, 'created', 'App\\Models\\Appointment', 76, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"6\\\",\\\"employee_id\\\":\\\"3\\\",\\\"start_at\\\":\\\"2026-05-19 16:30:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"0dec307a-e2b6-4b90-926d-9029f7ca3450\\\",\\\"appointment_code\\\":\\\"BV-PWZ2PLCG\\\",\\\"end_at\\\":\\\"2026-05-19 17:00:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-19 16:06:28\\\",\\\"created_at\\\":\\\"2026-05-19 16:06:28\\\",\\\"id\\\":76}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 13:06:28'),
(223, 1, 'updated', 'App\\Models\\Appointment', 76, NULL, '\"{\\\"subtotal\\\":450,\\\"total_price\\\":450}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 13:06:28'),
(224, 1, 'created', 'App\\Models\\Appointment', 77, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"8\\\",\\\"employee_id\\\":\\\"1\\\",\\\"start_at\\\":\\\"2026-05-19 21:30:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"6859d022-574d-4a77-9c2c-2d9e79d1575f\\\",\\\"appointment_code\\\":\\\"BV-IOPBFN6K\\\",\\\"end_at\\\":\\\"2026-05-19 22:00:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-19 16:07:19\\\",\\\"created_at\\\":\\\"2026-05-19 16:07:19\\\",\\\"id\\\":77}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 13:07:19'),
(225, 1, 'updated', 'App\\Models\\Appointment', 77, NULL, '\"{\\\"subtotal\\\":450,\\\"total_price\\\":450}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 13:07:19'),
(226, 1, 'updated', 'App\\Models\\Appointment', 77, '\"{\\\"status\\\":\\\"pending\\\",\\\"cancellation_reason\\\":null,\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-19T13:07:19.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"rejected\\\",\\\"cancellation_reason\\\":\\\"ahmet bey m\\\\u00fcsait de\\\\u011fil.\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-19 16:07:45\\\",\\\"updated_at\\\":\\\"2026-05-19 16:07:45\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 13:07:45'),
(227, 1, 'updated', 'App\\Models\\User', 1, '\"{\\\"last_login_at\\\":\\\"2026-05-19T13:02:26.000000Z\\\",\\\"updated_at\\\":\\\"2026-05-19T13:02:26.000000Z\\\"}\"', '\"{\\\"last_login_at\\\":\\\"2026-05-19 16:11:12\\\",\\\"updated_at\\\":\\\"2026-05-19 16:11:12\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.4 Safari/605.1.15', '2026-05-19 13:11:12'),
(228, 1, 'updated', 'App\\Models\\Appointment', 76, '\"{\\\"status\\\":\\\"pending\\\",\\\"cancellation_reason\\\":null,\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-19T13:06:28.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"rejected\\\",\\\"cancellation_reason\\\":\\\"M\\\\u00fc\\\\u015fteri talebi \\\\u00fczerine randevu reddedildi.\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-19 16:20:44\\\",\\\"updated_at\\\":\\\"2026-05-19 16:20:44\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 13:20:44'),
(229, 1, 'updated', 'App\\Models\\User', 1, '\"{\\\"last_login_at\\\":\\\"2026-05-19T13:11:12.000000Z\\\",\\\"updated_at\\\":\\\"2026-05-19T13:11:12.000000Z\\\"}\"', '\"{\\\"last_login_at\\\":\\\"2026-05-19 16:46:29\\\",\\\"updated_at\\\":\\\"2026-05-19 16:46:29\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 13:46:29'),
(230, 1, 'updated', 'App\\Models\\Service', 3, '\"{\\\"is_active\\\":true,\\\"updated_at\\\":\\\"2026-05-18T14:44:51.000000Z\\\"}\"', '\"{\\\"is_active\\\":false,\\\"updated_at\\\":\\\"2026-05-19 17:00:35\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 14:00:35'),
(231, 1, 'updated', 'App\\Models\\Service', 2, '\"{\\\"is_popular\\\":true,\\\"updated_at\\\":\\\"2026-05-18T20:22:37.000000Z\\\"}\"', '\"{\\\"is_popular\\\":false,\\\"updated_at\\\":\\\"2026-05-19 17:00:41\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 14:00:41'),
(232, 1, 'created', 'App\\Models\\Appointment', 78, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"6\\\",\\\"employee_id\\\":\\\"1\\\",\\\"start_at\\\":\\\"2026-05-19 17:30:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"0cc2ffbb-c4ec-45ab-b184-e0a7309cf182\\\",\\\"appointment_code\\\":\\\"BV-JOTZC65O\\\",\\\"end_at\\\":\\\"2026-05-19 18:00:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-19 17:01:29\\\",\\\"created_at\\\":\\\"2026-05-19 17:01:29\\\",\\\"id\\\":78}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 14:01:29'),
(233, 1, 'updated', 'App\\Models\\Appointment', 78, NULL, '\"{\\\"subtotal\\\":450,\\\"total_price\\\":450}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 14:01:29'),
(234, 1, 'created', 'App\\Models\\Appointment', 79, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"7\\\",\\\"employee_id\\\":\\\"2\\\",\\\"start_at\\\":\\\"2026-05-19 17:30:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"82f5a709-fa21-43a5-9873-d16cc69f668f\\\",\\\"appointment_code\\\":\\\"BV-K6VIKAAY\\\",\\\"end_at\\\":\\\"2026-05-19 18:00:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-19 17:01:41\\\",\\\"created_at\\\":\\\"2026-05-19 17:01:41\\\",\\\"id\\\":79}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 14:01:41'),
(235, 1, 'updated', 'App\\Models\\Appointment', 79, NULL, '\"{\\\"subtotal\\\":450,\\\"total_price\\\":450}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 14:01:41'),
(236, 1, 'created', 'App\\Models\\Appointment', 80, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"9\\\",\\\"employee_id\\\":\\\"3\\\",\\\"start_at\\\":\\\"2026-05-19 17:30:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"4b1ac0d7-c33c-48b0-b034-02c81f22044a\\\",\\\"appointment_code\\\":\\\"BV-TT3BJVTZ\\\",\\\"end_at\\\":\\\"2026-05-19 18:00:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-19 17:01:54\\\",\\\"created_at\\\":\\\"2026-05-19 17:01:54\\\",\\\"id\\\":80}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 14:01:54'),
(237, 1, 'updated', 'App\\Models\\Appointment', 80, NULL, '\"{\\\"subtotal\\\":250,\\\"total_price\\\":250}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 14:01:54'),
(238, 1, 'created', 'App\\Models\\Appointment', 81, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"10\\\",\\\"employee_id\\\":\\\"5\\\",\\\"start_at\\\":\\\"2026-05-19 18:00:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"28b95e31-074d-4d53-87b7-e6fbb34a5347\\\",\\\"appointment_code\\\":\\\"BV-MRKPXT3V\\\",\\\"end_at\\\":\\\"2026-05-19 18:30:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-19 17:02:07\\\",\\\"created_at\\\":\\\"2026-05-19 17:02:07\\\",\\\"id\\\":81}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 14:02:07'),
(239, 1, 'updated', 'App\\Models\\Appointment', 81, NULL, '\"{\\\"subtotal\\\":450,\\\"total_price\\\":450}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 14:02:07'),
(240, 1, 'created', 'App\\Models\\Appointment', 82, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"12\\\",\\\"employee_id\\\":\\\"5\\\",\\\"start_at\\\":\\\"2026-05-19 18:30:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"ee70cb1e-02c8-4213-a982-730b0b199af1\\\",\\\"appointment_code\\\":\\\"BV-P63QWF3C\\\",\\\"end_at\\\":\\\"2026-05-19 19:00:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-19 17:02:20\\\",\\\"created_at\\\":\\\"2026-05-19 17:02:20\\\",\\\"id\\\":82}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 14:02:20'),
(241, 1, 'updated', 'App\\Models\\Appointment', 82, NULL, '\"{\\\"subtotal\\\":350,\\\"total_price\\\":350}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 14:02:20'),
(242, 1, 'created', 'App\\Models\\Appointment', 83, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"8\\\",\\\"employee_id\\\":\\\"1\\\",\\\"start_at\\\":\\\"2026-05-19 18:00:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"2036e9de-9021-420d-8fa9-b419f2ec2849\\\",\\\"appointment_code\\\":\\\"BV-WCVQVBGY\\\",\\\"end_at\\\":\\\"2026-05-19 18:40:00\\\",\\\"total_duration\\\":40,\\\"updated_at\\\":\\\"2026-05-19 17:02:34\\\",\\\"created_at\\\":\\\"2026-05-19 17:02:34\\\",\\\"id\\\":83}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 14:02:34'),
(243, 1, 'updated', 'App\\Models\\Appointment', 83, NULL, '\"{\\\"subtotal\\\":600,\\\"total_price\\\":600}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 14:02:34'),
(244, 1, 'updated', 'App\\Models\\Appointment', 78, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-19T14:01:29.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-19 17:02:44\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 14:02:44'),
(245, 1, 'updated', 'App\\Models\\User', 1, '\"{\\\"last_login_at\\\":\\\"2026-05-19T13:46:29.000000Z\\\",\\\"updated_at\\\":\\\"2026-05-19T13:46:29.000000Z\\\"}\"', '\"{\\\"last_login_at\\\":\\\"2026-05-19 17:11:31\\\",\\\"updated_at\\\":\\\"2026-05-19 17:11:31\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 14:11:31'),
(246, 1, 'updated', 'App\\Models\\Appointment', 79, '\"{\\\"status\\\":\\\"pending\\\",\\\"cancellation_reason\\\":null,\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-19T14:01:41.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"rejected\\\",\\\"cancellation_reason\\\":\\\"d\\\\u00fckkan denetimi olacak\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-19 17:25:32\\\",\\\"updated_at\\\":\\\"2026-05-19 17:25:32\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 14:25:32'),
(247, 1, 'updated', 'App\\Models\\Appointment', 81, '\"{\\\"status\\\":\\\"pending\\\",\\\"cancellation_reason\\\":null,\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-19T14:02:07.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"rejected\\\",\\\"cancellation_reason\\\":\\\"d\\\\u00fckkan denetimi olacak\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-19 17:35:58\\\",\\\"updated_at\\\":\\\"2026-05-19 17:35:58\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 14:35:58'),
(248, 1, 'updated', 'App\\Models\\Appointment', 83, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-19T14:02:34.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-19 17:36:33\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 14:36:33'),
(249, 1, 'updated', 'App\\Models\\Appointment', 82, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-19T14:02:20.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-19 17:36:39\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 14:36:39'),
(250, 1, 'updated', 'App\\Models\\Appointment', 78, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-19T14:02:44.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-19 17:36:56\\\",\\\"updated_at\\\":\\\"2026-05-19 17:36:56\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 14:36:56'),
(251, 1, 'updated', 'App\\Models\\Appointment', 78, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 14:36:56'),
(252, 1, 'updated', 'App\\Models\\User', 1, '\"{\\\"last_login_at\\\":\\\"2026-05-19T14:11:31.000000Z\\\",\\\"updated_at\\\":\\\"2026-05-19T14:11:31.000000Z\\\"}\"', '\"{\\\"last_login_at\\\":\\\"2026-05-19 23:00:56\\\",\\\"updated_at\\\":\\\"2026-05-19 23:00:56\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 20:00:56'),
(253, 1, 'updated', 'App\\Models\\User', 1, '\"{\\\"last_login_at\\\":\\\"2026-05-19T20:00:56.000000Z\\\",\\\"updated_at\\\":\\\"2026-05-19T20:00:56.000000Z\\\"}\"', '\"{\\\"last_login_at\\\":\\\"2026-05-20 22:30:33\\\",\\\"updated_at\\\":\\\"2026-05-20 22:30:33\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-20 19:30:33'),
(254, 1, 'updated', 'App\\Models\\User', 1, '\"{\\\"last_login_at\\\":\\\"2026-05-20T19:30:33.000000Z\\\",\\\"updated_at\\\":\\\"2026-05-20T19:30:33.000000Z\\\"}\"', '\"{\\\"last_login_at\\\":\\\"2026-05-23 12:49:55\\\",\\\"updated_at\\\":\\\"2026-05-23 12:49:55\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 09:49:56'),
(255, 1, 'updated', 'App\\Models\\Appointment', 73, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-18T22:35:43.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-23 12:51:43\\\",\\\"updated_at\\\":\\\"2026-05-23 12:51:43\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 09:51:43'),
(256, 1, 'updated', 'App\\Models\\Appointment', 73, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 09:51:43'),
(257, 1, 'updated', 'App\\Models\\Appointment', 82, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-19T14:36:39.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-23 12:51:47\\\",\\\"updated_at\\\":\\\"2026-05-23 12:51:47\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 09:51:47'),
(258, 1, 'updated', 'App\\Models\\Appointment', 82, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 09:51:47'),
(259, 1, 'updated', 'App\\Models\\Appointment', 83, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-19T14:36:33.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-23 12:51:49\\\",\\\"updated_at\\\":\\\"2026-05-23 12:51:49\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 09:51:49'),
(260, 1, 'updated', 'App\\Models\\Appointment', 83, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 09:51:49'),
(261, 1, 'created', 'App\\Models\\Appointment', 84, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"6\\\",\\\"employee_id\\\":\\\"1\\\",\\\"start_at\\\":\\\"2026-05-23 13:00:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"a80128fe-2d4d-4a05-94e6-4eb8b36732a4\\\",\\\"appointment_code\\\":\\\"BV-XP9KSSWN\\\",\\\"end_at\\\":\\\"2026-05-23 13:30:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-23 12:52:21\\\",\\\"created_at\\\":\\\"2026-05-23 12:52:21\\\",\\\"id\\\":84}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 09:52:21'),
(262, 1, 'updated', 'App\\Models\\Appointment', 84, NULL, '\"{\\\"subtotal\\\":450,\\\"total_price\\\":450}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 09:52:21'),
(263, 1, 'created', 'App\\Models\\Appointment', 85, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"7\\\",\\\"employee_id\\\":\\\"2\\\",\\\"start_at\\\":\\\"2026-05-23 13:30:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"90e176f7-f4fc-4cbf-99b0-93f9d6df37e2\\\",\\\"appointment_code\\\":\\\"BV-P7WSF4YZ\\\",\\\"end_at\\\":\\\"2026-05-23 14:00:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-23 12:52:36\\\",\\\"created_at\\\":\\\"2026-05-23 12:52:36\\\",\\\"id\\\":85}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 09:52:36'),
(264, 1, 'updated', 'App\\Models\\Appointment', 85, NULL, '\"{\\\"subtotal\\\":450,\\\"total_price\\\":450}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 09:52:36'),
(265, 1, 'created', 'App\\Models\\Appointment', 86, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"8\\\",\\\"employee_id\\\":\\\"3\\\",\\\"start_at\\\":\\\"2026-05-23 14:00:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"7e2fca39-1e5f-406d-b6e6-cab049650cd9\\\",\\\"appointment_code\\\":\\\"BV-W4HNBJVV\\\",\\\"end_at\\\":\\\"2026-05-23 14:30:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-23 12:53:00\\\",\\\"created_at\\\":\\\"2026-05-23 12:53:00\\\",\\\"id\\\":86}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 09:53:00'),
(266, 1, 'updated', 'App\\Models\\Appointment', 86, NULL, '\"{\\\"subtotal\\\":450,\\\"total_price\\\":450}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 09:53:00'),
(267, 1, 'updated', 'App\\Models\\Appointment', 84, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-23T09:52:21.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-23 12:53:20\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 09:53:20'),
(268, 1, 'updated', 'App\\Models\\Appointment', 85, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-23T09:52:36.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-23 12:53:21\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 09:53:21'),
(269, 1, 'updated', 'App\\Models\\Appointment', 86, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-23T09:53:00.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-23 12:53:22\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 09:53:22'),
(270, 1, 'created', 'App\\Models\\Appointment', 87, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"8\\\",\\\"employee_id\\\":\\\"5\\\",\\\"start_at\\\":\\\"2026-05-23 20:00:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"fbcdb659-9b50-44c3-a1f4-d17666c5c5d7\\\",\\\"appointment_code\\\":\\\"BV-Y8YDFRI0\\\",\\\"end_at\\\":\\\"2026-05-23 20:30:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-23 12:53:52\\\",\\\"created_at\\\":\\\"2026-05-23 12:53:52\\\",\\\"id\\\":87}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 09:53:52'),
(271, 1, 'updated', 'App\\Models\\Appointment', 87, NULL, '\"{\\\"subtotal\\\":350,\\\"total_price\\\":350}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 09:53:52'),
(272, 1, 'created', 'App\\Models\\Appointment', 88, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"9\\\",\\\"employee_id\\\":\\\"2\\\",\\\"start_at\\\":\\\"2026-05-23 20:30:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"ccccd805-8e33-4e5b-9eee-a52bbe2b954c\\\",\\\"appointment_code\\\":\\\"BV-8SB9FNX8\\\",\\\"end_at\\\":\\\"2026-05-23 21:00:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-23 12:54:04\\\",\\\"created_at\\\":\\\"2026-05-23 12:54:04\\\",\\\"id\\\":88}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 09:54:04'),
(273, 1, 'updated', 'App\\Models\\Appointment', 88, NULL, '\"{\\\"subtotal\\\":450,\\\"total_price\\\":450}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 09:54:04'),
(274, 1, 'created', 'App\\Models\\Appointment', 89, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"7\\\",\\\"employee_id\\\":\\\"2\\\",\\\"start_at\\\":\\\"2026-05-23 21:00:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"70424024-f658-4eb3-bf2b-a82d9dca30e7\\\",\\\"appointment_code\\\":\\\"BV-VZJS4VEW\\\",\\\"end_at\\\":\\\"2026-05-23 21:30:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-23 12:54:18\\\",\\\"created_at\\\":\\\"2026-05-23 12:54:18\\\",\\\"id\\\":89}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 09:54:18'),
(275, 1, 'updated', 'App\\Models\\Appointment', 89, NULL, '\"{\\\"subtotal\\\":450,\\\"total_price\\\":450}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 09:54:18'),
(276, 1, 'updated', 'App\\Models\\Appointment', 87, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-23T09:53:52.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-23 12:54:39\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 09:54:39'),
(277, 1, 'updated', 'App\\Models\\Appointment', 89, '\"{\\\"status\\\":\\\"pending\\\",\\\"cancellation_reason\\\":null,\\\"cancelled_by\\\":null,\\\"cancelled_at\\\":null,\\\"updated_at\\\":\\\"2026-05-23T09:54:18.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"rejected\\\",\\\"cancellation_reason\\\":\\\"Can\\\\u0131m istemedi.\\\",\\\"cancelled_by\\\":1,\\\"cancelled_at\\\":\\\"2026-05-23 12:54:57\\\",\\\"updated_at\\\":\\\"2026-05-23 12:54:57\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 09:54:57'),
(278, 1, 'updated', 'App\\Models\\Appointment', 88, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-23T09:54:04.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-23 12:55:58\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 09:55:58'),
(279, 1, 'created', 'App\\Models\\Appointment', 90, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"10\\\",\\\"employee_id\\\":\\\"3\\\",\\\"start_at\\\":\\\"2026-05-23 18:30:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"dc4273dd-b144-4801-b393-e57a0a095b21\\\",\\\"appointment_code\\\":\\\"BV-JBY1DENA\\\",\\\"end_at\\\":\\\"2026-05-23 19:00:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-23 14:52:22\\\",\\\"created_at\\\":\\\"2026-05-23 14:52:22\\\",\\\"id\\\":90}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 11:52:22'),
(280, 1, 'updated', 'App\\Models\\Appointment', 90, NULL, '\"{\\\"subtotal\\\":450,\\\"total_price\\\":450}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 11:52:22'),
(281, 1, 'updated', 'App\\Models\\Appointment', 90, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-23T11:52:22.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-23 14:52:29\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 11:52:29'),
(282, 1, 'created', 'App\\Models\\Appointment', 91, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"7\\\",\\\"employee_id\\\":\\\"2\\\",\\\"start_at\\\":\\\"2026-05-23 19:30:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"0239abf6-565f-46bd-b669-9b0c4d3382bb\\\",\\\"appointment_code\\\":\\\"BV-L8GWWXPH\\\",\\\"end_at\\\":\\\"2026-05-23 20:00:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-23 14:52:51\\\",\\\"created_at\\\":\\\"2026-05-23 14:52:51\\\",\\\"id\\\":91}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 11:52:51'),
(283, 1, 'updated', 'App\\Models\\Appointment', 91, NULL, '\"{\\\"subtotal\\\":450,\\\"total_price\\\":450}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 11:52:51'),
(284, 1, 'created', 'App\\Models\\Appointment', 92, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"8\\\",\\\"employee_id\\\":\\\"2\\\",\\\"start_at\\\":\\\"2026-05-23 19:00:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"64647bdb-bacb-4431-aece-c0c42686ec46\\\",\\\"appointment_code\\\":\\\"BV-BZFILNCU\\\",\\\"end_at\\\":\\\"2026-05-23 19:30:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-23 14:53:04\\\",\\\"created_at\\\":\\\"2026-05-23 14:53:04\\\",\\\"id\\\":92}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 11:53:04'),
(285, 1, 'updated', 'App\\Models\\Appointment', 92, NULL, '\"{\\\"subtotal\\\":250,\\\"total_price\\\":250}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 11:53:04'),
(286, 1, 'updated', 'App\\Models\\Appointment', 92, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-23T11:53:04.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-23 14:53:07\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 11:53:07'),
(287, 1, 'updated', 'App\\Models\\Appointment', 91, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-23T11:52:51.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-23 14:53:09\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 11:53:09'),
(288, 1, 'updated', 'App\\Models\\Appointment', 80, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-19T14:01:54.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-23 14:53:58\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 11:53:58'),
(289, 1, 'updated', 'App\\Models\\Appointment', 80, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-23T11:53:58.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-23 14:54:05\\\",\\\"updated_at\\\":\\\"2026-05-23 14:54:05\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 11:54:05'),
(290, 1, 'updated', 'App\\Models\\Appointment', 80, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null,\\\"updated_at\\\":\\\"2026-05-23T11:54:05.000000Z\\\"}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\",\\\"updated_at\\\":\\\"2026-05-23 14:54:19\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 11:54:19'),
(291, 1, 'updated', 'App\\Models\\Appointment', 86, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-23T09:53:22.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-23 14:54:31\\\",\\\"updated_at\\\":\\\"2026-05-23 14:54:31\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 11:54:31'),
(292, 1, 'updated', 'App\\Models\\Appointment', 86, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 11:54:31'),
(293, 1, 'updated', 'App\\Models\\Appointment', 85, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-23T09:53:21.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-23 14:54:50\\\",\\\"updated_at\\\":\\\"2026-05-23 14:54:50\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 11:54:50'),
(294, 1, 'updated', 'App\\Models\\Appointment', 85, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 11:54:50'),
(295, 1, 'updated', 'App\\Models\\Appointment', 84, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-23T09:53:20.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-23 14:55:34\\\",\\\"updated_at\\\":\\\"2026-05-23 14:55:34\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 11:55:34'),
(296, 1, 'updated', 'App\\Models\\Appointment', 84, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 11:55:34'),
(297, 1, 'updated', 'App\\Models\\Campaign', 1, '\"{\\\"is_active\\\":true,\\\"updated_at\\\":\\\"2026-05-14T13:44:51.000000Z\\\"}\"', '\"{\\\"is_active\\\":false,\\\"updated_at\\\":\\\"2026-05-23 14:56:29\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 11:56:29'),
(298, 1, 'updated', 'App\\Models\\Campaign', 1, '\"{\\\"is_active\\\":false,\\\"updated_at\\\":\\\"2026-05-23T11:56:29.000000Z\\\"}\"', '\"{\\\"is_active\\\":true,\\\"updated_at\\\":\\\"2026-05-23 14:56:32\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 11:56:32'),
(299, 1, 'updated', 'App\\Models\\Campaign', 1, '\"{\\\"is_active\\\":true,\\\"updated_at\\\":\\\"2026-05-23T11:56:32.000000Z\\\"}\"', '\"{\\\"is_active\\\":false,\\\"updated_at\\\":\\\"2026-05-23 14:56:35\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 11:56:35'),
(300, 1, 'updated', 'App\\Models\\Campaign', 1, '\"{\\\"is_active\\\":false,\\\"updated_at\\\":\\\"2026-05-23T11:56:35.000000Z\\\"}\"', '\"{\\\"is_active\\\":true,\\\"updated_at\\\":\\\"2026-05-23 14:56:37\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 11:56:37'),
(301, 1, 'updated', 'App\\Models\\Campaign', 2, '\"{\\\"is_active\\\":true,\\\"updated_at\\\":\\\"2026-05-14T13:44:51.000000Z\\\"}\"', '\"{\\\"is_active\\\":false,\\\"updated_at\\\":\\\"2026-05-23 14:56:39\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 11:56:39'),
(302, 1, 'updated', 'App\\Models\\Campaign', 1, '\"{\\\"is_active\\\":true,\\\"updated_at\\\":\\\"2026-05-23T11:56:37.000000Z\\\"}\"', '\"{\\\"is_active\\\":false,\\\"updated_at\\\":\\\"2026-05-23 14:56:41\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 11:56:41'),
(303, 1, 'updated', 'App\\Models\\Campaign', 2, '\"{\\\"is_active\\\":false,\\\"updated_at\\\":\\\"2026-05-23T11:56:39.000000Z\\\"}\"', '\"{\\\"is_active\\\":true,\\\"updated_at\\\":\\\"2026-05-23 14:56:43\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 11:56:43'),
(304, 1, 'updated', 'App\\Models\\Campaign', 1, '\"{\\\"is_active\\\":false,\\\"updated_at\\\":\\\"2026-05-23T11:56:41.000000Z\\\"}\"', '\"{\\\"is_active\\\":true,\\\"updated_at\\\":\\\"2026-05-23 14:57:07\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 11:57:07'),
(305, 1, 'updated', 'App\\Models\\User', 1, '\"{\\\"last_login_at\\\":\\\"2026-05-23T09:49:55.000000Z\\\",\\\"updated_at\\\":\\\"2026-05-23T09:49:55.000000Z\\\"}\"', '\"{\\\"last_login_at\\\":\\\"2026-05-23 18:06:33\\\",\\\"updated_at\\\":\\\"2026-05-23 18:06:33\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 15:06:33'),
(306, 1, 'created', 'App\\Models\\Appointment', 93, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"10\\\",\\\"employee_id\\\":\\\"5\\\",\\\"start_at\\\":\\\"2026-05-23 18:30:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"781ae779-3854-4b70-b581-d80c315f0275\\\",\\\"appointment_code\\\":\\\"BV-YFFI9NEF\\\",\\\"end_at\\\":\\\"2026-05-23 19:00:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-23 18:16:23\\\",\\\"created_at\\\":\\\"2026-05-23 18:16:23\\\",\\\"id\\\":93}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 15:16:23'),
(307, 1, 'updated', 'App\\Models\\Appointment', 93, NULL, '\"{\\\"subtotal\\\":450,\\\"total_price\\\":450}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 15:16:23'),
(308, 1, 'updated', 'App\\Models\\Appointment', 93, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-23T15:16:23.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-23 18:17:22\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 15:17:22'),
(309, 1, 'updated', 'App\\Models\\Service', 6, '\"{\\\"is_active\\\":true,\\\"updated_at\\\":\\\"2026-05-18T13:28:28.000000Z\\\"}\"', '\"{\\\"is_active\\\":false,\\\"updated_at\\\":\\\"2026-05-23 18:19:10\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 15:19:10'),
(310, 1, 'updated', 'App\\Models\\Campaign', 1, '\"{\\\"is_active\\\":true,\\\"updated_at\\\":\\\"2026-05-23T11:57:07.000000Z\\\"}\"', '\"{\\\"is_active\\\":false,\\\"updated_at\\\":\\\"2026-05-23 18:20:45\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 15:20:45'),
(311, 1, 'updated', 'App\\Models\\Campaign', 1, '\"{\\\"is_active\\\":false,\\\"updated_at\\\":\\\"2026-05-23T15:20:45.000000Z\\\"}\"', '\"{\\\"is_active\\\":true,\\\"updated_at\\\":\\\"2026-05-23 18:20:47\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 15:20:47'),
(312, 1, 'updated', 'App\\Models\\Appointment', 87, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-23T09:54:39.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-23 20:09:57\\\",\\\"updated_at\\\":\\\"2026-05-23 20:09:57\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 17:09:57'),
(313, 1, 'updated', 'App\\Models\\Appointment', 87, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"credit_card\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 17:09:57'),
(314, 1, 'updated', 'App\\Models\\Appointment', 91, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-23T11:53:09.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-23 20:10:08\\\",\\\"updated_at\\\":\\\"2026-05-23 20:10:08\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 17:10:08'),
(315, 1, 'updated', 'App\\Models\\Appointment', 91, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 17:10:08'),
(316, 1, 'updated', 'App\\Models\\Appointment', 92, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-23T11:53:07.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-23 20:10:50\\\",\\\"updated_at\\\":\\\"2026-05-23 20:10:50\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 17:10:50'),
(317, 1, 'updated', 'App\\Models\\Appointment', 92, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 17:10:50'),
(318, 1, 'updated', 'App\\Models\\Appointment', 90, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-23T11:52:29.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-23 20:10:55\\\",\\\"updated_at\\\":\\\"2026-05-23 20:10:55\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 17:10:55'),
(319, 1, 'updated', 'App\\Models\\Appointment', 90, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 17:10:55'),
(320, 1, 'updated', 'App\\Models\\Appointment', 93, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"no_show\\\":false,\\\"updated_at\\\":\\\"2026-05-23T15:17:22.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"no_show\\\",\\\"no_show\\\":true,\\\"updated_at\\\":\\\"2026-05-23 20:11:20\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 17:11:20'),
(321, 1, 'updated', 'App\\Models\\Service', 3, '\"{\\\"is_active\\\":false,\\\"updated_at\\\":\\\"2026-05-19T14:00:35.000000Z\\\"}\"', '\"{\\\"is_active\\\":true,\\\"updated_at\\\":\\\"2026-05-23 20:16:20\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 17:16:20'),
(322, 1, 'updated', 'App\\Models\\Service', 2, '\"{\\\"discounted_price\\\":null,\\\"updated_at\\\":\\\"2026-05-19T14:00:41.000000Z\\\"}\"', '\"{\\\"discounted_price\\\":\\\"150\\\",\\\"updated_at\\\":\\\"2026-05-23 20:16:28\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 17:16:28'),
(323, 1, 'updated', 'App\\Models\\Service', 2, '\"{\\\"discounted_price\\\":\\\"150.00\\\",\\\"updated_at\\\":\\\"2026-05-23T17:16:28.000000Z\\\"}\"', '\"{\\\"discounted_price\\\":null,\\\"updated_at\\\":\\\"2026-05-23 20:16:37\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 17:16:37'),
(324, 1, 'created', 'App\\Models\\Appointment', 94, NULL, '\"{\\\"branch_id\\\":\\\"1\\\",\\\"customer_id\\\":\\\"7\\\",\\\"employee_id\\\":\\\"3\\\",\\\"start_at\\\":\\\"2026-05-23 21:30:00\\\",\\\"source\\\":\\\"admin_panel\\\",\\\"customer_note\\\":null,\\\"internal_note\\\":null,\\\"created_by\\\":1,\\\"uuid\\\":\\\"983494de-889f-4a0d-aed8-cab8244b404c\\\",\\\"appointment_code\\\":\\\"BV-RCQ2DFU2\\\",\\\"end_at\\\":\\\"2026-05-23 22:00:00\\\",\\\"total_duration\\\":30,\\\"updated_at\\\":\\\"2026-05-23 20:27:23\\\",\\\"created_at\\\":\\\"2026-05-23 20:27:23\\\",\\\"id\\\":94}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 17:27:23'),
(325, 1, 'updated', 'App\\Models\\Appointment', 94, NULL, '\"{\\\"subtotal\\\":300,\\\"total_price\\\":300}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 17:27:23'),
(326, 1, 'updated', 'App\\Models\\Appointment', 94, '\"{\\\"status\\\":\\\"pending\\\",\\\"updated_at\\\":\\\"2026-05-23T17:27:23.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"confirmed\\\",\\\"updated_at\\\":\\\"2026-05-23 20:29:09\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-23 17:29:09');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `model_type`, `model_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(327, 1, 'updated', 'App\\Models\\User', 1, '\"{\\\"last_login_at\\\":\\\"2026-05-23T15:06:33.000000Z\\\",\\\"updated_at\\\":\\\"2026-05-23T15:06:33.000000Z\\\"}\"', '\"{\\\"last_login_at\\\":\\\"2026-05-25 13:56:30\\\",\\\"updated_at\\\":\\\"2026-05-25 13:56:30\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 10:56:30'),
(328, 1, 'updated', 'App\\Models\\Appointment', 94, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-23T17:29:09.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-25 13:56:41\\\",\\\"updated_at\\\":\\\"2026-05-25 13:56:41\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 10:56:41'),
(329, 1, 'updated', 'App\\Models\\Appointment', 94, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 10:56:41'),
(330, 1, 'updated', 'App\\Models\\Appointment', 88, '\"{\\\"status\\\":\\\"confirmed\\\",\\\"completed_at\\\":null,\\\"updated_at\\\":\\\"2026-05-23T09:55:58.000000Z\\\"}\"', '\"{\\\"status\\\":\\\"completed\\\",\\\"completed_at\\\":\\\"2026-05-25 13:56:44\\\",\\\"updated_at\\\":\\\"2026-05-25 13:56:44\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 10:56:44'),
(331, 1, 'updated', 'App\\Models\\Appointment', 88, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-25 10:56:44'),
(332, 1, 'updated', 'App\\Models\\User', 1, '\"{\\\"last_login_at\\\":\\\"2026-05-25T10:56:30.000000Z\\\",\\\"updated_at\\\":\\\"2026-05-25T10:56:30.000000Z\\\"}\"', '\"{\\\"last_login_at\\\":\\\"2026-05-26 16:59:50\\\",\\\"updated_at\\\":\\\"2026-05-26 16:59:50\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 13:59:50'),
(333, 1, 'updated', 'App\\Models\\User', 1, '\"{\\\"last_login_at\\\":\\\"2026-05-26T13:59:50.000000Z\\\",\\\"updated_at\\\":\\\"2026-05-26T13:59:50.000000Z\\\"}\"', '\"{\\\"last_login_at\\\":\\\"2026-05-30 17:27:45\\\",\\\"updated_at\\\":\\\"2026-05-30 17:27:45\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-30 14:27:45'),
(334, 1, 'updated', 'App\\Models\\Appointment', 52, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null,\\\"updated_at\\\":\\\"2026-05-18T15:14:39.000000Z\\\"}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\",\\\"updated_at\\\":\\\"2026-05-30 18:00:14\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-30 15:00:14'),
(335, 1, 'updated', 'App\\Models\\Appointment', 47, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null,\\\"updated_at\\\":\\\"2026-05-18T15:14:48.000000Z\\\"}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\",\\\"updated_at\\\":\\\"2026-05-30 18:00:27\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-30 15:00:27'),
(336, 1, 'updated', 'App\\Models\\Appointment', 33, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null,\\\"updated_at\\\":\\\"2026-05-18T15:14:16.000000Z\\\"}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\",\\\"updated_at\\\":\\\"2026-05-30 18:14:15\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-30 15:14:15'),
(337, 1, 'updated', 'App\\Models\\User', 1, '\"{\\\"last_login_at\\\":\\\"2026-05-30T14:27:45.000000Z\\\",\\\"updated_at\\\":\\\"2026-05-30T14:27:45.000000Z\\\"}\"', '\"{\\\"last_login_at\\\":\\\"2026-06-08 17:45:30\\\",\\\"updated_at\\\":\\\"2026-06-08 17:45:30\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 14:45:30'),
(338, 1, 'updated', 'App\\Models\\Appointment', 46, '\"{\\\"payment_status\\\":\\\"unpaid\\\",\\\"payment_method\\\":null,\\\"updated_at\\\":\\\"2026-05-18T15:14:55.000000Z\\\"}\"', '\"{\\\"payment_status\\\":\\\"paid\\\",\\\"payment_method\\\":\\\"cash\\\",\\\"updated_at\\\":\\\"2026-06-08 17:51:24\\\"}\"', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 14:51:24');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `branches`
--

CREATE TABLE `branches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `district` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `timezone` varchar(50) DEFAULT 'Europe/Istanbul',
  `logo` varchar(255) DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `opening_time` time DEFAULT NULL,
  `closing_time` time DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `branches`
--

INSERT INTO `branches` (`id`, `uuid`, `name`, `slug`, `phone`, `email`, `city`, `district`, `address`, `latitude`, `longitude`, `timezone`, `logo`, `cover_image`, `opening_time`, `closing_time`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '1078874e-4f9b-11f1-98d5-2e5e95d91c8b', 'B&V Premium Barber', 'bv-premium-barber', '+905551112233', 'info@bvbarber.com', 'İzmir', 'Karşıyaka', 'Karşıyaka Mahallesi Barber Sokak No:10', 38.45500000, 27.11200000, 'Europe/Istanbul', 'branch_logo.png', 'branch_cover.png', '09:00:00', '22:00:00', 1, '2026-05-14 13:44:44', '2026-05-14 13:44:44', NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `branch_settings`
--

CREATE TABLE `branch_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `appointment_interval` int(11) DEFAULT 30,
  `cancellation_limit_hours` int(11) DEFAULT 2,
  `currency` varchar(10) DEFAULT 'TRY',
  `loyalty_enabled` tinyint(1) DEFAULT 1,
  `review_enabled` tinyint(1) DEFAULT 1,
  `online_payment_enabled` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `branch_settings`
--

INSERT INTO `branch_settings` (`id`, `branch_id`, `appointment_interval`, `cancellation_limit_hours`, `currency`, `loyalty_enabled`, `review_enabled`, `online_payment_enabled`, `created_at`, `updated_at`) VALUES
(1, 1, 30, 2, 'TRY', 1, 1, 1, '2026-05-14 13:44:44', '2026-05-14 13:44:44');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('bv-barber-cache-dashboard.barbers.1', 'a:4:{i:0;a:8:{s:2:\"id\";i:3;s:4:\"name\";s:10:\"Burak Kaya\";s:5:\"title\";s:10:\"VIP Barber\";s:5:\"photo\";N;s:22:\"completed_appointments\";i:1;s:7:\"revenue\";d:1000;s:6:\"rating\";d:4.5;s:15:\"commission_rate\";s:5:\"20.00\";}i:1;a:8:{s:2:\"id\";i:1;s:4:\"name\";s:13:\"Ahmet Yılmaz\";s:5:\"title\";s:11:\"Salon Owner\";s:5:\"photo\";N;s:22:\"completed_appointments\";i:0;s:7:\"revenue\";d:0;s:6:\"rating\";d:0;s:15:\"commission_rate\";s:5:\"15.00\";}i:2;a:8:{s:2:\"id\";i:2;s:4:\"name\";s:10:\"Emre Demir\";s:5:\"title\";s:13:\"Senior Barber\";s:5:\"photo\";N;s:22:\"completed_appointments\";i:0;s:7:\"revenue\";d:0;s:6:\"rating\";d:5;s:15:\"commission_rate\";s:5:\"35.00\";}i:3;a:8:{s:2:\"id\";i:4;s:4:\"name\";s:12:\"Selin Aydın\";s:5:\"title\";s:12:\"Receptionist\";s:5:\"photo\";N;s:22:\"completed_appointments\";i:0;s:7:\"revenue\";d:0;s:6:\"rating\";d:0;s:15:\"commission_rate\";s:4:\"0.00\";}}', 1778775903),
('bv-barber-cache-dashboard.chart.1', 'a:12:{i:0;a:3:{s:5:\"month\";i:1;s:5:\"label\";s:3:\"Oca\";s:7:\"revenue\";d:0;}i:1;a:3:{s:5:\"month\";i:2;s:5:\"label\";s:4:\"Şub\";s:7:\"revenue\";d:0;}i:2;a:3:{s:5:\"month\";i:3;s:5:\"label\";s:3:\"Mar\";s:7:\"revenue\";d:0;}i:3;a:3:{s:5:\"month\";i:4;s:5:\"label\";s:3:\"Nis\";s:7:\"revenue\";d:0;}i:4;a:3:{s:5:\"month\";i:5;s:5:\"label\";s:3:\"May\";s:7:\"revenue\";d:1000;}i:5;a:3:{s:5:\"month\";i:6;s:5:\"label\";s:3:\"Haz\";s:7:\"revenue\";d:0;}i:6;a:3:{s:5:\"month\";i:7;s:5:\"label\";s:3:\"Tem\";s:7:\"revenue\";d:0;}i:7;a:3:{s:5:\"month\";i:8;s:5:\"label\";s:4:\"Ağu\";s:7:\"revenue\";d:0;}i:8;a:3:{s:5:\"month\";i:9;s:5:\"label\";s:3:\"Eyl\";s:7:\"revenue\";d:0;}i:9;a:3:{s:5:\"month\";i:10;s:5:\"label\";s:3:\"Eki\";s:7:\"revenue\";d:0;}i:10;a:3:{s:5:\"month\";i:11;s:5:\"label\";s:3:\"Kas\";s:7:\"revenue\";d:0;}i:11;a:3:{s:5:\"month\";i:12;s:5:\"label\";s:3:\"Ara\";s:7:\"revenue\";d:0;}}', 1778775903),
('bv-barber-cache-dashboard.services.1', 'a:1:{i:0;O:8:\"stdClass\":4:{s:2:\"id\";i:3;s:4:\"name\";s:14:\"VIP Full Paket\";s:11:\"usage_count\";i:1;s:13:\"total_revenue\";s:7:\"1200.00\";}}', 1778775903),
('bv-barber-cache-dashboard.widgets.1', 'a:4:{s:7:\"revenue\";a:4:{s:5:\"daily\";i:0;s:6:\"weekly\";s:7:\"1000.00\";s:7:\"monthly\";s:7:\"1000.00\";s:6:\"yearly\";s:7:\"1000.00\";}s:12:\"appointments\";a:7:{s:11:\"today_total\";i:0;s:15:\"today_completed\";i:0;s:13:\"today_pending\";i:0;s:11:\"month_total\";i:3;s:15:\"month_cancelled\";i:0;s:13:\"month_no_show\";i:0;s:17:\"cancellation_rate\";d:0;}s:9:\"financial\";a:3:{s:6:\"income\";d:1450;s:7:\"expense\";d:4300;s:6:\"profit\";d:-2850;}s:9:\"customers\";a:4:{s:5:\"total\";i:3;s:14:\"new_this_month\";i:3;s:5:\"loyal\";i:0;s:12:\"avg_spending\";d:1000;}}', 1778775603);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `campaigns`
--

CREATE TABLE `campaigns` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `campaigns`
--

INSERT INTO `campaigns` (`id`, `branch_id`, `title`, `description`, `discount_type`, `discount_value`, `start_date`, `end_date`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Yaz Kampanyası', 'Tüm saç kesimlerinde indirim.', 'percentage', 10.00, '2026-06-01', '2026-08-31', 1, '2026-05-14 13:44:51', '2026-05-23 15:20:47'),
(2, 1, 'VIP İndirimi', 'VIP paketlerde özel indirim.', 'fixed', 200.00, '2026-05-01', '2026-06-30', 1, '2026-05-14 13:44:51', '2026-05-23 11:56:43');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `coupons`
--

CREATE TABLE `coupons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `campaign_id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL,
  `usage_limit` int(11) DEFAULT 1,
  `used_count` int(11) DEFAULT 0,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `coupons`
--

INSERT INTO `coupons` (`id`, `campaign_id`, `code`, `usage_limit`, `used_count`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'SUMMER10', 100, 12, '2026-08-31 23:59:59', '2026-05-14 13:44:51', '2026-05-14 13:44:51'),
(2, 2, 'VIP200', 50, 4, '2026-06-30 23:59:59', '2026-05-14 13:44:51', '2026-05-14 13:44:51');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `customer_notes`
--

CREATE TABLE `customer_notes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `note` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `customer_notes`
--

INSERT INTO `customer_notes` (`id`, `customer_id`, `employee_id`, `note`, `created_at`, `updated_at`) VALUES
(1, 6, 2, 'Fade model seviyor.', '2026-05-14 13:44:51', '2026-05-14 13:44:51'),
(2, 7, 3, 'VIP hizmetleri tercih ediyor.', '2026-05-14 13:44:51', '2026-05-14 13:44:51'),
(3, 8, 2, 'Sakal çizgisi hassas.', '2026-05-14 13:44:51', '2026-05-14 13:44:51');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `debts`
--

CREATE TABLE `debts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `appointment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `description` varchar(255) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'unpaid',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `debts`
--

INSERT INTO `debts` (`id`, `branch_id`, `customer_id`, `appointment_id`, `amount`, `paid_amount`, `description`, `due_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 6, 33, 300.00, 300.00, 'Randevu Borcu - #APT-3010', '2026-05-16', 'paid', '2026-05-14 21:23:54', '2026-05-30 15:14:15'),
(2, 1, 9, 46, 999.00, 999.00, 'Randevu Borcu - #BV-V43BVGP4', '2026-05-15', 'paid', '2026-05-14 22:29:14', '2026-06-08 14:51:24'),
(3, 1, 9, 47, 300.00, 300.00, 'Randevu Borcu - #BV-VJ8PDIAV', '2026-05-15', 'paid', '2026-05-14 22:31:57', '2026-05-30 15:00:27'),
(4, 1, 7, 52, 450.00, 450.00, 'Randevu Borcu - #BV-OHOIQNBJ', '2026-05-15', 'paid', '2026-05-14 23:04:05', '2026-05-30 15:00:14');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `devices`
--

CREATE TABLE `devices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `device_type` enum('ios','android','web') NOT NULL,
  `push_token` text NOT NULL,
  `app_version` varchar(50) DEFAULT NULL,
  `last_active_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `devices`
--

INSERT INTO `devices` (`id`, `user_id`, `device_type`, `push_token`, `app_version`, `last_active_at`, `created_at`, `updated_at`) VALUES
(1, 6, 'ios', 'fcm_token_ios_001', '1.0.0', '2026-05-14 13:44:51', '2026-05-14 13:44:51', '2026-05-14 13:44:51'),
(2, 7, 'ios', 'fcm_token_ios_002', '1.0.0', '2026-05-14 13:44:51', '2026-05-14 13:44:51', '2026-05-14 13:44:51'),
(3, 8, 'android', 'fcm_token_android_001', '1.0.0', '2026-05-14 13:44:51', '2026-05-14 13:44:51', '2026-05-14 13:44:51');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `employees`
--

CREATE TABLE `employees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `employee_code` varchar(50) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `biography` text DEFAULT NULL,
  `hire_date` date NOT NULL,
  `leave_date` date DEFAULT NULL,
  `salary_type` enum('fixed','commission','fixed_plus_commission','hourly') DEFAULT 'fixed',
  `salary_amount` decimal(10,2) DEFAULT 0.00,
  `commission_rate` decimal(5,2) DEFAULT 0.00,
  `daily_work_limit` int(11) DEFAULT 20,
  `appointment_color` varchar(20) DEFAULT '#000000',
  `is_visible` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `employees`
--

INSERT INTO `employees` (`id`, `branch_id`, `user_id`, `employee_code`, `title`, `biography`, `hire_date`, `leave_date`, `salary_type`, `salary_amount`, `commission_rate`, `daily_work_limit`, `appointment_color`, `is_visible`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 2, 'EMP-001', 'Salon Owner', 'Salon sahibi ve kıdemli barber.', '2020-01-01', NULL, 'fixed_plus_commission', 50000.00, 15.00, 20, '#111827', 1, 1, '2026-05-14 13:44:44', '2026-05-14 13:44:44', NULL),
(2, 1, 3, 'EMP-002', 'Senior Barber', 'Fade ve sakal tasarım uzmanı.', '2021-05-15', NULL, 'commission', 0.00, 35.00, 18, '#2563EB', 1, 1, '2026-05-14 13:44:44', '2026-05-14 13:44:44', NULL),
(3, 1, 4, 'EMP-003', 'VIP Barber', 'VIP müşteri deneyimi uzmanı.', '2022-03-10', NULL, 'fixed_plus_commission', 30000.00, 20.00, 15, '#DC2626', 1, 1, '2026-05-14 13:44:44', '2026-05-14 13:44:44', NULL),
(4, 1, 5, 'EMP-004', 'Receptionist', 'Müşteri karşılama ve randevu yönetimi.', '2023-01-01', NULL, 'fixed', 25000.00, 0.00, 0, '#16A34A', 0, 1, '2026-05-14 13:44:44', '2026-05-14 13:44:44', NULL),
(5, 1, 11, 'EMP-QXOKCY', 'Berber', NULL, '2026-05-18', NULL, 'fixed', 10000.00, 0.00, 20, '#000000', 1, 1, '2026-05-18 13:36:45', '2026-05-18 13:36:45', NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `employee_leaves`
--

CREATE TABLE `employee_leaves` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `leave_type` enum('annual','sick','unpaid','official','other') DEFAULT 'annual',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `approval_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `employee_schedules`
--

CREATE TABLE `employee_schedules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `work_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `break_start` time DEFAULT NULL,
  `break_end` time DEFAULT NULL,
  `is_day_off` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `employee_schedules`
--

INSERT INTO `employee_schedules` (`id`, `employee_id`, `work_date`, `start_time`, `end_time`, `break_start`, `break_end`, `is_day_off`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-05-15', '09:00:00', '21:00:00', '14:00:00', '15:00:00', 0, '2026-05-14 13:44:51', '2026-05-14 13:44:51'),
(2, 2, '2026-05-15', '10:00:00', '22:00:00', '15:00:00', '16:00:00', 0, '2026-05-14 13:44:51', '2026-05-14 13:44:51'),
(3, 3, '2026-05-15', '09:00:00', '20:00:00', '13:00:00', '14:00:00', 0, '2026-05-14 13:44:51', '2026-05-14 13:44:51'),
(4, 1, '2026-05-16', '09:00:00', '21:00:00', '14:00:00', '15:00:00', 0, '2026-05-14 13:44:51', '2026-05-14 13:44:51'),
(5, 2, '2026-05-16', '10:00:00', '22:00:00', '15:00:00', '16:00:00', 0, '2026-05-14 13:44:51', '2026-05-14 13:44:51'),
(6, 3, '2026-05-16', '09:00:00', '20:00:00', '13:00:00', '14:00:00', 0, '2026-05-14 13:44:51', '2026-05-14 13:44:51');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `employee_services`
--

CREATE TABLE `employee_services` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `service_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `employee_services`
--

INSERT INTO `employee_services` (`id`, `employee_id`, `service_id`, `created_at`) VALUES
(1, 1, 1, '2026-05-14 13:44:51'),
(2, 1, 2, '2026-05-14 13:44:51'),
(3, 1, 3, '2026-05-14 13:44:51'),
(4, 1, 4, '2026-05-14 13:44:51'),
(5, 2, 1, '2026-05-14 13:44:51'),
(6, 2, 2, '2026-05-14 13:44:51'),
(7, 2, 5, '2026-05-14 13:44:51'),
(8, 3, 1, '2026-05-14 13:44:51'),
(9, 3, 2, '2026-05-14 13:44:51'),
(10, 3, 3, '2026-05-14 13:44:51'),
(11, 3, 4, '2026-05-14 13:44:51');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `expenses`
--

CREATE TABLE `expenses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `expense_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `receipt_file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `expenses`
--

INSERT INTO `expenses` (`id`, `branch_id`, `category_id`, `created_by`, `amount`, `expense_date`, `description`, `receipt_file`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 2, 2500.00, '2026-05-10', 'Elektrik faturası Mayıs ayı', 'receipt_001.pdf', '2026-05-14 13:44:51', '2026-05-14 13:44:51'),
(2, 1, 4, 2, 1800.00, '2026-05-11', 'Yeni bakım ürünleri', 'receipt_002.pdf', '2026-05-14 13:44:51', '2026-05-14 13:44:51'),
(3, 1, 4, 1, 886.76, '2026-05-18', 'Deneme olarak ödeme kaydı.', 'receipts/n4GCpkllnwuECZ1HnPi2x5RKmNgmt6juEz91od8W.jpg', '2026-05-18 20:13:28', '2026-05-18 20:13:28'),
(4, 1, 1, 1, 100.00, '2026-05-18', NULL, NULL, '2026-05-18 20:16:05', '2026-05-18 20:16:05'),
(5, 1, 3, 1, 12000.00, '2026-05-19', 'Mustafa\'ya maaş atıldı.', NULL, '2026-05-19 13:13:47', '2026-05-19 13:17:02'),
(6, 1, 4, 1, 10.00, '2026-05-19', 'Deneme gider bilgisi.', 'receipts/XTFIi18kgnrU03tSZlrhYgGoaqjMzr4OkhmBbnlc.png', '2026-05-19 13:56:13', '2026-05-19 13:56:13'),
(7, 1, 2, 1, 15000.00, '2026-05-23', 'Dükkan Kirası', NULL, '2026-05-23 17:19:34', '2026-05-23 17:19:34');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `expense_categories`
--

CREATE TABLE `expense_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `expense_categories`
--

INSERT INTO `expense_categories` (`id`, `branch_id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 'Elektrik', 'Elektrik giderleri', '2026-05-14 13:44:51', '2026-05-14 13:44:51'),
(2, 1, 'Kira', 'Dükkan kira giderleri', '2026-05-14 13:44:51', '2026-05-14 13:44:51'),
(3, 1, 'Maaş', 'Personel maaş giderleri', '2026-05-14 13:44:51', '2026-05-14 13:44:51'),
(4, 1, 'Malzeme', 'Bakım ve ekipman giderleri', '2026-05-14 13:44:51', '2026-05-14 13:44:51');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `loyalty_accounts`
--

CREATE TABLE `loyalty_accounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `points_balance` int(11) DEFAULT 0,
  `total_earned` int(11) DEFAULT 0,
  `total_spent` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `loyalty_accounts`
--

INSERT INTO `loyalty_accounts` (`id`, `customer_id`, `points_balance`, `total_earned`, `total_spent`, `created_at`, `updated_at`) VALUES
(1, 6, 120, 300, 180, '2026-05-14 13:44:51', '2026-05-14 13:44:51'),
(2, 7, 500, 700, 200, '2026-05-14 13:44:51', '2026-05-14 13:44:51'),
(3, 8, 50, 50, 0, '2026-05-14 13:44:51', '2026-05-14 13:44:51');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `loyalty_transactions`
--

CREATE TABLE `loyalty_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loyalty_account_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('earn','spend','expire','manual') NOT NULL,
  `points` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `loyalty_transactions`
--

INSERT INTO `loyalty_transactions` (`id`, `loyalty_account_id`, `type`, `points`, `description`, `created_at`) VALUES
(1, 1, 'earn', 100, 'Randevu sonrası puan kazanımı', '2026-05-14 13:44:51'),
(2, 1, 'spend', 50, 'İndirim kullanımı', '2026-05-14 13:44:51'),
(3, 2, 'earn', 500, 'VIP paket bonus puanı', '2026-05-14 13:44:51');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2026_05_30_170000_create_debts_table', 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `type` varchar(100) DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `is_read` tinyint(1) DEFAULT 0,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `body`, `type`, `data`, `is_read`, `sent_at`, `created_at`) VALUES
(1, 6, 'Randevunuz Onaylandı', '15 Mayıs tarihli randevunuz onaylandı.', 'appointment', '{\"appointment_id\": 1}', 1, '2026-05-14 13:44:51', '2026-05-14 13:44:51'),
(2, 7, 'Kampanya Başladı', 'Yeni yaz kampanyasını kaçırmayın.', 'campaign', '{\"campaign_id\": 1}', 1, '2026-05-14 13:44:51', '2026-05-14 13:44:51');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `appointment_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','credit_card','bank_transfer','online') NOT NULL,
  `transaction_reference` varchar(255) DEFAULT NULL,
  `paid_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `payments`
--

INSERT INTO `payments` (`id`, `appointment_id`, `amount`, `payment_method`, `transaction_reference`, `paid_at`, `created_at`) VALUES
(3, 53, 450.00, 'credit_card', NULL, '2026-05-18 00:00:00', '2026-05-18 20:03:49'),
(4, 54, 300.00, 'cash', NULL, '2026-05-18 00:00:00', '2026-05-18 20:10:37'),
(6, 55, 300.00, 'cash', NULL, '2026-05-18 00:00:00', '2026-05-18 20:11:25'),
(7, 48, 350.00, 'credit_card', NULL, '2026-05-18 00:00:00', '2026-05-18 20:23:26'),
(8, 72, 600.00, 'cash', NULL, '2026-05-19 00:00:00', '2026-05-19 12:20:33'),
(9, 67, 250.00, 'cash', NULL, '2026-05-19 00:00:00', '2026-05-19 12:20:36'),
(10, 61, 250.00, 'cash', NULL, '2026-05-19 00:00:00', '2026-05-19 12:20:41'),
(11, 71, 450.00, 'cash', NULL, '2026-05-19 00:00:00', '2026-05-19 12:20:45'),
(12, 70, 250.00, 'cash', NULL, '2026-05-19 00:00:00', '2026-05-19 12:21:01'),
(13, 66, 1200.00, 'cash', NULL, '2026-05-19 00:00:00', '2026-05-19 12:21:04'),
(14, 60, 1200.00, 'cash', NULL, '2026-05-19 00:00:00', '2026-05-19 12:21:14'),
(15, 56, 450.00, 'cash', NULL, '2026-05-19 00:00:00', '2026-05-19 12:21:17'),
(16, 57, 450.00, 'cash', NULL, '2026-05-19 00:00:00', '2026-05-19 12:21:21'),
(17, 65, 1200.00, 'cash', NULL, '2026-05-19 00:00:00', '2026-05-19 12:21:23'),
(18, 64, 600.00, 'cash', NULL, '2026-05-19 00:00:00', '2026-05-19 12:21:26'),
(19, 63, 300.00, 'cash', NULL, '2026-05-19 00:00:00', '2026-05-19 12:21:30'),
(20, 69, 1200.00, 'cash', NULL, '2026-05-19 00:00:00', '2026-05-19 12:21:36'),
(21, 62, 350.00, 'cash', NULL, '2026-05-19 00:00:00', '2026-05-19 12:21:40'),
(22, 68, 300.00, 'cash', NULL, '2026-05-19 00:00:00', '2026-05-19 12:21:43'),
(23, 74, 250.00, 'cash', NULL, '2026-05-19 16:05:14', '2026-05-19 13:05:14'),
(24, 78, 450.00, 'cash', NULL, '2026-05-19 17:36:56', '2026-05-19 14:36:56'),
(25, 73, 450.00, 'cash', NULL, '2026-05-23 12:51:43', '2026-05-23 09:51:43'),
(26, 82, 350.00, 'cash', NULL, '2026-05-23 12:51:47', '2026-05-23 09:51:47'),
(27, 83, 600.00, 'cash', NULL, '2026-05-23 12:51:49', '2026-05-23 09:51:49'),
(28, 80, 250.00, 'cash', NULL, '2026-05-20 00:00:00', '2026-05-23 11:54:19'),
(29, 86, 450.00, 'cash', NULL, '2026-05-22 00:00:00', '2026-05-23 11:54:31'),
(30, 85, 450.00, 'cash', NULL, '2026-05-23 14:54:50', '2026-05-23 11:54:50'),
(31, 84, 450.00, 'cash', NULL, '2026-05-23 14:55:34', '2026-05-23 11:55:34'),
(32, 87, 350.00, 'credit_card', NULL, '2026-05-23 20:09:57', '2026-05-23 17:09:57'),
(33, 91, 450.00, 'cash', NULL, '2026-05-23 20:10:08', '2026-05-23 17:10:08'),
(34, 92, 250.00, 'cash', NULL, '2026-05-23 20:10:50', '2026-05-23 17:10:50'),
(35, 90, 450.00, 'cash', NULL, '2026-05-23 20:10:55', '2026-05-23 17:10:55'),
(36, 94, 300.00, 'cash', NULL, '2026-05-25 13:56:41', '2026-05-25 10:56:41'),
(37, 88, 450.00, 'cash', NULL, '2026-05-25 13:56:44', '2026-05-25 10:56:44'),
(38, 52, 450.00, 'cash', NULL, '2026-05-30 18:00:14', '2026-05-30 15:00:14'),
(39, 47, 300.00, 'cash', NULL, '2026-05-30 18:00:27', '2026-05-30 15:00:27'),
(40, 33, 300.00, 'cash', NULL, '2026-05-30 18:14:15', '2026-05-30 15:14:15'),
(41, 46, 999.00, 'cash', NULL, '2026-06-08 17:51:24', '2026-06-08 14:51:24');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `appointment_id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'super_admin', '2026-05-14 13:39:07', '2026-05-14 13:39:07'),
(2, 'Owner', 'owner', '2026-05-14 13:39:07', '2026-05-14 13:39:07'),
(3, 'Manager', 'manager', '2026-05-14 13:39:07', '2026-05-14 13:39:07'),
(4, 'Receptionist', 'receptionist', '2026-05-14 13:39:07', '2026-05-14 13:39:07'),
(5, 'Barber', 'barber', '2026-05-14 13:39:07', '2026-05-14 13:39:07'),
(6, 'Customer', 'customer', '2026-05-14 13:39:07', '2026-05-14 13:39:07');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `services`
--

CREATE TABLE `services` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `duration_minutes` int(11) NOT NULL,
  `buffer_time` int(11) DEFAULT 0,
  `price` decimal(10,2) NOT NULL,
  `discounted_price` decimal(10,2) DEFAULT NULL,
  `gender_type` enum('male','female','unisex') DEFAULT 'unisex',
  `image` varchar(255) DEFAULT NULL,
  `is_popular` tinyint(1) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `services`
--

INSERT INTO `services` (`id`, `branch_id`, `category_id`, `name`, `slug`, `description`, `duration_minutes`, `buffer_time`, `price`, `discounted_price`, `gender_type`, `image`, `is_popular`, `is_featured`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'Modern Saç Kesimi', 'modern-sac-kesimi', 'Modern fade ve profesyonel saç kesimi.', 30, 10, 500.00, 450.00, 'male', 'haircut_1.png', 1, 1, 1, '2026-05-14 13:44:44', '2026-05-14 22:32:31', NULL),
(2, 1, 2, 'Sakal Tasarımı', 'sakal-tasarimi', 'Profesyonel sakal şekillendirme.', 30, 5, 300.00, NULL, 'male', 'beard_1.png', 0, 0, 1, '2026-05-14 13:44:44', '2026-05-23 17:16:37', NULL),
(3, 1, 3, 'VIP Full Paket', 'vip-full-paket', 'Saç + sakal + bakım + maske.', 90, 15, 1200.00, NULL, 'male', 'vip_1.png', 1, 1, 1, '2026-05-14 13:44:44', '2026-05-23 17:16:20', NULL),
(4, 1, 4, 'Yüz Bakımı', 'yuz-bakimi', 'Derin cilt temizliği.', 40, 10, 600.00, NULL, 'unisex', 'care_1.png', 0, 0, 1, '2026-05-14 13:44:44', '2026-05-14 17:34:01', NULL),
(5, 1, 1, 'Çocuk Saç Kesimi', 'cocuk-sac-kesimi', 'Çocuk müşteriler için saç kesimi.', 30, 5, 350.00, NULL, 'male', 'child_1.png', 0, 0, 1, '2026-05-14 13:44:44', '2026-05-14 13:44:44', NULL),
(6, 1, 4, 'Fön Çekimi', 'fon-cekimi', 'Saça şekil vermek bizim işimiz. Sizi biz FÖNleyelim.', 30, 0, 250.00, NULL, 'male', NULL, 0, 0, 0, '2026-05-18 13:28:28', '2026-05-23 15:19:10', NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `service_categories`
--

CREATE TABLE `service_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `service_categories`
--

INSERT INTO `service_categories` (`id`, `branch_id`, `name`, `description`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Saç', 'Profesyonel saç hizmetleri', 1, 1, '2026-05-14 13:44:44', '2026-05-18 13:29:36'),
(2, 1, 'Sakal', 'Sakal tasarım ve bakım hizmetleri', 2, 1, '2026-05-14 13:44:44', '2026-05-14 13:44:44'),
(3, 1, 'VIP Paketler', 'Premium bakım paketleri', 3, 1, '2026-05-14 13:44:44', '2026-05-14 13:44:44'),
(4, 1, 'Bakım', 'Cilt ve saç bakım hizmetleri', 4, 1, '2026-05-14 13:44:44', '2026-05-14 13:44:44');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('3tpNs0mAqhMhTC7rUcJJBymzmQqGTNPjB4AY4R2O', 1, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJlVEFuRUZTajNrNVE2WVJKQ0lJVU9GaGRUeE5zc2Z4QXNPS1ZCeU9mIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9kYXNoYm9hcmQiLCJyb3V0ZSI6ImRhc2hib2FyZCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxfQ==', 1778775334),
('ZPRII3lMMx4GzXIsKFOVV9cKg8rfGZL9cJhRhXmu', 1, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJHODFNUDROOU1aUmdlaHRFZDlvY2FuN01CRGl6ZTZ0a3Noa05lZktrIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC9kYXNoYm9hcmQiLCJyb3V0ZSI6ImRhc2hib2FyZCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxfQ==', 1778775247);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `setting_key` varchar(150) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'app_name', 'B&V App', 'Application name', '2026-05-14 13:39:07', '2026-05-14 13:39:07'),
(2, 'currency', 'TRY', 'Default currency', '2026-05-14 13:39:07', '2026-05-14 13:39:07'),
(3, 'tax_rate', '20', 'Default tax rate', '2026-05-14 13:39:07', '2026-05-14 13:39:07'),
(4, 'appointment_interval', '30', 'Default appointment interval', '2026-05-14 13:39:07', '2026-05-14 13:39:07'),
(5, 'loyalty_enabled', '1', 'Loyalty system enabled', '2026-05-14 13:39:07', '2026-05-14 13:39:07');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `appointment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `expense_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `transaction_type` enum('income','expense','refund') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'TRY',
  `payment_method` enum('cash','credit_card','bank_transfer','online') DEFAULT NULL,
  `description` text DEFAULT NULL,
  `transaction_date` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `transactions`
--

INSERT INTO `transactions` (`id`, `branch_id`, `appointment_id`, `expense_id`, `created_by`, `transaction_type`, `amount`, `currency`, `payment_method`, `description`, `transaction_date`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, NULL, 5, 'income', 450.00, 'TRY', 'credit_card', 'Saç kesimi ödemesi', '2026-05-15 11:45:00', '2026-05-14 13:44:51', '2026-05-14 13:44:51'),
(2, 1, NULL, NULL, 4, 'income', 1000.00, 'TRY', 'cash', 'VIP paket ödemesi', '2026-05-15 14:30:00', '2026-05-14 13:44:51', '2026-05-14 13:44:51'),
(3, 1, NULL, NULL, 2, 'expense', 2500.00, 'TRY', 'bank_transfer', 'Elektrik faturası', '2026-05-10 12:00:00', '2026-05-14 13:44:51', '2026-05-14 13:44:51'),
(4, 1, 53, NULL, 1, 'income', 450.00, 'TRY', 'credit_card', 'Randevu Ödemesi - #BV-W1M8VTSA (MUSTAFA KARA)', '2026-05-18 00:00:00', '2026-05-18 20:03:49', '2026-05-18 20:03:49'),
(5, 1, 54, NULL, 1, 'income', 300.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-Q5RCIUMI (Can Öztürk)', '2026-05-18 00:00:00', '2026-05-18 20:10:37', '2026-05-18 20:10:37'),
(7, 1, 55, NULL, 1, 'income', 300.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-EQZ9ZNYW (Ali Koç)', '2026-05-18 00:00:00', '2026-05-18 20:11:25', '2026-05-18 20:11:25'),
(8, 1, NULL, NULL, 1, 'expense', 886.76, 'TRY', 'cash', 'Gider Harcaması - Malzeme (Deneme olarak ödeme kaydı.)', '2026-05-18 00:00:00', '2026-05-18 20:13:28', '2026-05-18 20:13:28'),
(9, 1, NULL, NULL, 1, 'expense', 50.00, 'TRY', 'bank_transfer', NULL, '2026-05-18 23:14:00', '2026-05-18 20:15:10', '2026-05-18 20:15:10'),
(10, 1, NULL, NULL, 1, 'expense', 100.00, 'TRY', 'cash', 'Gider Harcaması - Elektrik', '2026-05-18 00:00:00', '2026-05-18 20:16:05', '2026-05-18 20:16:05'),
(11, 1, NULL, NULL, 1, 'income', 5000.00, 'TRY', 'cash', 'Bağış (Mustafa KARA)', '2026-05-18 23:16:00', '2026-05-18 20:16:35', '2026-05-18 20:16:35'),
(12, 1, 48, NULL, 1, 'income', 350.00, 'TRY', 'credit_card', 'Randevu Ödemesi - #BV-B9BAEMHU (Mert Şahin)', '2026-05-18 00:00:00', '2026-05-18 20:23:26', '2026-05-18 20:23:26'),
(13, 1, 72, NULL, 1, 'income', 600.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-MFVEGME9 (MUSTAFA KARA)', '2026-05-19 00:00:00', '2026-05-19 12:20:33', '2026-05-19 12:20:33'),
(14, 1, 67, NULL, 1, 'income', 250.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-LXOLM9SV (Sinem DURMAZ)', '2026-05-19 00:00:00', '2026-05-19 12:20:36', '2026-05-19 12:20:36'),
(15, 1, 61, NULL, 1, 'income', 250.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-XCT3C2DR (Sinem DURMAZ)', '2026-05-19 00:00:00', '2026-05-19 12:20:41', '2026-05-19 12:20:41'),
(16, 1, 71, NULL, 1, 'income', 450.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-EJ7WTTMI (MUSTAFA KARA)', '2026-05-19 00:00:00', '2026-05-19 12:20:45', '2026-05-19 12:20:45'),
(17, 1, 70, NULL, 1, 'income', 250.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-2NC42NC1 (Ali Koç)', '2026-05-19 00:00:00', '2026-05-19 12:21:01', '2026-05-19 12:21:01'),
(18, 1, 66, NULL, 1, 'income', 1200.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-VQFW3IZ3 (MUSTAFA KARA)', '2026-05-19 00:00:00', '2026-05-19 12:21:04', '2026-05-19 12:21:04'),
(19, 1, 60, NULL, 1, 'income', 1200.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-4VAF0O2R (MUSTAFA KARA)', '2026-05-19 00:00:00', '2026-05-19 12:21:14', '2026-05-19 12:21:14'),
(20, 1, 56, NULL, 1, 'income', 450.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-MWPXXPXW (Can Öztürk)', '2026-05-19 00:00:00', '2026-05-19 12:21:17', '2026-05-19 12:21:17'),
(21, 1, 57, NULL, 1, 'income', 450.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-8F4MO8ST (Mert Şahin)', '2026-05-19 00:00:00', '2026-05-19 12:21:21', '2026-05-19 12:21:21'),
(22, 1, 65, NULL, 1, 'income', 1200.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-S8EXQGU0 (Görkem FİDAN)', '2026-05-19 00:00:00', '2026-05-19 12:21:23', '2026-05-19 12:21:23'),
(23, 1, 64, NULL, 1, 'income', 600.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-ZYVOVHSW (Ali Koç)', '2026-05-19 00:00:00', '2026-05-19 12:21:26', '2026-05-19 12:21:26'),
(24, 1, 63, NULL, 1, 'income', 300.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-YTLF8FBY (Mert Şahin)', '2026-05-19 00:00:00', '2026-05-19 12:21:30', '2026-05-19 12:21:30'),
(25, 1, 69, NULL, 1, 'income', 1200.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-LPUCFVYV (Mert Şahin)', '2026-05-19 00:00:00', '2026-05-19 12:21:36', '2026-05-19 12:21:36'),
(26, 1, 62, NULL, 1, 'income', 350.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-MGISHUK0 (Can Öztürk)', '2026-05-19 00:00:00', '2026-05-19 12:21:40', '2026-05-19 12:21:40'),
(27, 1, 68, NULL, 1, 'income', 300.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-6EJB0ONG (Can Öztürk)', '2026-05-19 00:00:00', '2026-05-19 12:21:43', '2026-05-19 12:21:43'),
(28, 1, 74, NULL, 1, 'income', 250.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-U8WZUDHA (Sinem DURMAZ)', '2026-05-19 16:05:14', '2026-05-19 13:05:14', '2026-05-19 13:05:14'),
(29, 1, NULL, 5, 1, 'expense', 12000.00, 'TRY', 'bank_transfer', 'Gider Harcaması - Maaş', '2026-05-19 00:00:00', '2026-05-19 13:13:47', '2026-05-19 13:13:47'),
(30, 1, NULL, 6, 1, 'expense', 10.00, 'TRY', 'cash', 'Gider Harcaması - Malzeme (Deneme gider bilgisi.)', '2026-05-19 00:00:00', '2026-05-19 13:56:13', '2026-05-19 13:56:13'),
(31, 1, 78, NULL, 1, 'income', 450.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-JOTZC65O (Can Öztürk)', '2026-05-19 17:36:56', '2026-05-19 14:36:56', '2026-05-19 14:36:56'),
(32, 1, 73, NULL, 1, 'income', 450.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-ICYB5NSS (MUSTAFA KARA)', '2026-05-23 12:51:43', '2026-05-23 09:51:43', '2026-05-23 09:51:43'),
(33, 1, 82, NULL, 1, 'income', 350.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-P63QWF3C (Sinem DURMAZ)', '2026-05-23 12:51:47', '2026-05-23 09:51:47', '2026-05-23 09:51:47'),
(34, 1, 83, NULL, 1, 'income', 600.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-WCVQVBGY (Ali Koç)', '2026-05-23 12:51:49', '2026-05-23 09:51:49', '2026-05-23 09:51:49'),
(35, 1, 80, NULL, 1, 'income', 250.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-TT3BJVTZ (Görkem FİDAN)', '2026-05-20 00:00:00', '2026-05-23 11:54:19', '2026-05-23 11:54:19'),
(36, 1, 86, NULL, 1, 'income', 450.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-W4HNBJVV (Ali Koç)', '2026-05-22 00:00:00', '2026-05-23 11:54:31', '2026-05-23 11:54:31'),
(37, 1, 85, NULL, 1, 'income', 450.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-P7WSF4YZ (Mert Şahin)', '2026-05-23 14:54:50', '2026-05-23 11:54:50', '2026-05-23 11:54:50'),
(38, 1, 84, NULL, 1, 'income', 450.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-XP9KSSWN (Can Öztürk)', '2026-05-23 14:55:34', '2026-05-23 11:55:34', '2026-05-23 11:55:34'),
(39, 1, 87, NULL, 1, 'income', 350.00, 'TRY', 'credit_card', 'Randevu Ödemesi - #BV-Y8YDFRI0 (Ali Koç)', '2026-05-23 20:09:57', '2026-05-23 17:09:57', '2026-05-23 17:09:57'),
(40, 1, 91, NULL, 1, 'income', 450.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-L8GWWXPH (Mert Şahin)', '2026-05-23 20:10:08', '2026-05-23 17:10:08', '2026-05-23 17:10:08'),
(41, 1, 92, NULL, 1, 'income', 250.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-BZFILNCU (Ali Koç)', '2026-05-23 20:10:50', '2026-05-23 17:10:50', '2026-05-23 17:10:50'),
(42, 1, 90, NULL, 1, 'income', 450.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-JBY1DENA (MUSTAFA KARA)', '2026-05-23 20:10:55', '2026-05-23 17:10:55', '2026-05-23 17:10:55'),
(43, 1, NULL, NULL, 1, 'income', 150.00, 'TRY', 'cash', 'ıughılu', '2026-05-23 20:17:00', '2026-05-23 17:17:20', '2026-05-23 17:17:20'),
(44, 1, NULL, 7, 1, 'expense', 15000.00, 'TRY', 'cash', 'Gider Harcaması - Kira (Dükkan Kirası)', '2026-05-23 00:00:00', '2026-05-23 17:19:34', '2026-05-23 17:19:34'),
(45, 1, 94, NULL, 1, 'income', 300.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-RCQ2DFU2 (Mert Şahin)', '2026-05-25 13:56:41', '2026-05-25 10:56:41', '2026-05-25 10:56:41'),
(46, 1, 88, NULL, 1, 'income', 450.00, 'TRY', 'cash', 'Randevu Ödemesi - #BV-8SB9FNX8 (Görkem FİDAN)', '2026-05-25 13:56:44', '2026-05-25 10:56:44', '2026-05-25 10:56:44'),
(47, 1, 52, NULL, 1, 'income', 450.00, 'TRY', 'cash', 'Borç Tahsilatı - Mert Şahin (#BV-OHOIQNBJ)', '2026-05-30 18:00:14', '2026-05-30 15:00:14', '2026-05-30 15:00:14'),
(48, 1, 47, NULL, 1, 'income', 300.00, 'TRY', 'cash', 'Borç Tahsilatı - Görkem FİDAN (#BV-VJ8PDIAV)', '2026-05-30 18:00:27', '2026-05-30 15:00:27', '2026-05-30 15:00:27'),
(49, 1, 33, NULL, 1, 'income', 300.00, 'TRY', 'cash', 'Borç Tahsilatı - Can Öztürk (#APT-3010)', '2026-05-30 18:14:15', '2026-05-30 15:14:15', '2026-05-30 15:14:15'),
(50, 1, 46, NULL, 1, 'income', 999.00, 'TRY', 'cash', 'Borç Tahsilatı - Görkem FİDAN (#BV-V43BVGP4)', '2026-06-08 17:51:24', '2026-06-08 14:51:24', '2026-06-08 14:51:24');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive','blocked','deleted') DEFAULT 'active',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`id`, `uuid`, `role_id`, `first_name`, `last_name`, `email`, `phone`, `password`, `gender`, `birth_date`, `profile_photo`, `email_verified_at`, `phone_verified_at`, `last_login_at`, `status`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '107e093a-4f9b-11f1-98d5-2e5e95d91c8b', 1, 'Mustafa', 'Kara', 'admin@bvbarber.com', '05554443322', '$2y$12$fEkyZNWK6pFExjKlPWpgtu21TtVSe7qDGzBIAH9EnrtHG6pCL.2Zi', 'male', '2000-01-01', NULL, NULL, NULL, '2026-06-08 14:45:30', 'active', NULL, '2026-05-14 13:44:44', '2026-06-08 14:45:30', NULL),
(2, '107e6100-4f9b-11f1-98d5-2e5e95d91c8b', 5, 'Ahmet', 'Yılmaz', 'owner@bvbarber.com', '+905552222222', '$2y$10$abcdefghijklmnopqrstuv', 'male', '1990-03-12', NULL, NULL, NULL, NULL, 'active', NULL, '2026-05-14 13:44:44', '2026-05-19 13:00:57', NULL),
(3, '107e7186-4f9b-11f1-98d5-2e5e95d91c8b', 5, 'Emre', 'Demir', 'emre@bvbarber.com', '+905553333333', '$2y$10$abcdefghijklmnopqrstuv', 'male', '1995-06-15', NULL, NULL, NULL, NULL, 'active', NULL, '2026-05-14 13:44:44', '2026-05-14 13:44:44', NULL),
(4, '107e7208-4f9b-11f1-98d5-2e5e95d91c8b', 5, 'Burak', 'Kaya', 'burak@bvbarber.com', '+905554444444', '$2y$10$abcdefghijklmnopqrstuv', 'male', '1993-08-22', NULL, NULL, NULL, NULL, 'active', NULL, '2026-05-14 13:44:44', '2026-05-14 13:44:44', NULL),
(5, '107e726c-4f9b-11f1-98d5-2e5e95d91c8b', 4, 'Selin', 'Aydın', 'selin@bvbarber.com', '+905555555555', '$2y$10$abcdefghijklmnopqrstuv', 'female', '1997-04-11', NULL, NULL, NULL, NULL, 'active', NULL, '2026-05-14 13:44:44', '2026-05-14 13:44:44', NULL),
(6, '107e72c6-4f9b-11f1-98d5-2e5e95d91c8b', 6, 'Can', 'Öztürk', 'can@example.com', '+905556666666', '$2y$10$abcdefghijklmnopqrstuv', 'male', '2001-05-20', NULL, NULL, NULL, NULL, 'active', NULL, '2026-05-14 13:44:44', '2026-05-14 13:44:44', NULL),
(7, '107e7316-4f9b-11f1-98d5-2e5e95d91c8b', 6, 'Mert', 'Şahin', 'mert@example.com', '+905557777777', '$2y$10$abcdefghijklmnopqrstuv', 'male', '1998-11-18', NULL, NULL, NULL, NULL, 'active', NULL, '2026-05-14 13:44:44', '2026-05-14 13:44:44', NULL),
(8, '107e7366-4f9b-11f1-98d5-2e5e95d91c8b', 6, 'Ali', 'Koç', 'ali@example.com', '+905558888888', '$2y$10$abcdefghijklmnopqrstuv', 'male', '2002-07-09', NULL, NULL, NULL, NULL, 'active', NULL, '2026-05-14 13:44:44', '2026-05-14 13:44:44', NULL),
(9, 'ad3f020b-1a57-434d-8672-d5cd49c81950', 6, 'Görkem', 'FİDAN', 'grkmfdn55@gmail.com', '05555555555', '$2y$12$ruWbuIK7clDcKiPQI6b24O1J2tDOvUfObY8vaOtr.Kp8VLnOWwHLG', 'male', '2000-01-01', NULL, NULL, NULL, NULL, 'active', NULL, '2026-05-14 17:32:59', '2026-05-14 17:32:59', NULL),
(10, '27e96822-8cb0-4b7a-b4e1-083f4f05d36f', 6, 'MUSTAFA', 'KARA', 'mustafakara200533@gmail.com', '5528120412', '$2y$12$ezy9r0EIgMECj4BxSEPWDOg2oR0BjexJWGvNNtk8nGD.9dpL.7UwS', 'male', '2005-12-04', NULL, NULL, NULL, NULL, 'active', NULL, '2026-05-18 13:26:34', '2026-05-18 13:26:34', NULL),
(11, '0688d9ee-eb10-43cf-8348-4a76908e380b', 5, 'Deneme', 'Çalışanı', 'deneme@mail.com', '05525525252', '$2y$12$x1z6BHucOz4VL.DyyYrHFObTWuxE1Dtq7yIDNbfZacaTAkOO0Jx3a', NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-05-18 13:36:45', '2026-05-18 13:36:45', NULL),
(12, '17366b11-e10f-4566-875d-2892a3fb7344', 6, 'Sinem', 'DURMAZ', 'sinem@mail.com', '05555555123', '$2y$12$J/Hk26PFda6oy9CAEtbo0OA9elHtZa86z3V8yO5HzkmH5m778hVz2', 'female', '2005-01-17', NULL, NULL, NULL, NULL, 'active', NULL, '2026-05-18 15:09:30', '2026-05-18 15:09:30', NULL);

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `appointment_code` (`appointment_code`),
  ADD KEY `fk_appointments_cancelled_by` (`cancelled_by`),
  ADD KEY `fk_appointments_created_by` (`created_by`),
  ADD KEY `idx_appointments_employee_date` (`employee_id`,`start_at`),
  ADD KEY `idx_appointments_customer` (`customer_id`),
  ADD KEY `idx_appointments_status` (`status`),
  ADD KEY `idx_appointments_branch_date` (`branch_id`,`start_at`),
  ADD KEY `idx_appointments_payment_status` (`payment_status`);

--
-- Tablo için indeksler `appointment_services`
--
ALTER TABLE `appointment_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_appointment_services_appointment` (`appointment_id`),
  ADD KEY `fk_appointment_services_service` (`service_id`),
  ADD KEY `fk_appointment_services_employee` (`employee_id`);

--
-- Tablo için indeksler `appointment_status_logs`
--
ALTER TABLE `appointment_status_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_appointment_status_logs_appointment` (`appointment_id`),
  ADD KEY `fk_appointment_status_logs_user` (`changed_by`);

--
-- Tablo için indeksler `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_audit_logs_user` (`user_id`);

--
-- Tablo için indeksler `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Tablo için indeksler `branch_settings`
--
ALTER TABLE `branch_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_branch_settings_branch` (`branch_id`);

--
-- Tablo için indeksler `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Tablo için indeksler `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Tablo için indeksler `campaigns`
--
ALTER TABLE `campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_campaigns_branch` (`branch_id`);

--
-- Tablo için indeksler `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `fk_coupons_campaign` (`campaign_id`);

--
-- Tablo için indeksler `customer_notes`
--
ALTER TABLE `customer_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_customer_notes_customer` (`customer_id`),
  ADD KEY `fk_customer_notes_employee` (`employee_id`);

--
-- Tablo için indeksler `debts`
--
ALTER TABLE `debts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `debts_branch_id_foreign` (`branch_id`),
  ADD KEY `debts_customer_id_foreign` (`customer_id`),
  ADD KEY `debts_appointment_id_foreign` (`appointment_id`);

--
-- Tablo için indeksler `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_devices_user` (`user_id`);

--
-- Tablo için indeksler `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_code` (`employee_code`),
  ADD KEY `idx_employees_branch` (`branch_id`),
  ADD KEY `idx_employees_user` (`user_id`),
  ADD KEY `idx_employees_active` (`is_active`);

--
-- Tablo için indeksler `employee_leaves`
--
ALTER TABLE `employee_leaves`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_employee_leaves_employee` (`employee_id`),
  ADD KEY `fk_employee_leaves_approved_by` (`approved_by`);

--
-- Tablo için indeksler `employee_schedules`
--
ALTER TABLE `employee_schedules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_employee_workdate` (`employee_id`,`work_date`),
  ADD KEY `idx_employee_schedules_date` (`work_date`);

--
-- Tablo için indeksler `employee_services`
--
ALTER TABLE `employee_services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_employee_service` (`employee_id`,`service_id`),
  ADD KEY `fk_employee_services_service` (`service_id`);

--
-- Tablo için indeksler `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_expenses_branch` (`branch_id`),
  ADD KEY `fk_expenses_category` (`category_id`),
  ADD KEY `fk_expenses_created_by` (`created_by`);

--
-- Tablo için indeksler `expense_categories`
--
ALTER TABLE `expense_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_expense_categories_branch` (`branch_id`);

--
-- Tablo için indeksler `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Tablo için indeksler `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Tablo için indeksler `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `loyalty_accounts`
--
ALTER TABLE `loyalty_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_id` (`customer_id`);

--
-- Tablo için indeksler `loyalty_transactions`
--
ALTER TABLE `loyalty_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_loyalty_transactions_account` (`loyalty_account_id`);

--
-- Tablo için indeksler `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user_read` (`user_id`,`is_read`);

--
-- Tablo için indeksler `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Tablo için indeksler `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_payments_appointment` (`appointment_id`);

--
-- Tablo için indeksler `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Tablo için indeksler `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_tokenable` (`tokenable_type`,`tokenable_id`);

--
-- Tablo için indeksler `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reviews_appointment` (`appointment_id`),
  ADD KEY `fk_reviews_customer` (`customer_id`),
  ADD KEY `fk_reviews_employee` (`employee_id`);

--
-- Tablo için indeksler `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Tablo için indeksler `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_role_permission` (`role_id`,`permission_id`),
  ADD KEY `fk_role_permissions_permission` (`permission_id`);

--
-- Tablo için indeksler `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_service_slug_branch` (`branch_id`,`slug`),
  ADD KEY `idx_services_branch` (`branch_id`),
  ADD KEY `idx_services_category` (`category_id`),
  ADD KEY `idx_services_active` (`is_active`);

--
-- Tablo için indeksler `service_categories`
--
ALTER TABLE `service_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_service_categories_branch` (`branch_id`);

--
-- Tablo için indeksler `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Tablo için indeksler `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Tablo için indeksler `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_transactions_appointment` (`appointment_id`),
  ADD KEY `fk_transactions_created_by` (`created_by`),
  ADD KEY `idx_transactions_branch_date` (`branch_id`,`transaction_date`),
  ADD KEY `idx_transactions_type` (`transaction_type`),
  ADD KEY `fk_transactions_expense` (`expense_id`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD KEY `idx_users_role` (`role_id`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_phone` (`phone`),
  ADD KEY `idx_users_status` (`status`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- Tablo için AUTO_INCREMENT değeri `appointment_services`
--
ALTER TABLE `appointment_services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- Tablo için AUTO_INCREMENT değeri `appointment_status_logs`
--
ALTER TABLE `appointment_status_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=185;

--
-- Tablo için AUTO_INCREMENT değeri `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=339;

--
-- Tablo için AUTO_INCREMENT değeri `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `branch_settings`
--
ALTER TABLE `branch_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `campaigns`
--
ALTER TABLE `campaigns`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `customer_notes`
--
ALTER TABLE `customer_notes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `debts`
--
ALTER TABLE `debts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Tablo için AUTO_INCREMENT değeri `devices`
--
ALTER TABLE `devices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Tablo için AUTO_INCREMENT değeri `employee_leaves`
--
ALTER TABLE `employee_leaves`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `employee_schedules`
--
ALTER TABLE `employee_schedules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Tablo için AUTO_INCREMENT değeri `employee_services`
--
ALTER TABLE `employee_services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Tablo için AUTO_INCREMENT değeri `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Tablo için AUTO_INCREMENT değeri `expense_categories`
--
ALTER TABLE `expense_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Tablo için AUTO_INCREMENT değeri `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `loyalty_accounts`
--
ALTER TABLE `loyalty_accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `loyalty_transactions`
--
ALTER TABLE `loyalty_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- Tablo için AUTO_INCREMENT değeri `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Tablo için AUTO_INCREMENT değeri `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Tablo için AUTO_INCREMENT değeri `service_categories`
--
ALTER TABLE `service_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Tablo için AUTO_INCREMENT değeri `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Tablo için AUTO_INCREMENT değeri `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_appointments_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_appointments_cancelled_by` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_appointments_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_appointments_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_appointments_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `appointment_services`
--
ALTER TABLE `appointment_services`
  ADD CONSTRAINT `fk_appointment_services_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_appointment_services_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_appointment_services_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `appointment_status_logs`
--
ALTER TABLE `appointment_status_logs`
  ADD CONSTRAINT `fk_appointment_status_logs_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_appointment_status_logs_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Tablo kısıtlamaları `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Tablo kısıtlamaları `branch_settings`
--
ALTER TABLE `branch_settings`
  ADD CONSTRAINT `fk_branch_settings_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `campaigns`
--
ALTER TABLE `campaigns`
  ADD CONSTRAINT `fk_campaigns_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `coupons`
--
ALTER TABLE `coupons`
  ADD CONSTRAINT `fk_coupons_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `customer_notes`
--
ALTER TABLE `customer_notes`
  ADD CONSTRAINT `fk_customer_notes_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_customer_notes_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `debts`
--
ALTER TABLE `debts`
  ADD CONSTRAINT `debts_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `debts_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `debts_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `devices`
--
ALTER TABLE `devices`
  ADD CONSTRAINT `fk_devices_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `fk_employees_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_employees_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `employee_leaves`
--
ALTER TABLE `employee_leaves`
  ADD CONSTRAINT `fk_employee_leaves_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_employee_leaves_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `employee_schedules`
--
ALTER TABLE `employee_schedules`
  ADD CONSTRAINT `fk_employee_schedules_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `employee_services`
--
ALTER TABLE `employee_services`
  ADD CONSTRAINT `fk_employee_services_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_employee_services_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `fk_expenses_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_expenses_category` FOREIGN KEY (`category_id`) REFERENCES `expense_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_expenses_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Tablo kısıtlamaları `expense_categories`
--
ALTER TABLE `expense_categories`
  ADD CONSTRAINT `fk_expense_categories_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `loyalty_accounts`
--
ALTER TABLE `loyalty_accounts`
  ADD CONSTRAINT `fk_loyalty_accounts_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `loyalty_transactions`
--
ALTER TABLE `loyalty_transactions`
  ADD CONSTRAINT `fk_loyalty_transactions_account` FOREIGN KEY (`loyalty_account_id`) REFERENCES `loyalty_accounts` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reviews_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reviews_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `fk_services_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_services_category` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`) ON DELETE SET NULL;

--
-- Tablo kısıtlamaları `service_categories`
--
ALTER TABLE `service_categories`
  ADD CONSTRAINT `fk_service_categories_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transactions_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_transactions_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_transactions_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_transactions_expense` FOREIGN KEY (`expense_id`) REFERENCES `expenses` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
