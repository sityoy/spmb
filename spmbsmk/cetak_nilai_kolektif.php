<?php
session_start();
if (!isset($_SESSION['login'])) { header("Location: login.php"); exit; }
include 'koneksi.php';

$gel = isset($_GET['gel']) ? $_GET['gel'] : 'Semua';
$sql_gel = ($gel == 'Semua') ? "" : "WHERE gelombang = '$gel'";

$query = "SELECT * FROM pendaftar $sql_gel ORDER BY pilihan_jurusan ASC, nilai_test DESC, nama_lengkap ASC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Nilai Kolektif - Gelombang <?php echo $gel; ?></title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12px; }
        .kop { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        h2, h3 { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: center; }
        th { background-color: #f1f5f9; }
        td:nth-child(2), td:nth-child(3) { text-align: left; }
        .btn-print { margin: 20px 0; padding: 10px 20px; background: #4f46e5; color: #fff; border: none; cursor: pointer; font-weight: bold; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <button class="no-print btn-print" onclick="window.print()">🖨️ Cetak Rekap Nilai</button>

    <div class="kop">
        <h2>REKAPITULASI NILAI CALON SISWA BARU</h2>
        <h3>SMKS PERMATA BUNDA I JAKARTA</h3>
        <p>Gelombang: <b><?php echo $gel; ?></b> | Tanggal Cetak: <?php echo date('d-m-Y'); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No. Pendaftaran</th>
                <th>Nama Lengkap</th>
                <th>Jurusan</th>
                <th>Rata2 Berkas (Asli)</th>
                <th>Nilai Test</th>
                <th>Nilai Akhir (Skor)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            while($row = mysqli_fetch_assoc($result)) {
                $asli_skl = (float)$row['nilai_skl'];
                $asli_tka = (float)$row['nilai_tka'];
                $rata_berkas_asli = ($asli_skl + $asli_tka) / 2;
                
                $bobot_skl = $asli_skl * 0.70;
                $bobot_tka = $asli_tka * 0.30;
                $nilai_berkas_bobot = ($bobot_skl + $bobot_tka) / 2;
                $nilai_test = (float)$row['nilai_test'];
                $nilai_akhir = ($nilai_berkas_bobot + $nilai_test) / 2;
            ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo $row['no_pendaftaran']; ?></td>
                <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                <td><?php echo ($row['pilihan_jurusan'] == 'Akuntansi dan Keuangan Lembaga') ? 'AKL' : 'MPLB'; ?></td>
                <td><?php echo number_format($rata_berkas_asli, 2); ?></td>
                <td><?php echo number_format($nilai_test, 2); ?></td>
                <td><b><?php echo number_format($nilai_akhir, 2); ?></b></td>
                <td><?php echo $row['status_konfirmasi']; ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>