<?php
// Hubungkan ke database
require_once __DIR__ . '/config/koneksi.php';

// Sesuaikan nama tabel jika berbeda (biasanya 'admin' atau 'users')
// Berdasarkan gambar struktur tabel Anda, kemungkinan namanya 'admin'
$nama_tabel = 'admin'; 

// Password yang direquest
$password_plain = 'Smkpb@#1';

// Mengenkripsi password menggunakan algoritma BCRYPT bawaan PHP
$password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);

$akun_baru = [
    'smkpb1',
    'permatabunda1'
];

echo "<h3>⚙️ Proses Eksekusi Akun Admin Baru</h3>";

foreach ($akun_baru as $user) {
    // Cek apakah username sudah dipakai
    $cek = mysqli_query($conn, "SELECT * FROM $nama_tabel WHERE username = '$user'");
    
    if (mysqli_num_rows($cek) > 0) {
        echo "<span style='color:#f59e0b; font-weight:bold;'>⚠️ Akun '$user' sudah ada di database!</span><br>";
    } else {
        // Masukkan data ke database dengan password yang sudah di-hash
        // is_login di-set 0 (offline)
        $query = "INSERT INTO $nama_tabel (username, password, is_login) VALUES ('$user', '$password_hashed', 0)";
        $eksekusi = mysqli_query($conn, $query);
        
        if ($eksekusi) {
            echo "<span style='color:#10b981; font-weight:bold;'>✅ Berhasil! Akun '$user' telah dibuat.</span><br>";
        } else {
            echo "<span style='color:#ef4444; font-weight:bold;'>❌ Gagal membuat '$user': " . mysqli_error($conn) . "</span><br>";
        }
    }
}

echo "<br><div style='padding:15px; background:#fee2e2; color:#991b1b; border:1px solid #f87171; border-radius:8px; display:inline-block; margin-top:20px;'>
        <b>⚠️ SANGAT PENTING:</b><br>
        Jika sudah berhasil dijalankan, segera <b>HAPUS</b> file <i>tambah_admin.php</i> ini dari VS Code demi keamanan!
      </div>";
?>