<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIKAP LSP - Portal Sistem Sertifikasi Profesi</title>

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

        .navbar-brand img {
            max-width: 200px;
            height: auto;
        }

        .btn-login {
            background: transparent;
            border: 2px solid var(--primary-blue);
            color: var(--primary-blue);
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
            white-space: nowrap;
        }

        .btn-login:hover {
            background: var(--primary-blue);
            color: white;
            transform: scale(1.05);
        }

        .btn-register {
            background: var(--accent-red);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
            border: 2px solid var(--accent-red);
            white-space: nowrap;
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
            padding: 120px 0 60px;
            position: relative;
            overflow: hidden;
            min-height: 85vh;
            display: flex;
            align-items: center;
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
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        .hero h1 {
            font-size: 3rem;
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
            font-size: 1.1rem;
            color: var(--text-gray);
            margin-bottom: 2rem;
            line-height: 1.8;
        }

        .hero-illustration {
            max-width: 100%;
            height: auto;
            animation: bounce 3s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-15px);
            }
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
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary-custom:hover {
            background: var(--dark-blue);
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(33, 150, 243, 0.4);
            color: white;
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
            text-decoration: none;
            display: inline-block;
        }

        .btn-secondary-custom:hover {
            background: var(--light-blue);
            color: var(--primary-blue);
        }

        /* Access Cards */
        .access-section {
            padding: 60px 0;
            background: white;
        }

        .section-title {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
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

        .access-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem 2rem;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 2px solid var(--light-blue);
            height: 100%;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .access-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(33, 150, 243, 0.1), transparent);
            transition: left 0.5s;
        }

        .access-card:hover::before {
            left: 100%;
        }

        .access-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 50px rgba(33, 150, 243, 0.2);
            border-color: var(--primary-blue);
        }

        .access-icon {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, var(--primary-blue), var(--sky-blue));
            color: white;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 1.5rem;
            transition: all 0.4s;
        }

        .access-card:hover .access-icon {
            transform: rotateY(360deg) scale(1.1);
        }

        .access-card h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        .access-card p {
            color: var(--text-gray);
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }

        .access-card .btn {
            width: 100%;
            padding: 0.8rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }

        /* How It Works */
        .how-it-works {
            padding: 60px 0;
            background: var(--bg-light);
        }

        .step-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            position: relative;
            border: 2px solid var(--light-blue);
            transition: all 0.4s;
            height: 100%;
        }

        .step-card:hover {
            border-color: var(--primary-blue);
            box-shadow: 0 10px 30px rgba(33, 150, 243, 0.15);
            transform: translateX(5px);
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
            transition: all 0.4s;
        }

        .step-card:hover .step-number {
            transform: rotate(360deg) scale(1.1);
            box-shadow: 0 5px 15px rgba(239, 83, 80, 0.4);
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
            margin-bottom: 1rem;
        }

        /* Footer */
        .footer {
            background: var(--text-dark);
            color: white;
            padding: 30px 0 20px;
        }

        .footer-content {
            text-align: center;
        }

        .footer-bottom {
            padding-top: 1rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        /* Responsive Improvements */
        @media (max-width: 992px) {
            .hero {
                padding: 100px 0 50px;
                min-height: auto;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .section-title h2 {
                font-size: 2.2rem;
            }

            .access-section {
                padding: 50px 0;
            }

            .how-it-works {
                padding: 50px 0;
            }
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 0.75rem 0;
            }

            .navbar-brand img {
                max-width: 180px;
            }

            .navbar-collapse {
                background: white;
                padding: 1rem;
                margin-top: 1rem;
                border-radius: 10px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            }

            .hero {
                padding: 90px 0 40px;
                min-height: auto;
            }

            .hero h1 {
                font-size: 2rem;
                margin-bottom: 1rem;
            }

            .hero p {
                font-size: 0.95rem;
                margin-bottom: 1.5rem;
            }

            .btn-primary-custom,
            .btn-secondary-custom {
                padding: 0.875rem 2rem;
                font-size: 1rem;
                width: 100%;
                text-align: center;
                margin-bottom: 0.75rem;
            }

            .section-title h2 {
                font-size: 1.875rem;
            }

            .section-title p {
                font-size: 1rem;
                padding: 0 1rem;
            }

            .access-card {
                padding: 2rem 1.5rem;
                margin-bottom: 1.5rem;
            }

            .access-icon {
                width: 75px;
                height: 75px;
                font-size: 2.2rem;
            }

            .access-card h3 {
                font-size: 1.3rem;
            }

            .step-card {
                margin-bottom: 1.5rem;
                padding: 1.75rem 1.5rem;
            }

            .step-number {
                width: 55px;
                height: 55px;
                font-size: 1.6rem;
            }

            .step-card h4 {
                font-size: 1.2rem;
            }

            .step-card p {
                font-size: 0.95rem;
            }

            .navbar-nav .nav-item {
                margin: 0.5rem 0;
            }

            .btn-login,
            .btn-register {
                width: 100%;
                margin: 0.25rem 0;
                text-align: center;
                display: block;
            }
        }

        @media (max-width: 576px) {
            .navbar-brand img {
                max-width: 150px;
            }

            .hero {
                padding: 80px 0 30px;
            }

            .hero h1 {
                font-size: 1.75rem;
                line-height: 1.3;
            }

            .hero p {
                font-size: 0.9rem;
                line-height: 1.6;
            }

            .btn-primary-custom,
            .btn-secondary-custom {
                padding: 0.75rem 1.5rem;
                font-size: 0.95rem;
            }

            .section-title {
                margin-bottom: 2rem;
            }

            .section-title h2 {
                font-size: 1.625rem;
            }

            .section-title .underline {
                width: 60px;
                height: 3px;
            }

            .access-section {
                padding: 40px 0;
            }

            .access-card {
                padding: 1.75rem 1.25rem;
            }

            .access-icon {
                width: 70px;
                height: 70px;
                font-size: 2rem;
                margin-bottom: 1.25rem;
            }

            .access-card h3 {
                font-size: 1.2rem;
                margin-bottom: 0.75rem;
            }

            .access-card p {
                font-size: 0.9rem;
                margin-bottom: 1.25rem;
            }

            .how-it-works {
                padding: 40px 0;
            }

            .step-card {
                padding: 1.5rem 1.25rem;
            }

            .step-number {
                width: 50px;
                height: 50px;
                font-size: 1.5rem;
                margin-bottom: 1.25rem;
            }

            .step-card h4 {
                font-size: 1.125rem;
                margin-bottom: 0.75rem;
            }

            .step-card p {
                font-size: 0.875rem;
                line-height: 1.6;
            }

            .step-card .btn {
                font-size: 0.9rem;
                padding: 0.7rem;
            }

            .footer {
                padding: 25px 0 15px;
            }

            .footer-content h5 {
                font-size: 1.1rem;
            }

            .footer-content p {
                font-size: 0.875rem;
            }

            .footer-bottom p {
                font-size: 0.8rem;
            }
        }

        @media (max-width: 400px) {
            .navbar-brand img {
                max-width: 130px;
            }

            .hero h1 {
                font-size: 1.5rem;
            }

            .btn-primary-custom,
            .btn-secondary-custom {
                padding: 0.7rem 1.25rem;
                font-size: 0.9rem;
            }

            .section-title h2 {
                font-size: 1.5rem;
            }

            .access-card,
            .step-card {
                padding: 1.5rem 1rem;
            }
        }

        /* Touch improvements for mobile */
        @media (hover: none) and (pointer: coarse) {
            .btn-login:active,
            .btn-register:active,
            .btn-primary-custom:active,
            .btn-secondary-custom:active {
                transform: scale(0.98);
            }

            .access-card:active {
                transform: scale(0.98);
            }
        }

        /* Prevent horizontal scroll */
        .container {
            max-width: 100%;
            padding-left: 15px;
            padding-right: 15px;
        }

        @media (min-width: 576px) {
            .container {
                max-width: 540px;
            }
        }

        @media (min-width: 768px) {
            .container {
                max-width: 720px;
            }
        }

        @media (min-width: 992px) {
            .container {
                max-width: 960px;
            }
        }

        @media (min-width: 1200px) {
            .container {
                max-width: 1140px;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="{{ asset('images/logo-lsp.png') }}" alt="LSP Logo">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item">
                        <a class="btn btn-login" href="{{ route('login') }}">
                            <i class="bi bi-box-arrow-in-right"></i> Masuk
                        </a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-register" href="{{ route('register') }}">
                            <i class="bi bi-person-plus"></i> Daftar
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="zoom-in-right">
                    <h1>
                        Portal <span class="highlight">Sistem</span> Sertifikasi <span class="accent">Profesi</span>
                    </h1>
                    <p>
                        Akses sistem manajemen sertifikasi profesi secara online. Daftar, kelola proses sertifikasi, dan
                        pantau status sertifikasi Anda dengan mudah dan efisien.
                    </p>
                    <div class="d-flex gap-3 flex-column flex-sm-row">
                        <a href="{{ route('register') }}" class="btn btn-primary-custom">
                            Daftar Sekarang <i class="bi bi-arrow-right"></i>
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-secondary-custom">
                            <i class="bi bi-box-arrow-in-right"></i> Masuk ke Sistem
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 mt-4 mt-lg-0" data-aos="zoom-in-left">
                    <img src="https://img.freepik.com/free-vector/online-certification-illustration_23-2148575636.jpg"
                        alt="Hero Illustration" class="hero-illustration">
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works">
        <div class="container">
            <div class="section-title" data-aos="flip-up">
                <h2>Cara Menggunakan Sistem</h2>
                <div class="underline"></div>
                <p>Proses sertifikasi yang mudah dalam 4 langkah</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6" data-aos="fade-right" data-aos-delay="100">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <h4>Daftar Akun</h4>
                        <p>Buat akun dengan mengisi formulir pendaftaran dan lengkapi data diri Anda</p>
                        <a href="{{ route('register') }}" class="btn btn-primary"
                            style="background: var(--primary-blue); border: none;">
                            <i class="bi bi-person-plus"></i> Daftar Sebagai Asesi
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-right" data-aos-delay="200">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <h4>Lengkapi Data</h4>
                        <p>Upload dokumen persyaratan dan pilih skema sertifikasi yang sesuai</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-right" data-aos-delay="300">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <h4>Bayar & Jadwal</h4>
                        <p>Lakukan pembayaran dan tunggu jadwal asesmen dari TUK</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-right" data-aos-delay="400">
                    <div class="step-card">
                        <div class="step-number">4</div>
                        <h4>Dapatkan Sertifikat</h4>
                        <p>Download sertifikat digital setelah dinyatakan kompeten</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <h5 style="font-weight: 600; margin-bottom: 0.5rem;">SIKAP LSP</h5>
                <p style="color: rgba(255, 255, 255, 0.7); max-width: 600px; margin: 0 auto;">
                    Sistem Informasi Kompetensi dan Asesmen Profesi
                </p>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 LSP-KAP. All Rights Reserved.</p>
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
            duration: 800,
            once: true,
            offset: 50,
            easing: 'ease-out-cubic'
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

        // Close navbar on mobile after clicking a link
        document.querySelectorAll('.navbar-nav a').forEach(link => {
            link.addEventListener('click', () => {
                const navbarCollapse = document.querySelector('.navbar-collapse');
                if (navbarCollapse.classList.contains('show')) {
                    const bsCollapse = new bootstrap.Collapse(navbarCollapse);
                    bsCollapse.hide();
                }
            });
        });
    </script>
</body>

</html>