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
    // Tangkap data Biodata
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $nisn = mysqli_real_escape_string($conn, $_POST['nisn']);
    $no_whatsapp = mysqli_real_escape_string($conn, $_POST['no_whatsapp']);
    $pilihan_jurusan = mysqli_real_escape_string($conn, $_POST['pilihan_jurusan']);

    // Tangkap data Nilai
    $nilai_skl  = mysqli_real_escape_string($conn, $_POST['nilai_skl']);
    $nilai_tka  = mysqli_real_escape_string($conn, $_POST['nilai_tka']);
    $nilai_test = mysqli_real_escape_string($conn, $_POST['nilai_test']);
    
    // Fungsi bantu untuk cek dan upload file baru
    function handleFileUpload($input_name, $old_file) {
        if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] == 0) {
            $ext = pathinfo($_FILES[$input_name]['name'], PATHINFO_EXTENSION);
            $new_name = $input_name . '_' . time() . '_' . rand(100,999) . '.' . $ext;
            $dest = 'uploads/' . $new_name;
            if (move_uploaded_file($_FILES[$input_name]['tmp_name'], $dest)) {
                return $new_name; // Berhasil upload, kembalikan nama baru
            }
        }
        return $old_file; // Gagal upload atau tidak ada file baru, pakai yang lama
    }

    // Validasi range input (Ditambah pengecualian angka 0 jika belum test)
    if (($nilai_test < 75 && $nilai_test != 0) || $nilai_test > 100) {
        echo "<script>alert('Nilai Uji Kejuruan (Test Panitia) harus 0 (Belum Ujian) atau berada di rentang 75 - 100!');</script>";
    } elseif ($nilai_skl < 0 || $nilai_skl > 100 || $nilai_tka < 0 || $nilai_tka > 100) {
        echo "<script>alert('Nilai SKL dan TKA harus berada di rentang 0 - 100!');</script>";
    } else {
        
        // Proses semua file
        $f_ijazah = handleFileUpload('file_ijazah', $data['file_ijazah']);
        $f_tka    = handleFileUpload('file_tka', $data['file_tka']);
        $f_kk     = handleFileUpload('file_kk', $data['file_kk']);
        $f_akte   = handleFileUpload('file_akte', $data['file_akte']);

        // Update semua data ke database
        $update = mysqli_query($conn, "UPDATE pendaftar SET 
            nama_lengkap = '$nama_lengkap',
            nisn = '$nisn',
            no_whatsapp = '$no_whatsapp',
            pilihan_jurusan = '$pilihan_jurusan',
            nilai_skl = '$nilai_skl', 
            nilai_tka = '$nilai_tka', 
            nilai_test = '$nilai_test',
            file_ijazah = '$f_ijazah',
            file_tka = '$f_tka',
            file_kk = '$f_kk',
            file_akte = '$f_akte'
            WHERE id = '$id'");
        
        if ($update) {
            echo "<script>alert('Perubahan Data dan Nilai berhasil disimpan!'); window.location='admin.php?tab=$tab';</script>";
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
    <title>Edit Data & Nilai Siswa - SPMB</title>
    <link rel="icon" type="image/x-icon" href="logo/logosmkpb.png">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8fafc; color: #1e293b; padding: 40px 20px; display: flex; justify-content: center; margin: 0; }
        .form-container { background: #fff; width: 100%; max-width: 900px; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        h3 { margin-top: 0; color: #1e293b; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; }
        
        /* Grid Layout */
        .grid-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        @media (max-width: 768px) { .grid-layout { grid-template-columns: 1fr; } }
        
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 13.5px; color: #475569; }
        input[type="text"], input[type="number"], select { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-size: 15px; }
        input:focus, select:focus { outline: none; border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,0.1); }
        
        .file-box { background: #f8fafc; padding: 15px; border: 1px dashed #94a3b8; border-radius: 6px; }
        .file-box input[type="file"] { margin-top: 10px; font-size: 13px; width: 100%; }
        .btn-lihat { background: #10b981; color: white; padding: 4px 10px; text-decoration: none; border-radius: 4px; font-size: 11px; display: inline-block; margin-left: 5px; font-weight: bold;}
        
        .btn-submit { background: #4f46e5; color: white; padding: 14px; border: none; border-radius: 8px; cursor: pointer; font-weight: 700; width: 100%; font-size: 15px; margin-top: 20px; transition: 0.2s; }
        .btn-submit:hover { background: #4338ca; }
        .btn-back { display: block; text-align: center; margin-top: 15px; color: #64748b; text-decoration: none; font-size: 14px; }
        
        .section-title { color: #4f46e5; font-size: 16px; font-weight: 700; margin-bottom: 15px; }
        .info-box { background: #f1f5f9; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 13.5px; line-height: 1.6; border: 1px solid #e2e8f0; }
        .divider { border-top: 1px dashed #cbd5e1; margin: 25px 0 20px 0; }
    </style>
</head>
<body>

<div class="form-container">
    <h3>📝 Edit Data, Berkas & Nilai Calon Siswa</h3>
    
    <div class="info-box">
        Silakan perbaiki biodata, perbarui lampiran file (jika ada yang salah/buram), atau input nilai hasil seleksi pada formulir di bawah ini.
    </div>

    <form action="" method="POST" enctype="multipart/form-data">
        
        <div class="grid-layout">
            <div>
                <div class="section-title">A. Biodata & Nilai Siswa</div>
                
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($data['nama_lengkap'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>NISN</label>
                    <input type="text" name="nisn" value="<?php echo htmlspecialchars($data['nisn'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>No. WhatsApp</label>
                    <input type="text" name="no_whatsapp" value="<?php echo htmlspecialchars($data['no_whatsapp'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <div class="form-group">
                    <label>Pilihan Komp. Keahlian</label>
                    <select name="pilihan_jurusan" required>
                        <option value="Akuntansi dan Keuangan Lembaga" <?php if($data['pilihan_jurusan'] == 'Akuntansi dan Keuangan Lembaga') echo 'selected'; ?>>Akuntansi dan Keuangan Lembaga (AKL)</option>
                        <option value="Manajemen Perkantoran dan Layanan Bisnis" <?php if($data['pilihan_jurusan'] == 'Manajemen Perkantoran dan Layanan Bisnis') echo 'selected'; ?>>Manajemen Perkantoran dan Layanan Bisnis (MPLB)</option>
                    </select>
                </div>

                <div class="divider"></div>

                <div class="form-group">
                    <label for="nilai_skl">Koreksi Rata-rata Nilai SIDANIRA / SKL</label>
                    <input type="number" name="nilai_skl" step="0.01" min="0" max="100" 
                        value="<?php echo $data['nilai_skl']; ?>" 
                        oninput="if(this.value > 100) this.value = 100; if(this.value < 0) this.value = 0; if(this.value.includes('.')){ let p=this.value.split('.'); if(p[1].length>2) this.value=p[0]+'.'+p[1].substring(0,2); }" required>
                </div>

                <div class="form-group">
                    <label for="nilai_tka">Koreksi Nilai Tes Akademik / TKA Asli</label>
                    <input type="number" name="nilai_tka" step="0.01" min="0" max="100" 
                        value="<?php echo $data['nilai_tka']; ?>" 
                        oninput="if(this.value > 100) this.value = 100; if(this.value < 0) this.value = 0; if(this.value.includes('.')){ let p=this.value.split('.'); if(p[1].length>2) this.value=p[0]+'.'+p[1].substring(0,2); }" required>
                </div>

                <div class="form-group">
                    <label for="nilai_test" style="color:#4f46e5;">Input Nilai Ujian Seleksi (Panitia)</label>
                    <input type="number" name="nilai_test" step="0.01" min="0" max="100" 
                        value="<?php echo $data['nilai_test']; ?>" 
                        oninput="if(this.value > 100) this.value = 100; if(this.value.includes('.')){ let p=this.value.split('.'); if(p[1].length>2) this.value=p[0]+'.'+p[1].substring(0,2); }" required>
                    <small style="color:#64748b; font-size:11px; margin-top:6px; display:block; line-height:1.4;">
                        *Isi <b>0</b> jika belum ujian. Jika sudah, rentang nilai <b>75.00 - 100.00</b>.<br>
                        *Gunakan tanda <b>titik (.)</b> untuk angka desimal.
                    </small>
                </div>
            </div>

            <div>
                <div class="section-title" style="color:#f59e0b;">B. Perbarui Berkas / Dokumen</div>
                <p style="font-size:12px; color:#64748b; margin-top:0;">*Abaikan/kosongkan input file di bawah ini jika Anda <b>TIDAK</b> ingin mengubah file lama.</p>

                <div class="form-group file-box">
                    <label>File Ijazah / SKL</label>
                    <?php if(!empty($data['file_ijazah'])): ?>
                        <span style="font-size:12px;">Status: ✅ Ada</span> 
                        <a href="view_file.php?file=<?php echo urlencode($data['file_ijazah']); ?>" target="_blank" class="btn-lihat">👁️ Pratinjau File Lama</a>
                    <?php else: ?>
                        <span style="font-size:12px; color:#dc2626;">Status: ❌ Belum ada</span>
                    <?php endif; ?>
                    <input type="file" name="file_ijazah" accept=".pdf,.jpg,.jpeg,.png">
                </div>

                <div class="form-group file-box">
                    <label>File TKA</label>
                    <?php if(!empty($data['file_tka'])): ?>
                        <span style="font-size:12px;">Status: ✅ Ada</span> 
                        <a href="view_file.php?file=<?php echo urlencode($data['file_tka']); ?>" target="_blank" class="btn-lihat">👁️ Pratinjau File Lama</a>
                    <?php endif; ?>
                    <input type="file" name="file_tka" accept=".pdf,.jpg,.jpeg,.png">
                </div>

                <div class="form-group file-box">
                    <label>Kartu Keluarga (KK)</label>
                    <?php if(!empty($data['file_kk'])): ?>
                        <span style="font-size:12px;">Status: ✅ Ada</span> 
                        <a href="view_file.php?file=<?php echo urlencode($data['file_kk']); ?>" target="_blank" class="btn-lihat">👁️ Pratinjau File Lama</a>
                    <?php endif; ?>
                    <input type="file" name="file_kk" accept=".pdf,.jpg,.jpeg,.png">
                </div>

                <div class="form-group file-box">
                    <label>Akte Kelahiran</label>
                    <?php if(!empty($data['file_akte'])): ?>
                        <span style="font-size:12px;">Status: ✅ Ada</span> 
                        <a href="view_file.php?file=<?php echo urlencode($data['file_akte']); ?>" target="_blank" class="btn-lihat">👁️ Pratinjau File Lama</a>
                    <?php endif; ?>
                    <input type="file" name="file_akte" accept=".pdf,.jpg,.jpeg,.png">
                </div>
            </div>
        </div>

        <button type="submit" name="submit" class="btn-submit">💾 Simpan Semua Perubahan</button>
        <a href="admin.php?tab=<?php echo $tab; ?>" class="btn-back">❌ Batal dan Kembali</a>
    </form>
</div>

<script>
    document.querySelector('form').addEventListener('submit', function() {
        const btn = document.querySelector('.btn-submit');
        btn.disabled = true;
        btn.innerText = "⏳ Menyimpan dan Mengunggah...";
    });
</script>

</body>
</html>