-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 19, 2025 at 04:30 PM
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
-- Database: `legacy_billiard`
--

-- --------------------------------------------------------

--
-- Table structure for table `booked_slots`
--

CREATE TABLE `booked_slots` (
  `id` int(11) NOT NULL,
  `meja_id` int(11) NOT NULL,
  `tanggal_booking` date NOT NULL,
  `waktu_mulai` time NOT NULL,
  `waktu_selesai` time NOT NULL,
  `booking_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booked_slots`
--

INSERT INTO `booked_slots` (`id`, `meja_id`, `tanggal_booking`, `waktu_mulai`, `waktu_selesai`, `booking_id`) VALUES
(1, 3, '2026-02-12', '13:00:00', '16:00:00', 1),
(2, 2, '2025-12-08', '21:00:00', '00:00:00', 2),
(3, 3, '2025-12-12', '12:00:00', '14:00:00', 3),
(4, 3, '2025-12-12', '13:00:00', '16:00:00', 4),
(5, 3, '2025-12-11', '11:00:00', '13:00:00', 5),
(6, 4, '2025-12-19', '17:00:00', '21:00:00', 6),
(7, 3, '2025-12-09', '16:00:00', '19:00:00', 7),
(8, 3, '2025-12-12', '14:00:00', '17:00:00', 8),
(9, 3, '2025-12-25', '12:00:00', '14:00:00', 9);

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `meja_id` int(11) NOT NULL,
  `nama_pemesan` varchar(100) NOT NULL,
  `telepon_pemesan` varchar(20) NOT NULL,
  `tanggal_booking` date NOT NULL,
  `waktu_mulai` time NOT NULL,
  `durasi_jam` int(11) NOT NULL,
  `paket_type` enum('perjam','promo_siang','promo_malam') NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `voucher_code` varchar(50) DEFAULT NULL,
  `diskon` decimal(10,2) DEFAULT 0.00,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `payment_status` enum('unpaid','paid') DEFAULT 'unpaid',
  `payment_method` varchar(50) DEFAULT 'cash',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`id`, `user_id`, `meja_id`, `nama_pemesan`, `telepon_pemesan`, `tanggal_booking`, `waktu_mulai`, `durasi_jam`, `paket_type`, `total_harga`, `voucher_code`, `diskon`, `status`, `payment_status`, `payment_method`, `created_at`, `updated_at`) VALUES
(1, NULL, 3, 'filbert', '+628123123123', '2026-02-12', '13:00:00', 3, 'promo_siang', 60000.00, NULL, 0.00, 'pending', 'unpaid', 'cash', '2025-12-04 11:44:05', '2025-12-04 11:44:05'),
(2, NULL, 2, 'filbert', '+628123123123', '2025-12-08', '21:00:00', 3, 'promo_siang', 50000.00, 'GRATIS10K', 10000.00, 'pending', 'unpaid', 'cash', '2025-12-04 11:44:05', '2025-12-04 11:44:05'),
(3, NULL, 3, 'filbert', '+628123123123', '2025-12-12', '12:00:00', 2, 'perjam', 110000.00, NULL, 0.00, 'pending', 'unpaid', 'cash', '2025-12-04 12:51:56', '2025-12-04 12:51:56'),
(4, NULL, 3, 'filbert', '+628123123123', '2025-12-12', '13:00:00', 3, 'promo_siang', 60000.00, NULL, 0.00, 'pending', 'unpaid', 'cash', '2025-12-04 12:53:56', '2025-12-04 12:53:56'),
(5, NULL, 3, 'filbert', '+628123123123', '2025-12-11', '11:00:00', 2, 'perjam', 68000.00, 'NEWMEMBER', 12000.00, 'pending', 'unpaid', 'cash', '2025-12-04 12:54:31', '2025-12-04 12:54:31'),
(6, NULL, 4, 'filbert', '+628123123123', '2025-12-19', '17:00:00', 4, 'perjam', 176000.00, 'DISKON20', 44000.00, 'pending', 'unpaid', 'cash', '2025-12-04 12:55:26', '2025-12-04 12:55:26'),
(7, NULL, 3, 'filbert', '+628123123123', '2025-12-09', '16:00:00', 3, 'perjam', 96000.00, 'DISKON20', 24000.00, 'pending', 'unpaid', 'cash', '2025-12-04 12:57:36', '2025-12-04 12:57:36'),
(8, NULL, 3, 'filbert', '+628123123123', '2025-12-12', '14:00:00', 3, 'promo_siang', 50000.00, 'GRATIS10K', 10000.00, 'pending', 'unpaid', 'cash', '2025-12-04 18:21:39', '2025-12-04 18:21:39'),
(9, NULL, 3, 'filbert', '082222222222', '2025-12-25', '12:00:00', 2, 'perjam', 70000.00, 'GRATIS10K', 10000.00, 'confirmed', 'unpaid', 'cash', '2025-12-19 15:03:17', '2025-12-19 15:03:17');

-- --------------------------------------------------------

--
-- Table structure for table `cabang`
--

CREATE TABLE `cabang` (
  `id` int(11) NOT NULL,
  `nama_cabang` varchar(50) NOT NULL,
  `alamat` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cabang`
--

INSERT INTO `cabang` (`id`, `nama_cabang`, `alamat`, `created_at`) VALUES
(1, 'BSD', 'Jl. Lingkar Luar Botanika Utara, BSD', '2025-12-03 18:53:00'),
(2, 'Tangerang Kota', 'Jl. Tangerang Kota No. 123', '2025-12-03 18:53:00');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `meja_id` int(11) NOT NULL,
  `nama_meja` varchar(50) NOT NULL,
  `tanggal_booking` date NOT NULL,
  `waktu_mulai` time NOT NULL,
  `durasi_jam` int(11) NOT NULL,
  `paket_type` enum('perjam','promo_siang','promo_malam') NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `voucher_code` varchar(50) DEFAULT NULL,
  `diskon` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gallery_images`
--

CREATE TABLE `gallery_images` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `uploaded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery_images`
--

INSERT INTO `gallery_images` (`id`, `filename`, `title`, `description`, `display_order`, `is_active`, `uploaded_by`, `created_at`) VALUES
(1, 'gallery1.jpg', 'Interior View', 'Modern dan nyaman untuk pengalaman terbaik', 1, 1, NULL, '2025-12-04 14:01:22'),
(2, 'gallery2.jpg', 'Premium Tables', 'Meja billiard standar internasional', 2, 1, NULL, '2025-12-04 14:01:22'),
(3, 'gallery3.jpg', 'Lounge Area', 'Ruang tunggu yang comfortable', 3, 1, NULL, '2025-12-04 14:01:22'),
(4, 'gallery4.jpg', 'Night View', 'Suasana malam yang elegan', 4, 1, NULL, '2025-12-04 14:01:22');

-- --------------------------------------------------------

--
-- Table structure for table `meja`
--

CREATE TABLE `meja` (
  `id` int(11) NOT NULL,
  `cabang_id` int(11) NOT NULL,
  `nama_meja` varchar(50) NOT NULL,
  `lantai` int(11) DEFAULT 1,
  `status` enum('tersedia','terisi','maintenance') DEFAULT 'tersedia',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meja`
--

INSERT INTO `meja` (`id`, `cabang_id`, `nama_meja`, `lantai`, `status`, `created_at`) VALUES
(1, 1, 'Meja 1', 1, 'tersedia', '2025-12-03 18:53:12'),
(2, 1, 'Meja 2', 1, 'tersedia', '2025-12-03 18:53:12'),
(3, 1, 'Meja 3', 1, 'tersedia', '2025-12-03 18:53:12'),
(4, 1, 'Meja 4', 1, 'tersedia', '2025-12-03 18:53:12'),
(5, 1, 'Meja 5', 1, 'tersedia', '2025-12-03 18:53:12'),
(6, 1, 'Meja 6', 1, 'tersedia', '2025-12-03 18:53:12'),
(7, 1, 'Meja 7', 1, 'tersedia', '2025-12-03 18:53:12'),
(8, 1, 'Meja 8', 1, 'tersedia', '2025-12-03 18:53:12'),
(9, 1, 'Meja 9', 1, 'terisi', '2025-12-03 18:53:12'),
(10, 1, 'Meja 10', 2, 'tersedia', '2025-12-03 18:53:12'),
(11, 1, 'Meja 11', 2, 'tersedia', '2025-12-03 18:53:12'),
(12, 1, 'Meja 12', 2, 'tersedia', '2025-12-03 18:53:12'),
(13, 1, 'Meja 13', 2, 'tersedia', '2025-12-03 18:53:12'),
(14, 1, 'Meja 14', 2, 'tersedia', '2025-12-03 18:53:12'),
(15, 1, 'Meja 15', 2, 'tersedia', '2025-12-03 18:53:12'),
(16, 1, 'Meja 16', 2, 'tersedia', '2025-12-03 18:53:12'),
(17, 1, 'Meja 17', 2, 'tersedia', '2025-12-03 18:53:12'),
(18, 1, 'Meja 18', 2, 'tersedia', '2025-12-03 18:53:12'),
(19, 1, 'Meja 19', 3, 'tersedia', '2025-12-03 18:53:12'),
(20, 1, 'Meja 20', 3, 'tersedia', '2025-12-03 18:53:12'),
(21, 1, 'Meja 21', 3, 'tersedia', '2025-12-03 18:53:12'),
(22, 1, 'Meja 22', 3, 'tersedia', '2025-12-03 18:53:12'),
(23, 1, 'Meja 23', 3, 'tersedia', '2025-12-03 18:53:12'),
(24, 1, 'Meja 24', 3, 'tersedia', '2025-12-03 18:53:12'),
(25, 1, 'Meja 25', 3, 'tersedia', '2025-12-03 18:53:12'),
(26, 1, 'Meja 26', 3, 'tersedia', '2025-12-03 18:53:12'),
(27, 1, 'Meja 27', 3, 'tersedia', '2025-12-03 18:53:12'),
(28, 2, 'Meja T1', 1, 'tersedia', '2025-12-03 18:53:12'),
(29, 2, 'Meja T2', 1, 'tersedia', '2025-12-03 18:53:12'),
(30, 2, 'Meja T3', 1, 'tersedia', '2025-12-03 18:53:12'),
(31, 2, 'Meja T4', 1, 'tersedia', '2025-12-03 18:53:12'),
(32, 2, 'Meja T5', 1, 'tersedia', '2025-12-03 18:53:12'),
(33, 2, 'Meja T6', 1, 'tersedia', '2025-12-03 18:53:12'),
(34, 2, 'Meja T7', 1, 'tersedia', '2025-12-03 18:53:12'),
(35, 2, 'Meja T8', 1, 'tersedia', '2025-12-03 18:53:12'),
(36, 2, 'Meja T9', 1, 'tersedia', '2025-12-03 18:53:12'),
(37, 2, 'Meja T10', 1, 'tersedia', '2025-12-03 18:53:12'),
(38, 2, 'Meja T11', 1, 'tersedia', '2025-12-03 18:53:12'),
(39, 2, 'Meja T12', 1, 'tersedia', '2025-12-03 18:53:12');

-- --------------------------------------------------------

--
-- Table structure for table `promo`
--

CREATE TABLE `promo` (
  `id` int(11) NOT NULL,
  `judul` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `harga` int(11) DEFAULT NULL,
  `durasi_jam` int(11) DEFAULT NULL,
  `jenis_promo` enum('promo_siang','promo_malam','spesial') NOT NULL,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `aktif` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `kode_promo` varchar(50) DEFAULT NULL,
  `tipe_diskon` enum('persen','nominal') DEFAULT 'persen',
  `nilai_diskon` decimal(10,2) DEFAULT 0.00,
  `min_pembelian` decimal(10,2) DEFAULT 0.00,
  `max_diskon` decimal(10,2) DEFAULT NULL,
  `kuota_penggunaan` int(11) DEFAULT NULL,
  `jumlah_terpakai` int(11) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promo`
--

INSERT INTO `promo` (`id`, `judul`, `deskripsi`, `gambar`, `harga`, `durasi_jam`, `jenis_promo`, `tanggal_mulai`, `tanggal_selesai`, `aktif`, `created_at`, `kode_promo`, `tipe_diskon`, `nilai_diskon`, `min_pembelian`, `max_diskon`, `kuota_penggunaan`, `jumlah_terpakai`, `created_by`) VALUES
(1, 'Happy Hour Siang', 'Main 3 jam hanya Rp 60.000! Berlaku Senin-Kamis jam 11:00-15:00', 'promo1.jpg', 60000, 3, 'promo_siang', '2024-01-01', '2024-12-31', 1, '2025-12-03 18:53:31', NULL, 'persen', 0.00, 0.00, NULL, NULL, 0, NULL),
(2, 'Promo Malam Weekend', 'Promo spesial malam weekend Rp 100.000 untuk 4 jam', 'promo2.jpg', 100000, 4, 'promo_malam', '2024-01-01', '2024-12-31', 1, '2025-12-03 18:53:31', NULL, 'persen', 0.00, 0.00, NULL, NULL, 0, NULL),
(3, 'Spesial Member', 'Diskon 20% untuk member baru! Daftar sekarang', 'promo3.jpg', 0, 0, 'spesial', '2024-01-01', '2024-12-31', 1, '2025-12-03 18:53:31', NULL, 'persen', 0.00, 0.00, NULL, NULL, 0, NULL),
(4, 'Tournament Weekend', 'Join tournament setiap weekend! Total hadiah 5 juta', 'promo4.jpg', 0, 0, 'spesial', '2024-01-01', '2024-12-31', 1, '2025-12-03 18:53:31', NULL, 'persen', 0.00, 0.00, NULL, NULL, 0, NULL),
(5, 'Diskon 20% Weekend', 'Gunakan kode WEEKEND20 untuk diskon 20%', NULL, NULL, NULL, 'spesial', '2025-01-01', '2025-12-31', 1, '2025-12-03 21:05:28', 'WEEKEND20', 'persen', 20.00, 100000.00, 50000.00, NULL, 0, NULL),
(6, 'Diskon Rp 30.000', 'Potongan langsung Rp 30.000 untuk pembelian min 150rb', NULL, NULL, NULL, 'spesial', '2025-01-01', '2025-12-31', 1, '2025-12-03 21:05:28', 'HEMAT30', 'nominal', 30000.00, 150000.00, NULL, NULL, 0, NULL),
(7, 'Member Baru 15%', 'Diskon 15% untuk member baru', NULL, NULL, NULL, 'spesial', '2025-01-01', '2025-12-31', 1, '2025-12-03 21:05:28', 'NEWMEMBER15', 'persen', 15.00, 50000.00, 25000.00, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','textarea','boolean','number','email','url') DEFAULT 'text',
  `category` varchar(50) DEFAULT 'general',
  `description` varchar(255) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_by`, `updated_at`, `created_at`) VALUES
(1, 'site_name', 'Legacy Billiard', 'text', 'general', 'Nama website', NULL, '2025-12-04 18:19:20', '2025-12-04 18:19:20'),
(2, 'site_description', 'Premium Billiard Center', 'textarea', 'general', 'Deskripsi singkat website', NULL, '2025-12-04 18:19:20', '2025-12-04 18:19:20'),
(3, 'site_email', 'info@legacybilliard.com', 'email', 'contact', 'Email utama website', NULL, '2025-12-04 18:19:20', '2025-12-04 18:19:20'),
(4, 'site_phone', '+62 812-3456-7890', 'text', 'contact', 'Nomor telepon', NULL, '2025-12-04 18:19:20', '2025-12-04 18:19:20'),
(5, 'site_address', 'Jl. Lingkar Luar Botanika Utara, BSD', 'textarea', 'contact', 'Alamat lengkap', NULL, '2025-12-04 18:19:20', '2025-12-04 18:19:20'),
(6, 'maintenance_mode', '1', 'boolean', 'system', 'Mode maintenance (0=off, 1=on)', NULL, '2025-12-04 18:20:55', '2025-12-04 18:19:20'),
(7, 'booking_min_hours', '1', 'number', 'booking', 'Minimum durasi booking (jam)', NULL, '2025-12-04 18:19:20', '2025-12-04 18:19:20'),
(8, 'booking_max_hours', '8', 'number', 'booking', 'Maximum durasi booking (jam)', NULL, '2025-12-04 18:19:20', '2025-12-04 18:19:20'),
(9, 'booking_advance_days', '30', 'number', 'booking', 'Maksimal hari booking di depan', NULL, '2025-12-04 18:19:20', '2025-12-04 18:19:20'),
(10, 'facebook_url', 'https://facebook.com/legacybilliard', 'url', 'social', 'Link Facebook', NULL, '2025-12-04 18:19:20', '2025-12-04 18:19:20'),
(11, 'instagram_url', 'https://instagram.com/legacybilliard', 'url', 'social', 'Link Instagram', NULL, '2025-12-04 18:19:20', '2025-12-04 18:19:20'),
(12, 'whatsapp_number', '6281234567890', 'text', 'contact', 'Nomor WhatsApp (format: 628xxx)', NULL, '2025-12-04 18:19:20', '2025-12-04 18:19:20'),
(13, 'open_hour', '10:00', 'text', 'operational', 'Jam buka', NULL, '2025-12-04 18:19:20', '2025-12-04 18:19:20'),
(14, 'close_hour', '02:00', 'text', 'operational', 'Jam tutup', NULL, '2025-12-04 18:19:20', '2025-12-04 18:19:20'),
(15, 'price_per_hour', '40000', 'number', 'pricing', 'Harga per jam normal', NULL, '2025-12-04 18:19:20', '2025-12-04 18:19:20'),
(16, 'promo_siang_price', '60000', 'number', 'pricing', 'Harga paket promo siang (3 jam)', NULL, '2025-12-04 18:19:20', '2025-12-04 18:19:20'),
(17, 'promo_malam_price', '100000', 'number', 'pricing', 'Harga paket promo malam (4 jam)', NULL, '2025-12-04 18:19:20', '2025-12-04 18:19:20');

-- --------------------------------------------------------

--
-- Table structure for table `tournaments`
--

CREATE TABLE `tournaments` (
  `id` int(11) NOT NULL,
  `nama_tournament` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tanggal_mulai` date NOT NULL,
  `waktu_mulai` time NOT NULL,
  `status` enum('open','ongoing','completed','cancelled') DEFAULT 'open',
  `winner_id` int(11) DEFAULT NULL,
  `max_peserta` int(11) DEFAULT 8,
  `current_peserta` int(11) DEFAULT 0,
  `hadiah` varchar(255) DEFAULT NULL,
  `banner_image` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tournaments`
--

INSERT INTO `tournaments` (`id`, `nama_tournament`, `deskripsi`, `tanggal_mulai`, `waktu_mulai`, `status`, `winner_id`, `max_peserta`, `current_peserta`, `hadiah`, `banner_image`, `created_by`, `created_at`, `updated_at`) VALUES
(2, 'fun games', 'HC 3 ONLY', '2025-12-08', '13:00:00', 'completed', NULL, 8, 0, 'Rp 1.000.000', NULL, 3, '2025-12-04 15:46:09', '2025-12-04 17:41:32'),
(3, 'Fun Games Arisan Part 2', 'kawjdnkwajd', '2025-12-31', '15:00:00', 'open', NULL, 32, 0, '5000000', NULL, 3, '2025-12-19 15:20:11', '2025-12-19 15:20:11');

-- --------------------------------------------------------

--
-- Table structure for table `tournament_matches`
--

CREATE TABLE `tournament_matches` (
  `id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `round` enum('semifinal','final_pool','grand_final') NOT NULL,
  `pool` enum('A','B') DEFAULT NULL,
  `match_number` int(11) NOT NULL COMMENT 'Match sequence in round',
  `player1_id` int(11) DEFAULT NULL,
  `player2_id` int(11) DEFAULT NULL,
  `player1_score` int(11) DEFAULT NULL,
  `player2_score` int(11) DEFAULT NULL,
  `player1_wins` int(11) DEFAULT 0,
  `player2_wins` int(11) DEFAULT 0,
  `winner_id` int(11) DEFAULT NULL,
  `status` enum('pending','ongoing','completed') DEFAULT 'pending',
  `best_of` int(11) DEFAULT 1,
  `game_number` int(11) DEFAULT 1,
  `match_date` datetime DEFAULT NULL,
  `next_match_id` int(11) DEFAULT NULL,
  `played_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tournament_matches`
--

INSERT INTO `tournament_matches` (`id`, `tournament_id`, `round`, `pool`, `match_number`, `player1_id`, `player2_id`, `player1_score`, `player2_score`, `player1_wins`, `player2_wins`, `winner_id`, `status`, `best_of`, `game_number`, `match_date`, `next_match_id`, `played_at`, `created_at`, `completed_at`) VALUES
(37, 2, 'semifinal', 'A', 1, NULL, NULL, 2, 1, 0, 0, NULL, 'completed', 1, 1, NULL, NULL, NULL, '2025-12-04 17:22:51', '2025-12-04 17:40:04'),
(38, 2, 'semifinal', 'A', 2, NULL, NULL, 2, 1, 0, 0, NULL, 'completed', 1, 1, NULL, NULL, NULL, '2025-12-04 17:22:51', '2025-12-04 17:40:19'),
(39, 2, 'semifinal', 'B', 3, NULL, NULL, 1, 2, 0, 0, NULL, 'completed', 1, 1, NULL, NULL, NULL, '2025-12-04 17:22:51', '2025-12-04 17:40:46'),
(40, 2, 'semifinal', 'B', 4, NULL, NULL, 1, 2, 0, 0, NULL, 'completed', 1, 1, NULL, NULL, NULL, '2025-12-04 17:22:51', '2025-12-04 17:41:06'),
(41, 2, 'final_pool', 'A', 1, NULL, NULL, 3, 2, 0, 0, NULL, 'completed', 1, 1, NULL, NULL, NULL, '2025-12-04 17:22:51', '2025-12-04 17:40:34'),
(42, 2, 'final_pool', 'B', 1, NULL, NULL, 3, 2, 0, 0, NULL, 'completed', 1, 1, NULL, NULL, NULL, '2025-12-04 17:22:51', '2025-12-04 17:41:17'),
(43, 2, 'grand_final', NULL, 1, NULL, NULL, 5, 4, 0, 0, NULL, 'completed', 1, 1, NULL, NULL, NULL, '2025-12-04 17:22:51', '2025-12-04 17:41:32');

-- --------------------------------------------------------

--
-- Table structure for table `tournament_notifications`
--

CREATE TABLE `tournament_notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `type` enum('registration','match_start','win','loss','champion') NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tournament_participants`
--

CREATE TABLE `tournament_participants` (
  `id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pool` enum('A','B') NOT NULL,
  `seed_position` int(11) NOT NULL COMMENT '1-4 for each pool',
  `status` enum('active','eliminated','winner','finalist') DEFAULT 'active',
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `password` varchar(255) NOT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT 'default-avatar.jpg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `role`, `password`, `telepon`, `foto_profil`, `created_at`, `updated_at`) VALUES
(3, 'Administrator', 'admin@legacybilliard.com', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+62811000000', 'default-avatar.jpg', '2025-12-04 14:00:17', '2025-12-19 15:28:12');

-- --------------------------------------------------------

--
-- Table structure for table `voucher`
--

CREATE TABLE `voucher` (
  `id` int(11) NOT NULL,
  `kode` varchar(50) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `tipe_diskon` enum('persen','nominal') NOT NULL DEFAULT 'persen',
  `nilai_diskon` decimal(10,2) NOT NULL,
  `min_transaksi` decimal(10,2) DEFAULT 0.00,
  `max_diskon` decimal(10,2) DEFAULT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `kuota` int(11) DEFAULT NULL,
  `terpakai` int(11) DEFAULT 0,
  `aktif` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `voucher`
--

INSERT INTO `voucher` (`id`, `kode`, `nama`, `tipe_diskon`, `nilai_diskon`, `min_transaksi`, `max_diskon`, `tanggal_mulai`, `tanggal_selesai`, `kuota`, `terpakai`, `aktif`, `created_at`) VALUES
(1, 'DISKON10', 'Diskon 10%', 'persen', 10.00, 50000.00, 20000.00, '2025-01-01', '2025-12-31', 100, 0, 1, '2025-12-03 21:36:53'),
(2, 'DISKON20', 'Diskon 20%', 'persen', 20.00, 100000.00, 50000.00, '2025-01-01', '2025-12-31', 50, 0, 1, '2025-12-03 21:36:53'),
(3, 'GRATIS10K', 'Gratis 10 Ribu', 'nominal', 10000.00, 50000.00, NULL, '2025-01-01', '2025-12-31', 200, 0, 1, '2025-12-03 21:36:53'),
(4, 'NEWMEMBER', 'New Member 15%', 'persen', 15.00, 0.00, 30000.00, '2025-01-01', '2025-12-31', NULL, 0, 1, '2025-12-03 21:36:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booked_slots`
--
ALTER TABLE `booked_slots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_slot` (`meja_id`,`tanggal_booking`,`waktu_mulai`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `meja_id` (`meja_id`);

--
-- Indexes for table `cabang`
--
ALTER TABLE `cabang`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `meja_id` (`meja_id`);

--
-- Indexes for table `gallery_images`
--
ALTER TABLE `gallery_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `meja`
--
ALTER TABLE `meja`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cabang_id` (`cabang_id`);

--
-- Indexes for table `promo`
--
ALTER TABLE `promo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_promo_created_by` (`created_by`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `tournaments`
--
ALTER TABLE `tournaments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `fk_tournament_winner` (`winner_id`);

--
-- Indexes for table `tournament_matches`
--
ALTER TABLE `tournament_matches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `player1_id` (`player1_id`),
  ADD KEY `player2_id` (`player2_id`),
  ADD KEY `winner_id` (`winner_id`),
  ADD KEY `idx_tournament_round` (`tournament_id`,`round`),
  ADD KEY `idx_tournament_pool` (`tournament_id`,`pool`);

--
-- Indexes for table `tournament_notifications`
--
ALTER TABLE `tournament_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `tournament_id` (`tournament_id`);

--
-- Indexes for table `tournament_participants`
--
ALTER TABLE `tournament_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_participant` (`tournament_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `voucher`
--
ALTER TABLE `voucher`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode` (`kode`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booked_slots`
--
ALTER TABLE `booked_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `cabang`
--
ALTER TABLE `cabang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `gallery_images`
--
ALTER TABLE `gallery_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `meja`
--
ALTER TABLE `meja`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `promo`
--
ALTER TABLE `promo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `tournaments`
--
ALTER TABLE `tournaments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tournament_matches`
--
ALTER TABLE `tournament_matches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `tournament_notifications`
--
ALTER TABLE `tournament_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tournament_participants`
--
ALTER TABLE `tournament_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `voucher`
--
ALTER TABLE `voucher`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booked_slots`
--
ALTER TABLE `booked_slots`
  ADD CONSTRAINT `booked_slots_ibfk_1` FOREIGN KEY (`meja_id`) REFERENCES `meja` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `booked_slots_ibfk_2` FOREIGN KEY (`booking_id`) REFERENCES `booking` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`meja_id`) REFERENCES `meja` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`meja_id`) REFERENCES `meja` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gallery_images`
--
ALTER TABLE `gallery_images`
  ADD CONSTRAINT `gallery_images_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `meja`
--
ALTER TABLE `meja`
  ADD CONSTRAINT `meja_ibfk_1` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `promo`
--
ALTER TABLE `promo`
  ADD CONSTRAINT `fk_promo_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `settings`
--
ALTER TABLE `settings`
  ADD CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tournaments`
--
ALTER TABLE `tournaments`
  ADD CONSTRAINT `fk_tournament_winner` FOREIGN KEY (`winner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tournaments_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tournament_matches`
--
ALTER TABLE `tournament_matches`
  ADD CONSTRAINT `tournament_matches_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tournament_matches_ibfk_2` FOREIGN KEY (`player1_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tournament_matches_ibfk_3` FOREIGN KEY (`player2_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tournament_matches_ibfk_4` FOREIGN KEY (`winner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tournament_notifications`
--
ALTER TABLE `tournament_notifications`
  ADD CONSTRAINT `tournament_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tournament_notifications_ibfk_2` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tournament_participants`
--
ALTER TABLE `tournament_participants`
  ADD CONSTRAINT `tournament_participants_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tournament_participants_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
