<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) { exit("Akses ditolak."); }

$jurusan = mysqli_real_escape_string($conn, $_GET['jurusan']);
$gel = mysqli_real_escape_string($conn, $_GET['gel']);
$status = mysqli_real_escape_string($conn, $_GET['status']); // 'LULUS' atau 'Menunggu'

$filter_gel = ($gel == 'Semua') ? "" : " AND gelombang = '$gel'";

// Eksekusi update masal
$update = mysqli_query($conn, "UPDATE pendaftar SET status_konfirmasi = '$status' WHERE pilihan_jurusan = '$jurusan' $filter_gel");

if ($update) {
    echo "<script>alert('Status berhasil diupdate untuk seluruh pendaftar $jurusan ($gel)!'); window.location='admin.php?tab=" . ($jurusan == 'Akuntansi dan Keuangan Lembaga' ? 'akl' : 'mplb') . "&gel=$gel';</script>";
} else {
    echo "<script>alert('Gagal mengupdate status!'); window.history.back();</script>";
}
?>