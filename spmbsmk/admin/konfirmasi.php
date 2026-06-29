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

require_once __DIR__ . '/config/koneksi.php';

// Validasi parameter wajib
if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Tambahkan sanitasi
    $status_request = preg_replace('/[^a-zA-Z0-9 ]/', '', $_GET['status']);
    $tab = preg_replace('/[^a-zA-Z0-9]/', '', $_GET['tab']);
    
    // TANGKAP ALASAN DARI JAVASCRIPT PROMPT (Jika Ada)
    $alasan = isset($_GET['alasan']) ? mysqli_real_escape_string($conn, trim($_GET['alasan'])) : '';

    if ($status_request == 'LULUS') {
        // 1. Aksi Kunci Kelulusan (Lulus)
        $query = "UPDATE pendaftar SET status_konfirmasi = 'LULUS' WHERE id = '$id'";
        $execute = mysqli_query($conn, $query);
        $msg = "Status siswa berhasil diperbarui menjadi LULUS!";
    } elseif ($status_request == 'Menunggu') {
        $query = "UPDATE pendaftar SET status_konfirmasi = 'Menunggu', alasan_pembatalan = '' WHERE id = '$id'";
        $execute = mysqli_query($conn, $query);
        $msg = "Status siswa berhasil dikembalikan menjadi Menunggu!";
    } elseif ($status_request == 'Tidak Jadi') {
        $query = "UPDATE pendaftar SET status_konfirmasi = 'Tidak Jadi', alasan_pembatalan = '$alasan' WHERE id = '$id'";
        $execute = mysqli_query($conn, $query);
        $msg = "Siswa dibatalkan/tidak lulus!";
    } elseif ($status_request == 'Pindah') {
        // Logic pindah jurusan
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
            $query = "UPDATE pendaftar SET pilihan_jurusan = '$jurusan_baru', status_konfirmasi = 'Menunggu', alasan_pembatalan = '' WHERE id = '$id'";
            $execute = mysqli_query($conn, $query);
            $msg = "Berhasil melempar $nama ke jurusan $nama_jurusan_baru!";
        }
    } elseif ($status_request == 'PindahGelombang') {
        // --- LOGIKA BARU: PINDAH GELOMBANG ---
        $ke_gelombang = isset($_GET['ke_gelombang']) ? mysqli_real_escape_string($conn, $_GET['ke_gelombang']) : '1';
        $query = "UPDATE pendaftar SET gelombang = '$ke_gelombang' WHERE id = '$id'";
        $execute = mysqli_query($conn, $query);
        $msg = "Berhasil memindahkan siswa ke Gelombang $ke_gelombang!";
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
    // Jika diakses langsung tanpa parameter
    header("Location: admin.php");
    exit;
}
?>