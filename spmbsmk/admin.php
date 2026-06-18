<?php
// ==========================================
// SECURITY LAYER: SECURE SESSION START
// ==========================================
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_use_only_cookies', 1);
    session_start();
}

// --- TAMBAHAN ANTI-HACK ---
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$_GET['tab'] = isset($_GET['tab']) ? preg_replace('/[^a-zA-Z0-9]/', '', $_GET['tab']) : 'akl';
$_GET['gel'] = isset($_GET['gel']) ? preg_replace('/[^a-zA-Z0-9]/', '', $_GET['gel']) : 'Semua';
// --- END ANTI-HACK ---

include 'koneksi.php';

$tab_aktif = isset($_GET['tab']) ? $_GET['tab'] : 'akl';
$gel_aktif = isset($_GET['gel']) ? $_GET['gel'] : 'Semua';

// Tangkap keyword pencarian
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$sql_gel_filter = ($gel_aktif == 'Semua') ? "" : " AND gelombang = '$gel_aktif'";
$label_gelombang = ($gel_aktif == 'Semua') ? "Semua Gelombang" : "Gelombang " . $gel_aktif;

// Filter Pencarian (Nama, No Daftar, atau NISN)
$sql_search_filter = ($search != '') ? " AND (nama_lengkap LIKE '%$search%' OR no_pendaftaran LIKE '%$search%' OR nisn LIKE '%$search%')" : "";

function hitungKuota($jurusan, $status, $gel, $conn) {
    $filter = ($gel == 'Semua') ? "" : " AND gelombang = '$gel'";
    $q = mysqli_query($conn, "SELECT COUNT(*) as total FROM pendaftar WHERE pilihan_jurusan = '$jurusan' AND status_konfirmasi = '$status' $filter");
    return mysqli_fetch_assoc($q)['total'];
}

// Menghitung statistik (Statistik tetap berdasarkan gelombang, mengabaikan filter pencarian agar angka total tetap akurat)
$tot_akl_all = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pendaftar WHERE pilihan_jurusan = 'Akuntansi dan Keuangan Lembaga' $sql_gel_filter"))['total'];
$akl_utama    = hitungKuota('Akuntansi dan Keuangan Lembaga', 'Jadi', $gel_aktif, $conn);

$tot_mplb_all = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pendaftar WHERE pilihan_jurusan = 'Manajemen Perkantoran dan Layanan Bisnis' $sql_gel_filter"))['total'];
$mplb_utama    = hitungKuota('Manajemen Perkantoran dan Layanan Bisnis', 'Jadi', $gel_aktif, $conn);

$tot_all = $tot_akl_all + $tot_mplb_all;

$order_logic = "ORDER BY CASE status_konfirmasi 
                    WHEN 'Jadi' THEN 1 
                    WHEN 'Belum' THEN 2 
                    WHEN 'Tidak Jadi' THEN 3 
                END ASC, nilai_akhir_sql DESC, tanggal_daftar ASC";

$rumus_nilai = "(((((nilai_skl * 0.7) + (nilai_tka * 0.3)) / 2) + nilai_test) / 2)";

// Query tabel digabungkan dengan Filter Gelombang & Filter Pencarian
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
        
        /* Modifikasi container filter agar mendukung form pencarian di sebelah kanan */
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

        .status-badge { padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 4px; text-transform: uppercase; }
        .badge-locked-utama { background: #d1fae5; color: #065f46; border: 1px solid #34d399; }
        .badge-forced-cadangan { background: #fee2e2; color: #991b1b; border: 1px solid #f87171; }
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
            <a href="broadcast_wa.php" class="btn-action" style="background: #25D366; color: white;">📢 Broadcast WA</a>
            <a href="cetak_kolektif.php" target="_blank" class="btn-action bg-kolektif">📑 Cetak Bukti Kolektif</a>
            <a href="formulir_offline.php" target="_blank" class="btn-action bg-offline">🖨️ Cetak Form Offline</a>
            <a href="logout.php" class="btn-action bg-danger-btn" onclick="return confirm('Keluar sistem?')">Logout</a>
        </div>
    </div>

    <div class="filter-container">
        <div class="filter-gelombang">
            <span style="font-weight:700; color:#475569; font-size:14px;">🎛️ Filter Tampilan:</span>
            <a href="?tab=<?php echo $tab_aktif; ?>&gel=Semua&search=<?php echo urlencode($search); ?>" class="btn-filter <?php echo ($gel_aktif == 'Semua') ? 'active' : ''; ?>">Semua Gelombang</a>
            <a href="?tab=<?php echo $tab_aktif; ?>&gel=1&search=<?php echo urlencode($search); ?>" class="btn-filter <?php echo ($gel_aktif == '1') ? 'active' : ''; ?>">Hanya Gelombang 1</a>
            <a href="?tab=<?php echo $tab_aktif; ?>&gel=2&search=<?php echo urlencode($search); ?>" class="btn-filter <?php echo ($gel_aktif == '2') ? 'active' : ''; ?>">Hanya Gelombang 2</a>
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
            <div class="stat-badge-small" style="background: #d1fae5; color: #065f46; border: 1px solid #34d399;">Lulus: <?php echo $akl_utama; ?> Siswa</div>
        </div>
        <div class="card-box" style="border-left: 5px solid #0284c7;">
            <h4>Statistik MPLB (<?php echo $label_gelombang; ?>)</h4>
            <div style="font-size: 26px; font-weight: 800; color:#1e293b;"><?php echo $tot_mplb_all; ?> <span style="font-size:14px; color:#64748b;">Mendaftar</span></div>
            <div class="stat-badge-small" style="background: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc;">Lulus: <?php echo $mplb_utama; ?> Siswa</div>
        </div>
    </div>

    <?php if($search != ''): ?>
        <div style="margin: 0 20px 15px 20px; color: #4f46e5; font-weight:bold; font-size:14px;">
            🔍 Menampilkan hasil pencarian untuk: "<?php echo htmlspecialchars($search); ?>"
        </div>
    <?php endif; ?>

    <button class="btn-toggle-jurusan <?php echo ($tab_aktif == 'akl') ? 'aktif' : ''; ?>" onclick="toggleTabel('panel_akl', this, 'akl')" style="border-left: 6px solid #10b981;">
        <h3>📁 Akuntansi dan Keuangan Lembaga (AKL)</h3>
        <div><span class="badge-count" style="background: #d1fae5; color: #065f46;">Lulus: <?php echo $akl_utama; ?> / <?php echo $tot_akl_all; ?></span></div>
    </button>

    <div id="panel_akl" class="panel-tabel <?php echo ($tab_aktif == 'akl') ? 'terbuka' : ''; ?>">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Identitas & Gelombang</th>
                        <th>No HP (WA)</th>
                        <th>Status</th>
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
                            if ($row['status_konfirmasi'] == 'Jadi') {
                                $zb = "<span class='status-badge badge-locked-utama'>🔒 Lulus</span>";
                            } elseif ($row['status_konfirmasi'] == 'Tidak Jadi') {
                                $zb = "<span class='status-badge badge-forced-cadangan'>❌ Tidak Jadi</span>";
                                if (!empty($row['alasan_pembatalan'])) {
                                    $zb .= "<span style='display:block; margin-top:6px; color:#ef4444; font-size:11.5px; font-weight:600; font-style:italic;'>💬 " . htmlspecialchars($row['alasan_pembatalan'], ENT_QUOTES, 'UTF-8') . "</span>";                                }
                            } else {
                                $zb = "<span class='status-badge badge-utama'>Menunggu ($rank)</span>";
                            }

                            // Format No HP untuk WA
                            $no_hp = preg_replace('/[^0-9]/', '', $row['no_whatsapp']);
                            if (substr($no_hp, 0, 1) == '0') {
                                $no_wa = '62' . substr($no_hp, 1);
                            } else {
                                $no_wa = $no_hp;
                            }
                            
                            $pesan_wa = urlencode("Halo Bapak/Ibu Calon Wali Murid dari *" . $row['nama_lengkap'] . "* (NISN: " . $row['nisn'] . ").\n\nBerikut kami sampaikan Bukti Pendaftaran dan Pengumuman SPMB SMK Permata Bunda I (Sekolah Swasta Gratis).\n\nSilakan klik tautan ini untuk mengunduh bukti Anda:\n" . $domain_web . "/bukti.php?no_pendaftaran=" . $row['no_pendaftaran']);

                            $asli_skl = (float)$row['nilai_skl'];
                            $asli_tka = (float)$row['nilai_tka'];
                            $bobot_skl = $asli_skl * 0.70;
                            $bobot_tka = $asli_tka * 0.30;
                            
                            $nilai_berkas_asli = ($asli_skl + $asli_tka) / 2;
                            $nilai_berkas_bobot = ($bobot_skl + $bobot_tka) / 2;
                            
                            $nilai_test = (float)$row['nilai_test'];
                            $nilai_akhir_total = ($nilai_berkas_bobot + $nilai_test) / 2;
                    ?>
                    <tr>
                        <td style="text-align:center; font-size:16px;"><b><?php echo $rank; ?></b></td>
                        <td>
                            <div class="identitas-box">
                                <span style="font-weight: 800; font-size: 14px;"><?php echo htmlspecialchars($row['nama_lengkap'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="small-text">NISN: <?php echo htmlspecialchars($row['nisn'], ENT_QUOTES, 'UTF-8');?></span>
                                <span class="small-text">Jalur: <b style="color:#d97706;">Gelombang <?php echo htmlspecialchars($row['gelombang'], ENT_QUOTES, 'UTF-8'); ?></b></span>
                            </div>
                        </td>
                        <td>
                            <span style="font-weight:600; display:block; margin-bottom:5px; font-size:13px;"><?php echo htmlspecialchars($row['no_whatsapp'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <a href="https://wa.me/<?php echo $no_wa; ?>?text=<?php echo $pesan_wa; ?>" target="_blank" class="btn-action bg-wa">💬 Kirim WA Bukti</a>
                        </td>
                        <td><?php echo $zb; ?></td>
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
                                <span class="small-text" style="color:#0284c7;">Berkas Bobot (/2): <b><?php echo number_format($nilai_berkas_bobot, 2); ?></b></span>
                                
                                <span class="small-text" style="color:#f59e0b; margin-top:4px;">
                                    Uji Test: <b><?php echo ($nilai_test > 0) ? number_format($nilai_test, 2) : '0.00 (Belum Ujian)'; ?></b>
                                </span>
                                
                                <div style="margin-top:5px; border-top:1px solid #cbd5e1; padding-top:5px; font-weight: 800; font-size: 14px; color:#0f172a;">
                                    RATA AKHIR: <span style="color:#10b981;"><?php echo number_format($nilai_akhir_total, 2); ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="bukti.php?no_pendaftaran=<?php echo urlencode($row['no_pendaftaran']); ?>" target="_blank" class="btn-action bg-print-bukti">Bukti</a>
                                <?php if($row['status_konfirmasi'] == 'Belum'): ?>
                                    <a href="edit.php?id=<?php echo $row['id']; ?>&tab=akl" class="btn-action bg-edit">Edit Siswa/Nilai</a>
                                    <a href="konfirmasi.php?id=<?php echo $row['id']; ?>&status=Jadi&tab=akl" class="btn-action bg-success-btn" onclick="return confirm('Kunci kelulusan untuk siswa ini?')">Lulus</a>
                                    <a href="konfirmasi.php?id=<?php echo $row['id']; ?>&status=Pindah&tab=mplb" class="btn-action bg-move-btn" onclick="return confirm('Pindahkan siswa ini ke jurusan MPLB?')">Lempar MPLB</a>
                                    <a href="#" class="btn-action bg-danger-btn" onclick="let alasan = prompt('Masukkan alasan pembatalan untuk siswa ini:'); if(alasan === null) return false; if(alasan.trim() === '') { alert('Alasan wajib diisi untuk membatalkan!'); return false; } window.location.href='konfirmasi.php?id=<?php echo $row['id']; ?>&status=Tidak Jadi&tab=akl&alasan=' + encodeURIComponent(alasan); return false;">Tidak Jadi</a>
                                <?php else: ?>
                                    <a href="edit.php?id=<?php echo $row['id']; ?>&tab=akl" class="btn-action bg-edit">Edit Data/Nilai</a>
                                    <a href="konfirmasi.php?id=<?php echo $row['id']; ?>&status=Reset&tab=akl" class="btn-action bg-reset-btn" onclick="return confirm('Kembalikan status siswa ke menunggu?')">Reset Status</a>
                                    <a href="konfirmasi.php?id=<?php echo $row['id']; ?>&status=Pindah&tab=mplb" class="btn-action bg-move-btn" onclick="return confirm('Pindahkan siswa ini ke jurusan MPLB?')">Lempar MPLB</a>
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

    <button class="btn-toggle-jurusan <?php echo ($tab_aktif == 'mplb') ? 'aktif' : ''; ?>" onclick="toggleTabel('panel_mplb', this, 'mplb')" style="border-left: 6px solid #0284c7;">
        <h3>📁 Manajemen Perkantoran dan Layanan Bisnis (MPLB)</h3>
        <div><span class="badge-count" style="background: #e0f2fe; color: #0369a1;">Lulus: <?php echo $mplb_utama; ?> / <?php echo $tot_mplb_all; ?></span></div>
    </button>

    <div id="panel_mplb" class="panel-tabel <?php echo ($tab_aktif == 'mplb') ? 'terbuka' : ''; ?>">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Identitas & Gelombang</th>
                        <th>No HP (WA)</th>
                        <th>Status</th>
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
                            if ($row['status_konfirmasi'] == 'Jadi') {
                                $zb = "<span class='status-badge badge-locked-utama'>🔒 Lulus</span>";
                            } elseif ($row['status_konfirmasi'] == 'Tidak Jadi') {
                                $zb = "<span class='status-badge badge-forced-cadangan'>❌ Tidak Jadi</span>";
                                if (!empty($row['alasan_pembatalan'])) {
                                    $zb .= "<span style='display:block; margin-top:6px; color:#ef4444; font-size:11.5px; font-weight:600; font-style:italic;'>💬 " . htmlspecialchars($row['alasan_pembatalan'], ENT_QUOTES, 'UTF-8') . "</span>";                                }
                            } else {
                                $zb = "<span class='status-badge badge-utama'>Menunggu ($rank)</span>";
                            }

                            $no_hp = preg_replace('/[^0-9]/', '', $row['no_whatsapp']);
                            if (substr($no_hp, 0, 1) == '0') {
                                $no_wa = '62' . substr($no_hp, 1);
                            } else {
                                $no_wa = $no_hp;
                            }
                            
                            $pesan_wa = urlencode("Halo Bapak/Ibu Calon Wali Murid dari *" . $row['nama_lengkap'] . "* (NISN: " . $row['nisn'] . ").\n\nBerikut kami sampaikan Bukti Pendaftaran dan Pengumuman SPMB SMK Permata Bunda I (Sekolah Swasta Gratis).\n\nSilakan klik tautan ini untuk mengunduh bukti Anda:\n" . $domain_web . "/bukti.php?no_pendaftaran=" . $row['no_pendaftaran']);

                            $asli_skl = (float)$row['nilai_skl'];
                            $asli_tka = (float)$row['nilai_tka'];
                            $bobot_skl = $asli_skl * 0.70;
                            $bobot_tka = $asli_tka * 0.30;
                            
                            $nilai_berkas_asli = ($asli_skl + $asli_tka) / 2;
                            $nilai_berkas_bobot = ($bobot_skl + $bobot_tka) / 2;
                            
                            $nilai_test = (float)$row['nilai_test'];
                            $nilai_akhir_total = ($nilai_berkas_bobot + $nilai_test) / 2;
                    ?>
                    <tr>
                        <td style="text-align:center; font-size:16px;"><b><?php echo $rank; ?></b></td>
                        <td>
                            <div class="identitas-box">
                                <span style="font-weight: 800; font-size: 14px;"><?php echo htmlspecialchars($row['nama_lengkap'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="small-text">NISN: <?php echo htmlspecialchars($row['nisn'], ENT_QUOTES, 'UTF-8');?></span>
                                <span class="small-text">Jalur: <b style="color:#d97706;">Gelombang <?php echo htmlspecialchars($row['gelombang'], ENT_QUOTES, 'UTF-8'); ?></b></span>
                            </div>
                        </td>
                        <td>
                            <span style="font-weight:600; display:block; margin-bottom:5px; font-size:13px;"><?php echo htmlspecialchars($row['no_whatsapp'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <a href="https://wa.me/<?php echo $no_wa; ?>?text=<?php echo $pesan_wa; ?>" target="_blank" class="btn-action bg-wa">💬 Kirim WA Bukti</a>
                        </td>
                        <td><?php echo $zb; ?></td>
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
                                <span class="small-text" style="color:#0284c7;">Berkas Bobot (/2): <b><?php echo number_format($nilai_berkas_bobot, 2); ?></b></span>
                                
                                <span class="small-text" style="color:#f59e0b; margin-top:4px;">
                                    Uji Test: <b><?php echo ($nilai_test > 0) ? number_format($nilai_test, 2) : '0.00 (Belum Ujian)'; ?></b>
                                </span>
                                
                                <div style="margin-top:5px; border-top:1px solid #cbd5e1; padding-top:5px; font-weight: 800; font-size: 14px; color:#0f172a;">
                                    RATA AKHIR: <span style="color:#10b981;"><?php echo number_format($nilai_akhir_total, 2); ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="bukti.php?no_pendaftaran=<?php echo urlencode($row['no_pendaftaran']); ?>" target="_blank" class="btn-action bg-print-bukti">Bukti</a>
                                <?php if($row['status_konfirmasi'] == 'Belum'): ?>
                                    <a href="edit.php?id=<?php echo $row['id']; ?>&tab=mplb" class="btn-action bg-edit">Edit Siswa/Nilai</a>
                                    <a href="konfirmasi.php?id=<?php echo $row['id']; ?>&status=Jadi&tab=mplb" class="btn-action bg-success-btn" onclick="return confirm('Kunci kelulusan untuk siswa ini?')">Lulus</a>
                                    <a href="konfirmasi.php?id=<?php echo $row['id']; ?>&status=Pindah&tab=akl" class="btn-action bg-move-btn" onclick="return confirm('Pindahkan siswa ini ke jurusan AKL?')">Lempar AKL</a>
                                    <a href="#" class="btn-action bg-danger-btn" onclick="let alasan = prompt('Masukkan alasan pembatalan untuk siswa ini:'); if(alasan === null) return false; if(alasan.trim() === '') { alert('Alasan wajib diisi untuk membatalkan!'); return false; } window.location.href='konfirmasi.php?id=<?php echo $row['id']; ?>&status=Tidak Jadi&tab=mplb&alasan=' + encodeURIComponent(alasan); return false;">Tidak Jadi</a>
                                <?php else: ?>
                                    <a href="edit.php?id=<?php echo $row['id']; ?>&tab=mplb" class="btn-action bg-edit">Edit Data/Nilai</a>
                                    <a href="konfirmasi.php?id=<?php echo $row['id']; ?>&status=Reset&tab=mplb" class="btn-action bg-reset-btn" onclick="return confirm('Kembalikan status siswa ke menunggu?')">Reset Status</a>
                                    <a href="konfirmasi.php?id=<?php echo $row['id']; ?>&status=Pindah&tab=akl" class="btn-action bg-move-btn" onclick="return confirm('Pindahkan siswa ini ke jurusan AKL?')">Lempar AKL</a>
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

    <script>
    function toggleTabel(idPanel, tombol, namaTab) {
        document.querySelectorAll('.panel-tabel').forEach(p => p.classList.remove('terbuka'));
        document.querySelectorAll('.btn-toggle-jurusan').forEach(b => b.classList.remove('aktif'));
        document.getElementById(idPanel).classList.add('terbuka');
        tombol.classList.add('aktif');
        
        // Simpan state Tab, Gelombang, dan Search ke URL tanpa reload
        const urlParams = new URLSearchParams(window.location.search);
        const currentGel = urlParams.get('gel') || 'Semua';
        const currentSearch = urlParams.get('search') || '';
        
        let newUrl = '?tab=' + namaTab + '&gel=' + currentGel;
        if(currentSearch !== '') {
            newUrl += '&search=' + encodeURIComponent(currentSearch);
        }
        
        window.history.replaceState(null, null, newUrl);
    }

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
            html += (b.file) ? 
                `<div class="berkas-item success">
                    <span>✅ ${b.nama}</span>
                    <button onclick="tampilkanPreview('view_file.php?file=${encodeURIComponent(b.file)}')" class="btn-lihat">👁️ Lihat</button>
                 </div>` : 
                `<div class="berkas-item danger"><span>❌ ${b.nama}</span></div>`;
        });
        
        if (kjp_status === 'Ya') {
            html += `<div class="berkas-item success"><span>✅ KJP</span><button onclick="tampilkanPreview('view_file.php?file=${encodeURIComponent(tabkjp)}')" class="btn-lihat">👁️ Lihat</button></div>`;
        }
        
        html += '</div>';
        html += '<div id="area-preview" style="margin-top:20px; border-top:2px solid #f1f5f9; padding-top:15px;"></div>';
        
        document.getElementById('mdl_body').innerHTML = html;
        document.getElementById('modalBerkas').style.display = 'flex';
    }

    function tampilkanPreview(url) {
        const area = document.getElementById('area-preview');
        const fileName = decodeURIComponent(url.split('file=')[1]);
        
        area.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">
                <p style="font-size:12px; margin:0;">Pratinjau:</p>
                <a href="${url}&download=true" target="_blank" 
                style="background:#64748b; color:white; padding:5px 10px; border-radius:5px; text-decoration:none; font-size:11px;">
                💾 Download Manual
                </a>
            </div>
            <iframe src="${url}" style="width:100%; height:500px; border:1px solid #ddd; border-radius:8px;"></iframe>
        `;
    }
    </script>
</body>
</html>