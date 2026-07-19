<?php
// ==========================================
// SECURITY LAYER: SECURE SESSION START
// ==========================================
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_use_only_cookies', 1);
    session_start();
}
date_default_timezone_set('Asia/Jakarta');
// --- TAMBAHAN ANTI-HACK ---
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$_GET['tab'] = isset($_GET['tab']) ? preg_replace('/[^a-zA-Z0-9]/', '', $_GET['tab']) : 'akl';
$_GET['gel'] = isset($_GET['gel']) ? preg_replace('/[^a-zA-Z0-9]/', '', $_GET['gel']) : 'Semua';
// --- END ANTI-HACK ---

include 'koneksi.php';

// ==========================================
// 1. PROSES AKSI STATUS JAKEDU (INDIVIDU)
// ==========================================
if (isset($_GET['jakedu']) && isset($_GET['id_siswa'])) {
    $id_jakedu = (int)$_GET['id_siswa'];
    $status_baru = ($_GET['jakedu'] == 'sudah') ? 'Sudah' : 'Belum';
    mysqli_query($conn, "UPDATE pendaftar SET status_jakedu = '$status_baru' WHERE id = $id_jakedu");
    
    $redirect_tab = $_GET['tab'];
    $redirect_gel = $_GET['gel'];
    echo "<script>window.location.href='admin.php?tab=$redirect_tab&gel=$redirect_gel';</script>";
    exit;
}

// ==========================================
// 2. PROSES JAKEDU MASSAL (KOLEKTIF)
// ==========================================
if (isset($_GET['jakedu_massal']) && isset($_GET['jurusan'])) {
    $status_j = $_GET['jakedu_massal'] == 'sudah' ? 'Sudah' : 'Belum';
    $jur_j = mysqli_real_escape_string($conn, $_GET['jurusan']);
    $gel_j = mysqli_real_escape_string($conn, $_GET['gel']);
    $filter_gel = ($gel_j == 'Semua') ? "" : " AND gelombang = '$gel_j'";

    mysqli_query($conn, "UPDATE pendaftar SET status_jakedu = '$status_j' WHERE pilihan_jurusan = '$jur_j' $filter_gel");
    
    $tab_r = isset($_GET['tab']) ? $_GET['tab'] : 'akl';
    echo "<script>alert('Status Jakedu kolektif berhasil diset!'); window.location.href='admin.php?tab=$tab_r&gel=$gel_j';</script>";
    exit;
}

// ==========================================
// 3. PROSES DUPLIKAT KE GELOMBANG LAIN
// ==========================================
if (isset($_GET['duplikat_id']) && isset($_GET['ke_gel'])) {
    $id_dup = (int)$_GET['duplikat_id'];
    $gel_tujuan = (int)$_GET['ke_gel'];
    $tab_r = isset($_GET['tab']) ? $_GET['tab'] : 'akl';
    $gel_r = isset($_GET['gel']) ? $_GET['gel'] : 'Semua';

    // Cek apakah NISN ini sudah ada di gelombang tujuan
    $q_cek = mysqli_query($conn, "SELECT nisn FROM pendaftar WHERE id = $id_dup");
    if ($r_cek = mysqli_fetch_assoc($q_cek)) {
        $nisn = $r_cek['nisn'];
        $cek_eks = mysqli_query($conn, "SELECT id FROM pendaftar WHERE nisn = '$nisn' AND gelombang = '$gel_tujuan'");
        if (mysqli_num_rows($cek_eks) > 0) {
            echo "<script>alert('Gagal! Siswa ini sudah pernah terdaftar atau diduplikat di Gelombang $gel_tujuan.'); window.history.back();</script>";
            exit;
        }
    }

    // Generate No Daftar Baru (Agar tidak bentrok Primary Key)
    $no_daftar_baru = "SPMB-SMKPB1-" . date('Y') . "-" . rand(1000, 9999) . "-G" . $gel_tujuan;

    // Salin Data ke Tabel Utama (Berkas tetap menggunakan path yang lama)
    $sql_dup = "INSERT INTO pendaftar 
        (no_pendaftaran, nama_lengkap, nik, tempat_lahir, tanggal_lahir, nisn, no_ijazah, asal_sekolah, riwayat_penyakit, alamat, kelurahan, kecamatan, no_whatsapp, pilihan_jurusan, nilai_skl, nilai_tka, nilai_test, file_ijazah, file_tka, file_kk, file_akte, no_kk, status_konfirmasi, catatan_panitia, alasan_pembatalan, file_ktp_bapak, file_ktp_ibu, file_sptjm, status_kjp, no_rek_kjp, file_tabungan_kjp, gelombang, tanggal_daftar, is_detail_filled, status_jakedu)
        SELECT 
        '$no_daftar_baru', nama_lengkap, nik, tempat_lahir, tanggal_lahir, nisn, no_ijazah, asal_sekolah, riwayat_penyakit, alamat, kelurahan, kecamatan, no_whatsapp, pilihan_jurusan, nilai_skl, nilai_tka, 0.00, file_ijazah, file_tka, file_kk, file_akte, no_kk, 'Menunggu', NULL, NULL, file_ktp_bapak, file_ktp_ibu, file_sptjm, status_kjp, no_rek_kjp, file_tabungan_kjp, '$gel_tujuan', NOW(), is_detail_filled, 'Belum'
        FROM pendaftar WHERE id = $id_dup";

    if (mysqli_query($conn, $sql_dup)) {
        $new_id = mysqli_insert_id($conn);
        
        // Salin Data ke Tabel Detail
        $sql_dup_det = "INSERT INTO pendaftar_detail 
            (pendaftar_id, jenis_kelamin, tanggal_kk, nama_ibu, agama, npsn_sekolah, kebutuhan_khusus)
            SELECT 
            $new_id, jenis_kelamin, tanggal_kk, nama_ibu, agama, npsn_sekolah, kebutuhan_khusus
            FROM pendaftar_detail WHERE pendaftar_id = $id_dup";
            
        mysqli_query($conn, $sql_dup_det);
        
        echo "<script>alert('Siswa berhasil diduplikat ke Gelombang $gel_tujuan!'); window.location.href='admin.php?tab=$tab_r&gel=$gel_r';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan saat duplikasi: " . mysqli_error($conn) . "'); window.history.back();</script>";
    }
    exit;
}

// Proses Update Jadwal & Pengaturan Kontrol Sistem masal
if (isset($_POST['simpan_jadwal'])) {
    $gel1 = mysqli_real_escape_string($conn, $_POST['buka_gel_1']);
    $gel2 = mysqli_real_escape_string($conn, $_POST['buka_gel_2']);
    $gel_aktif_form = mysqli_real_escape_string($conn, $_POST['gelombang_aktif']);
    $status_form = mysqli_real_escape_string($conn, $_POST['status_pendaftaran']);
    $q1 = mysqli_real_escape_string($conn, $_POST['max_kuota_g1']);
    $q2 = mysqli_real_escape_string($conn, $_POST['max_kuota_g2']);

    mysqli_query($conn, "UPDATE pengaturan SET 
        buka_gel_1 = '$gel1', 
        buka_gel_2 = '$gel2', 
        gelombang_aktif = '$gel_aktif_form', 
        status_pendaftaran = '$status_form', 
        max_kuota_g1 = '$q1', 
        max_kuota_g2 = '$q2' 
        WHERE id = 1");
        
    echo "<script>alert('Seluruh konfigurasi sistem berhasil diupdate!'); window.location='admin.php';</script>";
}
// Ambil data jadwal saat ini
$pengaturan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pengaturan WHERE id = 1"));

$tab_aktif = isset($_GET['tab']) ? $_GET['tab'] : 'akl';
$gel_aktif = isset($_GET['gel']) ? $_GET['gel'] : 'Semua';

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$sql_gel_filter = ($gel_aktif == 'Semua') ? "" : " AND gelombang = '$gel_aktif'";

// Format label gelombang (TIDAK ADA CADANGAN)
if ($gel_aktif == 'Semua') {
    $label_gelombang = "Semua Gelombang";
} else {
    $label_gelombang = "Gelombang " . $gel_aktif;
}

$sql_search_filter = ($search != '') ? " AND (nama_lengkap LIKE '%$search%' OR no_pendaftaran LIKE '%$search%' OR nisn LIKE '%$search%')" : "";

function hitungKuota($jurusan, $status, $gel, $conn) {
    $filter = ($gel == 'Semua') ? "" : " AND gelombang = '$gel'";
    $q = mysqli_query($conn, "SELECT COUNT(*) as total FROM pendaftar WHERE pilihan_jurusan = '$jurusan' AND status_konfirmasi = '$status' $filter");
    return mysqli_fetch_assoc($q)['total'];
}

// LOGIKA PEMBAGIAN KUOTA PER JURUSAN
$gelombang_id = (int)$pengaturan['gelombang_aktif'];
$kuota_g1 = (int)$pengaturan['max_kuota_g1'];
$kuota_g2 = (int)$pengaturan['max_kuota_g2'];
$kuota_aktif = ($gelombang_id == 1) ? $kuota_g1 : $kuota_g2;
$kuota_per_jurusan = floor($kuota_aktif / 2); // Kuota dibagi 2 rata untuk AKL & MPLB

$tot_akl_all = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pendaftar WHERE pilihan_jurusan = 'Akuntansi dan Keuangan Lembaga' $sql_gel_filter"))['total'];
$akl_utama    = hitungKuota('Akuntansi dan Keuangan Lembaga', 'LULUS', $gel_aktif, $conn);

$tot_mplb_all = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pendaftar WHERE pilihan_jurusan = 'Manajemen Perkantoran dan Layanan Bisnis' $sql_gel_filter"))['total'];
$mplb_utama    = hitungKuota('Manajemen Perkantoran dan Layanan Bisnis', 'LULUS', $gel_aktif, $conn);

$tot_all = $tot_akl_all + $tot_mplb_all;

$order_logic = "ORDER BY CASE status_konfirmasi 
                    WHEN 'LULUS' THEN 1 
                    WHEN 'Menunggu' THEN 2 
                    WHEN 'Tidak Jadi' THEN 3 
                END ASC, 
                nilai_akhir_sql DESC, 
                tanggal_lahir ASC, 
                tanggal_daftar ASC, 
                id ASC";

$rumus_nilai = "((nilai_skl * 0.70) + (nilai_tka * 0.30))";

$query_akl = "SELECT *, $rumus_nilai as nilai_akhir_sql FROM pendaftar WHERE pilihan_jurusan = 'Akuntansi dan Keuangan Lembaga' $sql_gel_filter $sql_search_filter $order_logic";
$result_akl = mysqli_query($conn, $query_akl);

$query_mplb = "SELECT *, $rumus_nilai as nilai_akhir_sql FROM pendaftar WHERE pilihan_jurusan = 'Manajemen Perkantoran dan Layanan Bisnis' $sql_gel_filter $sql_search_filter $order_logic";
$result_mplb = mysqli_query($conn, $query_mplb);

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$domain_web = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin SPMB</title>
    <link rel="icon" type="image/x-icon" href="logo/logosmkpb.png">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8fafc; color: #1e293b; margin: 0; padding-bottom: 50px;}
        .nav-admin { background: #fff; padding: 15px 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .filter-container { background: #fff; padding: 15px 25px; border-radius: 12px; margin: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        .filter-gelombang { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .search-box { display: flex; gap: 8px; flex-wrap: wrap; }
        .search-box input { padding: 8px 12px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; width: 250px; font-family: inherit; font-size: 13.5px; }
        .search-box input:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,0.1); }
        .btn-search { padding: 8px 15px; background: #4f46e5; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 13.5px; }
        .btn-reset-search { padding: 8px 15px; background: #ef4444; color: white; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 13.5px; }
        .btn-filter { padding: 8px 18px; border-radius: 8px; font-weight: 600; font-size: 13.5px; text-decoration: none; color: #64748b; background: #f1f5f9; border: 1px solid #cbd5e1; transition: all 0.2s; }
        .btn-filter:hover { background: #e2e8f0; }
        .btn-filter.active { background: #4f46e5; color: #fff; border-color: #4f46e5; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2); }
        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 0 20px 20px 20px; }
        .card-box { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); position: relative; overflow: hidden; }
        .card-box h4 { margin: 0 0 15px 0; font-size: 14px; color: #475569; font-weight: 700; text-transform: uppercase; }
        .stat-badge-small { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: bold; margin-top: 10px; }
        .btn-toggle-jurusan { width: calc(100% - 40px); margin: 15px 20px 0 20px; background: #ffffff; padding: 18px 25px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); display: flex; justify-content: space-between; align-items: center; border: 1px solid #e2e8f0; cursor: pointer; text-align: left; }
        .btn-toggle-jurusan h3 { margin: 0; font-size: 16px; color: #1e293b; font-weight: 800; display: flex; align-items: center; gap: 10px; }
        .badge-count { padding: 6px 14px; font-weight: bold; border-radius: 20px; font-size: 12.5px; margin-right: 15px; border: 1px solid currentColor; }
        .panel-tabel { max-height: 0; overflow: hidden; margin: 0 20px; opacity: 0; }
        .panel-tabel.terbuka { max-height: 8000px; margin-top: 15px; margin-bottom: 30px; opacity: 1; overflow: visible;}
        .table-responsive { overflow-x: auto; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; }
        table { width: 100%; border-collapse: collapse; white-space: nowrap; font-size: 13px; }
        th { background: #f1f5f9; padding: 14px 15px; font-weight: 700; color: #475569; text-align: left; border-bottom: 2px solid #e2e8f0; }
        td { padding: 14px 15px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .btn-group { display: flex; gap: 6px; flex-wrap: wrap; }
        .btn-action { display: inline-block; padding: 6px 12px; font-size: 11.5px; font-weight: 600; border-radius: 6px; text-decoration: none; border: none; cursor: pointer; color: white; text-align: center; }
        .bg-view { background: #4f46e5; } 
        .bg-edit { background: #0284c7; } 
        .bg-success-btn { background: #10b981; } 
        .bg-danger-btn { background: #ef4444; } 
        .bg-reset-btn { background: #64748b; } 
        .bg-move-btn { background: #f59e0b; } 
        .bg-print-bukti { background: #8b5cf6; } 
        .bg-offline { background: #334155; }
        .bg-wa { background: #25D366; color: white; } 
        .bg-kolektif { background: #f43f5e; color: white; } 
        
        /* CSS JAKEDU */
        .bg-jakedu-set { background: #059669; color: white; border: 1px solid #047857; }
        .bg-jakedu-unset { background: #94a3b8; color: white; }
        .badge-jkd { font-size: 10px; padding: 3px 6px; border-radius: 4px; font-weight: 800; display: inline-block; margin-left: 6px; vertical-align: middle; }
        .jkd-done { background: #d1fae5; color: #065f46; border: 1px solid #34d399; }
        .jkd-none { background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; }
        
        .status-badge { padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 4px; text-transform: uppercase; }
        .badge-locked-utama { background: #d1fae5; color: #065f46; border: 1px solid #34d399; }
        .badge-batal { background: #fee2e2; color: #991b1b; border: 1px solid #f87171; }
        .badge-utama { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
        .small-text { font-size: 11px; color: #64748b; display: block; line-height: 1.4; }
        .nilai-box { background: #f8fafc; padding: 10px 14px; border-radius: 8px; border: 1px solid #e2e8f0; display: inline-block; min-width: 220px;}
        .rincian-bobot { font-size: 10.5px; color: #64748b; margin-bottom: 6px; border-bottom: 1px dashed #cbd5e1; padding-bottom: 6px; line-height: 1.6; }
        .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15,23,42,0.6); display: none; align-items: center; justify-content: center; z-index: 9999; }
        .modal-content { background: #fff; width: 92%; max-width: 650px; border-radius: 16px; padding: 25px; max-height: 90vh; overflow-y: auto;}
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; }
        .grid-berkas { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; }
        .berkas-item { display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 13.5px; font-weight: 600; }
        .berkas-item.success { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
        .berkas-item.danger { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
        .berkas-item .btn-lihat { background: #10b981; color: #fff; padding: 5px 12px; border-radius: 6px; text-decoration: none; font-size: 11.5px; }
        tbody tr:hover { background-color: #eef2ff !important; transition: 0.2s; }
    </style>
</head>
<body>

    <div class="nav-admin">
        <div>
            <h2 style="margin: 0; font-size: 20px;">Dasbor Seleksi Penerimaan Siswa Baru</h2>
            <small style="color:#64748b;">Otoritas: <b><?php echo isset($_SESSION['admin_user']) ? $_SESSION['admin_user'] : 'Admin Panitia'; ?></b></small>
        </div>
        <div style="display:flex; gap:10px; flex-wrap: wrap;">
            <a href="cetak_nilai_kolektif.php?gel=<?php echo $gel_aktif; ?>" target="_blank" class="btn-action bg-edit" style="background: #0ea5e9;">📊 Cetak Rekap Nilai</a>
            <a href="cetak_data_full.php?gel=<?php echo $gel_aktif; ?>" target="_blank" class="btn-action" style="background: #14b8a6; color: white;">🗂️ Cetak Full Data</a>
            
            <!-- TOMBOL BARU: CETAK SISWA LULUS -->
            <a href="cetak_data_lulus.php?gel=<?php echo $gel_aktif; ?>" target="_blank" class="btn-action" style="background: #10b981; color: white;">🏆 Cetak Siswa Lulus</a>
            
            <a href="broadcast_wa.php" class="btn-action" style="background: #25D366; color: white;">📢 Broadcast WA</a>
            <a href="cetak_kolektif.php" target="_blank" class="btn-action bg-kolektif">📑 Cetak Bukti Kolektif</a>
            <a href="formulir_offline.php" target="_blank" class="btn-action bg-offline">🖨️ Cetak Form Offline</a>
            <a href="cetak_pakta_kolektif.php?tab=<?php echo $tab_aktif; ?>&gel=<?php echo $gel_aktif; ?>" target="_blank" class="btn-action" style="background: #eab308; color: #fff;">🖨️ Cetak Pakta Kolektif</a>
            
            <button onclick="document.getElementById('modalJadwal').style.display='flex'" class="btn-action bg-edit" style="background:#8b5cf6;">⚙️ Setting Jadwal</button>
            <a href="logout.php" class="btn-action bg-danger-btn" onclick="return confirm('Keluar sistem?')">Logout</a>
        </div>
    </div>
    <?php include "../tenggat/tenggat.php" ?>
    <div class="filter-container">
        <div class="filter-gelombang">
            <span style="font-weight:700; color:#475569; font-size:14px;">🎛️ Filter Tampilan:</span>
            <a href="?tab=<?php echo $tab_aktif; ?>&gel=Semua&search=<?php echo urlencode($search); ?>" class="btn-filter <?php echo ($gel_aktif == 'Semua') ? 'active' : ''; ?>">Semua Gelombang</a>
            <a href="?tab=<?php echo $tab_aktif; ?>&gel=1&search=<?php echo urlencode($search); ?>" class="btn-filter <?php echo ($gel_aktif == '1') ? 'active' : ''; ?>">Hanya Gelombang 1</a>
            <a href="?tab=<?php echo $tab_aktif; ?>&gel=2&search=<?php echo urlencode($search); ?>" class="btn-filter <?php echo ($gel_aktif == '2') ? 'active' : ''; ?>">Hanya Gelombang 2</a>
            <a href="daftar_ulang/index.php" class="btn-action" style="background: #e11d48; color: white; padding: 8px 15px; font-weight: 800; border: 2px solid #be123c;">📁 PANEL DAFTAR ULANG</a>
            <a href="export/excel_semua.php?gel=<?php echo $gel_aktif; ?>" class="btn-action" style="background: #15803d; color: white;">📥 Export Excel</a>
        </div>

        <form action="" method="GET" class="search-box">
            <input type="hidden" name="tab" value="<?php echo $tab_aktif; ?>">
            <input type="hidden" name="gel" value="<?php echo $gel_aktif; ?>">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari Nama / No Daftar / NISN...">
            <button type="submit" class="btn-search">🔍 Cari</button>
            <?php if($search != ''): ?>
                <a href="?tab=<?php echo $tab_aktif; ?>&gel=<?php echo $gel_aktif; ?>" class="btn-reset-search">✖ Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="summary-grid">
        <div class="card-box" style="border-left: 5px solid #4f46e5;">
            <h4>Total Pendaftar (<?php echo $label_gelombang; ?>)</h4>
            <div style="font-size: 32px; font-weight: 800; color:#1e293b;"><?php echo $tot_all; ?> <span style="font-size:14px; color:#64748b;">Siswa</span></div>
        </div>
        <div class="card-box" style="border-left: 5px solid #10b981;">
            <h4>Statistik AKL (<?php echo $label_gelombang; ?>)</h4>
            <div style="font-size: 26px; font-weight: 800; color:#1e293b;"><?php echo $tot_akl_all; ?> <span style="font-size:14px; color:#64748b;">Mendaftar</span></div>
            <div class="stat-badge-small" style="background: #d1fae5; color: #065f46; border: 1px solid #34d399;">Lulus: <?php echo $akl_utama; ?> / Kuota: <?php echo $kuota_per_jurusan; ?></div>
        </div>
        <div class="card-box" style="border-left: 5px solid #0284c7;">
            <h4>Statistik MPLB (<?php echo $label_gelombang; ?>)</h4>
            <div style="font-size: 26px; font-weight: 800; color:#1e293b;"><?php echo $tot_mplb_all; ?> <span style="font-size:14px; color:#64748b;">Mendaftar</span></div>
            <div class="stat-badge-small" style="background: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc;">Lulus: <?php echo $mplb_utama; ?> / Kuota: <?php echo $kuota_per_jurusan; ?></div>
        </div>
    </div>

    <?php if($search != ''): ?>
        <div style="margin: 0 20px 15px 20px; color: #4f46e5; font-weight:bold; font-size:14px;">
            🔍 Menampilkan hasil pencarian untuk: "<?php echo htmlspecialchars($search); ?>"
        </div>
    <?php endif; ?>

    <!-- TAB: AKUNTANSI -->
    <button class="btn-toggle-jurusan <?php echo ($tab_aktif == 'akl') ? 'aktif' : ''; ?>" onclick="toggleTabel('panel_akl', this, 'akl')" style="border-left: 6px solid #10b981;">
        <h3>📁 Akuntansi dan Keuangan Lembaga (AKL)</h3>
        <div>
            <a href="cetak_data_lulus.php?gel=<?php echo $gel_aktif; ?>&jurusan=Akuntansi%20dan%20Keuangan%20Lembaga" target="_blank" class="btn-action" style="background: #10b981;">Cetak Lulus AKL</a>

            <a href="konfirmasi_kolektif.php?jurusan=Akuntansi%20dan%20Keuangan%20Lembaga&gel=<?php echo $gel_aktif; ?>&status=LULUS&limit=<?php echo $kuota_per_jurusan; ?>" 
            class="btn-action bg-success-btn" onclick="return confirm('Yakin meluluskan <?php echo $kuota_per_jurusan; ?> siswa teratas untuk AKL di <?php echo $label_gelombang; ?>?')">Luluskan <?php echo $kuota_per_jurusan; ?> Teratas (Sesuai Kuota)</a>
            
            <a href="konfirmasi_kolektif.php?jurusan=Akuntansi%20dan%20Keuangan%20Lembaga&gel=<?php echo $gel_aktif; ?>&status=Menunggu" 
            class="btn-action bg-reset-btn" onclick="return confirm('Yakin reset status semua pendaftar AKL <?php echo $label_gelombang; ?>?')">Reset Semua</a>
            
            <a href="admin.php?jakedu_massal=sudah&jurusan=Akuntansi%20dan%20Keuangan%20Lembaga&gel=<?php echo $gel_aktif; ?>&tab=akl" 
            class="btn-action bg-jakedu-set" onclick="return confirm('Tandai SUDAH di-input ke Jakedu untuk semua pendaftar AKL <?php echo $label_gelombang; ?>?')">✅ Set Jakedu Semua</a>

            <span class="badge-count" style="background: #d1fae5; color: #065f46;">Lulus: <?php echo $akl_utama; ?> / <?php echo $tot_akl_all; ?></span>
        </div>
    </button>

    <div id="panel_akl" class="panel-tabel <?php echo ($tab_aktif == 'akl') ? 'terbuka' : ''; ?>">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Identitas & Gelombang</th>
                        <th>No HP (WA)</th>
                        <th>Status & Catatan</th>
                        <th>Dokumen Fisik</th>
                        <th>Evaluasi Nilai & Pembobotan</th>
                        <th>Aksi Keputusan Panitia</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1; 
                    if (mysqli_num_rows($result_akl) > 0) { 
                        while($row = mysqli_fetch_assoc($result_akl)) { 
                            if ($row['status_konfirmasi'] == 'LULUS') {
                                $zb = "<span class='status-badge badge-locked-utama'>🔒 Lulus</span>";
                            } elseif ($row['status_konfirmasi'] == 'Tidak Jadi') {
                                $zb = "<span class='status-badge badge-batal'>❌ Tidak Lulus / Batal</span>";
                                if (!empty($row['alasan_pembatalan'])) {
                                    $zb .= "<span style='display:block; margin-top:6px; color:#ef4444; font-size:11.5px; font-weight:600; font-style:italic;'>💬 " . htmlspecialchars($row['alasan_pembatalan'], ENT_QUOTES, 'UTF-8') . "</span>";                                }
                            } else {
                                $zb = "<span class='status-badge badge-utama'>Menunggu ($rank)</span>";
                            }

                            // Jakedu Logic Display
                            $status_jakedu = isset($row['status_jakedu']) ? $row['status_jakedu'] : 'Belum';
                            if ($status_jakedu == 'Sudah') {
                                $badge_jakedu = "<span class='badge-jkd jkd-done'>✅ JAKEDU</span>";
                            } else {
                                $badge_jakedu = "<span class='badge-jkd jkd-none'>❌ JAKEDU</span>";
                            }

                            $no_hp = preg_replace('/[^0-9]/', '', $row['no_whatsapp']);
                            if (substr($no_hp, 0, 1) == '0') {
                                $no_wa = '62' . substr($no_hp, 1);
                            } else {
                                $no_wa = $no_hp;
                            }
                            
                            $alasan_wa = !empty($row['alasan_pembatalan']) ? "\n*Catatan:* " . trim($row['alasan_pembatalan']) : "";
                            $status_wa = $row['status_konfirmasi'];
                            if($status_wa == 'Tidak Jadi') { $status_wa = "TIDAK LOLOS / BATAL"; }

                            $pesan_wa_mentah = "Halo Bapak/Ibu Calon Wali Murid dari *" . $row['nama_lengkap'] . "* (NISN: " . $row['nisn'] . ").\n\nBerdasarkan hasil seleksi Panitia SPMB SMK PERMATA BUNDA I JAKARTA, kami menginformasikan bahwa status pendaftaran putra/putri Anda saat ini adalah: *" . $status_wa . "*" . $alasan_wa . "\n\nJurusan: *" . $row['pilihan_jurusan'] . "*\nJalur: *Gelombang " . $row['gelombang'] . "*\n\nSilakan unduh atau cetak dokumen hasil seleksi pada tautan berikut:\n" . $domain_web . "/bukti.php?no_pendaftaran=" . $row['no_pendaftaran'] . "\n\nSilahkan lihat hasil live board pada tautan berikut\n*https://smkpb1.my.id/spmb/spmbsmk/live_board.php*\n\nJika Lulus/Kurang Berkas silahkan serahkan lalu ambil formulir daftar ulang ke sekolah\n\nTerima kasih.";

                            $pesan_wa = urlencode($pesan_wa_mentah);

                            $asli_skl = (float)$row['nilai_skl'];
                            $asli_tka = (float)$row['nilai_tka'];
                            $bobot_skl = $asli_skl * 0.70;
                            $bobot_tka = $asli_tka * 0.30;
                            
                            $nilai_berkas_asli = ($asli_skl + $asli_tka) / 2;
                            $nilai_berkas_bobot = $bobot_skl + $bobot_tka;
                    ?>
                    <tr>
                        <td style="text-align:center; font-size:16px;"><b><?php echo $rank; ?></b></td>
                        <td>
                            <div class="identitas-box">
                                <span style="font-weight: 800; font-size: 14px;"><?php echo htmlspecialchars($row['nama_lengkap'], ENT_QUOTES, 'UTF-8'); ?> <?php echo $badge_jakedu; ?></span>
                                <span class="small-text">NISN: <?php echo htmlspecialchars($row['nisn'], ENT_QUOTES, 'UTF-8');?></span>
                                <span class="small-text">Nomor Peserta: <?php echo htmlspecialchars($row['no_ijazah'], ENT_QUOTES, 'UTF-8');?></span>
                                <span class="small-text">Jalur: <b style="color:#d97706; text-transform: capitalize;">Gelombang <?php echo htmlspecialchars($row['gelombang'], ENT_QUOTES, 'UTF-8'); ?></b></span>
                            </div>
                        </td>
                        <td>
                            <span style="font-weight:600; display:block; margin-bottom:5px; font-size:13px;"><?php echo htmlspecialchars($row['no_whatsapp'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <a href="https://wa.me/<?php echo $no_wa; ?>?text=<?php echo $pesan_wa; ?>" target="_blank" class="btn-action bg-wa">💬 Kirim WA Bukti</a>
                        </td>
                        <td>
                            <?php echo $zb; ?>
                            
                            <?php if (!empty($row['catatan_panitia'])): ?>
                                <div style="margin-top: 8px; background: #fff1f2; border: 1px dashed #fda4af; padding: 8px; border-radius: 6px; font-size: 11px; color: #be123c; line-height: 1.4; max-width: 220px; white-space: normal;">
                                    <b>📌 Catatan Panitia:</b><br>
                                    <?php echo nl2br(htmlspecialchars($row['catatan_panitia'], ENT_QUOTES, 'UTF-8')); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn-action bg-view" onclick="bukaModalBerkas('<?php echo addslashes($row['nama_lengkap']); ?>', '<?php echo $row['file_ijazah']; ?>', '<?php echo $row['file_tka']; ?>', '<?php echo $row['file_kk']; ?>', '<?php echo $row['file_akte']; ?>', '<?php echo $row['file_ktp_bapak']; ?>', '<?php echo $row['file_ktp_ibu']; ?>', '<?php echo $row['file_sptjm']; ?>', '<?php echo $row['status_kjp']; ?>', '<?php echo $row['file_tabungan_kjp']; ?>')">📂 Cek Berkas</button>
                        </td>
                        <td>
                            <div class="nilai-box">
                                <div class="rincian-bobot">
                                    SDN Asli: <b><?php echo number_format($asli_skl, 2); ?></b> &rarr; Bobot 70%: <b style="color:#1e293b;"><?php echo number_format($bobot_skl, 2); ?></b><br>
                                    TKA Asli: <b><?php echo number_format($asli_tka, 2); ?></b> &rarr; Bobot 30%: <b style="color:#1e293b;"><?php echo number_format($bobot_tka, 2); ?></b>
                                </div>
                                <span class="small-text" style="color:#64748b;">Berkas Asli (Rata2): <b><?php echo number_format($nilai_berkas_asli, 2); ?></b></span>
                                <span class="small-text" style="color:#0284c7;">Berkas Bobot (70%+30%): <b><?php echo number_format($nilai_berkas_bobot, 2); ?></b></span>
                                
                                <div style="margin-top:5px; border-top:1px solid #cbd5e1; padding-top:5px; font-weight: 800; font-size: 14px; color:#0f172a;">
                                    RATA AKHIR: <span style="color:#10b981;"><?php echo number_format($nilai_berkas_bobot, 2); ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group">
                                <?php if($status_jakedu == 'Belum'): ?>
                                    <a href="admin.php?jakedu=sudah&id_siswa=<?php echo $row['id']; ?>&tab=akl&gel=<?php echo $gel_aktif; ?>" class="btn-action bg-jakedu-set" onclick="return confirm('Tandai siswa ini SUDAH di-input ke Jakedu?')">Input Jakedu</a>
                                <?php else: ?>
                                    <a href="admin.php?jakedu=belum&id_siswa=<?php echo $row['id']; ?>&tab=akl&gel=<?php echo $gel_aktif; ?>" class="btn-action bg-jakedu-unset" onclick="return confirm('Batalkan status Jakedu siswa ini?')">Batal Jakedu</a>
                                <?php endif; ?>

                                <a href="bukti.php?no_pendaftaran=<?php echo urlencode($row['no_pendaftaran']); ?>" target="_blank" class="btn-action bg-print-bukti">Bukti</a>
                                
                                <?php if($row['status_konfirmasi'] == 'Menunggu'): ?>
                                    <a href="edit.php?id=<?php echo $row['id']; ?>&tab=akl" class="btn-action bg-edit">Edit Siswa/Nilai</a>
                                    <a href="konfirmasi.php?id=<?php echo $row['id']; ?>&status=LULUS&tab=akl" class="btn-action bg-success-btn" onclick="return confirm('Kunci kelulusan untuk siswa ini?')">Lulus</a>
                                    <a href="konfirmasi.php?id=<?php echo $row['id']; ?>&status=Pindah&tab=akl" class="btn-action bg-move-btn" onclick="return confirm('Pindahkan siswa ini ke jurusan MPLB?')">Lempar MPLB</a>
                                    <a href="#" class="btn-action" style="background:#f97316;" onclick="pindahGelombang(<?php echo $row['id']; ?>, 'akl'); return false;">🔄 Lempar Gel</a>
                                    <a href="#" class="btn-action bg-edit" style="background:#8b5cf6;" onclick="duplikatGelombang(<?php echo $row['id']; ?>, 'akl'); return false;">📋 Duplikat Gel</a>
                                    
                                    <a href="#" class="btn-action bg-danger-btn" onclick="let alasan = prompt('Masukkan alasan tidak lulus/batal untuk siswa ini:'); if(alasan === null) return false; if(alasan.trim() === '') { alert('Alasan wajib diisi!'); return false; } window.location.href='konfirmasi.php?id=<?php echo $row['id']; ?>&status=Tidak Jadi&tab=akl&alasan=' + encodeURIComponent(alasan); return false;">Tidak Lulus</a>
                                <?php else: ?>
                                    <a href="edit.php?id=<?php echo $row['id']; ?>&tab=akl" class="btn-action bg-edit">Edit Data/Nilai</a>
                                    <a href="konfirmasi.php?id=<?php echo $row['id']; ?>&status=Menunggu&tab=akl" class="btn-action bg-reset-btn" onclick="return confirm('Kembalikan status siswa ke menunggu?')">Reset Status</a>
                                    <a href="konfirmasi.php?id=<?php echo $row['id']; ?>&status=Pindah&tab=mplb" class="btn-action bg-move-btn" onclick="return confirm('Pindahkan siswa ini ke jurusan MPLB?')">Lempar MPLB</a>
                                    <a href="#" class="btn-action bg-edit" style="background:#8b5cf6;" onclick="duplikatGelombang(<?php echo $row['id']; ?>, 'akl'); return false;">📋 Duplikat Gel</a>
                                    <a href="hapus.php?id=<?php echo $row['id']; ?>&tab=akl" class="btn-action bg-danger-btn" onclick="return confirm('Hapus permanen dari database?')">Hapus</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php $rank++; }} else { echo "<tr><td colspan='7' style='text-align:center; padding:30px;'>Pencarian/Data tidak ditemukan pada jurusan ini.</td></tr>"; } ?>
                </tbody>
            </table>
        </div>
    </div>
   
    <!-- TAB: MPLB -->
    <button class="btn-toggle-jurusan <?php echo ($tab_aktif == 'mplb') ? 'aktif' : ''; ?>" onclick="toggleTabel('panel_mplb', this, 'mplb')" style="border-left: 6px solid #0284c7;">
        <h3>📁 Manajemen Perkantoran dan Layanan Bisnis (MPLB)</h3>
    <div>
        <a href="cetak_data_lulus.php?gel=<?php echo $gel_aktif; ?>&jurusan=Manajemen%20Perkantoran%20dan%20Layanan%20Bisnis" target="_blank" class="btn-action" style="background: #10b981;">Cetak Lulus MPLB</a>
        
        <a href="konfirmasi_kolektif.php?jurusan=Manajemen%20Perkantoran%20dan%20Layanan%20Bisnis&gel=<?php echo $gel_aktif; ?>&status=LULUS&limit=<?php echo $kuota_per_jurusan; ?>" 
           class="btn-action bg-success-btn" onclick="return confirm('Yakin meluluskan <?php echo $kuota_per_jurusan; ?> siswa teratas untuk MPLB di <?php echo $label_gelombang; ?>?')">Luluskan <?php echo $kuota_per_jurusan; ?> Teratas (Sesuai Kuota)</a>
           
        <a href="konfirmasi_kolektif.php?jurusan=Manajemen%20Perkantoran%20dan%20Layanan%20Bisnis&gel=<?php echo $gel_aktif; ?>&status=Menunggu" 
           class="btn-action bg-reset-btn" onclick="return confirm('Yakin reset status semua pendaftar MPLB <?php echo $label_gelombang; ?>?')">Reset Semua</a>
        
        <a href="admin.php?jakedu_massal=sudah&jurusan=Manajemen%20Perkantoran%20dan%20Layanan%20Bisnis&gel=<?php echo $gel_aktif; ?>&tab=mplb" 
           class="btn-action bg-jakedu-set" onclick="return confirm('Tandai SUDAH di-input ke Jakedu untuk semua pendaftar MPLB <?php echo $label_gelombang; ?>?')">✅ Set Jakedu Semua</a>

        <span class="badge-count" style="background: #d1fae5; color: #065f46;">Lulus: <?php echo $mplb_utama; ?> / <?php echo $tot_mplb_all; ?></span>
    </div>
    </button>

    <div id="panel_mplb" class="panel-tabel <?php echo ($tab_aktif == 'mplb') ? 'terbuka' : ''; ?>">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Identitas & Gelombang</th>
                        <th>No HP (WA)</th>
                        <th>Status & Catatan</th>
                        <th>Dokumen Fisik</th>
                        <th>Evaluasi Nilai & Pembobotan</th>
                        <th>Aksi Keputusan Panitia</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1; 
                    if (mysqli_num_rows($result_mplb) > 0) { 
                        while ($row = mysqli_fetch_assoc($result_mplb)) { 
                            if ($row['status_konfirmasi'] == 'LULUS') {
                                $zb = "<span class='status-badge badge-locked-utama'>🔒 Lulus</span>";
                            } elseif ($row['status_konfirmasi'] == 'Tidak Jadi') {
                                $zb = "<span class='status-badge badge-batal'>❌ Tidak Lulus / Batal</span>";
                                if (!empty($row['alasan_pembatalan'])) {
                                    $zb .= "<span style='display:block; margin-top:6px; color:#ef4444; font-size:11.5px; font-weight:600; font-style:italic;'>💬 " . htmlspecialchars($row['alasan_pembatalan'], ENT_QUOTES, 'UTF-8') . "</span>";                                }
                            } else {
                                $zb = "<span class='status-badge badge-utama'>Menunggu ($rank)</span>";
                            }

                            // Jakedu Logic Display
                            $status_jakedu = isset($row['status_jakedu']) ? $row['status_jakedu'] : 'Belum';
                            if ($status_jakedu == 'Sudah') {
                                $badge_jakedu = "<span class='badge-jkd jkd-done'>✅ JAKEDU</span>";
                            } else {
                                $badge_jakedu = "<span class='badge-jkd jkd-none'>❌ JAKEDU</span>";
                            }

                            $no_hp = preg_replace('/[^0-9]/', '', $row['no_whatsapp']);
                            if (substr($no_hp, 0, 1) == '0') {
                                $no_wa = '62' . substr($no_hp, 1);
                            } else {
                                $no_wa = $no_hp;
                            }
                            
                            $alasan_wa = !empty($row['alasan_pembatalan']) ? "\n*Catatan:* " . trim($row['alasan_pembatalan']) : "";
                            $status_wa = $row['status_konfirmasi'];
                            if($status_wa == 'Tidak Jadi') { $status_wa = "TIDAK LOLOS / BATAL"; }

                            $pesan_wa_mentah = "Halo Bapak/Ibu Calon Wali Murid dari *" . $row['nama_lengkap'] . "* (NISN: " . $row['nisn'] . ").\n\nBerdasarkan hasil seleksi Panitia SPMB SMK PERMATA BUNDA I JAKARTA, kami menginformasikan bahwa status pendaftaran putra/putri Anda saat ini adalah: *" . $status_wa . "*" . $alasan_wa . "\n\nJurusan: *" . $row['pilihan_jurusan'] . "*\nJalur: *Gelombang " . $row['gelombang'] . "*\n\nSilakan unduh atau cetak dokumen hasil seleksi pada tautan berikut:\n" . $domain_web . "/bukti.php?no_pendaftaran=" . $row['no_pendaftaran'] . "\n\nSilahkan lihat hasil live board pada tautan berikut\n*https://smkpb1.my.id/spmb/spmbsmk/live_board.php*\n\nJika Lulus/Kurang Berkas silahkan serahkan lalu ambil formulir daftar ulang ke sekolah\n\nTerima kasih.";

                            $pesan_wa = urlencode($pesan_wa_mentah);

                            $asli_skl = (float)$row['nilai_skl'];
                            $asli_tka = (float)$row['nilai_tka'];
                            $bobot_skl = $asli_skl * 0.70;
                            $bobot_tka = $asli_tka * 0.30;
                            
                            $nilai_berkas_asli = ($asli_skl + $asli_tka) / 2;
                            $nilai_berkas_bobot = $bobot_skl + $bobot_tka;
                    ?>
                    <tr>
                        <td style="text-align:center; font-size:16px;"><b><?php echo $rank; ?></b></td>
                        <td>
                            <div class="identitas-box">
                                <span style="font-weight: 800; font-size: 14px;"><?php echo htmlspecialchars($row['nama_lengkap'], ENT_QUOTES, 'UTF-8'); ?> <?php echo $badge_jakedu; ?></span>
                                <span class="small-text">NISN: <?php echo htmlspecialchars($row['nisn'], ENT_QUOTES, 'UTF-8');?></span>
                                <span class="small-text">Nomor Peserta: <?php echo htmlspecialchars($row['no_ijazah'], ENT_QUOTES, 'UTF-8');?></span>
                                <span class="small-text">Jalur: <b style="color:#d97706; text-transform: capitalize;">Gelombang <?php echo htmlspecialchars($row['gelombang'], ENT_QUOTES, 'UTF-8'); ?></b></span>
                            </div>
                        </td>
                        <td>
                            <span style="font-weight:600; display:block; margin-bottom:5px; font-size:13px;"><?php echo htmlspecialchars($row['no_whatsapp'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <a href="https://wa.me/<?php echo $no_wa; ?>?text=<?php echo $pesan_wa; ?>" target="_blank" class="btn-action bg-wa">💬 Kirim WA Bukti</a>
                        </td>
                        <td>
                            <?php echo $zb; ?>
                            
                            <?php if (!empty($row['catatan_panitia'])): ?>
                                <div style="margin-top: 8px; background: #fff1f2; border: 1px dashed #fda4af; padding: 8px; border-radius: 6px; font-size: 11px; color: #be123c; line-height: 1.4; max-width: 220px; white-space: normal;">
                                    <b>📌 Catatan Panitia:</b><br>
                                    <?php echo nl2br(htmlspecialchars($row['catatan_panitia'], ENT_QUOTES, 'UTF-8')); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn-action bg-view" onclick="bukaModalBerkas('<?php echo addslashes($row['nama_lengkap']); ?>', '<?php echo $row['file_ijazah']; ?>', '<?php echo $row['file_tka']; ?>', '<?php echo $row['file_kk']; ?>', '<?php echo $row['file_akte']; ?>', '<?php echo $row['file_ktp_bapak']; ?>', '<?php echo $row['file_ktp_ibu']; ?>', '<?php echo $row['file_sptjm']; ?>', '<?php echo $row['status_kjp']; ?>', '<?php echo $row['file_tabungan_kjp']; ?>')">📂 Cek Berkas</button>
                        </td>
                        <td>
                            <div class="nilai-box">
                                <div class="rincian-bobot">
                                    SDN Asli: <b><?php echo number_format($asli_skl, 2); ?></b> &rarr; Bobot 70%: <b style="color:#1e293b;"><?php echo number_format($bobot_skl, 2); ?></b><br>
                                    TKA Asli: <b><?php echo number_format($asli_tka, 2); ?></b> &rarr; Bobot 30%: <b style="color:#1e293b;"><?php echo number_format($bobot_tka, 2); ?></b>
                                </div>
                                <span class="small-text" style="color:#64748b;">Berkas Asli (Rata2): <b><?php echo number_format($nilai_berkas_asli, 2); ?></b></span>
                                <span class="small-text" style="color:#0284c7;">Berkas Bobot (70%+30%): <b><?php echo number_format($nilai_berkas_bobot, 2); ?></b></span>
                                
                                <div style="margin-top:5px; border-top:1px solid #cbd5e1; padding-top:5px; font-weight: 800; font-size: 14px; color:#0f172a;">
                                    RATA AKHIR: <span style="color:#10b981;"><?php echo number_format($nilai_berkas_bobot, 2); ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group">
                                <?php if($status_jakedu == 'Belum'): ?>
                                    <a href="admin.php?jakedu=sudah&id_siswa=<?php echo $row['id']; ?>&tab=mplb&gel=<?php echo $gel_aktif; ?>" class="btn-action bg-jakedu-set" onclick="return confirm('Tandai siswa ini SUDAH di-input ke Jakedu?')">Input Jakedu</a>
                                <?php else: ?>
                                    <a href="admin.php?jakedu=belum&id_siswa=<?php echo $row['id']; ?>&tab=mplb&gel=<?php echo $gel_aktif; ?>" class="btn-action bg-jakedu-unset" onclick="return confirm('Batalkan status Jakedu siswa ini?')">Batal Jakedu</a>
                                <?php endif; ?>

                                <a href="bukti.php?no_pendaftaran=<?php echo urlencode($row['no_pendaftaran']); ?>" target="_blank" class="btn-action bg-print-bukti">Bukti</a>
                                
                                <?php if($row['status_konfirmasi'] == 'Menunggu'): ?>
                                    <a href="edit.php?id=<?php echo $row['id']; ?>&tab=mplb" class="btn-action bg-edit">Edit Siswa/Nilai</a>
                                    <a href="konfirmasi.php?id=<?php echo $row['id']; ?>&status=LULUS&tab=mplb" class="btn-action bg-success-btn" onclick="return confirm('Kunci kelulusan untuk siswa ini?')">Lulus</a>
                                    <a href="konfirmasi.php?id=<?php echo $row['id']; ?>&status=Pindah&tab=akl" class="btn-action bg-move-btn" onclick="return confirm('Pindahkan siswa ini ke jurusan AKL?')">Lempar AKL</a>
                                    <a href="#" class="btn-action" style="background:#f97316;" onclick="pindahGelombang(<?php echo $row['id']; ?>, 'mplb'); return false;">🔄 Lempar Gel</a>
                                    <a href="#" class="btn-action bg-edit" style="background:#8b5cf6;" onclick="duplikatGelombang(<?php echo $row['id']; ?>, 'mplb'); return false;">📋 Duplikat Gel</a>
                                    
                                    <a href="#" class="btn-action bg-danger-btn" onclick="let alasan = prompt('Masukkan alasan tidak lulus/batal untuk siswa ini:'); if(alasan === null) return false; if(alasan.trim() === '') { alert('Alasan wajib diisi!'); return false; } window.location.href='konfirmasi.php?id=<?php echo $row['id']; ?>&status=Tidak Jadi&tab=mplb&alasan=' + encodeURIComponent(alasan); return false;">Tidak Lulus</a>
                                <?php else: ?>
                                    <a href="edit.php?id=<?php echo $row['id']; ?>&tab=mplb" class="btn-action bg-edit">Edit Data/Nilai</a>
                                    <a href="konfirmasi.php?id=<?php echo $row['id']; ?>&status=Menunggu&tab=mplb" class="btn-action bg-reset-btn" onclick="return confirm('Kembalikan status siswa ke menunggu?')">Reset Status</a>
                                    <a href="konfirmasi.php?id=<?php echo $row['id']; ?>&status=Pindah&tab=akl" class="btn-action bg-move-btn" onclick="return confirm('Pindahkan siswa ini ke jurusan AKL?')">Lempar AKL</a>
                                    <a href="#" class="btn-action bg-edit" style="background:#8b5cf6;" onclick="duplikatGelombang(<?php echo $row['id']; ?>, 'mplb'); return false;">📋 Duplikat Gel</a>
                                    
                                    <a href="hapus.php?id=<?php echo $row['id']; ?>&tab=mplb" class="btn-action bg-danger-btn" onclick="return confirm('Hapus permanen dari database?')">Hapus</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php $rank++; }} else { echo "<tr><td colspan='7' style='text-align:center; padding:30px;'>Pencarian/Data tidak ditemukan pada jurusan ini.</td></tr>"; } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="modalBerkas" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 style="margin:0;">📂 File Pendaftar: <span id="mdl_nama" style="color:#4f46e5;"></span></h3>
                <button onclick="document.getElementById('modalBerkas').style.display='none'" style="border:none; background:#f1f5f9; padding:8px 12px; border-radius:6px; cursor:pointer; font-weight:bold;">Tutup ✕</button>
            </div>
            <div class="modal-body" id="mdl_body"></div>
        </div>
    </div>

   <?php
    function format_tgl_indo($datetime) {
        $bulan = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $timestamp = strtotime($datetime);
        return date('d', $timestamp) . ' ' . $bulan[(int)date('m', $timestamp)] . ' ' . date('Y H:i', $timestamp) . ' WIB';
    }
    ?>
    <div id="modalJadwal" class="modal-overlay">
        <div class="modal-content" style="max-width: 450px;">
            <div class="modal-header">
                <h3 style="margin:0;">⚙️ Kontrol Sistem Pusat</h3>
                <button onclick="document.getElementById('modalJadwal').style.display='none'" style="border:none; background:#f1f5f9; padding:8px 12px; border-radius:6px; cursor:pointer; font-weight:bold;">Tutup ✕</button>
            </div>
            <form method="POST">
                <div style="margin-bottom: 15px;">
                    <label style="font-weight:bold; font-size:13px; color:#475569; display:block; margin-bottom:5px;">Status Pendaftaran Global</label>
                    <select name="status_pendaftaran" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; font-weight: bold;">
                        <option value="buka" <?php echo ($pengaturan['status_pendaftaran'] == 'buka') ? 'selected' : ''; ?>>🟢 BUKA (Pendaftaran Aktif)</option>
                        <option value="tutup" <?php echo ($pengaturan['status_pendaftaran'] == 'tutup') ? 'selected' : ''; ?>>🔴 TUTUP (Kunci Akses Form)</option>
                    </select>
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="font-weight:bold; font-size:13px; color:#475569; display:block; margin-bottom:5px;">Jalur Gelombang yang Dibuka</label>
                    <select name="gelombang_aktif" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; font-weight: bold;">
                        <option value="1" <?php echo ($pengaturan['gelombang_aktif'] == 1) ? 'selected' : ''; ?>>Jalur Gelombang 1</option>
                        <option value="2" <?php echo ($pengaturan['gelombang_aktif'] == 2) ? 'selected' : ''; ?>>Jalur Gelombang 2</option>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                    <div>
                        <label style="font-weight:bold; font-size:12px; color:#475569; display:block; margin-bottom:5px;">Maks Kuota G1</label>
                        <input type="number" name="max_kuota_g1" value="<?php echo $pengaturan['max_kuota_g1']; ?>" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="font-weight:bold; font-size:12px; color:#475569; display:block; margin-bottom:5px;">Maks Kuota G2</label>
                        <input type="number" name="max_kuota_g2" value="<?php echo $pengaturan['max_kuota_g2']; ?>" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; box-sizing: border-box;">
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px solid #f1f5f9; margin: 15px 0;">

                <div style="margin-bottom: 15px;">
                    <label style="font-weight:bold; font-size:13px; color:#475569; display:block; margin-bottom:5px;">Jadwal Pengumuman Gelombang 1</label>
                    <input type="datetime-local" name="buka_gel_1" value="<?php echo date('Y-m-d\TH:i', strtotime($pengaturan['buka_gel_1'])); ?>" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; box-sizing: border-box;">
                </div>
                <div style="margin-bottom: 20px;">
                    <label style="font-weight:bold; font-size:13px; color:#475569; display:block; margin-bottom:5px;">Jadwal Pengumuman Gelombang 2</label>
                    <input type="datetime-local" name="buka_gel_2" value="<?php echo date('Y-m-d\TH:i', strtotime($pengaturan['buka_gel_2'])); ?>" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; box-sizing: border-box;">
                </div>
                <button type="submit" name="simpan_jadwal" style="width:100%; padding:12px; background:#4f46e5; color:white; border:none; border-radius:6px; font-weight:bold; cursor:pointer;">💾 Simpan Seluruh Perubahan</button>
            </form>
        </div>
    </div>

    <script>
    function duplikatGelombang(id, tabAsal) {
        let gel = prompt("Gandakan (Duplikat) siswa ini ke Gelombang mana?\n\nKetik salah satu:\n- 1\n- 2");
        if (gel !== null) {
            gel = gel.trim();
            let gel_valid = "";
            if (gel === "1") gel_valid = "1";
            else if (gel === "2") gel_valid = "2";
            
            if (gel_valid !== "") {
                if(confirm("Yakin ingin menduplikasi berkas siswa ini ke Gelombang " + gel_valid + "?")) {
                    window.location.href = "admin.php?duplikat_id=" + id + "&ke_gel=" + gel_valid + "&tab=" + tabAsal + "&gel=<?php echo $gel_aktif; ?>";
                }
            } else {
                alert("Gagal: Input tidak valid. Anda harus mengetik angka 1 atau 2.");
            }
        }
    }

    function pindahGelombang(id, tabAsal) {
        let gel = prompt("Pindahkan siswa ini ke Gelombang mana?\n\nKetik salah satu:\n- 1\n- 2");
        if (gel !== null) {
            gel = gel.trim();
            let gel_valid = "";
            if (gel === "1") gel_valid = "1";
            else if (gel === "2") gel_valid = "2";
            
            if (gel_valid !== "") {
                window.location.href = "konfirmasi.php?id=" + id + "&status=PindahGelombang&ke_gelombang=" + gel_valid + "&tab=" + tabAsal;
            } else {
                alert("Gagal: Input tidak valid. Harus mengetik 1 atau 2.");
            }
        }
    }
    
    function toggleTabel(idPanel, tombol, namaTab) {
        document.querySelectorAll('.panel-tabel').forEach(p => p.classList.remove('terbuka'));
        document.querySelectorAll('.btn-toggle-jurusan').forEach(b => b.classList.remove('aktif'));
        document.getElementById(idPanel).classList.add('terbuka');
        tombol.classList.add('aktif');
        
        const urlParams = new URLSearchParams(window.location.search);
        const currentGel = urlParams.get('gel') || 'Semua';
        const currentSearch = urlParams.get('search') || '';
        
        let newUrl = '?tab=' + namaTab + '&gel=' + currentGel;
        if(currentSearch !== '') {
            newUrl += '&search=' + encodeURIComponent(currentSearch);
        }
        
        window.history.replaceState(null, null, newUrl);
    }

    let currentRotation = 0;
    let currentScale = 1;
    let isDragging = false;
    let startX, startY, scrollLeft, scrollTop;

    function bukaModalBerkas(nama, ijazah, tka, kk, akte, ktpbapak, ktpibu, sptjm, kjp_status, tabkjp) {
        document.getElementById('mdl_nama').innerText = nama;
        
        const berkas = [
            { nama: 'Ijazah', file: ijazah },
            { nama: 'TKA', file: tka },
            { nama: 'KK', file: kk },
            { nama: 'Akte', file: akte },
            { nama: 'KTP Bpk', file: ktpbapak },
            { nama: 'KTP Ibu', file: ktpibu },
            { nama: 'SPTJM', file: sptjm }
        ];

        let html = '<div class="grid-berkas">';
        berkas.forEach(b => {
            if (b.file) {
                html += `<div class="berkas-item success">
                            <span>✅ ${b.nama}</span>
                            <button onclick="tampilkanPreview('${encodeURIComponent(b.file)}')" class="btn-lihat">👁️ Lihat</button>
                         </div>`;
            } else {
                html += `<div class="berkas-item danger"><span>❌ ${b.nama}</span></div>`;
            }
        });
        
        if (kjp_status === 'Ya') {
            if (tabkjp) {
                html += `<div class="berkas-item success"><span>✅ KJP</span><button onclick="tampilkanPreview('${encodeURIComponent(tabkjp)}')" class="btn-lihat">👁️ Lihat</button></div>`;
            } else {
                html += `<div class="berkas-item danger"><span>❌ KJP</span></div>`;
            }
        }
        
        html += '</div>';
        html += '<div id="area-preview" style="margin-top:20px; border-top:2px solid #f1f5f9; padding-top:15px;"></div>';
        
        document.getElementById('mdl_body').innerHTML = html;
        document.getElementById('modalBerkas').style.display = 'flex';
    }

    function tampilkanPreview(encodedFileName) {
        const area = document.getElementById('area-preview');
        const fileName = decodeURIComponent(encodedFileName);
        const fileUrl = 'uploads/' + fileName; 
        
        const ext = fileName.split('.').pop().toLowerCase();
        const isImage = ['jpg', 'jpeg', 'png', 'gif'].includes(ext);
        
        currentRotation = 0; 
        currentScale = 1;

        let btnControls = '';
        let previewContent = '';

        if (isImage) {
            btnControls = `
                <button onclick="zoomGambar(0.2)" style="background:#3b82f6; color:#fff; padding:6px 10px; border-radius:5px; text-decoration:none; font-size:12px; font-weight:bold; border:none; cursor:pointer; margin-right:5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">🔍 Zoom In</button>
                <button onclick="zoomGambar(-0.2)" style="background:#f97316; color:#fff; padding:6px 10px; border-radius:5px; text-decoration:none; font-size:12px; font-weight:bold; border:none; cursor:pointer; margin-right:5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">🔎 Zoom Out</button>
                <button onclick="putarGambar()" style="background:#eab308; color:#fff; padding:6px 10px; border-radius:5px; text-decoration:none; font-size:12px; font-weight:bold; border:none; cursor:pointer; margin-right:8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">🔄 Putar</button>
            `;
            
            previewContent = `
                <p style="font-size:11.5px; color:#64748b; margin-top:0; margin-bottom:8px; font-style:italic;">
                    💡 <b>Tips Cepat:</b> Gunakan <b>Scroll Mouse</b> untuk Zoom, dan <b>Klik Kiri + Geser Mouse</b> untuk melihat detail teks.
                </p>
                <div id="zoom-container" 
                     onwheel="zoomDenganMouse(event)"
                     onmousedown="mulaiDrag(event)"
                     onmousemove="sedangDrag(event)"
                     onmouseup="selesaiDrag()"
                     onmouseleave="selesaiDrag()"
                     style="background:#f1f5f9; border:1px solid #ddd; border-radius:8px; padding:20px; display:flex; justify-content:center; align-items:center; height:450px; overflow:auto; cursor:grab; position:relative;">
                    
                    <img id="img-preview" src="${fileUrl}" style="max-width:100%; max-height:100%; transition: transform 0.15s ease-out; border-radius:4px; transform-origin: center center; pointer-events:none;">
                </div>`;
        } else {
            previewContent = `<iframe src="${fileUrl}" style="width:100%; height:500px; border:1px solid #ddd; border-radius:8px;"></iframe>`;
        }
        
        area.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; flex-wrap:wrap; gap:10px;">
                <p style="font-size:13px; margin:0; font-weight:700; color:#475569;">Pratinjau: <span style="color:#0f172a;">${fileName}</span></p>
                <div style="display:flex; flex-wrap:wrap;">
                    ${btnControls}
                    <a href="${fileUrl}" download="${fileName}" 
                    style="background:#64748b; color:white; padding:6px 12px; border-radius:5px; text-decoration:none; font-size:12px; font-weight:bold; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    💾 Download
                    </a>
                </div>
            </div>
            ${previewContent}
        `;
    }

    function zoomGambar(step) {
        currentScale += step;
        if (currentScale < 0.3) currentScale = 0.3; 
        if (currentScale > 5.0) currentScale = 5.0; 
        terapkanTransformasi();
    }

    function zoomDenganMouse(event) {
        event.preventDefault(); 
        const step = event.deltaY < 0 ? 0.25 : -0.25; 
        zoomGambar(step);
    }

    function putarGambar() {
        currentRotation += 90;
        terapkanTransformasi();
    }

    function terapkanTransformasi() {
        const img = document.getElementById('img-preview');
        const container = document.getElementById('zoom-container');
        if (img) {
            if (currentScale > 1) {
                img.style.maxWidth = 'none';
                img.style.maxHeight = 'none';
                container.style.alignItems = 'flex-start'; 
                container.style.justifyContent = 'flex-start';
            } else {
                img.style.maxWidth = '100%';
                img.style.maxHeight = '100%';
                container.style.alignItems = 'center';
                container.style.justifyContent = 'center';
            }
            img.style.transform = `scale(${currentScale}) rotate(${currentRotation}deg)`;
        }
    }

    function mulaiDrag(e) {
        const container = document.getElementById('zoom-container');
        if(currentScale <= 1) return; 
        
        isDragging = true;
        container.style.cursor = 'grabbing';
        
        startX = e.pageX - container.offsetLeft;
        startY = e.pageY - container.offsetTop;
        scrollLeft = container.scrollLeft;
        scrollTop = container.scrollTop;
    }

    function sedangDrag(e) {
        if (!isDragging) return;
        e.preventDefault();
        
        const container = document.getElementById('zoom-container');
        const x = e.pageX - container.offsetLeft;
        const y = e.pageY - container.offsetTop;
        
        const walkX = (x - startX) * 1.5; 
        const walkY = (y - startY) * 1.5;
        
        container.scrollLeft = scrollLeft - walkX;
        container.scrollTop = scrollTop - walkY;
    }

    function selesaiDrag() {
        isDragging = false;
        const container = document.getElementById('zoom-container');
        if(container) container.style.cursor = 'grab';
    }
    </script>
</body>
</html>