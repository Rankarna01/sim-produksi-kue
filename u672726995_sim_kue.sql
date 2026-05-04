-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 04, 2026 at 03:43 PM
-- Server version: 11.8.6-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u672726995_sim_kue`
--

-- --------------------------------------------------------

--
-- Table structure for table `access_codes`
--

CREATE TABLE `access_codes` (
  `id` int(11) NOT NULL,
  `auth_code` varchar(10) NOT NULL,
  `created_by` int(11) NOT NULL,
  `valid_until` datetime NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `access_codes`
--

INSERT INTO `access_codes` (`id`, `auth_code`, `created_by`, `valid_until`, `is_used`, `created_at`) VALUES
(1, '648530', 1, '2026-04-11 16:55:34', 1, '2026-04-10 14:55:34'),
(2, '375983', 1, '2026-04-12 06:56:32', 0, '2026-04-11 04:56:32'),
(3, '900547', 1, '2026-04-12 08:33:18', 0, '2026-04-11 06:33:18'),
(4, '331410', 1, '2026-04-17 14:57:39', 1, '2026-04-16 12:57:39'),
(5, '186641', 1, '2026-04-19 19:28:15', 0, '2026-04-18 17:28:15'),
(6, '964907', 1, '2026-04-22 05:44:24', 1, '2026-04-21 03:44:24');

-- --------------------------------------------------------

--
-- Table structure for table `barang_keluar`
--

CREATE TABLE `barang_keluar` (
  `id` int(11) NOT NULL,
  `transaction_no` varchar(50) NOT NULL,
  `material_id` int(11) NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `status` enum('Rusak','Expired','Lainnya') DEFAULT 'Rusak',
  `notes` text DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approval_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'approved'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barang_keluar`
--

INSERT INTO `barang_keluar` (`id`, `transaction_no`, `material_id`, `qty`, `status`, `notes`, `user_id`, `created_at`, `approval_status`) VALUES
(1, 'OUT-20260418175753-91', 3, 10.00, 'Expired', '', 13, '2026-04-18 15:57:53', 'approved'),
(2, 'OUT-20260421053106-67', 3, 1.00, 'Rusak', '', 20, '2026-04-21 03:31:06', 'approved'),
(3, 'OUT-20260422061408-37', 3, 10.00, 'Expired', '', 20, '2026-04-22 04:14:08', 'rejected'),
(4, 'OUT-20260422080825-55', 3, 10.00, 'Expired', '', 20, '2026-04-22 06:08:25', 'approved'),
(5, 'OUT-20260422080900-78', 3, 10.00, 'Rusak', '', 20, '2026-04-22 06:09:00', 'rejected'),
(6, 'RET-2026042716294150', 3, 1.00, 'Lainnya', 'Retur ke Supplier (ID PO: 13). Alasan: rusak', 20, '2026-04-27 16:29:41', 'approved'),
(7, 'RET-2026050108464225', 3, 2.00, 'Lainnya', 'Retur ke Supplier (ID PO: 13). Alasan: ok', 20, '2026-05-01 08:46:42', 'approved'),
(8, 'RET-2026050402001359', 3, 1.00, 'Lainnya', 'Retur ke Supplier (ID PO: 13). Alasan: Salah', 20, '2026-05-04 02:00:13', 'approved'),
(9, 'RET-2026050402021567', 3, 2.00, 'Lainnya', 'Retur ke Supplier (ID PO: 13). Alasan: Ok', 20, '2026-05-04 02:02:15', 'approved'),
(10, 'RET-2026050402031112', 3, 2.00, 'Lainnya', 'Retur ke Supplier (ID PO: 13). Alasan: Ok', 20, '2026-05-04 02:03:11', 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `barang_masuk`
--

CREATE TABLE `barang_masuk` (
  `id` int(11) NOT NULL,
  `transaction_no` varchar(50) NOT NULL,
  `material_id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL,
  `source` enum('Manual','PO') DEFAULT 'Manual',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'approved',
  `expiry_date` date DEFAULT NULL,
  `po_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barang_masuk`
--

INSERT INTO `barang_masuk` (`id`, `transaction_no`, `material_id`, `supplier_id`, `qty`, `source`, `status`, `expiry_date`, `po_id`, `notes`, `user_id`, `created_at`) VALUES
(1, 'IN-20260418174734-59', 3, 1, 10.00, 'Manual', 'approved', '2026-04-19', NULL, '', 13, '2026-04-18 15:47:34'),
(2, 'IN-20260418175705-35', 3, 1, 10.00, 'Manual', 'approved', '2026-04-24', NULL, '', 13, '2026-04-18 15:57:05'),
(3, 'LC-19042026-1735', 5, 2, 10.00, 'PO', 'approved', '2026-04-21', 2, 'Penerimaan PO', 13, '2026-04-19 08:28:32'),
(4, 'LC-19042026-4079', 3, 1, 2.00, 'PO', 'approved', NULL, 1, 'Penerimaan PO', 13, '2026-04-19 08:28:44'),
(5, 'LC-19042026-3726', 3, 1, 1.00, 'PO', 'approved', NULL, 3, 'Penerimaan PO', 13, '2026-04-19 08:34:38'),
(6, 'LC-19042026-1779', 3, 2, 1.00, 'PO', 'approved', '2026-04-23', 4, 'Penerimaan PO', 13, '2026-04-19 09:15:13'),
(7, 'LC-20042026-4761', 3, 1, 3.00, 'PO', 'approved', '2026-04-23', 5, 'Penerimaan PO', 20, '2026-04-20 02:35:32'),
(8, 'LC-21042026-6465', 3, 1, 9.00, 'PO', 'approved', '2026-04-30', 6, 'Penerimaan PO', 20, '2026-04-21 02:44:34'),
(9, 'IN-20260421053038-49', 3, NULL, 1.00, 'Manual', 'approved', '2026-04-21', NULL, '', 20, '2026-04-21 03:30:38'),
(10, 'LC-21042026-5977', 3, 1, 1.00, 'PO', 'approved', '2026-04-25', 7, 'Penerimaan PO', 20, '2026-04-21 03:32:24'),
(11, 'IN-20260421123009-34', 3, 1, 10.00, 'Manual', 'approved', '2026-04-30', NULL, '', 20, '2026-04-21 10:30:09'),
(12, 'LC-21042026-7367', 3, 1, 10.00, 'PO', 'approved', '2026-04-25', 8, 'Penerimaan PO', 20, '2026-04-21 11:05:53'),
(13, 'LC-21042026-4730', 3, 2, 10.00, 'PO', 'approved', '2026-04-25', 9, 'Penerimaan PO', 20, '2026-04-21 11:23:31'),
(14, 'IN-20260422055616-54', 3, 1, 100.00, 'Manual', 'approved', '2026-04-30', NULL, '', 20, '2026-04-22 03:56:16'),
(15, 'IN-20260422061431-70', 3, 1, 100.00, 'Manual', 'rejected', '2026-04-22', NULL, '', 20, '2026-04-22 04:14:31'),
(16, 'IN-20260422080607-14', 3, 1, 100.00, 'Manual', 'approved', '2026-04-25', NULL, '', 20, '2026-04-22 06:06:07'),
(17, 'IN-20260422080740-14', 3, 1, 20.00, 'Manual', 'rejected', '2026-04-25', NULL, '', 20, '2026-04-22 06:07:40'),
(18, 'LC-26042026-2279', 3, 2, 2.00, 'PO', 'approved', '2026-04-26', 13, 'Penerimaan PO', 20, '2026-04-26 01:48:54'),
(19, 'LC-26042026-2279', 4, 2, 0.00, 'PO', 'approved', '2026-04-26', 13, 'Penerimaan PO', 20, '2026-04-26 01:48:54'),
(20, 'LC-27042026-4315', 5, 1, 0.00, 'PO', 'approved', '2026-04-28', 15, 'Penerimaan PO', 20, '2026-04-27 19:43:11');

-- --------------------------------------------------------

--
-- Table structure for table `barang_titipan`
--

CREATE TABLE `barang_titipan` (
  `id` int(11) NOT NULL,
  `nama_barang` varchar(150) NOT NULL,
  `nama_umkm` varchar(100) NOT NULL COMMENT 'Nama penitip / supplier',
  `harga_modal` int(11) NOT NULL DEFAULT 0 COMMENT 'Harga setor ke UMKM',
  `harga_jual` int(11) NOT NULL DEFAULT 0 COMMENT 'Harga jual ke konsumen',
  `stok` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barang_titipan`
--

INSERT INTO `barang_titipan` (`id`, `nama_barang`, `nama_umkm`, `harga_modal`, `harga_jual`, `stok`, `created_at`, `updated_at`) VALUES
(1, 'Keripik Pisang Coklat', 'UMKM Bu Tejo', 10000, 15000, 50, '2026-04-23 14:26:22', '2026-04-23 14:26:22'),
(2, 'Kacang Telur Pedas', 'Snack Jaya', 8000, 12000, 13, '2026-04-23 14:26:22', '2026-04-28 16:55:04'),
(3, 'Pempek', 'UMKM BU TUTI', 10000, 12000, 9, '2026-04-23 14:28:02', '2026-04-23 14:29:45');

-- --------------------------------------------------------

--
-- Table structure for table `barang_titipan_keluar`
--

CREATE TABLE `barang_titipan_keluar` (
  `id` int(11) NOT NULL,
  `out_no` varchar(50) NOT NULL,
  `titipan_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `reason` enum('Expired','Rusak','Diretur UMKM','Konsumsi Internal') NOT NULL,
  `notes` text DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barang_titipan_keluar`
--

INSERT INTO `barang_titipan_keluar` (`id`, `out_no`, `titipan_id`, `qty`, `reason`, `notes`, `user_id`, `created_at`) VALUES
(1, 'OUT-TTP-26042716243866', 2, 1, 'Expired', '', 18, '2026-04-27 16:24:38'),
(2, 'OUT-TTP-26042816550495', 2, 12, 'Expired', '', 18, '2026-04-28 16:55:04');

-- --------------------------------------------------------

--
-- Table structure for table `bom`
--

CREATE TABLE `bom` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `quantity_needed` decimal(10,2) NOT NULL,
  `unit_used` varchar(20) DEFAULT 'Gram'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bom`
--

INSERT INTO `bom` (`id`, `product_id`, `material_id`, `quantity_needed`, `unit_used`) VALUES
(23, 6, 3, 100.00, 'Gram'),
(24, 6, 2, 100.00, 'Gram'),
(28, 2, 5, 100.00, 'Gram'),
(29, 2, 4, 50.00, 'Gram'),
(31, 1, 3, 1.00, 'Gram'),
(32, 3, 4, 2.00, 'Kg');

-- --------------------------------------------------------

--
-- Table structure for table `bom_requests`
--

CREATE TABLE `bom_requests` (
  `id` int(11) NOT NULL,
  `request_no` varchar(50) NOT NULL,
  `product_id` int(11) NOT NULL COMMENT 'Produk yang resepnya mau diubah/dibuat',
  `user_id` int(11) NOT NULL COMMENT 'ID Staf yang mengajukan',
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `notes` text DEFAULT NULL COMMENT 'Alasan perubahan resep',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bom_requests`
--

INSERT INTO `bom_requests` (`id`, `request_no`, `product_id`, `user_id`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'BOM-260427-5B61', 3, 1, 'approved', 'bahan utama', '2026-04-27 16:23:35', '2026-04-27 16:23:46'),
(2, 'BOM-260429-9EF6', 2, 16, 'rejected', 'ubah', '2026-04-29 01:25:38', '2026-04-30 16:29:47');

-- --------------------------------------------------------

--
-- Table structure for table `bom_request_details`
--

CREATE TABLE `bom_request_details` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL COMMENT 'ID dari tabel bom_requests',
  `material_id` int(11) NOT NULL COMMENT 'Bahan baku dari Gudang Pilar',
  `quantity_needed` decimal(10,4) NOT NULL,
  `unit_used` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bom_request_details`
--

INSERT INTO `bom_request_details` (`id`, `request_id`, `material_id`, `quantity_needed`, `unit_used`) VALUES
(1, 1, 4, 2.0000, 'Kg'),
(2, 2, 1, 1.0000, 'Kg');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`) VALUES
(1, 'Roti Manis', '2026-04-03 17:00:17'),
(2, 'Roti Tawar', '2026-04-03 17:00:17'),
(3, 'Kue Kering', '2026-04-03 17:00:17'),
(5, 'Bolu', '2026-04-09 17:51:17');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `kitchen_id` int(11) DEFAULT NULL,
  `pin` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `name`, `kitchen_id`, `pin`, `created_at`) VALUES
(1, 'Andi', 1, '1234', '2026-04-01 15:01:06'),
(2, 'Budi', 1, '1234', '2026-04-01 15:01:06'),
(3, 'Siti', 2, '0000', '2026-04-01 15:01:06'),
(4, 'Randy', 2, '0000', '2026-04-01 15:26:29');

-- --------------------------------------------------------

--
-- Table structure for table `gudang_roles`
--

CREATE TABLE `gudang_roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(100) NOT NULL,
  `role_slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gudang_roles`
--

INSERT INTO `gudang_roles` (`id`, `role_name`, `role_slug`, `description`, `created_at`) VALUES
(1, 'Owner Produksi', 'owner_produksi', NULL, '2026-04-22 07:15:48'),
(2, 'Owner Gudang Pilar', 'owner_gudang', NULL, '2026-04-22 07:15:48'),
(3, 'Admin Gudang Utama', 'admin_gudang', NULL, '2026-04-22 07:15:48'),
(4, 'admin-gudang2', 'admin_gudang2', NULL, '2026-04-22 07:24:28');

-- --------------------------------------------------------

--
-- Table structure for table `gudang_role_permissions`
--

CREATE TABLE `gudang_role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_slug` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gudang_role_permissions`
--

INSERT INTO `gudang_role_permissions` (`role_id`, `permission_slug`) VALUES
(2, 'cetak_barcode'),
(2, 'dashboard'),
(2, 'data_opname'),
(2, 'lap_barang_keluar'),
(2, 'lap_barang_masuk'),
(2, 'lap_kartu_stok'),
(2, 'lap_pembayaran_po'),
(2, 'lap_perbandingan_harga'),
(2, 'lap_po'),
(2, 'lap_stok_menipis'),
(2, 'lap_stok_opname'),
(2, 'lap_stok_terbanyak'),
(2, 'lap_supplier'),
(2, 'manage_roles'),
(2, 'manage_users'),
(2, 'master_inventory'),
(2, 'master_kategori'),
(2, 'master_lokasi'),
(2, 'master_satuan'),
(2, 'otorisasi_opname'),
(2, 'pengaturan_karyawan'),
(2, 'pengaturan_pembayaran'),
(2, 'pengaturan_profil'),
(2, 'persetujuan'),
(2, 'persetujuan_izin_cetak'),
(2, 'persetujuan_keluar_manual'),
(2, 'persetujuan_masuk_manual'),
(2, 'persetujuan_po'),
(2, 'persetujuan_pr'),
(2, 'scanner_opname'),
(2, 'trx_barang_keluar'),
(2, 'trx_barang_masuk'),
(2, 'trx_pembayaran'),
(2, 'trx_permintaan_dapur'),
(2, 'trx_po'),
(2, 'trx_supplier'),
(3, 'dashboard'),
(4, 'cetak_barcode'),
(4, 'dashboard'),
(4, 'lap_barang_keluar'),
(4, 'lap_kartu_stok'),
(4, 'lap_perbandingan_harga'),
(4, 'lap_stok_terbanyak'),
(4, 'lap_supplier'),
(4, 'manage_roles'),
(4, 'manage_users'),
(4, 'master_lokasi'),
(4, 'master_satuan'),
(4, 'pengaturan_pembayaran'),
(4, 'pengaturan_profil'),
(4, 'persetujuan_histori'),
(4, 'persetujuan_izin_cetak'),
(4, 'trx_supplier');

-- --------------------------------------------------------

--
-- Table structure for table `gudang_stok_opnames`
--

CREATE TABLE `gudang_stok_opnames` (
  `id` int(11) NOT NULL,
  `opname_no` varchar(50) NOT NULL,
  `opname_date` datetime NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gudang_stok_opnames`
--

INSERT INTO `gudang_stok_opnames` (`id`, `opname_no`, `opname_date`, `status`, `notes`, `created_by`, `approved_by`, `created_at`, `updated_at`) VALUES
(1, 'SO-GDG-20260421060427-894', '2026-04-21 06:04:27', 'approved', NULL, 20, NULL, '2026-04-21 04:04:27', '2026-04-21 04:04:27'),
(2, 'SO-GDG-20260421072610-842', '2026-04-21 07:26:10', 'approved', NULL, 20, NULL, '2026-04-21 05:26:10', '2026-04-21 05:26:10');

-- --------------------------------------------------------

--
-- Table structure for table `gudang_stok_opname_details`
--

CREATE TABLE `gudang_stok_opname_details` (
  `id` int(11) NOT NULL,
  `opname_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `system_stock` decimal(10,2) NOT NULL,
  `physical_stock` decimal(10,2) NOT NULL,
  `difference` decimal(10,2) NOT NULL,
  `notes` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gudang_stok_opname_details`
--

INSERT INTO `gudang_stok_opname_details` (`id`, `opname_id`, `material_id`, `system_stock`, `physical_stock`, `difference`, `notes`) VALUES
(1, 1, 3, 2.00, 1.50, -0.50, ''),
(2, 2, 2, 89.50, 85.00, -4.50, '');

-- --------------------------------------------------------

--
-- Table structure for table `kitchens`
--

CREATE TABLE `kitchens` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `location` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kitchens`
--

INSERT INTO `kitchens` (`id`, `name`, `location`, `created_at`) VALUES
(1, 'Dapur 1', 'Medan', '2026-04-13 15:23:27'),
(2, 'dapur 2', 'medan petisahj', '2026-04-13 15:23:42');

-- --------------------------------------------------------

--
-- Table structure for table `master_lokasi_rak`
--

CREATE TABLE `master_lokasi_rak` (
  `id` int(11) NOT NULL,
  `kode_rak` varchar(50) NOT NULL,
  `nama_rak` varchar(100) NOT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `stock` decimal(10,4) DEFAULT 0.0000,
  `min_stock` decimal(10,4) DEFAULT 0.0000,
  `warehouse_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`id`, `code`, `name`, `unit`, `stock`, `min_stock`, `warehouse_id`, `created_at`) VALUES
(6, 'CB01', 'Coklat Batang Elmer', 'Kg', 0.8980, 10.0000, 1, '2026-04-13 08:59:54'),
(7, 'G01', 'Gula Pasir Kristal', 'Kg', 30.5000, 10.0000, 2, '2026-04-13 15:55:36'),
(12, 'MT01', 'Mentega Blueband', 'Kg', -59.0500, 1.0000, 1, '2026-04-13 16:08:49'),
(20, 'CB01', 'Coklat Batang Elmer', 'Pcs', 1.0000, 10.0000, 2, '2026-04-13 16:19:10'),
(21, 'MT01', 'Mentega Blueband', 'Kg', -5.0000, 10.0000, 2, '2026-04-13 16:19:55'),
(24, 'G02', 'Gula Pasir Kasar', 'Kg', -0.1000, 10.0000, 1, '2026-04-29 08:21:57'),
(25, 'G01', 'Gula Pasir Kristal', 'Kg', -0.5000, 10.0000, 1, '2026-04-29 08:21:57');

-- --------------------------------------------------------

--
-- Table structure for table `materials_stocks`
--

CREATE TABLE `materials_stocks` (
  `id` int(11) NOT NULL,
  `material_name` varchar(100) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `sku_code` varchar(50) DEFAULT NULL,
  `lokasi_rak_id` int(11) DEFAULT NULL,
  `stock` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_stock` decimal(10,2) NOT NULL DEFAULT 0.00,
  `expiry_date` date DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `unit` varchar(20) DEFAULT NULL,
  `rack_id` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `materials_stocks`
--

INSERT INTO `materials_stocks` (`id`, `material_name`, `category_id`, `sku_code`, `lokasi_rak_id`, `stock`, `min_stock`, `expiry_date`, `status`, `unit`, `rack_id`, `updated_at`) VALUES
(1, 'Tepung Terigu Segitiga Biru', 1, 'TP01', NULL, 500.00, 10.00, '2026-11-17', 'active', 'Kg', 3, '2026-04-17 08:24:26'),
(2, 'Gula Pasir Kristal', 1, 'G01', NULL, 85.00, 10.00, '2026-10-17', 'active', 'Kg', 1, '2026-04-21 05:26:10'),
(3, 'Coklat Batang Elmer', 1, 'CB01', NULL, 215.00, 10.00, '2026-04-26', 'active', 'Kg', 1, '2026-05-04 02:03:11'),
(4, 'Mentega Blueband', 1, 'MT01', NULL, 130.00, 10.00, '2026-04-26', 'active', 'Kg', 2, '2026-04-26 01:48:54'),
(5, 'Gula Pasir Kasar', 1, 'G02', NULL, 9.00, 5.00, '2026-04-28', 'active', 'Kg', 1, '2026-04-27 19:43:11');

-- --------------------------------------------------------

--
-- Table structure for table `material_categories`
--

CREATE TABLE `material_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `material_categories`
--

INSERT INTO `material_categories` (`id`, `name`, `description`) VALUES
(1, 'Bahan Baku', ''),
(2, 'kemasan', '');

-- --------------------------------------------------------

--
-- Table structure for table `material_opnames`
--

CREATE TABLE `material_opnames` (
  `id` int(11) NOT NULL,
  `opname_no` varchar(50) DEFAULT NULL,
  `material_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `system_stock` decimal(10,2) NOT NULL,
  `actual_stock` decimal(10,2) NOT NULL,
  `difference` decimal(10,2) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `material_opnames`
--

INSERT INTO `material_opnames` (`id`, `opname_no`, `material_id`, `user_id`, `system_stock`, `actual_stock`, `difference`, `reason`, `created_at`) VALUES
(1, NULL, 2, 1, 0.20, 1.00, 0.80, 'tidak ada', '2026-04-05 13:54:15'),
(2, NULL, 1, 1, 82.60, 1.00, -81.60, 'tidak ada', '2026-04-05 13:54:15'),
(3, 'SO-D0526-001', 2, 1, 10.00, 2.00, -8.00, '', '2026-04-05 14:16:18'),
(4, 'SO-D2126-001', 6, 1, 10.90, 1.00, -9.90, '', '2026-04-21 03:45:20'),
(5, 'SO-D2126-002', 22, 1, 10.00, 9.00, -1.00, '', '2026-04-21 04:37:09');

-- --------------------------------------------------------

--
-- Table structure for table `material_requests`
--

CREATE TABLE `material_requests` (
  `id` int(11) NOT NULL,
  `header_id` int(11) DEFAULT NULL,
  `request_no` varchar(20) DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `material_id` int(11) DEFAULT NULL,
  `qty_requested` decimal(15,2) DEFAULT NULL,
  `qty_approved` decimal(15,2) DEFAULT NULL,
  `status` enum('menunggu','diproses','ditolak') DEFAULT 'menunggu',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `material_requests`
--

INSERT INTO `material_requests` (`id`, `header_id`, `request_no`, `warehouse_id`, `user_id`, `material_id`, `qty_requested`, `qty_approved`, `status`, `notes`, `created_at`, `processed_at`) VALUES
(1, NULL, 'REQ-20260413-FCBB', 2, 1, 3, 20.00, 20.00, 'diproses', NULL, '2026-04-13 08:30:16', '2026-04-13 15:31:38'),
(2, NULL, 'REQ-20260413-A9C8', 1, 1, 2, 100.00, 100.00, 'diproses', NULL, '2026-04-13 08:32:10', '2026-04-13 15:32:18'),
(3, NULL, 'REQ-20260413-86BE', 1, 1, 3, 20.00, 20.00, 'diproses', NULL, '2026-04-13 08:46:59', '2026-04-13 15:47:06'),
(4, NULL, 'REQ-20260413-C324', 1, 1, 3, 10.00, 10.00, 'diproses', NULL, '2026-04-13 08:54:08', '2026-04-13 15:54:19'),
(5, NULL, 'REQ-20260413-2380', 1, 1, 3, 1.00, 1.00, 'diproses', NULL, '2026-04-13 08:59:50', '2026-04-13 15:59:54'),
(6, NULL, 'REQ-20260413-2A7C', 1, 1, 3, 1.00, 1.00, 'diproses', NULL, '2026-04-13 09:03:02', '2026-04-13 16:03:10'),
(7, NULL, 'REQ-20260413-9CA5', 1, 1, 3, 10.00, 10.00, 'diproses', NULL, '2026-04-13 09:03:25', '2026-04-13 16:03:36'),
(8, NULL, 'REQ-20260413-B5D5', 1, 1, 2, 1.00, NULL, 'ditolak', NULL, '2026-04-13 09:03:47', '2026-04-13 23:06:40'),
(9, NULL, 'REQ-20260413-C2D9', 2, 15, 2, 10.00, 10.00, 'diproses', NULL, '2026-04-13 15:55:26', '2026-04-13 22:55:36'),
(10, NULL, 'REQ-20260413-727F', 1, 15, 3, 1.00, 1.00, 'diproses', NULL, '2026-04-13 15:56:06', '2026-04-13 23:06:42'),
(11, NULL, 'REQ-20260413-9B1C', 1, 16, 2, 10.00, NULL, 'ditolak', NULL, '2026-04-13 16:06:22', '2026-04-13 23:06:50'),
(12, NULL, 'REQ-20260413-5D9F', 2, 17, 2, 20.00, 20.00, 'diproses', NULL, '2026-04-13 16:07:08', '2026-04-13 23:07:19'),
(13, NULL, 'REQ-20260413-4CE9', 1, 16, 2, 10.00, 10.00, 'diproses', NULL, '2026-04-13 16:07:46', '2026-04-13 23:18:39'),
(14, NULL, 'REQ-20260413-E657', 1, 16, 4, 10.00, 10.00, 'diproses', NULL, '2026-04-13 16:08:37', '2026-04-13 23:08:49'),
(15, NULL, 'REQ-20260413-E826', 2, 17, 3, 1.00, 1.00, 'diproses', NULL, '2026-04-13 16:09:52', '2026-04-13 23:19:10'),
(16, NULL, 'REQ-20260413-AA47', 2, 17, 4, 10.00, 10.00, 'diproses', NULL, '2026-04-13 16:10:46', '2026-04-13 23:19:55'),
(17, NULL, 'REQ-20260414-C6BA', 1, 16, 1, 10.00, 10.00, 'diproses', NULL, '2026-04-14 08:39:47', '2026-04-14 15:40:50'),
(18, NULL, 'REQ-20260417-167E', 1, 16, 3, 10.00, 10.00, 'diproses', NULL, '2026-04-17 09:05:22', '2026-04-17 16:05:42'),
(19, NULL, 'REQ-20260420-638E', 1, 16, 3, 0.50, 0.50, 'diproses', NULL, '2026-04-20 02:27:29', '2026-04-20 09:27:47'),
(20, NULL, 'REQ-20260421-D9BA', 1, 16, 5, 10.00, 10.00, 'diproses', NULL, '2026-04-21 04:38:12', '2026-04-21 11:38:23'),
(21, NULL, 'REQ-20260421-81C9', 1, 16, 5, 1.00, 1.00, 'diproses', NULL, '2026-04-21 04:38:53', '2026-04-21 11:39:05'),
(22, NULL, 'REQ-20260421-ED33', 1, 16, 3, 0.50, 0.50, 'diproses', NULL, '2026-04-21 04:51:22', '2026-04-21 12:17:36'),
(23, NULL, 'REQ-20260421-E90B', 1, 16, 2, 10.00, 10.00, 'diproses', NULL, '2026-04-21 05:18:19', '2026-04-21 12:18:50'),
(24, NULL, 'REQ-20260421-D0FF', 2, 17, 2, 0.50, 0.50, 'diproses', NULL, '2026-04-21 05:24:19', '2026-04-21 12:24:33'),
(25, 1, 'REQ-260429-CC5D', 1, 16, 3, 1.00, NULL, 'ditolak', NULL, '2026-04-29 01:29:50', '2026-04-29 01:33:15'),
(26, 1, 'REQ-260429-CC5D', 1, 16, 5, 2.00, NULL, 'ditolak', NULL, '2026-04-29 01:29:50', '2026-04-29 01:33:18');

-- --------------------------------------------------------

--
-- Table structure for table `material_requests_header`
--

CREATE TABLE `material_requests_header` (
  `id` int(11) NOT NULL,
  `request_no` varchar(50) NOT NULL,
  `warehouse_id` int(11) NOT NULL COMMENT 'ID Dapur yang meminta',
  `user_id` int(11) NOT NULL COMMENT 'User yang membuat request',
  `status` enum('menunggu','diproses','berhasil','ditolak') DEFAULT 'menunggu',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `material_requests_header`
--

INSERT INTO `material_requests_header` (`id`, `request_no`, `warehouse_id`, `user_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 'REQ-260429-CC5D', 1, 16, 'ditolak', '2026-04-29 01:29:50', '2026-04-29 01:33:18');

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `name`, `created_at`) VALUES
(1, 'Cash', '2026-04-19 08:40:06'),
(2, 'Giro/Cek', '2026-04-19 08:40:06'),
(3, 'QRIS', '2026-04-19 08:40:06'),
(4, 'Transfer Bank', '2026-04-19 08:40:06');

-- --------------------------------------------------------

--
-- Table structure for table `pengumuman`
--

CREATE TABLE `pengumuman` (
  `id` int(11) NOT NULL,
  `pesan` text NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengumuman`
--

INSERT INTO `pengumuman` (`id`, `pesan`, `is_active`, `created_at`, `created_by`) VALUES
(1, 'Selamat datang di Sistem ERP Gudang Pilar! Harap selalu lakukan stok opname di akhir bulan dan cek masa kadaluarsa bahan baku.', 1, '2026-04-22 06:41:26', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `po_returns`
--

CREATE TABLE `po_returns` (
  `id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `qty_return` decimal(10,2) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `po_returns`
--

INSERT INTO `po_returns` (`id`, `po_id`, `material_id`, `qty_return`, `price`, `reason`, `status`, `created_by`, `created_at`) VALUES
(1, 13, 3, 1.00, 12000.00, 'rusak', 'approved', 20, '2026-04-27 16:29:35'),
(2, 13, 3, 1.00, 12000.00, 'sobek', 'rejected', 20, '2026-05-01 08:43:56'),
(3, 13, 3, 2.00, 12000.00, 'ok', 'approved', 20, '2026-05-01 08:46:31'),
(4, 13, 3, 1.00, 12000.00, 'Salah', 'approved', 20, '2026-05-04 01:59:41'),
(5, 13, 3, 2.00, 12000.00, 'Ok', 'approved', 20, '2026-05-04 02:01:50'),
(6, 13, 3, 2.00, 12000.00, 'Ok', 'approved', 20, '2026-05-04 02:02:55');

-- --------------------------------------------------------

--
-- Table structure for table `productions`
--

CREATE TABLE `productions` (
  `id` int(11) NOT NULL,
  `invoice_no` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `status` enum('pending','masuk_gudang','expired','ditolak','dibatalkan') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `productions`
--

INSERT INTO `productions` (`id`, `invoice_no`, `user_id`, `employee_id`, `warehouse_id`, `status`, `notes`, `created_at`) VALUES
(1, 'INV-PRD-20260326-CE9D', 2, NULL, 1, 'masuk_gudang', NULL, '2026-03-26 18:45:49'),
(2, 'INV-PRD-20260326-2200', 2, NULL, 1, 'pending', NULL, '2026-03-26 19:30:13'),
(3, 'INV-PRD-20260328-23D2', 4, NULL, 1, 'pending', NULL, '2026-03-28 13:00:46'),
(4, 'INV-PRD-20260328-4992', 4, NULL, 1, 'masuk_gudang', NULL, '2026-03-28 13:38:55'),
(5, 'INV-PRD-20260328-A34C', 4, NULL, 1, 'masuk_gudang', NULL, '2026-03-28 17:02:48'),
(6, 'INV-PRD-20260330-54FD', 4, NULL, 1, 'pending', NULL, '2026-03-30 00:23:03'),
(7, 'INV-PRD-20260330-B6C9', 4, NULL, 1, 'pending', NULL, '2026-03-30 00:27:53'),
(8, 'INV-PRD-20260330-A7C4', 4, NULL, 1, 'pending', NULL, '2026-03-30 00:31:50'),
(9, 'INV-PRD-20260330-E962', 4, NULL, 1, 'pending', NULL, '2026-03-30 00:34:40'),
(10, 'INV-PRD-20260330-A15D', 4, NULL, 1, 'pending', NULL, '2026-03-30 00:36:42'),
(11, 'INV-PRD-20260330-FC22', 4, NULL, 1, 'pending', NULL, '2026-03-30 00:39:34'),
(12, 'INV-PRD-20260330-2E5B', 4, NULL, 1, 'pending', NULL, '2026-03-30 00:43:01'),
(13, 'INV-PRD-20260330-191D', 4, NULL, 1, 'pending', NULL, '2026-03-30 00:44:22'),
(14, 'INV-PRD-20260330-20D0', 4, NULL, 1, 'pending', NULL, '2026-03-30 00:44:59'),
(15, 'INV-PRD-20260330-488A', 4, NULL, 1, 'pending', NULL, '2026-03-30 12:14:07'),
(16, 'INV-PRD-20260331-719A', 4, NULL, 1, '', NULL, '2026-03-31 03:02:22'),
(17, 'INV-PRD-20260331-58B8', 4, NULL, 1, '', NULL, '2026-03-31 03:03:11'),
(18, 'INV-PRD-20260331-FA72', 4, NULL, 1, 'expired', NULL, '2026-03-31 03:04:03'),
(19, 'INV-PRD-20260331-8D34', 4, NULL, 1, 'expired', NULL, '2026-03-31 04:09:33'),
(20, 'INV-PRD-20260331-96A2', 4, NULL, 1, 'masuk_gudang', NULL, '2026-03-31 04:37:33'),
(23, 'INV-PRD-20260331-8186', 4, NULL, 1, 'pending', NULL, '2026-03-31 07:09:41'),
(24, 'INV-PRD-20260331-6C7A', 4, NULL, 1, 'pending', 'tes', '2026-03-31 10:56:19'),
(25, 'INV-PRD-20260331-E2BB', 4, NULL, 1, 'pending', '', '2026-03-31 10:56:40'),
(26, 'A131-0326-088B', 4, NULL, 1, 'pending', '', '2026-03-31 11:03:39'),
(27, 'A131-0326-B62B', 4, NULL, 1, 'pending', 'tes', '2026-03-31 12:42:32'),
(28, 'C3126-011', 4, NULL, 1, 'expired', 'tess', '2026-03-31 12:49:22'),
(29, 'C3126-012', 4, NULL, 1, 'pending', '', '2026-03-31 18:18:40'),
(30, 'D0126-002', 4, NULL, 1, 'pending', '1', '2026-04-01 04:47:17'),
(31, 'D0126-003', 4, NULL, 1, 'masuk_gudang', '', '2026-04-01 09:39:53'),
(32, 'D0126-004', 4, NULL, 1, 'pending', '', '2026-04-01 09:45:01'),
(33, 'D0126-005', 4, NULL, 1, 'pending', '', '2026-04-01 09:45:01'),
(37, 'D0126-006', 4, NULL, 2, 'masuk_gudang', '', '2026-04-01 09:52:16'),
(39, 'D0126-007', 2, 1, 1, 'masuk_gudang', '', '2026-04-01 15:05:03'),
(40, 'D0126-008', 2, 3, 2, 'masuk_gudang', '', '2026-04-01 15:10:22'),
(41, 'D0226-001', 2, 1, 1, 'expired', '', '2026-04-02 02:22:01'),
(42, 'D0226-002', 2, 4, 1, 'masuk_gudang', '', '2026-04-02 02:30:21'),
(43, 'D0326-001', 2, 4, 1, 'masuk_gudang', '', '2026-04-03 17:05:48'),
(44, 'D0426-002', 2, 1, 1, 'masuk_gudang', '', '2026-04-04 18:54:03'),
(48, 'D0426-003', 2, 2, 2, 'masuk_gudang', '', '2026-04-04 18:56:57'),
(49, 'D0426-004', 2, 4, 1, 'masuk_gudang', '', '2026-04-04 18:57:13'),
(50, 'D0526-001', 2, 2, 2, 'pending', '', '2026-04-05 16:08:01'),
(51, 'D0526-002', 2, 3, 1, 'masuk_gudang', '', '2026-04-05 16:08:35'),
(52, 'D0526-003', 2, 1, 1, 'pending', '', '2026-04-05 18:09:18'),
(53, 'D0526-004', 2, 1, 1, 'ditolak', '', '2026-04-05 18:10:28'),
(54, 'D0526-005', 2, 2, 1, 'masuk_gudang', '', '2026-04-05 18:12:36'),
(55, 'D0526-006', 2, 4, 1, 'expired', '', '2026-04-05 18:14:38'),
(58, 'D0726-001', 2, 2, 1, 'masuk_gudang', '', '2026-04-07 17:07:31'),
(63, 'D0826-001', 2, 1, 1, 'dibatalkan', '', '2026-04-08 08:22:44'),
(64, 'D0826-002', 2, 1, 1, 'masuk_gudang', '', '2026-04-08 08:24:27'),
(65, 'D0826-003', 2, 4, 2, 'dibatalkan', '', '2026-04-08 08:25:47'),
(66, 'D0826-004', 2, 1, 1, 'masuk_gudang', '', '2026-04-08 09:09:38'),
(67, 'D1026-001', 2, 4, 1, 'masuk_gudang', '', '2026-04-10 16:29:36'),
(68, 'D1026-002', 2, 4, 2, 'dibatalkan', '', '2026-04-10 16:30:16'),
(70, 'D1126-001', 2, 1, 1, 'dibatalkan', '', '2026-04-11 06:34:34'),
(71, 'D1126-002', 2, 2, 1, 'masuk_gudang', '', '2026-04-11 06:35:26'),
(72, 'D1326-001', 2, 2, 1, 'pending', '', '2026-04-13 17:24:23'),
(73, 'D1326-002', 2, 1, 1, 'pending', '', '2026-04-13 17:54:46'),
(74, 'D1326-003', 2, 4, 1, 'pending', '', '2026-04-13 17:57:47'),
(75, 'D1326-004', 2, 4, 2, 'pending', '', '2026-04-13 17:59:33'),
(76, 'D1326-005', 2, 1, 2, 'pending', '', '2026-04-13 18:05:26'),
(77, 'D1326-006', 2, 1, 2, 'pending', '', '2026-04-13 18:23:01'),
(78, 'D1326-007', 2, 4, 1, 'masuk_gudang', '', '2026-04-13 18:23:35'),
(80, 'D1326-008', 2, 4, 1, 'dibatalkan', '', '2026-04-13 18:26:21'),
(81, 'D1426-001', 2, 1, 1, 'pending', '', '2026-04-14 08:43:26'),
(82, 'D1426-002', 18, 1, 1, 'expired', '', '2026-04-14 17:45:07'),
(83, 'D1626-001', 2, 1, 2, 'masuk_gudang', '', '2026-04-16 13:25:18'),
(84, 'D1626-002', 18, 1, 2, 'masuk_gudang', '', '2026-04-16 13:49:02'),
(85, 'D1626-003', 18, 1, 2, 'masuk_gudang', '', '2026-04-16 14:23:53'),
(86, 'D1626-004', 18, 1, 1, 'dibatalkan', '', '2026-04-16 14:42:46'),
(88, 'D2026-001', 18, 1, 1, 'pending', '', '2026-04-20 03:19:02'),
(89, 'D2226-001', 18, 1, 1, 'pending', '', '2026-04-22 12:20:37'),
(90, 'D2226-002', 18, 1, 1, 'masuk_gudang', '', '2026-04-22 12:22:40'),
(91, 'D2226-003', 18, 1, 1, 'dibatalkan', '', '2026-04-22 12:23:18'),
(92, 'D2926-001', 2, 1, 1, 'masuk_gudang', '', '2026-04-29 08:21:57');

-- --------------------------------------------------------

--
-- Table structure for table `production_details`
--

CREATE TABLE `production_details` (
  `id` int(11) NOT NULL,
  `production_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `barcode` varchar(100) NOT NULL,
  `expired_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_details`
--

INSERT INTO `production_details` (`id`, `production_id`, `product_id`, `quantity`, `barcode`, `expired_date`) VALUES
(1, 1, 1, 1, 'BRC-20260326CE9D', NULL),
(2, 2, 1, 2, 'BRC-202603262200', NULL),
(3, 3, 1, 1, 'BRC-2026032823D2', NULL),
(4, 4, 1, 2, 'BRC-202603284992', NULL),
(5, 5, 1, 3, 'BRC-20260328A34C', NULL),
(6, 6, 1, 2, 'BRC-2026033054FD', NULL),
(7, 7, 1, 1, 'BRC-20260330B6C9', NULL),
(8, 8, 1, 1, 'BRC-20260330A7C4', NULL),
(9, 9, 1, 2, 'BRC-20260330E962', NULL),
(10, 10, 1, 5, 'BRC-20260330A15D', NULL),
(11, 11, 1, 1, 'BRC-20260330FC22', NULL),
(12, 12, 1, 1, 'BRC-202603302E5B', NULL),
(13, 13, 1, 1, 'BRC-20260330191D', NULL),
(14, 14, 1, 1, 'BRC-2026033020D0', NULL),
(15, 15, 1, 1, 'BRC-20260330488A', '2026-04-15'),
(16, 16, 1, 1, 'BRC-20260331719A', '2026-04-30'),
(17, 17, 1, 1, 'BRC-2026033158B8', '2026-04-03'),
(18, 18, 1, 1, 'BRC-20260331FA72', '2026-04-03'),
(19, 19, 1, 2, 'BRC-202603318D34', '2026-04-15'),
(20, 20, 1, 3, 'BRC-2026033196A2', '2026-04-22'),
(21, 23, 1, 1, 'BRC-20260331937A0', NULL),
(22, 23, 2, 2, 'BRC-20260331BA091', NULL),
(23, 24, 1, 1, 'BRC-20260331A0570', NULL),
(24, 25, 2, 2, 'BRC-20260331F64E0', NULL),
(25, 26, 3, 1, 'BRC-20260331312E0', NULL),
(26, 27, 2, 2, 'BRC-2026033117280', NULL),
(27, 28, 3, 1, 'BRC-20260331A9DB0', NULL),
(28, 29, 2, 1, 'BRC-20260331F2C30', NULL),
(29, 29, 1, 1, 'BRC-202603315CA91', NULL),
(30, 30, 2, 1, 'BRC-20260401EAF40', NULL),
(31, 30, 1, 1, 'BRC-202604011C191', NULL),
(32, 30, 3, 1, 'BRC-2026040128C12', NULL),
(33, 31, 1, 1, 'BRC-20260401F3830', NULL),
(34, 31, 2, 1, 'BRC-2026040101C81', NULL),
(35, 31, 3, 2, 'BRC-202604012AF32', NULL),
(36, 32, 2, 1, 'BRC-2026040184850', NULL),
(37, 33, 1, 1, 'BRC-202604019AD61', NULL),
(44, 37, 1, 1, 'BRC-202604016E8D0', NULL),
(45, 37, 3, 2, 'BRC-202604016E8D1', NULL),
(48, 39, 1, 1, 'BRC-20260401170503-5607-0', NULL),
(49, 39, 2, 1, 'BRC-20260401170503-5607-1', NULL),
(50, 40, 2, 2, 'BRC-20260401171022-1248-0', NULL),
(51, 40, 3, 2, 'BRC-20260401171022-1248-1', NULL),
(52, 41, 1, 2, 'BRC-20260402042201-2414-0', NULL),
(53, 41, 2, 1, 'BRC-20260402042201-2414-1', NULL),
(54, 42, 1, 1, 'BRC-20260402043021-5414-0', NULL),
(55, 42, 1, 2, 'BRC-20260402043021-5414-1', NULL),
(56, 43, 1, 1, 'BRC-20260403190548-4312-0', NULL),
(57, 43, 2, 2, 'BRC-20260403190548-4312-1', NULL),
(58, 44, 1, 1, 'BRC-20260404205403-6767-0', NULL),
(59, 44, 3, 2, 'BRC-20260404205403-6767-1', NULL),
(60, 48, 2, 1, 'BRC-20260404205657-6979-0', NULL),
(61, 49, 2, 2, 'BRC-20260404205713-6109-0', NULL),
(62, 49, 3, 2, 'BRC-20260404205713-6109-1', NULL),
(63, 50, 2, 1, 'BRC-20260405180801-9571-0', NULL),
(64, 50, 3, 1, 'BRC-20260405180801-9571-1', NULL),
(65, 51, 3, 1, 'BRC-20260405180835-1429-0', NULL),
(66, 51, 2, 1, 'BRC-20260405180835-1429-1', NULL),
(67, 52, 1, 1, 'BRC-20260405200918-6169-0', NULL),
(68, 52, 3, 1, 'BRC-20260405200918-6169-1', NULL),
(69, 53, 3, 1, 'BRC-20260405201028-5118-0', NULL),
(70, 54, 2, 2, 'BRC-20260405201236-9990-0', NULL),
(71, 55, 1, 1, 'BRC-20260405201438-2065-0', NULL),
(75, 58, 2, 1, 'D0726-001-1', NULL),
(76, 58, 3, 1, 'D0726-001-2', NULL),
(83, 63, 1, 1, 'D0826-001-1', NULL),
(84, 63, 2, 1, 'D0826-001-2', NULL),
(85, 64, 2, 2, 'D0826-002-1', NULL),
(86, 65, 2, 1, 'D0826-003-1', NULL),
(87, 66, 2, 2, 'D0826-004-1', NULL),
(88, 66, 1, 2, 'D0826-004-2', NULL),
(89, 66, 3, 2, 'D0826-004-3', NULL),
(90, 67, 2, 1, 'D1026-001-1', NULL),
(91, 67, 1, 2, 'D1026-001-2', NULL),
(92, 68, 2, 1, 'D1026-002-1', NULL),
(93, 68, 1, 1, 'D1026-002-2', NULL),
(95, 70, 1, 1, 'D1126-001-1', NULL),
(96, 70, 2, 1, 'D1126-001-2', NULL),
(97, 71, 2, 2, 'D1126-002-1', NULL),
(98, 72, 3, 1, 'D1326-001-1', NULL),
(99, 72, 6, 1, 'D1326-001-2', NULL),
(100, 73, 6, 1, 'D1326-002-1', NULL),
(101, 74, 3, 1, 'D1326-003-1', NULL),
(102, 75, 3, 1, 'D1326-004-1', NULL),
(103, 76, 3, 1, 'D1326-005-1', NULL),
(104, 77, 3, 1, 'D1326-006-1', NULL),
(105, 78, 3, 2, 'D1326-007-1', NULL),
(106, 80, 3, 1, 'D1326-008-1', NULL),
(107, 81, 3, 1, 'D1426-001-1', NULL),
(108, 82, 3, 1, 'D1426-002-1', NULL),
(109, 83, 3, 10, 'D1626-001-1', NULL),
(110, 84, 3, 1, 'D1626-002-1', NULL),
(111, 85, 3, 2, 'D1626-003-1', NULL),
(112, 86, 3, 1, 'D1626-004-1', NULL),
(113, 88, 3, 1, 'D2026-001-1', NULL),
(114, 89, 3, 2, 'D2226-001-1', NULL),
(115, 90, 2, 1, 'D2226-002-1', NULL),
(116, 91, 2, 2, 'D2226-003-1', NULL),
(117, 92, 2, 1, 'D2926-001-1', NULL),
(118, 92, 1, 2, 'D2926-001-2', NULL),
(119, 92, 6, 5, 'D2926-001-3', NULL),
(120, 92, 3, 10, 'D2926-001-4', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `production_plans`
--

CREATE TABLE `production_plans` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `karyawan_id` int(11) NOT NULL,
  `plan_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_plans`
--

INSERT INTO `production_plans` (`id`, `user_id`, `karyawan_id`, `plan_date`, `notes`, `created_at`) VALUES
(1, 18, 1, '2026-04-28', '', '2026-04-28 16:54:18'),
(2, 2, 1, '2026-04-29', '', '2026-04-29 08:19:23');

-- --------------------------------------------------------

--
-- Table structure for table `production_plan_details`
--

CREATE TABLE `production_plan_details` (
  `id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `target_qty` int(11) NOT NULL DEFAULT 0,
  `est_adonan_kg` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_plan_details`
--

INSERT INTO `production_plan_details` (`id`, `plan_id`, `product_id`, `target_qty`, `est_adonan_kg`) VALUES
(1, 1, 6, 1, 10.00),
(2, 1, 3, 1, 30.00),
(3, 1, 1, 1, 30.00),
(4, 2, 2, 3, 0.00),
(5, 2, 6, 2, 0.00),
(6, 2, 3, 1, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `stock` int(11) DEFAULT 0,
  `warehouse_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `code`, `name`, `category`, `price`, `stock`, `warehouse_id`, `created_at`) VALUES
(1, 'RO1', 'Roti Blueberrie', 'Roti Manis', 10000.00, 0, NULL, '2026-04-11 06:23:45'),
(2, 'BRW', 'Brownis Coklat', 'Roti Manis', 10000.00, 0, NULL, '2026-04-11 06:23:45'),
(3, 'RO2', 'Roti Keju', 'Kue Kering', 10000.00, 0, NULL, '2026-04-11 06:23:45'),
(6, 'G02', 'Roti kacanng', 'Roti Manis', 0.00, 0, NULL, '2026-04-11 06:23:45');

-- --------------------------------------------------------

--
-- Table structure for table `product_outs`
--

CREATE TABLE `product_outs` (
  `id` int(11) NOT NULL,
  `invoice_no` varchar(50) NOT NULL,
  `origin_invoice` varchar(50) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `reason` varchar(50) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_outs`
--

INSERT INTO `product_outs` (`id`, `invoice_no`, `origin_invoice`, `user_id`, `employee_id`, `product_id`, `quantity`, `reason`, `notes`, `created_at`) VALUES
(1, 'OUT-20260331-3914', 'INV-PRD-20260331-96A2', 4, NULL, 1, 3, 'Expired', '', '2026-03-31 11:31:55'),
(2, 'OUT-20260331-CB37', 'INV-PRD-20260331-96A2', 4, NULL, 1, 3, 'Expired', '', '2026-03-31 11:37:44'),
(3, 'OUT-20260331-B99E', 'INV-PRD-20260331-8D34', 4, NULL, 1, 2, 'Expired', '', '2026-03-31 11:43:15'),
(4, 'OUT-20260331-6B74', 'INV-PRD-20260331-FA72', 4, NULL, 1, 1, 'Expired', '', '2026-03-31 11:47:10'),
(5, 'OUT-20260331-7B75', 'C3126-011', 4, NULL, 3, 1, 'Expired', '', '2026-03-31 18:20:56'),
(6, 'OUT-20260401-4684', 'D0126-007', 2, 1, 1, 1, 'Expired', '', '2026-04-01 15:22:22'),
(9, 'OUT-20260402191049-4EA9', 'D0226-001', 2, 1, 1, 2, 'Expired', '', '2026-04-02 17:10:49'),
(10, 'OUT-20260402191603-1816', 'D0226-001', 2, 2, 2, 1, 'Expired', '', '2026-04-02 17:16:03'),
(11, 'OUT-20260402194320-F6B4', 'D0126-008', 2, 4, 2, 1, 'Expired', '', '2026-04-02 17:43:20'),
(12, 'OUT-20260402194320-F6B4', 'D0126-008', 2, 4, 3, 1, 'Expired', '', '2026-04-02 17:43:20'),
(13, 'OUT-20260406184342-04CB', 'D0526-005', 2, 4, 2, 1, 'Expired', '', '2026-04-06 16:43:42'),
(14, 'OUT-20260406185422-5236', 'D0526-006', 2, 4, 1, 1, 'Expired', '', '2026-04-06 16:54:22'),
(15, 'OUT-20260416153929-B8E1', 'D1626-001', 2, 1, 3, 1, 'Rusak', '', '2026-04-16 13:39:29'),
(16, 'OUT-20260416155004-07F1', 'D1426-002', 18, 2, 3, 1, 'Expired', '', '2026-04-16 13:50:04');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` int(11) NOT NULL,
  `po_no` varchar(50) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `shipping_date` date NOT NULL,
  `status` enum('waiting_approval','approved','received','rejected','cancelled') DEFAULT 'waiting_approval',
  `print_po_status` enum('unlocked','locked','pending_approval') NOT NULL DEFAULT 'unlocked',
  `print_po_count` int(11) NOT NULL DEFAULT 0,
  `print_terima_status` enum('unlocked','locked','pending_approval') NOT NULL DEFAULT 'unlocked',
  `print_terima_count` int(11) NOT NULL DEFAULT 0,
  `total_amount` decimal(15,2) DEFAULT 0.00,
  `paid_amount` decimal(15,2) DEFAULT 0.00,
  `payment_status` enum('unpaid','partial','paid') DEFAULT 'unpaid',
  `created_by` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`id`, `po_no`, `supplier_id`, `shipping_date`, `status`, `print_po_status`, `print_po_count`, `print_terima_status`, `print_terima_count`, `total_amount`, `paid_amount`, `payment_status`, `created_by`, `approved_by`, `created_at`, `updated_at`) VALUES
(1, 'LC-19042026-4079', 1, '2026-04-19', 'received', 'unlocked', 0, 'unlocked', 0, 200000.00, 100000.00, 'partial', 13, 13, '2026-04-19 07:51:43', '2026-04-19 08:51:22'),
(2, 'LC-19042026-1735', 2, '2026-04-21', 'received', 'unlocked', 0, 'unlocked', 0, 1000000.00, 0.00, 'unpaid', 13, 13, '2026-04-19 08:19:07', '2026-04-19 08:28:32'),
(3, 'LC-19042026-3726', 1, '2026-04-20', 'received', 'unlocked', 0, 'unlocked', 0, 10000.00, 10000.00, 'paid', 13, 13, '2026-04-19 08:34:09', '2026-04-19 08:52:37'),
(4, 'LC-19042026-1779', 2, '2026-04-19', 'received', 'unlocked', 0, 'unlocked', 0, 9000.00, 9000.00, 'paid', 13, 13, '2026-04-19 09:14:35', '2026-04-21 11:10:40'),
(5, 'LC-20042026-4761', 1, '2026-04-22', 'received', 'unlocked', 0, 'unlocked', 0, 900000.00, 900000.00, 'paid', 20, 20, '2026-04-20 02:31:23', '2026-04-21 03:24:13'),
(6, 'LC-21042026-6465', 1, '2026-04-25', 'received', 'unlocked', 0, 'unlocked', 0, 450000.00, 450000.00, 'paid', 20, 20, '2026-04-21 02:33:13', '2026-04-21 11:07:24'),
(7, 'LC-21042026-5977', 1, '2026-04-21', 'received', 'unlocked', 0, 'unlocked', 0, 50000.00, 50000.00, 'paid', 20, 20, '2026-04-21 03:31:39', '2026-04-21 11:40:42'),
(8, 'LC-21042026-7367', 1, '2026-04-21', 'received', 'locked', 0, 'unlocked', 0, 1000000.00, 900000.00, 'partial', 20, 20, '2026-04-21 11:05:20', '2026-04-26 01:41:12'),
(9, 'LC-21042026-4730', 2, '2026-04-23', 'received', 'locked', 0, 'locked', 0, 1000000.00, 0.00, 'unpaid', 20, 20, '2026-04-21 11:23:02', '2026-04-24 07:20:18'),
(10, 'LC-21042026-8746', 1, '2026-04-25', 'approved', 'locked', 0, 'unlocked', 0, 0.00, 0.00, 'unpaid', 20, 20, '2026-04-21 11:41:44', '2026-04-24 07:21:10'),
(11, 'LC-26042026-6429', 1, '2026-04-26', 'rejected', 'unlocked', 0, 'unlocked', 0, 0.00, 0.00, 'unpaid', 20, 20, '2026-04-26 01:29:51', '2026-04-26 01:30:54'),
(12, 'LC-26042026-7604', 2, '2026-04-26', 'approved', 'locked', 0, 'unlocked', 0, 0.00, 0.00, 'unpaid', 20, 20, '2026-04-26 01:31:31', '2026-04-26 01:42:02'),
(13, 'LC-26042026-2279', 2, '2026-04-26', 'received', 'locked', 0, 'unlocked', 0, -72000.00, 0.00, 'unpaid', 20, 20, '2026-04-26 01:35:22', '2026-05-04 02:03:11'),
(14, 'LC-27042026-7443', 1, '2026-04-28', 'approved', 'unlocked', 1, 'unlocked', 0, 0.00, 0.00, 'unpaid', 20, 20, '2026-04-27 16:28:11', '2026-04-27 16:29:09'),
(15, 'LC-27042026-4315', 1, '2026-04-28', 'received', 'unlocked', 0, 'locked', 1, 0.00, 0.00, 'unpaid', 20, 20, '2026-04-27 19:39:04', '2026-05-04 01:57:33');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_details`
--

CREATE TABLE `purchase_order_details` (
  `id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `price` decimal(15,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_order_details`
--

INSERT INTO `purchase_order_details` (`id`, `po_id`, `material_id`, `qty`, `price`) VALUES
(3, 2, 5, 10.00, 100000.00),
(4, 1, 3, 2.00, 100000.00),
(6, 3, 3, 1.00, 10000.00),
(8, 4, 3, 1.00, 9000.00),
(10, 5, 3, 3.00, 300000.00),
(12, 6, 3, 9.00, 50000.00),
(14, 7, 3, 1.00, 50000.00),
(16, 8, 3, 10.00, 100000.00),
(18, 9, 3, 10.00, 100000.00),
(19, 10, 2, 9.00, 0.00),
(20, 11, 5, 26.00, 0.00),
(21, 12, 3, 1.00, 0.00),
(22, 12, 4, 0.00, 0.00),
(26, 13, 3, 2.00, 12000.00),
(27, 13, 4, 0.00, 12000.00),
(28, 14, 3, 9.00, 0.00),
(30, 15, 5, 0.00, 13000.00);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_payments`
--

CREATE TABLE `purchase_order_payments` (
  `id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT 'Transfer Bank',
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_payments`
--

CREATE TABLE `purchase_payments` (
  `id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `payment_method_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_date` datetime NOT NULL,
  `notes` text DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_payments`
--

INSERT INTO `purchase_payments` (`id`, `po_id`, `payment_method_id`, `amount`, `payment_date`, `notes`, `user_id`, `created_at`) VALUES
(1, 3, 1, 5000.00, '2026-04-19 15:50:00', '', 13, '2026-04-19 08:51:00'),
(2, 3, 1, 2000.00, '2026-04-19 15:51:00', '', 13, '2026-04-19 08:51:10'),
(3, 1, 3, 100000.00, '2026-04-19 15:51:00', '', 13, '2026-04-19 08:51:22'),
(4, 3, 1, 3000.00, '2026-04-19 15:52:00', '', 13, '2026-04-19 08:52:37'),
(5, 4, 1, 9000.00, '2026-04-19 16:15:00', '', 13, '2026-04-19 09:15:32'),
(6, 5, 1, 150000.00, '2026-04-20 09:37:00', '', 20, '2026-04-20 02:37:48'),
(7, 5, 1, 150000.00, '2026-04-20 09:38:00', '', 20, '2026-04-20 02:38:42'),
(8, 6, 1, 300000.00, '2026-04-21 09:44:00', '', 20, '2026-04-21 02:45:04'),
(9, 6, 2, 50000.00, '2026-04-21 09:45:00', '', 20, '2026-04-21 02:46:00'),
(10, 6, 1, 100000.00, '2026-04-21 10:23:00', '', 20, '2026-04-21 03:23:49'),
(11, 5, 1, 600000.00, '2026-04-21 10:24:00', '', 20, '2026-04-21 03:24:13'),
(12, 7, 2, 50000.00, '2026-04-21 10:32:00', '', 20, '2026-04-21 03:32:37'),
(13, 8, 1, 900000.00, '2026-04-21 18:22:00', '', 20, '2026-04-21 11:22:15');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_requests`
--

CREATE TABLE `purchase_requests` (
  `id` int(11) NOT NULL,
  `request_no` varchar(50) NOT NULL,
  `material_id` int(11) NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','processing','completed','rejected') DEFAULT 'pending',
  `po_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_requests`
--

INSERT INTO `purchase_requests` (`id`, `request_no`, `material_id`, `qty`, `notes`, `status`, `po_id`, `user_id`, `created_at`) VALUES
(1, 'PR-20260417-FBF', 3, 10.00, '', 'completed', NULL, 13, '2026-04-17 10:16:54'),
(2, 'PR-20260417-F04', 3, 10.00, '', '', NULL, 13, '2026-04-17 10:24:13'),
(3, 'PR-20260419092156', 3, 1.00, '', 'processing', 1, 13, '2026-04-19 07:21:56'),
(4, 'PR-20260419101823', 5, 10.00, '', 'processing', 2, 13, '2026-04-19 08:18:23'),
(5, 'PR-20260419103341', 3, 1.00, '', 'processing', 3, 13, '2026-04-19 08:33:41'),
(6, 'PR-20260419111357', 3, 1.00, '', 'processing', 4, 13, '2026-04-19 09:13:57'),
(7, 'PR-20260420042834', 3, 2.00, '', '', NULL, 20, '2026-04-20 02:28:34'),
(8, 'PR-20260420042959', 3, 1.00, '', 'processing', 5, 20, '2026-04-20 02:29:59'),
(9, 'PR-20260421043240', 3, 10.00, '', 'processing', 6, 20, '2026-04-21 02:32:40'),
(10, 'PR-20260421053121', 3, 1.00, '', 'processing', 7, 20, '2026-04-21 03:31:21'),
(11, 'PR-20260421130500', 3, 10.00, '', 'processing', 8, 20, '2026-04-21 11:05:00'),
(12, 'PR-20260421132246', 3, 10.00, '', 'processing', 9, 20, '2026-04-21 11:22:46'),
(13, 'PR-20260421134132', 2, 10.00, '', 'processing', 10, 20, '2026-04-21 11:41:32'),
(14, 'PR-20260421142045', 2, 10.00, '', '', NULL, 20, '2026-04-21 12:20:45'),
(15, 'PR-2026042716274451', 3, 10.00, '', 'processing', 14, 20, '2026-04-27 16:27:44');

-- --------------------------------------------------------

--
-- Table structure for table `racks`
--

CREATE TABLE `racks` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `racks`
--

INSERT INTO `racks` (`id`, `name`, `description`) VALUES
(1, 'A-01', ''),
(2, 'A-02', ''),
(3, 'A-03', '');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_slug` varchar(50) NOT NULL,
  `role_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_slug`, `role_name`) VALUES
(1, 'owner', 'Owner / Pemilik'),
(2, 'admin', 'Admin Gudang'),
(3, 'produksi', 'Tim Produksi'),
(4, 'auditor', 'Auditor'),
(5, 'supervisor_gudang', 'Supervisor Gudang'),
(7, 'pegawai_gudang', 'Pegawai Gudang'),
(10, 'otorisasi', 'Otorisasi'),
(11, 'gudang_pilar', 'Admin Gudang Pilar'),
(14, 'admin_dapur_1', 'admin dapur 1'),
(15, 'admin_dapur_2', 'admin dapur 2');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role_slug` varchar(50) NOT NULL,
  `permission_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_slug`, `permission_name`) VALUES
(24, 'supervisor_gudang', 'master_gudang'),
(25, 'supervisor_gudang', 'master_produk'),
(26, 'supervisor_gudang', 'master_kategori'),
(27, 'supervisor_gudang', 'master_bahan'),
(28, 'supervisor_gudang', 'master_satuan'),
(29, 'supervisor_gudang', 'master_resep'),
(30, 'supervisor_gudang', 'view_dashboard'),
(31, 'supervisor_gudang', 'stok_opname'),
(229, 'pegawai_gudang', 'master_gudang'),
(230, 'pegawai_gudang', 'edit_master_gudang'),
(231, 'pegawai_gudang', 'hapus_master_gudang'),
(232, 'pegawai_gudang', 'master_produk'),
(233, 'pegawai_gudang', 'edit_master_produk'),
(234, 'pegawai_gudang', 'master_kategori'),
(235, 'pegawai_gudang', 'edit_master_kategori'),
(236, 'pegawai_gudang', 'hapus_master_kategori'),
(237, 'pegawai_gudang', 'master_bahan'),
(238, 'pegawai_gudang', 'edit_master_bahan'),
(239, 'pegawai_gudang', 'hapus_master_bahan'),
(240, 'pegawai_gudang', 'master_satuan'),
(241, 'pegawai_gudang', 'edit_master_satuan'),
(242, 'pegawai_gudang', 'hapus_master_satuan'),
(243, 'pegawai_gudang', 'view_dashboard'),
(244, 'pegawai_gudang', 'stok_opname'),
(245, 'pegawai_gudang', 'otorisasi'),
(350, 'admin_dapur_2', 'akses_dapur_2'),
(351, 'admin_dapur_2', 'manajemen_dapur'),
(352, 'admin_dapur_2', 'edit_manajemen_dapur'),
(353, 'admin_dapur_2', 'hapus_manajemen_dapur'),
(354, 'admin_dapur_2', 'master_bahan'),
(355, 'admin_dapur_2', 'edit_master_bahan'),
(356, 'admin_dapur_2', 'hapus_master_bahan'),
(357, 'admin_dapur_2', 'view_dashboard'),
(392, 'owner', 'manajemen_dapur'),
(393, 'owner', 'edit_manajemen_dapur'),
(394, 'owner', 'hapus_manajemen_dapur'),
(395, 'owner', 'master_gudang'),
(396, 'owner', 'edit_master_gudang'),
(397, 'owner', 'hapus_master_gudang'),
(398, 'owner', 'master_produk'),
(399, 'owner', 'edit_master_produk'),
(400, 'owner', 'hapus_master_produk'),
(401, 'owner', 'master_kategori'),
(402, 'owner', 'edit_master_kategori'),
(403, 'owner', 'hapus_master_kategori'),
(404, 'owner', 'master_bahan'),
(405, 'owner', 'edit_master_bahan'),
(406, 'owner', 'hapus_master_bahan'),
(407, 'owner', 'master_satuan'),
(408, 'owner', 'edit_master_satuan'),
(409, 'owner', 'hapus_master_satuan'),
(410, 'owner', 'master_resep'),
(411, 'owner', 'master_user'),
(412, 'owner', 'master_stok_pusat'),
(413, 'owner', 'edit_master_stok_pusat'),
(414, 'owner', 'hapus_master_stok_pusat'),
(415, 'owner', 'view_dashboard'),
(416, 'owner', 'stok_opname'),
(417, 'owner', 'otorisasi'),
(418, 'owner', 'laporan_produksi'),
(419, 'owner', 'laporan_keluar'),
(420, 'owner', 'audit_logs'),
(421, 'owner', 'analisa_produk'),
(422, 'owner', 'laporan_bahan'),
(423, 'owner', 'laporan_produk_jadi'),
(424, 'owner', 'laporan_bom'),
(425, 'owner', 'laporan_opname'),
(443, 'auditor', 'view_dashboard'),
(444, 'auditor', 'audit_logs'),
(445, 'auditor', 'analisa_produk'),
(446, 'auditor', 'laporan_bahan'),
(447, 'auditor', 'laporan_produk_jadi'),
(448, 'auditor', 'laporan_bom'),
(449, 'auditor', 'laporan_opname'),
(450, 'admin_dapur_1', 'akses_dapur_1'),
(451, 'admin_dapur_1', 'manajemen_dapur'),
(452, 'admin_dapur_1', 'edit_manajemen_dapur'),
(453, 'admin_dapur_1', 'hapus_manajemen_dapur'),
(454, 'admin_dapur_1', 'master_bahan'),
(455, 'admin_dapur_1', 'edit_master_bahan'),
(456, 'admin_dapur_1', 'hapus_master_bahan'),
(457, 'admin_dapur_1', 'master_resep'),
(458, 'admin_dapur_1', 'view_dashboard'),
(577, 'otorisasi', 'manajemen_dapur'),
(578, 'otorisasi', 'edit_manajemen_dapur'),
(579, 'otorisasi', 'view_dashboard'),
(580, 'otorisasi', 'stok_opname'),
(581, 'otorisasi', 'otorisasi'),
(582, 'otorisasi', 'laporan_produk_jadi'),
(583, 'otorisasi', 'laporan_bom'),
(584, 'otorisasi', 'laporan_opname');

-- --------------------------------------------------------

--
-- Table structure for table `stok_opname`
--

CREATE TABLE `stok_opname` (
  `id` int(11) NOT NULL,
  `opname_no` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stok_opname`
--

INSERT INTO `stok_opname` (`id`, `opname_no`, `user_id`, `created_at`) VALUES
(1, 'SO-20260418195743-918', 13, '2026-04-18 17:57:43'),
(2, 'SO-20260420044547-184', 20, '2026-04-20 02:45:47'),
(3, 'SO-20260421052227-170', 20, '2026-04-21 03:22:27');

-- --------------------------------------------------------

--
-- Table structure for table `stok_opname_details`
--

CREATE TABLE `stok_opname_details` (
  `id` int(11) NOT NULL,
  `opname_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `system_stock` decimal(10,2) NOT NULL,
  `physical_stock` decimal(10,2) NOT NULL,
  `difference` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stok_opname_details`
--

INSERT INTO `stok_opname_details` (`id`, `opname_id`, `material_id`, `system_stock`, `physical_stock`, `difference`, `notes`) VALUES
(1, 1, 3, 100.00, 1.00, -99.00, ''),
(2, 2, 3, 7.50, 1.00, -6.50, ''),
(3, 3, 3, 10.00, 1.00, -9.00, '');

-- --------------------------------------------------------

--
-- Table structure for table `stok_opname_keys`
--

CREATE TABLE `stok_opname_keys` (
  `id` int(11) NOT NULL,
  `access_code` varchar(10) NOT NULL,
  `valid_until` datetime NOT NULL,
  `status` enum('active','used','expired') DEFAULT 'active',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stok_opname_keys`
--

INSERT INTO `stok_opname_keys` (`id`, `access_code`, `valid_until`, `status`, `created_by`, `created_at`) VALUES
(1, '674536', '2026-04-19 19:38:37', 'expired', 13, '2026-04-18 17:38:37'),
(2, '216927', '2026-04-19 19:50:58', 'expired', 13, '2026-04-18 17:50:58'),
(3, '836855', '2026-04-21 04:45:09', 'expired', 20, '2026-04-20 02:45:09'),
(4, '709921', '2026-04-22 05:21:58', 'expired', 20, '2026-04-21 03:21:58'),
(5, '206903', '2026-04-25 04:57:33', 'expired', 20, '2026-04-24 04:57:33'),
(6, '152025', '2026-05-02 08:42:13', 'expired', 20, '2026-05-01 08:42:13');

-- --------------------------------------------------------

--
-- Table structure for table `store_profile`
--

CREATE TABLE `store_profile` (
  `id` int(11) NOT NULL,
  `dashboard_announcement` varchar(255) DEFAULT NULL,
  `store_name` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `req_approval_in` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Barang Masuk Manual',
  `req_approval_out` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Barang Keluar Manual',
  `req_approval_po` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Purchase Order',
  `req_approval_pr` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Permintaan Barang',
  `req_approval_print` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Izin Cetak',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `store_profile`
--

INSERT INTO `store_profile` (`id`, `dashboard_announcement`, `store_name`, `phone`, `email`, `address`, `logo_path`, `req_approval_in`, `req_approval_out`, `req_approval_po`, `req_approval_pr`, `req_approval_print`, `updated_at`) VALUES
(1, 'Stok opname', 'ROTIKU ERP', '(061) 1234567', 'logistik@rotiku.com', 'Jl. Gudang Utama No. 123, Medan, Sumatera Utara', 'uploads/logo_toko_1776839134.png', 1, 1, 1, 1, 1, '2026-04-30 16:28:26');

-- --------------------------------------------------------

--
-- Table structure for table `store_titipan_stocks`
--

CREATE TABLE `store_titipan_stocks` (
  `id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL COMMENT 'Lokasi Store/Gudang',
  `titipan_id` int(11) NOT NULL COMMENT 'ID dari master barang_titipan',
  `stock` int(11) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `store_titipan_stocks`
--

INSERT INTO `store_titipan_stocks` (`id`, `warehouse_id`, `titipan_id`, `stock`, `updated_at`) VALUES
(1, 1, 2, 4, '2026-04-23 14:29:50'),
(2, 1, 3, 1, '2026-04-23 14:29:50');

-- --------------------------------------------------------

--
-- Table structure for table `supervisor_pins`
--

CREATE TABLE `supervisor_pins` (
  `id` int(11) NOT NULL,
  `pin_type` varchar(50) DEFAULT 'delete_production',
  `pin_code` varchar(10) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supervisor_pins`
--

INSERT INTO `supervisor_pins` (`id`, `pin_type`, `pin_code`, `updated_at`) VALUES
(1, 'delete_production', '123456', '2026-04-10 16:18:02');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `contact_person`, `phone`, `email`, `address`, `description`, `created_at`) VALUES
(1, 'CV codifyhub.id', 'Randy', '085835116946', 'muhammadrandykarna@gmail.com', 'Medan jalan beringin', 'Spesialis Tepung', '2026-04-16 17:57:27'),
(2, 'PT Nusa Tirta', 'Ata', '082948294872', 'ata@gmail.com', 'Medan jalan mansyur', 'Supp Kemasan', '2026-04-16 17:58:12');

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `menu` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`id`, `user_id`, `action`, `menu`, `description`, `ip_address`, `created_at`) VALUES
(1, 1, 'save', 'Master Produk', 'Eksekusi [save] di menu [Master Produk]. Data: {\"id\":\"\",\"code\":\"G02\",\"name\":\"Roti kacanng\",\"category\":\"Roti Manis\",\"price\":\"0\"}', '::1', '2026-04-11 05:54:47'),
(2, 1, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-11 06:18:08'),
(3, 1, 'save', 'Master Bahan', 'Eksekusi [save] di menu [Master Bahan]. Data: {\"id\":\"\",\"code\":\"G02\",\"name\":\"Kacang\",\"unit\":\"Kg\",\"stock\":\"10\",\"min_stock\":\"100\"}', '::1', '2026-04-11 06:18:28'),
(4, 1, 'generate', 'Otorisasi', 'Eksekusi [generate] di menu [Otorisasi]. Data: []', '::1', '2026-04-11 06:33:18'),
(5, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-11 06:34:04'),
(6, 2, 'save', 'Input Produksi', 'Eksekusi [save] di menu [Input Produksi]. Data: {\"employee_id\":\"1\",\"product_id\":[\"1\",\"6\"],\"quantity\":[\"1\",\"1\"],\"warehouse_id\":\"1\",\"notes\":\"\"}', '::1', '2026-04-11 06:34:19'),
(7, 2, 'save', 'Input Produksi', 'Eksekusi [save] di menu [Input Produksi]. Data: {\"employee_id\":\"1\",\"product_id\":[\"1\",\"2\"],\"quantity\":[\"1\",\"1\"],\"warehouse_id\":\"1\",\"notes\":\"\"}', '::1', '2026-04-11 06:34:34'),
(8, 2, 'cancel_produksi', 'Riwayat Produksi', 'Eksekusi [cancel_produksi] di menu [Riwayat Produksi]. Data: {\"prod_id\":\"70\",\"pin\":\"123456\"}', '::1', '2026-04-11 06:35:07'),
(9, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-11 06:35:21'),
(10, 2, 'save', 'Input Produksi', 'Eksekusi [save] di menu [Input Produksi]. Data: {\"employee_id\":\"2\",\"product_id\":[\"2\"],\"quantity\":[\"1\"],\"warehouse_id\":\"1\",\"notes\":\"\"}', '::1', '2026-04-11 06:35:26'),
(11, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-11 06:35:42'),
(12, 3, 'scan', 'Scan Barcode', 'Eksekusi [scan] di menu [Scan Barcode]. Data: {\"barcode\":\"D1126-002-1\"}', '::1', '2026-04-11 06:35:54'),
(13, 3, 'execute_validasi', 'Scan Barcode', 'Eksekusi [execute_validasi] di menu [Scan Barcode]. Data: {\"prod_id\":\"71\",\"status\":\"ditolak\"}', '::1', '2026-04-11 06:35:58'),
(14, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-11 06:36:16'),
(15, 2, 'update_revisi', 'Riwayat Produksi', 'Eksekusi [update_revisi] di menu [Riwayat Produksi]. Data: {\"prod_id\":\"71\",\"detail_id\":[\"97\"],\"quantity\":[\"2\"]}', '::1', '2026-04-11 06:36:22'),
(16, 3, 'scan', 'Scan Barcode', 'Eksekusi [scan] di menu [Scan Barcode]. Data: {\"barcode\":\"D1126-002-1\"}', '::1', '2026-04-11 06:36:32'),
(17, 3, 'execute_validasi', 'Scan Barcode', 'Eksekusi [execute_validasi] di menu [Scan Barcode]. Data: {\"prod_id\":\"71\",\"status\":\"masuk_gudang\"}', '::1', '2026-04-11 06:36:32'),
(18, 1, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-11 06:37:45'),
(19, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '::1', '2026-04-12 15:27:36'),
(20, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-12 15:27:36'),
(21, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-12 15:27:36'),
(22, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '::1', '2026-04-12 15:28:20'),
(23, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-12 15:28:20'),
(24, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-12 15:28:20'),
(25, 1, 'save_user', 'Master User', 'Eksekusi [save_user] di menu [Master User]. Data: {\"id\":\"\",\"name\":\"Admin Gudang Utama\",\"username\":\"gudang\",\"password\":\"******\",\"role\":\"gudang_pilar\"}', '::1', '2026-04-12 15:28:41'),
(26, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-12 15:28:42'),
(27, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '::1', '2026-04-12 15:41:14'),
(28, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-12 15:41:14'),
(29, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-12 15:41:14'),
(30, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '::1', '2026-04-12 15:41:58'),
(31, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-12 15:41:58'),
(32, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-12 15:41:59'),
(33, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-12 16:23:42'),
(34, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-12 16:23:52'),
(35, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-12 16:24:34'),
(36, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-12 16:27:02'),
(37, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-12 16:29:33'),
(38, 1, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-12 16:36:18'),
(39, 1, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-12 16:46:56'),
(40, 1, 'tarik_stok', 'Master Bahan', 'Eksekusi [tarik_stok] di menu [Master Bahan]. Data: {\"pilar_id\":\"3\",\"qty\":\"20\"}', '::1', '2026-04-12 16:47:10'),
(41, 1, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-12 17:04:59'),
(42, 1, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-12 17:05:04'),
(43, 1, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-12 17:13:18'),
(44, 1, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-12 17:13:29'),
(45, 1, 'submit_request', 'Master Bahan', 'Eksekusi [submit_request] di menu [Master Bahan]. Data: {\"action\":\"submit_request\",\"warehouse_id\":\"1\",\"pilar_id\":\"3\",\"qty\":\"20\"}', '::1', '2026-04-12 17:13:35'),
(46, 1, 'submit_request', 'Master Bahan', 'Eksekusi [submit_request] di menu [Master Bahan]. Data: {\"action\":\"submit_request\",\"warehouse_id\":\"2\",\"pilar_id\":\"3\",\"qty\":\"20\"}', '::1', '2026-04-12 17:13:40'),
(47, 1, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:15:56'),
(48, 1, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:30:02'),
(49, 1, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:30:06'),
(50, 1, 'submit_request', 'Master Bahan', 'Eksekusi [submit_request] di menu [Master Bahan]. Data: {\"warehouse_id\":\"2\",\"pilar_id\":\"3\",\"qty\":\"100\"}', '::1', '2026-04-13 08:30:11'),
(51, 1, 'submit_request', 'Master Bahan', 'Eksekusi [submit_request] di menu [Master Bahan]. Data: {\"warehouse_id\":\"2\",\"pilar_id\":\"3\",\"qty\":\"20\"}', '::1', '2026-04-13 08:30:16'),
(52, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 08:31:25'),
(53, NULL, 'proses_kirim', 'Persetujuan', 'Eksekusi [proses_kirim] di menu [Persetujuan]. Data: {\"action\":\"proses_kirim\",\"id\":\"1\",\"qty_approved\":\"20\"}', '::1', '2026-04-13 08:31:38'),
(54, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 08:31:38'),
(55, 1, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:31:57'),
(56, 1, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:32:03'),
(57, 1, 'submit_request', 'Master Bahan', 'Eksekusi [submit_request] di menu [Master Bahan]. Data: {\"warehouse_id\":\"1\",\"pilar_id\":\"2\",\"qty\":\"100\"}', '::1', '2026-04-13 08:32:10'),
(58, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 08:32:14'),
(59, NULL, 'proses_kirim', 'Persetujuan', 'Eksekusi [proses_kirim] di menu [Persetujuan]. Data: {\"action\":\"proses_kirim\",\"id\":\"2\",\"qty_approved\":\"100\"}', '::1', '2026-04-13 08:32:18'),
(60, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 08:32:18'),
(61, 1, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:32:23'),
(62, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:46:17'),
(63, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:46:26'),
(64, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:46:27'),
(65, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:46:28'),
(66, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:46:51'),
(67, 1, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:46:53'),
(68, 1, 'submit_request', 'Master Bahan', 'Eksekusi [submit_request] di menu [Master Bahan]. Data: {\"warehouse_id\":\"1\",\"pilar_id\":\"3\",\"qty\":\"20\"}', '::1', '2026-04-13 08:46:59'),
(69, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:46:59'),
(70, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 08:47:04'),
(71, NULL, 'proses_kirim', 'Persetujuan', 'Eksekusi [proses_kirim] di menu [Persetujuan]. Data: {\"action\":\"proses_kirim\",\"id\":\"3\",\"qty_approved\":\"20\"}', '::1', '2026-04-13 08:47:06'),
(72, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 08:47:07'),
(73, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:47:10'),
(74, 1, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:53:36'),
(75, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:53:36'),
(76, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:53:39'),
(77, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:53:40'),
(78, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:53:41'),
(79, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:53:54'),
(80, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:53:55'),
(81, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:53:57'),
(82, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:53:57'),
(83, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:53:58'),
(84, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:53:59'),
(85, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:53:59'),
(86, 1, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:54:01'),
(87, 1, 'submit_request', 'Master Bahan', 'Eksekusi [submit_request] di menu [Master Bahan]. Data: {\"warehouse_id\":\"1\",\"pilar_id\":\"3\",\"qty\":\"10\"}', '::1', '2026-04-13 08:54:08'),
(88, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:54:08'),
(89, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 08:54:17'),
(90, NULL, 'proses_kirim', 'Persetujuan', 'Eksekusi [proses_kirim] di menu [Persetujuan]. Data: {\"action\":\"proses_kirim\",\"id\":\"4\",\"qty_approved\":\"10\"}', '::1', '2026-04-13 08:54:19'),
(91, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 08:54:19'),
(92, 1, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:54:25'),
(93, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:54:25'),
(94, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 08:59:29'),
(95, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 08:59:30'),
(96, 1, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:59:41'),
(97, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:59:41'),
(98, 1, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:59:42'),
(99, 1, 'submit_request', 'Master Bahan', 'Eksekusi [submit_request] di menu [Master Bahan]. Data: {\"warehouse_id\":\"1\",\"pilar_id\":\"3\",\"qty\":\"1\"}', '::1', '2026-04-13 08:59:50'),
(100, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 08:59:50'),
(101, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 08:59:52'),
(102, NULL, 'proses_kirim', 'Persetujuan', 'Eksekusi [proses_kirim] di menu [Persetujuan]. Data: {\"action\":\"proses_kirim\",\"id\":\"5\",\"qty_approved\":\"1\"}', '::1', '2026-04-13 08:59:54'),
(103, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 08:59:54'),
(104, 1, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 09:00:00'),
(105, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 09:00:00'),
(106, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 09:00:06'),
(107, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 09:00:10'),
(108, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 09:00:21'),
(109, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 09:00:21'),
(110, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 09:00:22'),
(111, 1, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 09:01:21'),
(112, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 09:01:21'),
(113, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 09:02:54'),
(114, 1, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 09:03:00'),
(115, 1, 'submit_request', 'Master Bahan', 'Eksekusi [submit_request] di menu [Master Bahan]. Data: {\"warehouse_id\":\"1\",\"pilar_id\":\"3\",\"qty\":\"1\"}', '::1', '2026-04-13 09:03:02'),
(116, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 09:03:02'),
(117, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 09:03:08'),
(118, NULL, 'proses_kirim', 'Persetujuan', 'Eksekusi [proses_kirim] di menu [Persetujuan]. Data: {\"action\":\"proses_kirim\",\"id\":\"6\",\"qty_approved\":\"1\"}', '::1', '2026-04-13 09:03:10'),
(119, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 09:03:10'),
(120, 1, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 09:03:15'),
(121, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 09:03:15'),
(122, 1, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 09:03:20'),
(123, 1, 'submit_request', 'Master Bahan', 'Eksekusi [submit_request] di menu [Master Bahan]. Data: {\"warehouse_id\":\"1\",\"pilar_id\":\"3\",\"qty\":\"10\"}', '::1', '2026-04-13 09:03:25'),
(124, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 09:03:25'),
(125, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 09:03:34'),
(126, NULL, 'proses_kirim', 'Persetujuan', 'Eksekusi [proses_kirim] di menu [Persetujuan]. Data: {\"action\":\"proses_kirim\",\"id\":\"7\",\"qty_approved\":\"10\"}', '::1', '2026-04-13 09:03:36'),
(127, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 09:03:36'),
(128, 1, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 09:03:39'),
(129, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 09:03:39'),
(130, 1, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 09:03:43'),
(131, 1, 'submit_request', 'Master Bahan', 'Eksekusi [submit_request] di menu [Master Bahan]. Data: {\"warehouse_id\":\"1\",\"pilar_id\":\"2\",\"qty\":\"1\"}', '::1', '2026-04-13 09:03:47'),
(132, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 09:03:47'),
(133, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 09:03:51'),
(134, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 09:04:04'),
(135, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 09:05:22'),
(136, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-13 09:16:48'),
(137, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 15:04:50'),
(138, 1, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 15:18:34'),
(139, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 15:18:34'),
(140, 1, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 15:19:40'),
(141, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 15:19:40'),
(142, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"edit\",\"old_slug\":\"owner\",\"role_name\":\"Owner \\/ Pemilik\",\"role_slug\":\"owner\",\"permissions\":[\"manajemen_dapur\",\"edit_manajemen_dapur\",\"hapus_manajemen_dapur\",\"master_gudang\",\"edit_master_gudang\",\"hapus_master_gudang\",\"master_produk\",\"edit_master_produk\",\"hapus_master_produk\",\"master_kategori\",\"edit_master_kategori\",\"hapus_master_kategori\",\"master_bahan\",\"edit_master_bahan\",\"hapus_master_bahan\",\"master_satuan\",\"edit_master_satuan\",\"hapus_master_satuan\",\"master_resep\",\"master_user\",\"view_dashboard\",\"stok_opname\",\"otorisasi\",\"laporan_produksi\",\"laporan_keluar\",\"audit_logs\",\"analisa_produk\",\"laporan_bahan\",\"laporan_produk_jadi\",\"laporan_bom\",\"laporan_opname\"]}', '::1', '2026-04-13 15:20:01'),
(143, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"edit\",\"old_slug\":\"owner\",\"role_name\":\"Owner \\/ Pemilik\",\"role_slug\":\"owner\",\"permissions\":[\"manajemen_dapur\",\"edit_manajemen_dapur\",\"hapus_manajemen_dapur\",\"master_gudang\",\"edit_master_gudang\",\"hapus_master_gudang\",\"master_produk\",\"edit_master_produk\",\"hapus_master_produk\",\"master_kategori\",\"edit_master_kategori\",\"hapus_master_kategori\",\"master_bahan\",\"edit_master_bahan\",\"hapus_master_bahan\",\"master_satuan\",\"edit_master_satuan\",\"hapus_master_satuan\",\"master_resep\",\"master_user\",\"master_stok_pusat\",\"edit_master_stok_pusat\",\"hapus_master_stok_pusat\",\"view_dashboard\",\"stok_opname\",\"otorisasi\",\"laporan_produksi\",\"laporan_keluar\",\"audit_logs\",\"analisa_produk\",\"laporan_bahan\",\"laporan_produk_jadi\",\"laporan_bom\",\"laporan_opname\"]}', '::1', '2026-04-13 15:20:26'),
(144, 1, 'save', 'Manajemen Dapur', 'Eksekusi [save] di menu [Manajemen Dapur]. Data: {\"id\":\"\",\"name\":\"Dapur 1\",\"location\":\"Medan \"}', '::1', '2026-04-13 15:23:27'),
(145, 1, 'save', 'Manajemen Dapur', 'Eksekusi [save] di menu [Manajemen Dapur]. Data: {\"id\":\"\",\"name\":\"dapur 2\",\"location\":\"medan petisahj\\r\\n\"}', '::1', '2026-04-13 15:23:42'),
(146, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"add\",\"old_slug\":\"\",\"role_name\":\"Akun dapur 1\",\"role_slug\":\"akun_dapur_1\",\"permissions\":[\"akses_dapur_1\",\"manajemen_dapur\",\"view_dashboard\"]}', '::1', '2026-04-13 15:24:12'),
(147, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"edit\",\"old_slug\":\"akun_dapur_1\",\"role_name\":\"admin dapur 1\",\"role_slug\":\"akun_dapur_1\",\"permissions\":[\"akses_dapur_1\",\"manajemen_dapur\",\"view_dashboard\"]}', '::1', '2026-04-13 15:24:28'),
(148, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '::1', '2026-04-13 15:24:49'),
(149, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-13 15:24:49'),
(150, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-13 15:24:49'),
(151, 1, 'save_user', 'Master User', 'Eksekusi [save_user] di menu [Master User]. Data: {\"id\":\"\",\"name\":\"Admin Dapur 1\",\"username\":\"dapur1\",\"password\":\"******\",\"role\":\"akun_dapur_1\"}', '::1', '2026-04-13 15:25:19'),
(152, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-13 15:25:19'),
(153, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"edit\",\"old_slug\":\"akun_dapur_1\",\"role_name\":\"admin dapur 1\",\"role_slug\":\"akun_dapur_1\",\"permissions\":[\"akses_dapur_1\",\"manajemen_dapur\",\"master_bahan\",\"edit_master_bahan\",\"hapus_master_bahan\",\"view_dashboard\"]}', '::1', '2026-04-13 15:30:41'),
(154, NULL, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 15:30:45'),
(155, NULL, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 15:30:45'),
(156, NULL, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 15:45:46'),
(157, NULL, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 15:45:46'),
(158, NULL, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 15:45:52'),
(159, NULL, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 15:45:57'),
(160, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"add\",\"old_slug\":\"\",\"role_name\":\"admin dapur 2\",\"role_slug\":\"admin_dapur_2\",\"permissions\":[\"akses_dapur_2\",\"manajemen_dapur\",\"edit_manajemen_dapur\",\"hapus_manajemen_dapur\",\"master_bahan\",\"edit_master_bahan\",\"hapus_master_bahan\",\"view_dashboard\"]}', '::1', '2026-04-13 15:46:33'),
(161, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '::1', '2026-04-13 15:46:37'),
(162, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-13 15:46:37'),
(163, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-13 15:46:37'),
(164, 1, 'save_user', 'Master User', 'Eksekusi [save_user] di menu [Master User]. Data: {\"id\":\"\",\"name\":\"admin dapur 2\",\"username\":\"dapur2\",\"password\":\"******\",\"role\":\"admin_dapur_2\"}', '::1', '2026-04-13 15:46:50'),
(165, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-13 15:46:50'),
(166, NULL, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 15:47:06'),
(167, NULL, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 15:47:06'),
(168, NULL, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 15:47:08'),
(169, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 15:54:37'),
(170, NULL, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 15:55:20'),
(171, NULL, 'submit_request', 'Master Bahan', 'Eksekusi [submit_request] di menu [Master Bahan]. Data: {\"warehouse_id\":\"2\",\"pilar_id\":\"2\",\"qty\":\"10\"}', '::1', '2026-04-13 15:55:26'),
(172, NULL, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 15:55:26'),
(173, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 15:55:32'),
(174, NULL, 'proses_kirim', 'Persetujuan', 'Eksekusi [proses_kirim] di menu [Persetujuan]. Data: {\"action\":\"proses_kirim\",\"id\":\"9\",\"qty_approved\":\"10\"}', '::1', '2026-04-13 15:55:36'),
(175, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 15:55:36'),
(176, NULL, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 15:55:38'),
(177, NULL, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 15:55:39'),
(178, NULL, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 15:55:41'),
(179, NULL, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 15:55:42'),
(180, NULL, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 15:55:45'),
(181, NULL, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 15:55:56'),
(182, NULL, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 15:55:57'),
(183, NULL, 'submit_request', 'Master Bahan', 'Eksekusi [submit_request] di menu [Master Bahan]. Data: {\"warehouse_id\":\"1\",\"pilar_id\":\"3\",\"qty\":\"100\"}', '::1', '2026-04-13 15:56:02'),
(184, NULL, 'submit_request', 'Master Bahan', 'Eksekusi [submit_request] di menu [Master Bahan]. Data: {\"warehouse_id\":\"1\",\"pilar_id\":\"3\",\"qty\":\"1\"}', '::1', '2026-04-13 15:56:06'),
(185, NULL, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 15:56:06'),
(186, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 15:56:14'),
(187, NULL, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:01:54'),
(188, NULL, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:01:54'),
(189, NULL, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:01:56'),
(190, NULL, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:01:58'),
(191, NULL, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:02:06'),
(192, NULL, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:02:06'),
(193, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '::1', '2026-04-13 16:02:23'),
(194, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-13 16:02:23'),
(195, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-13 16:02:23'),
(196, 1, 'delete_user', 'Master User', 'Eksekusi [delete_user] di menu [Master User]. Data: {\"id\":\"14\"}', '::1', '2026-04-13 16:02:26'),
(197, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-13 16:02:27'),
(198, 1, 'delete_user', 'Master User', 'Eksekusi [delete_user] di menu [Master User]. Data: {\"id\":\"15\"}', '::1', '2026-04-13 16:02:30'),
(199, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-13 16:02:30'),
(200, 1, 'delete', 'Manajemen Role', 'Eksekusi [delete] di menu [Manajemen Role]. Data: {\"slug\":\"akun_dapur_1\"}', '::1', '2026-04-13 16:02:37'),
(201, 1, 'delete', 'Manajemen Role', 'Eksekusi [delete] di menu [Manajemen Role]. Data: {\"slug\":\"admin_dapur_2\"}', '::1', '2026-04-13 16:02:40'),
(202, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"add\",\"old_slug\":\"\",\"role_name\":\"admin dapur 1\",\"role_slug\":\"admin_dapur_1\",\"permissions\":[\"akses_dapur_1\",\"manajemen_dapur\",\"edit_manajemen_dapur\",\"hapus_manajemen_dapur\",\"master_bahan\",\"edit_master_bahan\",\"hapus_master_bahan\",\"view_dashboard\"]}', '::1', '2026-04-13 16:03:00'),
(203, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"add\",\"old_slug\":\"\",\"role_name\":\"admin dapur 2\",\"role_slug\":\"admin_dapur_2\",\"permissions\":[\"akses_dapur_2\",\"manajemen_dapur\",\"edit_manajemen_dapur\",\"hapus_manajemen_dapur\",\"master_bahan\",\"edit_master_bahan\",\"hapus_master_bahan\",\"view_dashboard\"]}', '::1', '2026-04-13 16:03:22'),
(204, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '::1', '2026-04-13 16:03:35'),
(205, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-13 16:03:35'),
(206, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-13 16:03:35'),
(207, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '::1', '2026-04-13 16:04:01'),
(208, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-13 16:04:01'),
(209, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-13 16:04:01'),
(210, 1, 'save_user', 'Master User', 'Eksekusi [save_user] di menu [Master User]. Data: {\"id\":\"\",\"name\":\"Admin Dapur 1\",\"username\":\"dapur1\",\"password\":\"******\",\"role\":\"admin_dapur_1\"}', '::1', '2026-04-13 16:04:20'),
(211, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-13 16:04:20'),
(212, 1, 'save_user', 'Master User', 'Eksekusi [save_user] di menu [Master User]. Data: {\"id\":\"\",\"name\":\"admin dapur 2\",\"username\":\"dapur2\",\"password\":\"******\",\"role\":\"admin_dapur_2\"}', '::1', '2026-04-13 16:05:14'),
(213, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-13 16:05:14'),
(214, 16, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:05:47'),
(215, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:05:47'),
(216, 17, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:06:10'),
(217, 17, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:06:10'),
(218, 16, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:06:17'),
(219, 16, 'submit_request', 'Master Bahan', 'Eksekusi [submit_request] di menu [Master Bahan]. Data: {\"warehouse_id\":\"1\",\"pilar_id\":\"2\",\"qty\":\"10\"}', '::1', '2026-04-13 16:06:22'),
(220, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:06:23'),
(221, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 16:06:31'),
(222, NULL, 'proses_kirim', 'Persetujuan', 'Eksekusi [proses_kirim] di menu [Persetujuan]. Data: {\"action\":\"proses_kirim\",\"id\":\"8\",\"qty_approved\":\"1\"}', '::1', '2026-04-13 16:06:35'),
(223, NULL, 'tolak_kirim', 'Persetujuan', 'Eksekusi [tolak_kirim] di menu [Persetujuan]. Data: {\"action\":\"tolak_kirim\",\"id\":\"8\"}', '::1', '2026-04-13 16:06:40'),
(224, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 16:06:40'),
(225, NULL, 'proses_kirim', 'Persetujuan', 'Eksekusi [proses_kirim] di menu [Persetujuan]. Data: {\"action\":\"proses_kirim\",\"id\":\"10\",\"qty_approved\":\"1\"}', '::1', '2026-04-13 16:06:42'),
(226, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 16:06:42'),
(227, NULL, 'proses_kirim', 'Persetujuan', 'Eksekusi [proses_kirim] di menu [Persetujuan]. Data: {\"action\":\"proses_kirim\",\"id\":\"11\",\"qty_approved\":\"10\"}', '::1', '2026-04-13 16:06:47'),
(228, NULL, 'tolak_kirim', 'Persetujuan', 'Eksekusi [tolak_kirim] di menu [Persetujuan]. Data: {\"action\":\"tolak_kirim\",\"id\":\"11\"}', '::1', '2026-04-13 16:06:50'),
(229, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 16:06:50'),
(230, 16, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:06:55'),
(231, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:06:55'),
(232, 17, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:06:59'),
(233, 17, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:07:03'),
(234, 17, 'submit_request', 'Master Bahan', 'Eksekusi [submit_request] di menu [Master Bahan]. Data: {\"warehouse_id\":\"2\",\"pilar_id\":\"2\",\"qty\":\"20\"}', '::1', '2026-04-13 16:07:08'),
(235, 17, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:07:08'),
(236, 16, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:07:13'),
(237, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:07:13'),
(238, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 16:07:16'),
(239, NULL, 'proses_kirim', 'Persetujuan', 'Eksekusi [proses_kirim] di menu [Persetujuan]. Data: {\"action\":\"proses_kirim\",\"id\":\"12\",\"qty_approved\":\"20\"}', '::1', '2026-04-13 16:07:19'),
(240, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 16:07:19'),
(241, 17, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:07:25'),
(242, 17, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:07:25'),
(243, 17, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:07:29'),
(244, 17, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:07:29'),
(245, 17, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:07:30'),
(246, 17, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:07:30'),
(247, 17, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:07:31'),
(248, 17, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:07:31'),
(249, 17, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:07:31'),
(250, 16, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:07:38'),
(251, 16, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:07:41'),
(252, 16, 'submit_request', 'Master Bahan', 'Eksekusi [submit_request] di menu [Master Bahan]. Data: {\"warehouse_id\":\"1\",\"pilar_id\":\"2\",\"qty\":\"10\"}', '::1', '2026-04-13 16:07:46'),
(253, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:07:46'),
(254, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 16:07:55'),
(255, NULL, 'proses_kirim', 'Persetujuan', 'Eksekusi [proses_kirim] di menu [Persetujuan]. Data: {\"action\":\"proses_kirim\",\"id\":\"13\",\"qty_approved\":\"10\"}', '::1', '2026-04-13 16:07:57'),
(256, NULL, 'proses_kirim', 'Persetujuan', 'Eksekusi [proses_kirim] di menu [Persetujuan]. Data: {\"action\":\"proses_kirim\",\"id\":\"13\",\"qty_approved\":\"10\"}', '::1', '2026-04-13 16:08:00'),
(257, 16, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:08:29'),
(258, 16, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:08:32'),
(259, 16, 'submit_request', 'Master Bahan', 'Eksekusi [submit_request] di menu [Master Bahan]. Data: {\"warehouse_id\":\"1\",\"pilar_id\":\"4\",\"qty\":\"10\"}', '::1', '2026-04-13 16:08:37'),
(260, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:08:37'),
(261, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 16:08:42'),
(262, NULL, 'proses_kirim', 'Persetujuan', 'Eksekusi [proses_kirim] di menu [Persetujuan]. Data: {\"action\":\"proses_kirim\",\"id\":\"14\",\"qty_approved\":\"10\"}', '::1', '2026-04-13 16:08:49'),
(263, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 16:08:49'),
(264, NULL, 'proses_kirim', 'Persetujuan', 'Eksekusi [proses_kirim] di menu [Persetujuan]. Data: {\"action\":\"proses_kirim\",\"id\":\"13\",\"qty_approved\":\"10\"}', '::1', '2026-04-13 16:08:53'),
(265, NULL, 'proses_kirim', 'Persetujuan', 'Eksekusi [proses_kirim] di menu [Persetujuan]. Data: {\"action\":\"proses_kirim\",\"id\":\"13\",\"qty_approved\":\"10\"}', '::1', '2026-04-13 16:09:18'),
(266, 16, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:09:37'),
(267, 17, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:09:46'),
(268, 17, 'submit_request', 'Master Bahan', 'Eksekusi [submit_request] di menu [Master Bahan]. Data: {\"warehouse_id\":\"2\",\"pilar_id\":\"3\",\"qty\":\"1\"}', '::1', '2026-04-13 16:09:52'),
(269, 17, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:09:52'),
(270, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 16:09:58'),
(271, NULL, 'proses_kirim', 'Persetujuan', 'Eksekusi [proses_kirim] di menu [Persetujuan]. Data: {\"action\":\"proses_kirim\",\"id\":\"15\",\"qty_approved\":\"1\"}', '::1', '2026-04-13 16:10:01'),
(272, NULL, 'proses_kirim', 'Persetujuan', 'Eksekusi [proses_kirim] di menu [Persetujuan]. Data: {\"action\":\"proses_kirim\",\"id\":\"13\",\"qty_approved\":\"10\"}', '::1', '2026-04-13 16:10:09'),
(273, 17, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:10:28'),
(274, 17, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:10:28'),
(275, 16, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:10:34'),
(276, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:10:34'),
(277, 17, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:10:41'),
(278, 17, 'submit_request', 'Master Bahan', 'Eksekusi [submit_request] di menu [Master Bahan]. Data: {\"warehouse_id\":\"2\",\"pilar_id\":\"4\",\"qty\":\"10\"}', '::1', '2026-04-13 16:10:46'),
(279, 17, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:10:46'),
(280, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 16:10:51'),
(281, NULL, 'proses_kirim', 'Persetujuan', 'Eksekusi [proses_kirim] di menu [Persetujuan]. Data: {\"action\":\"proses_kirim\",\"id\":\"16\",\"qty_approved\":\"10\"}', '::1', '2026-04-13 16:10:54'),
(282, NULL, 'proses_kirim', 'Persetujuan', 'Eksekusi [proses_kirim] di menu [Persetujuan]. Data: {\"action\":\"proses_kirim\",\"id\":\"16\",\"qty_approved\":\"10\"}', '::1', '2026-04-13 16:10:57'),
(283, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 16:18:16'),
(284, NULL, 'proses_kirim', 'Persetujuan', 'Eksekusi [proses_kirim] di menu [Persetujuan]. Data: {\"action\":\"proses_kirim\",\"id\":\"13\",\"qty_approved\":\"10\"}', '::1', '2026-04-13 16:18:39'),
(285, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 16:18:39'),
(286, 16, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:18:44'),
(287, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:18:44'),
(288, 16, 'save', 'Master Bahan', 'Eksekusi [save] di menu [Master Bahan]. Data: {\"id\":\"12\",\"code\":\"MT01\",\"name\":\"Mentega Blueband\",\"unit\":\"Kg\",\"stock\":\"11\",\"min_stock\":\"1\"}', '::1', '2026-04-13 16:18:55'),
(289, 16, 'save', 'Master Bahan', 'Eksekusi [save] di menu [Master Bahan]. Data: {\"id\":\"12\",\"code\":\"MT01\",\"name\":\"Mentega Blueband\",\"unit\":\"Kg\",\"stock\":\"10\",\"min_stock\":\"1\"}', '::1', '2026-04-13 16:19:03'),
(290, NULL, 'proses_kirim', 'Persetujuan', 'Eksekusi [proses_kirim] di menu [Persetujuan]. Data: {\"action\":\"proses_kirim\",\"id\":\"15\",\"qty_approved\":\"1\"}', '::1', '2026-04-13 16:19:10'),
(291, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 16:19:10'),
(292, 17, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:19:20'),
(293, 17, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:19:20'),
(294, NULL, 'proses_kirim', 'Persetujuan', 'Eksekusi [proses_kirim] di menu [Persetujuan]. Data: {\"action\":\"proses_kirim\",\"id\":\"16\",\"qty_approved\":\"10\"}', '::1', '2026-04-13 16:19:55'),
(295, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 16:19:55'),
(296, 17, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:19:59'),
(297, 17, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:19:59'),
(298, 17, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:20:02'),
(299, 17, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:20:03'),
(300, 17, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 16:20:03'),
(301, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 16:21:04'),
(302, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 16:28:45'),
(303, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '::1', '2026-04-13 16:33:14'),
(304, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-13 16:33:14'),
(305, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-13 16:33:14'),
(306, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '::1', '2026-04-13 17:00:51'),
(307, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-13 17:00:51'),
(308, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-13 17:00:51'),
(309, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '::1', '2026-04-13 17:05:49'),
(310, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-13 17:05:49'),
(311, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-13 17:05:49'),
(312, 1, 'save_employee', 'Master User', 'Eksekusi [save_employee] di menu [Master User]. Data: {\"id\":\"1\",\"name\":\"Andi\",\"kitchen_id\":\"1\",\"pin\":\"1234\"}', '::1', '2026-04-13 17:06:17'),
(313, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-13 17:06:17'),
(314, 1, 'save_employee', 'Master User', 'Eksekusi [save_employee] di menu [Master User]. Data: {\"id\":\"2\",\"name\":\"Budi\",\"kitchen_id\":\"1\",\"pin\":\"1234\"}', '::1', '2026-04-13 17:06:26'),
(315, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-13 17:06:26'),
(316, 1, 'save_employee', 'Master User', 'Eksekusi [save_employee] di menu [Master User]. Data: {\"id\":\"4\",\"name\":\"Randy\",\"kitchen_id\":\"2\",\"pin\":\"1234\"}', '::1', '2026-04-13 17:06:47'),
(317, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-13 17:06:47'),
(318, 1, 'save_employee', 'Master User', 'Eksekusi [save_employee] di menu [Master User]. Data: {\"id\":\"3\",\"name\":\"Siti\",\"kitchen_id\":\"2\",\"pin\":\"1234\"}', '::1', '2026-04-13 17:06:56'),
(319, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-13 17:06:56'),
(320, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-13 17:22:05'),
(321, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:22:38'),
(322, 1, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:22:38'),
(323, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:22:40'),
(324, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:22:40');
INSERT INTO `system_logs` (`id`, `user_id`, `action`, `menu`, `description`, `ip_address`, `created_at`) VALUES
(325, 1, 'save_bom', 'Master Resep', 'Eksekusi [save_bom] di menu [Master Resep]. Data: {\"product_id\":\"6\",\"material_id\":\"20\",\"quantity_needed\":\"1\",\"unit_used\":\"Gram\"}', '::1', '2026-04-13 17:23:02'),
(326, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:23:02'),
(327, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:23:02'),
(328, 1, 'save_bom', 'Master Resep', 'Eksekusi [save_bom] di menu [Master Resep]. Data: {\"product_id\":\"6\",\"material_id\":\"7\",\"quantity_needed\":\"1\",\"unit_used\":\"Gram\"}', '::1', '2026-04-13 17:23:23'),
(329, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:23:23'),
(330, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:23:23'),
(331, 1, 'save_bom', 'Master Resep', 'Eksekusi [save_bom] di menu [Master Resep]. Data: {\"product_id\":\"6\",\"material_id\":\"12\",\"quantity_needed\":\"1\",\"unit_used\":\"Gram\"}', '::1', '2026-04-13 17:23:36'),
(332, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:23:36'),
(333, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:23:36'),
(334, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:23:41'),
(335, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:23:41'),
(336, 1, 'save_bom', 'Master Resep', 'Eksekusi [save_bom] di menu [Master Resep]. Data: {\"product_id\":\"3\",\"material_id\":\"6\",\"quantity_needed\":\"1\",\"unit_used\":\"Gram\"}', '::1', '2026-04-13 17:23:44'),
(337, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:23:44'),
(338, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:23:44'),
(339, 1, 'save_bom', 'Master Resep', 'Eksekusi [save_bom] di menu [Master Resep]. Data: {\"product_id\":\"3\",\"material_id\":\"7\",\"quantity_needed\":\"1\",\"unit_used\":\"Gram\"}', '::1', '2026-04-13 17:23:50'),
(340, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:23:50'),
(341, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:23:50'),
(342, 1, 'save_bom', 'Master Resep', 'Eksekusi [save_bom] di menu [Master Resep]. Data: {\"product_id\":\"3\",\"material_id\":\"12\",\"quantity_needed\":\"1\",\"unit_used\":\"Gram\"}', '::1', '2026-04-13 17:23:58'),
(343, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:23:58'),
(344, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:23:58'),
(345, 2, 'save', 'Input Produksi', 'Eksekusi [save] di menu [Input Produksi]. Data: {\"employee_id\":\"2\",\"product_id\":[\"3\",\"6\"],\"quantity\":[\"1\",\"1\"],\"warehouse_id\":\"1\",\"notes\":\"\",\"pin\":\"1234\"}', '::1', '2026-04-13 17:24:23'),
(346, 16, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 17:24:53'),
(347, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 17:24:53'),
(348, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-13 17:25:06'),
(349, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:25:14'),
(350, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:25:14'),
(351, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:27:28'),
(352, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:27:28'),
(353, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-13 17:28:25'),
(354, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:37:38'),
(355, 1, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:37:38'),
(356, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:37:39'),
(357, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:37:39'),
(358, 1, 'save_bom', 'Master Resep', 'Eksekusi [save_bom] di menu [Master Resep]. Data: {\"product_id\":\"6\",\"material_id\":\"3\",\"quantity_needed\":\"1\",\"unit_used\":\"Gram\"}', '::1', '2026-04-13 17:37:56'),
(359, 1, 'save_bom', 'Master Resep', 'Eksekusi [save_bom] di menu [Master Resep]. Data: {\"product_id\":\"6\",\"material_id\":\"3\",\"quantity_needed\":\"1\",\"unit_used\":\"Gram\"}', '::1', '2026-04-13 17:38:00'),
(360, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:38:40'),
(361, 1, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:38:40'),
(362, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:38:41'),
(363, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:38:41'),
(364, 1, 'save_bom', 'Master Resep', 'Eksekusi [save_bom] di menu [Master Resep]. Data: {\"product_id\":\"6\",\"material_id\":\"3\",\"quantity_needed\":\"1\",\"unit_used\":\"Gram\"}', '::1', '2026-04-13 17:38:45'),
(365, 16, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 17:52:27'),
(366, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 17:52:27'),
(367, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:52:34'),
(368, 1, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:52:34'),
(369, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:52:35'),
(370, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:52:35'),
(371, 1, 'save_bom', 'Master Resep', 'Eksekusi [save_bom] di menu [Master Resep]. Data: {\"product_id\":\"6\",\"material_id\":\"3\",\"quantity_needed\":\"100\",\"unit_used\":\"Gram\"}', '::1', '2026-04-13 17:52:45'),
(372, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:52:45'),
(373, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:52:45'),
(374, 1, 'save_bom', 'Master Resep', 'Eksekusi [save_bom] di menu [Master Resep]. Data: {\"product_id\":\"6\",\"material_id\":\"2\",\"quantity_needed\":\"100\",\"unit_used\":\"Gram\"}', '::1', '2026-04-13 17:52:55'),
(375, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:52:55'),
(376, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:52:55'),
(377, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-13 17:53:21'),
(378, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:53:36'),
(379, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:53:36'),
(380, 16, 'save', 'Master Bahan', 'Eksekusi [save] di menu [Master Bahan]. Data: {\"id\":\"6\",\"code\":\"CB01\",\"name\":\"Coklat Batang Elmer\",\"unit\":\"Kg\",\"stock\":\"11\",\"min_stock\":\"10\"}', '::1', '2026-04-13 17:54:06'),
(381, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:54:21'),
(382, 1, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:54:21'),
(383, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:54:22'),
(384, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:54:22'),
(385, 2, 'save', 'Input Produksi', 'Eksekusi [save] di menu [Input Produksi]. Data: {\"employee_id\":\"1\",\"product_id\":[\"6\"],\"quantity\":[\"1\"],\"warehouse_id\":\"1\",\"notes\":\"\",\"pin\":\"1234\"}', '::1', '2026-04-13 17:54:46'),
(386, 16, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 17:54:54'),
(387, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 17:54:54'),
(388, 16, 'save', 'Master Bahan', 'Eksekusi [save] di menu [Master Bahan]. Data: {\"id\":\"12\",\"code\":\"MT01\",\"name\":\"Mentega Blueband\",\"unit\":\"Kg\",\"stock\":\"1\",\"min_stock\":\"1\"}', '::1', '2026-04-13 17:56:08'),
(389, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:56:16'),
(390, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:56:16'),
(391, 1, 'save_bom', 'Master Resep', 'Eksekusi [save_bom] di menu [Master Resep]. Data: {\"product_id\":\"3\",\"material_id\":\"4\",\"quantity_needed\":\"1\",\"unit_used\":\"Gram\"}', '::1', '2026-04-13 17:56:21'),
(392, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:56:21'),
(393, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:56:21'),
(394, 1, 'delete_bom', 'Master Resep', 'Eksekusi [delete_bom] di menu [Master Resep]. Data: {\"id\":\"25\"}', '::1', '2026-04-13 17:56:23'),
(395, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:56:23'),
(396, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:56:23'),
(397, 1, 'save_bom', 'Master Resep', 'Eksekusi [save_bom] di menu [Master Resep]. Data: {\"product_id\":\"3\",\"material_id\":\"4\",\"quantity_needed\":\"2\",\"unit_used\":\"Kg\"}', '::1', '2026-04-13 17:56:31'),
(398, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:56:31'),
(399, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-13 17:56:32'),
(400, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-13 17:56:40'),
(401, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '::1', '2026-04-13 17:57:23'),
(402, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-13 17:57:24'),
(403, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-13 17:57:24'),
(404, 2, 'save', 'Input Produksi', 'Eksekusi [save] di menu [Input Produksi]. Data: {\"employee_id\":\"4\",\"product_id\":[\"3\"],\"quantity\":[\"1\"],\"warehouse_id\":\"1\",\"notes\":\"\",\"pin\":\"1234\"}', '::1', '2026-04-13 17:57:47'),
(405, 16, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 17:58:13'),
(406, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 17:58:13'),
(407, 17, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 17:58:48'),
(408, 17, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 17:58:48'),
(409, 17, 'save', 'Master Bahan', 'Eksekusi [save] di menu [Master Bahan]. Data: {\"id\":\"21\",\"code\":\"MT01\",\"name\":\"Mentega Blueband\",\"unit\":\"Kg\",\"stock\":\"1\",\"min_stock\":\"10\"}', '::1', '2026-04-13 17:58:55'),
(410, 2, 'save', 'Input Produksi', 'Eksekusi [save] di menu [Input Produksi]. Data: {\"employee_id\":\"4\",\"product_id\":[\"3\"],\"quantity\":[\"1\"],\"warehouse_id\":\"2\",\"notes\":\"\",\"pin\":\"1234\"}', '::1', '2026-04-13 17:59:33'),
(411, 17, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 17:59:42'),
(412, 17, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 17:59:42'),
(413, 16, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 18:04:29'),
(414, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 18:04:29'),
(415, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-13 18:04:37'),
(416, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-13 18:04:37'),
(417, 17, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 18:04:58'),
(418, 17, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 18:04:58'),
(419, 2, 'save', 'Input Produksi', 'Eksekusi [save] di menu [Input Produksi]. Data: {\"employee_id\":\"1\",\"product_id\":[\"3\"],\"quantity\":[\"1\"],\"warehouse_id\":\"2\",\"notes\":\"\",\"pin\":\"1234\"}', '::1', '2026-04-13 18:05:26'),
(420, 17, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 18:05:38'),
(421, 17, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 18:05:39'),
(422, 17, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 18:05:53'),
(423, 17, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 18:05:53'),
(424, 16, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 18:06:02'),
(425, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-13 18:06:02'),
(426, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-13 18:22:35'),
(427, 2, 'save', 'Input Produksi', 'Eksekusi [save] di menu [Input Produksi]. Data: {\"employee_id\":\"1\",\"product_id\":[\"3\"],\"quantity\":[\"1\"],\"warehouse_id\":\"2\",\"notes\":\"\",\"pin\":\"1234\"}', '::1', '2026-04-13 18:23:01'),
(428, 2, 'save', 'Input Produksi', 'Eksekusi [save] di menu [Input Produksi]. Data: {\"employee_id\":\"4\",\"product_id\":[\"3\"],\"quantity\":[\"1\"],\"warehouse_id\":\"1\",\"notes\":\"\",\"pin\":\"1234\"}', '::1', '2026-04-13 18:23:35'),
(429, 1, 'save_employee', 'Master User', 'Eksekusi [save_employee] di menu [Master User]. Data: {\"id\":\"4\",\"name\":\"Randy\",\"kitchen_id\":\"2\",\"pin\":\"0000\"}', '::1', '2026-04-13 18:24:50'),
(430, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-13 18:24:50'),
(431, 1, 'save_employee', 'Master User', 'Eksekusi [save_employee] di menu [Master User]. Data: {\"id\":\"3\",\"name\":\"Siti\",\"kitchen_id\":\"2\",\"pin\":\"0000\"}', '::1', '2026-04-13 18:25:07'),
(432, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-13 18:25:07'),
(433, 2, 'save', 'Input Produksi', 'Eksekusi [save] di menu [Input Produksi]. Data: {\"employee_id\":\"1\",\"product_id\":[\"1\"],\"quantity\":[\"1\"],\"warehouse_id\":\"1\",\"notes\":\"\",\"pin\":\"1234\"}', '::1', '2026-04-13 18:25:39'),
(434, 2, 'save', 'Input Produksi', 'Eksekusi [save] di menu [Input Produksi]. Data: {\"employee_id\":\"1\",\"product_id\":[\"3\"],\"quantity\":[\"1\"],\"warehouse_id\":\"1\",\"notes\":\"\",\"pin\":\"0000\"}', '::1', '2026-04-13 18:26:01'),
(435, 2, 'save', 'Input Produksi', 'Eksekusi [save] di menu [Input Produksi]. Data: {\"employee_id\":\"1\",\"product_id\":[\"3\"],\"quantity\":[\"1\"],\"warehouse_id\":\"1\",\"notes\":\"\",\"pin\":\"1235\"}', '::1', '2026-04-13 18:26:08'),
(436, 2, 'save', 'Input Produksi', 'Eksekusi [save] di menu [Input Produksi]. Data: {\"employee_id\":\"4\",\"product_id\":[\"3\"],\"quantity\":[\"1\"],\"warehouse_id\":\"1\",\"notes\":\"\",\"pin\":\"1234\"}', '::1', '2026-04-13 18:26:17'),
(437, 2, 'save', 'Input Produksi', 'Eksekusi [save] di menu [Input Produksi]. Data: {\"employee_id\":\"4\",\"product_id\":[\"3\"],\"quantity\":[\"1\"],\"warehouse_id\":\"1\",\"notes\":\"\",\"pin\":\"0000\"}', '::1', '2026-04-13 18:26:21'),
(438, 2, 'cancel_produksi', 'Riwayat Produksi', 'Eksekusi [cancel_produksi] di menu [Riwayat Produksi]. Data: {\"prod_id\":\"80\",\"pin\":\"123456\"}', '::1', '2026-04-13 18:41:15'),
(439, 2, 'get_employees', 'Produk Keluar', 'Eksekusi [get_employees] di menu [Produk Keluar]. Data: []', '::1', '2026-04-13 18:41:27'),
(440, 3, 'scan', 'Scan Barcode', 'Eksekusi [scan] di menu [Scan Barcode]. Data: {\"barcode\":\"D1326-008\"}', '::1', '2026-04-13 18:42:00'),
(441, 3, 'scan', 'Scan Barcode', 'Eksekusi [scan] di menu [Scan Barcode]. Data: {\"barcode\":\"D1326-007\"}', '::1', '2026-04-13 18:42:09'),
(442, 3, 'execute_validasi', 'Scan Barcode', 'Eksekusi [execute_validasi] di menu [Scan Barcode]. Data: {\"prod_id\":\"78\",\"status\":\"ditolak\"}', '::1', '2026-04-13 18:42:22'),
(443, 2, 'update_revisi', 'Riwayat Produksi', 'Eksekusi [update_revisi] di menu [Riwayat Produksi]. Data: {\"prod_id\":\"78\",\"detail_id\":[\"105\"],\"quantity\":[\"2\"]}', '::1', '2026-04-13 18:42:29'),
(444, 3, 'scan', 'Scan Barcode', 'Eksekusi [scan] di menu [Scan Barcode]. Data: {\"barcode\":\"D1326-007\"}', '::1', '2026-04-13 18:42:32'),
(445, 3, 'execute_validasi', 'Scan Barcode', 'Eksekusi [execute_validasi] di menu [Scan Barcode]. Data: {\"prod_id\":\"78\",\"status\":\"masuk_gudang\"}', '::1', '2026-04-13 18:42:33'),
(446, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-14 08:26:33'),
(447, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '::1', '2026-04-14 08:37:02'),
(448, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-14 08:37:02'),
(449, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-14 08:37:02'),
(450, 16, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-14 08:38:44'),
(451, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-14 08:38:44'),
(452, 16, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-14 08:39:01'),
(453, 16, 'submit_request', 'Master Bahan', 'Eksekusi [submit_request] di menu [Master Bahan]. Data: {\"warehouse_id\":\"1\",\"pilar_id\":\"1\",\"qty\":\"10\"}', '::1', '2026-04-14 08:39:47'),
(454, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-14 08:39:47'),
(455, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-14 08:39:51'),
(456, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-14 08:40:25'),
(457, NULL, 'proses_kirim', 'Persetujuan', 'Eksekusi [proses_kirim] di menu [Persetujuan]. Data: {\"action\":\"proses_kirim\",\"id\":\"17\",\"qty_approved\":\"10\"}', '::1', '2026-04-14 08:40:50'),
(458, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-14 08:40:50'),
(459, 16, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-14 08:41:04'),
(460, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-14 08:41:04'),
(461, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-14 08:41:29'),
(462, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-14 08:42:13'),
(463, 1, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '::1', '2026-04-14 08:42:13'),
(464, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-14 08:42:15'),
(465, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-14 08:42:15'),
(466, 2, 'save', 'Input Produksi', 'Eksekusi [save] di menu [Input Produksi]. Data: {\"employee_id\":\"1\",\"product_id\":[\"3\"],\"quantity\":[\"1\"],\"warehouse_id\":\"1\",\"notes\":\"\",\"pin\":\"0000\"}', '::1', '2026-04-14 08:43:15'),
(467, 2, 'save', 'Input Produksi', 'Eksekusi [save] di menu [Input Produksi]. Data: {\"employee_id\":\"1\",\"product_id\":[\"3\"],\"quantity\":[\"1\"],\"warehouse_id\":\"1\",\"notes\":\"\",\"pin\":\"1234\"}', '::1', '2026-04-14 08:43:26'),
(468, 16, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-14 08:43:37'),
(469, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-14 08:43:37'),
(470, 17, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-14 08:45:34'),
(471, 17, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-14 08:45:34'),
(472, 17, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-14 08:47:30'),
(473, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-14 08:48:02'),
(474, 2, 'dashboard_data', 'Dashboard', 'Eksekusi [dashboard_data] di menu [Dashboard]. Data: []', '::1', '2026-04-14 08:52:13'),
(475, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-14 08:52:15'),
(476, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '::1', '2026-04-14 08:52:22'),
(477, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-14 08:52:23'),
(478, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-14 08:52:23'),
(479, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-14 08:55:15'),
(480, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-14 17:20:10'),
(481, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '::1', '2026-04-14 17:32:35'),
(482, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-14 17:32:35'),
(483, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-14 17:32:35'),
(484, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '::1', '2026-04-14 17:37:03'),
(485, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-14 17:37:03'),
(486, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-14 17:37:03'),
(487, 1, 'save_user', 'Master User', 'Eksekusi [save_user] di menu [Master User]. Data: {\"id\":\"\",\"name\":\"admin produksi dapur 1\",\"username\":\"produksi\",\"password\":\"******\",\"role\":\"produksi\",\"kitchen_id\":\"1\"}', '::1', '2026-04-14 17:37:35'),
(488, 1, 'save_user', 'Master User', 'Eksekusi [save_user] di menu [Master User]. Data: {\"id\":\"\",\"name\":\"admin produksi dapur 1\",\"username\":\"produksi1\",\"password\":\"******\",\"role\":\"produksi\",\"kitchen_id\":\"1\"}', '::1', '2026-04-14 17:37:40'),
(489, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-14 17:37:40'),
(490, 1, 'save_user', 'Master User', 'Eksekusi [save_user] di menu [Master User]. Data: {\"id\":\"\",\"name\":\"admin produksi dapur 2\",\"username\":\"produksi2\",\"password\":\"******\",\"role\":\"produksi\",\"kitchen_id\":\"2\"}', '::1', '2026-04-14 17:38:09'),
(491, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-14 17:38:09'),
(492, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-14 17:41:53'),
(493, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-14 17:44:25'),
(494, 16, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-14 17:44:49'),
(495, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-14 17:44:49'),
(496, 18, 'save', 'Input Produksi', 'Eksekusi [save] di menu [Input Produksi]. Data: {\"employee_id\":\"1\",\"product_id\":[\"3\"],\"quantity\":[\"1\"],\"warehouse_id\":\"1\",\"notes\":\"\",\"pin\":\"1234\"}', '::1', '2026-04-14 17:45:07'),
(497, 16, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-14 17:45:17'),
(498, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-14 17:45:17'),
(499, 1, 'generate', 'Otorisasi', 'Eksekusi [generate] di menu [Otorisasi]. Data: []', '::1', '2026-04-16 12:57:39'),
(500, 1, 'verify_pin', 'Stok Opname', 'Eksekusi [verify_pin] di menu [Stok Opname]. Data: {\"pin\":\"331410\"}', '::1', '2026-04-16 12:58:00'),
(501, 1, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-16 13:20:52'),
(502, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-16 13:20:52'),
(503, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-16 13:21:36'),
(504, 2, 'get_employees', 'Produk Keluar', 'Eksekusi [get_employees] di menu [Produk Keluar]. Data: []', '::1', '2026-04-16 13:24:50'),
(505, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-16 13:25:02'),
(506, 2, 'save', 'Input Produksi', 'Eksekusi [save] di menu [Input Produksi]. Data: {\"employee_id\":\"1\",\"product_id\":[\"3\"],\"quantity\":[\"10\"],\"warehouse_id\":\"2\",\"notes\":\"\",\"pin\":\"1234\"}', '::1', '2026-04-16 13:25:18'),
(507, 2, 'get_employees', 'Produk Keluar', 'Eksekusi [get_employees] di menu [Produk Keluar]. Data: []', '::1', '2026-04-16 13:25:29'),
(508, 2, 'search_invoice', 'Produk Keluar', 'Eksekusi [search_invoice] di menu [Produk Keluar]. Data: []', '::1', '2026-04-16 13:25:36'),
(509, 3, 'scan', 'Scan Barcode', 'Eksekusi [scan] di menu [Scan Barcode]. Data: {\"barcode\":\"http:\\/\\/localhost\\/sim-produksi-kue\\/produksi\\/produk_keluar\\/\"}', '::1', '2026-04-16 13:26:12'),
(510, 3, 'scan', 'Scan Barcode', 'Eksekusi [scan] di menu [Scan Barcode]. Data: {\"barcode\":\"D1626-001\"}', '::1', '2026-04-16 13:26:15'),
(511, 3, 'execute_validasi', 'Scan Barcode', 'Eksekusi [execute_validasi] di menu [Scan Barcode]. Data: {\"prod_id\":\"83\",\"status\":\"masuk_gudang\"}', '::1', '2026-04-16 13:26:16'),
(512, 2, 'get_employees', 'Produk Keluar', 'Eksekusi [get_employees] di menu [Produk Keluar]. Data: []', '::1', '2026-04-16 13:26:21'),
(513, 2, 'search_invoice', 'Produk Keluar', 'Eksekusi [search_invoice] di menu [Produk Keluar]. Data: []', '::1', '2026-04-16 13:26:25'),
(514, 2, 'get_employees', 'Produk Keluar', 'Eksekusi [get_employees] di menu [Produk Keluar]. Data: []', '::1', '2026-04-16 13:39:15'),
(515, 2, 'search_invoice', 'Produk Keluar', 'Eksekusi [search_invoice] di menu [Produk Keluar]. Data: []', '::1', '2026-04-16 13:39:17'),
(516, 2, 'save', 'Produk Keluar', 'Eksekusi [save] di menu [Produk Keluar]. Data: {\"origin_invoice\":\"D1626-001\",\"employee_id\":\"1\",\"product_id\":[\"3\"],\"quantity\":[\"1\"],\"reason\":\"Rusak\",\"notes\":\"\"}', '::1', '2026-04-16 13:39:29'),
(517, 2, 'get_employees', 'Produk Keluar', 'Eksekusi [get_employees] di menu [Produk Keluar]. Data: []', '::1', '2026-04-16 13:47:18'),
(518, 2, 'get_employees', 'Produk Keluar', 'Eksekusi [get_employees] di menu [Produk Keluar]. Data: []', '::1', '2026-04-16 13:47:38'),
(519, 2, 'search_invoice', 'Produk Keluar', 'Eksekusi [search_invoice] di menu [Produk Keluar]. Data: []', '::1', '2026-04-16 13:47:40'),
(520, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-16 13:48:12'),
(521, 18, 'get_employees', 'Produk Keluar', 'Eksekusi [get_employees] di menu [Produk Keluar]. Data: []', '::1', '2026-04-16 13:48:14'),
(522, 18, 'search_invoice', 'Produk Keluar', 'Eksekusi [search_invoice] di menu [Produk Keluar]. Data: []', '::1', '2026-04-16 13:48:17'),
(523, 18, 'search_invoice', 'Produk Keluar', 'Eksekusi [search_invoice] di menu [Produk Keluar]. Data: []', '::1', '2026-04-16 13:48:20'),
(524, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-16 13:48:50'),
(525, 18, 'save', 'Input Produksi', 'Eksekusi [save] di menu [Input Produksi]. Data: {\"employee_id\":\"1\",\"product_id\":[\"3\"],\"quantity\":[\"1\"],\"warehouse_id\":\"2\",\"notes\":\"\",\"pin\":\"1234\"}', '::1', '2026-04-16 13:49:02'),
(526, 3, 'scan', 'Scan Barcode', 'Eksekusi [scan] di menu [Scan Barcode]. Data: {\"barcode\":\"D1626-002-1\"}', '::1', '2026-04-16 13:49:14'),
(527, 3, 'execute_validasi', 'Scan Barcode', 'Eksekusi [execute_validasi] di menu [Scan Barcode]. Data: {\"prod_id\":\"84\",\"status\":\"masuk_gudang\"}', '::1', '2026-04-16 13:49:15'),
(528, 18, 'get_employees', 'Produk Keluar', 'Eksekusi [get_employees] di menu [Produk Keluar]. Data: []', '::1', '2026-04-16 13:49:28'),
(529, 18, 'search_invoice', 'Produk Keluar', 'Eksekusi [search_invoice] di menu [Produk Keluar]. Data: []', '::1', '2026-04-16 13:49:30'),
(530, 3, 'scan', 'Scan Barcode', 'Eksekusi [scan] di menu [Scan Barcode]. Data: {\"barcode\":\"D1426-002\"}', '::1', '2026-04-16 13:49:36'),
(531, 3, 'execute_validasi', 'Scan Barcode', 'Eksekusi [execute_validasi] di menu [Scan Barcode]. Data: {\"prod_id\":\"82\",\"status\":\"masuk_gudang\"}', '::1', '2026-04-16 13:49:37'),
(532, 3, 'scan', 'Scan Barcode', 'Eksekusi [scan] di menu [Scan Barcode]. Data: {\"barcode\":\"D1426-002\"}', '::1', '2026-04-16 13:49:48'),
(533, 18, 'get_employees', 'Produk Keluar', 'Eksekusi [get_employees] di menu [Produk Keluar]. Data: []', '::1', '2026-04-16 13:49:51'),
(534, 18, 'search_invoice', 'Produk Keluar', 'Eksekusi [search_invoice] di menu [Produk Keluar]. Data: []', '::1', '2026-04-16 13:49:54'),
(535, 18, 'save', 'Produk Keluar', 'Eksekusi [save] di menu [Produk Keluar]. Data: {\"origin_invoice\":\"D1426-002\",\"employee_id\":\"2\",\"product_id\":[\"3\"],\"quantity\":[\"1\"],\"reason\":\"Expired\",\"notes\":\"\"}', '::1', '2026-04-16 13:50:04'),
(536, 18, 'get_employees', 'Produk Keluar', 'Eksekusi [get_employees] di menu [Produk Keluar]. Data: []', '::1', '2026-04-16 13:50:38'),
(537, 18, 'dashboard_data', 'Dashboard', 'Eksekusi [dashboard_data] di menu [Dashboard]. Data: []', '::1', '2026-04-16 14:23:11'),
(538, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-16 14:23:38'),
(539, 18, 'save', 'Input Produksi', 'Eksekusi [save] di menu [Input Produksi]. Data: {\"employee_id\":\"1\",\"product_id\":[\"3\"],\"quantity\":[\"1\"],\"warehouse_id\":\"2\",\"notes\":\"\",\"pin\":\"1234\"}', '::1', '2026-04-16 14:23:53'),
(540, 3, 'scan', 'Scan Barcode', 'Eksekusi [scan] di menu [Scan Barcode]. Data: {\"barcode\":\"let currentPage = 1; let alertShown = false;   function getTodayLocal() {     const now = new Date();     const year = now.getFullYear();     const month = String(now.getMonth() + 1).padStart(2, \'0\');     const day = String(now.getDate()).padStart(2, \'0\');     return `${year}-${month}-${day}`; }  document.addEventListener(\\\"DOMContentLoaded\\\", async () => {     await loadFilterGudang();          const today = getTodayLocal();     document.getElementById(\'start_date\').value = today;     document.getElementById(\'end_date\').value = today;          loadData(1); });  async function loadFilterGudang() {     try {         const response = await fetchAjax(\'logic.php?action=init_filter\', \'GET\');         if (response.status === \'success\') {             const selectStore = document.getElementById(\'warehouse_id\');             let optStore = \'<option value=\\\"\\\">Semua Store<\\/option>\';             response.warehouses.forEach(w => {                 optStore += `<option value=\\\"${w.id}\\\">Store: ${w.name}<\\/option>`;             });             if(selectStore) selectStore.innerHTML = optStore;              const selectKitchen = document.getElementById(\'kitchen_id\');             let optKitchen = \'<option value=\\\"\\\">Semua Dapur<\\/option>\';             response.kitchens.forEach(k => {                 optKitchen += `<option value=\\\"${k.id}\\\">${k.name}<\\/option>`;             });             if(selectKitchen) selectKitchen.innerHTML = optKitchen;         }     } catch (e) {         console.error(\\\"Gagal memuat filter dropdown\\\");     } }  document.getElementById(\'formFilter\').addEventListener(\'submit\', function(e) {     e.preventDefault();     loadData(1); });  function resetFilter() {     document.getElementById(\'formFilter\').reset();          const today = getTodayLocal();     document.getElementById(\'start_date\').value = today;     document.getElementById(\'end_date\').value = today;     document.getElementById(\'warehouse_id\').value = \'\';     document.getElementById(\'kitchen_id\').value = \'\';          loadData(1); }  async function loadData(page = 1) {     currentPage = page;     const tbody = document.getElementById(\'table-data\');     tbody.innerHTML = \'<tr><td colspan=\\\"8\\\" class=\\\"p-8 text-center text-secondary\\\"><i class=\\\"fa-solid fa-circle-notch fa-spin mr-2\\\"><\\/i> Memuat data antrean...<\\/td><\\/tr>\';          const start = document.getElementById(\'start_date\').value;     const end = document.getElementById(\'end_date\').value;     const warehouseId = document.getElementById(\'warehouse_id\').value;     const kitchenId = document.getElementById(\'kitchen_id\').value;          const url = `logic.php?action=read&start_date=${start}&end_date=${end}&warehouse_id=${warehouseId}&kitchen_id=${kitchenId}&page=${currentPage}`;     const response = await fetchAjax(url, \'GET\');          if (response.status === \'success\') {         document.getElementById(\'badge-count\').innerText = `${response.total_data} Item Tertunda`;          let html = \'\';         if (response.data.length === 0) {             html = \'<tr><td colspan=\\\"8\\\" class=\\\"p-12 text-center text-secondary\\\"><div class=\\\"flex flex-col items-center justify-center\\\"><i class=\\\"fa-solid fa-box-open text-4xl text-slate-300 mb-3\\\"><\\/i><span class=\\\"font-bold text-slate-500\\\">Hebat! Tidak ada antrean.<\\/span><span class=\\\"text-xs\\\">Semua barang produksi pada filter ini sudah divalidasi.<\\/span><\\/div><\\/td><\\/tr>\';         } else {             response.data.forEach((item, index) => {                 const no = (currentPage - 1) * 15 + index + 1;                 const d = new Date(item.created_at);                 const tgl = d.toLocaleDateString(\'id-ID\', { day: \'2-digit\', month: \'short\', year: \'numeric\' });                 const waktu = d.toLocaleTimeString(\'id-ID\', { hour: \'2-digit\', minute: \'2-digit\' });                  html += `                     <tr class=\\\"hover:bg-amber-50\\/30 transition-colors text-slate-700\\\">                         <td class=\\\"p-4 text-center text-slate-400 text-xs\\\">${no}<\\/td>                         <td class=\\\"p-4\\\">                             <div class=\\\"font-bold text-slate-700\\\">${tgl}<\\/div>                             <div class=\\\"text-[10px] text-slate-400 font-bold text-amber-600\\\">${waktu} WIB<\\/div>                         <\\/td>                         <td class=\\\"p-4 font-semibold text-slate-600 text-xs\\\">                             <i class=\\\"fa-solid fa-store text-slate-400 mr-1\\\"><\\/i>${item.gudang ?? \'-\'}                         <\\/td>                         <td class=\\\"p-4 font-mono text-xs font-bold text-slate-500\\\">${item.invoice_no}<\\/td>                         <td class=\\\"p-4 text-xs font-bold uppercase tracking-widest text-slate-500\\\">${item.asal_dapur || \'-\'}<\\/td>                         <td class=\\\"p-4 font-medium text-sm\\\">${item.karyawan}<\\/td>                         <td class=\\\"p-4 font-bold text-slate-800 text-sm\\\">${item.produk}<\\/td>                         <td class=\\\"p-4 text-center font-black text-amber-600 text-base\\\">${item.quantity}<\\/td>                     <\\/tr>                 `;             });         }         tbody.innerHTML = html;         renderPagination(response.total_pages, response.current_page);          \\/\\/ ==============================================================         \\/\\/ LOGIKA ALARM SORE (PENGINGAT VALIDASI BARANG HARI INI)         \\/\\/ ==============================================================         if (!alertShown && response.data.length > 0) {             const now = new Date();             const jamSekarang = now.getHours();                          if (jamSekarang >= 15) { \\/\\/ Jam 3 Sore ke atas                 const todayStr = getTodayLocal();                 const pendingTodayCount = response.data.filter(item => item.created_at.startsWith(todayStr)).length;                                  if (pendingTodayCount > 0) {                     Swal.fire({                         title: \'\\u26a0\\ufe0f Peringatan Sore!\',                         html: `<p style=\\\"color:#475569; font-weight:500;\\\">Terdapat <b>${pendingTodayCount} item<\\/b> produksi HARI INI yang nganggur di Dapur dan belum Anda validasi masuk ke Store.<br><br>Harap segera menuju Dapur dan lakukan Scan Barcode sebelum jam kerja berakhir!<\\/p>`,                         icon: \'warning\',                         confirmButtonText: \'Siap, Menuju Dapur!\',                         confirmButtonColor: \'#F59E0B\',                         customClass: { popup: \'rounded-3xl shadow-2xl border border-amber-200\' }                     });                     alertShown = true;                  }             }         }     } }  function renderPagination(totalPages, current) {     const container = document.getElementById(\'pagination\');     let html = \'\';     if (totalPages <= 1) { container.innerHTML = \'\'; return; }      html += `<button type=\\\"button\\\" ${current > 1 ? `onclick=\\\"loadData(${current - 1})\\\"` : \'disabled\'} class=\\\"px-4 py-2 rounded-lg ${current > 1 ? \'bg-white hover:bg-slate-100 text-slate-700\' : \'bg-slate-50 text-slate-300 cursor-not-allowed\'} border border-slate-200 text-sm font-semibold transition-colors shadow-sm\\\"><i class=\\\"fa-solid fa-chevron-left\\\"><\\/i><\\/button>`;      for (let i = 1; i <= totalPages; i++) {         if (i === current) {             html += `<button type=\\\"button\\\" class=\\\"px-4 py-2 rounded-lg bg-amber-500 border border-amber-500 text-white text-sm font-bold shadow-sm\\\">${i}<\\/button>`;         } else {             html += `<button type=\\\"button\\\" onclick=\\\"loadData(${i})\\\" class=\\\"px-4 py-2 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-sm font-semibold transition-colors shadow-sm\\\">${i}<\\/button>`;         }     }      html += `<button type=\\\"button\\\" ${current < totalPages ? `onclick=\\\"loadData(${current + 1})\\\"` : \'disabled\'} class=\\\"px-4 py-2 rounded-lg ${current < totalPages ? \'bg-white hover:bg-slate-100 text-slate-700\' : \'bg-slate-50 text-slate-300 cursor-not-allowed\'} border border-slate-200 text-sm font-semibold transition-colors shadow-sm\\\"><i class=\\\"fa-solid fa-chevron-right\\\"><\\/i><\\/button>`;      container.innerHTML = html; }  \\/\\/ =========================================================================== \\/\\/ CETAK PDF (ANTI LIMIT & BACA FILTER) \\/\\/ =========================================================================== async function cetakPDF() {     Swal.fire({ title: \'Menyiapkan Dokumen...\', text: \'Mengambil daftar jemputan...\', icon: \'info\', showConfirmButton: false, allowOutsideClick: false });          const start = document.getElementById(\'start_date\').value;     const end = document.getElementById(\'end_date\').value;     const warehouseId = document.getElementById(\'warehouse_id\').value;     const kitchenId = document.getElementById(\'kitchen_id\').value;          const url = `logic.php?action=read&start_date=${start}&end_date=${end}&warehouse_id=${warehouseId}&kitchen_id=${kitchenId}&is_print=true`;     const response = await fetchAjax(url, \'GET\');          if (response.status === \'success\') {         const wrapper = document.getElementById(\'print-table-wrapper\');         const now = new Date();                  const warehouseSelect = document.getElementById(\'warehouse_id\');         const warehouseName = warehouseId ? warehouseSelect.options[warehouseSelect.selectedIndex].text : \'Semua Store\';          document.getElementById(\'print-periode\').innerText = `Lokasi Penjemputan: ${warehouseName.toUpperCase()} | Dicetak pada: ${now.toLocaleDateString(\'id-ID\')} ${now.toLocaleTimeString(\'id-ID\')} WIB`;          let htmlPrint = `<table><thead><tr><th>No<\\/th><th>Waktu Produksi<\\/th><th>Store Tujuan<\\/th><th>No. Invoice<\\/th><th>Asal Dapur<\\/th><th>Karyawan<\\/th><th>Nama Produk<\\/th><th>Qty<\\/th><\\/tr><\\/thead><tbody>`;                  if(response.data.length === 0){              htmlPrint += `<tr><td colspan=\\\"8\\\" style=\\\"text-align:center;\\\">Tidak ada antrean barang pada filter tersebut.<\\/td><\\/tr>`;         } else {             response.data.forEach((item, index) => {                 const d = new Date(item.created_at);                 const tgl = d.toLocaleDateString(\'id-ID\', { day: \'2-digit\', month: \'short\', year: \'numeric\' }) + \' \' + d.toLocaleTimeString(\'id-ID\', { hour: \'2-digit\', minute: \'2-digit\' });                                  htmlPrint += `<tr>                     <td style=\\\"text-align:center;\\\">${index + 1}<\\/td>                     <td>${tgl}<\\/td>                     <td>${item.gudang ?? \'-\'}<\\/td>                     <td>${item.invoice_no}<\\/td>                     <td>${item.asal_dapur || \'-\'}<\\/td>                     <td>${item.karyawan}<\\/td>                     <td style=\\\"font-weight:bold;\\\">${item.produk}<\\/td>                     <td style=\\\"text-align:center; font-weight:bold; font-size:14px;\\\">${item.quantity}<\\/td>                 <\\/tr>`;             });         }                  htmlPrint += `<\\/tbody><\\/table>`;         wrapper.innerHTML = htmlPrint;         Swal.close();          setTimeout(() => { window.print(); }, 500);     } else {         Swal.fire(\'Error\', \'Gagal memuat data cetak\', \'error\');     } }\"}', '::1', '2026-04-16 14:42:05'),
(541, 3, 'scan', 'Scan Barcode', 'Eksekusi [scan] di menu [Scan Barcode]. Data: {\"barcode\":\"D1626-003\"}', '::1', '2026-04-16 14:42:16'),
(542, 3, 'execute_validasi', 'Scan Barcode', 'Eksekusi [execute_validasi] di menu [Scan Barcode]. Data: {\"prod_id\":\"85\",\"status\":\"ditolak\"}', '::1', '2026-04-16 14:42:17'),
(543, 18, 'update_revisi', 'Riwayat Produksi', 'Eksekusi [update_revisi] di menu [Riwayat Produksi]. Data: {\"prod_id\":\"85\",\"detail_id\":[\"111\"],\"quantity\":[\"2\"]}', '::1', '2026-04-16 14:42:24'),
(544, 3, 'scan', 'Scan Barcode', 'Eksekusi [scan] di menu [Scan Barcode]. Data: {\"barcode\":\"D1626-003\"}', '::1', '2026-04-16 14:42:27'),
(545, 3, 'execute_validasi', 'Scan Barcode', 'Eksekusi [execute_validasi] di menu [Scan Barcode]. Data: {\"prod_id\":\"85\",\"status\":\"masuk_gudang\"}', '::1', '2026-04-16 14:42:29'),
(546, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-16 14:42:34'),
(547, 18, 'save', 'Input Produksi', 'Eksekusi [save] di menu [Input Produksi]. Data: {\"employee_id\":\"1\",\"product_id\":[\"3\"],\"quantity\":[\"1\"],\"warehouse_id\":\"1\",\"notes\":\"\",\"pin\":\"1234\"}', '::1', '2026-04-16 14:42:46'),
(548, 3, 'scan', 'Scan Barcode', 'Eksekusi [scan] di menu [Scan Barcode]. Data: {\"barcode\":\"D1626-004\"}', '::1', '2026-04-16 14:42:53'),
(549, 3, 'execute_validasi', 'Scan Barcode', 'Eksekusi [execute_validasi] di menu [Scan Barcode]. Data: {\"prod_id\":\"86\",\"status\":\"ditolak\"}', '::1', '2026-04-16 14:42:54'),
(550, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-16 14:43:02'),
(551, 18, 'cancel_produksi', 'Riwayat Produksi', 'Eksekusi [cancel_produksi] di menu [Riwayat Produksi]. Data: {\"prod_id\":\"86\",\"pin\":\"123456\"}', '::1', '2026-04-16 14:43:09'),
(552, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-16 14:43:19'),
(553, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"edit\",\"old_slug\":\"owner\",\"role_name\":\"Owner \\/ Pemilik\",\"role_slug\":\"owner\",\"permissions\":[\"manajemen_dapur\",\"edit_manajemen_dapur\",\"hapus_manajemen_dapur\",\"master_gudang\",\"edit_master_gudang\",\"hapus_master_gudang\",\"master_produk\",\"edit_master_produk\",\"hapus_master_produk\",\"master_kategori\",\"edit_master_kategori\",\"hapus_master_kategori\",\"master_bahan\",\"edit_master_bahan\",\"hapus_master_bahan\",\"master_satuan\",\"edit_master_satuan\",\"hapus_master_satuan\",\"master_resep\",\"master_user\",\"master_stok_pusat\",\"edit_master_stok_pusat\",\"hapus_master_stok_pusat\",\"view_dashboard\",\"stok_opname\",\"otorisasi\",\"laporan_produksi\",\"laporan_keluar\",\"audit_logs\",\"analisa_produk\",\"laporan_bahan\",\"laporan_produk_jadi\",\"laporan_bom\",\"laporan_opname\"]}', '::1', '2026-04-16 15:03:13'),
(554, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"edit\",\"old_slug\":\"owner\",\"role_name\":\"Owner \\/ Pemilik\",\"role_slug\":\"owner\",\"permissions\":[\"manajemen_dapur\",\"edit_manajemen_dapur\",\"hapus_manajemen_dapur\",\"master_gudang\",\"edit_master_gudang\",\"hapus_master_gudang\",\"master_produk\",\"edit_master_produk\",\"hapus_master_produk\",\"master_kategori\",\"edit_master_kategori\",\"hapus_master_kategori\",\"master_bahan\",\"edit_master_bahan\",\"hapus_master_bahan\",\"master_satuan\",\"edit_master_satuan\",\"hapus_master_satuan\",\"master_resep\",\"master_user\",\"master_stok_pusat\",\"edit_master_stok_pusat\",\"hapus_master_stok_pusat\",\"view_dashboard\",\"stok_opname\",\"otorisasi\",\"laporan_produksi\",\"laporan_keluar\",\"audit_logs\",\"analisa_produk\",\"laporan_bahan\",\"laporan_produk_jadi\",\"laporan_bom\",\"laporan_opname\"]}', '::1', '2026-04-16 15:03:50'),
(555, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"edit\",\"old_slug\":\"admin_dapur_1\",\"role_name\":\"admin dapur 1\",\"role_slug\":\"admin_dapur_1\",\"permissions\":[\"akses_dapur_1\",\"manajemen_dapur\",\"edit_manajemen_dapur\",\"hapus_manajemen_dapur\",\"master_bahan\",\"edit_master_bahan\",\"hapus_master_bahan\",\"view_dashboard\",\"laporan_produk_jadi\"]}', '::1', '2026-04-16 15:04:46'),
(556, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"edit\",\"old_slug\":\"admin_dapur_1\",\"role_name\":\"admin dapur 1\",\"role_slug\":\"admin_dapur_1\",\"permissions\":[\"akses_dapur_1\",\"manajemen_dapur\",\"edit_manajemen_dapur\",\"hapus_manajemen_dapur\",\"master_bahan\",\"edit_master_bahan\",\"hapus_master_bahan\",\"view_dashboard\"]}', '::1', '2026-04-16 15:07:13'),
(557, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-16 15:38:57'),
(558, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-16 16:57:20'),
(559, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-16 16:57:26'),
(560, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-16 17:20:08'),
(561, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-16 17:20:22'),
(562, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-16 17:20:45');
INSERT INTO `system_logs` (`id`, `user_id`, `action`, `menu`, `description`, `ip_address`, `created_at`) VALUES
(563, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-16 17:20:48'),
(564, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-16 17:20:53'),
(565, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-16 17:25:59'),
(566, NULL, 'save', 'Kategori', 'Eksekusi [save] di menu [Kategori]. Data: {\"id\":\"\",\"name\":\"Tepung\",\"description\":\"\"}', '::1', '2026-04-16 17:26:11'),
(567, NULL, 'save', 'Kategori', 'Eksekusi [save] di menu [Kategori]. Data: {\"id\":\"\",\"name\":\"kemasan\",\"description\":\"\"}', '::1', '2026-04-16 17:26:17'),
(568, NULL, 'save', 'Lokasi', 'Eksekusi [save] di menu [Lokasi]. Data: {\"id\":\"\",\"name\":\"A-01\",\"description\":\"\"}', '::1', '2026-04-16 17:41:29'),
(569, NULL, 'save', 'Lokasi', 'Eksekusi [save] di menu [Lokasi]. Data: {\"id\":\"\",\"name\":\"A-02\",\"description\":\"\"}', '::1', '2026-04-16 17:41:35'),
(570, NULL, 'save', 'Lokasi', 'Eksekusi [save] di menu [Lokasi]. Data: {\"id\":\"\",\"name\":\"A-03\",\"description\":\"\"}', '::1', '2026-04-16 17:41:40'),
(571, NULL, 'save', 'Supplier', 'Eksekusi [save] di menu [Supplier]. Data: {\"id\":\"\",\"name\":\"CV codifyhub.id\",\"contact_person\":\"Randy\",\"phone\":\"085835116946\",\"email\":\"muhammadrandykarna@gmail.com\",\"address\":\"Medan jalan beringin\",\"description\":\"Spesialis Tepung\"}', '::1', '2026-04-16 17:57:27'),
(572, NULL, 'save', 'Supplier', 'Eksekusi [save] di menu [Supplier]. Data: {\"id\":\"\",\"name\":\"PT Nusa Tirta\",\"contact_person\":\"Ata\",\"phone\":\"082948294872\",\"email\":\"ata@gmail.com\",\"address\":\"Medan jalan mansyur\",\"description\":\"Supp Kemasan\"}', '::1', '2026-04-16 17:58:12'),
(573, NULL, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-16 19:06:29'),
(574, NULL, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-16 19:11:14'),
(575, NULL, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-17 08:21:07'),
(576, NULL, 'save', 'Kategori', 'Eksekusi [save] di menu [Kategori]. Data: {\"id\":\"1\",\"name\":\"Bahan Baku\",\"description\":\"\"}', '::1', '2026-04-17 08:22:26'),
(577, NULL, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-17 08:22:31'),
(578, NULL, 'save', 'Inventory', 'Eksekusi [save] di menu [Inventory]. Data: {\"id\":\"3\",\"sku_code\":\"CB01\",\"status\":\"active\",\"material_name\":\"Coklat Batang Elmer\",\"category_id\":\"1\",\"unit\":\"Kg\",\"rack_id\":\"1\",\"expiry_date\":\"2026-04-19\",\"stock\":\"100\",\"min_stock\":\"10\"}', '::1', '2026-04-17 08:23:02'),
(579, NULL, 'save', 'Inventory', 'Eksekusi [save] di menu [Inventory]. Data: {\"id\":\"3\",\"sku_code\":\"CB01\",\"status\":\"active\",\"material_name\":\"Coklat Batang Elmer\",\"category_id\":\"1\",\"unit\":\"Kg\",\"rack_id\":\"1\",\"expiry_date\":\"2026-06-19\",\"stock\":\"100.00\",\"min_stock\":\"10.00\"}', '::1', '2026-04-17 08:23:14'),
(580, NULL, 'save', 'Inventory', 'Eksekusi [save] di menu [Inventory]. Data: {\"id\":\"2\",\"sku_code\":\"G01\",\"status\":\"active\",\"material_name\":\"Gula Pasir Kristal\",\"category_id\":\"1\",\"unit\":\"Kg\",\"rack_id\":\"1\",\"expiry_date\":\"2026-10-17\",\"stock\":\"100\",\"min_stock\":\"10\"}', '::1', '2026-04-17 08:23:36'),
(581, NULL, 'save', 'Inventory', 'Eksekusi [save] di menu [Inventory]. Data: {\"id\":\"4\",\"sku_code\":\"MT01\",\"status\":\"active\",\"material_name\":\"Mentega Blueband\",\"category_id\":\"1\",\"unit\":\"Kg\",\"rack_id\":\"2\",\"expiry_date\":\"2026-12-17\",\"stock\":\"130.00\",\"min_stock\":\"10\"}', '::1', '2026-04-17 08:24:00'),
(582, NULL, 'save', 'Inventory', 'Eksekusi [save] di menu [Inventory]. Data: {\"id\":\"1\",\"sku_code\":\"TP01\",\"status\":\"active\",\"material_name\":\"Tepung Terigu Segitiga Biru\",\"category_id\":\"1\",\"unit\":\"Kg\",\"rack_id\":\"3\",\"expiry_date\":\"2026-11-17\",\"stock\":\"500\",\"min_stock\":\"10\"}', '::1', '2026-04-17 08:24:26'),
(583, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-17 08:31:04'),
(584, 1, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '::1', '2026-04-17 08:31:04'),
(585, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-17 08:31:05'),
(586, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-17 08:31:05'),
(587, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-17 08:31:34'),
(588, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-17 08:31:34'),
(589, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 08:35:32'),
(590, NULL, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-17 08:57:25'),
(591, 16, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-17 08:57:31'),
(592, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-17 08:57:31'),
(593, 16, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-17 08:57:31'),
(594, 16, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-17 08:57:39'),
(595, 16, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-17 09:05:17'),
(596, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-17 09:05:17'),
(597, 16, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-17 09:05:17'),
(598, 16, 'submit_request', 'Master Bahan', 'Eksekusi [submit_request] di menu [Master Bahan]. Data: {\"warehouse_id\":\"1\",\"pilar_id\":\"3\",\"qty\":\"10\"}', '::1', '2026-04-17 09:05:22'),
(599, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-17 09:05:23'),
(600, NULL, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-17 09:05:32'),
(601, NULL, 'approve', 'Permintaan', 'Eksekusi [approve] di menu [Permintaan]. Data: {\"id\":\"18\",\"material_id\":\"3\",\"qty_approved\":\"10\"}', '::1', '2026-04-17 09:05:42'),
(602, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 10:03:11'),
(603, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 10:12:05'),
(604, NULL, 'submit_request', 'Persetujuan', 'Eksekusi [submit_request] di menu [Persetujuan]. Data: {\"material_id\":\"3\",\"qty\":\"10\",\"notes\":\"\",\"action\":\"submit_request\"}', '::1', '2026-04-17 10:16:54'),
(605, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 10:16:54'),
(606, NULL, 'proses_ke_po', 'Persetujuan', 'Eksekusi [proses_ke_po] di menu [Persetujuan]. Data: {\"action\":\"proses_ke_po\",\"id\":\"1\"}', '::1', '2026-04-17 10:17:03'),
(607, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 10:17:03'),
(608, NULL, 'submit_request', 'Persetujuan', 'Eksekusi [submit_request] di menu [Persetujuan]. Data: {\"material_id\":\"3\",\"qty\":\"10\",\"notes\":\"\",\"action\":\"submit_request\"}', '::1', '2026-04-17 10:24:13'),
(609, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 10:24:13'),
(610, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 10:56:35'),
(611, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 10:56:38'),
(612, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 10:56:40'),
(613, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 10:56:40'),
(614, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 10:56:41'),
(615, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 10:56:41'),
(616, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 10:56:42'),
(617, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 10:56:42'),
(618, NULL, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '::1', '2026-04-17 10:56:43'),
(619, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:00:39'),
(620, NULL, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '::1', '2026-04-17 11:00:42'),
(621, NULL, 'save_po', 'Po', 'Eksekusi [save_po] di menu [Po]. Data: {\"action\":\"save_po\",\"supplier_id\":\"1\",\"shipping_date\":\"2026-04-18\",\"cart\":\"[{\\\"pr_id\\\":1,\\\"material_id\\\":3,\\\"material_name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty\\\":21,\\\"unit\\\":\\\"Kg\\\"}]\"}', '::1', '2026-04-17 11:01:25'),
(622, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:01:26'),
(623, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 11:05:43'),
(624, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 11:06:06'),
(625, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:06:12'),
(626, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:06:24'),
(627, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:06:24'),
(628, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:06:25'),
(629, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:06:26'),
(630, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:06:26'),
(631, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:06:27'),
(632, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:06:28'),
(633, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:06:28'),
(634, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:06:29'),
(635, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:06:30'),
(636, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:06:30'),
(637, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:06:31'),
(638, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:06:31'),
(639, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:06:31'),
(640, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:06:32'),
(641, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:06:32'),
(642, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 11:06:35'),
(643, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 11:14:49'),
(644, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 11:14:49'),
(645, NULL, 'get_po_detail', 'Persetujuan', 'Eksekusi [get_po_detail] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 11:14:51'),
(646, NULL, 'update_po_status', 'Persetujuan', 'Eksekusi [update_po_status] di menu [Persetujuan]. Data: {\"action\":\"update_po_status\",\"po_id\":\"1\",\"status\":\"approved\"}', '::1', '2026-04-17 11:14:54'),
(647, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 11:14:55'),
(648, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:15:05'),
(649, NULL, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '::1', '2026-04-17 11:15:06'),
(650, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 11:15:10'),
(651, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 11:15:11'),
(652, NULL, 'proses_ke_po', 'Persetujuan', 'Eksekusi [proses_ke_po] di menu [Persetujuan]. Data: {\"action\":\"proses_ke_po\",\"id\":\"2\"}', '::1', '2026-04-17 11:15:16'),
(653, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 11:15:16'),
(654, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:15:21'),
(655, NULL, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '::1', '2026-04-17 11:15:22'),
(656, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:23:22'),
(657, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:43:44'),
(658, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:45:02'),
(659, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:45:42'),
(660, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:46:20'),
(661, NULL, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '::1', '2026-04-17 11:48:03'),
(662, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 11:49:19'),
(663, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 11:49:25'),
(664, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 11:49:26'),
(665, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 11:49:32'),
(666, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 11:49:33'),
(667, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 11:49:33'),
(668, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 11:49:34'),
(669, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:49:46'),
(670, NULL, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '::1', '2026-04-17 11:49:47'),
(671, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 11:50:16'),
(672, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 11:50:22'),
(673, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 11:50:23'),
(674, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:51:25'),
(675, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:51:31'),
(676, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:51:31'),
(677, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:51:32'),
(678, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:51:32'),
(679, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:51:33'),
(680, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-17 11:51:33'),
(681, NULL, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '::1', '2026-04-17 11:51:34'),
(682, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 11:53:33'),
(683, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 11:53:33'),
(684, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 11:53:38'),
(685, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-17 11:53:39'),
(686, NULL, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-18 14:14:49'),
(687, NULL, 'archive', 'Inventory', 'Eksekusi [archive] di menu [Inventory]. Data: {\"id\":\"3\"}', '::1', '2026-04-18 14:15:26'),
(688, NULL, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-18 14:25:31'),
(689, NULL, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-18 14:25:36'),
(690, NULL, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-18 14:26:19'),
(691, NULL, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-18 14:39:43'),
(692, NULL, 'toggle_status', 'Inventory', 'Eksekusi [toggle_status] di menu [Inventory]. Data: {\"action\":\"toggle_status\",\"id\":\"3\",\"new_status\":\"active\"}', '::1', '2026-04-18 14:39:47'),
(693, NULL, 'download_template', 'Inventory', 'Eksekusi [download_template] di menu [Inventory]. Data: []', '::1', '2026-04-18 14:40:01'),
(694, NULL, 'export', 'Inventory', 'Eksekusi [export] di menu [Inventory]. Data: []', '::1', '2026-04-18 14:40:10'),
(695, NULL, 'save', 'Inventory', 'Eksekusi [save] di menu [Inventory]. Data: {\"id\":\"\",\"sku_code\":\"G02\",\"status\":\"active\",\"material_name\":\"Gula Pasir Kasar\",\"category_id\":\"1\",\"unit\":\"Kg\",\"rack_id\":\"1\",\"expiry_date\":\"2026-10-17\",\"stock\":\"10\",\"min_stock\":\"5\"}', '::1', '2026-04-18 14:40:58'),
(696, NULL, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-18 14:56:22'),
(697, NULL, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-18 14:56:23'),
(698, NULL, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-18 14:57:19'),
(699, NULL, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-18 14:57:39'),
(700, NULL, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-18 14:57:48'),
(701, NULL, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-18 15:04:32'),
(702, NULL, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-18 15:08:06'),
(703, NULL, 'read_detail', 'Monitoring Rak', 'Eksekusi [read_detail] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-18 15:08:07'),
(704, NULL, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-18 15:08:46'),
(705, NULL, 'read_detail', 'Monitoring Rak', 'Eksekusi [read_detail] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-18 15:08:48'),
(706, NULL, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-18 15:15:38'),
(707, NULL, 'read_detail', 'Monitoring Rak', 'Eksekusi [read_detail] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-18 15:15:39'),
(708, NULL, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-18 15:15:55'),
(709, NULL, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-18 15:41:48'),
(710, NULL, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-18 15:41:49'),
(711, NULL, 'save', 'Barang Masuk', 'Eksekusi [save] di menu [Barang Masuk]. Data: {\"material_id\":\"3\",\"qty\":\"10\",\"expiry_date\":\"2026-04-21\",\"supplier_id\":\"1\",\"notes\":\"\"}', '::1', '2026-04-18 15:42:05'),
(712, NULL, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-18 15:47:25'),
(713, NULL, 'save', 'Barang Masuk', 'Eksekusi [save] di menu [Barang Masuk]. Data: {\"material_id\":\"3\",\"qty\":\"10\",\"expiry_date\":\"2026-04-19\",\"supplier_id\":\"1\",\"notes\":\"\"}', '::1', '2026-04-18 15:47:34'),
(714, NULL, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-18 15:56:29'),
(715, NULL, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '::1', '2026-04-18 15:56:30'),
(716, NULL, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-18 15:56:39'),
(717, NULL, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-18 15:56:45'),
(718, NULL, 'save', 'Barang Masuk', 'Eksekusi [save] di menu [Barang Masuk]. Data: {\"material_id\":\"3\",\"qty\":\"10\",\"expiry_date\":\"2026-04-24\",\"supplier_id\":\"1\",\"notes\":\"\"}', '::1', '2026-04-18 15:57:05'),
(719, NULL, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-18 15:57:13'),
(720, NULL, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '::1', '2026-04-18 15:57:38'),
(721, NULL, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-18 15:57:40'),
(722, NULL, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '::1', '2026-04-18 15:57:41'),
(723, NULL, 'save', 'Barang Keluar', 'Eksekusi [save] di menu [Barang Keluar]. Data: {\"material_id\":\"3\",\"qty\":\"10\",\"status\":\"Expired\",\"notes\":\"\"}', '::1', '2026-04-18 15:57:53'),
(724, NULL, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '::1', '2026-04-18 15:57:53'),
(725, NULL, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-18 15:57:56'),
(726, NULL, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-18 16:43:26'),
(727, NULL, 'init_data', 'Cetak Barcode', 'Eksekusi [init_data] di menu [Cetak Barcode]. Data: []', '::1', '2026-04-18 16:43:27'),
(728, NULL, 'get_rack_items', 'Cetak Barcode', 'Eksekusi [get_rack_items] di menu [Cetak Barcode]. Data: []', '::1', '2026-04-18 16:43:51'),
(729, NULL, 'init_data', 'Cetak Barcode', 'Eksekusi [init_data] di menu [Cetak Barcode]. Data: []', '::1', '2026-04-18 16:52:19'),
(730, 1, 'generate', 'Otorisasi', 'Eksekusi [generate] di menu [Otorisasi]. Data: []', '::1', '2026-04-18 17:28:15'),
(731, NULL, 'init_data', 'Cetak Barcode', 'Eksekusi [init_data] di menu [Cetak Barcode]. Data: []', '::1', '2026-04-18 17:32:42'),
(732, NULL, 'init_data', 'Cetak Barcode', 'Eksekusi [init_data] di menu [Cetak Barcode]. Data: []', '::1', '2026-04-18 17:32:49'),
(733, NULL, 'init_data', 'Cetak Barcode', 'Eksekusi [init_data] di menu [Cetak Barcode]. Data: []', '::1', '2026-04-18 17:32:55'),
(734, NULL, 'generate', 'Otorisasi', 'Eksekusi [generate] di menu [Otorisasi]. Data: []', '::1', '2026-04-18 17:38:37'),
(735, NULL, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '::1', '2026-04-18 17:50:27'),
(736, NULL, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '::1', '2026-04-18 17:50:38'),
(737, NULL, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"674536\"}', '::1', '2026-04-18 17:50:44'),
(738, NULL, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"674536\"}', '::1', '2026-04-18 17:50:45'),
(739, NULL, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"674536\"}', '::1', '2026-04-18 17:50:50'),
(740, NULL, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"674536\"}', '::1', '2026-04-18 17:50:51'),
(741, NULL, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"674536\"}', '::1', '2026-04-18 17:50:52'),
(742, NULL, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"674536\"}', '::1', '2026-04-18 17:50:52'),
(743, NULL, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"674536\"}', '::1', '2026-04-18 17:50:52'),
(744, NULL, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"674536\"}', '::1', '2026-04-18 17:50:53'),
(745, NULL, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"674536\"}', '::1', '2026-04-18 17:50:53'),
(746, NULL, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"674536\"}', '::1', '2026-04-18 17:50:54'),
(747, NULL, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"674536\"}', '::1', '2026-04-18 17:50:54'),
(748, NULL, 'generate', 'Otorisasi', 'Eksekusi [generate] di menu [Otorisasi]. Data: []', '::1', '2026-04-18 17:50:58'),
(749, NULL, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '::1', '2026-04-18 17:51:05'),
(750, NULL, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '::1', '2026-04-18 17:51:15'),
(751, NULL, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '::1', '2026-04-18 17:51:18'),
(752, NULL, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"216927\"}', '::1', '2026-04-18 17:51:28'),
(753, NULL, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"216927\"}', '::1', '2026-04-18 17:51:29'),
(754, NULL, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"216927\"}', '::1', '2026-04-18 17:51:29'),
(755, NULL, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"216927\"}', '::1', '2026-04-18 17:51:50'),
(756, NULL, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"216927\"}', '::1', '2026-04-18 17:51:51'),
(757, NULL, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"216927\"}', '::1', '2026-04-18 17:51:51'),
(758, NULL, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"216927\"}', '::1', '2026-04-18 17:51:52'),
(759, NULL, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"216927\"}', '::1', '2026-04-18 17:51:53'),
(760, NULL, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"216927\"}', '::1', '2026-04-18 17:52:23'),
(761, NULL, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"216927\"}', '::1', '2026-04-18 17:52:43'),
(762, NULL, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"216927\"}', '::1', '2026-04-18 17:52:44'),
(763, NULL, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '::1', '2026-04-18 17:54:59'),
(764, NULL, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '::1', '2026-04-18 17:57:17'),
(765, NULL, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"674536\"}', '::1', '2026-04-18 17:57:25'),
(766, NULL, 'save_opname', 'Scanner', 'Eksekusi [save_opname] di menu [Scanner]. Data: {\"action\":\"save_opname\",\"drafts\":\"[{\\\"material_id\\\":\\\"3\\\",\\\"material_name\\\":\\\"[CB01] Coklat Batang Elmer\\\",\\\"system_stock\\\":100,\\\"physical_stock\\\":1,\\\"difference\\\":-99,\\\"unit\\\":\\\"Kg\\\",\\\"notes\\\":\\\"\\\"}]\"}', '::1', '2026-04-18 17:57:43'),
(767, NULL, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '::1', '2026-04-18 17:57:43'),
(768, NULL, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '::1', '2026-04-18 18:00:46'),
(769, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-18 18:00:54'),
(770, NULL, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-19 07:18:49'),
(771, NULL, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '::1', '2026-04-19 07:21:37'),
(772, NULL, 'save', 'Permintaan', 'Eksekusi [save] di menu [Permintaan]. Data: {\"action\":\"save\",\"cart\":\"[{\\\"material_id\\\":\\\"3\\\",\\\"name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty\\\":1,\\\"unit\\\":\\\"Kg\\\",\\\"notes\\\":\\\"\\\"}]\"}', '::1', '2026-04-19 07:21:56'),
(773, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 07:40:51'),
(774, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 07:40:58'),
(775, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 07:40:59'),
(776, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 07:40:59'),
(777, NULL, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '::1', '2026-04-19 07:41:00'),
(778, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 07:51:13'),
(779, NULL, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '::1', '2026-04-19 07:51:15'),
(780, NULL, 'save_po', 'Po', 'Eksekusi [save_po] di menu [Po]. Data: {\"action\":\"save_po\",\"supplier_id\":\"1\",\"shipping_date\":\"2026-04-19\",\"cart\":\"[{\\\"pr_id\\\":3,\\\"material_id\\\":3,\\\"material_name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty\\\":2,\\\"unit\\\":\\\"Kg\\\"}]\"}', '::1', '2026-04-19 07:51:43'),
(781, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 07:51:44'),
(782, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 07:51:47'),
(783, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 07:51:48'),
(784, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 07:51:49'),
(785, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 07:51:49'),
(786, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 07:52:36'),
(787, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 07:52:39'),
(788, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 07:52:41'),
(789, NULL, 'get_po_detail', 'Persetujuan', 'Eksekusi [get_po_detail] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 07:52:46'),
(790, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 07:53:44'),
(791, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 08:09:33'),
(792, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 08:09:35'),
(793, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 08:09:35'),
(794, NULL, 'get_po_detail', 'Persetujuan', 'Eksekusi [get_po_detail] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 08:09:38'),
(795, NULL, 'update_po_status', 'Persetujuan', 'Eksekusi [update_po_status] di menu [Persetujuan]. Data: {\"action\":\"update_po_status\",\"po_id\":\"1\",\"status\":\"approved\"}', '::1', '2026-04-19 08:09:43'),
(796, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 08:09:57'),
(797, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 08:11:15'),
(798, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 08:11:17'),
(799, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 08:11:17'),
(800, NULL, 'update_po_status', 'Persetujuan', 'Eksekusi [update_po_status] di menu [Persetujuan]. Data: {\"action\":\"update_po_status\",\"po_id\":\"1\",\"status\":\"approved\"}', '::1', '2026-04-19 08:11:19'),
(801, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 08:15:57'),
(802, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 08:15:57'),
(803, NULL, 'update_po_status', 'Persetujuan', 'Eksekusi [update_po_status] di menu [Persetujuan]. Data: {\"action\":\"update_po_status\",\"po_id\":\"1\",\"status\":\"approved\"}', '::1', '2026-04-19 08:15:59'),
(804, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 08:15:59'),
(805, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 08:16:05'),
(806, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 08:16:52'),
(807, NULL, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '::1', '2026-04-19 08:18:09'),
(808, NULL, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '::1', '2026-04-19 08:18:14'),
(809, NULL, 'save', 'Permintaan', 'Eksekusi [save] di menu [Permintaan]. Data: {\"action\":\"save\",\"cart\":\"[{\\\"material_id\\\":\\\"5\\\",\\\"name\\\":\\\"Gula Pasir Kasar\\\",\\\"qty\\\":10,\\\"unit\\\":\\\"Kg\\\",\\\"notes\\\":\\\"\\\"}]\"}', '::1', '2026-04-19 08:18:23'),
(810, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 08:18:55'),
(811, NULL, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '::1', '2026-04-19 08:18:56'),
(812, NULL, 'save_po', 'Po', 'Eksekusi [save_po] di menu [Po]. Data: {\"action\":\"save_po\",\"supplier_id\":\"2\",\"shipping_date\":\"2026-04-21\",\"cart\":\"[{\\\"pr_id\\\":4,\\\"material_id\\\":5,\\\"material_name\\\":\\\"Gula Pasir Kasar\\\",\\\"qty\\\":10,\\\"unit\\\":\\\"Kg\\\"}]\"}', '::1', '2026-04-19 08:19:07'),
(813, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 08:19:09'),
(814, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 08:19:11'),
(815, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 08:19:11'),
(816, NULL, 'update_po_status', 'Persetujuan', 'Eksekusi [update_po_status] di menu [Persetujuan]. Data: {\"action\":\"update_po_status\",\"po_id\":\"2\",\"status\":\"approved\"}', '::1', '2026-04-19 08:19:15'),
(817, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 08:19:15'),
(818, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 08:19:20'),
(819, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 08:28:07'),
(820, NULL, 'get_po_receive', 'Po', 'Eksekusi [get_po_receive] di menu [Po]. Data: []', '::1', '2026-04-19 08:28:09'),
(821, NULL, 'save_receive_po', 'Po', 'Eksekusi [save_receive_po] di menu [Po]. Data: {\"action\":\"save_receive_po\",\"po_id\":\"2\",\"items\":\"[{\\\"material_id\\\":5,\\\"material_name\\\":\\\"Gula Pasir Kasar\\\",\\\"qty_po\\\":10,\\\"qty_terima\\\":10,\\\"unit\\\":\\\"Kg\\\",\\\"price\\\":\\\"100000\\\",\\\"exp_date\\\":\\\"2026-04-21\\\"}]\"}', '::1', '2026-04-19 08:28:32'),
(822, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 08:28:32'),
(823, NULL, 'get_po_receive', 'Po', 'Eksekusi [get_po_receive] di menu [Po]. Data: []', '::1', '2026-04-19 08:28:38'),
(824, NULL, 'save_receive_po', 'Po', 'Eksekusi [save_receive_po] di menu [Po]. Data: {\"action\":\"save_receive_po\",\"po_id\":\"1\",\"items\":\"[{\\\"material_id\\\":3,\\\"material_name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty_po\\\":2,\\\"qty_terima\\\":2,\\\"unit\\\":\\\"Kg\\\",\\\"price\\\":\\\"100000\\\",\\\"exp_date\\\":\\\"\\\"}]\"}', '::1', '2026-04-19 08:28:44'),
(825, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 08:28:44'),
(826, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 08:33:15'),
(827, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 08:33:23'),
(828, NULL, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '::1', '2026-04-19 08:33:27'),
(829, NULL, 'save', 'Permintaan', 'Eksekusi [save] di menu [Permintaan]. Data: {\"action\":\"save\",\"cart\":\"[{\\\"material_id\\\":\\\"3\\\",\\\"name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty\\\":1,\\\"unit\\\":\\\"Kg\\\",\\\"notes\\\":\\\"\\\"}]\"}', '::1', '2026-04-19 08:33:41'),
(830, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 08:33:45'),
(831, NULL, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '::1', '2026-04-19 08:33:49'),
(832, NULL, 'save_po', 'Po', 'Eksekusi [save_po] di menu [Po]. Data: {\"action\":\"save_po\",\"supplier_id\":\"1\",\"shipping_date\":\"2026-04-20\",\"cart\":\"[{\\\"pr_id\\\":5,\\\"material_id\\\":3,\\\"material_name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty\\\":1,\\\"unit\\\":\\\"Kg\\\"}]\"}', '::1', '2026-04-19 08:34:09'),
(833, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 08:34:11'),
(834, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 08:34:17'),
(835, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 08:34:17'),
(836, NULL, 'update_po_status', 'Persetujuan', 'Eksekusi [update_po_status] di menu [Persetujuan]. Data: {\"action\":\"update_po_status\",\"po_id\":\"3\",\"status\":\"approved\"}', '::1', '2026-04-19 08:34:20'),
(837, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 08:34:20'),
(838, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 08:34:27'),
(839, NULL, 'get_po_receive', 'Po', 'Eksekusi [get_po_receive] di menu [Po]. Data: []', '::1', '2026-04-19 08:34:29'),
(840, NULL, 'save_receive_po', 'Po', 'Eksekusi [save_receive_po] di menu [Po]. Data: {\"action\":\"save_receive_po\",\"po_id\":\"3\",\"items\":\"[{\\\"material_id\\\":3,\\\"material_name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty_po\\\":1,\\\"qty_terima\\\":1,\\\"unit\\\":\\\"Kg\\\",\\\"price\\\":\\\"10000\\\",\\\"exp_date\\\":\\\"\\\"}]\"}', '::1', '2026-04-19 08:34:38'),
(841, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 08:34:38'),
(842, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 08:42:07'),
(843, NULL, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-19 08:50:42'),
(844, NULL, 'get_payment_data', 'Pembayaran', 'Eksekusi [get_payment_data] di menu [Pembayaran]. Data: []', '::1', '2026-04-19 08:50:50'),
(845, NULL, 'save_payment', 'Pembayaran', 'Eksekusi [save_payment] di menu [Pembayaran]. Data: {\"action\":\"save_payment\",\"po_id\":\"3\",\"method_id\":\"1\",\"amount\":\"5000\",\"pay_date\":\"2026-04-19T15:50\",\"notes\":\"\"}', '::1', '2026-04-19 08:51:00'),
(846, NULL, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-19 08:51:00'),
(847, NULL, 'get_payment_data', 'Pembayaran', 'Eksekusi [get_payment_data] di menu [Pembayaran]. Data: []', '::1', '2026-04-19 08:51:02'),
(848, NULL, 'save_payment', 'Pembayaran', 'Eksekusi [save_payment] di menu [Pembayaran]. Data: {\"action\":\"save_payment\",\"po_id\":\"3\",\"method_id\":\"1\",\"amount\":\"2000\",\"pay_date\":\"2026-04-19T15:51\",\"notes\":\"\"}', '::1', '2026-04-19 08:51:10'),
(849, NULL, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-19 08:51:10'),
(850, NULL, 'get_payment_data', 'Pembayaran', 'Eksekusi [get_payment_data] di menu [Pembayaran]. Data: []', '::1', '2026-04-19 08:51:15'),
(851, NULL, 'save_payment', 'Pembayaran', 'Eksekusi [save_payment] di menu [Pembayaran]. Data: {\"action\":\"save_payment\",\"po_id\":\"1\",\"method_id\":\"3\",\"amount\":\"100000\",\"pay_date\":\"2026-04-19T15:51\",\"notes\":\"\"}', '::1', '2026-04-19 08:51:22'),
(852, NULL, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-19 08:51:22'),
(853, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 08:51:32'),
(854, NULL, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-19 08:52:19'),
(855, NULL, 'get_payment_data', 'Pembayaran', 'Eksekusi [get_payment_data] di menu [Pembayaran]. Data: []', '::1', '2026-04-19 08:52:30'),
(856, NULL, 'save_payment', 'Pembayaran', 'Eksekusi [save_payment] di menu [Pembayaran]. Data: {\"action\":\"save_payment\",\"po_id\":\"3\",\"method_id\":\"1\",\"amount\":\"3000\",\"pay_date\":\"2026-04-19T15:52\",\"notes\":\"\"}', '::1', '2026-04-19 08:52:37'),
(857, NULL, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-19 08:52:37'),
(858, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 08:52:44'),
(859, NULL, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-19 08:52:59'),
(860, NULL, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-19 08:53:03'),
(861, NULL, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-19 08:53:04'),
(862, NULL, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-19 08:53:05'),
(863, NULL, 'read_comparison', 'Supplier', 'Eksekusi [read_comparison] di menu [Supplier]. Data: []', '::1', '2026-04-19 09:13:23'),
(864, NULL, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '::1', '2026-04-19 09:13:48'),
(865, NULL, 'save', 'Permintaan', 'Eksekusi [save] di menu [Permintaan]. Data: {\"action\":\"save\",\"cart\":\"[{\\\"material_id\\\":\\\"3\\\",\\\"name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty\\\":1,\\\"unit\\\":\\\"Kg\\\",\\\"notes\\\":\\\"\\\"}]\"}', '::1', '2026-04-19 09:13:57'),
(866, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 09:14:09'),
(867, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 09:14:13'),
(868, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 09:14:13'),
(869, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 09:14:23'),
(870, NULL, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '::1', '2026-04-19 09:14:25'),
(871, NULL, 'save_po', 'Po', 'Eksekusi [save_po] di menu [Po]. Data: {\"action\":\"save_po\",\"supplier_id\":\"2\",\"shipping_date\":\"2026-04-19\",\"cart\":\"[{\\\"pr_id\\\":6,\\\"material_id\\\":3,\\\"material_name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty\\\":1,\\\"unit\\\":\\\"Kg\\\"}]\"}', '::1', '2026-04-19 09:14:35'),
(872, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 09:14:37'),
(873, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 09:14:40'),
(874, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 09:14:40'),
(875, NULL, 'get_po_detail', 'Persetujuan', 'Eksekusi [get_po_detail] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 09:14:42'),
(876, NULL, 'update_po_status', 'Persetujuan', 'Eksekusi [update_po_status] di menu [Persetujuan]. Data: {\"action\":\"update_po_status\",\"po_id\":\"4\",\"status\":\"approved\"}', '::1', '2026-04-19 09:14:47'),
(877, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 09:14:48'),
(878, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 09:14:51'),
(879, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 09:14:52'),
(880, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 09:14:58'),
(881, NULL, 'get_po_receive', 'Po', 'Eksekusi [get_po_receive] di menu [Po]. Data: []', '::1', '2026-04-19 09:15:00'),
(882, NULL, 'save_receive_po', 'Po', 'Eksekusi [save_receive_po] di menu [Po]. Data: {\"action\":\"save_receive_po\",\"po_id\":\"4\",\"items\":\"[{\\\"material_id\\\":3,\\\"material_name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty_po\\\":1,\\\"qty_terima\\\":1,\\\"unit\\\":\\\"Kg\\\",\\\"price\\\":\\\"9000\\\",\\\"exp_date\\\":\\\"2026-04-23\\\"}]\"}', '::1', '2026-04-19 09:15:13'),
(883, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 09:15:14'),
(884, NULL, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-19 09:15:20'),
(885, NULL, 'get_payment_data', 'Pembayaran', 'Eksekusi [get_payment_data] di menu [Pembayaran]. Data: []', '::1', '2026-04-19 09:15:22'),
(886, NULL, 'save_payment', 'Pembayaran', 'Eksekusi [save_payment] di menu [Pembayaran]. Data: {\"action\":\"save_payment\",\"po_id\":\"4\",\"method_id\":\"1\",\"amount\":\"9000\",\"pay_date\":\"2026-04-19T16:15\",\"notes\":\"\"}', '::1', '2026-04-19 09:15:32'),
(887, NULL, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-19 09:15:32'),
(888, NULL, 'read_comparison', 'Supplier', 'Eksekusi [read_comparison] di menu [Supplier]. Data: []', '::1', '2026-04-19 09:15:40'),
(889, NULL, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-19 09:16:19'),
(890, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '::1', '2026-04-19 09:17:32'),
(891, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-19 09:17:32'),
(892, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-19 09:17:32'),
(893, NULL, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '::1', '2026-04-19 09:51:32'),
(894, NULL, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '::1', '2026-04-19 09:59:03'),
(895, NULL, 'init', 'Pembayaran', 'Eksekusi [init] di menu [Pembayaran]. Data: []', '::1', '2026-04-19 10:17:19'),
(896, NULL, 'init', 'Pembayaran', 'Eksekusi [init] di menu [Pembayaran]. Data: []', '::1', '2026-04-19 10:28:37'),
(897, NULL, 'init', 'Pembayaran', 'Eksekusi [init] di menu [Pembayaran]. Data: []', '::1', '2026-04-19 10:29:59'),
(898, NULL, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '::1', '2026-04-19 10:49:21'),
(899, NULL, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '::1', '2026-04-19 10:56:49'),
(900, NULL, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '::1', '2026-04-19 10:57:00'),
(901, NULL, 'init', 'Supplier-terakhir', 'Eksekusi [init] di menu [Supplier-terakhir]. Data: []', '::1', '2026-04-19 11:37:21'),
(902, NULL, 'init', 'Supplier-terakhir', 'Eksekusi [init] di menu [Supplier-terakhir]. Data: []', '::1', '2026-04-19 12:28:44'),
(903, NULL, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '::1', '2026-04-19 12:28:51'),
(904, NULL, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '::1', '2026-04-19 12:28:55'),
(905, NULL, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '::1', '2026-04-19 12:29:28'),
(906, NULL, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '::1', '2026-04-19 12:29:30'),
(907, NULL, 'init', 'Pembayaran', 'Eksekusi [init] di menu [Pembayaran]. Data: []', '::1', '2026-04-19 12:30:22'),
(908, NULL, 'init', 'Pembayaran', 'Eksekusi [init] di menu [Pembayaran]. Data: []', '::1', '2026-04-19 12:30:29'),
(909, NULL, 'init', 'Pembayaran', 'Eksekusi [init] di menu [Pembayaran]. Data: []', '::1', '2026-04-19 12:30:32');
INSERT INTO `system_logs` (`id`, `user_id`, `action`, `menu`, `description`, `ip_address`, `created_at`) VALUES
(910, NULL, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '::1', '2026-04-19 12:32:08'),
(911, NULL, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '::1', '2026-04-19 12:32:43'),
(912, NULL, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"216927\"}', '::1', '2026-04-19 12:32:46'),
(913, NULL, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '::1', '2026-04-19 12:33:05'),
(914, NULL, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-19 12:33:09'),
(915, NULL, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-19 12:33:15'),
(916, NULL, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-19 12:33:18'),
(917, NULL, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '::1', '2026-04-19 12:33:21'),
(918, NULL, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-19 12:33:22'),
(919, NULL, 'init_data', 'Cetak Barcode', 'Eksekusi [init_data] di menu [Cetak Barcode]. Data: []', '::1', '2026-04-19 12:33:27'),
(920, NULL, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '::1', '2026-04-19 12:33:33'),
(921, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 12:33:35'),
(922, NULL, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-19 12:33:37'),
(923, NULL, 'read_comparison', 'Supplier', 'Eksekusi [read_comparison] di menu [Supplier]. Data: []', '::1', '2026-04-19 12:33:38'),
(924, NULL, 'read_comparison', 'Supplier', 'Eksekusi [read_comparison] di menu [Supplier]. Data: []', '::1', '2026-04-19 12:33:44'),
(925, NULL, 'read_comparison', 'Supplier', 'Eksekusi [read_comparison] di menu [Supplier]. Data: []', '::1', '2026-04-19 12:33:48'),
(926, NULL, 'read_comparison', 'Supplier', 'Eksekusi [read_comparison] di menu [Supplier]. Data: []', '::1', '2026-04-19 12:34:24'),
(927, NULL, 'init', 'Pembayaran-po', 'Eksekusi [init] di menu [Pembayaran-po]. Data: []', '::1', '2026-04-19 12:34:57'),
(928, NULL, 'init', 'Pembayaran-po', 'Eksekusi [init] di menu [Pembayaran-po]. Data: []', '::1', '2026-04-19 12:35:19'),
(929, NULL, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '::1', '2026-04-19 12:35:23'),
(930, NULL, 'init', 'Supplier-terakhir', 'Eksekusi [init] di menu [Supplier-terakhir]. Data: []', '::1', '2026-04-19 12:35:30'),
(931, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 12:35:59'),
(932, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 12:35:59'),
(933, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 12:36:00'),
(934, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 12:36:01'),
(935, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 12:59:42'),
(936, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 12:59:42'),
(937, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 13:00:21'),
(938, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 13:00:21'),
(939, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 13:00:43'),
(940, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 13:00:43'),
(941, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 13:00:44'),
(942, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 13:00:44'),
(943, NULL, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-19 13:34:05'),
(944, NULL, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-19 13:34:18'),
(945, NULL, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-19 13:34:35'),
(946, NULL, 'get_dashboard_stats', 'Dashboard', 'Eksekusi [get_dashboard_stats] di menu [Dashboard]. Data: []', '::1', '2026-04-19 13:52:06'),
(947, NULL, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-19 13:52:43'),
(948, NULL, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-19 13:52:55'),
(949, NULL, 'Aksi Tidak Dikenal', 'Manajemen-role', 'Eksekusi [Aksi Tidak Dikenal] di menu [Manajemen-role]. Data: {\"role_id\":\"\",\"role_name\":\"Admin-gudang\",\"role_slug\":\"admin_gudang\",\"permissions\":[\"dashboard\",\"persetujuan\"]}', '::1', '2026-04-19 13:53:12'),
(950, NULL, 'Aksi Tidak Dikenal', 'Manajemen-role', 'Eksekusi [Aksi Tidak Dikenal] di menu [Manajemen-role]. Data: {\"role_id\":\"\",\"role_name\":\"Admin-gudang\",\"role_slug\":\"admin_gudang\",\"permissions\":[\"dashboard\",\"persetujuan\"]}', '::1', '2026-04-19 13:53:15'),
(951, NULL, 'Aksi Tidak Dikenal', 'Manajemen-role', 'Eksekusi [Aksi Tidak Dikenal] di menu [Manajemen-role]. Data: {\"role_id\":\"\",\"role_name\":\"Admin-gudang\",\"role_slug\":\"admin_gudang\",\"permissions\":[\"dashboard\",\"persetujuan\"]}', '::1', '2026-04-19 13:55:01'),
(952, NULL, 'save', 'Manajemen-role', 'Eksekusi [save] di menu [Manajemen-role]. Data: {\"role_id\":\"\",\"role_name\":\"Admin-gudang\",\"role_slug\":\"admin_gudang\",\"permissions\":[\"dashboard\",\"persetujuan\"],\"action\":\"save\"}', '::1', '2026-04-19 14:01:24'),
(953, NULL, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-19 14:01:29'),
(954, NULL, 'Aksi Tidak Dikenal', 'User-management', 'Eksekusi [Aksi Tidak Dikenal] di menu [User-management]. Data: {\"user_id\":\"\",\"name\":\"Randy admin gudang\",\"username\":\"admin-gudang\",\"password\":\"******\",\"role\":\"admin_gudang\",\"status\":\"active\"}', '::1', '2026-04-19 14:01:49'),
(955, NULL, 'Aksi Tidak Dikenal', 'User-management', 'Eksekusi [Aksi Tidak Dikenal] di menu [User-management]. Data: {\"user_id\":\"\",\"name\":\"Randy admin gudang\",\"username\":\"admin-gudang\",\"password\":\"******\",\"role\":\"admin_gudang\",\"status\":\"active\"}', '::1', '2026-04-19 14:01:51'),
(956, NULL, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-19 14:07:56'),
(957, NULL, 'save', 'User-management', 'Eksekusi [save] di menu [User-management]. Data: {\"user_id\":\"\",\"name\":\"Randy admin gudang\",\"username\":\"admin010\",\"password\":\"******\",\"role\":\"admin_gudang\",\"status\":\"active\",\"action\":\"save\"}', '::1', '2026-04-19 14:08:20'),
(958, NULL, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-19 14:20:33'),
(959, NULL, 'save', 'User-management', 'Eksekusi [save] di menu [User-management]. Data: {\"user_id\":\"\",\"name\":\"Randy admin gudang\",\"username\":\"admin010\",\"password\":\"******\",\"role\":\"admin_gudang\",\"status\":\"active\",\"action\":\"save\"}', '::1', '2026-04-19 14:20:51'),
(960, NULL, 'save', 'User-management', 'Eksekusi [save] di menu [User-management]. Data: {\"user_id\":\"20\",\"name\":\"Randy admin gudang\",\"username\":\"admin-gudang\",\"password\":\"******\",\"role\":\"admin_gudang\",\"status\":\"active\",\"action\":\"save\"}', '::1', '2026-04-19 14:21:26'),
(961, 20, 'get_dashboard_stats', 'Dashboard', 'Eksekusi [get_dashboard_stats] di menu [Dashboard]. Data: []', '::1', '2026-04-19 14:21:37'),
(962, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 14:21:46'),
(963, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 14:21:46'),
(964, 20, 'get_dashboard_stats', 'Dashboard', 'Eksekusi [get_dashboard_stats] di menu [Dashboard]. Data: []', '::1', '2026-04-19 14:22:17'),
(965, 20, 'save', 'Manajemen-role', 'Eksekusi [save] di menu [Manajemen-role]. Data: {\"role_id\":\"\",\"role_name\":\"owner\",\"role_slug\":\"owner\",\"permissions\":[\"dashboard\",\"lap_barang_masuk\",\"lap_barang_keluar\",\"lap_po\",\"lap_pembayaran_po\",\"lap_stok_opname\",\"lap_kartu_stok\",\"lap_stok_menipis\",\"lap_stok_terbanyak\",\"lap_perbandingan_harga\",\"lap_supplier\"],\"action\":\"save\"}', '::1', '2026-04-19 14:22:48'),
(966, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-19 14:22:54'),
(967, 20, 'save', 'User-management', 'Eksekusi [save] di menu [User-management]. Data: {\"user_id\":\"\",\"name\":\"deden\",\"username\":\"owner-gudang\",\"password\":\"******\",\"role\":\"owner\",\"status\":\"active\",\"action\":\"save\"}', '::1', '2026-04-19 14:23:10'),
(968, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-19 14:23:52'),
(969, NULL, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '::1', '2026-04-19 14:24:29'),
(970, NULL, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-19 14:24:29'),
(971, NULL, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-19 14:24:29'),
(972, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 14:27:16'),
(973, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 14:27:16'),
(974, 20, 'save', 'Manajemen-role', 'Eksekusi [save] di menu [Manajemen-role]. Data: {\"role_id\":\"\",\"role_name\":\"admin gudang 2\",\"role_slug\":\"admin_gudang_2\",\"permissions\":[\"dashboard\",\"persetujuan\"],\"action\":\"save\"}', '::1', '2026-04-19 14:31:37'),
(975, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-19 14:31:41'),
(976, 20, 'save', 'User-management', 'Eksekusi [save] di menu [User-management]. Data: {\"user_id\":\"\",\"name\":\"Rendy\",\"username\":\"admin-gudang2\",\"password\":\"******\",\"role\":\"admin_gudang_2\",\"status\":\"active\",\"action\":\"save\"}', '::1', '2026-04-19 14:32:07'),
(977, NULL, 'get_dashboard_stats', 'Dashboard', 'Eksekusi [get_dashboard_stats] di menu [Dashboard]. Data: []', '::1', '2026-04-19 14:32:29'),
(978, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-19 14:34:34'),
(979, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-19 14:34:54'),
(980, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-19 14:47:12'),
(981, NULL, 'get_dashboard_stats', 'Dashboard', 'Eksekusi [get_dashboard_stats] di menu [Dashboard]. Data: []', '::1', '2026-04-19 14:47:39'),
(982, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 14:47:41'),
(983, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-19 14:47:41'),
(984, NULL, 'get_dashboard_stats', 'Dashboard', 'Eksekusi [get_dashboard_stats] di menu [Dashboard]. Data: []', '::1', '2026-04-19 14:47:42'),
(985, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-19 15:14:53'),
(986, 20, 'save', 'Manajemen-role', 'Eksekusi [save] di menu [Manajemen-role]. Data: {\"role_id\":\"3\",\"role_name\":\"admin gudang 2\",\"role_slug\":\"admin_gudang_2\",\"permissions\":[\"dashboard\",\"persetujuan\",\"master_inventory\",\"master_kategori\"],\"action\":\"save\"}', '::1', '2026-04-19 15:16:58'),
(987, NULL, 'get_dashboard_stats', 'Dashboard', 'Eksekusi [get_dashboard_stats] di menu [Dashboard]. Data: []', '::1', '2026-04-19 15:17:01'),
(988, NULL, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-19 15:17:04'),
(989, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '::1', '2026-04-19 15:24:55'),
(990, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 15:25:26'),
(991, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 15:27:45'),
(992, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-19 15:28:31'),
(993, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-19 15:28:35'),
(994, 20, 'init', 'Pembayaran-po', 'Eksekusi [init] di menu [Pembayaran-po]. Data: []', '::1', '2026-04-19 15:32:08'),
(995, 20, 'init', 'Pembayaran-po', 'Eksekusi [init] di menu [Pembayaran-po]. Data: []', '::1', '2026-04-19 15:32:57'),
(996, 20, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '::1', '2026-04-19 15:33:03'),
(997, 20, 'init', 'Supplier-terakhir', 'Eksekusi [init] di menu [Supplier-terakhir]. Data: []', '::1', '2026-04-19 15:43:24'),
(998, 20, 'get_dashboard_stats', 'Dashboard', 'Eksekusi [get_dashboard_stats] di menu [Dashboard]. Data: []', '::1', '2026-04-20 02:03:53'),
(999, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-20 02:04:02'),
(1000, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-20 02:10:43'),
(1001, 20, 'get_dashboard_stats', 'Dashboard', 'Eksekusi [get_dashboard_stats] di menu [Dashboard]. Data: []', '::1', '2026-04-20 02:24:12'),
(1002, 20, 'get_dashboard_stats', 'Dashboard', 'Eksekusi [get_dashboard_stats] di menu [Dashboard]. Data: []', '::1', '2026-04-20 02:24:45'),
(1003, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:25:30'),
(1004, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:25:30'),
(1005, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:25:31'),
(1006, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:25:35'),
(1007, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:25:38'),
(1008, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:25:42'),
(1009, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '::1', '2026-04-20 02:25:59'),
(1010, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-20 02:26:23'),
(1011, 16, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-20 02:27:16'),
(1012, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-20 02:27:16'),
(1013, 16, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-20 02:27:17'),
(1014, 16, 'submit_request', 'Master Bahan', 'Eksekusi [submit_request] di menu [Master Bahan]. Data: {\"warehouse_id\":\"1\",\"pilar_id\":\"3\",\"qty\":\"0.5\"}', '::1', '2026-04-20 02:27:29'),
(1015, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-20 02:27:30'),
(1016, 20, 'approve', 'Permintaan-dapur', 'Eksekusi [approve] di menu [Permintaan-dapur]. Data: {\"id\":\"19\",\"material_id\":\"3\",\"qty_approved\":\"0.5\"}', '::1', '2026-04-20 02:27:47'),
(1017, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-20 02:27:52'),
(1018, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '::1', '2026-04-20 02:28:23'),
(1019, 20, 'save', 'Permintaan', 'Eksekusi [save] di menu [Permintaan]. Data: {\"action\":\"save\",\"cart\":\"[{\\\"material_id\\\":\\\"3\\\",\\\"name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty\\\":2,\\\"unit\\\":\\\"Kg\\\",\\\"notes\\\":\\\"\\\"}]\"}', '::1', '2026-04-20 02:28:34'),
(1020, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:28:40'),
(1021, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:28:40'),
(1022, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:28:45'),
(1023, 20, 'proses_ke_po', 'Persetujuan', 'Eksekusi [proses_ke_po] di menu [Persetujuan]. Data: {\"action\":\"proses_ke_po\",\"id\":\"7\"}', '::1', '2026-04-20 02:28:48'),
(1024, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:28:49'),
(1025, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-20 02:28:54'),
(1026, 20, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '::1', '2026-04-20 02:28:56'),
(1027, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-20 02:29:01'),
(1028, 20, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '::1', '2026-04-20 02:29:02'),
(1029, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:29:17'),
(1030, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:29:17'),
(1031, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:29:19'),
(1032, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:29:20'),
(1033, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-20 02:29:38'),
(1034, 20, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '::1', '2026-04-20 02:29:40'),
(1035, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '::1', '2026-04-20 02:29:44'),
(1036, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-20 02:29:47'),
(1037, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '::1', '2026-04-20 02:29:51'),
(1038, 20, 'save', 'Permintaan', 'Eksekusi [save] di menu [Permintaan]. Data: {\"action\":\"save\",\"cart\":\"[{\\\"material_id\\\":\\\"3\\\",\\\"name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty\\\":1,\\\"unit\\\":\\\"Kg\\\",\\\"notes\\\":\\\"\\\"}]\"}', '::1', '2026-04-20 02:29:59'),
(1039, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:30:07'),
(1040, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:30:07'),
(1041, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:30:11'),
(1042, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:30:18'),
(1043, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-20 02:30:26'),
(1044, 20, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '::1', '2026-04-20 02:30:32'),
(1045, 20, 'save_po', 'Po', 'Eksekusi [save_po] di menu [Po]. Data: {\"action\":\"save_po\",\"supplier_id\":\"1\",\"shipping_date\":\"2026-04-22\",\"cart\":\"[{\\\"pr_id\\\":8,\\\"material_id\\\":3,\\\"material_name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty\\\":3,\\\"unit\\\":\\\"Kg\\\"}]\"}', '::1', '2026-04-20 02:31:23'),
(1046, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-20 02:31:25'),
(1047, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:31:32'),
(1048, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:31:32'),
(1049, 20, 'get_po_detail', 'Persetujuan', 'Eksekusi [get_po_detail] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:31:37'),
(1050, 20, 'get_po_detail', 'Persetujuan', 'Eksekusi [get_po_detail] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:33:46'),
(1051, 20, 'update_po_status', 'Persetujuan', 'Eksekusi [update_po_status] di menu [Persetujuan]. Data: {\"action\":\"update_po_status\",\"po_id\":\"5\",\"status\":\"approved\"}', '::1', '2026-04-20 02:34:21'),
(1052, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:34:21'),
(1053, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-20 02:34:27'),
(1054, 20, 'get_po_receive', 'Po', 'Eksekusi [get_po_receive] di menu [Po]. Data: []', '::1', '2026-04-20 02:34:47'),
(1055, 20, 'save_receive_po', 'Po', 'Eksekusi [save_receive_po] di menu [Po]. Data: {\"action\":\"save_receive_po\",\"po_id\":\"5\",\"items\":\"[{\\\"material_id\\\":3,\\\"material_name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty_po\\\":3,\\\"qty_terima\\\":3,\\\"unit\\\":\\\"Kg\\\",\\\"price\\\":\\\"300000\\\",\\\"exp_date\\\":\\\"2026-04-23\\\"}]\"}', '::1', '2026-04-20 02:35:32'),
(1056, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-20 02:35:32'),
(1057, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-20 02:35:37'),
(1058, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-20 02:35:39'),
(1059, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-20 02:37:03'),
(1060, 20, 'get_payment_data', 'Pembayaran', 'Eksekusi [get_payment_data] di menu [Pembayaran]. Data: []', '::1', '2026-04-20 02:37:07'),
(1061, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-20 02:37:22'),
(1062, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-20 02:37:31'),
(1063, 20, 'get_payment_data', 'Pembayaran', 'Eksekusi [get_payment_data] di menu [Pembayaran]. Data: []', '::1', '2026-04-20 02:37:32'),
(1064, 20, 'save_payment', 'Pembayaran', 'Eksekusi [save_payment] di menu [Pembayaran]. Data: {\"action\":\"save_payment\",\"po_id\":\"5\",\"method_id\":\"1\",\"amount\":\"150000\",\"pay_date\":\"2026-04-20T09:37\",\"notes\":\"\"}', '::1', '2026-04-20 02:37:48'),
(1065, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-20 02:37:48'),
(1066, 20, 'get_payment_data', 'Pembayaran', 'Eksekusi [get_payment_data] di menu [Pembayaran]. Data: []', '::1', '2026-04-20 02:37:55'),
(1067, 20, 'get_payment_data', 'Pembayaran', 'Eksekusi [get_payment_data] di menu [Pembayaran]. Data: []', '::1', '2026-04-20 02:38:01'),
(1068, 20, 'save_payment', 'Pembayaran', 'Eksekusi [save_payment] di menu [Pembayaran]. Data: {\"action\":\"save_payment\",\"po_id\":\"5\",\"method_id\":\"1\",\"amount\":\"150000\",\"pay_date\":\"2026-04-20T09:38\",\"notes\":\"\"}', '::1', '2026-04-20 02:38:42'),
(1069, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-20 02:38:42'),
(1070, 20, 'init', 'Pembayaran-po', 'Eksekusi [init] di menu [Pembayaran-po]. Data: []', '::1', '2026-04-20 02:38:52'),
(1071, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-20 02:39:23'),
(1072, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-20 02:39:43'),
(1073, 20, 'read_detail', 'Monitoring Rak', 'Eksekusi [read_detail] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-20 02:39:45'),
(1074, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-20 02:39:48'),
(1075, 20, 'read_detail', 'Monitoring Rak', 'Eksekusi [read_detail] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-20 02:39:49'),
(1076, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-20 02:39:50'),
(1077, 20, 'read_detail', 'Monitoring Rak', 'Eksekusi [read_detail] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-20 02:39:51'),
(1078, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-20 02:39:52'),
(1079, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-20 02:39:54'),
(1080, 20, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '::1', '2026-04-20 02:41:14'),
(1081, 20, 'init_data', 'Cetak Barcode', 'Eksekusi [init_data] di menu [Cetak Barcode]. Data: []', '::1', '2026-04-20 02:41:42'),
(1082, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-20 02:42:29'),
(1083, 20, 'init_data', 'Cetak Barcode', 'Eksekusi [init_data] di menu [Cetak Barcode]. Data: []', '::1', '2026-04-20 02:42:46'),
(1084, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-20 02:43:16'),
(1085, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-20 02:43:19'),
(1086, 20, 'toggle_status', 'Inventory', 'Eksekusi [toggle_status] di menu [Inventory]. Data: {\"action\":\"toggle_status\",\"id\":\"3\",\"new_status\":\"inactive\"}', '::1', '2026-04-20 02:44:12'),
(1087, 20, 'toggle_status', 'Inventory', 'Eksekusi [toggle_status] di menu [Inventory]. Data: {\"action\":\"toggle_status\",\"id\":\"3\",\"new_status\":\"active\"}', '::1', '2026-04-20 02:44:23'),
(1088, 20, 'generate', 'Otorisasi', 'Eksekusi [generate] di menu [Otorisasi]. Data: []', '::1', '2026-04-20 02:45:09'),
(1089, 20, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '::1', '2026-04-20 02:45:12'),
(1090, 20, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '::1', '2026-04-20 02:45:22'),
(1091, 20, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"836855\"}', '::1', '2026-04-20 02:45:27'),
(1092, 20, 'save_opname', 'Scanner', 'Eksekusi [save_opname] di menu [Scanner]. Data: {\"action\":\"save_opname\",\"drafts\":\"[{\\\"material_id\\\":\\\"3\\\",\\\"material_name\\\":\\\"[CB01] Coklat Batang Elmer\\\",\\\"system_stock\\\":7.5,\\\"physical_stock\\\":1,\\\"difference\\\":-6.5,\\\"unit\\\":\\\"Kg\\\",\\\"notes\\\":\\\"\\\"}]\"}', '::1', '2026-04-20 02:45:47'),
(1093, 20, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '::1', '2026-04-20 02:45:47'),
(1094, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-20 02:45:53'),
(1095, 20, 'init', 'Pembayaran-po', 'Eksekusi [init] di menu [Pembayaran-po]. Data: []', '::1', '2026-04-20 02:46:27'),
(1096, 20, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '::1', '2026-04-20 02:46:35'),
(1097, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '::1', '2026-04-20 02:47:44'),
(1098, 20, 'init', 'Supplier-terakhir', 'Eksekusi [init] di menu [Supplier-terakhir]. Data: []', '::1', '2026-04-20 02:48:23'),
(1099, 20, 'init', 'Supplier-terakhir', 'Eksekusi [init] di menu [Supplier-terakhir]. Data: []', '::1', '2026-04-20 02:49:09'),
(1100, 20, 'init', 'Supplier-terakhir', 'Eksekusi [init] di menu [Supplier-terakhir]. Data: []', '::1', '2026-04-20 02:49:42'),
(1101, 20, 'init', 'Supplier-terakhir', 'Eksekusi [init] di menu [Supplier-terakhir]. Data: []', '::1', '2026-04-20 02:50:31'),
(1102, 20, 'init', 'Supplier-terakhir', 'Eksekusi [init] di menu [Supplier-terakhir]. Data: []', '::1', '2026-04-20 02:51:04'),
(1103, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-20 02:51:28'),
(1104, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:51:35'),
(1105, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:51:35'),
(1106, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-20 02:52:22'),
(1107, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:52:46'),
(1108, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:52:46'),
(1109, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-20 02:53:11'),
(1110, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:53:35'),
(1111, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:53:35'),
(1112, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:54:19'),
(1113, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:54:28'),
(1114, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:54:30'),
(1115, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-20 02:54:32'),
(1116, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-20 02:55:17'),
(1117, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-20 02:55:20'),
(1118, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-20 02:55:20'),
(1119, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-20 02:55:21'),
(1120, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-20 02:55:22'),
(1121, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '::1', '2026-04-20 03:03:31'),
(1122, 16, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-20 03:03:46'),
(1123, 16, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-20 03:03:50'),
(1124, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '::1', '2026-04-20 03:04:05'),
(1125, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-20 03:04:58'),
(1126, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-20 03:05:07'),
(1127, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-20 03:05:24'),
(1128, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-20 03:05:41'),
(1129, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '::1', '2026-04-20 03:09:24'),
(1130, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-20 03:10:23'),
(1131, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '::1', '2026-04-20 03:11:04'),
(1132, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-20 03:11:29'),
(1133, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-20 03:14:27'),
(1134, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-20 03:16:54'),
(1135, 18, 'get_employees', 'Produk Keluar', 'Eksekusi [get_employees] di menu [Produk Keluar]. Data: []', '::1', '2026-04-20 03:16:56'),
(1136, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-20 03:18:21'),
(1137, 18, 'save', 'Input Produksi', 'Eksekusi [save] di menu [Input Produksi]. Data: {\"employee_id\":\"1\",\"product_id\":[\"2\"],\"quantity\":[\"1\"],\"warehouse_id\":\"1\",\"notes\":\"\",\"pin\":\"1234\"}', '::1', '2026-04-20 03:18:54'),
(1138, 18, 'save', 'Input Produksi', 'Eksekusi [save] di menu [Input Produksi]. Data: {\"employee_id\":\"1\",\"product_id\":[\"3\"],\"quantity\":[\"1\"],\"warehouse_id\":\"1\",\"notes\":\"\",\"pin\":\"1234\"}', '::1', '2026-04-20 03:19:02'),
(1139, 18, 'get_employees', 'Produk Keluar', 'Eksekusi [get_employees] di menu [Produk Keluar]. Data: []', '::1', '2026-04-20 03:19:15'),
(1140, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-20 03:19:58'),
(1141, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-20 03:19:58'),
(1142, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-20 03:19:59'),
(1143, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-20 03:20:01'),
(1144, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-20 03:20:01'),
(1145, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-20 03:20:02'),
(1146, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-20 03:20:02'),
(1147, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-20 03:20:49'),
(1148, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-20 03:20:50'),
(1149, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-20 06:26:57'),
(1150, 1, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '::1', '2026-04-20 06:26:57'),
(1151, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-20 06:27:00'),
(1152, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-20 06:27:00'),
(1153, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-20 06:28:27'),
(1154, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-20 06:28:27'),
(1155, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-20 06:30:30'),
(1156, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-20 06:30:30'),
(1157, 20, 'get_dashboard_stats', 'Dashboard', 'Eksekusi [get_dashboard_stats] di menu [Dashboard]. Data: []', '::1', '2026-04-20 13:24:10'),
(1158, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-20 13:24:22'),
(1159, 20, 'get_dashboard_stats', 'Dashboard', 'Eksekusi [get_dashboard_stats] di menu [Dashboard]. Data: []', '127.0.0.1', '2026-04-21 02:32:09'),
(1160, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '127.0.0.1', '2026-04-21 02:32:29'),
(1161, 20, 'save', 'Permintaan', 'Eksekusi [save] di menu [Permintaan]. Data: {\"action\":\"save\",\"cart\":\"[{\\\"material_id\\\":\\\"3\\\",\\\"name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty\\\":10,\\\"unit\\\":\\\"Kg\\\",\\\"notes\\\":\\\"\\\"}]\"}', '::1', '2026-04-21 02:32:40'),
(1162, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 02:32:44'),
(1163, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 02:32:44'),
(1164, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 02:32:45'),
(1165, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 02:32:47'),
(1166, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 02:32:56'),
(1167, 20, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '::1', '2026-04-21 02:32:58'),
(1168, 20, 'save_po', 'Po', 'Eksekusi [save_po] di menu [Po]. Data: {\"action\":\"save_po\",\"supplier_id\":\"1\",\"shipping_date\":\"2026-04-25\",\"cart\":\"[{\\\"pr_id\\\":9,\\\"material_id\\\":3,\\\"material_name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty\\\":10,\\\"unit\\\":\\\"Kg\\\"}]\"}', '::1', '2026-04-21 02:33:13'),
(1169, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 02:33:15'),
(1170, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 02:33:22'),
(1171, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 02:33:22'),
(1172, 20, 'get_po_detail', 'Persetujuan', 'Eksekusi [get_po_detail] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 02:33:25'),
(1173, 20, 'get_po_detail', 'Persetujuan', 'Eksekusi [get_po_detail] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 02:33:35'),
(1174, 20, 'get_po_detail', 'Persetujuan', 'Eksekusi [get_po_detail] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 02:33:41'),
(1175, 20, 'get_po_detail', 'Persetujuan', 'Eksekusi [get_po_detail] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 02:36:00'),
(1176, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 02:42:27'),
(1177, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 02:42:27'),
(1178, 20, 'get_po_detail', 'Persetujuan', 'Eksekusi [get_po_detail] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 02:42:28'),
(1179, 20, 'update_po_status', 'Persetujuan', 'Eksekusi [update_po_status] di menu [Persetujuan]. Data: {\"po_id\":\"6\",\"detail_id\":[\"11\"],\"price\":[\"0.00\"],\"qty\":[\"9\"],\"action\":\"update_po_status\",\"status\":\"approved\"}', '::1', '2026-04-21 02:42:41'),
(1180, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 02:42:41'),
(1181, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 02:42:49'),
(1182, 20, 'get_po_receive', 'Po', 'Eksekusi [get_po_receive] di menu [Po]. Data: []', '::1', '2026-04-21 02:43:04'),
(1183, 20, 'save_receive_po', 'Po', 'Eksekusi [save_receive_po] di menu [Po]. Data: {\"action\":\"save_receive_po\",\"po_id\":\"6\",\"items\":\"[{\\\"material_id\\\":3,\\\"material_name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty_po\\\":9,\\\"qty_terima\\\":9,\\\"unit\\\":\\\"Kg\\\",\\\"price\\\":\\\"50000\\\",\\\"exp_date\\\":\\\"2026-04-30\\\"}]\"}', '::1', '2026-04-21 02:44:34'),
(1184, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 02:44:34'),
(1185, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-21 02:44:46'),
(1186, 20, 'get_payment_data', 'Pembayaran', 'Eksekusi [get_payment_data] di menu [Pembayaran]. Data: []', '::1', '2026-04-21 02:44:51'),
(1187, 20, 'save_payment', 'Pembayaran', 'Eksekusi [save_payment] di menu [Pembayaran]. Data: {\"action\":\"save_payment\",\"po_id\":\"6\",\"method_id\":\"1\",\"amount\":\"300000\",\"pay_date\":\"2026-04-21T09:44\",\"notes\":\"\"}', '::1', '2026-04-21 02:45:04'),
(1188, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-21 02:45:04'),
(1189, 20, 'get_payment_data', 'Pembayaran', 'Eksekusi [get_payment_data] di menu [Pembayaran]. Data: []', '::1', '2026-04-21 02:45:13'),
(1190, 20, 'save_payment', 'Pembayaran', 'Eksekusi [save_payment] di menu [Pembayaran]. Data: {\"action\":\"save_payment\",\"po_id\":\"6\",\"method_id\":\"2\",\"amount\":\"50000\",\"pay_date\":\"2026-04-21T09:45\",\"notes\":\"\"}', '::1', '2026-04-21 02:46:00'),
(1191, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-21 02:46:00'),
(1192, 20, 'get_payment_data', 'Pembayaran', 'Eksekusi [get_payment_data] di menu [Pembayaran]. Data: []', '::1', '2026-04-21 02:46:04'),
(1193, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-21 02:46:51'),
(1194, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-21 02:56:37'),
(1195, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 02:56:56'),
(1196, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-21 02:57:15'),
(1197, 20, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '::1', '2026-04-21 02:58:13'),
(1198, 20, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '::1', '2026-04-21 03:06:36'),
(1199, 20, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '::1', '2026-04-21 03:06:42'),
(1200, 20, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '::1', '2026-04-21 03:06:56'),
(1201, 20, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '::1', '2026-04-21 03:12:59'),
(1202, 20, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '::1', '2026-04-21 03:13:47'),
(1203, 20, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '::1', '2026-04-21 03:14:51'),
(1204, 20, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '::1', '2026-04-21 03:18:47'),
(1205, 20, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '::1', '2026-04-21 03:20:39'),
(1206, 20, 'get_dashboard_stats', 'Dashboard', 'Eksekusi [get_dashboard_stats] di menu [Dashboard]. Data: []', '::1', '2026-04-21 03:21:44'),
(1207, 20, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '::1', '2026-04-21 03:21:50'),
(1208, 20, 'generate', 'Otorisasi', 'Eksekusi [generate] di menu [Otorisasi]. Data: []', '::1', '2026-04-21 03:21:58'),
(1209, 20, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '::1', '2026-04-21 03:22:03'),
(1210, 20, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"709921\"}', '::1', '2026-04-21 03:22:06'),
(1211, 20, 'save_opname', 'Scanner', 'Eksekusi [save_opname] di menu [Scanner]. Data: {\"action\":\"save_opname\",\"drafts\":\"[{\\\"material_id\\\":\\\"3\\\",\\\"material_name\\\":\\\"[CB01] Coklat Batang Elmer\\\",\\\"system_stock\\\":10,\\\"physical_stock\\\":1,\\\"difference\\\":-9,\\\"unit\\\":\\\"Kg\\\",\\\"notes\\\":\\\"\\\"}]\"}', '::1', '2026-04-21 03:22:27'),
(1212, 20, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '::1', '2026-04-21 03:22:27'),
(1213, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-21 03:23:17'),
(1214, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-21 03:23:41'),
(1215, 20, 'get_payment_data', 'Pembayaran', 'Eksekusi [get_payment_data] di menu [Pembayaran]. Data: []', '::1', '2026-04-21 03:23:43'),
(1216, 20, 'save_payment', 'Pembayaran', 'Eksekusi [save_payment] di menu [Pembayaran]. Data: {\"action\":\"save_payment\",\"po_id\":\"6\",\"method_id\":\"1\",\"amount\":\"100000\",\"pay_date\":\"2026-04-21T10:23\",\"notes\":\"\"}', '::1', '2026-04-21 03:23:49'),
(1217, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-21 03:23:49'),
(1218, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-21 03:23:52'),
(1219, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-21 03:23:57'),
(1220, 20, 'get_payment_data', 'Pembayaran', 'Eksekusi [get_payment_data] di menu [Pembayaran]. Data: []', '::1', '2026-04-21 03:24:03'),
(1221, 20, 'save_payment', 'Pembayaran', 'Eksekusi [save_payment] di menu [Pembayaran]. Data: {\"action\":\"save_payment\",\"po_id\":\"5\",\"method_id\":\"1\",\"amount\":\"600000\",\"pay_date\":\"2026-04-21T10:24\",\"notes\":\"\"}', '::1', '2026-04-21 03:24:13'),
(1222, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-21 03:24:13'),
(1223, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-21 03:24:18'),
(1224, 20, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '::1', '2026-04-21 03:25:57'),
(1225, 20, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '::1', '2026-04-21 03:26:01'),
(1226, 20, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '::1', '2026-04-21 03:26:52'),
(1227, 16, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 03:29:35'),
(1228, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 03:29:35'),
(1229, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '::1', '2026-04-21 03:30:18'),
(1230, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-21 03:30:26'),
(1231, 20, 'save', 'Barang Masuk', 'Eksekusi [save] di menu [Barang Masuk]. Data: {\"material_id\":\"3\",\"qty\":\"1\",\"expiry_date\":\"2026-04-21\",\"supplier_id\":\"\",\"notes\":\"\"}', '::1', '2026-04-21 03:30:38'),
(1232, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-21 03:30:42'),
(1233, 20, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '::1', '2026-04-21 03:31:00'),
(1234, 20, 'save', 'Barang Keluar', 'Eksekusi [save] di menu [Barang Keluar]. Data: {\"material_id\":\"3\",\"qty\":\"1\",\"status\":\"Rusak\",\"notes\":\"\"}', '::1', '2026-04-21 03:31:06'),
(1235, 20, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '::1', '2026-04-21 03:31:06'),
(1236, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-21 03:31:09'),
(1237, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '::1', '2026-04-21 03:31:13'),
(1238, 20, 'save', 'Permintaan', 'Eksekusi [save] di menu [Permintaan]. Data: {\"action\":\"save\",\"cart\":\"[{\\\"material_id\\\":\\\"3\\\",\\\"name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty\\\":1,\\\"unit\\\":\\\"Kg\\\",\\\"notes\\\":\\\"\\\"}]\"}', '::1', '2026-04-21 03:31:21'),
(1239, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 03:31:24'),
(1240, 20, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '::1', '2026-04-21 03:31:25'),
(1241, 20, 'save_po', 'Po', 'Eksekusi [save_po] di menu [Po]. Data: {\"action\":\"save_po\",\"supplier_id\":\"1\",\"shipping_date\":\"2026-04-21\",\"cart\":\"[{\\\"pr_id\\\":10,\\\"material_id\\\":3,\\\"material_name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty\\\":1,\\\"unit\\\":\\\"Kg\\\"}]\"}', '::1', '2026-04-21 03:31:38'),
(1242, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 03:31:40'),
(1243, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-21 03:31:44'),
(1244, 20, 'get_payment_data', 'Pembayaran', 'Eksekusi [get_payment_data] di menu [Pembayaran]. Data: []', '::1', '2026-04-21 03:31:46'),
(1245, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 03:31:52'),
(1246, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 03:31:57'),
(1247, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 03:31:57'),
(1248, 20, 'update_po_status', 'Persetujuan', 'Eksekusi [update_po_status] di menu [Persetujuan]. Data: {\"action\":\"update_po_status\",\"po_id\":\"7\",\"status\":\"approved\"}', '::1', '2026-04-21 03:32:02'),
(1249, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 03:32:02'),
(1250, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 03:32:07'),
(1251, 20, 'get_po_receive', 'Po', 'Eksekusi [get_po_receive] di menu [Po]. Data: []', '::1', '2026-04-21 03:32:09'),
(1252, 20, 'save_receive_po', 'Po', 'Eksekusi [save_receive_po] di menu [Po]. Data: {\"action\":\"save_receive_po\",\"po_id\":\"7\",\"items\":\"[{\\\"material_id\\\":3,\\\"material_name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty_po\\\":1,\\\"qty_terima\\\":1,\\\"unit\\\":\\\"Kg\\\",\\\"price\\\":\\\"50000\\\",\\\"exp_date\\\":\\\"2026-04-25\\\"}]\"}', '::1', '2026-04-21 03:32:24'),
(1253, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 03:32:24'),
(1254, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-21 03:32:29');
INSERT INTO `system_logs` (`id`, `user_id`, `action`, `menu`, `description`, `ip_address`, `created_at`) VALUES
(1255, 20, 'get_payment_data', 'Pembayaran', 'Eksekusi [get_payment_data] di menu [Pembayaran]. Data: []', '::1', '2026-04-21 03:32:30'),
(1256, 20, 'save_payment', 'Pembayaran', 'Eksekusi [save_payment] di menu [Pembayaran]. Data: {\"action\":\"save_payment\",\"po_id\":\"7\",\"method_id\":\"2\",\"amount\":\"50000\",\"pay_date\":\"2026-04-21T10:32\",\"notes\":\"\"}', '::1', '2026-04-21 03:32:37'),
(1257, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-21 03:32:37'),
(1258, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-21 03:32:58'),
(1259, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 03:33:21'),
(1260, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 03:33:37'),
(1261, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 03:33:50'),
(1262, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 03:33:51'),
(1263, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 03:33:52'),
(1264, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 03:33:54'),
(1265, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 03:33:56'),
(1266, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 03:33:56'),
(1267, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 03:33:58'),
(1268, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 03:33:59'),
(1269, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 03:34:01'),
(1270, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 03:34:03'),
(1271, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 03:34:37'),
(1272, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 03:35:22'),
(1273, 1, 'generate', 'Otorisasi', 'Eksekusi [generate] di menu [Otorisasi]. Data: []', '::1', '2026-04-21 03:44:24'),
(1274, 1, 'verify_pin', 'Stok Opname', 'Eksekusi [verify_pin] di menu [Stok Opname]. Data: {\"pin\":\"964907\"}', '::1', '2026-04-21 03:44:38'),
(1275, 1, 'save', 'Stok Opname', 'Eksekusi [save] di menu [Stok Opname]. Data: {\"material_id\":[\"6\"],\"system_stock\":[\"10.9000\"],\"actual_stock\":[\"1\"],\"reason\":\"\"}', '::1', '2026-04-21 03:45:20'),
(1276, 20, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '::1', '2026-04-21 03:54:06'),
(1277, 20, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '::1', '2026-04-21 03:56:12'),
(1278, 20, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '::1', '2026-04-21 04:03:09'),
(1279, 20, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '::1', '2026-04-21 04:03:15'),
(1280, 20, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"709921\"}', '::1', '2026-04-21 04:03:18'),
(1281, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-21 04:03:27'),
(1282, 20, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '::1', '2026-04-21 04:03:31'),
(1283, 20, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"709921\"}', '::1', '2026-04-21 04:03:37'),
(1284, 20, 'save_opname', 'Scanner', 'Eksekusi [save_opname] di menu [Scanner]. Data: {\"action\":\"save_opname\",\"drafts\":\"[{\\\"material_id\\\":\\\"3\\\",\\\"material_name\\\":\\\"[CB01] Coklat Batang Elmer\\\",\\\"system_stock\\\":2,\\\"physical_stock\\\":1.5,\\\"difference\\\":-0.5,\\\"unit\\\":\\\"Kg\\\",\\\"notes\\\":\\\"\\\"}]\"}', '::1', '2026-04-21 04:04:27'),
(1285, 20, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '::1', '2026-04-21 04:04:27'),
(1286, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-21 04:04:38'),
(1287, 16, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 04:36:32'),
(1288, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 04:36:32'),
(1289, 16, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 04:36:43'),
(1290, 1, 'save', 'Stok Opname', 'Eksekusi [save] di menu [Stok Opname]. Data: {\"material_id\":[\"22\"],\"system_stock\":[\"10.0000\"],\"actual_stock\":[\"9\"],\"reason\":\"\"}', '::1', '2026-04-21 04:37:09'),
(1291, 16, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 04:37:15'),
(1292, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 04:37:15'),
(1293, 20, 'get_dashboard_stats', 'Dashboard', 'Eksekusi [get_dashboard_stats] di menu [Dashboard]. Data: []', '::1', '2026-04-21 04:37:52'),
(1294, 16, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 04:38:02'),
(1295, 16, 'submit_request', 'Master Bahan', 'Eksekusi [submit_request] di menu [Master Bahan]. Data: {\"warehouse_id\":\"1\",\"pilar_id\":\"5\",\"qty\":\"10\"}', '::1', '2026-04-21 04:38:12'),
(1296, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 04:38:12'),
(1297, 20, 'approve', 'Permintaan-dapur', 'Eksekusi [approve] di menu [Permintaan-dapur]. Data: {\"id\":\"20\",\"material_id\":\"5\",\"qty_approved\":\"10\"}', '::1', '2026-04-21 04:38:23'),
(1298, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 04:38:27'),
(1299, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 04:38:27'),
(1300, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-21 04:38:31'),
(1301, 16, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 04:38:36'),
(1302, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 04:38:36'),
(1303, 16, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 04:38:49'),
(1304, 16, 'submit_request', 'Master Bahan', 'Eksekusi [submit_request] di menu [Master Bahan]. Data: {\"warehouse_id\":\"1\",\"pilar_id\":\"5\",\"qty\":\"1\"}', '::1', '2026-04-21 04:38:53'),
(1305, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 04:38:53'),
(1306, 20, 'approve', 'Permintaan-dapur', 'Eksekusi [approve] di menu [Permintaan-dapur]. Data: {\"id\":\"21\",\"material_id\":\"5\",\"qty_approved\":\"1\"}', '::1', '2026-04-21 04:39:05'),
(1307, 16, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 04:39:08'),
(1308, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 04:39:08'),
(1309, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 04:39:12'),
(1310, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 04:39:12'),
(1311, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 04:39:14'),
(1312, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-21 04:39:26'),
(1313, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 04:39:33'),
(1314, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 04:39:33'),
(1315, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 04:39:35'),
(1316, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 04:39:36'),
(1317, 16, 'get_units', 'Master Bahan', 'Eksekusi [get_units] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 04:39:44'),
(1318, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 04:39:44'),
(1319, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-21 04:40:17'),
(1320, 16, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 04:40:35'),
(1321, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 04:50:57'),
(1322, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 04:51:01'),
(1323, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 04:51:02'),
(1324, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 04:51:04'),
(1325, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 04:51:06'),
(1326, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 04:51:06'),
(1327, 16, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 04:51:07'),
(1328, 16, 'submit_request', 'Master Bahan', 'Eksekusi [submit_request] di menu [Master Bahan]. Data: {\"warehouse_id\":\"1\",\"pilar_id\":\"3\",\"qty\":\"500\",\"req_unit\":\"gram\"}', '::1', '2026-04-21 04:51:22'),
(1329, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 04:51:22'),
(1330, 20, 'approve', 'Permintaan-dapur', 'Eksekusi [approve] di menu [Permintaan-dapur]. Data: {\"id\":\"22\",\"material_id\":\"3\",\"qty_approved\":\"0.5\"}', '::1', '2026-04-21 05:17:36'),
(1331, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-21 05:17:42'),
(1332, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 05:17:52'),
(1333, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 05:17:55'),
(1334, 16, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 05:18:11'),
(1335, 16, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 05:18:14'),
(1336, 16, 'submit_request', 'Master Bahan', 'Eksekusi [submit_request] di menu [Master Bahan]. Data: {\"warehouse_id\":\"1\",\"pilar_id\":\"2\",\"qty\":\"10\",\"req_unit\":\"default\"}', '::1', '2026-04-21 05:18:19'),
(1337, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 05:18:19'),
(1338, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-21 05:18:23'),
(1339, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 05:18:43'),
(1340, 20, 'approve', 'Permintaan-dapur', 'Eksekusi [approve] di menu [Permintaan-dapur]. Data: {\"id\":\"23\",\"material_id\":\"2\",\"qty_approved\":\"10\"}', '::1', '2026-04-21 05:18:50'),
(1341, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 05:18:55'),
(1342, 17, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 05:24:06'),
(1343, 17, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 05:24:08'),
(1344, 17, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 05:24:10'),
(1345, 17, 'submit_request', 'Master Bahan', 'Eksekusi [submit_request] di menu [Master Bahan]. Data: {\"warehouse_id\":\"2\",\"pilar_id\":\"2\",\"qty\":\"500\",\"req_unit\":\"gram\"}', '::1', '2026-04-21 05:24:19'),
(1346, 17, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 05:24:19'),
(1347, 20, 'approve', 'Permintaan-dapur', 'Eksekusi [approve] di menu [Permintaan-dapur]. Data: {\"id\":\"24\",\"material_id\":\"2\",\"qty_approved\":\"0.5\"}', '::1', '2026-04-21 05:24:33'),
(1348, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-21 05:24:37'),
(1349, 17, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-21 05:24:45'),
(1350, 20, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '::1', '2026-04-21 05:25:02'),
(1351, 20, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '::1', '2026-04-21 05:25:27'),
(1352, 20, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '::1', '2026-04-21 05:25:34'),
(1353, 20, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"709921\"}', '::1', '2026-04-21 05:25:37'),
(1354, 20, 'save_opname', 'Scanner', 'Eksekusi [save_opname] di menu [Scanner]. Data: {\"action\":\"save_opname\",\"drafts\":\"[{\\\"material_id\\\":\\\"2\\\",\\\"material_name\\\":\\\"[G01] Gula Pasir Kristal\\\",\\\"system_stock\\\":89.5,\\\"physical_stock\\\":85,\\\"difference\\\":-4.5,\\\"unit\\\":\\\"Kg\\\",\\\"notes\\\":\\\"\\\"}]\"}', '::1', '2026-04-21 05:26:10'),
(1355, 20, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '::1', '2026-04-21 05:26:10'),
(1356, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-21 05:26:14'),
(1357, 20, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '::1', '2026-04-21 05:26:27'),
(1358, 20, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '::1', '2026-04-21 05:26:49'),
(1359, 20, 'init', 'Supplier-terakhir', 'Eksekusi [init] di menu [Supplier-terakhir]. Data: []', '::1', '2026-04-21 09:54:16'),
(1360, 20, 'init', 'Supplier-terakhir', 'Eksekusi [init] di menu [Supplier-terakhir]. Data: []', '::1', '2026-04-21 10:03:18'),
(1361, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '::1', '2026-04-21 10:05:32'),
(1362, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-21 10:05:44'),
(1363, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 10:07:29'),
(1364, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 10:07:29'),
(1365, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-21 10:11:53'),
(1366, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-21 10:29:56'),
(1367, 20, 'save', 'Barang Masuk', 'Eksekusi [save] di menu [Barang Masuk]. Data: {\"material_id\":\"3\",\"qty\":\"10\",\"expiry_date\":\"2026-04-30\",\"supplier_id\":\"1\",\"notes\":\"\"}', '::1', '2026-04-21 10:30:09'),
(1368, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 10:31:40'),
(1369, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 10:31:40'),
(1370, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 10:36:39'),
(1371, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 10:36:39'),
(1372, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 10:36:39'),
(1373, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 10:37:19'),
(1374, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 10:37:21'),
(1375, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 10:37:32'),
(1376, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 10:37:32'),
(1377, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 10:37:32'),
(1378, 20, 'approve_manual', 'Persetujuan', 'Eksekusi [approve_manual] di menu [Persetujuan]. Data: {\"action\":\"approve_manual\",\"id\":\"11\"}', '::1', '2026-04-21 10:37:39'),
(1379, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 10:37:39'),
(1380, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-21 10:37:49'),
(1381, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-21 10:37:56'),
(1382, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 10:39:48'),
(1383, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 10:40:11'),
(1384, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 10:40:12'),
(1385, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 10:40:13'),
(1386, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 10:40:14'),
(1387, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 10:40:16'),
(1388, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 10:43:02'),
(1389, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 10:43:02'),
(1390, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 10:43:02'),
(1391, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 10:44:02'),
(1392, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-21 10:44:42'),
(1393, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 10:44:48'),
(1394, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 10:45:21'),
(1395, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 10:50:22'),
(1396, 20, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '::1', '2026-04-21 10:50:24'),
(1397, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 10:50:29'),
(1398, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:03:47'),
(1399, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:03:51'),
(1400, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:03:52'),
(1401, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:03:53'),
(1402, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:03:54'),
(1403, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:03:54'),
(1404, 20, 'mark_printed', 'Po', 'Eksekusi [mark_printed] di menu [Po]. Data: {\"action\":\"mark_printed\",\"id\":\"7\"}', '::1', '2026-04-21 11:04:00'),
(1405, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:04:00'),
(1406, 20, 'request_print', 'Po', 'Eksekusi [request_print] di menu [Po]. Data: {\"action\":\"request_print\",\"id\":\"7\"}', '::1', '2026-04-21 11:04:32'),
(1407, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:04:32'),
(1408, 20, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '::1', '2026-04-21 11:04:46'),
(1409, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '::1', '2026-04-21 11:04:53'),
(1410, 20, 'save', 'Permintaan', 'Eksekusi [save] di menu [Permintaan]. Data: {\"action\":\"save\",\"cart\":\"[{\\\"material_id\\\":\\\"3\\\",\\\"name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty\\\":10,\\\"unit\\\":\\\"Kg\\\",\\\"notes\\\":\\\"\\\"}]\"}', '::1', '2026-04-21 11:05:00'),
(1411, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:05:05'),
(1412, 20, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '::1', '2026-04-21 11:05:05'),
(1413, 20, 'save_po', 'Po', 'Eksekusi [save_po] di menu [Po]. Data: {\"action\":\"save_po\",\"supplier_id\":\"1\",\"shipping_date\":\"2026-04-21\",\"cart\":\"[{\\\"pr_id\\\":11,\\\"material_id\\\":3,\\\"material_name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty\\\":10,\\\"unit\\\":\\\"Kg\\\"}]\"}', '::1', '2026-04-21 11:05:20'),
(1414, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:05:22'),
(1415, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:05:24'),
(1416, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:05:24'),
(1417, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:05:24'),
(1418, 20, 'get_po_detail', 'Persetujuan', 'Eksekusi [get_po_detail] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:05:25'),
(1419, 20, 'update_po_status', 'Persetujuan', 'Eksekusi [update_po_status] di menu [Persetujuan]. Data: {\"action\":\"update_po_status\",\"po_id\":\"8\",\"status\":\"approved\"}', '::1', '2026-04-21 11:05:31'),
(1420, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:05:31'),
(1421, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:05:36'),
(1422, 20, 'get_po_receive', 'Po', 'Eksekusi [get_po_receive] di menu [Po]. Data: []', '::1', '2026-04-21 11:05:42'),
(1423, 20, 'save_receive_po', 'Po', 'Eksekusi [save_receive_po] di menu [Po]. Data: {\"action\":\"save_receive_po\",\"po_id\":\"8\",\"items\":\"[{\\\"material_id\\\":3,\\\"material_name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty_po\\\":10,\\\"qty_terima\\\":10,\\\"unit\\\":\\\"Kg\\\",\\\"price\\\":\\\"100000\\\",\\\"exp_date\\\":\\\"2026-04-25\\\"}]\"}', '::1', '2026-04-21 11:05:53'),
(1424, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:05:53'),
(1425, 20, 'mark_printed', 'Po', 'Eksekusi [mark_printed] di menu [Po]. Data: {\"action\":\"mark_printed\",\"id\":\"8\"}', '::1', '2026-04-21 11:05:58'),
(1426, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:05:58'),
(1427, 20, 'mark_printed', 'Po', 'Eksekusi [mark_printed] di menu [Po]. Data: {\"action\":\"mark_printed\",\"id\":\"6\"}', '::1', '2026-04-21 11:07:24'),
(1428, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:07:25'),
(1429, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:10:37'),
(1430, 20, 'mark_printed', 'Po', 'Eksekusi [mark_printed] di menu [Po]. Data: {\"action\":\"mark_printed\",\"id\":\"4\"}', '::1', '2026-04-21 11:10:40'),
(1431, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:10:40'),
(1432, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:21:07'),
(1433, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:21:23'),
(1434, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:21:24'),
(1435, 20, 'mark_printed', 'Po', 'Eksekusi [mark_printed] di menu [Po]. Data: {\"action\":\"mark_printed\",\"id\":\"7\",\"tipe\":\"po\"}', '::1', '2026-04-21 11:21:37'),
(1436, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:21:37'),
(1437, 20, 'mark_printed', 'Po', 'Eksekusi [mark_printed] di menu [Po]. Data: {\"action\":\"mark_printed\",\"id\":\"7\",\"tipe\":\"terima\"}', '::1', '2026-04-21 11:21:45'),
(1438, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:21:45'),
(1439, 20, 'request_print', 'Po', 'Eksekusi [request_print] di menu [Po]. Data: {\"action\":\"request_print\",\"id\":\"7\",\"tipe\":\"po\"}', '::1', '2026-04-21 11:21:52'),
(1440, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:21:52'),
(1441, 20, 'request_print', 'Po', 'Eksekusi [request_print] di menu [Po]. Data: {\"action\":\"request_print\",\"id\":\"7\",\"tipe\":\"terima\"}', '::1', '2026-04-21 11:21:55'),
(1442, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:21:55'),
(1443, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-21 11:22:06'),
(1444, 20, 'get_payment_data', 'Pembayaran', 'Eksekusi [get_payment_data] di menu [Pembayaran]. Data: []', '::1', '2026-04-21 11:22:08'),
(1445, 20, 'save_payment', 'Pembayaran', 'Eksekusi [save_payment] di menu [Pembayaran]. Data: {\"action\":\"save_payment\",\"po_id\":\"8\",\"method_id\":\"1\",\"amount\":\"900000\",\"pay_date\":\"2026-04-21T18:22\",\"notes\":\"\"}', '::1', '2026-04-21 11:22:15'),
(1446, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-21 11:22:16'),
(1447, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-21 11:22:21'),
(1448, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-21 11:22:22'),
(1449, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-21 11:22:24'),
(1450, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-21 11:22:26'),
(1451, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:22:30'),
(1452, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '::1', '2026-04-21 11:22:39'),
(1453, 20, 'save', 'Permintaan', 'Eksekusi [save] di menu [Permintaan]. Data: {\"action\":\"save\",\"cart\":\"[{\\\"material_id\\\":\\\"3\\\",\\\"name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty\\\":10,\\\"unit\\\":\\\"Kg\\\",\\\"notes\\\":\\\"\\\"}]\"}', '::1', '2026-04-21 11:22:46'),
(1454, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:22:52'),
(1455, 20, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '::1', '2026-04-21 11:22:52'),
(1456, 20, 'save_po', 'Po', 'Eksekusi [save_po] di menu [Po]. Data: {\"action\":\"save_po\",\"supplier_id\":\"2\",\"shipping_date\":\"2026-04-23\",\"cart\":\"[{\\\"pr_id\\\":12,\\\"material_id\\\":3,\\\"material_name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty\\\":10,\\\"unit\\\":\\\"Kg\\\"}]\"}', '::1', '2026-04-21 11:23:02'),
(1457, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:23:04'),
(1458, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:23:07'),
(1459, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:23:07'),
(1460, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:23:07'),
(1461, 20, 'update_po_status', 'Persetujuan', 'Eksekusi [update_po_status] di menu [Persetujuan]. Data: {\"action\":\"update_po_status\",\"po_id\":\"9\",\"status\":\"approved\"}', '::1', '2026-04-21 11:23:10'),
(1462, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:23:10'),
(1463, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:23:17'),
(1464, 20, 'get_po_receive', 'Po', 'Eksekusi [get_po_receive] di menu [Po]. Data: []', '::1', '2026-04-21 11:23:22'),
(1465, 20, 'save_receive_po', 'Po', 'Eksekusi [save_receive_po] di menu [Po]. Data: {\"action\":\"save_receive_po\",\"po_id\":\"9\",\"items\":\"[{\\\"material_id\\\":3,\\\"material_name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty_po\\\":10,\\\"qty_terima\\\":10,\\\"unit\\\":\\\"Kg\\\",\\\"price\\\":\\\"100000\\\",\\\"exp_date\\\":\\\"2026-04-25\\\"}]\"}', '::1', '2026-04-21 11:23:31'),
(1466, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:23:31'),
(1467, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:24:25'),
(1468, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:24:25'),
(1469, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:24:25'),
(1470, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:40:01'),
(1471, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:40:01'),
(1472, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:40:02'),
(1473, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:40:04'),
(1474, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:40:11'),
(1475, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:40:14'),
(1476, 20, 'read_histori', 'Persetujuan', 'Eksekusi [read_histori] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:40:18'),
(1477, 20, 'read_histori', 'Persetujuan', 'Eksekusi [read_histori] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:40:28'),
(1478, 20, 'read_histori', 'Persetujuan', 'Eksekusi [read_histori] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:40:29'),
(1479, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:40:30'),
(1480, 20, 'proses_izin_cetak', 'Persetujuan', 'Eksekusi [proses_izin_cetak] di menu [Persetujuan]. Data: {\"action\":\"proses_izin_cetak\",\"id\":\"7\",\"tipe\":\"po\",\"keputusan\":\"approve\"}', '::1', '2026-04-21 11:40:32'),
(1481, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:40:33'),
(1482, 20, 'read_histori', 'Persetujuan', 'Eksekusi [read_histori] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:40:34'),
(1483, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:40:38'),
(1484, 20, 'proses_izin_cetak', 'Persetujuan', 'Eksekusi [proses_izin_cetak] di menu [Persetujuan]. Data: {\"action\":\"proses_izin_cetak\",\"id\":\"7\",\"tipe\":\"terima\",\"keputusan\":\"approve\"}', '::1', '2026-04-21 11:40:42'),
(1485, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:40:42'),
(1486, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:40:47'),
(1487, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:40:51'),
(1488, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:40:54'),
(1489, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:40:55'),
(1490, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:40:55'),
(1491, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:40:58'),
(1492, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '::1', '2026-04-21 11:41:26'),
(1493, 20, 'save', 'Permintaan', 'Eksekusi [save] di menu [Permintaan]. Data: {\"action\":\"save\",\"cart\":\"[{\\\"material_id\\\":\\\"2\\\",\\\"name\\\":\\\"Gula Pasir Kristal\\\",\\\"qty\\\":10,\\\"unit\\\":\\\"Kg\\\",\\\"notes\\\":\\\"\\\"}]\"}', '::1', '2026-04-21 11:41:32'),
(1494, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:41:35'),
(1495, 20, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '::1', '2026-04-21 11:41:36'),
(1496, 20, 'save_po', 'Po', 'Eksekusi [save_po] di menu [Po]. Data: {\"action\":\"save_po\",\"supplier_id\":\"1\",\"shipping_date\":\"2026-04-25\",\"cart\":\"[{\\\"pr_id\\\":13,\\\"material_id\\\":2,\\\"material_name\\\":\\\"Gula Pasir Kristal\\\",\\\"qty\\\":10,\\\"unit\\\":\\\"Kg\\\"}]\"}', '::1', '2026-04-21 11:41:44'),
(1497, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:41:46'),
(1498, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:41:48'),
(1499, 20, 'get_po_detail', 'Persetujuan', 'Eksekusi [get_po_detail] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:41:51'),
(1500, 20, 'update_po_status', 'Persetujuan', 'Eksekusi [update_po_status] di menu [Persetujuan]. Data: {\"po_id\":\"10\",\"detail_id\":[\"19\"],\"price\":[\"0.00\"],\"qty\":[\"9\"],\"action\":\"update_po_status\",\"status\":\"approved\"}', '::1', '2026-04-21 11:42:03'),
(1501, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:42:03'),
(1502, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:42:08'),
(1503, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:42:11'),
(1504, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:42:12'),
(1505, 20, 'mark_printed', 'Po', 'Eksekusi [mark_printed] di menu [Po]. Data: {\"action\":\"mark_printed\",\"id\":\"10\",\"tipe\":\"po\"}', '::1', '2026-04-21 11:42:13'),
(1506, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:42:14'),
(1507, 20, 'request_print', 'Po', 'Eksekusi [request_print] di menu [Po]. Data: {\"action\":\"request_print\",\"id\":\"10\",\"tipe\":\"po\"}', '::1', '2026-04-21 11:42:18'),
(1508, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:42:19'),
(1509, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:42:21'),
(1510, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:42:22'),
(1511, 20, 'proses_izin_cetak', 'Persetujuan', 'Eksekusi [proses_izin_cetak] di menu [Persetujuan]. Data: {\"action\":\"proses_izin_cetak\",\"id\":\"10\",\"tipe\":\"po\",\"keputusan\":\"approve\"}', '::1', '2026-04-21 11:42:23'),
(1512, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 11:42:23'),
(1513, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 11:42:32'),
(1514, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-21 12:20:23'),
(1515, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '::1', '2026-04-21 12:20:37'),
(1516, 20, 'save', 'Permintaan', 'Eksekusi [save] di menu [Permintaan]. Data: {\"action\":\"save\",\"cart\":\"[{\\\"material_id\\\":\\\"2\\\",\\\"name\\\":\\\"Gula Pasir Kristal\\\",\\\"qty\\\":10,\\\"unit\\\":\\\"Kg\\\",\\\"notes\\\":\\\"\\\"}]\"}', '::1', '2026-04-21 12:20:45'),
(1517, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-21 12:20:50'),
(1518, 20, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '::1', '2026-04-21 12:20:52'),
(1519, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-21 12:21:07'),
(1520, 20, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '::1', '2026-04-21 13:55:52'),
(1521, 20, 'get_dashboard_stats', 'Dashboard', 'Eksekusi [get_dashboard_stats] di menu [Dashboard]. Data: []', '::1', '2026-04-22 03:27:57'),
(1522, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-22 03:28:48'),
(1523, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-22 03:50:16'),
(1524, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 03:52:48'),
(1525, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 03:52:54'),
(1526, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 03:52:56'),
(1527, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 03:52:58'),
(1528, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 03:55:42'),
(1529, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 03:55:46'),
(1530, 20, 'read_histori', 'Persetujuan', 'Eksekusi [read_histori] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 03:55:47'),
(1531, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 03:55:48'),
(1532, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 03:55:51'),
(1533, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 03:55:52'),
(1534, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 03:55:53'),
(1535, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 03:55:54'),
(1536, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 03:55:54'),
(1537, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 03:55:55'),
(1538, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 03:55:56'),
(1539, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 03:55:57'),
(1540, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-22 03:56:03'),
(1541, 20, 'save', 'Barang Masuk', 'Eksekusi [save] di menu [Barang Masuk]. Data: {\"material_id\":\"3\",\"qty\":\"100\",\"expiry_date\":\"2026-04-30\",\"supplier_id\":\"1\",\"notes\":\"\"}', '::1', '2026-04-22 03:56:16'),
(1542, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 03:56:19'),
(1543, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 03:56:20'),
(1544, 20, 'approve_manual', 'Persetujuan', 'Eksekusi [approve_manual] di menu [Persetujuan]. Data: {\"action\":\"approve_manual\",\"id\":\"14\"}', '::1', '2026-04-22 03:56:23'),
(1545, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 03:56:23'),
(1546, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 03:56:40'),
(1547, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 03:56:50'),
(1548, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 03:57:08'),
(1549, 20, 'get_dashboard_stats', 'Dashboard', 'Eksekusi [get_dashboard_stats] di menu [Dashboard]. Data: []', '::1', '2026-04-22 03:57:49'),
(1550, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 03:57:53'),
(1551, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 03:58:09'),
(1552, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 03:59:05'),
(1553, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:04:06'),
(1554, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:04:16'),
(1555, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:04:27'),
(1556, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:04:32'),
(1557, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:13:22'),
(1558, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:13:24'),
(1559, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:13:24'),
(1560, 20, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:13:25'),
(1561, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:13:51'),
(1562, 20, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:13:52'),
(1563, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:13:53'),
(1564, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:13:54'),
(1565, 20, 'read_histori', 'Persetujuan', 'Eksekusi [read_histori] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:13:54'),
(1566, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:13:57'),
(1567, 20, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:13:57'),
(1568, 20, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '::1', '2026-04-22 04:14:01'),
(1569, 20, 'save', 'Barang Keluar', 'Eksekusi [save] di menu [Barang Keluar]. Data: {\"material_id\":\"3\",\"qty\":\"10\",\"status\":\"Expired\",\"notes\":\"\"}', '::1', '2026-04-22 04:14:08'),
(1570, 20, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '::1', '2026-04-22 04:14:09'),
(1571, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:14:12'),
(1572, 20, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:14:13'),
(1573, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-22 04:14:18'),
(1574, 20, 'save', 'Barang Masuk', 'Eksekusi [save] di menu [Barang Masuk]. Data: {\"material_id\":\"3\",\"qty\":\"100\",\"expiry_date\":\"2026-04-22\",\"supplier_id\":\"1\",\"notes\":\"\"}', '::1', '2026-04-22 04:14:31'),
(1575, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:14:36'),
(1576, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:14:38'),
(1577, 20, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:14:39'),
(1578, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:14:40'),
(1579, 20, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:14:43'),
(1580, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:14:43'),
(1581, 20, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:14:44'),
(1582, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:14:44'),
(1583, 20, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:14:45'),
(1584, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:14:46'),
(1585, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:14:46'),
(1586, 20, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:14:47'),
(1587, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:14:48'),
(1588, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-22 04:14:55'),
(1589, 20, 'mark_printed', 'Po', 'Eksekusi [mark_printed] di menu [Po]. Data: {\"action\":\"mark_printed\",\"id\":\"10\",\"tipe\":\"po\"}', '::1', '2026-04-22 04:15:00'),
(1590, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-22 04:15:00'),
(1591, 20, 'request_print', 'Po', 'Eksekusi [request_print] di menu [Po]. Data: {\"action\":\"request_print\",\"id\":\"10\",\"tipe\":\"po\"}', '::1', '2026-04-22 04:15:05'),
(1592, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-22 04:15:05'),
(1593, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:15:08'),
(1594, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:15:09'),
(1595, 20, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:15:10'),
(1596, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:15:11'),
(1597, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:15:11'),
(1598, 20, 'proses_ke_po', 'Persetujuan', 'Eksekusi [proses_ke_po] di menu [Persetujuan]. Data: {\"action\":\"proses_ke_po\",\"id\":\"14\"}', '::1', '2026-04-22 04:15:14'),
(1599, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:15:14'),
(1600, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:15:16'),
(1601, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-22 04:15:21'),
(1602, 20, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '::1', '2026-04-22 04:15:23'),
(1603, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:15:26'),
(1604, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:17:38'),
(1605, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-22 04:29:42'),
(1606, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:31:42'),
(1607, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:31:43'),
(1608, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:31:44'),
(1609, 20, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:31:44'),
(1610, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:31:45'),
(1611, 20, 'read_histori', 'Persetujuan', 'Eksekusi [read_histori] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:31:45'),
(1612, 20, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:31:47'),
(1613, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:31:47'),
(1614, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:31:48'),
(1615, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:31:48'),
(1616, 20, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:31:49');
INSERT INTO `system_logs` (`id`, `user_id`, `action`, `menu`, `description`, `ip_address`, `created_at`) VALUES
(1617, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:31:49'),
(1618, 20, 'init', 'Supplier-terakhir', 'Eksekusi [init] di menu [Supplier-terakhir]. Data: []', '::1', '2026-04-22 04:31:54'),
(1619, 20, 'save', 'Manajemen-role', 'Eksekusi [save] di menu [Manajemen-role]. Data: {\"role_id\":\"3\",\"role_name\":\"admin gudang 2\",\"role_slug\":\"admin_gudang_2\",\"permissions\":[\"dashboard\",\"persetujuan\",\"persetujuan_po\",\"master_inventory\",\"master_kategori\"],\"action\":\"save\"}', '::1', '2026-04-22 04:32:16'),
(1620, NULL, 'get_dashboard_stats', 'Dashboard', 'Eksekusi [get_dashboard_stats] di menu [Dashboard]. Data: []', '::1', '2026-04-22 04:32:31'),
(1621, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:32:33'),
(1622, 20, 'save', 'Manajemen-role', 'Eksekusi [save] di menu [Manajemen-role]. Data: {\"role_id\":\"3\",\"role_name\":\"admin gudang 2\",\"role_slug\":\"admin_gudang_2\",\"permissions\":[\"dashboard\",\"persetujuan\",\"persetujuan_po\",\"persetujuan_pr\",\"master_inventory\",\"master_kategori\"],\"action\":\"save\"}', '::1', '2026-04-22 04:32:50'),
(1623, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:32:52'),
(1624, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:32:53'),
(1625, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:32:54'),
(1626, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:32:54'),
(1627, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:32:54'),
(1628, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:32:54'),
(1629, 20, 'save', 'Manajemen-role', 'Eksekusi [save] di menu [Manajemen-role]. Data: {\"role_id\":\"3\",\"role_name\":\"admin gudang 2\",\"role_slug\":\"admin_gudang_2\",\"permissions\":[\"dashboard\",\"persetujuan\",\"persetujuan_po\",\"persetujuan_pr\",\"persetujuan_masuk_manual\",\"master_inventory\",\"master_kategori\"],\"action\":\"save\"}', '::1', '2026-04-22 04:33:01'),
(1630, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:33:03'),
(1631, 20, 'save', 'Manajemen-role', 'Eksekusi [save] di menu [Manajemen-role]. Data: {\"role_id\":\"3\",\"role_name\":\"admin gudang 2\",\"role_slug\":\"admin_gudang_2\",\"permissions\":[\"dashboard\",\"persetujuan\",\"persetujuan_po\",\"persetujuan_pr\",\"persetujuan_masuk_manual\",\"master_inventory\",\"master_kategori\",\"manage_roles\"],\"action\":\"save\"}', '::1', '2026-04-22 04:33:21'),
(1632, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 04:33:24'),
(1633, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-22 05:43:21'),
(1634, 20, 'save', 'Profil', 'Eksekusi [save] di menu [Profil]. Data: {\"req_approval_out\":\"1\",\"req_approval_pr\":\"1\",\"req_approval_po\":\"1\",\"req_approval_print\":\"1\",\"store_name\":\"ROTIKU ERP\",\"address\":\"Jl. Gudang Utama No. 123, Medan, Sumatera Utara\",\"phone\":\"(061) 1234567\",\"email\":\"logistik@rotiku.com\",\"req_approval_in\":\"0\",\"action\":\"save\"}', '::1', '2026-04-22 06:05:41'),
(1635, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-22 06:05:56'),
(1636, 20, 'save', 'Barang Masuk', 'Eksekusi [save] di menu [Barang Masuk]. Data: {\"material_id\":\"3\",\"qty\":\"100\",\"expiry_date\":\"2026-04-25\",\"supplier_id\":\"1\",\"notes\":\"\"}', '::1', '2026-04-22 06:06:07'),
(1637, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-22 06:07:19'),
(1638, 20, 'save', 'Profil', 'Eksekusi [save] di menu [Profil]. Data: {\"req_approval_in\":\"1\",\"req_approval_out\":\"1\",\"req_approval_pr\":\"1\",\"req_approval_po\":\"1\",\"req_approval_print\":\"1\",\"store_name\":\"ROTIKU ERP\",\"address\":\"Jl. Gudang Utama No. 123, Medan, Sumatera Utara\",\"phone\":\"(061) 1234567\",\"email\":\"logistik@rotiku.com\",\"action\":\"save\"}', '::1', '2026-04-22 06:07:27'),
(1639, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-22 06:07:32'),
(1640, 20, 'save', 'Barang Masuk', 'Eksekusi [save] di menu [Barang Masuk]. Data: {\"material_id\":\"3\",\"qty\":\"20\",\"expiry_date\":\"2026-04-25\",\"supplier_id\":\"1\",\"notes\":\"\"}', '::1', '2026-04-22 06:07:40'),
(1641, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 06:07:50'),
(1642, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 06:07:52'),
(1643, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-22 06:08:08'),
(1644, 20, 'save', 'Profil', 'Eksekusi [save] di menu [Profil]. Data: {\"req_approval_in\":\"1\",\"req_approval_pr\":\"1\",\"req_approval_po\":\"1\",\"req_approval_print\":\"1\",\"store_name\":\"ROTIKU ERP\",\"address\":\"Jl. Gudang Utama No. 123, Medan, Sumatera Utara\",\"phone\":\"(061) 1234567\",\"email\":\"logistik@rotiku.com\",\"req_approval_out\":\"0\",\"action\":\"save\"}', '::1', '2026-04-22 06:08:14'),
(1645, 20, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '::1', '2026-04-22 06:08:18'),
(1646, 20, 'save', 'Barang Keluar', 'Eksekusi [save] di menu [Barang Keluar]. Data: {\"material_id\":\"3\",\"qty\":\"10\",\"status\":\"Expired\",\"notes\":\"\"}', '::1', '2026-04-22 06:08:25'),
(1647, 20, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '::1', '2026-04-22 06:08:25'),
(1648, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-22 06:08:37'),
(1649, 20, 'save', 'Profil', 'Eksekusi [save] di menu [Profil]. Data: {\"req_approval_in\":\"1\",\"req_approval_out\":\"1\",\"req_approval_pr\":\"1\",\"req_approval_po\":\"1\",\"req_approval_print\":\"1\",\"store_name\":\"ROTIKU ERP\",\"address\":\"Jl. Gudang Utama No. 123, Medan, Sumatera Utara\",\"phone\":\"(061) 1234567\",\"email\":\"logistik@rotiku.com\",\"action\":\"save\"}', '::1', '2026-04-22 06:08:43'),
(1650, 20, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '::1', '2026-04-22 06:08:55'),
(1651, 20, 'save', 'Barang Keluar', 'Eksekusi [save] di menu [Barang Keluar]. Data: {\"material_id\":\"3\",\"qty\":\"10\",\"status\":\"Rusak\",\"notes\":\"\"}', '::1', '2026-04-22 06:09:00'),
(1652, 20, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '::1', '2026-04-22 06:09:01'),
(1653, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 06:09:09'),
(1654, 20, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 06:09:10'),
(1655, 20, 'save', 'Profil', 'Eksekusi [save] di menu [Profil]. Data: {\"req_approval_in\":\"1\",\"req_approval_out\":\"1\",\"req_approval_pr\":\"1\",\"req_approval_po\":\"1\",\"store_name\":\"ROTIKU ERP\",\"address\":\"Jl. Gudang Utama No. 123, Medan, Sumatera Utara\",\"phone\":\"(061) 1234567\",\"email\":\"logistik@rotiku.com\",\"req_approval_print\":\"0\",\"action\":\"save\"}', '::1', '2026-04-22 06:20:01'),
(1656, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 06:20:06'),
(1657, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-22 06:20:18'),
(1658, 20, 'mark_printed', 'Po', 'Eksekusi [mark_printed] di menu [Po]. Data: {\"action\":\"mark_printed\",\"id\":\"9\",\"tipe\":\"po\"}', '::1', '2026-04-22 06:20:25'),
(1659, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-22 06:20:25'),
(1660, 20, 'mark_printed', 'Po', 'Eksekusi [mark_printed] di menu [Po]. Data: {\"action\":\"mark_printed\",\"id\":\"8\",\"tipe\":\"po\"}', '::1', '2026-04-22 06:20:38'),
(1661, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-22 06:20:38'),
(1662, 20, 'request_print', 'Po', 'Eksekusi [request_print] di menu [Po]. Data: {\"action\":\"request_print\",\"id\":\"9\",\"tipe\":\"po\"}', '::1', '2026-04-22 06:20:46'),
(1663, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-22 06:20:46'),
(1664, 20, 'save', 'Profil', 'Eksekusi [save] di menu [Profil]. Data: {\"req_approval_in\":\"1\",\"req_approval_out\":\"1\",\"req_approval_pr\":\"1\",\"req_approval_po\":\"1\",\"req_approval_print\":\"1\",\"store_name\":\"ROTIKU ERP\",\"address\":\"Jl. Gudang Utama No. 123, Medan, Sumatera Utara\",\"phone\":\"(061) 1234567\",\"email\":\"logistik@rotiku.com\",\"action\":\"save\"}', '::1', '2026-04-22 06:20:56'),
(1665, 20, 'save', 'Profil', 'Eksekusi [save] di menu [Profil]. Data: {\"req_approval_in\":\"1\",\"req_approval_out\":\"1\",\"req_approval_pr\":\"1\",\"req_approval_po\":\"1\",\"req_approval_print\":\"1\",\"store_name\":\"ROTIKU ERP\",\"address\":\"Jl. Gudang Utama No. 123, Medan, Sumatera Utara\",\"phone\":\"(061) 1234567\",\"email\":\"logistik@rotiku.com\",\"action\":\"save\"}', '::1', '2026-04-22 06:20:57'),
(1666, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 06:21:00'),
(1667, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 06:21:01'),
(1668, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-22 06:21:15'),
(1669, 20, 'request_print', 'Po', 'Eksekusi [request_print] di menu [Po]. Data: {\"action\":\"request_print\",\"id\":\"8\",\"tipe\":\"po\"}', '::1', '2026-04-22 06:21:18'),
(1670, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-22 06:21:18'),
(1671, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 06:21:23'),
(1672, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 06:21:24'),
(1673, 20, 'proses_izin_cetak', 'Persetujuan', 'Eksekusi [proses_izin_cetak] di menu [Persetujuan]. Data: {\"action\":\"proses_izin_cetak\",\"id\":\"8\",\"tipe\":\"po\",\"keputusan\":\"approve\"}', '::1', '2026-04-22 06:21:26'),
(1674, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 06:21:27'),
(1675, 20, 'proses_izin_cetak', 'Persetujuan', 'Eksekusi [proses_izin_cetak] di menu [Persetujuan]. Data: {\"action\":\"proses_izin_cetak\",\"id\":\"10\",\"tipe\":\"po\",\"keputusan\":\"approve\"}', '::1', '2026-04-22 06:21:29'),
(1676, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 06:21:29'),
(1677, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-22 06:21:50'),
(1678, 20, 'mark_printed', 'Po', 'Eksekusi [mark_printed] di menu [Po]. Data: {\"action\":\"mark_printed\",\"id\":\"10\",\"tipe\":\"po\"}', '::1', '2026-04-22 06:21:53'),
(1679, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-22 06:21:53'),
(1680, 20, 'request_print', 'Po', 'Eksekusi [request_print] di menu [Po]. Data: {\"action\":\"request_print\",\"id\":\"10\",\"tipe\":\"po\"}', '::1', '2026-04-22 06:23:42'),
(1681, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-22 06:23:42'),
(1682, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 06:23:46'),
(1683, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 06:23:53'),
(1684, 20, 'proses_izin_cetak', 'Persetujuan', 'Eksekusi [proses_izin_cetak] di menu [Persetujuan]. Data: {\"action\":\"proses_izin_cetak\",\"id\":\"10\",\"tipe\":\"po\",\"keputusan\":\"approve\"}', '::1', '2026-04-22 06:23:55'),
(1685, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 06:23:55'),
(1686, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-22 06:24:07'),
(1687, 20, 'save', 'Profil', 'Eksekusi [save] di menu [Profil]. Data: {\"req_approval_in\":\"1\",\"req_approval_out\":\"1\",\"req_approval_pr\":\"1\",\"req_approval_po\":\"1\",\"req_approval_print\":\"1\",\"store_name\":\"ROTIKU ERP\",\"address\":\"Jl. Gudang Utama No. 123, Medan, Sumatera Utara\",\"phone\":\"(061) 1234567\",\"email\":\"logistik@rotiku.com\",\"action\":\"save\"}', '::1', '2026-04-22 06:25:34'),
(1688, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-22 06:25:58'),
(1689, 20, 'mark_printed', 'Po', 'Eksekusi [mark_printed] di menu [Po]. Data: {\"action\":\"mark_printed\",\"id\":\"10\",\"tipe\":\"po\"}', '::1', '2026-04-22 06:26:00'),
(1690, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-22 06:26:01'),
(1691, 20, 'mark_printed', 'Po', 'Eksekusi [mark_printed] di menu [Po]. Data: {\"action\":\"mark_printed\",\"id\":\"9\",\"tipe\":\"terima\"}', '::1', '2026-04-22 06:26:22'),
(1692, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-22 06:26:22'),
(1693, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-22 06:30:47'),
(1694, 20, 'request_print', 'Po', 'Eksekusi [request_print] di menu [Po]. Data: {\"action\":\"request_print\",\"id\":\"9\",\"tipe\":\"terima\"}', '::1', '2026-04-22 06:30:50'),
(1695, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-22 06:30:50'),
(1696, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 06:30:53'),
(1697, 20, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 06:30:54'),
(1698, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 06:30:54'),
(1699, 20, 'proses_izin_cetak', 'Persetujuan', 'Eksekusi [proses_izin_cetak] di menu [Persetujuan]. Data: {\"action\":\"proses_izin_cetak\",\"id\":\"9\",\"tipe\":\"terima\",\"keputusan\":\"approve\"}', '::1', '2026-04-22 06:30:56'),
(1700, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 06:30:56'),
(1701, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-22 06:31:03'),
(1702, 20, 'mark_printed', 'Po', 'Eksekusi [mark_printed] di menu [Po]. Data: {\"action\":\"mark_printed\",\"id\":\"9\",\"tipe\":\"terima\"}', '::1', '2026-04-22 06:31:05'),
(1703, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-22 06:31:06'),
(1704, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-22 06:31:22'),
(1705, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-22 06:31:31'),
(1706, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-22 06:31:58'),
(1707, 20, 'get_dashboard_stats', 'Dashboard', 'Eksekusi [get_dashboard_stats] di menu [Dashboard]. Data: []', '::1', '2026-04-22 06:32:48'),
(1708, 20, 'get_dashboard_stats', 'Dashboard', 'Eksekusi [get_dashboard_stats] di menu [Dashboard]. Data: []', '::1', '2026-04-22 06:32:50'),
(1709, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '::1', '2026-04-22 06:41:34'),
(1710, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-22 06:45:52'),
(1711, 20, 'save', 'User-management', 'Eksekusi [save] di menu [User-management]. Data: {\"user_id\":\"1\",\"name\":\"Bapak Owner\",\"username\":\"owner-gudang\",\"password\":\"******\",\"role\":\"owner\",\"status\":\"active\",\"action\":\"save\"}', '::1', '2026-04-22 06:46:08'),
(1712, NULL, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '::1', '2026-04-22 06:46:37'),
(1713, NULL, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-22 06:46:37'),
(1714, NULL, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-22 06:46:37'),
(1715, NULL, 'delete_user', 'Master User', 'Eksekusi [delete_user] di menu [Master User]. Data: {\"id\":\"21\"}', '::1', '2026-04-22 06:46:50'),
(1716, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '::1', '2026-04-22 06:47:06'),
(1717, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-22 06:47:06'),
(1718, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-22 06:47:06'),
(1719, 1, 'delete_user', 'Master User', 'Eksekusi [delete_user] di menu [Master User]. Data: {\"id\":\"21\"}', '::1', '2026-04-22 06:47:09'),
(1720, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-22 06:47:09'),
(1721, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '::1', '2026-04-22 06:47:26'),
(1722, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-22 06:47:30'),
(1723, 20, 'save', 'User-management', 'Eksekusi [save] di menu [User-management]. Data: {\"user_id\":\"1\",\"name\":\"Bapak Owner\",\"username\":\"owner-gudang\",\"password\":\"******\",\"role\":\"owner\",\"status\":\"active\",\"action\":\"save\"}', '::1', '2026-04-22 06:47:39'),
(1724, 20, 'save', 'User-management', 'Eksekusi [save] di menu [User-management]. Data: {\"user_id\":\"1\",\"name\":\"Bapak Owner\",\"username\":\"owner-gudang\",\"password\":\"******\",\"role\":\"owner\",\"status\":\"active\",\"action\":\"save\"}', '::1', '2026-04-22 06:48:35'),
(1725, 20, 'save', 'Manajemen-role', 'Eksekusi [save] di menu [Manajemen-role]. Data: {\"role_id\":\"2\",\"role_name\":\"owner\",\"role_slug\":\"owner\",\"permissions\":[\"dashboard\",\"persetujuan\",\"persetujuan_po\",\"persetujuan_pr\",\"persetujuan_masuk_manual\",\"persetujuan_keluar_manual\",\"persetujuan_izin_cetak\",\"persetujuan_histori\",\"master_inventory\",\"master_kategori\",\"master_satuan\",\"master_lokasi\",\"monitoring_rak\",\"trx_barang_masuk\",\"trx_barang_keluar\",\"cetak_barcode\",\"data_opname\",\"otorisasi_opname\",\"scanner_opname\",\"trx_permintaan_dapur\",\"trx_permintaan_barang\",\"trx_po\",\"trx_pembayaran\",\"trx_supplier\",\"lap_barang_masuk\",\"lap_barang_keluar\",\"lap_po\",\"lap_pembayaran_po\",\"lap_stok_opname\",\"lap_kartu_stok\",\"lap_stok_menipis\",\"lap_stok_terbanyak\",\"lap_perbandingan_harga\",\"lap_supplier\",\"pengaturan_karyawan\",\"pengaturan_pembayaran\",\"manage_users\",\"manage_roles\",\"pengaturan_profil\"],\"action\":\"save\"}', '::1', '2026-04-22 06:49:10'),
(1726, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-22 06:50:09'),
(1727, NULL, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '::1', '2026-04-22 06:50:32'),
(1728, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-22 06:51:13'),
(1729, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '::1', '2026-04-22 06:51:16'),
(1730, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '::1', '2026-04-22 06:52:21'),
(1731, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-22 06:52:26'),
(1732, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '::1', '2026-04-22 06:53:36'),
(1733, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-22 06:53:36'),
(1734, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-22 06:53:36'),
(1735, 1, 'save_user', 'Master User', 'Eksekusi [save_user] di menu [Master User]. Data: {\"id\":\"1\",\"name\":\"Bapak Owner\",\"username\":\"owner-produksi\",\"password\":\"******\",\"role\":\"owner\",\"kitchen_id\":\"\"}', '::1', '2026-04-22 06:53:55'),
(1736, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-22 06:53:56'),
(1737, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '::1', '2026-04-22 06:58:35'),
(1738, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-22 06:59:23'),
(1739, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-22 07:09:59'),
(1740, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-22 07:10:15'),
(1741, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-22 07:10:26'),
(1742, 20, 'save', 'User-management', 'Eksekusi [save] di menu [User-management]. Data: {\"user_id\":\"1\",\"name\":\"Bapak Owner\",\"username\":\"owner-gudang\",\"password\":\"******\",\"role\":\"owner\",\"status\":\"active\",\"action\":\"save\"}', '::1', '2026-04-22 07:10:36'),
(1743, 20, 'save', 'User-management', 'Eksekusi [save] di menu [User-management]. Data: {\"user_id\":\"1\",\"name\":\"Bapak Owner\",\"username\":\"owner-produksi\",\"password\":\"******\",\"role\":\"owner\",\"status\":\"active\",\"action\":\"save\"}', '::1', '2026-04-22 07:10:56'),
(1744, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-22 07:18:03'),
(1745, 20, 'save', 'User-management', 'Eksekusi [save] di menu [User-management]. Data: {\"user_id\":\"20\",\"name\":\"Randy admin gudang\",\"username\":\"owner-gudang\",\"password\":\"******\",\"role\":\"owner_gudang\",\"status\":\"active\",\"action\":\"save\"}', '::1', '2026-04-22 07:18:32'),
(1746, 20, 'save', 'Manajemen-role', 'Eksekusi [save] di menu [Manajemen-role]. Data: {\"role_id\":\"2\",\"role_name\":\"Owner Gudang Pilar\",\"role_slug\":\"owner_gudang\",\"permissions\":[\"dashboard\",\"persetujuan\",\"persetujuan_po\",\"persetujuan_pr\",\"persetujuan_masuk_manual\",\"persetujuan_keluar_manual\",\"persetujuan_izin_cetak\",\"master_inventory\",\"master_kategori\",\"master_satuan\",\"master_lokasi\",\"trx_barang_masuk\",\"trx_barang_keluar\",\"cetak_barcode\",\"data_opname\",\"otorisasi_opname\",\"scanner_opname\",\"trx_permintaan_dapur\",\"trx_po\",\"trx_pembayaran\",\"trx_supplier\",\"lap_barang_masuk\",\"lap_barang_keluar\",\"lap_po\",\"lap_pembayaran_po\",\"lap_stok_opname\",\"lap_kartu_stok\",\"lap_stok_menipis\",\"lap_stok_terbanyak\",\"lap_perbandingan_harga\",\"lap_supplier\",\"pengaturan_karyawan\",\"pengaturan_pembayaran\",\"manage_users\",\"manage_roles\",\"pengaturan_profil\"],\"action\":\"save\"}', '::1', '2026-04-22 07:19:05'),
(1747, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '::1', '2026-04-22 07:19:24'),
(1748, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '::1', '2026-04-22 07:19:58'),
(1749, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '::1', '2026-04-22 07:20:00'),
(1750, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 07:20:24'),
(1751, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 07:20:25'),
(1752, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 07:20:26'),
(1753, 20, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 07:20:26'),
(1754, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 07:20:26'),
(1755, 20, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 07:20:27'),
(1756, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 07:20:28'),
(1757, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-22 07:20:30'),
(1758, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-22 07:20:32'),
(1759, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-22 07:20:35'),
(1760, 20, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '::1', '2026-04-22 07:20:36'),
(1761, 20, 'init_data', 'Cetak Barcode', 'Eksekusi [init_data] di menu [Cetak Barcode]. Data: []', '::1', '2026-04-22 07:20:37'),
(1762, 20, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '::1', '2026-04-22 07:20:43'),
(1763, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-22 07:20:47'),
(1764, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-22 07:20:49'),
(1765, 20, 'init', 'Pembayaran-po', 'Eksekusi [init] di menu [Pembayaran-po]. Data: []', '::1', '2026-04-22 07:20:57'),
(1766, 20, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '::1', '2026-04-22 07:21:02'),
(1767, 20, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '::1', '2026-04-22 07:21:05'),
(1768, 20, 'init', 'Supplier-terakhir', 'Eksekusi [init] di menu [Supplier-terakhir]. Data: []', '::1', '2026-04-22 07:21:14'),
(1769, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-22 07:21:21'),
(1770, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-22 07:21:24'),
(1771, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '::1', '2026-04-22 07:22:45'),
(1772, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-22 07:22:56'),
(1773, 20, 'save', 'Manajemen-role', 'Eksekusi [save] di menu [Manajemen-role]. Data: {\"role_id\":\"3\",\"role_name\":\"Admin Gudang Utama\",\"role_slug\":\"admin_gudang\",\"permissions\":[\"dashboard\"],\"action\":\"save\"}', '::1', '2026-04-22 07:23:11'),
(1774, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-22 07:23:13'),
(1775, 20, 'save', 'User-management', 'Eksekusi [save] di menu [User-management]. Data: {\"user_id\":\"\",\"name\":\"admin-gudang\",\"username\":\"admin-gudang\",\"password\":\"******\",\"role\":\"admin_gudang\",\"status\":\"active\",\"action\":\"save\"}', '::1', '2026-04-22 07:23:36'),
(1776, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-22 07:23:43'),
(1777, 23, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '::1', '2026-04-22 07:23:54'),
(1778, 23, 'save', 'Manajemen-role', 'Eksekusi [save] di menu [Manajemen-role]. Data: {\"role_id\":\"\",\"role_name\":\"admin-gudang2\",\"role_slug\":\"admin_gudang2\",\"permissions\":[\"dashboard\",\"persetujuan\"],\"action\":\"save\"}', '::1', '2026-04-22 07:24:28'),
(1779, 23, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-22 07:24:30'),
(1780, 23, 'save', 'User-management', 'Eksekusi [save] di menu [User-management]. Data: {\"user_id\":\"\",\"name\":\"admin-gudang2\",\"username\":\"admin-gudang2\",\"password\":\"******\",\"role\":\"admin_gudang2\",\"status\":\"active\",\"action\":\"save\"}', '::1', '2026-04-22 07:24:45'),
(1781, 23, 'save', 'User-management', 'Eksekusi [save] di menu [User-management]. Data: {\"user_id\":\"\",\"name\":\"admin-gudang3\",\"username\":\"admin-gudang2\",\"password\":\"******\",\"role\":\"admin_gudang2\",\"status\":\"active\",\"action\":\"save\"}', '::1', '2026-04-22 07:24:52'),
(1782, 23, 'save', 'User-management', 'Eksekusi [save] di menu [User-management]. Data: {\"user_id\":\"\",\"name\":\"Rendy\",\"username\":\"admin-gudang3\",\"password\":\"******\",\"role\":\"admin_gudang2\",\"status\":\"active\",\"action\":\"save\"}', '::1', '2026-04-22 07:25:02'),
(1783, NULL, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '::1', '2026-04-22 07:25:18'),
(1784, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '::1', '2026-04-22 07:32:11'),
(1785, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '::1', '2026-04-22 07:32:34'),
(1786, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '::1', '2026-04-22 07:50:30'),
(1787, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-22 07:50:31'),
(1788, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-22 07:50:31'),
(1789, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '::1', '2026-04-22 07:50:46'),
(1790, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-22 07:50:53'),
(1791, NULL, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '::1', '2026-04-22 07:52:25'),
(1792, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-22 12:15:44'),
(1793, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"edit\",\"old_slug\":\"auditor\",\"role_name\":\"Auditor\",\"role_slug\":\"auditor\",\"permissions\":[\"view_dashboard\",\"audit_logs\",\"analisa_produk\",\"laporan_bahan\",\"laporan_produk_jadi\",\"laporan_bom\",\"laporan_opname\"]}', '::1', '2026-04-22 12:18:46'),
(1794, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-22 12:19:40'),
(1795, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-22 12:20:22'),
(1796, 18, 'save', 'Input Produksi', 'Eksekusi [save] di menu [Input Produksi]. Data: {\"employee_id\":\"1\",\"product_id\":[\"3\"],\"quantity\":[\"2\"],\"warehouse_id\":\"1\",\"notes\":\"\",\"pin\":\"1234\"}', '::1', '2026-04-22 12:20:37'),
(1797, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-22 12:20:57'),
(1798, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"edit\",\"old_slug\":\"admin_dapur_1\",\"role_name\":\"admin dapur 1\",\"role_slug\":\"admin_dapur_1\",\"permissions\":[\"akses_dapur_1\",\"manajemen_dapur\",\"edit_manajemen_dapur\",\"hapus_manajemen_dapur\",\"master_bahan\",\"edit_master_bahan\",\"hapus_master_bahan\",\"master_resep\",\"view_dashboard\"]}', '::1', '2026-04-22 12:21:32'),
(1799, 16, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-22 12:21:45'),
(1800, 16, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '::1', '2026-04-22 12:21:45'),
(1801, 16, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-22 12:21:48'),
(1802, 16, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-22 12:21:48'),
(1803, 16, 'save_bom', 'Master Resep', 'Eksekusi [save_bom] di menu [Master Resep]. Data: {\"product_id\":\"2\",\"material_id\":\"3\",\"quantity_needed\":\"100\",\"unit_used\":\"Gram\"}', '::1', '2026-04-22 12:21:57'),
(1804, 16, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-22 12:21:57'),
(1805, 16, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-22 12:21:57'),
(1806, 16, 'save_bom', 'Master Resep', 'Eksekusi [save_bom] di menu [Master Resep]. Data: {\"product_id\":\"2\",\"material_id\":\"5\",\"quantity_needed\":\"100\",\"unit_used\":\"Gram\"}', '::1', '2026-04-22 12:22:03'),
(1807, 16, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-22 12:22:03'),
(1808, 16, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-22 12:22:03'),
(1809, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-22 12:22:10'),
(1810, 16, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-22 12:22:12'),
(1811, 16, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '::1', '2026-04-22 12:22:12'),
(1812, 16, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '::1', '2026-04-22 12:22:13'),
(1813, 16, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-22 12:22:14'),
(1814, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-22 12:22:21'),
(1815, 18, 'save', 'Input Produksi', 'Eksekusi [save] di menu [Input Produksi]. Data: {\"employee_id\":\"1\",\"product_id\":[\"2\"],\"quantity\":[\"10\"],\"warehouse_id\":\"1\",\"notes\":\"\",\"pin\":\"1234\"}', '::1', '2026-04-22 12:22:40'),
(1816, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-22 12:22:50'),
(1817, 18, 'save', 'Input Produksi', 'Eksekusi [save] di menu [Input Produksi]. Data: {\"employee_id\":\"1\",\"product_id\":[\"2\"],\"quantity\":[\"2\"],\"warehouse_id\":\"1\",\"notes\":\"\",\"pin\":\"1234\"}', '::1', '2026-04-22 12:23:18'),
(1818, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-22 12:23:26'),
(1819, 18, 'cancel_produksi', 'Riwayat Produksi', 'Eksekusi [cancel_produksi] di menu [Riwayat Produksi]. Data: {\"prod_id\":\"91\",\"pin\":\"123456\"}', '::1', '2026-04-22 12:23:49'),
(1820, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-22 12:23:57'),
(1821, 18, 'get_employees', 'Produk Keluar', 'Eksekusi [get_employees] di menu [Produk Keluar]. Data: []', '::1', '2026-04-22 12:24:02'),
(1822, 3, 'scan', 'Scan Barcode', 'Eksekusi [scan] di menu [Scan Barcode]. Data: {\"barcode\":\"D2226-002\"}', '::1', '2026-04-22 12:24:25'),
(1823, 3, 'execute_validasi', 'Scan Barcode', 'Eksekusi [execute_validasi] di menu [Scan Barcode]. Data: {\"prod_id\":\"90\",\"status\":\"ditolak\"}', '::1', '2026-04-22 12:24:31'),
(1824, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-22 12:24:42'),
(1825, 18, 'update_revisi', 'Riwayat Produksi', 'Eksekusi [update_revisi] di menu [Riwayat Produksi]. Data: {\"prod_id\":\"90\",\"detail_id\":[\"115\"],\"quantity\":[\"5\"]}', '::1', '2026-04-22 12:25:02'),
(1826, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-22 12:25:08'),
(1827, 3, 'scan', 'Scan Barcode', 'Eksekusi [scan] di menu [Scan Barcode]. Data: {\"barcode\":\"D2226-002\"}', '::1', '2026-04-22 12:25:33'),
(1828, 3, 'execute_validasi', 'Scan Barcode', 'Eksekusi [execute_validasi] di menu [Scan Barcode]. Data: {\"prod_id\":\"90\",\"status\":\"ditolak\"}', '::1', '2026-04-22 12:25:34'),
(1829, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '::1', '2026-04-22 12:25:45'),
(1830, 18, 'update_revisi', 'Riwayat Produksi', 'Eksekusi [update_revisi] di menu [Riwayat Produksi]. Data: {\"prod_id\":\"90\",\"detail_id\":[\"115\"],\"quantity\":[\"1\"]}', '::1', '2026-04-22 12:25:53'),
(1831, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-22 12:25:57'),
(1832, 3, 'scan', 'Scan Barcode', 'Eksekusi [scan] di menu [Scan Barcode]. Data: {\"barcode\":\"D2226-002\"}', '::1', '2026-04-22 12:26:19'),
(1833, 3, 'execute_validasi', 'Scan Barcode', 'Eksekusi [execute_validasi] di menu [Scan Barcode]. Data: {\"prod_id\":\"90\",\"status\":\"masuk_gudang\"}', '::1', '2026-04-22 12:26:19'),
(1834, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"edit\",\"old_slug\":\"otorisasi\",\"role_name\":\"Otorisasi\",\"role_slug\":\"otorisasi\",\"permissions\":[\"view_dashboard\"]}', '::1', '2026-04-22 12:28:04'),
(1835, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '::1', '2026-04-22 12:28:18'),
(1836, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-22 12:28:18'),
(1837, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-22 12:28:18'),
(1838, 1, 'save_user', 'Master User', 'Eksekusi [save_user] di menu [Master User]. Data: {\"id\":\"\",\"name\":\"testing\",\"username\":\"testing\",\"password\":\"******\",\"role\":\"otorisasi\",\"kitchen_id\":\"\"}', '::1', '2026-04-22 12:28:35'),
(1839, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-22 12:28:35'),
(1840, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '::1', '2026-04-22 12:28:50'),
(1841, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-22 12:28:50'),
(1842, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-22 12:28:50'),
(1843, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"edit\",\"old_slug\":\"otorisasi\",\"role_name\":\"Otorisasi\",\"role_slug\":\"otorisasi\",\"permissions\":[\"manajemen_dapur\",\"view_dashboard\"]}', '::1', '2026-04-22 12:29:10'),
(1844, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"edit\",\"old_slug\":\"otorisasi\",\"role_name\":\"Otorisasi\",\"role_slug\":\"otorisasi\",\"permissions\":[\"manajemen_dapur\",\"master_gudang\",\"view_dashboard\"]}', '::1', '2026-04-22 12:29:26'),
(1845, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"edit\",\"old_slug\":\"otorisasi\",\"role_name\":\"Otorisasi\",\"role_slug\":\"otorisasi\",\"permissions\":[\"manajemen_dapur\",\"master_gudang\",\"edit_master_gudang\",\"hapus_master_gudang\",\"view_dashboard\"]}', '::1', '2026-04-22 12:29:36'),
(1846, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"edit\",\"old_slug\":\"otorisasi\",\"role_name\":\"Otorisasi\",\"role_slug\":\"otorisasi\",\"permissions\":[\"manajemen_dapur\",\"master_gudang\",\"edit_master_gudang\",\"hapus_master_gudang\",\"master_produk\",\"edit_master_produk\",\"view_dashboard\"]}', '::1', '2026-04-22 12:29:43'),
(1847, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"edit\",\"old_slug\":\"otorisasi\",\"role_name\":\"Otorisasi\",\"role_slug\":\"otorisasi\",\"permissions\":[\"manajemen_dapur\",\"master_gudang\",\"edit_master_gudang\",\"hapus_master_gudang\",\"master_produk\",\"edit_master_produk\",\"hapus_master_produk\",\"view_dashboard\"]}', '::1', '2026-04-22 12:29:54'),
(1848, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"edit\",\"old_slug\":\"otorisasi\",\"role_name\":\"Otorisasi\",\"role_slug\":\"otorisasi\",\"permissions\":[\"manajemen_dapur\",\"master_gudang\",\"edit_master_gudang\",\"hapus_master_gudang\",\"master_produk\",\"edit_master_produk\",\"hapus_master_produk\",\"master_kategori\",\"edit_master_kategori\",\"view_dashboard\"]}', '::1', '2026-04-22 12:30:05'),
(1849, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"edit\",\"old_slug\":\"otorisasi\",\"role_name\":\"Otorisasi\",\"role_slug\":\"otorisasi\",\"permissions\":[\"manajemen_dapur\",\"master_gudang\",\"edit_master_gudang\",\"hapus_master_gudang\",\"master_produk\",\"edit_master_produk\",\"hapus_master_produk\",\"master_bahan\",\"edit_master_bahan\",\"hapus_master_bahan\",\"view_dashboard\"]}', '::1', '2026-04-22 12:30:17'),
(1850, NULL, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-22 12:30:24'),
(1851, NULL, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-22 12:30:29'),
(1852, NULL, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-22 12:30:30'),
(1853, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"edit\",\"old_slug\":\"otorisasi\",\"role_name\":\"Otorisasi\",\"role_slug\":\"otorisasi\",\"permissions\":[\"manajemen_dapur\",\"master_gudang\",\"edit_master_gudang\",\"hapus_master_gudang\",\"master_produk\",\"edit_master_produk\",\"hapus_master_produk\",\"master_bahan\",\"master_satuan\",\"edit_master_satuan\",\"view_dashboard\"]}', '::1', '2026-04-22 12:30:41'),
(1854, NULL, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '::1', '2026-04-22 12:30:43'),
(1855, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"edit\",\"old_slug\":\"otorisasi\",\"role_name\":\"Otorisasi\",\"role_slug\":\"otorisasi\",\"permissions\":[\"manajemen_dapur\",\"master_gudang\",\"edit_master_gudang\",\"hapus_master_gudang\",\"master_produk\",\"edit_master_produk\",\"hapus_master_produk\",\"master_bahan\",\"master_satuan\",\"edit_master_satuan\",\"hapus_master_satuan\",\"master_resep\",\"view_dashboard\"]}', '::1', '2026-04-22 12:30:54'),
(1856, NULL, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-22 12:30:57'),
(1857, NULL, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '::1', '2026-04-22 12:30:57'),
(1858, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"edit\",\"old_slug\":\"otorisasi\",\"role_name\":\"Otorisasi\",\"role_slug\":\"otorisasi\",\"permissions\":[\"master_resep\",\"master_user\",\"view_dashboard\"]}', '::1', '2026-04-22 12:31:09'),
(1859, NULL, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '::1', '2026-04-22 12:31:12'),
(1860, NULL, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '::1', '2026-04-22 12:31:12'),
(1861, NULL, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '::1', '2026-04-22 12:31:14'),
(1862, NULL, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-22 12:31:14'),
(1863, NULL, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-22 12:31:14'),
(1864, NULL, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '::1', '2026-04-22 12:31:18'),
(1865, NULL, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-22 12:31:18'),
(1866, NULL, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-22 12:31:18'),
(1867, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"edit\",\"old_slug\":\"otorisasi\",\"role_name\":\"Otorisasi\",\"role_slug\":\"otorisasi\",\"permissions\":[\"master_resep\",\"master_user\",\"master_stok_pusat\",\"edit_master_stok_pusat\",\"view_dashboard\"]}', '::1', '2026-04-22 12:31:30'),
(1868, NULL, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '::1', '2026-04-22 12:31:32'),
(1869, NULL, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-22 12:31:32'),
(1870, NULL, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-22 12:31:32'),
(1871, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"edit\",\"old_slug\":\"otorisasi\",\"role_name\":\"Otorisasi\",\"role_slug\":\"otorisasi\",\"permissions\":[\"master_resep\",\"master_user\",\"master_stok_pusat\",\"edit_master_stok_pusat\",\"view_dashboard\",\"stok_opname\"]}', '::1', '2026-04-22 12:31:49'),
(1872, NULL, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '::1', '2026-04-22 12:31:51'),
(1873, NULL, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '::1', '2026-04-22 12:31:51'),
(1874, NULL, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '::1', '2026-04-22 12:31:51'),
(1875, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"edit\",\"old_slug\":\"otorisasi\",\"role_name\":\"Otorisasi\",\"role_slug\":\"otorisasi\",\"permissions\":[\"view_dashboard\",\"stok_opname\",\"otorisasi\",\"laporan_produksi\",\"laporan_keluar\"]}', '::1', '2026-04-22 12:32:03'),
(1876, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"edit\",\"old_slug\":\"otorisasi\",\"role_name\":\"Otorisasi\",\"role_slug\":\"otorisasi\",\"permissions\":[\"view_dashboard\",\"stok_opname\",\"otorisasi\",\"laporan_produksi\",\"laporan_keluar\",\"audit_logs\"]}', '::1', '2026-04-22 12:32:21'),
(1877, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"edit\",\"old_slug\":\"otorisasi\",\"role_name\":\"Otorisasi\",\"role_slug\":\"otorisasi\",\"permissions\":[\"view_dashboard\",\"stok_opname\",\"otorisasi\",\"laporan_produksi\",\"laporan_keluar\",\"analisa_produk\"]}', '::1', '2026-04-22 12:32:29'),
(1878, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"edit\",\"old_slug\":\"otorisasi\",\"role_name\":\"Otorisasi\",\"role_slug\":\"otorisasi\",\"permissions\":[\"view_dashboard\",\"stok_opname\",\"otorisasi\",\"analisa_produk\",\"laporan_bahan\"]}', '::1', '2026-04-22 12:32:43'),
(1879, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"edit\",\"old_slug\":\"otorisasi\",\"role_name\":\"Otorisasi\",\"role_slug\":\"otorisasi\",\"permissions\":[\"view_dashboard\",\"stok_opname\",\"otorisasi\",\"laporan_bahan\",\"laporan_produk_jadi\"]}', '::1', '2026-04-22 12:32:52'),
(1880, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"edit\",\"old_slug\":\"otorisasi\",\"role_name\":\"Otorisasi\",\"role_slug\":\"otorisasi\",\"permissions\":[\"view_dashboard\",\"stok_opname\",\"otorisasi\",\"laporan_produk_jadi\",\"laporan_bom\",\"laporan_opname\"]}', '::1', '2026-04-22 12:33:05'),
(1881, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '::1', '2026-04-22 12:33:45'),
(1882, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-22 12:35:31'),
(1883, NULL, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '::1', '2026-04-22 12:35:48'),
(1884, 20, 'save', 'Manajemen-role', 'Eksekusi [save] di menu [Manajemen-role]. Data: {\"role_id\":\"4\",\"role_name\":\"admin-gudang2\",\"role_slug\":\"admin_gudang2\",\"permissions\":[\"dashboard\",\"persetujuan\",\"persetujuan_po\"],\"action\":\"save\"}', '::1', '2026-04-22 12:36:06'),
(1885, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 12:36:08'),
(1886, 20, 'save', 'Manajemen-role', 'Eksekusi [save] di menu [Manajemen-role]. Data: {\"role_id\":\"4\",\"role_name\":\"admin-gudang2\",\"role_slug\":\"admin_gudang2\",\"permissions\":[\"dashboard\",\"persetujuan\",\"persetujuan_po\",\"persetujuan_pr\"],\"action\":\"save\"}', '::1', '2026-04-22 12:36:16'),
(1887, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 12:36:19'),
(1888, 20, 'save', 'Manajemen-role', 'Eksekusi [save] di menu [Manajemen-role]. Data: {\"role_id\":\"4\",\"role_name\":\"admin-gudang2\",\"role_slug\":\"admin_gudang2\",\"permissions\":[\"dashboard\",\"persetujuan\",\"persetujuan_po\",\"persetujuan_pr\",\"persetujuan_masuk_manual\",\"persetujuan_keluar_manual\",\"persetujuan_izin_cetak\",\"persetujuan_histori\"],\"action\":\"save\"}', '::1', '2026-04-22 12:36:27'),
(1889, NULL, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 12:36:29');
INSERT INTO `system_logs` (`id`, `user_id`, `action`, `menu`, `description`, `ip_address`, `created_at`) VALUES
(1890, NULL, 'read_histori', 'Persetujuan', 'Eksekusi [read_histori] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 12:36:30'),
(1891, NULL, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 12:36:30'),
(1892, NULL, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 12:36:31'),
(1893, NULL, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 12:36:31'),
(1894, NULL, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '::1', '2026-04-22 12:36:32'),
(1895, 20, 'save', 'Manajemen-role', 'Eksekusi [save] di menu [Manajemen-role]. Data: {\"role_id\":\"4\",\"role_name\":\"admin-gudang2\",\"role_slug\":\"admin_gudang2\",\"permissions\":[\"dashboard\",\"persetujuan_izin_cetak\",\"persetujuan_histori\",\"master_inventory\"],\"action\":\"save\"}', '::1', '2026-04-22 12:36:43'),
(1896, NULL, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '::1', '2026-04-22 12:36:47'),
(1897, NULL, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-22 12:36:49'),
(1898, 20, 'save', 'Manajemen-role', 'Eksekusi [save] di menu [Manajemen-role]. Data: {\"role_id\":\"4\",\"role_name\":\"admin-gudang2\",\"role_slug\":\"admin_gudang2\",\"permissions\":[\"dashboard\",\"persetujuan_izin_cetak\",\"persetujuan_histori\",\"master_inventory\",\"master_kategori\",\"master_satuan\"],\"action\":\"save\"}', '::1', '2026-04-22 12:38:47'),
(1899, NULL, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-22 12:38:49'),
(1900, 20, 'save', 'Manajemen-role', 'Eksekusi [save] di menu [Manajemen-role]. Data: {\"role_id\":\"4\",\"role_name\":\"admin-gudang2\",\"role_slug\":\"admin_gudang2\",\"permissions\":[\"dashboard\",\"persetujuan_izin_cetak\",\"persetujuan_histori\",\"master_satuan\",\"master_lokasi\",\"monitoring_rak\"],\"action\":\"save\"}', '::1', '2026-04-22 12:39:15'),
(1901, NULL, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 12:39:20'),
(1902, NULL, 'read_detail', 'Monitoring Rak', 'Eksekusi [read_detail] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 12:39:21'),
(1903, NULL, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 12:39:29'),
(1904, NULL, 'read_detail', 'Monitoring Rak', 'Eksekusi [read_detail] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 12:39:30'),
(1905, NULL, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 12:39:32'),
(1906, NULL, 'read_detail', 'Monitoring Rak', 'Eksekusi [read_detail] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 12:39:33'),
(1907, NULL, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 12:39:34'),
(1908, 20, 'save', 'Manajemen-role', 'Eksekusi [save] di menu [Manajemen-role]. Data: {\"role_id\":\"4\",\"role_name\":\"admin-gudang2\",\"role_slug\":\"admin_gudang2\",\"permissions\":[\"dashboard\",\"persetujuan_izin_cetak\",\"persetujuan_histori\",\"master_satuan\",\"master_lokasi\",\"monitoring_rak\",\"trx_barang_masuk\",\"trx_barang_keluar\"],\"action\":\"save\"}', '::1', '2026-04-22 12:39:42'),
(1909, NULL, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 12:39:45'),
(1910, NULL, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-22 12:39:46'),
(1911, NULL, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '::1', '2026-04-22 12:39:47'),
(1912, NULL, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-22 12:39:49'),
(1913, NULL, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '::1', '2026-04-22 12:39:54'),
(1914, NULL, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-22 12:39:58'),
(1915, 20, 'save', 'Manajemen-role', 'Eksekusi [save] di menu [Manajemen-role]. Data: {\"role_id\":\"4\",\"role_name\":\"admin-gudang2\",\"role_slug\":\"admin_gudang2\",\"permissions\":[\"dashboard\",\"persetujuan_izin_cetak\",\"persetujuan_histori\",\"master_satuan\",\"master_lokasi\",\"trx_barang_keluar\",\"cetak_barcode\",\"data_opname\"],\"action\":\"save\"}', '::1', '2026-04-22 12:40:09'),
(1916, NULL, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '::1', '2026-04-22 12:40:12'),
(1917, NULL, 'init_data', 'Cetak Barcode', 'Eksekusi [init_data] di menu [Cetak Barcode]. Data: []', '::1', '2026-04-22 12:40:18'),
(1918, 20, 'save', 'Manajemen-role', 'Eksekusi [save] di menu [Manajemen-role]. Data: {\"role_id\":\"4\",\"role_name\":\"admin-gudang2\",\"role_slug\":\"admin_gudang2\",\"permissions\":[\"dashboard\",\"persetujuan_izin_cetak\",\"persetujuan_histori\",\"master_satuan\",\"master_lokasi\",\"data_opname\",\"otorisasi_opname\",\"scanner_opname\"],\"action\":\"save\"}', '::1', '2026-04-22 12:40:32'),
(1919, NULL, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '::1', '2026-04-22 12:40:37'),
(1920, 20, 'save', 'Manajemen-role', 'Eksekusi [save] di menu [Manajemen-role]. Data: {\"role_id\":\"4\",\"role_name\":\"admin-gudang2\",\"role_slug\":\"admin_gudang2\",\"permissions\":[\"dashboard\",\"persetujuan_izin_cetak\",\"persetujuan_histori\",\"master_satuan\",\"master_lokasi\",\"data_opname\",\"otorisasi_opname\",\"scanner_opname\",\"trx_permintaan_dapur\",\"trx_permintaan_barang\"],\"action\":\"save\"}', '::1', '2026-04-22 12:40:47'),
(1921, NULL, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '::1', '2026-04-22 12:40:49'),
(1922, NULL, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '::1', '2026-04-22 12:40:53'),
(1923, 20, 'save', 'Manajemen-role', 'Eksekusi [save] di menu [Manajemen-role]. Data: {\"role_id\":\"4\",\"role_name\":\"admin-gudang2\",\"role_slug\":\"admin_gudang2\",\"permissions\":[\"dashboard\",\"persetujuan_izin_cetak\",\"persetujuan_histori\",\"master_satuan\",\"master_lokasi\",\"trx_permintaan_barang\",\"trx_po\",\"trx_pembayaran\",\"trx_supplier\"],\"action\":\"save\"}', '::1', '2026-04-22 12:41:05'),
(1924, NULL, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '::1', '2026-04-22 12:41:07'),
(1925, NULL, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '::1', '2026-04-22 12:41:09'),
(1926, NULL, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '::1', '2026-04-22 12:41:10'),
(1927, 20, 'save', 'Manajemen-role', 'Eksekusi [save] di menu [Manajemen-role]. Data: {\"role_id\":\"4\",\"role_name\":\"admin-gudang2\",\"role_slug\":\"admin_gudang2\",\"permissions\":[\"dashboard\",\"persetujuan_izin_cetak\",\"persetujuan_histori\",\"master_satuan\",\"master_lokasi\",\"trx_supplier\",\"lap_barang_masuk\",\"lap_barang_keluar\"],\"action\":\"save\"}', '::1', '2026-04-22 12:41:20'),
(1928, 20, 'save', 'Manajemen-role', 'Eksekusi [save] di menu [Manajemen-role]. Data: {\"role_id\":\"4\",\"role_name\":\"admin-gudang2\",\"role_slug\":\"admin_gudang2\",\"permissions\":[\"dashboard\",\"persetujuan_izin_cetak\",\"persetujuan_histori\",\"master_satuan\",\"master_lokasi\",\"trx_supplier\",\"lap_barang_masuk\",\"lap_barang_keluar\",\"lap_po\",\"lap_pembayaran_po\"],\"action\":\"save\"}', '::1', '2026-04-22 12:41:31'),
(1929, NULL, 'init', 'Pembayaran-po', 'Eksekusi [init] di menu [Pembayaran-po]. Data: []', '::1', '2026-04-22 12:41:35'),
(1930, 20, 'save', 'Manajemen-role', 'Eksekusi [save] di menu [Manajemen-role]. Data: {\"role_id\":\"4\",\"role_name\":\"admin-gudang2\",\"role_slug\":\"admin_gudang2\",\"permissions\":[\"dashboard\",\"persetujuan_izin_cetak\",\"persetujuan_histori\",\"master_satuan\",\"master_lokasi\",\"trx_supplier\",\"lap_barang_masuk\",\"lap_barang_keluar\",\"lap_po\",\"lap_pembayaran_po\",\"lap_stok_opname\",\"lap_kartu_stok\"],\"action\":\"save\"}', '::1', '2026-04-22 12:41:43'),
(1931, NULL, 'init', 'Pembayaran-po', 'Eksekusi [init] di menu [Pembayaran-po]. Data: []', '::1', '2026-04-22 12:41:45'),
(1932, NULL, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '::1', '2026-04-22 12:41:48'),
(1933, 20, 'save', 'Manajemen-role', 'Eksekusi [save] di menu [Manajemen-role]. Data: {\"role_id\":\"4\",\"role_name\":\"admin-gudang2\",\"role_slug\":\"admin_gudang2\",\"permissions\":[\"dashboard\",\"persetujuan_izin_cetak\",\"persetujuan_histori\",\"master_satuan\",\"master_lokasi\",\"trx_supplier\",\"lap_barang_keluar\",\"lap_kartu_stok\",\"lap_stok_menipis\",\"lap_stok_terbanyak\"],\"action\":\"save\"}', '::1', '2026-04-22 12:41:58'),
(1934, NULL, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '::1', '2026-04-22 12:42:00'),
(1935, 20, 'save', 'Manajemen-role', 'Eksekusi [save] di menu [Manajemen-role]. Data: {\"role_id\":\"4\",\"role_name\":\"admin-gudang2\",\"role_slug\":\"admin_gudang2\",\"permissions\":[\"dashboard\",\"persetujuan_izin_cetak\",\"persetujuan_histori\",\"master_satuan\",\"master_lokasi\",\"trx_supplier\",\"lap_barang_keluar\",\"lap_kartu_stok\",\"lap_stok_terbanyak\",\"lap_perbandingan_harga\",\"lap_supplier\"],\"action\":\"save\"}', '::1', '2026-04-22 12:42:11'),
(1936, NULL, 'init', 'Supplier-terakhir', 'Eksekusi [init] di menu [Supplier-terakhir]. Data: []', '::1', '2026-04-22 12:42:16'),
(1937, 20, 'save', 'Manajemen-role', 'Eksekusi [save] di menu [Manajemen-role]. Data: {\"role_id\":\"4\",\"role_name\":\"admin-gudang2\",\"role_slug\":\"admin_gudang2\",\"permissions\":[\"dashboard\",\"persetujuan_izin_cetak\",\"persetujuan_histori\",\"master_satuan\",\"master_lokasi\",\"trx_supplier\",\"lap_barang_keluar\",\"lap_kartu_stok\",\"lap_stok_terbanyak\",\"lap_perbandingan_harga\",\"lap_supplier\",\"pengaturan_pembayaran\",\"manage_users\",\"manage_roles\",\"pengaturan_profil\"],\"action\":\"save\"}', '::1', '2026-04-22 12:43:04'),
(1938, NULL, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '::1', '2026-04-22 12:43:16'),
(1939, NULL, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '::1', '2026-04-22 12:43:20'),
(1940, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '::1', '2026-04-22 13:05:29'),
(1941, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 13:25:59'),
(1942, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 13:26:06'),
(1943, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 13:26:18'),
(1944, 20, 'read_detail', 'Monitoring Rak', 'Eksekusi [read_detail] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 13:26:19'),
(1945, 20, 'init_data', 'Cetak Barcode', 'Eksekusi [init_data] di menu [Cetak Barcode]. Data: []', '::1', '2026-04-22 13:51:40'),
(1946, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 13:57:54'),
(1947, 20, 'read_detail', 'Monitoring Rak', 'Eksekusi [read_detail] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 13:57:57'),
(1948, 20, 'init_data', 'Cetak Barcode', 'Eksekusi [init_data] di menu [Cetak Barcode]. Data: []', '::1', '2026-04-22 13:58:00'),
(1949, 20, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '::1', '2026-04-22 14:35:31'),
(1950, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 14:35:34'),
(1951, 20, 'init_data', 'Cetak Barcode', 'Eksekusi [init_data] di menu [Cetak Barcode]. Data: []', '::1', '2026-04-22 14:35:39'),
(1952, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:05:06'),
(1953, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:05:54'),
(1954, 20, 'read_detail', 'Monitoring Rak', 'Eksekusi [read_detail] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:05:55'),
(1955, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:06:00'),
(1956, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '::1', '2026-04-22 16:06:07'),
(1957, 20, 'init_data', 'Cetak Barcode', 'Eksekusi [init_data] di menu [Cetak Barcode]. Data: []', '::1', '2026-04-22 16:06:17'),
(1958, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:06:23'),
(1959, 20, 'read_detail', 'Monitoring Rak', 'Eksekusi [read_detail] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:06:25'),
(1960, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:06:29'),
(1961, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-22 16:10:11'),
(1962, 20, 'init_data', 'Cetak Barcode', 'Eksekusi [init_data] di menu [Cetak Barcode]. Data: []', '::1', '2026-04-22 16:12:58'),
(1963, 20, 'init_data', 'Cetak Barcode', 'Eksekusi [init_data] di menu [Cetak Barcode]. Data: []', '::1', '2026-04-22 16:17:24'),
(1964, 20, 'init_data', 'Cetak Barcode', 'Eksekusi [init_data] di menu [Cetak Barcode]. Data: []', '::1', '2026-04-22 16:21:58'),
(1965, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:28:06'),
(1966, 20, 'read_detail', 'Monitoring Rak', 'Eksekusi [read_detail] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:30:30'),
(1967, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:30:38'),
(1968, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:34:32'),
(1969, 20, 'save', 'Manajemen-role', 'Eksekusi [save] di menu [Manajemen-role]. Data: {\"role_id\":\"4\",\"role_name\":\"admin-gudang2\",\"role_slug\":\"admin_gudang2\",\"permissions\":[\"dashboard\",\"persetujuan_izin_cetak\",\"persetujuan_histori\",\"master_satuan\",\"master_lokasi\",\"cetak_barcode\",\"trx_supplier\",\"lap_barang_keluar\",\"lap_kartu_stok\",\"lap_stok_terbanyak\",\"lap_perbandingan_harga\",\"lap_supplier\",\"pengaturan_pembayaran\",\"manage_users\",\"manage_roles\",\"pengaturan_profil\"],\"action\":\"save\"}', '::1', '2026-04-22 16:35:08'),
(1970, NULL, 'init_data', 'Cetak Barcode', 'Eksekusi [init_data] di menu [Cetak Barcode]. Data: []', '::1', '2026-04-22 16:35:20'),
(1971, 20, 'init_data', 'Cetak Barcode', 'Eksekusi [init_data] di menu [Cetak Barcode]. Data: []', '::1', '2026-04-22 16:35:44'),
(1972, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:35:47'),
(1973, 20, 'scan_rack', 'Monitoring Rak', 'Eksekusi [scan_rack] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:35:49'),
(1974, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:35:49'),
(1975, 20, 'read_detail', 'Monitoring Rak', 'Eksekusi [read_detail] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:35:49'),
(1976, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:36:21'),
(1977, 20, 'read_detail', 'Monitoring Rak', 'Eksekusi [read_detail] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:36:22'),
(1978, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:36:23'),
(1979, 20, 'read_detail', 'Monitoring Rak', 'Eksekusi [read_detail] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:36:24'),
(1980, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:36:25'),
(1981, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:36:28'),
(1982, 20, 'scan_rack', 'Monitoring Rak', 'Eksekusi [scan_rack] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:36:30'),
(1983, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:36:33'),
(1984, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:36:34'),
(1985, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:36:35'),
(1986, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-22 16:36:40'),
(1987, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:37:16'),
(1988, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:37:17'),
(1989, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:43:52'),
(1990, 20, 'read_detail', 'Monitoring Rak', 'Eksekusi [read_detail] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:43:56'),
(1991, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:43:59'),
(1992, 20, 'read_detail', 'Monitoring Rak', 'Eksekusi [read_detail] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:44:00'),
(1993, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:44:01'),
(1994, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:44:49'),
(1995, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '::1', '2026-04-22 16:44:50'),
(1996, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '::1', '2026-04-22 16:44:54'),
(1997, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '114.5.144.31', '2026-04-22 17:20:45'),
(1998, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '114.5.144.31', '2026-04-22 17:20:52'),
(1999, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '114.5.144.31', '2026-04-22 17:20:53'),
(2000, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '114.5.144.31', '2026-04-22 17:20:56'),
(2001, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '114.5.144.31', '2026-04-22 17:21:02'),
(2002, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '114.5.144.31', '2026-04-22 17:21:03'),
(2003, 20, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '114.5.144.31', '2026-04-22 17:21:05'),
(2004, 20, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '114.5.144.31', '2026-04-22 17:21:11'),
(2005, 20, 'init_data', 'Cetak Barcode', 'Eksekusi [init_data] di menu [Cetak Barcode]. Data: []', '114.5.144.31', '2026-04-22 17:21:12'),
(2006, 20, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '114.5.144.31', '2026-04-22 17:21:20'),
(2007, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '114.5.144.31', '2026-04-22 17:21:25'),
(2008, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '114.5.144.31', '2026-04-22 17:21:27'),
(2009, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '140.213.202.226', '2026-04-22 21:22:32'),
(2010, 2, 'get_employees', 'Produk Keluar', 'Eksekusi [get_employees] di menu [Produk Keluar]. Data: []', '140.213.202.226', '2026-04-22 21:22:55'),
(2011, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '140.213.202.226', '2026-04-22 21:23:00'),
(2012, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '140.213.202.226', '2026-04-22 21:23:11'),
(2013, 2, 'dashboard_data', 'Dashboard', 'Eksekusi [dashboard_data] di menu [Dashboard]. Data: []', '140.213.202.226', '2026-04-22 21:23:23'),
(2014, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '140.213.202.226', '2026-04-22 21:27:43'),
(2015, 1, 'save', 'Barang Titipan', 'Eksekusi [save] di menu [Barang Titipan]. Data: {\"id\":\"\",\"nama_barang\":\"Pempek\",\"nama_umkm\":\"UMKM BU TUTI\",\"harga_modal\":\"10000\",\"harga_jual\":\"12000\",\"stok\":\"10\",\"action\":\"save\"}', '114.5.145.97', '2026-04-23 14:28:02'),
(2016, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '114.5.145.97', '2026-04-23 14:28:30'),
(2017, 18, 'init_form', 'Input Titipan', 'Eksekusi [init_form] di menu [Input Titipan]. Data: []', '114.5.145.97', '2026-04-23 14:28:32'),
(2018, 18, 'save', 'Input Titipan', 'Eksekusi [save] di menu [Input Titipan]. Data: {\"employee_id\":\"1\",\"product_id\":[\"2\",\"3\"],\"quantity\":[\"2\",\"1\"],\"warehouse_id\":\"1\",\"notes\":\"\",\"pin\":\"1234\"}', '114.5.145.97', '2026-04-23 14:28:49'),
(2019, 18, 'init_form', 'Input Titipan', 'Eksekusi [init_form] di menu [Input Titipan]. Data: []', '114.5.145.97', '2026-04-23 14:29:01'),
(2020, 3, 'scan', 'Scan Barcode', 'Eksekusi [scan] di menu [Scan Barcode]. Data: {\"barcode\":\"TTP-260423-001-1\"}', '114.5.145.97', '2026-04-23 14:29:21'),
(2021, 3, 'scan', 'Validasi Titipan', 'Eksekusi [scan] di menu [Validasi Titipan]. Data: {\"barcode\":\"TTP-260423-001-1\"}', '114.5.145.97', '2026-04-23 14:29:29'),
(2022, 3, 'execute_validasi', 'Validasi Titipan', 'Eksekusi [execute_validasi] di menu [Validasi Titipan]. Data: {\"prod_id\":\"1\",\"status\":\"ditolak\"}', '114.5.145.97', '2026-04-23 14:29:32'),
(2023, 18, 'get_revisi_data', 'Riwayat Titipan', 'Eksekusi [get_revisi_data] di menu [Riwayat Titipan]. Data: []', '114.5.145.97', '2026-04-23 14:29:38'),
(2024, 18, 'revisi', 'Riwayat Titipan', 'Eksekusi [revisi] di menu [Riwayat Titipan]. Data: {\"production_id\":\"1\",\"product_id\":[\"2\",\"3\"],\"quantity\":[\"4\",\"1\"],\"action\":\"revisi\",\"pin\":\"1234\"}', '114.5.145.97', '2026-04-23 14:29:45'),
(2025, 3, 'scan', 'Validasi Titipan', 'Eksekusi [scan] di menu [Validasi Titipan]. Data: {\"barcode\":\"TTP-260423-001-1\"}', '114.5.145.97', '2026-04-23 14:29:48'),
(2026, 3, 'execute_validasi', 'Validasi Titipan', 'Eksekusi [execute_validasi] di menu [Validasi Titipan]. Data: {\"prod_id\":\"1\",\"status\":\"received\"}', '114.5.145.97', '2026-04-23 14:29:50'),
(2027, 1, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '114.5.145.97', '2026-04-23 14:49:14'),
(2028, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '114.5.145.97', '2026-04-23 14:49:14'),
(2029, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '114.5.145.97', '2026-04-23 14:49:16'),
(2030, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '157.85.198.31', '2026-04-24 03:20:07'),
(2031, 2, 'init_form', 'Input Titipan', 'Eksekusi [init_form] di menu [Input Titipan]. Data: []', '157.85.198.31', '2026-04-24 03:20:16'),
(2032, 2, 'get_employees', 'Produk Keluar', 'Eksekusi [get_employees] di menu [Produk Keluar]. Data: []', '157.85.198.31', '2026-04-24 03:20:36'),
(2033, 2, 'init_form', 'Input Titipan', 'Eksekusi [init_form] di menu [Input Titipan]. Data: []', '157.85.198.31', '2026-04-24 03:20:49'),
(2034, 2, 'get_employees', 'Produk Keluar', 'Eksekusi [get_employees] di menu [Produk Keluar]. Data: []', '157.85.198.31', '2026-04-24 03:21:12'),
(2035, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '157.85.198.31', '2026-04-24 03:23:28'),
(2036, 1, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '157.85.198.31', '2026-04-24 03:23:28'),
(2037, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '157.85.198.31', '2026-04-24 03:23:30'),
(2038, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '157.85.198.31', '2026-04-24 03:23:34'),
(2039, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '157.85.198.31', '2026-04-24 03:23:40'),
(2040, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '157.85.198.31', '2026-04-24 03:23:58'),
(2041, 1, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '157.85.198.31', '2026-04-24 03:24:51'),
(2042, 1, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '157.85.198.31', '2026-04-24 03:25:40'),
(2043, 1, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '157.85.198.31', '2026-04-24 03:25:42'),
(2044, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '2404:8000:1044:4ea:4991:224a:dfbf:8054', '2026-04-24 03:45:24'),
(2045, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:4991:224a:dfbf:8054', '2026-04-24 03:46:02'),
(2046, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:4991:224a:dfbf:8054', '2026-04-24 03:46:03'),
(2047, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:4991:224a:dfbf:8054', '2026-04-24 03:46:05'),
(2048, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:4991:224a:dfbf:8054', '2026-04-24 03:46:13'),
(2049, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:4991:224a:dfbf:8054', '2026-04-24 03:46:19'),
(2050, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:4991:224a:dfbf:8054', '2026-04-24 03:46:21'),
(2051, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:4991:224a:dfbf:8054', '2026-04-24 03:46:24'),
(2052, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:4991:224a:dfbf:8054', '2026-04-24 03:46:34'),
(2053, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '2404:8000:1044:4ea:4991:224a:dfbf:8054', '2026-04-24 03:46:37'),
(2054, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '2404:8000:1044:4ea:4991:224a:dfbf:8054', '2026-04-24 03:47:00'),
(2055, 20, 'read_detail', 'Monitoring Rak', 'Eksekusi [read_detail] di menu [Monitoring Rak]. Data: []', '2404:8000:1044:4ea:4991:224a:dfbf:8054', '2026-04-24 03:47:02'),
(2056, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '2404:8000:1044:4ea:4991:224a:dfbf:8054', '2026-04-24 03:47:44'),
(2057, 20, 'read_detail', 'Monitoring Rak', 'Eksekusi [read_detail] di menu [Monitoring Rak]. Data: []', '2404:8000:1044:4ea:4991:224a:dfbf:8054', '2026-04-24 03:47:46'),
(2058, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '2404:8000:1044:4ea:4991:224a:dfbf:8054', '2026-04-24 03:47:48'),
(2059, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '2404:8000:1044:4ea:4991:224a:dfbf:8054', '2026-04-24 03:48:00'),
(2060, 20, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '2404:8000:1044:4ea:4991:224a:dfbf:8054', '2026-04-24 03:48:08'),
(2061, 20, 'init_data', 'Cetak Barcode', 'Eksekusi [init_data] di menu [Cetak Barcode]. Data: []', '2404:8000:1044:4ea:4991:224a:dfbf:8054', '2026-04-24 03:48:18'),
(2062, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '2404:8000:1044:4ea:4991:224a:dfbf:8054', '2026-04-24 03:48:42'),
(2063, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:4991:224a:dfbf:8054', '2026-04-24 03:48:48'),
(2064, 20, 'request_print', 'Po', 'Eksekusi [request_print] di menu [Po]. Data: {\"action\":\"request_print\",\"id\":\"10\",\"tipe\":\"po\"}', '2404:8000:1044:4ea:4991:224a:dfbf:8054', '2026-04-24 03:49:10'),
(2065, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:4991:224a:dfbf:8054', '2026-04-24 03:49:10'),
(2066, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:4991:224a:dfbf:8054', '2026-04-24 03:49:12'),
(2067, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:4991:224a:dfbf:8054', '2026-04-24 03:49:27'),
(2068, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:4991:224a:dfbf:8054', '2026-04-24 03:49:28'),
(2069, 20, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:4991:224a:dfbf:8054', '2026-04-24 03:49:29'),
(2070, 20, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:4991:224a:dfbf:8054', '2026-04-24 03:50:24'),
(2071, 20, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:4991:224a:dfbf:8054', '2026-04-24 03:50:25'),
(2072, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '2404:8000:1044:4ea:1dc9:bdbf:2e42:7577', '2026-04-24 03:53:34'),
(2073, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:1dc9:bdbf:2e42:7577', '2026-04-24 03:53:39'),
(2074, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:1dc9:bdbf:2e42:7577', '2026-04-24 03:53:42'),
(2075, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:1dc9:bdbf:2e42:7577', '2026-04-24 03:53:43'),
(2076, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '2404:8000:1044:4ea:1dc9:bdbf:2e42:7577', '2026-04-24 03:54:09'),
(2077, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '2404:8000:1044:4ea:1dc9:bdbf:2e42:7577', '2026-04-24 03:55:00'),
(2078, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:1dc9:bdbf:2e42:7577', '2026-04-24 03:55:13'),
(2079, 20, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '2404:8000:1044:4ea:4991:224a:dfbf:8054', '2026-04-24 03:56:01'),
(2080, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:00:45'),
(2081, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:00:49'),
(2082, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:00:50'),
(2083, 20, 'proses_izin_cetak', 'Persetujuan', 'Eksekusi [proses_izin_cetak] di menu [Persetujuan]. Data: {\"action\":\"proses_izin_cetak\",\"id\":\"10\",\"tipe\":\"po\",\"keputusan\":\"approve\"}', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:00:55'),
(2084, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:00:55'),
(2085, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:01:00'),
(2086, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:40:37'),
(2087, 20, 'read_histori', 'Persetujuan', 'Eksekusi [read_histori] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:40:38'),
(2088, 20, 'read_histori', 'Persetujuan', 'Eksekusi [read_histori] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:40:53'),
(2089, 20, 'read_histori', 'Persetujuan', 'Eksekusi [read_histori] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:40:55'),
(2090, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:41:00'),
(2091, 20, 'read_histori', 'Persetujuan', 'Eksekusi [read_histori] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:41:01'),
(2092, 20, 'read_histori', 'Persetujuan', 'Eksekusi [read_histori] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:41:04'),
(2093, 20, 'read_histori', 'Persetujuan', 'Eksekusi [read_histori] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:41:05'),
(2094, 20, 'read_histori', 'Persetujuan', 'Eksekusi [read_histori] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:41:07'),
(2095, 20, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:41:14'),
(2096, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:41:15'),
(2097, 20, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:41:16'),
(2098, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:41:18'),
(2099, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:41:20'),
(2100, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:41:23'),
(2101, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:41:24'),
(2102, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:41:32'),
(2103, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:41:33'),
(2104, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:41:46'),
(2105, 20, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:41:47'),
(2106, 20, 'read_histori', 'Persetujuan', 'Eksekusi [read_histori] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:41:49'),
(2107, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:41:55'),
(2108, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:41:55'),
(2109, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:42:00'),
(2110, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:42:22'),
(2111, 20, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:42:28'),
(2112, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:50:39'),
(2113, 20, 'init', 'Supplier-terakhir', 'Eksekusi [init] di menu [Supplier-terakhir]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:51:59'),
(2114, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:52:16'),
(2115, 20, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:52:33'),
(2116, 20, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:57:26'),
(2117, 20, 'generate', 'Otorisasi', 'Eksekusi [generate] di menu [Otorisasi]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:57:33'),
(2118, 20, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:57:45'),
(2119, 20, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"206903\"}', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:57:47'),
(2120, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 04:59:48'),
(2121, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:02:39'),
(2122, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:02:42'),
(2123, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:02:43'),
(2124, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:02:47'),
(2125, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:02:49'),
(2126, 1, 'delete', 'Master Bahan', 'Eksekusi [delete] di menu [Master Bahan]. Data: {\"id\":\"23\"}', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:03:02'),
(2127, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:03:50'),
(2128, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:03:50'),
(2129, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:03:50'),
(2130, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:04:45'),
(2131, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:04:54'),
(2132, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:05:03'),
(2133, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:05:09'),
(2134, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:05:24'),
(2135, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:05:24'),
(2136, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:05:24'),
(2137, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:06:02'),
(2138, 16, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:07:00'),
(2139, 16, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:07:00'),
(2140, 16, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:07:03'),
(2141, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:07:22'),
(2142, 16, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:07:32'),
(2143, 16, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:07:32'),
(2144, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:08:01'),
(2145, 18, 'init_form', 'Input Titipan', 'Eksekusi [init_form] di menu [Input Titipan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:08:07'),
(2146, 18, 'init_form', 'Input Titipan', 'Eksekusi [init_form] di menu [Input Titipan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:08:10'),
(2147, 18, 'get_employees', 'Produk Keluar', 'Eksekusi [get_employees] di menu [Produk Keluar]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:08:21'),
(2148, 16, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:08:39'),
(2149, 16, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:08:39'),
(2150, 16, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:08:44'),
(2151, 16, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:08:50'),
(2152, 16, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:08:50'),
(2153, 16, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:08:52'),
(2154, 16, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:12:22'),
(2155, 16, 'save_bom', 'Master Resep', 'Eksekusi [save_bom] di menu [Master Resep]. Data: {\"product_id\":\"2\",\"material_id\":\"3\",\"quantity_needed\":\"50\",\"unit_used\":\"Gram\"}', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:14:18'),
(2156, 16, 'save_bom', 'Master Resep', 'Eksekusi [save_bom] di menu [Master Resep]. Data: {\"product_id\":\"2\",\"material_id\":\"4\",\"quantity_needed\":\"50\",\"unit_used\":\"Gram\"}', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:14:33'),
(2157, 16, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:14:33'),
(2158, 16, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:14:33'),
(2159, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:14:45'),
(2160, 16, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:15:00'),
(2161, 16, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:15:00'),
(2162, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:15:50'),
(2163, 2, 'init_form', 'Input Titipan', 'Eksekusi [init_form] di menu [Input Titipan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:15:53'),
(2164, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:16:06'),
(2165, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:16:40'),
(2166, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:16:41'),
(2167, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:16:41'),
(2168, 1, 'delete_user', 'Master User', 'Eksekusi [delete_user] di menu [Master User]. Data: {\"id\":\"2\"}', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:16:50'),
(2169, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:17:56'),
(2170, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:19:32'),
(2171, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:19:32'),
(2172, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:19:32'),
(2173, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:20:31'),
(2174, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:20:57'),
(2175, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:21:14'),
(2176, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:21:14'),
(2177, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:21:14'),
(2178, 1, 'delete_user', 'Master User', 'Eksekusi [delete_user] di menu [Master User]. Data: {\"id\":\"2\"}', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:21:40'),
(2179, 1, 'save_user', 'Master User', 'Eksekusi [save_user] di menu [Master User]. Data: {\"id\":\"2\",\"name\":\"pegawai produksi\",\"username\":\"produksi\",\"password\":\"******\",\"role\":\"produksi\",\"kitchen_id\":\"1\"}', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:22:15'),
(2180, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:22:15'),
(2181, 1, 'delete_user', 'Master User', 'Eksekusi [delete_user] di menu [Master User]. Data: {\"id\":\"2\"}', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:22:25'),
(2182, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:23:36'),
(2183, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:23:52'),
(2184, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:23:52'),
(2185, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:23:52'),
(2186, 1, 'delete_user', 'Master User', 'Eksekusi [delete_user] di menu [Master User]. Data: {\"id\":\"25\"}', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:24:09'),
(2187, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:24:09'),
(2188, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:25:00');
INSERT INTO `system_logs` (`id`, `user_id`, `action`, `menu`, `description`, `ip_address`, `created_at`) VALUES
(2189, 16, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:25:01'),
(2190, 16, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:25:01'),
(2191, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:26:18'),
(2192, 16, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:26:29'),
(2193, 16, 'delete', 'Manajemen Dapur', 'Eksekusi [delete] di menu [Manajemen Dapur]. Data: {\"id\":\"1\"}', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:26:52'),
(2194, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:27:16'),
(2195, 1, 'delete', 'Master Bahan', 'Eksekusi [delete] di menu [Master Bahan]. Data: {\"id\":\"22\"}', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:27:23'),
(2196, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:27:38'),
(2197, 1, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:27:38'),
(2198, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:28:09'),
(2199, 1, 'delete', 'Master Bahan', 'Eksekusi [delete] di menu [Master Bahan]. Data: {\"id\":\"19\"}', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:28:29'),
(2200, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:28:43'),
(2201, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:30:08'),
(2202, 18, 'init_form', 'Input Titipan', 'Eksekusi [init_form] di menu [Input Titipan]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:30:10'),
(2203, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:31:11'),
(2204, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:31:11'),
(2205, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:31:11'),
(2206, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:31:37'),
(2207, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:31:37'),
(2208, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '2404:8000:1044:4ea:186c:7b45:5609:9c2c', '2026-04-24 05:31:37'),
(2209, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '2001:448a:1170:3260:ed9f:304a:1bb1:e154', '2026-04-24 06:00:35'),
(2210, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '2001:448a:1170:3260:ed9f:304a:1bb1:e154', '2026-04-24 06:00:51'),
(2211, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '2001:448a:1170:3260:ed9f:304a:1bb1:e154', '2026-04-24 06:00:52'),
(2212, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '2001:448a:1170:3260:ed9f:304a:1bb1:e154', '2026-04-24 06:00:58'),
(2213, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '2001:448a:1170:3260:ed9f:304a:1bb1:e154', '2026-04-24 06:01:16'),
(2214, 20, 'read_detail', 'Monitoring Rak', 'Eksekusi [read_detail] di menu [Monitoring Rak]. Data: []', '2001:448a:1170:3260:ed9f:304a:1bb1:e154', '2026-04-24 06:01:21'),
(2215, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '2001:448a:1170:3260:ed9f:304a:1bb1:e154', '2026-04-24 06:01:28'),
(2216, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '2001:448a:1170:3260:ed9f:304a:1bb1:e154', '2026-04-24 06:01:45'),
(2217, 20, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '2001:448a:1170:3260:ed9f:304a:1bb1:e154', '2026-04-24 06:01:49'),
(2218, 20, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '2001:448a:1170:3260:ed9f:304a:1bb1:e154', '2026-04-24 06:02:15'),
(2219, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:14:17'),
(2220, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:14:32'),
(2221, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:14:37'),
(2222, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:14:38'),
(2223, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:14:41'),
(2224, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:15:39'),
(2225, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:15:47'),
(2226, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:15:53'),
(2227, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:16:13'),
(2228, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:20:04'),
(2229, 20, 'mark_printed', 'Po', 'Eksekusi [mark_printed] di menu [Po]. Data: {\"action\":\"mark_printed\",\"id\":\"9\",\"tipe\":\"po\"}', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:20:18'),
(2230, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:20:18'),
(2231, 20, 'mark_printed', 'Po', 'Eksekusi [mark_printed] di menu [Po]. Data: {\"action\":\"mark_printed\",\"id\":\"10\",\"tipe\":\"po\"}', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:21:10'),
(2232, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:21:10'),
(2233, 20, 'get_po_receive', 'Po', 'Eksekusi [get_po_receive] di menu [Po]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:22:02'),
(2234, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:22:22'),
(2235, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:22:25'),
(2236, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:22:27'),
(2237, 20, 'init', 'Pembayaran-po', 'Eksekusi [init] di menu [Pembayaran-po]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:22:31'),
(2238, 20, 'init', 'Supplier-terakhir', 'Eksekusi [init] di menu [Supplier-terakhir]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:25:36'),
(2239, 20, 'init', 'Pembayaran-po', 'Eksekusi [init] di menu [Pembayaran-po]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:26:14'),
(2240, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:26:19'),
(2241, 20, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:26:20'),
(2242, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:29:26'),
(2243, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:29:28'),
(2244, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:29:35'),
(2245, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:29:36'),
(2246, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:29:37'),
(2247, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:29:41'),
(2248, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:29:43'),
(2249, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:29:45'),
(2250, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:29:45'),
(2251, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:29:46'),
(2252, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:30:07'),
(2253, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:30:09'),
(2254, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:30:20'),
(2255, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:30:23'),
(2256, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:30:39'),
(2257, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:30:48'),
(2258, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:30:54'),
(2259, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:30:57'),
(2260, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:30:58'),
(2261, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:31:06'),
(2262, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:31:08'),
(2263, 20, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:31:45'),
(2264, 20, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '2404:8000:1044:4ea:1991:c018:2d4d:e0b8', '2026-04-24 07:51:18'),
(2265, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 02:32:35'),
(2266, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 02:32:35'),
(2267, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 02:32:35'),
(2268, 1, 'delete_user', 'Master User', 'Eksekusi [delete_user] di menu [Master User]. Data: {\"id\":\"2\"}', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 02:32:46'),
(2269, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:22:03'),
(2270, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:22:03'),
(2271, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:22:03'),
(2272, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:22:08'),
(2273, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:22:09'),
(2274, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:22:09'),
(2275, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:22:22'),
(2276, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:22:22'),
(2277, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:22:22'),
(2278, 1, 'delete_user', 'Master User', 'Eksekusi [delete_user] di menu [Master User]. Data: {\"id\":\"2\"}', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:22:28'),
(2279, 1, 'delete_user', 'Master User', 'Eksekusi [delete_user] di menu [Master User]. Data: {\"id\":\"24\"}', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:22:41'),
(2280, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:22:41'),
(2281, 1, 'delete_user', 'Master User', 'Eksekusi [delete_user] di menu [Master User]. Data: {\"id\":\"22\"}', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:22:48'),
(2282, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:22:48'),
(2283, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:24:58'),
(2284, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:25:07'),
(2285, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:25:33'),
(2286, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:26:04'),
(2287, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:26:45'),
(2288, 20, 'init', 'Supplier-terakhir', 'Eksekusi [init] di menu [Supplier-terakhir]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:26:51'),
(2289, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:27:45'),
(2290, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:27:46'),
(2291, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:27:46'),
(2292, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:29:14'),
(2293, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:29:14'),
(2294, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:29:14'),
(2295, 1, 'delete_user', 'Master User', 'Eksekusi [delete_user] di menu [Master User]. Data: {\"id\":\"12\"}', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:29:20'),
(2296, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:29:20'),
(2297, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:29:41'),
(2298, 18, 'init_form', 'Input Titipan', 'Eksekusi [init_form] di menu [Input Titipan]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:29:50'),
(2299, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:30:36'),
(2300, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:30:36'),
(2301, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:30:36'),
(2302, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:37:13'),
(2303, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:37:13'),
(2304, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:37:13'),
(2305, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:39:41'),
(2306, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:39:41'),
(2307, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '2404:8000:1044:4ea:b81c:7508:5e94:34a1', '2026-04-25 07:39:41'),
(2308, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '114.10.85.205', '2026-04-25 14:30:18'),
(2309, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '114.10.85.205', '2026-04-25 14:30:23'),
(2310, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '114.10.85.205', '2026-04-25 14:30:24'),
(2311, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '114.10.85.205', '2026-04-25 14:30:28'),
(2312, 20, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '114.10.85.205', '2026-04-25 14:30:32'),
(2313, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '114.10.85.205', '2026-04-25 14:30:38'),
(2314, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '114.10.85.205', '2026-04-25 14:30:41'),
(2315, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '114.10.85.205', '2026-04-25 14:30:57'),
(2316, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '114.10.85.205', '2026-04-25 14:31:03'),
(2317, 20, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '114.10.85.205', '2026-04-25 14:31:05'),
(2318, 20, 'init_data', 'Cetak Barcode', 'Eksekusi [init_data] di menu [Cetak Barcode]. Data: []', '114.10.85.205', '2026-04-25 14:31:11'),
(2319, 20, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '114.10.85.205', '2026-04-25 14:31:27'),
(2320, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '114.10.85.205', '2026-04-25 14:31:36'),
(2321, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '140.213.202.203', '2026-04-26 00:30:45'),
(2322, 18, 'get_employees', 'Produk Keluar', 'Eksekusi [get_employees] di menu [Produk Keluar]. Data: []', '140.213.202.203', '2026-04-26 00:31:44'),
(2323, 18, 'init_form', 'Input Titipan', 'Eksekusi [init_form] di menu [Input Titipan]. Data: []', '140.213.202.203', '2026-04-26 00:32:32'),
(2324, 18, 'get_employees', 'Produk Keluar', 'Eksekusi [get_employees] di menu [Produk Keluar]. Data: []', '140.213.202.203', '2026-04-26 00:32:36'),
(2325, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '140.213.202.203', '2026-04-26 00:32:51'),
(2326, 16, 'delete', 'Manajemen Dapur', 'Eksekusi [delete] di menu [Manajemen Dapur]. Data: {\"id\":\"2\"}', '140.213.202.203', '2026-04-26 00:33:47'),
(2327, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '140.213.202.203', '2026-04-26 00:33:52'),
(2328, 16, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '140.213.202.203', '2026-04-26 00:34:02'),
(2329, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '140.213.202.203', '2026-04-26 00:34:24'),
(2330, 16, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '140.213.202.203', '2026-04-26 00:34:27'),
(2331, 16, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '140.213.202.203', '2026-04-26 00:34:27'),
(2332, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '140.213.202.203', '2026-04-26 00:34:30'),
(2333, 16, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '140.213.202.203', '2026-04-26 00:34:34'),
(2334, 16, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '140.213.202.203', '2026-04-26 00:34:34'),
(2335, 16, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '140.213.202.203', '2026-04-26 00:34:36'),
(2336, 16, 'delete_bom', 'Master Resep', 'Eksekusi [delete_bom] di menu [Master Resep]. Data: {\"id\":\"27\"}', '140.213.202.203', '2026-04-26 00:34:46'),
(2337, 16, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '140.213.202.203', '2026-04-26 00:34:46'),
(2338, 16, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '140.213.202.203', '2026-04-26 00:34:46'),
(2339, 16, 'save_bom', 'Master Resep', 'Eksekusi [save_bom] di menu [Master Resep]. Data: {\"product_id\":\"2\",\"material_id\":\"3\",\"quantity_needed\":\"2\",\"unit_used\":\"Gram\"}', '140.213.202.203', '2026-04-26 00:35:23'),
(2340, 16, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '140.213.202.203', '2026-04-26 00:35:24'),
(2341, 16, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '140.213.202.203', '2026-04-26 00:35:24'),
(2342, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '140.213.202.203', '2026-04-26 00:37:42'),
(2343, 18, 'dashboard_data', 'Dashboard', 'Eksekusi [dashboard_data] di menu [Dashboard]. Data: []', '140.213.202.203', '2026-04-26 00:37:46'),
(2344, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '140.213.202.203', '2026-04-26 00:38:45'),
(2345, 20, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '140.213.202.203', '2026-04-26 00:39:12'),
(2346, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '140.213.202.203', '2026-04-26 00:40:09'),
(2347, 20, 'read_histori', 'Persetujuan', 'Eksekusi [read_histori] di menu [Persetujuan]. Data: []', '140.213.202.203', '2026-04-26 00:40:14'),
(2348, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '140.213.202.203', '2026-04-26 00:40:32'),
(2349, 20, 'read_detail', 'Monitoring Rak', 'Eksekusi [read_detail] di menu [Monitoring Rak]. Data: []', '140.213.202.203', '2026-04-26 00:40:35'),
(2350, 20, 'init', 'Pembayaran-po', 'Eksekusi [init] di menu [Pembayaran-po]. Data: []', '140.213.202.203', '2026-04-26 00:41:09'),
(2351, 20, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '140.213.202.203', '2026-04-26 00:42:06'),
(2352, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '140.213.202.203', '2026-04-26 00:53:09'),
(2353, 16, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:19:52'),
(2354, 16, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:19:52'),
(2355, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:19:54'),
(2356, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:20:05'),
(2357, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:20:06'),
(2358, 16, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:20:42'),
(2359, 16, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:20:42'),
(2360, 16, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:21:00'),
(2361, 16, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:21:00'),
(2362, 16, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:21:02'),
(2363, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:22:08'),
(2364, 16, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:23:03'),
(2365, 16, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:23:03'),
(2366, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:23:52'),
(2367, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:26:58'),
(2368, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:26:58'),
(2369, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:26:58'),
(2370, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:27:15'),
(2371, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:27:44'),
(2372, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:27:49'),
(2373, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:28:05'),
(2374, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:29:06'),
(2375, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:29:20'),
(2376, 20, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:29:22'),
(2377, 20, 'save_po', 'Po', 'Eksekusi [save_po] di menu [Po]. Data: {\"action\":\"save_po\",\"supplier_id\":\"1\",\"shipping_date\":\"2026-04-26\",\"cart\":\"[{\\\"pr_id\\\":null,\\\"material_id\\\":5,\\\"material_name\\\":\\\"Gula Pasir Kasar\\\",\\\"qty\\\":26,\\\"unit\\\":\\\"Kg\\\"}]\"}', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:29:51'),
(2378, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:29:53'),
(2379, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:30:16'),
(2380, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:30:19'),
(2381, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:30:37'),
(2382, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:30:43'),
(2383, 20, 'get_po_detail', 'Persetujuan', 'Eksekusi [get_po_detail] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:30:45'),
(2384, 20, 'update_po_status', 'Persetujuan', 'Eksekusi [update_po_status] di menu [Persetujuan]. Data: {\"action\":\"update_po_status\",\"po_id\":\"11\",\"status\":\"rejected\"}', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:30:54'),
(2385, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:30:54'),
(2386, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:31:16'),
(2387, 20, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:31:17'),
(2388, 20, 'save_po', 'Po', 'Eksekusi [save_po] di menu [Po]. Data: {\"action\":\"save_po\",\"supplier_id\":\"2\",\"shipping_date\":\"2026-04-26\",\"cart\":\"[{\\\"pr_id\\\":null,\\\"material_id\\\":3,\\\"material_name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty\\\":1,\\\"unit\\\":\\\"Kg\\\"},{\\\"pr_id\\\":null,\\\"material_id\\\":4,\\\"material_name\\\":\\\"Mentega Blueband\\\",\\\"qty\\\":1,\\\"unit\\\":\\\"Kg\\\"}]\"}', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:31:31'),
(2389, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:31:33'),
(2390, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:31:35'),
(2391, 20, 'get_po_detail', 'Persetujuan', 'Eksekusi [get_po_detail] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:31:37'),
(2392, 20, 'update_po_status', 'Persetujuan', 'Eksekusi [update_po_status] di menu [Persetujuan]. Data: {\"po_id\":\"12\",\"detail_id\":[\"21\",\"22\"],\"price\":[\"0.00\",\"0.00\"],\"qty\":[\"1\",\"0\"],\"action\":\"update_po_status\",\"status\":\"approved\"}', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:31:52'),
(2393, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:31:53'),
(2394, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:32:05'),
(2395, 20, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:32:40'),
(2396, 20, 'save_po', 'Po', 'Eksekusi [save_po] di menu [Po]. Data: {\"action\":\"save_po\",\"supplier_id\":\"2\",\"shipping_date\":\"2026-04-26\",\"cart\":\"[{\\\"pr_id\\\":null,\\\"material_id\\\":3,\\\"material_name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty\\\":2,\\\"unit\\\":\\\"Kg\\\"},{\\\"pr_id\\\":null,\\\"material_id\\\":4,\\\"material_name\\\":\\\"Mentega Blueband\\\",\\\"qty\\\":1,\\\"unit\\\":\\\"Kg\\\"},{\\\"pr_id\\\":null,\\\"material_id\\\":1,\\\"material_name\\\":\\\"Tepung Terigu Segitiga Biru\\\",\\\"qty\\\":1,\\\"unit\\\":\\\"Kg\\\"}]\"}', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:35:22'),
(2397, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:35:24'),
(2398, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:35:27'),
(2399, 20, 'get_po_detail', 'Persetujuan', 'Eksekusi [get_po_detail] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:36:19'),
(2400, 20, 'update_po_status', 'Persetujuan', 'Eksekusi [update_po_status] di menu [Persetujuan]. Data: {\"po_id\":\"13\",\"detail_id\":[\"23\",\"24\",\"25\"],\"price\":[\"0.00\",\"0.00\",\"0.00\"],\"qty\":[\"2\",\"1\",\"1\"],\"action\":\"update_po_status\",\"status\":\"approved\"}', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:37:27'),
(2401, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:37:28'),
(2402, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:37:46'),
(2403, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:40:50'),
(2404, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:40:58'),
(2405, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:41:01'),
(2406, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:41:04'),
(2407, 20, 'mark_printed', 'Po', 'Eksekusi [mark_printed] di menu [Po]. Data: {\"action\":\"mark_printed\",\"id\":\"8\",\"tipe\":\"po\"}', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:41:12'),
(2408, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:41:12'),
(2409, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:41:17'),
(2410, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:41:31'),
(2411, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:41:36'),
(2412, 20, 'mark_printed', 'Po', 'Eksekusi [mark_printed] di menu [Po]. Data: {\"action\":\"mark_printed\",\"id\":\"13\",\"tipe\":\"po\"}', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:41:43'),
(2413, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:41:43'),
(2414, 20, 'mark_printed', 'Po', 'Eksekusi [mark_printed] di menu [Po]. Data: {\"action\":\"mark_printed\",\"id\":\"12\",\"tipe\":\"po\"}', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:42:02'),
(2415, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:42:02'),
(2416, 20, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:43:29'),
(2417, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:46:22'),
(2418, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:46:24'),
(2419, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:46:28'),
(2420, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:46:45'),
(2421, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:46:46'),
(2422, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:47:01'),
(2423, 20, 'get_po_receive', 'Po', 'Eksekusi [get_po_receive] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:47:08'),
(2424, 20, 'save_receive_po', 'Po', 'Eksekusi [save_receive_po] di menu [Po]. Data: {\"action\":\"save_receive_po\",\"po_id\":\"13\",\"items\":\"[{\\\"material_id\\\":3,\\\"material_name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty_po\\\":2,\\\"qty_terima\\\":2,\\\"unit\\\":\\\"Kg\\\",\\\"price\\\":\\\"12000\\\",\\\"exp_date\\\":\\\"2026-04-26\\\"},{\\\"material_id\\\":4,\\\"material_name\\\":\\\"Mentega Blueband\\\",\\\"qty_po\\\":1,\\\"qty_terima\\\":\\\"0\\\",\\\"unit\\\":\\\"Kg\\\",\\\"price\\\":\\\"12000\\\",\\\"exp_date\\\":\\\"2026-04-26\\\"}]\"}', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:48:54'),
(2425, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:48:54'),
(2426, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:49:01'),
(2427, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:49:26'),
(2428, 20, 'init', 'Pembayaran-po', 'Eksekusi [init] di menu [Pembayaran-po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:49:52'),
(2429, 20, 'init', 'Pembayaran-po', 'Eksekusi [init] di menu [Pembayaran-po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:50:31'),
(2430, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:50:37'),
(2431, 20, 'get_payment_data', 'Pembayaran', 'Eksekusi [get_payment_data] di menu [Pembayaran]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:50:40'),
(2432, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:51:08'),
(2433, 20, 'get_po_receive', 'Po', 'Eksekusi [get_po_receive] di menu [Po]. Data: []', '2404:8000:1044:4ea:7189:f955:7a87:3054', '2026-04-26 01:51:13'),
(2434, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '2001:448a:1170:1355:80ec:c8d9:ba43:bfd3', '2026-04-26 05:47:43'),
(2435, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2001:448a:1170:1355:80ec:c8d9:ba43:bfd3', '2026-04-26 05:48:29'),
(2436, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2001:448a:1170:1355:80ec:c8d9:ba43:bfd3', '2026-04-26 05:48:32'),
(2437, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2001:448a:1170:1355:80ec:c8d9:ba43:bfd3', '2026-04-26 05:48:35'),
(2438, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2001:448a:1170:1355:80ec:c8d9:ba43:bfd3', '2026-04-26 05:48:37'),
(2439, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-27 01:54:16'),
(2440, 18, 'init_form', 'Input Titipan', 'Eksekusi [init_form] di menu [Input Titipan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-27 01:54:22'),
(2441, 18, 'get_employees', 'Produk Keluar', 'Eksekusi [get_employees] di menu [Produk Keluar]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-27 01:54:29'),
(2442, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-27 02:02:42'),
(2443, 16, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-27 02:02:43'),
(2444, 16, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-27 02:02:43'),
(2445, 16, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-27 02:02:45'),
(2446, 16, 'save_bom', 'Master Resep', 'Eksekusi [save_bom] di menu [Master Resep]. Data: {\"product_id\":\"1\",\"material_id\":\"3\",\"quantity_needed\":\"1\",\"unit_used\":\"Gram\"}', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-27 02:02:51'),
(2447, 16, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-27 02:02:51'),
(2448, 16, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-27 02:02:51'),
(2449, 16, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-27 02:14:35'),
(2450, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-27 12:47:33'),
(2451, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-27 12:47:40'),
(2452, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-27 12:47:42'),
(2453, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-27 12:47:45'),
(2454, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-27 12:47:46'),
(2455, 16, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-27 12:47:57'),
(2456, 16, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-27 12:47:57'),
(2457, 16, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-27 12:48:00'),
(2458, 16, 'delete_bom', 'Master Resep', 'Eksekusi [delete_bom] di menu [Master Resep]. Data: {\"id\":\"30\"}', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-27 12:48:08'),
(2459, 16, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-27 12:48:09'),
(2460, 16, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-27 12:48:09'),
(2461, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-27 12:48:13'),
(2462, 16, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-27 12:48:28'),
(2463, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '114.10.85.243', '2026-04-27 16:22:45'),
(2464, 18, 'dashboard_data', 'Dashboard', 'Eksekusi [dashboard_data] di menu [Dashboard]. Data: []', '114.10.85.243', '2026-04-27 16:22:50'),
(2465, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '114.10.85.243', '2026-04-27 16:23:16'),
(2466, 1, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '114.10.85.243', '2026-04-27 16:23:16'),
(2467, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '114.10.85.243', '2026-04-27 16:23:18'),
(2468, 1, 'submit_bom_request', 'Master Resep', 'Eksekusi [submit_bom_request] di menu [Master Resep]. Data: {\"product_id\":\"3\",\"notes\":\"bahan utama\",\"drafts\":\"[{\\\"material_id\\\":4,\\\"name\\\":\\\"Mentega Blueband\\\",\\\"quantity_needed\\\":\\\"2.00\\\",\\\"unit_used\\\":\\\"Kg\\\"}]\"}', '114.10.85.243', '2026-04-27 16:23:35'),
(2469, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '114.10.85.243', '2026-04-27 16:23:35'),
(2470, 1, 'read_detail', 'Persetujuan', 'Eksekusi [read_detail] di menu [Persetujuan]. Data: []', '114.10.85.243', '2026-04-27 16:23:43'),
(2471, 1, 'approve', 'Persetujuan', 'Eksekusi [approve] di menu [Persetujuan]. Data: {\"action\":\"approve\",\"id\":\"1\"}', '114.10.85.243', '2026-04-27 16:23:46'),
(2472, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '114.10.85.243', '2026-04-27 16:23:50'),
(2473, 1, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '114.10.85.243', '2026-04-27 16:23:50'),
(2474, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '114.10.85.243', '2026-04-27 16:24:27'),
(2475, 18, 'init', 'Keluar Titipan', 'Eksekusi [init] di menu [Keluar Titipan]. Data: []', '114.10.85.243', '2026-04-27 16:24:33'),
(2476, 18, 'save', 'Keluar Titipan', 'Eksekusi [save] di menu [Keluar Titipan]. Data: {\"titipan_id\":\"2\",\"qty\":\"1\",\"reason\":\"Expired\",\"notes\":\"\"}', '114.10.85.243', '2026-04-27 16:24:38'),
(2477, 18, 'init', 'Keluar Titipan', 'Eksekusi [init] di menu [Keluar Titipan]. Data: []', '114.10.85.243', '2026-04-27 16:24:38'),
(2478, 1, 'save', 'Manajemen Role', 'Eksekusi [save] di menu [Manajemen Role]. Data: {\"mode\":\"edit\",\"old_slug\":\"otorisasi\",\"role_name\":\"Otorisasi\",\"role_slug\":\"otorisasi\",\"permissions\":[\"manajemen_dapur\",\"edit_manajemen_dapur\",\"view_dashboard\",\"stok_opname\",\"otorisasi\",\"laporan_produk_jadi\",\"laporan_bom\",\"laporan_opname\"]}', '114.10.85.243', '2026-04-27 16:25:19'),
(2479, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '114.10.85.243', '2026-04-27 16:25:25'),
(2480, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '114.10.85.243', '2026-04-27 16:25:25'),
(2481, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '114.10.85.243', '2026-04-27 16:25:25'),
(2482, 1, 'save_user', 'Master User', 'Eksekusi [save_user] di menu [Master User]. Data: {\"id\":\"\",\"name\":\"testing\",\"username\":\"testing\",\"password\":\"******\",\"role\":\"otorisasi\",\"kitchen_id\":\"\"}', '114.10.85.243', '2026-04-27 16:25:43'),
(2483, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '114.10.85.243', '2026-04-27 16:25:43'),
(2484, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '114.10.85.243', '2026-04-27 16:27:11'),
(2485, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '114.10.85.243', '2026-04-27 16:27:28'),
(2486, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '114.10.85.243', '2026-04-27 16:27:34'),
(2487, 20, 'save', 'Permintaan', 'Eksekusi [save] di menu [Permintaan]. Data: {\"action\":\"save\",\"cart\":\"[{\\\"material_id\\\":\\\"3\\\",\\\"name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty\\\":10,\\\"unit\\\":\\\"Kg\\\",\\\"notes\\\":\\\"\\\"}]\"}', '114.10.85.243', '2026-04-27 16:27:44'),
(2488, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '114.10.85.243', '2026-04-27 16:27:48'),
(2489, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '114.10.85.243', '2026-04-27 16:27:54'),
(2490, 20, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '114.10.85.243', '2026-04-27 16:27:57'),
(2491, 20, 'save_po', 'Po', 'Eksekusi [save_po] di menu [Po]. Data: {\"action\":\"save_po\",\"supplier_id\":\"1\",\"shipping_date\":\"2026-04-28\",\"cart\":\"[{\\\"pr_id\\\":15,\\\"material_id\\\":3,\\\"material_name\\\":\\\"Coklat Batang Elmer\\\",\\\"qty\\\":9,\\\"unit\\\":\\\"Kg\\\"}]\"}', '114.10.85.243', '2026-04-27 16:28:11'),
(2492, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '114.10.85.243', '2026-04-27 16:28:12');
INSERT INTO `system_logs` (`id`, `user_id`, `action`, `menu`, `description`, `ip_address`, `created_at`) VALUES
(2493, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '114.10.85.243', '2026-04-27 16:28:15'),
(2494, 20, 'update_po_status', 'Persetujuan', 'Eksekusi [update_po_status] di menu [Persetujuan]. Data: {\"action\":\"update_po_status\",\"po_id\":\"14\",\"status\":\"approved\"}', '114.10.85.243', '2026-04-27 16:28:17'),
(2495, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '114.10.85.243', '2026-04-27 16:28:17'),
(2496, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '114.10.85.243', '2026-04-27 16:28:23'),
(2497, 20, 'get_po_receive', 'Po', 'Eksekusi [get_po_receive] di menu [Po]. Data: []', '114.10.85.243', '2026-04-27 16:28:29'),
(2498, 20, 'mark_printed', 'Po', 'Eksekusi [mark_printed] di menu [Po]. Data: {\"action\":\"mark_printed\",\"id\":\"14\",\"tipe\":\"po\"}', '114.10.85.243', '2026-04-27 16:28:57'),
(2499, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '114.10.85.243', '2026-04-27 16:29:00'),
(2500, 20, 'request_print', 'Po', 'Eksekusi [request_print] di menu [Po]. Data: {\"action\":\"request_print\",\"id\":\"14\",\"tipe\":\"po\"}', '114.10.85.243', '2026-04-27 16:29:03'),
(2501, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '114.10.85.243', '2026-04-27 16:29:03'),
(2502, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '114.10.85.243', '2026-04-27 16:29:07'),
(2503, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '114.10.85.243', '2026-04-27 16:29:07'),
(2504, 20, 'proses_izin_cetak', 'Persetujuan', 'Eksekusi [proses_izin_cetak] di menu [Persetujuan]. Data: {\"action\":\"proses_izin_cetak\",\"id\":\"14\",\"tipe\":\"po\",\"keputusan\":\"approve\"}', '114.10.85.243', '2026-04-27 16:29:09'),
(2505, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '114.10.85.243', '2026-04-27 16:29:09'),
(2506, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '114.10.85.243', '2026-04-27 16:29:14'),
(2507, 20, 'get_po_retur', 'Po', 'Eksekusi [get_po_retur] di menu [Po]. Data: []', '114.10.85.243', '2026-04-27 16:29:25'),
(2508, 20, 'save_retur_po', 'Po', 'Eksekusi [save_retur_po] di menu [Po]. Data: {\"action\":\"save_retur_po\",\"po_id\":\"13\",\"reason\":\"rusak\",\"items\":\"[{\\\"material_id\\\":3,\\\"material_name\\\":\\\"Coklat Batang Elmer\\\",\\\"unit\\\":\\\"Kg\\\",\\\"price\\\":\\\"12000.00\\\",\\\"qty_terima\\\":2,\\\"qty_return\\\":1},{\\\"material_id\\\":4,\\\"material_name\\\":\\\"Mentega Blueband\\\",\\\"unit\\\":\\\"Kg\\\",\\\"price\\\":\\\"12000.00\\\",\\\"qty_terima\\\":0,\\\"qty_return\\\":0}]\"}', '114.10.85.243', '2026-04-27 16:29:35'),
(2509, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '114.10.85.243', '2026-04-27 16:29:38'),
(2510, 20, 'read_retur_po', 'Persetujuan', 'Eksekusi [read_retur_po] di menu [Persetujuan]. Data: []', '114.10.85.243', '2026-04-27 16:29:38'),
(2511, 20, 'proses_retur_po', 'Persetujuan', 'Eksekusi [proses_retur_po] di menu [Persetujuan]. Data: {\"action\":\"proses_retur_po\",\"id\":\"1\",\"keputusan\":\"approve\"}', '114.10.85.243', '2026-04-27 16:29:41'),
(2512, 20, 'read_retur_po', 'Persetujuan', 'Eksekusi [read_retur_po] di menu [Persetujuan]. Data: []', '114.10.85.243', '2026-04-27 16:29:41'),
(2513, 1, 'read_detail', 'Persetujuan', 'Eksekusi [read_detail] di menu [Persetujuan]. Data: []', '140.213.202.155', '2026-04-27 19:08:41'),
(2514, 1, 'read_detail', 'Persetujuan', 'Eksekusi [read_detail] di menu [Persetujuan]. Data: []', '140.213.202.155', '2026-04-27 19:08:49'),
(2515, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '140.213.202.155', '2026-04-27 19:09:30'),
(2516, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '140.213.202.155', '2026-04-27 19:09:59'),
(2517, 1, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '140.213.202.155', '2026-04-27 19:10:03'),
(2518, 1, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '140.213.202.155', '2026-04-27 19:10:11'),
(2519, 1, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '140.213.202.155', '2026-04-27 19:10:11'),
(2520, 1, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '140.213.202.155', '2026-04-27 19:10:14'),
(2521, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '140.213.202.155', '2026-04-27 19:10:34'),
(2522, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '140.213.202.155', '2026-04-27 19:10:43'),
(2523, 20, 'read_histori', 'Persetujuan', 'Eksekusi [read_histori] di menu [Persetujuan]. Data: []', '140.213.202.155', '2026-04-27 19:10:52'),
(2524, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '140.213.202.155', '2026-04-27 19:11:05'),
(2525, 20, 'read_racks', 'Monitoring Rak', 'Eksekusi [read_racks] di menu [Monitoring Rak]. Data: []', '140.213.202.155', '2026-04-27 19:11:46'),
(2526, 20, 'read_detail', 'Monitoring Rak', 'Eksekusi [read_detail] di menu [Monitoring Rak]. Data: []', '140.213.202.155', '2026-04-27 19:11:49'),
(2527, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '140.213.202.155', '2026-04-27 19:12:06'),
(2528, 20, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '140.213.202.155', '2026-04-27 19:12:16'),
(2529, 20, 'init_data', 'Cetak Barcode', 'Eksekusi [init_data] di menu [Cetak Barcode]. Data: []', '140.213.202.155', '2026-04-27 19:12:21'),
(2530, 20, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '140.213.202.155', '2026-04-27 19:13:36'),
(2531, 20, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '140.213.202.155', '2026-04-27 19:13:49'),
(2532, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '140.213.202.155', '2026-04-27 19:13:58'),
(2533, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '140.213.202.155', '2026-04-27 19:14:04'),
(2534, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '140.213.202.155', '2026-04-27 19:14:17'),
(2535, 20, 'init', 'Pembayaran-po', 'Eksekusi [init] di menu [Pembayaran-po]. Data: []', '140.213.202.155', '2026-04-27 19:15:15'),
(2536, 20, 'init', 'Supplier-terakhir', 'Eksekusi [init] di menu [Supplier-terakhir]. Data: []', '140.213.202.155', '2026-04-27 19:15:44'),
(2537, 20, 'init', 'Pembayaran-po', 'Eksekusi [init] di menu [Pembayaran-po]. Data: []', '140.213.202.155', '2026-04-27 19:17:12'),
(2538, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '140.213.202.155', '2026-04-27 19:18:13'),
(2539, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '140.213.202.155', '2026-04-27 19:18:29'),
(2540, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '140.213.202.155', '2026-04-27 19:19:20'),
(2541, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '140.213.202.155', '2026-04-27 19:19:20'),
(2542, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '140.213.202.155', '2026-04-27 19:19:20'),
(2543, 1, 'delete_user', 'Master User', 'Eksekusi [delete_user] di menu [Master User]. Data: {\"id\":\"26\"}', '140.213.202.155', '2026-04-27 19:20:04'),
(2544, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '140.213.202.155', '2026-04-27 19:20:04'),
(2545, 1, 'delete_user', 'Master User', 'Eksekusi [delete_user] di menu [Master User]. Data: {\"id\":\"20\"}', '140.213.202.155', '2026-04-27 19:20:11'),
(2546, 1, 'delete_user', 'Master User', 'Eksekusi [delete_user] di menu [Master User]. Data: {\"id\":\"13\"}', '140.213.202.155', '2026-04-27 19:20:51'),
(2547, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '140.213.202.155', '2026-04-27 19:20:52'),
(2548, 1, 'delete_user', 'Master User', 'Eksekusi [delete_user] di menu [Master User]. Data: {\"id\":\"9\"}', '140.213.202.155', '2026-04-27 19:21:00'),
(2549, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '140.213.202.155', '2026-04-27 19:21:00'),
(2550, 1, 'delete_user', 'Master User', 'Eksekusi [delete_user] di menu [Master User]. Data: {\"id\":\"20\"}', '140.213.202.155', '2026-04-27 19:21:28'),
(2551, 1, 'delete_user', 'Master User', 'Eksekusi [delete_user] di menu [Master User]. Data: {\"id\":\"20\"}', '140.213.202.155', '2026-04-27 19:21:40'),
(2552, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '114.10.85.243', '2026-04-27 19:23:43'),
(2553, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '114.10.85.243', '2026-04-27 19:23:44'),
(2554, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '114.10.85.243', '2026-04-27 19:23:44'),
(2555, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '140.213.202.155', '2026-04-27 19:26:59'),
(2556, 16, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '140.213.202.155', '2026-04-27 19:27:06'),
(2557, 16, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '140.213.202.155', '2026-04-27 19:27:06'),
(2558, 16, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '140.213.202.155', '2026-04-27 19:27:08'),
(2559, 16, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '140.213.202.155', '2026-04-27 19:27:32'),
(2560, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '140.213.202.155', '2026-04-27 19:27:56'),
(2561, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '140.213.202.155', '2026-04-27 19:29:09'),
(2562, 18, 'init_form', 'Input Titipan', 'Eksekusi [init_form] di menu [Input Titipan]. Data: []', '140.213.202.155', '2026-04-27 19:30:08'),
(2563, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '140.213.202.155', '2026-04-27 19:31:12'),
(2564, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '140.213.202.155', '2026-04-27 19:31:21'),
(2565, 20, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '140.213.202.155', '2026-04-27 19:31:22'),
(2566, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '140.213.202.155', '2026-04-27 19:32:55'),
(2567, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '140.213.202.155', '2026-04-27 19:33:24'),
(2568, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '140.213.202.155', '2026-04-27 19:34:03'),
(2569, 20, 'init_form', 'Barang Masuk', 'Eksekusi [init_form] di menu [Barang Masuk]. Data: []', '140.213.202.155', '2026-04-27 19:34:22'),
(2570, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '140.213.202.155', '2026-04-27 19:34:51'),
(2571, 20, 'init_form', 'Barang Keluar', 'Eksekusi [init_form] di menu [Barang Keluar]. Data: []', '140.213.202.155', '2026-04-27 19:35:06'),
(2572, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '140.213.202.155', '2026-04-27 19:35:58'),
(2573, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '140.213.202.155', '2026-04-27 19:37:59'),
(2574, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '140.213.202.155', '2026-04-27 19:38:50'),
(2575, 20, 'init_form', 'Po', 'Eksekusi [init_form] di menu [Po]. Data: []', '140.213.202.155', '2026-04-27 19:38:51'),
(2576, 20, 'save_po', 'Po', 'Eksekusi [save_po] di menu [Po]. Data: {\"action\":\"save_po\",\"supplier_id\":\"1\",\"shipping_date\":\"2026-04-28\",\"cart\":\"[{\\\"pr_id\\\":null,\\\"material_id\\\":5,\\\"material_name\\\":\\\"Gula Pasir Kasar\\\",\\\"qty\\\":1,\\\"unit\\\":\\\"Kg\\\"}]\"}', '140.213.202.155', '2026-04-27 19:39:04'),
(2577, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '140.213.202.155', '2026-04-27 19:39:05'),
(2578, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '140.213.202.155', '2026-04-27 19:39:10'),
(2579, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '140.213.202.155', '2026-04-27 19:39:56'),
(2580, 20, 'get_po_receive', 'Po', 'Eksekusi [get_po_receive] di menu [Po]. Data: []', '140.213.202.155', '2026-04-27 19:40:00'),
(2581, 20, 'get_po_receive', 'Po', 'Eksekusi [get_po_receive] di menu [Po]. Data: []', '140.213.202.155', '2026-04-27 19:40:08'),
(2582, 20, 'get_po_receive', 'Po', 'Eksekusi [get_po_receive] di menu [Po]. Data: []', '140.213.202.155', '2026-04-27 19:40:37'),
(2583, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '140.213.202.155', '2026-04-27 19:41:01'),
(2584, 20, 'update_po_status', 'Persetujuan', 'Eksekusi [update_po_status] di menu [Persetujuan]. Data: {\"action\":\"update_po_status\",\"po_id\":\"15\",\"status\":\"approved\"}', '140.213.202.155', '2026-04-27 19:41:03'),
(2585, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '140.213.202.155', '2026-04-27 19:41:03'),
(2586, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '140.213.202.155', '2026-04-27 19:41:17'),
(2587, 20, 'get_po_receive', 'Po', 'Eksekusi [get_po_receive] di menu [Po]. Data: []', '140.213.202.155', '2026-04-27 19:41:24'),
(2588, 20, 'save_receive_po', 'Po', 'Eksekusi [save_receive_po] di menu [Po]. Data: {\"action\":\"save_receive_po\",\"po_id\":\"15\",\"items\":\"[{\\\"material_id\\\":5,\\\"material_name\\\":\\\"Gula Pasir Kasar\\\",\\\"qty_po\\\":1,\\\"qty_terima\\\":\\\"0\\\",\\\"unit\\\":\\\"Kg\\\",\\\"price\\\":\\\"13000\\\",\\\"exp_date\\\":\\\"2026-04-28\\\"}]\"}', '140.213.202.155', '2026-04-27 19:43:11'),
(2589, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '140.213.202.155', '2026-04-27 19:43:11'),
(2590, 20, 'get_po_receive', 'Po', 'Eksekusi [get_po_receive] di menu [Po]. Data: []', '140.213.202.155', '2026-04-27 19:43:18'),
(2591, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '157.85.198.43', '2026-04-28 02:03:58'),
(2592, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '182.1.236.195', '2026-04-28 16:49:31'),
(2593, 18, 'init', 'Rencana Harian', 'Eksekusi [init] di menu [Rencana Harian]. Data: []', '182.1.236.195', '2026-04-28 16:52:58'),
(2594, 18, 'read_today', 'Rencana Harian', 'Eksekusi [read_today] di menu [Rencana Harian]. Data: []', '182.1.236.195', '2026-04-28 16:52:58'),
(2595, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '182.1.236.195', '2026-04-28 16:53:23'),
(2596, 18, 'check_plan', 'Input Produksi', 'Eksekusi [check_plan] di menu [Input Produksi]. Data: []', '182.1.236.195', '2026-04-28 16:53:25'),
(2597, 18, 'read_today', 'Rencana Harian', 'Eksekusi [read_today] di menu [Rencana Harian]. Data: []', '182.1.236.195', '2026-04-28 16:53:32'),
(2598, 18, 'init', 'Rencana Harian', 'Eksekusi [init] di menu [Rencana Harian]. Data: []', '182.1.236.195', '2026-04-28 16:53:32'),
(2599, 18, 'save_plan', 'Rencana Harian', 'Eksekusi [save_plan] di menu [Rencana Harian]. Data: {\"action\":\"save_plan\",\"karyawan_id\":\"1\",\"notes\":\"\",\"cart\":\"[{\\\"product_id\\\":\\\"6\\\",\\\"product_name\\\":\\\"Roti kacanng\\\",\\\"qty\\\":1,\\\"adonan\\\":10},{\\\"product_id\\\":\\\"3\\\",\\\"product_name\\\":\\\"Roti Keju\\\",\\\"qty\\\":1,\\\"adonan\\\":30},{\\\"product_id\\\":\\\"1\\\",\\\"product_name\\\":\\\"Roti Blueberrie\\\",\\\"qty\\\":1,\\\"adonan\\\":30}]\"}', '182.1.236.195', '2026-04-28 16:54:18'),
(2600, 18, 'read_today', 'Rencana Harian', 'Eksekusi [read_today] di menu [Rencana Harian]. Data: []', '182.1.236.195', '2026-04-28 16:54:18'),
(2601, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '182.1.236.195', '2026-04-28 16:54:23'),
(2602, 18, 'check_plan', 'Input Produksi', 'Eksekusi [check_plan] di menu [Input Produksi]. Data: []', '182.1.236.195', '2026-04-28 16:54:25'),
(2603, 18, 'check_plan', 'Input Produksi', 'Eksekusi [check_plan] di menu [Input Produksi]. Data: []', '182.1.236.195', '2026-04-28 16:54:28'),
(2604, 18, 'check_plan', 'Input Produksi', 'Eksekusi [check_plan] di menu [Input Produksi]. Data: []', '182.1.236.195', '2026-04-28 16:54:30'),
(2605, 18, 'init', 'Keluar Titipan', 'Eksekusi [init] di menu [Keluar Titipan]. Data: []', '182.1.236.195', '2026-04-28 16:54:39'),
(2606, 18, 'get_employees', 'Produk Keluar', 'Eksekusi [get_employees] di menu [Produk Keluar]. Data: []', '182.1.236.195', '2026-04-28 16:54:47'),
(2607, 18, 'init', 'Keluar Titipan', 'Eksekusi [init] di menu [Keluar Titipan]. Data: []', '182.1.236.195', '2026-04-28 16:54:54'),
(2608, 18, 'save', 'Keluar Titipan', 'Eksekusi [save] di menu [Keluar Titipan]. Data: {\"titipan_id\":\"2\",\"qty\":\"12\",\"reason\":\"Expired\",\"notes\":\"\"}', '182.1.236.195', '2026-04-28 16:55:04'),
(2609, 18, 'init', 'Keluar Titipan', 'Eksekusi [init] di menu [Keluar Titipan]. Data: []', '182.1.236.195', '2026-04-28 16:55:04'),
(2610, 18, 'get_employees', 'Produk Keluar', 'Eksekusi [get_employees] di menu [Produk Keluar]. Data: []', '182.1.236.195', '2026-04-28 16:55:18'),
(2611, 18, 'get_employees', 'Produk Keluar', 'Eksekusi [get_employees] di menu [Produk Keluar]. Data: []', '182.1.236.195', '2026-04-28 16:55:24'),
(2612, 1, 'init', 'Laporan Target Produksi', 'Eksekusi [init] di menu [Laporan Target Produksi]. Data: []', '182.1.236.195', '2026-04-28 16:56:05'),
(2613, 1, 'init', 'Laporan Target Produksi', 'Eksekusi [init] di menu [Laporan Target Produksi]. Data: []', '182.1.236.195', '2026-04-28 16:56:46'),
(2614, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '182.1.236.195', '2026-04-28 16:57:34'),
(2615, 18, 'init', 'Keluar Titipan', 'Eksekusi [init] di menu [Keluar Titipan]. Data: []', '182.1.236.195', '2026-04-28 16:57:53'),
(2616, 18, 'get_employees', 'Produk Keluar', 'Eksekusi [get_employees] di menu [Produk Keluar]. Data: []', '182.1.236.195', '2026-04-28 16:58:10'),
(2617, 18, 'init_form', 'Input Titipan', 'Eksekusi [init_form] di menu [Input Titipan]. Data: []', '182.1.236.195', '2026-04-28 16:58:38'),
(2618, 18, 'dashboard_data', 'Dashboard', 'Eksekusi [dashboard_data] di menu [Dashboard]. Data: []', '182.1.236.195', '2026-04-28 16:58:46'),
(2619, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '140.213.202.227', '2026-04-29 01:03:00'),
(2620, 18, 'init_form', 'Input Titipan', 'Eksekusi [init_form] di menu [Input Titipan]. Data: []', '140.213.202.227', '2026-04-29 01:03:06'),
(2621, 1, 'read_detail', 'Persetujuan', 'Eksekusi [read_detail] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:24:24'),
(2622, 1, 'read_detail', 'Persetujuan', 'Eksekusi [read_detail] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:24:37'),
(2623, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:25:02'),
(2624, 16, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:25:03'),
(2625, 16, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:25:03'),
(2626, 16, 'read_bom', 'Master Resep', 'Eksekusi [read_bom] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:25:04'),
(2627, 16, 'submit_bom_request', 'Master Resep', 'Eksekusi [submit_bom_request] di menu [Master Resep]. Data: {\"product_id\":\"2\",\"notes\":\"ubah\",\"drafts\":\"[{\\\"material_id\\\":\\\"1\\\",\\\"name\\\":\\\"TEPUNG TERIGU SEGITIGA BIRU\\\",\\\"quantity_needed\\\":1,\\\"unit_used\\\":\\\"Kg\\\"}]\"}', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:25:38'),
(2628, 16, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:25:38'),
(2629, 1, 'read_detail', 'Persetujuan', 'Eksekusi [read_detail] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:26:09'),
(2630, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:27:17'),
(2631, 2, 'init', 'Rencana Harian', 'Eksekusi [init] di menu [Rencana Harian]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:27:26'),
(2632, 2, 'read_today', 'Rencana Harian', 'Eksekusi [read_today] di menu [Rencana Harian]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:27:26'),
(2633, 2, 'init_form', 'Input Titipan', 'Eksekusi [init_form] di menu [Input Titipan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:27:27'),
(2634, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:27:32'),
(2635, 2, 'init', 'Rencana Harian', 'Eksekusi [init] di menu [Rencana Harian]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:27:36'),
(2636, 2, 'read_today', 'Rencana Harian', 'Eksekusi [read_today] di menu [Rencana Harian]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:27:36'),
(2637, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:28:13'),
(2638, 2, 'init_form', 'Input Titipan', 'Eksekusi [init_form] di menu [Input Titipan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:28:17'),
(2639, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:29:02'),
(2640, 16, 'read_pilar', 'Master Bahan', 'Eksekusi [read_pilar] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:29:07'),
(2641, 16, 'submit_request', 'Master Bahan', 'Eksekusi [submit_request] di menu [Master Bahan]. Data: {\"warehouse_id\":\"1\",\"pilar_id\":[\"3\",\"5\"],\"qty\":[\"1\",\"2\"],\"req_unit\":[\"default\",\"default\"]}', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:29:50'),
(2642, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:29:51'),
(2643, 16, 'read_request_detail', 'Master Bahan', 'Eksekusi [read_request_detail] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:29:54'),
(2644, 16, 'read_request_detail', 'Master Bahan', 'Eksekusi [read_request_detail] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:29:57'),
(2645, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:30:12'),
(2646, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:30:17'),
(2647, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:30:18'),
(2648, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:30:19'),
(2649, 20, 'reject_manual', 'Persetujuan', 'Eksekusi [reject_manual] di menu [Persetujuan]. Data: {\"action\":\"reject_manual\",\"id\":\"15\"}', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:30:27'),
(2650, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:30:27'),
(2651, 20, 'reject_manual', 'Persetujuan', 'Eksekusi [reject_manual] di menu [Persetujuan]. Data: {\"action\":\"reject_manual\",\"id\":\"17\"}', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:30:31'),
(2652, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:30:31'),
(2653, 20, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:30:32'),
(2654, 20, 'reject_keluar', 'Persetujuan', 'Eksekusi [reject_keluar] di menu [Persetujuan]. Data: {\"action\":\"reject_keluar\",\"id\":\"3\"}', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:30:35'),
(2655, 20, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:30:35'),
(2656, 20, 'reject_keluar', 'Persetujuan', 'Eksekusi [reject_keluar] di menu [Persetujuan]. Data: {\"action\":\"reject_keluar\",\"id\":\"5\"}', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:30:38'),
(2657, 20, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:30:38'),
(2658, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:30:40'),
(2659, 20, 'read_histori', 'Persetujuan', 'Eksekusi [read_histori] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:30:41'),
(2660, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:30:44'),
(2661, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:30:47'),
(2662, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:30:48'),
(2663, 20, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:30:49'),
(2664, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:30:50'),
(2665, 20, 'read_histori', 'Persetujuan', 'Eksekusi [read_histori] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:30:51'),
(2666, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:30:51'),
(2667, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:31:43'),
(2668, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:32:04'),
(2669, 16, 'read_request_detail', 'Master Bahan', 'Eksekusi [read_request_detail] di menu [Master Bahan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:32:06'),
(2670, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:32:23'),
(2671, 20, 'read_detail', 'Permintaan-dapur', 'Eksekusi [read_detail] di menu [Permintaan-dapur]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:33:09'),
(2672, 20, 'reject', 'Permintaan-dapur', 'Eksekusi [reject] di menu [Permintaan-dapur]. Data: {\"id\":\"25\",\"header_id\":\"1\"}', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:33:15'),
(2673, 20, 'read_detail', 'Permintaan-dapur', 'Eksekusi [read_detail] di menu [Permintaan-dapur]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:33:15'),
(2674, 20, 'reject', 'Permintaan-dapur', 'Eksekusi [reject] di menu [Permintaan-dapur]. Data: {\"id\":\"26\",\"header_id\":\"1\"}', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:33:18'),
(2675, 20, 'read_detail', 'Permintaan-dapur', 'Eksekusi [read_detail] di menu [Permintaan-dapur]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:33:18'),
(2676, 20, 'read_detail', 'Permintaan-dapur', 'Eksekusi [read_detail] di menu [Permintaan-dapur]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:33:21'),
(2677, 20, 'read_detail', 'Permintaan-dapur', 'Eksekusi [read_detail] di menu [Permintaan-dapur]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:33:30'),
(2678, 20, 'init_form', 'Permintaan', 'Eksekusi [init_form] di menu [Permintaan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:33:38'),
(2679, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:34:56'),
(2680, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:34:59'),
(2681, 20, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:35:09'),
(2682, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:35:14'),
(2683, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 01:35:15'),
(2684, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 08:18:30'),
(2685, 2, 'read_today', 'Rencana Harian', 'Eksekusi [read_today] di menu [Rencana Harian]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 08:18:34'),
(2686, 2, 'init', 'Rencana Harian', 'Eksekusi [init] di menu [Rencana Harian]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 08:18:34'),
(2687, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 08:18:40'),
(2688, 2, 'check_plan', 'Input Produksi', 'Eksekusi [check_plan] di menu [Input Produksi]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 08:18:42'),
(2689, 2, 'check_plan', 'Input Produksi', 'Eksekusi [check_plan] di menu [Input Produksi]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 08:18:44'),
(2690, 2, 'check_plan', 'Input Produksi', 'Eksekusi [check_plan] di menu [Input Produksi]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 08:18:46'),
(2691, 2, 'read_today', 'Rencana Harian', 'Eksekusi [read_today] di menu [Rencana Harian]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 08:18:51'),
(2692, 2, 'init', 'Rencana Harian', 'Eksekusi [init] di menu [Rencana Harian]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 08:18:51'),
(2693, 2, 'save_plan', 'Rencana Harian', 'Eksekusi [save_plan] di menu [Rencana Harian]. Data: {\"action\":\"save_plan\",\"karyawan_id\":\"1\",\"notes\":\"\",\"cart\":\"[{\\\"product_id\\\":\\\"2\\\",\\\"product_name\\\":\\\"Brownis Coklat\\\",\\\"qty\\\":3,\\\"adonan\\\":0},{\\\"product_id\\\":\\\"6\\\",\\\"product_name\\\":\\\"Roti kacanng\\\",\\\"qty\\\":2,\\\"adonan\\\":0},{\\\"product_id\\\":\\\"3\\\",\\\"product_name\\\":\\\"Roti Keju\\\",\\\"qty\\\":1,\\\"adonan\\\":0}]\"}', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 08:19:23'),
(2694, 2, 'read_today', 'Rencana Harian', 'Eksekusi [read_today] di menu [Rencana Harian]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 08:19:23'),
(2695, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 08:19:26'),
(2696, 2, 'check_plan', 'Input Produksi', 'Eksekusi [check_plan] di menu [Input Produksi]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 08:19:28'),
(2697, 2, 'save', 'Input Produksi', 'Eksekusi [save] di menu [Input Produksi]. Data: {\"employee_id\":\"1\",\"product_id\":[\"2\",\"1\",\"6\",\"3\"],\"quantity\":[\"2\",\"2\",\"5\",\"10\"],\"warehouse_id\":\"1\",\"notes\":\"\",\"pin\":\"1234\"}', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 08:21:57'),
(2698, 2, 'get_employees', 'Produk Keluar', 'Eksekusi [get_employees] di menu [Produk Keluar]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 08:27:21'),
(2699, 3, 'scan', 'Scan Barcode', 'Eksekusi [scan] di menu [Scan Barcode]. Data: {\"barcode\":\"D2926-001-1\"}', '2404:8000:1044:4ea:700d:e14d:8e65:9444', '2026-04-29 08:28:26'),
(2700, 3, 'execute_validasi', 'Scan Barcode', 'Eksekusi [execute_validasi] di menu [Scan Barcode]. Data: {\"prod_id\":\"92\",\"status\":\"ditolak\"}', '2404:8000:1044:4ea:700d:e14d:8e65:9444', '2026-04-29 08:28:54'),
(2701, 3, 'scan', 'Scan Barcode', 'Eksekusi [scan] di menu [Scan Barcode]. Data: {\"barcode\":\"D2926-001-1\"}', '2404:8000:1044:4ea:700d:e14d:8e65:9444', '2026-04-29 08:29:03'),
(2702, 2, 'init_form', 'Input Titipan', 'Eksekusi [init_form] di menu [Input Titipan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 08:29:07'),
(2703, 2, 'update_revisi', 'Riwayat Produksi', 'Eksekusi [update_revisi] di menu [Riwayat Produksi]. Data: {\"prod_id\":\"92\",\"detail_id\":[\"117\",\"118\",\"119\",\"120\"],\"quantity\":[\"1\",\"2\",\"5\",\"10\"]}', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 08:29:34'),
(2704, 2, 'init', 'Keluar Titipan', 'Eksekusi [init] di menu [Keluar Titipan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 08:29:47'),
(2705, 2, 'get_employees', 'Produk Keluar', 'Eksekusi [get_employees] di menu [Produk Keluar]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 08:29:50'),
(2706, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 08:30:07'),
(2707, 2, 'init', 'Rencana Harian', 'Eksekusi [init] di menu [Rencana Harian]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 08:30:08'),
(2708, 2, 'read_today', 'Rencana Harian', 'Eksekusi [read_today] di menu [Rencana Harian]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 08:30:08'),
(2709, 3, 'scan', 'Scan Barcode', 'Eksekusi [scan] di menu [Scan Barcode]. Data: {\"barcode\":\"D2926-001-1\"}', '2404:8000:1044:4ea:700d:e14d:8e65:9444', '2026-04-29 08:30:57'),
(2710, 3, 'execute_validasi', 'Scan Barcode', 'Eksekusi [execute_validasi] di menu [Scan Barcode]. Data: {\"prod_id\":\"92\",\"status\":\"masuk_gudang\"}', '2404:8000:1044:4ea:700d:e14d:8e65:9444', '2026-04-29 08:31:04'),
(2711, 2, 'init_form', 'Input Titipan', 'Eksekusi [init_form] di menu [Input Titipan]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 08:31:25'),
(2712, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '2404:8000:1044:4ea:a8b5:a682:2ac5:43a9', '2026-04-29 08:31:27'),
(2713, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '114.10.84.252', '2026-04-29 13:14:03'),
(2714, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '157.85.197.1', '2026-04-30 16:26:30'),
(2715, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '157.85.197.1', '2026-04-30 16:26:44'),
(2716, 18, 'init_form', 'Input Titipan', 'Eksekusi [init_form] di menu [Input Titipan]. Data: []', '157.85.197.1', '2026-04-30 16:26:57'),
(2717, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '157.85.197.1', '2026-04-30 16:27:06'),
(2718, 18, 'dashboard_data', 'Dashboard', 'Eksekusi [dashboard_data] di menu [Dashboard]. Data: []', '157.85.197.1', '2026-04-30 16:27:19'),
(2719, 18, 'read_today', 'Rencana Harian', 'Eksekusi [read_today] di menu [Rencana Harian]. Data: []', '157.85.197.1', '2026-04-30 16:27:28'),
(2720, 18, 'init', 'Rencana Harian', 'Eksekusi [init] di menu [Rencana Harian]. Data: []', '157.85.197.1', '2026-04-30 16:27:28'),
(2721, 18, 'dashboard_data', 'Dashboard', 'Eksekusi [dashboard_data] di menu [Dashboard]. Data: []', '157.85.197.1', '2026-04-30 16:27:56'),
(2722, 1, 'update_pengumuman', 'Dashboard', 'Eksekusi [update_pengumuman] di menu [Dashboard]. Data: {\"pengumuman\":\"Stok opname\",\"action\":\"update_pengumuman\"}', '157.85.197.1', '2026-04-30 16:28:26'),
(2723, 1, 'read_detail', 'Persetujuan', 'Eksekusi [read_detail] di menu [Persetujuan]. Data: []', '157.85.197.1', '2026-04-30 16:29:45'),
(2724, 1, 'reject', 'Persetujuan', 'Eksekusi [reject] di menu [Persetujuan]. Data: {\"action\":\"reject\",\"id\":\"2\"}', '157.85.197.1', '2026-04-30 16:29:47'),
(2725, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '157.85.197.1', '2026-04-30 16:30:11'),
(2726, 18, 'dashboard_data', 'Dashboard', 'Eksekusi [dashboard_data] di menu [Dashboard]. Data: []', '157.85.197.1', '2026-04-30 16:30:15'),
(2727, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '157.85.197.1', '2026-04-30 16:31:20'),
(2728, 18, 'read_today', 'Rencana Harian', 'Eksekusi [read_today] di menu [Rencana Harian]. Data: []', '157.85.197.1', '2026-04-30 16:31:32'),
(2729, 18, 'init', 'Rencana Harian', 'Eksekusi [init] di menu [Rencana Harian]. Data: []', '157.85.197.1', '2026-04-30 16:31:32'),
(2730, 18, 'init_form', 'Input Titipan', 'Eksekusi [init_form] di menu [Input Titipan]. Data: []', '157.85.197.1', '2026-04-30 16:31:46'),
(2731, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '114.10.81.84', '2026-04-30 16:49:24'),
(2732, 18, 'dashboard_data', 'Dashboard', 'Eksekusi [dashboard_data] di menu [Dashboard]. Data: []', '114.10.81.84', '2026-04-30 16:49:37'),
(2733, 18, 'init', 'Rencana Harian', 'Eksekusi [init] di menu [Rencana Harian]. Data: []', '114.10.81.84', '2026-04-30 16:50:00'),
(2734, 18, 'read_today', 'Rencana Harian', 'Eksekusi [read_today] di menu [Rencana Harian]. Data: []', '114.10.81.84', '2026-04-30 16:50:00'),
(2735, 18, 'init', 'Rencana Harian', 'Eksekusi [init] di menu [Rencana Harian]. Data: []', '114.10.81.84', '2026-04-30 16:50:08'),
(2736, 18, 'read_today', 'Rencana Harian', 'Eksekusi [read_today] di menu [Rencana Harian]. Data: []', '114.10.81.84', '2026-04-30 16:50:08'),
(2737, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '114.10.81.84', '2026-04-30 16:50:13'),
(2738, 18, 'dashboard_data', 'Dashboard', 'Eksekusi [dashboard_data] di menu [Dashboard]. Data: []', '114.10.81.84', '2026-04-30 16:50:14'),
(2739, 18, 'read_today', 'Rencana Harian', 'Eksekusi [read_today] di menu [Rencana Harian]. Data: []', '114.10.81.84', '2026-04-30 16:50:17'),
(2740, 18, 'init', 'Rencana Harian', 'Eksekusi [init] di menu [Rencana Harian]. Data: []', '114.10.81.84', '2026-04-30 16:50:17'),
(2741, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '114.10.81.84', '2026-05-01 02:25:40'),
(2742, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '110.137.74.223', '2026-05-01 08:28:58'),
(2743, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '110.137.74.223', '2026-05-01 08:29:13'),
(2744, 20, 'read_permintaan', 'Persetujuan', 'Eksekusi [read_permintaan] di menu [Persetujuan]. Data: []', '110.137.74.223', '2026-05-01 08:29:14'),
(2745, 20, 'read_manual', 'Persetujuan', 'Eksekusi [read_manual] di menu [Persetujuan]. Data: []', '110.137.74.223', '2026-05-01 08:29:15'),
(2746, 20, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '110.137.74.223', '2026-05-01 08:29:15'),
(2747, 20, 'read_izin_cetak', 'Persetujuan', 'Eksekusi [read_izin_cetak] di menu [Persetujuan]. Data: []', '110.137.74.223', '2026-05-01 08:29:16'),
(2748, 20, 'read_histori', 'Persetujuan', 'Eksekusi [read_histori] di menu [Persetujuan]. Data: []', '110.137.74.223', '2026-05-01 08:29:16'),
(2749, 20, 'read_retur_po', 'Persetujuan', 'Eksekusi [read_retur_po] di menu [Persetujuan]. Data: []', '110.137.74.223', '2026-05-01 08:29:17'),
(2750, 20, 'read_histori', 'Persetujuan', 'Eksekusi [read_histori] di menu [Persetujuan]. Data: []', '110.137.74.223', '2026-05-01 08:29:18'),
(2751, 20, 'read_histori', 'Persetujuan', 'Eksekusi [read_histori] di menu [Persetujuan]. Data: []', '110.137.74.223', '2026-05-01 08:29:21'),
(2752, 20, 'read_histori', 'Persetujuan', 'Eksekusi [read_histori] di menu [Persetujuan]. Data: []', '110.137.74.223', '2026-05-01 08:29:22'),
(2753, 20, 'read_histori', 'Persetujuan', 'Eksekusi [read_histori] di menu [Persetujuan]. Data: []', '110.137.74.223', '2026-05-01 08:29:24'),
(2754, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '110.137.74.223', '2026-05-01 08:29:29'),
(2755, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '110.137.74.223', '2026-05-01 08:29:32'),
(2756, 20, 'read_histori', 'Persetujuan', 'Eksekusi [read_histori] di menu [Persetujuan]. Data: []', '110.137.74.223', '2026-05-01 08:29:33'),
(2757, 20, 'read_histori', 'Persetujuan', 'Eksekusi [read_histori] di menu [Persetujuan]. Data: []', '110.137.74.223', '2026-05-01 08:29:37'),
(2758, 20, 'init_data', 'Cetak Barcode', 'Eksekusi [init_data] di menu [Cetak Barcode]. Data: []', '110.137.74.223', '2026-05-01 08:29:42'),
(2759, 20, 'init_data', 'Cetak Barcode', 'Eksekusi [init_data] di menu [Cetak Barcode]. Data: []', '110.137.74.223', '2026-05-01 08:37:12'),
(2760, 20, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '110.137.74.223', '2026-05-01 08:38:46'),
(2761, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '110.137.74.223', '2026-05-01 08:39:17'),
(2762, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '110.137.74.223', '2026-05-01 08:39:21'),
(2763, 20, 'get_roles', 'User-management', 'Eksekusi [get_roles] di menu [User-management]. Data: []', '110.137.74.223', '2026-05-01 08:39:52'),
(2764, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '110.137.74.223', '2026-05-01 08:40:59'),
(2765, 20, 'download_template', 'Inventory', 'Eksekusi [download_template] di menu [Inventory]. Data: []', '110.137.74.223', '2026-05-01 08:41:26'),
(2766, 20, 'export', 'Inventory', 'Eksekusi [export] di menu [Inventory]. Data: []', '110.137.74.223', '2026-05-01 08:41:38'),
(2767, 20, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '110.137.74.223', '2026-05-01 08:41:54'),
(2768, 20, 'generate', 'Otorisasi', 'Eksekusi [generate] di menu [Otorisasi]. Data: []', '110.137.74.223', '2026-05-01 08:42:13'),
(2769, 20, 'init_data', 'Scanner', 'Eksekusi [init_data] di menu [Scanner]. Data: []', '110.137.74.223', '2026-05-01 08:42:18'),
(2770, 20, 'verify_pin', 'Scanner', 'Eksekusi [verify_pin] di menu [Scanner]. Data: {\"action\":\"verify_pin\",\"pin\":\"152025\"}', '110.137.74.223', '2026-05-01 08:42:20'),
(2771, 20, 'download_template', 'Scanner', 'Eksekusi [download_template] di menu [Scanner]. Data: []', '110.137.74.223', '2026-05-01 08:42:22'),
(2772, 20, 'read_detail', 'Permintaan-dapur', 'Eksekusi [read_detail] di menu [Permintaan-dapur]. Data: []', '110.137.74.223', '2026-05-01 08:42:42'),
(2773, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '110.137.74.223', '2026-05-01 08:42:46'),
(2774, 20, 'get_po_receive', 'Po', 'Eksekusi [get_po_receive] di menu [Po]. Data: []', '110.137.74.223', '2026-05-01 08:42:49'),
(2775, 20, 'get_po_receive', 'Po', 'Eksekusi [get_po_receive] di menu [Po]. Data: []', '110.137.74.223', '2026-05-01 08:42:53'),
(2776, 20, 'get_po_retur', 'Po', 'Eksekusi [get_po_retur] di menu [Po]. Data: []', '110.137.74.223', '2026-05-01 08:42:57'),
(2777, 20, 'get_po_receive', 'Po', 'Eksekusi [get_po_receive] di menu [Po]. Data: []', '110.137.74.223', '2026-05-01 08:43:07'),
(2778, 20, 'get_po_retur', 'Po', 'Eksekusi [get_po_retur] di menu [Po]. Data: []', '110.137.74.223', '2026-05-01 08:43:11'),
(2779, 20, 'get_po_retur', 'Po', 'Eksekusi [get_po_retur] di menu [Po]. Data: []', '110.137.74.223', '2026-05-01 08:43:35'),
(2780, 20, 'save_retur_po', 'Po', 'Eksekusi [save_retur_po] di menu [Po]. Data: {\"action\":\"save_retur_po\",\"po_id\":\"13\",\"reason\":\"sobek\",\"items\":\"[{\\\"material_id\\\":3,\\\"material_name\\\":\\\"Coklat Batang Elmer\\\",\\\"unit\\\":\\\"Kg\\\",\\\"price\\\":\\\"12000.00\\\",\\\"qty_terima\\\":2,\\\"qty_return\\\":1},{\\\"material_id\\\":4,\\\"material_name\\\":\\\"Mentega Blueband\\\",\\\"unit\\\":\\\"Kg\\\",\\\"price\\\":\\\"12000.00\\\",\\\"qty_terima\\\":0,\\\"qty_return\\\":0}]\"}', '110.137.74.223', '2026-05-01 08:43:56'),
(2781, 20, 'get_po_retur', 'Po', 'Eksekusi [get_po_retur] di menu [Po]. Data: []', '110.137.74.223', '2026-05-01 08:44:09'),
(2782, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '110.137.74.223', '2026-05-01 08:44:14'),
(2783, 20, 'read_retur_po', 'Persetujuan', 'Eksekusi [read_retur_po] di menu [Persetujuan]. Data: []', '110.137.74.223', '2026-05-01 08:44:17'),
(2784, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '110.137.74.223', '2026-05-01 08:44:27'),
(2785, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '110.137.74.223', '2026-05-01 08:44:55'),
(2786, 20, 'read_retur_po', 'Persetujuan', 'Eksekusi [read_retur_po] di menu [Persetujuan]. Data: []', '110.137.74.223', '2026-05-01 08:44:57'),
(2787, 20, 'proses_retur_po', 'Persetujuan', 'Eksekusi [proses_retur_po] di menu [Persetujuan]. Data: {\"action\":\"proses_retur_po\",\"id\":\"2\",\"keputusan\":\"reject\"}', '110.137.74.223', '2026-05-01 08:45:09'),
(2788, 20, 'read_retur_po', 'Persetujuan', 'Eksekusi [read_retur_po] di menu [Persetujuan]. Data: []', '110.137.74.223', '2026-05-01 08:45:09'),
(2789, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '110.137.74.223', '2026-05-01 08:45:17'),
(2790, 20, 'init_form', 'Inventory', 'Eksekusi [init_form] di menu [Inventory]. Data: []', '110.137.74.223', '2026-05-01 08:45:45'),
(2791, 20, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '110.137.74.223', '2026-05-01 08:45:52'),
(2792, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '110.137.74.223', '2026-05-01 08:46:15'),
(2793, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '110.137.74.223', '2026-05-01 08:46:18'),
(2794, 20, 'get_po_retur', 'Po', 'Eksekusi [get_po_retur] di menu [Po]. Data: []', '110.137.74.223', '2026-05-01 08:46:20'),
(2795, 20, 'save_retur_po', 'Po', 'Eksekusi [save_retur_po] di menu [Po]. Data: {\"action\":\"save_retur_po\",\"po_id\":\"13\",\"reason\":\"ok\",\"items\":\"[{\\\"material_id\\\":3,\\\"material_name\\\":\\\"Coklat Batang Elmer\\\",\\\"unit\\\":\\\"Kg\\\",\\\"price\\\":\\\"12000.00\\\",\\\"qty_terima\\\":2,\\\"qty_return\\\":2},{\\\"material_id\\\":4,\\\"material_name\\\":\\\"Mentega Blueband\\\",\\\"unit\\\":\\\"Kg\\\",\\\"price\\\":\\\"12000.00\\\",\\\"qty_terima\\\":0,\\\"qty_return\\\":0}]\"}', '110.137.74.223', '2026-05-01 08:46:31'),
(2796, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '110.137.74.223', '2026-05-01 08:46:37'),
(2797, 20, 'read_retur_po', 'Persetujuan', 'Eksekusi [read_retur_po] di menu [Persetujuan]. Data: []', '110.137.74.223', '2026-05-01 08:46:39'),
(2798, 20, 'proses_retur_po', 'Persetujuan', 'Eksekusi [proses_retur_po] di menu [Persetujuan]. Data: {\"action\":\"proses_retur_po\",\"id\":\"3\",\"keputusan\":\"approve\"}', '110.137.74.223', '2026-05-01 08:46:42'),
(2799, 20, 'read_retur_po', 'Persetujuan', 'Eksekusi [read_retur_po] di menu [Persetujuan]. Data: []', '110.137.74.223', '2026-05-01 08:46:42'),
(2800, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '110.137.74.223', '2026-05-01 08:46:48'),
(2801, 20, 'get_po_receive', 'Po', 'Eksekusi [get_po_receive] di menu [Po]. Data: []', '110.137.74.223', '2026-05-01 08:46:55'),
(2802, 20, 'get_po_receive', 'Po', 'Eksekusi [get_po_receive] di menu [Po]. Data: []', '110.137.74.223', '2026-05-01 08:47:02'),
(2803, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '110.137.74.223', '2026-05-01 08:47:16'),
(2804, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '110.137.74.223', '2026-05-01 08:47:20'),
(2805, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '110.137.74.223', '2026-05-01 08:47:27'),
(2806, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '110.137.74.223', '2026-05-01 08:47:28');
INSERT INTO `system_logs` (`id`, `user_id`, `action`, `menu`, `description`, `ip_address`, `created_at`) VALUES
(2807, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '110.137.74.223', '2026-05-01 08:47:32'),
(2808, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '110.137.74.223', '2026-05-01 08:47:33'),
(2809, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '110.137.74.223', '2026-05-01 08:47:39'),
(2810, 20, 'get_payment_data', 'Pembayaran', 'Eksekusi [get_payment_data] di menu [Pembayaran]. Data: []', '110.137.74.223', '2026-05-01 08:47:42'),
(2811, 20, 'get_payment_data', 'Pembayaran', 'Eksekusi [get_payment_data] di menu [Pembayaran]. Data: []', '110.137.74.223', '2026-05-01 08:47:57'),
(2812, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '110.137.74.223', '2026-05-01 08:48:31'),
(2813, 20, 'get_po_receive', 'Po', 'Eksekusi [get_po_receive] di menu [Po]. Data: []', '110.137.74.223', '2026-05-01 08:49:01'),
(2814, 20, 'init', 'Kartu-stok', 'Eksekusi [init] di menu [Kartu-stok]. Data: []', '110.137.74.223', '2026-05-01 08:49:36'),
(2815, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '110.137.74.223', '2026-05-01 08:49:41'),
(2816, 20, 'init', 'Supplier-terakhir', 'Eksekusi [init] di menu [Supplier-terakhir]. Data: []', '110.137.74.223', '2026-05-01 08:50:19'),
(2817, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '182.9.48.155', '2026-05-02 12:37:32'),
(2818, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '182.9.48.155', '2026-05-02 12:37:40'),
(2819, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '157.85.197.10', '2026-05-02 13:59:07'),
(2820, 2, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '2404:8000:1044:4ea:b447:1edc:eee0:e90a', '2026-05-03 13:44:09'),
(2821, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '114.5.144.34', '2026-05-03 13:54:49'),
(2822, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '182.1.231.136', '2026-05-04 01:40:26'),
(2823, 18, 'init', 'Rencana Harian', 'Eksekusi [init] di menu [Rencana Harian]. Data: []', '182.1.231.136', '2026-05-04 01:40:47'),
(2824, 18, 'read_today', 'Rencana Harian', 'Eksekusi [read_today] di menu [Rencana Harian]. Data: []', '182.1.231.136', '2026-05-04 01:40:47'),
(2825, 18, 'dashboard_data', 'Dashboard', 'Eksekusi [dashboard_data] di menu [Dashboard]. Data: []', '182.1.231.136', '2026-05-04 01:40:53'),
(2826, 18, 'init', 'Rencana Harian', 'Eksekusi [init] di menu [Rencana Harian]. Data: []', '182.1.231.136', '2026-05-04 01:40:58'),
(2827, 18, 'read_today', 'Rencana Harian', 'Eksekusi [read_today] di menu [Rencana Harian]. Data: []', '182.1.231.136', '2026-05-04 01:40:58'),
(2828, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '182.1.231.136', '2026-05-04 01:41:01'),
(2829, 18, 'init', 'Rencana Harian', 'Eksekusi [init] di menu [Rencana Harian]. Data: []', '182.1.231.136', '2026-05-04 01:44:17'),
(2830, 18, 'read_today', 'Rencana Harian', 'Eksekusi [read_today] di menu [Rencana Harian]. Data: []', '182.1.231.136', '2026-05-04 01:44:17'),
(2831, 18, 'init_form', 'Input Produksi', 'Eksekusi [init_form] di menu [Input Produksi]. Data: []', '182.1.231.136', '2026-05-04 01:44:38'),
(2832, 18, 'init_form', 'Input Titipan', 'Eksekusi [init_form] di menu [Input Titipan]. Data: []', '182.1.231.136', '2026-05-04 01:44:41'),
(2833, 18, 'get_employees', 'Produk Keluar', 'Eksekusi [get_employees] di menu [Produk Keluar]. Data: []', '182.1.231.136', '2026-05-04 01:45:44'),
(2834, 18, 'init', 'Keluar Titipan', 'Eksekusi [init] di menu [Keluar Titipan]. Data: []', '182.1.231.136', '2026-05-04 01:46:08'),
(2835, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '182.1.231.136', '2026-05-04 01:47:27'),
(2836, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '182.1.231.136', '2026-05-04 01:47:32'),
(2837, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '182.1.231.136', '2026-05-04 01:47:34'),
(2838, 16, 'read_requests', 'Master Bahan', 'Eksekusi [read_requests] di menu [Master Bahan]. Data: []', '182.1.231.136', '2026-05-04 01:47:34'),
(2839, 16, 'read_products', 'Master Resep', 'Eksekusi [read_products] di menu [Master Resep]. Data: []', '182.1.231.136', '2026-05-04 01:47:38'),
(2840, 16, 'get_units', 'Master Resep', 'Eksekusi [get_units] di menu [Master Resep]. Data: []', '182.1.231.136', '2026-05-04 01:47:38'),
(2841, 1, 'get_roles', 'Master User', 'Eksekusi [get_roles] di menu [Master User]. Data: []', '182.1.231.136', '2026-05-04 01:50:52'),
(2842, 1, 'read_employees', 'Master User', 'Eksekusi [read_employees] di menu [Master User]. Data: []', '182.1.231.136', '2026-05-04 01:50:52'),
(2843, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '182.1.231.136', '2026-05-04 01:50:52'),
(2844, 1, 'delete_user', 'Master User', 'Eksekusi [delete_user] di menu [Master User]. Data: {\"id\":\"5\"}', '182.1.231.136', '2026-05-04 01:51:09'),
(2845, 1, 'read_users', 'Master User', 'Eksekusi [read_users] di menu [Master User]. Data: []', '182.1.231.136', '2026-05-04 01:51:09'),
(2846, 20, 'get_dashboard_data', 'Dashboard', 'Eksekusi [get_dashboard_data] di menu [Dashboard]. Data: []', '182.1.231.136', '2026-05-04 01:52:13'),
(2847, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '182.1.231.136', '2026-05-04 01:52:17'),
(2848, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '182.1.231.136', '2026-05-04 01:52:20'),
(2849, 20, 'read_keluar', 'Persetujuan', 'Eksekusi [read_keluar] di menu [Persetujuan]. Data: []', '182.1.231.136', '2026-05-04 01:52:22'),
(2850, 20, 'read_histori', 'Persetujuan', 'Eksekusi [read_histori] di menu [Persetujuan]. Data: []', '182.1.231.136', '2026-05-04 01:52:25'),
(2851, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '182.1.231.136', '2026-05-04 01:52:38'),
(2852, 20, 'get_po_receive', 'Po', 'Eksekusi [get_po_receive] di menu [Po]. Data: []', '182.1.231.136', '2026-05-04 01:52:48'),
(2853, 20, 'get_po_receive', 'Po', 'Eksekusi [get_po_receive] di menu [Po]. Data: []', '182.1.231.136', '2026-05-04 01:53:07'),
(2854, 20, 'get_po_retur', 'Po', 'Eksekusi [get_po_retur] di menu [Po]. Data: []', '182.1.231.136', '2026-05-04 01:53:28'),
(2855, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '182.1.231.136', '2026-05-04 01:54:37'),
(2856, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '182.1.231.136', '2026-05-04 01:54:39'),
(2857, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '182.1.231.136', '2026-05-04 01:54:42'),
(2858, 20, 'get_payment_data', 'Pembayaran', 'Eksekusi [get_payment_data] di menu [Pembayaran]. Data: []', '182.1.231.136', '2026-05-04 01:54:52'),
(2859, 20, 'get_payment_data', 'Pembayaran', 'Eksekusi [get_payment_data] di menu [Pembayaran]. Data: []', '182.1.231.136', '2026-05-04 01:56:04'),
(2860, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '182.1.231.136', '2026-05-04 01:57:18'),
(2861, 20, 'mark_printed', 'Po', 'Eksekusi [mark_printed] di menu [Po]. Data: {\"action\":\"mark_printed\",\"id\":\"15\",\"tipe\":\"terima\"}', '182.1.231.136', '2026-05-04 01:57:33'),
(2862, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '182.1.231.136', '2026-05-04 01:57:33'),
(2863, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '182.1.231.136', '2026-05-04 01:57:39'),
(2864, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '182.1.231.136', '2026-05-04 01:57:41'),
(2865, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '182.1.231.136', '2026-05-04 01:57:44'),
(2866, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '182.1.231.136', '2026-05-04 01:57:46'),
(2867, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '182.1.231.136', '2026-05-04 01:57:49'),
(2868, 20, 'get_payment_data', 'Pembayaran', 'Eksekusi [get_payment_data] di menu [Pembayaran]. Data: []', '182.1.231.136', '2026-05-04 01:57:53'),
(2869, 20, 'get_payment_data', 'Pembayaran', 'Eksekusi [get_payment_data] di menu [Pembayaran]. Data: []', '182.1.231.136', '2026-05-04 01:57:58'),
(2870, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '182.1.231.136', '2026-05-04 01:59:04'),
(2871, 20, 'get_po_retur', 'Po', 'Eksekusi [get_po_retur] di menu [Po]. Data: []', '182.1.231.136', '2026-05-04 01:59:07'),
(2872, 20, 'get_po_retur', 'Po', 'Eksekusi [get_po_retur] di menu [Po]. Data: []', '182.1.231.136', '2026-05-04 01:59:30'),
(2873, 20, 'save_retur_po', 'Po', 'Eksekusi [save_retur_po] di menu [Po]. Data: {\"action\":\"save_retur_po\",\"po_id\":\"13\",\"reason\":\"Salah\",\"items\":\"[{\\\"material_id\\\":3,\\\"material_name\\\":\\\"Coklat Batang Elmer\\\",\\\"unit\\\":\\\"Kg\\\",\\\"price\\\":\\\"12000.00\\\",\\\"qty_terima\\\":2,\\\"qty_return\\\":1},{\\\"material_id\\\":4,\\\"material_name\\\":\\\"Mentega Blueband\\\",\\\"unit\\\":\\\"Kg\\\",\\\"price\\\":\\\"12000.00\\\",\\\"qty_terima\\\":0,\\\"qty_return\\\":0}]\"}', '182.1.231.136', '2026-05-04 01:59:41'),
(2874, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '182.1.231.136', '2026-05-04 01:59:41'),
(2875, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '182.1.231.136', '2026-05-04 01:59:51'),
(2876, 20, 'read_bills', 'Pembayaran', 'Eksekusi [read_bills] di menu [Pembayaran]. Data: []', '182.1.231.136', '2026-05-04 01:59:54'),
(2877, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '182.1.231.136', '2026-05-04 01:59:56'),
(2878, 20, 'read_retur_po', 'Persetujuan', 'Eksekusi [read_retur_po] di menu [Persetujuan]. Data: []', '182.1.231.136', '2026-05-04 02:00:01'),
(2879, 20, 'proses_retur_po', 'Persetujuan', 'Eksekusi [proses_retur_po] di menu [Persetujuan]. Data: {\"action\":\"proses_retur_po\",\"id\":\"4\",\"keputusan\":\"approve\"}', '182.1.231.136', '2026-05-04 02:00:13'),
(2880, 20, 'read_retur_po', 'Persetujuan', 'Eksekusi [read_retur_po] di menu [Persetujuan]. Data: []', '182.1.231.136', '2026-05-04 02:00:13'),
(2881, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '182.1.231.136', '2026-05-04 02:01:07'),
(2882, 20, 'get_po_receive', 'Po', 'Eksekusi [get_po_receive] di menu [Po]. Data: []', '182.1.231.136', '2026-05-04 02:01:20'),
(2883, 20, 'get_po_retur', 'Po', 'Eksekusi [get_po_retur] di menu [Po]. Data: []', '182.1.231.136', '2026-05-04 02:01:37'),
(2884, 20, 'save_retur_po', 'Po', 'Eksekusi [save_retur_po] di menu [Po]. Data: {\"action\":\"save_retur_po\",\"po_id\":\"13\",\"reason\":\"Ok\",\"items\":\"[{\\\"material_id\\\":3,\\\"material_name\\\":\\\"Coklat Batang Elmer\\\",\\\"unit\\\":\\\"Kg\\\",\\\"price\\\":\\\"12000.00\\\",\\\"qty_terima\\\":2,\\\"qty_return\\\":2},{\\\"material_id\\\":4,\\\"material_name\\\":\\\"Mentega Blueband\\\",\\\"unit\\\":\\\"Kg\\\",\\\"price\\\":\\\"12000.00\\\",\\\"qty_terima\\\":0,\\\"qty_return\\\":0}]\"}', '182.1.231.136', '2026-05-04 02:01:50'),
(2885, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '182.1.231.136', '2026-05-04 02:01:51'),
(2886, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '182.1.231.136', '2026-05-04 02:02:08'),
(2887, 20, 'read_retur_po', 'Persetujuan', 'Eksekusi [read_retur_po] di menu [Persetujuan]. Data: []', '182.1.231.136', '2026-05-04 02:02:11'),
(2888, 20, 'proses_retur_po', 'Persetujuan', 'Eksekusi [proses_retur_po] di menu [Persetujuan]. Data: {\"action\":\"proses_retur_po\",\"id\":\"5\",\"keputusan\":\"approve\"}', '182.1.231.136', '2026-05-04 02:02:15'),
(2889, 20, 'read_retur_po', 'Persetujuan', 'Eksekusi [read_retur_po] di menu [Persetujuan]. Data: []', '182.1.231.136', '2026-05-04 02:02:15'),
(2890, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '182.1.231.136', '2026-05-04 02:02:19'),
(2891, 20, 'get_po_receive', 'Po', 'Eksekusi [get_po_receive] di menu [Po]. Data: []', '182.1.231.136', '2026-05-04 02:02:29'),
(2892, 20, 'get_po_retur', 'Po', 'Eksekusi [get_po_retur] di menu [Po]. Data: []', '182.1.231.136', '2026-05-04 02:02:46'),
(2893, 20, 'save_retur_po', 'Po', 'Eksekusi [save_retur_po] di menu [Po]. Data: {\"action\":\"save_retur_po\",\"po_id\":\"13\",\"reason\":\"Ok\",\"items\":\"[{\\\"material_id\\\":3,\\\"material_name\\\":\\\"Coklat Batang Elmer\\\",\\\"unit\\\":\\\"Kg\\\",\\\"price\\\":\\\"12000.00\\\",\\\"qty_terima\\\":2,\\\"qty_return\\\":2},{\\\"material_id\\\":4,\\\"material_name\\\":\\\"Mentega Blueband\\\",\\\"unit\\\":\\\"Kg\\\",\\\"price\\\":\\\"12000.00\\\",\\\"qty_terima\\\":0,\\\"qty_return\\\":0}]\"}', '182.1.231.136', '2026-05-04 02:02:55'),
(2894, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '182.1.231.136', '2026-05-04 02:02:56'),
(2895, 20, 'read_po_approval', 'Persetujuan', 'Eksekusi [read_po_approval] di menu [Persetujuan]. Data: []', '182.1.231.136', '2026-05-04 02:03:05'),
(2896, 20, 'read_retur_po', 'Persetujuan', 'Eksekusi [read_retur_po] di menu [Persetujuan]. Data: []', '182.1.231.136', '2026-05-04 02:03:08'),
(2897, 20, 'proses_retur_po', 'Persetujuan', 'Eksekusi [proses_retur_po] di menu [Persetujuan]. Data: {\"action\":\"proses_retur_po\",\"id\":\"6\",\"keputusan\":\"approve\"}', '182.1.231.136', '2026-05-04 02:03:11'),
(2898, 20, 'read_retur_po', 'Persetujuan', 'Eksekusi [read_retur_po] di menu [Persetujuan]. Data: []', '182.1.231.136', '2026-05-04 02:03:11'),
(2899, 20, 'read_po', 'Po', 'Eksekusi [read_po] di menu [Po]. Data: []', '182.1.231.136', '2026-05-04 02:03:19'),
(2900, 20, 'get_po_receive', 'Po', 'Eksekusi [get_po_receive] di menu [Po]. Data: []', '182.1.231.136', '2026-05-04 02:03:29');

-- --------------------------------------------------------

--
-- Table structure for table `titipan_productions`
--

CREATE TABLE `titipan_productions` (
  `id` int(11) NOT NULL,
  `invoice_no` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `status` enum('pending','received','ditolak','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `titipan_productions`
--

INSERT INTO `titipan_productions` (`id`, `invoice_no`, `user_id`, `employee_id`, `warehouse_id`, `status`, `notes`, `created_at`) VALUES
(1, 'TTP-260423-001', 18, 1, 1, 'received', '', '2026-04-23 14:28:49');

-- --------------------------------------------------------

--
-- Table structure for table `titipan_production_details`
--

CREATE TABLE `titipan_production_details` (
  `id` int(11) NOT NULL,
  `titipan_production_id` int(11) NOT NULL,
  `titipan_id` int(11) NOT NULL COMMENT 'ID dari tabel barang_titipan',
  `quantity` int(11) NOT NULL,
  `barcode` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `titipan_production_details`
--

INSERT INTO `titipan_production_details` (`id`, `titipan_production_id`, `titipan_id`, `quantity`, `barcode`) VALUES
(3, 1, 2, 4, 'TTP-260423-001-1'),
(4, 1, 3, 1, 'TTP-260423-001-2');

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `name`) VALUES
(1, 'Gram'),
(2, 'Kg'),
(4, 'Liter'),
(8, 'Ml'),
(3, 'Ons');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `gudang_role_id` int(11) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `kitchen_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `password`, `role`, `gudang_role_id`, `status`, `kitchen_id`, `warehouse_id`, `created_at`) VALUES
(1, 'Bapak Owner', 'owner-produksi', '$2y$10$zL1ZzrWsObkz8kfMeJGCB.IHUXB9dirvia9Y/iMG9rTPY3qpLqJsO', 'owner', NULL, 'active', NULL, NULL, '2026-03-26 09:36:51'),
(2, 'pegawai produksi', 'produksi', '$2y$10$9vrDYAu2zA/HZ.TGZ0hd0eSl4JopCmcj0u70V1IuaNmBDZF4zIZvi', 'produksi', NULL, 'active', 1, NULL, '2026-03-26 09:36:51'),
(3, 'Citra Admin', 'admin', '$2y$10$uQ.zKHEj05TBw4W/9l2a6.pqeTRvILO47OshcGypVK3pK5vqsazv6', 'admin', NULL, 'active', NULL, NULL, '2026-03-26 09:36:51'),
(4, 'Randy', 'randy', '$2y$10$GzrBP4d8/A4n1ZtKWh4zF.fCF2oFK6jDY6CcgVFBLgk1gEfzylv4O', 'produksi', NULL, 'active', NULL, NULL, '2026-03-28 12:56:33'),
(7, 'karna', 'karna', '$2y$10$0JZy1qXn2M1i31fSgHHlJuNZqligtI0qlBCK9P8av5HlKCdBONr4S', 'supervisor_gudang', NULL, 'active', NULL, NULL, '2026-04-09 09:05:15'),
(16, 'Admin Dapur 1', 'dapur1', '$2y$10$Fv357vO5DRE1VgvlrBjT4u84L6aW3/uU/oV1a2twRvspozvCx/RTW', 'admin_dapur_1', NULL, 'active', 1, NULL, '2026-04-13 16:04:20'),
(17, 'admin dapur 2', 'dapur2', '$2y$10$.O8fl7TZ9dOVjp4kn/0HDeGAl/RXSVESyWlsEPpdcr1z78ffl80pi', 'admin_dapur_2', NULL, 'active', 2, NULL, '2026-04-13 16:05:14'),
(18, 'admin produksi dapur 1', 'produksi1', '$2y$10$a5azpqqPBdZTu3/tL9IEC.kVL8FyboNbJy5KHqYSBZibR8QRsrMbC', 'produksi', NULL, 'active', 1, NULL, '2026-04-14 17:37:40'),
(19, 'admin produksi dapur 2', 'produksi2', '$2y$10$f4jtgpbaWJbt0oCyF8xV.OceN.0Uck.yK/njnRN4ASBHmZkjyLxr.', 'produksi', NULL, 'active', 2, NULL, '2026-04-14 17:38:09'),
(20, 'Randy admin gudang', 'owner-gudang', '$2y$10$FhrDdo8uQtIYxdMV5sci9.v4pSVOUqXxIx4d/DswXMjGwepvoV0r6', 'owner_gudang', 1, 'active', NULL, NULL, '2026-04-19 14:20:51'),
(23, 'admin-gudang', 'admin-gudang', '$2y$10$yiDR1dGiJpPHrGfz5seAremH3qdDDCHa6zCkTueCwXD4I74.jdKMm', 'admin', NULL, 'active', NULL, NULL, '2026-04-22 07:23:36');

-- --------------------------------------------------------

--
-- Table structure for table `warehouses`
--

CREATE TABLE `warehouses` (
  `id` int(11) NOT NULL,
  `code` varchar(20) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('material','product') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `warehouses`
--

INSERT INTO `warehouses` (`id`, `code`, `name`, `type`) VALUES
(1, 'GDG-01', 'gudang 01', 'material'),
(2, 'GDG-02', 'Gudang 02', 'material');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `access_codes`
--
ALTER TABLE `access_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `barang_keluar`
--
ALTER TABLE `barang_keluar`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `barang_masuk`
--
ALTER TABLE `barang_masuk`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `barang_titipan`
--
ALTER TABLE `barang_titipan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `barang_titipan_keluar`
--
ALTER TABLE `barang_titipan_keluar`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bom`
--
ALTER TABLE `bom`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `material_id` (`material_id`);

--
-- Indexes for table `bom_requests`
--
ALTER TABLE `bom_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_bom_req_no` (`request_no`);

--
-- Indexes for table `bom_request_details`
--
ALTER TABLE `bom_request_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gudang_roles`
--
ALTER TABLE `gudang_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_slug` (`role_slug`);

--
-- Indexes for table `gudang_role_permissions`
--
ALTER TABLE `gudang_role_permissions`
  ADD UNIQUE KEY `gudang_role_perm_unique` (`role_id`,`permission_slug`);

--
-- Indexes for table `gudang_stok_opnames`
--
ALTER TABLE `gudang_stok_opnames`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `opname_no` (`opname_no`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `gudang_stok_opname_details`
--
ALTER TABLE `gudang_stok_opname_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `opname_id` (`opname_id`),
  ADD KEY `material_id` (`material_id`);

--
-- Indexes for table `kitchens`
--
ALTER TABLE `kitchens`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `master_lokasi_rak`
--
ALTER TABLE `master_lokasi_rak`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_rak` (`kode_rak`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `warehouse_id` (`warehouse_id`);

--
-- Indexes for table `materials_stocks`
--
ALTER TABLE `materials_stocks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `material_categories`
--
ALTER TABLE `material_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `material_opnames`
--
ALTER TABLE `material_opnames`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `material_requests`
--
ALTER TABLE `material_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `material_requests_header`
--
ALTER TABLE `material_requests_header`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_req_no` (`request_no`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `po_returns`
--
ALTER TABLE `po_returns`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `productions`
--
ALTER TABLE `productions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_no` (`invoice_no`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `warehouse_id` (`warehouse_id`);

--
-- Indexes for table `production_details`
--
ALTER TABLE `production_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD KEY `production_id` (`production_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `production_plans`
--
ALTER TABLE `production_plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_plan_per_day` (`karyawan_id`,`plan_date`);

--
-- Indexes for table `production_plan_details`
--
ALTER TABLE `production_plan_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `warehouse_id` (`warehouse_id`);

--
-- Indexes for table `product_outs`
--
ALTER TABLE `product_outs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchase_order_details`
--
ALTER TABLE `purchase_order_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchase_order_payments`
--
ALTER TABLE `purchase_order_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchase_payments`
--
ALTER TABLE `purchase_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `racks`
--
ALTER TABLE `racks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_slug` (`role_slug`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_slug` (`role_slug`);

--
-- Indexes for table `stok_opname`
--
ALTER TABLE `stok_opname`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stok_opname_details`
--
ALTER TABLE `stok_opname_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stok_opname_keys`
--
ALTER TABLE `stok_opname_keys`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `store_profile`
--
ALTER TABLE `store_profile`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `store_titipan_stocks`
--
ALTER TABLE `store_titipan_stocks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_store_titipan` (`warehouse_id`,`titipan_id`);

--
-- Indexes for table `supervisor_pins`
--
ALTER TABLE `supervisor_pins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `titipan_productions`
--
ALTER TABLE `titipan_productions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `titipan_production_details`
--
ALTER TABLE `titipan_production_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `access_codes`
--
ALTER TABLE `access_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `barang_keluar`
--
ALTER TABLE `barang_keluar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `barang_masuk`
--
ALTER TABLE `barang_masuk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `barang_titipan`
--
ALTER TABLE `barang_titipan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `barang_titipan_keluar`
--
ALTER TABLE `barang_titipan_keluar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bom`
--
ALTER TABLE `bom`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `bom_requests`
--
ALTER TABLE `bom_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bom_request_details`
--
ALTER TABLE `bom_request_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `gudang_roles`
--
ALTER TABLE `gudang_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `gudang_stok_opnames`
--
ALTER TABLE `gudang_stok_opnames`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `gudang_stok_opname_details`
--
ALTER TABLE `gudang_stok_opname_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `kitchens`
--
ALTER TABLE `kitchens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `master_lokasi_rak`
--
ALTER TABLE `master_lokasi_rak`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `materials_stocks`
--
ALTER TABLE `materials_stocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `material_categories`
--
ALTER TABLE `material_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `material_opnames`
--
ALTER TABLE `material_opnames`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `material_requests`
--
ALTER TABLE `material_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `material_requests_header`
--
ALTER TABLE `material_requests_header`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pengumuman`
--
ALTER TABLE `pengumuman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `po_returns`
--
ALTER TABLE `po_returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `productions`
--
ALTER TABLE `productions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `production_details`
--
ALTER TABLE `production_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT for table `production_plans`
--
ALTER TABLE `production_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `production_plan_details`
--
ALTER TABLE `production_plan_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `product_outs`
--
ALTER TABLE `product_outs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `purchase_order_details`
--
ALTER TABLE `purchase_order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `purchase_order_payments`
--
ALTER TABLE `purchase_order_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_payments`
--
ALTER TABLE `purchase_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `racks`
--
ALTER TABLE `racks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=585;

--
-- AUTO_INCREMENT for table `stok_opname`
--
ALTER TABLE `stok_opname`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `stok_opname_details`
--
ALTER TABLE `stok_opname_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `stok_opname_keys`
--
ALTER TABLE `stok_opname_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `store_profile`
--
ALTER TABLE `store_profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `store_titipan_stocks`
--
ALTER TABLE `store_titipan_stocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `supervisor_pins`
--
ALTER TABLE `supervisor_pins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2901;

--
-- AUTO_INCREMENT for table `titipan_productions`
--
ALTER TABLE `titipan_productions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `titipan_production_details`
--
ALTER TABLE `titipan_production_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `access_codes`
--
ALTER TABLE `access_codes`
  ADD CONSTRAINT `access_codes_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bom`
--
ALTER TABLE `bom`
  ADD CONSTRAINT `bom_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gudang_role_permissions`
--
ALTER TABLE `gudang_role_permissions`
  ADD CONSTRAINT `gudang_role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `gudang_roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gudang_stok_opnames`
--
ALTER TABLE `gudang_stok_opnames`
  ADD CONSTRAINT `gudang_stok_opnames_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `gudang_stok_opname_details`
--
ALTER TABLE `gudang_stok_opname_details`
  ADD CONSTRAINT `gudang_stok_opname_details_ibfk_1` FOREIGN KEY (`opname_id`) REFERENCES `gudang_stok_opnames` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `gudang_stok_opname_details_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `materials_stocks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `productions`
--
ALTER TABLE `productions`
  ADD CONSTRAINT `productions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `productions_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `production_details`
--
ALTER TABLE `production_details`
  ADD CONSTRAINT `production_details_ibfk_1` FOREIGN KEY (`production_id`) REFERENCES `productions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `production_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_slug`) REFERENCES `roles` (`role_slug`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD CONSTRAINT `system_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
