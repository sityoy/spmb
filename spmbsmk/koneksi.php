<?php
// koneksi.php
$host = "localhost";
$user = "root"; 
$pass = "Smkpb@#1"; 
$db   = "uj_spmb";

// Gunakan @ untuk menyembunyikan detail error dari user umum
$conn = @mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    // Log error ke file sistem, jangan tampilkan ke user
    error_log("Koneksi database gagal: " . mysqli_connect_error());
    die("Sistem sedang dalam pemeliharaan. Silakan hubungi administrator.");
}

mysqli_set_charset($conn, "utf8mb4");
?>