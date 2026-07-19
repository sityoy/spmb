<?php
session_start();
if (!isset($_SESSION['login'])) { header("Location: login.php"); exit; }
include 'koneksi.php';

// ==========================================
// KONFIGURASI FONNTE API
// ==========================================
$fonnte_token = "eMSgD8XxEXL4rUGzAzDr"; 

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$domain_web = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);

$is_finished = false;
$sukses = 0;
$gagal = 0;
$detail_sukses = [];
$detail_gagal = [];

if (isset($_POST['kirim_broadcast'])) {
    set_time_limit(0); 

    $gel = mysqli_real_escape_string($conn, $_POST['gelombang']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $pesan_mentah = $_POST['pesan'];

    // Filter SQL
    $sql_gel = ($gel == 'Semua') ? "" : " AND gelombang = '$gel'";
    $sql_status = ($status == 'Semua') ? "" : " AND status_konfirmasi = '$status'";

    $query = mysqli_query($conn, "SELECT * FROM pendaftar WHERE 1=1 $sql_gel $sql_status");

    while ($row = mysqli_fetch_assoc($query)) {
        // Pembersihan Nomor WhatsApp
        $no_hp = preg_replace('/[^0-9]/', '', $row['no_whatsapp']);
        $no_wa = (substr($no_hp, 0, 1) == '0') ? '62' . substr($no_hp, 1) : $no_hp;

        if (strlen($no_wa) > 9) {
            $link_bukti = $domain_web . "/bukti.php?no_pendaftaran=" . urlencode($row['no_pendaftaran']);
            
            // Format Alasan Pembatalan
            $alasan = !empty($row['alasan_pembatalan']) ? "\n*Catatan:* " . trim($row['alasan_pembatalan']) : "";
            
            // Konversi teks status agar lebih ramah dibaca
            $teks_status = $row['status_konfirmasi'];
            if($teks_status == 'Tidak Jadi') { $teks_status = "TIDAK LOLOS / BATAL"; }
            
            $pesan_fix = str_replace(
                ['[NAMA]', '[NO_DAFTAR]', '[NISN]', '[JURUSAN]', '[GELOMBANG]', '[STATUS]', '[ALASAN]', '[LINK_BUKTI]'],
                [$row['nama_lengkap'], $row['no_pendaftaran'], $row['nisn'], $row['pilihan_jurusan'], $row['gelombang'], $teks_status, $alasan, $link_bukti],
                $pesan_mentah
            );

            // cURL ke API Fonnte
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.fonnte.com/send',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => array(
                    'target' => $no_wa,
                    'message' => $pesan_fix,
                    'delay' => '15' 
                ),
                CURLOPT_HTTPHEADER => array("Authorization: $fonnte_token"),
            ));

            $response = curl_exec($curl);
            $res_data = json_decode($response, true);
            
            if (isset($res_data['status']) && $res_data['status'] == true) { 
                $sukses++; 
                $detail_sukses[] = $row['nama_lengkap'] . " (" . $no_wa . ")";
            } else { 
                $gagal++; 
                $alasan_gagal = isset($res_data['reason']) ? $res_data['reason'] : 'Nomor tidak valid / Server error';
                $detail_gagal[] = $row['nama_lengkap'] . " - " . $no_wa . " [Error: " . $alasan_gagal . "]";
            }
            curl_close($curl);
            sleep(15); 
        } else {
            $gagal++;
            $detail_gagal[] = $row['nama_lengkap'] . " - " . $row['no_whatsapp'] . " [Error: Format nomor salah/terlalu pendek]";
        }
    }
    
    // Menandai proses selesai agar laporan bisa ditampilkan di HTML
    $is_finished = true;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Broadcast WhatsApp - SPMB</title>
    <link rel="icon" type="image/x-icon" href="logo/logosmkpb.png">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8fafc; padding: 40px; margin: 0; }
        .box { background: white; max-width: 600px; margin: auto; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 30px; }
        h2 { color: #25D366; margin-top: 0; }
        label { font-weight: bold; font-size: 14px; margin-top: 15px; display: block; color: #475569;}
        select, textarea { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 8px; box-sizing: border-box; }
        button { background: #25D366; color: white; border: none; padding: 12px; border-radius: 8px; width: 100%; font-weight: bold; font-size: 16px; margin-top: 20px; cursor: pointer; }
        .var-box { background: #f1f5f9; padding: 10px; border-radius: 8px; font-size: 13px; font-family: monospace; margin-top: 10px; }
        
        .report-box { border: 2px solid #25D366; background: #f0fdf4; }
        .report-title { color: #16a34a; margin-top: 0; }
        .list-group { background: white; padding: 15px 15px 15px 35px; border-radius: 8px; border: 1px solid #d1d5db; max-height: 200px; overflow-y: auto; font-size: 13.5px; }
        .text-sukses { color: #15803d; font-weight: bold; }
        .text-gagal { color: #b91c1c; font-weight: bold; }
    </style>
</head>
<body>

    <?php if ($is_finished): ?>
    <div class="box report-box">
        <h2 class="report-title">✅ Laporan Pengiriman Pesan</h2>
        <p style="font-size: 15px;">Total Berhasil: <b><?php echo $sukses; ?></b> | Total Gagal: <b><?php echo $gagal; ?></b></p>
        
        <h4 class="text-sukses">Daftar Terkirim (Sukses):</h4>
        <ul class="list-group">
            <?php 
            if(count($detail_sukses) > 0) {
                foreach($detail_sukses as $s) { echo "<li style='margin-bottom:5px;'>$s</li>"; }
            } else {
                echo "<li style='color:#6b7280;'>Tidak ada pesan yang berhasil terkirim.</li>";
            }
            ?>
        </ul>

        <h4 class="text-gagal">Daftar Gagal:</h4>
        <ul class="list-group" style="border-color:#fca5a5; background:#fef2f2;">
            <?php 
            if(count($detail_gagal) > 0) {
                foreach($detail_gagal as $g) { echo "<li style='margin-bottom:5px; color:#991b1b;'>$g</li>"; }
            } else {
                echo "<li style='color:#6b7280;'>Semua pesan berhasil terkirim, tidak ada yang gagal.</li>";
            }
            ?>
        </ul>

        <a href="admin.php" style="display:inline-block; padding:10px 20px; background:#4f46e5; color:white; text-decoration:none; border-radius:6px; font-weight:bold; margin-top:20px;">Kembali ke Dasbor</a>
        <a href="broadcast_wa.php" style="display:inline-block; padding:10px 20px; background:#64748b; color:white; text-decoration:none; border-radius:6px; font-weight:bold; margin-top:20px; margin-left:10px;">Kirim Pesan Lainnya</a>
    </div>
    <?php else: ?>

    <div class="box">
        <h2>📢 Siaran WhatsApp Massal</h2>
        <form method="POST" onsubmit="return confirm('Anda yakin akan mengirim pesan ini ke semua kontak yang dipilih?')">
            
            <label>Pilih Gelombang Target</label>
            <select name="gelombang">
                <option value="Semua">Semua Gelombang</option>
                <option value="1">Gelombang 1</option>
                <option value="2">Gelombang 2</option>
            </select>

            <label>Pilih Status Target</label>
            <select name="status">
                <option value="Semua">Kirim ke SEMUA pendaftar</option>
                <option value="LULUS">Hanya yang LULUS (Dalam Kuota)</option>
                <option value="Tidak Jadi">Hanya yang BATAL / TIDAK LOLOS</option>
                <option value="Menunggu">Hanya yang MENUNGGU</option>
            </select>

            <label>Isi Pesan WhatsApp</label>
            <div class="var-box">
                <b>[NAMA], [NISN], [NO_DAFTAR], [JURUSAN], [GELOMBANG], [STATUS], [ALASAN], [LINK_BUKTI]</b>
            </div>
            <textarea name="pesan" rows="10" required>Halo Bapak/Ibu Calon Wali Murid dari *[NAMA]* (NISN: [NISN]).

Berdasarkan hasil seleksi Panitia SPMB SMK PERMATA BUNDA I JAKARTA, kami menginformasikan bahwa status pendaftaran putra/putri Anda saat ini adalah: *[STATUS]*.[ALASAN]

Jurusan: *[JURUSAN]*
Jalur: *Gelombang [GELOMBANG]*

Silakan unduh atau cetak dokumen hasil seleksi pada tautan berikut:
[LINK_BUKTI]

Silahkan lihat hasil live board pada tautan berikut
*https://smkpb1.my.id/spmb/spmbsmk/live_board.php*

Jika Lulus/Kurang Berkas silahkan serahkan lalu ambil formulir daftar ulang WIB ke sekolah
Lalu kembalikan formulir Daftar ulang pada 2-3 Juli 08.00 - 12.00 WIB

Informasi hanya pada nomor :
Admin Sekolah: 081313841410 / 081316061113

Terima kasih.</textarea>

            <button type="submit" name="kirim_broadcast">🚀 Mulai Kirim Pesan</button>
            <a href="admin.php" style="display:block; text-align:center; margin-top:15px; color:#64748b; text-decoration:none;">Kembali ke Dasbor</a>
        </form>
    </div>
    <?php endif; ?>
</body>
</html>