<?php 
include 'koneksi.php';

// ===================================================================
// 1. PENGATURAN ZONA WAKTU & DETEKSI GELOMBANG OTOMATIS (ASIA/JAKARTA)
// ===================================================================
date_default_timezone_set('Asia/Jakarta');
$waktu_sekarang = time();

// TIMESTAMPS JADWAL GELOMBANG 1 (15 Juni 2026 s.d 30 Juni 2026)
$buka_g1  = strtotime('2026-06-15 06:00:00');
$tutup_g1 = strtotime('2026-06-30 23:59:59');

// TIMESTAMPS JADWAL GELOMBANG 2 (08 Juli 2026 s.d 09 Juli 2026)
$buka_g2  = strtotime('2026-07-08 06:00:00');
$tutup_g2 = strtotime('2026-07-09 23:59:59');

$pendaftaran_buka = false;
$gelombang_aktif  = "Pendaftaran Ditutup";
$gelombang_id     = 0;

if ($waktu_sekarang >= $buka_g1 && $waktu_sekarang <= $tutup_g1) {
    $pendaftaran_buka = true;
    $gelombang_aktif  = "Gelombang 1";
    $gelombang_id     = 1;
} elseif ($waktu_sekarang >= $buka_g2 && $waktu_sekarang <= $tutup_g2) {
    $pendaftaran_buka = true;
    $gelombang_aktif  = "Gelombang 2";
    $gelombang_id     = 2;
}


// ===================================================================
// 2. REGULASI BATAS KUOTA SISTEM (DINAMIS PER GELOMBANG)
// ===================================================================
$max_kuota = ($gelombang_id == 2) ? 11 : 25;


// ===================================================================
// 3. HITUNG KUOTA REAL-TIME DARI DATABASE BERDASARKAN GELOMBANG AKTIF
// ===================================================================
function hitungPendaftarGelombang($jurusan, $id_gel, $conn) {
    $q = mysqli_query($conn, "SELECT COUNT(*) as total FROM pendaftar WHERE pilihan_jurusan = '$jurusan' AND gelombang = '$id_gel'");
    return mysqli_fetch_assoc($q)['total'];
}

$total_akl  = hitungPendaftarGelombang('Akuntansi dan Keuangan Lembaga', $gelombang_id, $conn);
$total_mplb = hitungPendaftarGelombang('Manajemen Perkantoran dan Layanan Bisnis', $gelombang_id, $conn);

$pesan = "";

// ===================================================================
// 4. PROSES PENDAFTARAN (Dijalankan saat tombol Daftar diklik)
// ===================================================================
if (isset($_POST['daftar'])) {
    if (!$pendaftaran_buka) {
        die("Akses Ditolak: Pendaftaran saat ini sedang ditutup.");
    }

    $nama       = htmlspecialchars(strtoupper(trim(mysqli_real_escape_string($conn, $_POST['nama_lengkap']))));
    $tmpl_lahir = htmlspecialchars(ucwords(strtolower(trim(mysqli_real_escape_string($conn, $_POST['tempat_lahir'])))));
    $tgl_lahir  = trim(mysqli_real_escape_string($conn, $_POST['tanggal_lahir']));
    $nisn       = trim(mysqli_real_escape_string($conn, $_POST['nisn']));
    $no_ijazah  = trim(mysqli_real_escape_string($conn, $_POST['no_ijazah']));
    $asal       = htmlspecialchars(trim(mysqli_real_escape_string($conn, $_POST['asal_sekolah'])));
    $wa         = trim(mysqli_real_escape_string($conn, $_POST['no_whatsapp']));
    $jurusan    = trim(mysqli_real_escape_string($conn, $_POST['pilihan_jurusan']));
    $skl        = (float) trim(mysqli_real_escape_string($conn, $_POST['nilai_skl']));
    $tka        = (float) trim(mysqli_real_escape_string($conn, $_POST['nilai_tka']));
    $no_kk      = trim(mysqli_real_escape_string($conn, $_POST['no_kk']));

    $riwayat_penyakit = htmlspecialchars(trim(mysqli_real_escape_string($conn, $_POST['riwayat_penyakit'])));
    if (empty($riwayat_penyakit)) { $riwayat_penyakit = "Tidak Ada"; }
    
    $status_kjp = trim(mysqli_real_escape_string($conn, $_POST['status_kjp']));
    // Jika status KJP Ya, validasi panjang karakter (misal minimal 5 angka, maks 15)
    if ($status_kjp == 'Ya') {
        $no_rek_kjp = trim(mysqli_real_escape_string($conn, $_POST['no_rek_kjp']));
        
        if (strlen($no_rek_kjp) < 5 || strlen($no_rek_kjp) > 15) {
            $pesan = "<div class='alert alert-danger'><b>Pendaftaran Gagal!</b><br>Nomor rekening KJP tidak valid (minimal 5 angka, maksimal 15 angka).</div>";
            // Tambahkan penghentian proses jika tidak valid
        }
    }

    $cek_kuota_submit = hitungPendaftarGelombang($jurusan, $gelombang_id, $conn);

    $tanggal_lahir_obj = new DateTime($tgl_lahir);
    $hari_ini_obj      = new DateTime(); 
    $hitung_umur       = $hari_ini_obj->diff($tanggal_lahir_obj)->y;

    $max_file_size = 3145728; 
    $daftar_file = ['file_ijazah', 'file_tka', 'file_kk', 'file_akte', 'file_ktp_bapak', 'file_ktp_ibu', 'file_sptjm'];
    if ($status_kjp == 'Ya') { $daftar_file[] = 'file_tabungan_kjp'; }

    $file_terlalu_besar = false;
    $ekstensi_ilegal = false;
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];

    foreach ($daftar_file as $input_name) {
        if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] == 0) {
            if ($_FILES[$input_name]['size'] > $max_file_size) {
                $file_terlalu_besar = true;
            }
            $ext = strtolower(pathinfo($_FILES[$input_name]['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed_extensions)) {
                $ekstensi_ilegal = true;
            }
        }
    }

    // KODE BARU (Sudah mengecek NISN, Ijazah, No KK, dan No WhatsApp):
$cek_duplikat = "SELECT * FROM pendaftar WHERE no_ijazah = '$no_ijazah' OR nisn = '$nisn' OR no_kk = '$no_kk' OR no_whatsapp = '$wa'";
    $hasil_cek    = mysqli_query($conn, $cek_duplikat);

    if ($file_terlalu_besar) {
        $pesan = "<div class='alert alert-danger'><b>Pendaftaran Gagal!</b><br>Berkas melebihi batas maksimal 3MB.</div>";
    } elseif ($ekstensi_ilegal) {
        $pesan = "<div class='alert alert-danger'><b>Format Berkas Ilegal!</b><br>Sistem hanya menerima file gambar atau dokumen (.pdf).</div>";
    } elseif (mysqli_num_rows($hasil_cek) > 0) {
        $pesan = "<div class='alert alert-danger'><b>Pendaftaran Ditolak!</b><br>Maaf, NISN, Nomor Seri Ijazah, Nomor KK, atau Nomor WhatsApp Anda sudah pernah terdaftar di sistem kami.</div>";
    } elseif ($cek_kuota_submit >= $max_kuota) {
        $pesan = "<div class='alert alert-danger'><b>Pendaftaran Ditolak!</b><br>Maaf, Kuota untuk Jurusan tersebut pada " . $gelombang_aktif . " sudah penuh.</div>";
    } elseif (substr($no_kk, 0, 2) !== '31' || strlen($no_kk) !== 16) {
        $pesan = "<div class='alert alert-danger'><b>Pendaftaran Ditolak!</b><br>Program Khusus ini hanya menerima warga pemilik KK resmi Provinsi DKI Jakarta.</div>";
    } elseif ($hitung_umur < 13 || $hitung_umur > 21) {
        $pesan = "<div class='alert alert-danger'><b>Pendaftaran Ditolak!</b><br>Kriteria usia tidak sesuai persyaratan sistem PPDB (13 - 21 Tahun).</div>";
    } elseif ($skl > 100 || $tka > 100 || $skl < 0 || $tka < 0) {
        $pesan = "<div class='alert alert-danger'><b>Pendaftaran Ditolak!</b><br>Rentang penginputan nilai wajib berada di skala 0.00 - 100.00.</div>";
    } else {
        $folder_tujuan = "uploads/";
        if (!is_dir($folder_tujuan)) { mkdir($folder_tujuan, 0777, true); }

        $ext_ijazah   = strtolower(pathinfo($_FILES['file_ijazah']['name'], PATHINFO_EXTENSION));
        $nama_ijazah  = $nisn . "_ijazah_" . time() . "." . $ext_ijazah;
        
        $ext_tka      = strtolower(pathinfo($_FILES['file_tka']['name'], PATHINFO_EXTENSION));
        $nama_tka     = $nisn . "_tka_" . time() . "." . $ext_tka;
        
        $ext_kk       = strtolower(pathinfo($_FILES['file_kk']['name'], PATHINFO_EXTENSION));
        $nama_kk      = $nisn . "_kk_" . time() . "." . $ext_kk;
        
        $ext_akte     = strtolower(pathinfo($_FILES['file_akte']['name'], PATHINFO_EXTENSION));
        $nama_akte    = $nisn . "_akte_" . time() . "." . $ext_akte;
        
        $ext_ktp_bapak = strtolower(pathinfo($_FILES['file_ktp_bapak']['name'], PATHINFO_EXTENSION));
        $nama_ktp_bapak = $nisn . "_ktpbapak_" . time() . "." . $ext_ktp_bapak;
        
        $ext_ktp_ibu   = strtolower(pathinfo($_FILES['file_ktp_ibu']['name'], PATHINFO_EXTENSION));
        $nama_ktp_ibu  = $nisn . "_ktpibu_" . time() . "." . $ext_ktp_ibu;
        
        $ext_sptjm     = strtolower(pathinfo($_FILES['file_sptjm']['name'], PATHINFO_EXTENSION));
        $nama_sptjm    = $nisn . "_sptjm_" . time() . "." . $ext_sptjm;

        $nama_tabungan_kjp = "";
        $upload_kjp_status = true;
        if ($status_kjp == 'Ya') {
            $ext_tabungan_kjp  = strtolower(pathinfo($_FILES['file_tabungan_kjp']['name'], PATHINFO_EXTENSION));
            $nama_tabungan_kjp = $nisn . "_tabungankjp_" . time() . "." . $ext_tabungan_kjp;
            $upload_kjp_status = move_uploaded_file($_FILES['file_tabungan_kjp']['tmp_name'], $folder_tujuan . $nama_tabungan_kjp);
        }

        if (move_uploaded_file($_FILES['file_ijazah']['tmp_name'], $folder_tujuan . $nama_ijazah) && 
            move_uploaded_file($_FILES['file_tka']['tmp_name'], $folder_tujuan . $nama_tka) &&
            move_uploaded_file($_FILES['file_kk']['tmp_name'], $folder_tujuan . $nama_kk) &&
            move_uploaded_file($_FILES['file_akte']['tmp_name'], $folder_tujuan . $nama_akte) &&
            move_uploaded_file($_FILES['file_ktp_bapak']['tmp_name'], $folder_tujuan . $nama_ktp_bapak) &&
            move_uploaded_file($_FILES['file_ktp_ibu']['tmp_name'], $folder_tujuan . $nama_ktp_ibu) &&
            move_uploaded_file($_FILES['file_sptjm']['tmp_name'], $folder_tujuan . $nama_sptjm) && $upload_kjp_status) {
            
            $no_pendaftaran = "SPMB-SMKPB1-" . date('Y') . "-" . rand(1000, 9999);

            $query = "INSERT INTO pendaftar (no_pendaftaran, nama_lengkap, tempat_lahir, tanggal_lahir, nisn, no_ijazah, asal_sekolah, riwayat_penyakit, no_whatsapp, pilihan_jurusan, nilai_skl, nilai_tka, nilai_test, file_ijazah, file_tka, file_kk, file_akte, no_kk, status_konfirmasi, file_ktp_bapak, file_ktp_ibu, file_sptjm, status_kjp, no_rek_kjp, file_tabungan_kjp, gelombang) 
                      VALUES ('$no_pendaftaran', '$nama', '$tmpl_lahir', '$tgl_lahir', '$nisn', '$no_ijazah', '$asal', '$riwayat_penyakit', '$wa', '$jurusan', '$skl', '$tka', '0.00', '$nama_ijazah', '$nama_tka', '$nama_kk', '$nama_akte', '$no_kk', 'Belum', '$nama_ktp_bapak', '$nama_ktp_ibu', '$nama_sptjm', '$status_kjp', '$no_rek_kjp', '$nama_tabungan_kjp', '$gelombang_id')";

            if (mysqli_query($conn, $query)) {
                header("Location: bukti.php?no_pendaftaran=" . urlencode(trim($no_pendaftaran)));
                exit;
            } else {
                die("Gagal Menyimpan: " . mysqli_error($conn)); 
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPMB Portal - SMKS PERMATA BUNDA I</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f1f5f9; color: #1e293b; margin: 0; padding: 20px 0; }
        .container { max-width: 800px; background: #ffffff; border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); padding: 35px; margin: 0 auto; box-sizing: border-box; }
        
        .header { text-align: center; margin-bottom: 30px; }
        .header h2 { margin: 10px 0 5px 0; font-weight: 800; color: #0f172a; font-size: 24px; letter-spacing: -0.5px; }
        .header h4 { margin: 0 0 10px 0; color: #475569; font-weight: 600; font-size: 16px; }
        .tag-school { background: #e0e7ff; color: #4f46e5; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; display: inline-block; }
        .tag-gelombang { background: #fef3c7; color: #d97706; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; display: inline-block; margin-left: 5px; }

        .main-nav { display: flex; justify-content: center; gap: 15px; margin-bottom: 30px; border-bottom: 2px solid #e2e8f0; padding-bottom: 20px; flex-wrap: wrap; }
        .nav-link { padding: 10px 22px; font-weight: 600; color: #64748b; text-decoration: none; border-radius: 10px; transition: all 0.3s ease; font-size: 14px; border: 1px solid #e2e8f0; display: inline-flex; align-items: center; gap: 8px; }
        .nav-link:hover { background: #f8fafc; color: #4f46e5; border-color: #cbd5e1; }

        .grid-form { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        @media (max-width: 640px) { .grid-form { grid-template-columns: 1fr; gap: 15px; } }
        
        .form-group label { display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 8px; }
        .form-group input, .form-group select { width: 100%; padding: 14px 16px; border: 1.5px solid #cbd5e1; border-radius: 10px; font-family: inherit; font-size: 14px; transition: all 0.2s ease; box-sizing: border-box; background: #fff; color: #1e293b; outline: none; }
        .form-group input:focus, .form-group select:focus { border-color: #4f46e5; box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1); }
        
        .input-kapital { text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; }
        .section-title { font-size: 15px; font-weight: 800; color: #0f172a; margin-top: 25px; padding-bottom: 10px; border-bottom: 2px solid #f1f5f9; display: flex; align-items: center; gap: 6px; }
        
        .custom-upload-box { position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 20px 15px; text-align: center; background: #f8fafc; cursor: pointer; transition: all 0.2s ease; min-height: 100px; box-sizing: border-box; }
        .custom-upload-box:hover { background: #f0fdf4; border-color: #22c55e; }
        .custom-upload-box input[type="file"] { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 2; }
        .upload-icon { font-size: 26px; display: block; margin-bottom: 6px; pointer-events: none; }
        .upload-text { font-size: 13px; font-weight: 700; color: #475569; display: block; pointer-events: none; line-height: 1.4; }
        .upload-hint { font-size: 11px; color: #94a3b8; display: block; margin-top: 5px; font-weight: 500; pointer-events: none; }
        
        .custom-upload-box.file-loaded { background: #ecfdf5; border: 2px solid #10b981; }
        .custom-upload-box.file-loaded .upload-icon { content: "✅"; }
        .custom-upload-box.file-loaded .upload-text { color: #047857; }
        .custom-upload-box.file-loaded .upload-hint { color: #065f46; font-weight: 700; word-break: break-all; padding: 0 4px; }

        .btn { background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); color: white; border: none; padding: 16px 20px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: all 0.3s ease; width: 100%; font-size: 16px; margin-top: 15px; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2); }
        .btn:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 6px 15px rgba(79, 70, 229, 0.3); }
        .btn:disabled { background: #cbd5e1; cursor: not-allowed; box-shadow: none; transform: none; }

        .alert { padding: 15px 20px; border-radius: 10px; margin-bottom: 25px; font-size: 14px; line-height: 1.5; }
        .alert-danger { background: #fef2f2; border-left: 4px solid #ef4444; color: #991b1b; }
        .time-alert-box { background: #fffbeb; border: 1px solid #fde68a; padding: 30px; text-align: center; border-radius: 12px; color: #b45309; font-weight: 600; }
        
        .status-badge-neutral { background: #f1f5f9; border: 1.5px solid #cbd5e1; border-radius: 10px; text-align: center; font-weight: 700; padding: 14px 0; color: #64748b; font-size: 14px; box-sizing: border-box; display: block; width: 100%; }
        .status-valid { background: #ecfdf5; border-color: #10b981; color: #047857; }
        .status-invalid { background: #fef2f2; border-color: #ef4444; color: #b91c1c; }

        .kjp-wrapper-box { background: #f8fafc; border: 1px dashed #cbd5e1; padding: 20px; border-radius: 12px; margin-top: 15px; display: none; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>PORTAL SPMB ONLINE</h2>
        <h4>SMKS PERMATA BUNDA I JAKARTA</h4>
        <span class="tag-school">Tahun Ajaran 2026/2027</span>
        <span class="tag-gelombang">🔥 <?php echo $gelombang_aktif; ?></span>
    </div>

    <div class="main-nav">
        <a href="live_board.php" class="nav-link">📊 Live Board Sisa Kuota</a>
        <a href="pengumuman.php" class="nav-link">🔍 Cek Hasil Kelulusan Mandiri</a>
    </div>

    <?php if ($pesan != "") echo $pesan; ?>

    <?php if (!$pendaftaran_buka): ?>
        <div class="time-alert-box">
            ⚠️ Mohon Maaf, Sistem SPMB Online Saat Ini Sedang Ditutup.<br>
            <span style="font-size:13px; font-weight:500; color:#9a3412; display:block; margin-top:8px;">
                <b>Gelombang 1:</b> 15 Juni 2026 - 30 Juni 2026<br>
                <b>Gelombang 2:</b> 08 Juli 2026 - 09 Juli 2026
            </span>
        </div>
    <?php else: ?>
        <form action="" method="POST" enctype="multipart/form-data">
            
            <div class="section-title">👤 Identitas Pribadi Calon Siswa</div>
            <div class="grid-form" style="margin-top:15px;">
                <div class="form-group" style="grid-column: span 2;">
                    <label>Nama Lengkap (Sesuai Ijazah)</label>
                    <input type="text" name="nama_lengkap" class="input-kapital" placeholder="CONTOH: BUDI SETIAWAN" oninput="this.value = this.value.toUpperCase()" required>
                </div>

                <div class="form-group" style="grid-column: span 2;">
                    <label>Nomor Kartu Keluarga (KK DKI Jakarta)</label>
                    <div style="display: grid; grid-template-columns: 3fr 1fr; gap: 10px;">
                        <input type="text" id="no_kk" name="no_kk" maxlength="16" placeholder="Wajib 16 Digit & Diawali Angka 31" pattern="[0-9]{16}" oninput="jalankanValidasiSistemKomplit()" required>
                        <span id="box_status_kk" class="status-badge-neutral">Belum Valid</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" placeholder="Jakarta" required style="text-transform: capitalize;">
                </div>
                
                <div class="form-group">
                    <label>Tanggal Lahir & Status Batas Usia</label>
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 10px;">
                        <input type="date" id="tanggal_lahir" name="tanggal_lahir" onchange="jalankanValidasiSistemKomplit()" required>
                        <span id="box_status_umur" class="status-badge-neutral">- Thn</span>
                    </div>
                </div>

                <div class="form-group">
                    <label>NISN (10 Digit Wajib)</label>
                    <input type="text" 
                        name="nisn" 
                        id="nisn"
                        maxlength="10" 
                        placeholder="00xxxxxxxx" 
                        pattern="[0-9]{10}" 
                        oninput="this.value = this.value.replace(/[^0-9]/g, ''); jalankanValidasiNisn(this)" 
                        required>
                    <span id="box_status_nisn" class="status-badge-neutral">Belum Valid</span>
                </div>

                <div class="form-group">
                    <label>Nomor Seri Ijazah / SK Sidanira</label>
                    <input type="text" name="no_ijazah" placeholder="DN-xx/xxx/xxxxx" required>
                </div>

                <div class="form-group">
                    <label>Asal Sekolah (SMP / MTs)</label>
                    <input type="text" name="asal_sekolah" placeholder="SMP Negeri 1 Jakarta" required>
                </div>

                <div class="form-group">
                    <label>No. HP (WhatsApp) Aktif Orang Tua / Siswa</label>
                    <input type="tel" name="no_whatsapp" placeholder="08xxxxxxxxxx" required>
                </div>

                <div class="form-group" style="grid-column: span 2;">
                    <label>Riwayat Penyakit Khusus (Jika Ada)</label>
                    <input type="text" name="riwayat_penyakit" placeholder="Tulis 'Tidak Ada' jika sehat walafiat">
                </div>
            </div>

            <div class="section-title">🎓 Kompetensi Keahlian (Jurusan) & Nilai</div>
            <div class="grid-form" style="margin-top:15px;">
                <div class="form-group" style="grid-column: span 2;">
                    <label>Pilihan Kompetensi Keahlian (Jurusan) & Sisa Kuota <?php echo $gelombang_aktif; ?></label>
                    <select name="pilihan_jurusan" required>
                        <option value="">-- Silahkan Pilih Jurusan --</option>
                        
                        <option value="Akuntansi dan Keuangan Lembaga" <?php if($total_akl >= $max_kuota) echo 'disabled'; ?>>
                            Akuntansi dan Keuangan Lembaga (AKL) - Kuota Terisi: <?php echo $total_akl . "/" . $max_kuota; ?> 
                            <?php if($total_akl >= $max_kuota) echo '(PENUH)'; ?>
                        </option>
                        
                        <option value="Manajemen Perkantoran dan Layanan Bisnis" <?php if($total_mplb >= $max_kuota) echo 'disabled'; ?>>
                            Manajemen Perkantoran dan Layanan Bisnis (MPLB) - Kuota Terisi: <?php echo $total_mplb . "/" . $max_kuota; ?>
                            <?php if($total_mplb >= $max_kuota) echo '(PENUH)'; ?>
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Rata-rata Nilai SIDANIRA / SKL</label>
                    <input type="number" name="nilai_skl" step="0.01" min="0" max="100" placeholder="0.00" 
                        oninput="if(this.value > 100) this.value = 100; if(this.value < 0) this.value = 0; if(this.value.includes('.')){ let p=this.value.split('.'); if(p[1].length>2) this.value=p[0]+'.'+p[1].substring(0,2); }" required>
                    <small style="color:#64748b; font-size:11px; margin-top:4px; display:block;">*Gunakan tanda <b>titik (.)</b> untuk desimal. Contoh: <b>79.99</b></small>
                </div>

                <div class="form-group">
                    <label>Nilai Tes Kompetensi Akademik / TKA</label>
                    <input type="number" name="nilai_tka" step="0.01" min="0" max="100" placeholder="0.00" 
                        oninput="if(this.value > 100) this.value = 100; if(this.value < 0) this.value = 0; if(this.value.includes('.')){ let p=this.value.split('.'); if(p[1].length>2) this.value=p[0]+'.'+p[1].substring(0,2); }" required>
                    <small style="color:#64748b; font-size:11px; margin-top:4px; display:block;">*Gunakan tanda <b>titik (.)</b> untuk desimal. Contoh: <b>85.50</b></small>
                </div>
            </div>

            <div class="section-title">📁 Lampiran Dokumen Berkas Pendukung (Maks 3MB per File)</div>
            <div class="grid-form" style="margin-top:15px;">
                <div class="form-group">
                    <label>1. Scan Ijazah / SK Sidanira Asli <span style="color:red;">*</span></label>
                    <div class="custom-upload-box" id="box_ijazah">
                        <input type="file" name="file_ijazah" accept=".jpg,.jpeg,.png,.pdf" onchange="perbaruiPratinjauBerkasSistem(this, 'txt_ijazah', 'box_ijazah')" required>
                        <span class="upload-icon">📄</span>
                        <span class="upload-text" id="txt_ijazah">Ketuk / Seret Berkas</span>
                        <span class="upload-hint">Format: JPG, PNG, PDF</span>
                    </div>
                </div>

                <div class="form-group">
                    <label>2. Scan Hasil Nilai TKA / SKHU <span style="color:red;">*</span></label>
                    <div class="custom-upload-box" id="box_tka">
                        <input type="file" name="file_tka" accept=".jpg,.jpeg,.png,.pdf" onchange="perbaruiPratinjauBerkasSistem(this, 'txt_tka', 'box_tka')" required>
                        <span class="upload-icon">📄</span>
                        <span class="upload-text" id="txt_tka">Ketuk / Seret Berkas</span>
                        <span class="upload-hint">Format: JPG, PNG, PDF</span>
                    </div>
                </div>

                <div class="form-group">
                    <label>3. Scan Kartu Keluarga (KK) Asli <span style="color:red;">*</span></label>
                    <div class="custom-upload-box" id="box_kk">
                        <input type="file" name="file_kk" accept=".jpg,.jpeg,.png,.pdf" onchange="perbaruiPratinjauBerkasSistem(this, 'txt_kk', 'box_kk')" required>
                        <span class="upload-icon">📄</span>
                        <span class="upload-text" id="txt_kk">Ketuk / Seret Berkas</span>
                        <span class="upload-hint">Format: JPG, PNG, PDF</span>
                    </div>
                </div>

                <div class="form-group">
                    <label>4. Scan Akte Kelahiran Resmi <span style="color:red;">*</span></label>
                    <div class="custom-upload-box" id="box_akte">
                        <input type="file" name="file_akte" accept=".jpg,.jpeg,.png,.pdf" onchange="perbaruiPratinjauBerkasSistem(this, 'txt_akte', 'box_akte')" required>
                        <span class="upload-icon">📄</span>
                        <span class="upload-text" id="txt_akte">Ketuk / Seret Berkas</span>
                        <span class="upload-hint">Format: JPG, PNG, PDF</span>
                    </div>
                </div>

                <div class="form-group">
                    <label>5. Scan KTP Orang Tua (Bapak) <span style="color:red;">*</span></label>
                    <div class="custom-upload-box" id="box_ktp_bapak">
                        <input type="file" name="file_ktp_bapak" accept=".jpg,.jpeg,.png,.pdf" onchange="perbaruiPratinjauBerkasSistem(this, 'txt_ktp_bapak', 'box_ktp_bapak')" required>
                        <span class="upload-icon">💳</span>
                        <span class="upload-text" id="txt_ktp_bapak">Ketuk / Seret Berkas</span>
                        <span class="upload-hint">Format: JPG, PNG, PDF</span>
                    </div>
                </div>

                <div class="form-group">
                    <label>6. Scan KTP Orang Tua (Ibu) <span style="color:red;">*</span></label>
                    <div class="custom-upload-box" id="box_ktp_ibu">
                        <input type="file" name="file_ktp_ibu" accept=".jpg,.jpeg,.png,.pdf" onchange="perbaruiPratinjauBerkasSistem(this, 'txt_ktp_ibu', 'box_ktp_ibu')" required>
                        <span class="upload-icon">💳</span>
                        <span class="upload-text" id="txt_ktp_ibu">Ketuk / Seret Berkas</span>
                        <span class="upload-hint">Format: JPG, PNG, PDF</span>
                    </div>
                </div>

                <div class="form-group" style="grid-column: span 2;">
                    <label>7. Scan Surat Pertanggungjawaban Mutlak (SPTJM) Bermaterai 10.000 <span style="color:red;">*</span></label>
                    <div class="custom-upload-box" id="box_sptjm">
                        <input type="file" name="file_sptjm" accept=".jpg,.jpeg,.png,.pdf" onchange="perbaruiPratinjauBerkasSistem(this, 'txt_sptjm', 'box_sptjm')" required>
                        <span class="upload-icon">📜</span>
                        <span class="upload-text" id="txt_sptjm">Ketuk / Seret Dokumen SPTJM Kesini</span>
                        <span class="upload-hint">Format: JPG, PNG, PDF</span>
                    </div>
                </div>
            </div>

            <div class="section-title">💳 Kepemilikan Kartu Jakarta Pintar (KJP)</div>
            <div style="margin-top: 15px;">
                <div class="form-group">
                    <label>Apakah Calon Siswa Memiliki KJP Aktif?</label>
                    <select name="status_kjp" id="status_kjp_select" onchange="toggleKjpFormSistem(this.value)" required>
                        <option value="Tidak">Tidak Memiliki KJP</option>
                        <option value="Ya">Ya, Saya Pemilik KJP Aktif</option>
                    </select>
                </div>

                <div id="wrapper_kjp_kondisional" class="kjp-wrapper-box">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label>Nomor Rekening Buku Tabungan KJP</label>
                        <input type="text" 
                            name="no_rek_kjp" 
                            maxlength="15" 
                            id="no_rek_kjp_field" 
                            placeholder="Masukkan nomor rekening KJP (Maks 15 angka)" 
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>8. Scan Buku Tabungan KJP Halaman Depan <span style="color:red;">*</span></label>
                        <div class="custom-upload-box" id="box_tabungan_kjp">
                            <input type="file" name="file_tabungan_kjp" id="file_tabungan_kjp_field" accept=".jpg,.jpeg,.png,.pdf" onchange="perbaruiPratinjauBerkasSistem(this, 'txt_tabungan_kjp', 'box_tabungan_kjp')">
                            <span class="upload-icon">💳</span>
                            <span class="upload-text" id="txt_tabungan_kjp">Ketuk / Seret Berkas Buku Tabungan KJP Disini</span>
                            <span class="upload-hint">Format: JPG, PNG, PDF</span>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" name="daftar" id="btn_submit_form" class="btn">Kirim & Proses Formulir Pendaftaran</button>
        </form>
    <?php endif; ?>
</div>

<script>
function perbaruiPratinjauBerkasSistem(inputElement, textTargetId, boxTargetId) {
    const textTarget = document.getElementById(textTargetId);
    const boxTarget = document.getElementById(boxTargetId);
    
    if (inputElement.files && inputElement.files[0]) {
        const namaFile = inputElement.files[0].name;
        textTarget.innerText = "✓ Berkas Berhasil Terpilih";
        boxTarget.querySelector('.upload-hint').innerText = namaFile;
        boxTarget.classList.add('file-loaded');
    } else {
        textTarget.innerText = "Ketuk / Seret Berkas Kesini";
        boxTarget.querySelector('.upload-hint').innerText = "Format: JPG, PNG, PDF";
        boxTarget.classList.remove('file-loaded');
    }
}

function toggleKjpFormSistem(nilai) {
    const wrapper = document.getElementById('wrapper_kjp_kondisional');
    const noRekField = document.getElementById('no_rek_kjp_field');
    const fileField = document.getElementById('file_tabungan_kjp_field');
    
    if (nilai === 'Ya') {
        wrapper.style.display = 'block';
        noRekField.required = true;
        fileField.required = true;
    } else {
        wrapper.style.display = 'none';
        noRekField.required = false;
        fileField.required = false;
        noRekField.value = "";
        fileField.value = "";
        document.getElementById('txt_tabungan_kjp').innerText = "Ketuk / Seret Berkas Buku Tabungan KJP Disini";
        document.getElementById('box_tabungan_kjp').querySelector('.upload-hint').innerText = "Format: JPG, PNG, PDF";
        document.getElementById('box_tabungan_kjp').classList.remove('file-loaded');
    }
}
function jalankanValidasiNisn(input) {
    const badgeNisn = document.getElementById("box_status_nisn");
    const btnSubmit = document.getElementById("btn_submit_form");
    
    // Validasi: Harus tepat 10 karakter
    if (input.value.length === 10) {
        badgeNisn.innerText = "NISN Valid";
        badgeNisn.className = "status-badge-neutral status-valid";
        // Aktifkan tombol daftar jika valid
        btnSubmit.disabled = false;
    } else {
        badgeNisn.innerText = "Wajib 10 Angka";
        badgeNisn.className = "status-badge-neutral status-invalid";
        // Nonaktifkan tombol daftar jika belum valid
        btnSubmit.disabled = true;
    }
}

function jalankanValidasiSistemKomplit() {
    const tglLahirInput = document.getElementById("tanggal_lahir").value;
    const badgeUmur = document.getElementById("box_status_umur");
    const kkInput = document.getElementById("no_kk").value;
    const badgeKk = document.getElementById("box_status_kk");
    const btnSubmit = document.getElementById("btn_submit_form");
    
    let umurValid = false;
    let kkValid = false;

    if (tglLahirInput) {
        const lahir = new Date(tglLahirInput);
        const hariIni = new Date();
        let umur = hariIni.getFullYear() - lahir.getFullYear();
        const m = hariIni.getMonth() - lahir.getMonth();
        if (m < 0 || (m === 0 && hariIni.getDate() < lahir.getDate())) { umur--; }
        badgeUmur.innerText = umur + " Tahun";
        if (umur >= 13 && umur <= 21) {
            badgeUmur.className = "status-badge-neutral status-valid";
            umurValid = true;
        } else {
            badgeUmur.className = "status-badge-neutral status-invalid";
            umurValid = false;
        }
    }

    if (kkInput) {
        if (kkInput.length === 16 && kkInput.startsWith("31")) {
            badgeKk.innerText = "KK DKI Valid";
            badgeKk.className = "status-badge-neutral status-valid";
            kkValid = true;
        } else {
            badgeKk.innerText = "Bukan KK DKI";
            badgeKk.className = "status-badge-neutral status-invalid";
            kkValid = false;
        }
    } else {
        badgeKk.innerText = "Belum Valid";
        badgeKk.className = "status-badge-neutral";
    }

    if (tglLahirInput && kkInput) {
        if (umurValid && kkValid) { btnSubmit.disabled = false; } 
        else { btnSubmit.disabled = true; }
    }
}

// ==========================================
// FITUR AUTO-SAVE FORM (ANTI HILANG SAAT REFRESH)
// ==========================================
document.addEventListener("DOMContentLoaded", function() {
    // Pilih semua input teks, nomor, telp, date, dan dropdown (kecuali input file)
    const formElements = document.querySelectorAll("input:not([type='file']), select");
    
    // 1. Saat halaman dimuat, isi kembali kolom dengan data yang tersimpan
    formElements.forEach(el => {
        if (el.name && sessionStorage.getItem(el.name)) {
            el.value = sessionStorage.getItem(el.name);
            // Trigger validasi setelah nilai diisi otomatis
            if(el.name === "tanggal_lahir" || el.name === "no_kk") jalankanValidasiSistemKomplit();
            if(el.name === "status_kjp") toggleKjpFormSistem(el.value);
        }
        
        // 2. Simpan seketika setiap kali user mengetik/memilih
        el.addEventListener("input", function() {
            sessionStorage.setItem(el.name, el.value);
        });
        el.addEventListener("change", function() {
            sessionStorage.setItem(el.name, el.value);
        });
    });
});
</script>
</body>
</html>