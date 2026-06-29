<?php 
require_once __DIR__ . '/config/koneksi.php'; 

// 1. HITUNG KUOTA REAL-TIME DARI DATABASE SECARA SPESIFIK
function hitungKuota($jurusan, $status, $conn) {
    $q = mysqli_query($conn, "SELECT COUNT(*) as total FROM pendaftar WHERE pilihan_jurusan = '$jurusan' AND status_konfirmasi = '$status'");
    return mysqli_fetch_assoc($q)['total'];
}

// Menghitung jumlah terisi per jurusan dan status
$akl_utama    = hitungKuota('Akuntansi dan Keuangan Lembaga', 'Jadi', $conn);
$akl_cadangan = hitungKuota('Akuntansi dan Keuangan Lembaga', 'Belum', $conn);

$mplb_utama    = hitungKuota('Manajemen Perkantoran dan Layanan Bisnis', 'Jadi', $conn);
$mplb_cadangan = hitungKuota('Manajemen Perkantoran dan Layanan Bisnis', 'Belum', $conn);

// REGULASI BATA KUOTA SISTEM
$max_utama = 36;
$max_cadangan = 5;

// PENGATURAN BATAS TANGGAL PENDAFTARAN
date_default_timezone_set('Asia/Jakarta'); 
$waktu_sekarang = time();
$waktu_buka     = strtotime('2026-05-15 00:00:00'); 
$waktu_tutup    = strtotime('2026-06-26 23:59:59');

$pesan = "";

// LOGIKA URUTAN BURSA SELEKSI
$order_logic = "ORDER BY CASE status_konfirmasi 
                    WHEN 'Jadi' THEN 1 
                    WHEN 'Belum' THEN 2 
                    WHEN 'Tidak Jadi' THEN 3 
                END ASC, nilai_akhir DESC, tanggal_daftar ASC";

// Fungsi bantu format tanggal Indonesia
function tgl_indo_front($tanggal) {
    if (empty($tanggal) || $tanggal == '0000-00-00 00:00:00' || $tanggal == '0000-00-00') { return "-"; }
    $pecah_waktu = explode(' ', $tanggal);
    $hanya_tanggal = $pecah_waktu[0];
    $bulan_indo = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $split = explode('-', $hanya_tanggal);
    $hasil_tgl = $split[2] . ' ' . $bulan_indo[(int)$split[1]] . ' ' . $split[0];
    if (isset($pecah_waktu[1]) && $pecah_waktu[1] != '00:00:00') {
        return $hasil_tgl . ' - ' . date('H:i', strtotime($tanggal)) . ' WIB';
    }
    return $hasil_tgl;
}

// Fungsi Sensor NISN (Anti-Hack Privasi)
function sensorNISN($nisn) {
    if (strlen($nisn) == 10) {
        return substr($nisn, 0, 4) . "XXXX" . substr($nisn, 8, 2);
    }
    return substr($nisn, 0, 3) . "XXXX" . substr($nisn, -2);
}

// 2. PROSES KETIK TOMBOL DAFTAR DIKLIK
if (isset($_POST['daftar'])) {
    if ($waktu_sekarang < $waktu_buka || $waktu_sekarang > $waktu_tutup) {
        die("Akses Ditolak: Pendaftaran saat ini sedang ditutup.");
    }

    $nama       = strtoupper(trim(mysqli_real_escape_string($conn, $_POST['nama_lengkap'])));
    $tmpl_lahir = ucwords(strtolower(trim(mysqli_real_escape_string($conn, $_POST['tempat_lahir']))));
    $tgl_lahir  = trim(mysqli_real_escape_string($conn, $_POST['tanggal_lahir']));
    $nisn       = trim(mysqli_real_escape_string($conn, $_POST['nisn']));
    $no_ijazah  = trim(mysqli_real_escape_string($conn, $_POST['no_ijazah']));
    $asal       = trim(mysqli_real_escape_string($conn, $_POST['asal_sekolah']));
    $wa         = trim(mysqli_real_escape_string($conn, $_POST['no_whatsapp']));
    $jurusan    = trim(mysqli_real_escape_string($conn, $_POST['pilihan_jurusan']));
    $skl        = trim(mysqli_real_escape_string($conn, $_POST['nilai_skl']));
    $tka        = trim(mysqli_real_escape_string($conn, $_POST['nilai_tka']));
    $no_kk      = trim(mysqli_real_escape_string($conn, $_POST['no_kk']));
    
    $status_kjp = trim(mysqli_real_escape_string($conn, $_POST['status_kjp']));
    $no_rek_kjp = ($status_kjp == 'Ya') ? trim(mysqli_real_escape_string($conn, $_POST['no_rek_kjp'])) : '';

    // Hitung umur secara presisi dari input Tanggal Lahir
    $tanggal_lahir_obj = new DateTime($tgl_lahir);
    $hari_ini_obj      = new DateTime(); 
    $hitung_umur       = $hari_ini_obj->diff($tanggal_lahir_obj)->y;

    // VALIDASI FORM & UKURAN BERKAS
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

    // LOCK DUPLIKAT: Memeriksa apakah NISN atau Nomor Ijazah sudah pernah terdaftar di bursa
    $cek_duplikat = "SELECT * FROM pendaftar WHERE no_ijazah = '$no_ijazah' OR nisn = '$nisn'";
    $hasil_cek    = mysqli_query($conn, $cek_duplikat);

    if ($file_terlalu_besar) {
        $pesan = "<div class='alert alert-danger'><b>Pendaftaran Gagal!</b><br>Berkas melebihi batas maksimal 3MB.</div>";
    }
    elseif ($ekstensi_ilegal) {
        $pesan = "<div class='alert alert-danger'><b>Format Berkas Ilegal!</b><br>Sistem hanya menerima file gambar (.jpg, .jpeg, .png) atau dokumen (.pdf). Dokumen skrip dilarang!</div>";
    }
    elseif (mysqli_num_rows($hasil_cek) > 0) {
        $pesan = "<div class='alert alert-danger'><b>Pendaftaran Ditolak!</b><br>NISN atau Nomor Seri Ijazah/SIDANIRA Anda sudah terdaftar dalam sistem. Tidak bisa mendaftar kembali.</div>";
    } 
    elseif (substr($no_kk, 0, 2) !== '31' || strlen($no_kk) !== 16) {
        $pesan = "<div class='alert alert-danger'><b>Pendaftaran Ditolak!</b><br>Program Khusus ini hanya menerima warga pemilik KK resmi Provinsi DKI Jakarta (No. KK wajib 16 digit & diawali angka 31).</div>";
    }
    // LOCK UMUR: Harus berada di rentang minimal 13 tahun dan maksimal 23 tahun
    elseif ($hitung_umur < 13 || $hitung_umur > 23) {
        $pesan = "<div class='alert alert-danger'><b>Pendaftaran Ditolak!</b><br>Kriteria usia tidak sesuai persyaratan sistem PPDB (Persyaratan: Minimal 13 tahun & Maksimal 23 tahun. Usia Anda saat ini: $hitung_umur tahun).</div>";
    }
    elseif ($skl > 100 || $tka > 100 || $skl < 0 || $tka < 0) {
        $pesan = "<div class='alert alert-danger'><b>Pendaftaran Ditolak!</b><br>Rentang penginputan nilai wajib berada di skala 0.00 - 100.00.</div>";
    } 
    else {
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

            $query = "INSERT INTO pendaftar (no_pendaftaran, nama_lengkap, tempat_lahir, tanggal_lahir, nisn, no_ijazah, asal_sekolah, no_whatsapp, pilihan_jurusan, nilai_skl, nilai_tka, nilai_test, file_ijazah, file_tka, file_kk, file_akte, no_kk, status_konfirmasi, file_ktp_bapak, file_ktp_ibu, file_sptjm, status_kjp, no_rek_kjp, file_tabungan_kjp) 
                      VALUES ('$no_pendaftaran', '$nama', '$tmpl_lahir', '$tgl_lahir', '$nisn', '$no_ijazah', '$asal', '$wa', '$jurusan', '$skl', '$tka', '0.00', '$nama_ijazah', '$nama_tka', '$nama_kk', '$nama_akte', '$no_kk', 'Belum', '$nama_ktp_bapak', '$nama_ktp_ibu', '$nama_sptjm', '$status_kjp', '$no_rek_kjp', '$nama_tabungan_kjp')";

            if (mysqli_query($conn, $query)) {
                header("Location: bukti.php?no_pendaftaran=" . urlencode(trim($no_pendaftaran)));
                exit;
            } else {
                die("Gagal Menyimpan: " . mysqli_error($conn)); 
            }
        }
    }
}

// 3. PROSES CEK PENGUMUMAN MANDIRI
$pesan_pengumuman = "";
$siswa_ditemukan = null;
$peringkat_siswa = 0;

if (isset($_POST['cek_pengumuman'])) {
    $nisn_cek = trim(mysqli_real_escape_string($conn, $_POST['nisn_cek']));
    $query_cek = "SELECT * FROM pendaftar WHERE nisn = '$nisn_cek'";
    $res_cek = mysqli_query($conn, $query_cek);

    if (mysqli_num_rows($res_cek) === 1) {
        $siswa_ditemukan = mysqli_fetch_assoc($res_cek);
        $jrs_siswa = $siswa_ditemukan['pilihan_jurusan'];
        $id_siswa = $siswa_ditemukan['id'];

        $query_rank = "SELECT id, status_konfirmasi, ((((nilai_skl * 0.7) + (nilai_tka * 0.3)) + nilai_test) / 2) as nilai_akhir FROM pendaftar WHERE pilihan_jurusan = '$jrs_siswa' $order_logic";
        $res_rank = mysqli_query($conn, $query_rank);
        
        $rank_counter = 1;
        while ($row = mysqli_fetch_assoc($res_rank)) {
            if ($row['id'] == $id_siswa) { $peringkat_siswa = $rank_counter; break; }
            $rank_counter++;
        }
    } else {
        $pesan_pengumuman = "<div class='alert alert-danger'><b>Data Tidak Ditemukan!</b><br>NISN belum terdaftar dalam sistem bursa seleksi. Pastikan NISN yang dimasukkan sudah benar (10 digit).</div>";
    }
}

// 4. DATA UNTUK LIVE BOARD
$sql_query_bursa = "SELECT no_pendaftaran, nisn, nama_lengkap, asal_sekolah, status_konfirmasi, nilai_skl, nilai_tka, nilai_test, 
                    ((nilai_skl * 0.7) + (nilai_tka * 0.3)) as nilai_berkas,
                    ((((nilai_skl * 0.7) + (nilai_tka * 0.3)) + nilai_test) / 2) as nilai_akhir 
                    FROM pendaftar WHERE pilihan_jurusan = %s $order_logic";

$result_live_akl = mysqli_query($conn, sprintf($sql_query_bursa, "'Akuntansi dan Keuangan Lembaga'"));
$result_live_mplb = mysqli_query($conn, sprintf($sql_query_bursa, "'Manajemen Perkantoran dan Layanan Bisnis'"));

$page = isset($_GET['page']) ? $_GET['page'] : 'pendaftaran';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPMB Portal - SMKS PERMATA BUNDA I</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        
        body { font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif; background: #f1f5f9; color: #1e293b; margin: 0; padding: 20px 0; }
        .container { background: #ffffff; border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.01); padding: 35px; margin: 0 auto; box-sizing: border-box; }
        
        /* Header */
        .header { text-align: center; margin-bottom: 30px; }
        .header img { max-height: 80px; margin: 0 5px; }
        .header h2 { margin: 15px 0 5px 0; font-weight: 800; color: #0f172a; font-size: 24px; letter-spacing: -0.5px; }
        .header h4 { margin: 0 0 10px 0; color: #475569; font-weight: 600; font-size: 16px; }
        .tag-school { background: #e0e7ff; color: #4f46e5; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; display: inline-block; }

        /* Navigation */
        .main-nav { display: flex; justify-content: center; gap: 15px; margin-bottom: 30px; border-bottom: 2px solid #e2e8f0; padding-bottom: 20px; flex-wrap: wrap; }
        .nav-link { padding: 10px 22px; font-weight: 600; color: #64748b; text-decoration: none; border-radius: 10px; transition: all 0.3s ease; font-size: 14px; }
        .nav-link.active { background: #4f46e5; color: white; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25); }
        .nav-link:hover:not(.active) { background: #f8fafc; color: #4f46e5; }
        .live-badge { color: #ef4444; animation: blink 1.5s infinite; }
        @keyframes blink { 0% {opacity: 1;} 50% {opacity: 0.4;} 100% {opacity: 1;} }

        /* Form Inputs */
        .grid-form { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .full-width { grid-column: span 2; }
        .form-group label { display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 8px; }
        .form-group input, .form-group select { width: 100%; padding: 14px 16px; border: 1.5px solid #cbd5e1; border-radius: 10px; font-family: inherit; font-size: 14px; transition: all 0.2s ease; box-sizing: border-box; background: #fff; color: #1e293b; outline: none; }
        .form-group input:focus, .form-group select:focus { border-color: #4f46e5; box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1); }
        .form-group input::placeholder { color: #94a3b8; font-weight: 500; }
        .section-title { grid-column: span 2; font-size: 16px; font-weight: 800; color: #0f172a; margin-top: 15px; padding-bottom: 10px; border-bottom: 2px solid #f1f5f9; }
        
        /* Umur Wrapper */
        .grid-umur-wrapper { display: grid; grid-template-columns: 3fr 1fr; gap: 15px; }
        .display-umur-box { display: flex; flex-direction: column; }
        .umur-badge-neutral { background: #f1f5f9; border: 1.5px solid #cbd5e1; border-radius: 10px; text-align: center; font-weight: bold; padding: 14px 0; color: #64748b; font-size: 14px; }
        
        /* Upload Box */
        .custom-upload-box { position: relative; display: block; border: 2px dashed #cbd5e1; border-radius: 10px; padding: 22px 15px; text-align: center; background: #f8fafc; cursor: pointer; transition: all 0.2s ease; }
        .custom-upload-box:hover { background: #f0fdf4; border-color: #22c55e; }
        .custom-upload-box input[type="file"] { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
        .upload-icon { font-size: 28px; display: block; margin-bottom: 6px; }
        .upload-text { font-size: 13px; font-weight: 700; color: #475569; display: block; }
        .upload-hint { font-size: 11px; color: #94a3b8; display: block; margin-top: 4px; font-weight: 500; }
        .custom-upload-box.file-loaded { background: #f0fdf4; border: 2px solid #22c55e; }
        .custom-upload-box.file-loaded .upload-text { color: #166534; }
        .custom-upload-box.file-loaded .upload-hint { color: #15803d; font-weight: 700; }

        /* Buttons & Alerts */
        .btn { background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); color: white; border: none; padding: 16px 20px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: all 0.3s ease; width: 100%; font-size: 16px; font-family: inherit; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2); }
        .btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(79, 70, 229, 0.3); }
        .btn:disabled { background: #cbd5e1; cursor: not-allowed; box-shadow: none; transform: none; }
        .btn-print { background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); margin-top: 15px; text-decoration: none; display: inline-block; text-align: center; width: auto; padding: 14px 25px; }
        .btn-print:hover { box-shadow: 0 6px 15px rgba(16, 185, 129, 0.3); }

        .alert { padding: 15px 20px; border-radius: 10px; margin-bottom: 25px; font-size: 14px; line-height: 1.5; }
        .alert-danger { background: #fef2f2; border-left: 4px solid #ef4444; color: #991b1b; }
        .time-alert-box { background: #fffbeb; border: 1px solid #fde68a; padding: 30px; text-align: center; border-radius: 12px; color: #b45309; }
        
        .msg-error-field { font-size: 12px; font-weight: 700; color: #ef4444; display: block; margin-top: 6px; }
        .msg-success-field { font-size: 12px; font-weight: 700; color: #10b981; display: block; margin-top: 6px; }

        /* Hasil Pengumuman Card */
        .card-result { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .card-result-header { background: #f8fafc; padding: 20px; text-align: center; border-bottom: 1px solid #e2e8f0; }
        .card-result-body { padding: 25px; }
        .card-result-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .card-result-table td { padding: 12px 0; border-bottom: 1px dashed #e2e8f0; }
        .card-result-table td:first-child { color: #64748b; font-weight: 600; width: 40%; }
        .card-result-table td:last-child { color: #0f172a; font-weight: 700; text-align: right; }
        .status-box-result { text-align: center; padding: 20px; margin-top: 20px; border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0; }

        /* Live Board Layout */
        .search-container { margin-bottom: 20px; display: flex; justify-content: center; }
        .search-box { width: 100%; max-width: 500px; padding: 14px 20px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 14px; outline: none; text-align: center; font-weight: 600; box-shadow: 0 2px 5px rgba(0,0,0,0.02); transition: 0.2s; }
        .search-box:focus { border-color: #4f46e5; box-shadow: 0 4px 12px rgba(79,70,229,0.1); }
        .live-grid-layout { display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 25px; align-items: start; width: 100%; }
        .table-card { border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); background: #fff; border: 1px solid #e2e8f0; }
        .table-title { padding: 15px; font-weight: 800; color: white; font-size: 15px; text-align: center; letter-spacing: 0.5px; }
        
        /* Tabel Live Board */
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8fafc; padding: 12px; font-size: 12px; color: #475569; border-bottom: 2px solid #e2e8f0; text-transform: uppercase; letter-spacing: 0.5px; }
        .click-row { cursor: pointer; transition: background 0.2s; border-bottom: 1px solid #f1f5f9; }
        .click-row:hover { background: #f8fafc !important; }
        .detail-row { display: none; background: #fafafa !important; }
        .detail-box-wrapper { padding: 15px 20px; text-align: left; border-left: 4px solid #4f46e5; }
        .detail-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .detail-item { background: #fff; padding: 10px 12px; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        .detail-item small { color: #64748b; font-weight: 700; display: block; font-size: 10.5px; text-transform: uppercase; margin-bottom: 4px; }
        .detail-item strong { font-size: 14px; color: #0f172a; }
        .toggle-icon { font-size: 12px; margin-right: 6px; color: #94a3b8; display: inline-block; transition: transform 0.2s; }
        .open .toggle-icon { transform: rotate(90deg); color: #4f46e5; }
        
        /* Badges Live Board */
        .badge-status-live { padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 800; margin-left: 5px; }
        .live-utama { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .live-cadangan { background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; }
        .live-drop { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        
        /* Paginasi */
        .paginasi-wrapper { display: flex; justify-content: space-between; align-items: center; background: #fff; padding: 12px 15px; border-bottom: 1px solid #e2e8f0; font-size: 12px; font-weight: 600; color: #475569; }
        .pagination-buttons-group { display: flex; gap: 6px; align-items: center; }
        .btn-paginasi { background: #f8fafc; border: 1px solid #cbd5e1; padding: 6px 10px; border-radius: 6px; cursor: pointer; font-weight: 700; font-size: 11px; color: #475569; transition: 0.2s; }
        .btn-paginasi:hover:not(:disabled) { background: #e2e8f0; color: #1e293b; }
        .btn-paginasi:disabled { color: #94a3b8; cursor: not-allowed; opacity: 0.6; }
        .btn-all-toggle { background: #4f46e5; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-weight: 700; font-size: 11px; box-shadow: 0 2px 4px rgba(79,70,229,0.2); }
        .btn-all-toggle.all-aktif { background: #ef4444; box-shadow: 0 2px 4px rgba(239,68,68,0.2); }

        @media (max-width: 768px) {
            .grid-form { grid-template-columns: 1fr; }
            .full-width, .section-title { grid-column: span 1; }
            .grid-umur-wrapper { grid-template-columns: 1fr; }
            #form_kjp_opsional > div { grid-template-columns: 1fr; }
            .live-grid-layout { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="container" style="<?php echo ($page == 'live_board') ? 'max-width:1450px; width:97%;' : 'max-width:750px;'; ?>">
    
    <div class="header">
        <img src="logo/logopb.jpg" alt="Logo">
        <img src="logo/logopemda.png" alt="Logo DKI">
        <img src="logo/logosmkpb.png" alt="Logo SMK">
        <h2>PORTAL SPMB ONLINE</h2>
        <h4>SMKS PERMATA BUNDA I JAKARTA</h4>
        <span class="tag-school">Program Sekolah Gratis <?php echo date('Y'); ?></span>
    </div>

    <div class="main-nav">
        <a href="index.php?page=pendaftaran" class="nav-link <?php echo ($page == 'pendaftaran') ? 'active' : ''; ?>">📝 Pendaftaran</a>
        <a href="index.php?page=pengumuman" class="nav-link <?php echo ($page == 'pengumuman') ? 'active' : ''; ?>">📢 Cek Pengumuman</a>
        <a href="index.php?page=live_board" class="nav-link <?php echo ($page == 'live_board') ? 'active' : ''; ?>"><span class="live-badge">●</span> Live Board Kuota</a> 
    </div>

    <?php if ($page == 'pendaftaran'): ?>
        <?php if ($waktu_sekarang < $waktu_buka): ?>
            <div class="time-alert-box"><h3>Pendaftaran Belum Dibuka</h3></div>
        <?php elseif ($waktu_sekarang > $waktu_tutup): ?>
            <div class="time-alert-box" style="background: #fff5f5; border-color: #fca5a5;"><h3>Pendaftaran Sudah Ditutup</h3></div>
        <?php else: ?>
            <?php if ($pesan != "") echo $pesan; ?>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="grid-form">
                    <div class="section-title">👤 Data Identitas Calon Siswa</div>
                    
                    <div class="form-group full-width">
                        <label>Nama Lengkap (Sesuai Ijazah / SIDANIRA)</label>
                        <input type="text" name="nama_lengkap" placeholder="CONTOH: ANDIKA SAPUTRA" required oninput="this.value = this.value.toUpperCase();">
                    </div>
                    
                    <div class="form-group full-width">
                        <label>Nomor Kartu Keluarga (KK) <span style="color:#ef4444;">*Wajib Wilayah DKI Jakarta</span></label>
                        <input type="text" name="no_kk" id="kk_input" inputmode="numeric" maxlength="16" placeholder="Ketik 16 Digit No. KK (Diawali 31)" required oninput="validasiSukuKKDKI(this)">
                        <span id="kk_error_box"></span>
                    </div>
                    
                    <div class="form-group">
                        <label>Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" placeholder="Contoh: Jakarta" required style="text-transform: capitalize;">
                    </div>
                    
                    <div class="form-group grid-umur-wrapper">
                        <div>
                            <label>Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" id="tgl_lahir_input" required onchange="hitungUmurOtomatisJS(this)">
                        </div>
                        <div class="display-umur-box">
                            <label>Hitungan Umur</label>
                            <div id="umur_text" class="umur-badge-neutral">-</div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>NISN</label>
                        <input type="text" name="nisn" id="nisn_input" inputmode="numeric" maxlength="10" placeholder="10 Digit Angka" required oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                    </div>
                    
                    <div class="form-group">
                        <label>Nomor Seri Ijazah / SIDANIRA</label>
                        <input type="text" name="no_ijazah" placeholder="Contoh: DN-01/M-SM/23/XXXX" required>
                    </div>
                    
                    <div class="form-group full-width">
                        <label>Asal Sekolah (SMP/MTs)</label>
                        <input type="text" placeholder="Contoh: SMP Negeri 20 Jakarta" name="asal_sekolah" required>
                    </div>
                    
                    <div class="form-group full-width">
                        <label>No. HP / WhatsApp Aktif</label>
                        <input type="text" name="no_whatsapp" id="wa_input" inputmode="numeric" maxlength="13" placeholder="Contoh: 081234567890" required oninput="this.value = this.value.replace(/[^0-9]/g, '');"> 
                    </div>
                    
                    <div class="form-group full-width">
                        <label>Pilihan Kompetensi Keahlian</label>
                        <select name="pilihan_jurusan" required>
                            <option value="">-- Silakan Pilih Jurusan --</option>
                            <option value="Akuntansi dan Keuangan Lembaga">
                                Akuntansi dan Keuangan Lembaga (AKL) 
                                [Terisi Utama: <?php echo $akl_utama; ?>/36 | Cadangan: <?php echo $akl_cadangan; ?>/5]
                            </option>
                            <option value="Manajemen Perkantoran dan Layanan Bisnis">
                                Manajemen Perkantoran dan Layanan Bisnis (MPLB) 
                                [Terisi Utama: <?php echo $mplb_utama; ?>/36 | Cadangan: <?php echo $mplb_cadangan; ?>/5]
                            </option>
                        </select>
                        <small style="color:#64748b; font-size:12px; font-weight:500; display:block; margin-top:5px;">* Jika kuota pendaftar utama penuh, Anda akan otomatis masuk ke daftar antrean cadangan bursa.</small>
                    </div>
                    
                    <div class="section-title">📊 Data Nilai Seleksi Masuk</div>
                    <div class="form-group">
                        <label>Nilai Gabungan SIDANIRA</label>
                        <input type="number" step="0.01" min="0" max="100" name="nilai_skl" id="nilai_skl" required placeholder="0.00 - 100.00">
                    </div>
                    <div class="form-group">
                        <label>Nilai Tes Akademik (TKA)</label>
                        <input type="number" step="0.01" min="0" max="100" name="nilai_tka" id="nilai_tka" required placeholder="0.00 - 100.00">
                    </div>
                    
                    <div class="section-title">📁 Upload Dokumen Fisik Asli (Maks 3MB)</div>
                    
                    <div class="form-group">
                        <label>Scan Ijazah Asli *</label>
                        <div class="custom-upload-box" id="box_file_ijazah">
                            <span class="upload-icon">📄</span>
                            <span class="upload-text" id="text_file_ijazah">Ketuk / Seret Berkas Kesini</span>
                            <span class="upload-hint">Format: JPG, PNG, PDF</span>
                            <input type="file" name="file_ijazah" required onchange="updateVisualUploadBox(this, 'text_file_ijazah', 'box_file_ijazah')">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Scan TKA Asli *</label>
                        <div class="custom-upload-box" id="box_file_tka">
                            <span class="upload-icon">📄</span>
                            <span class="upload-text" id="text_file_tka">Ketuk / Seret Berkas Kesini</span>
                            <span class="upload-hint">Format: JPG, PNG, PDF</span>
                            <input type="file" name="file_tka" required onchange="updateVisualUploadBox(this, 'text_file_tka', 'box_file_tka')">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Scan KK (Kartu Keluarga) *</label>
                        <div class="custom-upload-box" id="box_file_kk">
                            <span class="upload-icon">📄</span>
                            <span class="upload-text" id="text_file_kk">Ketuk / Seret Berkas Kesini</span>
                            <span class="upload-hint">Format: JPG, PNG, PDF</span>
                            <input type="file" name="file_kk" required onchange="updateVisualUploadBox(this, 'text_file_kk', 'box_file_kk')">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Scan Akte Kelahiran *</label>
                        <div class="custom-upload-box" id="box_file_akte">
                            <span class="upload-icon">📄</span>
                            <span class="upload-text" id="text_file_akte">Ketuk / Seret Berkas Kesini</span>
                            <span class="upload-hint">Format: JPG, PNG, PDF</span>
                            <input type="file" name="file_akte" required onchange="updateVisualUploadBox(this, 'text_file_akte', 'box_file_akte')">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Scan KTP Bapak *</label>
                        <div class="custom-upload-box" id="box_file_ktp_bapak">
                            <span class="upload-icon">🪪</span>
                            <span class="upload-text" id="text_file_ktp_bapak">Ketuk / Seret Berkas Kesini</span>
                            <span class="upload-hint">Format: JPG, PNG, PDF</span>
                            <input type="file" name="file_ktp_bapak" required onchange="updateVisualUploadBox(this, 'text_file_ktp_bapak', 'box_file_ktp_bapak')">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Scan KTP Ibu *</label>
                        <div class="custom-upload-box" id="box_file_ktp_ibu">
                            <span class="upload-icon">🪪</span>
                            <span class="upload-text" id="text_file_ktp_ibu">Ketuk / Seret Berkas Kesini</span>
                            <span class="upload-hint">Format: JPG, PNG, PDF</span>
                            <input type="file" name="file_ktp_ibu" required onchange="updateVisualUploadBox(this, 'text_file_ktp_ibu', 'box_file_ktp_ibu')">
                        </div>
                    </div>
                    <div class="form-group full-width">
                        <label>Scan Lembar SPTJM *</label>
                        <div class="custom-upload-box" id="box_file_sptjm">
                            <span class="upload-icon">🖋️</span>
                            <span class="upload-text" id="text_file_sptjm">Upload Surat Pernyataan Tanggung Jawab Mutlak (SPTJM) Bermaterai</span>
                            <span class="upload-hint">Format: JPG, PNG, PDF</span>
                            <input type="file" name="file_sptjm" required onchange="updateVisualUploadBox(this, 'text_file_sptjm', 'box_file_sptjm')">
                        </div>
                    </div>
                    
                    <div class="section-title">💳 Kepemilikan KJP (Opsional)</div>
                    <div class="form-group full-width">
                        <label>Apakah Calon Siswa Memiliki KJP Aktif?</label>
                        <select name="status_kjp" id="status_kjp" onchange="toggleKJPFields()">
                            <option value="Tidak">Tidak Punya</option>
                            <option value="Ya">Ya, Memiliki KJP Aktif</option>
                        </select>
                    </div>
                    <div id="form_kjp_opsional" class="full-width" style="display: none; grid-column: span 2;">
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label>Nomor Rekening KJP</label>
                                <input type="number" name="no_rek_kjp" id="no_rek_kjp" placeholder="Ketik No. Rekening">
                            </div>
                            <div class="form-group">
                                <label>Scan Buku Tabungan KJP</label>
                                <div class="custom-upload-box" id="box_file_tabungan_kjp">
                                    <span class="upload-icon">💳</span>
                                    <span class="upload-text" id="text_file_tabungan_kjp">Ketuk / Seret Berkas</span>
                                    <span class="upload-hint">Format: JPG, PNG, PDF</span>
                                    <input type="file" name="file_tabungan_kjp" id="file_tabungan_kjp" onchange="updateVisualUploadBox(this, 'text_file_tabungan_kjp', 'box_file_tabungan_kjp')">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" name="daftar" class="btn" id="submit_btn">🚀 Kirim Form Pendaftaran</button>
            </form>
        <?php endif; ?>

    <?php elseif ($page == 'pengumuman'): ?>
        <?php if ($pesan_pengumuman != "") echo $pesan_pengumuman; ?>
        
        <!-- Form Cek Kelulusan Modern -->
        <div class="card-result" style="margin-bottom: 25px; padding: 30px;">
            <form action="" method="POST">
                <div class="form-group" style="margin: 0;">
                    <label style="text-align:center; font-size:16px; margin-bottom:15px; color:#0f172a;">Masukkan 10 Digit NISN Calon Siswa</label>
                    <input type="text" name="nisn_cek" inputmode="numeric" maxlength="10" placeholder="Contoh: 0081234567" required style="text-align:center; font-size:20px; font-weight:700; padding:18px; border-radius:12px;">
                </div>
                <button type="submit" name="cek_pengumuman" class="btn" style="margin-top: 15px;">🔍 Periksa Hasil Seleksi</button>
            </form>
        </div>

        <?php if ($siswa_ditemukan): 
            $singkatan_jrs = ($siswa_ditemukan['pilihan_jurusan'] == "Akuntansi dan Keuangan Lembaga") ? "AKL" : "MPLB";
            $nilai_akhir_siswa = ((((float)$siswa_ditemukan['nilai_skl'] * 0.7) + ((float)$siswa_ditemukan['nilai_tka'] * 0.3)) + (float)$siswa_ditemukan['nilai_test']) / 2;
        ?>
            <!-- Tampilan Kartu Hasil Seleksi & Cetak Bukti -->
            <div class="card-result" style="border-top: 6px solid #4f46e5;">
                <div class="card-result-header">
                    <h3 style="margin:0; font-size:18px; color:#0f172a;">📄 Hasil Verifikasi Sistem Bursa Pendaftaran</h3>
                </div>
                <div class="card-result-body">
                    <table class="card-result-table">
                        <tr><td>No. Pendaftaran</td><td><code><?php echo htmlspecialchars($siswa_ditemukan['no_pendaftaran'], ENT_QUOTES, 'UTF-8'); ?></code></td></tr>
                        <tr><td>Nama Lengkap</td><td><?php echo htmlspecialchars($siswa_ditemukan['nama_lengkap'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
                        <tr><td>NISN Siswa</td><td><?php echo htmlspecialchars($siswa_ditemukan['nisn'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
                        <tr><td>Asal Sekolah</td><td><?php echo htmlspecialchars($siswa_ditemukan['asal_sekolah'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
                        <tr><td>Kompetensi Keahlian</td><td><span class="tag-school"><?php echo $singkatan_jrs; ?></span></td></tr>
                        <tr><td>Nilai Total Sistem</td><td style="font-size:16px; color:#4f46e5;"><?php echo number_format($nilai_akhir_siswa, 2); ?></td></tr>
                    </table>

                    <div class="status-box-result">
                        <div style="font-size:14px; color:#64748b; font-weight:700; margin-bottom:5px; text-transform:uppercase;">Posisi Bursa Saat Ini</div>
                        <div style="font-size:32px; font-weight:800; color:#0f172a; margin-bottom: 10px;">Peringkat Ke-<?php echo $peringkat_siswa; ?></div>
                        
                        <?php if ($siswa_ditemukan['status_konfirmasi'] == 'Jadi'): ?>
                            <div style="color:#059669; font-weight:800; font-size:18px; background:#dcfce7; padding:10px; border-radius:8px; border:1px solid #bbf7d0;">🎉 SELAMAT! FIX LOLOS JALUR UTAMA</div>
                        <?php elseif ($siswa_ditemukan['status_konfirmasi'] == 'Tidak Jadi'): ?>
                            <div style="color:#dc2626; font-weight:800; font-size:18px; background:#fee2e2; padding:10px; border-radius:8px; border:1px solid #fecaca;">❌ PENDAFTARAN DIBATALKAN PANITIA</div>
                        <?php else: ?>
                            <?php if ($peringkat_siswa <= 36): ?>
                                <div style="color:#059669; font-weight:800; font-size:16px; background:#dcfce7; padding:10px; border-radius:8px; border:1px solid #bbf7d0;">✨ AMAN! MASUK KUOTA JALUR UTAMA</div>
                            <?php else: ?>
                                <div style="color:#d97706; font-weight:800; font-size:16px; background:#fef3c7; padding:10px; border-radius:8px; border:1px solid #fde68a;">⏳ MASUK ANTREAN CADANGAN (URUTAN KE-<?php echo ($peringkat_siswa - 36); ?>)</div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- TOMBOL CETAK BUKTI -->
                    <div style="text-align:center; margin-top: 10px;">
                        <a href="bukti.php?no_pendaftaran=<?php echo urlencode($siswa_ditemukan['no_pendaftaran']); ?>" target="_blank" class="btn btn-print">
                            🖨️ Cetak / Download Bukti Registrasi
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    <?php elseif ($page == 'live_board'): ?>
        <div class="search-container">
            <input type="text" id="live_search_input" class="search-box" placeholder="🔍 Saring Cepat Nama / Asal Sekolah Pendaftar..." oninput="jalankanPencarianLive()">
        </div>
        <div class="live-grid-layout">
            <!-- PANEL AKL -->
            <div class="table-card" id="panel_akl" style="border-top: 6px solid #4f46e5;">
                <div class="table-title" style="background:#4f46e5;">📈 LIVE BURSA - AKL</div>
                <div class="paginasi-wrapper">
                    <span id="info_tampil_akl">Hal 1</span>
                    <div class="pagination-buttons-group">
                        <button class="btn-paginasi" id="btn_first_akl" onclick="lompatHalamanKe('akl', 'first')">|&lt;</button>
                        <button class="btn-paginasi" id="btn_prev_akl" onclick="navigasiHalamanKe('akl', -1)">&lt;</button>
                        <button class="btn-paginasi" id="btn_next_akl" onclick="navigasiHalamanKe('akl', 1)">&gt;</button>
                        <button class="btn-paginasi" id="btn_last_akl" onclick="lompatHalamanKe('akl', 'last')">&gt;|</button>
                        <button class="btn-all-toggle" id="btn_all_akl" onclick="toggleModeTampilkanSemua('akl')">ALL</button>
                    </div>
                </div>
                <div style="overflow-x:auto;">
                    <table id="tabel_data_akl">
                        <thead>
                            <tr>
                                <th style="width:35px; text-align:center;">#</th>
                                <th>Identitas Pendaftar</th>
                                <th>Sekolah Asal</th>
                                <th style="text-align:center; width:60px;">Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $r = 1; 
                            if (mysqli_num_rows($result_live_akl) > 0) { 
                                while ($row = mysqli_fetch_assoc($result_live_akl)) { 
                                    $cl_row = ($row['status_konfirmasi'] == 'Jadi') ? 'rank-utama' : (($row['status_konfirmasi'] == 'Tidak Jadi') ? 'rank-cadangan text-muted-drop' : (($r <= 36) ? 'rank-utama' : 'rank-cadangan'));
                                    $badge = ($row['status_konfirmasi'] == 'Jadi') ? "<span class='badge-status-live live-utama'>✓ FIX</span>" : (($row['status_konfirmasi'] == 'Tidak Jadi') ? "<span class='badge-status-live live-drop'>BATAL</span>" : (($r <= 36) ? "<span class='badge-status-live live-utama'>UTAMA</span>" : "<span class='badge-status-live live-cadangan'>CADNG</span>"));
                            ?>
                                <tr class="click-row <?php echo $cl_row; ?>" onclick="toggleAccordionRow(this)">
                                    <td style="text-align:center; font-weight:800; font-size:14px;"><?php echo $r++; ?></td>
                                    <td>
                                        <div style="font-weight:700; color:#0f172a;"><span class="toggle-icon">▶</span><?php echo htmlspecialchars($row['nama_lengkap'], ENT_QUOTES, 'UTF-8'); ?> <?php echo $badge; ?></div>
                                        <div style="font-size:11px; color:#64748b; margin-top:3px; padding-left:14px;">NISN: <?php echo htmlspecialchars(sensorNISN($row['nisn']), ENT_QUOTES, 'UTF-8'); ?></div>
                                    </td>
                                    <td style="font-weight:600; color:#475569; font-size:12px;"><?php echo htmlspecialchars($row['asal_sekolah'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td style="text-align:center; font-weight:800; color:#4f46e5; background:#f8fafc; font-size:14px;"><?php echo number_format($row['nilai_akhir'], 2); ?></td>
                                </tr>
                                <tr class="detail-row">
                                    <td colspan="4" style="padding:0;">
                                        <div class="detail-box-wrapper">
                                            <div class="detail-grid">
                                                <div class="detail-item"><small>Sidanira</small><strong><?php echo number_format($row['nilai_skl'], 2); ?></strong></div>
                                                <div class="detail-item"><small>Tes TKA</small><strong><?php echo number_format($row['nilai_tka'], 2); ?></strong></div>
                                                <div class="detail-item" style="background:#f0fdf4; border-color:#bbf7d0;"><small style="color:#166534;">Nilai Berkas</small><strong style="color:#15803d;"><?php echo number_format($row['nilai_berkas'], 2); ?></strong></div>
                                                <div class="detail-item"><small>Uji Wawancara</small><strong><?php echo number_format($row['nilai_test'], 2); ?></strong></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php } } else { echo "<tr><td colspan='4' style='text-align:center; padding:20px; color:#94a3b8;'>Belum ada data pendaftar.</td></tr>"; } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PANEL MPLB -->
            <div class="table-card" id="panel_mplb" style="border-top: 6px solid #0284c7;">
                <div class="table-title" style="background:#0284c7;">📈 LIVE BURSA - MPLB</div>
                <div class="paginasi-wrapper">
                    <span id="info_tampil_mplb">Hal 1</span>
                    <div class="pagination-buttons-group">
                        <button class="btn-paginasi" id="btn_first_mplb" onclick="lompatHalamanKe('mplb', 'first')">|&lt;</button>
                        <button class="btn-paginasi" id="btn_prev_mplb" onclick="navigasiHalamanKe('mplb', -1)">&lt;</button>
                        <button class="btn-paginasi" id="btn_next_mplb" onclick="navigasiHalamanKe('mplb', 1)">&gt;</button>
                        <button class="btn-paginasi" id="btn_last_mplb" onclick="lompatHalamanKe('mplb', 'last')">&gt;|</button>
                        <button class="btn-all-toggle" id="btn_all_mplb" onclick="toggleModeTampilkanSemua('mplb')">ALL</button>
                    </div>
                </div>
                <div style="overflow-x:auto;">
                    <table id="tabel_data_mplb">
                        <thead>
                            <tr>
                                <th style="width:35px; text-align:center;">#</th>
                                <th>Identitas Pendaftar</th>
                                <th>Sekolah Asal</th>
                                <th style="text-align:center; width:60px;">Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $r = 1; 
                            if (mysqli_num_rows($result_live_mplb) > 0) { 
                                while ($row = mysqli_fetch_assoc($result_live_mplb)) { 
                                    $cl_row = ($row['status_konfirmasi'] == 'Jadi') ? 'rank-utama' : (($row['status_konfirmasi'] == 'Tidak Jadi') ? 'rank-cadangan text-muted-drop' : (($r <= 36) ? 'rank-utama' : 'rank-cadangan'));
                                    $badge = ($row['status_konfirmasi'] == 'Jadi') ? "<span class='badge-status-live live-utama'>✓ FIX</span>" : (($row['status_konfirmasi'] == 'Tidak Jadi') ? "<span class='badge-status-live live-drop'>BATAL</span>" : (($r <= 36) ? "<span class='badge-status-live live-utama'>UTAMA</span>" : "<span class='badge-status-live live-cadangan'>CADNG</span>"));
                            ?>
                                <tr class="click-row <?php echo $cl_row; ?>" onclick="toggleAccordionRow(this)">
                                    <td style="text-align:center; font-weight:800; font-size:14px;"><?php echo $r++; ?></td>
                                    <td>
                                        <div style="font-weight:700; color:#0f172a;"><span class="toggle-icon">▶</span><?php echo htmlspecialchars($row['nama_lengkap'], ENT_QUOTES, 'UTF-8'); ?> <?php echo $badge; ?></div>
                                        <div style="font-size:11px; color:#64748b; margin-top:3px; padding-left:14px;">NISN: <?php echo htmlspecialchars(sensorNISN($row['nisn']), ENT_QUOTES, 'UTF-8'); ?></div>
                                    </td>
                                    <td style="font-weight:600; color:#475569; font-size:12px;"><?php echo htmlspecialchars($row['asal_sekolah'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td style="text-align:center; font-weight:800; color:#0284c7; background:#f8fafc; font-size:14px;"><?php echo number_format($row['nilai_akhir'], 2); ?></td>
                                </tr>
                                <tr class="detail-row">
                                    <td colspan="4" style="padding:0;">
                                        <div class="detail-box-wrapper" style="border-left-color:#0284c7;">
                                            <div class="detail-grid">
                                                <div class="detail-item"><small>Sidanira</small><strong><?php echo number_format($row['nilai_skl'], 2); ?></strong></div>
                                                <div class="detail-item"><small>Tes TKA</small><strong><?php echo number_format($row['nilai_tka'], 2); ?></strong></div>
                                                <div class="detail-item" style="background:#f0fdf4; border-color:#bbf7d0;"><small style="color:#166534;">Nilai Berkas</small><strong style="color:#15803d;"><?php echo number_format($row['nilai_berkas'], 2); ?></strong></div>
                                                <div class="detail-item"><small>Uji Wawancara</small><strong><?php echo number_format($row['nilai_test'], 2); ?></strong></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php } } else { echo "<tr><td colspan='4' style='text-align:center; padding:20px; color:#94a3b8;'>Belum ada data pendaftar.</td></tr>"; } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Validasi Suku Angka KK DKI Jakarta (Logic Tidak Diubah)
function validasiSukuKKDKI(inputElement) {
    inputElement.value = inputElement.value.replace(/[^0-9]/g, '');
    const valKK = inputElement.value;
    const errBox = document.getElementById('kk_error_box');
    const submitBtn = document.getElementById('submit_btn');
    
    if (valKK === '') {
        errBox.innerHTML = '';
        submitBtn.disabled = false;
        return;
    }
    
    if (valKK.length > 0 && valKK.substring(0, 2) !== '31') {
        errBox.innerHTML = "<span class='msg-error-field'>❌ Pendaftaran Ditolak: Khusus pemilik KK Provinsi DKI Jakarta (No. KK harus diawali kode 31)!</span>";
        submitBtn.disabled = true;
    } else if (valKK.length > 0 && valKK.length !== 16) {
        errBox.innerHTML = "<span class='msg-error-field'>❌ No. KK wajib diisi lengkap berukuran 16 Digit (Saat ini: " + valKK.length + " digit).</span>";
        submitBtn.disabled = true;
    } else {
        errBox.innerHTML = "<span class='msg-success-field'>✓ Validasi KK Berhasil: Suku wilayah asal DKI Jakarta sah.</span>";
        submitBtn.disabled = false;
    }
}

// Hitung Umur Real-Time di Sisi Browser (Logic Tidak Diubah)
function hitungUmurOtomatisJS(inputElement) {
    const tanggalLahirVal = inputElement.value;
    const umurTextElement = document.getElementById('umur_text');
    const submitBtn = document.getElementById('submit_btn');
    
    if (!tanggalLahirVal) {
        umurTextElement.innerText = "-";
        umurTextElement.className = "umur-badge-neutral";
        submitBtn.disabled = false;
        return;
    }

    const tglLahir = new Date(tanggalLahirVal);
    const hariIni = new Date();
    
    let umur = hariIni.getFullYear() - tglLahir.getFullYear();
    const m = hariIni.getMonth() - tglLahir.getMonth();
    if (m < 0 || (m === 0 && hariIni.getDate() < tglLahir.getDate())) {
        umur--;
    }

    umurTextElement.innerText = umur + " Tahun";

    if (umur < 13 || umur > 23) {
        umurTextElement.style.background = "#ef4444";
        umurTextElement.style.color = "#ffffff";
        alert("Peringatan Batas Usia: Umur pendaftar harus minimal 13 tahun dan maksimal 23 tahun!");
        submitBtn.disabled = true;
    } else {
        umurTextElement.style.background = "#dcfce7";
        umurTextElement.style.color = "#166534";
        umurTextElement.style.borderColor = "#bbf7d0";
        submitBtn.disabled = false;
    }
}

// Visual Interaksi Upload Box 
function updateVisualUploadBox(inputElement, idTextTarget, idBoxTarget) {
    const textTarget = document.getElementById(idTextTarget);
    const boxTarget = document.getElementById(idBoxTarget);
    
    if (inputElement.files && inputElement.files.length > 0) {
        const namaFile = inputElement.files[0].name;
        textTarget.innerText = "✓ Berkas Terpilih";
        boxTarget.querySelector('.upload-hint').innerText = namaFile;
        boxTarget.classList.add('file-loaded');
    } else {
        textTarget.innerText = "Ketuk / Seret Berkas Kesini";
        boxTarget.querySelector('.upload-hint').innerText = "Format: JPG, PNG, PDF";
        boxTarget.classList.remove('file-loaded');
    }
}

// Logika Paginasi & Pencarian Tabel Bursa Live (Tetap)
const dataPerHalaman = 20;
let halamanSaatIni = { akl: 1, mplb: 1 };
let modeTampilkanSemua = { akl: false, mplb: false };

document.addEventListener("DOMContentLoaded", function() {
    if(document.getElementById('tabel_data_akl') || document.getElementById('tabel_data_mplb')) {
        eksekusiRenderPaginasiSistem('akl');
        eksekusiRenderPaginasiSistem('mplb');
    }
});

function navigasiHalamanKe(jenisTabel, arahPergeseran) {
    halamanSaatIni[jenisTabel] += arahPergeseran;
    eksekusiRenderPaginasiSistem(jenisTabel);
}

function lompatHalamanKe(jenisTabel, lokasiTujuan) {
    const barisData = document.querySelectorAll(`#tabel_data_${jenisTabel} tbody .click-row`);
    const totalHalaman = Math.ceil(barisData.length / dataPerHalaman);
    if (lokasiTujuan === 'first') { halamanSaatIni[jenisTabel] = 1; } 
    else if (lokasiTujuan === 'last') { halamanSaatIni[jenisTabel] = totalHalaman; }
    eksekusiRenderPaginasiSistem(jenisTabel);
}

function toggleModeTampilkanSemua(jenisTabel) {
    const btnAll = document.getElementById(`btn_all_${jenisTabel}`);
    modeTampilkanSemua[jenisTabel] = !modeTampilkanSemua[jenisTabel];
    if (modeTampilkanSemua[jenisTabel]) {
        btnAll.classList.add('all-aktif'); btnAll.innerText = "HAL";
    } else {
        btnAll.classList.remove('all-aktif'); btnAll.innerText = "ALL";
        halamanSaatIni[jenisTabel] = 1;
    }
    eksekusiRenderPaginasiSistem(jenisTabel);
}

function eksekusiRenderPaginasiSistem(jenisTabel) {
    const keyword = document.getElementById('live_search_input') ? document.getElementById('live_search_input').value.toUpperCase().trim() : '';
    const barisData = document.querySelectorAll(`#tabel_data_${jenisTabel} tbody .click-row`);
    if(barisData.length === 0) return;

    let dataTerfilter = [];
    barisData.forEach(tr => {
        if (cekKecocokanPencarian(tr, keyword)) { dataTerfilter.push(tr); } 
        else {
            tr.style.display = "none"; tr.classList.remove('open');
            if(tr.nextElementSibling && tr.nextElementSibling.classList.contains('detail-row')) tr.nextElementSibling.style.display = "none";
        }
    });

    const totalDataTerfilter = dataTerfilter.length;
    const totalHalaman = Math.ceil(totalDataTerfilter / dataPerHalaman);
    if (halamanSaatIni[jenisTabel] > totalHalaman) halamanSaatIni[jenisTabel] = totalHalaman;
    if (halamanSaatIni[jenisTabel] < 1) halamanSaatIni[jenisTabel] = 1;

    const indeksMulai = (halamanSaatIni[jenisTabel] - 1) * dataPerHalaman;
    const indeksSelesai = indeksMulai + dataPerHalaman;

    dataTerfilter.forEach((tr, idx) => {
        const nextRow = tr.nextElementSibling;
        if (modeTampilkanSemua[jenisTabel] || (idx >= indeksMulai && idx < indeksSelesai)) {
            tr.style.display = "";
            if (tr.classList.contains('open') && nextRow && nextRow.classList.contains('detail-row')) nextRow.style.display = "table-row";
        } else {
            tr.style.display = "none"; tr.classList.remove('open');
            if (nextRow && nextRow.classList.contains('detail-row')) nextRow.style.display = "none";
        }
    });

    const isAllMode = modeTampilkanSemua[jenisTabel];
    document.getElementById(`btn_first_${jenisTabel}`).disabled = (isAllMode || halamanSaatIni[jenisTabel] === 1 || totalHalaman <= 1);
    document.getElementById(`btn_prev_${jenisTabel}`).disabled  = (isAllMode || halamanSaatIni[jenisTabel] === 1 || totalHalaman <= 1);
    document.getElementById(`btn_next_${jenisTabel}`).disabled  = (isAllMode || halamanSaatIni[jenisTabel] === totalHalaman || totalHalaman <= 1);
    document.getElementById(`btn_last_${jenisTabel}`).disabled  = (isAllMode || halamanSaatIni[jenisTabel] === totalHalaman || totalHalaman <= 1);

    document.getElementById(`info_tampil_${jenisTabel}`).innerText = isAllMode ? `Total: ${totalDataTerfilter}` : `Hal ${halamanSaatIni[jenisTabel]} / ${totalHalaman || 1}`;
}

function cekKecocokanPencarian(tr, keyword) {
    if (keyword === '') return true;
    return (tr.cells[1].textContent.toUpperCase().includes(keyword) || tr.cells[2].textContent.toUpperCase().includes(keyword));
}

function jalankanPencarianLive() {
    eksekusiRenderPaginasiSistem('akl'); eksekusiRenderPaginasiSistem('mplb');
}

function toggleAccordionRow(clickedRow) {
    clickedRow.classList.toggle('open');
    const nextRow = clickedRow.nextElementSibling;
    if (nextRow && nextRow.classList.contains('detail-row')) {
        nextRow.style.display = (nextRow.style.display === 'table-row') ? 'none' : 'table-row';
    }
}

function toggleKJPFields() {
    var statusKJP = document.getElementById("status_kjp").value;
    document.getElementById("form_kjp_opsional").style.display = (statusKJP === "Ya") ? "grid" : "none";
}
</script>
</body>
</html>