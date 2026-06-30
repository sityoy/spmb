<?php
// ==========================================
// SECURITY LAYER: SECURE SESSION START
// ==========================================
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_use_only_cookies', 1);
    session_start();
}

// Pastikan hanya admin/panitia yang bisa akses halaman kolektif ini
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

// Ambil parameter filter gelombang (jika ada lemparan dari halaman admin)
$gel_aktif = isset($_GET['gel']) ? $_GET['gel'] : 'Semua';
$sql_gel_filter = ($gel_aktif == 'Semua') ? "" : " AND gelombang = '$gel_aktif'";

// Ambil HANYA siswa yang berstatus 'Jadi' (Lulus)
$query = "SELECT * FROM pendaftar WHERE status_konfirmasi = 'LULUS' $sql_gel_filter ORDER BY pilihan_jurusan ASC, nama_lengkap ASC";
$result = mysqli_query($conn, $query);

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
    <title>Cetak Kolektif - Bukti Kelulusan SPMB</title>
    <link rel="icon" type="image/x-icon" href="logo/logosmkpb.png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        
        body { font-family: 'Plus Jakarta Sans', Arial, sans-serif; color: #1f2937; background: #525659; margin: 0; padding: 20px 0; }
        
        /* Tombol Print Mengambang */
        .floating-btn-area {
            position: fixed; top: 15px; left: 50%; transform: translateX(-50%);
            background: rgba(255,255,255,0.9); padding: 10px 20px; border-radius: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2); z-index: 1000;
            display: flex; gap: 10px; align-items: center;
        }
        .btn-print-action { background: #4f46e5; color: white; border: none; padding: 10px 20px; border-radius: 20px; font-weight: bold; cursor: pointer; font-family: inherit;}
        .btn-print-action:hover { background: #4338ca; }
        .text-info-print { font-size: 14px; font-weight: bold; color: #333;}

        /* Kotak Halaman A4 */
        .page {
            width: 21cm;
            min-height: 29.7cm; /* Ukuran A4 */
            background: #fff;
            margin: 0 auto 30px auto;
            padding: 1.5cm 2cm;
            box-sizing: border-box;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            border-top: 8px solid #4f46e5;
            position: relative;
            /* PENTING: Memaksa ganti kertas baru per siswa saat dicetak */
            page-break-after: always; 
        }

        /* --- STYLING PERSIS SEPERTI DI BUKTI.PHP --- */
        .kop { text-align: center; border-bottom: 2px dashed #cbd5e1; padding-bottom: 15px; margin-bottom: 15px; }
        .kop img { max-width: 100%; height: auto; }
        .kop h2 { margin: 0; color: #4f46e5; font-size: 20px; font-weight: 800; }
        .kop p { margin: 4px 0 0 0; font-size: 13px; color: #4b5563; }
        .no-reg { text-align: center; font-size: 20px; font-weight: 800; color: #0f172a; margin: 10px 0 15px 0; letter-spacing: 1.5px; background:#f8fafc; padding:8px; border-radius:8px; border:1px solid #e2e8f0; word-break: break-all; }
        
        .main-table { width: 100%; border-collapse: collapse; margin-top: 5px; table-layout: auto; }
        .main-table td { padding: 6px 10px; border-bottom: 1px solid #f1f5f9; font-size: 12px; vertical-align: top; word-wrap: break-word; }
        .main-table td:first-child { font-weight: 600; color: #475569; width: 35%; }
        
        .box-nilai { background: #f8fafc; padding: 10px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 11.5px; line-height: 1.5; }
        .tabel-rincian { width: 100%; border-collapse: collapse; font-size: 11.5px; }
        .tabel-rincian td { padding: 3px 0; border: none; }
        .nilai-asli { font-weight: 700; color: #1e293b; }
        .nilai-bobot { font-weight: 800; color: #0284c7; }
        .garis-batas { border-top: 1px dashed #cbd5e1 !important; margin-top: 6px; padding-top: 6px !important; }
        
        .badge-tunggu { background: #fef3c7; color: #b45309; padding: 4px 8px; border-radius: 4px; font-size: 11.5px; font-weight: 600; font-style: italic; display: inline-block; }
        .badge-nilai { background: #ecfdf5; color: #059669; padding: 4px 10px; border-radius: 4px; font-size: 12.5px; font-weight: 800; border: 1px solid #10b981; display: inline-block;}
        
        .box-hasil-akhir { background: #eef2ff; border: 1.5px solid #c7d2fe; padding: 10px; border-radius: 8px; margin-top: 4px; }
        .text-hasil-akhir { color: #4f46e5; font-size: 16px; font-weight: 800; display: block; margin-top: 4px; }
        
        .check-icon { color: #059669; font-weight: bold; background: #d1fae5; padding: 2px 6px; border-radius: 4px; font-size: 11px; display: inline-block; margin-top: 2px;}

        /* CSS KHUSUS SAAT MODE PRINT AKTIF */
        @media print {
            body { background: #fff; padding: 0; margin: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .floating-btn-area { display: none; }
            .page { 
                margin: 0; padding: 1.5cm 2cm; border: none; box-shadow: none; width: 100%; height: auto;
                border-top: 8px solid #4f46e5 !important;
            }
        }
    </style>
</head>
<body>

    <div class="floating-btn-area">
        <span class="text-info-print">Ada <?php echo mysqli_num_rows($result); ?> Siswa Lulus</span>
        <button onclick="window.print()" class="btn-print-action">🖨️ Cetak Semua Bukti</button>
    </div>

    <?php 
    if (mysqli_num_rows($result) > 0) {
        while ($data = mysqli_fetch_assoc($result)) {
            // Kalkulasi Nilai
            $jrs = ($data['pilihan_jurusan'] == "Akuntansi dan Keuangan Lembaga") ? "Akuntansi dan Keuangan Lembaga (AKL)" : "Manajemen Perkantoran dan Layanan Bisnis (MPLB)";

            $asli_skl = (float)$data['nilai_skl'];
            $asli_tka = (float)$data['nilai_tka'];
            $nilai_berkas_asli = ($asli_skl + $asli_tka) / 2;
            $bobot_skl = $asli_skl * 0.70; 
            $bobot_tka = $asli_tka * 0.30; 
            $nilai_berkas_bobot = $bobot_skl + $bobot_tka;
            // $nilai_test = (float)$data['nilai_test'];
            // $nilai_akhir_total = ($nilai_berkas_bobot + $nilai_test) / 2;
            $nilai_akhir_total = $nilai_berkas_bobot;
            
    ?>
    
    <div class="page">
        <div class="kop">
            <img src="logo/logopb.jpg" alt="Logo Yayasan Permata Bunda" style="max-height: 80px; width: auto; margin-bottom: 10px;">
            <img src="logo/logopemda.png" alt="Logo Pemda DKI" style="max-height: 85px; width: auto; margin-bottom: 10px;">
            <img src="logo/logosmkpb.png" alt="Logo SMK PB1" style="max-height: 90px; width: auto; margin-bottom: 10px;">
            <h2>TANDA BUKTI PENDAFTARAN SPMB</h2>
            <p>SMKS PERMATA BUNDA I JAKARTA</p>
            <p style="font-weight: 800; color: #059669;">Tahun Ajaran 2026/2027 (Jalur: <?php echo htmlspecialchars($data['gelombang'], ENT_QUOTES, 'UTF-8'); ?>)</p>
        </div>
        
        <center style="font-size: 12px; font-weight:bold; color: #475569;">Nomor Pendaftaran:</center>
        <div class="no-reg">
            <?php echo htmlspecialchars($data['no_pendaftaran'], ENT_QUOTES, 'UTF-8'); ?>
        </div>

        <table class="main-table">
            <tr><td>Nama Lengkap</td><td>: <?php echo htmlspecialchars($data['nama_lengkap'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
            <!-- <tr><td>Nomor Kartu Keluarga (KK DKI)</td><td>: <?php echo htmlspecialchars($data['no_kk'], ENT_QUOTES, 'UTF-8'); ?></td></tr> -->
            <tr><td>Tempat, Tgl Lahir</td><td>: <?php echo htmlspecialchars($data['tempat_lahir'], ENT_QUOTES, 'UTF-8') . ", " . tgl_indo($data['tanggal_lahir']); ?></td></tr>
            <tr><td>NISN</td><td>: <?php echo htmlspecialchars($data['nisn'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
            <tr><td>Nomor Ijazah / SKL</td><td>: <?php echo htmlspecialchars($data['no_ijazah'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
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
            
            <!-- <tr>
                <td>Nilai Ujian (Test Panitia)</td>
                <td> 
                    <?php if ($nilai_test > 0): ?>
                        <span class="badge-nilai"><?php echo number_format($nilai_test, 2); ?></span>
                    <?php else: ?>
                        <span class="badge-tunggu">⏳ Menunggu</span>
                    <?php endif; ?>
                </td>
            </tr> -->

            <tr>
                <td style="vertical-align: middle;"><strong>Hasil Akhir Nilai</strong></td>
                <td>
                    <div class="box-hasil-akhir">
                        <span style="font-size: 9px; color: #4b5563; font-weight: 600;">Nilai Seleksi Akhir</span>
                        <?php if ($nilai_akhir_total > 0): ?>
                            <span class="text-hasil-akhir">= <?php echo number_format($nilai_akhir_total, 2); ?></span>
                        <?php else: ?>
                            <span class="text-hasil-akhir" style="color: #b45309;">= ⏳ Menunggu</span>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>

            <?php if ($data['status_kjp'] == 'Ya'): ?>
                <tr><td>Nomor Rekening KJP</td><td>: <?php echo htmlspecialchars($data['no_rek_kjp'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
                <tr><td>Lampiran Buku Tabungan KJP</td><td>: <span class="check-icon">✓ Terunggah</span></td></tr>
            <?php endif; ?>

            <tr><td>Lampiran Scan Ijazah / Sidanira</td><td>: <span class="check-icon">✓ Terunggah</span></td></tr>
            <tr><td>Lampiran Scan TKA</td><td>: <span class="check-icon">✓ Terunggah</span></td></tr>
            <tr><td>Lampiran Scan KK</td><td>: <span class="check-icon">✓ Terunggah</span></td></tr>
            <tr><td>Lampiran Scan Akte</td><td>: <span class="check-icon">✓ Terunggah</span></td></tr>
            <tr><td>Lampiran Scan KTP Bapak</td><td>: <span class="check-icon">✓ Terunggah</span></td></tr>
            <tr><td>Lampiran Scan KTP Ibu</td><td>: <span class="check-icon">✓ Terunggah</span></td></tr>
            <tr><td>Lampiran Scan SPTJM Bermeterai</td><td>: <span class="check-icon">✓ Terunggah</span></td></tr>
            
            <tr><td>Tanggal Daftar / Submit</td><td>: <b style="color:#475569;"><?php echo tgl_indo($data['tanggal_daftar']); ?></b></td></tr>
        </table>
        
        <div style="position:absolute; bottom:50px; right:50px; border:4px solid #10b981; border-radius:12px; padding:15px 30px; text-align:center; transform: rotate(-5deg); opacity:0.8;">
            <div style="font-size: 28px; font-weight: 900; color: #10b981; letter-spacing: 3px; margin-bottom:5px;">LULUS SELEKSI</div>
            <div style="font-size: 14px; font-weight: bold; color: #065f46;">SMK PERMATA BUNDA 1</div>
        </div>
    </div>

    <?php 
        } 
    } else {
        echo "<div class='page' style='display:flex; justify-content:center; align-items:center; height:100vh;'><h2 style='color:#ef4444;'>Tidak ada data siswa LULUS pada gelombang ini.</h2></div>";
    }
    ?>

</body>
</html>