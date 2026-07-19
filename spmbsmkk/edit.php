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

// Ambil data pendaftar berdasarkan ID (JOIN dengan pendaftar_detail)
$query = mysqli_query($conn, "SELECT p.*, pd.jenis_kelamin, pd.tanggal_kk, pd.nama_ibu, pd.agama, pd.npsn_sekolah, pd.kebutuhan_khusus 
                              FROM pendaftar p 
                              LEFT JOIN pendaftar_detail pd ON p.id = pd.pendaftar_id 
                              WHERE p.id = '$id'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Data pendaftar tidak ditemukan!'); window.location='admin.php?tab=$tab';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tangkap data Biodata Utama (Tabel pendaftar)
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $nik = mysqli_real_escape_string($conn, $_POST['nik']);
    $no_kk = mysqli_real_escape_string($conn, $_POST['no_kk']);
    $tempat_lahir = mysqli_real_escape_string($conn, $_POST['tempat_lahir']);
    $tanggal_lahir = mysqli_real_escape_string($conn, $_POST['tanggal_lahir']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $kelurahan = mysqli_real_escape_string($conn, $_POST['kelurahan']);
    $kecamatan = mysqli_real_escape_string($conn, $_POST['kecamatan']);
    $nisn = mysqli_real_escape_string($conn, $_POST['nisn']);
    $nopes = mysqli_real_escape_string($conn, $_POST['no_ijazah']);
    $no_whatsapp = mysqli_real_escape_string($conn, $_POST['no_whatsapp']);
    $pilihan_jurusan = mysqli_real_escape_string($conn, $_POST['pilihan_jurusan']);
    $asal_sekolah = mysqli_real_escape_string($conn, $_POST['asal_sekolah']);
    $riwayat_penyakit = mysqli_real_escape_string($conn, $_POST['riwayat_penyakit']);
    
    // Tangkap data Relasi (Tabel pendaftar_detail)
    $jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
    $agama = mysqli_real_escape_string($conn, $_POST['agama']);
    $nama_ibu = mysqli_real_escape_string($conn, $_POST['nama_ibu']);
    $tanggal_kk = mysqli_real_escape_string($conn, $_POST['tanggal_kk']);
    $npsn_sekolah = mysqli_real_escape_string($conn, $_POST['npsn_sekolah']);
    $kebutuhan_khusus = mysqli_real_escape_string($conn, $_POST['kebutuhan_khusus']);

    // Tangkap KJP
    $status_kjp = mysqli_real_escape_string($conn, $_POST['status_kjp']);
    $no_rek_kjp = mysqli_real_escape_string($conn, $_POST['no_rek_kjp']);

    // Tangkap data Nilai & Catatan
    $nilai_skl  = mysqli_real_escape_string($conn, $_POST['nilai_skl']);
    $nilai_tka  = mysqli_real_escape_string($conn, $_POST['nilai_tka']);
    $nilai_test = mysqli_real_escape_string($conn, $_POST['nilai_test']);
    $catatan_panitia = mysqli_real_escape_string($conn, $_POST['catatan_panitia']);
    
    // Fungsi bantu untuk cek dan upload file baru
    function handleFileUpload($input_name, $old_file) {
        if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] == 0) {
            $ext = pathinfo($_FILES[$input_name]['name'], PATHINFO_EXTENSION);
            $new_name = $input_name . '_' . time() . '_' . rand(100,999) . '.' . $ext;
            $dest = 'uploads/' . $new_name;
            if (move_uploaded_file($_FILES[$input_name]['tmp_name'], $dest)) {
                return $new_name; 
            }
        }
        return $old_file; 
    }

    // Validasi range input
    if (($nilai_test < 0 && $nilai_test != 0) || $nilai_test > 100) {
        echo "<script>alert('Nilai Uji Kejuruan (Test Panitia) harus 0 atau berada di rentang 0 - 100!');</script>";
    } elseif ($nilai_skl < 0 || $nilai_skl > 100 || $nilai_tka < 0 || $nilai_tka > 100) {
        echo "<script>alert('Nilai SKL dan TKA harus berada di rentang 0 - 100!');</script>";
    } else {
        
        // Proses semua file (Lengkap)
        $f_ijazah = handleFileUpload('file_ijazah', $data['file_ijazah']);
        $f_tka    = handleFileUpload('file_tka', $data['file_tka']);
        $f_kk     = handleFileUpload('file_kk', $data['file_kk']);
        $f_akte   = handleFileUpload('file_akte', $data['file_akte']);
        $f_ktp_bapak = handleFileUpload('file_ktp_bapak', $data['file_ktp_bapak']);
        $f_ktp_ibu   = handleFileUpload('file_ktp_ibu', $data['file_ktp_ibu']);
        $f_sptjm  = handleFileUpload('file_sptjm', $data['file_sptjm']);
        $f_kjp    = handleFileUpload('file_tabungan_kjp', $data['file_tabungan_kjp']);

        // 1. UPDATE TABEL UTAMA (pendaftar)
        $update_utama = mysqli_query($conn, "UPDATE pendaftar SET 
            nama_lengkap = '$nama_lengkap', nik = '$nik', no_kk = '$no_kk',
            tempat_lahir = '$tempat_lahir', tanggal_lahir = '$tanggal_lahir',
            alamat = '$alamat', kelurahan = '$kelurahan', kecamatan = '$kecamatan',
            nisn = '$nisn', no_ijazah = '$nopes', asal_sekolah = '$asal_sekolah',
            no_whatsapp = '$no_whatsapp', pilihan_jurusan = '$pilihan_jurusan',
            riwayat_penyakit = '$riwayat_penyakit', status_kjp = '$status_kjp', no_rek_kjp = '$no_rek_kjp',
            nilai_skl = '$nilai_skl', nilai_tka = '$nilai_tka', nilai_test = '$nilai_test',
            catatan_panitia = '$catatan_panitia',
            file_ijazah = '$f_ijazah', file_tka = '$f_tka', file_kk = '$f_kk', file_akte = '$f_akte',
            file_ktp_bapak = '$f_ktp_bapak', file_ktp_ibu = '$f_ktp_ibu', file_sptjm = '$f_sptjm', file_tabungan_kjp = '$f_kjp'
            WHERE id = '$id'");
        
        // 2. LOGIKA UPDATE / INSERT TABEL DETAIL (ANTI-GAGAL)
        $cek_detail = mysqli_query($conn, "SELECT pendaftar_id FROM pendaftar_detail WHERE pendaftar_id = '$id'");
        if ($cek_detail && mysqli_num_rows($cek_detail) > 0) {
            // Jika data detail sudah ada, lakukan UPDATE
            mysqli_query($conn, "UPDATE pendaftar_detail SET 
                jenis_kelamin = '$jenis_kelamin', agama = '$agama', nama_ibu = '$nama_ibu', 
                tanggal_kk = '$tanggal_kk', npsn_sekolah = '$npsn_sekolah', kebutuhan_khusus = '$kebutuhan_khusus' 
                WHERE pendaftar_id = '$id'");
        } else {
            // Jika data detail BELUM ADA (Kasus hasil Duplikat), lakukan INSERT baru
            mysqli_query($conn, "INSERT INTO pendaftar_detail 
                (pendaftar_id, jenis_kelamin, agama, nama_ibu, tanggal_kk, npsn_sekolah, kebutuhan_khusus) 
                VALUES ('$id', '$jenis_kelamin', '$agama', '$nama_ibu', '$tanggal_kk', '$npsn_sekolah', '$kebutuhan_khusus')");
        }
        
        if ($update_utama) {
            echo "<script>alert('Perubahan Data, Nilai, dan Catatan berhasil disimpan!'); window.location='admin.php?tab=$tab';</script>";
        } else {
            echo "<script>alert('Gagal menyimpan perubahan Utama! Error: " . mysqli_error($conn) . "');</script>";
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
        .form-container { background: #fff; width: 100%; max-width: 1200px; padding: 30px 40px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        h3 { margin-top: 0; color: #1e293b; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; font-size: 22px; }
        
        .grid-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
        @media (max-width: 900px) { .grid-layout { grid-template-columns: 1fr; gap: 20px;} }
        
        .form-group { margin-bottom: 15px; }
        .form-group-inline { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        
        label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;}
        input[type="text"], input[type="number"], input[type="date"], select, textarea { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-size: 14.5px; font-family: inherit; background: #f8fafc; }
        textarea { resize: vertical; min-height: 80px; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,0.1); background: #fff;}
        
        .file-box { background: #f8fafc; padding: 15px; border: 1px dashed #94a3b8; border-radius: 6px; display: flex; flex-direction: column; gap: 8px;}
        .file-box input[type="file"] { font-size: 13px; width: 100%; padding: 0; border: none; background: transparent;}
        .btn-lihat { background: #10b981; color: white; padding: 4px 10px; text-decoration: none; border-radius: 4px; font-size: 11px; display: inline-block; font-weight: bold;}
        
        .btn-submit { background: #4f46e5; color: white; padding: 16px; border: none; border-radius: 8px; cursor: pointer; font-weight: 700; width: 100%; font-size: 16px; margin-top: 30px; transition: 0.2s; text-transform: uppercase; letter-spacing: 1px;}
        .btn-submit:hover { background: #4338ca; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(79,70,229,0.2);}
        .btn-back { display: block; text-align: center; margin-top: 20px; color: #64748b; text-decoration: none; font-size: 14px; font-weight: 600;}
        .btn-back:hover { color: #0f172a; }
        
        .section-title { color: #4f46e5; font-size: 16px; font-weight: 800; margin-bottom: 20px; border-bottom: 2px solid #eef2ff; padding-bottom: 8px; display: flex; align-items: center; gap: 8px;}
        .info-box { background: #f0fdf4; color: #166534; padding: 15px 20px; border-radius: 8px; margin-bottom: 30px; font-size: 14px; border: 1px solid #bbf7d0; font-weight: 500;}
        
        .catatan-box { background: #fff1f2; border: 1px solid #fecdd3; padding: 20px; border-radius: 8px; margin-top: 25px; }
        .catatan-box textarea { border-color: #fda4af; background: #fff; }
        .catatan-box textarea:focus { border-color: #e11d48; box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.1); }
    </style>
</head>
<body>

<div class="form-container">
    <h3>📝 Edit Data Komplit & Evaluasi Siswa</h3>
    
    <div class="info-box">
        💡 <b>Tips Panitia:</b> Semua data pendaftar (Biodata, Berkas, Nilai) telah disatukan di halaman ini. Pastikan Anda menekan tombol <b>Simpan Perubahan</b> di bagian paling bawah setelah selesai mengedit.
    </div>

    <form action="" method="POST" enctype="multipart/form-data">
        
        <div class="grid-layout">
            <!-- KOLOM KIRI: BIODATA & KELUARGA -->
            <div>
                <div class="section-title">👤 A. Identitas Utama & Keluarga</div>
                
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($data['nama_lengkap'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                
                <div class="form-group-inline">
                    <div class="form-group">
                        <label>NIK Siswa</label>
                        <input type="text" name="nik" value="<?php echo htmlspecialchars($data['nik'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>NISN</label>
                        <input type="text" name="nisn" value="<?php echo htmlspecialchars($data['nisn'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                </div>

                <div class="form-group-inline">
                    <div class="form-group">
                        <label>Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" value="<?php echo htmlspecialchars($data['tempat_lahir'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" value="<?php echo htmlspecialchars($data['tanggal_lahir'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                </div>

                <div class="form-group-inline">
                    <div class="form-group">
                        <label>Jenis Kelamin</label>
                        <select name="jenis_kelamin" required>
                            <option value="Laki-laki" <?php if(isset($data['jenis_kelamin']) && $data['jenis_kelamin'] == 'Laki-laki') echo 'selected'; ?>>Laki-laki</option>
                            <option value="Perempuan" <?php if(isset($data['jenis_kelamin']) && $data['jenis_kelamin'] == 'Perempuan') echo 'selected'; ?>>Perempuan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Agama</label>
                        <select name="agama" required>
                            <?php 
                            $agama_list = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];
                            foreach($agama_list as $agm) {
                                $sel = (isset($data['agama']) && $data['agama'] == $agm) ? 'selected' : '';
                                echo "<option value='$agm' $sel>$agm</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>No. Kartu Keluarga (KK)</label>
                    <input type="text" name="no_kk" value="<?php echo htmlspecialchars($data['no_kk'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                
                <div class="form-group-inline">
                    <div class="form-group">
                        <label>Nama Ibu Kandung</label>
                        <input type="text" name="nama_ibu" value="<?php echo htmlspecialchars($data['nama_ibu'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Terbit KK</label>
                        <input type="date" name="tanggal_kk" value="<?php echo htmlspecialchars($data['tanggal_kk'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Alamat Lengkap Domisili</label>
                    <textarea name="alamat" required><?php echo htmlspecialchars($data['alamat'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>

                <div class="form-group-inline">
                    <div class="form-group">
                        <label>Kecamatan</label>
                        <input type="text" name="kecamatan" value="<?php echo htmlspecialchars($data['kecamatan'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Kelurahan / Desa</label>
                        <input type="text" name="kelurahan" value="<?php echo htmlspecialchars($data['kelurahan'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>No. WhatsApp Aktif</label>
                    <input type="text" name="no_whatsapp" value="<?php echo htmlspecialchars($data['no_whatsapp'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
            </div>

            <!-- KOLOM KANAN: AKADEMIK, NILAI & BERKAS -->
            <div>
                <div class="section-title">🏫 B. Data Akademik & Jurusan</div>

                <div class="form-group-inline">
                    <div class="form-group">
                        <label>Asal Sekolah (SMP/MTs)</label>
                        <input type="text" name="asal_sekolah" value="<?php echo htmlspecialchars($data['asal_sekolah'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>NPSN Sekolah</label>
                        <input type="text" name="npsn_sekolah" value="<?php echo htmlspecialchars($data['npsn_sekolah'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Nomor Peserta Sidanira</label>
                    <input type="text" name="no_ijazah" value="<?php echo htmlspecialchars($data['no_ijazah'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <div class="form-group">
                    <label>Pilihan Konsentrasi Keahlian</label>
                    <select name="pilihan_jurusan" required>
                        <option value="Akuntansi dan Keuangan Lembaga" <?php if($data['pilihan_jurusan'] == 'Akuntansi dan Keuangan Lembaga') echo 'selected'; ?>>Akuntansi dan Keuangan Lembaga (AKL)</option>
                        <option value="Manajemen Perkantoran dan Layanan Bisnis" <?php if($data['pilihan_jurusan'] == 'Manajemen Perkantoran dan Layanan Bisnis') echo 'selected'; ?>>Manajemen Perkantoran dan Layanan Bisnis (MPLB)</option>
                    </select>
                </div>

                <div class="form-group-inline">
                    <div class="form-group">
                        <label>Riwayat Penyakit</label>
                        <input type="text" name="riwayat_penyakit" value="<?php echo htmlspecialchars($data['riwayat_penyakit'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Kebutuhan Khusus</label>
                        <select name="kebutuhan_khusus" required>
                            <?php 
                            $keb_list = ['Tidak ada', 'Tunanetra', 'Tunarungu', 'Tunadaksa'];
                            foreach($keb_list as $kb) {
                                $sel = (isset($data['kebutuhan_khusus']) && $data['kebutuhan_khusus'] == $kb) ? 'selected' : '';
                                echo "<option value='$kb' $sel>$kb</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="section-title" style="margin-top: 30px;">📊 C. KJP & Evaluasi Nilai</div>
                
                <div class="form-group-inline">
                    <div class="form-group">
                        <label>Status KJP</label>
                        <select name="status_kjp">
                            <option value="Tidak" <?php if(isset($data['status_kjp']) && $data['status_kjp'] == 'Tidak') echo 'selected'; ?>>Tidak Punya KJP</option>
                            <option value="Ya" <?php if(isset($data['status_kjp']) && $data['status_kjp'] == 'Ya') echo 'selected'; ?>>Punya KJP</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>No. Rekening KJP</label>
                        <input type="text" name="no_rek_kjp" value="<?php echo htmlspecialchars($data['no_rek_kjp'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Kosongkan jika tidak ada">
                    </div>
                </div>

                <div class="form-group-inline">
                    <div class="form-group">
                        <label style="color:#0284c7;">Nilai SIDANIRA Asli</label>
                        <input type="number" name="nilai_skl" step="0.01" min="0" max="100" value="<?php echo $data['nilai_skl']; ?>" oninput="if(this.value > 100) this.value = 100; if(this.value < 0) this.value = 0;" required>
                    </div>
                    <div class="form-group">
                        <label style="color:#0284c7;">Nilai TKA Asli</label>
                        <input type="number" name="nilai_tka" step="0.01" min="0" max="100" value="<?php echo $data['nilai_tka']; ?>" oninput="if(this.value > 100) this.value = 100; if(this.value < 0) this.value = 0;" required>
                    </div>
                </div>

                <div class="form-group">
                    <label style="color:#10b981;">Nilai Ujian / Wawancara (Opsional Panitia)</label>
                    <input type="number" name="nilai_test" step="0.01" min="0" max="100" value="<?php echo $data['nilai_test']; ?>" oninput="if(this.value > 100) this.value = 100; if(this.value < 0) this.value = 0;" required>
                </div>
            </div>
        </div>

        <div class="section-title" style="margin-top: 35px; color:#f59e0b; border-bottom-color:#fef3c7;">📁 D. Perbarui Berkas Dokumen (Kosongkan jika tidak diubah)</div>
        <div class="grid-layout" style="gap: 20px;">
            <div class="file-box">
                <label>1. Scan Ijazah / SK Sidanira</label>
                <div>
                    <?php if(!empty($data['file_ijazah'])): ?><a href="view_file.php?file=<?php echo urlencode($data['file_ijazah']); ?>" target="_blank" class="btn-lihat">👁️ Pratinjau</a><?php else: ?><span style="color:#dc2626; font-size:12px;">❌ Belum ada</span><?php endif; ?>
                </div>
                <input type="file" name="file_ijazah" accept=".pdf,.jpg,.jpeg,.png">
            </div>

            <div class="file-box">
                <label>2. Scan Hasil TKA</label>
                <div>
                    <?php if(!empty($data['file_tka'])): ?><a href="view_file.php?file=<?php echo urlencode($data['file_tka']); ?>" target="_blank" class="btn-lihat">👁️ Pratinjau</a><?php else: ?><span style="color:#dc2626; font-size:12px;">❌ Belum ada</span><?php endif; ?>
                </div>
                <input type="file" name="file_tka" accept=".pdf,.jpg,.jpeg,.png">
            </div>

            <div class="file-box">
                <label>3. Scan Kartu Keluarga (KK)</label>
                <div>
                    <?php if(!empty($data['file_kk'])): ?><a href="view_file.php?file=<?php echo urlencode($data['file_kk']); ?>" target="_blank" class="btn-lihat">👁️ Pratinjau</a><?php else: ?><span style="color:#dc2626; font-size:12px;">❌ Belum ada</span><?php endif; ?>
                </div>
                <input type="file" name="file_kk" accept=".pdf,.jpg,.jpeg,.png">
            </div>

            <div class="file-box">
                <label>4. Scan Akte Kelahiran</label>
                <div>
                    <?php if(!empty($data['file_akte'])): ?><a href="view_file.php?file=<?php echo urlencode($data['file_akte']); ?>" target="_blank" class="btn-lihat">👁️ Pratinjau</a><?php else: ?><span style="color:#dc2626; font-size:12px;">❌ Belum ada</span><?php endif; ?>
                </div>
                <input type="file" name="file_akte" accept=".pdf,.jpg,.jpeg,.png">
            </div>

            <div class="file-box">
                <label>5. Scan KTP Bapak</label>
                <div>
                    <?php if(!empty($data['file_ktp_bapak'])): ?><a href="view_file.php?file=<?php echo urlencode($data['file_ktp_bapak']); ?>" target="_blank" class="btn-lihat">👁️ Pratinjau</a><?php else: ?><span style="color:#dc2626; font-size:12px;">❌ Belum ada</span><?php endif; ?>
                </div>
                <input type="file" name="file_ktp_bapak" accept=".pdf,.jpg,.jpeg,.png">
            </div>

            <div class="file-box">
                <label>6. Scan KTP Ibu</label>
                <div>
                    <?php if(!empty($data['file_ktp_ibu'])): ?><a href="view_file.php?file=<?php echo urlencode($data['file_ktp_ibu']); ?>" target="_blank" class="btn-lihat">👁️ Pratinjau</a><?php else: ?><span style="color:#dc2626; font-size:12px;">❌ Belum ada</span><?php endif; ?>
                </div>
                <input type="file" name="file_ktp_ibu" accept=".pdf,.jpg,.jpeg,.png">
            </div>

            <div class="file-box">
                <label>7. File SPTJM Bermeterai</label>
                <div>
                    <?php if(!empty($data['file_sptjm'])): ?><a href="view_file.php?file=<?php echo urlencode($data['file_sptjm']); ?>" target="_blank" class="btn-lihat">👁️ Pratinjau</a><?php else: ?><span style="color:#dc2626; font-size:12px;">❌ Belum ada</span><?php endif; ?>
                </div>
                <input type="file" name="file_sptjm" accept=".pdf,.jpg,.jpeg,.png">
            </div>

            <div class="file-box">
                <label>8. Scan Tabungan KJP</label>
                <div>
                    <?php if(!empty($data['file_tabungan_kjp'])): ?><a href="view_file.php?file=<?php echo urlencode($data['file_tabungan_kjp']); ?>" target="_blank" class="btn-lihat">👁️ Pratinjau</a><?php else: ?><span style="color:#dc2626; font-size:12px;">❌ Belum ada</span><?php endif; ?>
                </div>
                <input type="file" name="file_tabungan_kjp" accept=".pdf,.jpg,.jpeg,.png">
            </div>
        </div>

        <div class="catatan-box">
            <div class="section-title" style="color:#e11d48; margin-top:0; border-bottom-color:#fecdd3; margin-bottom: 5px;">⚠️ E. Catatan Evaluasi & Peringatan Panitia</div>
            <p style="font-size:13px; color:#9f1239; margin-top:0; margin-bottom: 15px;">*Gunakan kolom ini untuk mencatat berkas yang kurang, buram, peringatan, atau hal lain terkait siswa ini. Catatan ini akan muncul di dashboard Admin dan cetakan Bukti Siswa (jika Lulus/Tidak Lulus).</p>
            
            <div class="form-group" style="margin-bottom:0;">
                <textarea name="catatan_panitia" placeholder="Contoh: File KK buram, minta bawa aslinya waktu daftar ulang..."><?php echo isset($data['catatan_panitia']) ? htmlspecialchars($data['catatan_panitia'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
            </div>
        </div>

        <button type="submit" name="submit" class="btn-submit">💾 Simpan Semua Perubahan</button>
        <a href="admin.php?tab=<?php echo $tab; ?>" class="btn-back">❌ Batal dan Kembali ke Dashboard</a>
    </form>
</div>

<script>
    document.querySelector('form').addEventListener('submit', function() {
        const btn = document.querySelector('.btn-submit');
        btn.disabled = true;
        btn.style.background = '#94a3b8';
        btn.innerText = "⏳ Menyimpan Data & Mengunggah File...";
    });
</script>

</body>
</html>