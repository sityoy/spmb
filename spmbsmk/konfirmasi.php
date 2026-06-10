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
    $status_request = $_GET['status'];
    $tab = isset($_GET['tab']) ? $_GET['tab'] : 'akl'; // Mengembalikan admin ke tab asal

    if ($status_request == 'Jadi') {
        // 1. Aksi Kunci Kelulusan (Lulus)
        $query = "UPDATE pendaftar SET status_konfirmasi = 'Jadi' WHERE id = '$id'";
        $execute = mysqli_query($conn, $query);
        $msg = "Status siswa berhasil diperbarui menjadi LULUS!";
        
    } elseif ($status_request == 'Tidak Jadi') {
        // 2. Aksi Pembatalan (Tidak Jadi)
        $query = "UPDATE pendaftar SET status_konfirmasi = 'Tidak Jadi' WHERE id = '$id'";
        $execute = mysqli_query($conn, $query);
        $msg = "Siswa telah ditandai sebagai TIDAK JADI.";
        
    } elseif ($status_request == 'Reset') {
        // 3. Aksi Reset Status ke Menunggu Antrian
        $query = "UPDATE pendaftar SET status_konfirmasi = 'Belum' WHERE id = '$id'";
        $execute = mysqli_query($conn, $query);
        $msg = "Status kelulusan siswa berhasil di-reset.";
        
    } elseif ($status_request == 'Pindah') {
        // 4. LOGIKA CERDAS: Lempar / Pindah Jurusan Otomatis
        // Ambil data jurusan saat ini dulu
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
            
            // Update jurusan baru dan reset status konfirmasinya ke 'Belum' agar dievaluasi ulang nilainya
            $query = "UPDATE pendaftar SET pilihan_jurusan = '$jurusan_baru', status_konfirmasi = 'Belum' WHERE id = '$id'";
            $execute = mysqli_query($conn, $query);
            $msg = "Berhasil melempar $nama ke jurusan $nama_jurusan_baru!";
        }
    }

    // Redirect kembali ke admin.php dengan tab yang sesuai agar kerja admin tidak terganggu
    if ($execute) {
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