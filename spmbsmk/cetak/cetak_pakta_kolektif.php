<?php
// ==========================================
// SECURITY LAYER: SECURE SESSION START
// ==========================================
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_use_only_cookies', 1);
    session_start();
}
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['login'])) {
    die("Akses ditolak. Silakan login sebagai admin.");
}

require_once __DIR__ . '/config/koneksi.php';

// Hanya fokus pada filter Gelombang, abaikan Tab jurusan agar AKL dan MPLB keambil semua
$gel_aktif = isset($_GET['gel']) ? preg_replace('/[^a-zA-Z0-9]/', '', $_GET['gel']) : 'Semua';

$filter_gel = ($gel_aktif == 'Semua') ? "" : " WHERE gelombang = '$gel_aktif'";
$label_gel = ($gel_aktif == 'Semua') ? "Semua Gelombang" : (($gel_aktif == 'Cadangan') ? "Cadangan / Antrian" : "Gelombang " . $gel_aktif);

// Mengambil SEMUA siswa dari tabel, diurutkan berdasarkan jurusan dulu, baru nama abjad
$query = mysqli_query($conn, "SELECT * FROM pendaftar $filter_gel ORDER BY pilihan_jurusan ASC, nama_lengkap ASC");

if (mysqli_num_rows($query) == 0) {
    echo "<script>alert('Tidak ada data siswa pada filter ini!'); window.close();</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Pakta Integritas Kolektif - <?php echo $label_gel; ?></title>
    <link rel="icon" type="image/x-icon" href="logo/logosmkpb.png">
    <style>
        /* Pengaturan ukuran kertas dan font untuk cetak (A4) */
        @page { size: A4; margin: 2cm; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; color: #000; background: #e2e8f0; margin: 0; padding: 20px 0; }
        
        /* Container untuk tampilan layar */
        .kertas-a4 { background: #fff; width: 210mm; min-height: 297mm; margin: 0 auto 20px auto; padding: 2cm; box-sizing: border-box; box-shadow: 0 4px 10px rgba(0,0,0,0.1); position: relative; }
        
        .judul-surat { text-align: center; font-weight: bold; margin-bottom: 25px; line-height: 1.3; }
        .judul-surat span { text-decoration: underline; font-size: 14pt; }
        
        .biodata-grid { display: grid; grid-template-columns: 180px 10px 1fr; margin-bottom: 5px; }
        .list-poin { margin-top: 15px; margin-bottom: 20px; padding-left: 20px; text-align: justify; }
        .list-poin li { margin-bottom: 10px; }
        
        /* Tombol Cetak (Hanya tampil di layar, hilang saat diprint) */
        .btn-print-area { text-align: center; margin-bottom: 20px; position: sticky; top: 10px; z-index: 100; }
        .btn { padding: 12px 25px; font-size: 15px; font-weight: bold; border-radius: 8px; cursor: pointer; text-decoration: none; border: none; color: white; display: inline-block; margin: 0 5px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .btn-cetak { background: #eab308; color: #000; }
        .btn-kembali { background: #64748b; }
        
        /* Tabel Tanda Tangan Sesuai Desain */
        .ttd-table { width: 100%; border-collapse: collapse; text-align: left; margin-top: 30px; }
        .ttd-table td { vertical-align: top; }
        
        /* CSS KHUSUS PRINT */
        @media print {
            body { background: #fff; padding: 0; }
            .kertas-a4 { box-shadow: none; padding: 0; width: 100%; min-height: auto; margin: 0; }
            .btn-print-area { display: none; }
            @page { margin: 1.5cm; }
            
            /* INI YANG MEMBUAT SETIAP SISWA DI-PRINT DI HALAMAN BARU */
            .page-break { page-break-after: always; }
            .page-break:last-child { page-break-after: auto; }
        }
    </style>
</head>
<body>

    <div class="btn-print-area">
        <button onclick="window.print()" class="btn btn-cetak">🖨️ Cetak Seluruh Pakta (<?php echo mysqli_num_rows($query); ?> Siswa)</button>
        <button onclick="window.close()" class="btn btn-kembali">Tutup Halaman</button>
    </div>

    <?php 
    while ($data = mysqli_fetch_assoc($query)): 
        // Deteksi singkatan jurusan masing-masing siswa
        $jurusan_siswa = ($data['pilihan_jurusan'] == 'Akuntansi dan Keuangan Lembaga') ? 'Akuntansi dan Keuangan Lembaga (AKL)' : 'Manajemen Perkantoran dan Layanan Bisnis (MPLB)';
    ?>
    <div class="kertas-a4 page-break">
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
            <div class="biodata-grid"><div>Nama Lengkap</div><div>:</div><div><b><?php echo htmlspecialchars($data['nama_lengkap'], ENT_QUOTES, 'UTF-8'); ?></b></div></div>
            <div class="biodata-grid"><div>NISN</div><div>:</div><div><?php echo htmlspecialchars($data['nisn'], ENT_QUOTES, 'UTF-8'); ?></div></div>
            <div class="biodata-grid"><div>Asal Sekolah</div><div>:</div><div><?php echo htmlspecialchars($data['asal_sekolah'], ENT_QUOTES, 'UTF-8'); ?></div></div>
            <div class="biodata-grid"><div>Pilihan Jurusan</div><div>:</div><div><b><?php echo $jurusan_siswa; ?></b></div></div>
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

        <p style="text-align: justify; margin-bottom: 3px;">
            Demikian Pakta Integritas ini kami buat dengan sebenar-benarnya dalam keadaan sadar dan sehat jasmani maupun rohani untuk digunakan sebagaimana mestinya. Kami memahami bahwa kesepakatan ini mengikat secara moral, administratif, dan hukum.
        </p>

        <div style="text-align: right; margin-bottom: 5px;">
            Jakarta, .................................. <?php echo date('Y'); ?>
        </div>

        <p style="margin-bottom: 1px;">Mengetahui dan Menyetujui,</p>
        <table class="ttd-table">
            <tr>
                <td style="width: 35%; padding-bottom: 10px;">Orang Tua / Wali</td>
                <td style="width: 25%;"></td>
                <td style="width: 50%; padding-bottom: 40px;">Calon Siswa</td>
            </tr>
            <tr>
                <td style="height: 80px; vertical-align: bottom;">
                    <!-- <span style="font-size: 10px; color: #666; border: 1px dashed #999; padding: 2px 10px; display: inline-block; margin-bottom: 5px;">Meterai Rp10.000</span><br> -->
                    (......................................................)<br>
                    Nama Jelas Orang Tua/Wali
                </td>
                <td></td>
                <td style="height: 80px; vertical-align: bottom;">
                    <br><br>
                    (<b><?php echo htmlspecialchars($data['nama_lengkap'], ENT_QUOTES, 'UTF-8'); ?></b>)<br>
                    Nama Jelas Calon Siswa
                </td>
            </tr>
            <tr>
                <td colspan="3" style="height: 2px;"></td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: center; padding-bottom: 20px; padding-top: 20px;">
                    Kepala Sekolah<br><br><br><br><br>
                    <b>H. Hery Darda, S.Sos, M.Pd</b><br>
                    NIP. ........................................
                </td>
            </tr>
        </table>
    </div>
    <?php endwhile; ?>

</body>
</html>