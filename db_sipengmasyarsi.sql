-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 23, 2025 at 03:59 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_sipengmasyarsi`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
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
-- Table structure for table `jobs`
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
-- Table structure for table `job_batches`
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
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_12_13_115203_create_panduans_table', 2),
(5, '2025_01_01_000000_add_ldap_columns_to_users_table', 3),
(6, '2025_12_16_084556_add_auth_type_to_users_table', 4),
(10, '2025_12_16_123157_create_proposal_core_atribut_table', 5),
(12, '2025_12_16_123157_create_proposal_core_identitas_table', 5),
(14, '2025_12_16_123157_create_proposal_core_uraian_table', 5),
(15, '2025_12_16_123158_create_proposal_core_pengesahan_table', 5),
(19, '2025_12_16_123157_create_proposal_core_anggota_mahasiswa_table', 9),
(22, '2025_12_16_091554_create_proposals_table', 11),
(23, '2025_12_20_104328_create_proposal_lampirans_table', 12),
(24, '2025_12_16_123157_create_proposal_core_anggota_dosen_table', 13),
(26, '2025_12_16_123157_create_proposal_core_biaya_table', 14),
(27, '2025_12_18_154124_create_proposal_persetujuan_table', 15);

-- --------------------------------------------------------

--
-- Table structure for table `panduan`
--

CREATE TABLE `panduan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `file` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `proposal`
--

CREATE TABLE `proposal` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `tahun_pelaksanaan` year(4) NOT NULL,
  `skala_pelaksanaan` varchar(30) NOT NULL,
  `skema` tinyint(3) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `proposal_core_anggota_dosen`
--

CREATE TABLE `proposal_core_anggota_dosen` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `proposal_id` bigint(20) UNSIGNED NOT NULL,
  `nidn` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `fakultas` varchar(100) DEFAULT NULL,
  `prodi` varchar(100) DEFAULT NULL,
  `peran` varchar(50) NOT NULL,
  `is_approved_dosen` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `proposal_core_anggota_mahasiswa`
--

CREATE TABLE `proposal_core_anggota_mahasiswa` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `proposal_id` bigint(20) UNSIGNED NOT NULL,
  `npm` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `prodi` varchar(100) DEFAULT NULL,
  `peran` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `proposal_core_atribut`
--

CREATE TABLE `proposal_core_atribut` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `proposal_id` bigint(20) UNSIGNED NOT NULL,
  `rumpun_ilmu` varchar(50) NOT NULL,
  `nama_institusi_mitra` varchar(50) DEFAULT NULL,
  `penanggung_jawab_mitra` varchar(50) DEFAULT NULL,
  `alamat_mitra` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `proposal_core_biaya`
--

CREATE TABLE `proposal_core_biaya` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `proposal_id` bigint(20) UNSIGNED NOT NULL,
  `honor_output` bigint(20) UNSIGNED NOT NULL,
  `belanja_non_operasional` bigint(20) UNSIGNED NOT NULL,
  `bahan_habis_pakai` bigint(20) UNSIGNED NOT NULL,
  `transportasi` bigint(20) UNSIGNED NOT NULL,
  `jumlah_tendik` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `proposal_core_identitas`
--

CREATE TABLE `proposal_core_identitas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `proposal_id` bigint(20) UNSIGNED NOT NULL,
  `judul` varchar(100) NOT NULL,
  `abstrak` varchar(500) NOT NULL,
  `keyword` varchar(150) NOT NULL,
  `periode_kegiatan` tinyint(3) UNSIGNED NOT NULL,
  `bidang_fokus` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `proposal_core_pengesahan`
--

CREATE TABLE `proposal_core_pengesahan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `proposal_id` bigint(20) UNSIGNED NOT NULL,
  `kota` varchar(50) NOT NULL,
  `jabatan_mengetahui` varchar(50) NOT NULL,
  `nama_mengetahui` varchar(50) NOT NULL,
  `nip_mengetahui` varchar(30) NOT NULL,
  `jabatan_menyetujui` varchar(50) NOT NULL,
  `nama_menyetujui` varchar(50) NOT NULL,
  `nip_menyetujui` varchar(30) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `proposal_core_uraian`
--

CREATE TABLE `proposal_core_uraian` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `proposal_id` bigint(20) UNSIGNED NOT NULL,
  `objek_pengabdian` varchar(50) NOT NULL,
  `instansi_terlibat` varchar(50) NOT NULL,
  `lokasi_pengabdian` varchar(100) NOT NULL,
  `temuan_ditargetkan` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `proposal_lampiran`
--

CREATE TABLE `proposal_lampiran` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `proposal_id` bigint(20) UNSIGNED NOT NULL,
  `kategori` enum('dokumen','artikel','sertifikat','hki') NOT NULL,
  `judul` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `proposal_persetujuan`
--

CREATE TABLE `proposal_persetujuan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `proposal_id` bigint(20) UNSIGNED NOT NULL,
  `is_approved_wadek3` tinyint(1) NOT NULL DEFAULT 0,
  `is_approved_dekan` tinyint(1) NOT NULL DEFAULT 0,
  `is_approved_warek3` tinyint(1) NOT NULL DEFAULT 0,
  `is_approved_pusat` tinyint(1) NOT NULL DEFAULT 0,
  `is_approved_admin` tinyint(1) NOT NULL DEFAULT 0,
  `feedback` varchar(255) DEFAULT NULL,
  `rejected_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
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
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('FXpvE8D9lIw1T1W4ZnnEqDeuVzOcNQbWROIr6mcj', NULL, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTGc0UWRXUGFnZ2N0MXJHYkwwUzEzbm0yUU1mNEV2cFpwbHl3TjlVMiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fX0=', 1766501879);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nidn` varchar(255) DEFAULT NULL,
  `fakultas` varchar(255) DEFAULT NULL,
  `prodi` varchar(255) DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'Dosen Pengusul',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `guid` varchar(255) DEFAULT NULL,
  `domain` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `password`, `nidn`, `fakultas`, `prodi`, `role`, `remember_token`, `created_at`, `updated_at`, `guid`, `domain`) VALUES
(1, 'Administrator', 'admin', '$2y$12$dV3m.mIB5FaQlAONqr3/qe/hJvTxWTvQKsJ2yo4dl/rwQTXqhdWPm', NULL, NULL, NULL, 'Admin', NULL, '2025-12-10 22:57:26', '2025-12-10 22:57:26', NULL, NULL),
(10, 'dosen', 'dosen', '$2y$12$NvJSsCWLMPHrDrYcL2SryuFkLsf6.z/C1lM7vc3IxLPZgKnt1pTmu', '12345678', 'Teknologi Informasi', 'Teknik Informatika', 'Dosen', NULL, '2025-12-12 02:25:15', '2025-12-23 07:33:34', NULL, NULL),
(11, 'wadek 3', 'wadek', '$2y$12$oVjcjyaQlug1v4xj4BugRuCZjH7c8YQWcKoqCUjXLghwSbmAdZUVe', '12121212121', 'Teknologi Informasi', 'Teknik Informatika', 'Wakil Dekan', NULL, '2025-12-12 18:49:20', '2025-12-12 18:49:20', NULL, NULL),
(12, 'dekan', 'dekan', '$2y$12$5SWRbCFaQEoPoG6x9.YCx.GYhdHUg1HtACPPOJO4heYinnBZr7RvC', '213241412', 'Teknologi Informasi', 'Teknik Informatika', 'Dekan', NULL, '2025-12-14 01:58:11', '2025-12-14 17:13:45', NULL, NULL),
(30, 'dosenbaru', 'dosenbaru', '$2y$12$hVb7RcM/jdz46XrccvgmMeI6OxQW1e2qgbCOE6klozIvYwDgAkfGG', '9184910490214', 'Teknologi Informasi', 'Perpustakaan dan Sains Informasi', 'Dosen', NULL, '2025-12-23 07:55:42', '2025-12-23 07:55:42', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `panduan`
--
ALTER TABLE `panduan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `proposal`
--
ALTER TABLE `proposal`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `proposal_core_anggota_dosen`
--
ALTER TABLE `proposal_core_anggota_dosen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proposal_core_anggota_dosen_proposal_id_index` (`proposal_id`);

--
-- Indexes for table `proposal_core_anggota_mahasiswa`
--
ALTER TABLE `proposal_core_anggota_mahasiswa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proposal_core_anggota_mahasiswa_proposal_id_index` (`proposal_id`);

--
-- Indexes for table `proposal_core_atribut`
--
ALTER TABLE `proposal_core_atribut`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proposal_core_atribut_proposal_id_index` (`proposal_id`);

--
-- Indexes for table `proposal_core_biaya`
--
ALTER TABLE `proposal_core_biaya`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proposal_core_biaya_proposal_id_index` (`proposal_id`);

--
-- Indexes for table `proposal_core_identitas`
--
ALTER TABLE `proposal_core_identitas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proposal_core_identitas_proposal_id_index` (`proposal_id`);

--
-- Indexes for table `proposal_core_pengesahan`
--
ALTER TABLE `proposal_core_pengesahan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proposal_core_pengesahan_proposal_id_index` (`proposal_id`);

--
-- Indexes for table `proposal_core_uraian`
--
ALTER TABLE `proposal_core_uraian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proposal_core_uraian_proposal_id_index` (`proposal_id`);

--
-- Indexes for table `proposal_lampiran`
--
ALTER TABLE `proposal_lampiran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proposal_lampiran_proposal_id_index` (`proposal_id`);

--
-- Indexes for table `proposal_persetujuan`
--
ALTER TABLE `proposal_persetujuan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proposal_persetujuan_proposal_id_index` (`proposal_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_nidn_unique` (`nidn`),
  ADD UNIQUE KEY `users_guid_unique` (`guid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `panduan`
--
ALTER TABLE `panduan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `proposal`
--
ALTER TABLE `proposal`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `proposal_core_anggota_dosen`
--
ALTER TABLE `proposal_core_anggota_dosen`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `proposal_core_anggota_mahasiswa`
--
ALTER TABLE `proposal_core_anggota_mahasiswa`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `proposal_core_atribut`
--
ALTER TABLE `proposal_core_atribut`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `proposal_core_biaya`
--
ALTER TABLE `proposal_core_biaya`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `proposal_core_identitas`
--
ALTER TABLE `proposal_core_identitas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `proposal_core_pengesahan`
--
ALTER TABLE `proposal_core_pengesahan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `proposal_core_uraian`
--
ALTER TABLE `proposal_core_uraian`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `proposal_lampiran`
--
ALTER TABLE `proposal_lampiran`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `proposal_persetujuan`
--
ALTER TABLE `proposal_persetujuan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
