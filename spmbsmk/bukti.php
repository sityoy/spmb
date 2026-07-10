<?php
session_start();
include 'koneksi.php';

if (!isset($_GET['no_pendaftaran'])) {
    header("Location: index.php");
    exit;
}

$no_daftar = trim($_GET['no_pendaftaran']); 
$no_daftar_clean = mysqli_real_escape_string($conn, $no_daftar);

// Ambil data dari database (JOIN dengan pendaftar_detail untuk data terlengkap)
$query = "SELECT p.*, pd.jenis_kelamin, pd.tanggal_kk, pd.nama_ibu, pd.agama, pd.npsn_sekolah, pd.kebutuhan_khusus 
          FROM pendaftar p 
          LEFT JOIN pendaftar_detail pd ON p.id = pd.pendaftar_id 
          WHERE TRIM(p.no_pendaftaran) = TRIM('$no_daftar_clean')";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) { 
    die("Data dengan nomor pendaftaran <b>" . htmlspecialchars($no_daftar) . "</b> tidak ditemukan."); 
}

// ==========================================
// ANTI-HACK: PROTEKSI KEBOCORAN DATA PRIBADI
// ==========================================
if (!isset($_SESSION['login'])) {
    if (isset($_POST['verifikasi_nisn'])) {
        if ($_POST['verifikasi_nisn'] !== $data['nisn']) {
            die("<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>
                 <h3 style='color:red;'>Akses Ditolak!</h3>
                 <p>NISN yang Anda masukkan salah.</p>
                 <a href='bukti.php?no_pendaftaran=".urlencode($no_daftar)."'>Coba Lagi</a>
                 </div>");
        }
        $_SESSION['izin_akses_bukti_' . $no_daftar_clean] = true;
    } 
    elseif (!isset($_SESSION['izin_akses_bukti_' . $no_daftar_clean])) {
        die("
        <div style='font-family: sans-serif; max-width: 400px; margin: 50px auto; text-align: center; background: #f8fafc; padding: 30px; border-radius: 12px; border: 1px solid #cbd5e1;'>
            <h3 style='color: #4f46e5; margin-top:0;'>🔒 Keamanan Data Privasi</h3>
            <p style='font-size: 14px; color: #475569;'>Untuk melihat dan mencetak bukti pendaftaran ini, silakan masukkan <b>NISN</b> pendaftar sebagai verifikasi.</p>
            <form method='POST'>
                <input type='text' name='verifikasi_nisn' placeholder='Masukkan 10 Digit NISN' required style='width: 100%; padding: 12px; box-sizing: border-box; margin-bottom: 15px; border: 1px solid #cbd5e1; border-radius: 8px; text-align: center; letter-spacing: 2px; font-size: 16px;'>
                <button type='submit' style='width: 100%; padding: 12px; background: #4f46e5; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;'>Buka Bukti Pendaftaran</button>
            </form>
        </div>
        ");
    }
}
// ==========================================

// LOGIKA PENENTUAN STATUS KELULUSAN
$status_db = $data['status_konfirmasi'];
if ($status_db == 'LULUS') {
    $status_teks = "LULUS SELEKSI";
    $status_warna_bg = "#d1fae5";
    $status_warna_teks = "#065f46";
    $status_border = "#10b981";
    $pesan_tambahan = "Selamat! Anda dinyatakan LULUS. Silakan segera melakukan proses Daftar Ulang.";
    $catatan_panitia = !empty($data['catatan_panitia']) ? htmlspecialchars($data['catatan_panitia']) : "Tidak ada catatan tambahan dari panitia.";
} elseif ($status_db == 'Tidak Jadi') {
    $status_teks = "TIDAK LULUS / GUGUR";
    $status_warna_bg = "#fee2e2";
    $status_warna_teks = "#991b1b";
    $status_border = "#ef4444";
    $alasan = !empty($data['alasan_pembatalan']) ? htmlspecialchars($data['alasan_pembatalan']) : "Mohon maaf, Anda belum dapat diterima pada seleksi kali ini.";
    $catatan_panitia = !empty($data['catatan_panitia']) ? htmlspecialchars($data['catatan_panitia']) : "Tidak ada catatan tambahan dari panitia.";
    $pesan_tambahan = "Keterangan: <b>" . $alasan . "</b>";
} else {
    // Logika Khusus Tampilan Status untuk Cadangan
    if ($data['gelombang'] == 'Cadangan') {
        $status_teks = "ANTRIAN CADANGAN";
        $status_warna_bg = "#fffbeb";
        $status_warna_teks = "#b45309";
        $status_border = "#fde68a";
        $pesan_tambahan = "Pendaftaran Anda masuk ke antrian Cadangan. Kami akan menghubungi Anda jika terdapat kuota kosong dari pendaftar utama yang mengundurkan diri.";
    } else {
        $status_teks = "MENUNGGU PENGUMUMAN";
        $status_warna_bg = "#fef3c7";
        $status_warna_teks = "#b45309";
        $status_border = "#f59e0b";
        $pesan_tambahan = "Berkas Anda sedang dalam proses verifikasi dan penilaian oleh panitia seleksi.";
    }
}

$jrs = ($data['pilihan_jurusan'] == "Akuntansi dan Keuangan Lembaga") ? "Akuntansi dan Keuangan Lembaga (AKL)" : "Manajemen Perkantoran dan Layanan Bisnis (MPLB)";
$label_gel_bukti = ($data['gelombang'] == 'Cadangan') ? 'Cadangan / Antrian' : 'Gelombang ' . $data['gelombang'];

// LOGIKA PERHITUNGAN NILAI BARU
$asli_skl = (float)$data['nilai_skl'];
$asli_tka = (float)$data['nilai_tka'];
$nilai_berkas_asli = ($asli_skl + $asli_tka) / 2;
$bobot_skl = $asli_skl * 0.70; 
$bobot_tka = $asli_tka * 0.30; 
$nilai_berkas_bobot = $bobot_skl + $bobot_tka; 
$nilai_test = (float)$data['nilai_test'];

// Handling variabel Null dari LEFT JOIN (jika data lama belum ada detail)
$jk = !empty($data['jenis_kelamin']) ? $data['jenis_kelamin'] : "-";
$agama = !empty($data['agama']) ? $data['agama'] : "-";
$nama_ibu = !empty($data['nama_ibu']) ? $data['nama_ibu'] : "-";
$tgl_kk = !empty($data['tanggal_kk']) ? $data['tanggal_kk'] : "-";
$npsn = !empty($data['npsn_sekolah']) ? $data['npsn_sekolah'] : "-";
$kb_khusus = !empty($data['kebutuhan_khusus']) ? $data['kebutuhan_khusus'] : "Tidak Ada";

function tgl_indo($tanggal) {
    if (empty($tanggal) || $tanggal == '0000-00-00 00:00:00' || $tanggal == '0000-00-00' || $tanggal == '-') { return "-"; }
    $pecah_waktu = explode(' ', $tanggal);
    $hanya_tanggal = $pecah_waktu[0];
    $bulan_indo = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $split = explode('-', $hanya_tanggal);
    if(count($split) < 3) return $tanggal;
    $tgl   = $split[2];
    $bulan = (int)$split[1];
    $tahun = $split[0];
    $hasil_tgl = $tgl . ' ' . $bulan_indo[$bulan] . ' ' . $tahun;
    if (isset($pecah_waktu[1]) && $pecah_waktu[1] != '00:00:00') {
        $jam = date('H:i', strtotime($tanggal));
        return $hasil_tgl . ' - ' . $jam . ' WIB';
    }
    return $hasil_tgl;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti_Pendaftaran_<?php echo htmlspecialchars($data['no_pendaftaran'], ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="icon" type="image/x-icon" href="logo/logosmkpb.png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        
        body { font-family: 'Plus Jakarta Sans', Arial, sans-serif; color: #1f2937; background: #f3f4f6; margin: 0; padding: 20px 10px; }
        .card-bukti { max-width: 1100px; background: #fff; margin: 0 auto; padding: 30px 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border-top: 8px solid #4f46e5; box-sizing: border-box; }
        
        /* Kop Header (Compact) */
        .kop-wrapper { display: flex; align-items: center; justify-content: center; gap: 20px; border-bottom: 2px dashed #cbd5e1; padding-bottom: 15px; margin-bottom: 20px; }
        .kop-logos img { max-height: 80px; margin: 0 5px; }
        .kop-text { text-align: left; }
        .kop-text h2 { margin: 0; color: #4f46e5; font-size: 24px; font-weight: 800; }
        .kop-text p { margin: 4px 0 0 0; font-size: 14px; color: #4b5563; }
        
        .no-reg { text-align: center; font-size: 22px; font-weight: 800; color: #0f172a; margin: 10px 0 15px 0; letter-spacing: 1.5px; background:#f8fafc; padding:8px; border-radius:8px; border:1px solid #e2e8f0; word-break: break-all; }
        
        /* Status */
        .box-status { text-align: center; padding: 12px; border-radius: 8px; margin-bottom: 20px; border-width: 2px; border-style: solid; }
        .box-status h3 { margin: 0 0 5px 0; font-size: 20px; font-weight: 800; letter-spacing: 1px; }
        .box-status span { font-size: 13px; display: block; }

        /* Grid System (Landscape Format) */
        .grid-container { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .section-title { font-size: 14px; font-weight: 800; color: #4f46e5; border-bottom: 2px solid #e2e8f0; padding-bottom: 5px; margin-top: 0; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .main-table { width: 100%; border-collapse: collapse; table-layout: auto; }
        .main-table td { padding: 8px 5px; border-bottom: 1px solid #f1f5f9; font-size: 13px; vertical-align: top; word-wrap: break-word; }
        .main-table td:first-child { font-weight: 600; color: #475569; width: 40%; }
        
        /* Evaluasi Nilai */
        .box-nilai { background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 12.5px; line-height: 1.6; margin-top: 5px;}
        .tabel-rincian { width: 100%; border-collapse: collapse; }
        .tabel-rincian td { padding: 4px 0; border: none; }
        .nilai-asli { font-weight: 700; color: #1e293b; }
        .nilai-bobot { font-weight: 800; color: #0284c7; }
        .garis-batas { border-top: 1px dashed #cbd5e1 !important; margin-top: 6px; padding-top: 6px !important; }
        .box-hasil-akhir { background: #eef2ff; border: 1.5px solid #c7d2fe; padding: 10px; border-radius: 8px; margin-top: 5px; text-align: center; }
        .text-hasil-akhir { color: #4f46e5; font-size: 18px; font-weight: 800; display: block; margin-top: 2px; }
        
        .check-icon { color: #059669; font-weight: bold; background: #d1fae5; padding: 2px 6px; border-radius: 4px; font-size: 11px; display: inline-block;}
        
        .btn-area { text-align: center; margin-top: 30px; display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; }
        .btn-print, .btn-next { padding: 12px 24px; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; text-decoration: none; font-size: 14px; transition: 0.2s; }
        .btn-print { background: #4f46e5; }
        .btn-next { background: #10b981; }

        @media (max-width: 768px) {
            .grid-container { grid-template-columns: 1fr; gap: 20px; }
            .kop-wrapper { flex-direction: column; text-align: center; }
            .kop-text { text-align: center; }
        }

        /* 🖨️ PRINT SETTINGS: OTOMATIS LANDSCAPE */
        @page { size: A4 landscape; margin: 1cm; }
        @media print {
            body { background: #fff; padding: 0; margin: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .card-bukti { box-shadow: none; margin: 0; padding: 0; border: none; border-top: 8px solid #4f46e5 !important; max-width: 100%; width: 100%; }
            .btn-area { display: none; } 
            
            .kop-wrapper { padding-bottom: 10px !important; margin-bottom: 15px !important; }
            .kop-logos img { max-height: 60px !important; }
            .kop-text h2 { font-size: 20px !important; }
            .kop-text p { font-size: 12px !important; }
            .no-reg { font-size: 18px !important; margin: 5px 0 10px 0 !important; padding: 6px !important;}
            
            .box-status { padding: 8px !important; margin-bottom: 15px !important; }
            .box-status h3 { font-size: 16px !important; }
            .box-status span { font-size: 12px !important; }
            
            .grid-container { gap: 20px !important; }
            .main-table td { padding: 6px 4px !important; font-size: 12px !important; line-height: 1.3 !important; }
            .section-title { font-size: 13px !important; margin-bottom: 5px !important; }
            .box-nilai { padding: 8px !important; }
            .tabel-rincian td { padding: 2px 0 !important; font-size: 11.5px !important; }
            .box-hasil-akhir { padding: 8px !important; }
            .text-hasil-akhir { font-size: 16px !important; }
        }
    </style>
</head>
<body>

<div class="card-bukti">
    <div class="kop-wrapper">
        <div class="kop-logos">
            <img src="logo/logopb.jpg" alt="Logo Yayasan">
            <img src="logo/logopemda.png" alt="Logo Pemda">
            <img src="logo/logosmkpb.png" alt="Logo SMK">
        </div>
        <div class="kop-text">
            <h2>TANDA BUKTI PENDAFTARAN SPMB</h2>
            <p>SMKS PERMATA BUNDA I JAKARTA</p>
            <p style="font-weight: 800; color: #059669;">Tahun Ajaran 2026/2027 (Jalur: <?php echo htmlspecialchars($label_gel_bukti, ENT_QUOTES, 'UTF-8'); ?>)</p>
        </div>
    </div>
    
    <center style="font-size: 12px; font-weight:bold; color: #475569; text-transform: uppercase;">Nomor Registrasi Sistem</center>
    <div class="no-reg">
        <?php echo htmlspecialchars($data['no_pendaftaran'], ENT_QUOTES, 'UTF-8'); ?>
    </div>

    <div class="box-status" style="background: <?php echo $status_warna_bg; ?>; border-color: <?php echo $status_border; ?>; color: <?php echo $status_warna_teks; ?>;">
        <h3><?php echo $status_teks; ?></h3>
        <span><?php echo $pesan_tambahan; ?></span>
        <?php if (!empty($data['catatan_panitia'])): ?>
            <span style="background: #f6f4f3; padding: 5px; border-radius: 7px; margin-top: 5px; display: inline-block;">📋 Catatan: <?php echo $catatan_panitia; ?></span>
        <?php endif; ?>
    </div>

    <!-- GRID LAYOUT KIRI DAN KANAN -->
    <div class="grid-container">
        
        <!-- KOLOM KIRI: IDENTITAS -->
        <div class="col-kiri">
            <h4 class="section-title">A. Identitas Diri & Keluarga</h4>
            <table class="main-table">
                <tr><td>Nama Lengkap</td><td>: <b><?php echo htmlspecialchars($data['nama_lengkap'], ENT_QUOTES, 'UTF-8'); ?></b></td></tr>
                <tr><td>Jenis Kelamin</td><td>: <?php echo htmlspecialchars($jk, ENT_QUOTES, 'UTF-8'); ?></td></tr>
                <tr><td>Agama</td><td>: <?php echo htmlspecialchars($agama, ENT_QUOTES, 'UTF-8'); ?></td></tr>
                <tr><td>Tempat, Tgl Lahir</td><td>: <?php echo htmlspecialchars($data['tempat_lahir'], ENT_QUOTES, 'UTF-8') . ", " . tgl_indo($data['tanggal_lahir']); ?></td></tr>
                
                <tr><td>NIK Siswa</td><td>: <?php echo htmlspecialchars($data['nik'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
                <tr><td>Nomor KK</td><td>: <?php echo htmlspecialchars($data['no_kk'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
                <tr><td>Tgl. Terbit KK</td><td>: <?php echo tgl_indo($tgl_kk); ?></td></tr>
                
                <tr><td>Nama Ibu Kandung</td><td>: <?php echo htmlspecialchars($nama_ibu, ENT_QUOTES, 'UTF-8'); ?></td></tr>
                <tr><td>No. WhatsApp (Aktif)</td><td>: <?php echo htmlspecialchars($data['no_whatsapp'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
                
                <tr><td>Alamat Domisili</td><td>: <?php echo htmlspecialchars($data['alamat'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
                <tr><td>Kelurahan / Desa</td><td>: <?php echo htmlspecialchars($data['kelurahan'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
                <tr><td>Kecamatan</td><td>: <?php echo htmlspecialchars($data['kecamatan'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
                
                <tr>
                    <td>Riwayat Penyakit</td>
                    <td>: <span style="color:#dc2626; font-weight:bold;"><?php echo htmlspecialchars($data['riwayat_penyakit'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                </tr>
                <tr>
                    <td>Kebutuhan Khusus</td>
                    <td>: <span style="color:#d97706; font-weight:bold;"><?php echo htmlspecialchars($kb_khusus, ENT_QUOTES, 'UTF-8'); ?></span></td>
                </tr>
            </table>
        </div>

        <!-- KOLOM KANAN: AKADEMIK & BERKAS -->
        <div class="col-kanan">
            <h4 class="section-title">B. Data Akademik & Jurusan</h4>
            <table class="main-table">
                <tr><td>Asal Sekolah</td><td>: <b><?php echo htmlspecialchars($data['asal_sekolah'], ENT_QUOTES, 'UTF-8'); ?></b></td></tr>
                <tr><td>NPSN Sekolah</td><td>: <?php echo htmlspecialchars($npsn, ENT_QUOTES, 'UTF-8'); ?></td></tr>
                <tr><td>NISN</td><td>: <?php echo htmlspecialchars($data['nisn'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
                <tr><td>Nomor Sidanira</td><td>: <?php echo htmlspecialchars($data['no_ijazah'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
                <tr><td>Pilihan Konsentrasi</td><td>: <b style="color:#4f46e5;"><?php echo $jrs; ?></b></td></tr>
                
                <tr>
                    <td colspan="2">
                        <div class="box-nilai">
                            <table class="tabel-rincian">
                                <tr>
                                    <td style="width: 50%;">Sidanira Asli : <span class="nilai-asli"><?php echo number_format($asli_skl, 2); ?></span></td>
                                    <td style="color: #64748b;">&rarr; Bobot 70% = <span class="nilai-bobot"><?php echo number_format($bobot_skl, 2); ?></span></td>
                                </tr>
                                <tr>
                                    <td>TKA Asli &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span class="nilai-asli"><?php echo number_format($asli_tka, 2); ?></span></td>
                                    <td style="color: #64748b;">&rarr; Bobot 30% = <span class="nilai-bobot"><?php echo number_format($bobot_tka, 2); ?></span></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="garis-batas">
                                        <div style="margin-bottom: 4px; color: #475569;">Hasil Nilai Berkas Asli (Rata-rata) : <b><?php echo number_format($nilai_berkas_asli, 2); ?></b></div>
                                    </td>
                                </tr>
                            </table>
                            <div class="box-hasil-akhir">
                                <span style="font-size: 11px; color: #4b5563; font-weight: 600;">NILAI SELEKSI AKHIR BERBOBOT</span>
                                <?php if ($nilai_berkas_bobot > 0): ?>
                                    <span class="text-hasil-akhir"><?php echo number_format($nilai_berkas_bobot, 2); ?></span>
                                <?php else: ?>
                                    <span class="text-hasil-akhir" style="color: #b45309; font-size:14px;">⏳ Menunggu</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <h4 class="section-title" style="margin-top: 20px;">C. Status Berkas Digital</h4>
            <table class="main-table">
                <?php if ($data['status_kjp'] == 'Ya'): ?>
                    <tr><td>Nomor Tabungan KJP</td><td>: <?php echo htmlspecialchars($data['no_rek_kjp'], ENT_QUOTES, 'UTF-8'); ?> <span class="check-icon">✓ Ada</span></td></tr>
                <?php endif; ?>
                <tr><td>Kelengkapan Dasar</td><td>: <span class="check-icon">✓ Ijazah/Sidanira</span> <span class="check-icon">✓ TKA/SKHU</span></td></tr>
                <tr><td>Kelengkapan Kependudukan</td><td>: <span class="check-icon">✓ Kartu Keluarga</span> <span class="check-icon">✓ Akta Lahir</span> <span class="check-icon">✓ KTP Orang Tua</span></td></tr>
                <tr><td>Pakta Integritas</td><td>: <span class="check-icon">✓ SPTJM Bermeterai</span></td></tr>
                <tr><td>Waktu Pendaftaran</td><td>: <b style="color:#475569;"><?php echo tgl_indo($data['tanggal_daftar']); ?></b></td></tr>
            </table>
        </div>
    </div> <!-- End Grid Container -->

    <div class="btn-area">
        <button onclick="window.print();" class="btn-print">🖨️ Cetak / Simpan PDF (Landscape)</button>
        <a href="index.php" class="btn-next">Kembali ke Beranda ➔</a>
    </div>
</div>

</body>
</html>