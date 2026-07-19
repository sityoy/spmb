<?php
session_start();
if (!isset($_SESSION['login'])) { 
    header("Location: ../login.php"); 
    exit; 
}
include '../koneksi.php';

$gel = isset($_GET['gel']) ? mysqli_real_escape_string($conn, $_GET['gel']) : 'Semua';
$sql_gel = ($gel == 'Semua') ? "" : "WHERE p.gelombang = '$gel'";

// Set nama file dinamis
$nama_file = "Data_SPMB_Lengkap_Dengan_Rank_" . ($gel == 'Semua' ? 'Semua_Gelombang' : 'Gel_'.$gel) . "_" . date('Ymd_His') . ".xls";

// Header wajib untuk force download Excel
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=$nama_file");
header("Pragma: no-cache");
header("Expires: 0");

$query = "SELECT p.*, pd.* FROM pendaftar p 
          LEFT JOIN pendaftar_detail pd ON p.id = pd.pendaftar_id 
          $sql_gel";
$result = mysqli_query($conn, $query);

// ================================================================
// LOGIKA PEMERINGKATAN (70% SKL + 30% TKA) & SORTING
// ================================================================
$pendaftar = [];
while($row = mysqli_fetch_assoc($result)) {
    // 1. Ambil nilai asli, ubah koma ke titik, lalu jadikan float untuk dihitung
    $n_skl_clean = (float) str_replace(',', '.', $row['nilai_skl']);
    $n_tka_clean = (float) str_replace(',', '.', $row['nilai_tka']);
    
    // 2. HITUNG RUMUS & BOBOT
    $bobot_skl = $n_skl_clean * 0.70; // 70% dari Sidanira
    $bobot_tka = $n_tka_clean * 0.30; // 30% dari TKA
    $nilai_akhir = $bobot_skl + $bobot_tka; // Total Akhir
    
    // Simpan hasil hitungan ke dalam array
    $row['n_skl_clean'] = $n_skl_clean;
    $row['n_tka_clean'] = $n_tka_clean;
    $row['bobot_skl'] = $bobot_skl;
    $row['bobot_tka'] = $bobot_tka;
    $row['n_akhir'] = $nilai_akhir;
    
    $pendaftar[] = $row;
}

// Urutkan Data (Sorting)
usort($pendaftar, function($a, $b) {
    if ($a['pilihan_jurusan'] === $b['pilihan_jurusan']) {
        
        $a_lulus = ($a['status_konfirmasi'] == 'LULUS') ? 1 : 0;
        $b_lulus = ($b['status_konfirmasi'] == 'LULUS') ? 1 : 0;
        
        if ($a_lulus === $b_lulus) {
            if ($a['n_akhir'] === $b['n_akhir']) {
                if ($a['n_tka_clean'] === $b['n_tka_clean']) {
                    return $b['n_skl_clean'] <=> $a['n_skl_clean']; 
                }
                return $b['n_tka_clean'] <=> $a['n_tka_clean'];
            }
            return $b['n_akhir'] <=> $a['n_akhir'];
        }
        return $b_lulus <=> $a_lulus;
    }
    return $a['pilihan_jurusan'] <=> $b['pilihan_jurusan'];
});
// ================================================================
?>
<!-- CSS Khusus untuk menjinakkan Excel -->
<style>
    .text-cell { mso-number-format: "\@"; } /* Paksa murni jadi Teks */
    table th { text-align: center; vertical-align: middle; } /* Teks Header ke tengah */
</style>

<table border="1">
    <thead>
        <!-- BARIS HEADER PERTAMA -->
        <tr style="background-color: #f1f5f9; font-weight: bold;">
            <th rowspan="2">No</th>
            <th rowspan="2" style="background-color: #fef08a;">Peringkat (Rank)</th>
            <th rowspan="2">No Pendaftaran</th>
            <th rowspan="2">Nama Lengkap</th>
            <th rowspan="2">NIK</th>
            <th rowspan="2">NISN</th>
            <th rowspan="2">No Sidanira</th>
            <th rowspan="2">Pilihan Jurusan</th>
            <th rowspan="2">Gelombang</th>
            <th rowspan="2">Status Konfirmasi</th>
            <th rowspan="2">Status Daftar Ulang</th>
            <th rowspan="2">Status Jakedu</th>
            <th rowspan="2">Asal Sekolah</th>
            <th rowspan="2">NPSN Sekolah</th>
            <th rowspan="2">Tempat Lahir</th>
            <th rowspan="2">Tanggal Lahir</th>
            <th rowspan="2">Jenis Kelamin</th>
            <th rowspan="2">Agama</th>
            <th rowspan="2">No WA (HP)</th>
            <th rowspan="2">Alamat Domisili</th>
            <th rowspan="2">Kecamatan</th>
            <th rowspan="2">Kelurahan</th>
            <th rowspan="2">Nama Ibu Kandung</th>
            <th rowspan="2">No KK</th>
            <th rowspan="2">Tanggal KK</th>
            <th rowspan="2">Status KJP</th>
            <th rowspan="2">No Rekening KJP</th>
            <th rowspan="2">Kebutuhan Khusus</th>
            <th rowspan="2">Riwayat Penyakit</th>
            
            <!-- GROUP HEADER UNTUK NILAI (Menggabungkan 2 kolom ke samping) -->
            <th colspan="2">Sidanira /<br>SKL</th>
            <th colspan="2">Tes TKA /<br>SKHU</th>
            
            <th rowspan="2" style="background-color: #d1fae5;">Nilai Akhir (Total)</th>
        </tr>
        <!-- BARIS HEADER KEDUA (Pecahan dari Sidanira & TKA) -->
        <tr style="background-color: #f1f5f9; font-weight: bold;">
            <th>Asli</th>
            <th style="background-color: #e0f2fe;">Bobot (70%)</th>
            <th>Asli</th>
            <th style="background-color: #e0f2fe;">Bobot (30%)</th>
        </tr>
    </thead>
    <tbody>
    <?php 
    $no = 1;
    $rank = 1;
    $jurusan_saat_ini = "";

    foreach ($pendaftar as $row) {
        
        if ($jurusan_saat_ini !== $row['pilihan_jurusan']) {
            $jurusan_saat_ini = $row['pilihan_jurusan'];
            $rank = 1; 
        }

        if ($row['status_konfirmasi'] == 'LULUS') {
            $cetak_rank = $rank++;
        } else {
            $cetak_rank = "-"; 
        }

        echo "<tr>";
        echo "<td>".$no++."</td>";
        echo "<td style='text-align: center; font-weight: bold; color: #b91c1c;'>".$cetak_rank."</td>";
        
        echo "<td class='text-cell'>".$row['no_pendaftaran']."</td>";
        echo "<td>".$row['nama_lengkap']."</td>";
        echo "<td class='text-cell'>".$row['nik']."</td>"; 
        echo "<td class='text-cell'>".$row['nisn']."</td>"; 
        echo "<td class='text-cell'>".$row['no_ijazah']."</td>"; 
        echo "<td>".$row['pilihan_jurusan']."</td>";
        echo "<td>".$row['gelombang']."</td>";
        echo "<td>".$row['status_konfirmasi']."</td>";
        echo "<td>".$row['status_daftar_ulang']."</td>";
        echo "<td>".$row['status_jakedu']."</td>";
        echo "<td>".$row['asal_sekolah']."</td>";
        echo "<td class='text-cell'>".$row['npsn_sekolah']."</td>";
        echo "<td>".$row['tempat_lahir']."</td>";
        echo "<td>".$row['tanggal_lahir']."</td>";
        echo "<td>".$row['jenis_kelamin']."</td>";
        echo "<td>".$row['agama']."</td>";
        echo "<td class='text-cell'>".$row['no_whatsapp']."</td>";
        echo "<td>".$row['alamat']."</td>";
        echo "<td>".$row['kecamatan']."</td>";
        echo "<td>".$row['kelurahan']."</td>";
        echo "<td>".$row['nama_ibu']."</td>";
        echo "<td class='text-cell'>".$row['no_kk']."</td>";
        echo "<td>".$row['tanggal_kk']."</td>";
        echo "<td>".$row['status_kjp']."</td>";
        echo "<td class='text-cell'>".$row['no_rek_kjp']."</td>";
        echo "<td>".$row['kebutuhan_khusus']."</td>";
        echo "<td>".$row['riwayat_penyakit']."</td>";
        
        // Kolom Nilai: Sesuai urutan header tingkat
        echo "<td class='text-cell' style='text-align: center;'>".$row['nilai_skl']."</td>";
        echo "<td class='text-cell' style='text-align: center; color: #0369a1;'>".number_format($row['bobot_skl'], 2, '.', '')."</td>";
        
        echo "<td class='text-cell' style='text-align: center;'>".$row['nilai_tka']."</td>";
        echo "<td class='text-cell' style='text-align: center; color: #0369a1;'>".number_format($row['bobot_tka'], 2, '.', '')."</td>";
        
        echo "<td class='text-cell' style='text-align: center; font-weight: bold;'>".number_format($row['n_akhir'], 2, '.', '')."</td>";
        
        echo "</tr>";
    }
    ?>
    </tbody>
</table>