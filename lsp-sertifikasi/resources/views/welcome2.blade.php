<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIKAP LSP - Sistem Informasi Kompetensi dan Asesmen Profesi</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
    :root {
        --primary-blue: #2196F3;
        --light-blue: #E3F2FD;
        --sky-blue: #64B5F6;
        --dark-blue: #1976D2;
        --accent-red: #EF5350;
        --dark-red: #D32F2F;
        --text-dark: #212121;
        --text-gray: #616161;
        --bg-light: #F5F5F5;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Poppins', sans-serif;
        color: var(--text-dark);
        overflow-x: hidden;
    }

    /* Navbar */
    .navbar {
        background: white;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        padding: 1rem 0;
        transition: all 0.3s ease;
    }

    .navbar.scrolled {
        padding: 0.5rem 0;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .navbar-brand {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-blue);
    }

    .navbar-brand span {
        color: var(--accent-red);
    }

    .nav-link {
        color: var(--text-dark);
        font-weight: 500;
        margin: 0 0.5rem;
        transition: color 0.3s;
    }

    .nav-link:hover {
        color: var(--primary-blue);
    }

    .btn-login {
        background: transparent;
        border: 2px solid var(--primary-blue);
        color: var(--primary-blue);
        padding: 0.5rem 1.5rem;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-login:hover {
        background: var(--primary-blue);
        color: white;
    }

    .btn-register {
        background: var(--accent-red);
        color: white;
        padding: 0.5rem 1.5rem;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s;
        border: 2px solid var(--accent-red);
    }

    .btn-register:hover {
        background: var(--dark-red);
        border-color: var(--dark-red);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(239, 83, 80, 0.3);
    }

    /* Hero Section */
    .hero {
        background: linear-gradient(135deg, var(--light-blue) 0%, white 100%);
        padding: 120px 0 80px;
        position: relative;
        overflow: hidden;
    }

    .hero::before {
        content: '';
        position: absolute;
        width: 500px;
        height: 500px;
        background: var(--primary-blue);
        opacity: 0.05;
        border-radius: 50%;
        top: -200px;
        right: -200px;
    }

    .hero h1 {
        font-size: 3.5rem;
        font-weight: 800;
        color: var(--text-dark);
        margin-bottom: 1.5rem;
        line-height: 1.2;
    }

    .hero h1 .highlight {
        color: var(--primary-blue);
    }

    .hero h1 .accent {
        color: var(--accent-red);
    }

    .hero p {
        font-size: 1.2rem;
        color: var(--text-gray);
        margin-bottom: 2rem;
        line-height: 1.8;
    }

    .hero-illustration {
        max-width: 100%;
        height: auto;
    }

    .btn-primary-custom {
        background: var(--primary-blue);
        color: white;
        padding: 1rem 2.5rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1.1rem;
        border: none;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(33, 150, 243, 0.3);
    }

    .btn-primary-custom:hover {
        background: var(--dark-blue);
        transform: translateY(-3px);
        box-shadow: 0 6px 25px rgba(33, 150, 243, 0.4);
    }

    .btn-secondary-custom {
        background: white;
        color: var(--primary-blue);
        padding: 1rem 2.5rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1.1rem;
        border: 2px solid var(--primary-blue);
        transition: all 0.3s;
    }

    .btn-secondary-custom:hover {
        background: var(--light-blue);
    }

    /* Features Section */
    .features {
        padding: 80px 0;
        background: white;
    }

    .section-title {
        text-align: center;
        margin-bottom: 3rem;
    }

    .section-title h2 {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 1rem;
    }

    .section-title .underline {
        width: 80px;
        height: 4px;
        background: var(--accent-red);
        margin: 0 auto 1rem;
        border-radius: 2px;
    }

    .section-title p {
        color: var(--text-gray);
        font-size: 1.1rem;
    }

    .feature-card {
        background: white;
        border-radius: 20px;
        padding: 2.5rem;
        text-align: center;
        transition: all 0.3s;
        border: 2px solid var(--light-blue);
        height: 100%;
    }

    .feature-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(33, 150, 243, 0.15);
        border-color: var(--primary-blue);
    }

    .feature-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--primary-blue), var(--sky-blue));
        color: white;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        margin: 0 auto 1.5rem;
        transition: all 0.3s;
    }

    .feature-card:hover .feature-icon {
        transform: rotateY(360deg);
    }

    .feature-card h3 {
        font-size: 1.4rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: var(--text-dark);
    }

    .feature-card p {
        color: var(--text-gray);
        line-height: 1.7;
    }

    /* How It Works */
    .how-it-works {
        padding: 80px 0;
        background: var(--bg-light);
    }

    .step-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        position: relative;
        border: 2px solid var(--light-blue);
        transition: all 0.3s;
    }

    .step-card:hover {
        border-color: var(--primary-blue);
        box-shadow: 0 10px 30px rgba(33, 150, 243, 0.1);
    }

    .step-number {
        width: 60px;
        height: 60px;
        background: var(--accent-red);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
    }

    .step-card h4 {
        font-size: 1.3rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: var(--text-dark);
    }

    .step-card p {
        color: var(--text-gray);
        line-height: 1.7;
    }

    /* Stats Section */
    .stats {
        background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
        padding: 60px 0;
        color: white;
    }

    .stat-item {
        text-align: center;
        padding: 1.5rem;
    }

    .stat-number {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        font-size: 1.1rem;
        opacity: 0.9;
    }

    /* Roles Section */
    .roles {
        padding: 80px 0;
        background: white;
    }

    .role-card {
        background: white;
        border-radius: 20px;
        padding: 2.5rem;
        text-align: center;
        border: 2px solid var(--light-blue);
        transition: all 0.3s;
        height: 100%;
    }

    .role-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(33, 150, 243, 0.15);
        border-color: var(--primary-blue);
    }

    .role-icon {
        width: 100px;
        height: 100px;
        background: var(--light-blue);
        color: var(--primary-blue);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        margin: 0 auto 1.5rem;
        transition: all 0.3s;
    }

    .role-card:hover .role-icon {
        background: var(--primary-blue);
        color: white;
    }

    .role-card h3 {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: var(--text-dark);
    }

    .role-card ul {
        list-style: none;
        padding: 0;
        text-align: left;
    }

    .role-card ul li {
        padding: 0.5rem 0;
        color: var(--text-gray);
        position: relative;
        padding-left: 1.5rem;
    }

    .role-card ul li::before {
        content: '\F26A';
        font-family: 'bootstrap-icons';
        position: absolute;
        left: 0;
        color: var(--primary-blue);
    }

    /* CTA Section */
    .cta {
        background: linear-gradient(135deg, var(--light-blue) 0%, white 100%);
        padding: 80px 0;
        position: relative;
        overflow: hidden;
    }

    .cta::before {
        content: '';
        position: absolute;
        width: 400px;
        height: 400px;
        background: var(--accent-red);
        opacity: 0.05;
        border-radius: 50%;
        bottom: -200px;
        left: -200px;
    }

    .cta h2 {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 1.5rem;
    }

    .cta p {
        font-size: 1.2rem;
        color: var(--text-gray);
        margin-bottom: 2rem;
    }

    /* Footer */
    .footer {
        background: var(--text-dark);
        color: white;
        padding: 60px 0 30px;
    }

    .footer h5 {
        font-weight: 600;
        margin-bottom: 1.5rem;
    }

    .footer ul {
        list-style: none;
        padding: 0;
    }

    .footer ul li {
        margin-bottom: 0.8rem;
    }

    .footer a {
        color: rgba(255, 255, 255, 0.7);
        text-decoration: none;
        transition: color 0.3s;
    }

    .footer a:hover {
        color: var(--primary-blue);
    }

    .social-links a {
        display: inline-block;
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        text-align: center;
        line-height: 40px;
        margin-right: 0.5rem;
        transition: all 0.3s;
    }

    .social-links a:hover {
        background: var(--primary-blue);
        transform: translateY(-3px);
    }

    .footer-bottom {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        margin-top: 3rem;
        padding-top: 2rem;
        text-align: center;
        color: rgba(255, 255, 255, 0.7);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .hero h1 {
            font-size: 2.5rem;
        }

        .hero p {
            font-size: 1rem;
        }

        .section-title h2 {
            font-size: 2rem;
        }

        .stat-number {
            font-size: 2rem;
        }
    }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                SIKAP <span>LSP</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="#beranda">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#fitur">Fitur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#cara-kerja">Cara Kerja</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#peran">Peran</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#kontak">Kontak</a>
                    </li>
                    <li class="nav-item ms-3">
                        <a class="btn btn-login" href="{{ route('login') }}">
                            <i class="bi bi-box-arrow-in-right"></i> Masuk
                        </a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-register" href="{{ route('register') }}">
                            <i class="bi bi-person-plus"></i> Daftar
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="beranda">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <h1>
                        Sistem Informasi <span class="highlight">Kompetensi</span> dan <span class="accent">Asesmen
                            Profesi</span>
                    </h1>
                    <p>
                        Platform digital terintegrasi untuk manajemen sertifikasi profesi yang efisien, transparan, dan
                        terpercaya. Kelola seluruh proses sertifikasi dari pendaftaran hingga penerbitan sertifikat
                        dalam satu sistem.
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="{{ route('register') }}" class="btn btn-primary-custom">
                            Mulai Sekarang <i class="bi bi-arrow-right"></i>
                        </a>
                        <a href="#cara-kerja" class="btn btn-secondary-custom">
                            <i class="bi bi-play-circle"></i> Pelajari Lebih Lanjut
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 mt-5 mt-lg-0" data-aos="fade-left">
                    <img src="https://img.freepik.com/free-vector/online-certification-illustration_23-2148575636.jpg"
                        alt="Hero Illustration" class="hero-illustration">
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-item">
                        <div class="stat-number">1000+</div>
                        <div class="stat-label">Asesi Tersertifikasi</div>
                    </div>
                </div>
                <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-item">
                        <div class="stat-number">50+</div>
                        <div class="stat-label">TUK Terdaftar</div>
                    </div>
                </div>
                <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-item">
                        <div class="stat-number">25+</div>
                        <div class="stat-label">Skema Sertifikasi</div>
                    </div>
                </div>
                <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="stat-item">
                        <div class="stat-number">99%</div>
                        <div class="stat-label">Kepuasan Pengguna</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="fitur">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Fitur Unggulan</h2>
                <div class="underline"></div>
                <p>Solusi lengkap untuk manajemen sertifikasi profesi yang modern dan efisien</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-person-check"></i>
                        </div>
                        <h3>Pendaftaran Online</h3>
                        <p>Daftar sertifikasi secara online kapan saja, di mana saja dengan proses yang cepat dan
                            mudah</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <h3>Penjadwalan Otomatis</h3>
                        <p>Sistem penjadwalan asesmen yang fleksibel dan terintegrasi dengan notifikasi otomatis</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-credit-card"></i>
                        </div>
                        <h3>Pembayaran Digital</h3>
                        <p>Integrasi payment gateway untuk pembayaran yang aman, cepat, dan terpercaya</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <h3>Dokumen Digital</h3>
                        <p>Manajemen dokumen digital yang terorganisir dengan sistem penyimpanan cloud yang aman</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <h3>Tracking Progress</h3>
                        <p>Pantau status sertifikasi Anda secara real-time dari pendaftaran hingga penerbitan</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="600">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-award"></i>
                        </div>
                        <h3>Sertifikat Digital</h3>
                        <p>Sertifikat digital yang terverifikasi dan dapat diunduh kapan saja untuk keperluan Anda</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works" id="cara-kerja">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Cara Kerja</h2>
                <div class="underline"></div>
                <p>Proses sertifikasi yang mudah dan efisien dalam 4 langkah sederhana</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <h4>Daftar Akun</h4>
                        <p>Buat akun dan lengkapi profil Anda dengan data yang akurat untuk memulai proses sertifikasi
                        </p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <h4>Pilih Skema</h4>
                        <p>Pilih skema sertifikasi yang sesuai dengan kebutuhan dan kompetensi Anda dari daftar yang
                            tersedia</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <h4>Ikuti Asesmen</h4>
                        <p>Hadiri jadwal asesmen sesuai waktu yang telah ditentukan oleh TUK untuk proses penilaian</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="step-card">
                        <div class="step-number">4</div>
                        <h4>Dapatkan Sertifikat</h4>
                        <p>Unduh sertifikat digital Anda setelah dinyatakan kompeten dan selesaikan pembayaran</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Roles Section -->
    <section class="roles" id="peran">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Peran Pengguna</h2>
                <div class="underline"></div>
                <p>Sistem yang dirancang untuk memenuhi kebutuhan setiap pemangku kepentingan</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="role-card">
                        <div class="role-icon">
                            <i class="bi bi-person-badge"></i>
                        </div>
                        <h3>Asesi</h3>
                        <ul>
                            <li>Pendaftaran online mandiri</li>
                            <li>Upload dokumen persyaratan</li>
                            <li>Pembayaran digital</li>
                            <li>Tracking status real-time</li>
                            <li>Download sertifikat digital</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="role-card">
                        <div class="role-icon">
                            <i class="bi bi-building"></i>
                        </div>
                        <h3>TUK</h3>
                        <ul>
                            <li>Registrasi kolektif asesi</li>
                            <li>Verifikasi data asesi</li>
                            <li>Penjadwalan asesmen</li>
                            <li>Manajemen pembayaran</li>
                            <li>Laporan dan monitoring</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="role-card">
                        <div class="role-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h3>Admin LSP</h3>
                        <ul>
                            <li>Verifikasi pendaftaran</li>
                            <li>Manajemen skema sertifikasi</li>
                            <li>Penetapan biaya</li>
                            <li>Penerbitan sertifikat</li>
                            <li>Dashboard analitik</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container text-center">
            <div class="row justify-content-center">
                <div class="col-lg-8" data-aos="fade-up">
                    <h2>Siap Memulai Perjalanan Sertifikasi Anda?</h2>
                    <p>Bergabunglah dengan ribuan profesional yang telah mempercayai SIKAP LSP untuk sertifikasi
                        kompetensi mereka</p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="{{ route('register') }}" class="btn btn-primary-custom">
                            Daftar Sekarang <i class="bi bi-arrow-right"></i>
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-secondary-custom">
                            <i class="bi bi-box-arrow-in-right"></i> Masuk
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer" id="kontak">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5>SIKAP LSP</h5>
                    <p class="mb-3">Sistem Informasi Kompetensi dan Asesmen Profesi - Platform digital terpercaya
                        untuk manajemen sertifikasi profesional.</p>
                    <div class="social-links">
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-twitter"></i></a>
                        <a href="#"><i class="bi bi-instagram"></i></a>
                        <a href="#"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Menu</h5>
                    <ul>
                        <li><a href="#beranda">Beranda</a></li>
                        <li><a href="#fitur">Fitur</a></li>
                        <li><a href="#cara-kerja">Cara Kerja</a></li>
                        <li><a href="#peran">Peran</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Layanan</h5>
                    <ul>
                        <li><a href="#">Sertifikasi Profesi</a></li>
                        <li><a href="#">Pelatihan</a></li>
                        <li><a href="#">Konsultasi</a></li>
                        <li><a href="#">Verifikasi Sertifikat</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Kontak</h5>
                    <ul>
                        <li><i class="bi bi-geo-alt"></i> Jakarta, Indonesia</li>
                        <li><i class="bi bi-telephone"></i> +62 21 1234 5678</li>
                        <li><i class="bi bi-envelope"></i> info@sikaplsp.id</li>
                        <li><i class="bi bi-clock"></i> Senin - Jumat, 08:00 - 17:00</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 SIKAP LSP. All Rights Reserved. Developed with excellence.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
    // Initialize AOS
    AOS.init({
        duration: 1000,
        once: true,
        offset: 100
    });

    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // Smooth scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const offsetTop = target.offsetTop - 70;
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
    </script>
</body>

</html>