<?php
session_start();
require_once __DIR__ . '/config/koneksi.php';

// Update status di database sebelum sesi dihancurkan
// Anda perlu menyimpan ID user di sesi saat login tadi
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $conn->query("UPDATE pengguna SET is_logged_in = 0 WHERE id = '$uid'");
}

$_SESSION = [];
session_unset();
session_destroy();

header("Location: login.php");
exit;
?>