<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_use_only_cookies', 1);
    session_start();
}

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

if (!isset($_GET['id'])) {
    header("Location: admin.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'akl';

// Ambil data pendaftar berdasarkan ID
$query = mysqli_query($conn, "SELECT * FROM pendaftar WHERE id = '$id'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Data pendaftar tidak ditemukan!'); window.location='admin.php?tab=$tab';</script>";
    exit;
}

if (isset($_POST['submit'])) {
    $nilai_test = mysqli_real_escape_string($conn, $_POST['nilai_test']);
    
    // Validasi range input 
    if ($nilai_test < 75 || $nilai_test > 100) {
        echo "<script>alert('Nilai harus berada di rentang 75 - 100!');</script>";
    } else {
        $update = mysqli_query($conn, "UPDATE pendaftar SET nilai_test = '$nilai_test' WHERE id = '$id'");
        if ($update) {
            echo "<script>alert('Nilai Uji Kejuruan berhasil disimpan!'); window.location='admin.php?tab=$tab';</script>";
        } else {
            echo "<script>alert('Gagal menyimpan perubahan!');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Nilai Uji - SPMB</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8fafc; color: #1e293b; padding: 40px; display: flex; justify-content: center; }
        .form-container { background: #fff; width: 100%; max-width: 480px; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        h3 { margin-top: 0; color: #1e293b; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; }
        .form-group { margin-bottom: 25px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #475569; }
        input[type="number"] { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-size: 16px; }
        .btn-submit { background: #4f46e5; color: white; padding: 12px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; width: 100%; font-size: 15px; }
        .btn-submit:hover { background: #4338ca; }
        .btn-back { display: block; text-align: center; margin-top: 15px; color: #64748b; text-decoration: none; font-size: 14px; }
        .info-box { background: #f1f5f9; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 13.5px; line-height: 1.6; }
    </style>
</head>
<body>

<div class="form-container">
    <h3>📝 Input Nilai Uji Test Kejuruan</h3>
    
    <div class="info-box">
        <strong>Nama Lengkap:</strong> <?php echo htmlspecialchars($data['nama_lengkap'], ENT_QUOTES, 'UTF-8'); ?><br>
        <strong>NISN Calon Siswa:</strong> <?php echo htmlspecialchars($data['nisn'], ENT_QUOTES, 'UTF-8'); ?><br>
        <strong>Pilihan Komp. Keahlian:</strong> <?php echo htmlspecialchars($data['pilihan_jurusan'], ENT_QUOTES, 'UTF-8'); ?>
    </div>

    <form action="" method="POST">
        <div class="form-group">
            <label for="nilai_test">Nilai Hasil Ujian Seleksi (Panitia)</label>
            <input type="number" 
                name="nilai_test" 
                step="0.01" 
                min="75" 
                max="100" 
                value="<?php echo $data['nilai_test']; ?>" 
                oninput="if(this.value > 100) this.value = 100; if(this.value.includes('.')){ let p=this.value.split('.'); if(p[1].length>2) this.value=p[0]+'.'+p[1].substring(0,2); }" 
                onblur="if(this.value !== '' && this.value < 75) this.value = 75;"
                required>
            <small style="color:#64748b; font-size:11px; margin-top:4px; display:block;">
                *Rentang nilai <b>75.00 - 100.00</b>. Gunakan tanda <b>titik (.)</b> untuk desimal.
            </small>
        </div>
        <button type="submit" name="submit" class="btn-submit">Simpan Nilai Akhir</button>
        <a href="admin.php?tab=<?php echo $tab; ?>" class="btn-back">❌ Batal dan Kembali</a>
    </form>
</div>

</body>
</html>