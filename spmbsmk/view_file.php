<?php
session_start();

// 1. Cek Login (Kunci Utama)
if (!isset($_SESSION['login'])) {
    die("Akses Ditolak: Anda harus login sebagai admin untuk melihat berkas ini.");
}

// 2. Cek apakah ada request file
if (!isset($_GET['file']) || empty($_GET['file'])) {
    die("Akses Ditolak: Parameter file tidak ditemukan.");
}

// 3. Bersihkan nama file dari injeksi path (Mencegah Directory Traversal / Path Traversal)
$nama_file = basename($_GET['file']); 

// 4. Tentukan folder penyimpanan yang asli (sesuaikan nama foldernya jika berbeda)
$folder_berkas = 'uploads/'; // atau _berkas_secure/ tergantung pengaturan awal Anda
$path_lengkap = $folder_berkas . $nama_file;

// 5. Verifikasi ketersediaan file
if (file_exists($path_lengkap) && is_file($path_lengkap)) {
    // Ambil mime type (ekstensi gambar atau pdf)
    $mime_type = mime_content_type($path_lengkap);
    
    // Set Header agar browser membaca file tersebut
    header("Content-Type: $mime_type");
    header("Content-Disposition: inline; filename=\"" . $nama_file . "\"");
    header("Content-Length: " . filesize($path_lengkap));
    header("Cache-Control: private, max-age=0, must-revalidate");
    header("Pragma: public");
    
    // Tampilkan file
    readfile($path_lengkap);
    exit;
} else {
    die("Error 404: File tidak ditemukan di server atau telah dihapus.");
}
?>