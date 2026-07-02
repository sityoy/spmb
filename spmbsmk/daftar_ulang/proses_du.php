<?php
session_start();
if (!isset($_SESSION['login'])) { header("Location: ../login.php"); exit; }
include '../koneksi.php';

if (!isset($_GET['id'])) { header("Location: index.php"); exit; }
$id = mysqli_real_escape_string($conn, $_GET['id']);

$query_siswa = mysqli_query($conn, "SELECT p.*, d.ukuran_baju, d.bawa_skl_asli, d.bawa_kk_fc, d.bawa_akte_fc, d.bawa_ktp_ortu_fc, d.bawa_rapot_fc, d.catatan_du 
                                    FROM pendaftar p 
                                    LEFT JOIN daftar_ulang d ON p.id = d.id_pendaftar 
                                    WHERE p.id = '$id'");
$data = mysqli_fetch_assoc($query_siswa);

if (!$data) { echo "<script>alert('Siswa tidak ditemukan!'); window.location='index.php';</script>"; exit; }

if (isset($_POST['simpan_du'])) {
    $ukuran_baju = mysqli_real_escape_string($conn, $_POST['ukuran_baju']);
    $skl = isset($_POST['bawa_skl_asli']) ? 'Ya' : 'Tidak';
    $kk = isset($_POST['bawa_kk_fc']) ? 'Ya' : 'Tidak';
    $akte = isset($_POST['bawa_akte_fc']) ? 'Ya' : 'Tidak';
    $ktp = isset($_POST['bawa_ktp_ortu_fc']) ? 'Ya' : 'Tidak';
    $rapot = isset($_POST['bawa_rapot_fc']) ? 'Ya' : 'Tidak';
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan_du']);
    $waktu_sekarang = date('Y-m-d H:i:s');

    if ($data['status_daftar_ulang'] == 'Belum') {
        mysqli_query($conn, "INSERT INTO daftar_ulang (id_pendaftar, tanggal_du, ukuran_baju, bawa_skl_asli, bawa_kk_fc, bawa_akte_fc, bawa_ktp_ortu_fc, bawa_rapot_fc, catatan_du) 
                             VALUES ('$id', '$waktu_sekarang', '$ukuran_baju', '$skl', '$kk', '$akte', '$ktp', '$rapot', '$catatan')");
        mysqli_query($conn, "UPDATE pendaftar SET status_daftar_ulang = 'Sudah' WHERE id = '$id'");
    } else {
        mysqli_query($conn, "UPDATE daftar_ulang SET 
                             ukuran_baju = '$ukuran_baju', bawa_skl_asli = '$skl', bawa_kk_fc = '$kk', 
                             bawa_akte_fc = '$akte', bawa_ktp_ortu_fc = '$ktp', bawa_rapot_fc = '$rapot', catatan_du = '$catatan' 
                             WHERE id_pendaftar = '$id'");
    }
    echo "<script>alert('Data Daftar Ulang Berhasil Disimpan!'); window.location='index.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Proses Daftar Ulang - <?php echo $data['nama_lengkap']; ?></title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8fafc; display: flex; justify-content: center; padding: 40px 20px; }
        .box { background: white; max-width: 600px; width: 100%; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .identitas { background: #f1f5f9; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 5px solid #4f46e5; }
        h3 { margin-top: 0; color: #1e293b; }
        .form-group { margin-bottom: 15px; }
        label { font-weight: bold; font-size: 13.5px; display: block; margin-bottom: 5px; color: #475569;}
        select, textarea { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-family: inherit;}
        .checkbox-group { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px; background: #fafafa;}
        .checkbox-group input { transform: scale(1.3); cursor: pointer; }
        .btn-submit { background: #10b981; color: white; border: none; padding: 12px; width: 100%; font-size: 15px; font-weight: bold; border-radius: 6px; cursor: pointer; margin-top: 10px; }
        .btn-kembali { display: block; text-align: center; margin-top: 15px; color: #64748b; text-decoration: none; font-size: 14px; font-weight: bold;}
    </style>
</head>
<body>
    <div class="box">
        <h3>✅ Form Proses Daftar Ulang</h3>
        <div class="identitas">
            <b style="font-size:16px;"><?php echo $data['nama_lengkap']; ?></b><br>
            <span style="font-size:13px; color:#475569;">No: <?php echo $data['no_pendaftaran']; ?> | NISN: <?php echo $data['nisn']; ?></span><br>
            <span style="font-size:13px; font-weight:bold; color:#d97706;">Jurusan: <?php echo $data['pilihan_jurusan']; ?></span>
        </div>

        <form method="POST">
            <div class="form-group">
                <label>Ukuran Seragam / Baju</label>
                <select name="ukuran_baju" required>
                    <option value="">-- Pilih Ukuran --</option>
                    <option value="S" <?php echo (isset($data['ukuran_baju']) && $data['ukuran_baju'] == 'S') ? 'selected' : ''; ?>>S (Small)</option>
                    <option value="M" <?php echo (isset($data['ukuran_baju']) && $data['ukuran_baju'] == 'M') ? 'selected' : ''; ?>>M (Medium)</option>
                    <option value="L" <?php echo (isset($data['ukuran_baju']) && $data['ukuran_baju'] == 'L') ? 'selected' : ''; ?>>L (Large)</option>
                    <option value="XL" <?php echo (isset($data['ukuran_baju']) && $data['ukuran_baju'] == 'XL') ? 'selected' : ''; ?>>XL</option>
                    <option value="XXL" <?php echo (isset($data['ukuran_baju']) && $data['ukuran_baju'] == 'XXL') ? 'selected' : ''; ?>>XXL</option>
                </select>
            </div>

            <label>Ceklis Kelengkapan Berkas Fisik yang Dibawa:</label>
            <div class="checkbox-group">
                <input type="checkbox" name="bawa_skl_asli" id="skl" <?php echo (isset($data['bawa_skl_asli']) && $data['bawa_skl_asli'] == 'Ya') ? 'checked' : ''; ?>>
                <label for="skl" style="margin:0; cursor:pointer;">SKL / Ijazah Asli</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" name="bawa_kk_fc" id="kk" <?php echo (isset($data['bawa_kk_fc']) && $data['bawa_kk_fc'] == 'Ya') ? 'checked' : ''; ?>>
                <label for="kk" style="margin:0; cursor:pointer;">Fotokopi Kartu Keluarga (KK)</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" name="bawa_akte_fc" id="akte" <?php echo (isset($data['bawa_akte_fc']) && $data['bawa_akte_fc'] == 'Ya') ? 'checked' : ''; ?>>
                <label for="akte" style="margin:0; cursor:pointer;">Fotokopi Akte Kelahiran</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" name="bawa_ktp_ortu_fc" id="ktp" <?php echo (isset($data['bawa_ktp_ortu_fc']) && $data['bawa_ktp_ortu_fc'] == 'Ya') ? 'checked' : ''; ?>>
                <label for="ktp" style="margin:0; cursor:pointer;">Fotokopi KTP Orang Tua (Ayah & Ibu)</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" name="bawa_rapot_fc" id="rapot" <?php echo (isset($data['bawa_rapot_fc']) && $data['bawa_rapot_fc'] == 'Ya') ? 'checked' : ''; ?>>
                <label for="rapot" style="margin:0; cursor:pointer;">Fotokopi Raport SMP (Semester 1 - 6)</label>
            </div>

            <div class="form-group" style="margin-top: 15px;">
                <label>Catatan Tambahan (Opsional)</label>
                <textarea name="catatan_du" rows="3" placeholder="Contoh: KK belum dilegalisir, janji menyusul besok..."><?php echo htmlspecialchars($data['catatan_du'] ?? ''); ?></textarea>
            </div>

            <button type="submit" name="simpan_du" class="btn-submit">💾 Simpan & Konfirmasi Daftar Ulang</button>
            <a href="index.php" class="btn-kembali">🔙 Batal / Kembali</a>
        </form>
    </div>
</body>
</html>