<?php
// Pastikan file ini berada di folder yang sama dengan koneksi.php
require_once __DIR__ . '/config/koneksi.php';

// Menangkap ID atau No Pendaftaran dari URL
$id_pendaftar = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

// Jika menggunakan no_pendaftaran di URL, ubah query WHERE id = '$id...' menjadi WHERE no_pendaftaran = '$id...'
$query = mysqli_query($conn, "SELECT * FROM pendaftar WHERE id = '$id_pendaftar'");

if (mysqli_num_rows($query) == 0) {
    echo "<script>alert('Data pendaftar tidak ditemukan!'); window.close();</script>";
    exit;
}

$data = mysqli_fetch_assoc($query);

// Menentukan nama jurusan lengkap
$jurusan = ($data['pilihan_jurusan'] == 'Akuntansi dan Keuangan Lembaga') ? 'Akuntansi dan Keuangan Lembaga (AKL)' : 'Manajemen Perkantoran dan Layanan Bisnis (MPLB)';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Pakta Integritas - <?php echo htmlspecialchars($data['nama_lengkap']); ?></title>
    <link rel="icon" type="image/x-icon" href="logo/logosmkpb.png">
    <style>
        /* Pengaturan ukuran kertas dan font untuk cetak (A4) */
        @page { size: A4; margin: 2cm; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; color: #000; background: #e2e8f0; margin: 0; padding: 20px 0; }
        
        .kertas-a4 { background: #fff; width: 210mm; min-height: 297mm; margin: 0 auto; padding: 2cm; box-sizing: border-box; box-shadow: 0 4px 10px rgba(0,0,0,0.1); position: relative; }
        
        .judul-surat { text-align: center; font-weight: bold; margin-bottom: 25px; line-height: 1.3; }
        .judul-surat span { text-decoration: underline; font-size: 14pt; }
        
        .biodata-grid { display: grid; grid-template-columns: 180px 10px 1fr; margin-bottom: 5px; }
        .list-poin { margin-top: 15px; margin-bottom: 20px; padding-left: 20px; text-align: justify; }
        .list-poin li { margin-bottom: 10px; }
        
        /* Tombol Cetak (Hanya tampil di layar, hilang saat diprint) */
        .btn-print-area { text-align: center; margin-bottom: 20px; }
        .btn { padding: 10px 20px; font-size: 14px; font-weight: bold; border-radius: 6px; cursor: pointer; text-decoration: none; border: none; color: white; display: inline-block; margin: 0 5px; }
        .btn-cetak { background: #4f46e5; }
        .btn-kembali { background: #64748b; }
        
        /* Tabel Tanda Tangan Sesuai Desain */
        .ttd-table { width: 100%; border-collapse: collapse; text-align: left; margin-top: 30px; }
        .ttd-table td { vertical-align: top; }
        
        @media print {
            body { background: #fff; padding: 0; }
            .kertas-a4 { box-shadow: none; padding: 0; width: 100%; min-height: auto; margin: 0; }
            .btn-print-area { display: none; }
            @page { margin: 1.5cm; }
        }
    </style>
</head>
<body>

    <div class="btn-print-area">
        <button onclick="window.print()" class="btn btn-cetak">🖨️ Cetak Pakta Integritas</button>
        <button onclick="window.close()" class="btn btn-kembali">Tutup Halaman</button>
    </div>

    <div class="kertas-a4">
        <div class="judul-surat">
            <span>SURAT PERNYATAAN / PAKTA INTEGRITAS</span><br>
            PESERTA DIDIK BARU DAN ORANG TUA/WALI<br>
            PROGRAM SEKOLAH GRATIS SMKS PERMATA BUNDA I JAKARTA
        </div>

        <p style="margin-top:0;">Yang bertanda tangan di bawah ini, saya selaku Orang Tua / Wali dari Calon Peserta Didik Baru:</p>

        <b>I. Biodata Orang Tua / Wali</b>
        <div style="margin-top: 5px; margin-bottom: 15px;">
            <div class="biodata-grid"><div>Nama Lengkap</div><div>:</div><div>............................................................................................................</div></div>
            <div class="biodata-grid"><div>NIK (No. KTP)</div><div>:</div><div>............................................................................................................</div></div>
            <div class="biodata-grid"><div>Pekerjaan</div><div>:</div><div>............................................................................................................</div></div>
            <div class="biodata-grid"><div>Alamat Lengkap</div><div>:</div><div>............................................................................................................</div></div>
            <div class="biodata-grid"><div>No. HP / WhatsApp</div><div>:</div><div>............................................................................................................</div></div>
        </div>

        <b>II. Biodata Calon Peserta Didik</b>
        <div style="margin-top: 5px; margin-bottom: 15px;">
            <div class="biodata-grid"><div>Nama Lengkap</div><div>:</div><div><b><?php echo htmlspecialchars($data['nama_lengkap']); ?></b></div></div>
            <div class="biodata-grid"><div>NISN</div><div>:</div><div><?php echo htmlspecialchars($data['nisn']); ?></div></div>
            <div class="biodata-grid"><div>Asal Sekolah</div><div>:</div><div><?php echo htmlspecialchars($data['asal_sekolah']); ?></div></div>
            <div class="biodata-grid"><div>Pilihan Jurusan</div><div>:</div><div><?php echo $jurusan; ?></div></div>
        </div>

        <p style="text-align: justify; margin-top: 20px;">
            Sehubungan dengan diterimanya anak saya sebagai peserta didik baru di <b>SMKS Permata Bunda I Jakarta</b> melalui jalur <b>Program Sekolah Swasta Gratis</b>, dengan kesadaran penuh dan tanpa paksaan dari pihak mana pun, saya bersama anak saya menyatakan hal-hal sebagai berikut:
        </p>

        <ol class="list-poin">
            <li><b>Keabsahan Data & Dokumen:</b> Semua data dan dokumen persyaratan (Sidanira, Ijazah/SKL, KK, KTP, dan dokumen pendukung lainnya) yang diserahkan kepada pihak sekolah adalah <b>BENAR dan ASLI</b>. Apabila di kemudian hari terbukti ada pemalsuan data, kami bersedia menerima sanksi pembatalan kelulusan.</li>
            <li><b>Pembebasan Biaya Pendidikan & Bebas Pungutan Liar:</b> Kami memahami bahwa program ini <b>TIDAK DIPUNGUT BIAYA</b> pendidikan sekolah (Gratis Uang Pangkal/Gedung dan Gratis SPP Bulanan). Kami juga bersedia untuk <b>tidak melayani, tidak memberikan imbalan, dan tidak melakukan praktik pungutan liar (suap)</b> dalam bentuk apa pun kepada panitia, guru, staf, atau pihak mana pun yang mengatasnamakan sekolah terkait proses penerimaan ini.</li>
            <li><b>Tanggung Jawab Kebutuhan Personal:</b> Kami menyadari bahwa pembebasan biaya ini mencakup biaya operasional sekolah. Adapun biaya yang sifatnya murni kebutuhan operasional pribadi siswa (seperti uang saku, ongkos transportasi, alat tulis pribadi, maupun kelengkapan seragam/atribut yang menjadi hak milik pribadi siswa) tetap menjadi komitmen dan tanggung jawab kami secara mandiri selaku orang tua/wali.</li>
            <li><b>Komitmen Belajar & Tata Tertib:</b> Anak saya akan belajar dengan sungguh-sungguh, berakhlak mulia, menjaga nama baik almamater, serta tunduk dan patuh pada seluruh tata tertib dan aturan kedisiplinan yang berlaku di SMKS Permata Bunda I Jakarta. Apabila anak saya melakukan pelanggaran berat, kami siap menerima sanksi akademis hingga sanksi dikembalikan kepada orang tua (Dikeluarkan).</li>
            <li><b>Penyelesaian Masa Pendidikan (Anti Putus Sekolah):</b> Mengingat anak saya menempati kuota prioritas <i>Sekolah Gratis</i> yang sangat terbatas, kami <b>berkomitmen agar anak saya menyelesaikan pendidikan hingga lulus (Kelas XII)</b>. Kami tidak akan mengundurkan diri atau pindah sekolah di tengah masa pendidikan tanpa alasan yang sangat mendesak dan sah secara hukum (misalnya: pindah domisili antar-provinsi yang tidak memungkinkan akses ke sekolah).</li>
            <li><b>Sanksi Pengunduran Diri Sepihak:</b> Apabila di kemudian hari anak saya mengundurkan diri atas kemauan sendiri tanpa alasan yang sah, atau dikeluarkan karena pelanggaran berat tata tertib, maka kami bersedia <b>mengganti seluruh kerugian biaya pendidikan</b> yang selama ini telah disubsidi/digratiskan oleh pihak Sekolah/Yayasan, agar dana tersebut dapat dialihkan untuk membantu kelangsungan pendidikan siswa lain.</li>
            <li><b>Dukungan Penuh Orang Tua:</b> Saya selaku Orang Tua/Wali akan mendukung penuh proses kegiatan belajar mengajar, aktif berkomunikasi dengan pihak sekolah, memastikan tingkat kehadiran anak saya di sekolah, serta turut mengawasi pergaulan anak di luar jam sekolah.</li>
        </ol>

        <p style="text-align: justify; margin-bottom: 25px;">
            Demikian Pakta Integritas ini kami buat dengan sebenar-benarnya dalam keadaan sadar dan sehat jasmani maupun rohani untuk digunakan sebagaimana mestinya. Kami memahami bahwa kesepakatan ini mengikat secara moral, administratif, dan hukum.
        </p>

        <div style="text-align: right; margin-bottom: 10px;">
            Jakarta, .................................. <?php echo date('Y'); ?>
        </div>

        <p style="margin-bottom: 5px;">Mengetahui dan Menyetujui,</p>
        <table class="ttd-table">
            <tr>
                <td style="width: 35%; padding-bottom: 10px;">Orang Tua / Wali</td>
                <td style="width: 30%;"></td>
                <td style="width: 35%; padding-bottom: 10px;">Calon Peserta Didik Baru</td>
            </tr>
            <tr>
                <td style="height: 80px; vertical-align: bottom;">
                    <span style="font-size: 10px; color: #666; border: 1px dashed #999; padding: 2px 10px; display: inline-block; margin-bottom: 5px;">Meterai Rp10.000</span><br>
                    (......................................................)<br>
                    Nama Jelas Orang Tua/Wali
                </td>
                <td></td>
                <td style="height: 80px; vertical-align: bottom;">
                    <br><br>
                    (......................................................)<br>
                    Nama Jelas Calon Peserta Didik Baru
                </td>
            </tr>
            <tr>
                <td colspan="3" style="height: 30px;"></td> <!-- Spasi kosong -->
            </tr>
            <tr>
                <td></td>
                <td style="text-align: left;">Kepala Sekolah</td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td style="height: 90px; vertical-align: bottom; text-align: left;">
                    <b>H. Hery Darda, S.Sos, M.Pd</b><br>
                    NIP. ........................................
                </td>
                <td></td>
            </tr>
        </table>

    </div>

</body>
</html>