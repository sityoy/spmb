<?php
session_status() === PHP_SESSION_NONE ? session_start() : null;
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['login'])) { 
    header("Location: login.php"); 
    exit; 
}
require_once __DIR__ . '/config/koneksi.php';

$gel = isset($_GET['gel']) ? preg_replace('/[^a-zA-Z0-9]/', '', $_GET['gel']) : 'Semua';
$sql_gel = ($gel == 'Semua') ? "" : "WHERE gelombang = '$gel'";
$label_gel = ($gel == 'Semua') ? 'Semua Gelombang' : (($gel == 'Cadangan') ? 'Cadangan / Antrian' : 'Gelombang ' . $gel);

// Menggunakan rumus matematika langsung di SQL agar bisa di-Sort (ORDER BY) berdasarkan Skor Akhir tertinggi
$query = "SELECT *, 
          ((((nilai_skl * 0.70) + (nilai_tka * 0.30)) / 2) + nilai_test) / 2 AS skor_akhir 
          FROM pendaftar 
          $sql_gel 
          ORDER BY pilihan_jurusan ASC, skor_akhir DESC, nama_lengkap ASC";

$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Nilai & Peringkat - <?php echo $label_gel; ?></title>
    <link rel="icon" type="image/x-icon" href="logo/logosmkpb.png">
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11px; color: #000; background: #fff; padding: 10px; }
        .kop { text-align: center; margin-bottom: 20px; border-bottom: 3px double #000; padding-bottom: 10px; }
        h2, h3 { margin: 4px 0; letter-spacing: 0.5px; }
        h2 { font-size: 16px; font-weight: bold; }
        h3 { font-size: 14px; font-weight: bold; }
        p { margin: 5px 0 0 0; font-size: 11px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 5px 4px; text-align: center; vertical-align: middle; }
        th { background-color: #f8fafc; font-weight: bold; font-size: 11px; }
        
        .text-left { text-align: left; padding-left: 6px; }
        .rank-badge { background: #4f46e5; color: white; padding: 2px 6px; border-radius: 4px; font-weight: bold; display: inline-block; }
        
        .btn-print { margin-bottom: 15px; padding: 10px 20px; background: #4f46e5; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 13px; }
        
        @media print { 
            .no-print { display: none; } 
            body { padding: 0; }
            @page { size: A4 landscape; margin: 1cm; }
            .rank-badge { background: transparent; color: #000; padding: 0; border: none; }
        }
    </style>
</head>
<body>

    <button class="no-print btn-print" onclick="window.print()">🖨️ Cetak Rekap & Peringkat (A4 Landscape)</button>

    <div class="kop">
        <h2>REKAPITULASI PERINGKAT & NILAI CALON PESERTA DIDIK BARU</h2>
        <h3>SMKS PERMATA BUNDA I JAKARTA</h3>
        <p>Filter Jalur: <b><?php echo $label_gel; ?></b> | Tanggal Cetak: 
        <?php 
        $bulan_indo = array(1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
        echo date('d') . ' ' . $bulan_indo[(int)date('m')] . ' ' . date('Y H:i'); 
        ?> WIB</p>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 2%;">No</th>
                <th rowspan="2" style="width: 3%;">Rank</th>
                <th rowspan="2" style="width: 8%;">No. Pend</th>
                <th rowspan="2" style="width: 14%;">Nama Lengkap</th>
                <th rowspan="2" style="width: 6%;">NISN</th>
                <th rowspan="2" style="width: 8%;">NIK</th>
                <th rowspan="2" style="width: 4%;">Umur</th>
                <th rowspan="2" style="width: 12%;">Asal Sekolah</th>
                <th rowspan="2" style="width: 4%;">Jurusan</th>
                <th colspan="2" style="width: 10%;">Sidanira / SKL</th>
                <th colspan="2" style="width: 10%;">Tes TKA / SKHU</th>
                <th rowspan="2" style="width: 5%;">Nilai Uji</th>
                <th rowspan="2" style="width: 6%;">Nilai Akhir</th>
                <th rowspan="2" style="width: 8%;">Status Seleksi</th>
            </tr>
            <tr>
                <th>Asli</th>
                <th>Bobot(70%)</th>
                <th>Asli</th>
                <th>Bobot(30%)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            $rank = 1;
            $jurusan_aktif = "";

            if (mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    
                    // Logika Reset Ranking per Jurusan
                    if ($jurusan_aktif !== $row['pilihan_jurusan']) {
                        $rank = 1; 
                        $jurusan_aktif = $row['pilihan_jurusan'];
                    }

                    $asli_skl = (float)$row['nilai_skl'];
                    $bobot_skl = $asli_skl * 0.70;
                    
                    $asli_tka = (float)$row['nilai_tka'];
                    $bobot_tka = $asli_tka * 0.30;
                    
                    $nilai_berkas_bobot = ($bobot_skl + $bobot_tka) / 2;
                    $nilai_test = (float)$row['nilai_test'];
                    $nilai_akhir = (float)$row['skor_akhir'];

                    // Perhitungan Umur
                    $umur_output = "-";
                    if (!empty($row['tanggal_lahir']) && $row['tanggal_lahir'] != '0000-00-00') {
                        $tanggal_lahir_siswa = new DateTime($row['tanggal_lahir']);
                        $hari_ini = new DateTime();
                        $umur_output = $hari_ini->diff($tanggal_lahir_siswa)->y . " Thn";
                    }

                    // Format Status
                    $status_badge = $row['status_konfirmasi'];
                    if ($status_badge == 'LULUS') {
                        $status_text = "🔒 LULUS";
                    } elseif ($status_badge == 'Tidak Jadi') {
                        $status_text = "❌ BATAL";
                    } else {
                        $status_text = "⏳ MENUNGGU";
                    }
            ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><span class="rank-badge"><?php echo $rank++; ?></span></td>
                <td>`<?php echo $row['no_pendaftaran']; ?>`</td>
                <td class="text-left"><?php echo htmlspecialchars($row['nama_lengkap'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($row['nisn'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($row['nik'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo $umur_output; ?></td>
                <td class="text-left"><?php echo htmlspecialchars($row['asal_sekolah'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo ($row['pilihan_jurusan'] == 'Akuntansi dan Keuangan Lembaga') ? 'AKL' : 'MPLB'; ?></td>
                <td><?php echo number_format($asli_skl, 2); ?></td>
                <td style="color: #475569;"><?php echo number_format($bobot_skl, 2); ?></td>
                <td><?php echo number_format($asli_tka, 2); ?></td>
                <td style="color: #475569;"><?php echo number_format($bobot_tka, 2); ?></td>
                <td><?php echo ($nilai_test > 0) ? number_format($nilai_test, 2) : '0.00'; ?></td>
                <td><b style="font-size: 11.5px;"><?php echo number_format($nilai_akhir, 2); ?></b></td>
                <td><?php echo $status_text; ?></td>
            </tr>
            <?php 
                } 
            } else { 
                echo "<tr><td colspan='16' style='padding: 20px; font-weight: bold;'>Tidak ada data siswa pendaftar pada filter ini.</td></tr>"; 
            } 
            ?>
        </tbody>
    </table>

</body>
</html>