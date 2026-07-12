<?php
session_start();
if (!isset($_SESSION['login'])) { 
    header("Location: ../login.php"); 
    exit; 
}
include '../koneksi.php';

$gel = isset($_GET['gel']) ? mysqli_real_escape_string($conn, $_GET['gel']) : 'Semua';
$sql_gel = ($gel == 'Semua') ? "" : "WHERE p.gelombang = '$gel'";

// Set nama file dinamis sesuai tanggal dan gelombang
$nama_file = "Data_SPMB_Lengkap_" . ($gel == 'Semua' ? 'Semua_Gelombang' : 'Gel_'.$gel) . "_" . date('Ymd_His') . ".xls";

// Header wajib untuk force download sebagai Excel
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=$nama_file");
header("Pragma: no-cache");
header("Expires: 0");

// Tarik SEMUA data dengan JOIN
$query = "SELECT p.*, pd.* FROM pendaftar p 
          LEFT JOIN pendaftar_detail pd ON p.id = pd.pendaftar_id 
          $sql_gel 
          ORDER BY p.pilihan_jurusan ASC, p.nama_lengkap ASC";
$result = mysqli_query($conn, $query);
?>
<table border="1">
    <tr style="background-color: #f1f5f9; font-weight: bold;">
        <th>No</th>
        <th>No Pendaftaran</th>
        <th>NISN</th>
        <th>Nama Lengkap</th>
        <th>Pilihan Jurusan</th>
        <th>Gelombang</th>
        <th>Status Konfirmasi</th>
        <th>Status Daftar Ulang</th>
        <th>Status Jakedu</th>
        <th>Asal Sekolah</th>
        <th>NPSN Sekolah</th>
        <th>Tempat Lahir</th>
        <th>Tanggal Lahir</th>
        <th>Jenis Kelamin</th>
        <th>Agama</th>
        <th>No WA (HP)</th>
        <th>Alamat Domisili</th>
        <th>Kecamatan</th>
        <th>Kelurahan</th>
        <th>Nama Ibu Kandung</th>
        <th>No KK</th>
        <th>Tanggal KK</th>
        <th>Status KJP</th>
        <th>No Rekening KJP</th>
        <th>Kebutuhan Khusus</th>
        <th>Riwayat Penyakit</th>
        <th>Nilai SKL</th>
        <th>Nilai TKA</th>
        <th>Nilai Test Akhir</th>
    </tr>
    <?php 
    $no = 1;
    while($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>".$no++."</td>";
        echo "<td>".$row['no_pendaftaran']."</td>";
        // Tambahkan petik satu (') agar angka nol di depan tidak hilang di Excel (seperti 00123...)
        echo "<td>'".$row['nisn']."</td>"; 
        echo "<td>".$row['nama_lengkap']."</td>";
        echo "<td>".$row['pilihan_jurusan']."</td>";
        echo "<td>".$row['gelombang']."</td>";
        echo "<td>".$row['status_konfirmasi']."</td>";
        echo "<td>".$row['status_daftar_ulang']."</td>";
        echo "<td>".$row['status_jakedu']."</td>";
        echo "<td>".$row['asal_sekolah']."</td>";
        echo "<td>'".$row['npsn_sekolah']."</td>";
        echo "<td>".$row['tempat_lahir']."</td>";
        echo "<td>".$row['tanggal_lahir']."</td>";
        echo "<td>".$row['jenis_kelamin']."</td>";
        echo "<td>".$row['agama']."</td>";
        echo "<td>'".$row['no_whatsapp']."</td>";
        echo "<td>".$row['alamat']."</td>";
        echo "<td>".$row['kecamatan']."</td>";
        echo "<td>".$row['kelurahan']."</td>";
        echo "<td>".$row['nama_ibu']."</td>";
        echo "<td>'".$row['no_kk']."</td>";
        echo "<td>".$row['tanggal_kk']."</td>";
        echo "<td>".$row['status_kjp']."</td>";
        echo "<td>'".$row['no_rek_kjp']."</td>";
        echo "<td>".$row['kebutuhan_khusus']."</td>";
        echo "<td>".$row['riwayat_penyakit']."</td>";
        echo "<td>".$row['nilai_skl']."</td>";
        echo "<td>".$row['nilai_tka']."</td>";
        echo "<td>".$row['nilai_test']."</td>";
        echo "</tr>";
    }
    ?>
</table>