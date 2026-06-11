<?php
// Formulir Pendaftaran Offline (Siap Cetak / Print-Ready)
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
session_start();
if (!isset($_SESSION['login'])) {
    die("<h1>Akses Ditolak</h1><p>Hanya Panitia yang dapat mengakses Formulir Offline. Silakan login ke Dasbor Admin terlebih dahulu.</p>");
}
date_default_timezone_set('Asia/Jakarta');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Offline SPMB - SMKS PERMATA BUNDA I</title>
    <link rel="icon" type="image/x-icon" href="logo/logosmkpb.png">
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 13px; line-height: 1.3; color: #000; background: #fff; margin: 0; padding: 10px; }
        .print-container { max-width: 750px; margin: 0 auto; padding: 5px; }
        
        /* Kop Surat Resmi */
        .kop-surat { display: flex; align-items: center; border-bottom: 4px double #000; padding-bottom: 5px; margin-bottom: 12px; text-align: center; }
        .kop-logo { width: 60px; height: auto; }
        .kop-teks { flex: 1; padding: 0 10px; }
        .kop-teks h2 { margin: 0; font-size: 16px; font-weight: bold; letter-spacing: 0.5px; }
        .kop-teks h3 { margin: 1px 0; font-size: 14px; font-weight: bold; }
        .kop-teks p { margin: 1px 0; font-size: 10px; font-style: italic; line-height: 1.2; }

        .judul-form { text-align: center; font-weight: bold; font-size: 14px; text-decoration: underline; margin-bottom: 2px; }
        .sub-judul { text-align: center; font-size: 11px; margin-bottom: 12px; color: #333; }

        /* Desain Blok Tabel Isian */
        .section-title { font-weight: bold; text-transform: uppercase; background: #f2f2f2; padding: 3px 6px; border: 1px solid #000; margin-top: 10px; font-size: 11.5px; }
        .table-isian { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .table-isian td { padding: 4px 6px; vertical-align: middle; border: 1px solid #000; }
        .table-isian td.label { width: 32%; font-size: 12px; }
        .table-isian td.titik { width: 2%; text-align: center; border-right: none; border-left: none; }
        .table-isian td.field { width: 66%; border-left: none; font-size: 12px; }
        
        .kotak-kosong { display: inline-block; width: 11px; height: 11px; border: 1px solid #000; margin-right: 5px; vertical-align: middle; }
        .strip-isi { border-bottom: 1px dotted #000; width: 85%; display: inline-block; height: 12px; }

        /* Desain Tabel Nilai Kombinasi Rapor & TKA */
        .table-nilai { width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 11.5px; }
        .table-nilai th { background: #f2f2f2; padding: 4px; border: 1px solid #000; font-weight: bold; text-align: center; font-size: 11px; }
        .table-nilai td { padding: 4px; border: 1px solid #000; text-align: center; }
        .table-nilai td.mapel { text-align: left; font-weight: bold; padding-left: 6px; width: 35%; }
        .bg-tka-column { background: #fafafa; font-weight: bold; }

        /* Sesi Tanda Tangan */
        .tabel-ttd { width: 100%; margin-top: 10px; border-collapse: collapse; font-size: 12px; }
        .tabel-ttd td { width: 50%; text-align: center; vertical-align: top; padding-bottom: 35px; }

        /* Slip Tanda Terima Panitia */
        .slip-tanda-terima { margin-top: 15px; border-top: 2px dashed #000; padding-top: 10px; page-break-inside: avoid; }
        
        /* Proteksi Layout Cetak Kertas A4 Fit */
        @media print {
            @page { size: A4; margin: 0.8cm 1cm 0.8cm 1cm; }
            body { padding: 0; margin: 0; font-size: 11.5px; background: #fff; margin-bottom: 20px;}
            .print-container { max-width: 100%; padding: 0; }
            .no-print { display: none; }
            .slip-tanda-terima { page-break-inside: avoid; break-inside: avoid; }
            .custom-upload-box { display: none; }
        }

        /* Tombol Aksi */
        .action-bar { background: #f1f5f9; padding: 10px; border-radius: 8px; display: flex; gap: 10px; justify-content: center; margin-bottom: 15px; border: 1px solid #cbd5e1; }
        .btn { padding: 6px 14px; font-size: 13px; font-weight: bold; border-radius: 4px; cursor: pointer; text-decoration: none; border: none; }
        .btn-print { background: #4f46e5; color: white; }
        .btn-back { background: #64748b; color: white; }
        
        /* Footer Watermark */
        .footer-watermark {
            position: fixed;
            bottom: 0;
            left: 0;
            font-size: 10px;
            color: #999;
            font-family: Arial, sans-serif;
            padding: 10px;
            z-index: -1;
        }

        /* Watermark Tengah Transparan */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(99, 81, 81, 0.3);
            z-index: -1;
            pointer-events: none;
            font-weight: bold;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    
<div class="print-container no-print">
    <div class="action-bar">
        <button onclick="window.print()" class="btn btn-print">🖨️ Cetak / Simpan PDF Formulir (A4)</button>
        <a href="admin.php" class="btn btn-back">⬅️ Kembali ke Dasbor Admin</a>
    </div>
</div>

<div class="print-container">
    
    <div class="kop-surat">
        <img src="logo/logosmkpb.png" class="kop-logo" alt="Logo SMK">
        <div class="kop-teks">
            <h2>YAYASAN PERMATA BUNDA JAKARTA</h2>
            <h3>SMKS PERMATA BUNDA I JAKARTA</h3>
            <p>(BIDANG KEAHLIAN BISNIS DAN MANAJEMEN)</p>
            <p>Jl. Jamblang Raya No.2 LM Jakarta Barat (11270)</p>
            <p>Telp. (021) 6318076 - (021) 6318850</p>
        </div>
        <img src="logo/logopemda.png" class="kop-logo" alt="Logo DKI">
    </div>

    <div class="judul-form">FORMULIR PENDAFTARAN PESERTA DIDIK BARU</div>
    <div class="sub-judul">Sistem Penerimaan Murid Baru - Program Sekolah Gratis 2026</div>

    <form>
        <div class="section-title">Pilihan Kompetensi Keahlian (Isi Dengan Tanda Centang ✓)</div>
        <table class="table-isian">
            <tr>
                <td style="padding: 6px; text-align: center;">
                    <span class="kotak-kosong"></span> Akuntansi dan Keuangan Lembaga (AKL)
                    <span style="margin-left: 60px;"></span>
                    <span class="kotak-kosong"></span> Manajemen Perkantoran dan Layanan Bisnis (MPLB)
                </td>
            </tr>
        </table>

        <div class="section-title">A. Data Pribadi Calon Siswa (Tulis Menggunakan Huruf Kapital)</div>
        <table class="table-isian">
            <tr>
                <td class="label">Nama Lengkap (Sesuai Ijazah)</td><td class="titik">:</td>
                <td class="field"><span class="strip-isi" style="width:95%;"></span></td>
            </tr>
            <tr>
                <td class="label">Nomor Induk Siswa Nasional (NISN)</td><td class="titik">:</td>
                <td class="field"><span class="strip-isi" style="width:50%;"></span> (Wajib 10 Digit)</td>
            </tr>
            <tr>
                <td class="label">Nomor Kartu Keluarga (KK)</td><td class="titik">:</td>
                <td class="field"><span class="strip-isi" style="width:50%;"></span> (Khusus KK DKI Jakarta)</td>
            </tr>
            <tr>
                <td class="label">Riwayat Penyakit Khusus</td><td class="titik">:</td>
                <td class="field"><span class="strip-isi" style="width:85%;"></span></td>
            </tr>
            <tr>
                <td class="label">Tempat / Tanggal Lahir</td><td class="titik">:</td>
                <td class="field"><span class="strip-isi" style="width:70%;"></span></td>
            </tr>
            <tr>
                <td class="label">Asal Sekolah (SMP / MTs)</td><td class="titik">:</td>
                <td class="field"><span class="strip-isi" style="width:85%;"></span></td>
            </tr>
            <tr>
                <td class="label">Nomor Seri Ijazah / Sidanira</td><td class="titik">:</td>
                <td class="field"><span class="strip-isi" style="width:85%;"></span></td>
            </tr>
            <tr>
                <td class="label">No. HP / WhatsApp Aktif</td><td class="titik">:</td>
                <td class="field"><span class="strip-isi" style="width:50%;"></span></td>
            </tr>
        </table>

        <div class="section-title">B. Rekapitulasi Nilai Pengetahuan Rapor SMP & Hasil Tes TKA</div>
        <table class="table-nilai">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 4%;">No</th>
                    <th rowspan="2" style="text-align: left; padding-left: 6px;">Mata Pelajaran</th>
                    <th colspan="2">Kelas VII</th>
                    <th colspan="2">Kelas VIII</th>
                    <th>Kelas IX</th>
                    <th rowspan="2" style="width: 16%; background:#e2e8f0; border: 1px solid #000;">NILAI TES TKA<br>(Diisi Panitia)</th>
                </tr>
                <tr>
                    <th style="width: 10%;">Smt 1</th>
                    <th style="width: 10%;">Smt 2</th>
                    <th style="width: 10%;">Smt 1</th>
                    <th style="width: 10%;">Smt 2</th>
                    <th style="width: 10%;">Smt 1</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td><td class="mapel">Pendidikan Pancasila dan Kewarganegaraan (PKN)</td>
                    <td></td><td></td><td></td><td></td><td></td>
                    <td style="background:#f1f5f9; color:#94a3b8; font-size:10px;">Tidak Ada Tes</td>
                </tr>
                <tr>
                    <td>2</td><td class="mapel">Bahasa Indonesia</td>
                    <td></td><td></td><td></td><td></td><td></td>
                    <td class="bg-tka-column"></td>
                </tr>
                <tr>
                    <td>3</td><td class="mapel">Matematika (MTK)</td>
                    <td></td><td></td><td></td><td></td><td></td>
                    <td class="bg-tka-column"></td>
                </tr>
                <tr>
                    <td>4</td><td class="mapel">Ilmu Pengetahuan Alam (IPA)</td>
                    <td></td><td></td><td></td><td></td><td></td>
                    <td style="background:#f1f5f9; color:#94a3b8; font-size:10px;">Tidak Ada Tes</td>
                </tr>
                <tr>
                    <td>5</td><td class="mapel">Ilmu Pengetahuan Sosial (IPS)</td>
                    <td></td><td></td><td></td><td></td><td></td>
                    <td style="background:#f1f5f9; color:#94a3b8; font-size:10px;">Tidak Ada Tes</td>
                </tr>
                <tr>
                    <td>6</td><td class="mapel">Bahasa Inggris</td>
                    <td></td><td></td><td></td><td></td><td></td>
                    <td style="background:#f1f5f9; color:#94a3b8; font-size:10px;">Tidak Ada Tes</td>
                </tr>
                <tr style="font-weight:bold;">
                    <td>7</td><td style="background:#eef2ff;">RATA-RATA RAPOR (SIDANIRA)</td><td style="background:#eef2ff;"></td><td style="background:#eef2ff;"></td><td style="background:#eef2ff;"></td><td style="background:#eef2ff;"></td><td style="background:#eef2ff;"></td>
                    <td style="background:red; color:white; font-size:10px;">TKA</td>
                </tr>
                <tr style="font-weight:bold">
                    <td>8</td><td style="background:#eef2ff;">RATA-RATA TKA (B. Indo & MTK)</td>
                    <td style="background:#f1f5f9; color:#94a3b8; font-size:10px;">SIDANIRA</td><td style="background:#f1f5f9; color:#94a3b8; font-size:10px;">SIDANIRA</td><td style="background:#f1f5f9; color:#94a3b8; font-size:10px;">SIDANIRA</td>
                    <td style="background:#f1f5f9; color:#94a3b8; font-size:10px;">SIDANIRA</td><td style="background:#f1f5f9; color:#94a3b8; font-size:10px;">SIDANIRA</td> <td></td>
                    
                </tr>
            </tbody>
        </table>
        <table class="table-nilai" style="margin-top: -5px;">
    
    
    <tr style="background:white; color:black; font-weight:bold;">
        <td style="text-align: center; padding: 10px;">NILAI AKHIR (Rata-rata Rapor + Rata-rata TKA) / 2</td>
        <td style="width: 25%;"></td>
    </tr>
</table>

        <div class="section-title">C. Kepemilikan Jaminan Finansial Daerah</div>
        <table class="table-isian">
            <tr>
                <td class="label">Memiliki Kartu Jakarta Pintar (KJP)</td><td class="titik">:</td>
                <td class="field">
                    <span class="kotak-kosong"></span> Tidak Punya 
                    <span style="margin-left:40px;"></span>
                    <span class="kotak-kosong"></span> Ya, Memiliki KJP Aktif
                </td>
            </tr>
            <tr>
                <td class="label">Nomor Rekening Bank DKI (Jika KJP)</td><td class="titik">:</td>
                <td class="field"><span class="strip-isi" style="width:60%;"></span></td>
            </tr>
        </table>

        <p style="font-size: 10px; text-align: justify; margin: 6px 0;">
            Segala data nilai dan identitas yang diisikan dalam formulir manual luring ini adalah benar sesuai aslinya dan akan dipertanggungjawabkan keabsahannya berdasarkan dokumen fisik pendukung pendaftaran PPDB Sekolah Gratis SMKS PERMATA BUNDA I JAKARTA
        </p>

        <table class="tabel-ttd">
            <tr>
                <td>
                    Mengetahui,<br>Orang Tua / Wali Calon Siswa<br><br><br><br>
                    ( .................................................... )
                </td>
                <td>
                    Jakarta, ............................ 2026<br>Calon Pendaftar Siswa Baru<br><br><br><br>
                    ( .................................................... )
                </td>
            </tr>
        </table>

        <div class="slip-tanda-terima">
            <div class="judul-form" style="font-size:12px; text-decoration:none; margin-bottom:4px;">✂️ LEMBAR KENDALI VERIFIKASI PANITIA & BUKTI SERAH TERIMA DOKUMEN</div>
            
            <table style="width:100%; border-collapse:collapse; font-size:11px; margin-bottom:5px;">
                <tr>
                    <td style="border:1px solid #000; padding:4px; width:40%;"><b>Nama Pendaftar:</b> ........................................</td>
                    <td style="border:1px solid #000; padding:4px; width:30%;"><b>NISN:</b> ........................................</td>
                    <td style="border:1px solid #000; padding:4px; width:30%;"><b>Tanggal:</b> ........................ 2026</td>
                </tr>
            </table>

            <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                <thead>
                    <tr style="background:#f2f2f2;">
                        <th style="border:1px solid #000; padding:3px; width:4%;">No</th>
                        <th style="border:1px solid #000; padding:3px; text-align:left;">Nama Dokumen Persyaratan Fisik</th>
                        <th style="border:1px solid #000; padding:3px; width:15%;">Ada (Lengkap)</th>
                        <th style="border:1px solid #000; padding:3px; width:15%;">Tidak Ada</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td style="border:1px solid #000; text-align:center; padding:2px;">1</td><td style="border:1px solid #000; padding:2px;">Fotokopi Rapor Lengkap Semester 1 s.d 5</td><td style="border:1px solid #000; text-align:center;">[  ]</td><td style="border:1px solid #000; text-align:center;">[  ]</td></tr>
                    <tr><td style="border:1px solid #000; text-align:center; padding:2px;">2</td><td style="border:1px solid #000; padding:2px;">Fotokopi Ijazah / Surat Keterangan Lulus (SKL) Terlegalisir</td><td style="border:1px solid #000; text-align:center;">[  ]</td><td style="border:1px solid #000; text-align:center;">[  ]</td></tr>
                    <tr><td style="border:1px solid #000; text-align:center; padding:2px;">3</td><td style="border:1px solid #000; padding:2px;">Fotokopi Kartu Keluarga (KK) Wilayah DKI Jakarta</td><td style="border:1px solid #000; text-align:center;">[  ]</td><td style="border:1px solid #000; text-align:center;">[  ]</td></tr>
                    <tr><td style="border:1px solid #000; text-align:center; padding:2px;">4</td><td style="border:1px solid #000; padding:2px;">Fotokopi Akte Kelahiran Calon Siswa</td><td style="border:1px solid #000; text-align:center;">[  ]</td><td style="border:1px solid #000; text-align:center;">[  ]</td></tr>
                    <tr><td style="border:1px solid #000; text-align:center; padding:2px;">5</td><td style="border:1px solid #000; padding:2px;">Fotokopi KTP Orang Tua (Ayah dan Ibu Kandung)</td><td style="border:1px solid #000; text-align:center;">[  ]</td><td style="border:1px solid #000; text-align:center;">[  ]</td></tr>
                    <tr><td style="border:1px solid #000; text-align:center; padding:2px;">6</td><td style="border:1px solid #000; padding:2px;">Surat Pertanggungjawaban Mutlak (SPTJM) Asli Bermaterai 10.000</td><td style="border:1px solid #000; text-align:center;">[  ]</td><td style="border:1px solid #000; text-align:center;">[  ]</td></tr>
                    <tr><td style="border:1px solid #000; text-align:center; padding:2px;">7</td><td style="border:1px solid #000; padding:2px;">Fotokopi Buku Tabungan KJP Halaman Depan (Bagi Pemilik KJP)</td><td style="border:1px solid #000; text-align:center;">[  ]</td><td style="border:1px solid #000; text-align:center;">[  ]</td></tr>
                </tbody>
            </table>

            <table style="width: 100%; margin-top: 10px; font-size:11px;">
                <tr>
                    <td style="width: 50%; text-align: center; padding-bottom:20px;">Paraf Penyerah Berkas (Wali Murid)<br><br><br><br>( .................................................... )</td>
                    <td style="width: 50%; text-align: center; padding-bottom:20px;">Tim Verifikator PPDB Offline<br><br><br><br>( .................................................... )</td>
                </tr>
            </table>
        </div>
    </form>
</div>
<!-- <div class="watermark">SIS.COM</div> -->
</body>
<!-- <div class="footer-watermark"><i>SIS.COM</i></div> -->
</html>