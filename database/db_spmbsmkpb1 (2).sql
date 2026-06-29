-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 29 Jun 2026 pada 16.44
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
  `gelombang` varchar(20) DEFAULT NULL,
  `alasan_pembatalan` text DEFAULT NULL,
  `file_ktp_bapak` varchar(255) DEFAULT NULL,
  `file_ktp_ibu` varchar(255) DEFAULT NULL,
  `file_sptjm` varchar(255) DEFAULT NULL,
  `status_kjp` enum('Ya','Tidak') DEFAULT 'Tidak',
  `no_rek_kjp` varchar(50) DEFAULT NULL,
  `file_tabungan_kjp` varchar(255) DEFAULT NULL,
  `catatan_panitia` text DEFAULT NULL,
  `is_detail_filled` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pendaftar`
--

INSERT INTO `pendaftar` (`id`, `no_pendaftaran`, `nama_lengkap`, `nik`, `tempat_lahir`, `tanggal_lahir`, `nisn`, `no_ijazah`, `asal_sekolah`, `riwayat_penyakit`, `alamat`, `kelurahan`, `kecamatan`, `no_whatsapp`, `pilihan_jurusan`, `nilai_skl`, `tanggal_daftar`, `nilai_tka`, `nilai_test`, `nilai_berkas`, `file_ijazah`, `file_tka`, `file_kk`, `file_akte`, `no_kk`, `status_konfirmasi`, `gelombang`, `alasan_pembatalan`, `file_ktp_bapak`, `file_ktp_ibu`, `file_sptjm`, `status_kjp`, `no_rek_kjp`, `file_tabungan_kjp`, `catatan_panitia`, `is_detail_filled`) VALUES
(1, 'SPMB-SMKPB1-2026-4400', 'MATTHEW TRIADERALDO', '3173061808090003', 'Jakarta', '2009-08-18', '0091370856', '13/PK.01.02', 'SMP NEGRI 249', 'Tidak ada', 'Tambora I GG IV No. 92', 'Tambora', 'Tambora', '085927511988', 'Manajemen Perkantoran dan Layanan Bisnis', 80.57, '2026-06-15 01:55:06', 46.60, 79.73, 0.00, 'file_ijazah_1782723500_580.jpg', '0091370856_tka_1781488506.jpeg', '0091370856_kk_1781488506.jpeg', '0091370856_akte_1781488506.jpeg', '3173040211230003', 'Menunggu', '1', NULL, '0091370856_ktpbapak_1781488506.jpeg', '0091370856_ktpibu_1781488506.jpeg', '0091370856_sptjm_1781488506.jpeg', 'Tidak', '', '', 'Bawa FC TKA dan Rapot Smt 1 - 5 (Lapor Diri)', 0),
(2, 'SPMB-SMKPB1-2026-1935', 'CARLISSA AGRATA', '3173045111111001', 'Jakarta', '2011-11-11', '0112819297', '046/SK/SMP-PB/VI/2026', 'SMP PERMATA BUNDA', 'Tidak ada', 'TSS GG TRIKORA II / 38', 'Duri Utara', 'Tambora', '081219913611', 'Manajemen Perkantoran dan Layanan Bisnis', 81.80, '2026-06-15 02:45:32', 63.33, 82.70, 0.00, 'file_ijazah_1782724100_353.jpg', '0112819297_tka_1781491532.jpeg', '0112819297_kk_1781491532.jpeg', '0112819297_akte_1781491532.jpeg', '3173042112100169', 'Menunggu', '1', NULL, '0112819297_ktpbapak_1781491532.jpeg', '0112819297_ktpibu_1781491532.jpeg', '0112819297_sptjm_1781491532.jpeg', 'Ya', '300231988502', '0112819297_tabungankjp_1781491532.jpeg', 'Bawa FC Sertifikat TKA dan Rapot Smt 1 - 5 (Lapor Diri)', 0),
(4, 'SPMB-SMKPB1-2026-1425', 'ALVIN CULLEN', '3173041804101002', 'Jakarta', '2010-04-18', '0104155757', '166.100/PK.01.02', 'SMP NEGRI 83 JAKARTA', 'Tidak ada', 'Rusunawa Tambora I TWR A LT XI / XII', 'Angke', 'Tambora', '085282666955', 'Manajemen Perkantoran dan Layanan Bisnis', 79.90, '2026-06-15 02:58:26', 63.33, 80.66, 0.00, 'file_ijazah_1782723725_736.jpg', '0104155757_tka_1781492306.jpeg', '0104155757_kk_1781492306.jpeg', '0104155757_akte_1781492306.jpeg', '3173042302151011', 'Menunggu', '1', NULL, '0104155757_ktpbapak_1781492306.jpeg', '0104155757_ktpibu_1781492306.jpeg', '0104155757_sptjm_1781492306.jpeg', 'Ya', '10223123403', '0104155757_tabungankjp_1781492306.jpeg', 'Bawa FC Rapot Smt 1 - 5 dan TKA (Lapor Diri)', 0),
(5, 'SPMB-SMKPB1-2026-3985', 'DEVIN SHEN', '3173040103111004', 'Jakarta', '2011-03-01', '0113814175', '046/SK', 'SMP PERMATA BUNDA', 'Tidak ada', 'Jembatan Besi', 'Jembatan Besi', 'Tambora', '081286672288', 'Manajemen Perkantoran dan Layanan Bisnis', 76.73, '2026-06-15 03:16:11', 63.33, 75.33, 0.00, 'file_ijazah_1782719664_408.jpg', '0113814175_tka_1781493371.jpeg', '0113814175_kk_1781493371.jpeg', '0113814175_akte_1781493371.jpeg', '3173041310101044', 'Menunggu', '1', NULL, '0113814175_ktpbapak_1781493371.jpeg', '0113814175_ktpibu_1781493371.jpeg', '0113814175_sptjm_1781493371.jpeg', 'Tidak', '', '', 'Lengkap', 0),
(6, 'SPMB-SMKPB1-2026-9365', 'SANDY KURNIAWAN', '3173011005111015', 'Jakarta', '2011-05-10', '0113531147', '002/SKL/SMP-CMI/VI/2026', 'SMP CINDERA MATA INDAH', 'TIDAK ADA', 'Jl. Trikora II No. 19', 'Duri Utara', 'Tambora', '0895321910082', 'Akuntansi dan Keuangan Lembaga', 78.20, '2026-06-15 03:34:07', 56.67, 72.46, 0.00, 'file_ijazah_1782724722_449.jpg', '0113531147_tka_1781494447.jpeg', '0113531147_kk_1781494447.jpeg', '0113531147_akte_1781494447.jpeg', '3173041011150010', 'Menunggu', '1', NULL, '0113531147_ktpbapak_1781494447.jpeg', '0113531147_ktpibu_1781494447.jpeg', '0113531147_sptjm_1781494447.jpeg', 'Tidak', '', '', 'Lengkap', 0),
(7, 'SPMB-SMKPB1-2026-5989', 'CARLLA FLORENCIA', '3172056104111002', 'Jakarta', '2011-04-21', '0018732391', '027/KEP/SMP-HK/VI/2026', 'SMP HATI KUDUS', 'Tidak ada', 'Jl. Utama IV No. 1 A', 'Jelambar', 'Grogol Petamburan', '081220419139', 'Manajemen Perkantoran dan Layanan Bisnis', 75.69, '2026-06-15 03:42:32', 0.00, 69.11, 0.00, '0018732391_ijazah_1781494952.jpeg', '0018732391_tka_1781494952.jpeg', '0018732391_kk_1781494952.jpeg', '0018732391_akte_1781494952.jpeg', '3173022910190008', 'Menunggu', '1', NULL, '0018732391_ktpbapak_1781494952.jpeg', '0018732391_ktpibu_1781494952.jpeg', '0018732391_sptjm_1781494952.jpeg', 'Ya', '32423117610', '0018732391_tabungankjp_1781494952.jpeg', 'Belum ada Sidanira dan TKA, Bawa FC Rapot Smt 1 - 5 dan (Lapor Diri)', 0),
(8, 'SPMB-SMKPB1-2026-9059', 'ANGEL CHRISTIANA', '3173045604111011', 'Jakarta', '2011-04-16', '0118960126', '047/SK', 'SMP PERMATA BUNDA', 'Tidak ada', 'Duri Bangkit', 'Jembatan Besi', 'Tambora', '081398294558', 'Manajemen Perkantoran dan Layanan Bisnis', 76.97, '2026-06-15 03:59:11', 56.67, 76.46, 0.00, 'file_ijazah_1782724335_481.jpg', '0118960126_tka_1781495951.jpeg', '0118960126_kk_1781495951.jpeg', '0118960126_akte_1781495951.jpeg', '3173042110240003', 'Menunggu', '1', NULL, '0118960126_ktpbapak_1781495951.jpeg', '0118960126_ktpibu_1781495951.jpeg', '0118960126_sptjm_1781495951.jpeg', 'Tidak', '', '', 'Bawa FC Sertifikat TKA (Lapor Diri)', 0),
(9, 'SPMB-SMKPB1-2026-1009', 'FELIX LIEVARO', '3173041510101006', 'Jakarta', '2010-10-15', '0108025554', '054/SK/SMP-PB/VI/2026', 'SMP PERMATA BUNDA', 'TIDAK ADA', 'Krendang Raya No. 109', 'Duri Utara', 'Tambora', '085847531046', 'Manajemen Perkantoran dan Layanan Bisnis', 80.87, '2026-06-15 04:12:07', 63.33, 80.60, 0.00, 'file_ijazah_1782724513_514.jpg', '0108025554_tka_1781496727.jpeg', '0108025554_kk_1781496727.jpeg', '0108025554_akte_1781496727.jpeg', '3173041101100079', 'Menunggu', '1', NULL, '0108025554_ktpbapak_1781496727.jpeg', '0108025554_ktpibu_1781496727.jpeg', '0108025554_sptjm_1781496727.jpeg', 'Tidak', '', '', 'Bawa FC Sertifikat TKA dan Rapot Smt 1 - 5 (Lapor Diri)', 0),
(10, 'SPMB-SMKPB1-2026-8319', 'CHEN CUN YI', '3173041603111007', 'Kuching', '2011-03-16', '0115397193', '051', 'SMP PERMATA BUNDA', 'TIDAK ADA', 'Jl. Duri Selatan I No. 52', 'Duri Selatan', 'Tambora', '085782922159', 'Akuntansi dan Keuangan Lembaga', 78.57, '2026-06-15 04:23:48', 68.33, 79.00, 0.00, 'file_ijazah_1782726338_742.jpg', 'file_tka_1782726338_518.jpg', '0115397193_kk_1781497428.jpeg', '0115397193_akte_1781497428.jpeg', '3173042504250004', 'Menunggu', '1', NULL, '0115397193_ktpbapak_1781497428.jpeg', '0115397193_ktpibu_1781497428.jpeg', '0115397193_sptjm_1781497428.jpeg', 'Tidak', '', '', 'Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0),
(11, 'SPMB-SMKPB1-2026-8354', 'OKTAFIANUS', '6172021210100002', 'Jakarta', '2010-10-12', '0107636121', '055/SK/SMP-PB/VI/2026', 'SMP PERMATA BUNDA', 'DARAH TINGGI', 'Krendang Timur III No. 1', 'Krendang', 'Tambora', '0895629369497', 'Manajemen Perkantoran dan Layanan Bisnis', 79.70, '2026-06-15 04:27:42', 63.33, 78.00, 0.00, 'file_ijazah_1782723883_168.jpg', '0107636121_tka_1781497662.jpeg', '0107636121_kk_1781497662.jpeg', '0107636121_akte_1781497662.jpeg', '3173042811180011', 'Menunggu', '1', NULL, '0107636121_ktpbapak_1781497662.jpeg', '0107636121_ktpibu_1781497662.jpeg', '0107636121_sptjm_1781497662.jpeg', 'Tidak', '', '', 'Bawa FC Sertifikat TKA dan Rapot Smt 1 - 5 (Lapor Diri)', 0),
(12, 'SPMB-SMKPB1-2026-4455', 'FEBRYANT WIJAYA', '6171042802110003', 'Jakarta', '2011-02-28', '0122905828', '056/SK/SMP-PB/VI/2026', 'SMP PERMATA BUNDA', 'TIDAK ADA', 'Panca Krida I No. 22', 'Duri Utara', 'Tambora', '085774236972', 'Manajemen Perkantoran dan Layanan Bisnis', 76.07, '2026-06-15 04:40:15', 68.00, 75.46, 0.00, 'file_ijazah_1782725621_797.jpg', '0122905828_tka_1781498415.jpeg', '0122905828_kk_1781498415.jpeg', '0122905828_akte_1781498415.jpeg', '3173040202160021', 'Menunggu', '1', NULL, '0122905828_ktpbapak_1781498415.jpeg', '0122905828_ktpibu_1781498415.jpeg', '0122905828_sptjm_1781498415.jpeg', 'Ya', '30023195320', '0122905828_tabungankjp_1781498415.jpeg', 'Bawa FC Sertifikat TKA dan Rapot Smt 1 - 5 (Lapor Diri)', 0),
(14, 'SPMB-SMKPB1-2026-1676', 'FELIX CHRISTIAN CUNG', '3173030503111001', 'Jakarta', '2011-03-05', '0117992825', '002/SKL/SMP-CMI', 'SMP CINDERA MATA INDAH', 'TIDAK ADA', 'Jl. Mangga Besar VI SLT. / 26', 'Taman Sari', 'Taman Sari', '0895911212353', 'Akuntansi dan Keuangan Lembaga', 83.17, '2026-06-15 05:10:17', 60.00, 83.20, 0.00, 'file_ijazah_1782726824_275.jpg', '0117992825_tka_1781500217.jpeg', '0117992825_kk_1781500217.jpeg', '0117992825_akte_1781500217.jpeg', '3173031201091122', 'Menunggu', '1', NULL, '0117992825_ktpbapak_1781500217.jpeg', '0117992825_ktpibu_1781500217.jpeg', '0117992825_sptjm_1781500217.jpeg', 'Ya', '31623076394', '0117992825_tabungankjp_1781500217.jpeg', 'Bawa FC Sertifikat TKA dan Rapot Smt 1 - 5 (Lapor Diri)', 0),
(15, 'SPMB-SMKPB1-2026-2478', 'ENI', '3173046203111007', 'Jakarta', '2011-03-22', '0114485472', '057/SK/SMP-PB/VI/2026', 'SMP PERMATA BUNDA', 'TIDAK ADA', 'GG Balok II No. 20 A', 'Duri Utara', 'Tambora', '0895391848001', 'Akuntansi dan Keuangan Lembaga', 74.30, '2026-06-15 05:17:03', 31.66, 74.00, 0.00, 'file_ijazah_1782725908_117.jpg', 'file_tka_1782725908_444.jpg', '0114485472_kk_1781500623.jpeg', '0114485472_akte_1781500623.jpeg', '3173042406111028', 'Menunggu', '1', NULL, '0114485472_ktpbapak_1781500623.jpeg', '0114485472_ktpibu_1781500623.jpeg', '0114485472_sptjm_1781500623.jpeg', 'Ya', '30023198892', '0114485472_tabungankjp_1781500623.jpeg', 'Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0),
(16, 'SPMB-SMKPB1-2026-4683', 'ARDIANSYAH DWI LAKSONO', '3171011404111004', 'Jakarta', '2011-04-14', '0113755309', '032/081/SMP-BT.IKA/V/2026', 'SMP BHINEKA TUNGGAL IKA', 'TIDAK ADA', 'JLN DURI B GG IX', 'Duri Pulo', 'Gambir', '087861754482', 'Akuntansi dan Keuangan Lembaga', 80.13, '2026-06-15 07:41:39', 73.33, 78.33, 0.00, 'file_ijazah_1782726099_216.jpg', 'file_tka_1782726099_261.jpg', '0113755309_kk_1781509299.jpeg', '0113755309_akte_1781509299.jpeg', '3171012610100027', 'Menunggu', '1', '', '0113755309_ktpbapak_1781509299.jpeg', '0113755309_ktpibu_1781509299.jpeg', '0113755309_sptjm_1781509299.jpeg', 'Tidak', '', '', 'Bawa Sertifikat TKA dan FC Rapot Smt 1 - 5 (Lapor Diri)', 0),
(17, 'SPMB-SMKPB1-2026-7021', 'BRYAN VINCENTIUS', '3173041807111012', 'Singkawang', '2011-07-18', '0115866655', '112/SMP.BDY/SKL/VI/2026', 'SMP BUDAYA', 'TIDAK ADA', 'Jl. Duri Baru', 'Jembatan Besi', 'Tambora', '082125570985', 'Akuntansi dan Keuangan Lembaga', 80.40, '2026-06-15 08:29:24', 66.67, 79.66, 0.00, 'file_ijazah_1782726634_627.jpg', '0115866655_tka_1781512164.jpeg', '0115866655_kk_1781512164.jpeg', '0115866655_akte_1781512164.jpeg', '3173040309121022', 'Menunggu', '1', NULL, '0115866655_ktpbapak_1781512164.jpeg', '0115866655_ktpibu_1781512164.jpeg', '0115866655_sptjm_1781512164.jpeg', 'Ya', '30523101997', '0115866655_tabungankjp_1781512164.jpeg', 'Bawa FC Sertifikat TKA dan Rapot Smt 1 - 5 (Lapor Diri)', 0),
(18, 'SPMB-SMKPB1-2026-3225', 'RIKI BONG', '6101161008080003', 'Tebas', '2008-08-10', '0081227309', '109/SMP.BDY/SKL/VI/2026', 'SMP BUDAYA', 'TIDAK ADA', 'Pekapuran Raya / 9', 'Tanah Sereal', 'Tambora', '081213032455', 'Manajemen Perkantoran dan Layanan Bisnis', 74.00, '2026-06-17 03:55:42', 46.67, 74.00, 0.00, '0081227309_ijazah_1781668542.jpeg', '0081227309_tka_1781668542.jpeg', '0081227309_kk_1781668542.jpeg', '0081227309_akte_1781668542.jpeg', '3173040410230018', 'Menunggu', '1', NULL, '0081227309_ktpbapak_1781668542.jpeg', '0081227309_ktpibu_1781668542.jpeg', '0081227309_sptjm_1781668542.jpeg', 'Tidak', '', '', 'Bawa FC Sidanira, Nilai SDN diganti dengan Nilai Rata-Rapat Rekapitulasi pada Formulir', 0),
(19, 'SPMB-SMKPB1-2026-8275', 'ELVIN FERNANDO', '3173041906111002', 'Jakarta', '2011-06-19', '0119763297', '110/SMP.BDY/SKL/VI/2026', 'smp budaya', 'TIDAK ADA', 'Jl. Tambora III GG V No. 9 D', 'Tambora', 'Tambora', '082261285848', 'Manajemen Perkantoran dan Layanan Bisnis', 74.30, '2026-06-17 04:04:47', 43.33, 74.26, 0.00, '0119763297_ijazah_1781669087.jpeg', '0119763297_tka_1781669087.jpeg', '0119763297_kk_1781669087.jpeg', '0119763297_akte_1781669087.jpeg', '3173042302170005', 'Menunggu', '1', NULL, '0119763297_ktpbapak_1781669087.jpeg', '0119763297_ktpibu_1781669087.jpeg', '0119763297_sptjm_1781669087.jpeg', 'Tidak', '', '', 'Belum ada Sidanira, Nilai SDN diganti menggunakan Rekapitulasi Rapor dari Formulir', 0),
(20, 'SPMB-SMKPB1-2026-5487', 'MARCO VINCENT', '3173042707111003', 'Jakarta', '2011-07-27', '0118156546', '058/SK/SMP-PB/VI/2026', 'SMP PERMATA BUNDA', 'TIDAK ADA', 'Sawah Lio II GG V No. 3', 'Jembatan Lima', 'Tambora', '081289205573', 'Manajemen Perkantoran dan Layanan Bisnis', 81.00, '2026-06-17 08:08:56', 70.00, 79.00, 0.00, 'file_ijazah_1782722814_177.jpg', '0118156546_tka_1781683736.jpeg', '0118156546_kk_1781683736.jpeg', '0118156546_akte_1781683736.jpeg', '3173042707111003', 'Menunggu', '1', NULL, '0118156546_ktpbapak_1781683736.jpeg', '0118156546_ktpibu_1781683736.jpeg', '0118156546_sptjm_1781683736.jpeg', 'Ya', '30023224168', '0118156546_tabungankjp_1781683736.jpeg', 'Bawa FC Rapot Smt 1 - 5 dan TKA (Lapor Diri)', 0),
(21, 'SPMB-SMKPB1-2026-1306', 'LIONEL SAPUTRA CONG', '3173042808111003', 'Jakarta', '2011-08-28', '0119269211', '50 TAHUN 2026', 'SMP NEGRI 63 JAKARTA', 'tidak ada', 'Jl. Kalianyar', 'Kali Anyar', 'Tambora', '085777118549', 'Manajemen Perkantoran dan Layanan Bisnis', 85.93, '2026-06-18 03:52:13', 66.67, 85.73, 0.00, 'file_ijazah_1782725402_964.jpg', '0119269211_tka_1781754733.jpg', '0119269211_kk_1781754733.jpg', '0119269211_akte_1781754733.jpg', '3173042001094424', 'Menunggu', '1', NULL, '0119269211_ktpbapak_1781754733.jpg', '0119269211_ktpibu_1781754733.jpg', '0119269211_sptjm_1781754733.jpg', 'Tidak', '', '', 'Bawa FC Sertifikat TKA dan Rapot Smt 1 - 5 (Lapor Diri)', 0),
(22, 'SPMB-SMKPB1-2026-6986', 'KASIH AGAVE UNAS', '6205022709110001', 'Barito Utara', '2011-09-27', '0114161483', '029/SMP-CN/VI/2026', 'SMP Candra Naya', 'Tidak Ada', 'Jl. Rawa Bebek No.8A', 'Penjaringan', 'Penjaringan', '081281161925', 'Akuntansi dan Keuangan Lembaga', 81.91, '2026-06-18 06:56:08', 50.00, 79.00, 0.00, '0114161483_ijazah_1781765768.jpg', '0114161483_tka_1781765768.jpg', '0114161483_kk_1781765768.jpg', '0114161483_akte_1781765768.jpg', '3172012505151005', 'Menunggu', '1', NULL, '0114161483_ktpbapak_1781765768.jpg', '0114161483_ktpibu_1781765768.jpg', '0114161483_sptjm_1781765768.jpg', 'Ya', '1022312301', '0114161483_tabungankjp_1781765768.jpg', 'Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0),
(23, 'SPMB-SMKPB1-2026-6292', 'CINDY CECILIA', '3173036211111004', 'Jakarta', '2011-11-22', '0114725564', '222/SKL/SMP-CMI', 'SMP CINDERA MATA INDAH', 'TIDAK ADA', 'Krendang Tengah Dalam No. 85', 'Krendang', 'Tambora', '085219519899', 'Manajemen Perkantoran dan Layanan Bisnis', 83.00, '2026-06-19 04:02:32', 66.67, 83.13, 0.00, 'file_ijazah_1782723308_321.jpg', '0114725564_tka_1781841752.jpg', '0114725564_kk_1781841752.jpg', '0114725564_akte_1781841752.jpg', '3173041303200018', 'Menunggu', '1', NULL, '0114725564_ktpbapak_1781841752.jpg', '0114725564_ktpibu_1781841752.jpg', '0114725564_sptjm_1781841752.jpg', 'Ya', '30023198515', '0114725564_tabungankjp_1781841752.jpg', 'Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0),
(24, 'SPMB-SMKPB1-2026-4280', 'DOMINIQUE ANGEL LEA BOY', '3173046604111004', 'Jakarta', '2011-04-26', '0117441627', '112 - 2026', 'SMP NEGRI 63 JAKARTA', 'TIDAK ADA', 'KP Krendang No. 14', 'Duri Utara', 'Tambora', '081289221108', 'Manajemen Perkantoran dan Layanan Bisnis', 82.03, '2026-06-19 04:31:52', 40.00, 81.66, 0.00, 'file_ijazah_1782725200_308.jpg', 'file_tka_1782725200_487.jpg', '0117441627_kk_1781843512.jpg', '0117441627_akte_1781843512.jpg', '3173041001098910', 'Menunggu', '1', NULL, '0117441627_ktpbapak_1781843512.jpg', '0117441627_ktpibu_1781843512.jpg', '0117441627_sptjm_1781843512.jpg', 'Ya', '30023197764', '0117441627_tabungankjp_1781843512.jpg', 'Lengkap', 0),
(25, 'SPMB-SMKPB1-2026-1558', 'MUTIARA MERRY', '6172026412100001', 'Singkawang', '2010-12-24', '0108411094', '053 PB', 'SMP PERMATA BUNDA', 'TIDAK ADA', 'Pekapuran VII No. 14', 'Tanah Sereal', 'Tambora', '089602756381', 'Manajemen Perkantoran dan Layanan Bisnis', 79.67, '2026-06-22 03:29:26', 60.00, 78.80, 0.00, '0108411094_ijazah_1782098966.jpeg', '0108411094_tka_1782098966.jpeg', '0108411094_kk_1782098966.jpeg', '0108411094_akte_1782098966.jpeg', '3173040208220010', 'Menunggu', '1', NULL, '0108411094_ktpbapak_1782098966.jpeg', '0108411094_ktpibu_1782098966.jpeg', '0108411094_sptjm_1782098966.jpeg', 'Tidak', '', '', 'Scan SKL ganti dengan Sidanira, Bawa FC Rapot Smt 1 - 5 dan TKA (Lapor Diri)', 0),
(26, 'SPMB-SMKPB1-2026-2722', 'VICTORIA FELICE', '3173046890101007', 'Jakarta', '2010-09-28', '0101295537', '054 PB', 'SMP PERMATA BUNDA', 'TIDAK ADA', 'Jl. Petak Serani, Pekapuran IV No.20', 'Tanah Sereal', 'Tanbora', '081253628811', 'Manajemen Perkantoran dan Layanan Bisnis', 76.97, '2026-06-22 03:58:45', 63.33, 75.80, 0.00, 'file_ijazah_1782441711_864.jpg', 'file_tka_1782441711_439.jpg', '0101295537_kk_1782100725.jpeg', '0101295537_akte_1782100725.jpeg', '3173041301090184', 'Menunggu', '1', NULL, '0101295537_ktpbapak_1782100725.jpeg', '0101295537_ktpibu_1782100725.jpeg', '0101295537_sptjm_1782100725.jpeg', 'Ya', '30023025033', '0101295537_tabungankjp_1782100725.jpeg', 'Lengkap', 0),
(27, 'SPMB-SMKPB1-2026-3956', 'GLADIES OCTAVIANI', '3173036610111002', 'Jakarta', '2011-10-26', '0118100484', '003/SKL/SMP-CMI', 'SMP CINDERA MATA INDAH', 'TIDAK ADA', 'Jl. Keutamaan Dalam', 'Krukut', 'Taman Sari', '089643369273', 'Manajemen Perkantoran dan Layanan Bisnis', 81.40, '2026-06-22 06:13:57', 48.33, 86.33, 0.00, '0118100484_ijazah_1782108837.jpeg', '0118100484_tka_1782108837.jpeg', '0118100484_kk_1782108837.jpeg', '0118100484_akte_1782108837.jpeg', '3173032706131008', 'Menunggu', '1', NULL, '0118100484_ktpbapak_1782108837.jpeg', '0118100484_ktpibu_1782108837.jpeg', '0118100484_sptjm_1782108837.jpeg', 'Ya', '30523099283', '0118100484_tabungankjp_1782108837.jpeg', 'Scan SKL ganti dengan Sidanira, Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0),
(28, 'SPMB-SMKPB1-2026-5373', 'HAIKAL KHOIRUL MULYA', '3173060104101014', 'Tangerang', '2010-04-01', '0107617868', '311/PK.01.02', 'SMP NEGRI 105 JAKARTA', 'TIDAK ADA', 'KP. Cipondo', 'Semanan', 'Kalideres', '085218799115', 'Manajemen Perkantoran dan Layanan Bisnis', 83.10, '2026-06-22 06:37:31', 68.33, 82.93, 0.00, 'file_ijazah_1782723102_414.jpg', '0107617868_tka_1782110251.jpeg', '0107617868_kk_1782110251.jpeg', '0107617868_akte_1782110251.jpeg', '3173062003141064', 'Menunggu', '1', NULL, '0107617868_ktpbapak_1782110251.jpeg', '0107617868_ktpibu_1782110251.jpeg', '0107617868_sptjm_1782110251.jpeg', 'Ya', '31823101875', '0107617868_tabungankjp_1782110251.jpeg', 'Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0),
(29, 'SPMB-SMKPB1-2026-9337', 'VANESSA', '3173046002111001', 'Jakarta', '2011-02-20', '0116136505', '123/SMP/YPB/VI/2026', 'SMP KRISTEN PANCARAN BERKAT', 'TIDAK ADA', 'Kalianyar IV', 'Kali Anyar', 'Tambora', '08986631985', 'Manajemen Perkantoran dan Layanan Bisnis', 85.05, '2026-06-23 01:47:20', 55.00, 75.53, 0.00, '0116136505_ijazah_1782179240.jpeg', '0116136505_tka_1782179240.jpeg', '0116136505_kk_1782179240.jpeg', '0116136505_akte_1782179240.jpeg', '3173042001095709', 'Menunggu', '1', NULL, '0116136505_ktpbapak_1782179240.jpeg', '0116136505_ktpibu_1782179240.jpeg', '0116136505_sptjm_1782179240.jpeg', 'Tidak', '', '', 'Lenkap (Sidanira menggunakan SKL)', 0),
(30, 'SPMB-SMKPB1-2026-5367', 'QAYLA PUTRI KHAIRUNISA', '3173036409101003', 'Jakarta', '2010-09-24', '0101652338', '2628000088', 'SMP ISLAM TAMBORA', 'Tidak Ada', 'Jl. Kp. Jawa Kb. Sayur', 'Keagungan', 'Taman Sari', '081219752700', 'Manajemen Perkantoran dan Layanan Bisnis', 86.53, '2026-06-23 05:19:14', 50.00, 84.80, 0.00, '0101652338_ijazah_1782191954.jpg', '0101652338_tka_1782191954.jpg', '0101652338_kk_1782191954.jpg', '0101652338_akte_1782191954.jpg', '3173032303160001', 'Menunggu', '1', NULL, '0101652338_ktpbapak_1782191954.jpg', '0101652338_ktpibu_1782191954.jpg', '0101652338_sptjm_1782191954.jpg', 'Ya', '30023189222', '0101652338_tabungankjp_1782191954.jpg', 'Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0),
(31, 'SPMB-SMKPB1-2026-8633', 'MAHIRA ZALFA OKTOVIARA', '3173046510090004', 'Jakarta', '2009-10-25', '0098402196', '180/PK.01.02/2026', 'SMP NEGERI 159 JAKARTA', 'TIDAK  ADA', 'Kalianyar', 'Kali Anyar', 'Tambora', '081389795645', 'Manajemen Perkantoran dan Layanan Bisnis', 82.37, '2026-06-23 06:59:42', 41.66, 81.46, 0.00, '0098402196_ijazah_1782197982.jpeg', '0098402196_tka_1782197982.jpeg', '0098402196_kk_1782197982.jpeg', '0098402196_akte_1782197982.jpeg', '3173041812100107', 'Menunggu', '1', NULL, '0098402196_ktpbapak_1782197982.jpeg', '0098402196_ktpibu_1782197982.jpeg', '0098402196_sptjm_1782197982.jpeg', 'Tidak', '', '', 'Lengkap', 0),
(32, 'SPMB-SMKPB1-2026-6258', 'BELLA RAHMAH NAFISAH', '3173044611101001', 'Jakarta', '2010-11-06', '3104584084', '108.229/SKL/SMP-IT/V/2026', 'SMP ISLAM TAMBORA', 'TIDAK  ADA', 'Pekpuran II / 15 C', 'Tanah Sereal', 'Tambora', '087778132770', 'Manajemen Perkantoran dan Layanan Bisnis', 85.37, '2026-06-23 07:31:50', 26.67, 83.40, 0.00, 'file_ijazah_1782721722_556.jpg', '3104584084_tka_1782199910.jpeg', '3104584084_kk_1782199910.jpeg', '3104584084_akte_1782199910.jpeg', '3173042401121030', 'Menunggu', '1', NULL, '3104584084_ktpbapak_1782199910.jpeg', '3104584084_ktpibu_1782199910.jpeg', '3104584084_sptjm_1782199910.jpeg', 'Ya', '31023082476', '3104584084_tabungankjp_1782199910.jpeg', 'Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0),
(33, 'SPMB-SMKPB1-2026-9211', 'YEMIMA SITUMEANG', '3172026110091003', 'Jakarta', '2009-10-21', '0096908327', '252/PK.01.02/2026', 'SMP NEGERI 152 JAKARTA', 'TIDAK ADA', 'Jl. Pohon Beringin No. 58 B', 'Sunter Jaya', 'Tanjung Priok', '085175007183', 'Manajemen Perkantoran dan Layanan Bisnis', 84.07, '2026-06-24 02:04:42', 50.00, 85.06, 0.00, '0096908327_ijazah_1782266682.jpeg', '0096908327_tka_1782266682.jpeg', '0096908327_kk_1782266682.jpeg', '0096908327_akte_1782266682.jpeg', '3173202220109968', 'Menunggu', '1', NULL, '0096908327_ktpbapak_1782266682.jpeg', '0096908327_ktpibu_1782266682.jpeg', '0096908327_sptjm_1782266682.jpeg', 'Ya', '12223592912', '0096908327_tabungankjp_1782266682.jpeg', 'Scan SKL ganti Sidanira, Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0),
(34, 'SPMB-SMKPB1-2026-1688', 'AHMAD RAMADHANNU', '3173043007111006', 'Jakarta', '2011-07-30', '3118670102', '083/SK/SMP-IF/V/2026', 'SMP ISLAM FATAHILLAH', 'TIDAK ADA', 'Pekapuran GG V Dalam No. 13 C', 'Tanah Sereal', 'Tambora', '088295318232', 'Manajemen Perkantoran dan Layanan Bisnis', 77.50, '2026-06-24 02:20:56', 66.66, 76.86, 0.00, '3118670102_ijazah_1782267656.jpeg', '3118670102_tka_1782267656.jpeg', '3118670102_kk_1782267656.jpeg', '3118670102_akte_1782267656.jpeg', '3173040208111019', 'Menunggu', '1', NULL, '3118670102_ktpbapak_1782267656.jpeg', '3118670102_ktpibu_1782267656.jpeg', '3118670102_sptjm_1782267656.jpeg', 'Ya', '31023082425', '3118670102_tabungankjp_1782267656.jpeg', 'Scan SKL ganti Sidanira, Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0),
(35, 'SPMB-SMKPB1-2026-1643', 'LAKHSMAN MOHINDER SAPUTRA', '3173040512090003', 'Jakarta', '2009-12-05', '0094306777', '023/PK/01.02/2026', 'SMPN 54', 'TIDAK ADA', 'Jl. Gudang Areng. II No. 40', 'Tanah Sereal', 'Tambora', '089631115597', 'Manajemen Perkantoran dan Layanan Bisnis', 78.57, '2026-06-24 02:48:53', 50.00, 0.00, 0.00, '0094306777_ijazah_1782269333.jpeg', '0094306777_tka_1782269333.jpeg', '0094306777_kk_1782269333.jpeg', '0094306777_akte_1782269333.jpeg', '3173041005100112', 'Menunggu', '2', NULL, '0094306777_ktpbapak_1782269333.jpeg', '0094306777_ktpibu_1782269333.jpeg', '0094306777_sptjm_1782269333.jpeg', 'Ya', '31023084801', '0094306777_tabungankjp_1782269333.jpeg', NULL, 0),
(36, 'SPMB-SMKPB1-2026-8343', 'AGUS MAULANA', '3173040108101005', 'Jakarta', '2010-08-01', '3000917942', '131.229/SKL/SMP-IT/V/2026', 'SMP ISLAM TAMBORA', 'TIDAK ADA', 'Jl. Pekapuran V / 37 A', 'Tanah Sereal', 'Tambora', '085813133891', 'Akuntansi dan Keuangan Lembaga', 84.43, '2026-06-24 07:12:20', 35.00, 82.66, 0.00, '3000917942_ijazah_1782285140.jpeg', '3000917942_tka_1782285140.jpeg', '3000917942_kk_1782285140.jpeg', '3000917942_akte_1782285140.jpeg', '3173045505900004', 'Menunggu', '1', NULL, '3000917942_ktpbapak_1782285140.jpeg', '3000917942_ktpibu_1782285140.jpeg', '3000917942_sptjm_1782285140.jpeg', 'Ya', '31023085122', '3000917942_tabungankjp_1782285140.jpeg', 'Scan SKL ganti Sidanira, Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0),
(37, 'SPMB-SMKPB1-2026-7293', 'MUHAMMAD VALEN MULIAWAN', '3173041302111001', 'Jakarta', '2011-02-13', '3113318317', '41/SKL/SMPICM/VI/2026', 'SMPI CHAIRIYAH MANSURIYAH', 'TIDAK ADA', 'Jln. Pekapuran II No. 18 B ', 'Tanah Sereal', 'Tambora', '081292158877', 'Akuntansi dan Keuangan Lembaga', 80.53, '2026-06-24 07:38:18', 40.00, 80.00, 0.00, '3113318317_ijazah_1782286698.jpeg', '3113318317_tka_1782286698.jpeg', '3113318317_kk_1782286698.jpeg', '3113318317_akte_1782286698.jpeg', '3173041201096937', 'Menunggu', '1', '', '3113318317_ktpbapak_1782286698.jpeg', '3113318317_ktpibu_1782286698.jpeg', '3113318317_sptjm_1782286698.jpeg', 'Ya', '31023082611', '3113318317_tabungankjp_1782286698.jpeg', 'Scan SKL ganti Sidanira', 0),
(38, 'SPMB-SMKPB1-2026-8728', 'BRANDON IMANUEL SUSETIA', '3173044204141002', 'Jakarta', '2010-12-28', '0111994287', '126/SMP-BDY/SK-TKA/VI/2026', 'SMP BUDAYA', 'TIDAK ADA', 'Jl. Tanah Sereal XVIII No.4', 'Tanah Sereal', 'Tambora', '085178530388', 'Akuntansi dan Keuangan Lembaga', 74.63, '2026-06-25 07:50:52', 53.33, 75.20, 0.00, 'file_ijazah_1782440704_540.jpg', 'file_tka_1782440704_162.jpg', '0111994287_kk_1782373852.jpeg', '0111994287_akte_1782373852.jpeg', '3173042306111027', 'Menunggu', '1', NULL, '0111994287_ktpbapak_1782373852.jpeg', '0111994287_ktpibu_1782373852.jpeg', '0111994287_sptjm_1782373852.jpeg', 'Tidak', '', '', 'FC Rapot smt 1 - 5 (Lapor Diri)', 0),
(39, 'SPMB-SMKPB1-2026-3298', 'EVANS SUPRIYANTO', '3173032606101002', 'Jakarta', '2010-06-26', '0108866603', '12/SKL/SMPICM/VI/2026', 'SMPI CHAIRIYAH MANSURIYAH', 'TIDAK ADA', 'JL. Keamanan No 84 rt 13/ rw 003', 'Keagungan', 'Taman Sari', '0838775014000', 'Akuntansi dan Keuangan Lembaga', 81.27, '2026-06-26 08:38:00', 53.33, 81.26, 0.00, '0108866603_ijazah_1782437880.jpeg', '0108866603_tka_1782437880.jpeg', '0108866603_kk_1782437880.jpeg', '0108866603_akte_1782437880.jpeg', '3173031507240007', 'Menunggu', '1', NULL, '0108866603_ktpbapak_1782437880.jpeg', '0108866603_ktpibu_1782437880.jpeg', '0108866603_sptjm_1782437880.jpeg', 'Ya', '180201022539501', '0108866603_tabungankjp_1782437880.jpeg', 'FC Rapot Smt 1 - 5 (Lapor Diri)', 0),
(40, 'SPMB-SMKPB1-2026-3101', 'KRISTIN FRETY SIREGAR', '3173044912101010', 'Jakarta', '2010-12-09', '0104638460', '089/SKL/SMP-CMI/VI/2026', 'SMPI Cindera Mata Indah', 'TIDAK ADA', 'JL. Songsi Dalam no 12 tanah sereal', 'Tanah Sereal', 'Tambora', '081221694488', 'Akuntansi dan Keuangan Lembaga', 80.03, '2026-06-26 11:19:10', 51.66, 79.80, 0.00, '0104638460_ijazah_1782447550.jpeg', '0104638460_tka_1782447550.jpeg', '0104638460_kk_1782447550.jpeg', '0104638460_akte_1782447550.jpeg', '3173040211101044', 'Menunggu', '1', NULL, '0104638460_ktpbapak_1782447550.jpeg', '0104638460_ktpibu_1782447550.jpeg', '0104638460_sptjm_1782447550.jpeg', 'Tidak', '', '', 'Scan SKL ganti Sidanira, Bawa FC Rapot Smt 1 - 5 (Lapor Diri)', 0),
(41, 'SPMB-SMKPB1-2026-8099', 'WAHYU RIYANTO', '3173040611101004', 'Jakarta', '2010-11-05', '0107427798', '400.3.11.1/334/2026', 'SMP NEGRI 1 SELOMERTO', 'TIDAK ADA', 'KP DURI DALAM RT 001 RW 005 NO 40', 'Duri Selatan', 'Tambora', '089613346069', 'Akuntansi dan Keuangan Lembaga', 80.37, '2026-06-26 11:48:56', 46.67, 80.13, 0.00, '0107427798_ijazah_1782449336.jpeg', '0107427798_tka_1782449336.jpeg', '0107427798_kk_1782449336.jpeg', '0107427798_akte_1782449336.jpeg', '3173043012101051', 'Menunggu', '1', NULL, '0107427798_ktpbapak_1782449336.jpeg', '0107427798_ktpibu_1782449336.jpeg', '0107427798_sptjm_1782449336.jpeg', 'Tidak', '', '', 'Tidak ada Sidanira.', 0),
(42, 'SPMB-SMKPB1-2026-9520', 'DARVIN SETIAWAN', '3171010111101002', 'Jakarta', '2010-11-01', '0109604571', '124202600569278', 'SKB 24', 'TIDAK ADA', 'KP DURI BARAT NO 19 RT/RW 007/008', 'Duri Pulo', 'Gambir', '081287718536', 'Akuntansi dan Keuangan Lembaga', 71.13, '2026-06-26 13:29:54', 46.66, 71.00, 0.00, '0109604571_ijazah_1782455394.jpeg', '0109604571_tka_1782455394.jpeg', '0109604571_kk_1782455394.jpeg', '0109604571_akte_1782455394.jpeg', '3171012708190004', 'Menunggu', '1', NULL, '0109604571_ktpbapak_1782455394.jpeg', '0109604571_ktpibu_1782455394.jpeg', '0109604571_sptjm_1782455394.jpeg', 'Tidak', '', '', 'Bawa FC KK dan TKA (Lapor Diri)\r\nADA KJP TAPI TIDAK INPUT', 0),
(43, 'SPMB-SMKPB1-2026-8303', 'AILEEN JOICE KANE SYLPH MILLIONAIRE', '3173046306111008', 'Jakarta', '2011-06-23', '0118483281', '2628040001', 'SMP KEMULIAAN BUNDA', 'Tidak Ada', 'Jl. Terate GG. IV No. 23', 'Jembatan Lima', 'Tambora', '085693794757', 'Manajemen Perkantoran dan Layanan Bisnis', 83.50, '2026-06-26 14:08:48', 68.34, 0.00, 0.00, '0118483281_ijazah_1782457728.jpg', '0118483281_tka_1782457728.jpg', '0118483281_kk_1782457728.jpg', '0118483281_akte_1782457728.jpg', '3173041904111030', 'Menunggu', '2', NULL, '0118483281_ktpbapak_1782457728.jpg', '0118483281_ktpibu_1782457728.jpg', '0118483281_sptjm_1782457728.jpg', 'Ya', '30523123320', '0118483281_tabungankjp_1782457728.jpg', NULL, 0),
(44, 'SPMB-SMKPB1-2026-3734', 'PUTRI AYU RAHMAWATI', '3173035601111002', 'Jakarta', '2011-01-16', '0111057233', '2627870093', 'SMPN 63', 'Tidak Ada', 'Jl. Kesederhanaan', 'Keagungan', 'Taman Sari', '085693794757', 'Manajemen Perkantoran dan Layanan Bisnis', 82.97, '2026-06-26 14:20:17', 53.33, 0.00, 0.00, '0111057233_ijazah_1782458417.jpg', '0111057233_tka_1782458417.jpg', '0111057233_kk_1782458417.jpg', '0111057233_akte_1782458417.jpg', '3173031111100058', 'Menunggu', '2', NULL, '0111057233_ktpbapak_1782458417.jpg', '0111057233_ktpibu_1782458417.jpg', '0111057233_sptjm_1782458417.jpg', 'Tidak', '', '', NULL, 0),
(45, 'SPMB-SMKPB1-2026-1148', 'NATASHA', '3173046412101004', 'Jakarta', '2010-12-24', '0106035992', '2627900012', 'SMP BUDI BAHASA', 'Tidak Ada', 'GG Mesjid I Dalam No. 27', 'Angke', 'Tambora', '087785595159', 'Manajemen Perkantoran dan Layanan Bisnis', 74.30, '2026-06-26 14:29:56', 55.00, 0.00, 0.00, '0106035992_ijazah_1782458996.jpg', '0106035992_tka_1782458996.jpg', '0106035992_kk_1782458996.jpg', '0106035992_akte_1782458996.jpg', '3173042711150012', 'Menunggu', '2', NULL, '0106035992_ktpbapak_1782458996.jpg', '0106035992_ktpibu_1782458996.jpg', '0106035992_sptjm_1782458996.jpg', 'Tidak', '', '', NULL, 0),
(46, 'SPMB-SMKPB1-2026-7822', 'SOVITALIA', '3603206809100001', 'Tangerang', '2010-09-28', '0101092205', '055 PB', 'SMP PERMATA BUNDA', 'TIDAK ADA', 'KP DURI DALAM RT/RW 008/005', 'Duri Selatan', 'Tambora', '08212100157', 'Akuntansi dan Keuangan Lembaga', 77.57, '2026-06-29 07:54:55', 55.00, 0.00, 0.00, '0101092205_ijazah_1782694495.jpeg', '0101092205_tka_1782694495.jpeg', '0101092205_kk_1782694495.jpeg', '0101092205_akte_1782694495.jpeg', '3173042105260007', 'Menunggu', '1', NULL, '0101092205_ktpbapak_1782694495.jpeg', '0101092205_ktpibu_1782694495.jpeg', '0101092205_sptjm_1782694495.jpeg', 'Tidak', '', '', NULL, 0),
(47, 'SPMB-SMKPB1-2026-5663', 'CHARLES ALESIO', '6172021310100002', 'Singkawang', '2010-10-13', '0104628907', '234/SMP.BDY/SKL/VI/2026', 'SMP BUDAYA', 'TIDAK ADA', 'JL. Jelambar Barat 2 G No 460 A', 'Jelambar Baru', 'Grogol Petamburan', '0895347203000', 'Akuntansi dan Keuangan Lembaga', 82.97, '2026-06-29 10:17:44', 65.00, 0.00, 0.00, '0104628907_ijazah_1782703064.jpeg', '0104628907_tka_1782703064.jpeg', '0104628907_kk_1782703064.jpeg', '0104628907_akte_1782703064.jpeg', '3173020512190018', 'Menunggu', '1', NULL, '0104628907_ktpbapak_1782703064.jpeg', '0104628907_ktpibu_1782703064.jpeg', '0104628907_sptjm_1782703064.jpeg', 'Tidak', '', '', NULL, 0),
(48, 'SPMB-SMKPB1-2026-3779', 'AKBAR DWI TAMA', '3173012806101006', 'Jakarta', '2010-06-28', '0108680620', '367/I01.3/SMP.TJ/2026', 'SMP TANJUNG', 'TIDAK ADA', 'Krendang Selatan 1 No. 1', 'Krendang', 'Tambora', '081399023903', 'Akuntansi dan Keuangan Lembaga', 82.63, '2026-06-29 10:40:14', 45.00, 0.00, 0.00, '0108680620_ijazah_1782704414.jpeg', '0108680620_tka_1782704414.jpeg', '0108680620_kk_1782704414.jpeg', '0108680620_akte_1782704414.jpeg', '3171040812210007', 'Menunggu', '1', NULL, '0108680620_ktpbapak_1782704414.jpeg', '0108680620_ktpibu_1782704414.jpeg', '0108680620_sptjm_1782704414.jpeg', 'Tidak', '', '', NULL, 0),
(49, 'SPMB-SMKPB1-2026-6455', 'GIZELLE  GERALDINE CIPTADI', '3671085808090006', 'Jakarta', '2010-06-28', '0092924985', '64/PB.SMS/UPK/S5/V/2026', 'PKBM SOLUSI MANDIRI SENTOSA', 'TIDAK ADA', 'JL. PELOPOR  RTO4 RW 15', 'Tegal Alur', 'Kalideres', '082246295526', 'Akuntansi dan Keuangan Lembaga', 81.00, '2026-06-29 12:09:18', 58.33, 0.00, 0.00, '0092924985_ijazah_1782709758.pdf', '0092924985_tka_1782709758.jpeg', '0092924985_kk_1782709758.jpeg', '0092924985_akte_1782709758.jpeg', '3172042811160003', 'Menunggu', '1', NULL, '0092924985_ktpbapak_1782709758.jpeg', '0092924985_ktpibu_1782709758.jpeg', '0092924985_sptjm_1782709758.jpeg', 'Tidak', '', '', NULL, 0),
(50, 'SPMB-SMKPB1-2026-1531', 'NICHOLAS ANDRIANO', '3173042508111006', 'Jakarta', '2011-08-25', '0114832715', '2624700016', 'SMP ST PAULUS', 'TIDAK ADA', 'KP. KRENDANG PULO NO 10 D', 'Duri Utara', 'Tambora', '081318545376', 'Akuntansi dan Keuangan Lembaga', 77.57, '2026-06-29 12:22:04', 70.00, 0.00, 0.00, '0114832715_ijazah_1782710524.jpeg', '0114832715_tka_1782710524.jpeg', '0114832715_kk_1782710524.jpeg', '0114832715_akte_1782710524.jpeg', '3173041203141030', 'Menunggu', '1', NULL, '0114832715_ktpbapak_1782710524.jpeg', '0114832715_ktpibu_1782710524.jpeg', '0114832715_sptjm_1782710524.jpeg', 'Tidak', '', '', NULL, 0),
(51, 'SPMB-SMKPB1-2026-2628', 'GRACE EMMANUELL', '3173045008111007', 'Jakarta', '2011-08-10', '0113006724', '2627870116', 'SMP NEGERI 63 JAKARTA', 'TIDAK ADA', 'JL. TIANG BENDERA VI/42', 'Roa Malaka', 'Tambora', '085714196968', 'Akuntansi dan Keuangan Lembaga', 81.10, '2026-06-29 13:35:31', 55.00, 0.00, 0.00, '0113006724_ijazah_1782714931.jpeg', '0113006724_tka_1782714931.jpeg', '0113006724_kk_1782714931.jpeg', '0113006724_akte_1782714931.jpeg', '3173040707111006', 'Menunggu', '1', NULL, '0113006724_ktpbapak_1782714931.jpeg', '0113006724_ktpibu_1782714931.jpeg', '0113006724_sptjm_1782714931.jpeg', 'Tidak', '', '', NULL, 0),
(52, 'SPMB-SMKPB1-2026-4900', 'SURYA RIDHO ALIF PRATAMA', '3173032705111005', 'Jakarta', '2011-08-10', '0111346539', '2628100096', 'SMP NEGERI 22 JAKARTA', 'TIDAK ADA', 'JL. LADA DALAM', 'Pinangsia', 'Taman Sari', '088212458434', 'Akuntansi dan Keuangan Lembaga', 77.80, '2026-06-29 13:48:46', 40.00, 0.00, 0.00, '0111346539_ijazah_1782715726.jpeg', '0111346539_tka_1782715726.jpeg', '0111346539_kk_1782715726.jpeg', '0111346539_akte_1782715726.jpeg', '3173032211101001', 'Menunggu', '1', NULL, '0111346539_ktpbapak_1782715726.jpeg', '0111346539_ktpibu_1782715726.jpeg', '0111346539_sptjm_1782715726.jpeg', 'Tidak', '', '', NULL, 0),
(53, 'SPMB-SMKPB1-2026-6303', 'VIONA FLORENSIA', '3173044810111004', 'Jakarta', '2011-10-08', '0117921164', '2628080052', 'SMP SINAR DHARMA', 'TIDAK ADA', 'KP. KRENDANG NO 19', 'Duri Utara', 'Tambora', '083167425973', 'Akuntansi dan Keuangan Lembaga', 75.90, '2026-06-29 14:02:02', 66.66, 0.00, 0.00, '0117921164_ijazah_1782716522.jpeg', '0117921164_tka_1782716522.jpeg', '0117921164_kk_1782716522.jpeg', '0117921164_akte_1782716522.jpeg', '3173040710111041', 'Menunggu', '1', NULL, '0117921164_ktpbapak_1782716522.jpeg', '0117921164_ktpibu_1782716522.jpeg', '0117921164_sptjm_1782716522.jpeg', 'Tidak', '', '', NULL, 0),
(54, 'SPMB-SMKPB1-2026-7529', 'JOANA MAGDALENA', '3171034905101003', 'Jakarta', '2010-05-09', '0104258327', '2624260052', 'SMP NEGERI 59 JAKARTA', 'TIDAK ADA', 'JL. SUNTER JAYA II A NO 53', 'Sunter Jaya', 'Tanjung Priok', '081382523992', 'Manajemen Perkantoran dan Layanan Bisnis', 81.58, '2026-06-29 14:50:37', 40.00, 0.00, 0.00, '0104258327_ijazah_1782719437.jpeg', '0104258327_tka_1782719437.jpeg', '0104258327_kk_1782719437.jpeg', '0104258327_akte_1782719437.jpeg', '3172022401180018', 'Menunggu', '2', NULL, '0104258327_ktpbapak_1782719437.jpeg', '0104258327_ktpibu_1782719437.jpeg', '0104258327_sptjm_1782719437.jpeg', 'Tidak', '', '', NULL, 0),
(55, 'SPMB-SMKPB1-2026-9828', 'DIANA WIJAYA', '3173046008111006', 'Jakarta', '2011-08-20', '0106707881', '121202614250110', 'SMP GENESARET', 'TIDAK ADA', 'jl. pekojan III KP JANIS NO 38', 'Pekojan', 'Tambora', '085697770137', 'Akuntansi dan Keuangan Lembaga', 71.49, '2026-06-29 15:49:20', 55.00, 0.00, 0.00, '0106707881_ijazah_1782722960.jpeg', '0106707881_tka_1782722960.jpeg', '0106707881_kk_1782722960.jpeg', '0106707881_akte_1782722960.jpeg', '3173040501095082', 'Menunggu', '1', NULL, '0106707881_ktpbapak_1782722960.jpeg', '0106707881_ktpibu_1782722960.jpeg', '0106707881_sptjm_1782722960.jpeg', 'Ya', '30523102225', '0106707881_tabungankjp_1782722960.jpeg', NULL, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaturan`
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
-- Dumping data untuk tabel `pengaturan`
--

INSERT INTO `pengaturan` (`id`, `buka_gel_1`, `buka_gel_2`, `gelombang_aktif`, `status_pendaftaran`, `max_kuota_g1`, `max_kuota_g2`) VALUES
(1, '2026-07-01 15:00:00', '2026-07-10 15:00:00', 1, 'buka', 25, 11);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
