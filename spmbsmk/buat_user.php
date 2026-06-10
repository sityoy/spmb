<?php
include 'koneksi.php';

// Kosongkan tabel pengguna terlebih dahulu agar bersih
mysqli_query($conn, "TRUNCATE TABLE pengguna");

$username = "Admin2";
$password_asli = "SMKPB@#1";

// Membuat enkripsi yang bener-bener fresh dan bersih
$password_aman = password_hash($password_asli, PASSWORD_DEFAULT);

$query = "INSERT INTO pengguna (username, password) VALUES ('$username', '$password_aman')";

if (mysqli_query($conn, $query)) {
    echo "<h3>Akun Berhasil Dibuat Ulang!</h3>";
    echo "Username: <b>" . $username . "</b><br>";
    echo "Password: <b>" . $password_asli . "</b><br><br>";
    echo "<a href='login.php'>Silakan Buka Halaman Login</a>";
} else {
    echo "Gagal membuat akun: " . mysqli_error($conn);
}
?>