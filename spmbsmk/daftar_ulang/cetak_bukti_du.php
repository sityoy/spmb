<?php
session_start();
if (!isset($_SESSION['login'])) { header("Location: ../login.php"); exit; }
include '../koneksi.php';

if (!isset($_GET['id'])) { header("Location: index.php"); exit; }
$id = mysqli_real_escape_string($conn, $_GET['id']);

// Mengambil data gabungan siswa dan pendaftaran ulang
$query = mysqli_query($conn, "SELECT p.*, d.* FROM pendaftar p JOIN daftar_ulang d ON p.id = d.id_pendaftar WHERE p.id = '$id'");
$data = mysqli_fetch_assoc($query);

if (!$data) { echo "Data daftar ulang tidak ditemukan!"; exit; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Daftar Ulang - <?php echo $data['nama_lengkap']; ?></title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; color: #000; padding: 20px; max-width: 800px; margin: auto; }
        .kop { text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        .kop h2, .kop h3, .kop p { margin: 3px 0; }
        .title { text-align: center; font-size: 18px; font-weight: bold; text-decoration: underline; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table td { padding: 8px; vertical-align: top; font-size: 14px; }
        .tabel-berkas { width: 100%; border: 1px solid #000; }
        .tabel-berkas th, .tabel-berkas td { border: 1px solid #000; padding: 8px; text-align: left; }
        .tabel-berkas th { background: #f0f0f0; }
        .ttd-box { width: 100%; display: flex; justify-content: space-between; margin-top: 40px; }
        .ttd { text-align: center; width: 250px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <button class="no-print" onclick="window.print()" style="margin-bottom:20px; padding:10px; background:#4f46e5; color:white; border:none; cursor:pointer;">🖨️ Cetak Bukti</button>

    <div class="kop">
        <h2>PANITIA PENERIMAAN PESERTA DIDIK BARU</h2>
        <h3>SMKS PERMATA BUNDA I JAKARTA</h3>
        <p>Jl. Jamblang Raya No. 2 LM  | Telp: (021) 6318076</p>
    </div>

    <div class="title">BUKTI SAH DAFTAR ULANG SISWA BARU</div>

    <table>
        <tr><td style="width: 200px;">No. Pendaftaran</td><td>: <b><?php echo $data['no_pendaftaran']; ?></b></td></tr>
        <tr><td>Nama Lengkap</td><td>: <?php echo $data['nama_lengkap']; ?></td></tr>
        <tr><td>NISN</td><td>: <?php echo $data['nisn']; ?></td></tr>
        <tr><td>Kompetensi Keahlian</td><td>: <b><?php echo $data['pilihan_jurusan']; ?></b></td></tr>
        <tr><td>Jalur Pendaftaran</td><td>: <b>Gelombang <?php echo $data['gelombang']; ?></b></td></tr> <tr><td>Tanggal Daftar Ulang</td><td>: <?php echo date('d-m-Y H:i', strtotime($data['tanggal_du'])); ?> WIB</td></tr>
        <tr><td>Ukuran Seragam</td><td>: <b><?php echo $data['ukuran_baju']; ?></b></td></tr>
    </table>

    <p style="font-weight: bold; margin-bottom: 5px;">Status Penyerahan Berkas Fisik:</p>
    <table class="tabel-berkas">
        <tr>
            <th style="width: 5%;">No</th>
            <th style="width: 65%;">Nama Berkas Dokumen</th>
            <th style="width: 30%; text-align: center;">Status Penyerahan</th>
        </tr>
        <tr><td>1</td><td>SKL / Ijazah Asli</td><td style="text-align: center;"><?php echo ($data['bawa_skl_asli']=='Ya')?'✅ Diterima':'❌ Belum'; ?></td></tr>
        <tr><td>2</td><td>Fotokopi Kartu Keluarga (KK)</td><td style="text-align: center;"><?php echo ($data['bawa_kk_fc']=='Ya')?'✅ Diterima':'❌ Belum'; ?></td></tr>
        <tr><td>3</td><td>Fotokopi Akte Kelahiran</td><td style="text-align: center;"><?php echo ($data['bawa_akte_fc']=='Ya')?'✅ Diterima':'❌ Belum'; ?></td></tr>
        <tr><td>4</td><td>Fotokopi KTP Orang Tua</td><td style="text-align: center;"><?php echo ($data['bawa_ktp_ortu_fc']=='Ya')?'✅ Diterima':'❌ Belum'; ?></td></tr>
        <tr><td>5</td><td>Fotokopi Raport SMP (Semester 1 - 6)</td><td style="text-align: center;"><?php echo ($data['bawa_rapot_fc']=='Ya')?'✅ Diterima':'❌ Belum'; ?></td></tr>
    </table>

    <?php if(!empty($data['catatan_du'])): ?>
    <p><b>Catatan Panitia:</b> <i><?php echo $data['catatan_du']; ?></i></p>
    <?php endif; ?>

    <p style="font-size: 13px; font-style: italic; margin-top:20px;">*Bukti ini harap disimpan oleh wali murid sebagai tanda sah telah melakukan daftar ulang di SMKS Permata Bunda I Jakarta.</p>

    <div class="ttd-box">
        <div class="ttd">
            <p>Orang Tua / Wali Murid,</p>
            <br><br><br><br>
            <p>( .............................................. )</p>
        </div>
        <div class="ttd">
            <p>Jakarta, <?php echo date('d M Y', strtotime($data['tanggal_du'])); ?><br>Panitia Penerimaan,</p>
            <br><br><br>
            <!-- <p><b>( <?php echo isset($_SESSION['admin_user']) ? $_SESSION['admin_user'] : 'Admin SPMB'; ?> )</b></p> -->
            <p>( .............................................. )</p>
        </div>
    </div>
</body>
</html>