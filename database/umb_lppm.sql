-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 02 Bulan Mei 2026 pada 09.42
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `umb_lppm`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `documents`
--

CREATE TABLE `documents` (
  `document_id` char(36) NOT NULL,
  `proposal_id` char(36) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `document_type` varchar(50) NOT NULL DEFAULT 'proposal',
  `uploaded_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
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
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2026_04_30_174600_create_proposals_table', 1),
(6, '2026_04_30_174700_create_proposal_members_table', 1),
(7, '2026_04_30_174800_create_documents_table', 1),
(8, '2026_04_30_174900_create_reviews_table', 1),
(9, '2026_04_30_175000_create_reports_table', 1),
(10, '2026_04_30_175100_create_partners_table', 1),
(11, '2026_04_30_175200_create_notifications_table', 1),
(12, '2026_04_30_201000_add_sso_columns_to_users_table', 2),
(13, '2026_04_30_204500_add_sso_access_columns_to_users_table', 3);

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` char(36) NOT NULL,
  `user_id` char(36) NOT NULL,
  `message` text NOT NULL,
  `channel` varchar(30) NOT NULL DEFAULT 'in_app',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `partners`
--

CREATE TABLE `partners` (
  `partner_id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `institution_type` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `personal_access_tokens`
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
-- Struktur dari tabel `proposals`
--

CREATE TABLE `proposals` (
  `proposal_id` char(36) NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL,
  `scheme` varchar(100) NOT NULL,
  `user_id` char(36) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'draft',
  `year` year(4) NOT NULL,
  `summary` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `proposal_members`
--

CREATE TABLE `proposal_members` (
  `member_id` char(36) NOT NULL,
  `proposal_id` char(36) NOT NULL,
  `user_id` char(36) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'anggota',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `proposal_partner`
--

CREATE TABLE `proposal_partner` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `proposal_id` char(36) NOT NULL,
  `partner_id` char(36) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `reports`
--

CREATE TABLE `reports` (
  `report_id` char(36) NOT NULL,
  `proposal_id` char(36) NOT NULL,
  `progress` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `reviews`
--

CREATE TABLE `reviews` (
  `review_id` char(36) NOT NULL,
  `proposal_id` char(36) NOT NULL,
  `reviewer_id` char(36) NOT NULL,
  `score` decimal(5,2) UNSIGNED DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `user_id` char(36) NOT NULL,
  `sso_user_id` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'dosen',
  `identity_number` varchar(50) DEFAULT NULL,
  `faculty` varchar(255) DEFAULT NULL,
  `study_program` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `sso_roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`sso_roles`)),
  `sso_permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`sso_permissions`)),
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`user_id`, `sso_user_id`, `username`, `full_name`, `name`, `email`, `role`, `identity_number`, `faculty`, `study_program`, `avatar`, `is_admin`, `sso_roles`, `sso_permissions`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
('a1aac784-afcd-4477-b73a-ff9f37b48631', 'c8e59090-c2b2-4568-81d9-024d379901a8', 'admin', 'admin', 'admin', 'admin@mayasaribakti.ac.id', 'admin', NULL, NULL, NULL, NULL, 1, '[\"admin\"]', '[\"api.view\",\"lppm.access.manage\",\"lppm.report.view\",\"lppm.proposal.view_all\",\"lppm.review.view\",\"lppm.proposal.submit\",\"lppm.document.upload\",\"lppm.proposal.update\",\"lecturer.view\",\"lppm.document.view\",\"employee.view\",\"lppm.access\",\"lppm.review.assign\",\"lppm.dashboard.view\",\"yayasan.view\",\"lppm.review.approve\",\"user.delete\",\"user.create\",\"settings.view\",\"user.edit\",\"lppm.report.create\",\"student.view\",\"lppm.notification.view\",\"user.view\",\"role.view\",\"lppm.proposal.create\",\"permission.view\",\"lppm.proposal.view\",\"lppm.review.score\",\"role.create\"]', NULL, '$2y$10$/N27g.T7GcFN62suF8d4Bu.5PZdm1KpFaXOjdS.0MwyoZJMyfhQLy', NULL, '2026-04-30 04:30:38', '2026-04-30 07:44:39'),
('a1ab1654-63e9-4d7c-83c1-e7cfc0f9e6c4', '8965b622-b9d4-443e-a42d-766f8275e192', 'dRief', 'Dadang Arief Nurahmat, S.Kom.', 'Dadang Arief Nurahmat, S.Kom.', 'ariefjava9@gmail.com', 'lppm_admin', '3278040711680007', NULL, NULL, 'avatar_c465f363-f1d7-4b0e-b27b-717c7b1eb715.png', 0, '[\"lppm_admin\"]', '[\"lppm.access.manage\",\"lppm.report.view\",\"lppm.proposal.view_all\",\"lppm.review.view\",\"lppm.proposal.submit\",\"lppm.document.upload\",\"lppm.proposal.update\",\"lppm.document.view\",\"lppm.access\",\"lppm.review.assign\",\"lppm.dashboard.view\",\"lppm.review.approve\",\"lppm.report.create\",\"lppm.notification.view\",\"lppm.proposal.create\",\"lppm.proposal.view\",\"lppm.review.score\"]', NULL, '$2y$10$3f7Rxc7ZO1nlS3Ilr98BCuA0QGHmv.KqBLwe0R9CZCo1CyXGTAeAu', NULL, '2026-04-30 08:11:01', '2026-04-30 08:50:39');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `documents_proposal_id_foreign` (`proposal_id`),
  ADD KEY `documents_uploaded_by_foreign` (`uploaded_by`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `notifications_user_id_foreign` (`user_id`);

--
-- Indeks untuk tabel `partners`
--
ALTER TABLE `partners`
  ADD PRIMARY KEY (`partner_id`);

--
-- Indeks untuk tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indeks untuk tabel `proposals`
--
ALTER TABLE `proposals`
  ADD PRIMARY KEY (`proposal_id`),
  ADD KEY `proposals_user_id_foreign` (`user_id`);

--
-- Indeks untuk tabel `proposal_members`
--
ALTER TABLE `proposal_members`
  ADD PRIMARY KEY (`member_id`),
  ADD KEY `proposal_members_proposal_id_foreign` (`proposal_id`),
  ADD KEY `proposal_members_user_id_foreign` (`user_id`);

--
-- Indeks untuk tabel `proposal_partner`
--
ALTER TABLE `proposal_partner`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `proposal_partner_proposal_id_partner_id_unique` (`proposal_id`,`partner_id`),
  ADD KEY `proposal_partner_partner_id_foreign` (`partner_id`);

--
-- Indeks untuk tabel `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `reports_proposal_id_foreign` (`proposal_id`);

--
-- Indeks untuk tabel `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `reviews_proposal_id_foreign` (`proposal_id`),
  ADD KEY `reviews_reviewer_id_foreign` (`reviewer_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_sso_user_id_unique` (`sso_user_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `proposal_partner`
--
ALTER TABLE `proposal_partner`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_proposal_id_foreign` FOREIGN KEY (`proposal_id`) REFERENCES `proposals` (`proposal_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `proposals`
--
ALTER TABLE `proposals`
  ADD CONSTRAINT `proposals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `proposal_members`
--
ALTER TABLE `proposal_members`
  ADD CONSTRAINT `proposal_members_proposal_id_foreign` FOREIGN KEY (`proposal_id`) REFERENCES `proposals` (`proposal_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `proposal_members_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `proposal_partner`
--
ALTER TABLE `proposal_partner`
  ADD CONSTRAINT `proposal_partner_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`partner_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `proposal_partner_proposal_id_foreign` FOREIGN KEY (`proposal_id`) REFERENCES `proposals` (`proposal_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_proposal_id_foreign` FOREIGN KEY (`proposal_id`) REFERENCES `proposals` (`proposal_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_proposal_id_foreign` FOREIGN KEY (`proposal_id`) REFERENCES `proposals` (`proposal_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
