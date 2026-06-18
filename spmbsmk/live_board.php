<?php
date_default_timezone_set('Asia/Jakarta');
include 'koneksi.php';

// Menentukan parameter Gelombang yang dipilih (default Gelombang 1)
$gelombang = isset($_GET['gelombang']) ? (int)$_GET['gelombang'] : 1;
if ($gelombang !== 1 && $gelombang !== 2) {
    $gelombang = 1;
}

$today = date('Y-m-d');
$is_locked = false;
$tanggal_buka = "";

// Validasi Tanggal Pembukaan Live Board per Gelombang
if ($gelombang == 1 && $today < '2026-07-01') {
    $is_locked = true;
    $tanggal_buka = "1 Juli 2026";
} elseif ($gelombang == 2 && $today < '2026-07-10') {
    $is_locked = true;
    $tanggal_buka = "10 Juli 2026";
}

// Set Kuota Maksimal Utama Berdasarkan Gelombang
$quota = ($gelombang == 1) ? 25 : 11;

// Logika Pengurutan Peringkat Panitia
$order_logic = "ORDER BY CASE status_konfirmasi 
                    WHEN 'Jadi' THEN 1 
                    WHEN 'Belum' THEN 2 
                    WHEN 'Tidak Jadi' THEN 3 
                END ASC, nilai_akhir DESC, tanggal_daftar ASC";

if (!$is_locked) {
    // RUMUS YANG SINKRON DENGAN ADMIN
    $rumus_sql = "(((((nilai_skl * 0.7) + (nilai_tka * 0.3)) / 2) + nilai_test) / 2)";

    $query_live_akl = "SELECT *, $rumus_sql as nilai_akhir 
                       FROM pendaftar 
                       WHERE pilihan_jurusan = 'Akuntansi dan Keuangan Lembaga' AND gelombang = '$gelombang'
                       $order_logic";
    $result_live_akl = mysqli_query($conn, $query_live_akl);

    $query_live_mplb = "SELECT *, $rumus_sql as nilai_akhir 
                        FROM pendaftar 
                        WHERE pilihan_jurusan = 'Manajemen Perkantoran dan Layanan Bisnis' AND gelombang = '$gelombang'
                        $order_logic";
    $result_live_mplb = mysqli_query($conn, $query_live_mplb);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Live Board Peringkat Hasil Seleksi SPMB</title>
    <link rel="icon" type="image/x-icon" href="logo/logosmkpb.png">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f1f5f9; color: #334155; margin: 0; padding: 0; }
        .container { max-width: 1000px; margin: 20px auto; padding: 15px; box-sizing: border-box; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #1e293b; font-size: 20px; text-transform: uppercase; }
        .header p { margin: 5px 0 0 0; color: #64748b; font-size: 13px; }
        
        /* Tab Navigasi Gelombang */
        .tab-container { display: flex; justify-content: center; gap: 10px; margin-bottom: 20px; }
        .tab-btn { padding: 10px 18px; background: white; color: #475569; text-decoration: none; font-weight: bold; border-radius: 8px; font-size: 13px; border: 1px solid #cbd5e1; transition: all 0.2s; }
        .tab-btn.active { background: #4f46e5; color: white; border-color: #4f46e5; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.1); }
        
        /* Layout Papan Peringkat Responsive */
        .board-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .table-card { background: white; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.02); overflow: hidden; }
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; } /* Perbaikan UI/UX HP */
        
        /* --- UI/UX Dropdown Baru --- */
        .dropdown-content {
            visibility: hidden;
            opacity: 0;
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 12px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 16px;
            z-index: 999;
            width: 240px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            font-size: 12.5px;
            border-radius: 12px;
            text-align: left;
            color: #334155;
            cursor: default;
            transform: translateY(-10px);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Segitiga Penunjuk (Arrow Pointer) ke arah tombol */
        .dropdown-content::before {
            content: '';
            position: absolute;
            top: -6px;
            right: 24px;
            width: 12px;
            height: 12px;
            background: #ffffff;
            border-left: 1px solid #e2e8f0;
            border-top: 1px solid #e2e8f0;
            transform: rotate(45deg);
        }

        /* Class animasi saat dropdown aktif */
        .dropdown-content.show {
            visibility: visible;
            opacity: 1;
            transform: translateY(0);
        }

        @media(max-width: 768px) {
            .board-layout { grid-template-columns: 1fr; gap: 20px; }
            .container { padding: 10px; }
            
            /* Ubah tampilan tabel menjadi kartu di HP */
            thead { display: none; } /* Sembunyikan header tabel di HP */
            tr { display: block; background: #fff; margin-bottom: 12px; border-radius: 10px; padding: 15px; border: 1px solid #e2e8f0; position: relative; }
            td { display: flex; justify-content: space-between; padding: 6px 0; border: none; text-align: right; }
            td:nth-child(1) { position: absolute; top: 15px; left: 15px; font-size: 16px; } /* Peringkat */
            td:nth-child(2), td:nth-child(3) { display: none; } /* Sembunyikan ID di HP untuk hemat tempat */
            td:nth-child(4) { display: block; padding-left: 30px; text-align: left; font-size: 15px; } /* Nama */
            td:nth-child(5) { display: block; font-size: 11px; color: #64748b; text-align: left; padding-left: 30px; } /* Asal */
            td:nth-child(6) { border-top: 1px solid #f1f5f9; margin-top: 10px; padding-top: 10px; justify-content: flex-end; }
            
            .dropdown-content { right: 0; width: 260px; }
            .dropdown-content::before { right: 15px; }
        }
        /* ----------------------------- */

        .info-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .info-row.bold { font-weight: 700; color: #0f172a; }

        .btn-nilai-dropdown {
            background: #f8fafc; 
            color: #4f46e5; 
            border: 1px solid #cbd5e1; 
            padding: 5px 10px; 
            border-radius: 6px; 
            font-weight: bold; 
            font-size: 12px; 
            cursor: pointer; 
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-nilai-dropdown:hover { background: #e0e7ff; border-color: #4f46e5; }

        .table-title { color: white; font-weight: bold; padding: 12px; font-size: 13px; text-align: center; letter-spacing: 0.5px; }
        
        table { width: 100%; border-collapse: collapse; font-size: 12.5px; min-width: 380px; }
        th { background: #f8fafc; color: #475569; font-weight: 600; padding: 10px; border-bottom: 2px solid #e2e8f0; }
        td { padding: 10px 8px; border-bottom: 1px solid #f1f5f9; text-align: center; }
        .text-left { text-align: left; }
        
        /* Badges status live board */
        .badge-status-live { display: inline-block; padding: 2px 6px; font-size: 9px; font-weight: bold; border-radius: 4px; margin-top: 4px; text-transform: uppercase; line-height: 1.2; }
        .live-utama { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .live-cadangan { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .live-drop { background: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; text-decoration: line-through; }
        
        .rank-utama { background-color: #f8fafc; }
        .rank-cadangan { background-color: #ffffff; }
        .text-muted-drop { color: #9ca3af !important; text-decoration: line-through; }
        
        .btn-back { display: block; text-align: center; width: max-content; margin: 25px auto 0 auto; color: #4f46e5; text-decoration: none; font-weight: 600; font-size: 14px; padding: 10px 20px; border: 1px solid #e0e7ff; background: white; border-radius: 8px; }
        
        /* Kartu Kunci Pengumuman */
        .lock-card { background: white; border-radius: 12px; border: 1px solid #fca5a5; padding: 35px 20px; text-align: center; max-width: 480px; margin: 30px auto; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.04); }
        .lock-icon { font-size: 40px; margin-bottom: 10px; }
        .lock-title { font-size: 16px; font-weight: bold; color: #991b1b; margin-bottom: 6px; }
        .lock-desc { font-size: 13px; color: #64748b; line-height: 1.5; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>PAPAN PERINGKAT REAL-TIME (LIVE BOARD)</h2>
        <p>SMKS PERMATA BUNDA I JAKARTA</p>
    </div>

    <div class="tab-container">
        <a href="live_board.php?gelombang=1" class="tab-btn <?php echo ($gelombang == 1) ? 'active' : ''; ?>">Gelombang 1</a>
        <a href="live_board.php?gelombang=2" class="tab-btn <?php echo ($gelombang == 2) ? 'active' : ''; ?>">Gelombang 2</a>
    </div>

    <?php if ($is_locked): ?>
        <div class="lock-card">
            <div class="lock-icon">🔒</div>
            <div class="lock-title">Papan Peringkat Belum Diakses</div>
            <div class="lock-desc">
                Live Board peringkat penyeleksian untuk <b>Gelombang <?php echo $gelombang; ?></b> baru akan dibuka secara resmi pada tanggal <b><?php echo $tanggal_buka; ?></b>.
            </div>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 20px;">
            <div style="background: #4f46e5; color: white; padding: 12px 5px; border-radius: 12px; text-align: center;">
                <div style="font-size: 10px; opacity: 0.8; text-transform: uppercase;">AKL</div>
                <div style="font-size: 18px; font-weight: 800;"><?php echo mysqli_num_rows($result_live_akl); ?></div>
            </div>
            <div style="background: #ffffff; color: #1e293b; padding: 12px 5px; border-radius: 12px; text-align: center; border: 2px solid #4f46e5;">
                <div style="font-size: 10px; opacity: 0.8; text-transform: uppercase; font-weight: 700;">TOTAL</div>
                <div style="font-size: 18px; font-weight: 800; color: #4f46e5;"><?php echo mysqli_num_rows($result_live_akl) + mysqli_num_rows($result_live_mplb); ?></div>
            </div>
            <div style="background: #0284c7; color: white; padding: 12px 5px; border-radius: 12px; text-align: center;">
                <div style="font-size: 10px; opacity: 0.8; text-transform: uppercase;">MPLB</div>
                <div style="font-size: 18px; font-weight: 800;"><?php echo mysqli_num_rows($result_live_mplb); ?></div>
            </div>
        </div>
        <p style="text-align:center; font-size:12px; color:#64748b; margin-bottom: 15px; padding: 0 10px;">
            Sistem memproses peringkat real-time berdasarkan Nilai Akhir tertinggi. Kuota Utama Terbatas: <b><?php echo $quota; ?> Siswa / Jurusan</b>.
        </p>

        <div class="board-layout">
            <div class="table-card">
                <div class="table-title" style="background:#4f46e5;">AKUNTANSI & KEUANGAN LEMBAGA (AKL)</div>
                <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th style="width:35px;">No</th>
                            <th class="text-left">No. Pendaftaran</th>
                            <th class="text-left">NISN</th>
                            <th class="text-left">Nama Pendaftar</th>
                            <th class="text-left">Asal Sekolah</th>
                            <th>Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    $r = 1; 
                    if (mysqli_num_rows($result_live_akl) > 0) { 
                        while ($row = mysqli_fetch_assoc($result_live_akl)) { 
                            if ($row['status_konfirmasi'] == 'Jadi') {
                                $cl_row = 'rank-utama';
                                $badge = "<br><span class='badge-status-live live-utama'>✓ FIX UTAMA</span>";
                            } elseif ($row['status_konfirmasi'] == 'Tidak Jadi') {
                                $cl_row = 'rank-cadangan text-muted-drop';
                                $badge = "<br><span class='badge-status-live live-drop'>BATAL</span>";
                            } else {
                                $cl_row = ($r <= $quota) ? 'rank-utama' : 'rank-cadangan';
                                $badge = ($r <= $quota) ? "<br><span class='badge-status-live live-utama'>UTAMA</span>" : "<br><span class='badge-status-live live-cadangan'>CADANGAN</span>";
                            }
                            
                            $bobot_skl      = $row['nilai_skl'] * 0.7;
                            $bobot_tka      = $row['nilai_tka'] * 0.3;
                            $hasil_berkas   = ($bobot_skl + $bobot_tka) / 2; 
                            $nilai_test     = (float)$row['nilai_test'];    
                            $total_gabungan = $hasil_berkas + $nilai_test;  
                            $nilai_akhir_fix = $total_gabungan / 2;
                            
                            // Sensor NISN untuk privasi
                            $nisn_sensor = substr($row['nisn'], 0, 3) . '****' . substr($row['nisn'], -3);
                    ?>
                        <tr class="<?php echo $cl_row; ?>">
                            <td><b><?php echo $r++; ?></b></td>
                            <td class="text-left"><span style="font-size:11px; font-family:monospace; color:#475569; background:#f1f5f9; padding:2px 6px; border-radius:4px;"><?php echo htmlspecialchars($row['no_pendaftaran']); ?></span></td>
                            <td class="text-left"><span style="font-size:12px; font-weight:700; color:#64748b;"><?php echo $nisn_sensor; ?></span></td>
                            <td class="text-left"><b><?php echo htmlspecialchars($row['nama_lengkap']); ?></b><?php echo $badge; ?></td>
                            <td class="text-left" style="font-size:11px;"><?php echo htmlspecialchars($row['asal_sekolah']); ?></td>
                            <td>
                                <div style="position:relative; display:inline-block;">
                                    <button class="btn-nilai-dropdown" onclick="toggleInfoNilai(this)">
    <span style="color:#1e293b; font-size: 13px;"><?php echo number_format($nilai_akhir_fix, 2); ?></span>
    <span style="margin-left:6px; opacity:0.5;">details</span>
</button>
                                    
                                    <div class="dropdown-content">
                                        <div style="font-weight:800; color:#4f46e5; margin-bottom:10px; text-align:center; border-bottom:2px solid #f1f5f9; padding-bottom:6px;">
                                            RINCIAN PENILAIAN
                                        </div>
                                        <div class="info-row">
                                            <span>Sidanira (70%)</span> 
                                            <span><?php echo number_format($bobot_skl, 2); ?></span>
                                        </div>
                                        <div class="info-row">
                                            <span>TKA (30%)</span> 
                                            <span><?php echo number_format($bobot_tka, 2); ?></span>
                                        </div>
                                        <hr style="margin:6px 0; border:0; border-top:1px dashed #cbd5e1;">
                                        <div class="info-row bold">
                                            <span>Hasil Berkas (bagi 2)</span> 
                                            <span><?php echo number_format($hasil_berkas, 2); ?></span>
                                        </div>
                                        <div class="info-row">
                                            <span>Nilai Uji (Test)</span> 
                                            <span><?php echo number_format($nilai_test, 2); ?></span>
                                        </div>
                                        <hr style="margin:6px 0; border:0; border-top:1px solid #94a3b8;">
                                        <div class="info-row bold">
                                            <span>Total (Berkas+Test)</span> 
                                            <span><?php echo number_format($total_gabungan, 2); ?></span>
                                        </div>
                                        <div class="info-row">
                                            <span>Hasil Akhir (bagi 2)</span> 
                                            <span>/ 2</span>
                                        </div>
                                        <hr style="margin:6px 0; border:0; border-top:2px solid #e2e8f0;">
                                        <div class="info-row bold" style="color:#059669; font-size:14px;">
                                            <span>NILAI AKHIR</span> 
                                            <span><?php echo number_format($nilai_akhir_fix, 2); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php } } else { echo "<tr><td colspan='6'>Belum ada data.</td></tr>"; } ?>
                    </tbody>
                </table>
                </div>
            </div>

            <div class="table-card">
                <div class="table-title" style="background:#0284c7;">MANAJEMEN PERKANTORAN & LAYANAN BISNIS (MPLB)</div>
                <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th style="width:35px;">No</th>
                            <th class="text-left">No. Pendaftaran</th>
                            <th class="text-left">NISN</th>
                            <th class="text-left">Nama Pendaftar</th>
                            <th class="text-left">Asal Sekolah</th>
                            <th>Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    $r = 1; 
                    if (mysqli_num_rows($result_live_mplb) > 0) { 
                        while ($row = mysqli_fetch_assoc($result_live_mplb)) { 
                            if ($row['status_konfirmasi'] == 'Jadi') {
                                $cl_row = 'rank-utama';
                                $badge = "<br><span class='badge-status-live live-utama'>✓ FIX UTAMA</span>";
                            } elseif ($row['status_konfirmasi'] == 'Tidak Jadi') {
                                $cl_row = 'rank-cadangan text-muted-drop';
                                $badge = "<br><span class='badge-status-live live-drop'>BATAL</span>";
                            } else {
                                $cl_row = ($r <= $quota) ? 'rank-utama' : 'rank-cadangan';
                                $badge = ($r <= $quota) ? "<br><span class='badge-status-live live-utama'>UTAMA</span>" : "<br><span class='badge-status-live live-cadangan'>CADANGAN</span>";
                            }
                            
                            $bobot_skl      = $row['nilai_skl'] * 0.7;
                            $bobot_tka      = $row['nilai_tka'] * 0.3;
                            $hasil_berkas   = ($bobot_skl + $bobot_tka) / 2; 
                            $nilai_test     = (float)$row['nilai_test'];    
                            $total_gabungan = $hasil_berkas + $nilai_test;  
                            $nilai_akhir_fix = $total_gabungan / 2;
                            
                            // Sensor NISN untuk privasi
                            $nisn_sensor = substr($row['nisn'], 0, 3) . '****' . substr($row['nisn'], -3);
                    ?>
                        <tr class="<?php echo $cl_row; ?>">
                            <td><b><?php echo $r++; ?></b></td>
                            <td class="text-left"><span style="font-size:11px; font-family:monospace; color:#475569; background:#f1f5f9; padding:2px 6px; border-radius:4px;"><?php echo htmlspecialchars($row['no_pendaftaran']); ?></span></td>
                            <td class="text-left"><span style="font-size:12px; font-weight:700; color:#64748b;"><?php echo $nisn_sensor; ?></span></td>
                            <td class="text-left"><b><?php echo htmlspecialchars($row['nama_lengkap']); ?></b><?php echo $badge; ?></td>
                            <td class="text-left" style="font-size:11px;"><?php echo htmlspecialchars($row['asal_sekolah']); ?></td>
                            <td>
                                <div style="position:relative; display:inline-block;">
                                    <button class="btn-nilai-dropdown" onclick="toggleInfoNilai(this)">
    <span style="color:#1e293b; font-size: 13px;"><?php echo number_format($nilai_akhir_fix, 2); ?></span>
    <span style="margin-left:6px; opacity:0.5;">details</span>
</button>
                                    
                                    <div class="dropdown-content">
                                        <div style="font-weight:800; color:#4f46e5; margin-bottom:10px; text-align:center; border-bottom:2px solid #f1f5f9; padding-bottom:6px;">
                                            RINCIAN PENILAIAN
                                        </div>
                                        <div class="info-row">
                                            <span>Sidanira (70%)</span> 
                                            <span><?php echo number_format($bobot_skl, 2); ?></span>
                                        </div>
                                        <div class="info-row">
                                            <span>TKA (30%)</span> 
                                            <span><?php echo number_format($bobot_tka, 2); ?></span>
                                        </div>
                                        <hr style="margin:6px 0; border:0; border-top:1px dashed #cbd5e1;">
                                        <div class="info-row bold">
                                            <span>Hasil Berkas (bagi 2)</span> 
                                            <span><?php echo number_format($hasil_berkas, 2); ?></span>
                                        </div>
                                        <div class="info-row">
                                            <span>Nilai Uji (Test)</span> 
                                            <span><?php echo number_format($nilai_test, 2); ?></span>
                                        </div>
                                        <hr style="margin:6px 0; border:0; border-top:1px solid #94a3b8;">
                                        <div class="info-row bold">
                                            <span>Total (Berkas+Test)</span> 
                                            <span><?php echo number_format($total_gabungan, 2); ?></span>
                                        </div>
                                        <div class="info-row">
                                            <span>Hasil Akhir (bagi 2)</span> 
                                            <span>/ 2</span>
                                        </div>
                                        <hr style="margin:6px 0; border:0; border-top:2px solid #e2e8f0;">
                                        <div class="info-row bold" style="color:#059669; font-size:14px;">
                                            <span>NILAI AKHIR</span> 
                                            <span><?php echo number_format($nilai_akhir_fix, 2); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php } } else { echo "<tr><td colspan='6'>Belum ada data.</td></tr>"; } ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <a href="index.php" class="btn-back">← Kembali ke Portal Utama</a>
</div>

<script>
// Logic baru agar interaksi mulus, terutama di HP
// Logic UI/UX Dropdown Animasi Smooth
function toggleInfoNilai(btn) {
    let dropdownTarget = btn.nextElementSibling;
    
    // Tutup semua dropdown lain yang sedang terbuka
    let allDropdowns = document.querySelectorAll('.dropdown-content');
    allDropdowns.forEach(function(drop) {
        if (drop !== dropdownTarget) {
            drop.classList.remove('show');
        }
    });

    // Toggle dropdown yang diklik (muncul / sembunyi)
    dropdownTarget.classList.toggle('show');
}

// Menutup menu jika user klik area kosong di layar
document.addEventListener('click', function(event) {
    let isClickInsideBtn = event.target.closest('.btn-nilai-dropdown');
    let isClickInsideMenu = event.target.closest('.dropdown-content');

    if (!isClickInsideBtn && !isClickInsideMenu) {
        let allDropdowns = document.querySelectorAll('.dropdown-content');
        allDropdowns.forEach(function(drop) {
            drop.classList.remove('show');
        });
    }
});
</script>
</body>
</html>