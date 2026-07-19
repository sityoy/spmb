<?php
session_start();
if (!isset($_SESSION['login'])) { 
    header("Location: ../login.php"); 
    exit; 
}
include '../koneksi.php';

// ========================================================
// LOGIKA 1-KLIK TOGGLE DAFTAR ULANG (TANPA PROSES FORM)
// ========================================================
if (isset($_GET['toggle_du'])) {
    $id_du = (int)$_GET['toggle_du'];
    
    // Cek status saat ini
    $q_cek = mysqli_query($conn, "SELECT status_daftar_ulang FROM pendaftar WHERE id = $id_du");
    if ($row_cek = mysqli_fetch_assoc($q_cek)) {
        if ($row_cek['status_daftar_ulang'] == 'Sudah') {
            // BATALKAN DAFTAR ULANG
            mysqli_query($conn, "UPDATE pendaftar SET status_daftar_ulang = 'Belum' WHERE id = $id_du");
            mysqli_query($conn, "DELETE FROM daftar_ulang WHERE id_pendaftar = $id_du");
        } else {
            // SET JADI SUDAH DAFTAR ULANG
            mysqli_query($conn, "UPDATE pendaftar SET status_daftar_ulang = 'Sudah' WHERE id = $id_du");
            
            // Masukkan data dummy yang sesuai tipe data kolom agar tidak kena SQL Error 500
            $cek_du = mysqli_query($conn, "SELECT id_pendaftar FROM daftar_ulang WHERE id_pendaftar = $id_du");
            if (mysqli_num_rows($cek_du) == 0) {
                // Catatan: ukuran_baju dikosongkan ('') agar tidak bentrok dengan ENUM database
                mysqli_query($conn, "INSERT INTO daftar_ulang 
                    (id_pendaftar, tanggal_du, ukuran_baju, bawa_skl_asli, bawa_kk_fc, bawa_akte_fc, bawa_ktp_ortu_fc, bawa_rapot_fc, catatan_du) 
                    VALUES 
                    ($id_du, NOW(), '', 'Tidak', 'Tidak', 'Tidak', 'Tidak', 'Tidak', '')");
            }
        }
    }
    
    // Kembalikan ke halaman index.php (karena sudah di dalam folder daftar_ulang)
    $r_gel = isset($_GET['gel']) ? $_GET['gel'] : 'Semua';
    $r_jur = isset($_GET['jur']) ? $_GET['jur'] : 'Semua';
    $r_cari = isset($_GET['cari']) ? $_GET['cari'] : '';
    
    header("Location: index.php?gel=$r_gel&jur=".urlencode($r_jur)."&cari=".urlencode($r_cari));
    exit;
}
// ========================================================

// Tangkap Filter
$gel_aktif = isset($_GET['gel']) ? preg_replace('/[^a-zA-Z0-9]/', '', $_GET['gel']) : 'Semua';
$jur_aktif = isset($_GET['jur']) ? $_GET['jur'] : 'Semua';
$cari = isset($_GET['cari']) ? mysqli_real_escape_string($conn, trim($_GET['cari'])) : '';

// Susun Query SQL
$sql_gel = ($gel_aktif == 'Semua') ? "" : " AND p.gelombang = '$gel_aktif'";
$sql_jur = ($jur_aktif == 'Semua') ? "" : " AND p.pilihan_jurusan = '$jur_aktif'";
$sql_cari = ($cari == '') ? "" : " AND (p.nama_lengkap LIKE '%$cari%' OR p.nisn LIKE '%$cari%')";

$query = "SELECT p.id, p.no_pendaftaran, p.nama_lengkap, p.nisn, p.pilihan_jurusan, p.gelombang, p.status_daftar_ulang,
                 d.tanggal_du
          FROM pendaftar p
          LEFT JOIN daftar_ulang d ON p.id = d.id_pendaftar
          WHERE p.status_konfirmasi = 'LULUS' $sql_gel $sql_jur $sql_cari
          ORDER BY p.status_daftar_ulang ASC, p.nama_lengkap ASC";
$result = mysqli_query($conn, $query);

$tot_lulus = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM pendaftar p WHERE status_konfirmasi = 'LULUS' $sql_gel $sql_jur $sql_cari"));
$tot_sudah = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM pendaftar p WHERE status_konfirmasi = 'LULUS' AND status_daftar_ulang = 'Sudah' $sql_gel $sql_jur $sql_cari"));
$tot_belum = $tot_lulus - $tot_sudah;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Daftar Ulang - SPMB SMKPB1</title>
    <link rel="icon" type="image/x-icon" href="../logo/logosmkpb.png">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8fafc; color: #1e293b; margin: 0; padding-bottom: 50px;}
        .nav-admin { background: #fff; padding: 15px 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        
        .filter-container { background: #fff; padding: 15px 25px; border-radius: 12px; margin: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        .filter-left { display: flex; gap: 20px; flex-wrap: wrap; }
        .filter-group { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .btn-filter { padding: 8px 15px; border-radius: 8px; font-weight: 600; font-size: 13px; text-decoration: none; color: #64748b; background: #f1f5f9; border: 1px solid #cbd5e1; transition: 0.2s; }
        .btn-filter:hover { background: #e2e8f0; }
        .btn-filter.active { background: #4f46e5; color: #fff; border-color: #4f46e5; }
        
        .search-form { display: flex; gap: 8px; align-items: center; flex-grow: 1; max-width: 400px; justify-content: flex-end; }
        .search-input { padding: 9px 15px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13.5px; width: 100%; outline: none; }
        .search-input:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,0.1); }
        .btn-search { background: #4f46e5; color: white; border: none; padding: 9px 15px; border-radius: 8px; font-weight: bold; cursor: pointer; }
        .btn-reset { background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; padding: 8px 12px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: bold; }

        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 0 20px 20px 20px; }
        .card-box { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .card-box h4 { margin: 0 0 10px 0; font-size: 13px; color: #64748b; text-transform: uppercase; }
        .card-box .angka { font-size: 32px; font-weight: 800; color: #1e293b; }

        .table-responsive { overflow-x: auto; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; margin: 0 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; white-space: nowrap; font-size: 13.5px; }
        th { background: #f1f5f9; padding: 15px; font-weight: 700; color: #475569; text-align: left; border-bottom: 2px solid #e2e8f0; }
        td { padding: 15px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        tbody tr:hover { background-color: #f8fafc; }

        .btn-action { display: inline-block; padding: 7px 12px; font-size: 12px; font-weight: 600; border-radius: 6px; text-decoration: none; border: none; cursor: pointer; color: white; text-align: center; margin-right: 4px; }
        .bg-proses { background: #10b981; }
        .bg-batal { background: #ef4444; }
        .bg-cetak { background: #4f46e5; }
        .bg-kembali { background: #64748b; }
        
        .badge-status { padding: 5px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        .badge-sudah { background: #d1fae5; color: #065f46; border: 1px solid #34d399; }
        .badge-belum { background: #fee2e2; color: #991b1b; border: 1px solid #f87171; }
    </style>
</head>
<body>

    <div class="nav-admin">
        <div>
            <h2 style="margin: 0; font-size: 20px; color:#4f46e5;">📝 Panel Administrasi Daftar Ulang</h2>
            <small style="color:#64748b;">Hanya menampilkan siswa yang berstatus <b>LULUS SELEKSI</b>.</small>
        </div>
        <div>
            <a href="../admin.php" class="btn-action bg-kembali">🔙 Kembali ke Dasbor Utama</a>
        </div>
    </div>

    <div class="filter-container">
        <div class="filter-left">
            <div class="filter-group">
                <span style="font-weight:700; color:#475569; font-size:13.5px;">Gelombang:</span>
                <a href="?gel=Semua&jur=<?php echo urlencode($jur_aktif); ?>&cari=<?php echo urlencode($cari); ?>" class="btn-filter <?php echo ($gel_aktif == 'Semua') ? 'active' : ''; ?>">Semua</a>
                <a href="?gel=1&jur=<?php echo urlencode($jur_aktif); ?>&cari=<?php echo urlencode($cari); ?>" class="btn-filter <?php echo ($gel_aktif == '1') ? 'active' : ''; ?>">Gel 1</a>
                <a href="?gel=2&jur=<?php echo urlencode($jur_aktif); ?>&cari=<?php echo urlencode($cari); ?>" class="btn-filter <?php echo ($gel_aktif == '2') ? 'active' : ''; ?>">Gel 2</a>
            </div>
            <div class="filter-group">
                <span style="font-weight:700; color:#475569; font-size:13.5px;">Jurusan:</span>
                <a href="?gel=<?php echo $gel_aktif; ?>&jur=Semua&cari=<?php echo urlencode($cari); ?>" class="btn-filter <?php echo ($jur_aktif == 'Semua') ? 'active' : ''; ?>">Semua</a>
                <a href="?gel=<?php echo $gel_aktif; ?>&jur=Akuntansi+dan+Keuangan+Lembaga&cari=<?php echo urlencode($cari); ?>" class="btn-filter <?php echo ($jur_aktif == 'Akuntansi dan Keuangan Lembaga') ? 'active' : ''; ?>">AKL</a>
                <a href="?gel=<?php echo $gel_aktif; ?>&jur=Manajemen+Perkantoran+dan+Layanan+Bisnis&cari=<?php echo urlencode($cari); ?>" class="btn-filter <?php echo ($jur_aktif == 'Manajemen Perkantoran dan Layanan Bisnis') ? 'active' : ''; ?>">MPLB</a>
            </div>
        </div>

        <form class="search-form" method="GET" action="">
            <input type="hidden" name="gel" value="<?php echo htmlspecialchars($gel_aktif); ?>">
            <input type="hidden" name="jur" value="<?php echo htmlspecialchars($jur_aktif); ?>">
            <input type="text" name="cari" class="search-input" placeholder="🔍 Cari Nama atau NISN..." value="<?php echo htmlspecialchars($cari); ?>">
            <button type="submit" class="btn-search">Cari</button>
            <?php if($cari != ''): ?>
                <a href="?gel=<?php echo urlencode($gel_aktif); ?>&jur=<?php echo urlencode($jur_aktif); ?>" class="btn-reset">✖ Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="summary-grid">
        <div class="card-box" style="border-left: 5px solid #3b82f6;">
            <h4>Total Siswa Lulus</h4>
            <div class="angka"><?php echo $tot_lulus; ?> <span style="font-size:14px; font-weight:normal; color:#64748b;">Orang</span></div>
        </div>
        <div class="card-box" style="border-left: 5px solid #10b981; background: #f0fdf4;">
            <h4 style="color: #047857;">Sudah Daftar Ulang</h4>
            <div class="angka" style="color: #047857;"><?php echo $tot_sudah; ?> <span style="font-size:14px; font-weight:normal;">Orang</span></div>
        </div>
        <div class="card-box" style="border-left: 5px solid #ef4444; background: #fef2f2;">
            <h4 style="color: #b91c1c;">Belum Daftar Ulang</h4>
            <div class="angka" style="color: #b91c1c;"><?php echo $tot_belum; ?> <span style="font-size:14px; font-weight:normal;">Orang</span></div>
        </div>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width: 3%;">No</th>
                    <th style="width: 25%;">Identitas Siswa</th>
                    <th style="width: 20%;">Jurusan & Gelombang</th>
                    <th style="width: 15%;">Status DU</th>
                    <th style="width: 17%;">Waktu Pendaftaran Ulang</th>
                    <th style="width: 20%;">Aksi Cepat</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1; 
                if (mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) { 
                        $status_du = $row['status_daftar_ulang'];
                        $jur_singkat = ($row['pilihan_jurusan'] == 'Akuntansi dan Keuangan Lembaga') ? 'AKL' : 'MPLB';
                ?>
                <tr>
                    <td style="text-align: center; font-weight: bold;"><?php echo $no++; ?></td>
                    <td>
                        <b style="font-size:14.5px; display:block; color:#0f172a;"><?php echo htmlspecialchars($row['nama_lengkap']); ?></b>
                        <span style="font-size:12px; color:#64748b;">NISN: <?php echo $row['nisn']; ?></span><br>
                        <span style="font-size:12px; color:#64748b;">No: <?php echo $row['no_pendaftaran']; ?></span>
                    </td>
                    <td>
                        <b><?php echo $jur_singkat; ?></b><br>
                        <span style="font-size:12px; color:#f59e0b; font-weight:bold;">Gelombang <?php echo $row['gelombang']; ?></span>
                    </td>
                    <td>
                        <?php if($status_du == 'Sudah'): ?>
                            <span class="badge-status badge-sudah">✅ SUDAH D.U</span>
                        <?php else: ?>
                            <span class="badge-status badge-belum">❌ BELUM</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($status_du == 'Sudah'): ?>
                            <div style="background:#f8fafc; padding:8px; border-radius:6px; border:1px solid #e2e8f0;">
                                <b style="color:#4f46e5; font-size: 13px;"><?php echo date('d/m/Y - H:i', strtotime($row['tanggal_du'])); ?></b><br>
                                <span style="font-size:11px; color:#64748b;">Disetujui Admin</span>
                            </div>
                        <?php else: ?>
                            <span style="font-size:11.5px; color:#94a3b8; font-style:italic;">Belum terkonfirmasi...</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($status_du == 'Belum'): ?>
                            <!-- TOMBOL 1 KLIK SET DAFTAR ULANG -->
                            <a href="?toggle_du=<?php echo $row['id']; ?>&gel=<?php echo $gel_aktif; ?>&jur=<?php echo urlencode($jur_aktif); ?>&cari=<?php echo urlencode($cari); ?>" class="btn-action bg-proses" onclick="return confirm('Tandai siswa ini SUDAH Daftar Ulang?')">✅ Set Daftar Ulang</a>
                        <?php else: ?>
                            <!-- TOMBOL 1 KLIK BATALKAN -->
                            <a href="?toggle_du=<?php echo $row['id']; ?>&gel=<?php echo $gel_aktif; ?>&jur=<?php echo urlencode($jur_aktif); ?>&cari=<?php echo urlencode($cari); ?>" class="btn-action bg-batal" onclick="return confirm('Batalkan status Daftar Ulang siswa ini?')">❌ Batal</a>
                            
                            <!-- <a href="cetak_bukti_du.php?id=<?php echo $row['id']; ?>" target="_blank" class="btn-action bg-cetak">🖨️ Cetak Bukti</a> -->
                        <?php endif; ?>
                    </td>
                </tr>
                <?php 
                    } 
                } else {
                    echo "<tr><td colspan='6' style='text-align:center; padding:30px; font-weight:bold; color:#64748b;'>Data pendaftar tidak ditemukan pada filter/pencarian ini.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>