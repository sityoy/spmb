<?php
include 'koneksi.php';

if (!isset($_GET['no_pendaftaran'])) {
    header("Location: index.php");
    exit;
}

$no_daftar = trim($_GET['no_pendaftaran']); 

// Pencarian data menggunakan TRIM dan Proteksi SQL Injection
$no_daftar_clean = mysqli_real_escape_string($conn, $no_daftar);
$query = "SELECT * FROM pendaftar WHERE TRIM(no_pendaftaran) = TRIM('$no_daftar_clean')";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) { 
    die("Data dengan nomor pendaftaran <b>" . htmlspecialchars($no_daftar) . "</b> tidak ditemukan di database. Pastikan format nomor benar."); 
}

$jrs = ($data['pilihan_jurusan'] == "Akuntansi dan Keuangan Lembaga") ? "Akuntansi dan Keuangan Lembaga (AKL)" : "Manajemen Perkantoran dan Layanan Bisnis (MPLB)";

// ==============================================================
// LOGIKA PERHITUNGAN NILAI ASLI DAN BOBOT (/2)
// ==============================================================
$asli_skl = (float)$data['nilai_skl'];
$asli_tka = (float)$data['nilai_tka'];

// 1. Hitung Nilai Berkas Asli (Rata-rata Murni)
$nilai_berkas_asli = ($asli_skl + $asli_tka) / 2;

// 2. Hitung Bobot 70% dan 30%
$bobot_skl = $asli_skl * 0.70; 
$bobot_tka = $asli_tka * 0.30; 

// 3. Nilai Berkas Berbobot (Sesuai rumus: dibagi 2)
$nilai_berkas_bobot = ($bobot_skl + $bobot_tka) / 2;

// 4. Nilai Test & Nilai Akhir Total
$nilai_test = (float)$data['nilai_test'];
$nilai_akhir_total = ($nilai_berkas_bobot + $nilai_test) / 2;

// FUNGSI FORMAT TANGGAL INDONESIA
function tgl_indo($tanggal) {
    if (empty($tanggal) || $tanggal == '0000-00-00 00:00:00' || $tanggal == '0000-00-00') {
        return "-";
    }
    
    $pecah_waktu = explode(' ', $tanggal);
    $hanya_tanggal = $pecah_waktu[0];
    
    $bulan_indo = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
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
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        
        body { font-family: 'Plus Jakarta Sans', Arial, sans-serif; color: #1f2937; background: #f3f4f6; margin: 0; padding: 20px; }
        .card-bukti { max-width: 700px; background: #fff; margin: 20px auto; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border-top: 8px solid #4f46e5; }
        .kop { text-align: center; border-bottom: 2px dashed #cbd5e1; padding-bottom: 20px; margin-bottom: 25px; }
        .kop h2 { margin: 0; color: #4f46e5; font-size: 22px; font-weight: 800; }
        .kop p { margin: 6px 0 0 0; font-size: 14px; color: #4b5563; }
        .no-reg { text-align: center; font-size: 24px; font-weight: 800; color: #0f172a; margin: 15px 0 25px 0; letter-spacing: 1.5px; background:#f8fafc; padding:10px; border-radius:8px; border:1px solid #e2e8f0; }
        
        .main-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .main-table td { padding: 10px; border-bottom: 1px solid #f1f5f9; font-size: 14px; vertical-align: top; }
        .main-table td:first-child { font-weight: 600; color: #475569; width: 35%; }
        
        /* Area Desain Nilai Transparan */
        .box-nilai { background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 13px; line-height: 1.6; }
        .tabel-rincian { width: 100%; border-collapse: collapse; font-size: 13px; }
        .tabel-rincian td { padding: 5px 0; border: none; }
        .nilai-asli { font-weight: 700; color: #1e293b; }
        .nilai-bobot { font-weight: 800; color: #0284c7; }
        .garis-batas { border-top: 1px dashed #cbd5e1 !important; margin-top: 8px; padding-top: 8px !important; }
        
        .badge-tunggu { background: #fef3c7; color: #b45309; padding: 6px 10px; border-radius: 6px; font-size: 12.5px; font-weight: 600; font-style: italic; display: inline-block; }
        .badge-nilai { background: #ecfdf5; color: #059669; padding: 6px 12px; border-radius: 6px; font-size: 14px; font-weight: 800; border: 1px solid #10b981; display: inline-block;}
        
        /* Box Hasil Akhir Total */
        .box-hasil-akhir { background: #eef2ff; border: 1.5px solid #c7d2fe; padding: 15px; border-radius: 8px; margin-top: 5px; }
        .text-hasil-akhir { color: #4f46e5; font-size: 18px; font-weight: 800; display: block; margin-top: 4px; }
        
        .btn-area { text-align: center; margin-top: 35px; display: flex; gap: 15px; justify-content: center; }
        .btn-print { padding: 12px 24px; background: #4f46e5; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; text-decoration: none; font-size: 14px; transition: 0.2s;}
        .btn-print:hover { background: #4338ca; }
        .btn-next { padding: 12px 24px; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; text-decoration: none; font-size: 14px; transition: 0.2s;}
        .btn-next:hover { background: #059669; }
        .check-icon { color: #059669; font-weight: bold; background: #d1fae5; padding: 2px 8px; border-radius: 4px; font-size: 12px; }
        
        @media print {
            body { background: #fff; padding: 0; }
            .card-bukti { box-shadow: none; margin: 0; padding: 0; border: none; max-width: 100%; }
            .btn-area { display: none; }
            .box-nilai, .box-hasil-akhir { border: 1px solid #000; background: transparent; }
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
        <p style="font-weight: 800; color: #059669;">Tahun Ajaran 2026/2027 (Jalur: <?php echo htmlspecialchars($data['gelombang'], ENT_QUOTES, 'UTF-8'); ?>)</p>
    </div>
<center>Nomor Pendaftaran:</center>
    <div class="no-reg">
        
        <?php echo htmlspecialchars($data['no_pendaftaran'], ENT_QUOTES, 'UTF-8'); ?></div>

    <table class="main-table">
        <tr><td>Nama Lengkap</td><td>: <?php echo htmlspecialchars($data['nama_lengkap'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
        <tr><td>Nomor Kartu Keluarga (KK DKI Jakarta)</td><td>: <?php echo htmlspecialchars($data['no_kk'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
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
                            <td style="color: #64748b; font-size: 12px;">&rarr; Bobot 70% = <span class="nilai-bobot"><?php echo number_format($bobot_skl, 2); ?></span></td>
                        </tr>
                        <tr>
                            <td>TKA Asli &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span class="nilai-asli"><?php echo number_format($asli_tka, 2); ?></span></td>
                            <td style="color: #64748b; font-size: 12px;">&rarr; Bobot 30% = <span class="nilai-bobot"><?php echo number_format($bobot_tka, 2); ?></span></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="garis-batas">
                                <div style="margin-bottom: 6px; color: #475569;">Hasil Nilai Berkas Asli (Rata-rata) : <b><?php echo number_format($nilai_berkas_asli, 2); ?></b></div>
                                <strong style="color: #0f172a; font-size: 14px;">Nilai Berkas Berbobot (Dibagi 2) : <span style="color:#4f46e5;"><?php echo number_format($nilai_berkas_bobot, 2); ?></span></strong>
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
                    <span class="badge-tunggu">⏳ Menunggu (Akan diumumkan panitia)</span>
                <?php endif; ?>
            </td>
        </tr>

        <tr>
            <td style="vertical-align: middle;"><strong>Hasil Akhir Nilai</strong></td>
            <td>
                <div class="box-hasil-akhir">
                    <span style="font-size: 12px; color: #4b5563; font-weight: 600;">(Nilai Berkas Berbobot) + Nilai Ujian (Test Panitia) / 2</span>
                    <?php if ($nilai_test > 0): ?>
                        <span class="text-hasil-akhir">= <?php echo number_format($nilai_akhir_total, 2); ?></span>
                    <?php else: ?>
                        <span class="text-hasil-akhir" style="color: #b45309; font-size: 15px;">= ⏳ Menunggu Nilai Ujian</span>
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