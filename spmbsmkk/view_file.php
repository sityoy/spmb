<?php
session_start();

// Jika ada parameter download=true, paksa jadi attachment (download)
if (isset($_GET['download']) && $_GET['download'] === 'true') {
    header('Content-Disposition: attachment; filename="' . basename($_GET['file']) . '"');
} else {
    header('Content-Disposition: inline; filename="' . basename($_GET['file']) . '"');
}

if (!isset($_SESSION['login'])) {
    die("Akses Ditolak: Anda harus login sebagai admin.");
}

if (!isset($_GET['file']) || empty($_GET['file'])) {
    die("Akses Ditolak: File tidak ditemukan.");
}

$nama_file = basename($_GET['file']); 
$folder_berkas = 'uploads/'; 
$path_lengkap = $folder_berkas . $nama_file;

if (file_exists($path_lengkap)) {
    $ekstensi = strtolower(pathinfo($path_lengkap, PATHINFO_EXTENSION));
    
    // Tentukan Content-Type manual untuk memastikan browser tidak download paksa
    $mimes = [
        'pdf'  => 'application/pdf',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif'
    ];
    
    $mime_type = isset($mimes[$ekstensi]) ? $mimes[$ekstensi] : mime_content_type($path_lengkap);
    
    header("Content-Type: $mime_type");
    // 'inline' memerintahkan browser untuk menampilkan langsung (bukan download)
    header("Content-Disposition: inline; filename=\"" . $nama_file . "\"");
    header("Content-Length: " . filesize($path_lengkap));
    header("Cache-Control: no-cache, must-revalidate");
    
    readfile($path_lengkap);
    exit;
} else {
    die("Error 404: File tidak ditemukan.");
}
?>