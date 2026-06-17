<?php
// ==========================================
// SECURITY LAYER: SECURE SESSION START
// ==========================================

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_use_only_cookies', 1);
    session_start();
}

// Pastikan hanya admin yang bisa mengakses file eksekusi ini
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

// Validasi parameter wajib
if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Tambahkan sanitasi
    $status_request = preg_replace('/[^a-zA-Z0-9 ]/', '', $_GET['status']);
    $tab = preg_replace('/[^a-zA-Z0-9]/', '', $_GET['tab']);
    
    // TANGKAP ALASAN DARI JAVASCRIPT PROMPT (Jika Ada)
    $alasan = isset($_GET['alasan']) ? mysqli_real_escape_string($conn, trim($_GET['alasan'])) : '';

    if ($status_request == 'Jadi') {
        // 1. Aksi Kunci Kelulusan (Lulus)
        $query = "UPDATE pendaftar SET status_konfirmasi = 'Jadi' WHERE id = '$id'";
        $execute = mysqli_query($conn, $query);
        $msg = "Status siswa berhasil diperbarui menjadi LULUS!";
        
    } elseif ($status_request == 'Tidak Jadi') {
        // 2. Aksi Pembatalan (Tidak Jadi) BESERTA ALASANNYA
        $query = "UPDATE pendaftar SET status_konfirmasi = 'Tidak Jadi', alasan_pembatalan = '$alasan' WHERE id = '$id'";
        $execute = mysqli_query($conn, $query);
        $msg = "Siswa telah dibatalkan dengan alasan: " . $alasan;
        
    } elseif ($status_request == 'Reset') {
        // 3. Aksi Reset Status ke Menunggu Antrian (Alasan otomatis dikosongkan lagi)
        $query = "UPDATE pendaftar SET status_konfirmasi = 'Belum', alasan_pembatalan = '' WHERE id = '$id'";
        $execute = mysqli_query($conn, $query);
        $msg = "Status kelulusan siswa berhasil di-reset.";
        
    } elseif ($status_request == 'Pindah') {
        // 4. LOGIKA CERDAS: Lempar / Pindah Jurusan Otomatis
        $cek_data = mysqli_query($conn, "SELECT nama_lengkap, pilihan_jurusan FROM pendaftar WHERE id = '$id'");
        $data_siswa = mysqli_fetch_assoc($cek_data);
        
        if ($data_siswa) {
            $nama = $data_siswa['nama_lengkap'];
            $jurusan_sekarang = $data_siswa['pilihan_jurusan'];
            
            // Tukar posisi jurusan ke sebaliknya
            if ($jurusan_sekarang == 'Akuntansi dan Keuangan Lembaga') {
                $jurusan_baru = 'Manajemen Perkantoran dan Layanan Bisnis';
                $nama_jurusan_baru = 'MPLB';
            } else {
                $jurusan_baru = 'Akuntansi dan Keuangan Lembaga';
                $nama_jurusan_baru = 'AKL';
            }
            
            // Update jurusan baru dan reset status konfirmasinya ke 'Belum'
            $query = "UPDATE pendaftar SET pilihan_jurusan = '$jurusan_baru', status_konfirmasi = 'Belum', alasan_pembatalan = '' WHERE id = '$id'";
            $execute = mysqli_query($conn, $query);
            $msg = "Berhasil melempar $nama ke jurusan $nama_jurusan_baru!";
        }
    }

    // Redirect kembali ke admin.php dengan tab yang sesuai
    if (isset($execute) && $execute) {
        echo "<script>
                alert('$msg');
                window.location = 'admin.php?tab=$tab';
              </script>";
    } else {
        echo "<script>
                alert('Gagal mengeksekusi perintah database!');
                window.location = 'admin.php?tab=$tab';
              </script>";
    }
} else {
    // Jika diakses ilegal tanpa parameter, tendang ke dashboard
    header("Location: admin.php");
    exit;
}
?>