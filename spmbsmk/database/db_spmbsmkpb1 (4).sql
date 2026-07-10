-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 10, 2026 at 03:14 PM
-- Server version: 10.11.14-MariaDB-0ubuntu0.24.04.1
-- PHP Version: 8.3.31

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
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `is_logged_in` tinyint(1) DEFAULT NULL,
  `last_activity` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `daftar_ulang`
--

CREATE TABLE `daftar_ulang` (
  `id_du` int(11) NOT NULL,
  `id_pendaftar` int(11) NOT NULL,
  `tanggal_du` datetime NOT NULL,
  `ukuran_baju` varchar(10) NOT NULL,
  `bawa_skl_asli` enum('Ya','Tidak') DEFAULT 'Tidak',
  `bawa_kk_fc` enum('Ya','Tidak') DEFAULT 'Tidak',
  `bawa_akte_fc` enum('Ya','Tidak') DEFAULT 'Tidak',
  `bawa_ktp_ortu_fc` enum('Ya','Tidak') DEFAULT 'Tidak',
  `bawa_rapot_fc` enum('Ya','Tidak') DEFAULT 'Tidak',
  `catatan_du` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daftar_ulang`
--

INSERT INTO `daftar_ulang` (`id_du`, `id_pendaftar`, `tanggal_du`, `ukuran_baju`, `bawa_skl_asli`, `bawa_kk_fc`, `bawa_akte_fc`, `bawa_ktp_ortu_fc`, `bawa_rapot_fc`, `catatan_du`) VALUES
(1, 36, '2026-07-02 05:13:32', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Ya', ''),
(2, 39, '2026-07-02 05:21:57', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(3, 56, '2026-07-02 05:22:31', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(4, 29, '2026-07-02 05:25:13', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(5, 37, '2026-07-02 05:26:16', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(6, 45, '2026-07-02 05:28:38', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(7, 35, '2026-07-02 05:29:49', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(8, 4, '2026-07-02 05:30:14', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(9, 2, '2026-07-02 05:31:01', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(10, 1, '2026-07-02 05:31:55', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(11, 41, '2026-07-02 05:32:56', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(12, 65, '2026-07-02 05:34:22', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(13, 59, '2026-07-02 05:34:56', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(14, 10, '2026-07-02 05:35:22', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(15, 31, '2026-07-02 05:36:08', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(16, 38, '2026-07-02 05:36:33', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(17, 28, '2026-07-02 05:37:06', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(18, 5, '2026-07-02 05:37:33', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(19, 33, '2026-07-02 05:38:07', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(20, 14, '2026-07-02 05:38:43', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(21, 58, '2026-07-02 06:15:40', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(22, 20, '2026-07-02 06:17:29', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(23, 21, '2026-07-02 06:18:04', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(24, 9, '2026-07-02 06:19:42', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(25, 11, '2026-07-02 06:20:11', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(26, 44, '2026-07-02 06:20:40', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(27, 25, '2026-07-02 06:21:18', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(28, 26, '2026-07-02 06:21:52', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(29, 12, '2026-07-02 06:22:56', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(30, 27, '2026-07-02 06:29:33', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(31, 24, '2026-07-02 06:30:32', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(32, 54, '2026-07-02 06:31:35', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(33, 47, '2026-07-02 06:32:17', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(34, 17, '2026-07-02 06:32:38', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(35, 50, '2026-07-02 06:36:29', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(36, 57, '2026-07-02 06:36:51', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(37, 64, '2026-07-02 06:37:09', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(38, 49, '2026-07-02 06:37:37', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(39, 62, '2026-07-02 06:38:00', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(40, 51, '2026-07-02 06:38:33', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(41, 53, '2026-07-02 06:38:54', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(42, 22, '2026-07-02 06:39:20', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(43, 6, '2026-07-02 06:39:44', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(44, 40, '2026-07-02 06:40:13', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(45, 60, '2026-07-02 06:41:04', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', ''),
(46, 48, '2026-07-02 06:41:27', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Ya', ''),
(47, 55, '2026-07-02 06:42:29', 'L', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', '');

-- --------------------------------------------------------

--
-- Table structure for table `pendaftar`
--

CREATE TABLE `pendaftar` (
  `id` int(11) NOT NULL,
  `no_pendaftaran` varchar(255) DEFAULT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `nik` varchar(16) DEFAULT NULL,
  `tempat_lahir` varchar(50) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `nisn` varchar(10) NOT NULL,
  `no_ijazah` varchar(50) NOT NULL,
  `asal_sekolah` varchar(100) NOT NULL,
  `riwayat_penyakit` varchar(255) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `kelurahan` varchar(100) DEFAULT NULL,
  `kecamatan` varchar(100) DEFAULT NULL,
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
  `status_daftar_ulang` enum('Belum','Sudah') DEFAULT 'Belum',
  `gelombang` varchar(20) DEFAULT NULL,
  `alasan_pembatalan` text DEFAULT NULL,
  `file_ktp_bapak` varchar(255) DEFAULT NULL,
  `file_ktp_ibu` varchar(255) DEFAULT NULL,
  `file_sptjm` varchar(255) DEFAULT NULL,
  `status_kjp` enum('Ya','Tidak') DEFAULT 'Tidak',
  `no_rek_kjp` varchar(50) DEFAULT NULL,
  `file_tabungan_kjp` varchar(255) DEFAULT NULL,
  `catatan_panitia` text DEFAULT NULL,
  `is_detail_filled` tinyint(1) DEFAULT 0,
  `status_jakedu` enum('Belum','Sudah') DEFAULT 'Belum'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pendaftar`
--

INSERT INTO `pendaftar` (`id`, `no_pendaftaran`, `nama_lengkap`, `nik`, `tempat_lahir`, `tanggal_lahir`, `nisn`, `no_ijazah`, `asal_sekolah`, `riwayat_penyakit`, `alamat`, `kelurahan`, `kecamatan`, `no_whatsapp`, `pilihan_jurusan`, `nilai_skl`, `tanggal_daftar`, `nilai_tka`, `nilai_test`, `nilai_berkas`, `file_ijazah`, `file_tka`, `file_kk`, `file_akte`, `no_kk`, `status_konfirmasi`, `status_daftar_ulang`, `gelombang`, `alasan_pembatalan`, `file_ktp_bapak`, `file_ktp_ibu`, `file_sptjm`, `status_kjp`, `no_rek_kjp`, `file_tabungan_kjp`, `catatan_panitia`, `is_detail_filled`, `status_jakedu`) VALUES
(1, 'SPMB-SMKPB1-2026-4400', 'MATTHEW TRIADERALDO', '3173061808090003', 'Jakarta', '2009-08-18', '0091370856', '2628290059', 'SMP NEGRI 249', 'Tidak ada', 'Tambora I GG IV No. 92', 'Tambora', 'Tambora', '085927511988', 'Manajemen Perkantoran dan Layanan Bisnis', 80.57, '2026-06-15 01:55:06', 46.60, 79.73, 0.00, 'file_ijazah_1782723500_580.jpg', '0091370856_tka_1781488506.jpeg', '0091370856_kk_1781488506.jpeg', '0091370856_akte_1781488506.jpeg', '3173040211230003', 'LULUS', 'Sudah', '1', NULL, '0091370856_ktpbapak_1781488506.jpeg', '0091370856_ktpibu_1781488506.jpeg', '0091370856_sptjm_1781488506.jpeg', 'Tidak', '', '', 'Bawa FC TKA dan Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(2, 'SPMB-SMKPB1-2026-1935', 'CARLISSA AGRATA', '3173045111111001', 'Jakarta', '2011-11-11', '0112819297', '2627870050', 'SMP PERMATA BUNDA', 'Tidak ada', 'TSS GG TRIKORA II / 38', 'Duri Utara', 'Tambora', '081219913611', 'Manajemen Perkantoran dan Layanan Bisnis', 81.80, '2026-06-15 02:45:32', 63.33, 82.70, 0.00, 'file_ijazah_1782724100_353.jpg', '0112819297_tka_1781491532.jpeg', '0112819297_kk_1781491532.jpeg', '0112819297_akte_1781491532.jpeg', '3173042112100169', 'LULUS', 'Sudah', '1', NULL, '0112819297_ktpbapak_1781491532.jpeg', '0112819297_ktpibu_1781491532.jpeg', '0112819297_sptjm_1781491532.jpeg', 'Ya', '300231988502', '0112819297_tabungankjp_1781491532.jpeg', 'Bawa FC Sertifikat TKA dan Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(4, 'SPMB-SMKPB1-2026-1425', 'ALVIN CULLEN', '3173041804101002', 'Jakarta', '2010-04-18', '0104155757', '2627570101', 'SMP NEGRI 83 JAKARTA', 'Tidak ada', 'Rusunawa Tambora I TWR A LT XI / XII', 'Angke', 'Tambora', '085282666955', 'Manajemen Perkantoran dan Layanan Bisnis', 79.90, '2026-06-15 02:58:26', 56.67, 80.66, 0.00, 'file_ijazah_1782723725_736.jpg', '0104155757_tka_1781492306.jpeg', '0104155757_kk_1781492306.jpeg', '0104155757_akte_1781492306.jpeg', '3173042302151011', 'LULUS', 'Sudah', '1', NULL, '0104155757_ktpbapak_1781492306.jpeg', '0104155757_ktpibu_1781492306.jpeg', '0104155757_sptjm_1781492306.jpeg', 'Ya', '10223123403', '0104155757_tabungankjp_1781492306.jpeg', 'Bawa FC Rapot Smt 1 - 5 dan TKA (Lapor Diri)', 0, 'Sudah'),
(5, 'SPMB-SMKPB1-2026-3985', 'DEVIN SHEN', '3173040103111004', 'Jakarta', '2011-03-01', '0113814175', '2628060041', 'SMP PERMATA BUNDA', 'Tidak ada', 'Jembatan Besi', 'Jembatan Besi', 'Tambora', '081286672288', 'Manajemen Perkantoran dan Layanan Bisnis', 76.73, '2026-06-15 03:16:11', 63.33, 75.33, 0.00, 'file_ijazah_1782719664_408.jpg', '0113814175_tka_1781493371.jpeg', '0113814175_kk_1781493371.jpeg', '0113814175_akte_1781493371.jpeg', '3173041310101044', 'LULUS', 'Sudah', '1', NULL, '0113814175_ktpbapak_1781493371.jpeg', '0113814175_ktpibu_1781493371.jpeg', '0113814175_sptjm_1781493371.jpeg', 'Tidak', '', '', 'Lengkap', 0, 'Sudah'),
(6, 'SPMB-SMKPB1-2026-9365', 'SANDY KURNIAWAN', '3173011005111015', 'Jakarta', '2011-05-10', '0113531147', '2627930014', 'SMP CINDERA MATA INDAH', 'TIDAK ADA', 'Jl. Trikora II No. 19', 'Duri Utara', 'Tambora', '0895321910082', 'Akuntansi dan Keuangan Lembaga', 78.20, '2026-06-15 15:34:07', 56.67, 72.46, 0.00, 'file_ijazah_1782724722_449.jpg', '0113531147_tka_1781494447.jpeg', '0113531147_kk_1781494447.jpeg', '0113531147_akte_1781494447.jpeg', '3173041011150010', 'LULUS', 'Sudah', '1', NULL, '0113531147_ktpbapak_1781494447.jpeg', '0113531147_ktpibu_1781494447.jpeg', '0113531147_sptjm_1781494447.jpeg', 'Tidak', '', '', 'Lengkap', 0, 'Sudah'),
(7, 'SPMB-SMKPB1-2026-5989', 'CARLLA FLORENCIA', '3172056104111002', 'Jakarta', '2011-04-21', '0018732391', '027/KEP/SMP-HK/VI/2026', 'SMP HATI KUDUS', 'Tidak ada', 'Jl. Utama IV No. 1 A', 'Jelambar', 'Grogol Petamburan', '081220419139', 'Manajemen Perkantoran dan Layanan Bisnis', 75.69, '2026-06-15 03:42:32', 0.00, 69.11, 0.00, '0018732391_ijazah_1781494952.jpeg', '0018732391_tka_1781494952.jpeg', '0018732391_kk_1781494952.jpeg', '0018732391_akte_1781494952.jpeg', '3173022910190008', 'Tidak Jadi', 'Belum', '1', 'Tidak Lolos', '0018732391_ktpbapak_1781494952.jpeg', '0018732391_ktpibu_1781494952.jpeg', '0018732391_sptjm_1781494952.jpeg', 'Ya', '32423117610', '0018732391_tabungankjp_1781494952.jpeg', 'Belum ada Sidanira dan TKA, Bawa FC Rapot Smt 1 - 5 dan (Lapor Diri)', 0, 'Sudah'),
(8, 'SPMB-SMKPB1-2026-9059', 'ANGEL CHRISTIANA', '3173045604111011', 'Jakarta', '2011-04-16', '0118960126', '047/SK', 'SMP PERMATA BUNDA', 'Tidak ada', 'Duri Bangkit', 'Jembatan Besi', 'Tambora', '081398294558', 'Manajemen Perkantoran dan Layanan Bisnis', 76.97, '2026-06-15 03:59:11', 46.67, 76.46, 0.00, 'file_ijazah_1782724335_481.jpg', '0118960126_tka_1781495951.jpeg', '0118960126_kk_1781495951.jpeg', '0118960126_akte_1781495951.jpeg', '3173042110240003', 'Tidak Jadi', 'Belum', '1', 'Tidak Lolos', '0118960126_ktpbapak_1781495951.jpeg', '0118960126_ktpibu_1781495951.jpeg', '0118960126_sptjm_1781495951.jpeg', 'Tidak', '', '', 'Bawa FC Sertifikat TKA (Lapor Diri)', 0, 'Sudah'),
(9, 'SPMB-SMKPB1-2026-1009', 'FELIX LIEVARO', '3173041510101006', 'Jakarta', '2010-10-15', '0108025554', '2628060013', 'SMP PERMATA BUNDA', 'TIDAK ADA', 'Krendang Raya No. 109', 'Duri Utara', 'Tambora', '085847531046', 'Manajemen Perkantoran dan Layanan Bisnis', 80.87, '2026-06-15 04:12:07', 63.33, 80.60, 0.00, 'file_ijazah_1782724513_514.jpg', '0108025554_tka_1781496727.jpeg', '0108025554_kk_1781496727.jpeg', '0108025554_akte_1781496727.jpeg', '3173041101100079', 'LULUS', 'Sudah', '1', NULL, '0108025554_ktpbapak_1781496727.jpeg', '0108025554_ktpibu_1781496727.jpeg', '0108025554_sptjm_1781496727.jpeg', 'Tidak', '', '', 'Bawa FC Sertifikat TKA dan Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(10, 'SPMB-SMKPB1-2026-8319', 'CHEN CUN YI', '3173041603111007', 'Kuching', '2011-03-16', '0115397193', '2628060008', 'SMP PERMATA BUNDA', 'TIDAK ADA', 'Jl. Duri Selatan I No. 52', 'Duri Selatan', 'Tambora', '085782922159', 'Akuntansi dan Keuangan Lembaga', 78.57, '2026-06-15 04:23:48', 68.33, 79.00, 0.00, 'file_ijazah_1782726338_742.jpg', 'file_tka_1782726338_518.jpg', '0115397193_kk_1781497428.jpeg', '0115397193_akte_1781497428.jpeg', '3173042504250004', 'LULUS', 'Sudah', '1', NULL, '0115397193_ktpbapak_1781497428.jpeg', '0115397193_ktpibu_1781497428.jpeg', '0115397193_sptjm_1781497428.jpeg', 'Tidak', '', '', 'Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(11, 'SPMB-SMKPB1-2026-8354', 'OKTAFIANUS', '6172021210100002', 'Jakarta', '2010-10-12', '0107636121', '2628060055', 'SMP PERMATA BUNDA', 'DARAH TINGGI', 'Krendang Timur III No. 1', 'Krendang', 'Tambora', '0895629369497', 'Manajemen Perkantoran dan Layanan Bisnis', 79.70, '2026-06-15 04:27:42', 63.34, 78.00, 0.00, 'file_ijazah_1782723883_168.jpg', '0107636121_tka_1781497662.jpeg', '0107636121_kk_1781497662.jpeg', '0107636121_akte_1781497662.jpeg', '3173042811180011', 'LULUS', 'Sudah', '1', NULL, '0107636121_ktpbapak_1781497662.jpeg', '0107636121_ktpibu_1781497662.jpeg', '0107636121_sptjm_1781497662.jpeg', 'Tidak', '', '', 'Bawa FC Sertifikat TKA dan Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(12, 'SPMB-SMKPB1-2026-4455', 'FEBRYANT WIJAYA', '6171042802110003', 'Jakarta', '2011-02-28', '0122905828', '2628060042', 'SMP PERMATA BUNDA', 'TIDAK ADA', 'Panca Krida I No. 22', 'Duri Utara', 'Tambora', '085774236972', 'Manajemen Perkantoran dan Layanan Bisnis', 76.07, '2026-06-15 04:40:15', 65.00, 75.46, 0.00, 'file_ijazah_1782725621_797.jpg', '0122905828_tka_1781498415.jpeg', '0122905828_kk_1781498415.jpeg', '0122905828_akte_1781498415.jpeg', '3173040202160021', 'LULUS', 'Sudah', '1', NULL, '0122905828_ktpbapak_1781498415.jpeg', '0122905828_ktpibu_1781498415.jpeg', '0122905828_sptjm_1781498415.jpeg', 'Ya', '30023195320', '0122905828_tabungankjp_1781498415.jpeg', 'Bawa FC Sertifikat TKA dan Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(14, 'SPMB-SMKPB1-2026-1676', 'FELIX CHRISTIAN CUNG', '3173030503111001', 'Jakarta', '2011-03-05', '0117992825', '2627930007', 'SMP CINDERA MATA INDAH', 'TIDAK ADA', 'Jl. Mangga Besar VI SLT. / 26', 'Taman Sari', 'Taman Sari', '0895911212353', 'Akuntansi dan Keuangan Lembaga', 83.17, '2026-06-15 05:10:17', 60.00, 83.20, 0.00, 'file_ijazah_1782726824_275.jpg', '0117992825_tka_1781500217.jpeg', '0117992825_kk_1781500217.jpeg', '0117992825_akte_1781500217.jpeg', '3173031201091122', 'LULUS', 'Sudah', '1', NULL, '0117992825_ktpbapak_1781500217.jpeg', '0117992825_ktpibu_1781500217.jpeg', '0117992825_sptjm_1781500217.jpeg', 'Ya', '31623076394', '0117992825_tabungankjp_1781500217.jpeg', 'Bawa FC Sertifikat TKA dan Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(15, 'SPMB-SMKPB1-2026-2478', 'ENI', '3173046203111007', 'Jakarta', '2011-03-22', '0114485472', '2628060011', 'SMP PERMATA BUNDA', 'TIDAK ADA', 'GG Balok II No. 20 A', 'Duri Utara', 'Tambora', '0895391848001', 'Akuntansi dan Keuangan Lembaga', 74.30, '2026-06-15 05:17:03', 31.66, 74.00, 0.00, 'file_ijazah_1782725908_117.jpg', 'file_tka_1782725908_444.jpg', '0114485472_kk_1781500623.jpeg', '0114485472_akte_1781500623.jpeg', '3173042406111028', 'Tidak Jadi', 'Belum', '1', 'Tidak Lolos', '0114485472_ktpbapak_1781500623.jpeg', '0114485472_ktpibu_1781500623.jpeg', '0114485472_sptjm_1781500623.jpeg', 'Ya', '30023198892', '0114485472_tabungankjp_1781500623.jpeg', 'Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(16, 'SPMB-SMKPB1-2026-4683', 'ARDIANSYAH DWI LAKSONO', '3171011404111004', 'Jakarta', '2011-04-14', '0113755309', '2627880020', 'SMP BHINEKA TUNGGAL IKA', 'TIDAK ADA', 'JLN DURI B GG IX', 'Duri Pulo', 'Gambir', '087861754482', 'Akuntansi dan Keuangan Lembaga', 80.13, '2026-06-15 07:41:39', 73.33, 78.33, 0.00, 'file_ijazah_1782726099_216.jpg', 'file_tka_1782726099_261.jpg', '0113755309_kk_1781509299.jpeg', '0113755309_akte_1781509299.jpeg', '3171012610100027', 'Tidak Jadi', 'Belum', '1', 'Lebih memilih di SMK YP IPPI PETOJO', '0113755309_ktpbapak_1781509299.jpeg', '0113755309_ktpibu_1781509299.jpeg', '0113755309_sptjm_1781509299.jpeg', 'Tidak', '', '', 'Bawa Sertifikat TKA dan FC Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(17, 'SPMB-SMKPB1-2026-7021', 'BRYAN VINCENTIUS', '3173041807111012', 'Singkawang', '2011-07-18', '0115866655', '2627890076', 'SMP BUDAYA', 'TIDAK ADA', 'Jl. Duri Baru', 'Jembatan Besi', 'Tambora', '082125570985', 'Akuntansi dan Keuangan Lembaga', 80.40, '2026-06-15 08:29:24', 66.67, 79.66, 0.00, 'file_ijazah_1782726634_627.jpg', '0115866655_tka_1781512164.jpeg', '0115866655_kk_1781512164.jpeg', '0115866655_akte_1781512164.jpeg', '3173040309121022', 'LULUS', 'Sudah', '1', NULL, '0115866655_ktpbapak_1781512164.jpeg', '0115866655_ktpibu_1781512164.jpeg', '0115866655_sptjm_1781512164.jpeg', 'Ya', '30523101997', '0115866655_tabungankjp_1781512164.jpeg', 'Bawa FC Sertifikat TKA dan Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(18, 'SPMB-SMKPB1-2026-3225', 'RIKI BONG', '6101161008080003', 'Tebas', '2008-08-10', '0081227309', '109/SMP.BDY/SKL/VI/2026', 'SMP BUDAYA', 'TIDAK ADA', 'Pekapuran Raya / 9', 'Tanah Sereal', 'Tambora', '081213032455', 'Manajemen Perkantoran dan Layanan Bisnis', 74.00, '2026-06-17 03:55:42', 46.67, 74.00, 0.00, '0081227309_ijazah_1781668542.jpeg', '0081227309_tka_1781668542.jpeg', '0081227309_kk_1781668542.jpeg', '0081227309_akte_1781668542.jpeg', '3173040410230018', 'Tidak Jadi', 'Belum', '1', 'Tidak Lolos', '0081227309_ktpbapak_1781668542.jpeg', '0081227309_ktpibu_1781668542.jpeg', '0081227309_sptjm_1781668542.jpeg', 'Tidak', '', '', 'Bawa FC Sidanira, Nilai SDN diganti dengan Nilai Rata-Rapat Rekapitulasi pada Formulir', 0, 'Sudah'),
(19, 'SPMB-SMKPB1-2026-8275', 'ELVIN FERNANDO', '3173041906111002', 'Jakarta', '2011-06-19', '0119763297', '110/SMP.BDY/SKL/VI/2026', 'SMP BUDAYA', 'TIDAK ADA', 'Jl. Tambora III GG V No. 9 D', 'Tambora', 'Tambora', '082261285848', 'Manajemen Perkantoran dan Layanan Bisnis', 74.30, '2026-06-17 04:04:47', 43.33, 74.26, 0.00, '0119763297_ijazah_1781669087.jpeg', '0119763297_tka_1781669087.jpeg', '0119763297_kk_1781669087.jpeg', '0119763297_akte_1781669087.jpeg', '3173042302170005', 'Tidak Jadi', 'Belum', '1', 'Tidak Lolos', '0119763297_ktpbapak_1781669087.jpeg', '0119763297_ktpibu_1781669087.jpeg', '0119763297_sptjm_1781669087.jpeg', 'Tidak', '', '', 'Belum ada Sidanira, Nilai SDN diganti menggunakan Rekapitulasi Rapor dari Formulir', 0, 'Sudah'),
(20, 'SPMB-SMKPB1-2026-5487', 'MARCO VINCENT', '3173042707111003', 'Jakarta', '2011-07-27', '0118156546', '2628060020', 'SMP PERMATA BUNDA', 'TIDAK ADA', 'Sawah Lio II GG V No. 3', 'Jembatan Lima', 'Tambora', '081289205573', 'Manajemen Perkantoran dan Layanan Bisnis', 81.00, '2026-06-17 08:08:56', 70.00, 79.00, 0.00, 'file_ijazah_1782722814_177.jpg', '0118156546_tka_1781683736.jpeg', '0118156546_kk_1781683736.jpeg', '0118156546_akte_1781683736.jpeg', '3173042707111003', 'LULUS', 'Sudah', '1', NULL, '0118156546_ktpbapak_1781683736.jpeg', '0118156546_ktpibu_1781683736.jpeg', '0118156546_sptjm_1781683736.jpeg', 'Ya', '30023224168', '0118156546_tabungankjp_1781683736.jpeg', 'Bawa FC Rapot Smt 1 - 5 dan TKA (Lapor Diri)', 0, 'Sudah'),
(21, 'SPMB-SMKPB1-2026-1306', 'LIONEL SAPUTRA CONG', '3173042808111003', 'Jakarta', '2011-08-28', '0119269211', '2627870050', 'SMP NEGRI 63 JAKARTA', 'tidak ada', 'Jl. Kalianyar', 'Kali Anyar', 'Tambora', '085777118549', 'Manajemen Perkantoran dan Layanan Bisnis', 85.93, '2026-06-18 03:52:13', 56.67, 85.73, 0.00, 'file_ijazah_1782725402_964.jpg', '0119269211_tka_1781754733.jpg', '0119269211_kk_1781754733.jpg', '0119269211_akte_1781754733.jpg', '3173042001094424', 'LULUS', 'Sudah', '1', NULL, '0119269211_ktpbapak_1781754733.jpg', '0119269211_ktpibu_1781754733.jpg', '0119269211_sptjm_1781754733.jpg', 'Tidak', '', '', 'Bawa FC Sertifikat TKA dan Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(22, 'SPMB-SMKPB1-2026-6986', 'KASIH AGAVE UNAS', '6205022709110001', 'Barito Utara', '2011-09-27', '0114161483', '2627920045', 'SMP Candra Naya', 'Tidak Ada', 'Jl. Rawa Bebek No.8A', 'Penjaringan', 'Penjaringan', '081281161925', 'Akuntansi dan Keuangan Lembaga', 81.91, '2026-06-18 06:56:08', 50.00, 79.00, 0.00, '0114161483_ijazah_1781765768.jpg', '0114161483_tka_1781765768.jpg', '0114161483_kk_1781765768.jpg', '0114161483_akte_1781765768.jpg', '3172012505151005', 'LULUS', 'Sudah', '1', NULL, '0114161483_ktpbapak_1781765768.jpg', '0114161483_ktpibu_1781765768.jpg', '0114161483_sptjm_1781765768.jpg', 'Ya', '1022312301', '0114161483_tabungankjp_1781765768.jpg', 'Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(23, 'SPMB-SMKPB1-2026-6292', 'CINDY CECILIA', '3173036211111004', 'Jakarta', '2011-11-22', '0114725564', '2627930004', 'SMP CINDERA MATA INDAH', 'TIDAK ADA', 'Krendang Tengah Dalam No. 85', 'Krendang', 'Tambora', '085219519899', 'Manajemen Perkantoran dan Layanan Bisnis', 83.00, '2026-06-19 04:02:32', 66.67, 83.13, 0.00, 'file_ijazah_1782723308_321.jpg', '0114725564_tka_1781841752.jpg', '0114725564_kk_1781841752.jpg', '0114725564_akte_1781841752.jpg', '3173041303200018', 'LULUS', 'Belum', '1', '', '0114725564_ktpbapak_1781841752.jpg', '0114725564_ktpibu_1781841752.jpg', '0114725564_sptjm_1781841752.jpg', 'Ya', '30023198515', '0114725564_tabungankjp_1781841752.jpg', 'Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(24, 'SPMB-SMKPB1-2026-4280', 'DOMINIQUE ANGEL LEA BOY', '3173046604111004', 'Jakarta', '2011-04-26', '0117441627', '2627870112', 'SMP NEGRI 63 JAKARTA', 'TIDAK ADA', 'KP Krendang No. 14', 'Duri Utara', 'Tambora', '081289221108', 'Manajemen Perkantoran dan Layanan Bisnis', 82.03, '2026-06-19 04:31:52', 40.00, 81.66, 0.00, 'file_ijazah_1782725200_308.jpg', 'file_tka_1782725200_487.jpg', '0117441627_kk_1781843512.jpg', '0117441627_akte_1781843512.jpg', '3173041001098910', 'LULUS', 'Sudah', '1', NULL, '0117441627_ktpbapak_1781843512.jpg', '0117441627_ktpibu_1781843512.jpg', '0117441627_sptjm_1781843512.jpg', 'Ya', '30023197764', '0117441627_tabungankjp_1781843512.jpg', 'Lengkap', 0, 'Sudah'),
(25, 'SPMB-SMKPB1-2026-1558', 'MUTIARA MERRY', '6172026412100001', 'Singkawang', '2010-12-24', '0108411094', '2628060053', 'SMP PERMATA BUNDA', 'TIDAK ADA', 'Pekapuran VII No. 14', 'Tanah Sereal', 'Tambora', '089602756381', 'Manajemen Perkantoran dan Layanan Bisnis', 79.67, '2026-06-22 03:29:26', 60.00, 78.80, 0.00, '0108411094_ijazah_1782098966.jpeg', '0108411094_tka_1782098966.jpeg', '0108411094_kk_1782098966.jpeg', '0108411094_akte_1782098966.jpeg', '3173040208220010', 'LULUS', 'Sudah', '1', NULL, '0108411094_ktpbapak_1782098966.jpeg', '0108411094_ktpibu_1782098966.jpeg', '0108411094_sptjm_1782098966.jpeg', 'Tidak', '', '', 'Scan SKL ganti dengan Sidanira, Bawa FC Rapot Smt 1 - 5 dan TKA (Lapor Diri)', 0, 'Sudah'),
(26, 'SPMB-SMKPB1-2026-2722', 'VICTORIA FELICE', '3173046890101007', 'Jakarta', '2010-09-28', '0101295537', '2628060029', 'SMP PERMATA BUNDA', 'TIDAK ADA', 'Jl. Petak Serani, Pekapuran IV No.20', 'Tanah Sereal', 'Tanbora', '081253628811', 'Manajemen Perkantoran dan Layanan Bisnis', 76.97, '2026-06-22 15:58:45', 63.33, 75.80, 0.00, 'file_ijazah_1782441711_864.jpg', 'file_tka_1782441711_439.jpg', '0101295537_kk_1782100725.jpeg', '0101295537_akte_1782100725.jpeg', '3173041301090184', 'LULUS', 'Sudah', '1', NULL, '0101295537_ktpbapak_1782100725.jpeg', '0101295537_ktpibu_1782100725.jpeg', '0101295537_sptjm_1782100725.jpeg', 'Ya', '30023025033', '0101295537_tabungankjp_1782100725.jpeg', 'Lengkap', 0, 'Sudah'),
(27, 'SPMB-SMKPB1-2026-3956', 'GLADIES OCTAVIANI', '3173036610111002', 'Jakarta', '2011-10-26', '0118100484', '2627930008', 'SMP CINDERA MATA INDAH', 'TIDAK ADA', 'Jl. Keutamaan Dalam', 'Krukut', 'Taman Sari', '089643369273', 'Manajemen Perkantoran dan Layanan Bisnis', 81.40, '2026-06-22 06:13:57', 48.33, 86.33, 0.00, '0118100484_ijazah_1782108837.jpeg', '0118100484_tka_1782108837.jpeg', '0118100484_kk_1782108837.jpeg', '0118100484_akte_1782108837.jpeg', '3173032706131008', 'LULUS', 'Sudah', '1', NULL, '0118100484_ktpbapak_1782108837.jpeg', '0118100484_ktpibu_1782108837.jpeg', '0118100484_sptjm_1782108837.jpeg', 'Ya', '30523099283', '0118100484_tabungankjp_1782108837.jpeg', 'Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(28, 'SPMB-SMKPB1-2026-5373', 'HAIKAL KHOIRUL MULYA', '3173060104101014', 'Tangerang', '2010-04-01', '0107617868', '2626670091', 'SMP NEGRI 105 JAKARTA', 'TIDAK ADA', 'KP. Cipondo', 'Semanan', 'Kalideres', '085218799115', 'Manajemen Perkantoran dan Layanan Bisnis', 83.10, '2026-06-22 06:37:31', 68.33, 82.93, 0.00, 'file_ijazah_1782723102_414.jpg', '0107617868_tka_1782110251.jpeg', '0107617868_kk_1782110251.jpeg', '0107617868_akte_1782110251.jpeg', '3173062003141064', 'LULUS', 'Sudah', '1', NULL, '0107617868_ktpbapak_1782110251.jpeg', '0107617868_ktpibu_1782110251.jpeg', '0107617868_sptjm_1782110251.jpeg', 'Ya', '31823101875', '0107617868_tabungankjp_1782110251.jpeg', 'Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(29, 'SPMB-SMKPB1-2026-9337', 'VANESSA', '3173046002111001', 'Jakarta', '2011-02-20', '0116136505', '123/SMP/YPB/VI/2026', 'SMP KRISTEN PANCARAN BERKAT', 'TIDAK ADA', 'Kalianyar IV', 'Kali Anyar', 'Tambora', '08986631985', 'Manajemen Perkantoran dan Layanan Bisnis', 77.10, '2026-06-23 01:47:20', 55.00, 75.53, 0.00, '0116136505_ijazah_1782179240.jpeg', '0116136505_tka_1782179240.jpeg', '0116136505_kk_1782179240.jpeg', '0116136505_akte_1782179240.jpeg', '3173042001095709', 'LULUS', 'Sudah', '1', NULL, '0116136505_ktpbapak_1782179240.jpeg', '0116136505_ktpibu_1782179240.jpeg', '0116136505_sptjm_1782179240.jpeg', 'Tidak', '', '', 'Lengkap (Sidanira menggunakan SKL) Nilai menggunakan SKL', 0, 'Sudah'),
(30, 'SPMB-SMKPB1-2026-5367', 'QAYLA PUTRI KHAIRUNISA', '3173036409101003', 'Jakarta', '2010-09-24', '0101652338', '2628000088', 'SMP ISLAM TAMBORA', 'Tidak Ada', 'Jl. Kp. Jawa Kb. Sayur', 'Keagungan', 'Taman Sari', '081219752700', 'Manajemen Perkantoran dan Layanan Bisnis', 86.53, '2026-06-23 12:19:14', 50.00, 84.80, 0.00, '0101652338_ijazah_1782191954.jpg', '0101652338_tka_1782191954.jpg', '0101652338_kk_1782191954.jpg', '0101652338_akte_1782191954.jpg', '3173032303160001', 'Tidak Jadi', 'Belum', '1', 'Lebih memilih di SMK YP IPPI PETOJO', '0101652338_ktpbapak_1782191954.jpg', '0101652338_ktpibu_1782191954.jpg', '0101652338_sptjm_1782191954.jpg', 'Ya', '30023189222', '0101652338_tabungankjp_1782191954.jpg', 'Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(31, 'SPMB-SMKPB1-2026-8633', 'MAHIRA ZALFA OKTOVIARA', '3173046510090004', 'Jakarta', '2009-10-25', '0098402196', '2627850020', 'SMP NEGERI 159 JAKARTA', 'TIDAK  ADA', 'Kalianyar', 'Kali Anyar', 'Tambora', '081389795645', 'Manajemen Perkantoran dan Layanan Bisnis', 82.37, '2026-06-23 06:59:42', 41.66, 81.46, 0.00, 'file_ijazah_1782783800_472.jpg', '0098402196_tka_1782197982.jpeg', '0098402196_kk_1782197982.jpeg', '0098402196_akte_1782197982.jpeg', '3173041812100107', 'LULUS', 'Sudah', '1', NULL, '0098402196_ktpbapak_1782197982.jpeg', '0098402196_ktpibu_1782197982.jpeg', '0098402196_sptjm_1782197982.jpeg', 'Tidak', '', '', 'Lengkap', 0, 'Sudah'),
(32, 'SPMB-SMKPB1-2026-6258', 'BELLA RAHMAH NAFISAH', '3173044611101001', 'Jakarta', '2010-11-06', '3104584084', '108.229/SKL/SMP-IT/V/2026', 'SMP ISLAM TAMBORA', 'TIDAK  ADA', 'Pekpuran II / 15 C', 'Tanah Sereal', 'Tambora', '087778132770', 'Manajemen Perkantoran dan Layanan Bisnis', 85.37, '2026-06-23 07:31:50', 26.67, 83.40, 0.00, 'file_ijazah_1782721722_556.jpg', '3104584084_tka_1782199910.jpeg', '3104584084_kk_1782199910.jpeg', '3104584084_akte_1782199910.jpeg', '3173042401121030', 'Tidak Jadi', 'Belum', '1', 'Tidak Lolos', '3104584084_ktpbapak_1782199910.jpeg', '3104584084_ktpibu_1782199910.jpeg', '3104584084_sptjm_1782199910.jpeg', 'Ya', '31023082476', '3104584084_tabungankjp_1782199910.jpeg', 'Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(33, 'SPMB-SMKPB1-2026-9211', 'YEMIMA SITUMEANG', '3172026110091003', 'Jakarta', '2009-10-21', '0096908327', '2625320203', 'SMP NEGERI 152 JAKARTA', 'TIDAK ADA', 'Jl. Pohon Beringin No. 58 B', 'Sunter Jaya', 'Tanjung Priok', '085175007183', 'Manajemen Perkantoran dan Layanan Bisnis', 84.07, '2026-06-24 14:04:42', 50.00, 85.06, 0.00, 'file_ijazah_1782783624_375.jpg', '0096908327_tka_1782266682.jpeg', '0096908327_kk_1782266682.jpeg', '0096908327_akte_1782266682.jpeg', '3173202220109968', 'LULUS', 'Sudah', '1', NULL, '0096908327_ktpbapak_1782266682.jpeg', '0096908327_ktpibu_1782266682.jpeg', '0096908327_sptjm_1782266682.jpeg', 'Ya', '12223592912', '0096908327_tabungankjp_1782266682.jpeg', 'Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(34, 'SPMB-SMKPB1-2026-1688', 'AHMAD RAMADHANNU', '3173043007111006', 'Jakarta', '2011-07-30', '3118670102', '083/SK/SMP-IF/V/2026', 'SMP ISLAM FATAHILLAH', 'TIDAK ADA', 'Pekapuran GG V Dalam No. 13 C', 'Tanah Sereal', 'Tambora', '088295318232', 'Manajemen Perkantoran dan Layanan Bisnis', 77.50, '2026-06-24 14:20:56', 66.66, 76.86, 0.00, '3118670102_ijazah_1782267656.jpeg', '3118670102_tka_1782267656.jpeg', '3118670102_kk_1782267656.jpeg', '3118670102_akte_1782267656.jpeg', '3173040208111019', 'Tidak Jadi', 'Belum', '1', 'Masuk SPMB Bersama', '3118670102_ktpbapak_1782267656.jpeg', '3118670102_ktpibu_1782267656.jpeg', '3118670102_sptjm_1782267656.jpeg', 'Ya', '31023082425', '3118670102_tabungankjp_1782267656.jpeg', 'SPMB / PPDB Bersama SMAS KRISTEN PANCANRAN BERKAT (Tidak Input di JakEdu)', 0, 'Sudah'),
(35, 'SPMB-SMKPB1-2026-1643', 'LAKHSMAN MOHINDER SAPUTRA', '3173040512090003', 'Jakarta', '2009-12-05', '0094306777', '2628110180', 'SMPN 54', 'TIDAK ADA', 'Jl. Gudang Areng. II No. 40', 'Tanah Sereal', 'Tambora', '089631115597', 'Manajemen Perkantoran dan Layanan Bisnis', 77.73, '2026-06-24 14:48:53', 50.00, 0.00, 0.00, '0094306777_ijazah_1782269333.jpeg', '0094306777_tka_1782269333.jpeg', '0094306777_kk_1782269333.jpeg', '0094306777_akte_1782269333.jpeg', '3173041005100112', 'LULUS', 'Sudah', '1', NULL, '0094306777_ktpbapak_1782269333.jpeg', '0094306777_ktpibu_1782269333.jpeg', '0094306777_sptjm_1782269333.jpeg', 'Ya', '31023084801', '0094306777_tabungankjp_1782269333.jpeg', 'Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(36, 'SPMB-SMKPB1-2026-8343', 'AGUS MAULANA', '3173040108101005', 'Jakarta', '2010-08-01', '3000917942', '2628000070', 'SMP ISLAM TAMBORA', 'TIDAK ADA', 'Jl. Pekapuran V / 37 A', 'Tanah Sereal', 'Tambora', '085813133891', 'Akuntansi dan Keuangan Lembaga', 84.43, '2026-06-24 07:12:20', 35.00, 82.66, 0.00, 'file_ijazah_1782783423_314.jpg', '3000917942_tka_1782285140.jpeg', '3000917942_kk_1782285140.jpeg', '3000917942_akte_1782285140.jpeg', '3173045505900004', 'LULUS', 'Sudah', '1', NULL, '3000917942_ktpbapak_1782285140.jpeg', '3000917942_ktpibu_1782285140.jpeg', '3000917942_sptjm_1782285140.jpeg', 'Ya', '31023085122', '3000917942_tabungankjp_1782285140.jpeg', 'Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(37, 'SPMB-SMKPB1-2026-7293', 'MUHAMMAD VALEN MULIAWAN', '3173041302111001', 'Jakarta', '2011-02-13', '3113318317', '2627990019', 'SMPI CHAIRIYAH MANSURIYAH', 'TIDAK ADA', 'Jln. Pekapuran II No. 18 B ', 'Tanah Sereal', 'Tambora', '081292158877', 'Akuntansi dan Keuangan Lembaga', 80.53, '2026-06-24 07:38:18', 40.00, 80.00, 0.00, 'file_ijazah_1782787732_691.jpg', '3113318317_tka_1782286698.jpeg', '3113318317_kk_1782286698.jpeg', '3113318317_akte_1782286698.jpeg', '3173041201096937', 'LULUS', 'Sudah', '1', '', '3113318317_ktpbapak_1782286698.jpeg', '3113318317_ktpibu_1782286698.jpeg', '3113318317_sptjm_1782286698.jpeg', 'Ya', '31023082611', '3113318317_tabungankjp_1782286698.jpeg', 'Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(38, 'SPMB-SMKPB1-2026-8728', 'BRANDON IMANUEL SUSETIA', '3173044204141002', 'Jakarta', '2010-12-28', '0111994287', '2627890075', 'SMP BUDAYA', 'TIDAK ADA', 'Jl. Tanah Sereal XVIII No.4', 'Tanah Sereal', 'Tambora', '085178530388', 'Akuntansi dan Keuangan Lembaga', 74.63, '2026-06-25 07:50:52', 53.33, 75.20, 0.00, 'file_ijazah_1782440704_540.jpg', 'file_tka_1782440704_162.jpg', '0111994287_kk_1782373852.jpeg', '0111994287_akte_1782373852.jpeg', '3173042306111027', 'LULUS', 'Sudah', '1', NULL, '0111994287_ktpbapak_1782373852.jpeg', '0111994287_ktpibu_1782373852.jpeg', '0111994287_sptjm_1782373852.jpeg', 'Tidak', '', '', 'FC Rapot smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(39, 'SPMB-SMKPB1-2026-3298', 'EVANS SUPRIYANTO', '3173032606101002', 'Jakarta', '2010-06-26', '0108866603', '2627990040', 'SMPI CHAIRIYAH MANSURIYAH', 'TIDAK ADA', 'JL. Keamanan No 84 rt 13/ rw 003', 'Keagungan', 'Taman Sari', '0838775014000', 'Akuntansi dan Keuangan Lembaga', 81.27, '2026-06-26 08:38:00', 53.33, 81.26, 0.00, 'file_ijazah_1782783959_683.jpg', '0108866603_tka_1782437880.jpeg', '0108866603_kk_1782437880.jpeg', '0108866603_akte_1782437880.jpeg', '3173031507240007', 'LULUS', 'Sudah', '1', NULL, '0108866603_ktpbapak_1782437880.jpeg', '0108866603_ktpibu_1782437880.jpeg', '0108866603_sptjm_1782437880.jpeg', 'Ya', '180201022539501', '0108866603_tabungankjp_1782437880.jpeg', 'FC Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(40, 'SPMB-SMKPB1-2026-3101', 'KRISTIN FRETY SIREGAR', '3173044912101010', 'Jakarta', '2010-12-09', '0104638460', '2627930010', 'SMPI Cindera Mata Indah', 'TIDAK ADA', 'JL. Songsi Dalam no 12 tanah sereal', 'Tanah Sereal', 'Tambora', '081221694488', 'Akuntansi dan Keuangan Lembaga', 80.03, '2026-06-26 11:19:10', 51.66, 79.80, 0.00, 'file_ijazah_1782784662_641.jpg', '0104638460_tka_1782447550.jpeg', '0104638460_kk_1782447550.jpeg', '0104638460_akte_1782447550.jpeg', '3173040211101044', 'LULUS', 'Sudah', '1', NULL, '0104638460_ktpbapak_1782447550.jpeg', '0104638460_ktpibu_1782447550.jpeg', '0104638460_sptjm_1782447550.jpeg', 'Tidak', '', '', 'Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(41, 'SPMB-SMKPB1-2026-8099', 'WAHYU RIYANTO', '3173040611101004', 'Jakarta', '2010-11-05', '0107427798', '400.3.11.1/334/2026', 'SMP NEGRI 1 SELOMERTO', 'TIDAK ADA', 'KP DURI DALAM RT 001 RW 005 NO 40', 'Duri Selatan', 'Tambora', '089613346069', 'Akuntansi dan Keuangan Lembaga', 80.37, '2026-06-26 11:48:56', 46.67, 80.13, 0.00, '0107427798_ijazah_1782449336.jpeg', '0107427798_tka_1782449336.jpeg', '0107427798_kk_1782449336.jpeg', '0107427798_akte_1782449336.jpeg', '3173043012101051', 'LULUS', 'Sudah', '1', NULL, '0107427798_ktpbapak_1782449336.jpeg', '0107427798_ktpibu_1782449336.jpeg', '0107427798_sptjm_1782449336.jpeg', 'Tidak', '', '', 'Tidak ada Sidanira. Nilai Menggunakan Rapot', 0, 'Sudah'),
(42, 'SPMB-SMKPB1-2026-9520', 'DARVIN SETIAWAN', '3171010111101002', 'Jakarta', '2010-11-01', '0109604571', '124202600569278', 'SKB 24', 'TIDAK ADA', 'KP DURI BARAT NO 19 RT/RW 007/008', 'Duri Pulo', 'Gambir', '081287718536', 'Akuntansi dan Keuangan Lembaga', 71.13, '2026-06-26 13:29:54', 46.66, 71.00, 0.00, '0109604571_ijazah_1782455394.jpeg', '0109604571_tka_1782455394.jpeg', '0109604571_kk_1782455394.jpeg', '0109604571_akte_1782455394.jpeg', '3171012708190004', 'Tidak Jadi', 'Belum', '1', 'Tidak Lolos', '0109604571_ktpbapak_1782455394.jpeg', '0109604571_ktpibu_1782455394.jpeg', '0109604571_sptjm_1782455394.jpeg', 'Tidak', '', '', 'Bawa FC KK dan TKA (Lapor Diri)\r\nADA KJP TAPI TIDAK INPUT', 0, 'Sudah'),
(43, 'SPMB-SMKPB1-2026-8303', 'AILEEN JOICE KANE SYLPH MILLIONAIRE', '3173046306111008', 'Jakarta', '2011-06-23', '0118483281', '2628040001', 'SMP KEMULIAAN BUNDA', 'Tidak Ada', 'Jl. Terate GG. IV No. 23', 'Jembatan Lima', 'Tambora', '085693794757', 'Manajemen Perkantoran dan Layanan Bisnis', 83.50, '2026-06-26 14:08:48', 68.34, 0.00, 0.00, '0118483281_ijazah_1782457728.jpg', '0118483281_tka_1782457728.jpg', '0118483281_kk_1782457728.jpg', '0118483281_akte_1782457728.jpg', '3173041904111030', 'LULUS', 'Belum', '1', '', '0118483281_ktpbapak_1782457728.jpg', '0118483281_ktpibu_1782457728.jpg', '0118483281_sptjm_1782457728.jpg', 'Ya', '30523123320', '0118483281_tabungankjp_1782457728.jpg', 'Bawa FC Rapot smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(44, 'SPMB-SMKPB1-2026-3734', 'PUTRI AYU RAHMAWATI', '3173035601111002', 'Jakarta', '2011-01-16', '0111057233', '2627870093', 'SMPN 63', 'Tidak Ada', 'Jl. Kesederhanaan', 'Keagungan', 'Taman Sari', '085693794757', 'Manajemen Perkantoran dan Layanan Bisnis', 82.97, '2026-06-26 14:20:17', 53.33, 0.00, 0.00, '0111057233_ijazah_1782458417.jpg', '0111057233_tka_1782458417.jpg', '0111057233_kk_1782458417.jpg', '0111057233_akte_1782458417.jpg', '3173031111100058', 'LULUS', 'Sudah', '1', NULL, '0111057233_ktpbapak_1782458417.jpg', '0111057233_ktpibu_1782458417.jpg', '0111057233_sptjm_1782458417.jpg', 'Tidak', '', '', 'Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(45, 'SPMB-SMKPB1-2026-1148', 'NATASHA', '3173046412101004', 'Jakarta', '2010-12-24', '0106035992', '2627900012', 'SMP BUDI BAHASA', 'Tidak Ada', 'GG Mesjid I Dalam No. 27', 'Angke', 'Tambora', '087785595159', 'Manajemen Perkantoran dan Layanan Bisnis', 74.30, '2026-06-26 14:29:56', 55.00, 0.00, 0.00, '0106035992_ijazah_1782458996.jpg', '0106035992_tka_1782458996.jpg', '0106035992_kk_1782458996.jpg', '0106035992_akte_1782458996.jpg', '3173042711150012', 'LULUS', 'Sudah', '1', NULL, '0106035992_ktpbapak_1782458996.jpg', '0106035992_ktpibu_1782458996.jpg', '0106035992_sptjm_1782458996.jpg', 'Tidak', '', '', 'Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(46, 'SPMB-SMKPB1-2026-7822', 'SOVITALIA', '3603206809100001', 'Tangerang', '2010-09-28', '0101092205', '055 PB', 'SMP PERMATA BUNDA', 'TIDAK ADA', 'KP DURI DALAM RT/RW 008/005', 'Duri Selatan', 'Tambora', '08212100157', 'Manajemen Perkantoran dan Layanan Bisnis', 77.57, '2026-06-29 07:54:55', 55.00, 0.00, 0.00, '0101092205_ijazah_1782694495.jpeg', '0101092205_tka_1782694495.jpeg', '0101092205_kk_1782694495.jpeg', '0101092205_akte_1782694495.jpeg', '3173042105260007', 'Tidak Jadi', 'Belum', '1', 'KK Tangerang', '0101092205_ktpbapak_1782694495.jpeg', '0101092205_ktpibu_1782694495.jpeg', '0101092205_sptjm_1782694495.jpeg', 'Tidak', '', '', 'KK Tangerang Tidak input di jakedu', 0, 'Sudah'),
(47, 'SPMB-SMKPB1-2026-5663', 'CHARLES ALESIO', '6172021310100002', 'Singkawang', '2010-10-13', '0104628907', '2627890079', 'SMP BUDAYA', 'TIDAK ADA', 'JL. Jelambar Barat 2 G No 460 A', 'Jelambar Baru', 'Grogol Petamburan', '0895347203000', 'Akuntansi dan Keuangan Lembaga', 82.97, '2026-06-29 10:17:44', 65.00, 0.00, 0.00, '0104628907_ijazah_1782703064.jpeg', '0104628907_tka_1782703064.jpeg', '0104628907_kk_1782703064.jpeg', '0104628907_akte_1782703064.jpeg', '3173020512190018', 'LULUS', 'Sudah', '1', NULL, '0104628907_ktpbapak_1782703064.jpeg', '0104628907_ktpibu_1782703064.jpeg', '0104628907_sptjm_1782703064.jpeg', 'Tidak', '', '', 'Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(48, 'SPMB-SMKPB1-2026-3779', 'AKBAR DWI TAMA', '3173012806101006', 'Jakarta', '2010-06-28', '0108680620', '2627810073', 'SMP TANJUNG', 'TIDAK ADA', 'Krendang Selatan 1 No. 1', 'Krendang', 'Tambora', '081399023903', 'Akuntansi dan Keuangan Lembaga', 80.47, '2026-06-29 10:40:14', 45.00, 0.00, 0.00, 'file_ijazah_1782785899_722.jpg', '0108680620_tka_1782704414.jpeg', '0108680620_kk_1782704414.jpeg', '0108680620_akte_1782704414.jpeg', '3171040812210007', 'LULUS', 'Sudah', '1', NULL, '0108680620_ktpbapak_1782704414.jpeg', '0108680620_ktpibu_1782704414.jpeg', '0108680620_sptjm_1782704414.jpeg', 'Tidak', '', '', 'Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(49, 'SPMB-SMKPB1-2026-6455', 'GIZELLE  GERALDINE CIPTADI', '3671085808090006', 'Jakarta', '2010-06-28', '0092924985', '2635590009', 'PKBM SOLUSI MANDIRI SENTOSA', 'TIDAK ADA', 'JL. PELOPOR  RTO4 RW 15', 'Tegal Alur', 'Kalideres', '082246295526', 'Akuntansi dan Keuangan Lembaga', 81.00, '2026-06-29 12:09:18', 58.33, 0.00, 0.00, '0092924985_ijazah_1782709758.pdf', '0092924985_tka_1782709758.jpeg', '0092924985_kk_1782709758.jpeg', '0092924985_akte_1782709758.jpeg', '3172042811160003', 'LULUS', 'Sudah', '1', NULL, '0092924985_ktpbapak_1782709758.jpeg', '0092924985_ktpibu_1782709758.jpeg', '0092924985_sptjm_1782709758.jpeg', 'Tidak', '', '', 'Bawa FC Rapot Smt 1 - 5 (Lengkap)', 0, 'Sudah'),
(50, 'SPMB-SMKPB1-2026-1531', 'NICHOLAS ANDRIANO', '3173042508111006', 'Jakarta', '2011-08-25', '0114832715', '2624700016', 'SMP ST PAULUS', 'TIDAK ADA', 'KP. KRENDANG PULO NO 10 D', 'Duri Utara', 'Tambora', '081318545376', 'Akuntansi dan Keuangan Lembaga', 77.57, '2026-06-29 12:22:04', 70.00, 0.00, 0.00, '0114832715_ijazah_1782710524.jpeg', '0114832715_tka_1782710524.jpeg', '0114832715_kk_1782710524.jpeg', '0114832715_akte_1782710524.jpeg', '3173041203141030', 'LULUS', 'Sudah', '1', NULL, '0114832715_ktpbapak_1782710524.jpeg', '0114832715_ktpibu_1782710524.jpeg', '0114832715_sptjm_1782710524.jpeg', 'Tidak', '', '', 'Bawa FC Rapot Smt 1 - 5 (Lengkap)', 0, 'Sudah'),
(51, 'SPMB-SMKPB1-2026-2628', 'GRACE EMMANUELL', '3173045008111007', 'Jakarta', '2011-08-10', '0113006724', '2627870116', 'SMP NEGERI 63 JAKARTA', 'TIDAK ADA', 'JL. TIANG BENDERA VI/42', 'Roa Malaka', 'Tambora', '085714196968', 'Akuntansi dan Keuangan Lembaga', 81.10, '2026-06-29 13:35:31', 55.00, 0.00, 0.00, '0113006724_ijazah_1782714931.jpeg', '0113006724_tka_1782714931.jpeg', '0113006724_kk_1782714931.jpeg', '0113006724_akte_1782714931.jpeg', '3173040707111006', 'LULUS', 'Sudah', '1', NULL, '0113006724_ktpbapak_1782714931.jpeg', '0113006724_ktpibu_1782714931.jpeg', '0113006724_sptjm_1782714931.jpeg', 'Tidak', '', '', 'Bawa FC Rapot Amt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(52, 'SPMB-SMKPB1-2026-4900', 'SURYA RIDHO ALIF PRATAMA', '3173032705111005', 'Jakarta', '2011-08-10', '0111346539', '2628100096', 'SMP NEGERI 22 JAKARTA', 'TIDAK ADA', 'JL. LADA DALAM', 'Pinangsia', 'Taman Sari', '088212458434', 'Akuntansi dan Keuangan Lembaga', 77.80, '2026-06-29 13:48:46', 40.00, 0.00, 0.00, '0111346539_ijazah_1782715726.jpeg', '0111346539_tka_1782715726.jpeg', '0111346539_kk_1782715726.jpeg', '0111346539_akte_1782715726.jpeg', '3173032211101001', 'Tidak Jadi', 'Belum', '1', 'Tidak Lolos', '0111346539_ktpbapak_1782715726.jpeg', '0111346539_ktpibu_1782715726.jpeg', '0111346539_sptjm_1782715726.jpeg', 'Tidak', '', '', NULL, 0, 'Sudah'),
(53, 'SPMB-SMKPB1-2026-6303', 'VIONA FLORENSIA', '3173044810111004', 'Jakarta', '2011-10-08', '0117921164', '2628080052', 'SMP SINAR DHARMA', 'TIDAK ADA', 'KP. KRENDANG NO 19', 'Duri Utara', 'Tambora', '083167425973', 'Akuntansi dan Keuangan Lembaga', 75.90, '2026-06-29 14:02:02', 66.66, 0.00, 0.00, '0117921164_ijazah_1782716522.jpeg', '0117921164_tka_1782716522.jpeg', '0117921164_kk_1782716522.jpeg', '0117921164_akte_1782716522.jpeg', '3173040710111041', 'LULUS', 'Sudah', '1', NULL, '0117921164_ktpbapak_1782716522.jpeg', '0117921164_ktpibu_1782716522.jpeg', '0117921164_sptjm_1782716522.jpeg', 'Tidak', '', '', 'Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(54, 'SPMB-SMKPB1-2026-7529', 'JOANA MAGDALENA', '3171034905101003', 'Jakarta', '2010-05-09', '0104258327', '2624260052', 'SMP NEGERI 59 JAKARTA', 'TIDAK ADA', 'JL. SUNTER JAYA II A NO 53', 'Sunter Jaya', 'Tanjung Priok', '081382523992', 'Manajemen Perkantoran dan Layanan Bisnis', 79.97, '2026-06-29 14:50:37', 40.00, 0.00, 0.00, '0104258327_ijazah_1782719437.jpeg', '0104258327_tka_1782719437.jpeg', '0104258327_kk_1782719437.jpeg', '0104258327_akte_1782719437.jpeg', '3172022401180018', 'LULUS', 'Sudah', '1', '', '0104258327_ktpbapak_1782719437.jpeg', '0104258327_ktpibu_1782719437.jpeg', '0104258327_sptjm_1782719437.jpeg', 'Tidak', '', '', 'Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(55, 'SPMB-SMKPB1-2026-9828', 'DIANA WIJAYA', '3173046008111006', 'Jakarta', '2011-08-20', '0106707881', '2624910011', 'SMP GENESARET', 'TIDAK ADA', 'jl. pekojan III KP JANIS NO 38', 'Pekojan', 'Tambora', '085697770137', 'Akuntansi dan Keuangan Lembaga', 71.49, '2026-06-29 15:49:20', 55.00, 0.00, 0.00, '0106707881_ijazah_1782722960.jpeg', '0106707881_tka_1782722960.jpeg', '0106707881_kk_1782722960.jpeg', '0106707881_akte_1782722960.jpeg', '3173040501095082', 'LULUS', 'Sudah', '1', NULL, '0106707881_ktpbapak_1782722960.jpeg', '0106707881_ktpibu_1782722960.jpeg', '0106707881_sptjm_1782722960.jpeg', 'Ya', '30523102225', '0106707881_tabungankjp_1782722960.jpeg', '', 0, 'Sudah'),
(56, 'SPMB-SMKPB1-2026-4543', 'CARREN CARISSA', '3173046812101002', 'Jakarta', '2010-12-28', '0105491407', '2627890078', 'SMP BUDAYA', 'TIDAK ADA', 'KP KRENDANG PULO III NO 26', 'Duri Utara', 'Tambora', '082298663311', 'Akuntansi dan Keuangan Lembaga', 74.43, '2026-06-30 08:16:59', 53.33, 0.00, 0.00, '0105491407_ijazah_1782782219.jpeg', '0105491407_tka_1782782219.jpeg', '0105491407_kk_1782782219.jpeg', '0105491407_akte_1782782219.jpeg', '3173041001098596', 'LULUS', 'Sudah', '1', NULL, '0105491407_ktpbapak_1782782219.jpeg', '0105491407_ktpibu_1782782219.jpeg', '0105491407_sptjm_1782782219.jpeg', 'Ya', '30023197730', '0105491407_tabungankjp_1782782219.jpeg', 'Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(57, 'SPMB-SMKPB1-2026-9990', 'KRISTIN', '3173046212091005', 'Jakarta', '2009-12-22', '0094858605', '2627870122', 'SMP NEGERI 63 JAKARTA', 'TIDAK ADA', 'GG SIAGA II NO 25', 'Angke', 'Tambora', '085695962983', 'Akuntansi dan Keuangan Lembaga', 83.60, '2026-06-30 08:43:38', 53.33, 0.00, 0.00, '0094858605_ijazah_1782783818.jpeg', '0094858605_tka_1782783818.jpeg', '0094858605_kk_1782783818.jpeg', '0094858605_akte_1782783818.jpeg', '3173042801100049', 'LULUS', 'Sudah', '1', NULL, '0094858605_ktpbapak_1782783818.jpeg', '0094858605_ktpibu_1782783818.jpeg', '0094858605_sptjm_1782783818.jpeg', 'Ya', '30023548559', '0094858605_tabungankjp_1782783818.jpeg', 'Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(58, 'SPMB-SMKPB1-2026-4359', 'RIADY GIDEONY PARDEDE', '3173010906101002', 'Jakarta', '2010-06-09', '0108179510', '2628250029', 'SMP NEGERI 132 JAKARTA', 'TIDAK ADA', 'JL. TAWANG MANGU KALIMATI RT 2 RW 6', 'Kedaung Kali Angke', 'Cengkareng', '081246279516', 'Manajemen Perkantoran dan Layanan Bisnis', 84.27, '2026-06-30 08:54:00', 56.66, 0.00, 0.00, '0108179510_ijazah_1782784440.jpeg', '0108179510_tka_1782784440.jpeg', '0108179510_kk_1782784440.jpeg', '0108179510_akte_1782784440.jpeg', '3173011301220027', 'LULUS', 'Sudah', '1', NULL, '0108179510_ktpbapak_1782784440.jpeg', '0108179510_ktpibu_1782784440.jpeg', '0108179510_sptjm_1782784440.jpeg', 'Ya', '31523193546', '0108179510_tabungankjp_1782784440.jpeg', 'Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(59, 'SPMB-SMKPB1-2026-9018', 'HENOKH HASIHOLAN SIREGAR', '3174060209101007', 'Jakarta', '2010-09-02', '0105379371', '9606151', 'SMP NEGERI 212  JAKARTA', 'TIDAK ADA', 'JL. kaimun jaya IV/34', 'Cilandak Barat', 'Cilandak', '08986504860', 'Manajemen Perkantoran dan Layanan Bisnis', 85.03, '2026-06-30 09:12:24', 58.33, 0.00, 0.00, '0105379371_ijazah_1782785544.jpeg', '0105379371_tka_1782785544.jpeg', '0105379371_kk_1782785544.jpeg', '0105379371_akte_1782785544.jpeg', '3174061706100012', 'LULUS', 'Sudah', '1', NULL, '0105379371_ktpbapak_1782785544.jpeg', '0105379371_ktpibu_1782785544.jpeg', '0105379371_sptjm_1782785544.jpeg', 'Tidak', '', '', 'Bawa FC Sidanira dan Rapot Smt 1 - 5 (Lapor Diri), Nilai menggunakan Formulir', 0, 'Sudah'),
(60, 'SPMB-SMKPB1-2026-9715', 'INDRI VIKA SALAMAH', '3173035403101004', 'Jakarta', '2010-03-14', '0103411211', '2635330019', 'PKBM AL LATHIEF', 'TIDAK ADA', 'jl. ln lada dalam rt 5/6', 'Pinangsia', 'Taman Sari', '08812551489', 'Akuntansi dan Keuangan Lembaga', 87.07, '2026-06-30 09:52:12', 30.00, 0.00, 0.00, '0103411211_ijazah_1782787932.jpeg', '0103411211_tka_1782787932.jpeg', '0103411211_kk_1782787932.jpeg', '0103411211_akte_1782787932.jpeg', '3173030611151002', 'LULUS', 'Sudah', '1', NULL, '0103411211_ktpbapak_1782787932.jpeg', '0103411211_ktpibu_1782787932.jpeg', '0103411211_sptjm_1782787932.jpeg', 'Ya', '30023545801', '0103411211_tabungankjp_1782787932.jpeg', NULL, 0, 'Sudah'),
(61, 'SPMB-SMKPB1-2026-4416', 'YUSHRAN HANIF KHAERULLAH', '3173042112101005', 'Jakarta', '2010-12-21', '0108988482', '2624720192', 'SMP YP IPPI PETOJO', 'TIDAK ADA', 'Jl.TERATE VII RT 3 RW 4', 'Jembatan Lima', 'Tambora', '085777257232', 'Akuntansi dan Keuangan Lembaga', 76.93, '2026-06-30 11:12:26', 43.33, 0.00, 0.00, '0108988482_ijazah_1782792746.jpeg', '0108988482_tka_1782792746.jpeg', '0108988482_kk_1782792746.jpeg', '0108988482_akte_1782792746.jpeg', '3173041709101003', 'Tidak Jadi', 'Belum', '1', 'Tidak Lolos', '0108988482_ktpbapak_1782792746.jpeg', '0108988482_ktpibu_1782792746.jpeg', '0108988482_sptjm_1782792746.jpeg', 'Ya', '15728008677', '0108988482_tabungankjp_1782792746.jpeg', 'Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(62, 'SPMB-SMKPB1-2026-5974', 'PUTRI CANTIKA MAHARANI', '3173045601111004', 'Jakarta', '2011-01-16', '0113688607', '2627810033', 'SMP TANJUNG', 'TIDAK ADA', 'RUSUNAWA TAMBORA 1 TWR A LT 12/4', 'Angke', 'Tambora', '085888644903', 'Akuntansi dan Keuangan Lembaga', 82.80, '2026-06-30 11:31:31', 51.67, 0.00, 0.00, '0113688607_ijazah_1782793891.jpeg', '0113688607_tka_1782793891.jpeg', '0113688607_kk_1782793891.jpeg', '0113688607_akte_1782793891.jpeg', '3173041504131021', 'LULUS', 'Sudah', '1', NULL, '0113688607_ktpbapak_1782793891.jpeg', '0113688607_ktpibu_1782793891.jpeg', '0113688607_sptjm_1782793891.jpeg', 'Tidak', '', '', 'Bawa FC Sertifikat TKA dan Rapot Smt 1 - 5 (Lapor Diri)', 0, 'Sudah'),
(63, 'SPMB-SMKPB1-2026-8695', 'ALBIANT RIZKILAH PRATANA', '3173070404101009', 'Jakarta', '2010-04-04', '0106652809', '2627440006', 'SMPN 88', 'Tidak Ada', 'GG Kiapang No. 24', 'Kota Bambu Selatan', 'Palmerah', '08812551489', 'Akuntansi dan Keuangan Lembaga', 84.70, '2026-06-30 12:57:00', 50.00, 0.00, 0.00, '0106652809_ijazah_1782799020.jpg', '0106652809_tka_1782799020.jpg', '0106652809_kk_1782799020.jpg', '0106652809_akte_1782799020.jpg', '3173071601096304', 'Tidak Jadi', 'Belum', '1', 'KK > 15 Juni 2025', '0106652809_ktpbapak_1782799020.jpg', '0106652809_ktpibu_1782799020.jpg', '0106652809_sptjm_1782799020.jpg', 'Ya', '11523009971', '0106652809_tabungankjp_1782799020.jpg', 'KK > 15 Juni 2026 Tidak di input di jak edu (Tidak Ada Kabar)', 0, 'Sudah'),
(64, 'SPMB-SMKPB1-2026-8899', 'ASHLEY ZOE', '3173027003111002', 'Jakarta', '2011-05-30', '0112398380', '2627670033', 'SMP HATI KUDUS', 'Tidak Ada', 'KP. BALI GG MASJID', 'Wijaya Kusuma', 'Grogol Petamburan', '081324426203', 'Akuntansi dan Keuangan Lembaga', 79.10, '2026-06-30 13:04:23', 63.33, 0.00, 0.00, '0112398380_ijazah_1782799463.jpg', '0112398380_tka_1782799463.jpg', '0112398380_kk_1782799463.jpg', '0112398380_akte_1782799463.jpg', '3173021111100036', 'LULUS', 'Sudah', '1', NULL, '0112398380_ktpbapak_1782799463.jpg', '0112398380_ktpibu_1782799463.jpg', '0112398380_sptjm_1782799463.jpg', 'Tidak', '', '', '', 0, 'Sudah'),
(65, 'SPMB-SMKPB1-2026-1657', 'YOGA CHRISTIAN HALIM', '6171040201090001', 'Yogyakarta', '2009-01-02', '0093341979', '2627910008', 'SMP CANDRA JAYA', 'Tidak Ada', 'JL. DURI BARU NO. 10A', 'Jembatan Besi', 'Tambora', '081284630435', 'Akuntansi dan Keuangan Lembaga', 81.83, '2026-06-30 13:10:57', 36.67, 0.00, 0.00, '0000000000_ijazah_1782799857.jpeg', '0000000000_tka_1782799857.jpeg', '0000000000_kk_1782799857.jpeg', '0000000000_akte_1782799857.jpeg', '3173041812200009', 'LULUS', 'Sudah', '1', NULL, '0000000000_ktpbapak_1782799857.jpeg', '0000000000_ktpibu_1782799857.jpeg', '0000000000_sptjm_1782799857.jpeg', 'Tidak', '', '', '', 0, 'Sudah'),
(66, 'SPMB-SMKPB1-2026-6551', 'HANI FATMAWARNI', '3173036209101003', 'Jakarta', '2010-09-22', '0104986410', '2628170004', 'SMP MELANIA I', 'Tidak Ada', 'Jl. Taman Sari III No. 24', 'Taman Sari', 'Maphar', '081519738700', 'Akuntansi dan Keuangan Lembaga', 82.40, '2026-06-30 14:19:25', 61.67, 0.00, 0.00, '0104986410_ijazah_1782803965.jpg', '0104986410_tka_1782803965.jpg', '0104986410_kk_1782803965.jpg', '0104986410_akte_1782803965.jpg', '3173030803111021', 'LULUS', 'Belum', '1', '', '0104986410_ktpbapak_1782803965.jpg', '0104986410_ktpibu_1782803965.jpg', '0104986410_sptjm_1782803965.jpg', 'Tidak', '', '', '', 0, 'Sudah'),
(68, 'SPMB-SMKPB1-2026-2463', 'HEINDRA BONG', '3173041005111002', 'Jakarta', '2011-05-10', '0112069387', '2627560187', 'SMP NEGRI 82 JAKARTA', 'Tidak Ada', 'JL SETIA JAYA 11 NO 47. RT 04/08 JELAMBAR BARU', 'Jelambar Baru', 'Grogol Petamburan', '081218550320', 'Akuntansi dan Keuangan Lembaga', 85.43, '2026-07-08 08:33:24', 70.00, 0.00, 0.00, '0112069387_ijazah_1783474404.jpeg', '0112069387_tka_1783474404.jpeg', '0112069387_kk_1783474404.jpeg', '0112069387_akte_1783474404.jpeg', '3173021005230008', 'LULUS', 'Belum', '2', NULL, '0112069387_ktpbapak_1783474404.jpeg', '0112069387_ktpibu_1783474404.jpeg', '0112069387_sptjm_1783474404.jpeg', 'Tidak', '', '', NULL, 1, 'Sudah'),
(69, 'SPMB-SMKPB1-2026-8992', 'LILIS CAROLINE PETRONIS', '3173036806101004', 'Jakarta', '2010-06-28', '0102395705', '2624640015', 'SMP AL-IRSYAD AL-ISLAMIYAH', 'Tidak Ada', 'JL THALIB II DALAM RT 013/005', 'Krukut', 'Taman Sari', '083159492558', 'Manajemen Perkantoran dan Layanan Bisnis', 79.30, '2026-07-08 09:53:05', 40.00, 0.00, 0.00, '0102395705_ijazah_1783479185.jpeg', '0102395705_tka_1783479185.jpeg', '0102395705_kk_1783479185.jpeg', '0102395705_akte_1783479185.jpeg', '3173031601121009', 'LULUS', 'Belum', '2', NULL, '0102395705_ktpbapak_1783479185.jpeg', '0102395705_ktpibu_1783479185.jpeg', '0102395705_sptjm_1783479185.jpeg', 'Ya', '01223122300', '0102395705_tabungankjp_1783479185.jpeg', NULL, 1, 'Sudah'),
(70, 'SPMB-SMKPB1-2026-4460', 'AZRIL ZIDAN ALPIANSYAH', '3173042005101003', 'Jakarta', '2010-05-20', '0108438031', '20311070', 'SMP NEGRI 3 GIRIMARTO', 'Tidak Ada', 'JL KAMPUNG DURI DALAM 02/05 NO 16', 'Duri Selatan', 'Tambora', '085771751140', 'Akuntansi dan Keuangan Lembaga', 83.23, '2026-07-08 10:46:29', 55.00, 0.00, 0.00, '0108438031_ijazah_1783482389.jpeg', 'file_tka_1783484406_688.jpeg', '0108438031_kk_1783482389.jpeg', '0108438031_akte_1783482389.jpeg', '3173042101093298', 'LULUS', 'Belum', '2', NULL, '0108438031_ktpbapak_1783482389.jpeg', '0108438031_ktpibu_1783482389.jpeg', '0108438031_sptjm_1783482389.jpeg', 'Tidak', '', '', 'Nilai menggunakan rapot', 1, 'Sudah'),
(71, 'SPMB-SMKPB1-2026-2481', 'ASHLAN ROHMANESTI', '3172012905101004', 'Jakarta', '2010-05-29', '0109907607', '2624770245', 'SMP NEGRI 21 JAKARTA', 'Tidak Ada', 'KP BARU KB KOJA', 'Penjaringan', 'Penjaringan', '0857713181888', 'Akuntansi dan Keuangan Lembaga', 80.77, '2026-07-08 11:00:26', 60.00, 0.00, 0.00, '0109907607_ijazah_1783483226.jpeg', '0109907607_tka_1783483226.jpeg', '0109907607_kk_1783483226.jpeg', '0109907607_akte_1783483226.jpeg', '3172011109230004', 'LULUS', 'Belum', '2', NULL, '0109907607_ktpbapak_1783483226.jpeg', '0109907607_ktpibu_1783483226.jpeg', '0109907607_sptjm_1783483226.jpeg', 'Tidak', '', '', '', 1, 'Sudah'),
(72, 'SPMB-SMKPB1-2026-4759', 'MUHAMMAD RIZKY APRILIANO', '3328152604110002', 'Tegal', '2011-04-26', '0113749599', '2627850194', 'SMP NEGRI 159 JAKARTA', 'Tidak Ada', 'JL JEMBATAN 2 BARAT', 'Angke', 'Tambora', '085215485025', 'Manajemen Perkantoran dan Layanan Bisnis', 81.93, '2026-07-08 11:36:17', 50.00, 0.00, 0.00, '0113749599_ijazah_1783485377.jpeg', '0113749599_tka_1783485377.jpeg', '0113749599_kk_1783485377.jpeg', '0113749599_akte_1783485377.jpeg', '3173041011141011', 'Tidak Jadi', 'Belum', '2', 'Lebih memilih SMKS AL MUTTAQIN', '0113749599_ktpbapak_1783485377.jpeg', '0113749599_ktpibu_1783485377.jpeg', '0113749599_sptjm_1783485377.jpeg', 'Tidak', '', '', '', 1, 'Sudah');
INSERT INTO `pendaftar` (`id`, `no_pendaftaran`, `nama_lengkap`, `nik`, `tempat_lahir`, `tanggal_lahir`, `nisn`, `no_ijazah`, `asal_sekolah`, `riwayat_penyakit`, `alamat`, `kelurahan`, `kecamatan`, `no_whatsapp`, `pilihan_jurusan`, `nilai_skl`, `tanggal_daftar`, `nilai_tka`, `nilai_test`, `nilai_berkas`, `file_ijazah`, `file_tka`, `file_kk`, `file_akte`, `no_kk`, `status_konfirmasi`, `status_daftar_ulang`, `gelombang`, `alasan_pembatalan`, `file_ktp_bapak`, `file_ktp_ibu`, `file_sptjm`, `status_kjp`, `no_rek_kjp`, `file_tabungan_kjp`, `catatan_panitia`, `is_detail_filled`, `status_jakedu`) VALUES
(73, 'SPMB-SMKPB1-2026-3928', 'FITRI OKTAVIANI', '3173024110091005', 'Jakarta', '2009-10-01', '0094701407', '2627570147', 'SMP NEGRI 83 JAKARTA', 'Tidak Ada', 'KP DURI BARAT RT09/RW08', 'Grogol', 'Grogol Petamburan', '088294872499', 'Manajemen Perkantoran dan Layanan Bisnis', 81.13, '2026-07-08 11:46:02', 55.00, 0.00, 0.00, '0094701407_ijazah_1783485962.jpeg', '0094701407_tka_1783485962.jpeg', '0094701407_kk_1783485962.jpeg', '0094701407_akte_1783485962.jpeg', '3173022903110032', 'LULUS', 'Belum', '2', NULL, '0094701407_ktpbapak_1783485962.jpeg', '0094701407_ktpibu_1783485962.jpeg', '0094701407_sptjm_1783485962.jpeg', 'Tidak', '', '', '', 1, 'Sudah'),
(74, 'SPMB-SMKPB1-2026-5593', 'YOU RAISE SIHOMBING', '3172010304101006', 'Jakarta', '2010-04-03', '0108732516', '2624770103', 'SMP NEGRI 21 JAKARTA', 'Tidak Ada', 'SARUSUNAWA TOWER D LT 8 NO 4', 'Penjaringan', 'Penjaringan', '081218280657', 'Akuntansi dan Keuangan Lembaga', 79.33, '2026-07-08 12:13:52', 51.66, 0.00, 0.00, '0108732516_ijazah_1783487632.jpeg', '0108732516_tka_1783487632.jpeg', '0108732516_kk_1783487632.jpeg', '0108732516_akte_1783487632.jpeg', '3172010502094601', 'LULUS', 'Belum', '2', NULL, '0108732516_ktpbapak_1783487632.jpeg', '0108732516_ktpibu_1783487632.jpeg', '0108732516_sptjm_1783487632.jpeg', 'Ya', '30223073221', '0108732516_tabungankjp_1783487632.jpeg', '', 1, 'Sudah'),
(75, 'SPMB-SMKPB1-2026-4984', 'SWASTI WIDIASIH', '3276086407090006', 'Depok', '2009-07-24', '0092008947', '2624620033', 'SMP NEGRI 72', 'Tidak Ada', 'JL PET SABANGAN 3/52 C', 'Petojo Selatan', 'Gambir', '083830355104', 'Manajemen Perkantoran dan Layanan Bisnis', 76.60, '2026-07-08 12:40:17', 38.33, 0.00, 0.00, '0092008947_ijazah_1783489217.jpeg', '0092008947_tka_1783489217.jpeg', '0092008947_kk_1783489217.jpeg', '0092008947_akte_1783489217.jpeg', '3171011410210002', 'LULUS', 'Belum', '2', NULL, '0092008947_ktpbapak_1783489217.jpeg', '0092008947_ktpibu_1783489217.jpeg', '0092008947_sptjm_1783489217.jpeg', 'Ya', '157280045231', '0092008947_tabungankjp_1783489217.jpeg', NULL, 1, 'Sudah'),
(76, 'SPMB-SMKPB1-2026-1053', 'ACHMAT RISKY BADILA', '3171032610091010', 'Baturaja', '2009-10-26', '3096886448', '2634420007', 'SANGGAR KEGIATAN BELAJAR 01', 'Tidak Ada', 'JL angkasa dalam 2 no 45', 'Gunung Sahari Selatan', 'Kemayoran', '089648511166', 'Akuntansi dan Keuangan Lembaga', 82.80, '2026-07-08 12:55:35', 45.00, 0.00, 0.00, '3096886448_ijazah_1783490135.jpeg', '3096886448_tka_1783490135.jpeg', '3096886448_kk_1783490135.pdf', '3096886448_akte_1783490135.jpeg', '3171030903230012', 'LULUS', 'Belum', '2', NULL, '3096886448_ktpbapak_1783490135.jpeg', '3096886448_ktpibu_1783490135.jpeg', '3096886448_sptjm_1783490135.jpeg', 'Ya', '14528007545', '3096886448_tabungankjp_1783490135.jpeg', NULL, 1, 'Sudah'),
(77, 'SPMB-SMKPB1-2026-9019', 'ALIEF RADHIKA FARSYA', '3173042108101006', 'Jakarta', '2010-08-21', '0089653373', '0089653373', 'SMP PLUS NURUL HIKMAH AL HAKIM', 'Tidak Ada', 'JL TANAH SEREAL RT 013/012 NO19', 'Tanah Sereal', 'Tambora', '082177745223', 'Manajemen Perkantoran dan Layanan Bisnis', 82.43, '2026-07-08 14:17:09', 45.00, 0.00, 0.00, '0089653373_ijazah_1783495029.jpeg', '0089653373_tka_1783495029.jpeg', '0089653373_kk_1783495029.jpeg', '0089653373_akte_1783495029.jpeg', '3173041209111015', 'LULUS', 'Belum', '2', NULL, '0089653373_ktpbapak_1783495029.jpeg', '0089653373_ktpibu_1783495029.jpeg', '0089653373_sptjm_1783495029.jpeg', 'Tidak', '', '', 'Nilai menggunakan rapot', 1, 'Sudah'),
(78, 'SPMB-SMKPB1-2026-1936', 'MARCEL JULIANTO', '3171021107091005', 'Jakarta', '2009-07-11', '0091185299', '2628220010', 'SMP BALA KESELAMATAN', 'Tidak Ada', 'JL LAUTZE DALAM NO 2 KARTINI SAWAH BESAR', 'Kartini', 'Sawah Besar', '085175395279', 'Manajemen Perkantoran dan Layanan Bisnis', 78.07, '2026-07-08 14:34:31', 48.35, 0.00, 0.00, '0091185299_ijazah_1783496071.jpeg', '0091185299_tka_1783496071.jpeg', '0091185299_kk_1783496071.jpeg', '0091185299_akte_1783496071.jpeg', '3171020703250002', 'LULUS', 'Belum', '2', NULL, '0091185299_ktpbapak_1783496071.jpeg', '0091185299_ktpibu_1783496071.jpeg', '0091185299_sptjm_1783496071.jpeg', 'Tidak', '', '', NULL, 1, 'Sudah'),
(79, 'SPMB-SMKPB1-2026-6530', 'RIRIN RIHANNA SHAKILA', '3173045407101002', 'Jakarta', '2010-07-14', '0085107376', '0085107376', 'SMP BANTEN RAYA CIKULUR', 'Tidak Ada', 'JEMBATAN BESI 004/002', 'Jembatan Besi', 'Tambora', '087770001793', 'Manajemen Perkantoran dan Layanan Bisnis', 80.00, '2026-07-08 15:39:48', 50.00, 0.00, 0.00, '0085107376_ijazah_1783499988.jpeg', '0085107376_tka_1783499988.jpeg', '0085107376_kk_1783499988.jpeg', '0085107376_akte_1783499988.jpeg', '3173041701099952', 'Tidak Jadi', 'Belum', '2', 'Lebih memilih SMKS MAARIF', '0085107376_ktpbapak_1783499988.jpeg', '0085107376_ktpibu_1783499988.jpeg', '0085107376_sptjm_1783499988.jpeg', 'Tidak', '', '', 'Nilai menggunakan rapot / Lebih memilih SMKS MAARIF', 1, 'Sudah'),
(80, 'SPMB-SMKPB1-2026-1957', 'ALFIANA PUTRA', '3602152406100001', 'Lebak', '2010-06-24', '0108884021', '2624770175', 'SMP NEGRI 21', 'Tidak Ada', 'SARUSUNAWA TOWER A / 20 NO 3', 'Penjaringan', 'Penjaringan', '081585766558', 'Manajemen Perkantoran dan Layanan Bisnis', 80.27, '2026-07-08 15:54:54', 43.33, 0.00, 0.00, '0108884021_ijazah_1783500894.jpeg', '0108884021_tka_1783500894.jpeg', '0108884021_kk_1783500894.jpeg', '0108884021_akte_1783500894.jpeg', '3172010602151020', 'LULUS', 'Belum', '2', NULL, '0108884021_ktpbapak_1783500894.jpeg', '0108884021_ktpibu_1783500894.jpeg', '0108884021_sptjm_1783500894.jpeg', 'Tidak', '', '', NULL, 1, 'Sudah'),
(81, 'SPMB-SMKPB1-2026-5338', 'ZENNY MAYADI', '3173032912070004', 'Jakarta', '2007-12-29', '3077665859', '2627990068', 'SMPI CHAIRIYAH MANSURIYAH', 'Tidak Ada', 'JL. KEAMANAN DALAM II  RT 3 RW 07', 'Keagungan', 'Taman Sari', '087892253338', 'Akuntansi dan Keuangan Lembaga', 81.50, '2026-07-09 08:50:45', 36.67, 0.00, 0.00, '3077665859_ijazah_1783561845.jpeg', '3077665859_tka_1783561845.jpeg', '3077665859_kk_1783561845.jpeg', '3077665859_akte_1783561845.jpeg', '3173030902110074', 'LULUS', 'Belum', '2', NULL, '3077665859_ktpbapak_1783561845.jpeg', '3077665859_ktpibu_1783561845.jpeg', '3077665859_sptjm_1783561845.jpeg', 'Ya', '31023547835', '3077665859_tabungankjp_1783561845.jpeg', NULL, 1, 'Sudah'),
(82, 'SPMB-SMKPB1-2026-9833', 'FERDLI PRATAMA NUR SOBARI', '3311020502100003', 'Jakarta', '2010-02-05', '0106651526', '2624720022', 'SMP YP IPPI PETOJO', 'Tidak Ada', 'KP DURI DALAM NO 13 B', 'Duri Selatan', 'Tambora', '085119782264', 'Akuntansi dan Keuangan Lembaga', 78.10, '2026-07-09 10:10:09', 56.67, 0.00, 0.00, '0106651526_ijazah_1783566609.jpeg', '0106651526_tka_1783566609.jpeg', '0106651526_kk_1783566609.jpeg', '0106651526_akte_1783566609.jpeg', '3173041401200021', 'LULUS', 'Belum', '2', NULL, '0106651526_ktpbapak_1783566609.jpeg', '0106651526_ktpibu_1783566609.jpeg', '0106651526_sptjm_1783566609.jpeg', 'Tidak', '', '', '', 1, 'Sudah'),
(83, 'SPMB-SMKPB1-2026-8625', 'RIDHO NUR RIZKY FEBRIANSYAH', '3173042802101004', 'Jakarta', '2010-02-28', '0108376866', '2628110024', 'SMP Negri 54 jakarta', 'Tidak Ada', 'Jl kp duri dalam no 53 rt 06/05', 'Duri Selatan', 'Tambora', '085718943757', 'Akuntansi dan Keuangan Lembaga', 82.03, '2026-07-09 10:15:58', 53.33, 0.00, 0.00, '0108376866_ijazah_1783566958.jpg', '0108376866_tka_1783566958.jpg', '0108376866_kk_1783566958.jpg', '0108376866_akte_1783566958.jpg', '3173040909111002', 'LULUS', 'Belum', '2', NULL, '0108376866_ktpbapak_1783566958.jpg', '0108376866_ktpibu_1783566958.jpg', '0108376866_sptjm_1783566958.jpg', 'Ya', '30023197594', '0108376866_tabungankjp_1783566958.jpg', NULL, 1, 'Sudah'),
(84, 'SPMB-SMKPB1-2026-7044', 'DEVID HERMANSYAH', '3173031610101001', 'Jakarta', '2010-10-16', '0106738501', '2634420013', 'SKB 01', 'Tidak Ada', 'JL. KESEDERHANAAN DALAM RT 2 RW 3', 'Keagungan', 'Taman Sari', '0895326579037', 'Akuntansi dan Keuangan Lembaga', 72.97, '2026-07-09 10:22:08', 48.33, 0.00, 0.00, '0106738501_ijazah_1783567328.jpeg', '0106738501_tka_1783567328.jpeg', '0106738501_kk_1783567328.jpeg', '0106738501_akte_1783567328.jpeg', '3173031603170006', 'Tidak Jadi', 'Belum', '2', 'Cadangan', '0106738501_ktpbapak_1783567328.jpeg', '0106738501_ktpibu_1783567328.jpeg', '0106738501_sptjm_1783567328.jpeg', 'Tidak', '', '', NULL, 1, 'Sudah'),
(85, 'SPMB-SMKPB1-2026-1971-G2', 'BELLA RAHMAH NAFISAH', '3173044611101001', 'Jakarta', '2010-11-06', '3104584084', '2628000005', 'SMP ISLAM TAMBORA', 'TIDAK  ADA', 'Pekpuran II / 15 C 006/003', 'Tanah Sereal', 'Tambora', '087778132770', 'Manajemen Perkantoran dan Layanan Bisnis', 85.37, '2026-07-09 11:48:07', 26.67, 0.00, 0.00, 'file_ijazah_1782721722_556.jpg', '3104584084_tka_1782199910.jpeg', '3104584084_kk_1782199910.jpeg', '3104584084_akte_1782199910.jpeg', '3173042401121030', 'LULUS', 'Belum', '2', NULL, '3104584084_ktpbapak_1782199910.jpeg', '3104584084_ktpibu_1782199910.jpeg', '3104584084_sptjm_1782199910.jpeg', 'Ya', '31023082476', '3104584084_tabungankjp_1782199910.jpeg', '', 0, 'Sudah'),
(86, 'SPMB-SMKPB1-2026-3610-G2', 'RIKI BONG', '6101161008080003', 'Tebas', '2008-08-10', '0081227309', '2627890021', 'SMP BUDAYA', 'TIDAK ADA', 'Pekapuran Raya / 9 Rt.009/004', 'Tanah Sereal', 'Tambora', '081213032455', 'Manajemen Perkantoran dan Layanan Bisnis', 74.00, '2026-07-09 11:48:20', 46.67, 0.00, 0.00, '0081227309_ijazah_1781668542.jpeg', '0081227309_tka_1781668542.jpeg', '0081227309_kk_1781668542.jpeg', '0081227309_akte_1781668542.jpeg', '3173040410230018', 'LULUS', 'Belum', '2', '', '0081227309_ktpbapak_1781668542.jpeg', '0081227309_ktpibu_1781668542.jpeg', '0081227309_sptjm_1781668542.jpeg', 'Tidak', '', '', '', 0, 'Sudah'),
(87, 'SPMB-SMKPB1-2026-2661-G2', 'ELVIN FERNANDO', '3173041906111002', 'Jakarta', '2011-06-19', '0119763297', '2627890006', 'SMP BUDAYA', 'TIDAK ADA', 'Jl. Tambora III GG V No. 9 D', 'Tambora', 'Tambora', '082261285848', 'Manajemen Perkantoran dan Layanan Bisnis', 74.30, '2026-07-09 11:48:47', 43.34, 0.00, 0.00, '0119763297_ijazah_1781669087.jpeg', '0119763297_tka_1781669087.jpeg', '0119763297_kk_1781669087.jpeg', '0119763297_akte_1781669087.jpeg', '3173042302170005', 'LULUS', 'Belum', '2', NULL, '0119763297_ktpbapak_1781669087.jpeg', '0119763297_ktpibu_1781669087.jpeg', '0119763297_sptjm_1781669087.jpeg', 'Tidak', '', '', '', 0, 'Sudah'),
(88, 'SPMB-SMKPB1-2026-9899-G2', 'CARLLA FLORENCIA', '3172056104111002', 'Jakarta', '2011-04-21', '0018732391', '2627670034', 'SMP HATI KUDUS', 'Tidak ada', 'Jl. Utama IV No. 1 A 001/008', 'Jelambar', 'Grogol Petamburan', '081220419139', 'Manajemen Perkantoran dan Layanan Bisnis', 72.07, '2026-07-09 12:48:55', 61.67, 0.00, 0.00, '0018732391_ijazah_1781494952.jpeg', '0018732391_tka_1781494952.jpeg', '0018732391_kk_1781494952.jpeg', '0018732391_akte_1781494952.jpeg', '3173022910190008', 'LULUS', 'Belum', '2', NULL, '0018732391_ktpbapak_1781494952.jpeg', '0018732391_ktpibu_1781494952.jpeg', '0018732391_sptjm_1781494952.jpeg', 'Ya', '32423117610', '0018732391_tabungankjp_1781494952.jpeg', '', 0, 'Sudah'),
(89, 'SPMB-SMKPB1-2026-1759', 'MUHAMMAD JEFFREN', '3172032506111011', 'Jakarta', '2011-06-25', '0115689317', '2627720045', 'SMP Pancaran Berkat', 'Tidak Ada', 'JL Jelambar Barat II F No.434', 'Jelambar Baru', 'Grogol Petamburan', '081297976315', 'Akuntansi dan Keuangan Lembaga', 74.57, '2026-07-09 11:50:23', 65.00, 0.00, 0.00, '0115689317_ijazah_1783572623.jpg', '0115689317_tka_1783572623.jpg', '0115689317_kk_1783572623.jpg', '0115689317_akte_1783572623.jpg', '3173022909160006', 'LULUS', 'Belum', '2', NULL, '0115689317_ktpbapak_1783572623.jpg', '0115689317_ktpibu_1783572623.jpg', '0115689317_sptjm_1783572623.jpg', 'Tidak', '', '', NULL, 1, 'Sudah'),
(90, 'SPMB-SMKPB1-2026-3641', 'JERUSHA KRISTIANI', '3171026601101001', 'Jakarta', '2010-01-26', '0103193227', '2624480009', 'SMP Negri 64', 'Tidak Ada', 'JL G gg I no 5\r\nKarang anyar', 'Karang Anyar', 'Sawah Besar', '081296098165', 'Manajemen Perkantoran dan Layanan Bisnis', 76.30, '2026-07-09 12:13:04', 31.67, 0.00, 0.00, '0103193227_ijazah_1783573984.jpg', 'file_tka_1783574164_155.jpeg', '0103193227_kk_1783573984.jpg', '0103193227_akte_1783573984.jpg', '3171022006160002', 'LULUS', 'Belum', '2', '', '0103193227_ktpbapak_1783573984.jpg', '0103193227_ktpibu_1783573984.jpg', '0103193227_sptjm_1783573984.jpg', 'Tidak', '', '', '', 1, 'Sudah'),
(91, 'SPMB-SMKPB1-2026-4998', 'MARIA BUNYAMIN', '3171015502111002', 'Jakarta', '2011-02-15', '0119598442', '2627870051', 'SMP NEGRI 63', 'Tidak Ada', 'JL BANDENGAN UTARA 1 GG LANGGAR NO 19', 'Pekojan', 'Tambora', '08132153452', 'Akuntansi dan Keuangan Lembaga', 82.23, '2026-07-09 12:37:28', 50.00, 0.00, 0.00, '0119598442_ijazah_1783575448.jpeg', '0119598442_tka_1783575448.jpeg', '0119598442_kk_1783575448.jpeg', '0119598442_akte_1783575448.jpeg', '3173042004180015', 'LULUS', 'Belum', '2', NULL, '0119598442_ktpbapak_1783575448.jpeg', '0119598442_ktpibu_1783575448.jpeg', '0119598442_sptjm_1783575448.jpeg', 'Tidak', '', '', NULL, 1, 'Sudah'),
(92, 'SPMB-SMKPB1-2026-8338-G2', 'ANGEL CHRISTIANA', '3173045604111011', 'Jakarta', '2011-04-16', '0118960126', '047/SK', 'SMP PERMATA BUNDA', 'Tidak ada', 'Duri Bangkit', 'Jembatan Besi', 'Tambora', '081398294558', 'Akuntansi dan Keuangan Lembaga', 76.97, '2026-07-09 08:15:24', 46.67, 0.00, 0.00, 'file_ijazah_1782724335_481.jpg', '0118960126_tka_1781495951.jpeg', '0118960126_kk_1781495951.jpeg', '0118960126_akte_1781495951.jpeg', '3173042110240003', 'LULUS', 'Belum', '2', '', '0118960126_ktpbapak_1781495951.jpeg', '0118960126_ktpibu_1781495951.jpeg', '0118960126_sptjm_1781495951.jpeg', 'Tidak', '', '', NULL, 0, 'Belum');

-- --------------------------------------------------------

--
-- Table structure for table `pendaftar_detail`
--

CREATE TABLE `pendaftar_detail` (
  `id_detail` int(11) NOT NULL,
  `pendaftar_id` int(11) NOT NULL,
  `jenis_kelamin` varchar(20) DEFAULT NULL,
  `tanggal_kk` date DEFAULT NULL,
  `nama_ibu` varchar(100) DEFAULT NULL,
  `agama` varchar(50) DEFAULT NULL,
  `npsn_sekolah` varchar(20) DEFAULT NULL,
  `kebutuhan_khusus` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pendaftar_detail`
--

INSERT INTO `pendaftar_detail` (`id_detail`, `pendaftar_id`, `jenis_kelamin`, `tanggal_kk`, `nama_ibu`, `agama`, `npsn_sekolah`, `kebutuhan_khusus`) VALUES
(1, 68, 'Laki-laki', '2023-05-11', 'SRI ASTUTI', 'Islam', '20101539', 'Tidak ada'),
(2, 69, 'Perempuan', '2021-07-21', 'SANTI NURMALA', 'Islam', '20100292', 'Tidak ada'),
(3, 70, 'Laki-laki', '2021-12-10', 'WIWI RESNIAWATI', 'Islam', '20311070', 'Tidak ada'),
(4, 71, 'Laki-laki', '2023-09-11', 'DWI ROSNITA', 'Islam', '20100770', 'Tidak ada'),
(5, 72, 'Laki-laki', '2023-06-19', 'DEWI KARTIKA', 'Islam', '20101576', 'Tidak ada'),
(6, 73, 'Perempuan', '2023-11-24', 'YULIANTI', 'Islam', '20101538', 'Tidak ada'),
(7, 74, 'Laki-laki', '2022-10-19', 'SULIM MARIA', 'Kristen Protestan', '20100770', 'Tidak ada'),
(8, 75, 'Perempuan', '2021-10-14', 'DEDE PARIDA', 'Islam', '20100267', 'Tidak ada'),
(9, 76, 'Laki-laki', '2023-03-14', 'HAIRRIAH', 'Islam', '26344200', 'Tidak ada'),
(10, 77, 'Laki-laki', '2024-09-18', 'IDAH ROSIDAH', 'Islam', '20231068', 'Tidak ada'),
(11, 78, 'Laki-laki', '2025-03-10', 'JUMIATI', 'Islam', '20106911', 'Tidak ada'),
(12, 79, 'Perempuan', '2019-11-18', 'DEDE ROSADAH', 'Islam', '20601807', 'Tidak ada'),
(13, 80, 'Laki-laki', '2024-07-12', 'PUJI SAFITRI', 'Islam', '20100770', 'Tidak ada'),
(14, 81, 'Laki-laki', '2024-09-18', 'SUKIYAH', 'Islam', '20108810', 'Tidak ada'),
(15, 82, 'Laki-laki', '2023-02-28', 'SUMIYATI', 'Islam', '20100249', 'Tidak ada'),
(16, 83, 'Laki-laki', '2023-02-21', 'NUR AZIZAH', 'Islam', '20101544', 'Tidak ada'),
(17, 84, 'Laki-laki', '2023-02-28', 'FENI ERMAWATI', 'Islam', '26344200', 'Tidak ada'),
(18, 89, 'Laki-laki', '2023-11-27', 'SRI WARSIH', 'Kristen Protestan', '20106851', 'Tidak ada'),
(19, 90, 'Perempuan', '2024-11-25', 'NOVITA KRISANTI', 'Kristen Protestan', '20100280', 'Tidak ada'),
(20, 86, 'Laki-laki', '2023-11-16', 'DJONG MIAU SIAN', 'Islam', '20106751', 'Tidak ada'),
(21, 85, 'Laki-laki', '2021-12-03', 'RUKIYEM', 'Islam', '20106808', 'Tidak ada'),
(22, 87, 'Laki-laki', '2024-03-04', 'LUSIANA', 'Islam', '20106751', 'Tidak ada'),
(23, 91, 'Perempuan', '2021-06-05', 'NURJANAH', 'Islam', '20101542', 'Tidak ada'),
(24, 88, 'Laki-laki', '2019-10-29', 'ENITA EMERIA', 'Islam', '20106798', 'Tidak ada');

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id` int(11) NOT NULL,
  `buka_gel_1` datetime DEFAULT NULL,
  `buka_gel_2` datetime DEFAULT NULL,
  `gelombang_aktif` int(11) DEFAULT 1,
  `status_pendaftaran` enum('buka','tutup') DEFAULT 'buka',
  `max_kuota_g1` int(11) DEFAULT 25,
  `max_kuota_g2` int(11) DEFAULT 11
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengaturan`
--

INSERT INTO `pengaturan` (`id`, `buka_gel_1`, `buka_gel_2`, `gelombang_aktif`, `status_pendaftaran`, `max_kuota_g1`, `max_kuota_g2`) VALUES
(1, '2026-06-30 15:00:00', '2026-07-09 15:00:00', 2, 'buka', 25, 11);

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_login` tinyint(10) DEFAULT NULL,
  `last_activity` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id`, `username`, `password`, `is_login`, `last_activity`) VALUES
(1, 'Admin1', '$2y$12$smX.90pStfOk6yNyiJi3pO4gSbluMxoxr3qxaKUCQe0HTeFW05g8i', 0, NULL),
(2, 'Admin2', '$2y$12$smX.90pStfOk6yNyiJi3pO4gSbluMxoxr3qxaKUCQe0HTeFW05g8i', 0, NULL),
(3, 'Admin3', '$2y$12$smX.90pStfOk6yNyiJi3pO4gSbluMxoxr3qxaKUCQe0HTeFW05g8i', 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `daftar_ulang`
--
ALTER TABLE `daftar_ulang`
  ADD PRIMARY KEY (`id_du`);

--
-- Indexes for table `pendaftar`
--
ALTER TABLE `pendaftar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_pendaftaran` (`no_pendaftaran`);

--
-- Indexes for table `pendaftar_detail`
--
ALTER TABLE `pendaftar_detail`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `pendaftar_id` (`pendaftar_id`);

--
-- Indexes for table `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `daftar_ulang`
--
ALTER TABLE `daftar_ulang`
  MODIFY `id_du` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `pendaftar`
--
ALTER TABLE `pendaftar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `pendaftar_detail`
--
ALTER TABLE `pendaftar_detail`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pendaftar_detail`
--
ALTER TABLE `pendaftar_detail`
  ADD CONSTRAINT `pendaftar_detail_ibfk_1` FOREIGN KEY (`pendaftar_id`) REFERENCES `pendaftar` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
