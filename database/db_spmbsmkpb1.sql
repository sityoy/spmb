-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 21 Jun 2026 pada 14.24
-- Versi server: 10.11.14-MariaDB-0ubuntu0.24.04.1
-- Versi PHP: 8.3.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_spmbsmkpb1`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `is_logged_in` tinyint(1) DEFAULT NULL,
  `last_activity` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pendaftar`
--

CREATE TABLE `pendaftar` (
  `id` int(11) NOT NULL,
  `no_pendaftaran` varchar(255) DEFAULT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `tempat_lahir` varchar(50) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `nisn` varchar(10) NOT NULL,
  `no_ijazah` varchar(50) NOT NULL,
  `asal_sekolah` varchar(100) NOT NULL,
  `riwayat_penyakit` varchar(255) DEFAULT NULL,
  `no_whatsapp` varchar(15) NOT NULL,
  `pilihan_jurusan` varchar(50) NOT NULL,
  `nilai_skl` decimal(5,2) DEFAULT 0.00,
  `tanggal_daftar` timestamp NOT NULL DEFAULT current_timestamp(),
  `nilai_tka` decimal(5,2) DEFAULT 0.00,
  `nilai_test` decimal(5,2) DEFAULT 0.00,
  `nilai_berkas` decimal(5,2) DEFAULT 0.00,
  `file_ijazah` varchar(255) DEFAULT NULL,
  `file_tka` varchar(255) DEFAULT NULL,
  `file_kk` varchar(255) DEFAULT NULL,
  `file_akte` varchar(255) DEFAULT NULL,
  `no_kk` varchar(16) DEFAULT NULL,
  `status_konfirmasi` varchar(20) DEFAULT 'Belum',
  `gelombang` tinyint(2) DEFAULT NULL,
  `alasan_pembatalan` text DEFAULT NULL,
  `file_ktp_bapak` varchar(255) DEFAULT NULL,
  `file_ktp_ibu` varchar(255) DEFAULT NULL,
  `file_sptjm` varchar(255) DEFAULT NULL,
  `status_kjp` enum('Ya','Tidak') DEFAULT 'Tidak',
  `no_rek_kjp` varchar(50) DEFAULT NULL,
  `file_tabungan_kjp` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pendaftar`
--

INSERT INTO `pendaftar` (`id`, `no_pendaftaran`, `nama_lengkap`, `tempat_lahir`, `tanggal_lahir`, `nisn`, `no_ijazah`, `asal_sekolah`, `riwayat_penyakit`, `no_whatsapp`, `pilihan_jurusan`, `nilai_skl`, `tanggal_daftar`, `nilai_tka`, `nilai_test`, `nilai_berkas`, `file_ijazah`, `file_tka`, `file_kk`, `file_akte`, `no_kk`, `status_konfirmasi`, `gelombang`, `alasan_pembatalan`, `file_ktp_bapak`, `file_ktp_ibu`, `file_sptjm`, `status_kjp`, `no_rek_kjp`, `file_tabungan_kjp`) VALUES
(1, 'SPMB-SMKPB1-2026-4400', 'MATTHEW TRIADERALDO', 'Jakarta', '2009-08-18', '0091370856', '13/PK.01.02', 'SMP NEGRI 249', 'Tidak ada', '085927511988', 'Manajemen Perkantoran dan Layanan Bisnis', 81.85, '2026-06-15 01:55:06', 46.60, 79.73, 0.00, '0091370856_ijazah_1781488506.jpeg', '0091370856_tka_1781488506.jpeg', '0091370856_kk_1781488506.jpeg', '0091370856_akte_1781488506.jpeg', '3173040211230003', 'Menunggu', 1, NULL, '0091370856_ktpbapak_1781488506.jpeg', '0091370856_ktpibu_1781488506.jpeg', '0091370856_sptjm_1781488506.jpeg', 'Tidak', '', ''),
(2, 'SPMB-SMKPB1-2026-1935', 'CARLISSA AGRATA', 'Jakarta', '2011-11-11', '0112819297', '046/SK/SMP-PB/VI/2026', 'SMP PERMATA BUNDA', 'Tidak ada', '081219913611', 'Manajemen Perkantoran dan Layanan Bisnis', 82.07, '2026-06-15 02:45:32', 63.33, 82.70, 0.00, '0112819297_ijazah_1781491532.jpeg', '0112819297_tka_1781491532.jpeg', '0112819297_kk_1781491532.jpeg', '0112819297_akte_1781491532.jpeg', '3173042112100169', 'Menunggu', 1, NULL, '0112819297_ktpbapak_1781491532.jpeg', '0112819297_ktpibu_1781491532.jpeg', '0112819297_sptjm_1781491532.jpeg', 'Ya', '300231988502', '0112819297_tabungankjp_1781491532.jpeg'),
(4, 'SPMB-SMKPB1-2026-1425', 'ALVIN CULLEN', 'Jakarta', '2010-04-18', '0104155757', '166.100/PK.01.02', 'SMP NEGRI 83 JAKARTA', 'Tidak ada', '085282666955', 'Manajemen Perkantoran dan Layanan Bisnis', 82.82, '2026-06-15 02:58:26', 63.33, 80.66, 0.00, '0104155757_ijazah_1781492306.jpeg', '0104155757_tka_1781492306.jpeg', '0104155757_kk_1781492306.jpeg', '0104155757_akte_1781492306.jpeg', '3173042302151011', 'Menunggu', 1, NULL, '0104155757_ktpbapak_1781492306.jpeg', '0104155757_ktpibu_1781492306.jpeg', '0104155757_sptjm_1781492306.jpeg', 'Ya', '10223123403', '0104155757_tabungankjp_1781492306.jpeg'),
(5, 'SPMB-SMKPB1-2026-3985', 'DEVIN SHEN', 'Jakarta', '2011-03-01', '0113814175', '046/SK', 'SMP PERMATA BUNDA', 'Tidak ada', '081286672288', 'Manajemen Perkantoran dan Layanan Bisnis', 77.46, '2026-06-15 03:16:11', 63.33, 75.33, 0.00, '0113814175_ijazah_1781493371.jpeg', '0113814175_tka_1781493371.jpeg', '0113814175_kk_1781493371.jpeg', '0113814175_akte_1781493371.jpeg', '3173041310101044', 'Menunggu', 1, NULL, '0113814175_ktpbapak_1781493371.jpeg', '0113814175_ktpibu_1781493371.jpeg', '0113814175_sptjm_1781493371.jpeg', 'Tidak', '', ''),
(6, 'SPMB-SMKPB1-2026-9365', 'SANDY KURNIAWAN', 'Jakarta', '2011-05-10', '0113531147', '002/SKL/SMP-CMI/VI/2026', 'SMP CINDERA MATA INDAH', 'TIDAK ADA', '0895321910082', 'Akuntansi dan Keuangan Lembaga', 78.90, '2026-06-15 03:34:07', 56.67, 72.46, 0.00, '0113531147_ijazah_1781494447.jpeg', '0113531147_tka_1781494447.jpeg', '0113531147_kk_1781494447.jpeg', '0113531147_akte_1781494447.jpeg', '3173041011150010', 'Menunggu', 1, NULL, '0113531147_ktpbapak_1781494447.jpeg', '0113531147_ktpibu_1781494447.jpeg', '0113531147_sptjm_1781494447.jpeg', 'Tidak', '', ''),
(7, 'SPMB-SMKPB1-2026-5989', 'CARLLA FLORENCIA', 'Jakarta', '2011-04-21', '0018732391', '027/KEP/SMP-HK/VI/2026', 'SMP HATI KUDUS', 'Tidak ada', '081220419139', 'Manajemen Perkantoran dan Layanan Bisnis', 75.69, '2026-06-15 03:42:32', 0.00, 69.11, 0.00, '0018732391_ijazah_1781494952.jpeg', '0018732391_tka_1781494952.jpeg', '0018732391_kk_1781494952.jpeg', '0018732391_akte_1781494952.jpeg', '3173022910190008', 'Menunggu', 1, NULL, '0018732391_ktpbapak_1781494952.jpeg', '0018732391_ktpibu_1781494952.jpeg', '0018732391_sptjm_1781494952.jpeg', 'Ya', '32423117610', '0018732391_tabungankjp_1781494952.jpeg'),
(8, 'SPMB-SMKPB1-2026-9059', 'ANGEL CHRISTIANA', 'Jakarta', '2011-04-16', '0118960126', '047/SK', 'SMP PERMATA BUNDA', 'Tidak ada', '081398294558', 'Manajemen Perkantoran dan Layanan Bisnis', 78.34, '2026-06-15 03:59:11', 56.67, 76.46, 0.00, '0118960126_ijazah_1781495951.jpeg', '0118960126_tka_1781495951.jpeg', '0118960126_kk_1781495951.jpeg', '0118960126_akte_1781495951.jpeg', '3173042110240003', 'Menunggu', 1, NULL, '0118960126_ktpbapak_1781495951.jpeg', '0118960126_ktpibu_1781495951.jpeg', '0118960126_sptjm_1781495951.jpeg', 'Tidak', '', ''),
(9, 'SPMB-SMKPB1-2026-1009', 'FELIX LIEVARO', 'Jakarta', '2010-10-15', '0108025554', '054/SK/SMP-PB/VI/2026', 'SMP PERMATA BUNDA', 'TIDAK ADA', '085847531046', 'Manajemen Perkantoran dan Layanan Bisnis', 80.20, '2026-06-15 04:12:07', 63.33, 0.00, 0.00, '0108025554_ijazah_1781496727.jpeg', '0108025554_tka_1781496727.jpeg', '0108025554_kk_1781496727.jpeg', '0108025554_akte_1781496727.jpeg', '3173041101100079', 'Menunggu', 1, NULL, '0108025554_ktpbapak_1781496727.jpeg', '0108025554_ktpibu_1781496727.jpeg', '0108025554_sptjm_1781496727.jpeg', 'Tidak', '', ''),
(10, 'SPMB-SMKPB1-2026-8319', 'CHEN CUN YI', 'Kuching', '2011-03-16', '0115397193', '051', 'SMP PERMATA BUNDA', 'TIDAK ADA', '085782922159', 'Akuntansi dan Keuangan Lembaga', 79.25, '2026-06-15 04:23:48', 68.33, 79.00, 0.00, '0115397193_ijazah_1781497428.jpeg', '0115397193_tka_1781497428.jpeg', '0115397193_kk_1781497428.jpeg', '0115397193_akte_1781497428.jpeg', '3173042504250004', 'Menunggu', 1, NULL, '0115397193_ktpbapak_1781497428.jpeg', '0115397193_ktpibu_1781497428.jpeg', '0115397193_sptjm_1781497428.jpeg', 'Tidak', '', ''),
(11, 'SPMB-SMKPB1-2026-8354', 'OKTAFIANUS', 'Jakarta', '2010-10-12', '0107636121', '055/SK/SMP-PB/VI/2026', 'SMP PERMATA BUNDA', 'DARAH TINGGI', '0895629369497', 'Manajemen Perkantoran dan Layanan Bisnis', 80.19, '2026-06-15 04:27:42', 63.33, 78.00, 0.00, '0107636121_ijazah_1781497662.jpeg', '0107636121_tka_1781497662.jpeg', '0107636121_kk_1781497662.jpeg', '0107636121_akte_1781497662.jpeg', '3173042811180011', 'Menunggu', 1, NULL, '0107636121_ktpbapak_1781497662.jpeg', '0107636121_ktpibu_1781497662.jpeg', '0107636121_sptjm_1781497662.jpeg', 'Tidak', '', ''),
(12, 'SPMB-SMKPB1-2026-4455', 'FEBRYANT WIJAYA', 'Jakarta', '2011-02-28', '0122905828', '056/SK/SMP-PB/VI/2026', 'SMP PERMATA BUNDA', 'TIDAK ADA', '085774236972', 'Manajemen Perkantoran dan Layanan Bisnis', 77.25, '2026-06-15 04:40:15', 68.00, 75.46, 0.00, '0122905828_ijazah_1781498415.jpeg', '0122905828_tka_1781498415.jpeg', '0122905828_kk_1781498415.jpeg', '0122905828_akte_1781498415.jpeg', '3173040202160021', 'Menunggu', 1, NULL, '0122905828_ktpbapak_1781498415.jpeg', '0122905828_ktpibu_1781498415.jpeg', '0122905828_sptjm_1781498415.jpeg', 'Ya', '30023195320', '0122905828_tabungankjp_1781498415.jpeg'),
(14, 'SPMB-SMKPB1-2026-1676', 'FELIX CHRISTIAN CUNG', 'Jakarta', '2011-03-05', '0117992825', '002/SKL/SMP-CMI', 'SMP CINDERA MATA INDAH', 'TIDAK ADA', '0895911212353', 'Akuntansi dan Keuangan Lembaga', 83.30, '2026-06-15 05:10:17', 60.00, 83.20, 0.00, '0117992825_ijazah_1781500217.jpeg', '0117992825_tka_1781500217.jpeg', '0117992825_kk_1781500217.jpeg', '0117992825_akte_1781500217.jpeg', '3173031201091122', 'Menunggu', 1, NULL, '0117992825_ktpbapak_1781500217.jpeg', '0117992825_ktpibu_1781500217.jpeg', '0117992825_sptjm_1781500217.jpeg', 'Ya', '31623076394', '0117992825_tabungankjp_1781500217.jpeg'),
(15, 'SPMB-SMKPB1-2026-2478', 'ENI', 'Jakarta', '2011-03-22', '0114485472', '057/SK/SMP-PB/VI/2026', 'SMP PERMATA BUNDA', 'TIDAK ADA', '0895391848001', 'Akuntansi dan Keuangan Lembaga', 75.68, '2026-06-15 05:17:03', 31.66, 74.00, 0.00, '0114485472_ijazah_1781500623.jpeg', '0114485472_tka_1781500623.jpeg', '0114485472_kk_1781500623.jpeg', '0114485472_akte_1781500623.jpeg', '3173042406111028', 'Menunggu', 1, NULL, '0114485472_ktpbapak_1781500623.jpeg', '0114485472_ktpibu_1781500623.jpeg', '0114485472_sptjm_1781500623.jpeg', 'Ya', '30023198892', '0114485472_tabungankjp_1781500623.jpeg'),
(16, 'SPMB-SMKPB1-2026-4683', 'ARDIANSYAH DWI LAKSONO', 'Jakarta', '2011-04-14', '0113755309', '032/081/SMP-BT.IKA/V/2026', 'SMP BHINEKA TUNGGAL IKA', 'TIDAK ADA', '087861754482', 'Akuntansi dan Keuangan Lembaga', 84.13, '2026-06-15 07:41:39', 73.33, 78.33, 0.00, '0113755309_ijazah_1781509299.jpeg', '0113755309_tka_1781509299.jpeg', '0113755309_kk_1781509299.jpeg', '0113755309_akte_1781509299.jpeg', '3171012610100027', 'Menunggu', 1, '', '0113755309_ktpbapak_1781509299.jpeg', '0113755309_ktpibu_1781509299.jpeg', '0113755309_sptjm_1781509299.jpeg', 'Tidak', '', ''),
(17, 'SPMB-SMKPB1-2026-7021', 'BRYAN VINCENTIUS', 'Singkawang', '2011-07-18', '0115866655', '112/SMP.BDY/SKL/VI/2026', 'SMP BUDAYA', 'TIDAK ADA', '082125570985', 'Akuntansi dan Keuangan Lembaga', 80.40, '2026-06-15 08:29:24', 66.67, 79.66, 0.00, '0115866655_ijazah_1781512164.jpeg', '0115866655_tka_1781512164.jpeg', '0115866655_kk_1781512164.jpeg', '0115866655_akte_1781512164.jpeg', '3173040309121022', 'Menunggu', 1, NULL, '0115866655_ktpbapak_1781512164.jpeg', '0115866655_ktpibu_1781512164.jpeg', '0115866655_sptjm_1781512164.jpeg', 'Ya', '30523101997', '0115866655_tabungankjp_1781512164.jpeg'),
(18, 'SPMB-SMKPB1-2026-3225', 'RIKI BONG', 'Tebas', '2008-08-10', '0081227309', '109/SMP.BDY/SKL/VI/2026', 'SMP BUDAYA', 'TIDAK ADA', '081213032455', 'Manajemen Perkantoran dan Layanan Bisnis', 0.00, '2026-06-17 03:55:42', 46.67, 74.00, 0.00, '0081227309_ijazah_1781668542.jpeg', '0081227309_tka_1781668542.jpeg', '0081227309_kk_1781668542.jpeg', '0081227309_akte_1781668542.jpeg', '3173040410230018', 'Menunggu', 1, NULL, '0081227309_ktpbapak_1781668542.jpeg', '0081227309_ktpibu_1781668542.jpeg', '0081227309_sptjm_1781668542.jpeg', 'Tidak', '', ''),
(19, 'SPMB-SMKPB1-2026-8275', 'ELVIN FERNANDO', 'Jakarta', '2011-06-19', '0119763297', '110/SMP.BDY/SKL/VI/2026', 'smp budaya', 'TIDAK ADA', '082261285848', 'Manajemen Perkantoran dan Layanan Bisnis', 0.00, '2026-06-17 04:04:47', 43.33, 74.26, 0.00, '0119763297_ijazah_1781669087.jpeg', '0119763297_tka_1781669087.jpeg', '0119763297_kk_1781669087.jpeg', '0119763297_akte_1781669087.jpeg', '3173042302170005', 'Menunggu', 1, NULL, '0119763297_ktpbapak_1781669087.jpeg', '0119763297_ktpibu_1781669087.jpeg', '0119763297_sptjm_1781669087.jpeg', 'Tidak', '', ''),
(20, 'SPMB-SMKPB1-2026-5487', 'MARCO VINCENT', 'Jakarta', '2011-07-27', '0118156546', '058/SK/SMP-PB/VI/2026', 'SMP PERMATA BUNDA', 'TIDAK ADA', '081289205573', 'Manajemen Perkantoran dan Layanan Bisnis', 81.94, '2026-06-17 08:08:56', 70.00, 79.00, 0.00, '0118156546_ijazah_1781683736.jpeg', '0118156546_tka_1781683736.jpeg', '0118156546_kk_1781683736.jpeg', '0118156546_akte_1781683736.jpeg', '3173042707111003', 'Menunggu', 1, NULL, '0118156546_ktpbapak_1781683736.jpeg', '0118156546_ktpibu_1781683736.jpeg', '0118156546_sptjm_1781683736.jpeg', 'Ya', '30023224168', '0118156546_tabungankjp_1781683736.jpeg'),
(21, 'SPMB-SMKPB1-2026-1306', 'LIONEL SAPUTRA CONG', 'Jakarta', '2011-08-28', '0119269211', '50 TAHUN 2026', 'SMP NEGRI 63 JAKARTA', 'tidak ada', '085777118549', 'Manajemen Perkantoran dan Layanan Bisnis', 89.79, '2026-06-18 03:52:13', 66.67, 85.73, 0.00, '0119269211_ijazah_1781754733.jpg', '0119269211_tka_1781754733.jpg', '0119269211_kk_1781754733.jpg', '0119269211_akte_1781754733.jpg', '3173042001094424', 'Menunggu', 1, NULL, '0119269211_ktpbapak_1781754733.jpg', '0119269211_ktpibu_1781754733.jpg', '0119269211_sptjm_1781754733.jpg', 'Tidak', '', ''),
(22, 'SPMB-SMKPB1-2026-6986', 'KASIH AGAVE UNAS', 'Barito Utara', '2011-09-27', '0114161483', '029/SMP-CN/VI/2026', 'SMP Candra Naya', 'Tidak Ada', '081281161925', 'Akuntansi dan Keuangan Lembaga', 81.91, '2026-06-18 06:56:08', 50.00, 0.00, 0.00, '0114161483_ijazah_1781765768.jpg', '0114161483_tka_1781765768.jpg', '0114161483_kk_1781765768.jpg', '0114161483_akte_1781765768.jpg', '3172012505151005', 'Menunggu', 1, NULL, '0114161483_ktpbapak_1781765768.jpg', '0114161483_ktpibu_1781765768.jpg', '0114161483_sptjm_1781765768.jpg', 'Ya', '1022312301', '0114161483_tabungankjp_1781765768.jpg'),
(23, 'SPMB-SMKPB1-2026-6292', 'CINDY CECILIA', 'Jakarta', '2011-11-22', '0114725564', '222/SKL/SMP-CMI', 'SMP CINDERA MATA INDAH', 'TIDAK ADA', '085219519899', 'Manajemen Perkantoran dan Layanan Bisnis', 83.00, '2026-06-19 04:02:32', 66.67, 83.13, 0.00, '0114725564_ijazah_1781841752.jpg', '0114725564_tka_1781841752.jpg', '0114725564_kk_1781841752.jpg', '0114725564_akte_1781841752.jpg', '3173041303200018', 'Menunggu', 1, NULL, '0114725564_ktpbapak_1781841752.jpg', '0114725564_ktpibu_1781841752.jpg', '0114725564_sptjm_1781841752.jpg', 'Ya', '30023198515', '0114725564_tabungankjp_1781841752.jpg'),
(24, 'SPMB-SMKPB1-2026-4280', 'DOMINIQUE ANGEL LEA BOY', 'Jakarta', '2011-04-26', '0117441627', '112 - 2026', 'SMP NEGRI 63 JAKARTA', 'TIDAK ADA', '081289221108', 'Manajemen Perkantoran dan Layanan Bisnis', 86.48, '2026-06-19 04:31:52', 40.00, 81.66, 0.00, '0117441627_ijazah_1781843512.jpg', 'file_tka_1781843802_765.jpg', '0117441627_kk_1781843512.jpg', '0117441627_akte_1781843512.jpg', '3173041001098910', 'Menunggu', 1, NULL, '0117441627_ktpbapak_1781843512.jpg', '0117441627_ktpibu_1781843512.jpg', '0117441627_sptjm_1781843512.jpg', 'Ya', '30023197764', '0117441627_tabungankjp_1781843512.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id` int(11) NOT NULL,
  `buka_gel_1` datetime DEFAULT NULL,
  `buka_gel_2` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengaturan`
--

INSERT INTO `pengaturan` (`id`, `buka_gel_1`, `buka_gel_2`) VALUES
(1, '2026-07-01 15:00:00', '2026-07-10 15:00:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengguna`
--

CREATE TABLE `pengguna` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_login` tinyint(10) DEFAULT NULL,
  `last_activity` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengguna`
--

INSERT INTO `pengguna` (`id`, `username`, `password`, `is_login`, `last_activity`) VALUES
(1, 'Admin1', '$2y$12$smX.90pStfOk6yNyiJi3pO4gSbluMxoxr3qxaKUCQe0HTeFW05g8i', 0, NULL),
(2, 'Admin2', '$2y$12$smX.90pStfOk6yNyiJi3pO4gSbluMxoxr3qxaKUCQe0HTeFW05g8i', 0, NULL),
(3, 'Admin3', '$2y$12$smX.90pStfOk6yNyiJi3pO4gSbluMxoxr3qxaKUCQe0HTeFW05g8i', 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `pendaftar`
--
ALTER TABLE `pendaftar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_pendaftaran` (`no_pendaftaran`);

--
-- Indeks untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `pendaftar`
--
ALTER TABLE `pendaftar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
