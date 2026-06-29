<?php
// Halaman Utama Profil Resmi SMKS PERMATA BUNDA I JAKARTA
date_default_timezone_set('Asia/Jakarta');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMKS PERMATA BUNDA I JAKARTA - Official Website</title>
    <link rel="icon" type="image/x-icon" href="spmbsmk/logo/logosmkpb.png">

    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --secondary: #0284c7;
            --dark: #0f172a;
            --light: #f8fafc;
            --gray: #64748b;
        }

        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: var(--dark);
            background-color: #fff;
            line-height: 1.6;
        }

        /* Navigation */
        nav {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo-area {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .logo-area img {
            max-height: 50px;
            width: auto;
        }
        .logo-title h1 {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
            color: var(--primary);a
        }
        .logo-title p {
            margin: 0;
            font-size: 11px;
            color: var(--gray);
            font-weight: 600;
        }
        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        .nav-links a {
            text-decoration: none;
            color: var(--dark);
            font-weight: 600;
            font-size: 14px;
            transition: color 0.2s;
        }
        .nav-links a:hover {
            color: var(--primary);
        }
        .btn-spmb-nav {
            background: var(--primary);
            color: #fff !important;
            padding: 8px 16px;
            border-radius: 6px;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
        }
        .btn-spmb-nav:hover {
            background: var(--primary-hover);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #e0e7ff 0%, #f0f9ff 100%);
            padding: 80px 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .hero-container {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }
        .school-tag {
            background: #fff;
            color: var(--primary);
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            display: inline-block;
            margin-bottom: 20px;
        }
        .hero h2 {
            font-size: 38px;
            font-weight: 800;
            margin: 0 0 15px 0;
            color: var(--dark);
            letter-spacing: -0.5px;
            line-height: 1.2;
        }
        .hero p {
            font-size: 16px;
            color: var(--gray);
            margin: 0 0 35px 0;
        }
        .cta-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-main {
            padding: 14px 30px;
            font-size: 15px;
            font-weight: 700;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-primary {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
        }
        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #fff;
            color: var(--dark);
            border: 2px solid #cbd5e1;
        }
        .btn-secondary:hover {
            background: var(--light);
            transform: translateY(-2px);
        }

        /* Jurusan / Kompetensi Keahlian */
        .features {
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 20px;
        }
        .section-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .section-header h3 {
            font-size: 26px;
            font-weight: 800;
            margin: 0 0 10px 0;
        }
        .section-header p {
            color: var(--gray);
            margin: 0;
            font-size: 14px;
        }
        .grid-jurusan {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        .card-jurusan {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 30px;
            transition: all 0.3s;
            position: relative;
        }
        .card-jurusan:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.05);
        }
        .card-akl { border-top: 5px solid var(--primary); }
        .card-mplb { border-top: 5px solid var(--secondary); }
        
        .icon-box {
            font-size: 32px;
            margin-bottom: 15px;
        }
        .card-jurusan h4 {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 10px 0;
        }
        .card-jurusan p {
            font-size: 13.5px;
            color: var(--gray);
            margin: 0 0 20px 0;
            text-align: justify;
        }
        .badge-gratis {
            background: #f0fdf4;
            color: #166534;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            display: inline-block;
        }

        /* Info Alur */
        .info-alur {
            background: var(--light);
            padding: 60px 20px;
        }
        .steps-container {
            max-width: 1000px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .step-item {
            text-align: center;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .step-number {
            width: 35px;
            height: 35px;
            background: var(--primary);
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin: 0 auto 10px auto;
        }
        .step-item h5 {
            margin: 0 0 5px 0;
            font-size: 14px;
            font-weight: 700;
        }
        .step-item p {
            margin: 0;
            font-size: 12px;
            color: var(--gray);
        }

        /* Footer */
        footer {
            background: var(--dark);
            color: #94a3b8;
            padding: 40px 20px;
            font-size: 13px;
            text-align: center;
            border-top: 1px solid #1e293b;
        }
        footer p { margin: 5px 0; }
        footer strong { color: #fff; }

        @media (max-width: 768px) {
            .nav-links { display: none; }
            .hero h2 { font-size: 28px; }
            .hero p { font-size: 14px; }
        }
    </style>
</head>
<body>

    <nav>
        <div class="nav-container">
            <div class="logo-area">
                <img src="spmbsmk/logo/logosmkpb.png" alt="Logo SMK">
                <div class="logo-title">
                    <h1>SMKS PERMATA BUNDA I</h1>
                    <!-- <p>YAYASAN PERMATA BUNDA JAKARTA</p> -->
                </div>
            </div>
            <div class="nav-links">
                <a href="#">Beranda</a>
                <a href="#jurusan">Kompetensi</a>
                <a href="#alur">Alur PPDB</a>
                <a href="spmbsmk/admin/admin.php">Login Admin</a>
                <a href="spmbsmk/index.php" class="btn-spmb-nav">Portal SPMB Online 📝</a>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="hero-container">
            <span class="school-tag">Penerimaan Murid Baru T.A 2026/2027</span>
            <h2>Membangun Generasi Unggul,<br>Siap Kerja & Berakhlak Mulia</h2>
            <p>Selamat datang di Official Website SMKS Permata Bunda I Jakarta. Kami membuka kesempatan emas bergabung dalam Program Sekolah Gratis Tahun Pelajaran 2026/2027 khusus bagi lulusan SMP/MTs sederajat.</p>
            <div class="cta-group">
                <a href="spmbsmk/index.php?page=pendaftaran" class="btn-main btn-primary">📝 Daftar Online Sekarang</a>
                <a href="spmbsmk/formulir_offline.php" target="_blank" class="btn-main btn-secondary">🖨️ Cetak Form Offline</a>
                <a href="spmbsmk/index.php?page=live_board" class="btn-main btn-secondary" style="color: var(--primary); font-weight: bold;">📊 Live Bursa Seleksi</a>
            </div>
        </div>
    </section>

    <section class="features" id="jurusan">
        <div class="section-header">
            <h3>Kompetensi Keahlian Unggulan</h3>
            <p>Kurikulum berbasis industri yang dirancang untuk melahirkan tenaga ahli siap kerja di era digital.</p>
        </div>
        <div class="grid-jurusan">
            <div class="card-jurusan card-akl">
                <div class="icon-box">📈</div>
                <h4>Akuntansi dan Keuangan Lembaga (AKL)</h4>
                <p>Membekali siswa dengan keahlian mengelola transaksi keuangan manual maupun komputerisasi (Spreadsheet & MYOB), administrasi perpajakan, serta pembukuan lembaga bisnis/pemerintah.</p>
                <span class="badge-gratis">✓ Penuh Program Sekolah Gratis</span>
            </div>
            <div class="card-jurusan card-mplb">
                <div class="icon-box">🗂️</div>
                <h4>Manajemen Perkantoran dan Layanan Bisnis (MPLB)</h4>
                <p>Membentuk profesional muda dalam tata kelola perkantoran modern, korespondensi bisnis digital, manajemen kearsipan elektronis, pelayanan prima (*excellent service*), serta administrasi bisnis umum.</p>
                <span class="badge-gratis">✓ Penuh Program Sekolah Gratis</span>
            </div>
        </div>
    </section>

    <section class="info-alur" id="alur">
        <div class="section-header">
            <h3>Alur Sistem Bursa Seleksi Masuk</h3>
            <p>Prosedur pendaftaran program sekolah gratis terintegrasi real-time.</p>
        </div>
        <div class="steps-container">
            <div class="step-item">
                <div class="step-number">1</div>
                <h5>Isi Formulir</h5>
                <p>Isi data diri online di portal SPMB atau bawa form offline manual ke posko sekolah.</p>
            </div>
            <div class="step-item">
                <div class="step-number">2</div>
                <h5>Upload Berkas</h5>
                <p>Unggah scan dokumen fisik asli (Ijazah, KK DKI, Akte, SPTJM, KJP) maks 3MB.</p>
            </div>
            <div class="step-item">
                <div class="step-number">3</div>
                <h5>Uji Kompetensi</h5>
                <p>Mengikuti seleksi nilai berkas Rapor (Sidanira) & Pelaksanaan Tes TKA Panitia.</p>
            </div>
            <div class="step-item">
                <div class="step-number">4</div>
                <h5>Pantau Rangking</h5>
                <p>Pantau pergerakan peringkat bursa seleksi real-time 60 detik di menu Live Board.</p>
            </div>
        </div>
    </section>

    <footer>
        <p><strong>SMKS PERMATA BUNDA I JAKARTA</strong></p>
        <p>Bidang Keahlian Bisnis dan Manajemen | Program Sekolah Gratis 2026</p>
        <p style="font-size:11px; color:#64748b; margin-top:2px;">Jl. Jamblang Raya No.2 LM, Jakarta Barat, DKI Jakarta (11270)</p>
        <p style="font-size:11px; color:#64748b; margin-top:2px;">Telp. (021) 6318076 - (021) 6318850</p>
        <p style="font-size:11.5px; color:#475569; margin-top:2px;">&copy; <?php echo date('Y'); ?> SMKS Permata Banda I Jakarta. All Rights Reserved.</p>
    </footer>

</body>
</html>