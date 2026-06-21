<?php
date_default_timezone_set('Asia/Jakarta');
include 'koneksi.php';

// Menentukan parameter Gelombang yang dipilih (default Gelombang 1)
$gelombang = isset($_GET['gelombang']) ? (int)$_GET['gelombang'] : 1;
if ($gelombang !== 1 && $gelombang !== 2) {
    $gelombang = 1;
}

// Ambil Pengaturan Jadwal dari Database
$pengaturan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pengaturan WHERE id = 1"));
$sekarang = date('Y-m-d H:i:s');
$is_locked = false;
$tanggal_buka = "";

// Validasi Tanggal Pembukaan Live Board dari database
$bulan_indo = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
if ($gelombang == 1 && $sekarang < $pengaturan['buka_gel_1']) {
    $is_locked = true;
    $ts = strtotime($pengaturan['buka_gel_1']);
    $tanggal_buka = date('d', $ts) . ' ' . $bulan_indo[(int)date('m', $ts)] . ' ' . date('Y H:i', $ts) . " WIB";
} elseif ($gelombang == 2 && $sekarang < $pengaturan['buka_gel_2']) {
    $is_locked = true;
    $ts = strtotime($pengaturan['buka_gel_2']);
    $tanggal_buka = date('d', $ts) . ' ' . $bulan_indo[(int)date('m', $ts)] . ' ' . date('Y H:i', $ts) . " WIB";
}

// Set Kuota Maksimal Utama Berdasarkan Gelombang
$quota = ($gelombang == 1) ? 25 : 11;

// Logika Pengurutan Peringkat Panitia
$order_logic = "ORDER BY CASE status_konfirmasi 
                    WHEN 'LULUS' THEN 1 
                    WHEN 'Menunggu' THEN 2 
                    WHEN 'Tidak Jadi' THEN 3 
                END ASC, nilai_akhir DESC, tanggal_daftar ASC";

if (!$is_locked) {
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
    <meta http-equiv="refresh" content="60">
    <title>Live Board Peringkat Hasil Seleksi SPMB</title>
    <link rel="icon" type="image/x-icon" href="logo/logosmkpb.png">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8fafc; color: #1e293b; margin: 0; padding-bottom: 50px; }
        
        .header { background: #fff; padding: 25px 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); text-align: center; border-bottom: 1px solid #e2e8f0; margin-bottom: 25px; }
        .header h2 { margin: 0; color: #1e293b; font-size: 22px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px; }
        .header p { margin: 8px 0 0 0; color: #64748b; font-size: 14px; font-weight: 500; }
        .live-indicator { display: inline-block; background: #fee2e2; color: #ef4444; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; margin-top: 10px; border: 1px solid #fca5a5; animation: blink 2s infinite; }
        
        @keyframes blink { 0% { opacity: 1; } 50% { opacity: 0.6; } 100% { opacity: 1; } }

        .container { max-width: 1200px; margin: 0 auto; padding: 0 15px; box-sizing: border-box; }
        
        .tab-container { display: flex; justify-content: center; gap: 12px; margin-bottom: 25px; flex-wrap: wrap; }
        .tab-btn { padding: 12px 25px; background: white; color: #64748b; text-decoration: none; font-weight: 700; border-radius: 8px; font-size: 14px; border: 1px solid #cbd5e1; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .tab-btn:hover { background: #f1f5f9; }
        .tab-btn.active { background: #4f46e5; color: white; border-color: #4f46e5; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2); }
        
        /* Summary Grid Ala Admin */
        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card-box { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .card-box h4 { margin: 0 0 10px 0; font-size: 13px; color: #64748b; font-weight: 700; text-transform: uppercase; }
        .card-number { font-size: 32px; font-weight: 800; color: #1e293b; }
        
        /* Tabel Full Width */
        .section-title { font-size: 18px; font-weight: 800; color: #1e293b; margin: 0 0 15px 0; display: flex; align-items: center; gap: 10px; }
        .table-card { background: white; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 40px; }
        .table-responsive { overflow-x: auto; }
        
        table { width: 100%; border-collapse: collapse; font-size: 14px; white-space: nowrap; }
        th { background: #f8fafc; color: #475569; font-weight: 700; padding: 15px; border-bottom: 2px solid #e2e8f0; text-align: left; }
        td { padding: 15px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        tr:hover { background-color: #f8fafc; }
        
        /* Status Badges */
        .status-badge { display: inline-block; padding: 5px 12px; font-size: 11px; font-weight: 700; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-masuk { background: #d1fae5; color: #065f46; border: 1px solid #34d399; }
        .badge-luar { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
        .badge-batal { background: #fee2e2; color: #991b1b; border: 1px solid #f87171; text-decoration: line-through; }
        
        .row-luar td { background-color: #fafafa; opacity: 0.8; }
        .row-batal td { opacity: 0.5; }
        
        .btn-detail { background: #e0e7ff; color: #4f46e5; border: 1px solid #c7d2fe; padding: 8px 15px; border-radius: 8px; font-weight: 700; font-size: 12.5px; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px; }
        .btn-detail:hover { background: #4f46e5; color: white; }

        .btn-back { display: block; text-align: center; width: max-content; margin: 30px auto 0 auto; color: #4f46e5; text-decoration: none; font-weight: 700; font-size: 15px; padding: 12px 25px; border: 2px solid #e0e7ff; background: white; border-radius: 10px; transition: 0.2s; }
        .btn-back:hover { background: #f8fafc; border-color: #c7d2fe; }
        
        /* Modal Style Admin */
        .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15,23,42,0.7); display: none; align-items: center; justify-content: center; z-index: 9999; padding: 15px; }
        .modal-content { background: #fff; width: 100%; max-width: 450px; border-radius: 16px; padding: 25px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px; color: #475569; }
        .info-row.bold { font-weight: 800; color: #1e293b; }
        
        /* Lock Card */
        .lock-card { background: white; border-radius: 16px; border: 1px solid #fca5a5; padding: 40px 20px; text-align: center; max-width: 500px; margin: 40px auto; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); }
        .lock-icon { font-size: 50px; margin-bottom: 15px; }
        .lock-title { font-size: 18px; font-weight: 800; color: #991b1b; margin-bottom: 8px; }
        .lock-desc { font-size: 14px; color: #64748b; line-height: 1.6; }

        @media(max-width: 768px) {
            .summary-grid { grid-template-columns: 1fr; gap: 15px; }
            thead { display: none; } 
            tr { display: flex; flex-direction: column; background: #fff; margin-bottom: 15px; border-radius: 12px; padding: 15px; border: 1px solid #e2e8f0; position: relative; }
            td { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border: none; text-align: right; }
            td::before { content: attr(data-label); font-weight: 600; color: #64748b; font-size: 12px; text-transform: uppercase; }
            td:nth-child(1) { position: absolute; top: -10px; left: 15px; background: #1e293b; color: white; padding: 4px 10px; border-radius: 8px; font-weight: bold; font-size: 14px; z-index: 10; }
            td:nth-child(1)::before { display: none; }
            td:nth-child(2) { margin-top: 15px; flex-direction: column; align-items: flex-start; text-align: left; }
            td:nth-child(2)::before { display: none; }
            td:nth-child(6) { border-top: 1px dashed #e2e8f0; margin-top: 10px; padding-top: 15px; }
            td:nth-child(6)::before { display: none; }
            .btn-detail { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>Papan Peringkat Real-Time</h2>
        <p>SMKS PERMATA BUNDA I JAKARTA</p>
        <div class="live-indicator">🔴 LIVE SYSTEM</div>
    </div>

<div class="container">
    <div class="tab-container">
        <a href="live_board.php?gelombang=1" class="tab-btn <?php echo ($gelombang == 1) ? 'active' : ''; ?>">Gelombang 1</a>
        <a href="live_board.php?gelombang=2" class="tab-btn <?php echo ($gelombang == 2) ? 'active' : ''; ?>">Gelombang 2</a>
    </div>

    <?php if ($is_locked): ?>
        <div class="lock-card">
            <div class="lock-icon">🔒</div>
            <div class="lock-title">Papan Peringkat Belum Diakses</div>
            <div class="lock-desc">
                Sistem perankingan untuk <b>Gelombang <?php echo $gelombang; ?></b> masih dikunci oleh Panitia.<br>Akan dibuka secara resmi pada <b><?php echo $tanggal_buka; ?></b>.
            </div>
        </div>
    <?php else: ?>
        
        <div class="summary-grid">
            <div class="card-box" style="border-left: 5px solid #4f46e5;">
                <h4>Total Mendaftar Gel. <?php echo $gelombang; ?></h4>
                <div class="card-number"><?php echo mysqli_num_rows($result_live_akl) + mysqli_num_rows($result_live_mplb); ?> <span style="font-size:14px; color:#64748b; font-weight:500;">Siswa</span></div>
            </div>
            <div class="card-box" style="border-left: 5px solid #10b981;">
                <h4>Kompetisi AKL</h4>
                <div class="card-number"><?php echo mysqli_num_rows($result_live_akl); ?> <span style="font-size:14px; color:#64748b; font-weight:500;">Siswa</span></div>
                <div style="font-size:12px; color:#059669; font-weight:bold; margin-top:5px;">Memperebutkan <?php echo $quota; ?> Kuota</div>
            </div>
            <div class="card-box" style="border-left: 5px solid #0284c7;">
                <h4>Kompetisi MPLB</h4>
                <div class="card-number"><?php echo mysqli_num_rows($result_live_mplb); ?> <span style="font-size:14px; color:#64748b; font-weight:500;">Siswa</span></div>
                <div style="font-size:12px; color:#0284c7; font-weight:bold; margin-top:5px;">Memperebutkan <?php echo $quota; ?> Kuota</div>
            </div>
        </div>

        <h3 class="section-title">📁 Akuntansi dan Keuangan Lembaga (AKL)</h3>
        <div class="table-card">
            <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width:50px; text-align:center;">Rank</th>
                        <th>Identitas Siswa</th>
                        <th>Asal Sekolah</th>
                        <th>Nilai Akhir</th>
                        <th>Status Seleksi</th>
                        <th style="text-align:center;">Rincian</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                $r = 1; 
                if (mysqli_num_rows($result_live_akl) > 0) { 
                    while ($row = mysqli_fetch_assoc($result_live_akl)) { 
                        if ($row['status_konfirmasi'] == 'LULUS') {
                            $cl_row = '';
                            $badge = "<span class='status-badge badge-masuk'>✓ MASUK KUOTA</span>";
                        } elseif ($row['status_konfirmasi'] == 'Tidak Jadi') {
                            $cl_row = 'row-batal';
                            $badge = "<span class='status-badge badge-batal'>BATAL MENGUNDURKAN DIRI</span>";
                        } else {
                            $cl_row = ($r <= $quota) ? '' : 'row-luar';
                            $badge = ($r <= $quota) ? "<span class='status-badge badge-masuk'>KUOTA UTAMA</span>" : "<span class='status-badge badge-luar'>DI LUAR KUOTA</span>";
                        }
                        
                        $asli_skl = (float)$row['nilai_skl'];
                        $asli_tka = (float)$row['nilai_tka'];
                        $bobot_skl = $asli_skl * 0.70;
                        $bobot_tka = $asli_tka * 0.30;
                        $nilai_berkas_asli = ($asli_skl + $asli_tka) / 2;
                        $nilai_berkas_bobot = ($bobot_skl + $bobot_tka) / 2;
                        $nilai_test = (float)$row['nilai_test'];
                        $nilai_akhir_fix = ($nilai_berkas_bobot + $nilai_test) / 2;
                        
                        $nisn_sensor = substr($row['nisn'], 0, 3) . '****' . substr($row['nisn'], -3);
                        
                        // Siapkan parameter aman untuk JavaScript
                        $js_nama = addslashes(htmlspecialchars($row['nama_lengkap'], ENT_QUOTES));
                ?>
                    <tr class="<?php echo $cl_row; ?>">
                        <td data-label="Peringkat" style="text-align:center; font-size:18px; font-weight:800; color:#4f46e5;"><?php echo $r++; ?></td>
                        <td data-label="Identitas">
                            <div style="font-weight: 800; color:#1e293b; font-size:15px; margin-bottom:4px;"><?php echo htmlspecialchars($row['nama_lengkap']); ?></div>
                            <div style="font-size:12px; color:#64748b; font-family:monospace; background:#f1f5f9; padding:2px 6px; border-radius:4px; display:inline-block;">NISN: <?php echo $nisn_sensor; ?></div>
                        </td>
                        <td data-label="Asal Sekolah" style="color:#475569;"><?php echo htmlspecialchars($row['asal_sekolah']); ?></td>
                        <td data-label="Nilai Akhir" style="font-size:16px; font-weight:800; color:#10b981;"><?php echo number_format($nilai_akhir_fix, 2); ?></td>
                        <td data-label="Status"><?php echo $badge; ?></td>
                        <td data-label="Rincian" style="text-align:center;">
                            <button class="btn-detail" onclick="bukaModalNilai('<?php echo $js_nama; ?>', '<?php echo number_format($asli_skl,2); ?>', '<?php echo number_format($bobot_skl,2); ?>', '<?php echo number_format($asli_tka,2); ?>', '<?php echo number_format($bobot_tka,2); ?>', '<?php echo number_format($nilai_berkas_bobot,2); ?>', '<?php echo number_format($nilai_test,2); ?>', '<?php echo number_format($nilai_akhir_fix,2); ?>')">
                                📊 Detail Nilai
                            </button>
                        </td>
                    </tr>
                <?php } } else { echo "<tr><td colspan='6' style='text-align:center; padding:30px; color:#64748b;'>Belum ada data pendaftar.</td></tr>"; } ?>
                </tbody>
            </table>
            </div>
        </div>

        <h3 class="section-title">📁 Manajemen Perkantoran dan Layanan Bisnis (MPLB)</h3>
        <div class="table-card">
            <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width:50px; text-align:center;">Rank</th>
                        <th>Identitas Siswa</th>
                        <th>Asal Sekolah</th>
                        <th>Nilai Akhir</th>
                        <th>Status Seleksi</th>
                        <th style="text-align:center;">Rincian</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                $r = 1; 
                if (mysqli_num_rows($result_live_mplb) > 0) { 
                    while ($row = mysqli_fetch_assoc($result_live_mplb)) { 
                        if ($row['status_konfirmasi'] == 'LULUS') {
                            $cl_row = '';
                            $badge = "<span class='status-badge badge-masuk'>✓ MASUK KUOTA</span>";
                        } elseif ($row['status_konfirmasi'] == 'Tidak Jadi') {
                            $cl_row = 'row-batal';
                            $badge = "<span class='status-badge badge-batal'>BATAL MENGUNDURKAN DIRI</span>";
                        } else {
                            $cl_row = ($r <= $quota) ? '' : 'row-luar';
                            $badge = ($r <= $quota) ? "<span class='status-badge badge-masuk'>KUOTA UTAMA</span>" : "<span class='status-badge badge-luar'>DI LUAR KUOTA</span>";
                        }
                        
                        $asli_skl = (float)$row['nilai_skl'];
                        $asli_tka = (float)$row['nilai_tka'];
                        $bobot_skl = $asli_skl * 0.70;
                        $bobot_tka = $asli_tka * 0.30;
                        $nilai_berkas_asli = ($asli_skl + $asli_tka) / 2;
                        $nilai_berkas_bobot = ($bobot_skl + $bobot_tka) / 2;
                        $nilai_test = (float)$row['nilai_test'];
                        $nilai_akhir_fix = ($nilai_berkas_bobot + $nilai_test) / 2;
                        
                        $nisn_sensor = substr($row['nisn'], 0, 3) . '****' . substr($row['nisn'], -3);
                        $js_nama = addslashes(htmlspecialchars($row['nama_lengkap'], ENT_QUOTES));
                ?>
                    <tr class="<?php echo $cl_row; ?>">
                        <td data-label="Peringkat" style="text-align:center; font-size:18px; font-weight:800; color:#0284c7;"><?php echo $r++; ?></td>
                        <td data-label="Identitas">
                            <div style="font-weight: 800; color:#1e293b; font-size:15px; margin-bottom:4px;"><?php echo htmlspecialchars($row['nama_lengkap']); ?></div>
                            <div style="font-size:12px; color:#64748b; font-family:monospace; background:#f1f5f9; padding:2px 6px; border-radius:4px; display:inline-block;">NISN: <?php echo $nisn_sensor; ?></div>
                        </td>
                        <td data-label="Asal Sekolah" style="color:#475569;"><?php echo htmlspecialchars($row['asal_sekolah']); ?></td>
                        <td data-label="Nilai Akhir" style="font-size:16px; font-weight:800; color:#10b981;"><?php echo number_format($nilai_akhir_fix, 2); ?></td>
                        <td data-label="Status"><?php echo $badge; ?></td>
                        <td data-label="Rincian" style="text-align:center;">
                            <button class="btn-detail" onclick="bukaModalNilai('<?php echo $js_nama; ?>', '<?php echo number_format($asli_skl,2); ?>', '<?php echo number_format($bobot_skl,2); ?>', '<?php echo number_format($asli_tka,2); ?>', '<?php echo number_format($bobot_tka,2); ?>', '<?php echo number_format($nilai_berkas_bobot,2); ?>', '<?php echo number_format($nilai_test,2); ?>', '<?php echo number_format($nilai_akhir_fix,2); ?>')">
                                📊 Detail Nilai
                            </button>
                        </td>
                    </tr>
                <?php } } else { echo "<tr><td colspan='6' style='text-align:center; padding:30px; color:#64748b;'>Belum ada data pendaftar.</td></tr>"; } ?>
                </tbody>
            </table>
            </div>
        </div>
    <?php endif; ?>

    <a href="index.php" class="btn-back">← Kembali ke Portal Utama</a>
</div>

<div id="modalNilai" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <div>
                <h3 style="margin:0; font-size:18px; color:#1e293b;">Rincian Penilaian</h3>
                <div id="mdl_nama" style="font-size:13px; color:#4f46e5; font-weight:700; margin-top:5px;"></div>
            </div>
            <button onclick="document.getElementById('modalNilai').style.display='none'" style="border:none; background:#f1f5f9; padding:8px 12px; border-radius:8px; cursor:pointer; font-weight:bold; color:#475569;">Tutup ✕</button>
        </div>
        
        <div style="background:#f8fafc; padding:15px; border-radius:12px; border:1px solid #e2e8f0; margin-bottom:15px;">
            <div class="info-row">
                <span>Nilai Sidanira (Asli)</span>
                <span id="mdl_sdn_asli" style="font-weight:600;"></span>
            </div>
            <div class="info-row" style="color:#0284c7;">
                <span>↳ Bobot Sidanira (70%)</span>
                <span id="mdl_sdn_bobot" style="font-weight:bold;"></span>
            </div>
            <hr style="border:0; border-top:1px dashed #cbd5e1; margin:10px 0;">
            <div class="info-row">
                <span>Nilai TKA (Asli)</span>
                <span id="mdl_tka_asli" style="font-weight:600;"></span>
            </div>
            <div class="info-row" style="color:#0284c7;">
                <span>↳ Bobot TKA (30%)</span>
                <span id="mdl_tka_bobot" style="font-weight:bold;"></span>
            </div>
        </div>

        <div class="info-row bold" style="padding:0 5px;">
            <span>Gabungan Berkas (Bagi 2)</span>
            <span id="mdl_gab_berkas"></span>
        </div>
        <div class="info-row bold" style="padding:0 5px;">
            <span>Nilai Uji (Test)</span>
            <span id="mdl_test"></span>
        </div>
        
        <div style="margin-top:20px; background:#e0f2fe; padding:15px; border-radius:12px; border:1px solid #bae6fd; text-align:center;">
            <div style="font-size:12px; color:#0369a1; font-weight:bold; text-transform:uppercase; margin-bottom:5px;">RATA-RATA AKHIR (Berkas + Test / 2)</div>
            <div id="mdl_akhir" style="font-size:32px; font-weight:800; color:#0284c7;"></div>
        </div>
    </div>
</div>

<script>
// Fungsi Buka Modal Detail Nilai
function bukaModalNilai(nama, sdn_asli, sdn_bobot, tka_asli, tka_bobot, gab_berkas, test, akhir) {
    document.getElementById('mdl_nama').innerText = nama;
    document.getElementById('mdl_sdn_asli').innerText = sdn_asli;
    document.getElementById('mdl_sdn_bobot').innerText = sdn_bobot;
    document.getElementById('mdl_tka_asli').innerText = tka_asli;
    document.getElementById('mdl_tka_bobot').innerText = tka_bobot;
    document.getElementById('mdl_gab_berkas').innerText = gab_berkas;
    document.getElementById('mdl_test').innerText = test;
    document.getElementById('mdl_akhir').innerText = akhir;
    
    document.getElementById('modalNilai').style.display = 'flex';
}

// Menutup modal jika klik di luar area putih (content)
document.getElementById('modalNilai').addEventListener('click', function(e) {
    if (e.target === this) {
        this.style.display = 'none';
    }
});
</script>
</body>