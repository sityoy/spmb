<?php
session_start();
if (!isset($_SESSION['login'])) { header("Location: ../login.php"); exit; }
include '../koneksi.php';

if (!isset($_GET['id'])) { header("Location: index.php"); exit; }
$id = mysqli_real_escape_string($conn, $_GET['id']);

$query_siswa = mysqli_query($conn, "SELECT p.*, d.* FROM pendaftar p LEFT JOIN daftar_ulang d ON p.id = d.id_pendaftar WHERE p.id = '$id'");
$data = mysqli_fetch_assoc($query_siswa);

if (!$data || $data['status_daftar_ulang'] != 'Sudah') { 
    echo "<script>alert('Data Daftar Ulang tidak ditemukan atau siswa belum daftar ulang!'); window.close();</script>"; 
    exit; 
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tanda Terima Daftar Ulang - <?php echo htmlspecialchars($data['nama_lengkap']); ?></title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 14px; color: #000; padding: 20px; }
        .kertas { max-width: 800px; margin: 0 auto; border: 1px solid #000; padding: 30px; }
        .kop { text-align: center; margin-bottom: 20px; border-bottom: 4px double #000; padding-bottom: 15px; }
        .kop h2, .kop h3 { margin: 5px 0; }
        .title { text-align: center; font-size: 18px; font-weight: bold; text-decoration: underline; margin-bottom: 20px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .tb-identitas td { padding: 8px 5px; vertical-align: top; }
        .tb-identitas td:first-child { width: 25%; font-weight: bold; }
        .tb-identitas td:nth-child(2) { width: 2%; }
        
        .tb-berkas th, .tb-berkas td { border: 1px solid #000; padding: 8px; text-align: center; }
        .tb-berkas th { background-color: #f2f2f2; }
        .tb-berkas td:nth-child(2) { text-align: left; }
        
        .ttd-box { display: flex; justify-content: space-between; margin-top: 40px; text-align: center; }
        .ttd-space { height: 80px; }
        
        @media print { 
            body { padding: 0; background: none; }
            .kertas { border: none; padding: 0; margin: 0; max-width: 100%; }
            .no-print { display: none; }
        }
        
        .btn-print { display: block; margin: 0 auto 20px auto; padding: 10px 20px; background: #4f46e5; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 14px; }
    </style>
</head>
<body>

    <button class="no-print btn-print" onclick="window.print()">🖨️ Cetak Bukti Pendaftaran Ulang</button>

    <div class="kertas">
        <div class="kop">
            <h2>PANITIA SELEKSI PENERIMAAN SISWA BARU</h2>
            <h3>SMKS PERMATA BUNDA I JAKARTA</h3>
            <p style="margin: 5px 0 0 0; font-size: 12px;">Tahun Pelajaran 2026/2027</p>
        </div>

        <div class="title">TANDA TERIMA DAFTAR ULANG</div>

        <table class="tb-identitas">
            <tr>
                <td>Nomor Pendaftaran</td>
                <td>:</td>
                <td><b><?php echo htmlspecialchars($data['no_pendaftaran']); ?></b></td>
            </tr>
            <tr>
                <td>Nama Lengkap</td>
                <td>:</td>
                <td><?php echo htmlspecialchars($data['nama_lengkap']); ?></td>
            </tr>
            <tr>
                <td>NISN</td>
                <td>:</td>
                <td><?php echo htmlspecialchars($data['nisn']); ?></td>
            </tr>
            <tr>
                <td>Asal Sekolah</td>
                <td>:</td>
                <td><?php echo htmlspecialchars($data['asal_sekolah']); ?></td>
            </tr>
            <tr>
                <td>Program Keahlian</td>
                <td>:</td>
                <td><b><?php echo htmlspecialchars($data['pilihan_jurusan']); ?></b></td>
            </tr>
            <tr>
                <td>Ukuran Seragam / Baju</td>
                <td>:</td>
                <td><b><?php echo htmlspecialchars($data['ukuran_baju'] ?? 'Belum Diinput'); ?></b></td>
            </tr>
        </table>

        <p>Telah melengkapi dan menyerahkan berkas persyaratan daftar ulang sebagai berikut:</p>
        
        <table class="tb-berkas">
            <thead>
                <tr>
                    <th style="width: 10%;">No</th>
                    <th style="width: 70%;">Jenis Berkas Dokumen Fisik</th>
                    <th style="width: 20%;">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>SKL / Ijazah Asli</td>
                    <td><?php echo (isset($data['bawa_skl_asli']) && $data['bawa_skl_asli'] == 'Ya') ? '✔️ Sesuai' : '❌ Kurang'; ?></td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Fotokopi Kartu Keluarga (KK)</td>
                    <td><?php echo (isset($data['bawa_kk_fc']) && $data['bawa_kk_fc'] == 'Ya') ? '✔️ Sesuai' : '❌ Kurang'; ?></td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Fotokopi Akte Kelahiran</td>
                    <td><?php echo (isset($data['bawa_akte_fc']) && $data['bawa_akte_fc'] == 'Ya') ? '✔️ Sesuai' : '❌ Kurang'; ?></td>
                </tr>
                <tr>
                    <td>4</td>
                    <td>Fotokopi KTP Orang Tua (Ayah & Ibu)</td>
                    <td><?php echo (isset($data['bawa_ktp_ortu_fc']) && $data['bawa_ktp_ortu_fc'] == 'Ya') ? '✔️ Sesuai' : '❌ Kurang'; ?></td>
                </tr>
                <tr>
                    <td>5</td>
                    <td>Fotokopi Raport SMP (Semester 1 - 6)</td>
                    <td><?php echo (isset($data['bawa_rapot_fc']) && $data['bawa_rapot_fc'] == 'Ya') ? '✔️ Sesuai' : '❌ Kurang'; ?></td>
                </tr>
            </tbody>
        </table>
        
        <?php if (!empty($data['catatan_du'])): ?>
            <p><b>Catatan Panitia:</b> <br> <i><?php echo htmlspecialchars($data['catatan_du']); ?></i></p>
        <?php endif; ?>

        <div class="ttd-box">
            <div style="width: 40%;">
                <p>Menyerahkan,</p>
                <p>Calon Siswa / Orang Tua Wali</p>
                <div class="ttd-space"></div>
                <p><b>(_________________________)</b></p>
            </div>
            <div style="width: 40%;">
                <p>Jakarta, <?php echo date('d-m-Y', strtotime($data['tanggal_du'])); ?></p>
                <p>Panitia Penerimaan (Admin)</p>
                <div class="ttd-space"></div>
                <p><b>(_________________________)</b></p>
            </div>
        </div>
    </div>

</body>
</html>