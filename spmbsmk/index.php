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
// 2. INFORMASI KOMPETISI PENDAFTAR SAAT INI (TANPA CADANGAN)
// ===================================================================
function hitungPendaftarGelombang($jurusan, $id_gel, $conn) {
    $q = mysqli_query($conn, "SELECT COUNT(*) as total FROM pendaftar WHERE pilihan_jurusan = '$jurusan' AND gelombang = '$id_gel'");
    return mysqli_fetch_assoc($q)['total'];
}

$kuota_aktif = ($gelombang_id == 1) ? $kuota_g1 : $kuota_g2;

$total_akl = hitungPendaftarGelombang('Akuntansi dan Keuangan Lembaga', $gelombang_id, $conn);
$teks_akl = "Total Pendaftar: $total_akl Siswa (Memperebutkan $kuota_aktif Kuota)";

$total_mplb = hitungPendaftarGelombang('Manajemen Perkantoran dan Layanan Bisnis', $gelombang_id, $conn);
$teks_mplb = "Total Pendaftar: $total_mplb Siswa (Memperebutkan $kuota_aktif Kuota)";

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
    $tanggal_kk = trim(mysqli_real_escape_string($conn, $_POST['tanggal_kk']));

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
            $pesan = "<div class='alert-msg invalid-msg'><b>Pendaftaran Gagal!</b><br>Nomor rekening KJP tidak valid (minimal 5 angka, maksimal 15 angka).</div>";
        }
    }

    $tanggal_lahir_obj = new DateTime($tgl_lahir);
    $hari_ini_obj      = new DateTime(); 
    $hitung_umur       = $hari_ini_obj->diff($tanggal_lahir_obj)->y;

    $batas_kk = strtotime('2025-06-15');
    $input_kk = strtotime($tanggal_kk);

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

    $cek_duplikat = "SELECT * FROM pendaftar WHERE gelombang = '$gelombang_id' AND (no_ijazah = '$no_ijazah' OR nisn = '$nisn' OR no_kk = '$no_kk' OR nik = '$nik')";
    $hasil_cek    = mysqli_query($conn, $cek_duplikat);

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
        $pesan = "<div class='alert-msg invalid-msg'><b>Pendaftaran Gagal!</b><br>Berkas melebihi batas maksimal 3MB.</div>";
    } elseif ($ekstensi_ilegal) {
        $pesan = "<div class='alert-msg invalid-msg'><b>Format Berkas Ilegal!</b><br>Sistem hanya menerima file gambar atau dokumen (.pdf).</div>";
    } elseif ($jarak_tidak_valid) {
        $pesan = "<div class='alert-msg invalid-msg'><b>Pendaftaran Ditolak!</b><br>Lokasi Anda tidak valid atau berada di luar area sekolah. Silakan lakukan Verifikasi Lokasi.</div>";
    } elseif (mysqli_num_rows($hasil_cek) > 0) {
        $pesan = "<div class='alert-msg invalid-msg'><b>Pendaftaran Ditolak!</b><br>Maaf, NIK, NISN, Nomor Peserta SIDANIRA, atau Nomor KK Anda sudah pernah terdaftar di sistem kami. Anda tidak bisa mendaftar ganda.</div>";
    } elseif (substr($no_kk, 0, 2) !== '31' || strlen($no_kk) !== 16) {
        $pesan = "<div class='alert-msg invalid-msg'><b>Pendaftaran Ditolak!</b><br>Program Khusus ini hanya menerima warga pemilik KK resmi Provinsi DKI Jakarta.</div>";
    } elseif ($input_kk >= $batas_kk) {
        $pesan = "<div class='alert-msg invalid-msg'><b>Pendaftaran Ditolak!</b><br>Tanggal terbit Kartu Keluarga (KK) wajib diterbitkan sebelum tanggal 15 Juni 2025.</div>";
    } elseif ($hitung_umur < 13 || $hitung_umur > 21) {
        $pesan = "<div class='alert-msg invalid-msg'><b>Pendaftaran Ditolak!</b><br>Kriteria usia tidak sesuai persyaratan sistem PPDB (13 - 21 Tahun).</div>";
    } elseif ($skl > 100 || $tka > 100 || $skl < 0 || $tka < 0) {
        $pesan = "<div class='alert-msg invalid-msg'><b>Pendaftaran Ditolak!</b><br>Rentang penginputan nilai wajib berada di skala 0.00 - 100.00.</div>";
    } else {
        $gelombang_final = $gelombang_id;

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
            
            $query = "INSERT INTO pendaftar (no_pendaftaran, nama_lengkap, nik, tempat_lahir, tanggal_lahir, nisn, no_ijazah, asal_sekolah, riwayat_penyakit, alamat, kelurahan, kecamatan, no_whatsapp, pilihan_jurusan, nilai_skl, nilai_tka, nilai_test, file_ijazah, file_tka, file_kk, file_akte, no_kk, status_konfirmasi, file_ktp_bapak, file_ktp_ibu, file_sptjm, status_kjp, no_rek_kjp, file_tabungan_kjp, gelombang, tanggal_daftar, is_detail_filled) 
                      VALUES ('$no_pendaftaran', '$nama', '$nik', '$tmpl_lahir', '$tgl_lahir', '$nisn', '$no_ijazah', '$asal', '$riwayat_penyakit', '$alamat', '$kelurahan', '$kecamatan', '$wa', '$jurusan', '$skl', '$tka', '0.00', '$nama_ijazah', '$nama_tka', '$nama_kk', '$nama_akte', '$no_kk', 'Menunggu', '$nama_ktp_bapak', '$nama_ktp_ibu', '$nama_sptjm', '$status_kjp', '$no_rek_kjp', '$nama_tabungan_kjp', '$gelombang_final', '$waktu_daftar', 1)";

            if (mysqli_query($conn, $query)) {
                $id_pendaftar = mysqli_insert_id($conn);
                
                $jenis_kelamin = htmlspecialchars(trim(mysqli_real_escape_string($conn, $_POST['jenis_kelamin'])));
                $nama_ibu = htmlspecialchars(trim(mysqli_real_escape_string($conn, $_POST['nama_ibu'])));
                $agama = htmlspecialchars(trim(mysqli_real_escape_string($conn, $_POST['agama'])));
                $npsn_sekolah = trim(mysqli_real_escape_string($conn, $_POST['npsn_sekolah']));
                
                $kebutuhan_khusus = htmlspecialchars(trim(mysqli_real_escape_string($conn, $_POST['kebutuhan_khusus'])));
                if (empty($kebutuhan_khusus)) { $kebutuhan_khusus = "Tidak Ada"; }

                $query_detail = "INSERT INTO pendaftar_detail 
                                (pendaftar_id, jenis_kelamin, tanggal_kk, nama_ibu, agama, npsn_sekolah, kebutuhan_khusus) 
                                VALUES 
                                ('$id_pendaftar', '$jenis_kelamin', '$tanggal_kk', '$nama_ibu', '$agama', '$npsn_sekolah', '$kebutuhan_khusus')";
                
                mysqli_query($conn, $query_detail);

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
        
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --bg-color: #f8fafc;
            --surface: #ffffff;
            --border: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --success: #10b981;
            --success-bg: #dcfce7;
            --danger: #ef4444;
            --danger-bg: #fee2e2;
            --radius-lg: 16px;
            --radius-md: 10px;
        }

        * { box-sizing: border-box; }
        
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--bg-color); 
            color: var(--text-main); 
            margin: 0; 
            padding: 40px 15px; 
            line-height: 1.6;
        }
        
        .container { max-width: 850px; margin: 0 auto; }
        
        .input-kapital { text-transform: uppercase; font-weight: 600; }
        
        /* Cards Design - Minimalist */
        .card { 
            background: var(--surface); 
            border-radius: var(--radius-lg); 
            margin-bottom: 30px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05); 
            border: 1px solid var(--border); 
            overflow: hidden; 
            transition: all 0.3s ease;
        }
        .card:focus-within {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
            border-color: #cbd5e1;
        }

        .card-header { 
            padding: 24px 30px 20px; 
            border-bottom: 1px solid var(--border); 
            display: flex; 
            align-items: center; 
            gap: 12px;
            background: #ffffff;
        }
        
        .step-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: #eef2ff;
            color: var(--primary);
            border-radius: 50%;
            font-weight: 800;
            font-size: 14px;
        }

        .card-header h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -0.3px;
        }

        .card-body { padding: 30px; }
        
        /* Form Grids */
        .grid-form { 
            display: grid; 
            grid-template-columns: repeat(2, 1fr); 
            gap: 24px; 
        }
        .full-width { grid-column: 1 / -1; }
        
        /* Inputs & Labels */
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label { 
            font-size: 13px; 
            font-weight: 700; 
            color: #334155; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
        }
        .form-group label span { color: var(--danger); margin-left: 2px; }
        
        .form-group input:not([type='file']), 
        .form-group select, 
        .form-group textarea { 
            width: 100%; 
            padding: 14px 16px; 
            border: 1.5px solid var(--border); 
            border-radius: var(--radius-md); 
            font-family: inherit; 
            font-size: 14px; 
            background: #f8fafc; 
            color: var(--text-main); 
            outline: none; 
            transition: all 0.2s ease;
        }
        
        .form-group input:not([type='file']):focus, 
        .form-group select:focus, 
        .form-group textarea:focus { 
            background: #ffffff; 
            border-color: var(--primary); 
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1); 
        }
        .form-group textarea { resize: vertical; min-height: 100px; }

        /* Helper / Validation text under input */
        .validation-msg {
            font-size: 12px;
            font-weight: 600;
            margin-top: 4px;
            padding: 4px 10px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            width: max-content;
            transition: all 0.2s ease;
        }
        .msg-valid { background: var(--success-bg); color: var(--success); border: 1px solid #bbf7d0; }
        .msg-invalid { background: var(--danger-bg); color: var(--danger); border: 1px solid #fecaca; }
        .msg-info { background: #e0e7ff; color: var(--primary); border: 1px solid #c7d2fe; }
        
        /* Custom Upload Box */
        .custom-upload-box { 
            position: relative; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center; 
            border: 2px dashed #cbd5e1; 
            border-radius: var(--radius-md); 
            padding: 24px 16px; 
            text-align: center; 
            background: #f8fafc; 
            cursor: pointer; 
            transition: all 0.2s ease; 
        }
        .custom-upload-box:hover { background: #f0fdf4; border-color: var(--success); }
        .custom-upload-box input[type="file"] { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 2; }
        
        .upload-icon { font-size: 24px; margin-bottom: 8px; pointer-events: none; transition: transform 0.2s; }
        .custom-upload-box:hover .upload-icon { transform: translateY(-3px); }
        .upload-text { font-size: 13px; font-weight: 700; color: #475569; pointer-events: none; }
        .upload-hint { font-size: 12px; color: #94a3b8; font-weight: 500; pointer-events: none; margin-top: 4px; }
        
        .custom-upload-box.file-loaded { background: var(--success-bg); border: 2px solid var(--success); }
        .custom-upload-box.file-loaded .upload-icon { content: "✅"; transform: scale(1.1); }
        .custom-upload-box.file-loaded .upload-text { color: #065f46; }

        /* Buttons & Alerts */
        .btn-primary { 
            background: var(--primary); 
            color: white; 
            border: none; 
            padding: 18px 24px; 
            border-radius: var(--radius-md); 
            font-weight: 700; 
            cursor: pointer; 
            transition: all 0.2s ease; 
            width: 100%; 
            font-size: 15px; 
            text-transform: uppercase; 
            letter-spacing: 1px;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2); 
        }
        .btn-primary:hover:not(:disabled) { background: var(--primary-hover); transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3); }
        .btn-primary:active:not(:disabled) { transform: translateY(0); }
        .btn-primary:disabled { background: #e2e8f0; color: #94a3b8; cursor: not-allowed; box-shadow: none; }

        .btn-secondary {
            background: #ffffff;
            color: var(--text-main);
            border: 1px solid var(--border);
            padding: 14px 20px;
            border-radius: var(--radius-md);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-secondary:hover { background: #f1f5f9; border-color: #cbd5e1; }

        .alert-box { 
            background: #eff6ff; 
            border-left: 4px solid #3b82f6; 
            padding: 16px 20px; 
            border-radius: 8px; 
            margin-bottom: 24px; 
            color: #1e3a8a; 
            font-size: 14px; 
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert-msg {
            padding: 16px;
            border-radius: var(--radius-md);
            margin-bottom: 24px;
            font-size: 14px;
        }
        .invalid-msg { background: var(--danger-bg); border: 1px solid #fecaca; color: #991b1b; }

        /* Header styling */
        .page-header { text-align: center; margin-bottom: 40px; }
        .page-header img { max-height: 70px; margin: 0 8px; vertical-align: middle; }
        .page-header h2 { margin: 20px 0 5px; font-weight: 800; font-size: 24px; color: var(--text-main); letter-spacing: -0.5px; }
        .page-header p { margin: 0 0 16px; color: var(--text-muted); font-size: 15px; font-weight: 500; }
        .gelombang-tag { background: #e0e7ff; color: var(--primary); padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 700; display: inline-block; }
        
        @media (max-width: 640px) { 
            body { padding: 20px 10px; }
            .grid-form { grid-template-columns: 1fr; gap: 20px; } 
            .card-header, .card-body { padding: 20px; }
            .page-header img { max-height: 50px; }
        }
    </style>
</head>
<body>

<div class="container">
    <?php
        // Logika Peringatan Pembayaran (Paid)
        $tanggal_sekarang = new DateTime('2026-07-08'); // Tanggal hari ini
        $batas_bayar = new DateTime('2026-07-11');
        $selisih = $tanggal_sekarang->diff($batas_bayar)->days;

        if ($tanggal_sekarang <= $batas_bayar) {
            echo "
            <div style='background: #fee2e2; border: 1px solid #f87171; color: #991b1b; padding: 15px; border-radius: 12px; margin-bottom: 20px; text-align: center; font-weight: 700; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);'>
                ⚠️ PERINGATAN PEMBAYARAN: Batas pembayaran terakhir adalah 11 Juli 2026. <br>
                <span style='color: #dc2626;'>Sisa waktu Anda tinggal " . $selisih . " hari lagi. Mohon segera selesaikan administrasi!</span>
            </div>";
        }
    ?>
    <div class="page-header">
        <img src="logo/logopb.jpg" alt="Logo Yayasan">
        <img src="logo/logopemda.png" alt="Logo Pemda">
        <img src="logo/logosmkpb.png" alt="Logo SMK">
        <h2>PORTAL SPMB ONLINE</h2>
        <p>SMKS PERMATA BUNDA I JAKARTA</p>
        <span class="gelombang-tag">🔥 <?php echo $gelombang_aktif; ?></span>
    </div>

    <?php if ($pesan != "") echo $pesan; ?>

    <?php if (!$pendaftaran_buka): ?>
        <div class="alert-msg invalid-msg" style="text-align: center; padding: 30px;">
            <h3 style="margin-top:0;">⚠️ Pendaftaran Ditutup</h3>
            Mohon Maaf, Sistem Penerimaan Siswa Baru Saat Ini Sedang Ditutup Sementara.<br>
            Silakan hubungi pihak panitia sekolah untuk informasi lebih lanjut.
        </div>
    <?php else: ?>
        <form action="" method="POST" enctype="multipart/form-data">
            <div style="display:none;">
                <input type="text" name="website_checker" value="">
            </div>

            <!-- SECTION 1: Identitas Siswa -->
            <div class="card">
                <div class="card-header">
                    <div class="step-indicator">1</div>
                    <h3>Identitas Calon Siswa</h3>
                </div>
                <div class="card-body">
                    
                    <div class="form-group full-width" style="margin-bottom: 24px;">
                        <label>Nomor Peserta SIDANIRA <span>*</span></label>
                        <input type="tel" name="no_ijazah" placeholder="Ketik hanya angka nomor peserta SIDANIRA" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                    </div>

                    <div class="grid-form">
                        <div class="form-group">
                            <label>NIK (16 Digit) <span>*</span></label>
                            <input type="tel" name="nik" maxlength="16" placeholder="Masukkan NIK sesuai KK" pattern="[0-9]{16}" oninput="this.value = this.value.replace(/[^0-9]/g, ''); jalankanValidasiNik(this)" required>
                            <div id="box_status_nik" class="validation-msg" style="display:none;"></div>
                        </div>

                        <div class="form-group">
                            <label>NISN (10 Digit) <span>*</span></label>
                            <input type="tel" name="nisn" maxlength="10" placeholder="Nomor Induk Siswa Nasional" pattern="[0-9]{10}" oninput="this.value = this.value.replace(/[^0-9]/g, ''); jalankanValidasiNisn(this)" required>
                            <div id="box_status_nisn" class="validation-msg" style="display:none;"></div>
                        </div>

                        <div class="form-group">
                            <label>No. KK (16 Digit) <span>*</span></label>
                            <input type="tel" id="no_kk" name="no_kk" maxlength="16" placeholder="Diawali angka 31 (DKI Jakarta)" pattern="[0-9]{16}" oninput="this.value = this.value.replace(/[^0-9]/g, ''); jalankanValidasiSistemKomplit()" required>
                            <div id="box_status_kk" class="validation-msg" style="display:none;"></div>
                        </div>

                        <div class="form-group">
                            <label>Tanggal Terbit KK <span>*</span></label>
                            <input type="date" id="tanggal_kk" name="tanggal_kk" onchange="jalankanValidasiSistemKomplit()" required>
                            <div id="box_status_tgl_kk" class="validation-msg" style="display:none;"></div>
                        </div>

                        <div class="form-group full-width">
                            <label>Nama Lengkap <span>*</span></label>
                            <input type="text" name="nama_lengkap" class="input-kapital" placeholder="Sesuai Akta Kelahiran" oninput="this.value = this.value.toUpperCase()" required>
                        </div>

                        <div class="form-group">
                            <label>Tempat Lahir <span>*</span></label>
                            <input type="text" name="tempat_lahir" placeholder="Kota tempat lahir" style="text-transform: capitalize;" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Tanggal Lahir & Usia <span>*</span></label>
                            <input type="date" id="tanggal_lahir" name="tanggal_lahir" onchange="jalankanValidasiSistemKomplit()" required>
                            <div id="box_status_umur" class="validation-msg" style="display:none;"></div>
                        </div>

                        <div class="form-group">
                            <label>Jenis Kelamin <span>*</span></label>
                            <select name="jenis_kelamin" required>
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>
                        
                        <div class="form-group full-width">
                            <label>Pilihan Sekolah Gratis / Konsentrasi <span>*</span></label>
                            <select name="pilihan_jurusan" required>
                                <option value="">-- Pilih Program / Konsentrasi --</option>
                                <option value="Akuntansi dan Keuangan Lembaga">Akuntansi dan Keuangan Lembaga (AKL) - <?php echo $teks_akl; ?></option>
                                <option value="Manajemen Perkantoran dan Layanan Bisnis">Manajemen Perkantoran dan Layanan Bisnis (MPLB) - <?php echo $teks_mplb; ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="alert-box" style="margin-top: 24px;">
                        <span style="font-size: 24px;">🛡️</span>
                        <div>
                            <strong>Verifikasi Kependudukan Terpusat</strong><br>
                            Sistem Dukcapil akan memverifikasi kesesuaian NIK, Nama, dan Tanggal Lahir Anda.
                        </div>
                    </div>

                    <div class="grid-form">
                        <div class="form-group">
                            <label>Agama <span>*</span></label>
                            <select name="agama" required>
                                <option value="">-- Pilih Agama --</option>
                                <option value="Islam">Islam</option>
                                <option value="Kristen Protestan">Kristen Protestan</option>
                                <option value="Katolik">Katolik</option>
                                <option value="Hindu">Hindu</option>
                                <option value="Buddha">Buddha</option>
                                <option value="Konghucu">Konghucu</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Kebutuhan Khusus <span>*</span></label>
                            <select name="kebutuhan_khusus" required>
                                <option value="Tidak ada">Tidak ada</option>
                                <option value="Tunanetra">Tunanetra</option>
                                <option value="Tunarungu">Tunarungu</option>
                                <option value="Tunadaksa">Tunadaksa</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Data Orang Tua & Alamat -->
            <div class="card">
                <div class="card-header">
                    <div class="step-indicator">2</div>
                    <h3>Data Orang Tua & Alamat</h3>
                </div>
                <div class="card-body grid-form">
                    <div class="form-group">
                        <label>Nama Ibu Kandung <span>*</span></label>
                        <input type="text" name="nama_ibu" class="input-kapital" placeholder="Nama ibu kandung sesuai KK" oninput="this.value = this.value.toUpperCase()" required>
                    </div>
                    <div class="form-group">
                        <label>No. HP Orang Tua / Wali <span>*</span></label>
                        <input type="tel" name="no_whatsapp" maxlength="15" placeholder="08xxxxxxxx" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                    </div>
                    
                    <div class="form-group full-width">
                        <label>Alamat Lengkap Domisili <span>*</span></label>
                        <textarea name="alamat" placeholder="Tuliskan nama jalan, RT/RW, dan detail rumah" required></textarea>
                    </div>

                    <div class="form-group">
                        <label>Kecamatan <span>*</span></label>
                        <input type="text" name="kecamatan" placeholder="Contoh: Tambora" required>
                    </div>
                    <div class="form-group">
                        <label>Kelurahan <span>*</span></label>
                        <input type="text" name="kelurahan" placeholder="Contoh: Angke" required>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: Riwayat Akademik -->
            <div class="card">
                <div class="card-header">
                    <div class="step-indicator">3</div>
                    <h3>Riwayat Akademik Asal</h3>
                </div>
                <div class="card-body">
                    <div class="grid-form" style="margin-bottom: 24px;">
                        <div class="form-group">
                            <label>NPSN Sekolah Asal <span>*</span></label>
                            <input type="tel" name="npsn_sekolah" maxlength="8" placeholder="8 Karakter NPSN" pattern="[0-9]{8}" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                        </div>
                        <div class="form-group">
                            <label>Nama Sekolah Asal <span>*</span></label>
                            <input type="text" name="asal_sekolah" placeholder="Nama lengkap SMP/MTs asal" required>
                        </div>
                    </div>

                    <div style="border-top: 1px solid var(--border); padding-top: 24px;" class="grid-form">
                        <div class="form-group">
                            <label style="color: var(--primary);">Nilai Rata-Rata SIDANIRA <span>*</span></label>
                            <input type="number" name="nilai_skl" step="0.01" min="0" max="100" placeholder="0.00" oninput="if(this.value > 100) this.value = 100; if(this.value < 0) this.value = 0;" required style="background: #eef2ff; border-color: #c7d2fe; color: #3730a3; font-weight: bold;">
                        </div>
                        <div class="form-group">
                            <label style="color: var(--primary);">Nilai Rata-Rata Tes TKA <span>*</span></label>
                            <input type="number" name="nilai_tka" step="0.01" min="0" max="100" placeholder="0.00" oninput="if(this.value > 100) this.value = 100; if(this.value < 0) this.value = 0;" required style="background: #eef2ff; border-color: #c7d2fe; color: #3730a3; font-weight: bold;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 4: Upload Berkas & Keamanan -->
            <div class="card">
                <div class="card-header">
                    <div class="step-indicator">4</div>
                    <h3>Upload Berkas Pendukung</h3>
                </div>
                <div class="card-body">
                    <div class="grid-form">
                        <div class="form-group">
                            <label>1. Scan Ijazah / SK Sidanira Asli <span>*</span></label>
                            <div class="custom-upload-box" id="box_ijazah">
                                <input type="file" name="file_ijazah" accept=".jpg,.jpeg,.png,.pdf" onchange="perbaruiPratinjauBerkasSistem(this, 'txt_ijazah', 'box_ijazah')" required>
                                <span class="upload-icon">📄</span>
                                <span class="upload-text" id="txt_ijazah">Pilih / Seret Berkas</span>
                                <span class="upload-hint">JPG, PNG, PDF (Maks 3MB)</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>2. Scan Hasil Nilai TKA / SKHU <span>*</span></label>
                            <div class="custom-upload-box" id="box_tka">
                                <input type="file" name="file_tka" accept=".jpg,.jpeg,.png,.pdf" onchange="perbaruiPratinjauBerkasSistem(this, 'txt_tka', 'box_tka')" required>
                                <span class="upload-icon">📄</span>
                                <span class="upload-text" id="txt_tka">Pilih / Seret Berkas</span>
                                <span class="upload-hint">JPG, PNG, PDF (Maks 3MB)</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>3. Scan Kartu Keluarga (KK) <span>*</span></label>
                            <div class="custom-upload-box" id="box_kk_up">
                                <input type="file" name="file_kk" accept=".jpg,.jpeg,.png,.pdf" onchange="perbaruiPratinjauBerkasSistem(this, 'txt_kk_up', 'box_kk_up')" required>
                                <span class="upload-icon">📄</span>
                                <span class="upload-text" id="txt_kk_up">Pilih / Seret Berkas</span>
                                <span class="upload-hint">JPG, PNG, PDF (Maks 3MB)</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>4. Scan Akte Kelahiran <span>*</span></label>
                            <div class="custom-upload-box" id="box_akte">
                                <input type="file" name="file_akte" accept=".jpg,.jpeg,.png,.pdf" onchange="perbaruiPratinjauBerkasSistem(this, 'txt_akte', 'box_akte')" required>
                                <span class="upload-icon">📄</span>
                                <span class="upload-text" id="txt_akte">Pilih / Seret Berkas</span>
                                <span class="upload-hint">JPG, PNG, PDF (Maks 3MB)</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>5. Scan KTP Bapak <span>*</span></label>
                            <div class="custom-upload-box" id="box_ktp_bapak">
                                <input type="file" name="file_ktp_bapak" accept=".jpg,.jpeg,.png,.pdf" onchange="perbaruiPratinjauBerkasSistem(this, 'txt_ktp_bapak', 'box_ktp_bapak')" required>
                                <span class="upload-icon">💳</span>
                                <span class="upload-text" id="txt_ktp_bapak">Pilih / Seret Berkas</span>
                                <span class="upload-hint">JPG, PNG, PDF (Maks 3MB)</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>6. Scan KTP Ibu <span>*</span></label>
                            <div class="custom-upload-box" id="box_ktp_ibu">
                                <input type="file" name="file_ktp_ibu" accept=".jpg,.jpeg,.png,.pdf" onchange="perbaruiPratinjauBerkasSistem(this, 'txt_ktp_ibu', 'box_ktp_ibu')" required>
                                <span class="upload-icon">💳</span>
                                <span class="upload-text" id="txt_ktp_ibu">Pilih / Seret Berkas</span>
                                <span class="upload-hint">JPG, PNG, PDF (Maks 3MB)</span>
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <label>7. Surat Pertanggungjawaban Mutlak (SPTJM) <span>*</span></label>
                            <div class="custom-upload-box" id="box_sptjm" style="background: #fefce8; border-color: #fde047;">
                                <input type="file" name="file_sptjm" accept=".jpg,.jpeg,.png,.pdf" onchange="perbaruiPratinjauBerkasSistem(this, 'txt_sptjm', 'box_sptjm')" required>
                                <span class="upload-icon">📜</span>
                                <span class="upload-text" id="txt_sptjm">Unggah Dokumen SPTJM Kesini</span>
                                <span class="upload-hint">JPG, PNG, PDF (Maks 3MB)</span>
                            </div>
                        </div>
                    </div>

                    <div style="border-top: 1px solid var(--border); margin-top: 30px; padding-top: 30px;">
                        <div class="form-group full-width" style="margin-bottom: 20px;">
                            <label>Status Kepemilikan KJP</label>
                            <select name="status_kjp" onchange="toggleKjpFormSistem(this.value)" required>
                                <option value="Tidak">Tidak Memiliki KJP</option>
                                <option value="Ya">Ya, Saya Pemilik KJP Aktif</option>
                            </select>
                        </div>

                        <div id="wrapper_kjp_kondisional" style="display: none; background: #f8fafc; border: 1px dashed var(--border); padding: 24px; border-radius: var(--radius-md); margin-bottom: 30px;">
                            <div class="grid-form">
                                <div class="form-group">
                                    <label>Nomor Rekening Tabungan KJP <span>*</span></label>
                                    <input type="tel" name="no_rek_kjp" id="no_rek_kjp_field" maxlength="15" placeholder="Ketik nomor rekening" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>
                                <div class="form-group">
                                    <label>8. Scan Buku Tabungan KJP <span>*</span></label>
                                    <div class="custom-upload-box" id="box_tabungan_kjp" style="min-height: 80px; padding: 15px;">
                                        <input type="file" name="file_tabungan_kjp" id="file_tabungan_kjp_field" accept=".jpg,.jpeg,.png,.pdf" onchange="perbaruiPratinjauBerkasSistem(this, 'txt_tabungan_kjp', 'box_tabungan_kjp')">
                                        <span class="upload-text" id="txt_tabungan_kjp" style="font-size: 12px;">Pilih File</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: var(--radius-md); padding: 24px; text-align: center;">
                            <h4 style="margin: 0 0 16px; font-size: 14px; text-transform: uppercase; color: var(--text-main);">Verifikasi Lokasi Sekolah</h4>
                            <button type="button" class="btn-secondary" onclick="cekLokasi()" style="margin-bottom: 16px;">
                                📍 Verifikasi Lokasi Sekarang
                            </button>
                            <div id="info-lokasi" class="validation-msg msg-info" style="display: flex; margin: 0 auto;">Belum diverifikasi. Tekan tombol di atas.</div>
                            <input type="hidden" name="lat" id="lat">
                            <input type="hidden" name="long" id="long">
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" name="daftar" id="btn_submit_form" class="btn-primary" disabled>
                Proses Pendaftaran
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
let isKkDateValid = false;

function cekSemuaValidasi() {
    const btnSubmit = document.getElementById("btn_submit_form");
    if (isLokasiValid && isNisnValid && isNikValid && isUmurValid && isKkValid && isKkDateValid) {
        btnSubmit.disabled = false; 
    } else {
        btnSubmit.disabled = true;  
    }
}

function cekLokasi() {
    const infoLokasi = document.getElementById('info-lokasi');
    infoLokasi.innerHTML = "⏳ Sedang mencari lokasi Anda...";
    infoLokasi.className = "validation-msg msg-info";

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
                infoLokasi.innerHTML = "✅ Valid (Jarak: " + jarak.toFixed(2) + "m)";
                infoLokasi.className = "validation-msg msg-valid";
                document.getElementById('lat').value = userLat;
                document.getElementById('long').value = userLong;
                isLokasiValid = true; 
            } else {
                alert("Pendaftaran Gagal!\nAnda berada di luar radius 590m dari sekolah. Jarak Anda: " + Math.round(jarak) + " meter.");
                infoLokasi.innerHTML = "❌ Terlalu Jauh";
                infoLokasi.className = "validation-msg msg-invalid";
                isLokasiValid = false;
            }
            cekSemuaValidasi();
        }, function(error) {
            alert("Mohon izinkan/aktifkan GPS Lokasi Anda di Browser.");
            infoLokasi.innerHTML = "❌ Akses GPS Ditolak";
            infoLokasi.className = "validation-msg msg-invalid";
        });
    }
}

function perbaruiPratinjauBerkasSistem(inputElement, textTargetId, boxTargetId) {
    const textTarget = document.getElementById(textTargetId);
    const boxTarget = document.getElementById(boxTargetId);
    
    if (inputElement.files && inputElement.files[0]) {
        const namaFile = inputElement.files[0].name;
        textTarget.innerText = "✓ Berkas Terpilih";
        if(boxTarget.querySelector('.upload-hint')){
            boxTarget.querySelector('.upload-hint').innerText = namaFile;
        }
        boxTarget.classList.add('file-loaded');
    } else {
        textTarget.innerText = "Pilih / Seret Berkas";
        if(boxTarget.querySelector('.upload-hint')){
            boxTarget.querySelector('.upload-hint').innerText = "JPG, PNG, PDF (Maks 3MB)";
        }
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
        document.getElementById('txt_tabungan_kjp').innerText = "Pilih File";
        document.getElementById('box_tabungan_kjp').classList.remove('file-loaded');
    }
}

function jalankanValidasiNik(input) {
    const badgeNik = document.getElementById("box_status_nik");
    badgeNik.style.display = "flex";
    if (input.value.length === 16) {
        badgeNik.innerHTML = "✓ NIK Valid";
        badgeNik.className = "validation-msg msg-valid";
        isNikValid = true; 
    } else {
        badgeNik.innerHTML = "❌ Wajib 16 Digit";
        badgeNik.className = "validation-msg msg-invalid";
        isNikValid = false;
    }
    cekSemuaValidasi(); 
}

function jalankanValidasiNisn(input) {
    const badgeNisn = document.getElementById("box_status_nisn");
    badgeNisn.style.display = "flex";
    if (input.value.length === 10) {
        badgeNisn.innerHTML = "✓ NISN Valid";
        badgeNisn.className = "validation-msg msg-valid";
        isNisnValid = true; 
    } else {
        badgeNisn.innerHTML = "❌ Wajib 10 Digit";
        badgeNisn.className = "validation-msg msg-invalid";
        isNisnValid = false;
    }
    cekSemuaValidasi(); 
}

function jalankanValidasiSistemKomplit() {
    const tglLahirInput = document.getElementById("tanggal_lahir").value;
    const badgeUmur = document.getElementById("box_status_umur");
    
    const kkInput = document.getElementById("no_kk").value;
    const badgeKk = document.getElementById("box_status_kk");
    
    const tglKkInput = document.getElementById("tanggal_kk").value;
    const badgeTglKk = document.getElementById("box_status_tgl_kk");
    
    // Cek Umur (Langsung Nampil Di Bawah Input Tanggal Lahir)
    if (tglLahirInput) {
        badgeUmur.style.display = "flex";
        const lahir = new Date(tglLahirInput);
        const hariIni = new Date();
        let umur = hariIni.getFullYear() - lahir.getFullYear();
        const m = hariIni.getMonth() - lahir.getMonth();
        if (m < 0 || (m === 0 && hariIni.getDate() < lahir.getDate())) { umur--; }
        
        if (umur >= 13 && umur <= 21) {
            badgeUmur.innerHTML = "✓ Usia " + umur + " Tahun (Lolos)";
            badgeUmur.className = "validation-msg msg-valid";
            isUmurValid = true; 
        } else {
            badgeUmur.innerHTML = "❌ Usia " + umur + " Tahun (Ditolak)";
            badgeUmur.className = "validation-msg msg-invalid";
            isUmurValid = false;
        }
    }

    // Cek Nomor KK
    if (kkInput) {
        badgeKk.style.display = "flex";
        if (kkInput.length === 16 && kkInput.startsWith("31")) {
            badgeKk.innerHTML = "✓ KK Resmi DKI Jakarta";
            badgeKk.className = "validation-msg msg-valid";
            isKkValid = true; 
        } else {
            badgeKk.innerHTML = "❌ Bukan KK DKI / <16 Digit";
            badgeKk.className = "validation-msg msg-invalid";
            isKkValid = false;
        }
    }

    // Cek Tanggal Terbit KK (< 15 Juni 2025)
    if (tglKkInput) {
        badgeTglKk.style.display = "flex";
        const dateKk = new Date(tglKkInput);
        const limitDate = new Date('2025-06-15');

        if (dateKk < limitDate) {
            badgeTglKk.innerHTML = "✓ Sesuai Syarat";
            badgeTglKk.className = "validation-msg msg-valid";
            isKkDateValid = true;
        } else {
            badgeTglKk.innerHTML = "❌ Wajib di bawah 15 Jun 2025";
            badgeTglKk.className = "validation-msg msg-invalid";
            isKkDateValid = false;
        }
    }

    cekSemuaValidasi(); 
}

// Auto-Load Session Data (Biar kalau refresh, data gak hilang)
document.addEventListener("DOMContentLoaded", function() {
    const formElements = document.querySelectorAll("input:not([type='file']), select, textarea");
    formElements.forEach(el => {
        if (el.name && sessionStorage.getItem(el.name)) {
            el.value = sessionStorage.getItem(el.name);
            if(el.name === "tanggal_lahir" || el.name === "no_kk" || el.name === "tanggal_kk") jalankanValidasiSistemKomplit();
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