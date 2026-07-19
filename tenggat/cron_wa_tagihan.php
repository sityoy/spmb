<?php
// HANYA JALANKAN VIA CRON JOB SERVER
date_default_timezone_set('Asia/Jakarta');

$tanggal_sekarang = new DateTime();
$batas_bayar = new DateTime('2026-07-11');
$selisih = (int) $tanggal_sekarang->diff($batas_bayar)->format('%r%a');

if ($selisih <= 3) {
    
    $status_waktu = ($selisih < 0) ? "*TERLAMBAT " . abs($selisih) . " HARI*" : "*$selisih HARI LAGI*";
    
    // 3 Nomor tujuan dipisahkan koma
    $nomor_tujuan = "6281313841410,628875139513,6282112113157"; 
    $link_invoice = "https://smkpb1.my.id/spmb/tenggat/invoice/invoice-09062026-2724.pdf";
    
    $pesan = "⚠️ *PENGINGAT PEMBAYARAN SERVER SPMB* ⚠️\n\n";
    $pesan .= "Halo Bapak/Ibu Pimpinan dan Admin SMKS PERMATA BUNDA I JAKARTA,\n\n";
    $pesan .= "Perkenalkan, saya *Lena Septiana* dari *SIS.COM*. Berhubung seluruh rangkaian sistem Pendaftaran SPMB telah selesai dilaksanakan dengan baik, maka sesuai dengan kesepakatan kerja sama, kami mohon izin untuk menyampaikan tagihan layanan server dan sistem SPMB yang saat ini berstatus *UNPAID*.\n\n";
    $pesan .= "📝 *No. Invoice:* #09062026-2724\n";
    $pesan .= "⏳ *Batas Pembayaran:* 11 Juli 2026\n";
    $pesan .= "🚨 *Status Waktu:* $status_waktu\n\n";
    $pesan .= "💰 *TOTAL TAGIHAN: Rp 1.508.500*\n\n";
    $pesan .= "📄 *Lihat Rincian Invoice:* $link_invoice\n\n";
    $pesan .= "💳 *Metode Pembayaran (a/n LENA SEPTIANA):*\n";
    $pesan .= "1. BCA: 5370-2568-63\n";
    $pesan .= "2. Bank Jakarta: 43-2231-0520-1\n\n";
    $pesan .= "Mohon kesediaannya untuk segera menyelesaikan administrasi pembayaran dan mengirimkan bukti transfer kepada kami. Silakan abaikan pesan ini apabila Bapak/Ibu telah melakukan pembayaran.\n\n";
    $pesan .= "Terima kasih atas kerja sama yang terjalin dengan baik,\n*SIS.COM - Software House & IT Solutions*";

    // EKSEKUSI API FONNTE
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://api.fonnte.com/send',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => array(
        'target' => $nomor_tujuan,
        'message' => $pesan,
        'delay' => '30', // <--- INI PENGATURAN DELAY 30 DETIK ANTAR NOMOR
      ),
      CURLOPT_HTTPHEADER => array(
        'Authorization: vV9YVTVBau4HxGJrM1Jk'
      ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    
    // Log hasil untuk pengecekan di file log_wa.txt
    file_put_contents(__DIR__ . '/log_wa.txt', "[" . date('Y-m-d H:i:s') . "] Status: " . $response . PHP_EOL, FILE_APPEND);
    
    echo $response;
    echo " Sistem Cron Berjalan. Pesan log: " . $status_waktu;
}
?>