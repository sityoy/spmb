<?php
session_start();
if (!isset($_SESSION['login'])) { header("Location: login.php"); exit; }
include 'koneksi.php';

// ==========================================
// KONFIGURASI FONNTE API
// ==========================================
$fonnte_token = "AabZp5e5JT61dZpckh8c"; 

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$domain_web = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);

if (isset($_POST['kirim_broadcast'])) {
    set_time_limit(0); 

    $gel = mysqli_real_escape_string($conn, $_POST['gelombang']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $pesan_mentah = $_POST['pesan'];

    // Filter SQL
    $sql_gel = ($gel == 'Semua') ? "" : " AND gelombang = '$gel'";
    $sql_status = ($status == 'Semua') ? "" : " AND status_konfirmasi = '$status'";

    $query = mysqli_query($conn, "SELECT * FROM pendaftar WHERE 1=1 $sql_gel $sql_status");
    
    $sukses = 0;
    $gagal = 0;

    while ($row = mysqli_fetch_assoc($query)) {
        // Pembersihan Nomor WhatsApp
        $no_hp = preg_replace('/[^0-9]/', '', $row['no_whatsapp']);
        $no_wa = (substr($no_hp, 0, 1) == '0') ? '62' . substr($no_hp, 1) : $no_hp;

        if (strlen($no_wa) > 9) {
            $link_bukti = $domain_web . "/bukti.php?no_pendaftaran=" . urlencode($row['no_pendaftaran']);
            
            // Perbaikan Logika: Jika alasan kosong, jangan tampilkan labelnya
            $alasan = !empty($row['alasan_pembatalan']) ? "\nAlasan: " . $row['alasan_pembatalan'] : "";
            
            $pesan_fix = str_replace(
                ['[NAMA]', '[NO_DAFTAR]', '[NISN]', '[JURUSAN]', '[GELOMBANG]', '[STATUS]', '[ALASAN]', '[LINK_BUKTI]'],
                [$row['nama_lengkap'], $row['no_pendaftaran'], $row['nisn'], $row['pilihan_jurusan'], $row['gelombang'], $row['status_konfirmasi'], $alasan, $link_bukti],
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
                    'delay' => '2' 
                ),
                CURLOPT_HTTPHEADER => array("Authorization: $fonnte_token"),
            ));

            $response = curl_exec($curl);
            $res_data = json_decode($response, true);
            
            if (isset($res_data['status']) && $res_data['status'] == true) { 
                $sukses++; 
            } else { 
                $gagal++; 
            }
            curl_close($curl);
            sleep(2); 
        } else {
            $gagal++;
        }
    }
    echo "<script>alert('Selesai! Berhasil: $sukses, Gagal: $gagal'); window.location='admin.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Broadcast WhatsApp - SPMB</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8fafc; padding: 40px; }
        .box { background: white; max-width: 600px; margin: auto; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        h2 { color: #25D366; margin-top: 0; }
        label { font-weight: bold; font-size: 14px; margin-top: 15px; display: block; color: #475569;}
        select, textarea { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 8px; box-sizing: border-box; }
        button { background: #25D366; color: white; border: none; padding: 12px; border-radius: 8px; width: 100%; font-weight: bold; font-size: 16px; margin-top: 20px; cursor: pointer; }
        .var-box { background: #f1f5f9; padding: 10px; border-radius: 8px; font-size: 13px; font-family: monospace; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="box">
        <h2>📢 Siaran WhatsApp Massal</h2>
        <form method="POST" onsubmit="return confirm('Anda yakin akan mengirim pesan ini?')">
            <label>Pilih Gelombang Target</label>
            <select name="gelombang">
                <option value="Semua">Semua Gelombang</option>
                <option value="1">Gelombang 1</option>
                <option value="2">Gelombang 2</option>
            </select>

            <label>Pilih Status Target</label>
            <select name="status">
                <option value="Semua">Kirim ke SEMUA pendaftar</option>
                <option value="LULUS">Hanya yang LULUS</option>
                <option value="Tidak Jadi">Hanya yang TIDAK LULUS</option>
                <option value="Menunggu">Hanya yang MENUNGGU</option>
            </select>

            <label>Isi Pesan WhatsApp</label>
            <div class="var-box">
                <b>[NAMA], [NISN], [NO_DAFTAR], [JURUSAN],SMK [GELOMBANG], [STATUS], [ALASAN], [LINK_BUKTI]</b>
            </div>
            <textarea name="pesan" rows="10" required>Halo Bapak/Ibu Calon Wali Murid dari *[NAMA]* (NISN: [NISN]).

Berdasarkan hasil seleksi Panitia SPMB SMK PERMATA BUNDA I JAKARTA, kami menginformasikan bahwa status pendaftaran putra/putri Anda saat ini adalah: *[STATUS]*.[ALASAN]

Jurusan: *[JURUSAN]*
Gelombang: *[GELOMBANG]*

Silakan unduh dokumen hasil seleksi pada tautan berikut:
[LINK_BUKTI]

Terima kasih.</textarea>

            <button type="submit" name="kirim_broadcast">🚀 Mulai Kirim Pesan</button>
            <a href="admin.php" style="display:block; text-align:center; margin-top:15px; color:#64748b; text-decoration:none;">Kembali</a>
        </form>
    </div>
</body>
</html>