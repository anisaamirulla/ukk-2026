-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 06 Nov 2026 pada 09.01
-- Versi server: 10.4.25-MariaDB
-- Versi PHP: 8.0.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_ukk_2026`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_guru`
--

CREATE TABLE `t_guru` (
  `id` int(11) NOT NULL,
  `nip` varchar(30) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `t_guru`
--

INSERT INTO `t_guru` (`id`, `nip`, `nama`, `email`, `status_aktif`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 'NIP00001', 'Wawan Kurniawan, S.Pd.I', 'guru1@ukk2026.sch.id', 1, 1, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(2, 'NIP00002', 'Ai Nuryaidah, S.Pd', 'guru2@ukk2026.sch.id', 1, 2, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(3, 'NIP00003', 'Maya Permata Endah, S.Pd', 'guru3@ukk2026.sch.id', 1, 3, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(4, 'NIP00004', 'Dody Jaenudin, S.Pd.', 'guru4@ukk2026.sch.id', 1, 4, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(5, 'NIP00005', 'Dedi Kardiman, S.Pd', 'guru5@ukk2026.sch.id', 1, 5, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(6, 'NIP00006', 'Uca Musa Yusenda, S.Pd', 'guru6@ukk2026.sch.id', 1, 6, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(7, 'NIP00007', 'Drs. Ahmad Nur Mukhlis', 'guru7@ukk2026.sch.id', 1, 7, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(8, 'NIP00008', 'Drs. Heri', 'guru8@ukk2026.sch.id', 1, 8, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(9, 'NIP00009', 'Asep Bastaman', 'guru9@ukk2026.sch.id', 1, 9, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(10, 'NIP00010', 'H. Encep Maemun, S.T., M.Pd', 'guru10@ukk2026.sch.id', 1, 10, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(11, 'NIP00011', 'Opik', 'guru11@ukk2026.sch.id', 1, 11, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(12, 'NIP00012', 'Ida Farida, S.Pd', 'guru12@ukk2026.sch.id', 1, 12, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(13, 'NIP00013', 'Idah Mahmudah, S.Pd', 'guru13@ukk2026.sch.id', 1, 13, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(14, 'NIP00014', 'Drs. Muhamad Ali', 'guru14@ukk2026.sch.id', 1, 14, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(15, 'NIP00015', 'Helmi Ali Akbar, S.Pd.I', 'guru15@ukk2026.sch.id', 1, 15, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(16, 'NIP00016', 'Syeni Suswarini, S.Pd', 'guru16@ukk2026.sch.id', 1, 16, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(17, 'NIP00017', 'Wiwid Yulianti, S.Pd', 'guru17@ukk2026.sch.id', 1, 17, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(18, 'NIP00018', 'Ansor Muhajir, S.T', 'guru18@ukk2026.sch.id', 1, 18, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(19, 'NIP00019', 'Ogi Gaosun Gais, S.Pd.I', 'guru19@ukk2026.sch.id', 1, 19, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(20, 'NIP00020', 'Sandi Mulyana, S.Pd', 'guru20@ukk2026.sch.id', 1, 20, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(21, 'NIP00021', 'Erna Suryani, S.Pd', 'guru21@ukk2026.sch.id', 1, 21, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(22, 'NIP00022', 'Ai Wahidah, S.Ag', 'guru22@ukk2026.sch.id', 1, 22, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(23, 'NIP00023', 'Chandra Aditya Yusuf, S.Pd', 'guru23@ukk2026.sch.id', 1, 23, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(24, 'NIP00024', 'Arif Kusmawan, S.Pd', 'guru24@ukk2026.sch.id', 1, 24, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(25, 'NIP00025', 'Asep Taufiq Hidayat, S.Kom', 'guru25@ukk2026.sch.id', 1, 25, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(26, 'NIP00026', 'Rendy Ichsan Kusumah, S.T', 'guru26@ukk2026.sch.id', 1, 26, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(27, 'NIP00027', 'Feri Rahmawan, S.Pd', 'guru27@ukk2026.sch.id', 1, 27, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(28, 'NIP00028', 'Ari Sandriana, S.Kom', 'guru28@ukk2026.sch.id', 1, 28, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(29, 'NIP00029', 'Yaneu Nurdian, S.Pd, M.Pd', 'guru29@ukk2026.sch.id', 1, 29, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(30, 'NIP00030', 'Ucu Muhammad Khoer N, S.Pd', 'guru30@ukk2026.sch.id', 1, 30, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(31, 'NIP00031', 'Mira Dwi Rahmawati, S.Ag', 'guru31@ukk2026.sch.id', 1, 31, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(32, 'NIP00032', 'Aldi Maulana, S.Pd', 'guru32@ukk2026.sch.id', 1, 32, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(33, 'NIP00033', 'Eli Rosliah, S.Pd', 'guru33@ukk2026.sch.id', 1, 33, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(34, 'NIP00034', 'Isni Nur Fitriani, S.Pd', 'guru34@ukk2026.sch.id', 1, 34, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(35, 'NIP00035', 'Aanisah Putri Nur Aini, S.Pd', 'guru35@ukk2026.sch.id', 1, 35, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(36, 'NIP00036', 'Yusi Luthfiani, S.Sos.I', 'guru36@ukk2026.sch.id', 1, 36, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(37, 'NIP00037', 'Lilis Rustandy Mawardi, S.Pd., M.Pd', 'guru37@ukk2026.sch.id', 1, 37, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(38, 'NIP00038', 'Yusrin Noer Sabrina, S.Pd', 'guru38@ukk2026.sch.id', 1, 38, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(39, 'NIP00039', 'Senny Indayani, S.Pd', 'guru39@ukk2026.sch.id', 1, 39, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(40, 'NIP00040', 'Ilma Amalia, S.Pd', 'guru40@ukk2026.sch.id', 1, 40, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(44, 'NIP00044', 'Alvira Sofwatunnisa, S.Pd', 'guru44@ukk2026.sch.id', 1, 44, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(45, 'NIP00045', 'Risna Andriyani, S.Pd', 'guru45@ukk2026.sch.id', 1, 45, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(46, 'NIP00046', 'Dra. Tintin Noerhayati', 'guru46@ukk2026.sch.id', 1, 46, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(47, 'NIP00047', 'Rizkia Fahira, S.Pd', 'guru47@ukk2026.sch.id', 1, 47, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(48, 'NIP00048', 'Frasetio', 'guru48@ukk2026.sch.id', 1, 48, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(49, 'NIP00049', 'Ratnaningsih Hatil Aeni, S.Pd', 'guru49@ukk2026.sch.id', 1, 49, '2026-09-23 02:31:55', '2026-09-23 02:31:55'),
(52, '976568586575786896', 'Dheny Darajatul Hasani, S.Pd', 'guru52@ukk2026.sch.id', 1, 52, '2026-10-23 06:48:58', '2026-10-23 06:48:58'),
(53, '232436547698809809', 'Fahrullah, S.Pd', 'guru53@ukk2026.sch.id', 1, 53, '2026-10-23 06:49:58', '2026-10-23 06:49:58'),
(54, '024264635078709734', 'Aan Andriana, S.T', 'guru54@ukk2026.sch.id', 1, 54, '2026-10-23 06:55:06', '2026-10-23 06:55:06'),
(55, '000313695221966226', 'Fitri Fauziah, S.T', 'guru55@ukk2026.sch.id', 1, 55, '2026-11-04 02:09:21', '2026-11-04 02:09:21'),
(56, '458834748124526184', 'Nida Aulia, S.Pd', 'guru56@ukk2026.sch.id', 1, 56, '2026-11-04 02:20:57', '2026-11-04 02:20:57'),
(57, '021654978265020545', 'Sri Nurrizki Putri Krisnadi, S.Pd', 'guru57@ukk2026.sch.id', 1, 57, '2026-11-04 02:33:58', '2026-11-04 02:33:58'),
(58, 'NIP00058', 'Riesy Putri Navia, S.Kom', 'guru58@ukk2026.sch.id', 1, 41, '2026-11-04 08:34:33', '2026-11-04 08:34:33'),
(59, 'NIP00059', 'Rurry Febriani Maryadi, S.Pd', 'guru59@ukk2026.sch.id', 1, 42, '2026-11-04 08:34:33', '2026-11-04 08:34:33'),
(60, 'NIP00060', 'Irvan Rizal, S.Pd', 'guru60@ukk2026.sch.id', 1, 43, '2026-11-04 08:34:33', '2026-11-04 08:34:33'),
(63, '003161496102542015', 'Raihan Hilal', 'raihan@gmail.com', 1, 60, '2026-11-06 07:53:12', '2026-11-06 07:53:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_kelas`
--

CREATE TABLE `t_kelas` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `tingkat` varchar(20) NOT NULL,
  `jurusan` varchar(100) NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `t_kelas`
--

INSERT INTO `t_kelas` (`id`, `nama`, `tingkat`, `jurusan`, `status_aktif`, `created_at`, `updated_at`) VALUES
(2, 'XTKJ 1', 'X', 'Teknik Komputer dan Jaringan', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(3, 'XBD', 'X', 'Bisnis Daring dan Pemasaran', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(4, 'XI BD', 'XI', 'Teknik Komputer dan Jaringan', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(5, 'XI RPL 1', 'XI', 'Rekayasa Perangkat Lunak', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(6, 'XRPL 1', 'X', 'Rekayasa Perangkat Lunak', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(7, 'XII RPL 1', 'XII', 'Rekayasa Perangkat Lunak', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(8, 'XII TKJ 4', 'XII', 'Manajemen Perkantoran', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(9, 'XRPL 2', 'X', 'Rekayasa Perangkat Lunak', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(10, 'XTKJ 2', 'X', 'Teknik Komputer dan Jaringan', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(11, 'XII TKJ 1', 'XII', 'Rekayasa Perangkat Lunak', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(12, 'XI TKR 2', 'XI', 'Teknik Kendaraan Ringan', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(13, 'XII TKJ 3', 'XII', 'Rekayasa Perangkat Lunak', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(14, 'XII RPL 2', 'XII', 'Rekayasa Perangkat Lunak', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(15, 'XTKJ 3', 'X', 'Teknik Komputer dan Jaringan', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(16, 'XTKR 3', 'X', 'Akuntansi', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(17, 'XI TKJ 1', 'XI', 'Teknik Komputer dan Jaringan', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(18, 'XTSM 1', 'X', 'Manajemen Perkantoran', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(19, 'XII TKJ 2', 'XII', 'Manajemen Perkantoran', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(20, 'XII BD', 'XI', 'Bisnis Daring dan Pemasaran', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(21, 'XTKR 4', 'X', 'Rekayasa Perangkat Lunak', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(22, 'XII TKR 4', 'XII', 'Rekayasa Perangkat Lunak', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(23, 'XTKR 1', 'X', 'Rekayasa Perangkat Lunak', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(24, 'XTSM 2', 'X', 'Teknik Komputer dan Jaringan', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(25, 'XI TKJ 2', 'XI', 'Teknik Komputer dan Jaringan', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(26, 'XII TKR 5', 'XII', 'Teknik Kendaraan Ringan', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(27, 'XII TSM 1', 'XII', 'Teknik Sepeda Motor', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(28, 'XI TSM 2', 'XI', 'Teknik Sepeda Motor', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(29, 'XTKR 2', 'X', 'Manajemen Perkantoran', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(30, 'XTSM 3', 'X', 'Teknik Sepeda Motor', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(31, 'XII TSM 2', 'XII', 'Teknik Sepeda Motor', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(32, 'XII TSM 3', 'XII', 'Teknik Sepeda Motor', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(33, 'XII TKR 2', 'XII', 'Teknik Kendaraan Ringan', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(34, 'XTKR 5', 'X', 'Teknik Komputer dan Jaringan', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(35, 'XI TKR 1', 'XI', 'Teknik Kendaraan Ringan', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(36, 'XI TKR 1', 'XI', 'Teknik Kendaraan Ringan', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(37, 'XI TKR 3', 'XI', 'Teknik Kendaraan Ringan', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(38, 'XI TKR 3', 'XI', 'Teknik Kendaraan Ringan', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(39, 'XI TSM 1', 'XI', 'Teknik Sepeda Motor', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(40, 'X BDP 3', 'X', 'Bisnis Daring dan Pemasaran', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(41, 'XI RPL 4', 'XI', 'Rekayasa Perangkat Lunak', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(42, 'XI RPL 5', 'XI', 'Rekayasa Perangkat Lunak', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(43, 'XI TKJ 3', 'XI', 'Teknik Komputer dan Jaringan', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(44, 'XI TKJ 4', 'XI', 'Teknik Komputer dan Jaringan', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(45, 'XI AKL 3', 'XI', 'Akuntansi', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(46, 'XI AKL 4', 'XI', 'Akuntansi', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(47, 'XI MPLB 3', 'XI', 'Manajemen Perkantoran', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(48, 'XI MPLB 4', 'XI', 'Manajemen Perkantoran', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(49, 'XI BDP 2', 'XI', 'Bisnis Daring dan Pemasaran', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(50, 'XI BDP 3', 'XI', 'Bisnis Daring dan Pemasaran', 1, '2026-09-23 03:54:49', '2026-09-23 03:54:49'),
(54, 'XII TSM 4', 'XII', 'Teknik Sepedah Motor', 1, '2026-11-06 07:54:38', '2026-11-06 07:54:38');

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_kelas_siswa`
--

CREATE TABLE `t_kelas_siswa` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `tahun_ajaran_id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `t_kelas_siswa`
--

INSERT INTO `t_kelas_siswa` (`id`, `siswa_id`, `tahun_ajaran_id`, `kelas_id`, `tanggal_mulai`, `tanggal_selesai`, `status_aktif`, `created_at`, `updated_at`) VALUES
(3, 3, 3, 3, '2026-01-01', '2026-12-31', 1, '2026-09-23 03:56:12', '2026-09-23 03:56:12'),
(4, 4, 4, 4, '2026-01-01', '2026-12-31', 1, '2026-09-23 03:56:12', '2026-09-23 03:56:12'),
(5, 5, 5, 5, '2026-01-01', '2026-12-31', 1, '2026-09-23 03:56:12', '2026-09-23 03:56:12'),
(6, 6, 6, 6, '2026-01-01', '2026-12-31', 1, '2026-09-23 03:56:12', '2026-09-23 03:56:12'),
(7, 7, 7, 7, '2026-01-01', '2026-12-31', 1, '2026-09-23 03:56:12', '2026-09-23 03:56:12'),
(8, 8, 8, 8, '2026-01-01', '2026-12-31', 1, '2026-09-23 03:56:12', '2026-09-23 03:56:12'),
(9, 9, 9, 9, '2026-01-01', '2026-12-31', 1, '2026-09-23 03:56:12', '2026-09-23 03:56:12'),
(10, 10, 10, 10, '2026-01-01', '2026-12-31', 1, '2026-09-23 03:56:12', '2026-09-23 03:56:12'),
(11, 11, 11, 11, '2026-01-01', '2026-12-31', 1, '2026-09-23 03:56:12', '2026-09-23 03:56:12'),
(12, 12, 12, 12, '2026-01-01', '2026-12-31', 1, '2026-09-23 03:56:12', '2026-09-23 03:56:12'),
(13, 13, 13, 13, '2026-01-01', '2026-12-31', 1, '2026-09-23 03:56:12', '2026-09-23 03:56:12'),
(14, 14, 14, 14, '2026-01-01', '2026-12-31', 1, '2026-09-23 03:56:12', '2026-09-23 03:56:12'),
(15, 15, 15, 15, '2026-01-01', '2026-12-31', 1, '2026-09-23 03:56:12', '2026-09-23 03:56:12'),
(16, 16, 16, 16, '2026-01-01', '2026-12-31', 1, '2026-09-23 03:56:12', '2026-09-23 03:56:12'),
(17, 17, 17, 17, '2026-01-01', '2026-12-31', 1, '2026-09-23 03:56:12', '2026-09-23 03:56:12'),
(18, 18, 18, 18, '2026-01-01', '2026-12-31', 1, '2026-09-23 03:56:12', '2026-09-23 03:56:12'),
(19, 19, 19, 19, '2026-01-01', '2026-12-31', 1, '2026-09-23 03:56:12', '2026-09-23 03:56:12'),
(20, 20, 20, 20, '2026-01-01', '2026-12-31', 1, '2026-09-23 03:56:12', '2026-09-23 03:56:12'),
(21, 21, 21, 21, '2026-01-01', '2026-12-31', 1, '2026-09-23 03:56:12', '2026-09-23 03:56:12'),
(22, 22, 22, 22, '2026-01-01', '2026-12-31', 1, '2026-09-23 03:56:12', '2026-09-23 03:56:12'),
(23, 23, 23, 23, '2026-01-01', '2026-12-31', 1, '2026-09-23 03:56:12', '2026-09-23 03:56:12'),
(24, 24, 24, 24, '2026-01-01', '2026-12-31', 1, '2026-09-23 03:56:12', '2026-09-23 03:56:12'),
(25, 25, 25, 25, '2026-01-01', '2026-12-31', 1, '2026-09-23 03:56:12', '2026-09-23 03:56:12'),
(26, 26, 26, 26, '2026-01-01', '2026-12-31', 1, '2026-09-23 03:56:12', '2026-09-23 03:56:12'),
(27, 27, 27, 27, '2026-01-01', '2026-12-31', 1, '2026-09-23 03:56:12', '2026-09-23 03:56:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_pelanggaran`
--

CREATE TABLE `t_pelanggaran` (
  `id` int(11) NOT NULL,
  `pelanggaran_kategori_id` int(11) NOT NULL,
  `kode` varchar(30) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `poin` int(11) NOT NULL,
  `deksripsi` text NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `t_pelanggaran`
--

INSERT INTO `t_pelanggaran` (`id`, `pelanggaran_kategori_id`, `kode`, `nama`, `poin`, `deksripsi`, `status_aktif`, `created_at`, `updated_at`) VALUES
(51, 1, 'P001', 'Terlambat masuk sekolah', 5, 'Datang setelah jam masuk sekolah', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(52, 2, 'P002', 'Tidak hadir tanpa keterangan', 10, 'Tidak masuk sekolah tanpa keterangan', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(53, 3, 'P003', 'Tidak memakai seragam lengkap', 5, 'Tidak menggunakan seragam lengkap', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(54, 4, 'P004', 'Rambut tidak sesuai aturan', 3, 'Rambut tidak sesuai ketentuan sekolah', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(55, 5, 'P005', 'Tidak menjaga ketertiban', 5, 'Mengganggu ketertiban sekolah', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(56, 6, 'P006', 'Membuang sampah sembarangan', 3, 'Tidak menjaga kebersihan lingkungan', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(57, 7, 'P007', 'Tidak mengerjakan tugas', 5, 'Tidak mengerjakan tugas sekolah', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(58, 8, 'P008', 'Tidak mengikuti kegiatan', 5, 'Tidak mengikuti kegiatan sekolah', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(59, 9, 'P009', 'Tidak mengikuti apel', 5, 'Tidak mengikuti apel dengan tertib', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(60, 10, 'P010', 'Tidak mengikuti kegiatan wajib', 5, 'Tidak mengikuti kegiatan wajib sekolah', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(61, 11, 'P011', 'Terlambat masuk kelas', 3, 'Terlambat masuk kelas setelah pembelajaran dimulai', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(62, 12, 'P012', 'Berkata tidak sopan', 5, 'Menggunakan kata-kata yang tidak sopan', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(63, 13, 'P013', 'Atribut tidak lengkap', 3, 'Tidak menggunakan atribut sekolah lengkap', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(64, 14, 'P014', 'Tidak rapi', 3, 'Penampilan tidak sesuai aturan sekolah', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(65, 15, 'P015', 'Membuat keributan', 5, 'Membuat keributan di lingkungan sekolah', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(66, 16, 'P016', 'Tidak menjaga kebersihan', 3, 'Tidak menjaga kebersihan kelas', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(67, 17, 'P017', 'Tidak mengerjakan piket', 3, 'Tidak melaksanakan piket kelas', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(68, 18, 'P018', 'Keluar kelas tanpa izin', 5, 'Keluar kelas tanpa izin guru', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(69, 19, 'P019', 'Meninggalkan sekolah tanpa izin', 10, 'Meninggalkan sekolah tanpa izin', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(70, 20, 'P020', 'Berkelahi', 15, 'Terlibat perkelahian di sekolah', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(71, 21, 'P021', 'Mengganggu teman', 3, 'Mengganggu teman saat pembelajaran', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(72, 22, 'P022', 'Tidak menghormati guru', 10, 'Tidak menghormati guru', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(73, 23, 'P023', 'Tidak mengikuti pembelajaran', 5, 'Tidak mengikuti pembelajaran', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(74, 24, 'P024', 'Menggunakan HP saat belajar', 5, 'Menggunakan HP saat pembelajaran', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(75, 25, 'P025', 'Menyontek', 10, 'Melakukan tindakan menyontek', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(76, 26, 'P026', 'Merusak fasilitas sekolah', 15, 'Merusak fasilitas milik sekolah', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(77, 27, 'P027', 'Mencoret fasilitas sekolah', 10, 'Mencoret fasilitas sekolah', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(78, 28, 'P028', 'Membawa barang terlarang', 15, 'Membawa barang yang dilarang sekolah', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(79, 29, 'P029', 'Tidur saat pembelajaran', 3, 'Tidur ketika proses pembelajaran berlangsung', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(80, 30, 'P030', 'Makan saat pembelajaran', 3, 'Makan saat proses pembelajaran', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(81, 31, 'P031', 'Minum saat pembelajaran', 2, 'Minum saat proses pembelajaran', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(82, 32, 'P032', 'Tidak membawa buku', 2, 'Tidak membawa buku pelajaran', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(83, 33, 'P033', 'Tidak membawa perlengkapan', 2, 'Tidak membawa perlengkapan belajar', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(84, 34, 'P034', 'Tidak memakai sepatu sesuai aturan', 3, 'Sepatu tidak sesuai aturan sekolah', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(85, 35, 'P035', 'Tidak memakai kaus kaki', 2, 'Tidak menggunakan kaus kaki sesuai aturan', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(86, 36, 'P036', 'Tidak membawa kartu pelajar', 2, 'Tidak membawa kartu pelajar', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(87, 37, 'P037', 'Parkir sembarangan', 3, 'Memarkir kendaraan tidak pada tempatnya', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(88, 38, 'P038', 'Tidak memakai helm', 5, 'Tidak memakai helm saat berkendara', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(89, 39, 'P039', 'Membawa kendaraan tidak sesuai aturan', 5, 'Membawa kendaraan yang tidak sesuai aturan', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(90, 40, 'P040', 'Mengganggu kegiatan sekolah', 5, 'Mengganggu kegiatan sekolah', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(91, 41, 'P041', 'Tidak mengikuti upacara', 5, 'Tidak mengikuti upacara sekolah', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(92, 42, 'P042', 'Membuat gaduh', 5, 'Membuat suasana gaduh di sekolah', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(93, 43, 'P043', 'Membawa barang tidak diperlukan', 3, 'Membawa barang yang tidak diperlukan', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(94, 44, 'P044', 'Pakaian tidak sesuai aturan', 5, 'Menggunakan pakaian yang tidak sesuai aturan', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(95, 45, 'P045', 'Mengabaikan teguran guru', 5, 'Tidak mengindahkan teguran guru', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(96, 46, 'P046', 'Tidak menjaga fasilitas', 5, 'Tidak menjaga fasilitas sekolah', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(97, 47, 'P047', 'Tidak mengembalikan fasilitas', 5, 'Tidak mengembalikan fasilitas yang digunakan', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(98, 48, 'P048', 'Tidak mengikuti aturan sekolah', 5, 'Melanggar aturan sekolah', 1, '2026-09-23 04:04:56', '2026-09-23 04:04:56'),
(101, 16, 'P055', 'Menggunakan Mekup', 5, 'Menggunakan Makeup', 1, '2026-11-05 01:20:37', '2026-11-05 01:20:37');

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_pelanggaran_kategori`
--

CREATE TABLE `t_pelanggaran_kategori` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `deksripsi` text NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `t_pelanggaran_kategori`
--

INSERT INTO `t_pelanggaran_kategori` (`id`, `nama`, `deksripsi`, `status_aktif`, `created_at`, `updated_at`) VALUES
(1, 'Terlambat', 'Datang setelah waktu masuk sekolah.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(2, 'Tidak Hadir', 'Tidak hadir tanpa keterangan.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(3, 'Seragam', 'Pelanggaran ketentuan seragam.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(4, 'Kerapian', 'Pelanggaran terkait kerapian.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(5, 'Ketertiban', 'Pelanggaran ketertiban sekolah.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(6, 'Kebersihan', 'Tidak menjaga kebersihan.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(7, 'Tugas', 'Tidak melaksanakan tugas.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(8, 'Kehadiran', 'Pelanggaran aturan kehadiran.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(9, 'Apel', 'Tidak mengikuti apel dengan tertib.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(10, 'Kegiatan', 'Tidak mengikuti kegiatan sekolah.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(11, 'Terlambat 11', 'Datang setelah waktu masuk sekolah.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(12, 'Tidak Hadir 12', 'Tidak hadir tanpa keterangan.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(13, 'Seragam 13', 'Pelanggaran ketentuan seragam.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(14, 'Kerapian 14', 'Pelanggaran terkait kerapian.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(15, 'Ketertiban 15', 'Pelanggaran ketertiban sekolah.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(16, 'Kebersihan 16', 'Tidak menjaga kebersihan.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(17, 'Tugas 17', 'Tidak melaksanakan tugas.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(18, 'Kehadiran 18', 'Pelanggaran aturan kehadiran.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(19, 'Apel 19', 'Tidak mengikuti apel dengan tertib.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(20, 'Kegiatan 20', 'Tidak mengikuti kegiatan sekolah.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(21, 'Terlambat 21', 'Datang setelah waktu masuk sekolah.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(22, 'Tidak Hadir 22', 'Tidak hadir tanpa keterangan.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(23, 'Seragam 23', 'Pelanggaran ketentuan seragam.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(24, 'Kerapian 24', 'Pelanggaran terkait kerapian.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(25, 'Ketertiban 25', 'Pelanggaran ketertiban sekolah.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(26, 'Kebersihan 26', 'Tidak menjaga kebersihan.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(27, 'Tugas 27', 'Tidak melaksanakan tugas.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(28, 'Kehadiran 28', 'Pelanggaran aturan kehadiran.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(29, 'Apel 29', 'Tidak mengikuti apel dengan tertib.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(30, 'Kegiatan 30', 'Tidak mengikuti kegiatan sekolah.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(31, 'Terlambat 31', 'Datang setelah waktu masuk sekolah.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(32, 'Tidak Hadir 32', 'Tidak hadir tanpa keterangan.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(33, 'Seragam 33', 'Pelanggaran ketentuan seragam.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(34, 'Kerapian 34', 'Pelanggaran terkait kerapian.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(35, 'Ketertiban 35', 'Pelanggaran ketertiban sekolah.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(36, 'Kebersihan 36', 'Tidak menjaga kebersihan.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(37, 'Tugas 37', 'Tidak melaksanakan tugas.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(38, 'Kehadiran 38', 'Pelanggaran aturan kehadiran.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(39, 'Apel 39', 'Tidak mengikuti apel dengan tertib.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(40, 'Kegiatan 40', 'Tidak mengikuti kegiatan sekolah.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(41, 'Terlambat 41', 'Datang setelah waktu masuk sekolah.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(42, 'Tidak Hadir 42', 'Tidak hadir tanpa keterangan.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(43, 'Seragam 43', 'Pelanggaran ketentuan seragam.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(44, 'Kerapian 44', 'Pelanggaran terkait kerapian.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(45, 'Ketertiban 45', 'Pelanggaran ketertiban sekolah.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(46, 'Kebersihan 46', 'Tidak menjaga kebersihan.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(47, 'Tugas 47', 'Tidak melaksanakan tugas.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(48, 'Kehadiran 48', 'Pelanggaran aturan kehadiran.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(49, 'Apel 49', 'Tidak mengikuti apel dengan tertib.', 1, '2026-09-23 04:04:08', '2026-09-23 04:04:08'),
(51, 'Apel Pagi', 'Tidak Mengikuti Apel', 1, '2026-11-05 01:19:00', '2026-11-05 01:19:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_pelanggaran_siswa`
--

CREATE TABLE `t_pelanggaran_siswa` (
  `id` int(11) NOT NULL,
  `tahun_ajaran_id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `nama_siswa` varchar(150) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `nama_kelas` varchar(100) NOT NULL,
  `pelanggaran_id` int(11) NOT NULL,
  `nama_pelanggaran` varchar(150) NOT NULL,
  `pelanggaran_kategori_id` int(11) NOT NULL,
  `guru_id` int(11) NOT NULL,
  `nama_guru` varchar(150) NOT NULL,
  `tanggal` date NOT NULL,
  `keterangan` text NOT NULL,
  `poin` int(11) NOT NULL,
  `tindakan` text NOT NULL,
  `status` varchar(30) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `t_pelanggaran_siswa`
--

INSERT INTO `t_pelanggaran_siswa` (`id`, `tahun_ajaran_id`, `siswa_id`, `nama_siswa`, `kelas_id`, `nama_kelas`, `pelanggaran_id`, `nama_pelanggaran`, `pelanggaran_kategori_id`, `guru_id`, `nama_guru`, `tanggal`, `keterangan`, `poin`, `tindakan`, `status`, `created_at`, `updated_at`) VALUES
(3, 51, 4, 'ADITTIA NUR LANTIP', 3, 'XII AKL 1', 53, 'Tidak memakai seragam lengkap', 3, 3, 'Ansor Muhajir', '2026-08-03', 'Tidak memakai seragam lengkap', 5, 'Tugas Tambahan', 'Selesai', '2026-09-23 04:32:46', '2026-09-23 04:32:46'),
(4, 51, 4, 'AGNIA ROSYA NOERILLAH', 4, 'X MPLB 1', 54, 'Rambut tidak sesuai aturan', 4, 4, 'Deni Setiawan', '2026-08-04', 'Rambut tidak sesuai aturan sekolah', 6, 'Pemanggilan Wali', 'Diproses', '2026-09-23 04:32:46', '2026-09-23 04:32:46'),
(5, 5, 5, 'ANISA AMIRULLA', 5, 'XI TBSM 1', 55, 'Tidak menjaga ketertiban', 5, 5, 'Rina Marlina', '2026-08-05', 'Mengganggu ketertiban sekolah', 5, 'Pembinaan', 'Diproses', '2026-09-23 04:32:46', '2026-09-23 04:32:46'),
(6, 2, 6, 'ARADEA KANDI', 2, 'XI TKJ 1', 92, 'Menyontek', 2, 2, 'Aldi Maulana', '2026-09-12', 'Menyontek saat ujian', 8, 'Peringatan', 'Diproses', '2026-09-23 04:32:46', '2026-09-23 04:32:46'),
(7, 7, 7, 'ARFA DIESA OKMALIEZA', 2, 'XI TKJ 1', 57, 'Tidak mengerjakan tugas', 2, 2, 'Aldi Maulana', '2026-08-07', 'Tidak mengerjakan tugas sekolah', 4, 'Tugas Tambahan', 'Diproses', '2026-09-23 04:32:46', '2026-09-23 04:32:46'),
(8, 51, 8, 'FITRI NUR SYAMI', 3, 'XII AKL 1', 58, 'Tidak mengikuti kegiatan', 3, 3, 'Ansor Muhajir', '2026-08-08', 'Tidak mengikuti kegiatan sekolah', 5, 'Peringatan', 'Belum Diproses', '2026-09-23 04:32:46', '2026-09-23 04:32:46'),
(9, 4, 9, 'JUNITA PUTRI ', 4, 'X MPLB 1', 59, 'Tidak mengikuti apel', 4, 4, 'Deni Setiawan', '2026-08-09', 'Tidak mengikuti apel pagi', 3, 'Pembinaan', 'Selesai', '2026-09-23 04:32:46', '2026-09-23 04:32:46'),
(10, 5, 10, 'MOHAMAD ATALARIK ALI SYAHBANA', 5, 'XI TBSM 1', 60, 'Tidak mengikuti kegiatan wajib', 5, 5, 'Rina Marlina', '2026-08-10', 'Tidak mengikuti kegiatan wajib', 5, 'Peringatan', 'Diproses', '2026-09-23 04:32:46', '2026-09-23 04:32:46'),
(11, 2, 11, 'MUHAMAD FAHRIL FAHRULROZI', 2, 'XI TKJ 1', 62, 'Berkata tidak sopan', 2, 2, 'Aldi Maulana', '2026-08-12', 'Berkata tidak sopan kepada teman', 5, 'Peringatan', 'Diproses', '2026-09-23 04:32:46', '2026-09-23 04:32:46'),
(13, 3, 13, 'MUHAMMAD ADNAN SYAEFUL ISLAMI', 3, 'XII AKL 1', 63, 'Atribut tidak lengkap', 3, 3, 'Ansor Muhajir', '2026-08-13', 'Atribut seragam tidak lengkap', 3, 'Pembinaan', 'Selesai', '2026-09-23 04:32:46', '2026-09-23 04:32:46'),
(14, 4, 14, 'MUHAMMAD ZAHRU AQILA FIKRI', 4, 'X MPLB 1', 64, 'Tidak rapi', 4, 4, 'Deni Setiawan', '2026-08-14', 'Penampilan tidak rapi', 3, 'Pembinaan', 'Diproses', '2026-09-23 04:32:46', '2026-09-23 04:32:46'),
(15, 5, 15, 'NADIA ZULFA SORAYA', 5, 'XI TBSM 1', 65, 'Membuat keributan', 5, 5, 'Rina Marlina', '2026-08-15', 'Membuat keributan di kelas', 6, 'Pemanggilan Wali', 'Diproses', '2026-09-23 04:32:46', '2026-09-23 04:32:46'),
(17, 2, 17, 'NAZRIL ILHAM ', 2, 'XI TKJ 1', 67, 'Tidak mengerjakan piket', 2, 2, 'Aldi Maulana', '2026-08-17', 'Tidak melaksanakan piket kelas', 4, 'Tugas Tambahan', 'Diproses', '2026-09-23 04:32:46', '2026-09-23 04:32:46'),
(18, 3, 18, 'PUSPITA DEWI', 3, 'XII AKL 1', 68, 'Keluar kelas tanpa izin', 3, 3, 'Ansor Muhajir', '2026-08-18', 'Keluar kelas tanpa izin guru', 5, 'Peringatan', 'Belum Diproses', '2026-09-23 04:32:46', '2026-09-23 04:32:46'),
(19, 4, 19, 'RAKHA MUHAMMAD HAYKAL', 4, 'X MPLB 1', 69, 'Meninggalkan sekolah tanpa izin', 4, 4, 'Deni Setiawan', '2026-08-19', 'Meninggalkan sekolah tanpa izin', 7, 'Pemanggilan Wali', 'Diproses', '2026-09-23 04:32:46', '2026-09-23 04:32:46'),
(20, 5, 20, 'SAHIRA FIRAJ', 5, 'XI TBSM 1', 70, 'Berkelahi', 5, 5, 'Rina Marlina', '2026-08-20', 'Terlibat perkelahian', 10, 'Pemanggilan Wali', 'Diproses', '2026-09-23 04:32:46', '2026-09-23 04:32:46'),
(22, 2, 22, 'RANI MELIAN', 2, 'XI TKJ 1', 72, 'Tidak menghormati guru', 2, 2, 'Aldi Maulana', '2026-08-22', 'Tidak menghormati guru', 6, 'Peringatan', 'Diproses', '2026-09-23 04:32:46', '2026-09-23 04:32:46'),
(23, 3, 23, 'REHAN MUZAKI', 3, 'XII AKL 1', 73, 'Tidak mengikuti pembelajaran', 3, 3, 'Ansor Muhajir', '2026-08-23', 'Tidak mengikuti pembelajaran', 5, 'Pembinaan', 'Belum Diproses', '2026-09-23 04:32:46', '2026-09-23 04:32:46'),
(24, 4, 24, 'RESA JULIANSYAH', 4, 'X MPLB 1', 74, 'Menggunakan HP saat belajar', 4, 4, 'Deni Setiawan', '2026-08-24', 'Menggunakan HP saat pembelajaran', 6, 'Peringatan', 'Diproses', '2026-09-23 04:32:46', '2026-09-23 04:32:46'),
(25, 5, 25, 'RIZKI PERMATA SUGIANTO', 5, 'XI TBSM 1', 75, 'Menyontek', 5, 5, 'Rina Marlina', '2026-08-25', 'Menyontek saat mengerjakan tugas', 8, 'Peringatan', 'Selesai', '2026-09-23 04:32:46', '2026-09-23 04:32:46'),
(52, 5, 25, 'SENDI MARDIAN', 5, 'XI TBSM 1', 75, 'Menyontek', 5, 5, 'Rina Marlina', '2026-08-25', 'Menyontek saat mengerjakan tugas', 8, 'Peringatan', 'Selesai', '2026-09-23 04:32:46', '2026-09-23 04:32:46'),
(53, 5, 25, 'SAHASIDAN NIZZAR', 5, 'XI TBSM 1', 75, 'Menyontek', 5, 5, 'Rina Marlina', '2026-08-25', 'Menyontek saat mengerjakan tugas', 8, 'Peringatan', 'Selesai', '2026-09-23 04:32:46', '2026-09-23 04:32:46'),
(54, 5, 25, 'SERLY SATRIANI', 5, 'XI TBSM 1', 75, 'Menyontek', 5, 5, 'Rina Marlina', '2026-08-25', 'Menyontek saat mengerjakan tugas', 8, 'Peringatan', 'Selesai', '2026-09-23 04:32:46', '2026-09-23 04:32:46'),
(55, 5, 25, 'SOFI NURJANAH', 5, 'XI TBSM 1', 75, 'Menyontek', 5, 5, 'Rina Marlina', '2026-08-25', 'Menyontek saat mengerjakan tugas', 8, 'Peringatan', 'Selesai', '2026-09-23 04:32:46', '2026-09-23 04:32:46'),
(56, 5, 25, 'SURAYA SALSABILA AZZAHRA', 5, 'XI TBSM 1', 75, 'Menyontek', 5, 5, 'Rina Marlina', '2026-08-25', 'Menyontek saat mengerjakan tugas', 8, 'Peringatan', 'Selesai', '2026-09-23 04:32:46', '2026-09-23 04:32:46'),
(57, 5, 25, 'TONI ANTONIO', 5, 'XI TBSM 1', 75, 'Menyontek', 5, 5, 'Rina Marlina', '2026-08-25', 'Menyontek saat mengerjakan tugas', 8, 'Peringatan', 'Selesai', '2026-09-23 04:32:46', '2026-09-23 04:32:46'),
(58, 51, 4, 'AGNIA ROSYA NOERILLAH', 4, 'X MPLB 1', 54, 'Menggunakan Makeup', 4, 4, 'Lina Marlina', '2026-08-04', 'Menggunakan makeup saat apel ', 6, 'Pemanggilan Wali', 'Diproses', '2026-09-23 04:32:46', '2026-09-23 04:32:46');

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_siswa`
--

CREATE TABLE `t_siswa` (
  `id` int(11) NOT NULL,
  `nis` varchar(30) NOT NULL,
  `nisn` varchar(20) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `jenis_kelamin` char(5) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `alamat` text NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `t_siswa`
--

INSERT INTO `t_siswa` (`id`, `nis`, `nisn`, `nama`, `jenis_kelamin`, `tanggal_lahir`, `alamat`, `status_aktif`, `created_at`, `updated_at`) VALUES
(3, '8787680097', '0198362435', 'Adittia Nur Latip', 'L', '2008-03-09', 'Jl. Pendidikan No. 3, Tasikmalaya', 1, '2026-09-23 02:32:16', '2026-09-23 02:32:16'),
(4, '0310654981', '0025697984', 'Agnia Rosya Noerillah', 'P', '2007-04-12', 'Jl. Pendidikan No. 4, Tasikmalaya', 1, '2026-09-23 02:32:16', '2026-09-23 02:32:16'),
(5, 'NIP00005', 'NISN0000000005', 'Anisa Amirulla', 'P', '2008-05-15', 'Jl. Pendidikan No. 5, Tasikmalaya', 1, '2026-09-23 02:32:16', '2026-09-23 02:32:16'),
(6, 'NIP00006', 'NISN0000000006', 'Aradea Kandi', 'L', '2007-06-18', 'Jl. Pendidikan No. 6, Tasikmalaya', 1, '2026-09-23 02:32:16', '2026-09-23 02:32:16'),
(7, 'NIP00007', 'NISN0000000007', 'Arfa Diesa Okmalieza', 'P', '2008-07-21', 'Jl. Pendidikan No. 7, Tasikmalaya', 1, '2026-09-23 02:32:16', '2026-09-23 02:32:16'),
(8, 'NIP00008', 'NISN0000000008', 'Fitri Nur Syami', 'P', '2007-08-24', 'Jl. Pendidikan No. 8, Tasikmalaya', 1, '2026-09-23 02:32:16', '2026-09-23 02:32:16'),
(9, 'NIP00009', 'NISN0000000009', 'Junita Putri Nur Rahman', 'P', '2008-09-27', 'Jl. Pendidikan No. 9, Tasikmalaya', 1, '2026-09-23 02:32:16', '2026-09-23 02:32:16'),
(10, 'NIP00010', 'NISN0000000010', 'Mohammad Atalarik Ali Syahbana', 'L', '2007-10-03', 'Jl. Pendidikan No. 10, Tasikmalaya', 1, '2026-09-23 02:32:16', '2026-09-23 02:32:16'),
(11, 'NIP00011', 'NISN0000000011', 'Muhamad Fahril Fahrurozi', 'L', '2008-11-06', 'Jl. Pendidikan No. 11, Tasikmalaya', 1, '2026-09-23 02:32:16', '2026-09-23 02:32:16'),
(12, 'NIP00012', 'NISN0000000012', 'Muhammad Adnan Syaeful Islami', 'L', '2007-12-09', 'Jl. Pendidikan No. 12, Tasikmalaya', 1, '2026-09-23 02:32:16', '2026-09-23 02:32:16'),
(13, 'NIP00013', 'NISN0000000013', 'Muhammad Zahru Aqila Fikri', 'L', '2008-01-12', 'Jl. Pendidikan No. 13, Tasikmalaya', 1, '2026-09-23 02:32:16', '2026-09-23 02:32:16'),
(14, 'NIP00014', 'NISN0000000014', 'Nadia Zulfa Soraya', 'P', '2007-02-15', 'Jl. Pendidikan No. 14, Tasikmalaya', 1, '2026-09-23 02:32:16', '2026-09-23 02:32:16'),
(15, 'NIP00015', 'NISN0000000015', 'Nazril Ilham', 'L', '2008-03-18', 'Jl. Pendidikan No. 15, Tasikmalaya', 1, '2026-09-23 02:32:16', '2026-09-23 02:32:16'),
(16, 'NIP00016', 'NISN0000000016', 'Puspita Dewi', 'P', '2007-04-21', 'Jl. Pendidikan No. 16, Tasikmalaya', 1, '2026-09-23 02:32:16', '2026-09-23 02:32:16'),
(17, 'NIP00017', 'NISN0000000017', 'Rakha Muhammad Haykal', 'L', '2008-05-24', 'Jl. Pendidikan No. 17, Tasikmalaya', 1, '2026-09-23 02:32:16', '2026-09-23 02:32:16'),
(18, 'NIP00018', 'NISN0000000018', 'Rani Meliani', 'P', '2007-06-27', 'Jl. Pendidikan No. 18, Tasikmalaya', 1, '2026-09-23 02:32:16', '2026-09-23 02:32:16'),
(19, 'NIP00019', 'NISN0000000019', 'Reihan Muzaki', 'L', '2008-07-03', 'Jl. Pendidikan No. 19, Tasikmalaya', 1, '2026-09-23 02:32:16', '2026-09-23 02:32:16'),
(20, 'NIP00020', 'NISN0000000020', 'Resa Juliansah', 'L', '2007-08-06', 'Jl. Pendidikan No. 20, Tasikmalaya', 1, '2026-09-23 02:32:16', '2026-09-23 02:32:16'),
(21, 'NIP00021', 'NISN0000000021', 'Rizki Permata Sugianto', 'L', '2008-09-09', 'Jl. Pendidikan No. 21, Tasikmalaya', 1, '2026-09-23 02:32:16', '2026-09-23 02:32:16'),
(22, 'NIP00022', 'NISN0000000022', 'Sahira Firaj', 'P', '2007-10-12', 'Jl. Pendidikan No. 22, Tasikmalaya', 1, '2026-09-23 02:32:16', '2026-09-23 02:32:16'),
(23, 'NIP00023', 'NISN0000000023', 'Sendi Mardian ', 'L', '2008-11-15', 'Jl. Pendidikan No. 23, Tasikmalaya', 1, '2026-09-23 02:32:16', '2026-09-23 02:32:16'),
(24, 'NIP00024', 'NISN0000000024', 'Shahaidan Nizzar', 'L', '2007-12-18', 'Jl. Pendidikan No. 24, Tasikmalaya', 1, '2026-09-23 02:32:16', '2026-09-23 02:32:16'),
(25, 'NIP00025', 'NISN0000000025', 'Sherly Satriani', 'P', '2008-01-21', 'Jl. Pendidikan No. 25, Tasikmalaya', 1, '2026-09-23 02:32:16', '2026-09-23 02:32:16'),
(26, 'NIP00026', 'NISN0000000026', 'Sofi Nurjanah', 'P', '2007-02-24', 'Jl. Pendidikan No. 26, Tasikmalaya', 1, '2026-09-23 02:32:16', '2026-09-23 02:32:16'),
(27, 'NIP00027', 'NISN0000000027', 'Toni Antonio', 'L', '2008-03-27', 'Jl. Pendidikan No. 27, Tasikmalaya', 1, '2026-09-23 02:32:16', '2026-09-23 02:32:16'),
(56, '0031574974', '7878452122', 'Nisa', 'P', '2023-04-04', 'jln.mkjbig', 1, '2026-11-04 08:22:56', '2026-11-04 08:22:56'),
(59, '0031265494', '0021574842', 'Azis Pridaus', 'L', '2006-11-04', 'jln.kosipa', 1, '2026-11-06 07:51:23', '2026-11-06 07:51:23');

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_tahun_ajaran`
--

CREATE TABLE `t_tahun_ajaran` (
  `id` int(11) NOT NULL,
  `nama` varchar(20) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `t_tahun_ajaran`
--

INSERT INTO `t_tahun_ajaran` (`id`, `nama`, `tanggal_mulai`, `tanggal_selesai`, `status_aktif`, `created_at`, `updated_at`) VALUES
(1, 'Tahun Ajaran 1978/19', '1978-07-01', '1979-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(2, 'Tahun Ajaran 1979/19', '1979-07-01', '1980-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(3, 'Tahun Ajaran 1980/19', '1980-07-01', '1981-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(4, 'Tahun Ajaran 1981/19', '1981-07-01', '1982-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(5, 'Tahun Ajaran 1982/19', '1982-07-01', '1983-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(6, 'Tahun Ajaran 1983/19', '1983-07-01', '1984-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(7, 'Tahun Ajaran 1984/19', '1984-07-01', '1985-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(8, 'Tahun Ajaran 1985/19', '1985-07-01', '1986-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(9, 'Tahun Ajaran 1986/19', '1986-07-01', '1987-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(10, 'Tahun Ajaran 1987/19', '1987-07-01', '1988-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(11, 'Tahun Ajaran 1988/19', '1988-07-01', '1989-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(12, 'Tahun Ajaran 1989/19', '1989-07-01', '1990-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(13, 'Tahun Ajaran 1990/19', '1990-07-01', '1991-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(14, 'Tahun Ajaran 1991/19', '1991-07-01', '1992-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(15, 'Tahun Ajaran 1992/19', '1992-07-01', '1993-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(16, 'Tahun Ajaran 1993/19', '1993-07-01', '1994-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(17, 'Tahun Ajaran 1994/19', '1994-07-01', '1995-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(18, 'Tahun Ajaran 1995/19', '1995-07-01', '1996-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(19, 'Tahun Ajaran 1996/19', '1996-07-01', '1997-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(20, 'Tahun Ajaran 1997/19', '1997-07-01', '1998-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(21, 'Tahun Ajaran 1998/19', '1998-07-01', '1999-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(22, 'Tahun Ajaran 1999/20', '1999-07-01', '2000-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(23, 'Tahun Ajaran 2000/20', '2000-07-01', '2001-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(24, 'Tahun Ajaran 2001/20', '2001-07-01', '2002-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(25, 'Tahun Ajaran 2002/20', '2002-07-01', '2003-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(26, 'Tahun Ajaran 2003/20', '2003-07-01', '2004-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(27, 'Tahun Ajaran 2004/20', '2004-07-01', '2005-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(28, 'Tahun Ajaran 2005/20', '2005-07-01', '2006-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(29, 'Tahun Ajaran 2006/20', '2006-07-01', '2007-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(30, 'Tahun Ajaran 2007/20', '2007-07-01', '2008-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(31, 'Tahun Ajaran 2008/20', '2008-07-01', '2009-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(32, 'Tahun Ajaran 2009/20', '2009-07-01', '2010-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(33, 'Tahun Ajaran 2010/20', '2010-07-01', '2011-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(34, 'Tahun Ajaran 2011/20', '2011-07-01', '2012-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(35, 'Tahun Ajaran 2012/20', '2012-07-01', '2013-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(36, 'Tahun Ajaran 2013/20', '2013-07-01', '2014-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(37, 'Tahun Ajaran 2014/20', '2014-07-01', '2015-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(38, 'Tahun Ajaran 2015/20', '2015-07-01', '2016-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(39, 'Tahun Ajaran 2016/20', '2016-07-01', '2017-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(40, 'Tahun Ajaran 2017/20', '2017-07-01', '2018-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(41, 'Tahun Ajaran 2018/20', '2018-07-01', '2019-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(42, 'Tahun Ajaran 2019/20', '2019-07-01', '2020-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(43, 'Tahun Ajaran 2020/20', '2020-07-01', '2021-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(44, 'Tahun Ajaran 2021/20', '2021-07-01', '2022-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(45, 'Tahun Ajaran 2022/20', '2022-07-01', '2023-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(46, 'Tahun Ajaran 2023/20', '2023-07-01', '2024-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(47, 'Tahun Ajaran 2024/20', '2024-07-01', '2025-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(48, 'Tahun Ajaran 2025/20', '2025-07-01', '2026-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(49, 'Tahun Ajaran 2026/20', '2026-07-01', '2027-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(50, 'Tahun Ajaran 2027/20', '2027-07-01', '2028-06-30', 0, '2026-09-23 02:15:57', '2026-09-23 02:15:57'),
(51, '2026/2027', '2026-11-04', '2027-11-04', 0, '2026-11-04 01:15:43', '2026-11-04 01:15:43'),
(53, '2026/2028', '2026-11-05', '2028-11-05', 1, '2026-11-05 00:47:06', '2026-11-05 00:47:06');

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_user`
--

CREATE TABLE `t_user` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verifed_at` timestamp NULL DEFAULT current_timestamp(),
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) NOT NULL,
  `role` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `t_user`
--

INSERT INTO `t_user` (`id`, `name`, `email`, `email_verifed_at`, `password`, `remember_token`, `role`, `created_at`, `updated_at`) VALUES
(1, 'User 1', 'user1@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$RSAzj89SW4GdcivVlTC3De03/cMZy.ZHm.uNqpps5iOQO9BoeRSYO', '', 'admin', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(2, 'User 2', 'user2@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$J2srBXqTIYg6I4Anqhnod.4wTqySVGj./3IcHNvowpxyVxPszaw5W', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(3, 'User 3', 'user3@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(4, 'User 4', 'user4@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(5, 'User 5', 'user5@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(6, 'User 6', 'user6@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(7, 'User 7', 'user7@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(8, 'User 8', 'user8@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(9, 'User 9', 'user9@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(10, 'User 10', 'user10@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(11, 'User 11', 'user11@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(12, 'User 12', 'user12@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(13, 'User 13', 'user13@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(14, 'User 14', 'user14@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(15, 'User 15', 'user15@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(16, 'User 16', 'user16@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(17, 'User 17', 'user17@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(18, 'User 18', 'user18@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(19, 'User 19', 'user19@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(20, 'User 20', 'user20@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(21, 'User 21', 'user21@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(22, 'User 22', 'user22@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(23, 'User 23', 'user23@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(24, 'User 24', 'user24@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(25, 'User 25', 'user25@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(26, 'User 26', 'user26@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(27, 'User 27', 'user27@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(28, 'User 28', 'user28@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(29, 'User 29', 'user29@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(30, 'User 30', 'user30@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(31, 'User 31', 'user31@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(32, 'User 32', 'user32@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$k1v4Lyo10htmmDXrVfRbBOuBrFbnmeolauLV47WUZ/DsrQL6nXNAa', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(33, 'User 33', 'user33@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(34, 'User 34', 'user34@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(35, 'User 35', 'user35@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(36, 'User 36', 'user36@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(37, 'User 37', 'user37@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(38, 'User 38', 'user38@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(39, 'User 39', 'user39@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(40, 'User 40', 'user40@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'guru', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(41, 'User 41', 'user41@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'petugas', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(42, 'User 42', 'user42@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'petugas', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(43, 'User 43', 'user43@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'petugas', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(44, 'User 44', 'user44@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'petugas', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(45, 'User 45', 'user45@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'petugas', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(46, 'User 46', 'user46@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'petugas', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(47, 'User 47', 'user47@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'petugas', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(48, 'User 48', 'user48@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'petugas', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(49, 'User 49', 'user49@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'petugas', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(50, 'Budi Hartono', 'guru50@ukk2026.sch.id', '2026-09-23 02:21:30', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNO1234567890', '', 'petugas', '2026-09-23 02:21:30', '2026-09-23 02:21:30'),
(51, 'Asih Permata', 'asih@gmail.com', '2026-10-23 05:38:18', '$2y$10$7wP1.7yYItzyzxAIIDBgo.GBXEP9e5CboIsfiKoNTjyRV6O9gkqqO', '', 'guru', '2026-10-23 05:38:18', '2026-10-23 05:38:18'),
(52, 'Yati Nur', 'yati@gmail.com', '2026-10-23 06:48:58', '$2y$10$Z8xX41jg5lBy7TeQHm37tOB3Kuy0W0zGpNQK/ElrbAEQ1lhSMDCXK', '', 'guru', '2026-10-23 06:48:58', '2026-10-23 06:48:58'),
(53, 'Yanti', 'yanti@gmail.com', '2026-10-23 06:49:58', '$2y$10$GgIqnfLPAP5BgWnQb68clecuKcMijZNft/kfYIsJBJ48VXF0Sk/1u', '', 'guru', '2026-10-23 06:49:58', '2026-10-23 06:49:58'),
(54, 'Asep', 'asep@gmail.com', '2026-10-23 06:55:06', '$2y$10$.9z9TAEKR1fbVQXT0/lE7uWpax3PE3oLO3u4Zx6qmt1bKF2jC.tui', '', 'guru', '2026-10-23 06:55:06', '2026-10-23 06:55:06'),
(55, 'Aji Makbur', 'aji@gmail.com', '2026-11-04 02:09:21', 'aji123456', '', 'guru', '2026-11-04 02:09:21', '2026-11-04 02:09:21'),
(56, 'Budi Bareto', 'budibaheula@gmail.com', '2026-11-04 02:20:57', '$2y$10$Ch.REePFXvIe5YlIHQRLzeSMtiAQoahhHQHBG8Ca5Y0L4fhRkJsda', '', 'guru', '2026-11-04 02:20:57', '2026-11-04 02:20:57'),
(57, 'Asih Raya', 'asihraya@gmail.com', '2026-11-04 02:33:58', '$2y$10$pZsY6TDE5ghyd2NdSAsV.uRy/upaaqqosHK2wT22wwJFcsIVJUBl.', '', 'guru', '2026-11-04 02:33:58', '2026-11-04 02:33:58'),
(58, 'Adittia Nur Latip', 'adittia@gmail.com', '2026-11-05 00:43:35', '$2y$12$P5toav6j1zVwNaOU2Pw9NeRmpxlDAVxZHlWrv4H6sk8WjI1kbE0r2', '', 'guru', '2026-11-05 00:43:35', '2026-11-05 00:43:35'),
(59, 'Budi Hartono', 'budi@gamil.com', '2026-11-05 02:16:04', '$2y$12$gBeODoO4LJ0Tr7zUCPYPa.UfWnzpGKqodJdtiCuX5F8paURXNCc/e', '', 'guru', '2026-11-05 02:16:04', '2026-11-05 02:16:04'),
(60, 'Raihan Hilal', 'raihan@gmail.com', '2026-11-06 07:53:12', '$2y$10$KJmntg6x10dYGTJ3q6t3Geyb1jeqqDonC686FaByyB/WdXo.63OoW', '', 'guru', '2026-11-06 07:53:12', '2026-11-06 07:53:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_wali_kelas`
--

CREATE TABLE `t_wali_kelas` (
  `id` int(11) NOT NULL,
  `tahun_ajaran_id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `guru_id` int(11) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `t_wali_kelas`
--

INSERT INTO `t_wali_kelas` (`id`, `tahun_ajaran_id`, `kelas_id`, `guru_id`, `tanggal_mulai`, `tanggal_selesai`, `status_aktif`, `created_at`, `updated_at`) VALUES
(2, 2, 2, 2, '2026-07-01', '2027-06-30', 0, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(3, 3, 3, 3, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(4, 4, 4, 4, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(5, 5, 5, 5, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(8, 8, 8, 8, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(9, 9, 9, 9, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(10, 10, 10, 10, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(11, 11, 11, 11, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(12, 12, 12, 12, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(13, 13, 13, 13, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(14, 14, 14, 14, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(15, 15, 15, 15, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(16, 16, 16, 16, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(17, 17, 17, 17, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(18, 18, 18, 18, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(19, 19, 19, 19, '2026-07-01', '2027-06-30', 0, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(20, 20, 20, 20, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(21, 21, 21, 21, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(22, 22, 22, 22, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(23, 23, 23, 23, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(24, 24, 24, 24, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(25, 25, 25, 25, '2026-07-01', '2027-06-30', 0, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(26, 26, 26, 26, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(27, 27, 27, 27, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(28, 28, 28, 28, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(29, 29, 29, 29, '2026-07-01', '2027-06-30', 0, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(30, 30, 30, 30, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(31, 31, 31, 31, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(32, 32, 32, 32, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(34, 34, 34, 34, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(35, 35, 35, 35, '2026-07-01', '2027-06-30', 0, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(36, 36, 36, 36, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(37, 37, 37, 37, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(38, 38, 38, 38, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(39, 39, 39, 39, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(44, 51, 44, 44, '2026-07-01', '2027-06-30', 0, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(45, 45, 45, 45, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(46, 46, 46, 46, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(47, 47, 47, 47, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(48, 48, 48, 48, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(49, 49, 49, 49, '2026-07-01', '2027-06-30', 1, '2026-09-23 04:19:18', '2026-09-23 04:19:18'),
(51, 51, 35, 31, '2026-07-01', '2027-06-30', 1, '2026-11-04 01:41:31', '2026-11-04 01:41:31'),
(52, 51, 2, 21, '2026-07-01', '2027-06-30', 1, '2026-11-04 02:04:50', '2026-11-04 02:04:50'),
(53, 51, 19, 2, '2026-07-01', '2027-06-30', 1, '2026-11-04 02:10:58', '2026-11-04 02:10:58'),
(54, 51, 29, 60, '2026-07-01', '2027-06-30', 1, '2026-11-05 01:15:30', '2026-11-05 01:15:30'),
(55, 51, 25, 26, '2026-07-01', '2027-06-30', 1, '2026-11-05 01:17:10', '2026-11-05 01:17:10'),
(56, 51, 3, 27, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(57, 51, 4, 16, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(58, 51, 5, 58, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(59, 51, 6, 18, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(60, 51, 7, 28, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(61, 51, 8, 44, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(62, 51, 9, 38, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(63, 51, 10, 53, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(64, 51, 11, 40, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(65, 51, 12, 11, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(66, 51, 13, 52, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(67, 51, 14, 32, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(68, 51, 15, 34, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(69, 51, 16, 47, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(70, 51, 17, 39, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(71, 51, 18, 55, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(72, 51, 20, 29, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(73, 51, 21, 24, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(74, 51, 22, 24, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(75, 51, 23, 15, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(76, 51, 24, 35, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(77, 51, 26, 49, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(78, 51, 27, 22, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(79, 51, 28, 54, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(80, 51, 30, 37, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(81, 51, 31, 56, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(82, 51, 32, 57, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(83, 51, 33, 59, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(84, 51, 34, 33, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(85, 51, 36, 36, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(86, 51, 37, 30, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(87, 51, 38, 45, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(88, 51, 39, 23, '2026-07-01', '2027-06-30', 1, '2026-11-06 06:35:25', '2026-11-06 06:35:25'),
(119, 51, 54, 63, '2026-11-06', '2028-11-06', 1, '2026-11-06 07:57:03', '2026-11-06 07:57:03');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `t_guru`
--
ALTER TABLE `t_guru`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `t_kelas`
--
ALTER TABLE `t_kelas`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `t_kelas_siswa`
--
ALTER TABLE `t_kelas_siswa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `tahun_ajaran_id` (`tahun_ajaran_id`),
  ADD KEY `kelas_id` (`kelas_id`);

--
-- Indeks untuk tabel `t_pelanggaran`
--
ALTER TABLE `t_pelanggaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pelanggaran_kategori_id` (`pelanggaran_kategori_id`);

--
-- Indeks untuk tabel `t_pelanggaran_kategori`
--
ALTER TABLE `t_pelanggaran_kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `t_pelanggaran_siswa`
--
ALTER TABLE `t_pelanggaran_siswa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tahun_ajaran_id` (`tahun_ajaran_id`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `pelanggaran_id` (`pelanggaran_id`),
  ADD KEY `kelas_id` (`kelas_id`),
  ADD KEY `pelanggaran_kategori_id` (`pelanggaran_kategori_id`),
  ADD KEY `guru_id` (`guru_id`);

--
-- Indeks untuk tabel `t_siswa`
--
ALTER TABLE `t_siswa`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `t_tahun_ajaran`
--
ALTER TABLE `t_tahun_ajaran`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `t_user`
--
ALTER TABLE `t_user`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `t_wali_kelas`
--
ALTER TABLE `t_wali_kelas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tahun_ajaran_id` (`tahun_ajaran_id`),
  ADD KEY `kelas_id` (`kelas_id`),
  ADD KEY `guru_id` (`guru_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `t_guru`
--
ALTER TABLE `t_guru`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT untuk tabel `t_kelas`
--
ALTER TABLE `t_kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT untuk tabel `t_kelas_siswa`
--
ALTER TABLE `t_kelas_siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT untuk tabel `t_pelanggaran`
--
ALTER TABLE `t_pelanggaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT untuk tabel `t_pelanggaran_kategori`
--
ALTER TABLE `t_pelanggaran_kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT untuk tabel `t_pelanggaran_siswa`
--
ALTER TABLE `t_pelanggaran_siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT untuk tabel `t_siswa`
--
ALTER TABLE `t_siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT untuk tabel `t_tahun_ajaran`
--
ALTER TABLE `t_tahun_ajaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT untuk tabel `t_user`
--
ALTER TABLE `t_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT untuk tabel `t_wali_kelas`
--
ALTER TABLE `t_wali_kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `t_guru`
--
ALTER TABLE `t_guru`
  ADD CONSTRAINT `t_guru_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `t_user` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `t_kelas_siswa`
--
ALTER TABLE `t_kelas_siswa`
  ADD CONSTRAINT `fk_kelas_siswa_kelas` FOREIGN KEY (`kelas_id`) REFERENCES `t_kelas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `t_kelas_siswa_ibfk_1` FOREIGN KEY (`kelas_id`) REFERENCES `t_kelas` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `t_kelas_siswa_ibfk_2` FOREIGN KEY (`siswa_id`) REFERENCES `t_siswa` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `t_kelas_siswa_ibfk_3` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `t_tahun_ajaran` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `t_pelanggaran`
--
ALTER TABLE `t_pelanggaran`
  ADD CONSTRAINT `t_pelanggaran_ibfk_1` FOREIGN KEY (`pelanggaran_kategori_id`) REFERENCES `t_pelanggaran_kategori` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `t_pelanggaran_siswa`
--
ALTER TABLE `t_pelanggaran_siswa`
  ADD CONSTRAINT `t_pelanggaran_siswa_ibfk_1` FOREIGN KEY (`guru_id`) REFERENCES `t_guru` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `t_pelanggaran_siswa_ibfk_2` FOREIGN KEY (`kelas_id`) REFERENCES `t_kelas` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `t_pelanggaran_siswa_ibfk_3` FOREIGN KEY (`pelanggaran_id`) REFERENCES `t_pelanggaran` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `t_pelanggaran_siswa_ibfk_4` FOREIGN KEY (`pelanggaran_kategori_id`) REFERENCES `t_pelanggaran_kategori` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `t_pelanggaran_siswa_ibfk_5` FOREIGN KEY (`siswa_id`) REFERENCES `t_siswa` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `t_pelanggaran_siswa_ibfk_6` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `t_tahun_ajaran` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `t_wali_kelas`
--
ALTER TABLE `t_wali_kelas`
  ADD CONSTRAINT `t_wali_kelas_ibfk_1` FOREIGN KEY (`guru_id`) REFERENCES `t_guru` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `t_wali_kelas_ibfk_2` FOREIGN KEY (`kelas_id`) REFERENCES `t_kelas` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `t_wali_kelas_ibfk_3` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `t_tahun_ajaran` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
