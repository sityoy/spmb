<?php
// Ambil tanggal server otomatis hari ini
$tanggal_sekarang = new DateTime(); 
// Set batas bayar sesuai tanggal peringatan
$batas_bayar = new DateTime('2026-07-11'); 

// Hitung selisih hari (otomatis minus kalau telat)
$selisih = (int) $tanggal_sekarang->diff($batas_bayar)->format('%r%a'); 

if ($selisih <= 3) { 
    $teks_hari = ($selisih < 0) ? "TERLAMBAT " . abs($selisih) . " HARI" : $selisih . " hari lagi";
    $warna_bg = ($selisih < 0) ? "#7f1d1d" : "#fee2e2"; 
    $warna_teks = ($selisih < 0) ? "#fecaca" : "#991b1b";

    echo "
    <div style='background: $warna_bg; border: 1px solid #f87171; color: $warna_teks; padding: 15px; border-radius: 12px; margin-bottom: 20px; text-align: center; font-weight: 700; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);'>
        ⚠️ PERINGATAN PEMBAYARAN: Batas pembayaran terakhir adalah 11 Juli 2026. <br>
        <span style='color: #fbbf24;'>Sisa waktu Anda: $teks_hari. Mohon segera selesaikan administrasi!</span><br>
        <!-- Link sudah diperbaiki tanpa hashtag -->
        <a href='tenggat/invoice/invoice-09062026-2724.pdf' target='_blank' style='display:inline-block; margin-top:10px; padding:6px 12px; background:#4f46e5; color:#fff; text-decoration:none; border-radius:6px; font-size:12px;'>Lihat Rincian Invoice</a>
    </div>";
}
?>