<?php
        // Logika Peringatan Pembayaran (Paid)
        $tanggal_sekarang = new DateTime('2026-07-10'); // Tanggal hari ini
        $batas_bayar = new DateTime('2026-07-11');
        $selisih = $tanggal_sekarang->diff($batas_bayar)->days;

        if ($tanggal_sekarang <= $batas_bayar) {
            echo "
            <div style='background: #fee2e2; border: 1px solid #f87171; color: #991b1b; padding: 15px; border-radius: 12px; margin-bottom: 20px; text-align: center; font-weight: 700; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);'>
                ⚠️ PERINGATAN PEMBAYARAN: Batas pembayaran terakhir adalah 11 Juli 2026. <br>
                <span style='color: #dc2626;'>Sisa waktu Anda tinggal " . $selisih . " hari lagi. Mohon segera selesaikan administrasi!</span>
            </div>";
        }
    ?>