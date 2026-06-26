<?php
session_start();
include 'koneksi.php';

if (!isset($_GET['no_pendaftaran'])) {
    header("Location: index.php");
    exit;
}

$no_daftar = trim($_GET['no_pendaftaran']); 
$no_daftar_clean = mysqli_real_escape_string($conn, $no_daftar);

// Ambil data dari database dulu
$query = "SELECT * FROM pendaftar WHERE TRIM(no_pendaftaran) = TRIM('$no_daftar_clean')";
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
} elseif ($status_db == 'Tidak Jadi') {
    $status_teks = "TIDAK LULUS / GUGUR";
    $status_warna_bg = "#fee2e2";
    $status_warna_teks = "#991b1b";
    $status_border = "#ef4444";
    $alasan = !empty($data['alasan_pembatalan']) ? htmlspecialchars($data['alasan_pembatalan']) : "Mohon maaf, Anda belum dapat diterima pada seleksi kali ini.";
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

$asli_skl = (float)$data['nilai_skl'];
$asli_tka = (float)$data['nilai_tka'];
$nilai_berkas_asli = ($asli_skl + $asli_tka) / 2;
$bobot_skl = $asli_skl * 0.70; 
$bobot_tka = $asli_tka * 0.30; 
$nilai_berkas_bobot = ($bobot_skl + $bobot_tka) / 2;
$nilai_test = (float)$data['nilai_test'];
$nilai_akhir_total = ($nilai_berkas_bobot + $nilai_test) / 2;

function tgl_indo($tanggal) {
    if (empty($tanggal) || $tanggal == '0000-00-00 00:00:00' || $tanggal == '0000-00-00') { return "-"; }
    $pecah_waktu = explode(' ', $tanggal);
    $hanya_tanggal = $pecah_waktu[0];
    $bulan_indo = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $split = explode('-', $hanya_tanggal);
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
        .card-bukti { max-width: 700px; background: #fff; margin: 0 auto; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border-top: 8px solid #4f46e5; box-sizing: border-box; }
        .kop { text-align: center; border-bottom: 2px dashed #cbd5e1; padding-bottom: 20px; margin-bottom: 25px; }
        .kop img { max-width: 100%; height: auto; }
        .kop h2 { margin: 0; color: #4f46e5; font-size: 22px; font-weight: 800; }
        .kop p { margin: 6px 0 0 0; font-size: 14px; color: #4b5563; }
        .no-reg { text-align: center; font-size: 24px; font-weight: 800; color: #0f172a; margin: 15px 0 20px 0; letter-spacing: 1.5px; background:#f8fafc; padding:10px; border-radius:8px; border:1px solid #e2e8f0; word-break: break-all; }
        
        /* Kotak Status */
        .box-status { text-align: center; padding: 15px; border-radius: 8px; margin-bottom: 25px; border-width: 2px; border-style: solid; }
        .box-status h3 { margin: 0 0 5px 0; font-size: 22px; font-weight: 800; letter-spacing: 1px; }
        .box-status span { font-size: 14px; display: block; }

        .main-table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: auto; }
        .main-table td { padding: 10px; border-bottom: 1px solid #f1f5f9; font-size: 14px; vertical-align: top; word-wrap: break-word; }
        .main-table td:first-child { font-weight: 600; color: #475569; width: 35%; }
        
        .box-nilai { background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 13px; line-height: 1.6; }
        .tabel-rincian { width: 100%; border-collapse: collapse; font-size: 13px; }
        .tabel-rincian td { padding: 5px 0; border: none; }
        .nilai-asli { font-weight: 700; color: #1e293b; }
        .nilai-bobot { font-weight: 800; color: #0284c7; }
        .garis-batas { border-top: 1px dashed #cbd5e1 !important; margin-top: 8px; padding-top: 8px !important; }
        
        .badge-tunggu { background: #fef3c7; color: #b45309; padding: 6px 10px; border-radius: 6px; font-size: 12.5px; font-weight: 600; font-style: italic; display: inline-block; }
        .badge-nilai { background: #ecfdf5; color: #059669; padding: 6px 12px; border-radius: 6px; font-size: 14px; font-weight: 800; border: 1px solid #10b981; display: inline-block;}
        
        .box-hasil-akhir { background: #eef2ff; border: 1.5px solid #c7d2fe; padding: 15px; border-radius: 8px; margin-top: 5px; }
        .text-hasil-akhir { color: #4f46e5; font-size: 18px; font-weight: 800; display: block; margin-top: 4px; }
        
        .btn-area { text-align: center; margin-top: 35px; display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; }
        .btn-print, .btn-next { padding: 12px 24px; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; text-decoration: none; font-size: 14px; transition: 0.2s; box-sizing: border-box;}
        .btn-print { background: #4f46e5; }
        .btn-print:hover { background: #4338ca; }
        .btn-next { background: #10b981; }
        .btn-next:hover { background: #059669; }
        .check-icon { color: #059669; font-weight: bold; background: #d1fae5; padding: 2px 8px; border-radius: 4px; font-size: 12px; display: inline-block; margin-top: 4px;}
        
        /* RESPONSIVE & PRINT SETTINGS */
        @media (max-width: 600px) {
            body { padding: 10px 5px; }
            .card-bukti { padding: 20px 15px; border-top-width: 6px; }
            .kop h2 { font-size: 18px; }
            .kop p { font-size: 12px; }
            .no-reg { font-size: 18px; padding: 8px; }
            .box-status h3 { font-size: 18px; }
            .box-status span { font-size: 12px; }
            .main-table td { display: block; width: 100% !important; padding: 6px 0; border: none; }
            .main-table tr { border-bottom: 1px solid #e2e8f0; display: block; padding: 8px 0; }
            .main-table tr:last-child { border-bottom: none; }
            .main-table td:first-child { font-size: 12px; color: #64748b; padding-bottom: 2px; } 
            .main-table td:last-child { font-size: 14px; font-weight: 500; color: #1e293b; padding-top: 0; }
            .tabel-rincian td { display: block; width: 100%; text-align: left; }
            .tabel-rincian tr { display: block; margin-bottom: 8px; border-bottom: 1px dotted #cbd5e1; padding-bottom: 8px;}
            .tabel-rincian tr:last-child { border-bottom: none; }
            .btn-area { flex-direction: column; gap: 10px; }
            .btn-print, .btn-next { width: 100%; }
        }

        @page { size: A4; margin: 0.5cm; }
        @media print {
            body { background: #fff; padding: 0; margin: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .card-bukti { box-shadow: none; margin: 0 auto; padding: 0; border: none; border-top: 8px solid #4f46e5 !important; max-width: 100%; width: 100%; }
            .btn-area { display: none; } 
            .kop { padding-bottom: 8px !important; margin-bottom: 10px !important; }
            .kop img { max-height: 55px !important; margin-bottom: 5px !important; }
            .kop h2 { font-size: 16px !important; }
            .kop p { font-size: 11px !important; margin-top: 2px !important; }
            .no-reg { font-size: 16px !important; margin: 8px 0 10px 0 !important; }
            .box-status { padding: 10px !important; margin-bottom: 15px !important; }
            .box-status h3 { font-size: 18px !important; }
            .main-table { width: 100%; }
            .main-table tr { display: table-row; } 
            .main-table td { display: table-cell; width: auto !important; border-bottom: 1px solid #f1f5f9; padding: 4px 5px !important; font-size: 11px !important; line-height: 1.3 !important; }
            .main-table td:first-child { width: 32% !important; font-size: 11px !important; color: #475569; }
            .box-nilai { padding: 6px 10px !important; border: 1px solid #cbd5e1 !important; }
            .tabel-rincian { font-size: 11px !important; }
            .tabel-rincian td { display: table-cell; padding: 2px 0 !important; }
            .tabel-rincian tr { display: table-row; margin-bottom: 0; padding-bottom: 0;}
            .box-hasil-akhir { padding: 6px 10px !important; border: 1px solid #c7d2fe !important; margin-top: 2px !important;}
            .text-hasil-akhir { font-size: 14px !important; margin-top: 2px !important; }
            .badge-tunggu { font-size: 10px !important; padding: 4px 6px !important;}
            .badge-nilai { font-size: 11px !important; padding: 4px 6px !important;}
            .check-icon { font-size: 10px !important; padding: 1px 4px !important; margin-top: 1px !important; }
            .garis-batas { margin-top: 4px !important; padding-top: 4px !important; }
        }
    </style>
</head>
<body>

<div class="card-bukti">
    <div class="kop">
        <img src="logo/logopb.jpg" alt="Logo Yayasan Permata Bunda" style="max-height: 97px; width: auto; margin-bottom: 15px;">
        <img src="logo/logopemda.png" alt="Logo Pemda DKI" style="max-height: 100px; width: auto; margin-bottom: 15px;">
        <img src="logo/logosmkpb.png" alt="Logo SMK PB1" style="max-height: 110px; width: auto; margin-bottom: 15px;">
        <h2>TANDA BUKTI PENDAFTARAN SPMB</h2>
        <p>SMKS PERMATA BUNDA I JAKARTA</p>
        <p style="font-weight: 800; color: #059669;">Tahun Ajaran 2026/2027 (Jalur: <?php echo htmlspecialchars($label_gel_bukti, ENT_QUOTES, 'UTF-8'); ?>)</p>
    </div>
    
    <center style="font-size: 13px; font-weight:bold; color: #475569;">Nomor Pendaftaran:</center>
    <div class="no-reg">
        <?php echo htmlspecialchars($data['no_pendaftaran'], ENT_QUOTES, 'UTF-8'); ?>
    </div>

    <div class="box-status" style="background: <?php echo $status_warna_bg; ?>; border-color: <?php echo $status_border; ?>; color: <?php echo $status_warna_teks; ?>;">
        <h3><?php echo $status_teks; ?></h3>
        <span><?php echo $pesan_tambahan; ?></span>
    </div>

    <table class="main-table">
        <tr><td>Nama Lengkap</td><td>: <?php echo htmlspecialchars($data['nama_lengkap'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
        <tr><td>Nomor Induk Kependudukan (NIK)</td><td>: <?php echo htmlspecialchars($data['nik'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
        <tr><td>Nomor Kartu Keluarga (KK DKI)</td><td>: <?php echo htmlspecialchars($data['no_kk'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
        <tr><td>Tempat, Tgl Lahir</td><td>: <?php echo htmlspecialchars($data['tempat_lahir'], ENT_QUOTES, 'UTF-8') . ", " . tgl_indo($data['tanggal_lahir']); ?></td></tr>
        <tr><td>NISN</td><td>: <?php echo htmlspecialchars($data['nisn'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
        <tr><td>Nomor Ijazah / SKL</td><td>: <?php echo htmlspecialchars($data['no_ijazah'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
        <tr><td>Alamat Domisili</td><td>: <?php echo htmlspecialchars($data['alamat'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
        <tr><td>Kelurahan / Desa</td><td>: <?php echo htmlspecialchars($data['kelurahan'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
        <tr><td>Kecamatan</td><td>: <?php echo htmlspecialchars($data['kecamatan'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
        <tr><td>Asal Sekolah</td><td>: <?php echo htmlspecialchars($data['asal_sekolah'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
        <tr><td>No. WhatsApp</td><td>: <?php echo htmlspecialchars($data['no_whatsapp'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
        <tr><td>Pilihan Jurusan</td><td>: <b style="color:#4f46e5;"><?php echo $jrs; ?></b></td></tr>
        
        <tr>
            <td>Riwayat Penyakit Khusus</td>
            <td>: <span style="color:#dc2626; font-weight:bold;"><?php echo htmlspecialchars($data['riwayat_penyakit'], ENT_QUOTES, 'UTF-8'); ?></span></td>
        </tr>

        <tr>
            <td>Rincian Evaluasi Nilai Berkas</td>
            <td>
                <div class="box-nilai">
                    <table class="tabel-rincian">
                        <tr>
                            <td style="width: 48%;">Sidanira Asli : <span class="nilai-asli"><?php echo number_format($asli_skl, 2); ?></span></td>
                            <td style="color: #64748b;">&rarr; Bobot 70% = <span class="nilai-bobot"><?php echo number_format($bobot_skl, 2); ?></span></td>
                        </tr>
                        <tr>
                            <td>TKA Asli &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span class="nilai-asli"><?php echo number_format($asli_tka, 2); ?></span></td>
                            <td style="color: #64748b;">&rarr; Bobot 30% = <span class="nilai-bobot"><?php echo number_format($bobot_tka, 2); ?></span></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="garis-batas">
                                <div style="margin-bottom: 4px; color: #475569;">Hasil Nilai Berkas Asli (Rata-rata) : <b><?php echo number_format($nilai_berkas_asli, 2); ?></b></div>
                                <strong style="color: #0f172a;">Nilai Berkas Berbobot (Dibagi 2) : <span style="color:#4f46e5;"><?php echo number_format($nilai_berkas_bobot, 2); ?></span></strong>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
        
        <tr>
            <td>Nilai Ujian (Test Panitia)</td>
            <td> 
                <?php if ($nilai_test > 0): ?>
                    <span class="badge-nilai"><?php echo number_format($nilai_test, 2); ?></span>
                <?php else: ?>
                    <span class="badge-tunggu">⏳ Menunggu</span>
                <?php endif; ?>
            </td>
        </tr>

        <tr>
            <td style="vertical-align: middle;"><strong>Hasil Akhir Nilai</strong></td>
            <td>
                <div class="box-hasil-akhir">
                    <span style="font-size: 10px; color: #4b5563; font-weight: 600;">(Nilai Berkas Berbobot) + Nilai Ujian (Test Panitia) / 2</span>
                    <?php if ($nilai_test > 0): ?>
                        <span class="text-hasil-akhir">= <?php echo number_format($nilai_akhir_total, 2); ?></span>
                    <?php else: ?>
                        <span class="text-hasil-akhir" style="color: #b45309;">= ⏳ Menunggu</span>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php if ($data['status_kjp'] == 'Ya'): ?>
            <tr><td>Nomor Rekening KJP</td><td>: <?php echo htmlspecialchars($data['no_rek_kjp'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
            <tr><td>Lampiran Buku Tabungan KJP</td><td>: <span class="check-icon">✓ Sudah Terunggah</span></td></tr>
        <?php endif; ?>

        <tr><td>Lampiran Scan Ijazah / Sidanira</td><td>: <span class="check-icon">✓ Sudah Terunggah</span></td></tr>
        <tr><td>Lampiran Scan TKA</td><td>: <span class="check-icon">✓ Sudah Terunggah</span></td></tr>
        <tr><td>Lampiran Scan KK</td><td>: <span class="check-icon">✓ Sudah Terunggah</span></td></tr>
        <tr><td>Lampiran Scan Akte</td><td>: <span class="check-icon">✓ Sudah Terunggah</span></td></tr>
        <tr><td>Lampiran Scan KTP Bapak</td><td>: <span class="check-icon">✓ Sudah Terunggah</span></td></tr>
        <tr><td>Lampiran Scan KTP Ibu</td><td>: <span class="check-icon">✓ Sudah Terunggah</span></td></tr>
        <tr><td>Lampiran Scan SPTJM Bermeterai</td><td>: <span class="check-icon">✓ Sudah Terunggah</span></td></tr>
        
        <tr><td>Tanggal Daftar / Submit</td><td>: <b style="color:#475569;"><?php echo tgl_indo($data['tanggal_daftar']); ?></b></td></tr>
    </table>

    <div class="btn-area">
        <button onclick="window.print();" class="btn-print">📥 Unduh / Cetak Bukti</button>
        <a href="index.php" class="btn-next">Kembali ke Portal Utama ➔</a>
    </div>
</div>

</body>
</html>