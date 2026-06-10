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
if ($gelombang == 1 && $today < '2026-06-10') {
    $is_locked = true;
    $tanggal_buka = "6 Juli 2026";
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
    // 1. Ambil data Live Board AKL per gelombang terpilih (Persis dengan admin.php)
    $query_live_akl = "SELECT *, 
                       ((((nilai_skl * 0.7) + (nilai_tka * 0.3)) + nilai_test) / 2) as nilai_akhir 
                       FROM pendaftar 
                       WHERE pilihan_jurusan = 'Akuntansi dan Keuangan Lembaga' AND gelombang = '$gelombang'
                       $order_logic";
    $result_live_akl = mysqli_query($conn, $query_live_akl);

    // 2. Ambil data Live Board MPLB per gelombang terpilih (Persis dengan admin.php)
    $query_live_mplb = "SELECT *, 
                        ((((nilai_skl * 0.7) + (nilai_tka * 0.3)) + nilai_test) / 2) as nilai_akhir 
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Board Peringkat Hasil Seleksi SPMB</title>
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
        .table-card { overflow: visible !important; background: white; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.02); overflow: hidden; }
        .dropdown-content {
            display: none; 
            position: absolute; 
            right: 0; 
            background: #ffffff; 
            border: 1px solid #cbd5e1; 
            padding: 15px; 
            z-index: 9999 !important; 
            width: 220px; 
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15), 0 8px 10px -6px rgba(0,0,0,0.1); 
            font-size: 12px; 
            border-radius: 8px; 
            text-align: left;
            color: #334155;
            cursor: default;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

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
        
        table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
        th { background: #f8fafc; color: #475569; font-weight: 600; padding: 10px; border-bottom: 2px solid #e2e8f0; }
        td { padding: 10px 8px; border-bottom: 1px solid #f1f5f9; text-align: center; }
        .text-left { text-align: left; }
        
        /* Badges status live board */
        .badge-status-live { display: inline-block; padding: 2px 6px; font-size: 9px; font-weight: bold; border-radius: 4px; margin-left: 5px; text-transform: uppercase; vertical-align: middle; }
        .live-utama { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .live-cadangan { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .live-drop { background: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; text-decoration: line-through; }
        
        .rank-utama { background-color: #f8fafc; }
        .rank-cadangan { background-color: #ffffff; }
        .text-muted-drop { color: #9ca3af !important; text-decoration: line-through; }
        
        .btn-back { display: block; text-align: center; width: max-content; margin: 25px auto 0 auto; color: #4f46e5; text-decoration: none; font-weight: 600; font-size: 14px; }
        
        /* Kartu Kunci Pengumuman */
        .lock-card { background: white; border-radius: 12px; border: 1px solid #fca5a5; padding: 35px 20px; text-align: center; max-width: 480px; margin: 30px auto; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.04); }
        .lock-icon { font-size: 40px; margin-bottom: 10px; }
        .lock-title { font-size: 16px; font-weight: bold; color: #991b1b; margin-bottom: 6px; }
        .lock-desc { font-size: 13px; color: #64748b; line-height: 1.5; }

        @media(max-width: 768px) {
            .board-layout { grid-template-columns: 1fr; }
        }
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
        <p style="text-align:center; font-size:12px; color:#64748b; margin-bottom: 15px; padding: 0 10px;">
            Sistem memproses peringkat real-time berdasarkan Nilai Akhir tertinggi. Kuota Utama Terbatas: <b><?php echo $quota; ?> Siswa / Jurusan</b>.
        </p>

        <div class="board-layout">
            <div class="table-card">
                <div class="table-title" style="background:#4f46e5;">AKUNTANSI & KEUANGAN LEMBAGA (AKL)</div>
                <table>
                    <thead>
                        <tr>
                            <th style="width:35px;">No</th>
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
            // LOGIKA BADGE STATUS
            if ($row['status_konfirmasi'] == 'Jadi') {
                $cl_row = 'rank-utama';
                $badge = "<span class='badge-status-live live-utama'>✓ FIX UTAMA</span>";
            } elseif ($row['status_konfirmasi'] == 'Tidak Jadi') {
                $cl_row = 'rank-cadangan text-muted-drop';
                $badge = "<span class='badge-status-live live-drop'>BATAL</span>";
            } else {
                $cl_row = ($r <= $quota) ? 'rank-utama' : 'rank-cadangan';
                $badge = ($r <= $quota) ? "<span class='badge-status-live live-utama'>UTAMA</span>" : "<span class='badge-status-live live-cadangan'>CADANGAN</span>";
            }
            
            // Perhitungan Rincian
            $hasil_berkas = (($row['nilai_skl'] * 0.7) + ($row['nilai_tka'] * 0.3)) / 2;
    ?>
        <tr class="<?php echo $cl_row; ?>">
            <td><b><?php echo $r++; ?></b></td>
            <td class="text-left"><b><?php echo htmlspecialchars($row['nama_lengkap']); ?></b><?php echo $badge; ?></td>
            <td class="text-left" style="font-size:11px;"><?php echo htmlspecialchars($row['asal_sekolah']); ?></td>
            <td>
    <div style="position:relative; display:inline-block;">
        <button class="btn-nilai-dropdown" onclick="toggleInfoNilai(this)">
            <?php echo number_format($row['nilai_akhir'], 2); ?> <span>▼</span>
        </button>
        
        <div class="dropdown-content">
            <div style="font-weight:800; color:#4f46e5; margin-bottom:10px; text-align:center; border-bottom:2px solid #f1f5f9; padding-bottom:6px; letter-spacing:0.5px;">
                RINCIAN PENILAIAN
            </div>
            
            <div class="info-row">
                <span>Sidanira (70%)</span> 
                <span><?php echo number_format($row['nilai_skl'] * 0.7, 2); ?></span>
            </div>
            <div class="info-row">
                <span>TKA (30%)</span> 
                <span><?php echo number_format($row['nilai_tka'] * 0.3, 2); ?></span>
            </div>
            
            <hr style="margin:8px 0; border:0; border-top:1px dashed #cbd5e1;">
            
            <div class="info-row bold">
                <span>Hasil Berkas / 2</span> 
                <span><?php echo number_format($hasil_berkas, 2); ?></span>
            </div>
            <div class="info-row">
                <span>Nilai Panitia / 2</span> 
                <span><?php echo number_format($row['nilai_test'] / 2, 2); ?></span>
            </div>
            
            <hr style="margin:8px 0; border:0; border-top:2px solid #e2e8f0;">
            
            <div class="info-row bold" style="color:#059669; font-size:14px;">
                <span>NILAI AKHIR</span> 
                <span><?php echo number_format($row['nilai_akhir'], 2); ?></span>
            </div>
        </div>
    </div>
</td>
        </tr>
    <?php } } else { echo "<tr><td colspan='4'>Belum ada data.</td></tr>"; } ?>
</tbody>
</table>
</div>

            <div class="table-card">
<div class="table-title" style="background:#0284c7;">MANAJEMEN PERKANTORAN & LAYANAN BISNIS (MPLB)</div>
<table>
<thead>
    <tr>
        <th style="width:35px;">No</th>
        <th class="text-left">Nama</th>
        <th class="text-left">Asal</th>
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
                $badge = "<span class='badge-status-live live-utama'>✓ FIX UTAMA</span>";
            } elseif ($row['status_konfirmasi'] == 'Tidak Jadi') {
                $cl_row = 'rank-cadangan text-muted-drop';
                $badge = "<span class='badge-status-live live-drop'>BATAL</span>";
            } else {
                $cl_row = ($r <= $quota) ? 'rank-utama' : 'rank-cadangan';
                $badge = ($r <= $quota) ? "<span class='badge-status-live live-utama'>UTAMA</span>" : "<span class='badge-status-live live-cadangan'>CADANGAN</span>";
            }
            $hasil_berkas = (($row['nilai_skl'] * 0.7) + ($row['nilai_tka'] * 0.3)) / 2;
    ?>
        <tr class="<?php echo $cl_row; ?>">
            <td><b><?php echo $r++; ?></b></td>
            <td class="text-left"><b><?php echo htmlspecialchars($row['nama_lengkap']); ?></b><?php echo $badge; ?></td>
            <td class="text-left" style="font-size:11px;"><?php echo htmlspecialchars($row['asal_sekolah']); ?></td>
            <td>
    <div style="position:relative; display:inline-block;">
        <button class="btn-nilai-dropdown" onclick="toggleInfoNilai(this)">
            <?php echo number_format($row['nilai_akhir'], 2); ?> <span>▼</span>
        </button>
        
        <div class="dropdown-content">
            <div style="font-weight:800; color:#4f46e5; margin-bottom:10px; text-align:center; border-bottom:2px solid #f1f5f9; padding-bottom:6px; letter-spacing:0.5px;">
                RINCIAN PENILAIAN
            </div>
            
            <div class="info-row">
                <span>Sidanira (70%)</span> 
                <span><?php echo number_format($row['nilai_skl'] * 0.7, 2); ?></span>
            </div>
            <div class="info-row">
                <span>TKA (30%)</span> 
                <span><?php echo number_format($row['nilai_tka'] * 0.3, 2); ?></span>
            </div>
            
            <hr style="margin:8px 0; border:0; border-top:1px dashed #cbd5e1;">
            
            <div class="info-row bold">
                <span>Hasil Berkas / 2</span> 
                <span><?php echo number_format($hasil_berkas, 2); ?></span>
            </div>
            <div class="info-row">
                <span>Nilai Panitia / 2</span> 
                <span><?php echo number_format($row['nilai_test'] / 2, 2); ?></span>
            </div>
            
            <hr style="margin:8px 0; border:0; border-top:2px solid #e2e8f0;">
            
            <div class="info-row bold" style="color:#059669; font-size:14px;">
                <span>NILAI AKHIR</span> 
                <span><?php echo number_format($row['nilai_akhir'], 2); ?></span>
            </div>
        </div>
    </div>
</td>
        </tr>
    <?php } } else { echo "<tr><td colspan='4'>Belum ada data.</td></tr>"; } ?>
</tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <a href="index.php" class="btn-back">← Kembali ke Portal Utama</a>
</div>
<script>
    function toggleInfoNilai(btn) {
    let dropdown = btn.nextElementSibling;
    dropdown.style.display = (dropdown.style.display === "block") ? "none" : "block";
}

// Menutup dropdown jika klik di luar
window.onclick = function(event) {
    if (!event.target.matches('button')) {
        let dropdowns = document.getElementsByClassName("dropdown-content");
        for (let i = 0; i < dropdowns.length; i++) {
            dropdowns[i].style.display = "none";
        }
    }
}
</script>
</body>
</html>