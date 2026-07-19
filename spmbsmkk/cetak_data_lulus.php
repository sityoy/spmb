<?php
session_status() === PHP_SESSION_NONE ? session_start() : null;
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

// Ambil parameter
$gel = isset($_GET['gel']) ? mysqli_real_escape_string($conn, $_GET['gel']) : 'Semua';
$jurusan = isset($_GET['jurusan']) ? mysqli_real_escape_string($conn, $_GET['jurusan']) : 'Semua';

$label_gel = ($gel == 'Semua') ? 'Semua Gelombang' : 'Gelombang ' . $gel;
$label_jur = ($jurusan == 'Semua') ? 'Semua Jurusan' : $jurusan;

// Bangun query filter
$filter = [];
if ($gel !== 'Semua') {
    $filter[] = "gelombang = '$gel'";
}
if ($jurusan !== 'Semua') {
    $filter[] = "pilihan_jurusan = '$jurusan'";
}

$sql_filter = !empty($filter) ? "WHERE " . implode(" AND ", $filter) : "";

// LOGIKA URUTAN: Lolos di atas, nilai tertinggi di atas
$query = "SELECT *, ((nilai_skl * 0.70) + (nilai_tka * 0.30)) as nilai_akhir_rank 
          FROM pendaftar 
          $sql_filter 
          ORDER BY CASE status_konfirmasi 
                    WHEN 'LULUS' THEN 1 
                    WHEN 'Menunggu' THEN 2 
                    WHEN 'Tidak Jadi' THEN 3 
                END ASC, 
                nilai_akhir_rank DESC, 
                nama_lengkap ASC";

$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Data Siswa - <?php echo $label_jur; ?></title>
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
        
        .status-lulus { color: #166534; font-weight: bold; }
        .status-gagal { color: #dc2626; font-weight: bold; }
        .status-tunggu { color: #b45309; font-weight: bold; }

        .btn-print { margin-bottom: 15px; padding: 10px 20px; background: #4f46e5; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 13px; }
        
        @media print { 
            .no-print { display: none; } 
            body { padding: 0; }
            @page { size: A4 landscape; margin: 1cm; }
        }
    </style>
</head>
<body>

    <button class="no-print btn-print" onclick="window.print()">🖨️ Cetak Data Siswa (A4 Landscape)</button>

    <div class="kop">
        <h2>LAPORAN DATA LENGKAP SISWA SPMB</h2>
        <h3>SMKS PERMATA BUNDA I JAKARTA</h3>
        <p>Jalur: <b><?php echo $label_gel; ?></b> | Jurusan: <b><?php echo $label_jur; ?></b> | Tanggal Cetak: 
        <?php 
        $bulan_indo = array(1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
        echo date('d') . ' ' . $bulan_indo[(int)date('m')] . ' ' . date('Y H:i'); 
        ?> WIB</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 10%;">No Pendaftaran</th>
                <th style="width: 22%;">Nama Lengkap</th>
                <th style="width: 12%;">NISN</th>
                <th style="width: 23%;">Asal Sekolah</th>
                <th style="width: 10%;">Gelombang</th>
                <th style="width: 20%;">Status Seleksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if (mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    // Logika Status: Jika Tidak Lolos, tampilkan alasan di bawahnya
                    $status_asli = $row['status_konfirmasi'];
                    if ($status_asli == 'Tidak Jadi') {
                        $alasan = !empty($row['alasan_pembatalan']) ? htmlspecialchars($row['alasan_pembatalan'], ENT_QUOTES, 'UTF-8') : 'Mengundurkan Diri';
                        $status_tampil = "❌ TIDAK LOLOS <br><span style='font-size:9.5px; font-weight:normal; color:#000;'>($alasan)</span>";
                        $status_class = 'status-gagal';
                    } elseif ($status_asli == 'LULUS') {
                        $status_tampil = '🔒 LULUS';
                        $status_class = 'status-lulus';
                    } else {
                        $status_tampil = '⏳ MENUNGGU';
                        $status_class = 'status-tunggu';
                    }
                    
                    echo "<tr>
                        <td>$no</td>
                        <td>{$row['no_pendaftaran']}</td>
                        <td class='text-left'>" . htmlspecialchars($row['nama_lengkap'], ENT_QUOTES, 'UTF-8') . "</td>
                        <td>" . htmlspecialchars($row['nisn'], ENT_QUOTES, 'UTF-8') . "</td>
                        <td class='text-left'>" . htmlspecialchars($row['asal_sekolah'], ENT_QUOTES, 'UTF-8') . "</td>
                        <td>{$row['gelombang']}</td>
                        <td class='$status_class'>$status_tampil</td>
                    </tr>";
                    $no++;
                }
            } else {
                echo "<tr><td colspan='7' style='padding: 20px; font-weight: bold;'>Tidak ada data pendaftar pada filter ini.</td></tr>";
            }
            ?>
        </tbody>
    </table>

</body>
</html>