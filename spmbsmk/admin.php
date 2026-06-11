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
// 1. Mencegah akses langsung ke file ini oleh user yang tidak punya izin di sesi
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// 2. Mencegah XSS (Cross-Site Scripting) pada parameter URL
$_GET['tab'] = isset($_GET['tab']) ? preg_replace('/[^a-zA-Z0-9]/', '', $_GET['tab']) : 'akl';
$_GET['gel'] = isset($_GET['gel']) ? preg_replace('/[^a-zA-Z0-9]/', '', $_GET['gel']) : 'Semua';
// --- END ANTI-HACK ---

include 'koneksi.php';

$tab_aktif = isset($_GET['tab']) ? $_GET['tab'] : 'akl';

// MENGAMBIL FILTER GELOMBANG DARI URL (SEKARANG PAKAI ANGKA 1 ATAU 2)
$gel_aktif = isset($_GET['gel']) ? $_GET['gel'] : 'Semua';
$sql_gel_filter = ($gel_aktif == 'Semua') ? "" : " AND gelombang = '$gel_aktif'";

// Label untuk tampilan UI agar tetap enak dibaca ("Semua Gelombang", "Gelombang 1", "Gelombang 2")
$label_gelombang = ($gel_aktif == 'Semua') ? "Semua Gelombang" : "Gelombang " . $gel_aktif;

// FUNGSI MENGHITUNG KUOTA DINAMIS PER GELOMBANG
function hitungKuota($jurusan, $status, $gel, $conn) {
    $filter = ($gel == 'Semua') ? "" : " AND gelombang = '$gel'";
    $q = mysqli_query($conn, "SELECT COUNT(*) as total FROM pendaftar WHERE pilihan_jurusan = '$jurusan' AND status_konfirmasi = '$status' $filter");
    return mysqli_fetch_assoc($q)['total'];
}

// Menghitung statistik berdasarkan filter gelombang yang aktif
$tot_akl_all = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pendaftar WHERE pilihan_jurusan = 'Akuntansi dan Keuangan Lembaga' $sql_gel_filter"))['total'];
$akl_utama    = hitungKuota('Akuntansi dan Keuangan Lembaga', 'Jadi', $gel_aktif, $conn);

$tot_mplb_all = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pendaftar WHERE pilihan_jurusan = 'Manajemen Perkantoran dan Layanan Bisnis' $sql_gel_filter"))['total'];
$mplb_utama    = hitungKuota('Manajemen Perkantoran dan Layanan Bisnis', 'Jadi', $gel_aktif, $conn);

$tot_all = $tot_akl_all + $tot_mplb_all;

// LOGIKA URUTAN BERDASARKAN NILAI AKHIR (SUDAH DIPERBAIKI MENGGUNAKAN nilai_akhir_sql)
$order_logic = "ORDER BY CASE status_konfirmasi 
                    WHEN 'Jadi' THEN 1 
                    WHEN 'Belum' THEN 2 
                    WHEN 'Tidak Jadi' THEN 3 
                END ASC, nilai_akhir_sql DESC, tanggal_daftar ASC";

$rumus_nilai = "(((((nilai_skl * 0.7) + (nilai_tka * 0.3)) / 2) + nilai_test) / 2)";

$query_akl = "SELECT *, $rumus_nilai as nilai_akhir_sql FROM pendaftar WHERE pilihan_jurusan = 'Akuntansi dan Keuangan Lembaga' $sql_gel_filter $order_logic";
$result_akl = mysqli_query($conn, $query_akl);

$query_mplb = "SELECT *, $rumus_nilai as nilai_akhir_sql FROM pendaftar WHERE pilihan_jurusan = 'Manajemen Perkantoran dan Layanan Bisnis' $sql_gel_filter $order_logic";
$result_mplb = mysqli_query($conn, $query_mplb);
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
        
        /* Area Filter Gelombang */
        .filter-gelombang { background: #fff; padding: 15px 25px; border-radius: 12px; margin: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .btn-filter { padding: 8px 18px; border-radius: 8px; font-weight: 600; font-size: 13.5px; text-decoration: none; color: #64748b; background: #f1f5f9; border: 1px solid #cbd5e1; transition: all 0.2s; }
        .btn-filter:hover { background: #e2e8f0; }
        .btn-filter.active { background: #4f46e5; color: #fff; border-color: #4f46e5; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2); }

        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 0 20px 20px 20px; }
        .card-box { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .card-box h4 { margin: 0 0 15px 0; font-size: 14px; color: #475569; font-weight: 700; text-transform: uppercase; }
        
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
        .bg-view { background: #4f46e5; } .bg-edit { background: #0284c7; } .bg-success-btn { background: #10b981; } .bg-danger-btn { background: #ef4444; } .bg-reset-btn { background: #64748b; } .bg-move-btn { background: #f59e0b; } .bg-print-bukti { background: #8b5cf6; } .bg-offline { background: #334155; }
        
        .status-badge { padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 4px; text-transform: uppercase; }
        .badge-locked-utama { background: #d1fae5; color: #065f46; border: 1px solid #34d399; }
        .badge-forced-cadangan { background: #fee2e2; color: #991b1b; border: 1px solid #f87171; }
        .badge-utama { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
        
        .small-text { font-size: 11px; color: #64748b; display: block; line-height: 1.4; }
        
        /* Box Nilai Diperlebar dan Diperjelas */
        .nilai-box { background: #f8fafc; padding: 10px 14px; border-radius: 8px; border: 1px solid #e2e8f0; display: inline-block; min-width: 220px;}
        .rincian-bobot { font-size: 10.5px; color: #64748b; margin-bottom: 6px; border-bottom: 1px dashed #cbd5e1; padding-bottom: 6px; line-height: 1.6; }
        
        /* Modal Style */
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
        <div style="display:flex; gap:10px;">
            <a href="formulir_offline.php" target="_blank" class="btn-action bg-offline">🖨️ Cetak Form Offline</a>
            <a href="logout.php" class="btn-action bg-danger-btn" onclick="return confirm('Keluar sistem?')">Logout</a>
        </div>
    </div>

    <div class="filter-gelombang">
        <span style="font-weight:700; color:#475569; font-size:14px;">🎛️ Filter Tampilan:</span>
        <a href="?tab=<?php echo $tab_aktif; ?>&gel=Semua" class="btn-filter <?php echo ($gel_aktif == 'Semua') ? 'active' : ''; ?>">Semua Gelombang</a>
        <a href="?tab=<?php echo $tab_aktif; ?>&gel=1" class="btn-filter <?php echo ($gel_aktif == '1') ? 'active' : ''; ?>">Hanya Gelombang 1</a>
        <a href="?tab=<?php echo $tab_aktif; ?>&gel=2" class="btn-filter <?php echo ($gel_aktif == '2') ? 'active' : ''; ?>">Hanya Gelombang 2</a>
    </div>

    <div class="summary-grid">
        <div class="card-box" style="border-left: 5px solid #4f46e5;">
            <h4>Total Pendaftar (<?php echo $label_gelombang; ?>)</h4>
            <div style="font-size: 32px; font-weight: 800; color:#1e293b;"><?php echo $tot_all; ?> <span style="font-size:14px; color:#64748b;">Siswa</span></div>
        </div>
        <div class="card-box" style="border-left: 5px solid #10b981;">
            <h4>Lulus AKL (<?php echo $label_gelombang; ?>)</h4>
            <div style="font-size: 26px; font-weight: 800; color:#065f46;"><?php echo $akl_utama; ?> <span style="font-size:14px; color:#64748b;">Siswa</span></div>
        </div>
        <div class="card-box" style="border-left: 5px solid #0284c7;">
            <h4>Lulus MPLB (<?php echo $label_gelombang; ?>)</h4>
            <div style="font-size: 26px; font-weight: 800; color:#0369a1;"><?php echo $mplb_utama; ?> <span style="font-size:14px; color:#64748b;">Siswa</span></div>
        </div>
    </div>

    <button class="btn-toggle-jurusan <?php echo ($tab_aktif == 'akl') ? 'aktif' : ''; ?>" onclick="toggleTabel('panel_akl', this, 'akl')" style="border-left: 6px solid #10b981;">
        <h3>📁 Akuntansi dan Keuangan Lembaga (AKL)</h3>
        <div><span class="badge-count" style="background: #d1fae5; color: #065f46;">Lulus: <?php echo $akl_utama; ?></span></div>
    </button>

    <div id="panel_akl" class="panel-tabel <?php echo ($tab_aktif == 'akl') ? 'terbuka' : ''; ?>">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Identitas & Gelombang</th>
                        <th>Riwayat Penyakit</th>
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
                            } else {
                                $zb = "<span class='status-badge badge-utama'>Menunggu ($rank)</span>";
                            }

                            // PERHITUNGAN UNTUK TAMPILAN ADMIN (Selaras dengan bukti cetak)
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
                        <td><b style="color:#dc2626; font-size:11px;"><?php echo htmlspecialchars($row['riwayat_penyakit'], ENT_QUOTES, 'UTF-8'); ?></b></td>
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
                                    <a href="edit.php?id=<?php echo $row['id']; ?>&tab=akl" class="btn-action bg-edit">Isi Nilai Test</a>
                                    <a href="konfirmasi.php?id=<?php echo $row['id']; ?>&status=Jadi&tab=akl" class="btn-action bg-success-btn" onclick="return confirm('Kunci kelulusan untuk siswa ini?')">Lulus</a>
                                    <a href="konfirmasi.php?id=<?php echo $row['id']; ?>&status=Pindah&tab=mplb" class="btn-action bg-move-btn" onclick="return confirm('Pindahkan siswa ini ke jurusan MPLB?')">Lempar MPLB</a>
                                    <a href="konfirmasi.php?id=<?php echo $row['id']; ?>&status=Tidak Jadi&tab=akl" class="btn-action bg-danger-btn" onclick="return confirm('Batalkan pendaftar ini?')">Tidak Jadi</a>
                                <?php else: ?>
                                    <a href="konfirmasi.php?id=<?php echo $row['id']; ?>&status=Reset&tab=akl" class="btn-action bg-reset-btn" onclick="return confirm('Kembalikan status siswa ke menunggu?')">Reset Status</a>
                                    <a href="konfirmasi.php?id=<?php echo $row['id']; ?>&status=Pindah&tab=mplb" class="btn-action bg-move-btn" onclick="return confirm('Pindahkan siswa ini ke jurusan MPLB?')">Lempar MPLB</a>
                                    <a href="hapus.php?id=<?php echo $row['id']; ?>&tab=akl" class="btn-action bg-danger-btn" onclick="return confirm('Hapus permanen dari database?')">Hapus</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php $rank++; }} else { echo "<tr><td colspan='7' style='text-align:center; padding:30px;'>Tidak ada data pada gelombang yang dipilih.</td></tr>"; } ?>
                </tbody>
            </table>
        </div>
    </div>

    <button class="btn-toggle-jurusan <?php echo ($tab_aktif == 'mplb') ? 'aktif' : ''; ?>" onclick="toggleTabel('panel_mplb', this, 'mplb')" style="border-left: 6px solid #0284c7;">
        <h3>📁 Manajemen Perkantoran dan Layanan Bisnis (MPLB)</h3>
        <div><span class="badge-count" style="background: #e0f2fe; color: #0369a1;">Lulus: <?php echo $mplb_utama; ?></span></div>
    </button>

    <div id="panel_mplb" class="panel-tabel <?php echo ($tab_aktif == 'mplb') ? 'terbuka' : ''; ?>">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Identitas & Gelombang</th>
                        <th>Riwayat Penyakit</th>
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
                            } else {
                                $zb = "<span class='status-badge badge-utama'>Menunggu ($rank)</span>";
                            }

                            // PERHITUNGAN UNTUK TAMPILAN ADMIN
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
                        <td><b style="color:#dc2626; font-size:11px;"><?php echo htmlspecialchars($row['riwayat_penyakit'], ENT_QUOTES, 'UTF-8'); ?></b></td>
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
                                    <a href="edit.php?id=<?php echo $row['id']; ?>&tab=mplb" class="btn-action bg-edit">Isi Nilai Test</a>
                                    <a href="konfirmasi.php?id=<?php echo $row['id']; ?>&status=Jadi&tab=mplb" class="btn-action bg-success-btn" onclick="return confirm('Kunci kelulusan untuk siswa ini?')">Lulus</a>
                                    <a href="konfirmasi.php?id=<?php echo $row['id']; ?>&status=Pindah&tab=akl" class="btn-action bg-move-btn" onclick="return confirm('Pindahkan siswa ini ke jurusan AKL?')">Lempar AKL</a>
                                    <a href="konfirmasi.php?id=<?php echo $row['id']; ?>&status=Tidak Jadi&tab=mplb" class="btn-action bg-danger-btn" onclick="return confirm('Batalkan pendaftar ini?')">Tidak Jadi</a>
                                <?php else: ?>
                                    <a href="konfirmasi.php?id=<?php echo $row['id']; ?>&status=Reset&tab=mplb" class="btn-action bg-reset-btn" onclick="return confirm('Kembalikan status siswa ke menunggu?')">Reset Status</a>
                                    <a href="konfirmasi.php?id=<?php echo $row['id']; ?>&status=Pindah&tab=akl" class="btn-action bg-move-btn" onclick="return confirm('Pindahkan siswa ini ke jurusan AKL?')">Lempar AKL</a>
                                    <a href="hapus.php?id=<?php echo $row['id']; ?>&tab=mplb" class="btn-action bg-danger-btn" onclick="return confirm('Hapus permanen dari database?')">Hapus</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php $rank++; }} else { echo "<tr><td colspan='7' style='text-align:center; padding:30px;'>Tidak ada data pada gelombang yang dipilih.</td></tr>"; } ?>
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
    // FUNGSI TOGGLE JURUSAN & MENYIMPAN STATUS FILTER GELOMBANG
    function toggleTabel(idPanel, tombol, namaTab) {
        document.querySelectorAll('.panel-tabel').forEach(p => p.classList.remove('terbuka'));
        document.querySelectorAll('.btn-toggle-jurusan').forEach(b => b.classList.remove('aktif'));
        document.getElementById(idPanel).classList.add('terbuka');
        tombol.classList.add('aktif');
        
        const urlParams = new URLSearchParams(window.location.search);
        const currentGel = urlParams.get('gel') || 'Semua';
        window.history.replaceState(null, null, '?tab=' + namaTab + '&gel=' + currentGel);
    }

    // Modal berkas
    function bukaModalBerkas(nama, ijazah, tka, kk, akte, ktpbapak, ktpibu, sptjm, kjp_status, tabkjp) {
        document.getElementById('mdl_nama').innerText = nama;
        let html = '<div class="grid-berkas">';
        html += tplFile('Ijazah', ijazah); html += tplFile('TKA', tka); html += tplFile('KK', kk); html += tplFile('Akte', akte);
        html += tplFile('KTP Bpk', ktpbapak); html += tplFile('KTP Ibu', ktpibu); html += tplFile('SPTJM', sptjm);
        html += (kjp_status === 'Ya') ? tplFile('KJP', tabkjp) : `<div class="berkas-item" style="background:#f8fafc; border-color:#e2e8f0;"><span>ℹ️ Bukan Pemilik KJP</span></div>`;
        html += '</div>';
        document.getElementById('mdl_body').innerHTML = html;
        document.getElementById('modalBerkas').style.display = 'flex';
    }

    // PENGAMANAN LINK BERKAS VIA SCRIPT (VIEW_FILE.PHP)
    function tplFile(lbl, file) {
        return (file) ? `<div class="berkas-item success"><span>✅ ${lbl}</span><a href="view_file.php?file=${encodeURIComponent(file)}" target="_blank" class="btn-lihat">👁️ Lihat File (Aman)</a></div>` 
                      : `<div class="berkas-item danger"><span>❌ ${lbl}</span></div>`;
    }
    </script>
</body>
</html>