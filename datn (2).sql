-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th7 21, 2025 lúc 10:20 AM
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
  `don_gia` decimal(15,2) DEFAULT NULL,
  `thanh_tien` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chi_tiet_don_hang`
--

INSERT INTO `chi_tiet_don_hang` (`id`, `don_hang_id`, `san_pham_bien_the_id`, `so_luong`, `don_gia`, `thanh_tien`) VALUES
(1, 2, 25, 10, 30000.00, 0.00),
(2, 4, 24, 1, 35000.00, 35000.00),
(3, 5, 30, 3, 40000.00, 120000.00),
(4, 6, 24, 1, 35000.00, 35000.00),
(5, 7, 24, 4, 35000.00, 140000.00),
(6, 8, 19, 3, 30000.00, 90000.00),
(7, 9, 26, 3, 15000.00, 45000.00),
(8, 10, 30, 3, 40000.00, 120000.00),
(9, 11, 18, 5, 40000.00, 200000.00),
(10, 12, 24, 4, 35000.00, 140000.00),
(11, 13, 30, 6, 40000.00, 240000.00),
(12, 14, 30, 1, 40000.00, 40000.00),
(13, 14, 26, 4, 15000.00, 60000.00),
(14, 15, 24, 2, 35000.00, 70000.00);

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
  `ngay_sua` datetime DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `danh_muc_san_pham`
--

INSERT INTO `danh_muc_san_pham` (`category_id`, `ten_danh_muc`, `mo_ta`, `slug`, `ngay_tao`, `ngay_sua`, `deleted_at`) VALUES
(1, 'gia dụng a', 'đồ gia dụng', 'ok chua', NULL, '2025-06-24 15:28:16', NULL),
(2, 'Cao Tuấn Vỹ', '4', NULL, NULL, NULL, NULL);

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
  `ngay_sua` datetime DEFAULT NULL,
  `trang_thai` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `danh_muc_tin_tuc`
--

INSERT INTO `danh_muc_tin_tuc` (`id_danh_muc_tin_tuc`, `ten_danh_muc`, `mo_ta`, `hinh_anh`, `ngay_tao`, `ngay_sua`, `trang_thai`) VALUES
(2, 'aaa', 'daa', 'Tintuc/NftaYw0bNurSNMySEO0IMceaziGV3iF1m9jrk4Tb.png', '2025-06-11 16:05:33', '2025-06-17 09:55:53', 1),
(8, 'dibada', 'aa', 'Tintuc/rgdnwGVkeUxiynAMYOdRECP60PaTDtU7W7QBE5LY.png', '2025-06-17 10:14:51', '2025-06-17 10:16:09', 1),
(9, 'ma da', 'aa', 'Tintuc/vUq98yF14u5JRwPdwwJ6f5wFGymTpaZqSAt9g8Jr.png', '2025-06-23 14:19:42', '2025-06-23 14:35:44', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `dia_chi`
--

CREATE TABLE `dia_chi` (
  `id_dia_chi` int(11) NOT NULL,
  `nguoi_dung_id` int(11) DEFAULT NULL,
  `dia_chi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `dia_chi`
--

INSERT INTO `dia_chi` (`id_dia_chi`, `nguoi_dung_id`, `dia_chi`) VALUES
(1, 2, 'Thành phố Hà Nội, Huyện Ba Vì, Xã Cẩm Lĩnh, Thôn 5'),
(2, 9, 'Thành phố Đà Nẵng, Quận Hải Châu, Phường Hải Châu II, Hội An');

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
  `trang_thai_don_hang` int(11) DEFAULT NULL,
  `ghi_chu` text DEFAULT NULL,
  `phi_van_chuyen` decimal(15,2) DEFAULT NULL,
  `tong_tien` int(100) DEFAULT NULL,
  `ngay_dat` datetime DEFAULT NULL,
  `ngay_sua` datetime DEFAULT NULL,
  `ngay_xoa` timestamp NULL DEFAULT NULL,
  `is_paid` int(10) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `don_hang`
--

INSERT INTO `don_hang` (`id`, `nguoi_dung_id`, `phuong_thuc_thanh_toan_id`, `id_giam_gia`, `dia_chi_id`, `trang_thai_don_hang`, `ghi_chu`, `phi_van_chuyen`, `tong_tien`, `ngay_dat`, `ngay_sua`, `ngay_xoa`, `is_paid`) VALUES
(2, 5, 1, NULL, 1, 2, NULL, 30000.00, NULL, '2025-07-09 00:28:14', NULL, NULL, 0),
(4, 9, 1, NULL, 2, 3, NULL, 15000.00, 35000, '2025-07-18 04:40:58', NULL, NULL, 1),
(5, 9, 2, NULL, 2, 5, NULL, 15000.00, 120000, '2025-07-18 06:49:51', NULL, NULL, 1),
(6, 9, 1, NULL, 2, 1, NULL, 15000.00, 35000, '2025-07-18 07:18:07', NULL, NULL, 0),
(7, 9, 2, NULL, 2, 2, NULL, 25000.00, 140000, '2025-07-18 09:51:05', NULL, NULL, 1),
(8, 9, 1, NULL, 2, 1, NULL, 15000.00, 90000, '2025-07-18 11:53:28', NULL, NULL, 0),
(9, 9, 1, NULL, 2, 1, NULL, 25000.00, 45000, '2025-07-18 11:54:56', NULL, NULL, 0),
(10, 9, 1, NULL, 2, 1, NULL, 15000.00, 120000, '2025-07-18 11:56:27', NULL, NULL, 0),
(11, 9, 1, NULL, 2, 3, NULL, 15000.00, 200000, '2025-07-18 11:57:01', NULL, NULL, 0),
(12, 9, 2, NULL, 2, 4, NULL, 15000.00, 140000, '2025-07-18 11:57:57', NULL, NULL, 1),
(13, 9, 1, NULL, 2, 5, NULL, 15000.00, 240000, '2025-07-18 11:58:47', NULL, NULL, 1),
(14, 9, 2, NULL, 2, 3, NULL, 25000.00, 100000, '2025-07-18 12:19:57', NULL, NULL, 1),
(15, 9, 2, NULL, 2, 5, NULL, 15000.00, 70000, '2025-07-20 12:18:14', NULL, NULL, 1);

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

--
-- Đang đổ dữ liệu cho bảng `gio_hang`
--

INSERT INTO `gio_hang` (`gio_hang_id`, `nguoi_dung_id`, `ngay_tao`, `ngay_sua`) VALUES
(1, 2, NULL, NULL),
(2, NULL, NULL, NULL);

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

--
-- Đang đổ dữ liệu cho bảng `gio_hang_chi_tiet`
--

INSERT INTO `gio_hang_chi_tiet` (`gio_hang_chi_tiet_id`, `gio_hang_id`, `san_pham_bien_the_id`, `so_luong`, `don_gia`, `ngay_tao`, `ngay_sua`) VALUES
(1, 1, 26, 5, 20000.00, NULL, NULL),
(2, 2, 25, 3, 18000.00, NULL, NULL),
(3, 2, 24, 1, 35000.00, NULL, NULL),
(4, 1, 24, 2, 35000.00, NULL, NULL),
(5, 1, 18, 9, 40000.00, NULL, NULL),
(6, 1, 25, 1, 18000.00, NULL, NULL);

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
(16, 24, 'products/VwfLDJf2TtYKQaKKMyMklRw0JiAIoVQdk0rgx65Y.png', NULL, NULL),
(17, 25, 'products/Ap2sKhWSFA8qkIRCrgIS7a8reZfRofXPCCbUivMs.png', NULL, NULL),
(18, 26, 'products/p2Vt5UbUZRZd4OXbFjmug3q83k6f5MhNqBDjBLMM.png', NULL, NULL),
(19, 27, 'products/sfyIlYdygCYaJLeGterohSL0IP0L93tlTTmux3ur.png', NULL, NULL);

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
  `vai_tro_id` int(11) DEFAULT 0,
  `trang_thai` tinyint(11) NOT NULL DEFAULT 0,
  `slug` varchar(255) DEFAULT NULL,
  `ngay_tao` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `activation_token` varchar(100) DEFAULT NULL,
  `ngay_sua` datetime DEFAULT NULL,
  `fcm_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `nguoi_dung`
--

INSERT INTO `nguoi_dung` (`nguoi_dung_id`, `ho_ten`, `email`, `sdt`, `mat_khau`, `anh_dai_dien`, `vai_tro_id`, `trang_thai`, `slug`, `ngay_tao`, `is_active`, `activation_token`, `ngay_sua`, `fcm_token`) VALUES
(1, 'Cao tuấn Vỹ', 'vyctps41133@gmail.com', NULL, '$2y$12$qwPl09xReZZx0MV.wFP.Tuc.zBV/1uPPK0NJfZ8LoVqRESZ5SOBW.', NULL, 0, 1, NULL, '2025-06-06 06:57:59', 0, NULL, NULL, NULL),
(2, 'dương', 'vyctps411333@gmail.com', '0363994247', '$2y$12$vdmaTh7z7jv/9Lc6OHn8tugbvWt1EMMTNSsEjBViQ94je8blqpAPq', NULL, 1, 0, NULL, '2025-06-06 16:22:24', 1, '', NULL, NULL),
(3, 'anpham', 'anpham16042005@gmail.com', '012345678', '$2y$12$DIOdoxMYXkeUWzTqeLV2re3poj7s5jqj2iocBAjSWJ1NKAHvoypqu', NULL, 1, 0, NULL, '2025-06-07 12:41:51', 0, NULL, NULL, NULL),
(4, 'nqchi123', 'nqchi123@gmail.com', NULL, '$2y$12$h46bdPfNBs9386NmDR4e9OYylDVrPrgM6KPk5KwKJnrnz6x2RoEIG', NULL, 1, 0, NULL, '2025-06-11 15:25:45', 1, NULL, NULL, NULL),
(5, 'duong123', 'duong123@gmail.com', NULL, '$2y$12$heXlUO80U9NNsxsOwmrinOnTIQ4w3KEJLiB7NrDfaS8SN7B5Zgqmm', NULL, 0, 0, NULL, '2025-06-18 13:33:56', 0, 'NfsApeGllKJ3JvWbh8LLUHZIubqKCJU3GOo5tXbHIWKH6Mn1B7IVwHkATJmb', NULL, NULL),
(6, 'aaa', 'aaa@gmail.com', NULL, '$2y$12$VzqpmB2sb8AT0w.bxTVMWOKjM1199hQ2WtSBfgPTF3df99mE7TeV2', NULL, 0, 0, NULL, '2025-06-18 13:34:25', 0, '2Yi6HiBWEDdlZL6fxybgUAuEZeacgs47AVpjsbTLx7U3d7STK7DwpYMGHErO', NULL, NULL),
(7, 'Vỹ Xấu trai', 'vyxautrai111@gmail.com', '', '$2y$12$q3/n/Ed4.S87E0LUx1lyb.Gg8ZDs1GLOssnFSvoLXaovOUt30yJYW', NULL, 0, 0, NULL, NULL, 1, NULL, NULL, NULL),
(8, 'yorskai09', 'yorskai09@gmail.com', NULL, '$2y$12$eeF8tbDyYjeTJDI/2Qbr0.OdWmQYlD0r.fniHBjiwep02U.8FM2Za', NULL, 0, 0, NULL, '2025-07-12 03:35:58', 1, NULL, NULL, 'fMIwCKVvCGaQLJju67V2im:APA91bG1c0x_2K8uzTEQRcDf8Xh27YTMzGJ6t4oDPxBy5xCczzH_gnn9V5rp7q9odpEmOyeNgdiluuS6PgGvIFie0G51KYcM84N1yDRBD6EwN_zaxngzxe0'),
(9, 'namad', 'namad@gmail.com', '09045674375', '$2y$12$BRr0AKV3ZL187bh1xNGq7u7rKPZ3UCu8KnutfgcfShfHlCBJAR2NS', NULL, 1, 0, NULL, '2025-07-18 04:14:42', 1, 'gGPCfoXDla9CN2IGMMeviDo9xvCVageSWiB6UzlO4uknS2u7SEclEyiDFNyD', NULL, NULL);

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
(36, 'App\\Models\\User', 3, 'api_token', '8ddc2d63384639edd64615f738a74e2a5c29346a0454aa8969741a89e86640ca', '[\"*\"]', NULL, NULL, '2025-06-07 05:41:51', '2025-06-07 05:41:51'),
(37, 'App\\Models\\User', 2, 'api_token', '398390a4665e5a6b368a6fd212c7d4cbd92f129cbbbba9af0bc3c8000b05758d', '[\"*\"]', '2025-06-07 06:29:16', NULL, '2025-06-07 05:42:50', '2025-06-07 06:29:16'),
(38, 'App\\Models\\User', 2, 'api_token', '5cd0b715de591614795237efdcd70cee138ea5b5debbbe4f602491d2b6823e2c', '[\"*\"]', '2025-06-07 07:35:06', NULL, '2025-06-07 06:29:39', '2025-06-07 07:35:06'),
(39, 'App\\Models\\User', 2, 'api_token', 'c4aecf4a929a7cb3ae4c58919bfa8808ea0fff86b9cd95c54c0c0f804fbb06aa', '[\"*\"]', '2025-06-07 06:33:57', NULL, '2025-06-07 06:33:48', '2025-06-07 06:33:57'),
(40, 'App\\Models\\User', 3, 'api_token', 'b8b71af23c9e36aa02d11b38bb029072b59c8c9e93d7d3aab2646b3b6ec219ac', '[\"*\"]', '2025-06-07 07:43:56', NULL, '2025-06-07 07:36:04', '2025-06-07 07:43:56'),
(41, 'App\\Models\\User', 3, 'api_token', 'afe4d42044d2abeb3a011616c0c5069b3401f8c457ba45bafc49d197de977e91', '[\"*\"]', '2025-06-07 07:59:23', NULL, '2025-06-07 07:44:52', '2025-06-07 07:59:23'),
(42, 'App\\Models\\User', 3, 'api_token', '1e3b78802356e8108825ff36a7d616da3dbe327c4ce6aa93491095261d02c6f7', '[\"*\"]', '2025-06-07 08:52:57', NULL, '2025-06-07 08:47:41', '2025-06-07 08:52:57'),
(43, 'App\\Models\\User', 3, 'api_token', '51aa27352a443c1b4cb743825d207f0e95f6c2758ed10b59e719485c9a4adb74', '[\"*\"]', '2025-06-07 09:36:01', NULL, '2025-06-07 09:10:18', '2025-06-07 09:36:01'),
(44, 'App\\Models\\User', 4, 'api_token', 'b77242026b9dedc12a7e5d5eb8159f3d3644896c6fa220d9994484d678b5df0d', '[\"*\"]', NULL, NULL, '2025-06-11 08:25:45', '2025-06-11 08:25:45'),
(45, 'App\\Models\\User', 4, 'api_token', '6eda1909dd9e45a9fe86fc2e24fdae0c0d1354ede53e8c7f9842ca076b128ec9', '[\"*\"]', NULL, NULL, '2025-06-11 08:25:53', '2025-06-11 08:25:53'),
(46, 'App\\Models\\User', 4, 'api_token', 'ff6b337bb0c94dfb6524ad29ac069c64e323e0d9eb91d0b265ba297f25d67934', '[\"*\"]', '2025-06-13 08:16:08', NULL, '2025-06-11 08:26:34', '2025-06-13 08:16:08'),
(47, 'App\\Models\\User', 4, 'api_token', 'cdb621a38a990f9c379e495ab276aa33a9b5385aac0ced965450785fe186a29b', '[\"*\"]', '2025-06-13 09:36:29', NULL, '2025-06-12 03:05:33', '2025-06-13 09:36:29'),
(48, 'App\\Models\\User', 4, 'api_token', '92e8cb4a5f710232c5fb3a5ea2abb558955265dddbfa3aee82216bdd618487e3', '[\"*\"]', NULL, NULL, '2025-06-13 08:18:25', '2025-06-13 08:18:25'),
(49, 'App\\Models\\User', 4, 'api_token', 'fb61829241bcf5607ff0f38160e5ed5abd9f642814aace00a29a0e6831d1f5f4', '[\"*\"]', NULL, NULL, '2025-06-13 08:19:20', '2025-06-13 08:19:20'),
(50, 'App\\Models\\User', 4, 'api_token', '16da4f9c1be513879179708d747d17212fed9a5d54f45d3e584e0802578bcba4', '[\"*\"]', NULL, NULL, '2025-06-13 08:19:22', '2025-06-13 08:19:22'),
(51, 'App\\Models\\User', 4, 'api_token', '1e3e1a63d5bddd69eeb02c14d06b71f34c4dde78c193e88b035f6e47ce43705e', '[\"*\"]', NULL, NULL, '2025-06-13 08:19:22', '2025-06-13 08:19:22'),
(52, 'App\\Models\\User', 4, 'api_token', '026e449119d17cf938125e35c41285435169ebac47997daad0e4f93cc1f027e9', '[\"*\"]', NULL, NULL, '2025-06-13 08:25:23', '2025-06-13 08:25:23'),
(53, 'App\\Models\\User', 4, 'api_token', 'f87edb7dd559db43088fedceab1848cf0939d5cd46c219e6a71a05bbca56971f', '[\"*\"]', NULL, NULL, '2025-06-13 08:25:24', '2025-06-13 08:25:24'),
(54, 'App\\Models\\User', 4, 'api_token', '101d1af451404e771fdab53040a120fd61853d2362cc9bdd0fcf1e10a2bc372f', '[\"*\"]', NULL, NULL, '2025-06-13 08:25:26', '2025-06-13 08:25:26'),
(55, 'App\\Models\\User', 4, 'api_token', '1a62133c178a2ec62ab27d00ff64ffe00d3b0238e91defba7314344201574252', '[\"*\"]', NULL, NULL, '2025-06-13 08:25:29', '2025-06-13 08:25:29'),
(56, 'App\\Models\\User', 4, 'api_token', '6f75959b583821f7ecb20101f80376ed1a27195b50d3dc68e52af1227d4205cf', '[\"*\"]', NULL, NULL, '2025-06-13 08:25:30', '2025-06-13 08:25:30'),
(57, 'App\\Models\\User', 4, 'api_token', '453a3099533d76a2a0839961ebd5a5137b108780ee40026ec60bfd3860fbfae6', '[\"*\"]', NULL, NULL, '2025-06-13 08:25:42', '2025-06-13 08:25:42'),
(58, 'App\\Models\\User', 4, 'api_token', 'c08d710e0bbcddfff39254cec5ac17f603b1838ce644f6e1a5d7e8bec051a7d6', '[\"*\"]', '2025-06-13 08:46:38', NULL, '2025-06-13 08:34:03', '2025-06-13 08:46:38'),
(59, 'App\\Models\\User', 4, 'api_token', 'adf4b50b052bcb147746210855649a4be4630e504bd5afc266433937d1febce3', '[\"*\"]', '2025-06-23 10:36:25', NULL, '2025-06-13 08:50:22', '2025-06-23 10:36:25'),
(60, 'App\\Models\\User', 4, 'api_token', 'd67f76d151a873911ad53b6050394ff96682410e1c4f06acc9268cce33103adb', '[\"*\"]', '2025-06-14 09:46:38', NULL, '2025-06-14 08:34:50', '2025-06-14 09:46:38'),
(61, 'App\\Models\\User', 4, 'api_token', 'f02a1667af9d99b68759275eb0e7e8978ca3c9da1e12558844028ee9d30c66b0', '[\"*\"]', '2025-06-15 08:22:02', NULL, '2025-06-15 07:25:17', '2025-06-15 08:22:02'),
(62, 'App\\Models\\User', 4, 'api_token', '4b4c4f5890acfe3a21cdffa48341b9f82592eaec8a086f91df77e10b6e1ecc2d', '[\"*\"]', '2025-06-18 06:33:27', NULL, '2025-06-16 07:58:12', '2025-06-18 06:33:27'),
(63, 'App\\Models\\User', 4, 'api_token', 'da0ee471dc851641ffbb0eeec03a07f23a44cfcfb389295ad9e2a2ec59bff096', '[\"*\"]', '2025-06-26 00:14:28', NULL, '2025-06-18 06:34:59', '2025-06-26 00:14:28'),
(64, 'App\\Models\\User', 4, 'api_token', '79d1ef233e9622c7d442523d35cccb461700420a524455659f175c7ab0efa915', '[\"*\"]', '2025-06-26 03:51:22', NULL, '2025-06-24 08:13:50', '2025-06-26 03:51:22'),
(65, 'App\\Models\\User', 2, 'api_token', 'fffe056fd5b7eaa08d0daae6c46ffbd7c7c497a907de9f1e83fb94930c8cd622', '[\"*\"]', NULL, NULL, '2025-06-29 21:26:17', '2025-06-29 21:26:17'),
(66, 'App\\Models\\User', 2, 'api_token', 'cb3c92a304c7110fdcd9ad223f70bc0e6bc07e68c26bb65ac81f1cf6f279c0b9', '[\"*\"]', NULL, NULL, '2025-06-29 21:26:19', '2025-06-29 21:26:19'),
(67, 'App\\Models\\User', 2, 'api_token', 'd7881a77ba7935e8df215ad0267048341ef8ea823588625f799db2c4849fa10f', '[\"*\"]', NULL, NULL, '2025-06-29 21:26:29', '2025-06-29 21:26:29'),
(68, 'App\\Models\\User', 2, 'api_token', '77edafa53e1e777fb79306b30e035131fc9dd420d8c6b0c45f63b3014e161095', '[\"*\"]', '2025-07-01 21:03:54', NULL, '2025-06-29 21:27:10', '2025-07-01 21:03:54'),
(69, 'App\\Models\\User', 2, 'api_token', 'dce8145cba8414d0d5b1268db4d78c836d6fe64f6bc807a54bfcb63f9cd9575a', '[\"*\"]', NULL, NULL, '2025-06-30 18:14:07', '2025-06-30 18:14:07'),
(70, 'App\\Models\\User', 2, 'api_token', '16882485bbaaa88227b1d66588c1f0de732ab9ed7fb503e85e5c7ff1b684c838', '[\"*\"]', '2025-06-30 21:30:15', NULL, '2025-06-30 18:14:08', '2025-06-30 21:30:15'),
(71, 'App\\Models\\User', 2, 'api_token', '427bff339437ffdc8c932b867560ea0f0e3a795abeecd481378baca5f81a279b', '[\"*\"]', '2025-07-02 10:14:50', NULL, '2025-07-02 01:39:27', '2025-07-02 10:14:50'),
(72, 'App\\Models\\User', 2, 'api_token', 'ca0d9d8ee040d08e2f9e96ebb26c2e7325a4c2bcab7aaaab192b49d397923e4c', '[\"*\"]', '2025-07-02 19:42:39', NULL, '2025-07-02 19:42:12', '2025-07-02 19:42:39'),
(73, 'App\\Models\\User', 2, 'api_token', 'ff98cdedb1aea5c1295b3f5999c95276ef4b0803b55b2b65895803e2e11d9fa4', '[\"*\"]', '2025-07-04 08:53:29', NULL, '2025-07-02 19:54:10', '2025-07-04 08:53:29'),
(74, 'App\\Models\\User', 2, 'api_token', '226ba5a0f97353d6b3a4c1988b4ce0dee5889bd4e3d882a655d6b011f279c270', '[\"*\"]', NULL, NULL, '2025-07-04 09:02:27', '2025-07-04 09:02:27'),
(75, 'App\\Models\\User', 2, 'api_token', '03e11883edb109062dd8b56d8507bae649fac512141d6689683352b04421587a', '[\"*\"]', NULL, NULL, '2025-07-04 09:14:28', '2025-07-04 09:14:28'),
(76, 'App\\Models\\User', 2, 'api_token', '7f8657273609e31962ce5b13e4679e797c2e4e0e11637f0a289dba570b6daa19', '[\"*\"]', '2025-07-05 07:55:10', NULL, '2025-07-04 09:39:13', '2025-07-05 07:55:10'),
(77, 'App\\Models\\User', 2, 'api_token', '5ad36d89874fec85f84fc63f11e476f73c52bcaa28a4bbe919d3c8969b71201b', '[\"*\"]', NULL, NULL, '2025-07-05 08:01:44', '2025-07-05 08:01:44'),
(78, 'App\\Models\\User', 2, 'api_token', 'df01a66e72f1eec22f94b5f9d3e1ad631d06de43f93a6e7e2b2796b61833e2da', '[\"*\"]', '2025-07-05 08:27:10', NULL, '2025-07-05 08:27:03', '2025-07-05 08:27:10'),
(79, 'App\\Models\\User', 7, 'google-login-token', '3ca24cba63f04acc82ea4ec185862151f796c1e2cf760ce325004ddb7fce7d64', '[\"*\"]', '2025-07-05 08:56:05', NULL, '2025-07-05 08:55:59', '2025-07-05 08:56:05'),
(80, 'App\\Models\\User', 2, 'api_token', '90f24ef195ec19ddb316145e67b8142291e92123f1b5a975e1f47a5d4d95f614', '[\"*\"]', '2025-07-05 20:49:02', NULL, '2025-07-05 08:56:19', '2025-07-05 20:49:02'),
(81, 'App\\Models\\User', 2, 'api_token', '8182fd5ce6f9ef46e862ad927cd58c0774be2d5868a3b44097439f1bf23fde7e', '[\"*\"]', '2025-07-09 10:33:13', NULL, '2025-07-05 21:13:00', '2025-07-09 10:33:13'),
(82, 'App\\Models\\User', 2, 'api_token', 'e880a4220b8416b5552f64d498281f376ae008fb99f743f0c8f1e34ac5310181', '[\"*\"]', '2025-07-09 21:02:41', NULL, '2025-07-09 20:03:52', '2025-07-09 21:02:41'),
(83, 'App\\Models\\User', 2, 'api_token', 'd09cc8ed7a0c9587b67e761e1032bd1f01bfa4446a12b113d5fd3a844f48bf6e', '[\"*\"]', '2025-07-10 01:42:01', NULL, '2025-07-09 22:27:57', '2025-07-10 01:42:01'),
(84, 'App\\Models\\User', 2, 'api_token', 'df0975d2c5096dcf47f4abdbdd192e953b822bf78787c53bf1ffa53790c7b5e3', '[\"*\"]', '2025-07-10 08:52:27', NULL, '2025-07-10 05:12:05', '2025-07-10 08:52:27'),
(85, 'App\\Models\\User', 2, 'api_token', 'd7d6d7369e02d73e78e4c8e13cd4e196040f29c793dfd280d588fea739ca0f57', '[\"*\"]', '2025-07-10 22:51:31', NULL, '2025-07-10 08:52:48', '2025-07-10 22:51:31'),
(86, 'App\\Models\\User', 2, 'api_token', 'b297e406253a577d1e3b67aea66dd8b4671b59525197a189d6d1d25b3e56b84d', '[\"*\"]', '2025-07-11 10:25:35', NULL, '2025-07-11 08:46:34', '2025-07-11 10:25:35'),
(87, 'App\\Models\\User', 2, 'api_token', 'c368d0d87b253c0b105e1c192d3aad2ab861cb103b179e159b782c71b34a6056', '[\"*\"]', '2025-07-11 20:32:23', NULL, '2025-07-11 20:32:14', '2025-07-11 20:32:23'),
(88, 'App\\Models\\User', 8, 'api_token', '928feb9b814a36518114a0d5614919e790ccc16dc80bde8a467f9b5bb811b18a', '[\"*\"]', NULL, NULL, '2025-07-11 20:36:33', '2025-07-11 20:36:33'),
(89, 'App\\Models\\User', 2, 'api_token', '2268ae4d6e729a2944c8e755bbabffdbfe613f4c07926b416ea67b3eef24f0dc', '[\"*\"]', '2025-07-11 21:35:51', NULL, '2025-07-11 20:37:12', '2025-07-11 21:35:51'),
(90, 'App\\Models\\User', 8, 'api_token', '204b7bfd98b8f8ca3e18ad51dce4c45960b53cc411f09d2c527bc9561016b11c', '[\"*\"]', NULL, NULL, '2025-07-11 21:40:36', '2025-07-11 21:40:36'),
(91, 'App\\Models\\User', 2, 'api_token', '22b2882b62db9a1d078ac533954cff0058355f23f16ddd13bea742b7268fe26b', '[\"*\"]', '2025-07-11 22:39:25', NULL, '2025-07-11 21:40:48', '2025-07-11 22:39:25'),
(92, 'App\\Models\\User', 2, 'api_token', '7464b00fb182778cc2878dec8f3c39e8b966bcad5a14635847bd9d165d1a02cf', '[\"*\"]', '2025-07-12 06:26:54', NULL, '2025-07-11 22:39:41', '2025-07-12 06:26:54'),
(93, 'App\\Models\\User', 8, 'api_token', '2986d8bd638c205f5b8dbf925524504a8498df12a3cec9aa7779f84335c0ed7b', '[\"*\"]', NULL, NULL, '2025-07-11 22:40:04', '2025-07-11 22:40:04'),
(94, 'App\\Models\\User', 8, 'api_token', '8ed9182d4e8e9863fb7af4d1eaf1216ac6e3adb1ac61dedd8b0732ad50a32a10', '[\"*\"]', NULL, NULL, '2025-07-11 22:41:02', '2025-07-11 22:41:02'),
(95, 'App\\Models\\User', 8, 'api_token', '9bfad92805d4fe9afa088ffe480f3831e4e63f175961a3cd2ad8f0e2f21eb96c', '[\"*\"]', NULL, NULL, '2025-07-11 22:50:53', '2025-07-11 22:50:53'),
(96, 'App\\Models\\User', 8, 'api_token', '1ac8356cba8890e1eab5f776674406f42bd68937b893086a1be21fc984d08843', '[\"*\"]', NULL, NULL, '2025-07-11 23:00:37', '2025-07-11 23:00:37'),
(97, 'App\\Models\\User', 8, 'api_token', 'c30b709bbb85f43818cebbdb3a1af095b483c2ffe3b5b8bc315ebfeb8ccfa22e', '[\"*\"]', NULL, NULL, '2025-07-11 23:04:23', '2025-07-11 23:04:23'),
(98, 'App\\Models\\User', 8, 'api_token', '597999057e66c6c4dc5aff42933853b2b61407e5b3a236fb330ba91c70e40192', '[\"*\"]', NULL, NULL, '2025-07-11 23:11:39', '2025-07-11 23:11:39'),
(99, 'App\\Models\\User', 8, 'api_token', '367444a0f323defd7e4f062aa4a8a1ec9a85b87d68299881af7586f2435d3ed2', '[\"*\"]', NULL, NULL, '2025-07-11 23:12:01', '2025-07-11 23:12:01'),
(100, 'App\\Models\\User', 8, 'api_token', '1edc04b99e2ebc1aaa3911f1e30ee95653853e3c68fecb9774b1cb7bf958d2d8', '[\"*\"]', NULL, NULL, '2025-07-11 23:12:54', '2025-07-11 23:12:54'),
(101, 'App\\Models\\User', 8, 'api_token', '1092080d3e61c35865d1a645d51d8ca260abeb2fc7b7ec840f4abaca18cd6dd3', '[\"*\"]', NULL, NULL, '2025-07-11 23:13:07', '2025-07-11 23:13:07'),
(102, 'App\\Models\\User', 8, 'api_token', '723b736b191ec2e4982cced60adce35b90f6ac346443e3d3439a1eb96200b3f3', '[\"*\"]', NULL, NULL, '2025-07-11 23:25:27', '2025-07-11 23:25:27'),
(103, 'App\\Models\\User', 8, 'api_token', '7510aff0afbba2dc786531e25bd8d8c82a24cec0bc45093e70cf13218ebfc49a', '[\"*\"]', NULL, NULL, '2025-07-11 23:28:33', '2025-07-11 23:28:33'),
(104, 'App\\Models\\User', 8, 'api_token', 'f8371cafc1054787e8593bc4efeb98866d3448c05635f0367543eb799b003e79', '[\"*\"]', NULL, NULL, '2025-07-11 23:49:51', '2025-07-11 23:49:51'),
(105, 'App\\Models\\User', 8, 'api_token', '3c729686f25b6d9b85046f81cf552f0b26d7249ec0a21d9f8fbe549ed94b2eb7', '[\"*\"]', '2025-07-11 23:50:54', NULL, '2025-07-11 23:50:42', '2025-07-11 23:50:54'),
(106, 'App\\Models\\User', 9, 'api_token', '2f648b38c9af49b248d9564dc53ca1b88266d9871fca3fd23b2a22fa5e951c0e', '[\"*\"]', '2025-07-18 05:32:18', NULL, '2025-07-17 21:15:43', '2025-07-18 05:32:18'),
(107, 'App\\Models\\User', 9, 'api_token', 'da07fa2d2d1b580db65da236e326f3813716d6a575eba2fec4a1b40aae25169c', '[\"*\"]', '2025-07-20 05:35:13', NULL, '2025-07-20 05:11:12', '2025-07-20 05:35:13'),
(108, 'App\\Models\\User', 9, 'api_token', 'e8ca9177c2722b8d67652d1c9b9b2ad2302d51d56986f3174921d7b6bd66bb26', '[\"*\"]', '2025-07-20 23:52:16', NULL, '2025-07-20 20:55:22', '2025-07-20 23:52:16');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phuong_thuc_thanh_toan`
--

CREATE TABLE `phuong_thuc_thanh_toan` (
  `phuong_thuc_thanh_toan_id` int(11) NOT NULL,
  `ten_pttt` varchar(255) DEFAULT NULL,
  `trang_thai` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `phuong_thuc_thanh_toan`
--

INSERT INTO `phuong_thuc_thanh_toan` (`phuong_thuc_thanh_toan_id`, `ten_pttt`, `trang_thai`) VALUES
(1, 'Tiền Mặt', 1),
(2, 'Thanh Toán Ngân Hàng', 2),
(3, 'MoMo', 3);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `san_pham`
--

CREATE TABLE `san_pham` (
  `san_pham_id` int(11) NOT NULL,
  `ten_san_pham` varchar(255) DEFAULT NULL,
  `mo_ta` text DEFAULT NULL,
  `gia` decimal(15,2) DEFAULT NULL,
  `noi_bat` tinyint(1) DEFAULT NULL,
  `the` text DEFAULT NULL COMMENT 'Các thẻ sản phẩm, cách nhau bởi dấu phẩy',
  `Tieu_de_seo` varchar(255) DEFAULT NULL COMMENT 'Tiêu đề SEO',
  `Tu_khoa` text DEFAULT NULL COMMENT 'Các từ khóa SEO',
  `Mo_ta_seo` text DEFAULT NULL COMMENT 'Mô tả SEO',
  `ngay_bat_dau_giam_gia` date DEFAULT NULL,
  `ngay_ket_thuc_giam_gia` date DEFAULT NULL,
  `khuyen_mai` decimal(5,2) DEFAULT NULL,
  `ngay_tao` datetime DEFAULT NULL,
  `ngay_sua` datetime DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `ten_danh_muc_id` int(11) DEFAULT NULL,
  `trang_thai` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `san_pham`
--

INSERT INTO `san_pham` (`san_pham_id`, `ten_san_pham`, `mo_ta`, `gia`, `noi_bat`, `the`, `Tieu_de_seo`, `Tu_khoa`, `Mo_ta_seo`, `ngay_bat_dau_giam_gia`, `ngay_ket_thuc_giam_gia`, `khuyen_mai`, `ngay_tao`, `ngay_sua`, `slug`, `ten_danh_muc_id`, `trang_thai`) VALUES
(24, 'Bánh Mì Croissant', '<h1>B&aacute;nh M&igrave; Croissant Sừng Tr&acirc;u Cao Cấp - Hương Vị Ph&aacute;p Quốc Ngay Tại Việt Nam</h1>\n<p>Kh&aacute;m ph&aacute; hương vị quyến rũ của <strong>b&aacute;nh m&igrave; Croissant sừng tr&acirc;u</strong> cao cấp, sản phẩm được l&agrave;m từ những nguy&ecirc;n liệu tươi ngon nhất, mang đến trải nghiệm ẩm thực tuyệt vời ngay tại nh&agrave; bạn. Với lớp vỏ ngo&agrave;i gi&ograve;n tan, nhiều lớp xếp chồng tinh tế v&agrave; phần ruột mềm mại, thơm ngậy, b&aacute;nh m&igrave; Croissant của ch&uacute;ng t&ocirc;i chắc chắn sẽ l&agrave;m h&agrave;i l&ograve;ng cả những thực kh&aacute;ch kh&oacute; t&iacute;nh nhất. Kh&ocirc;ng chỉ l&agrave; một m&oacute;n ăn s&aacute;ng ho&agrave;n hảo, b&aacute;nh m&igrave; Croissant c&ograve;n l&agrave; lựa chọn l&yacute; tưởng cho những buổi tr&agrave; chiều thư gi&atilde;n hay l&agrave;m qu&agrave; tặng &yacute; nghĩa cho người th&acirc;n, bạn b&egrave;.</p>\n<p><img style=\"display: block; margin-left: auto; margin-right: auto;\" src=\"http://localhost:8000/uploads/editor_images/1751424094.png\" width=\"801\" height=\"801\"></p>\n<h2>Sự Kết Hợp Ho&agrave;n Hảo Giữa Truyền Thống V&agrave; Hiện Đại</h2>\n<p>B&aacute;nh m&igrave; Croissant của ch&uacute;ng t&ocirc;i được chế biến theo c&ocirc;ng thức truyền thống của Ph&aacute;p, kết hợp với những cải tiến hiện đại để đảm bảo chất lượng v&agrave; hương vị tốt nhất. Ch&uacute;ng t&ocirc;i sử dụng bột m&igrave; cao cấp nhập khẩu, bơ tươi chất lượng cao v&agrave; c&aacute;c nguy&ecirc;n liệu kh&aacute;c được tuyển chọn kỹ c&agrave;ng, đảm bảo mang đến cho bạn sản phẩm thơm ngon, an to&agrave;n cho sức khỏe. Quy tr&igrave;nh sản xuất được gi&aacute;m s&aacute;t chặt chẽ, đảm bảo vệ sinh an to&agrave;n thực phẩm theo ti&ecirc;u chuẩn quốc tế.</p>\n<h3>Nguy&ecirc;n Liệu Cao Cấp, Hương Vị Tuyệt Vời</h3>\n<p>Sự kh&aacute;c biệt của b&aacute;nh m&igrave; Croissant của ch&uacute;ng t&ocirc;i nằm ở việc lựa chọn nguy&ecirc;n liệu. Ch&uacute;ng t&ocirc;i chỉ sử dụng bột m&igrave; số 8 c&oacute; độ đ&agrave;n hồi cao, tạo n&ecirc;n lớp vỏ gi&ograve;n rụm đặc trưng. Bơ tươi được nhập khẩu từ New Zealand, c&oacute; h&agrave;m lượng chất b&eacute;o cao, gi&uacute;p tạo n&ecirc;n lớp vỏ nhiều lớp xếp chồng tinh tế v&agrave; hương vị thơm b&eacute;o kh&oacute; cưỡng. Kh&ocirc;ng chỉ vậy, ch&uacute;ng t&ocirc;i c&ograve;n sử dụng đường m&iacute;a hữu cơ v&agrave; men nở tự nhi&ecirc;n, đảm bảo hương vị tự nhi&ecirc;n v&agrave; tốt cho sức khỏe.</p>\n<p>Ngo&agrave;i ra, để đ&aacute;p ứng nhu cầu đa dạng của kh&aacute;ch h&agrave;ng, ch&uacute;ng t&ocirc;i cung cấp nhiều lựa chọn kh&aacute;c nhau như: b&aacute;nh m&igrave; Croissant truyền thống, b&aacute;nh m&igrave; Croissant nh&acirc;n chocolate, b&aacute;nh m&igrave; Croissant nh&acirc;n ph&ocirc; mai, b&aacute;nh m&igrave; Croissant nh&acirc;n mứt tr&aacute;i c&acirc;y&hellip; Mỗi loại đều c&oacute; hương vị đặc trưng ri&ecirc;ng, hứa hẹn mang đến cho bạn những trải nghiệm ẩm thực phong ph&uacute; v&agrave; kh&oacute; qu&ecirc;n.</p>\n<h2>T&iacute;nh Năng Nổi Bật Của B&aacute;nh M&igrave; Croissant</h2>\n<p>B&aacute;nh m&igrave; Croissant của ch&uacute;ng t&ocirc;i sở hữu những t&iacute;nh năng nổi bật sau:</p>\n<ul>\n<ul>\n<li><strong>Vỏ ngo&agrave;i gi&ograve;n tan:</strong> Lớp vỏ nhiều lớp xếp chồng tinh tế, tạo n&ecirc;n độ gi&ograve;n rụm đặc trưng của b&aacute;nh m&igrave; Croissant.</li>\n<li><strong>Ruột mềm mại, thơm ngậy:</strong> Phần ruột b&aacute;nh mềm mại, xốp nhẹ, thơm m&ugrave;i bơ sữa, tan chảy trong miệng.</li>\n<li><strong>Hương vị thơm ngon, hấp dẫn:</strong> Sự kết hợp ho&agrave;n hảo giữa vị ngọt thanh của đường, vị b&eacute;o ngậy của bơ v&agrave; m&ugrave;i thơm quyến rũ của bột m&igrave; tạo n&ecirc;n hương vị kh&oacute; cưỡng.</li>\n<li><strong>Đa dạng lựa chọn:</strong> Nhiều loại nh&acirc;n b&aacute;nh kh&aacute;c nhau để bạn lựa chọn theo sở th&iacute;ch.</li>\n<li><strong>An to&agrave;n vệ sinh thực phẩm:</strong> Sản xuất theo quy tr&igrave;nh kh&eacute;p k&iacute;n, đảm bảo vệ sinh an to&agrave;n thực phẩm theo ti&ecirc;u chuẩn quốc tế.</li>\n<li><strong>Đ&oacute;ng g&oacute;i tiện lợi:</strong> B&aacute;nh được đ&oacute;ng g&oacute;i cẩn thận, giữ được độ tươi ngon v&agrave; dễ d&agrave;ng bảo quản.</li>\n</ul>\n</ul>\n<h2>Th&ocirc;ng Số Kỹ Thuật</h2>\n<p>(Lưu &yacute;: Th&ocirc;ng số kỹ thuật c&oacute; thể thay đổi t&ugrave;y thuộc v&agrave;o loại b&aacute;nh v&agrave; trọng lượng)</p>\n<ul>\n<ul>\n<li><strong>Trọng lượng trung b&igrave;nh:</strong> 40-50g/chiếc (t&ugrave;y loại)</li>\n<li><strong>K&iacute;ch thước trung b&igrave;nh:</strong> 10cm x 5cm x 4cm (t&ugrave;y loại)</li>\n<li><strong>Hạn sử dụng:</strong> 3 ng&agrave;y kể từ ng&agrave;y sản xuất (nếu bảo quản ở nhiệt độ ph&ograve;ng), 7 ng&agrave;y nếu bảo quản trong tủ lạnh.</li>\n<li><strong>Hướng dẫn bảo quản:</strong> Bảo quản ở nhiệt độ ph&ograve;ng hoặc trong tủ lạnh.</li>\n</ul>\n</ul>\n<h2>Lợi &Iacute;ch Khi Sử Dụng B&aacute;nh M&igrave; Croissant</h2>\n<p>B&aacute;nh m&igrave; Croissant kh&ocirc;ng chỉ l&agrave; một m&oacute;n ăn ngon miệng m&agrave; c&ograve;n mang lại nhiều lợi &iacute;ch:</p>\n<ul>\n<ul>\n<li><strong>Bổ sung năng lượng:</strong> L&agrave; nguồn cung cấp năng lượng nhanh ch&oacute;ng v&agrave; hiệu quả cho cơ thể.</li>\n<li><strong>Thư gi&atilde;n tinh thần:</strong> Hương vị thơm ngon của b&aacute;nh gi&uacute;p thư gi&atilde;n tinh thần, giảm stress.</li>\n<li><strong>M&oacute;n ăn l&yacute; tưởng cho mọi bữa ăn:</strong> Ph&ugrave; hợp cho bữa s&aacute;ng, bữa phụ hoặc d&ugrave;ng k&egrave;m với c&agrave; ph&ecirc;, tr&agrave; sữa.</li>\n<li><strong>Qu&agrave; tặng &yacute; nghĩa:</strong> L&agrave; m&oacute;n qu&agrave; nhỏ nhưng chứa đựng t&igrave;nh cảm lớn lao d&agrave;nh cho người th&acirc;n, bạn b&egrave;.</li>\n</ul>\n</ul>\n<h2>Hướng Dẫn Sử Dụng</h2>\n<p>B&aacute;nh m&igrave; Croissant c&oacute; thể ăn trực tiếp hoặc l&agrave;m n&oacute;ng lại bằng l&ograve; nướng hoặc l&ograve; vi s&oacute;ng để c&oacute; hương vị thơm ngon nhất. Để l&agrave;m n&oacute;ng lại bằng l&ograve; nướng, bạn n&ecirc;n đặt b&aacute;nh v&agrave;o l&ograve; nướng ở nhiệt độ 180 độ C trong khoảng 3-5 ph&uacute;t. Để l&agrave;m n&oacute;ng lại bằng l&ograve; vi s&oacute;ng, bạn n&ecirc;n h&acirc;m n&oacute;ng ở chế độ lửa vừa trong khoảng 15-20 gi&acirc;y.</p>\n<h2>Lời K&ecirc;u Gọi H&agrave;nh Động</h2>\n<p>Đừng bỏ lỡ cơ hội thưởng thức hương vị tuyệt vời của <strong>b&aacute;nh m&igrave; Croissant sừng tr&acirc;u cao cấp</strong>. H&atilde;y đặt h&agrave;ng ngay h&ocirc;m nay để trải nghiệm sự kh&aacute;c biệt! Click v&agrave;o n&uacute;t \"Mua ngay\" để đặt h&agrave;ng hoặc li&ecirc;n hệ với ch&uacute;ng t&ocirc;i qua hotline [Số điện thoại] để được tư vấn chi tiết.</p>\n<p>[Th&ecirc;m n&uacute;t \"Mua ngay\" hoặc li&ecirc;n kết đến trang sản phẩm]</p>', 15000.00, 2, 'Bánh Mì , Bánh Croissant', 'Bánh Mì Croissant Sốp Ngon - Giao Hộ Nhanh', 'bánh mì croissant, croissant, bánh mì, đặt bánh online', 'Thưởng thức Bánh Mì Croissant giòn tan, lớp ngoài vàng ươm, lớp trong mềm mại, nhân đa dạng hấp dẫn.  Được làm từ nguyên liệu tươi ngon, đảm bảo vệ sinh an toàn...', NULL, NULL, 5.00, NULL, NULL, 'banh-mi-croissant', 1, 1),
(25, 'Cơm Bò Chiên Trứng', '<h1>Cơm Bò Chiên Trứng - Món Ăn Nhanh Gọn, Bổ Dưỡng, Đầy Đủ Dinh Dưỡng</h1>\r\n\r\n<p>Bạn đang tìm kiếm một bữa ăn nhanh chóng, tiện lợi nhưng vẫn đảm bảo đầy đủ chất dinh dưỡng?  Cơm bò chiên trứng chính là sự lựa chọn hoàn hảo! Sản phẩm của chúng tôi kết hợp hoàn hảo giữa cơm nóng dẻo, thịt bò mềm thơm, và trứng chiên vàng ươm, tạo nên một món ăn hấp dẫn khó cưỡng.  Không cần tốn nhiều thời gian chuẩn bị, bạn vẫn có thể thưởng thức một bữa ăn ngon lành và bổ dưỡng ngay tại nhà.  Hãy cùng khám phá chi tiết về sản phẩm Cơm bò chiên trứng của chúng tôi để trải nghiệm hương vị tuyệt vời này!</p>\r\n\r\n<h2>Hương Vị Đậm Đà, Thơm Ngon Khó Cưỡng</h2>\r\n\r\n<p>Món Cơm bò chiên trứng của chúng tôi được chế biến từ những nguyên liệu tươi ngon, được tuyển chọn kỹ càng. Thịt bò được lựa chọn kỹ, mềm ngọt, không dai, được tẩm ướp gia vị đậm đà, tạo nên hương vị thơm ngon đặc trưng. Trứng gà tươi ngon, được chiên vàng ươm, tạo nên lớp vỏ giòn rụm bên ngoài và phần lòng trắng mềm mịn bên trong. Cơm được nấu từ loại gạo thơm ngon, dẻo mềm, hấp thụ trọn vẹn vị ngon của thịt bò và trứng. Tất cả hòa quyện lại tạo nên một món ăn hoàn hảo, chinh phục mọi giác quan.</p>\r\n\r\n<p>Chúng tôi cam kết sử dụng nguồn nguyên liệu sạch, an toàn thực phẩm, không sử dụng chất bảo quản, tạo màu hay chất phụ gia độc hại.  Vì sức khỏe của bạn là ưu tiên hàng đầu của chúng tôi.</p>\r\n\r\n\r\n<h2>Thành Phần và Thông Tin Dinh Dưỡng</h2>\r\n\r\n<h3>Nguyên liệu chính:</h3>\r\n\r\n<p>• Thịt bò tươi ngon (xuất xứ: [ghi rõ nguồn gốc]), được tẩm ướp gia vị đặc biệt.\r\n• Gạo thơm ngon, dẻo mềm ([ghi rõ loại gạo]).\r\n• Trứng gà ta tươi sạch.\r\n• Dầu ăn tinh luyện.\r\n• Gia vị: muối, đường, tiêu, nước mắm,… ([ghi rõ thành phần gia vị cụ thể]).</p>\r\n\r\n<h3>Thông tin dinh dưỡng (trung bình cho 1 phần ăn):</h3>\r\n\r\n<p>(Lưu ý:  Thông tin dinh dưỡng là giá trị ước tính và có thể thay đổi tùy thuộc vào kích thước phần ăn.  Cần bổ sung thông tin dinh dưỡng cụ thể  từ phòng thí nghiệm kiểm định).\r\n• Năng lượng: [Số kcal]\r\n• Protein: [Số g]\r\n• Carbohydrate: [Số g]\r\n• Chất béo: [Số g]\r\n• Chất xơ: [Số g]</p>\r\n\r\n\r\n<h3>Quy trình sản xuất hiện đại, đảm bảo vệ sinh an toàn thực phẩm:</h3>\r\n\r\n<p>Sản phẩm Cơm bò chiên trứng được sản xuất trên dây chuyền công nghệ hiện đại, khép kín, đảm bảo vệ sinh an toàn thực phẩm theo tiêu chuẩn quốc gia.  Chúng tôi tuân thủ nghiêm ngặt các quy trình kiểm soát chất lượng, từ khâu lựa chọn nguyên liệu đến đóng gói sản phẩm, đảm bảo mang đến cho khách hàng sản phẩm chất lượng cao nhất.</p>\r\n\r\n\r\n<h2>Lợi Ích Khi Sử Dụng Cơm Bò Chiên Trứng</h2>\r\n\r\n<p>• Tiết kiệm thời gian:  Chỉ cần vài phút là bạn đã có ngay một bữa ăn ngon lành, không cần phải mất thời gian chuẩn bị nguyên liệu và nấu nướng.\r\n• Bổ dưỡng:  Cơm bò chiên trứng cung cấp đầy đủ các chất dinh dưỡng cần thiết cho cơ thể, bao gồm protein, carbohydrate, chất béo và các vitamin, khoáng chất.\r\n• Tiện lợi:  Sản phẩm được đóng gói tiện lợi, dễ dàng mang theo khi đi làm, đi học hoặc đi du lịch.\r\n• Hương vị thơm ngon:  Món ăn được chế biến với công thức đặc biệt, tạo nên hương vị thơm ngon, hấp dẫn, đảm bảo sẽ làm hài lòng khẩu vị của bạn.\r\n• An toàn vệ sinh thực phẩm:  Chúng tôi cam kết sử dụng nguyên liệu sạch, an toàn và sản xuất theo quy trình khép kín, đảm bảo vệ sinh an toàn thực phẩm.</p>\r\n\r\n\r\n<h2>Hướng Dẫn Sử Dụng</h2>\r\n\r\n<p>Sản phẩm được đóng gói sẵn, chỉ cần làm nóng lại trước khi ăn. Bạn có thể sử dụng lò vi sóng, lò nướng hoặc chảo để làm nóng sản phẩm.  (Ghi rõ hướng dẫn cụ thể, ví dụ: thời gian làm nóng, nhiệt độ…)</p>\r\n\r\n<p><strong>Lưu ý:</strong>  Sau khi làm nóng, hãy kiểm tra nhiệt độ trước khi ăn để tránh bị bỏng.</p>\r\n\r\n\r\n<h2>Mua Ngay Cơm Bò Chiên Trứng - Trải Nghiệm Hương Vị Tuyệt Vời!</h2>\r\n\r\n<p>Đừng bỏ lỡ cơ hội thưởng thức món Cơm bò chiên trứng thơm ngon, bổ dưỡng này!  Hãy đặt hàng ngay hôm nay để được hưởng những ưu đãi hấp dẫn.  Chúng tôi cam kết giao hàng nhanh chóng, tận nơi và đảm bảo chất lượng sản phẩm.  Click vào nút \"Mua ngay\" hoặc liên hệ với chúng tôi qua số điện thoại [Số điện thoại] để được tư vấn và đặt hàng.</p>\r\n\r\n<p><strong>Khuyến mãi đặc biệt:</strong> [Thêm các chương trình khuyến mãi, giảm giá, quà tặng…]</p>', 30000.00, 2, 'Cơm Bò , Cơm , Cơm Chiên Trứng', 'Cơm bò chiên trứng ngon tuyệt', 'cơm bò chiên trứng, bò chiên trứng, cơm bò, món ngon, đặt cơm online', 'Thưởng thức món cơm bò chiên trứng thơm ngon, đậm đà tại cửa hàng chúng tôi! Giao hàng tận nơi, nhanh chóng. Đặt món ngay hôm nay để nhận ưu đãi hấp dẫn! #cơmBò...', NULL, NULL, 3.00, NULL, NULL, 'com-bo-chien-trung', 1, 1),
(26, 'Cháo Thịt Bằm', '<ul><h1>Cháo Thịt Bằm - Món Ăn Dinh Dưỡng Hoàn Hảo Cho Cả Gia Đình</h1>\r\n\r\n<p>Chào mừng bạn đến với sản phẩm Cháo Thịt Bằm cao cấp của chúng tôi!  Sản phẩm được chế biến từ những nguyên liệu tươi ngon, đảm bảo an toàn vệ sinh thực phẩm, mang đến cho bạn và gia đình một bữa ăn thơm ngon, bổ dưỡng và tiện lợi.  Cháo Thịt Bằm là sự lựa chọn hoàn hảo cho những bữa ăn nhanh, những ngày bận rộn, hay đơn giản là muốn thưởng thức một món ăn truyền thống nhưng vẫn đảm bảo chất lượng cao.  Với hương vị thơm ngon, giàu dinh dưỡng, cháo thịt bằm chắc chắn sẽ làm hài lòng cả những người khó tính nhất.</p>\r\n\r\n<h2>Tại Sao Nên Chọn Cháo Thịt Bằm Của Chúng Tôi?</h2>\r\n\r\n<p>Trong cuộc sống hiện đại bận rộn, việc chuẩn bị những bữa ăn đầy đủ dinh dưỡng cho gia đình đôi khi trở nên khó khăn. Hiểu được điều đó, chúng tôi mang đến cho bạn Cháo Thịt Bằm - giải pháp hoàn hảo cho những bữa ăn nhanh chóng, tiện lợi mà vẫn đảm bảo chất lượng và dinh dưỡng.  Không chỉ là một món ăn đơn thuần, Cháo Thịt Bằm của chúng tôi còn mang đến nhiều lợi ích tuyệt vời cho sức khỏe của bạn và gia đình.</p>\r\n\r\n<h3>Nguyên Liệu Tươi Ngon, An Toàn Vệ Sinh Thực Phẩm</h3>\r\n\r\n<p>Chúng tôi cam kết sử dụng 100% nguyên liệu tươi ngon, được lựa chọn kỹ càng từ các nhà cung cấp uy tín.  Gạo được chọn lọc kỹ, hạt đều, thơm ngon,  thịt được xay nhuyễn mịn, không lẫn tạp chất.  Quá trình chế biến được thực hiện theo tiêu chuẩn vệ sinh an toàn thực phẩm nghiêm ngặt, đảm bảo mang đến cho bạn sản phẩm chất lượng cao nhất.  Chúng tôi tuyệt đối không sử dụng chất bảo quản, chất tạo màu hay phụ gia độc hại, đảm bảo an toàn cho sức khỏe của người tiêu dùng, đặc biệt là trẻ nhỏ và người già.</p>\r\n\r\n<h3>Hương Vị Thơm Ngon, Đầy Đủ Dinh Dưỡng</h3>\r\n\r\n<p>Cháo Thịt Bằm của chúng tôi sở hữu hương vị thơm ngon, đậm đà, hấp dẫn người dùng ngay từ lần đầu tiên thưởng thức.  Sự kết hợp hài hòa giữa vị ngọt thanh của gạo, vị thơm ngon của thịt bằm và gia vị được nêm nếm vừa phải tạo nên một món ăn hoàn hảo.  Sản phẩm cung cấp đầy đủ các chất dinh dưỡng cần thiết cho cơ thể như protein, chất xơ, vitamin và khoáng chất, giúp bổ sung năng lượng, tăng cường sức đề kháng và hỗ trợ hệ tiêu hóa khỏe mạnh.  Cháo Thịt Bằm rất dễ tiêu hóa, thích hợp cho mọi lứa tuổi, đặc biệt là trẻ em, người già và những người có hệ tiêu hóa yếu.</p>\r\n\r\n<h3>Quy Trình Sản Xuất Hiện Đại, Đảm Bảo Chất Lượng</h3>\r\n\r\n<p>Toàn bộ quy trình sản xuất Cháo Thịt Bằm được thực hiện trên dây chuyền công nghệ hiện đại, khép kín, đảm bảo chất lượng sản phẩm từ khâu lựa chọn nguyên liệu đến khâu đóng gói.  Chúng tôi luôn tuân thủ nghiêm ngặt các quy định về an toàn vệ sinh thực phẩm, đảm bảo sản phẩm luôn đạt chất lượng tốt nhất và an toàn cho sức khỏe người tiêu dùng.  Sản phẩm được đóng gói kỹ lưỡng, bảo quản ở điều kiện lý tưởng, giúp giữ được hương vị và chất lượng tốt nhất trong thời gian dài.</p>\r\n\r\n\r\n<h3>Thông Số Kỹ Thuật</h3>\r\n\r\n<p>Sản phẩm được đóng gói theo các loại: </p>\r\n<ul>\r\n    <li>Gói 200g</li>\r\n    <li>Gói 400g</li>\r\n    <li>Hũ 500g</li>\r\n</ul>\r\n<p>Hạn sử dụng:  (ghi rõ hạn sử dụng trên bao bì)</p>\r\n<p>Bảo quản:  Bảo quản ở nhiệt độ thường, nơi khô ráo, thoáng mát. Sau khi mở gói, nên sử dụng ngay.</p>\r\n\r\n\r\n<h3>Lợi Ích Sử Dụng Cháo Thịt Bằm</h3>\r\n\r\n<ul>\r\n    <li>Tiện lợi, tiết kiệm thời gian:  Chỉ cần hâm nóng là có thể thưởng thức ngay.</li>\r\n    <li>Bổ dưỡng, cung cấp đầy đủ chất dinh dưỡng cho cơ thể.</li>\r\n    <li>Dễ tiêu hóa, phù hợp với mọi lứa tuổi.</li>\r\n    <li>An toàn vệ sinh thực phẩm, không chứa chất bảo quản.</li>\r\n    <li>Hương vị thơm ngon, hấp dẫn.</li>\r\n    <li>Giá cả hợp lý.</li>\r\n</ul>\r\n\r\n\r\n<h3>Hướng Dẫn Sử Dụng</h3>\r\n\r\n<p>Để thưởng thức Cháo Thịt Bằm ngon nhất, bạn có thể làm theo các bước sau:</p>\r\n<ol>\r\n    <li>Mở gói sản phẩm.</li>\r\n    <li>Cho cháo vào tô hoặc chén.</li>\r\n    <li>Hâm nóng cháo bằng lò vi sóng hoặc đun cách thủy trong khoảng 3-5 phút.</li>\r\n    <li>Tùy thích, bạn có thể thêm rau sống, hành lá, tiêu… để tăng thêm hương vị.</li>\r\n    <li>Thưởng thức ngay khi còn nóng.</li>\r\n</ol>\r\n\r\n\r\n<h2>Mua Ngay Cháo Thịt Bằm để Trải Nghiệm Hương Vị Tuyệt Vời!</h2>\r\n\r\n<p>Đừng bỏ lỡ cơ hội thưởng thức Cháo Thịt Bằm thơm ngon, bổ dưỡng và tiện lợi. Hãy đặt hàng ngay hôm nay để nhận được nhiều ưu đãi hấp dẫn từ chúng tôi!  Click vào nút \"Mua ngay\" hoặc liên hệ với chúng tôi qua hotline để được tư vấn và đặt hàng nhanh chóng.  Chúng tôi cam kết mang đến cho bạn sự hài lòng tuyệt đối!</p>\r\n\r\n\r\n<p><strong>Click vào đây để đặt hàng ngay!</strong>  [Link đặt hàng]</p>\r\n</ul>', 20000.00, 1, NULL, 'Cháo Thịt Bằm ngon - Mẹ Bé Yêu thích', 'cháo thịt bằm, cháo trẻ em, cháo dinh dưỡng, thực phẩm dinh dưỡng trẻ em', 'Cháo thịt bằm thơm ngon, bổ dưỡng, dễ tiêu, thích hợp cho bé từ 6 tháng tuổi.  Nguyên liệu tươi sạch, chế biến kỹ càng.  Mua ngay cháo thịt bằm chất lượng cao t...', NULL, NULL, 0.00, NULL, NULL, 'chao-thit-bam', 1, 0),
(27, 'Mỳ ý Thịt Viên', '<h1>M&igrave; &Yacute; Thịt Vi&ecirc;n Cao Cấp: Hương Vị &Yacute; Đảo Ngọt Ng&agrave;o, Đậm Đ&agrave;</h1>\r\n<p>Kh&aacute;m ph&aacute; m&oacute;n m&igrave; &Yacute; thịt vi&ecirc;n thơm ngon, bổ dưỡng, chế biến đơn giản nhưng mang đến hương vị tuyệt vời cho cả gia đ&igrave;nh. Sản phẩm m&igrave; &Yacute; thịt vi&ecirc;n cao cấp của ch&uacute;ng t&ocirc;i được l&agrave;m từ nguy&ecirc;n liệu tươi ngon, kỹ thuật chế biến hiện đại, đảm bảo chất lượng v&agrave; an to&agrave;n vệ sinh thực phẩm. Với hương vị đậm đ&agrave;, kết hợp ho&agrave;n hảo giữa sợi m&igrave; &Yacute; dai ngon v&agrave; thịt vi&ecirc;n mềm ngọt, m&oacute;n ăn n&agrave;y sẽ chinh phục cả những thực kh&aacute;ch kh&oacute; t&iacute;nh nhất.</p>\r\n<h2>Thưởng Thức M&oacute;n M&igrave; &Yacute; Thịt Vi&ecirc;n: Sự Kết Hợp Ho&agrave;n Hảo Của Hương Vị V&agrave; Dinh Dưỡng</h2>\r\n<p>M&igrave; &Yacute; thịt vi&ecirc;n kh&ocirc;ng chỉ l&agrave; một m&oacute;n ăn ngon miệng m&agrave; c&ograve;n l&agrave; nguồn cung cấp dinh dưỡng dồi d&agrave;o cho cơ thể. Sản phẩm của ch&uacute;ng t&ocirc;i được chế biến từ những nguy&ecirc;n liệu được chọn lọc kỹ c&agrave;ng, đảm bảo chất lượng v&agrave; độ tươi ngon. Thịt vi&ecirc;n được l&agrave;m từ thịt heo/b&ograve; tươi ngon, kh&ocirc;ng chứa chất bảo quản, tạo n&ecirc;n hương vị thơm ngon tự nhi&ecirc;n. Sợi m&igrave; &Yacute; được l&agrave;m từ bột m&igrave; chất lượng cao, tạo n&ecirc;n độ dai v&agrave; mềm vừa phải, kh&ocirc;ng bị n&aacute;t khi nấu. Sự kết hợp ho&agrave;n hảo giữa m&igrave; &Yacute; v&agrave; thịt vi&ecirc;n mang đến một m&oacute;n ăn gi&agrave;u protein, chất xơ v&agrave; c&aacute;c vitamin cần thiết cho sức khỏe.</p>\r\n<h3>Nguy&ecirc;n Liệu Tuyệt Vời, Quy Tr&igrave;nh Sản Xuất Hiện Đại</h3>\r\n<p>Ch&uacute;ng t&ocirc;i cam kết mang đến cho kh&aacute;ch h&agrave;ng những sản phẩm chất lượng cao nhất. M&igrave; &Yacute; thịt vi&ecirc;n của ch&uacute;ng t&ocirc;i được sản xuất tr&ecirc;n d&acirc;y chuyền c&ocirc;ng nghệ hiện đại, đảm bảo vệ sinh an to&agrave;n thực phẩm. Nguy&ecirc;n liệu được lựa chọn kỹ c&agrave;ng, từ nguồn gốc xuất xứ đến chất lượng đều được kiểm so&aacute;t chặt chẽ. Thịt vi&ecirc;n được l&agrave;m từ thịt tươi ngon, kh&ocirc;ng sử dụng chất bảo quản, tạo n&ecirc;n hương vị thơm ngon tự nhi&ecirc;n. Sợi m&igrave; &Yacute; được l&agrave;m từ bột m&igrave; cao cấp, tạo n&ecirc;n độ dai, mềm vừa phải, kh&ocirc;ng bị n&aacute;t khi nấu. Quy tr&igrave;nh sản xuất kh&eacute;p k&iacute;n, tu&acirc;n thủ nghi&ecirc;m ngặt c&aacute;c ti&ecirc;u chuẩn vệ sinh an to&agrave;n thực phẩm, đảm bảo mang đến cho người ti&ecirc;u d&ugrave;ng sản phẩm an to&agrave;n v&agrave; chất lượng.</p>\r\n<h3>T&iacute;nh Năng Nổi Bật Của M&igrave; &Yacute; Thịt Vi&ecirc;n Cao Cấp</h3>\r\n<p>&bull; <strong>Hương vị thơm ngon, đậm đ&agrave;:</strong> Sự kết hợp ho&agrave;n hảo giữa sợi m&igrave; &Yacute; dai ngon v&agrave; thịt vi&ecirc;n mềm ngọt, tạo n&ecirc;n một m&oacute;n ăn hấp dẫn kh&oacute; cưỡng. <br>&bull; <strong>Nguy&ecirc;n liệu tươi ngon, chất lượng cao:</strong> Được l&agrave;m từ thịt heo/b&ograve; tươi ngon, kh&ocirc;ng chứa chất bảo quản, đảm bảo an to&agrave;n vệ sinh thực phẩm. <br>&bull; <strong>Dễ d&agrave;ng chế biến:</strong> Chỉ cần đun s&ocirc;i nước, cho m&igrave; v&agrave;o nấu ch&iacute;n, sau đ&oacute; th&ecirc;m thịt vi&ecirc;n v&agrave; gia vị t&ugrave;y th&iacute;ch. <br>&bull; <strong>Tiết kiệm thời gian:</strong> Gi&uacute;p bạn tiết kiệm thời gian nấu nướng, ph&ugrave; hợp với cuộc sống hiện đại bận rộn. <br>&bull; <strong>Đ&oacute;ng g&oacute;i tiện lợi:</strong> Sản phẩm được đ&oacute;ng g&oacute;i tiện lợi, dễ bảo quản v&agrave; vận chuyển.</p>\r\n<h3>Th&ocirc;ng Số Kỹ Thuật (&Aacute;p dụng cho g&oacute;i 500g l&agrave;m v&iacute; dụ)</h3>\r\n<p>&bull; <strong>T&ecirc;n sản phẩm:</strong> M&igrave; &Yacute; Thịt Vi&ecirc;n Cao Cấp <br>&bull; <strong>Khối lượng tịnh:</strong> 500g (c&oacute; thể thay đổi t&ugrave;y theo đ&oacute;ng g&oacute;i) <br>&bull; <strong>Th&agrave;nh phần:</strong> M&igrave; &Yacute; (bột m&igrave;, nước, muối, trứng), thịt heo/b&ograve; xay nhuyễn (tỷ lệ t&ugrave;y thuộc v&agrave;o loại sản phẩm), gia vị (muối, ti&ecirc;u, h&agrave;nh, tỏi,...) (liệt k&ecirc; chi tiết c&aacute;c loại gia vị) <br>&bull; <strong>Hạn sử dụng:</strong> (ghi r&otilde; hạn sử dụng, v&iacute; dụ: 6 th&aacute;ng kể từ ng&agrave;y sản xuất) <br>&bull; <strong>Hướng dẫn bảo quản:</strong> Bảo quản nơi kh&ocirc; r&aacute;o, tho&aacute;ng m&aacute;t, tr&aacute;nh &aacute;nh nắng trực tiếp. Sau khi mở g&oacute;i, n&ecirc;n bảo quản trong tủ lạnh.</p>\r\n<h3>Lợi &Iacute;ch Sử Dụng M&igrave; &Yacute; Thịt Vi&ecirc;n</h3>\r\n<p>&bull; <strong>Bổ sung dinh dưỡng:</strong> Cung cấp nguồn protein, chất xơ v&agrave; c&aacute;c vitamin cần thiết cho sức khỏe. <br>&bull; <strong>Tiết kiệm thời gian:</strong> Gi&uacute;p bạn tiết kiệm thời gian nấu nướng, nhanh ch&oacute;ng v&agrave; tiện lợi. <br>&bull; <strong>M&oacute;n ăn ngon miệng:</strong> Mang đến một bữa ăn ngon miệng, hấp dẫn cho cả gia đ&igrave;nh. <br>&bull; <strong>Th&iacute;ch hợp cho nhiều đối tượng:</strong> Ph&ugrave; hợp với mọi lứa tuổi, từ trẻ em đến người lớn. <br>&bull; <strong>Đa dạng c&aacute;ch chế biến:</strong> C&oacute; thể chế biến th&agrave;nh nhiều m&oacute;n ăn kh&aacute;c nhau, t&ugrave;y theo sở th&iacute;ch của bạn.</p>\r\n<h3>Hướng Dẫn Sử Dụng</h3>\r\n<p>1. Đun s&ocirc;i một lượng nước vừa đủ trong nồi. <br>2. Cho m&igrave; &Yacute; v&agrave;o nồi nước s&ocirc;i, đun trong khoảng 8-10 ph&uacute;t (hoặc theo hướng dẫn tr&ecirc;n bao b&igrave;) cho đến khi m&igrave; ch&iacute;n mềm. <br>3. Vớt m&igrave; ra, để r&aacute;o nước. <br>4. Cho thịt vi&ecirc;n v&agrave;o chảo, x&agrave;o sơ qua cho n&oacute;ng. <br>5. Trộn m&igrave; &Yacute; v&agrave; thịt vi&ecirc;n, th&ecirc;m gia vị t&ugrave;y th&iacute;ch (nước tương, tương ớt, dầu &ocirc; liu&hellip;)</p>\r\n<h3>Lời K&ecirc;u Gọi H&agrave;nh Động</h3>\r\n<p>Bạn đ&atilde; sẵn s&agrave;ng thưởng thức m&oacute;n m&igrave; &Yacute; thịt vi&ecirc;n thơm ngon, bổ dưỡng n&agrave;y chưa? H&atilde;y đặt h&agrave;ng ngay h&ocirc;m nay để trải nghiệm hương vị tuyệt vời của sản phẩm! Click v&agrave;o n&uacute;t \"Mua ngay\" hoặc li&ecirc;n hệ với ch&uacute;ng t&ocirc;i qua hotline để được tư vấn v&agrave; đặt h&agrave;ng. Số lượng c&oacute; hạn, h&atilde;y nhanh tay đặt h&agrave;ng để kh&ocirc;ng bỏ lỡ cơ hội thưởng thức m&oacute;n ăn tuyệt vời n&agrave;y!</p>', NULL, 1, NULL, 'Mỳ Ý Thịt Viên ngon tuyệt - Giao tận nơi', 'Mỳ Ý Thịt Viên, mì ý, thịt viên, món ăn ngon', 'Thưởng thức ngay Mỳ Ý Thịt Viên thơm ngon, đậm đà, được chế biến từ nguyên liệu tươi sạch.  Giao hàng nhanh chóng, tận nơi.  Miễn phí vận chuyển cho đơn hàng tr...', NULL, NULL, 3.00, NULL, NULL, 'my-y-thit-vien', 1, 1),
(28, 'Cơm gà kho gừng', '<ul><h1>Cơm Gà Kho Gừng: Hương Vị Truyền Thống, Món Ăn Ngon Tuyệt Đỉnh</h1>\r\n\r\n<p>Món cơm gà kho gừng, một món ăn dân dã nhưng lại mang đậm hương vị truyền thống Việt Nam, nay đã có mặt tại cửa hàng chúng tôi.  Với công thức gia truyền, kết hợp cùng nguyên liệu tươi ngon, chúng tôi cam kết mang đến cho bạn trải nghiệm ẩm thực tuyệt vời nhất.  Món ăn không chỉ thơm ngon, hấp dẫn mà còn được chế biến đảm bảo vệ sinh an toàn thực phẩm, thích hợp cho cả gia đình thưởng thức.</p>\r\n\r\n\r\n<h2>Hương vị đặc trưng của Cơm Gà Kho Gừng</h2>\r\n\r\n<p>Cơm gà kho gừng của chúng tôi khác biệt với những món ăn khác trên thị trường bởi sự kết hợp hài hòa giữa vị ngọt của thịt gà, vị cay nhẹ của gừng, và hương thơm nồng nàn của các loại gia vị.  Thịt gà được lựa chọn kỹ càng, đảm bảo tươi ngon, chắc thịt.  Gừng được sử dụng là gừng tươi, được chọn lọc, xay nhuyễn để giữ nguyên vẹn hương vị và độ cay đặc trưng.  Quá trình kho được thực hiện tỉ mỉ, giúp thịt gà thấm gia vị, mềm mại và không bị khô. Hạt cơm được nấu dẻo, tơi, hấp thụ trọn vẹn hương vị của nước kho gà, tạo nên một món ăn hoàn hảo.</p>\r\n\r\n\r\n<h3>Nguyên liệu tươi ngon, chế biến sạch sẽ</h3>\r\n\r\n<p>Chúng tôi luôn đặt chất lượng lên hàng đầu.  Tất cả nguyên liệu làm nên món cơm gà kho gừng đều được chọn lọc kỹ càng, đảm bảo tươi ngon và an toàn vệ sinh thực phẩm. Gà ta được chọn lựa từ những nguồn cung cấp uy tín, đảm bảo chất lượng thịt thơm ngon, không chất tạo nạc. Gừng tươi được nhập khẩu từ những vùng trồng gừng nổi tiếng, đảm bảo độ cay và hương thơm đặc trưng.  Gia vị được sử dụng là các loại gia vị tự nhiên, không chứa chất bảo quản, tạo màu hay phụ gia độc hại.  Toàn bộ quy trình chế biến được thực hiện trong môi trường sạch sẽ, đảm bảo vệ sinh an toàn thực phẩm theo tiêu chuẩn.</p>\r\n\r\n\r\n<h2>Tính năng nổi bật của sản phẩm Cơm Gà Kho Gừng</h2>\r\n\r\n<p>Cơm gà kho gừng của chúng tôi có những tính năng nổi bật sau:</p>\r\n<ul>\r\n    <li><b>Hương vị đậm đà, hấp dẫn:</b> Sự kết hợp hoàn hảo giữa vị ngọt của gà, cay nhẹ của gừng và thơm của gia vị tạo nên một hương vị khó quên.</li>\r\n    <li><b>Thịt gà mềm, ngon:</b>  Thịt gà được kho kỹ càng, mềm mại, không bị khô, dễ ăn.</li>\r\n    <li><b>Cơm dẻo, tơi:</b> Hạt cơm được nấu dẻo, tơi, thấm đẫm vị ngọt của nước kho gà.</li>\r\n    <li><b>Nguyên liệu tươi ngon, an toàn:</b> Sử dụng 100% nguyên liệu tươi ngon, an toàn vệ sinh thực phẩm.</li>\r\n    <li><b>Tiện lợi, tiết kiệm thời gian:</b>  Sản phẩm được đóng gói tiện lợi, giúp bạn tiết kiệm thời gian chuẩn bị bữa ăn.</li>\r\n    <li><b>Đóng gói kỹ lưỡng:</b> Sản phẩm được đóng gói kín đáo, giữ được độ tươi ngon và hương vị.</li>\r\n</ul>\r\n\r\n\r\n<h3>Thông số kỹ thuật (áp dụng nếu sản phẩm đóng gói sẵn)</h3>\r\n\r\n<p>\r\n<li><strong>Khối lượng:</strong>  [Ví dụ: 500g/ hộp]</li><li><strong>Thành phần:</strong> [Liệt kê đầy đủ thành phần, ví dụ: Gà ta, gừng tươi, nước mắm, đường, muối, tiêu...]</li><li><strong>Ngày sản xuất/Hạn sử dụng:</strong> [Ghi rõ ngày sản xuất và hạn sử dụng]</li><li><strong>Hướng dẫn bảo quản:</strong> [Ví dụ: Bảo quản ở nhiệt độ từ 0-4 độ C, sử dụng trong vòng 24h sau khi mở bao bì]</li></p>\r\n\r\n\r\n<h2>Lợi ích khi sử dụng Cơm Gà Kho Gừng</h2>\r\n\r\n<p>Sử dụng cơm gà kho gừng của chúng tôi mang lại nhiều lợi ích:</p>\r\n<ul>\r\n    <li><b>Tiết kiệm thời gian:</b> Bạn không cần mất nhiều thời gian để chuẩn bị nguyên liệu và chế biến món ăn.</li>\r\n    <li><b>Bữa ăn ngon miệng, đầy đủ dinh dưỡng:</b> Cơm gà kho gừng cung cấp đầy đủ chất dinh dưỡng cần thiết cho cơ thể.</li>\r\n    <li><b>Thưởng thức hương vị truyền thống:</b>  Bạn có thể thưởng thức hương vị đậm đà của món ăn truyền thống Việt Nam một cách tiện lợi.</li>\r\n    <li><b>Tuyệt vời cho cả gia đình:</b>  Đây là món ăn thích hợp cho mọi lứa tuổi trong gia đình.</li>\r\n    <li><b>Dễ dàng chế biến:</b> Chỉ cần làm nóng lại là bạn đã có ngay một bữa ăn ngon.</li>\r\n</ul>\r\n\r\n\r\n<h3>Hướng dẫn sử dụng (nếu cần)</h3>\r\n\r\n<p>Để thưởng thức món cơm gà kho gừng ngon nhất, bạn có thể làm nóng lại bằng lò vi sóng hoặc hấp cách thủy.  Không nên chiên hoặc rán sản phẩm.</p>\r\n\r\n\r\n<h2>Lời kêu gọi hành động</h2>\r\n\r\n<p>Đừng bỏ lỡ cơ hội thưởng thức món cơm gà kho gừng thơm ngon, đậm đà hương vị truyền thống.  Hãy đặt hàng ngay hôm nay để nhận được ưu đãi hấp dẫn!  Click vào nút \"Mua ngay\" hoặc liên hệ với chúng tôi qua hotline [Số điện thoại] để được tư vấn và đặt hàng.  Số lượng có hạn, nhanh tay đặt hàng để không bỏ lỡ nhé!</p>\r\n</ul>', NULL, 1, NULL, 'Cơm gà kho gừng ngon tuyệt - Món ngon mỗi ngày', 'cơm gà kho gừng, cơm gà, gà kho gừng, món ngon Việt Nam', 'Cơm gà kho gừng đậm đà, thơm ngon với công thức gia truyền. Gà mềm, cơm dẻo, thấm đẫm vị gừng cay nồng.  Nhanh tay đặt hàng ngay hôm nay để thưởng thức món ăn h...', NULL, NULL, 0.00, NULL, NULL, 'com-ga-kho-gung', 1, 1),
(29, 'Cháo Thịt Bằm', '<p>Ch&aacute;o Thịt Bằm</p>', 20000.00, 0, 'Cháo Thịt Bằm', 'Cháo Thịt Bằm', 'Cháo Thịt Bằm', '<p>Ch&aacute;o Thịt Bằm</p>', NULL, NULL, 0.00, NULL, NULL, 'chao-thit-bam-45', 1, 1),
(30, 'Cháo Thịt Bằm0925', NULL, 20000.00, 1, NULL, 'Cháo Thịt Bằm', 'Cháo Thịt Bằm', '<p>Ch&aacute;o Thịt Bằm</p>', NULL, NULL, 8.00, NULL, NULL, 'chao-thit-bam0925', 1, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `san_pham_bien_the`
--

CREATE TABLE `san_pham_bien_the` (
  `bien_the_id` int(11) NOT NULL,
  `san_pham_id` int(11) DEFAULT NULL,
  `ten_bien_the` varchar(255) NOT NULL,
  `kich_thuoc` varchar(100) DEFAULT NULL,
  `mau_sac` varchar(100) DEFAULT NULL,
  `trong_luong` int(11) DEFAULT NULL COMMENT 'Đơn vị gram',
  `chieu_dai` int(11) DEFAULT NULL COMMENT 'Đơn vị cm',
  `chieu_rong` int(11) DEFAULT NULL COMMENT 'Đơn vị cm',
  `chieu_cao` int(11) DEFAULT NULL COMMENT 'Đơn vị cm',
  `so_luong_ton_kho` int(11) DEFAULT NULL,
  `gia` decimal(15,2) DEFAULT NULL,
  `ngay_tao` datetime DEFAULT NULL,
  `ngay_sua` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `san_pham_bien_the`
--

INSERT INTO `san_pham_bien_the` (`bien_the_id`, `san_pham_id`, `ten_bien_the`, `kich_thuoc`, `mau_sac`, `trong_luong`, `chieu_dai`, `chieu_rong`, `chieu_cao`, `so_luong_ton_kho`, `gia`, `ngay_tao`, `ngay_sua`) VALUES
(18, 27, 'Mỳ Ý Thịt Viên', 'Phần Lớn', '', NULL, NULL, NULL, NULL, 15, 40000.00, NULL, NULL),
(19, 27, 'Mỳ Ý Thịt Viên ', 'Phần Nhỏ', '', NULL, NULL, NULL, NULL, 7, 30000.00, NULL, NULL),
(24, 25, 'Cơm Bò Chiên Trứng', 'Phần Lớn', 'Không', NULL, NULL, NULL, NULL, 9, 35000.00, NULL, NULL),
(25, 24, 'Bánh Mì Croissant ', 'Phần Lớn', 'Đen', NULL, NULL, NULL, NULL, 19, 18000.00, NULL, NULL),
(26, 24, 'Bánh Mì Croissant ', 'Phần Nhỏ', 'Cam', NULL, NULL, NULL, NULL, 3, 15000.00, NULL, NULL),
(30, 28, 'Cơm gà kho gừng', 'Phần Lớn', '', NULL, NULL, NULL, NULL, 0, 40000.00, NULL, NULL),
(36, 30, 'Cháo Thịt Bằm ', 'Phần Lớn', '', NULL, NULL, NULL, NULL, 10, 30000.00, NULL, NULL);

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
('HfHauB0TYAJEvehldIQNdsam4AM3ApT7g8K3l8nI', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQUw0bFdJd3MwVHRiYmpWTXhPU2JoZjA5REVJdTloQURyejd4VjdIQiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1751332683),
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
-- Cấu trúc bảng cho bảng `slide`
--

CREATE TABLE `slide` (
  `slide_id` int(10) UNSIGNED NOT NULL,
  `ten_slide` varchar(100) NOT NULL,
  `hien_thi` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `slide`
--

INSERT INTO `slide` (`slide_id`, `ten_slide`, `hien_thi`) VALUES
(5, 'Trang Chủ', 1),
(6, 'Liên Hệ', 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `slide_hinh_anh`
--

CREATE TABLE `slide_hinh_anh` (
  `id` int(10) UNSIGNED NOT NULL,
  `slide_id` int(10) UNSIGNED NOT NULL,
  `duong_dan` varchar(255) NOT NULL,
  `dieu_huong` varchar(255) DEFAULT NULL,
  `thu_tu` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `slide_hinh_anh`
--

INSERT INTO `slide_hinh_anh` (`id`, `slide_id`, `duong_dan`, `dieu_huong`, `thu_tu`) VALUES
(19, 5, 'slides/FQaGLRBSbRjJ2l7BMQPTcJsySMJdWVw76mZke7sd.png', NULL, 1),
(20, 5, 'slides/v9Wz53QrNL6mcMTsBfchily8bm9EusrYlfh9kc8n.png', NULL, 2),
(21, 5, 'slides/WL07QzSSBD4n65dYOb7P26yYZL9iIXmP4QVTijHc.png', NULL, 3),
(22, 6, 'slides/bwcvtzmLeODLCr0WZEPfgEEfEFdYOj0ZHJnIPL1i.png', NULL, 1),
(23, 6, 'slides/FVHFwZeBDBA0wnfYhjsbpYARi10Xu0jB6b268vWl.png', NULL, 2),
(24, 6, 'slides/grnC3fFCR8aIr5NAZ57Tsnkj3GKIFxQfEaulMSYY.png', NULL, 3);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thong_bao`
--

CREATE TABLE `thong_bao` (
  `id` int(11) NOT NULL,
  `loai_thong_bao` varchar(50) DEFAULT NULL,
  `mo_ta` varchar(255) DEFAULT NULL,
  `tin_bao` text DEFAULT NULL,
  `da_xem` tinyint(1) DEFAULT 0,
  `ngay_tao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tintuc`
--

CREATE TABLE `tintuc` (
  `id` int(11) NOT NULL,
  `trang_thai` tinyint(1) DEFAULT 1,
  `id_danh_muc_tin_tuc` int(11) DEFAULT NULL,
  `tieude` varchar(255) DEFAULT NULL,
  `hinh_anh` varchar(100) DEFAULT NULL,
  `noidung` text DEFAULT NULL,
  `ngay_dang` datetime DEFAULT NULL,
  `duyet_tin_tuc` tinyint(1) DEFAULT 0,
  `noi_bat` tinyint(1) DEFAULT 0,
  `slug` varchar(225) DEFAULT NULL,
  `luot_like` int(11) DEFAULT 0,
  `luot_xem` int(11) DEFAULT 0,
  `tieu_de_seo` varchar(255) DEFAULT NULL,
  `mo_ta_seo` text DEFAULT NULL,
  `tu_khoa_seo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `tintuc`
--

INSERT INTO `tintuc` (`id`, `trang_thai`, `id_danh_muc_tin_tuc`, `tieude`, `hinh_anh`, `noidung`, `ngay_dang`, `duyet_tin_tuc`, `noi_bat`, `slug`, `luot_like`, `luot_xem`, `tieu_de_seo`, `mo_ta_seo`, `tu_khoa_seo`) VALUES
(31, 1, 9, 'Bánh Mì Ổ Buổi Sáng – Sản Phẩm Mới Giúp Bạn Khởi Đầu Ngày Mới Trọn Vẹn!', 'Tintuc/QsgOZeyGt8g9p8CbJlh5tQbtHG8RRUS435WMShaa.png', '{\"time\":1751335778764,\"blocks\":[{\"id\":\"83OO2WStVM\",\"type\":\"paragraph\",\"data\":{\"text\":\"Chúng tôi hân hạnh giới thiệu sản phẩm mới – Bánh Mì Ổ Buổi Sáng, lựa chọn hoàn hảo cho bữa sáng đầy năng lượng!\"}},{\"id\":\"-f3Dy5yANG\",\"type\":\"paragraph\",\"data\":{\"text\":\"Sản phẩm được làm từ những nguyên liệu tươi sạch, kết hợp giữa lớp vỏ bánh giòn rụm và phần nhân đậm đà, mang đến trải nghiệm ẩm thực không thể bỏ qua.\"}},{\"id\":\"y76FmOIK7Z\",\"type\":\"paragraph\",\"data\":{\"text\":\"🔍 Điểm nổi bật:\"}},{\"id\":\"TQiG91cGTS\",\"type\":\"list\",\"data\":{\"style\":\"unordered\",\"meta\":{},\"items\":[{\"content\":\"Ngon – Sạch – Nhanh chóng: Bánh được làm trong ngày, đảm bảo chất lượng và vệ sinh.\\n\",\"meta\":{},\"items\":[]},{\"content\":\"Đa dạng nhân bánh: Chả lụa, pate, thịt nguội, trứng ốp la, chà bông,...\\n\",\"meta\":{},\"items\":[]},{\"content\":\"Giao hàng tận nơi toàn quốc với đội ngũ vận chuyển nhanh và chuyên nghiệp.\\n\",\"meta\":{},\"items\":[]}]}},{\"id\":\"In6xauyazK\",\"type\":\"paragraph\",\"data\":{\"text\":\"\\n🎁 Ưu đãi đặc biệt trong tháng 7:\"}},{\"id\":\"1PBAho0ocC\",\"type\":\"list\",\"data\":{\"style\":\"unordered\",\"meta\":{},\"items\":[{\"content\":\"Giảm ngay 15% cho đơn hàng đầu tiên.\\n\",\"meta\":{},\"items\":[]},{\"content\":\"Miễn phí giao hàng cho khu vực nội thành Hồ Chí Minh.\\n\",\"meta\":{},\"items\":[]},{\"content\":\"Tặng thêm 1 bánh miễn phí khi mua từ 5 ổ trở lên.\\n\",\"meta\":{},\"items\":[]}]}},{\"id\":\"C3CZsGZJgE\",\"type\":\"paragraph\",\"data\":{\"text\":\"\\n📌 Đặt hàng ngay hôm nay tại:\"}},{\"id\":\"L24F8Ii5Ir\",\"type\":\"paragraph\",\"data\":{\"text\":\"👉 Website: <a href=\\\"#\\\">banhmi404.vn</a>\\n👉 Hotline: 1900 404 404\\n👉 Hoặc đặt qua app giao hàng yêu thích của bạn\"}}],\"version\":\"2.31.0-rc.7\"}', '2025-07-01 00:00:00', 1, 1, 'banh-mi-o-buoi-sang-san-pham-moi-giup-ban-khoi-au-ngay-moi-tron-ven', 0, 0, NULL, NULL, NULL),
(32, 1, 8, 'Gai xinh', 'Tintuc/T2RDm9XP3sTjXDrx98dM5zy0VNHw4spmh2EI6Aog.jpg', '<body>\r\n<h1>\"Gai xinh\": Thực trạng và tác động của thuật ngữ gây tranh cãi trên mạng xã hội Việt Nam</h1>\r\n\r\n<p><strong>Giới thiệu:</strong>  Thuật ngữ \"gai xinh\" gần đây xuất hiện tràn lan trên các nền tảng mạng xã hội Việt Nam, thu hút sự chú ý của đông đảo người dùng. Tuy nhiên, bên cạnh sự tò mò và thích thú, nó cũng gây ra nhiều tranh luận về tính phù hợp, văn hóa và tác động xã hội. Bài viết này sẽ đi sâu phân tích hiện tượng này, từ nguồn gốc, ý nghĩa cho đến những hệ quả mà nó mang lại.</p>\r\n\r\n<img src=\"image_gai_xinh.jpg\" alt=\"Hình ảnh minh họa về hiện tượng \'gai xinh\'\" width=\"600\">\r\n\r\n<p><strong>Nội dung chính:</strong></p>\r\n\r\n<h2>Nguồn gốc và ý nghĩa của thuật ngữ \"gai xinh\"</h2>\r\n<p>Thuật ngữ \"gai xinh\" xuất phát từ đâu? Hiện chưa có một nguồn gốc chính thức được xác định. Tuy nhiên, nhiều người cho rằng nó bắt nguồn từ việc ghép hai từ \"gai\" (có thể hiểu là \"gái\" theo cách viết tắt, hoặc ám chỉ sự quyến rũ, nổi bật) và \"xinh\".  Từ đó, \"gai xinh\" được sử dụng để chỉ những cô gái trẻ, xinh đẹp, thu hút sự chú ý trên mạng xã hội.  Tuy nhiên, nghĩa của từ này khá mơ hồ và dễ bị hiểu sai lệch, tạo nên sự nhầm lẫn và tranh cãi.</p>\r\n\r\n<h2>Sự lan truyền và phổ biến của \"gai xinh\" trên mạng xã hội</h2>\r\n<p>Nhờ tính chất ngắn gọn, dễ nhớ và gây tò mò, thuật ngữ \"gai xinh\" nhanh chóng lan truyền trên các nền tảng mạng xã hội như Facebook, TikTok, Instagram...  Việc sử dụng hashtag #gaixinh càng góp phần đẩy mạnh sự phổ biến của nó.  Nhiều người dùng sử dụng từ này để miêu tả những hình ảnh, video về các cô gái xinh đẹp, tạo nên một cộng đồng người dùng khá đông đảo.</p>\r\n\r\n<ul>\r\n<li><strong>Mặt tích cực:</strong> Thuật ngữ này giúp tạo ra một không gian chia sẻ hình ảnh, video về người mẫu, diễn viên, người nổi tiếng hoặc các cô gái có ngoại hình thu hút.</li>\r\n<li><strong>Mặt tiêu cực:</strong> Việc sử dụng rộng rãi và thiếu kiểm soát dễ dẫn đến sự lạm dụng, biến tướng, gây ra những hệ quả tiêu cực.</li>\r\n</ul>\r\n\r\n\r\n<h2>Những hệ quả tiêu cực của việc sử dụng thuật ngữ \"gai xinh\"</h2>\r\n<h3>Vấn đề về văn hóa và đạo đức</h3>\r\n<p>Việc sử dụng thuật ngữ \"gai xinh\" một cách tùy tiện có thể bị coi là thiếu tôn trọng phụ nữ,  khái quát hóa vẻ đẹp và làm giảm giá trị con người xuống chỉ còn hình thức bên ngoài. Nó có thể góp phần vào việc thúc đẩy văn hóa \"body shaming\" và tạo ra áp lực thẩm mỹ không lành mạnh cho giới trẻ.</p>\r\n\r\n<h3>Nguy cơ bị lợi dụng và quấy rối trực tuyến</h3>\r\n<p>Nhiều người dùng mạng xã hội lợi dụng thuật ngữ \"gai xinh\" để thu hút sự chú ý, đăng tải những nội dung phản cảm, khiêu khích, thậm chí là quấy rối, tấn công tình dục người khác.  Điều này đòi hỏi sự quản lý chặt chẽ hơn từ các nền tảng mạng xã hội.</p>\r\n\r\n<h3>Ảnh hưởng đến hình ảnh của phụ nữ Việt Nam</h3>\r\n<p>Việc sử dụng quá mức và thiếu kiểm soát thuật ngữ \"gai xinh\" có thể tạo ra ấn tượng tiêu cực về hình ảnh phụ nữ Việt Nam trong mắt cộng đồng quốc tế.  Điều này ảnh hưởng đến sự phát triển toàn diện và vị thế của phụ nữ trong xã hội.</p>\r\n\r\n\r\n<p><strong>Phân tích:</strong>  Hiện tượng \"gai xinh\" phản ánh một thực trạng đáng báo động về văn hóa mạng xã hội Việt Nam.  Việc thiếu ý thức, quản lý lỏng lẻo và sự thiếu hiểu biết về tác động của ngôn từ trên mạng xã hội đang tạo điều kiện cho những hiện tượng tiêu cực này phát triển.</p>\r\n\r\n<p><strong>Kết luận:</strong> Thuật ngữ \"gai xinh\" cần được xem xét lại về tính phù hợp và tác động xã hội.  Việc sử dụng ngôn từ có trách nhiệm, tôn trọng và ý thức về văn hóa trên mạng xã hội là điều cần thiết để xây dựng một cộng đồng mạng lành mạnh và tích cực.</p>\r\n\r\n<p><strong>Lời kêu gọi hành động:</strong>  Chúng ta cần cùng nhau tạo ra một môi trường mạng xã hội lành mạnh, nơi mà ngôn từ được sử dụng có trách nhiệm và tôn trọng. Hãy cẩn trọng trong việc sử dụng ngôn từ trên mạng xã hội và cùng lên tiếng phản đối những hành vi sử dụng thuật ngữ \"gai xinh\" một cách thiếu văn hóa và gây hại.</p>\r\n</body>', '2025-07-05 00:00:00', 0, 0, 'gai-xinh', 0, 0, 'Ảnh gái xinh cực hot - cập nhật liên tục', 'Tuyệt phẩm ảnh gái xinh nóng bỏng nhất, cập nhật liên tục mỗi ngày chỉ có tại đây!  Cùng chiêm ngưỡng vẻ đẹp quyến rũ, thu hút của các người mẫu, hot girl Việt...', 'gái xinh, ảnh gái xinh, hình gái xinh, hot girl');

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

--
-- Đang đổ dữ liệu cho bảng `trang_tinh`
--

INSERT INTO `trang_tinh` (`Trang_tinh_id`, `Ten_trang`, `Tieu_de_trang`, `Noi_dung_trang`) VALUES
(1, 'gioi-thieu', 'Giới Thiệu', '{\"time\":1750600151759,\"blocks\":[{\"id\":\"dRiXQEXgz4\",\"type\":\"header\",\"data\":{\"text\":\"Chào mừng đến với FoF Mart – Siêu thị online cho mọi nhà!\",\"level\":2}},{\"id\":\"b5C-VLduqD\",\"type\":\"paragraph\",\"data\":{\"text\":\"FoF Mart là siêu thị trực tuyến hiện đại, chuyên cung cấp hàng hóa tiêu dùng thiết yếu với chất lượng đảm bảo, giá cả hợp lý và dịch vụ giao hàng nhanh chóng trên toàn quốc.\"}},{\"id\":\"viExBNmV6k\",\"type\":\"paragraph\",\"data\":{\"text\":\"FoF Mart cam kết mang đến trải nghiệm mua sắm tiện lợi, an toàn và thông minh cho người tiêu dùng Việt, thông qua nền tảng thương mại điện tử hiện đại và dịch vụ tận tâm.\"}},{\"id\":\"6nqvhfjqlU\",\"type\":\"list\",\"data\":{\"style\":\"unordered\",\"meta\":{},\"items\":[{\"content\":\"Chất lượng sản phẩm là ưu tiên hàng đầu\",\"meta\":{},\"items\":[]},{\"content\":\"Khách hàng là trung tâm của mọi hoạt động\",\"meta\":{},\"items\":[]},{\"content\":\"Giao hàng nhanh – Đúng hẹn – An toàn\",\"meta\":{},\"items\":[]},{\"content\":\"Minh bạch, chính trực trong kinh doanh\",\"meta\":{},\"items\":[]}]}},{\"id\":\"CX5nYMc9Lg\",\"type\":\"paragraph\",\"data\":{\"text\":\"FoF Mart – Mua sắm dễ dàng, tận hưởng mỗi ngày.\"}}],\"version\":\"2.31.0-rc.7\"}'),
(4, 'dieu-khoan', 'Điều khoản sử dụng', NULL),
(5, 'tam-nhin-su-menh', 'Tầm nhìn, Sứ mệnh và Giá trị cốt lõi', '{\"time\":1750602777253,\"blocks\":[{\"id\":\"PhbzJF4Pfm\",\"type\":\"header\",\"data\":{\"text\":\"Tầm Nhìn, Sứ Mệnh và Giá Trị Cốt Lõi của FoF Mart\",\"level\":2}},{\"id\":\"NFI2a32KEG\",\"type\":\"paragraph\",\"data\":{\"text\":\"<b>Tầm nhìn</b>\"}},{\"id\":\"smNYuQ8G5Y\",\"type\":\"paragraph\",\"data\":{\"text\":\"FoF Mart hướng tới trở thành siêu thị trực tuyến hàng đầu tại Việt Nam – nơi mọi người có thể mua sắm mọi thứ một cách dễ dàng, nhanh chóng và tin cậy, từ bất kỳ đâu, vào bất kỳ thời điểm nào.\"}},{\"id\":\"gBUUts5UoH\",\"type\":\"paragraph\",\"data\":{\"text\":\"<b>Sứ mệnh</b>\"}},{\"id\":\"HfxUNEJrRx\",\"type\":\"paragraph\",\"data\":{\"text\":\"Chúng tôi cam kết mang đến trải nghiệm mua sắm tiện lợi, chất lượng và thân thiện với người dùng. FoF Mart không chỉ là nơi bán hàng, mà còn là người bạn đồng hành trong cuộc sống hiện đại, giúp khách hàng tiết kiệm thời gian, công sức và chi phí.\"}},{\"id\":\"W1b7i3jpu6\",\"type\":\"paragraph\",\"data\":{\"text\":\"&nbsp;&nbsp;<b>Giá trị cốt lõi</b>\"}},{\"id\":\"ErIsiyJefU\",\"type\":\"paragraph\",\"data\":{\"text\":\"Khách hàng là trung tâm:Luôn đặt lợi ích và sự hài lòng của khách hàng lên hàng đầu.\"}},{\"id\":\"NX3VZmR5ln\",\"type\":\"paragraph\",\"data\":{\"text\":\"Chất lượng và minh bạch:Sản phẩm rõ nguồn gốc, chất lượng đảm bảo, thông tin minh bạch.\"}}],\"version\":\"2.31.0-rc.7\"}'),
(7, 'lien-he', 'Liên Hệ', '{\"time\":1750605441805,\"blocks\":[{\"id\":\"gxfBKEo1JR\",\"type\":\"header\",\"data\":{\"text\":\"CÔNG TY TNHH NĂM THÀNH VIÊN 404 NOT FUOND\",\"level\":4}},{\"id\":\"2yO-lHIYx0\",\"type\":\"paragraph\",\"data\":{\"text\":\"<b>Ngành:</b> Buôn bán bột trắng\"}},{\"id\":\"djgtZTJUEQ\",\"type\":\"paragraph\",\"data\":{\"text\":\"<b>Email:</b> 404notfuond@gmail.com\"}},{\"id\":\"H7dSnQkGLQ\",\"type\":\"paragraph\",\"data\":{\"text\":\"<b>Sđt</b>: 09222025\"}},{\"id\":\"9zW_26MnNl\",\"type\":\"paragraph\",\"data\":{\"text\":\"<b>Địa chỉ:</b> Công viên phần mềm quang trung , Q12 , Hồ Chí Minh\"}}],\"version\":\"2.31.0-rc.7\"}'),
(8, 'khoi-nguon', 'Khởi nguồn', '{\"time\":1750605996125,\"blocks\":[{\"id\":\"_kf8bWBHUB\",\"type\":\"header\",\"data\":{\"text\":\"FoF Mart được ra đời từ một câu hỏi đơn giản nhưng đầy trăn trở:\",\"level\":5}},{\"id\":\"LbzWaPKbfp\",\"type\":\"paragraph\",\"data\":{\"text\":\"\\\"Tại sao việc mua những sản phẩm thiết yếu hàng ngày lại mất quá nhiều thời gian và công sức?\\\"\"}},{\"id\":\"VeH6Nk1gwU\",\"type\":\"paragraph\",\"data\":{\"text\":\"Trong bối cảnh cuộc sống ngày càng bận rộn, người tiêu dùng hiện đại mong muốn sự tiện lợi, nhanh chóng nhưng vẫn đảm bảo chất lượng và giá cả hợp lý. FoF Mart ra đời với sứ mệnh kết nối người tiêu dùng với nguồn hàng uy tín, mang siêu thị đến tận nhà, chỉ bằng vài cú click.\"}}],\"version\":\"2.31.0-rc.7\"}');

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
(0, 'user', 'người dung\r\n'),
(1, 'admin', 'quản trị viên');

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
-- Chỉ mục cho bảng `slide`
--
ALTER TABLE `slide`
  ADD PRIMARY KEY (`slide_id`);

--
-- Chỉ mục cho bảng `slide_hinh_anh`
--
ALTER TABLE `slide_hinh_anh`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_slide_id` (`slide_id`);

--
-- Chỉ mục cho bảng `thong_bao`
--
ALTER TABLE `thong_bao`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `danh_muc_san_pham`
--
ALTER TABLE `danh_muc_san_pham`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `danh_muc_tin_tuc`
--
ALTER TABLE `danh_muc_tin_tuc`
  MODIFY `id_danh_muc_tin_tuc` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `dia_chi`
--
ALTER TABLE `dia_chi`
  MODIFY `id_dia_chi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `don_hang`
--
ALTER TABLE `don_hang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
  MODIFY `gio_hang_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `gio_hang_chi_tiet`
--
ALTER TABLE `gio_hang_chi_tiet`
  MODIFY `gio_hang_chi_tiet_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT cho bảng `hinh_anh_san_pham`
--
ALTER TABLE `hinh_anh_san_pham`
  MODIFY `hinh_anh_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

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
  MODIFY `nguoi_dung_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT cho bảng `phuong_thuc_thanh_toan`
--
ALTER TABLE `phuong_thuc_thanh_toan`
  MODIFY `phuong_thuc_thanh_toan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `san_pham`
--
ALTER TABLE `san_pham`
  MODIFY `san_pham_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT cho bảng `san_pham_bien_the`
--
ALTER TABLE `san_pham_bien_the`
  MODIFY `bien_the_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT cho bảng `slide`
--
ALTER TABLE `slide`
  MODIFY `slide_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `slide_hinh_anh`
--
ALTER TABLE `slide_hinh_anh`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT cho bảng `thong_bao`
--
ALTER TABLE `thong_bao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `tintuc`
--
ALTER TABLE `tintuc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT cho bảng `trang_tinh`
--
ALTER TABLE `trang_tinh`
  MODIFY `Trang_tinh_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
-- Các ràng buộc cho bảng `slide_hinh_anh`
--
ALTER TABLE `slide_hinh_anh`
  ADD CONSTRAINT `fk_slide_id` FOREIGN KEY (`slide_id`) REFERENCES `slide` (`slide_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `tintuc`
--
ALTER TABLE `tintuc`
  ADD CONSTRAINT `tintuc_ibfk_1` FOREIGN KEY (`id_danh_muc_tin_tuc`) REFERENCES `danh_muc_tin_tuc` (`id_danh_muc_tin_tuc`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
