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

    .navbar-brand img {
        max-width: 200px;
        height: auto;
    }

    /* Navbar Text untuk User yang Login */
    .navbar-text {
        font-weight: 500;
        font-size: 0.95rem;
        color: var(--text-gray) !important;
        padding: 0.5rem 1rem;
        background: var(--light-blue);
        border-radius: 50px;
    }

    .navbar-text i {
        font-size: 1.1rem;
        margin-right: 0.25rem;
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

    /* Logout Button */
    .btn-outline-danger {
        border: 2px solid var(--accent-red);
        color: var(--accent-red);
        transition: all 0.3s;
    }

    .btn-outline-danger:hover {
        background: var(--accent-red);
        color: white;
        transform: scale(1.05);
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

        0%,
        100% {
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

        0%,
        100% {
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

    /* How It Works */
    .how-it-works {
        padding: 60px 0;
        background: var(--bg-light);
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

    /* Responsive */
    @media (max-width: 768px) {
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

        .navbar-text {
            display: block;
            text-align: center;
            margin-bottom: 0.5rem;
        }

        .navbar-nav .nav-item form {
            width: 100%;
        }

        .navbar-nav .nav-item form button {
            width: 100%;
            margin: 0.25rem 0;
        }

        .btn-primary-custom {
            width: 100%;
            text-align: center;
            margin-bottom: 0.5rem;
        }

        .btn-login,
        .btn-register {
            width: 100%;
            margin: 0.25rem 0;
            text-align: center;
            display: block;
        }

        .hero {
            padding: 90px 0 40px;
        }

        .hero h1 {
            font-size: 2rem;
        }

        .btn-secondary-custom {
            width: 100%;
        }
    }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <img src="{{ asset('images/logo-lsp.png') }}" alt="LSP Logo">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    @auth
                    {{-- ✅ Jika sudah login --}}
                    <li class="nav-item me-3">
                        <span class="navbar-text text-muted">
                            <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                        </span>
                    </li>
                    <li class="nav-item">
                        @if(Auth::user()->isAdmin())
                        <a class="btn btn-primary-custom" href="{{ route('admin.dashboard') }}"
                            style="padding: 0.5rem 1.5rem; font-size: 1rem;">
                            <i class="bi bi-speedometer2"></i> Dashboard Admin
                        </a>
                        @elseif(Auth::user()->isTuk())
                        <a class="btn btn-primary-custom" href="{{ route('tuk.dashboard') }}"
                            style="padding: 0.5rem 1.5rem; font-size: 1rem;">
                            <i class="bi bi-speedometer2"></i> Dashboard TUK
                        </a>
                        @elseif(Auth::user()->isAsesi())
                        <a class="btn btn-primary-custom" href="{{ route('asesi.dashboard') }}"
                            style="padding: 0.5rem 1.5rem; font-size: 1rem;">
                            <i class="bi bi-speedometer2"></i> Dashboard Saya
                        </a>
                        @endif
                    </li>
                    <li class="nav-item ms-lg-2">
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger"
                                style="padding: 0.5rem 1.5rem; border-radius: 50px; font-weight: 600;">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </button>
                        </form>
                    </li>
                    @else
                    {{-- ✅ Jika belum login --}}
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
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="zoom-in-right">
                    @auth
                    @if(Auth::user()->isAdmin())
                    <h1>
                        Selamat Datang, <span class="highlight">Admin</span>!
                    </h1>
                    <p>
                        Kelola sistem sertifikasi profesi, verifikasi data asesi, dan pantau seluruh proses sertifikasi
                        dengan mudah.
                    </p>
                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <i class="bi bi-shield-check me-2" style="font-size: 1.5rem;"></i>
                        <div>
                            <strong>{{ Auth::user()->name }}</strong><br>
                            <small>Administrator Sistem</small>
                        </div>
                    </div>
                    <div class="d-flex gap-3 flex-column flex-sm-row">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary-custom">
                            <i class="bi bi-speedometer2"></i> Dashboard Admin
                        </a>
                        <a href="{{ route('admin.verifications') }}" class="btn btn-secondary-custom">
                            <i class="bi bi-check-circle"></i> Verifikasi Data
                        </a>
                    </div>
                    @elseif(Auth::user()->isTuk())
                    <h1>
                        Selamat Datang, <span class="highlight">TUK</span>!
                    </h1>
                    <p>
                        Kelola pendaftaran kolektif, verifikasi asesi, dan atur jadwal asesmen dengan efisien.
                    </p>
                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <i class="bi bi-building me-2" style="font-size: 1.5rem;"></i>
                        <div>
                            <strong>{{ Auth::user()->name }}</strong><br>
                            <small>Tempat Uji Kompetensi</small>
                        </div>
                    </div>
                    <div class="d-flex gap-3 flex-column flex-sm-row">
                        <a href="{{ route('tuk.dashboard') }}" class="btn btn-primary-custom">
                            <i class="bi bi-speedometer2"></i> Dashboard TUK
                        </a>
                        <a href="{{ route('tuk.collective') }}" class="btn btn-secondary-custom">
                            <i class="bi bi-people"></i> Pendaftaran Kolektif
                        </a>
                    </div>
                    @elseif(Auth::user()->isAsesi())
                    <h1>
                        Selamat Datang, <span class="highlight">{{ Auth::user()->name }}</span>!
                    </h1>
                    <p>
                        Kelola proses sertifikasi Anda, pantau status pendaftaran, dan download sertifikat digital
                        dengan mudah.
                    </p>
                    <div class="alert alert-success d-flex align-items-center" role="alert">
                        <i class="bi bi-person-check-fill me-2" style="font-size: 1.5rem;"></i>
                        <div>
                            <strong>Akun Asesi Aktif</strong><br>
                            <small>Anda dapat mengakses semua fitur sistem</small>
                        </div>
                    </div>
                    <div class="d-flex gap-3 flex-column flex-sm-row">
                        <a href="{{ route('asesi.dashboard') }}" class="btn btn-primary-custom">
                            <i class="bi bi-speedometer2"></i> Dashboard Saya
                        </a>
                        <a href="{{ route('asesi.tracking') }}" class="btn btn-secondary-custom">
                            <i class="bi bi-clock-history"></i> Tracking Status
                        </a>
                    </div>
                    @endif
                    @else
                    {{-- ✅ Guest View --}}
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
                    @endauth
                </div>
                <div class="col-lg-6 mt-4 mt-lg-0" data-aos="zoom-in-left">
                    <img src="https://img.freepik.com/free-vector/online-certification-illustration_23-2148575636.jpg"
                        alt="Hero Illustration" class="hero-illustration">
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works">
        <div class="container">
            @auth
            @if(Auth::user()->isAdmin())
            {{-- Admin View --}}
            <div class="section-title" data-aos="flip-up">
                <h2>Tugas Administrator</h2>
                <div class="underline"></div>
                <p>Kelola sistem sertifikasi dengan efisien</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6" data-aos="fade-right" data-aos-delay="100">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <h4>Kelola TUK & Skema</h4>
                        <p>Tambah dan kelola data TUK serta skema sertifikasi</p>
                        <a href="{{ route('admin.tuks') }}" class="btn btn-primary w-100">
                            <i class="bi bi-building"></i> Kelola TUK
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-right" data-aos-delay="200">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <h4>Verifikasi Data</h4>
                        <p>Verifikasi data asesi dan setujui pendaftaran</p>
                        <a href="{{ route('admin.verifications') }}" class="btn btn-primary w-100">
                            <i class="bi bi-check-circle"></i> Verifikasi
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-right" data-aos-delay="300">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <h4>Monitor Pembayaran</h4>
                        <p>Pantau dan verifikasi pembayaran asesi</p>
                        <a href="{{ route('admin.payments') }}" class="btn btn-primary w-100">
                            <i class="bi bi-cash-coin"></i> Pembayaran
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-right" data-aos-delay="400">
                    <div class="step-card">
                        <div class="step-number">4</div>
                        <h4>Input Hasil & Laporan</h4>
                        <p>Input hasil asesmen dan generate laporan</p>
                        <a href="{{ route('admin.assessments') }}" class="btn btn-primary w-100">
                            <i class="bi bi-file-earmark-text"></i> Asesmen
                        </a>
                    </div>
                </div>
            </div>
            @elseif(Auth::user()->isTuk())
            {{-- TUK View --}}
            <div class="section-title" data-aos="flip-up">
                <h2>Tugas TUK</h2>
                <div class="underline"></div>
                <p>Kelola asesi dan jadwal asesmen</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6" data-aos="fade-right" data-aos-delay="100">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <h4>Daftar Kolektif</h4>
                        <p>Daftarkan asesi secara kolektif dengan mudah</p>
                        <a href="{{ route('tuk.collective') }}" class="btn btn-primary w-100">
                            <i class="bi bi-people"></i> Daftar Kolektif
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-right" data-aos-delay="200">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <h4>Verifikasi Asesi</h4>
                        <p>Verifikasi kelengkapan data asesi yang terdaftar</p>
                        <a href="{{ route('tuk.verifications') }}" class="btn btn-primary w-100">
                            <i class="bi bi-check-circle"></i> Verifikasi
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-right" data-aos-delay="300">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <h4>Buat Jadwal</h4>
                        <p>Atur jadwal asesmen untuk asesi</p>
                        <a href="{{ route('tuk.schedules') }}" class="btn btn-primary w-100">
                            <i class="bi bi-calendar-event"></i> Jadwal
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-right" data-aos-delay="400">
                    <div class="step-card">
                        <div class="step-number">4</div>
                        <h4>Kelola Asesi</h4>
                        <p>Pantau dan kelola data asesi yang terdaftar</p>
                        <a href="{{ route('tuk.asesi') }}" class="btn btn-primary w-100">
                            <i class="bi bi-person-lines-fill"></i> Data Asesi
                        </a>
                    </div>
                </div>
            </div>
            @elseif(Auth::user()->isAsesi())
            {{-- Asesi View --}}
            <div class="section-title" data-aos="flip-up">
                <h2>Cara Menggunakan Sistem</h2>
                <div class="underline"></div>
                <p>Proses sertifikasi yang mudah dalam 4 langkah</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6" data-aos="fade-right" data-aos-delay="100">
                    <div class="step-card">
                        <div class="step-number">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <h4>Akun Terdaftar</h4>
                        <p>Akun Anda sudah terdaftar sebagai Asesi</p>
                        <span class="badge bg-success w-100 py-2">
                            <i class="bi bi-person-check"></i> Sudah Terdaftar
                        </span>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-right" data-aos-delay="200">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <h4>Lengkapi Data</h4>
                        <p>Upload dokumen persyaratan dan pilih skema sertifikasi</p>
                        <a href="{{ route('asesi.complete-data') }}" class="btn btn-primary w-100">
                            <i class="bi bi-pencil"></i> Lengkapi Data
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-right" data-aos-delay="300">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <h4>Bayar & Jadwal</h4>
                        <p>Lakukan pembayaran dan tunggu jadwal asesmen dari TUK</p>
                        <a href="{{ route('asesi.tracking') }}" class="btn btn-primary w-100">
                            <i class="bi bi-clock-history"></i> Tracking
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-right" data-aos-delay="400">
                    <div class="step-card">
                        <div class="step-number">4</div>
                        <h4>Dapatkan Sertifikat</h4>
                        <p>Download sertifikat digital setelah dinyatakan kompeten</p>
                        <a href="{{ route('asesi.certificate') }}" class="btn btn-primary w-100">
                            <i class="bi bi-download"></i> Sertifikat
                        </a>
                    </div>
                </div>
            </div>
            @endif
            @else
            {{-- Guest View --}}
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
                        <a href="{{ route('register') }}" class="btn btn-primary w-100"
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
            @endauth
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
    document.querySelectorAll('.navbar-nav a, .navbar-nav button').forEach(element => {
        element.addEventListener('click', () => {
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