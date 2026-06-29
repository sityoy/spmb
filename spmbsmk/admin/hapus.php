<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_use_only_cookies', 1);
    session_start();
}

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/config/koneksi.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $tab = isset($_GET['tab']) ? $_GET['tab'] : 'akl';
    
    // Proses hapus record pendaftar
    $delete = mysqli_query($conn, "DELETE FROM pendaftar WHERE id = '$id'");
    
    if ($delete) {
        echo "<script>alert('Data pendaftar telah berhasil dihapus secara permanen!'); window.location='admin.php?tab=$tab';</script>";
    } else {
        echo "<script>alert('Sistem gagal menghapus data dari database!'); window.location='admin.php?tab=$tab';</script>";
    }
} else {
    header("Location: admin.php");
    exit;
}
?>