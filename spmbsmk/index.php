<?php 
session_start();
include 'koneksi.php';

// ===================================================================
// 1. PENGATURAN KONTROL SISTEM DINAMIS BERBASIS DATABASE (ADMIN PANEL)
// ===================================================================
date_default_timezone_set('Asia/Jakarta');

if (!empty($_POST['website_checker'])) {
    die("Akses ditolak (Bot detected).");
}

$pengaturan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pengaturan WHERE id = 1"));

$gelombang_id     = (int)$pengaturan['gelombang_aktif'];
$gelombang_aktif  = "Gelombang " . $gelombang_id;

$kuota_g1 = (int)$pengaturan['max_kuota_g1'];
$kuota_g2 = (int)$pengaturan['max_kuota_g2'];

$pendaftaran_buka = ($pengaturan['status_pendaftaran'] === 'buka');

// ===================================================================
// 2. HITUNG KUOTA BERTINGKAT UNTUK LOGIKA OVERFLOW (LEMPAR GELOMBANG)
// ===================================================================
function hitungPendaftarGelombang($jurusan, $id_gel, $conn) {
    $q = mysqli_query($conn, "SELECT COUNT(*) as total FROM pendaftar WHERE pilihan_jurusan = '$jurusan' AND gelombang = '$id_gel'");
    return mysqli_fetch_assoc($q)['total'];
}

// Hitung Pendaftar AKL
$total_akl_g1 = hitungPendaftarGelombang('Akuntansi dan Keuangan Lembaga', '1', $conn);
$total_akl_g2 = hitungPendaftarGelombang('Akuntansi dan Keuangan Lembaga', '2', $conn);

if ($total_akl_g1 < $kuota_g1) {
    $sisa = $kuota_g1 - $total_akl_g1;
    $teks_akl = "Sisa Kuota Gel 1: $sisa Kursi";
} elseif ($total_akl_g2 < $kuota_g2) {
    $sisa = $kuota_g2 - $total_akl_g2;
    $teks_akl = "Sisa Kuota Gel 2: $sisa Kursi (Gel 1 Penuh)";
} else {
    $teks_akl = "Masuk Antrian Cadangan (Kuota Penuh)";
}

// Hitung Pendaftar MPLB
$total_mplb_g1 = hitungPendaftarGelombang('Manajemen Perkantoran dan Layanan Bisnis', '1', $conn);
$total_mplb_g2 = hitungPendaftarGelombang('Manajemen Perkantoran dan Layanan Bisnis', '2', $conn);

if ($total_mplb_g1 < $kuota_g1) {
    $sisa = $kuota_g1 - $total_mplb_g1;
    $teks_mplb = "Sisa Kuota Gel 1: $sisa Kursi";
} elseif ($total_mplb_g2 < $kuota_g2) {
    $sisa = $kuota_g2 - $total_mplb_g2;
    $teks_mplb = "Sisa Kuota Gel 2: $sisa Kursi (Gel 1 Penuh)";
} else {
    $teks_mplb = "Masuk Antrian Cadangan (Kuota Penuh)";
}

$pesan = "";

// ===================================================================
// 3. PROSES SUBMISSION DATA FORMULIR PENDAFTARAN
// ===================================================================
if (isset($_POST['daftar'])) {
    if (!$pendaftaran_buka) {
        die("Akses Ditolak: Pendaftaran saat ini sedang ditutup.");
    }

    $nama       = htmlspecialchars(strtoupper(trim(mysqli_real_escape_string($conn, $_POST['nama_lengkap']))));
    $nik        = trim(mysqli_real_escape_string($conn, $_POST['nik']));
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
    
    $alamat     = htmlspecialchars(trim(mysqli_real_escape_string($conn, $_POST['alamat'])));
    $kelurahan  = htmlspecialchars(ucwords(strtolower(trim(mysqli_real_escape_string($conn, $_POST['kelurahan'])))));
    $kecamatan  = htmlspecialchars(ucwords(strtolower(trim(mysqli_real_escape_string($conn, $_POST['kecamatan'])))));

    $status_kjp = trim(mysqli_real_escape_string($conn, $_POST['status_kjp']));
    $no_rek_kjp = ""; 
    if ($status_kjp == 'Ya') {
        $no_rek_kjp = trim(mysqli_real_escape_string($conn, $_POST['no_rek_kjp']));
        if (strlen($no_rek_kjp) < 5 || strlen($no_rek_kjp) > 15) {
            $pesan = "<div class='alert alert-danger'><b>Pendaftaran Gagal!</b><br>Nomor rekening KJP tidak valid (minimal 5 angka, maksimal 15 angka).</div>";
        }
    }

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

    $cek_duplikat = "SELECT * FROM pendaftar WHERE no_ijazah = '$no_ijazah' OR nisn = '$nisn' OR no_kk = '$no_kk' OR nik = '$nik'";
    $hasil_cek    = mysqli_query($conn, $cek_duplikat);

    // ANTI-HACK LOKASI
    $jarak_tidak_valid = false;
    if (isset($_POST['lat']) && isset($_POST['long'])) {
        $lat = (float)$_POST['lat'];
        $long = (float)$_POST['long'];
        
        $latSekolah = -6.157462; 
        $longSekolah = 106.8035761; 
        
        $theta = $long - $longSekolah;
        $dist = sin(deg2rad($lat)) * sin(deg2rad($latSekolah)) +  cos(deg2rad($lat)) * cos(deg2rad($latSekolah)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $jarak_meter = $dist * 60 * 1.1515 * 1.609344 * 1000;

        if ($jarak_meter > 590) { $jarak_tidak_valid = true; }
    } else {
        $jarak_tidak_valid = true; 
    }

    if ($file_terlalu_besar) {
        $pesan = "<div class='alert alert-danger'><b>Pendaftaran Gagal!</b><br>Berkas melebihi batas maksimal 3MB.</div>";
    } elseif ($ekstensi_ilegal) {
        $pesan = "<div class='alert alert-danger'><b>Format Berkas Ilegal!</b><br>Sistem hanya menerima file gambar atau dokumen (.pdf).</div>";
    } elseif ($jarak_tidak_valid) {
        $pesan = "<div class='alert alert-danger'><b>Pendaftaran Ditolak!</b><br>Lokasi Anda tidak valid atau berada di luar area sekolah. Silakan lakukan Verifikasi Lokasi.</div>";
    } elseif (mysqli_num_rows($hasil_cek) > 0) {
        $pesan = "<div class='alert alert-danger'><b>Pendaftaran Ditolak!</b><br>Maaf, NIK, NISN, Nomor Seri Ijazah, atau Nomor KK Anda sudah pernah terdaftar di sistem kami. Anda tidak bisa mendaftar ganda.</div>";
    } elseif (substr($no_kk, 0, 2) !== '31' || strlen($no_kk) !== 16) {
        $pesan = "<div class='alert alert-danger'><b>Pendaftaran Ditolak!</b><br>Program Khusus ini hanya menerima warga pemilik KK resmi Provinsi DKI Jakarta.</div>";
    } elseif ($hitung_umur < 13 || $hitung_umur > 21) {
        $pesan = "<div class='alert alert-danger'><b>Pendaftaran Ditolak!</b><br>Kriteria usia tidak sesuai persyaratan sistem PPDB (13 - 21 Tahun).</div>";
    } elseif ($skl > 100 || $tka > 100 || $skl < 0 || $tka < 0) {
        $pesan = "<div class='alert alert-danger'><b>Pendaftaran Ditolak!</b><br>Rentang penginputan nilai wajib berada di skala 0.00 - 100.00.</div>";
    } else {
        
        // PENENTUAN GELOMBANG / CADANGAN SECARA DINAMIS
        $cek_g1 = hitungPendaftarGelombang($jurusan, '1', $conn);
        $cek_g2 = hitungPendaftarGelombang($jurusan, '2', $conn);
        
        if ($cek_g1 < $pengaturan['max_kuota_g1']) {
            $gelombang_final = '1';
        } elseif ($cek_g2 < $pengaturan['max_kuota_g2']) {
            $gelombang_final = '2';
        } else {
            $gelombang_final = 'Cadangan';
        }

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
            $waktu_daftar = date('Y-m-d H:i:s');
            
            // Insert data dengan $gelombang_final (Overflow System)
            $query = "INSERT INTO pendaftar (no_pendaftaran, nama_lengkap, nik, tempat_lahir, tanggal_lahir, nisn, no_ijazah, asal_sekolah, riwayat_penyakit, alamat, kelurahan, kecamatan, no_whatsapp, pilihan_jurusan, nilai_skl, nilai_tka, nilai_test, file_ijazah, file_tka, file_kk, file_akte, no_kk, status_konfirmasi, file_ktp_bapak, file_ktp_ibu, file_sptjm, status_kjp, no_rek_kjp, file_tabungan_kjp, gelombang, tanggal_daftar) 
                      VALUES ('$no_pendaftaran', '$nama', '$nik', '$tmpl_lahir', '$tgl_lahir', '$nisn', '$no_ijazah', '$asal', '$riwayat_penyakit', '$alamat', '$kelurahan', '$kecamatan', '$wa', '$jurusan', '$skl', '$tka', '0.00', '$nama_ijazah', '$nama_tka', '$nama_kk', '$nama_akte', '$no_kk', 'Menunggu', '$nama_ktp_bapak', '$nama_ktp_ibu', '$nama_sptjm', '$status_kjp', '$no_rek_kjp', '$nama_tabungan_kjp', '$gelombang_final', '$waktu_daftar')";

            if (mysqli_query($conn, $query)) {
                $_SESSION['izin_akses_bukti_' . $no_pendaftaran] = true;
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SPMB Portal - SMKS PERMATA BUNDA I</title>
    <link rel="icon" type="image/x-icon" href="logo/logosmkpb.png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f1f5f9; color: #1e293b; margin: 0; padding: 20px 10px; }
        .container { max-width: 800px; background: #ffffff; border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); padding: 35px; margin: 0 auto; box-sizing: border-box; }
        
        .header { text-align: center; margin-bottom: 30px; }
        .header h2 { margin: 10px 0 5px 0; font-weight: 800; color: #0f172a; font-size: 24px; letter-spacing: -0.5px; }
        .header h4 { margin: 0 0 10px 0; color: #475569; font-weight: 600; font-size: 16px; }
        .tag-school, .tag-gelombang { background: #e0e7ff; color: #4f46e5; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; display: inline-block; margin-bottom: 5px;}
        .tag-gelombang { background: #fef3c7; color: #d97706; margin-left: 5px; }

        .main-nav { display: flex; justify-content: center; gap: 15px; margin-bottom: 30px; border-bottom: 2px solid #e2e8f0; padding-bottom: 20px; flex-wrap: wrap; }
        .nav-link { padding: 10px 22px; font-weight: 600; color: #64748b; text-decoration: none; border-radius: 10px; transition: all 0.3s ease; font-size: 14px; border: 1px solid #e2e8f0; display: inline-flex; align-items: center; justify-content: center; gap: 8px; }
        .nav-link:hover { background: #f8fafc; color: #4f46e5; border-color: #cbd5e1; }

        .grid-form { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .full-width { grid-column: span 2; }
        .input-group-badge { display: grid; grid-template-columns: 3fr 1fr; gap: 10px; }
        .input-group-badge-date { display: grid; grid-template-columns: 2fr 1fr; gap: 10px; }
        
        .form-group label { display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 8px; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 14px 16px; border: 1.5px solid #cbd5e1; border-radius: 10px; font-family: inherit; font-size: 14px; transition: all 0.2s ease; box-sizing: border-box; background: #fff; color: #1e293b; outline: none; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: #4f46e5; box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1); }
        .form-group textarea { resize: vertical; min-height: 80px; }
        
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
        #info-lokasi { font-size: 12px; margin-top: 10px; font-weight: bold; text-align: center; padding: 8px; border-radius: 6px;}

        @media (max-width: 640px) { 
            body { padding: 10px; }
            .container { padding: 20px 15px; border-radius: 12px; }
            .header h2 { font-size: 20px; }
            .header h4 { font-size: 14px; }
            .tag-school, .tag-gelombang { display: block; margin: 5px auto; width: max-content; margin-left: auto; margin-right: auto;}
            .main-nav { flex-direction: column; gap: 10px; padding-bottom: 15px; }
            .nav-link { width: 100%; box-sizing: border-box; }
            .grid-form { grid-template-columns: 1fr; gap: 15px; } 
            .full-width { grid-column: span 1; }
            .input-group-badge, .input-group-badge-date { grid-template-columns: 1fr; gap: 8px; }
            .form-group input, .form-group select, .form-group textarea { font-size: 16px; padding: 12px 14px; }
            .status-badge-neutral { padding: 12px 0; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <img src="logo/logopb.jpg" alt="Logo Yayasan Permata Bunda" style="max-height: 97px; width: auto; margin-bottom: 15px;">
        <img src="logo/logopemda.png" alt="Logo Pemda DKI" style="max-height: 100px; width: auto; margin-bottom: 15px;">
        <img src="logo/logosmkpb.png" alt="Logo SMK PB1" style="max-height: 110px; width: auto; margin-bottom: 15px;">
        <h2>PORTAL SPMB ONLINE</h2>
        <h2>SMKS PERMATA BUNDA I JAKARTA</h2>
        <h4>Sekolah Swasta Gratis</h4>

        <span class="tag-school">Tahun Ajaran 2026/2027</span>
        <span class="tag-gelombang">🔥 <?php echo $gelombang_aktif; ?></span>
    </div>

    <div class="main-nav">
        <a href="live_board.php" class="nav-link">📊 Live Board Sisa Kuota</a>
        <a href="pengumuman.php" class="nav-link">🔍 Cek Hasil Kelulusan</a>
    </div>

    <?php if ($pesan != "") echo $pesan; ?>

    <?php if (!$pendaftaran_buka): ?>
        <div class="time-alert-box">
            ⚠️ Mohon Maaf, Sistem Penerimaan Siswa Baru Saat Ini Sedang Ditutup Sementara Oleh Panitia Admin.<br>
            <span style="font-size:13px; font-weight:500; color:#9a3412; display:block; margin-top:8px;">
                Silakan hubungi pihak panitia sekolah untuk informasi jadwal pembukaan jalur berikutnya.
            </span>
        </div>
    <?php else: ?>
        <form action="" method="POST" enctype="multipart/form-data">
            <div style="display:none;">
                <input type="text" name="website_checker" value="">
            </div>
            
            <div class="section-title">👤 Identitas Pribadi Calon Siswa</div>
            <div class="grid-form" style="margin-top:15px;">
                <div class="form-group full-width">
                    <label>Nama Lengkap (Sesuai Ijazah)</label>
                    <input type="text" name="nama_lengkap" class="input-kapital" placeholder="CONTOH: BUDI SETIAWAN" oninput="this.value = this.value.toUpperCase()" required>
                </div>

                <div class="form-group full-width">
                    <label>Nomor Induk Kependudukan (NIK Siswa)</label>
                    <div class="input-group-badge">
                        <input type="tel" name="nik" id="nik" maxlength="16" placeholder="Wajib 16 Digit Angka NIK" pattern="[0-9]{16}" oninput="this.value = this.value.replace(/[^0-9]/g, ''); jalankanValidasiNik(this)" required>
                        <span id="box_status_nik" class="status-badge-neutral">Belum Valid</span>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label>Nomor Kartu Keluarga (KK DKI Jakarta)</label>
                    <div class="input-group-badge">
                        <input type="tel" id="no_kk" name="no_kk" maxlength="16" placeholder="Wajib 16 Digit & Diawali Angka 31" pattern="[0-9]{16}" oninput="this.value = this.value.replace(/[^0-9]/g, ''); jalankanValidasiSistemKomplit()" required>
                        <span id="box_status_kk" class="status-badge-neutral">Belum Valid</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" placeholder="Jakarta" required style="text-transform: capitalize;">
                </div>
                
                <div class="form-group">
                    <label>Tanggal Lahir & Status Batas Usia</label>
                    <div class="input-group-badge-date">
                        <input type="date" id="tanggal_lahir" name="tanggal_lahir" onchange="jalankanValidasiSistemKomplit()" required>
                        <span id="box_status_umur" class="status-badge-neutral">- Thn</span>
                    </div>
                </div>

                <div class="form-group">
                    <label>NISN (10 Digit Wajib)</label>
                    <input type="tel" 
                        name="nisn" 
                        id="nisn"
                        maxlength="10" 
                        placeholder="00xxxxxxxx" 
                        pattern="[0-9]{10}" 
                        oninput="this.value = this.value.replace(/[^0-9]/g, ''); jalankanValidasiNisn(this)" 
                        required>
                    <span id="box_status_nisn" class="status-badge-neutral" style="margin-top: 8px;">Belum Valid</span>
                </div>

                <div class="form-group">
                    <label>Nomor Seri Ijazah / SK Sidanira</label>
                    <input type="text" name="no_ijazah" placeholder="DN-xx/xxx/xxxxx" required>
                </div>
                
                <div class="form-group full-width">
                    <label>Alamat Lengkap Domisili Siswa</label>
                    <textarea name="alamat" placeholder="Contoh : Jl. Jamblang Raya No.2LM ....." required></textarea>
                </div>

                <div class="form-group">
                    <label>Kelurahan / Desa</label>
                    <input type="text" name="kelurahan" placeholder="Contoh : Kalianyar" required>
                </div>

                <div class="form-group">
                    <label>Kecamatan</label>
                    <input type="text" name="kecamatan" placeholder="Contoh : Tambora" required>
                </div>

                <div class="form-group">
                    <label>Asal Sekolah (SMP / MTs)</label>
                    <input type="text" name="asal_sekolah" placeholder="SMP Negeri 1 Jakarta" required>
                </div>

                <div class="form-group">
                    <label>No. HP (WhatsApp) Aktif</label>
                    <input type="tel" name="no_whatsapp" maxlength="15" placeholder="08xxxxxxxxxx" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                </div>

                <div class="form-group full-width">
                    <label>Riwayat Penyakit Khusus (Jika Ada)</label>
                    <input type="text" name="riwayat_penyakit" placeholder="Tulis 'Tidak Ada' jika sehat walafiat">
                </div>
            </div>

            <div class="section-title">🎓 Kompetensi Keahlian (Jurusan) & Nilai</div>
            <div class="grid-form" style="margin-top:15px;">
                <div class="form-group full-width">
                    <label>Pilihan Kompetensi Keahlian (Jurusan)</label>
                    <select name="pilihan_jurusan" required>
                        <option value="">-- Silahkan Pilih Jurusan --</option>
                        
                        <option value="Akuntansi dan Keuangan Lembaga">
                            Akuntansi dan Keuangan Lembaga (AKL) - <?php echo $teks_akl; ?>
                        </option>
                        
                        <option value="Manajemen Perkantoran dan Layanan Bisnis">
                            Manajamen Perkantoran dan Layanan Bisnis (MPLB) - <?php echo $teks_mplb; ?>
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
                    <label>Nilai Tes Akademik / TKA</label>
                    <input type="number" name="nilai_tka" step="0.01" min="0" max="100" placeholder="0.00" 
                        oninput="if(this.value > 100) this.value = 100; if(this.value < 0) this.value = 0; if(this.value.includes('.')){ let p=this.value.split('.'); if(p[1].length>2) this.value=p[0]+'.'+p[1].substring(0,2); }" required>
                    <small style="color:#64748b; font-size:11px; margin-top:4px; display:block;">*Gunakan tanda <b>titik (.)</b> untuk desimal. Contoh: <b>80.99</b></small>
                </div>
            </div>

            <div class="section-title">📁 Lampiran Dokumen (Maks 3MB)</div>
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
                    <label>3. Scan Kartu Keluarga (KK) Asli - Terbaru (Wajib DKI) <span style="color:red;">*</span></label>
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
                    <label>5. Scan KTP Bapak (Kandung) <span style="color:red;">*</span></label>
                    <div class="custom-upload-box" id="box_ktp_bapak">
                        <input type="file" name="file_ktp_bapak" accept=".jpg,.jpeg,.png,.pdf" onchange="perbaruiPratinjauBerkasSistem(this, 'txt_ktp_bapak', 'box_ktp_bapak')" required>
                        <span class="upload-icon">💳</span>
                        <span class="upload-text" id="txt_ktp_bapak">Ketuk / Seret Berkas</span>
                        <span class="upload-hint">Format: JPG, PNG, PDF</span>
                    </div>
                </div>

                <div class="form-group">
                    <label>6. Scan KTP Ibu <span style="color:red;">*</span></label>
                    <div class="custom-upload-box" id="box_ktp_ibu">
                        <input type="file" name="file_ktp_ibu" accept=".jpg,.jpeg,.png,.pdf" onchange="perbaruiPratinjauBerkasSistem(this, 'txt_ktp_ibu', 'box_ktp_ibu')" required>
                        <span class="upload-icon">💳</span>
                        <span class="upload-text" id="txt_ktp_ibu">Ketuk / Seret Berkas</span>
                        <span class="upload-hint">Format: JPG, PNG, PDF</span>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label>7. Scan Surat Pertanggungjawaban Mutlak (SPTJM) <span style="color:red;">*</span></label>
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
                        <input type="tel" 
                            name="no_rek_kjp" 
                            maxlength="15" 
                            id="no_rek_kjp_field" 
                            placeholder="Maksimal 15 angka" 
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>8. Scan Buku Tabungan KJP <span style="color:red;">*</span></label>
                        <div class="custom-upload-box" id="box_tabungan_kjp">
                            <input type="file" name="file_tabungan_kjp" id="file_tabungan_kjp_field" accept=".jpg,.jpeg,.png,.pdf" onchange="perbaruiPratinjauBerkasSistem(this, 'txt_tabungan_kjp', 'box_tabungan_kjp')">
                            <span class="upload-icon">💳</span>
                            <span class="upload-text" id="txt_tabungan_kjp">Ketuk / Seret Tabungan KJP</span>
                            <span class="upload-hint">Format: JPG, PNG, PDF</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-title">📍 Keamanan Sistem Terpadu</div>
            <div class="form-group full-width" style="margin-top: 15px; border: 1.5px solid #cbd5e1; padding: 20px; border-radius: 12px; background: #f8fafc;">
                <label style="text-align: center; font-size: 14px;">Verifikasi Lokasi Sekolah</label>
                <button type="button" class="btn" style="background:#64748b; box-shadow: none; margin-top:5px;" onclick="cekLokasi()">📍 Verifikasi Lokasi Saya</button>
                <div id="info-lokasi" class="status-badge-neutral">Belum diverifikasi. Tekan tombol di atas.</div>
                <input type="hidden" name="lat" id="lat">
                <input type="hidden" name="long" id="long">
            </div>

            <button type="submit" name="daftar" id="btn_submit_form" class="btn" disabled>
                Kirim & Proses Formulir Pendaftaran
            </button>
        </form>
    <?php endif; ?>
</div>

<script>
let isLokasiValid = false;
let isNisnValid = false;
let isNikValid = false;
let isUmurValid = false;
let isKkValid = false;

function cekSemuaValidasi() {
    const btnSubmit = document.getElementById("btn_submit_form");
    if (isLokasiValid && isNisnValid && isNikValid && isUmurValid && isKkValid) {
        btnSubmit.disabled = false; 
    } else {
        btnSubmit.disabled = true;  
    }
}

function cekLokasi() {
    document.getElementById('info-lokasi').innerHTML = "⏳ Sedang mencari lokasi...";
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const latSekolah = -6.157462; 
            const longSekolah = 106.8035761;
            
            const userLat = position.coords.latitude;
            const userLong = position.coords.longitude;
            
            const R = 6371000; 
            const dLat = (userLat - latSekolah) * Math.PI / 180;
            const dLong = (userLong - longSekolah) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(latSekolah * Math.PI / 180) * Math.cos(userLat * Math.PI / 180) * Math.sin(dLong/2) * Math.sin(dLong/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            const jarak = R * c;

            if (jarak <= 590) {
                document.getElementById('info-lokasi').innerHTML = "✅ Lokasi Valid (Jarak: " + jarak.toFixed(2) + "m)";
                document.getElementById('info-lokasi').className = "status-valid";
                document.getElementById('lat').value = userLat;
                document.getElementById('long').value = userLong;
                isLokasiValid = true; 
                cekSemuaValidasi();   
            } else {
                alert("Pendaftaran Gagal!\nAnda berada di luar radius 590m dari sekolah. Jarak Anda: " + Math.round(jarak) + " meter.");
                document.getElementById('info-lokasi').innerHTML = "❌ Lokasi Terlalu Jauh";
                document.getElementById('info-lokasi').className = "status-invalid";
                isLokasiValid = false;
                cekSemuaValidasi();
            }
        }, function(error) {
            alert("Mohon izinkan/aktifkan GPS Lokasi Anda di Browser.");
            document.getElementById('info-lokasi').innerHTML = "❌ Akses GPS Ditolak";
        });
    }
}

function perbaruiPratinjauBerkasSistem(inputElement, textTargetId, boxTargetId) {
    const textTarget = document.getElementById(textTargetId);
    const boxTarget = document.getElementById(boxTargetId);
    
    if (inputElement.files && inputElement.files[0]) {
        const namaFile = inputElement.files[0].name;
        textTarget.innerText = "✓ Berkas Terpilih";
        boxTarget.querySelector('.upload-hint').innerText = namaFile;
        boxTarget.classList.add('file-loaded');
    } else {
        textTarget.innerText = "Ketuk / Seret Berkas";
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
        document.getElementById('txt_tabungan_kjp').innerText = "Ketuk / Seret Tabungan KJP";
        document.getElementById('box_tabungan_kjp').querySelector('.upload-hint').innerText = "Format: JPG, PNG, PDF";
        document.getElementById('box_tabungan_kjp').classList.remove('file-loaded');
    }
}

function jalankanValidasiNik(input) {
    const badgeNik = document.getElementById("box_status_nik");
    if (input.value.length === 16) {
        badgeNik.innerText = "✅ NIK Valid";
        badgeNik.className = "status-valid";
        isNikValid = true; 
    } else {
        badgeNik.innerText = "❌ Wajib 16 Angka";
        badgeNik.className = "status-invalid";
        isNikValid = false;
    }
    cekSemuaValidasi(); 
}

function jalankanValidasiNisn(input) {
    const badgeNisn = document.getElementById("box_status_nisn");
    if (input.value.length === 10) {
        badgeNisn.innerText = "✅ NISN Valid";
        badgeNisn.className = "status-valid";
        isNisnValid = true; 
    } else {
        badgeNisn.innerText = "❌ Wajib 10 Angka";
        badgeNisn.className = "status-invalid";
        isNisnValid = false;
    }
    cekSemuaValidasi(); 
}

function jalankanValidasiSistemKomplit() {
    const tglLahirInput = document.getElementById("tanggal_lahir").value;
    const badgeUmur = document.getElementById("box_status_umur");
    const kkInput = document.getElementById("no_kk").value;
    const badgeKk = document.getElementById("box_status_kk");
    
    if (tglLahirInput) {
        const lahir = new Date(tglLahirInput);
        const hariIni = new Date();
        let umur = hariIni.getFullYear() - lahir.getFullYear();
        const m = hariIni.getMonth() - lahir.getMonth();
        if (m < 0 || (m === 0 && hariIni.getDate() < lahir.getDate())) { umur--; }
        
        if (umur >= 13 && umur <= 21) {
            badgeUmur.innerText = "✅ " + umur + " Thn";
            badgeUmur.className = "status-valid";
            isUmurValid = true; 
        } else {
            badgeUmur.innerText = "❌ " + umur + " Thn (Tidak Lolos)";
            badgeUmur.className = "status-invalid";
            isUmurValid = false;
        }
    }

    if (kkInput) {
        if (kkInput.length === 16 && kkInput.startsWith("31")) {
            badgeKk.innerText = "✅ KK DKI Valid";
            badgeKk.className = "status-valid";
            isKkValid = true; 
        } else {
            badgeKk.innerText = "❌ Bukan KK DKI / < 16 Digit";
            badgeKk.className = "status-invalid";
            isKkValid = false;
        }
    }

    cekSemuaValidasi(); 
}

document.addEventListener("DOMContentLoaded", function() {
    const formElements = document.querySelectorAll("input:not([type='file']), select, textarea");
    formElements.forEach(el => {
        if (el.name && sessionStorage.getItem(el.name)) {
            el.value = sessionStorage.getItem(el.name);
            if(el.name === "tanggal_lahir" || el.name === "no_kk") jalankanValidasiSistemKomplit();
            if(el.name === "nisn") jalankanValidasiNisn(el);
            if(el.name === "nik") jalankanValidasiNik(el);
            if(el.name === "status_kjp") toggleKjpFormSistem(el.value);
        }
        el.addEventListener("input", function() { sessionStorage.setItem(el.name, el.value); });
        el.addEventListener("change", function() { sessionStorage.setItem(el.name, el.value); });
    });
});
</script>
</body>
</html>