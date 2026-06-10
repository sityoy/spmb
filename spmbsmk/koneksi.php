<?php
// koneksi.php
$host = "localhost";
$user = "root"; // Sesuaikan dengan user hosting Anda
$pass = "Smkpb@#1";     // Sesuaikan dengan password hosting Anda
$db   = "db_spmbsmkpb1"; // Sesuaikan dengan nama database Anda

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// WAJIB: Set charset untuk mencegah SQL Injection berbasis karakter aneh
mysqli_set_charset($conn, "utf8mb4");
?>