<?php
include 'koneksi.php';

$pesan = "";
$tampil_hasil = false;
$siswa_ditemukan = null;
$peringkat_siswa = 0;
$quota = 25; // default penampung kuota

// Fungsi bantu format tanggal Indonesia
function tgl_indo_pengumuman($tanggal) {
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

if (isset($_POST['cek'])) {
    $nisn = trim(mysqli_real_escape_string($conn, $_POST['nisn']));

    if (empty($nisn)) {
        $pesan = "<div class='alert alert-danger'>Silakan masukkan NISN Anda terlebih dahulu!</div>";
    } else {
        // Cari data pendaftar berdasarkan NISN
        $query_siswa = "SELECT * FROM pendaftar WHERE nisn = '$nisn'";
        $result_siswa = mysqli_query($conn, $query_siswa);

        if (mysqli_num_rows($result_siswa) === 1) {
            $siswa_ditemukan = mysqli_fetch_assoc($result_siswa);
            $gelombang_siswa = (int)$siswa_ditemukan['gelombang'];
            $jurusan_siswa   = $siswa_ditemukan['pilihan_jurusan'];
            $id_pendaftar    = $siswa_ditemukan['id'];
            
            $today = date('Y-m-d');
            $boleh_buka = false;
            $tgl_buka_pesan = "";

            // Validasi tanggal pembukaan pengumuman berdasarkan Gelombang pendaftar
            if ($gelombang_siswa == 1) {
                if ($today >= '2026-07-06') { $boleh_buka = true; }
                else { $tgl_buka_pesan = "6 Juli 2026"; }
            } elseif ($gelombang_siswa == 2) {
                if ($today >= '2026-07-10') { $boleh_buka = true; }
                else { $tgl_buka_pesan = "10 Juli 2026"; }
            }

            if (!$boleh_buka) {
                $pesan = "<div class='alert alert-danger'><b>Pengumuman Kelulusan Belum Dibuka!</b><br>Anda tercatat sebagai pendaftar <b>Gelombang $gelombang_siswa</b>. Hasil seleksi peringkat baru akan diumumkan resmi pada <b>$tgl_buka_pesan</b>.</div>";
            } else {
                $quota = ($gelombang_siswa == 1) ? 25 : 11;

                // Hitung posisi peringkat siswa tersinkronisasi penuh dengan Live Board
                $query_rank = "SELECT id, ((nilai_skl + nilai_tka) / 2) as nilai_akhir 
                               FROM pendaftar 
                               WHERE pilihan_jurusan = '$jurusan_siswa' AND gelombang = '$gelombang_siswa'
                               ORDER BY CASE status_konfirmasi 
                                   WHEN 'Jadi' THEN 1 
                                   WHEN 'Belum' THEN 2 
                                   WHEN 'Tidak Jadi' THEN 3 
                               END ASC, nilai_akhir DESC, tanggal_daftar ASC";
                $result_rank = mysqli_query($conn, $query_rank);

                $peringkat_siswa = 1;
                while ($row = mysqli_fetch_assoc($result_rank)) {
                    if ($row['id'] == $id_pendaftar) {
                        break; 
                    }
                    $peringkat_siswa++;
                }

                $tampil_hasil = true;
                $nilai_akhir = ($siswa_ditemukan['nilai_skl'] + $siswa_ditemukan['nilai_tka']) / 2;
                $singkatan_jurusan = ($jurusan_siswa == "Akuntansi dan Keuangan Lembaga") ? "AKL" : "MPLB";
            }
        } else {
            $pesan = "<div class='alert alert-danger'><b>Data Tidak Ditemukan!</b><br>NISN yang Anda masukkan tidak terdaftar dalam sistem kelulusan SPMB.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengumuman Hasil Seleksi Kelulusan SPMB</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #f1f5f9; color: #334155; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; padding: 15px; box-sizing: border-box; }
        .header { text-align: center; background: #4f46e5; color: white; padding: 25px 15px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .header h2 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .header h4 { margin: 5px 0 0 0; font-size: 13px; font-weight: 400; opacity: 0.9; }
        .tag-school { display: inline-block; background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 20px; font-size: 11px; margin-top: 10px; font-weight: 600; }
        
        .alert { padding: 12px 15px; border-radius: 8px; font-size: 13.5px; margin-bottom: 15px; line-height: 1.5; }
        .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        
        .btn { width: 100%; padding: 12px; background: #4f46e5; color: white; border: none; border-radius: 6px; font-weight: bold; font-size: 15px; cursor: pointer; }
        .btn:hover { background: #4338ca; }
        .btn-reprint { display: inline-block; padding: 10px 16px; background: #10b981; color: white; text-decoration: none; font-weight: bold; border-radius: 6px; font-size: 13px; margin-top: 15px; border: none; }
        .btn-reprint:hover { background: #059669; }
        .btn-secondary { display: block; text-align: center; margin-top: 20px; color: #4f46e5; text-decoration: none; font-size: 14px; font-weight: 600; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>HASIL SELEKSI AKHIR SISWA</h2>
        <h4>SMKS PERMATA BUNDA I JAKARTA</h4>
        <span class="tag-school">Pengumuman Resmi Berdasarkan Kuota Gelombang</span>
    </div>

    <?php if ($pesan != "") echo $pesan; ?>

    <form action="" method="POST" style="background:#fff; border:1px solid #e2e8f0; padding:20px; border-radius:12px; margin-bottom: 25px;">
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="text-align: center; display: block; font-size: 13.5px; font-weight: 600; margin-bottom: 8px; color: #475569;">Masukkan 10 Digit NISN Calon Siswa</label>
            <input type="text" name="nisn" inputmode="numeric" maxlength="10" placeholder="Contoh: 0081234567" required style="text-align: center; font-size: 16px; letter-spacing: 2px; padding: 12px; width: 100%; box-sizing: border-box; border: 1px solid #cbd5e1; border-radius: 6px;">
        </div>
        <button type="submit" name="cek" class="btn">Periksa Hasil Kelulusan</button>
    </form>

    <?php if ($tampil_hasil): ?>
        <div style="background:#fff; border:1px solid #cbd5e1; border-top: 6px solid #4f46e5; padding:20px; border-radius:12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
            <div style="text-align: center; border-bottom: 2px dashed #cbd5e1; padding-bottom: 12px; margin-bottom: 15px;">
                <h3 style="margin: 0; color: #334155; font-size: 15px;">RANGKUMAN HASIL PENDAFTAR</h3>
                <p style="margin: 4px 0 0 0; font-size: 12px; color: #64748b; font-weight: 600;">No. Registrasi: <?php echo $siswa_ditemukan['no_pendaftaran']; ?></p>
            </div>

            <table style="width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 20px;">
                <tr><td style="padding:7px; font-weight:600; color:#475569; width:40%; text-align:left; border-bottom:1px solid #f1f5f9;">Nama Lengkap</td><td style="padding:7px; text-align:left; border-bottom:1px solid #f1f5f9;">: <?php echo $siswa_ditemukan['nama_lengkap']; ?></td></tr>
                <tr><td style="padding:7px; font-weight:600; color:#475569; text-align:left; border-bottom:1px solid #f1f5f9;">Gelombang</td><td style="padding:7px; text-align:left; border-bottom:1px solid #f1f5f9;">: <b>Gelombang <?php echo $siswa_ditemukan['gelombang']; ?></b></td></tr>
                <tr><td style="padding:7px; font-weight:600; color:#475569; text-align:left; border-bottom:1px solid #f1f5f9;">Tempat, Tgl Lahir</td><td style="padding:7px; text-align:left; border-bottom:1px solid #f1f5f9;">: <?php echo $siswa_ditemukan['tempat_lahir'] . ", " . tgl_indo_pengumuman($siswa_ditemukan['tanggal_lahir']); ?></td></tr>
                <tr><td style="padding:7px; font-weight:600; color:#475569; text-align:left; border-bottom:1px solid #f1f5f9;">NISN</td><td style="padding:7px; text-align:left; border-bottom:1px solid #f1f5f9;">: <?php echo $siswa_ditemukan['nisn']; ?></td></tr>
                <tr><td style="padding:7px; font-weight:600; color:#475569; text-align:left; border-bottom:1px solid #f1f5f9;">No. Ijazah / Sidanira</td><td style="padding:7px; text-align:left; border-bottom:1px solid #f1f5f9;">: <?php echo $siswa_ditemukan['no_ijazah']; ?></td></tr>
                <tr><td style="padding:7px; font-weight:600; color:#475569; text-align:left; border-bottom:1px solid #f1f5f9;">Asal Sekolah</td><td style="padding:7px; text-align:left; border-bottom:1px solid #f1f5f9;">: <?php echo $siswa_ditemukan['asal_sekolah']; ?></td></tr>
                <tr><td style="padding:7px; font-weight:600; color:#475569; text-align:left; border-bottom:1px solid #f1f5f9;">Pilihan Jurusan</td><td style="padding:7px; text-align:left; border-bottom:1px solid #f1f5f9;">: <b><?php echo $singkatan_jurusan; ?></b></td></tr>
                <tr><td style="padding:7px; font-weight:600; color:#475569; text-align:left; border-bottom:1px solid #f1f5f9;">Nilai Akhir Seleksi</td><td style="padding:7px; text-align:left; border-bottom:1px solid #f1f5f9; font-weight:bold; color:#4f46e5;">: <?php echo number_format($nilai_akhir, 2); ?></td></tr>
            </table>

            <div style="text-align: center; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <span style="font-size: 12.5px; color: #64748b; display: block;">Hasil Peringkat Kelulusan Gelombang <?php echo $siswa_ditemukan['gelombang']; ?>:</span>
                <div style="font-size:26px; font-weight:800; color:#4f46e5; background:#e0e7ff; display:inline-block; padding:6px 20px; border-radius:12px; margin:10px 0;">Peringkat <?php echo $peringkat_siswa; ?></div>

                <?php if ($siswa_ditemukan['status_konfirmasi'] == 'Jadi' || ($siswa_ditemukan['status_konfirmasi'] == 'Belum' && $peringkat_siswa <= $quota)): ?>
                    <div class="alert alert-success" style="margin: 5px 0 0 0; font-weight: bold;">🎉 SELAMAT! ANDA MASUK KUOTA UTAMA SEKOLAH GRATIS</div>
                <?php elseif ($siswa_ditemukan['status_konfirmasi'] == 'Tidak Jadi'): ?>
                    <div class="alert alert-danger" style="margin: 5px 0 0 0; font-weight: bold; background: #f3f4f6; color: #4b5563; border-color: #e5e7eb;">❌ STATUS: BATAL / MENGUNDURKAN DIRI</div>
                <?php else: ?>
                    <div class="alert alert-danger" style="margin: 5px 0 0 0; font-weight: bold; background: #fff7ed; color: #c2410c; border-color: #fed7aa;">⏳ STATUS: ANTRIAN CADANGAN (URUTAN KE-<?php echo ($peringkat_siswa - $quota); ?>)</div>
                <?php endif; ?>

                <a href="bukti.php?no_pendaftaran=<?php echo $siswa_ditemukan['no_pendaftaran']; ?>" target="_blank" class="btn-reprint">🖨️ Cetak Bukti Pendaftaran</a>
            </div>
        </div>
    <?php endif; ?>

    <a href="index.php" class="btn-secondary">← Kembali ke Portal Utama</a>
</div>

</body>
</html>