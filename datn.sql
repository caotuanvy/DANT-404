-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th6 07, 2025 lúc 07:24 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `datn`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `banner`
--

CREATE TABLE `banner` (
  `Banner_id` int(11) NOT NULL,
  `Ten_banner` varchar(255) DEFAULT NULL,
  `Hinh_anh_1` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `binh_luan`
--

CREATE TABLE `binh_luan` (
  `binh_luan_id` int(11) NOT NULL,
  `nguoi_dung_id` int(11) DEFAULT NULL,
  `san_pham_id` int(11) DEFAULT NULL,
  `tin_tuc_id` int(11) DEFAULT NULL,
  `tieude` varchar(255) DEFAULT NULL,
  `noidung` text DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `trang_thai` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chi_tiet_don_hang`
--

CREATE TABLE `chi_tiet_don_hang` (
  `id` int(11) NOT NULL,
  `don_hang_id` int(11) DEFAULT NULL,
  `san_pham_bien_the_id` int(11) DEFAULT NULL,
  `so_luong` int(11) DEFAULT NULL,
  `don_gia` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danh_muc_san_pham`
--

CREATE TABLE `danh_muc_san_pham` (
  `category_id` int(11) NOT NULL,
  `ten_danh_muc` varchar(255) DEFAULT NULL,
  `mo_ta` text DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `ngay_tao` datetime DEFAULT NULL,
  `ngay_sua` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `danh_muc_san_pham`
--

INSERT INTO `danh_muc_san_pham` (`category_id`, `ten_danh_muc`, `mo_ta`, `slug`, `ngay_tao`, `ngay_sua`) VALUES
(1, 'gia dụng', 'đồ gia dụng', 'ok chua', NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danh_muc_tin_tuc`
--

CREATE TABLE `danh_muc_tin_tuc` (
  `id_danh_muc_tin_tuc` int(11) NOT NULL,
  `ten_danh_muc` varchar(255) DEFAULT NULL,
  `mo_ta` text DEFAULT NULL,
  `hinh_anh` varchar(100) DEFAULT NULL,
  `ngay_tao` datetime DEFAULT NULL,
  `ngay_sua` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `danh_muc_tin_tuc`
--

INSERT INTO `danh_muc_tin_tuc` (`id_danh_muc_tin_tuc`, `ten_danh_muc`, `mo_ta`, `hinh_anh`, `ngay_tao`, `ngay_sua`) VALUES
(1, 'duong', 'aaaaaaaaaaaaaaaaaa', NULL, '2025-06-03 23:10:59', '2025-06-07 17:13:16');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `dia_chi`
--

CREATE TABLE `dia_chi` (
  `id_dia_chi` int(11) NOT NULL,
  `nguoi_dung_id` int(11) DEFAULT NULL,
  `dia_chi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `doitac`
--

CREATE TABLE `doitac` (
  `Ten_doi_tac` varchar(255) NOT NULL,
  `Thuong_hieu_doi_tac` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `don_hang`
--

CREATE TABLE `don_hang` (
  `id` int(11) NOT NULL,
  `nguoi_dung_id` int(11) DEFAULT NULL,
  `phuong_thuc_thanh_toan_id` int(11) DEFAULT NULL,
  `id_giam_gia` int(11) DEFAULT NULL,
  `dia_chi_id` int(11) DEFAULT NULL,
  `trang_thai` int(11) DEFAULT NULL,
  `ghi_chu` text DEFAULT NULL,
  `phi_van_chuyen` decimal(15,2) DEFAULT NULL,
  `ngay_tao` datetime DEFAULT NULL,
  `ngay_sua` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `failed_jobs`
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
-- Cấu trúc bảng cho bảng `giam_gia`
--

CREATE TABLE `giam_gia` (
  `giam_gia_id` int(11) NOT NULL,
  `ten_giam_gia` varchar(255) DEFAULT NULL,
  `dieu_kien` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `gio_hang`
--

CREATE TABLE `gio_hang` (
  `gio_hang_id` int(11) NOT NULL,
  `nguoi_dung_id` int(11) DEFAULT NULL,
  `ngay_tao` datetime DEFAULT NULL,
  `ngay_sua` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `gio_hang_chi_tiet`
--

CREATE TABLE `gio_hang_chi_tiet` (
  `gio_hang_chi_tiet_id` int(11) NOT NULL,
  `gio_hang_id` int(11) DEFAULT NULL,
  `san_pham_bien_the_id` int(11) DEFAULT NULL,
  `so_luong` int(11) DEFAULT NULL,
  `don_gia` decimal(15,2) DEFAULT NULL,
  `ngay_tao` datetime DEFAULT NULL,
  `ngay_sua` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hinh_anh_san_pham`
--

CREATE TABLE `hinh_anh_san_pham` (
  `hinh_anh_id` int(11) NOT NULL,
  `san_pham_id` int(11) DEFAULT NULL,
  `duongdan` varchar(255) DEFAULT NULL,
  `mo_ta` text DEFAULT NULL,
  `ngay_tao` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `hinh_anh_san_pham`
--

INSERT INTO `hinh_anh_san_pham` (`hinh_anh_id`, `san_pham_id`, `duongdan`, `mo_ta`, `ngay_tao`) VALUES
(5, 8, 'products/h3gblHPW2ZjXThhPvlrdVyRbJXHurwEXLnHnrKwK.png', NULL, '2025-06-07 11:50:51');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hinh_anh_tin_tuc`
--

CREATE TABLE `hinh_anh_tin_tuc` (
  `hinh_anh_id` int(11) NOT NULL,
  `tin_tuc_id` int(11) DEFAULT NULL,
  `duongdan` varchar(255) DEFAULT NULL,
  `mo_ta` text DEFAULT NULL,
  `ngay_tao` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `jobs`
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
-- Cấu trúc bảng cho bảng `job_batches`
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
-- Cấu trúc bảng cho bảng `logo`
--

CREATE TABLE `logo` (
  `Logo_id` int(11) NOT NULL,
  `Ten_logo` varchar(255) DEFAULT NULL,
  `Hinh_anh_logo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `mang_xa_hoi`
--

CREATE TABLE `mang_xa_hoi` (
  `Mang_Xa_Hoi_id` int(11) NOT NULL,
  `Ten_mang_xa_hoi` varchar(255) DEFAULT NULL,
  `Link_mang_xa_hoi` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_06_04_151247_create_personal_access_tokens_table', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nguoi_dung`
--

CREATE TABLE `nguoi_dung` (
  `nguoi_dung_id` int(11) NOT NULL,
  `ho_ten` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `sdt` varchar(20) DEFAULT NULL,
  `mat_khau` varchar(255) DEFAULT NULL,
  `anh_dai_dien` varchar(255) DEFAULT NULL,
  `vai_tro_id` int(11) DEFAULT NULL,
  `trang_thai` int(11) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `ngay_tao` datetime DEFAULT NULL,
  `ngay_sua` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `nguoi_dung`
--

INSERT INTO `nguoi_dung` (`nguoi_dung_id`, `ho_ten`, `email`, `sdt`, `mat_khau`, `anh_dai_dien`, `vai_tro_id`, `trang_thai`, `slug`, `ngay_tao`, `ngay_sua`) VALUES
(1, 'Cao tuấn Vỹ', 'vyctps41133@gmail.com', NULL, '$2y$12$qwPl09xReZZx0MV.wFP.Tuc.zBV/1uPPK0NJfZ8LoVqRESZ5SOBW.', NULL, 1, NULL, NULL, '2025-06-06 06:57:59', NULL),
(2, 'dương', 'vyctps411333@gmail.com', '0363994247', '$2y$12$vdmaTh7z7jv/9Lc6OHn8tugbvWt1EMMTNSsEjBViQ94je8blqpAPq', NULL, 1, 1, NULL, '2025-06-06 16:22:24', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `personal_access_tokens`
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

--
-- Đang đổ dữ liệu cho bảng `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\NguoiDung', 1, 'api-token', '9fefa025426ea94d13854b2be2ac26b1a9b870ade459e25e46c26efedfd5efc3', '[\"*\"]', '2025-06-06 00:05:40', NULL, '2025-06-06 00:00:36', '2025-06-06 00:05:40'),
(2, 'App\\Models\\NguoiDung', 1, 'api-token', 'ad48acd7e39bf620785f7ebd4f60ce3a57c1baf839d582398fa350f07757b5eb', '[\"*\"]', '2025-06-06 06:31:36', NULL, '2025-06-06 00:21:24', '2025-06-06 06:31:36'),
(3, 'App\\Models\\NguoiDung', 1, 'api-token', 'f875dd00f04be34b9e574a4615cddcfd9ebbada916c976e323625103d4aa85ec', '[\"*\"]', '2025-06-06 04:45:53', NULL, '2025-06-06 04:17:12', '2025-06-06 04:45:53'),
(4, 'App\\Models\\NguoiDung', 1, 'api-token', '740aaa7b280be7ce27167ec5fa08025a7ba4cbd759d42e61aed7b5ce5abf6e24', '[\"*\"]', '2025-06-06 05:14:18', NULL, '2025-06-06 04:46:46', '2025-06-06 05:14:18'),
(5, 'App\\Models\\NguoiDung', 1, 'api-token', 'd61b95ee7b6aff4c0bf89760af73bff34db6c9df1e641dd950e2753cce51ed4f', '[\"*\"]', NULL, NULL, '2025-06-06 04:54:28', '2025-06-06 04:54:28'),
(6, 'App\\Models\\NguoiDung', 1, 'api-token', '63b07fc447a32d5a7316a54c0e4e85045a1b4dff197c05f90d0f3e86ad671e25', '[\"*\"]', '2025-06-06 06:03:27', NULL, '2025-06-06 04:55:43', '2025-06-06 06:03:27'),
(7, 'App\\Models\\NguoiDung', 1, 'api-token', 'c0e4dc25dcc5703d1bf178ba1bc42a6895b2d88af78cc93c5e624ebbe28cf68e', '[\"*\"]', '2025-06-06 05:02:44', NULL, '2025-06-06 05:02:30', '2025-06-06 05:02:44'),
(8, 'App\\Models\\NguoiDung', 1, 'api-token', '55a9f218a8606e1db797800b0ff9771caa530a3a2a10048f461dc777ff6fcbdd', '[\"*\"]', NULL, NULL, '2025-06-06 05:14:42', '2025-06-06 05:14:42'),
(9, 'App\\Models\\NguoiDung', 1, 'api-token', 'd865249837caf8e85458519d2e11e23dc509c7ffef519bf91623a8ede19626c0', '[\"*\"]', NULL, NULL, '2025-06-06 06:00:29', '2025-06-06 06:00:29'),
(10, 'App\\Models\\NguoiDung', 1, 'api-token', '478d71cdc26414b385263856d11861fa80b1924de28ba58133e00e62ad11e392', '[\"*\"]', NULL, NULL, '2025-06-06 06:03:16', '2025-06-06 06:03:16'),
(11, 'App\\Models\\NguoiDung', 1, 'api-token', '5ccf3e195239ac2e5f8d50e508bd2807d11623e31377f8d41e7c66198c657682', '[\"*\"]', NULL, NULL, '2025-06-06 06:04:23', '2025-06-06 06:04:23'),
(12, 'App\\Models\\NguoiDung', 1, 'api-token', 'c788cef1eec1964784d3a1092f09099236e8aa1d6a1810b02aafd124b70dbec3', '[\"*\"]', NULL, NULL, '2025-06-06 06:04:57', '2025-06-06 06:04:57'),
(13, 'App\\Models\\NguoiDung', 1, 'api-token', 'd2c1b385741fc321ecca8c1f8699947fe2931dc0bfa1dba96842df6ab9dff45a', '[\"*\"]', NULL, NULL, '2025-06-06 06:29:23', '2025-06-06 06:29:23'),
(14, 'App\\Models\\NguoiDung', 1, 'api-token', '6539925e4fa197c44e2631743021dfbe852b4a734e769581a8e5027d4eb3430c', '[\"*\"]', '2025-06-06 06:36:00', NULL, '2025-06-06 06:35:44', '2025-06-06 06:36:00'),
(15, 'App\\Models\\NguoiDung', 1, 'api-token', '70b56e7e24fa496b35442da8bc3ec8d6c06e4e8bd26fdc220b3f585d0d8367d2', '[\"*\"]', '2025-06-06 07:09:58', NULL, '2025-06-06 06:44:27', '2025-06-06 07:09:58'),
(16, 'App\\Models\\NguoiDung', 1, 'api-token', 'd300de2530ace1d5927c9cd83737a04977161e81e75773d7f12b6434ba104175', '[\"*\"]', NULL, NULL, '2025-06-06 06:58:06', '2025-06-06 06:58:06'),
(17, 'App\\Models\\NguoiDung', 1, 'api-token', 'a745f382312656fbfb6f94dcf524fe6986cbdd34972f396bfd9beff678a56149', '[\"*\"]', NULL, NULL, '2025-06-06 07:05:57', '2025-06-06 07:05:57'),
(18, 'App\\Models\\NguoiDung', 1, 'api-token', '4cbb4752770b227c95b7d9b21a7d824eeb09b02e7de2458ac972f084f4fadd55', '[\"*\"]', NULL, NULL, '2025-06-06 07:09:24', '2025-06-06 07:09:24'),
(19, 'App\\Models\\NguoiDung', 1, 'api-token', 'e1c102e1dacdd2911777f39578b6e273a9c7beaeb6dcb13721707163524ad9f0', '[\"*\"]', '2025-06-06 07:37:31', NULL, '2025-06-06 07:28:58', '2025-06-06 07:37:31'),
(20, 'App\\Models\\NguoiDung', 1, 'api-token', '4e1a43212a005c7fb7e411758cf7e0d03ae5f8bef622362a08ac940f134ec983', '[\"*\"]', NULL, NULL, '2025-06-06 07:41:30', '2025-06-06 07:41:30'),
(21, 'App\\Models\\NguoiDung', 1, 'api-token', '8c6b772d7d05fb3a2c2e55c0e97767b43d26cbea91c44b74834e9effc9c39f23', '[\"*\"]', '2025-06-06 07:41:46', NULL, '2025-06-06 07:41:35', '2025-06-06 07:41:46'),
(22, 'App\\Models\\User', 1, 'api_token', 'f5efb2ee8266616e8a754b97e662cb85c39ca0fa6235580ccf720ff1ee207420', '[\"*\"]', '2025-06-06 09:19:49', NULL, '2025-06-06 09:17:22', '2025-06-06 09:19:49'),
(23, 'App\\Models\\User', 2, 'api_token', '0c2cc4c3a84de231aec9c006b869d78266329000aac422e41e3b7d2b1ee416db', '[\"*\"]', '2025-06-06 10:17:38', NULL, '2025-06-06 09:22:24', '2025-06-06 10:17:38'),
(24, 'App\\Models\\User', 2, 'api_token', '119564bdb91ad61ab29e8c7548e766a5abc4f9355a00129dbc72bc7878ed1f93', '[\"*\"]', '2025-06-06 19:06:27', NULL, '2025-06-06 19:06:23', '2025-06-06 19:06:27'),
(25, 'App\\Models\\User', 2, 'api_token', 'c642620eba5bd8557dd31d6893bc674814ca3717650c3549b32d8b4dbd172730', '[\"*\"]', '2025-06-06 19:06:31', NULL, '2025-06-06 19:06:25', '2025-06-06 19:06:31'),
(26, 'App\\Models\\User', 2, 'api_token', '36c10ce516550f7faeeaa9f1a01afba5be19ad22aff4b39a529676394b23df3f', '[\"*\"]', '2025-06-06 19:06:36', NULL, '2025-06-06 19:06:28', '2025-06-06 19:06:36'),
(27, 'App\\Models\\User', 2, 'api_token', 'f6c49636b62e7ae8d235302b1012fe7122d53a8e8f38f1ace7751e2f174ddee1', '[\"*\"]', '2025-06-06 19:06:36', NULL, '2025-06-06 19:06:29', '2025-06-06 19:06:36'),
(28, 'App\\Models\\User', 2, 'api_token', 'b7975fa9b11c7a164cb39ee379a660bc1f289a7ef6b7ef107b3a48be3a72d2f7', '[\"*\"]', '2025-06-06 19:06:37', NULL, '2025-06-06 19:06:31', '2025-06-06 19:06:37'),
(29, 'App\\Models\\User', 2, 'api_token', '77556f2aa0193a9bd185ddf04d4d13aefb16fa8d31d8c482bb0b2ba72c448898', '[\"*\"]', '2025-06-06 19:06:37', NULL, '2025-06-06 19:06:33', '2025-06-06 19:06:37'),
(30, 'App\\Models\\User', 2, 'api_token', '4494b9cde068d0580afe3d542f0d64a71bf84c9d1aa91c3243e265611b0c5325', '[\"*\"]', '2025-06-06 19:06:38', NULL, '2025-06-06 19:06:34', '2025-06-06 19:06:38'),
(31, 'App\\Models\\User', 2, 'api_token', 'ce11ffb7914238642c7561fbdf40d664781b0343087d51e0669a4f7353c5ec08', '[\"*\"]', '2025-06-06 19:27:17', NULL, '2025-06-06 19:06:35', '2025-06-06 19:27:17'),
(32, 'App\\Models\\User', 2, 'api_token', '10b0a456dccbf5d88b5f1defcafc9e255df7c3b80b762371bf6060d5c0204da5', '[\"*\"]', '2025-06-06 19:47:23', NULL, '2025-06-06 19:27:53', '2025-06-06 19:47:23'),
(33, 'App\\Models\\User', 2, 'api_token', '53ca1134dedfd3c4055b28b39c0bccd0ff01c65a3316045a4682812cc618e2ad', '[\"*\"]', '2025-06-06 19:50:11', NULL, '2025-06-06 19:50:08', '2025-06-06 19:50:11'),
(34, 'App\\Models\\User', 2, 'api_token', 'ff27843041d02f6f6be248475aa18ffd703d92d1a47a2bc476f31bd7effcb837', '[\"*\"]', '2025-06-07 00:11:02', NULL, '2025-06-06 19:50:10', '2025-06-07 00:11:02'),
(35, 'App\\Models\\User', 2, 'api_token', '6fac22b3ea82078c4fc92abbc08c0fe090e3c8822187b339c68e3f7f8de7b4c8', '[\"*\"]', '2025-06-07 05:14:17', NULL, '2025-06-07 00:11:09', '2025-06-07 05:14:17'),
(36, 'App\\Models\\User', 2, 'api_token', 'c5fda3a4e1eec5b99bb330f3a1cf61f68083ebba9e4f2292c123c09f6b438037', '[\"*\"]', '2025-06-07 05:41:31', NULL, '2025-06-07 05:41:30', '2025-06-07 05:41:31'),
(37, 'App\\Models\\User', 2, 'api_token', '50c05529e656dd88e87bfc4e4306e5a87c28d14cca19ce4ca471179f618ce534', '[\"*\"]', '2025-06-07 06:45:45', NULL, '2025-06-07 05:41:32', '2025-06-07 06:45:45'),
(38, 'App\\Models\\User', 2, 'api_token', 'd61a939bef041501b2fb99c713254a79e035d4c69b090bd90bde9e02c93c51b5', '[\"*\"]', NULL, NULL, '2025-06-07 08:26:37', '2025-06-07 08:26:37'),
(39, 'App\\Models\\User', 2, 'api_token', 'd0a1d8923db252fb2aba57ade69346f3bfd6b29929ee1b154c426bdbd5a04164', '[\"*\"]', NULL, NULL, '2025-06-07 08:26:37', '2025-06-07 08:26:37'),
(40, 'App\\Models\\User', 2, 'api_token', '248a8d9dc14b0e0af06b8f095fea036e40456caa6567c13d6bc42a72b013644d', '[\"*\"]', NULL, NULL, '2025-06-07 08:26:38', '2025-06-07 08:26:38'),
(41, 'App\\Models\\User', 2, 'api_token', '38aab867b1684a2acd480a82332ae093d0360513439296faf5189fd046272818', '[\"*\"]', NULL, NULL, '2025-06-07 08:26:39', '2025-06-07 08:26:39'),
(42, 'App\\Models\\User', 2, 'api_token', '4b0fbee6c044637fadc9c1db3548dee072a52cc83830cbed138560c183212e69', '[\"*\"]', '2025-06-07 08:27:18', NULL, '2025-06-07 08:27:16', '2025-06-07 08:27:18'),
(43, 'App\\Models\\User', 2, 'api_token', 'ea80b69ee35ae7af8c5a2d216d36f65f5c72e2dfb353cdb932ebc842f6d72005', '[\"*\"]', '2025-06-07 08:27:19', NULL, '2025-06-07 08:27:17', '2025-06-07 08:27:19'),
(44, 'App\\Models\\User', 2, 'api_token', '15b3ce1d9834ecfaca39571364c7298af5c804de036a542f18d2124219ac837b', '[\"*\"]', '2025-06-07 08:27:20', NULL, '2025-06-07 08:27:18', '2025-06-07 08:27:20'),
(45, 'App\\Models\\User', 2, 'api_token', '81edae73becca779a07d92c0f7dcbba3785f4f8c44e68900d6581e64d7f6af34', '[\"*\"]', '2025-06-07 08:27:20', NULL, '2025-06-07 08:27:19', '2025-06-07 08:27:20'),
(46, 'App\\Models\\User', 2, 'api_token', '8c5d918889cd24998800f7418cbeb792983211d51229ebbb7e50f5e27c9c10ae', '[\"*\"]', '2025-06-07 10:21:13', NULL, '2025-06-07 08:27:19', '2025-06-07 10:21:13');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phuong_thuc_thanh_toan`
--

CREATE TABLE `phuong_thuc_thanh_toan` (
  `phuong_thuc_thanh_toan_id` int(11) NOT NULL,
  `ten_pttt` varchar(255) DEFAULT NULL,
  `trang_thai` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `san_pham`
--

CREATE TABLE `san_pham` (
  `san_pham_id` int(11) NOT NULL,
  `ten_san_pham` varchar(255) DEFAULT NULL,
  `mo_ta` text DEFAULT NULL,
  `noi_bat` tinyint(1) DEFAULT NULL,
  `khuyen_mai` text DEFAULT NULL,
  `ngay_tao` datetime DEFAULT NULL,
  `ngay_sua` datetime DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `ten_danh_muc_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `san_pham`
--

INSERT INTO `san_pham` (`san_pham_id`, `ten_san_pham`, `mo_ta`, `noi_bat`, `khuyen_mai`, `ngay_tao`, `ngay_sua`, `slug`, `ten_danh_muc_id`) VALUES
(1, 'duong', 'duong', 1, '20', '2025-06-06 23:57:27', NULL, 'ok', 1),
(2, 'Cao Tuaanss Vỹ', 'ddđ', 1, NULL, NULL, NULL, NULL, 1),
(3, 'Hồ Quốc Nam', 'Cặc Nam To Lắm', NULL, NULL, NULL, NULL, NULL, 1),
(4, 'jasrwdhoapyn', '444aa', NULL, NULL, NULL, NULL, NULL, 1),
(5, 'ok chưa', 'dddd', NULL, NULL, NULL, NULL, NULL, 1),
(6, 'ok chưa', 'dddd', NULL, NULL, NULL, NULL, NULL, 1),
(7, 'Cao Tuấn Vỹ', 'dđ', NULL, NULL, NULL, NULL, NULL, 1),
(8, 'Cao Tuấn Vỹ', 'ddd', NULL, NULL, NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `san_pham_bien_the`
--

CREATE TABLE `san_pham_bien_the` (
  `bien_the_id` int(11) NOT NULL,
  `san_pham_id` int(11) DEFAULT NULL,
  `kich_thuoc` varchar(100) DEFAULT NULL,
  `mau_sac` varchar(100) DEFAULT NULL,
  `so_luong_ton_kho` int(11) DEFAULT NULL,
  `gia` decimal(15,2) DEFAULT NULL,
  `ngay_tao` datetime DEFAULT NULL,
  `ngay_sua` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sessions`
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
-- Đang đổ dữ liệu cho bảng `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('0STgg1gVwBLfGtQ0qQEDmba7JM94nLP3ZXxzhcom', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoia0psVjRJTTFNcE92MlY0Z0hrOFFwdjBSMDFJSmpQSVNDcm93R3Y3MCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749223481),
('15Mox4lFd4WqkYAgFjBy7maPms1GzdO7HgSXgIGa', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiZHU2dU9leTd0WUFqOEROZjlzNWVISDJKTldrV0pjSFB1RXl1YzRPdyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749221555),
('2oybZlNjGga1GTyzxQoRR6baBcNYKRh2TB2mCc72', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiYVF1NW5JNjdGZm5jazU3a1JVNXQwR3VMSEpKZDNySVNsVVl6WTdBciI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749223466),
('4KdDIaVAZXADzCn91yKcE8uXfJVgveUViopUSK2E', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiUzI3a1JWOVZkN1hVb3htTE1TS3p1YU1iYUdScUtsUE5xalBWSGdYWSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749221554),
('7q0cnCcDJ57Z9dIu4Akac7fmj5FD0u3t3RZpKSW5', NULL, '127.0.0.1', 'PostmanRuntime/7.39.1', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiM0ZzZnl0RDd3MVBoUUQ4WGNmbTk4enZYZjgxZ0tVVkxKNkhKU3F1RCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749219707),
('8I28FCSzXh3dlfP6IZrJY4dBdrDMVWTIIFt5qXq7', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiVzdkRWpUaURib0gyMmpMcnVMZkVCME50ZFZ1OGd3cnJnZGZGT3dLTCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749223466),
('botmSNp7T5gmLVQjIM1aS7y2WRk0eOiKsYaHnar7', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoidXM1RXZHY3FaanU2aGFjalRGcVFLNEY2TW5STUdGazJOT2tFREk1aSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749221825),
('bx21D66FaveCwNFPSeeUQgJlIawxkjUrGfi05lP8', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiUUFiQVhZUVZ4SnQ5MVNPaFl6R3lBWHFLTWkzUU1aZlZiWHBLWmljZCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749221560),
('Cd9TulJSXl8EO3gWIkVs49ZmOJGI4nlhrBTChz8X', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidHNyNE93aFBtWVp0WGVPS0QxbEdTWFdFcVFONjBZMlVuTWFicjRrUCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zYW5jdHVtL2NzcmYtY29va2llIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749221985),
('cOeeql2yn62ixh5JWh0CSfqEBtJCuGOu5UUnZVLa', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiSDFUS29XSlVXc0hwN0FHU1pmRU5lZnFmQmtseUVTU3NXOVh4OHN3dSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749223342),
('DtWa5pupHzjbwr7i3ddIiVo5Nt7iTg4ZiW5dJGdD', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoia1c3Vko3SVNlZ2VOdHpVUm9GU2hhSGsyZjN2cGlpdER3YzVsUlpHSyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749221824),
('ej5PK03RaOcn0bpF98nP374Ij5NZldXBL3v9Kt6l', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiaFN1VVJ3UDltQW00OU1XdHVvblFiRnBwa1pUdktxQU9MZ3VGSkdNMCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvbmd1b2ktZHVuZyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1749222786),
('FbWPaDQiTlYLi2NTc72pH7J8nUjbxehWV0TgaGYR', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiYnRxcUhyS2hndlV3YnlmMWhxZVpOTmdtUHdnaEZHWWRRdTZIRXZtUyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749221823),
('gfTQhrjfLaMO4GlAawojf9Fkk0h3Cievx0YrCYeW', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoieUNRUG5YN1didmI5Y1VLam5PZVJHRWZFckhrY1h5RzVZZkg2N0xDdiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749223228),
('hTtVVHmsY5nIA9NKUbt1J7xqxbLJKKaXNXoabP6C', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiN1UxQk93dTVkS3N3dVV6eUwxT1FvNG44WE5VaXFxQkdtazV5WmVmcyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zYW5jdHVtL2NzcmYtY29va2llIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749224019),
('HwDActMkGH5iEffQEnhVBAcCGhwSSIo2Wr5Z0dWe', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiU3hKWXZGNHZjM0hMVXpaMkdSeDBxRk1nMDNlZ3dtWkZQclRqcVBHRyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749223462),
('J4yGzlcXI9W677ElNQozhqhxX9qBCBoed69lLpFj', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiWWVCMm00M296NXkwc1h1ZkxrOE0xV3lyVmdZdm9pdkQ5c0Zubm0wOSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749221555),
('KMkiDh1W6QsHGIpGRggJBmMt3y8K1LGXBOiQswSJ', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiM0I0RkhaeUpFZ0t3U0hZdldTRWNCNEhhODIwNjUzUXZWVVZxRTFKMSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749223339),
('o6IPIUhsrPn7YTNOLuIpq6RxwfnmLRCU9GtxV8JR', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoianM4Ym1WQmRYaVp2SnNBT3Y4WHNvak1pTDBVM1h1TEEydnhISDAxaSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749223221),
('oiHOok16QUb5sRAdF1Ec0c4QJZ8xtetqfAz1CwsC', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiUEdCMGsyQ00xSUxpRTFMVnVrT3paT0ZNejIwRU5vbEJmUmhXTXhWNyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749223250),
('pwBHN64BOyCupy7UreZZmLAXUXdgbkMMo5Ih9aQC', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidVdFM2M5aWhTYldoY1E2M1ZyYmxCZE02eU1aUTlhOEdma1MxTHppVSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zYW5jdHVtL2NzcmYtY29va2llIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749224215),
('QfjC2MuYAm33Vl2L0E7HjYg5rqHwvu67wkyzv0Qz', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoid1RNZ3VhTTFBcnRWeWMxa3NheEdQRGpRV0FQQXJmVzI2bUJ4MktxViI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1749216013),
('QNbYcN1qaDMBjHMn1JISlicCfRVLceU9HdSsnCZn', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiV0d4eG1YaWNUaThQNWphV1JHVjhrQ1VQSENwb1lsbVFUNlBvazNOUiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvaGVsbG8iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1749222587),
('R69aJVSHyGoC2vG6XJmzWRVTN167z3DNGDNO3Gzj', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiQ1dGc0phOVZRR01aTERtMzl2MWlLY1l1aDRGb1NWb3c1aDh1T1EzTyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749223345),
('Rrm2GcoKqGmB4XCXSZYPNk9xqkro1OyiiN7BJaPG', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZkhLSlZINDA0RURjWm8xWGxMVkJRbGQyYUpRUGpJVjRSY2NzZXc5dCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvaGVsbG8iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1749222587),
('v9gV1lEuUwSjN4wl9KkYtYnIU8hysVkKokMRvLvn', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiQlhZejdtWW5HMGVSNk9nZmZ0cE5tM0ZNZnVKMjVtQUlXU2xnczZHbSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749221555),
('XgxovdJOH1nCxZwDIgeNAiaxcdev8gr1dIBTswMO', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNVNtNG5qNkdUVGI4R0xqSXVUZjk0TzVkQjA3ZDZod3NkdWhKZ2VhYiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1749222736),
('zEMJdb8Te0UWjiLpM4tofvMkagIhVLaac53IwJKc', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiQnRuWUJScVByeEkyWjcwbnBjcmExMmIyTTV5emhVdlpad05oTmxyVSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749221554);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `slide_show`
--

CREATE TABLE `slide_show` (
  `Slide_id` int(11) NOT NULL,
  `Ten_slide` varchar(255) DEFAULT NULL,
  `Hinh_anh_1` varchar(255) DEFAULT NULL,
  `Hinh_anh_2` varchar(255) DEFAULT NULL,
  `Hinh_anh_3` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tintuc`
--

CREATE TABLE `tintuc` (
  `id` int(11) NOT NULL,
  `id_danh_muc_tin_tuc` int(11) DEFAULT NULL,
  `tieude` varchar(255) DEFAULT NULL,
  `noidung` text DEFAULT NULL,
  `ngay_dang` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `trang_tinh`
--

CREATE TABLE `trang_tinh` (
  `Trang_tinh_id` int(11) NOT NULL,
  `Ten_trang` varchar(255) DEFAULT NULL,
  `Tieu_de_trang` varchar(255) DEFAULT NULL,
  `Noi_dung_trang` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `vai_tro`
--

CREATE TABLE `vai_tro` (
  `vai_tro_id` int(11) NOT NULL,
  `ten` varchar(100) DEFAULT NULL,
  `mo_ta` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `vai_tro`
--

INSERT INTO `vai_tro` (`vai_tro_id`, `ten`, `mo_ta`) VALUES
(1, 'admin', 'quản trị viên'),
(2, 'user', 'người dung\r\n');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `banner`
--
ALTER TABLE `banner`
  ADD PRIMARY KEY (`Banner_id`);

--
-- Chỉ mục cho bảng `binh_luan`
--
ALTER TABLE `binh_luan`
  ADD PRIMARY KEY (`binh_luan_id`),
  ADD KEY `nguoi_dung_id` (`nguoi_dung_id`),
  ADD KEY `san_pham_id` (`san_pham_id`),
  ADD KEY `tin_tuc_id` (`tin_tuc_id`);

--
-- Chỉ mục cho bảng `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Chỉ mục cho bảng `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Chỉ mục cho bảng `chi_tiet_don_hang`
--
ALTER TABLE `chi_tiet_don_hang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `don_hang_id` (`don_hang_id`),
  ADD KEY `san_pham_bien_the_id` (`san_pham_bien_the_id`);

--
-- Chỉ mục cho bảng `danh_muc_san_pham`
--
ALTER TABLE `danh_muc_san_pham`
  ADD PRIMARY KEY (`category_id`);

--
-- Chỉ mục cho bảng `danh_muc_tin_tuc`
--
ALTER TABLE `danh_muc_tin_tuc`
  ADD PRIMARY KEY (`id_danh_muc_tin_tuc`);

--
-- Chỉ mục cho bảng `dia_chi`
--
ALTER TABLE `dia_chi`
  ADD PRIMARY KEY (`id_dia_chi`),
  ADD KEY `nguoi_dung_id` (`nguoi_dung_id`);

--
-- Chỉ mục cho bảng `doitac`
--
ALTER TABLE `doitac`
  ADD PRIMARY KEY (`Ten_doi_tac`);

--
-- Chỉ mục cho bảng `don_hang`
--
ALTER TABLE `don_hang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nguoi_dung_id` (`nguoi_dung_id`),
  ADD KEY `phuong_thuc_thanh_toan_id` (`phuong_thuc_thanh_toan_id`),
  ADD KEY `id_giam_gia` (`id_giam_gia`),
  ADD KEY `dia_chi_id` (`dia_chi_id`);

--
-- Chỉ mục cho bảng `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Chỉ mục cho bảng `giam_gia`
--
ALTER TABLE `giam_gia`
  ADD PRIMARY KEY (`giam_gia_id`);

--
-- Chỉ mục cho bảng `gio_hang`
--
ALTER TABLE `gio_hang`
  ADD PRIMARY KEY (`gio_hang_id`),
  ADD KEY `nguoi_dung_id` (`nguoi_dung_id`);

--
-- Chỉ mục cho bảng `gio_hang_chi_tiet`
--
ALTER TABLE `gio_hang_chi_tiet`
  ADD PRIMARY KEY (`gio_hang_chi_tiet_id`),
  ADD KEY `gio_hang_id` (`gio_hang_id`),
  ADD KEY `san_pham_bien_the_id` (`san_pham_bien_the_id`);

--
-- Chỉ mục cho bảng `hinh_anh_san_pham`
--
ALTER TABLE `hinh_anh_san_pham`
  ADD PRIMARY KEY (`hinh_anh_id`),
  ADD KEY `san_pham_id` (`san_pham_id`);

--
-- Chỉ mục cho bảng `hinh_anh_tin_tuc`
--
ALTER TABLE `hinh_anh_tin_tuc`
  ADD PRIMARY KEY (`hinh_anh_id`),
  ADD KEY `tin_tuc_id` (`tin_tuc_id`);

--
-- Chỉ mục cho bảng `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Chỉ mục cho bảng `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `logo`
--
ALTER TABLE `logo`
  ADD PRIMARY KEY (`Logo_id`);

--
-- Chỉ mục cho bảng `mang_xa_hoi`
--
ALTER TABLE `mang_xa_hoi`
  ADD PRIMARY KEY (`Mang_Xa_Hoi_id`);

--
-- Chỉ mục cho bảng `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  ADD PRIMARY KEY (`nguoi_dung_id`),
  ADD KEY `vai_tro_id` (`vai_tro_id`);

--
-- Chỉ mục cho bảng `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Chỉ mục cho bảng `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Chỉ mục cho bảng `phuong_thuc_thanh_toan`
--
ALTER TABLE `phuong_thuc_thanh_toan`
  ADD PRIMARY KEY (`phuong_thuc_thanh_toan_id`);

--
-- Chỉ mục cho bảng `san_pham`
--
ALTER TABLE `san_pham`
  ADD PRIMARY KEY (`san_pham_id`),
  ADD KEY `ten_danh_muc_id` (`ten_danh_muc_id`);

--
-- Chỉ mục cho bảng `san_pham_bien_the`
--
ALTER TABLE `san_pham_bien_the`
  ADD PRIMARY KEY (`bien_the_id`),
  ADD KEY `san_pham_id` (`san_pham_id`);

--
-- Chỉ mục cho bảng `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Chỉ mục cho bảng `slide_show`
--
ALTER TABLE `slide_show`
  ADD PRIMARY KEY (`Slide_id`);

--
-- Chỉ mục cho bảng `tintuc`
--
ALTER TABLE `tintuc`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_danh_muc_tin_tuc` (`id_danh_muc_tin_tuc`);

--
-- Chỉ mục cho bảng `trang_tinh`
--
ALTER TABLE `trang_tinh`
  ADD PRIMARY KEY (`Trang_tinh_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Chỉ mục cho bảng `vai_tro`
--
ALTER TABLE `vai_tro`
  ADD PRIMARY KEY (`vai_tro_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `banner`
--
ALTER TABLE `banner`
  MODIFY `Banner_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `binh_luan`
--
ALTER TABLE `binh_luan`
  MODIFY `binh_luan_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `chi_tiet_don_hang`
--
ALTER TABLE `chi_tiet_don_hang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `danh_muc_san_pham`
--
ALTER TABLE `danh_muc_san_pham`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `danh_muc_tin_tuc`
--
ALTER TABLE `danh_muc_tin_tuc`
  MODIFY `id_danh_muc_tin_tuc` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `dia_chi`
--
ALTER TABLE `dia_chi`
  MODIFY `id_dia_chi` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `don_hang`
--
ALTER TABLE `don_hang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `giam_gia`
--
ALTER TABLE `giam_gia`
  MODIFY `giam_gia_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `gio_hang`
--
ALTER TABLE `gio_hang`
  MODIFY `gio_hang_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `gio_hang_chi_tiet`
--
ALTER TABLE `gio_hang_chi_tiet`
  MODIFY `gio_hang_chi_tiet_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `hinh_anh_san_pham`
--
ALTER TABLE `hinh_anh_san_pham`
  MODIFY `hinh_anh_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `hinh_anh_tin_tuc`
--
ALTER TABLE `hinh_anh_tin_tuc`
  MODIFY `hinh_anh_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `logo`
--
ALTER TABLE `logo`
  MODIFY `Logo_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `mang_xa_hoi`
--
ALTER TABLE `mang_xa_hoi`
  MODIFY `Mang_Xa_Hoi_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  MODIFY `nguoi_dung_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT cho bảng `phuong_thuc_thanh_toan`
--
ALTER TABLE `phuong_thuc_thanh_toan`
  MODIFY `phuong_thuc_thanh_toan_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `san_pham`
--
ALTER TABLE `san_pham`
  MODIFY `san_pham_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `san_pham_bien_the`
--
ALTER TABLE `san_pham_bien_the`
  MODIFY `bien_the_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `slide_show`
--
ALTER TABLE `slide_show`
  MODIFY `Slide_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `tintuc`
--
ALTER TABLE `tintuc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `trang_tinh`
--
ALTER TABLE `trang_tinh`
  MODIFY `Trang_tinh_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `vai_tro`
--
ALTER TABLE `vai_tro`
  MODIFY `vai_tro_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `binh_luan`
--
ALTER TABLE `binh_luan`
  ADD CONSTRAINT `binh_luan_ibfk_1` FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoi_dung` (`nguoi_dung_id`),
  ADD CONSTRAINT `binh_luan_ibfk_2` FOREIGN KEY (`san_pham_id`) REFERENCES `san_pham` (`san_pham_id`),
  ADD CONSTRAINT `binh_luan_ibfk_3` FOREIGN KEY (`tin_tuc_id`) REFERENCES `tintuc` (`id`);

--
-- Các ràng buộc cho bảng `chi_tiet_don_hang`
--
ALTER TABLE `chi_tiet_don_hang`
  ADD CONSTRAINT `chi_tiet_don_hang_ibfk_1` FOREIGN KEY (`don_hang_id`) REFERENCES `don_hang` (`id`),
  ADD CONSTRAINT `chi_tiet_don_hang_ibfk_2` FOREIGN KEY (`san_pham_bien_the_id`) REFERENCES `san_pham_bien_the` (`bien_the_id`);

--
-- Các ràng buộc cho bảng `dia_chi`
--
ALTER TABLE `dia_chi`
  ADD CONSTRAINT `dia_chi_ibfk_1` FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoi_dung` (`nguoi_dung_id`);

--
-- Các ràng buộc cho bảng `don_hang`
--
ALTER TABLE `don_hang`
  ADD CONSTRAINT `don_hang_ibfk_1` FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoi_dung` (`nguoi_dung_id`),
  ADD CONSTRAINT `don_hang_ibfk_2` FOREIGN KEY (`phuong_thuc_thanh_toan_id`) REFERENCES `phuong_thuc_thanh_toan` (`phuong_thuc_thanh_toan_id`),
  ADD CONSTRAINT `don_hang_ibfk_3` FOREIGN KEY (`id_giam_gia`) REFERENCES `giam_gia` (`giam_gia_id`),
  ADD CONSTRAINT `don_hang_ibfk_4` FOREIGN KEY (`dia_chi_id`) REFERENCES `dia_chi` (`id_dia_chi`);

--
-- Các ràng buộc cho bảng `gio_hang`
--
ALTER TABLE `gio_hang`
  ADD CONSTRAINT `gio_hang_ibfk_1` FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoi_dung` (`nguoi_dung_id`);

--
-- Các ràng buộc cho bảng `gio_hang_chi_tiet`
--
ALTER TABLE `gio_hang_chi_tiet`
  ADD CONSTRAINT `gio_hang_chi_tiet_ibfk_1` FOREIGN KEY (`gio_hang_id`) REFERENCES `gio_hang` (`gio_hang_id`),
  ADD CONSTRAINT `gio_hang_chi_tiet_ibfk_2` FOREIGN KEY (`san_pham_bien_the_id`) REFERENCES `san_pham_bien_the` (`bien_the_id`);

--
-- Các ràng buộc cho bảng `hinh_anh_san_pham`
--
ALTER TABLE `hinh_anh_san_pham`
  ADD CONSTRAINT `hinh_anh_san_pham_ibfk_1` FOREIGN KEY (`san_pham_id`) REFERENCES `san_pham` (`san_pham_id`);

--
-- Các ràng buộc cho bảng `hinh_anh_tin_tuc`
--
ALTER TABLE `hinh_anh_tin_tuc`
  ADD CONSTRAINT `hinh_anh_tin_tuc_ibfk_1` FOREIGN KEY (`tin_tuc_id`) REFERENCES `tintuc` (`id`);

--
-- Các ràng buộc cho bảng `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  ADD CONSTRAINT `nguoi_dung_ibfk_1` FOREIGN KEY (`vai_tro_id`) REFERENCES `vai_tro` (`vai_tro_id`);

--
-- Các ràng buộc cho bảng `san_pham`
--
ALTER TABLE `san_pham`
  ADD CONSTRAINT `san_pham_ibfk_1` FOREIGN KEY (`ten_danh_muc_id`) REFERENCES `danh_muc_san_pham` (`category_id`);

--
-- Các ràng buộc cho bảng `san_pham_bien_the`
--
ALTER TABLE `san_pham_bien_the`
  ADD CONSTRAINT `san_pham_bien_the_ibfk_1` FOREIGN KEY (`san_pham_id`) REFERENCES `san_pham` (`san_pham_id`);

--
-- Các ràng buộc cho bảng `tintuc`
--
ALTER TABLE `tintuc`
  ADD CONSTRAINT `tintuc_ibfk_1` FOREIGN KEY (`id_danh_muc_tin_tuc`) REFERENCES `danh_muc_tin_tuc` (`id_danh_muc_tin_tuc`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
