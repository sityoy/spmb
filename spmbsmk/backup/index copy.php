<?php 
require_once __DIR__ . '/config/koneksi.php'; 

// 1. HITUNG KUOTA REAL-TIME DARI DATABASE
$query_akl  = "SELECT COUNT(*) as total FROM pendaftar WHERE pilihan_jurusan = 'Akuntansi dan Keuangan Lembaga'";
$terisi_akl = mysqli_fetch_assoc(mysqli_query($conn, $query_akl))['total'];

$query_mplb  = "SELECT COUNT(*) as total FROM pendaftar WHERE pilihan_jurusan = 'Manajemen Perkantoran dan Layanan Bisnis'";
$terisi_mplb = mysqli_fetch_assoc(mysqli_query($conn, $query_mplb))['total'];

// REGULASI KUOTA
$max_utama = 36;
$max_cadangan = 5;
$total_maksimal_sistem = $max_utama + $max_cadangan; // 41 Siswa

// PENGATURAN BATAS TANGGAL PENDAFTARAN (15 Juni 2026 - 26 Juni 2026)
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

    $tanggal_lahir_obj = new DateTime($tgl_lahir);
    $hari_ini_obj      = new DateTime(); 
    $hitung_umur       = $hari_ini_obj->diff($tanggal_lahir_obj)->y;

    $max_file_size = 3145728; 
    $daftar_file = ['file_ijazah', 'file_tka', 'file_kk', 'file_akte', 'file_ktp_bapak', 'file_ktp_ibu', 'file_sptjm'];
    $file_terlalu_besar = false;

    foreach ($daftar_file as $input_name) {
        if (isset($_FILES[$input_name]) && $_FILES[$input_name]['size'] > $max_file_size) {
            $file_terlalu_besar = true;
            break;
        }
    }

    $cek_duplikat = "SELECT * FROM pendaftar WHERE no_ijazah = '$no_ijazah' OR nisn = '$nisn' OR no_kk = '$no_kk'";
    $hasil_cek    = mysqli_query($conn, $cek_duplikat);

    if ($file_terlalu_besar) {
        $pesan = "<div class='alert alert-danger'><b>Pendaftaran Gagal!</b><br>Berkas melebihi batas maksimal 3MB.</div>";
    }
    elseif (mysqli_num_rows($hasil_cek) > 0) {
        $pesan = "<div class='alert alert-danger'><b>Pendaftaran Ditolak!</b><br>Data Anda sudah terdaftar sebelumnya.</div>";
    } 
    elseif (substr($no_kk, 0, 2) !== '31' || strlen($no_kk) !== 16) {
        $pesan = "<div class='alert alert-danger'><b>Pendaftaran Ditolak!</b><br>Program Khusus warga pemilik KK DKI Jakarta.</div>";
    }
    elseif ($hitung_umur > 21) {
        $pesan = "<div class='alert alert-danger'><b>Pendaftaran Ditolak!</b><br>Maksimal usia 21 tahun.</div>";
    }
    elseif ($skl > 100 || $tka > 100 || $skl < 0 || $tka < 0) {
        $pesan = "<div class='alert alert-danger'><b>Pendaftaran Ditolak!</b><br>Rentang input nilai wajib 0 - 100.</div>";
    } 
    else {
        $folder_tujuan = "uploads/";
        if (!is_dir($folder_tujuan)) { mkdir($folder_tujuan, 0777, true); }

        $ext_ijazah   = pathinfo($_FILES['file_ijazah']['name'], PATHINFO_EXTENSION);
        $nama_ijazah  = $nisn . "_ijazah_" . time() . "." . $ext_ijazah;
        $ext_tka      = pathinfo($_FILES['file_tka']['name'], PATHINFO_EXTENSION);
        $nama_tka     = $nisn . "_tka_" . time() . "." . $ext_tka;
        $ext_kk       = pathinfo($_FILES['file_kk']['name'], PATHINFO_EXTENSION);
        $nama_kk      = $nisn . "_kk_" . time() . "." . $ext_kk;
        $ext_akte     = pathinfo($_FILES['file_akte']['name'], PATHINFO_EXTENSION);
        $nama_akte    = $nisn . "_akte_" . time() . "." . $ext_akte;
        $ext_ktp_bapak = pathinfo($_FILES['file_ktp_bapak']['name'], PATHINFO_EXTENSION);
        $nama_ktp_bapak = $nisn . "_ktpbapak_" . time() . "." . $ext_ktp_bapak;
        $ext_ktp_ibu   = pathinfo($_FILES['file_ktp_ibu']['name'], PATHINFO_EXTENSION);
        $nama_ktp_ibu  = $nisn . "_ktpibu_" . time() . "." . $ext_ktp_ibu;
        $ext_sptjm     = pathinfo($_FILES['file_sptjm']['name'], PATHINFO_EXTENSION);
        $nama_sptjm    = $nisn . "_sptjm_" . time() . "." . $ext_sptjm;

        $nama_tabungan_kjp = "";
        $upload_kjp_status = true;
        if ($status_kjp == 'Ya') {
            $ext_tabungan_kjp  = pathinfo($_FILES['file_tabungan_kjp']['name'], PATHINFO_EXTENSION);
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

        // Formula pembatasan nilai akhir maksimal 100 proporsional
        $query_rank = "SELECT id, status_konfirmasi, ((((nilai_skl * 0.7) + (nilai_tka * 0.3)) + nilai_test) / 2) as nilai_akhir FROM pendaftar WHERE pilihan_jurusan = '$jrs_siswa' $order_logic";
        $res_rank = mysqli_query($conn, $query_rank);
        
        $rank_counter = 1;
        while ($row = mysqli_fetch_assoc($res_rank)) {
            if ($row['id'] == $id_siswa) { $peringkat_siswa = $rank_counter; break; }
            $rank_counter++;
        }
    } else {
        $pesan_pengumuman = "<div class='alert alert-danger'>NISN belum terdaftar.</div>";
    }
}

// 4. DATA UNTUK LIVE BOARD (Kalkulasi Berjenjang Bobot Maksimal 100)
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
    <link rel="stylesheet" href="css/index.css">
    <?php if($page == 'live_board') { echo '<meta http-equiv="refresh" content="60;url=index.php?page=live_board">'; } ?>
    <style>
        .search-container { margin-bottom: 15px; display: flex; justify-content: center; }
        .search-box { width: 100%; max-width: 450px; padding: 10px 14px; border: 2px solid #cbd5e1; border-radius: 6px; font-size: 13px; outline: none; text-align: center; font-weight: 500; }
        .search-box:focus { border-color: #4f46e5; }
        
        /* CSS Khusus Efek Kanan Kiri Side-by-Side Grid & Accordion */
        .live-grid-layout { display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 20px; align-items: start; width: 100%; margin-top: 10px; }
        .click-row { cursor: pointer; transition: background 0.15s; }
        .click-row:hover { background: #f8fafc !important; }
        .detail-row { display: none; background: #fafafa !important; }
        .detail-box-wrapper { padding: 10px 15px; text-align: left; font-size: 12.5px; border-left: 4px solid #4f46e5; }
        .detail-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; }
        .detail-item { background: #fff; padding: 8px; border-radius: 4px; border: 1px solid #e2e8f0; }
        .detail-item small { color: #64748b; font-weight: bold; display: block; font-size: 10px; text-transform: uppercase; margin-bottom: 2px; }
        .toggle-icon { font-size: 11px; margin-right: 4px; color: #64748b; display: inline-block; transition: transform 0.2s; }
        .open .toggle-icon { transform: rotate(90deg); color: #4f46e5; }

        /* Gaya Kontrol Pagination Mini */
        .paginasi-wrapper { display: flex; justify-content: space-between; align-items: center; background: #f8fafc; padding: 8px 12px; border-bottom: 1px solid #e2e8f0; font-size: 12px; }
        .btn-paginasi { background: #fff; border: 1px solid #cbd5e1; padding: 4px 10px; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 11.5px; color: #475569; }
        .btn-paginasi.aktif { background: #4f46e5; color: white; border-color: #4f46e5; }
    </style>
</head>
<body>

<div class="container" style="<?php echo ($page == 'live_board') ? 'max-width:1450px; width:97%;' : 'max-width:750px;'; ?>">
    
    <div class="header">
        <img src="logo/logopb.jpg" alt="Logo Yayasan" style="max-height: 80px; width: auto; margin-bottom: 10px;">
        <img src="logo/logopemda.png" alt="Logo DKI" style="max-height: 80px; width: auto; margin-bottom: 10px; margin-left: 10px;">
        <img src="logo/logosmkpb.png" alt="Logo SMK" style="max-height: 85px; width: auto; margin-bottom: 10px; margin-left: 10px;">
        <h2>PORTAL SPMB ONLINE</h2>
        <h4>SMKS PERMATA BUNDA I JAKARTA</h4>
        <span class="tag-school">Program Sekolah Gratis 2026</span>
    </div>

    <div class="main-nav">
        <a href="index.php?page=pendaftaran" class="nav-link <?php echo ($page == 'pendaftaran') ? 'active' : ''; ?>">📝 Pendaftaran</a>
        <a href="index.php?page=pengumuman" class="nav-link <?php echo ($page == 'pengumuman') ? 'active' : ''; ?>">📢 Pengumuman</a>
        <a href="index.php?page=live_board" class="nav-link <?php echo ($page == 'live_board') ? 'active' : ''; ?>"><span class="live-badge">●</span> Live Board <br> Refresh Otomatis 60 Detik </a> 
    </div>

    <?php if ($page == 'pendaftaran'): ?>
        <?php if ($waktu_sekarang < $waktu_buka): ?>
            <div class="time-alert-box">
                <div style="font-size: 50px; margin-bottom: 10px;">⏳</div>
                <h3 style="color: #1e293b; margin: 0 0 8px 0; font-size: 18px;">Pendaftaran Belum Dibuka</h3>
                <a href="formulir_offline.php" target="_blank" style="display: inline-block; background: #4f46e5; color: white; font-weight: bold; font-size: 13px; padding: 10px 20px; border-radius: 6px; text-decoration: none;">🖨️ Cetak Formulir Offline Lebih Awal</a>
            </div>
        <?php elseif ($waktu_sekarang > $waktu_tutup): ?>
            <div class="time-alert-box" style="background: #fff5f5; border-color: #fca5a5;">
                <div style="font-size: 50px; margin-bottom: 10px;">❌</div>
                <h3 style="color: #991b1b; margin: 0 0 8px 0; font-size: 18px;">Pendaftaran Sudah Ditutup</h3>
            </div>
        <?php else: ?>
            <?php if ($pesan != "") echo $pesan; ?>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="grid-form">
                    <div class="section-title">Data Calon Siswa</div>
                    <div class="form-group full-width"><label>Nama Lengkap</label><input type="text" name="nama_lengkap" required oninput="this.value = this.value.toUpperCase();" style="text-transform: uppercase;"></div>
                    <div class="form-group full-width"><label>Nomor Kartu Keluarga (KK)</label><input type="text" name="no_kk" id="kk_input" inputmode="numeric" maxlength="16" required oninput="validasiKKDKI()"><span id="kk_error" style="font-size:11px; font-weight:600; display:block; margin-top:4px;"></span></div>
                    <div class="form-group"><label>Tempat Lahir</label><input type="text" name="tempat_lahir" required style="text-transform: capitalize;"></div>
                    <div class="form-group grid-umur-wrapper"><div><label>Tanggal Lahir</label><input type="date" name="tanggal_lahir" id="tgl_lahir_input" required onchange="hitungDanValidasiUmur()"></div><div class="display-umur-box"><label>Umur</label><div id="umur_text" class="umur-badge-neutral">-</div></div></div>
                    <div class="form-group"><label>NISN</label><input type="text" name="nisn" id="nisn_input" inputmode="numeric" maxlength="10" required oninput="validasiNISN()"><span id="nisn_error" style="font-size:11px; font-weight:600; display:block; margin-top:4px;"></span></div>
                    <div class="form-group"><label>Nomor Ijazah</label><input type="text" name="no_ijazah" required></div>
                    <div class="form-group full-width"><label>Asal Sekolah (SMP/MTs)</label><input type="text" name="asal_sekolah" required></div>
                    <div class="form-group full-width"><label>No. WhatsApp</label><input type="text" name="no_whatsapp" id="wa_input" inputmode="numeric" maxlength="13" required oninput="validasiWA()"><span id="wa_error" style="font-size:11px; font-weight:600; display:block; margin-top:4px;"></span></div>
                    <div class="form-group full-width">
                        <label>Pilihan Kompetensi Keahlian</label>
                        <select name="pilihan_jurusan" required>
                            <option value="">-- Pilih Jurusan --</option>
                            <option value="Akuntansi dan Keuangan Lembaga">Akuntansi dan Keuangan Lembaga (AKL)</option>
                            <option value="Manajemen Perkantoran dan Layanan Bisnis">Manajemen Perkantoran dan Layanan Bisnis (MPLB)</option>
                        </select>
                    </div>
                    <div class="section-title">Data Nilai Seleksi</div>
                    <div class="form-group"><label>Rata-rata Ujian / Sidanira</label><input type="number" step="0.01" min="0" max="100" name="nilai_skl" id="nilai_skl" required placeholder="0.00"></div>
                    <div class="form-group"><label>Nilai Tes TKA</label><input type="number" step="0.01" min="0" max="100" name="nilai_tka" id="nilai_tka" required placeholder="0.00"></div>
                    <div class="section-title">Upload Dokumen Fisik Asli</div>
                    <div class="form-group"><label>Scan Ijazah *</label><input type="file" name="file_ijazah" required></div>
                    <div class="form-group"><label>Scan TKA *</label><input type="file" name="file_tka" required></div>
                    <div class="form-group"><label>Scan KK *</label><input type="file" name="file_kk" required></div>
                    <div class="form-group"><label>Scan Akte *</label><input type="file" name="file_akte" required></div>
                    <div class="form-group"><label>Scan KTP Bapak *</label><input type="file" name="file_ktp_bapak" required></div>
                    <div class="form-group"><label>Scan KTP Ibu *</label><input type="file" name="file_ktp_ibu" required></div>
                    <div class="form-group full-width"><label>Scan SPTJM *</label><input type="file" name="file_sptjm" required></div>
                    <div class="form-group full-width">
                        <label>Apakah Memiliki KJP?</label>
                        <select name="status_kjp" id="status_kjp" onchange="toggleKJPFields()">
                            <option value="Tidak">Tidak Punya</option>
                            <option value="Ya">Ya, Memiliki KJP Aktif</option>
                        </select>
                    </div>
                    <div id="form_kjp_opsional" class="full-width" style="display: none; grid-column: span 2;">
                        <div style="display:grid; grid-template-columns: 1fr; gap: 20px;"><div class="form-group"><label>Nomor Rekening KJP</label><input type="number" name="no_rek_kjp" id="no_rek_kjp"></div><div class="form-group"><label>Scan Tabungan KJP</label><input type="file" name="file_tabungan_kjp"></div></div>
                    </div>
                </div>
                <button type="submit" name="daftar" class="btn" id="submit_btn">Kirim Form Pendaftaran</button>
            </form>
        <?php endif; ?>

    <?php elseif ($page == 'live_board'): ?>
        <div class="search-container">
            <input type="text" id="live_search_input" class="search-box" placeholder="🔍 Cari Nama Pendaftar, No. Pendaftaran, atau Asal Sekolah..." oninput="jalankanPencarianLive()">
        </div>

        <div class="live-grid-layout">
            
            <div class="table-card" style="border-top: 6px solid #4f46e5;" id="panel_akl">
                <div class="table-title" style="background:#4f46e5; padding:10px; font-weight:bold; color:white; font-size:14px; text-align:center;">📈 AKUNTANSI & KEUANGAN LEMBAGA (AKL)</div>
                
                <div class="paginasi-wrapper">
                    <div>Menampilkan: <span id="info_tampil_akl">0</span> Data</div>
                    <div style="display:flex; gap:5px;">
                        <button class="btn-paginasi aktif" onclick="ubahHalamanLimit('akl', 20)">20</button>
                        <button class="btn-paginasi" onclick="ubahHalamanLimit('akl', 'all')">All</button>
                    </div>
                </div>

                <div class="table-responsive-wrapper">
                    <table style="width:100%;" id="tabel_data_akl">
                        <thead>
                            <tr style="background:#f8fafc; border-bottom:2px solid #e2e8f0; font-size:12px;">
                                <th style="width:35px; padding:8px;">#</th>
                                <th style="text-align:left; padding:8px;">Identitas Siswa (Klik Detail)</th>
                                <th style="text-align:left; padding:8px;">Asal Sekolah</th>
                                <th style="width:75px; padding:8px; text-align:center; background:#eeeffe; color:#4338ca;">Nilai Akhir</th>
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
                                    <td style="padding:10px; text-align:center; font-size:12px;"><b><?php echo $r++; ?></b></td>
                                    <td style="padding:10px; text-align:left; font-size:12.5px;">
                                        <span class="toggle-icon">▶</span>
                                        <strong><?php echo htmlspecialchars($row['nama_lengkap'], ENT_QUOTES, 'UTF-8'); ?></strong> <?php echo $badge; ?>
                                        <div style="font-size:10.5px; color:#64748b; margin-top:2px;">No: <?php echo htmlspecialchars($row['no_pendaftaran'], ENT_QUOTES, 'UTF-8'); ?> | NISN: <?php echo htmlspecialchars(sensorNISN($row['nisn']), ENT_QUOTES, 'UTF-8'); ?></div>
                                    </td>
                                    <td style="padding:10px; text-align:left; font-size:12px; color:#475569;"><?php echo htmlspecialchars($row['asal_sekolah'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td style="padding:10px; text-align:center; font-weight:bold; color:#4f46e5; background:#f5f3ff; font-size:13.5px;"><?php echo number_format($row['nilai_akhir'], 2); ?></td>
                                </tr>
                                <tr class="detail-row">
                                    <td colspan="4" style="padding:0; border-top:none;">
                                        <div class="detail-box-wrapper">
                                            <div class="detail-grid">
                                                <div class="detail-item"><small>Sidanira (70%)</small><strong><?php echo number_format($row['nilai_skl'], 2); ?></strong></div>
                                                <div class="detail-item"><small>Tes TKA (30%)</small><strong><?php echo number_format($row['nilai_tka'], 2); ?></strong></div>
                                                <div class="detail-item" style="background:#f0fdf4;"><small style="color:#166534;">Hasil Berkas</small><strong style="color:#15803d;"><?php echo number_format($row['nilai_berkas'], 2); ?></strong></div>
                                                <div class="detail-item"><small>Uji Panitia</small><strong><?php echo number_format($row['nilai_test'], 2); ?></strong></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php 
                                } 
                            } else { 
                                echo "<tr><td colspan='4' style='color:#94a3b8; padding:20px; text-align:center;'>Belum ada data pendaftar.</td></tr>"; 
                            } 
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="table-card" style="border-top: 6px solid #0284c7;" id="panel_mplb">
                <div class="table-title" style="background:#0284c7; padding:10px; font-weight:bold; color:white; font-size:14px; text-align:center;">📈 MANAJEMEN PERKANTORAN & LAYANAN BISNIS (MPLB)</div>
                
                <div class="paginasi-wrapper">
                    <div>Menampilkan: <span id="info_tampil_mplb">0</span> Data</div>
                    <div style="display:flex; gap:5px;">
                        <button class="btn-paginasi aktif" onclick="ubahHalamanLimit('mplb', 20)">20</button>
                        <button class="btn-paginasi" onclick="ubahHalamanLimit('mplb', 'all')">All</button>
                    </div>
                </div>

                <div class="table-responsive-wrapper">
                    <table style="width:100%;" id="tabel_data_mplb">
                        <thead>
                            <tr style="background:#f8fafc; border-bottom:2px solid #e2e8f0; font-size:12px;">
                                <th style="width:35px; padding:8px;">#</th>
                                <th style="text-align:left; padding:8px;">Identitas Siswa (Klik Detail)</th>
                                <th style="text-align:left; padding:8px;">Asal Sekolah</th>
                                <th style="width:75px; padding:8px; text-align:center; background:#f0f9ff; color:#0369a1;">Nilai Akhir</th>
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
                                    <td style="padding:10px; text-align:center; font-size:12px;"><b><?php echo $r++; ?></b></td>
                                    <td style="padding:10px; text-align:left; font-size:12.5px;">
                                        <span class="toggle-icon">▶</span>
                                        <strong><?php echo htmlspecialchars($row['nama_lengkap'], ENT_QUOTES, 'UTF-8'); ?></strong> <?php echo $badge; ?>
                                        <div style="font-size:10.5px; color:#64748b; margin-top:2px;">No: <?php echo htmlspecialchars($row['no_pendaftaran'], ENT_QUOTES, 'UTF-8'); ?> | NISN: <?php echo htmlspecialchars(sensorNISN($row['nisn']), ENT_QUOTES, 'UTF-8'); ?></div>
                                    </td>
                                    <td style="padding:10px; text-align:left; font-size:12px; color:#475569;"><?php echo htmlspecialchars($row['asal_sekolah'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td style="padding:10px; text-align:center; font-weight:bold; color:#0284c7; background:#f0f9ff; font-size:13.5px;"><?php echo number_format($row['nilai_akhir'], 2); ?></td>
                                </tr>
                                <tr class="detail-row">
                                    <td colspan="4" style="padding:0; border-top:none;">
                                        <div class="detail-box-wrapper" style="border-left-color:#0284c7;">
                                            <div class="detail-grid">
                                                <div class="detail-item"><small>Sidanira (70%)</small><strong><?php echo number_format($row['nilai_skl'], 2); ?></strong></div>
                                                <div class="detail-item"><small>Tes TKA (30%)</small><strong><?php echo number_format($row['nilai_tka'], 2); ?></strong></div>
                                                <div class="detail-item" style="background:#f0fdf4;"><small style="color:#166534;">Hasil Berkas</small><strong style="color:#15803d;"><?php echo number_format($row['nilai_berkas'], 2); ?></strong></div>
                                                <div class="detail-item"><small>Uji Panitia</small><strong><?php echo number_format($row['nilai_test'], 2); ?></strong></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php 
                                } 
                            } else { 
                                echo "<tr><td colspan='4' style='color:#94a3b8; padding:20px; text-align:center;'>Belum ada data pendaftar.</td></tr>"; 
                            } 
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    <?php endif; ?>
</div>

<div id="modalFoto" class="modal-overlay" onclick="tutupModal()"><span class="modal-close">&times;</span><img class="modal-content" id="imgModal"></div>

<script>
// KONFIGURASI ENGINE PAGINASI & SEARCH CLIENT-SIDE (ANTI OVERLOAD HOSTING)
let limitAkl = 20;
let limitMplb = 20;

document.addEventListener("DOMContentLoaded", function() {
    if(document.getElementById('tabel_data_akl') || document.getElementById('tabel_data_mplb')) {
        prosesRenderPaginasiTabel();
    }
});

function ubahHalamanLimit(jenisTabel, limitBaru) {
    // Ubah status tombol paginasi yang aktif
    const tombolPaginasi = event.target.parentElement.querySelectorAll('.btn-paginasi');
    tombolPaginasi.forEach(btn => btn.classList.remove('aktif'));
    event.target.classList.add('aktif');

    if (jenisTabel === 'akl') { limitAkl = limitBaru; } 
    else { limitMplb = limitBaru; }
    
    prosesRenderPaginasiTabel();
}

function prosesRenderPaginasiTabel() {
    const keyword = document.getElementById('live_search_input') ? document.getElementById('live_search_input').value.toUpperCase().trim() : '';
    
    // Pemrosesan Paginasi AKL
    const barisAkl = document.querySelectorAll('#tabel_data_akl tbody .click-row');
    let counterAkl = 0;
    barisAkl.forEach(tr => {
        const cocokCari = cekKecocokanKeyword(tr, keyword);
        const nextRow = tr.nextElementSibling;

        if (cocokCari && (limitAkl === 'all' || counterAkl < limitAkl)) {
            tr.style.display = "";
            counterAkl++;
        } else {
            tr.style.display = "none";
            tr.classList.remove('open');
            if(nextRow && nextRow.classList.contains('detail-row')) nextRow.style.display = "none";
        }
    });
    if(document.getElementById('info_tampil_akl')) document.getElementById('info_tampil_akl').innerText = counterAkl;

    // Pemrosesan Paginasi MPLB
    const barisMplb = document.querySelectorAll('#tabel_data_mplb tbody .click-row');
    let counterMplb = 0;
    barisMplb.forEach(tr => {
        const cocokCari = cekKecocokanKeyword(tr, keyword);
        const nextRow = tr.nextElementSibling;

        if (cocokCari && (limitMplb === 'all' || counterMplb < limitMplb)) {
            tr.style.display = "";
            counterMplb++;
        } else {
            tr.style.display = "none";
            tr.classList.remove('open');
            if(nextRow && nextRow.classList.contains('detail-row')) nextRow.style.display = "none";
        }
    });
    if(document.getElementById('info_tampil_mplb')) document.getElementById('info_tampil_mplb').innerText = counterMplb;
}

function cekKecocokanKeyword(tr, keyword) {
    if (keyword === '') return true;
    const identitasText = tr.cells[1] ? tr.cells[1].textContent.toUpperCase() : '';
    const sekolahText   = tr.cells[2] ? tr.cells[2].textContent.toUpperCase() : '';
    return (identitasText.includes(keyword) || sekolahText.includes(keyword));
}

function jalankanPencarianLive() {
    prosesRenderPaginasiTabel();
}

function toggleAccordionRow(clickedRow) {
    clickedRow.classList.toggle('open');
    const nextRow = clickedRow.nextElementSibling;
    if (nextRow && nextRow.classList.contains('detail-row')) {
        nextRow.style.display = (nextRow.style.display === 'table-row') ? 'none' : 'table-row';
    }
}

function tutupModal() { document.getElementById('modalFoto').style.display = "none"; }
function toggleKJPFields() {
    var statusKJP = document.getElementById("status_kjp").value;
    document.getElementById("form_kjp_opsional").style.display = (statusKJP === "Ya") ? "grid" : "none";
}
</script>
</body>
</html>