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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tangkap ketiga nilai dari form
    $nilai_skl  = mysqli_real_escape_string($conn, $_POST['nilai_skl']);
    $nilai_tka  = mysqli_real_escape_string($conn, $_POST['nilai_tka']);
    $nilai_test = mysqli_real_escape_string($conn, $_POST['nilai_test']);
    
    // Validasi range input 
    if ($nilai_test < 75 || $nilai_test > 100) {
        echo "<script>alert('Nilai Uji Kejuruan (Test Panitia) harus berada di rentang 75 - 100!');</script>";
    } elseif ($nilai_skl < 0 || $nilai_skl > 100 || $nilai_tka < 0 || $nilai_tka > 100) {
        echo "<script>alert('Nilai SKL dan TKA harus berada di rentang 0 - 100!');</script>";
    } else {
        // Update ketiga nilai ke database
        $update = mysqli_query($conn, "UPDATE pendaftar SET nilai_skl = '$nilai_skl', nilai_tka = '$nilai_tka', nilai_test = '$nilai_test' WHERE id = '$id'");
        
        if ($update) {
            echo "<script>alert('Perubahan Nilai berhasil disimpan!'); window.location='admin.php?tab=$tab';</script>";
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
    <title>Edit Nilai Siswa - SPMB</title>
    <link rel="icon" type="image/x-icon" href="logo/logosmkpb.png">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8fafc; color: #1e293b; padding: 40px; display: flex; justify-content: center; }
        .form-container { background: #fff; width: 100%; max-width: 480px; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        h3 { margin-top: 0; color: #1e293b; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #475569; }
        input[type="number"] { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-size: 16px; }
        input[type="number"]:focus { outline: none; border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,0.1); }
        .btn-submit { background: #4f46e5; color: white; padding: 14px; border: none; border-radius: 8px; cursor: pointer; font-weight: 700; width: 100%; font-size: 15px; margin-top: 10px; transition: 0.2s; }
        .btn-submit:hover { background: #4338ca; }
        .btn-back { display: block; text-align: center; margin-top: 15px; color: #64748b; text-decoration: none; font-size: 14px; }
        .info-box { background: #f1f5f9; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 13.5px; line-height: 1.6; border: 1px solid #e2e8f0; }
        .divider { border-top: 1px dashed #cbd5e1; margin: 25px 0 20px 0; }
    </style>
</head>
<body>

<div class="form-container">
    <h3>📝 Edit & Input Nilai Calon Siswa</h3>
    
    <div class="info-box">
        <strong>Nama Lengkap:</strong> <?php echo htmlspecialchars($data['nama_lengkap'], ENT_QUOTES, 'UTF-8'); ?><br>
        <strong>NISN Calon Siswa:</strong> <?php echo htmlspecialchars($data['nisn'], ENT_QUOTES, 'UTF-8'); ?><br>
        <strong>Pilihan Komp. Keahlian:</strong> <?php echo htmlspecialchars($data['pilihan_jurusan'], ENT_QUOTES, 'UTF-8'); ?>
    </div>

    <form action="" method="POST">
        <div class="form-group">
            <label for="nilai_skl">Koreksi Rata-rata Nilai SIDANIRA / SKL</label>
            <input type="number" 
                name="nilai_skl" 
                step="0.01" min="0" max="100" 
                value="<?php echo $data['nilai_skl']; ?>" 
                oninput="if(this.value > 100) this.value = 100; if(this.value < 0) this.value = 0; if(this.value.includes('.')){ let p=this.value.split('.'); if(p[1].length>2) this.value=p[0]+'.'+p[1].substring(0,2); }" 
                required>
        </div>

        <div class="form-group">
            <label for="nilai_tka">Koreksi Nilai Tes Akademik / TKA Asli</label>
            <input type="number" 
                name="nilai_tka" 
                step="0.01" min="0" max="100" 
                value="<?php echo $data['nilai_tka']; ?>" 
                oninput="if(this.value > 100) this.value = 100; if(this.value < 0) this.value = 0; if(this.value.includes('.')){ let p=this.value.split('.'); if(p[1].length>2) this.value=p[0]+'.'+p[1].substring(0,2); }" 
                required>
        </div>

        <div class="divider"></div>

        <div class="form-group">
            <label for="nilai_test">Input Nilai Hasil Ujian Seleksi (Panitia)</label>
            <input type="number" 
                name="nilai_test" 
                step="0.01" min="75" max="100" 
                value="<?php echo $data['nilai_test']; ?>" 
                oninput="if(this.value > 100) this.value = 100; if(this.value.includes('.')){ let p=this.value.split('.'); if(p[1].length>2) this.value=p[0]+'.'+p[1].substring(0,2); }" 
                required>
            <small style="color:#64748b; font-size:11px; margin-top:6px; display:block; line-height:1.4;">
                *Rentang nilai <b>75.00 - 100.00</b> untuk Ujian Panitia.<br>
                *Gunakan tanda <b>titik (.)</b> untuk angka desimal.
            </small>
        </div>

        <button type="submit" name="submit" class="btn-submit">💾 Simpan Perubahan Nilai</button>
        <a href="admin.php?tab=<?php echo $tab; ?>" class="btn-back">❌ Batal dan Kembali</a>
    </form>
</div>

<script>
    document.querySelector('form').addEventListener('submit', function() {
        const btn = document.querySelector('.btn-submit');
        btn.disabled = true;
        btn.innerText = "⏳ Menyimpan...";
    });
</script>

</body>
</html>