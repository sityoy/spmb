<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['login'])) {
    die("Akses Ditolak. Silakan login sebagai admin.");
}

include 'koneksi.php';

$gel = isset($_GET['gel']) ? mysqli_real_escape_string($conn, $_GET['gel']) : 'Semua';

$filter_and = ($gel == 'Semua') ? "" : "AND gelombang = '$gel'";
$label_gel  = ($gel == 'Semua') ? "Semua Gelombang" : (($gel == 'Cadangan') ? "Cadangan / Antrian" : "Gelombang " . $gel);

// Rumus Nilai untuk sorting
$rumus_nilai = "((nilai_skl * 0.70) + (nilai_tka * 0.30))";
$order_logic = "ORDER BY CASE status_konfirmasi WHEN 'LULUS' THEN 1 WHEN 'Menunggu' THEN 2 WHEN 'Tidak Jadi' THEN 3 END ASC, nilai_akhir_sql DESC, nama_lengkap ASC";

$q_akl = mysqli_query($conn, "SELECT *, $rumus_nilai as nilai_akhir_sql FROM pendaftar WHERE pilihan_jurusan = 'Akuntansi dan Keuangan Lembaga' $filter_and $order_logic");
$q_mplb = mysqli_query($conn, "SELECT *, $rumus_nilai as nilai_akhir_sql FROM pendaftar WHERE pilihan_jurusan = 'Manajemen Perkantoran dan Layanan Bisnis' $filter_and $order_logic");

function format_tgl_indo($tanggal) {
    $bulan = array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des');
    $pecahkan = explode('-', date('Y-m-d', strtotime($tanggal)));
    return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
}

function hitung_umur($tanggal_lahir) {
    $tgl_lahir = new DateTime($tanggal_lahir);
    $hari_ini = new DateTime();
    return $hari_ini->diff($tgl_lahir)->y;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Data Full - SPMB</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10px; color: #000; background: #fff; padding: 10px; }
        .kop { text-align: center; margin-bottom: 20px; border-bottom: 3px double #000; padding-bottom: 10px; }
        h2, h3 { margin: 4px 0; letter-spacing: 0.5px; }
        h2 { font-size: 16px; font-weight: bold; text-transform: uppercase; }
        h3 { font-size: 14px; font-weight: bold; text-transform: uppercase; }
        p { margin: 5px 0 0 0; font-size: 11px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; table-layout: fixed; }
        th, td { border: 1px solid #000; padding: 5px 4px; text-align: center; vertical-align: middle; word-wrap: break-word; }
        th { background-color: #f8fafc; font-weight: bold; font-size: 9.5px; }
        
        .text-left { text-align: left; padding-left: 6px; }
        .sub-title { font-size: 12px; font-weight: bold; background: #eee; padding: 5px 10px; margin-bottom: 10px; display: inline-block; border: 1px solid #000; }
        
        .btn-print { margin-bottom: 15px; padding: 10px 20px; background: #4f46e5; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 13px; }
        
        @media print { 
            .no-print { display: none; } 
            body { padding: 0; }
            @page { size: A4 landscape; margin: 1cm; }
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="btn-print no-print">🖨️ Cetak Data Full (A4 Landscape)</button>

    <div class="kop">
        <h2>REKAPITULASI DATA LENGKAP CALON PESERTA DIDIK BARU</h2>
        <h3>SMKS PERMATA BUNDA I JAKARTA</h3>
        <p>Filter Jalur: <b><?php echo $label_gel; ?></b> | Tanggal Cetak: 
        <?php 
        $bulan_indo = array(1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
        echo date('d') . ' ' . $bulan_indo[(int)date('m')] . ' ' . date('Y H:i'); 
        ?> WIB</p>
    </div>

    <?php 
    $jurusan_list = [
        ['title' => 'AKUNTANSI DAN KEUANGAN LEMBAGA (AKL)', 'data' => $q_akl],
        ['title' => 'MANAJEMEN PERKANTORAN DAN LAYANAN BISNIS (MPLB)', 'data' => $q_mplb]
    ];

    foreach($jurusan_list as $index => $j): ?>
        <?php if($index > 0) echo '<div class="page-break"></div>'; ?>
        
        <div class="sub-title">📂 JURUSAN: <?php echo $j['title']; ?></div>
        
        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="width: 2%;">No</th>
                    <th rowspan="2" style="width: 6%;">No Daftar</th>
                    <th rowspan="2" style="width: 10%;">Nama Lengkap</th>
                    <th rowspan="2" style="width: 10%;">Asal Sekolah</th>
                    <th rowspan="2" style="width: 6%;">NISN</th>
                    <th rowspan="2" style="width: 7%;">NIK</th>
                    <th rowspan="2" style="width: 7%;">TTL / Umur</th>
                    <th rowspan="2" style="width: 7%;">Alamat</th>
                    <th rowspan="2" style="width: 7%;">Kelurahan</th>
                    <th rowspan="2" style="width: 7%;">Kecamatan</th>
                    <th rowspan="2" style="width: 6%;">WA / KJP</th>
                    <th colspan="2" style="width: 8%;">Sidanira / SKL</th>
                    <th colspan="2" style="width: 8%;">Tes TKA / SKHU</th>
                    <!-- <th rowspan="2" style="width: 4%;">Test</th> -->
                    <th rowspan="2" style="width: 5%;">Akhir</th>
                    <th rowspan="2" style="width: 6%;">Status</th>
                    <th rowspan="2" style="width: 13%;">Catatan Panitia</th>
                </tr>
                <tr>
                    <th>Asli</th>
                    <th>Bobot(70%)</th>
                    <th>Asli</th>
                    <th>Bobot(30%)</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                if(mysqli_num_rows($j['data']) > 0):
                    while($r = mysqli_fetch_assoc($j['data'])):
                        $asli_skl = (float)$r['nilai_skl'];
                        $bobot_skl = $asli_skl * 0.70;
                        $asli_tka = (float)$r['nilai_tka'];
                        $bobot_tka = $asli_tka * 0.30;

                        $alamat = htmlspecialchars($r['alamat'], ENT_QUOTES, 'UTF-8');
                        $kel = htmlspecialchars($r['kelurahan'], ENT_QUOTES, 'UTF-8');
                        $kec = htmlspecialchars($r['kecamatan'], ENT_QUOTES, 'UTF-8');
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td>`<?php echo $r['no_pendaftaran']; ?>`</td>
                    <td class="text-left"><b><?php echo strtoupper($r['nama_lengkap']); ?></b></td>
                    <td class="text-left"><b><?php echo strtoupper($r['asal_sekolah']); ?></b></td>
                    <td><?php echo $r['nisn']; ?></td>
                    <td><?php echo $r['nik']; ?></td>
                    <td><?php echo format_tgl_indo($r['tanggal_lahir']); ?><br><b>(<?php echo hitung_umur($r['tanggal_lahir']); ?> Thn)</b></td>
                    
                    <td class="text-left">
                        <?php echo $alamat; ?><br>
                        
                    </td>
                    <td class="text-left"><span style="font-size: 8.5px; color: #444; font-weight: bold;"><?php echo $kel; ?><br></td>
                    <td class="text-left"><span style="font-size: 8.5px; color: #444; font-weight: bold;"><?php echo $kec; ?><br></td>

                    <td><?php echo $r['no_whatsapp']; ?><br><?php echo ($r['status_kjp'] == 'Ya') ? '<b>KJP</b>' : '-'; ?></td>
                    <td><?php echo number_format($asli_skl, 2); ?></td>
                    <td style="color: #475569;"><?php echo number_format($bobot_skl, 2); ?></td>
                    <td><?php echo number_format($asli_tka, 2); ?></td>
                    <td style="color: #475569;"><?php echo number_format($bobot_tka, 2); ?></td>
                    <!-- <td><?php echo number_format($r['nilai_test'], 2); ?></td> -->
                    <td><b style="font-size: 11px;"><?php echo number_format($r['nilai_akhir_sql'], 2); ?></b></td>
                    <td><?php echo ($r['status_konfirmasi'] == 'LULUS') ? '<b>LULUS</b>' : $r['status_konfirmasi']; ?></td>
                    <td class="text-left" style="font-size: 9px; line-height: 1.3;"><?php echo !empty($r['catatan_panitia']) ? htmlspecialchars($r['catatan_panitia'], ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                </tr>
                <?php 
                    endwhile; 
                else: 
                ?>
                <tr>
                    <td colspan="19" style="padding: 15px; font-weight: bold;">Tidak ada data pada jurusan ini.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endforeach; ?>

    <div style="float: right; text-align: center; width: 250px; margin-top: 20px; page-break-inside: avoid;">
        Jakarta, <?php echo date('d F Y'); ?><br>
        Ketua Panitia SPMB,<br><br><br><br><br>
        <b>_________________________</b><br>
        NIP.
    </div>

</body>
</html>